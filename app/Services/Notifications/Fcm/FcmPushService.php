<?php

declare(strict_types=1);

namespace App\Services\Notifications\Fcm;

use App\Models\User;
use App\Models\UserDeviceToken;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class FcmPushService
{
    protected ?string $projectId;
    protected ?string $serviceAccountPath;

    public function __construct()
    {
        $this->projectId = config('services.fcm.project_id', 'dsbilling-enterprise');
        $this->serviceAccountPath = config('services.fcm.service_account_path');
    }

    /**
     * Kirim notifikasi push ke semua token aktif milik user tertentu.
     */
    public function sendToUser(
        int $userId,
        string $title,
        string $body,
        array $data = [],
        string $channelId = 'channel_general'
    ): array {
        $tokens = UserDeviceToken::query()
            ->where('user_id', $userId)
            ->active()
            ->get();

        if ($tokens->isEmpty()) {
            return [
                'total'     => 0,
                'sent'      => 0,
                'failed'    => 0,
                'revoked'   => 0,
                'skipped'   => true,
                'message'   => 'Tidak ada token perangkat aktif untuk user ini.',
            ];
        }

        $results = [
            'total'   => $tokens->count(),
            'sent'    => 0,
            'failed'  => 0,
            'revoked' => 0,
            'details' => [],
        ];

        foreach ($tokens as $deviceToken) {
            $outcome = $this->sendRawMessage(
                fcmToken: $deviceToken->fcm_token,
                title: $title,
                body: $body,
                data: $data,
                channelId: $channelId
            );

            $results['details'][] = [
                'device_id' => $deviceToken->device_id,
                'status'    => $outcome['status'],
            ];

            if ($outcome['status'] === 'accepted') {
                $results['sent']++;
                $deviceToken->update(['last_seen_at' => now()]);
            } elseif ($outcome['status'] === 'invalid_token') {
                $results['revoked']++;
                // Nonaktifkan token secara otomatis jika Firebase melaporkan tidak valid / unregistered
                $deviceToken->update([
                    'is_active'  => false,
                    'revoked_at' => now(),
                ]);
            } else {
                $results['failed']++;
            }
        }

        return $results;
    }

    /**
     * Kirim pesan mentah ke satu token FCM spesifik menggunakan FCM HTTP v1.
     */
    public function sendRawMessage(
        string $fcmToken,
        string $title,
        string $body,
        array $data = [],
        string $channelId = 'channel_general'
    ): array {
        // Normalisasi seluruh value di array data agar bertipe string (persyaratan FCM HTTP v1)
        $normalizedData = [];
        foreach ($data as $k => $v) {
            $normalizedData[(string) $k] = is_scalar($v) ? (string) $v : json_encode($v);
        }

        // Dapatkan OAuth2 Access Token
        $accessToken = $this->getAccessToken();
        if (!$accessToken) {
            Log::info('[FcmPushService] FCM skip: Access token tidak tersedia (kredensial Firebase belum dikonfigurasi).');
            return [
                'status'  => 'skipped',
                'message' => 'Credentials not configured',
            ];
        }

        $url = "https://fcm.googleapis.com/v1/projects/{$this->projectId}/messages:send";

        $payload = [
            'message' => [
                'token' => $fcmToken,
                'notification' => [
                    'title' => $title,
                    'body'  => $body,
                ],
                'data' => $normalizedData,
                'android' => [
                    'priority' => 'HIGH',
                    'notification' => [
                        'channel_id' => $channelId,
                        'sound'      => 'default',
                    ],
                ],
            ],
        ];

        try {
            $response = Http::withToken($accessToken)
                ->timeout(10)
                ->post($url, $payload);

            if ($response->successful()) {
                return [
                    'status'   => 'accepted',
                    'response' => $response->json(),
                ];
            }

            $statusCode = $response->status();
            $respBody = $response->json();
            $errorCode = $respBody['error']['details'][0]['errorCode'] ?? ($respBody['error']['status'] ?? '');

            // Token tidak valid atau user telah uninstall aplikasi
            if ($statusCode === 404 || in_array($errorCode, ['UNREGISTERED', 'INVALID_ARGUMENT'], true)) {
                Log::warning("[FcmPushService] Token FCM tidak valid ({$errorCode}), mencabut token.");
                return [
                    'status'     => 'invalid_token',
                    'error_code' => $errorCode,
                ];
            }

            Log::warning("[FcmPushService] FCM HTTP v1 gagal [{$statusCode}]: " . json_encode($respBody));
            return [
                'status'     => 'failed',
                'error_code' => $errorCode ?: 'HTTP_' . $statusCode,
            ];

        } catch (\Throwable $e) {
            Log::warning("[FcmPushService] Exception saat menghubungi FCM: {$e->getMessage()}");
            return [
                'status'  => 'failed',
                'message' => $e->getMessage(),
            ];
        }
    }

    /**
     * Dapatkan OAuth2 Access Token dari Google Service Account menggunakan JWT Assertion.
     */
    public function getAccessToken(): ?string
    {
        return Cache::remember('fcm_google_access_token', 3300, function () {
            $serviceAccount = $this->loadServiceAccount();
            if (!$serviceAccount) {
                return null;
            }

            $clientEmail = $serviceAccount['client_email'] ?? null;
            $privateKey = $serviceAccount['private_key'] ?? null;

            if (!$clientEmail || !$privateKey) {
                return null;
            }

            $now = time();
            $header = ['alg' => 'RS256', 'typ' => 'JWT'];
            $claim = [
                'iss'   => $clientEmail,
                'scope' => 'https://www.googleapis.com/auth/firebase.messaging',
                'aud'   => 'https://oauth2.googleapis.com/token',
                'exp'   => $now + 3600,
                'iat'   => $now,
            ];

            $base64UrlHeader = $this->base64UrlEncode(json_encode($header));
            $base64UrlClaim = $this->base64UrlEncode(json_encode($claim));
            $signatureInput = "{$base64UrlHeader}.{$base64UrlClaim}";

            $signature = '';
            $success = openssl_sign($signatureInput, $signature, $privateKey, OPENSSL_ALGO_SHA256);
            if (!$success) {
                Log::error('[FcmPushService] Gagal menandatangani JWT Service Account.');
                return null;
            }

            $jwt = "{$signatureInput}." . $this->base64UrlEncode($signature);

            // Tukar JWT dengan Access Token di server OAuth2 Google
            $response = Http::asForm()
                ->timeout(10)
                ->post('https://oauth2.googleapis.com/token', [
                    'grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer',
                    'assertion'  => $jwt,
                ]);

            if ($response->successful()) {
                return $response->json('access_token');
            }

            Log::error('[FcmPushService] Gagal menukar JWT dengan Access Token: ' . $response->body());
            return null;
        });
    }

    /**
     * Muat kredensial Service Account JSON.
     */
    protected function loadServiceAccount(): ?array
    {
        // 1. Cek string JSON mentah di environment variable
        $rawJson = config('services.fcm.service_account_json');
        if ($rawJson) {
            $decoded = json_decode($rawJson, true);
            if (is_array($decoded)) return $decoded;
        }

        // 2. Cek file kredensial di path yang dikonfigurasi
        if ($this->serviceAccountPath && file_exists($this->serviceAccountPath)) {
            $contents = file_get_contents($this->serviceAccountPath);
            $decoded = json_decode($contents, true);
            if (is_array($decoded)) return $decoded;
        }

        return null;
    }

    protected function base64UrlEncode(string $data): string
    {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }
}
