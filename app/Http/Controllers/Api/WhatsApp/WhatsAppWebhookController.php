<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\WhatsApp;

use App\Services\Notifications\WhatsApp\WaGatewayRegistry;
use App\Services\Notifications\WhatsApp\WhatsAppNotificationService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\RateLimiter;

/**
 * SSOT: Incoming WhatsApp Webhook Controller — untuk semua 4 driver.
 *
 * Route pattern: POST /api/whatsapp/webhook/{driver}/{sigToken}
 *   • driver  = fonnte | mpwa | baileys | qontak
 *   • sigToken = md5(app.key + ':' + driver)  — layer keamanan tambahan sebelum signature driver dievaluasi.
 *
 * Alur:
 * 1. Rate limit per-driver per-IP (60/min, global 300/min)
 * 2. Verifikasi URL sigToken
 * 3. Dedup rawBody SHA256 (TTL 1 hari) — prevent replay (same webhook hit 2x)
 * 4. Panggil $driver->parseWebhook(rawBody, headers) → WaIncomingMessage
 * 5. Jika incoming chat = kirim ke Bot Router via handleIncoming → sendImmediate balasan synchronous
 */
final class WhatsAppWebhookController extends Controller
{
    public function __construct(
        private readonly WaGatewayRegistry            $registry,
        private readonly WhatsAppNotificationService  $service,
    ) {}

    public function __invoke(Request $request, string $driver, string $sigToken): Response
    {
        // 1. Global throttle
        if (!RateLimiter::attempt('wa:wh:global', 300, fn() => true, 60)) {
            return response('', 429)->header('Retry-After', '60');
        }
        $ip = (string)$request->ip();
        $key = 'wa:wh:' . $driver . ':' . $ip;
        if (!RateLimiter::attempt($key, 60, fn() => true, 60)) {
            return response('', 429)->header('Retry-After', '60');
        }

        // 2. Verifikasi URL signature token (md5(app.key+driver))
        $expected = substr(md5(config('app.key', '') . ':' . $driver), 0, 24);
        if (hash_equals($expected, $sigToken) === false) {
            Log::warning('[WA-WEBHOOK] Invalid signatureToken', ['driver' => $driver]);
            return response('', 403);
        }

        // 3. Validasi driver enabled
        $d = $this->registry->get($driver);
        if (!$d) {
            return response('', 404);
        }

        // 4. Dedup berdasarkan raw body SHA256 — return 200 OK saja
        $rawBody = (string)$request->getContent();
        $sha = hash('sha256', $rawBody);
        $dedupKey = 'wa:wh:dedup:' . $driver . ':' . $sha;
        if (Cache::has($dedupKey)) {
            Log::info('[WA-WEBHOOK] duplicate (dedup)', ['driver' => $driver]);
            return response('', 200);
        }
        Cache::put($dedupKey, 1, 86400);

        // 5. Parse via driver-specific signature validator
        $headersFlat = [];
        foreach ($request->headers->all() as $k => $vs) {
            $headersFlat[strtolower((string)$k)] = is_array($vs) ? (string)($vs[0] ?? '') : (string)$vs;
        }
        $incoming = null;
        try {
            $incoming = $d->parseWebhook($rawBody, $headersFlat);
        } catch (\Throwable $e) {
            Log::warning('[WA-WEBHOOK] parseWebhook throw', ['driver' => $driver, 'err' => $e->getMessage()]);
            return response('', 200);
        }

        if ($incoming === null) {
            // Status update (delivery / read) — no action, accept 200
            return response('', 200);
        }

        // 6. Bot Router: kalau incoming adalah text chat dari user, route
        if ($incoming->isTextMessage() && $incoming->fromPhone !== '') {
            try {
                $reply = $this->service->handleIncoming($incoming);
                if ($reply) {
                    $this->service->sendImmediate($reply);
                }
            } catch (\Throwable $e) {
                Log::error('[WA-BOT] handleIncoming fatal', ['err' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            }
        }

        return response('', 200);
    }

    public function health(Request $request, string $driver, string $sigToken): Response
    {
        $expected = substr(md5(config('app.key', '') . ':' . $driver), 0, 24);
        if (hash_equals($expected, $sigToken) === false) return response('', 403);
        $d = $this->registry->get($driver);
        if (!$d) return response('', 404);
        try {
            $h = $d->health();
        } catch (\Throwable $e) {
            return response(['driver' => $driver, 'ok' => false, 'err' => $e->getMessage()], 502);
        }
        return response(['driver' => $driver, 'ok' => true, 'health' => $h], 200);
    }
}
