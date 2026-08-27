<?php

declare(strict_types=1);

namespace App\Services\Notifications\WhatsApp\Drivers;

use App\Services\Notifications\WhatsApp\Contracts\WaGatewayDriverInterface;
use App\Services\Notifications\WhatsApp\ValueObjects\WaIncomingMessage;
use App\Services\Notifications\WhatsApp\ValueObjects\WaOutgoingMessage;
use App\Services\Notifications\WhatsApp\ValueObjects\WaSendResult;
use Illuminate\Support\Facades\Http;

/**
 * Baileys REST Wrapper Driver.
 *
 * Untuk Node.js Baileys self-hosted yang diexpose via REST API.
 * Contoh REST wrapper: Baileys-Bolt / WA-JS REST / md-rest-api.
 *
 * Endpoint: http://your-baileys-host:{port}
 * Optional Bearer Token.
 */
final class BaileysRestWaDriver implements WaGatewayDriverInterface
{
    private array $config = [];

    public static function driverKey(): string { return 'baileys'; }
    public static function driverLabel(): string { return 'Baileys REST (Free Self-Hosted - Node.js)'; }

    public function withConfig(array $config): self
    {
        $this->config = $config;
        return $this;
    }

    private function baseUrl(): string { return rtrim((string)($this->config['api_base_url'] ?? 'http://127.0.0.1:3000'), '/'); }
    private function bearer(): string { return (string)($this->config['api_token'] ?? ''); }
    private function sessionId(): string { return (string)($this->config['session_id'] ?? 'dsbilling'); }

    public function sendMessage(WaOutgoingMessage $msg): WaSendResult
    {
        if (!$msg->isPhoneValid()) return new WaSendResult(success: false, errorMessage: 'Invalid phone: ' . $msg->toPhone);
        $t0 = microtime(true);
        try {
            $url = "{$this->baseUrl()}/client/{$this->sessionId()}/send-text";
            $payload = ['to' => $msg->toPhone . '@s.whatsapp.net', 'text' => $msg->text];
            if ($msg->type === 'image') {
                $url = "{$this->baseUrl()}/client/{$this->sessionId()}/send-image";
                $payload = ['to' => $msg->toPhone . '@s.whatsapp.net', 'imageUrl' => $msg->mediaUrl, 'caption' => $msg->text];
            } elseif ($msg->type === 'document') {
                $url = "{$this->baseUrl()}/client/{$this->sessionId()}/send-document";
                $payload = ['to' => $msg->toPhone . '@s.whatsapp.net', 'documentUrl' => $msg->mediaUrl, 'fileName' => $msg->fileName, 'caption' => $msg->text];
            } elseif ($msg->type === 'button') {
                $url = "{$this->baseUrl()}/client/{$this->sessionId()}/send-buttons";
                $payload = [
                    'to' => $msg->toPhone . '@s.whatsapp.net',
                    'text' => $msg->text,
                    'buttons' => array_map(fn($b) => ['buttonId' => $b['id'] ?? uniqid('', true), 'buttonText' => ['displayText' => $b['title'] ?? '']], $msg->buttons),
                    'footer' => 'dsBilling Enterprise',
                ];
            }

            $req = Http::timeout(20);
            if ($this->bearer() !== '') $req = $req->withToken($this->bearer());
            $resp = $req->asJson()->post($url, $payload);
            $raw = $resp->body();
            $lat = (microtime(true) - $t0) * 1000;
            $data = json_decode($raw, true) ?: [];
            $ok = $resp->successful() && (empty($data['error'] ?? null));
            return new WaSendResult(
                success: $ok,
                gatewayMessageId: (string)(is_array($data['messageID'] ?? null) ? '' : (string)($data['messageID'] ?? ($data['key']['id'] ?? ''))),
                latencyMs: $lat,
                errorMessage: $ok ? '' : (string)($data['error'] ?? ('HTTP ' . $resp->status())),
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
                if (strtolower((string)$k) === 'x-baileys-hmac') {
                    $recvd = is_array($v) ? (string)($v[0] ?? '') : (string)$v;
                    break;
                }
            }
            if ($recvd !== '') {
                if (!hash_equals(hash_hmac('sha256', $rawBody, $secret), $recvd)) return null;
            }
        }
        $d = json_decode($rawBody, true);
        if (!is_array($d)) return null;
        $msgs = $d['messages'] ?? ($d['data']['messages'] ?? null);
        if (is_array($msgs) && count($msgs) > 0) $d = (array)$msgs[0];

        // Remote JID: 628xxx@s.whatsapp.net
        $remoteJid = (string)($d['key']['remoteJid'] ?? ($d['from'] ?? ''));
        if ($remoteJid === '') return null;
        $from = preg_replace('/@.*$/', '', $remoteJid);

        $msg = $d['message'] ?? $d;
        $conversation = (string)($msg['conversation'] ?? ($msg['extendedTextMessage']['text'] ?? ($msg['message']['conversation'] ?? '')));
        if ($conversation === '') return null;
        $msgId = (string)($d['key']['id'] ?? ($d['messageId'] ?? ''));
        $push = (string)($d['pushName'] ?? ($d['sender_name'] ?? null));
        $isGroup = str_contains($remoteJid, '@g.us');

        return new WaIncomingMessage(
            fromPhone: WaOutgoingMessage::normalizePhone($from),
            text: trim(mb_strtolower($conversation)),
            rawText: $conversation,
            messageId: $msgId,
            driverKey: self::driverKey(),
            senderName: $push ?: null,
            isGroup: $isGroup,
            groupId: $isGroup ? $remoteJid : null,
            receivedAtIso: now()->toIso8601String(),
            rawPayload: $d,
        );
    }

    public function health(): array
    {
        $t0 = microtime(true);
        try {
            $req = Http::timeout(8);
            if ($this->bearer() !== '') $req = $req->withToken($this->bearer());
            $resp = $req->get("{$this->baseUrl()}/client/{$this->sessionId()}/status");
            return ['ok' => $resp->successful(), 'latency_ms' => round((microtime(true) - $t0) * 1000, 1), 'message' => $resp->body()];
        } catch (\Throwable $e) {
            return ['ok' => false, 'latency_ms' => round((microtime(true) - $t0) * 1000, 1), 'message' => $e->getMessage()];
        }
    }
}
