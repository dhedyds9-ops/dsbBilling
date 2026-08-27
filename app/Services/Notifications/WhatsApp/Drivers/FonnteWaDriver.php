<?php

declare(strict_types=1);

namespace App\Services\Notifications\WhatsApp\Drivers;

use App\Services\Notifications\WhatsApp\Contracts\WaGatewayDriverInterface;
use App\Services\Notifications\WhatsApp\Exceptions\WaGatewayException;
use App\Services\Notifications\WhatsApp\ValueObjects\WaIncomingMessage;
use App\Services\Notifications\WhatsApp\ValueObjects\WaOutgoingMessage;
use App\Services\Notifications\WhatsApp\ValueObjects\WaSendResult;
use Illuminate\Support\Facades\Http;

/**
 * Fonnte API Driver (Paid, Anti-banned populer di Indonesia ISP billing).
 *
 * Endpoint: https://api.fonnte.com
 * Authentikasi: Header Authorization = TOKEN_FONNTE
 *
 * Docs: https://docs.fonnte.com
 */
final class FonnteWaDriver implements WaGatewayDriverInterface
{
    private array $config = [];

    public static function driverKey(): string { return 'fonnte'; }
    public static function driverLabel(): string { return 'Fonnte API (Recommended - Anti Banned)'; }

    public function withConfig(array $config): self
    {
        $this->config = $config;
        return $this;
    }

    private function apiUrl(): string
    {
        return (string)($this->config['api_base_url'] ?? 'https://api.fonnte.com');
    }

    private function token(): string
    {
        return (string)($this->config['api_token'] ?? '');
    }

    public function sendMessage(WaOutgoingMessage $msg): WaSendResult
    {
        if ($this->token() === '') throw WaGatewayException::create(self::driverKey(), 'api_token kosong');
        if (!$msg->isPhoneValid()) {
            return new WaSendResult(success: false, errorMessage: 'Invalid phone number format: ' . $msg->toPhone);
        }
        $t0 = microtime(true);
        try {
            if ($msg->type === 'text') {
                $payload = [
                    'target' => $msg->toPhone,
                    'message' => $msg->text,
                    'delay' => (string)($this->config['delay_ms'] ?? '1'),
                ];
                if ($msg->priorityHigh) $payload['delay'] = '0';
                $resp = Http::withToken($this->token())
                    ->timeout(15)
                    ->asForm()
                    ->post($this->apiUrl() . '/send-message', $payload);
            } elseif ($msg->type === 'image') {
                $payload = [
                    'target' => $msg->toPhone,
                    'url' => $msg->mediaUrl,
                    'message' => $msg->text,
                ];
                $resp = Http::withToken($this->token())
                    ->timeout(20)
                    ->asForm()
                    ->post($this->apiUrl() . '/send-image', $payload);
            } elseif ($msg->type === 'document') {
                $payload = [
                    'target' => $msg->toPhone,
                    'url' => $msg->mediaUrl,
                    'filename' => $msg->fileName ?: 'document.pdf',
                    'message' => $msg->text,
                ];
                $resp = Http::withToken($this->token())
                    ->timeout(30)
                    ->asForm()
                    ->post($this->apiUrl() . '/send-document', $payload);
            } else {
                // Default fallback to text
                $payload = ['target' => $msg->toPhone, 'message' => $msg->text];
                $resp = Http::withToken($this->token())
                    ->timeout(15)
                    ->asForm()
                    ->post($this->apiUrl() . '/send-message', $payload);
            }
            $raw = $resp->body();
            $lat = (microtime(true) - $t0) * 1000;
            $data = json_decode($raw, true) ?: [];
            $status = (bool)($data['status'] ?? false);
            $errMsg = (string)($data['message'] ?? '');

            if (!$resp->successful() || !$status) {
                return new WaSendResult(
                    success: false, latencyMs: $lat,
                    errorCode: (string)($data['code'] ?? (string)$resp->status()),
                    errorMessage: $errMsg ?: ('HTTP ' . $resp->status()),
                    rawResponse: $raw
                );
            }
            return new WaSendResult(
                success: true,
                gatewayMessageId: (string)($data['id'] ?? ''),
                latencyMs: $lat,
                rawResponse: $raw,
            );
        } catch (\Throwable $e) {
            return new WaSendResult(
                success: false,
                latencyMs: (microtime(true) - $t0) * 1000,
                errorMessage: $e->getMessage(),
            );
        }
    }

    public function parseWebhook(string $rawBody, array $headers): ?WaIncomingMessage
    {
        $token = (string)($this->config['webhook_token'] ?? '');
        if ($token !== '') {
            $recvd = '';
            foreach ($headers as $k => $v) {
                if (strtolower((string)$k) === 'x-webhook-token') {
                    $recvd = is_array($v) ? (string)($v[0] ?? '') : (string)$v;
                    break;
                }
            }
            if (!hash_equals($token, $recvd)) {
                $data = json_decode($rawBody, true);
                $inToken = (string)($data['token'] ?? '');
                if (!hash_equals($token, $inToken)) return null;
            }
        }
        $data = json_decode($rawBody, true);
        if (!is_array($data)) return null;

        $type = (string)($data['type'] ?? '');
        $cmd = (string)($data['command'] ?? '');
        if ($type !== 'message' && $cmd !== 'incoming') return null;

        $from = (string)($data['sender'] ?? ($data['from'] ?? ''));
        if ($from === '') return null;
        $text = (string)($data['message'] ?? ($data['text'] ?? ''));
        $msgId = (string)($data['id'] ?? ($data['message_id'] ?? ''));
        $deviceId = (string)($data['device_id'] ?? '');

        return new WaIncomingMessage(
            fromPhone: WaOutgoingMessage::normalizePhone($from),
            text: trim(mb_strtolower($text)),
            rawText: $text,
            messageId: $msgId,
            driverKey: self::driverKey(),
            senderName: (string)($data['name'] ?? null),
            isGroup: !empty($data['group']),
            groupId: isset($data['group_id']) ? (string)$data['group_id'] : null,
            receivedAtIso: now()->toIso8601String(),
            rawPayload: $data,
        );
    }

    public function health(): array
    {
        $t0 = microtime(true);
        try {
            $resp = Http::withToken($this->token())->timeout(10)->get($this->apiUrl() . '/devices');
            $ok = $resp->successful();
            $lat = (microtime(true) - $t0) * 1000;
            return ['ok' => $ok, 'latency_ms' => round($lat, 1), 'message' => $ok ? 'Device API reachable' : $resp->body()];
        } catch (\Throwable $e) {
            return ['ok' => false, 'latency_ms' => round((microtime(true) - $t0) * 1000, 1), 'message' => $e->getMessage()];
        }
    }
}
