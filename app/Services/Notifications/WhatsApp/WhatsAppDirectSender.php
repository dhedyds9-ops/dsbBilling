<?php

declare(strict_types=1);

namespace App\Services\Notifications\WhatsApp;

use App\Models\Setting;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WhatsAppDirectSender
{
    /**
     * Kirim pesan WhatsApp secara langsung (synchronous HTTP) ke nomor HP atau Grup WhatsApp
     * Mendukung Fonnte, Wablas, dan Whacenter sesuai pengaturan di Pengaturan > WhatsApp
     *
     * @return array{success: bool, error: ?string, response: mixed}
     */
    public static function send(string $target, string $message): array
    {
        $conn = Setting::getValue('whatsapp.connection', []);
        $provider = strtolower((string)($conn['provider'] ?? 'fonnte'));
        $token = trim((string)($conn['api_token'] ?? ''));
        $target = trim($target);

        if (empty($token)) {
            Log::warning('[WhatsAppDirectSender] API Token WhatsApp belum diisi di menu Pengaturan > WhatsApp');
            return ['success' => false, 'error' => 'API Token WhatsApp belum diisi di Pengaturan WhatsApp', 'response' => null];
        }

        if (empty($target)) {
            Log::warning('[WhatsAppDirectSender] Nomor / ID Target WhatsApp kosong');
            return ['success' => false, 'error' => 'Nomor atau ID grup target kosong', 'response' => null];
        }

        try {
            if ($provider === 'fonnte') {
                $url = 'https://api.fonnte.com/send';

                $response = Http::timeout(15)
                    ->withHeaders([
                        'Authorization' => $token,
                    ])
                    ->asForm()
                    ->post($url, [
                        'target'      => $target,
                        'message'     => $message,
                        'delay'       => '0',
                        'countryCode' => '62',
                    ]);

                $body = $response->json() ?? [];
                $status = (bool)($body['status'] ?? false);
                $reason = (string)($body['reason'] ?? ($body['message'] ?? ''));

                Log::info('[WhatsAppDirectSender] Fonnte send response', [
                    'target'      => $target,
                    'http_status' => $response->status(),
                    'status'      => $status,
                    'reason'      => $reason,
                    'raw'         => $response->body(),
                ]);

                // Simpan ke riwayat pengiriman
                try {
                    \App\Models\Integration\WaMessageHistory::create([
                        'recipient_number' => $target,
                        'message'          => $message,
                        'category'         => 'support',
                        'status'           => $status ? 'sent' : 'failed',
                        'error_message'    => $status ? null : ($reason ?: $response->body()),
                        'payload'          => $body,
                    ]);
                } catch (\Throwable) {}

                return [
                    'success'  => $status,
                    'error'    => $status ? null : ($reason ?: 'HTTP ' . $response->status()),
                    'response' => $body,
                ];
            } elseif ($provider === 'wablas') {
                $url = !empty($conn['base_url']) ? $conn['base_url'] : 'https://jogja.wablas.com/api/send-message';
                $cleanPhone = preg_replace('/[^0-9]/', '', $target);
                if (str_starts_with($cleanPhone, '0')) $cleanPhone = '62' . substr($cleanPhone, 1);

                $response = Http::timeout(15)
                    ->withHeaders(['Wablas-Api-Key' => $token])
                    ->asForm()
                    ->post($url, [
                        'phone'   => $cleanPhone,
                        'message' => $message,
                        'secret'  => false,
                    ]);

                $body = $response->json() ?? [];
                $ok = $response->successful() && (bool)($body['status'] ?? false);
                return ['success' => $ok, 'error' => $ok ? null : $response->body(), 'response' => $body];
            } elseif ($provider === 'whacenter') {
                $url = !empty($conn['base_url']) ? $conn['base_url'] : 'https://app.whacenter.com/api/send';
                $cleanPhone = preg_replace('/[^0-9]/', '', $target);
                if (str_starts_with($cleanPhone, '0')) $cleanPhone = '62' . substr($cleanPhone, 1);

                $response = Http::timeout(15)
                    ->asForm()
                    ->post($url, [
                        'device_id' => $conn['device_id'] ?? '',
                        'number'    => $cleanPhone,
                        'message'   => $message,
                    ]);

                $body = $response->json() ?? [];
                $ok = $response->successful() && (bool)($body['status'] ?? false);
                return ['success' => $ok, 'error' => $ok ? null : $response->body(), 'response' => $body];
            }
        } catch (\Throwable $e) {
            Log::error('[WhatsAppDirectSender] Exception: ' . $e->getMessage(), [
                'target' => $target,
            ]);
            return ['success' => false, 'error' => $e->getMessage(), 'response' => null];
        }

        return ['success' => false, 'error' => 'Provider WhatsApp tidak didukung: ' . $provider, 'response' => null];
    }
}
