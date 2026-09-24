<?php

declare(strict_types=1);

namespace App\Services\Notifications\WhatsApp\Drivers;

use App\Services\Notifications\WhatsApp\Contracts\WaGatewayDriverInterface;
use App\Services\Notifications\WhatsApp\ValueObjects\WaIncomingMessage;
use App\Services\Notifications\WhatsApp\ValueObjects\WaOutgoingMessage;
use App\Services\Notifications\WhatsApp\ValueObjects\WaSendResult;
use Illuminate\Support\Facades\Http;

/**
 * Mekari Qontak Driver (Official WhatsApp Business API - Meta Partner).
 *
 * Hanya template yang sudah disetujui Meta bisa dikirim.
 * Tidak ada resiko banned.
 *
 * Endpoint: https://chat-service.qontak.com
 * Auth: Bearer Token (Qontak Access Token)
 * Channel ID: ID WhatsApp Business yang di-approve di Qontak dashboard.
 */
final class QontakWaDriver implements WaGatewayDriverInterface
{
    private array $config = [];

    public static function driverKey(): string { return 'qontak'; }
    public static function driverLabel(): string { return 'Mekari Qontak (Official WABA - Meta Partner)'; }

    public function withConfig(array $config): self
    {
        $this->config = $config;
        return $this;
    }

    private function baseUrl(): string { return rtrim((string)($this->config['api_base_url'] ?? 'https://chat-service.qontak.com'), '/'); }
    private function token(): string { return (string)($this->config['access_token'] ?? ''); }
    private function channelId(): string { return (string)($this->config['channel_id'] ?? ''); }

    public function sendMessage(WaOutgoingMessage $msg): WaSendResult
    {
        if ($this->token() === '' || $this->channelId() === '') {
            return new WaSendResult(success: false, errorMessage: 'Qontak access_token / channel_id kosong');
        }
        if (!$msg->isPhoneValid()) return new WaSendResult(success: false, errorMessage: 'Invalid phone: ' . $msg->toPhone);
        $t0 = microtime(true);
        try {
            // Qontak tergantung tipe: template approved OR broadcast text via integration
            if ($msg->type === 'template' && $msg->templateName !== '') {
                $components = $this->normalizeTemplateComponents($msg->templateComponents);
                $payload = [
                    'to_name' => 'Customer',
                    'to_number' => $msg->toPhone,
                    'message_template_id' => (int)($this->config['template_ids'][$msg->templateName] ?? 0),
                    'channel_integration_id' => $this->channelId(),
                    'language' => ['code' => 'id'],
                    'parameters' => $components,
                ];
                $endpoint = '/api/v1/whatsapp/shots';
            } else {
                // Broadcast via Agent-less (pesan bebas tapi biasanya dibatasi untuk notification)
                $payload = [
                    'to_number' => $msg->toPhone,
                    'to_name' => 'Customer',
                    'message' => $msg->text,
                    'channel_id' => $this->channelId(),
                ];
                $endpoint = '/api/open/v1/whatsapp/messages';
            }
            $resp = Http::withToken($this->token())
                ->timeout(20)
                ->asJson()
                ->post($this->baseUrl() . $endpoint, $payload);
            $raw = $resp->body();
            $lat = (microtime(true) - $t0) * 1000;
            $data = json_decode($raw, true) ?: [];
            $ok = $resp->successful() && ($data['status'] ?? 'failed') !== 'failed' && empty($data['error'] ?? null);
            return new WaSendResult(
                success: $ok,
                gatewayMessageId: (string)($data['data']['id'] ?? ($data['id'] ?? '')),
                latencyMs: $lat,
                errorMessage: $ok ? '' : (string)(($data['error']['message'] ?? null) ?? ($data['message'] ?? ('HTTP ' . $resp->status()))),
                rawResponse: $raw,
            );
        } catch (\Throwable $e) {
            return new WaSendResult(success: false, latencyMs: (microtime(true) - $t0) * 1000, errorMessage: $e->getMessage());
        }
    }

    public function parseWebhook(string $rawBody, array $headers): ?WaIncomingMessage
    {
        $token = (string)($this->config['webhook_token'] ?? '');
        if ($token !== '') {
            $recvd = '';
            foreach ($headers as $k => $v) {
                if (strtolower((string)$k) === 'x-qontak-token') {
                    $recvd = is_array($v) ? (string)($v[0] ?? '') : (string)$v;
                    break;
                }
            }
            if ($recvd !== '' && !hash_equals($token, $recvd)) return null;
        }
        $data = json_decode($rawBody, true);
        if (!is_array($data)) return null;
        $msg = $data['messages'][0] ?? $data;
        $from = (string)($msg['from']['whatsapp_number'] ?? ($msg['from']['number'] ?? ($msg['from'] ?? '')));
        if ($from === '') return null;
        $text = (string)($msg['text']['body'] ?? ($msg['text'] ?? ($msg['message']['text'] ?? '')));
        if ($text === '') return null;
        $msgid = (string)($msg['id'] ?? ($msg['message_id'] ?? ''));
        return new WaIncomingMessage(
            fromPhone: WaOutgoingMessage::normalizePhone($from),
            text: trim(mb_strtolower($text)),
            rawText: $text,
            messageId: $msgid,
            driverKey: self::driverKey(),
            senderName: (string)($msg['from']['name'] ?? ($msg['from_name'] ?? null)),
            receivedAtIso: now()->toIso8601String(),
            rawPayload: $data,
        );
    }

    public function health(): array
    {
        $t0 = microtime(true);
        try {
            $resp = Http::withToken($this->token())->timeout(8)->get($this->baseUrl() . '/api/open/v1/channels');
            return ['ok' => $resp->successful(), 'latency_ms' => round((microtime(true) - $t0) * 1000, 1), 'message' => $resp->successful() ? 'Channels API OK' : $resp->body()];
        } catch (\Throwable $e) {
            return ['ok' => false, 'latency_ms' => round((microtime(true) - $t0) * 1000, 1), 'message' => $e->getMessage()];
        }
    }

    private function normalizeTemplateComponents(array $raw): array
    {
        $body = [];
        foreach (($raw['body'] ?? []) as $i => $value) {
            $body[] = ['key' => (string)($i + 1), 'value' => (string)$value];
        }
        return ['body' => $body, 'buttons' => $raw['buttons'] ?? []];
    }
}
