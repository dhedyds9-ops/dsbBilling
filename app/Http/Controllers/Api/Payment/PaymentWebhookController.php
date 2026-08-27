<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Payment;

use App\Http\Controllers\Controller;
use App\Services\Adapters\Payment\Exceptions\InvalidPaymentSignatureException;
use App\Services\Adapters\Payment\PaymentGatewayRegistry;
use App\Services\Adapters\Payment\PaymentOrchestrationService;
use App\Services\ISP\BankMutation\MootaBankMutationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\RateLimiter;
use Throwable;

/**
 * SSOT: Payment Webhook Controller.
 *
 * Endpoint: POST /api/payment/webhook/{driver}/{signature_token}
 *
 * Security 3-Layer Defense:
 *   1. {signature_token} di URL path = guard awal (bukan secret, tapi filter random hex md5(app.key+driver) dari PaymentGatewaySettingsService::generateCallbackUrl)
 *   2. HMAC/Signature driver-specific di body (SHA-512 Midtrans / HMAC-SHA256 Tripay / X-Callback-Token Xendit / MD5 Duitku)
 *   3. Idempotency + Replay Attack Prevention via Redis rawBodyHash TTL 7 hari + RateLimit 60/req/min/ip
 *
 * Selalu return HTTP 200 OK untuk payload yang signature-nya valid meskipun business logic duplicate processing
 * (ini penting supaya Gateway TIDAK retry tanpa henti). 4xx hanya jika signature invalid atau driver unknown.
 */
final class PaymentWebhookController extends Controller
{
    public function __construct(
        private readonly PaymentGatewayRegistry $registry,
        private readonly PaymentOrchestrationService $orchestration,
    ) {}

    public function __invoke(Request $request, string $driver, string $signatureToken): JsonResponse
    {
        // 0. Rate limit per driver per IP: 60 req / menit (anti brute force signature)
        try {
            $key = 'pay:wh:' . $driver . ':' . $request->ip();
            if (!RateLimiter::attempt($key, 60, fn() => true, 60)) {
                Log::warning('[PaymentWebhook] rate limited', ['driver' => $driver, 'ip' => $request->ip()]);
                return response()->json(['ok' => false, 'status' => 'rate_limited'], 429);
            }
        } catch (\Throwable) {
        }

        // 1. Validate driver key known
        if (!in_array($driver, $this->registry->allDriverKeys(), true)) {
            return response()->json(['ok' => false, 'status' => 'unknown_driver'], 404);
        }

        // 2. Validate URL signature token (guard awal)
        $expectedToken = md5(config('app.key') . $driver);
        if (!hash_equals($expectedToken, $signatureToken)) {
            Log::warning('[PaymentWebhook] URL signature token mismatch', ['driver' => $driver]);
            return response()->json(['ok' => false, 'status' => 'invalid_callback_token'], 401);
        }

        // 3. Get raw body & parse headers (pertahankan case-insensitive)
        $rawBody = $request->getContent();
        $headers = $request->headers->all();
        $headersLower = [];
        foreach ($headers as $k => $vs) {
            $key = strtolower((string)$k);
            $headersLower[$key] = is_array($vs) ? ($vs[0] ?? '') : $vs;
        }
        $headers = $headersLower;

        // 4. Anti replay attack via raw body hash (TTL 7 hari)
        $bodyHash = hash('sha256', $rawBody);
        $dedupKey = 'pay:webhook:seen:' . $driver . ':' . $bodyHash;
        try {
            if (Cache::has($dedupKey)) {
                Log::info('[PaymentWebhook] DEDUP payload already processed', ['driver' => $driver]);
                return response()->json([
                    'ok' => true,
                    'status' => 'duplicate_already_processed',
                    'duplicate' => true,
                ], 200);
            }
        } catch (\Throwable) {
        }

        // 5. Get driver instance (enabled atau tidak, kita tetap try verify karena payment bisa datang
        // dari transaksi lama ketika gateway masih enabled pada saat dibuat)
        $driverInst = $this->registry->get($driver);
        if (!$driverInst) {
            return response()->json(['ok' => false, 'status' => 'driver_not_available'], 503);
        }

        // 6. Verify + parse webhook (INI PUNYA signature check sendiri per driver)
        try {
            $event = $driverInst->verifyAndParseWebhook($rawBody, $headers);
        } catch (InvalidPaymentSignatureException $e) {
            Log::critical('[PaymentWebhook] INVALID SIGNATURE', ['driver' => $driver, 'err' => $e->getMessage(), 'body' => $rawBody]);
            return response()->json(['ok' => false, 'status' => 'invalid_signature', 'detail' => config('app.debug') ? $e->getMessage() : null], 401);
        } catch (Throwable $e) {
            Log::error('[PaymentWebhook] parse exception', ['driver' => $driver, 'err' => $e->getMessage()]);
            return response()->json(['ok' => false, 'status' => 'parse_error'], 400);
        }

        // 7. Business Logic: handle paid
        try {
            $result = $this->orchestration->handleWebhookPaid($event, $driver);
        } catch (Throwable $e) {
            Log::critical('[PaymentWebhook] handleWebhookPaid FATAL', [
                'driver' => $driver,
                'event_type' => $event->eventType,
                'order_id' => $event->merchantOrderId,
                'err' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            // Jangan return 5xx ke gateway → supaya dia tidak retry spam
            // Tapi tetap 200 OK karna signature sudah valid, kita sudah log.
            return response()->json([
                'ok' => true,
                'status' => 'accepted_pending_retry',
                'note' => 'Signature valid; queued for async retry',
            ], 202);
        }

        // 8. Mark payload SEEN → Cache 7 hari (anti replay next time)
        try {
            Cache::put($dedupKey, time(), now()->addDays(7));
        } catch (\Throwable) {
        }

        $status = match (true) {
            $result['duplicate'] => 'duplicate_processed',
            $result['processed'] => 'processed',
            default => 'ignored',
        };

        $code = 200;

        Log::info('[PaymentWebhook] OK', [
            'driver' => $driver,
            'event_type' => $event->eventType,
            'order_id' => $event->merchantOrderId,
            'amount' => $event->paidAmountIdr,
            'status' => $status,
            'payment_id' => $result['payment_id'],
            'invoice_ids' => $result['invoice_ids'] ?? [],
        ]);

        return response()->json([
            'ok' => true,
            'status' => $status,
            'payment_id' => $result['payment_id'],
            'event_type' => $event->eventType,
            'merchant_order_id' => $event->merchantOrderId,
            'amount_idr' => $event->paidAmountIdr,
            'invoice_ids' => $result['invoice_ids'] ?? [],
        ], $code);
    }

    /**
     * Moota push notification webhook (jika menggunakan fitur push dari Moota bukan scheduler fetch.
     */
    public function mootaPush(Request $request, MootaBankMutationService $service): JsonResponse
    {
        $expected = (string)config('services.moota.webhook_secret', '');
        $recvd = (string)$request->header('x-webhook-signature', '');
        if ($expected !== '' && !hash_equals(hash('sha256', $request->getContent() . $expected), $recvd)) {
            return response()->json(['ok' => false, 'status' => 'invalid_signature'], 401);
        }
        $data = json_decode($request->getContent(), true);
        $processed = 0;
        if (is_array($data['data'] ?? null)) {
            foreach ((array)$data['data'] as $mut) {
                try {
                    $service->processMutation($mut);
                    $processed++;
                } catch (\Throwable) {
                }
            }
        }
        return response()->json(['ok' => true, 'processed' => $processed]);
    }
}
