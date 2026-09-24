<?php

declare(strict_types=1);

namespace App\Services\Notifications\WhatsApp\Drivers;

use App\Services\Notifications\WhatsApp\Contracts\WaGatewayDriverInterface;
use App\Services\Notifications\WhatsApp\ValueObjects\WaIncomingMessage;
use App\Services\Notifications\WhatsApp\ValueObjects\WaOutgoingMessage;
use App\Services\Notifications\WhatsApp\ValueObjects\WaSendResult;
use Illuminate\Support\Facades\Http;

/**
 * MPWA (Multi-Panel WhatsApp) / OneSender Driver.
 *
 * Populer untuk ISP: self-hosted (On-Premise MPWA VPS) atau SaaS OneSender.
 * Endpoint custom (configurable): http://ip-vps:{port}
 * Auth: api_key dari panel / Signature.
 */
final class MpwaWaDriver implements WaGatewayDriverInterface
{
    private array $config = [];

    public static function driverKey(): string { return 'mpwa'; }
    public static function driverLabel(): string { return 'MPWA / OneSender (Self-Hosted / SaaS Multi-Panel)'; }

    public function withConfig(array $config): self
    {
        $this->config = $config;
        return $this;
    }

    private function baseUrl(): string
    {
        return rtrim((string)($this->config['api_base_url'] ?? 'http://127.0.0.1:8080'), '/');
    }

    private function apiKey(): string { return (string)($this->config['api_key'] ?? ''); }
    private function senderNumber(): string { return (string)($this->config['sender_number'] ?? ''); }

    public function sendMessage(WaOutgoingMessage $msg): WaSendResult
    {
        if (!$msg->isPhoneValid()) {
            return new WaSendResult(success: false, errorMessage: 'Invalid phone: ' . $msg->toPhone);
        }
        $t0 = microtime(true);
        try {
            // MPWA/OneSender pattern: POST /api/send-message dengan JSON
            $type = match ($msg->type) {
                'image' => 'image',
                'video' => 'video',
                'document' => 'document',
                'audio' => 'audio',
                default => 'text',
            };
            $payload = [
                'api_key' => $this->apiKey(),
                'sender' => $this->senderNumber() ?: null,
                'destination' => $msg->toPhone,
                'type' => $type,
                'message' => $msg->text,
            ];
            if (in_array($type, ['image', 'video', 'document', 'audio'])) {
                $payload['url'] = $msg->mediaUrl;
                if ($type === 'document') $payload['filename'] = $msg->fileName;
            }
            if (count($msg->buttons) > 0) {
                $payload['buttons'] = $msg->buttons;
                $payload['footer'] = 'dsBilling Enterprise';
            }
            $resp = Http::timeout(15)->asJson()->post($this->baseUrl() . '/api/send-message', $payload);
            $raw = $resp->body();
            $lat = (microtime(true) - $t0) * 1000;
            $data = json_decode($raw, true) ?: [];
            $ok = $resp->successful() && (($data['success'] ?? false) || ($data['status'] ?? false));
            if (!$ok) {
                return new WaSendResult(
                    success: false, latencyMs: $lat,
                    errorCode: (string)($data['code'] ?? (string)$resp->status()),
                    errorMessage: (string)($data['message'] ?? ('HTTP ' . $resp->status())),
                    rawResponse: $raw,
                );
            }
            return new WaSendResult(
                success: true,
                gatewayMessageId: (string)($data['message_id'] ?? ($data['data']['message_id'] ?? '')),
                latencyMs: $lat,
                rawResponse: $raw,
            );
        } catch (\Throwable $e) {
            return new WaSendResult(success: false, latencyMs: (microtime(true) - $t0) * 1000, errorMessage: $e->getMessage());
        }
    }

    public function parseWebhook(string $rawBody, array $headers): ?WaIncomingMessage
    {
        $secret = (string)($this->config['webhook_secret'] ?? '');
        if ($secret !== '') {
            $recvd = '';
            foreach ($headers as $k => $v) {
                if (strtolower((string)$k) === 'x-signature') {
                    $recvd = is_array($v) ? (string)($v[0] ?? '') : (string)$v;
                    break;
                }
            }
            if ($recvd !== '') {
                $expected = hash_hmac('sha256', $rawBody, $secret);
                if (!hash_equals($expected, $recvd)) return null;
            }
        }
        $data = json_decode($rawBody, true);
        if (!is_array($data)) return null;
        $event = strtolower((string)($data['event'] ?? ($data['type'] ?? '')));
        if ($event && !in_array($event, ['message:in', 'message', 'incoming_message', 'chat'])) return null;
        $from = (string)($data['from'] ?? ($data['sender'] ?? ($data['phone'] ?? '')));
        if ($from === '') return null;
        $text = (string)($data['text'] ?? ($data['message'] ?? ($data['body'] ?? '')));
        $msgid = (string)($data['message_id'] ?? ($data['id'] ?? ''));
        return new WaIncomingMessage(
            fromPhone: WaOutgoingMessage::normalizePhone($from),
            text: trim(mb_strtolower($text)),
            rawText: $text,
            messageId: $msgid,
            driverKey: self::driverKey(),
            senderName: (string)($data['pushName'] ?? ($data['sender_name'] ?? null)),
            isGroup: !empty($data['isGroup']) || !empty($data['group_id']),
            groupId: isset($data['group_id']) ? (string)$data['group_id'] : null,
            receivedAtIso: now()->toIso8601String(),
            rawPayload: $data,
        );
    }

    public function health(): array
    {
        $t0 = microtime(true);
        try {
            $resp = Http::timeout(8)->get($this->baseUrl() . '/health');
            return ['ok' => $resp->successful(), 'latency_ms' => round((microtime(true) - $t0) * 1000, 1), 'message' => $resp->successful() ? 'OK' : $resp->body()];
        } catch (\Throwable $e) {
            return ['ok' => false, 'latency_ms' => round((microtime(true) - $t0) * 1000, 1), 'message' => $e->getMessage()];
        }
    }
}
