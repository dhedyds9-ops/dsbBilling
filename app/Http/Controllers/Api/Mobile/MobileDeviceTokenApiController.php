<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Mobile;

use App\Http\Controllers\Controller;
use App\Models\UserDeviceToken;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class MobileDeviceTokenApiController extends Controller
{
    /**
     * Daftarkan atau perbarui token FCM perangkat teknisi.
     */
    public function register(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'device_id'   => 'required|string|max:120',
            'fcm_token'   => 'required|string|min:10|max:500',
            'device_name' => 'nullable|string|max:150',
            'app_version' => 'nullable|string|max:50',
            'platform'    => 'nullable|string|in:android,ios',
        ]);

        $user = $request->user();
        $deviceId = trim($validated['device_id']);
        $fcmToken = trim($validated['fcm_token']);

        // 1. Jika token ini sebelumnya tercatat aktif pada user lain (e.g. ganti akun di HP yang sama),
        // cabut kepemilikan token dari user lama demi keamanan multi-tenant.
        UserDeviceToken::query()
            ->where('fcm_token', $fcmToken)
            ->where('user_id', '!=', $user->id)
            ->update([
                'is_active'  => false,
                'revoked_at' => now(),
            ]);

        // 2. Upsert token untuk pasangan user_id + device_id
        $deviceToken = UserDeviceToken::updateOrCreate(
            [
                'user_id'   => $user->id,
                'device_id' => $deviceId,
            ],
            [
                'company_id'   => $user->company_id ?? null,
                'fcm_token'    => $fcmToken,
                'platform'     => $validated['platform'] ?? 'android',
                'app_version'  => $validated['app_version'] ?? null,
                'device_name'  => $validated['device_name'] ?? null,
                'is_active'    => true,
                'revoked_at'   => null,
                'last_seen_at' => now(),
            ]
        );

        Log::info('[DeviceToken] Token FCM berhasil didaftarkan', [
            'user_id'   => $user->id,
            'device_id' => $deviceId,
            'platform'  => $deviceToken->platform,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Token perangkat berhasil didaftarkan.',
            'data'    => [
                'device_id'    => $deviceToken->device_id,
                'platform'     => $deviceToken->platform,
                'is_active'    => $deviceToken->is_active,
                'last_seen_at' => $deviceToken->last_seen_at?->toIso8601String(),
            ],
        ]);
    }

    /**
     * Perbarui token saat Firebase melakukan rotasi token (Token Rotation).
     */
    public function refresh(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'device_id' => 'required|string|max:120',
            'new_token' => 'required|string|min:10|max:500',
            'old_token' => 'nullable|string|max:500',
        ]);

        $user = $request->user();
        $deviceId = trim($validated['device_id']);
        $newToken = trim($validated['new_token']);

        // Cabut token baru jika sebelumnya dipakai user lain
        UserDeviceToken::query()
            ->where('fcm_token', $newToken)
            ->where('user_id', '!=', $user->id)
            ->update([
                'is_active'  => false,
                'revoked_at' => now(),
            ]);

        $deviceToken = UserDeviceToken::query()
            ->where('user_id', $user->id)
            ->where('device_id', $deviceId)
            ->first();

        if ($deviceToken) {
            $deviceToken->update([
                'fcm_token'    => $newToken,
                'is_active'    => true,
                'revoked_at'   => null,
                'last_seen_at' => now(),
            ]);
        } else {
            $deviceToken = UserDeviceToken::create([
                'user_id'      => $user->id,
                'company_id'   => $user->company_id ?? null,
                'device_id'    => $deviceId,
                'fcm_token'    => $newToken,
                'is_active'    => true,
                'last_seen_at' => now(),
            ]);
        }

        Log::info('[DeviceToken] Token FCM berhasil dirotasi', [
            'user_id'   => $user->id,
            'device_id' => $deviceId,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Token perangkat berhasil diperbarui.',
            'data'    => [
                'device_id' => $deviceToken->device_id,
                'is_active' => $deviceToken->is_active,
            ],
        ]);
    }

    /**
     * Cabut token saat user logout dari perangkat mobile.
     */
    public function unregister(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'device_id' => 'nullable|string|max:120',
            'fcm_token' => 'nullable|string|max:500',
        ]);

        $user = $request->user();
        $query = UserDeviceToken::query()->where('user_id', $user->id);

        if (!empty($validated['device_id'])) {
            $query->where('device_id', $validated['device_id']);
        } elseif (!empty($validated['fcm_token'])) {
            $query->where('fcm_token', $validated['fcm_token']);
        }

        $revokedCount = $query->update([
            'is_active'  => false,
            'revoked_at' => now(),
        ]);

        Log::info('[DeviceToken] Token FCM dicabut (logout)', [
            'user_id' => $user->id,
            'count'   => $revokedCount,
        ]);

        return response()->json([
            'success' => true,
            'message' => "Token perangkat berhasil dicabut ({$revokedCount} perangkat).",
        ]);
    }

    /**
     * Perbarui status kehadiran aktif (heartbeat/ping) perangkat.
     */
    public function ping(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'device_id' => 'required|string|max:120',
        ]);

        $user = $request->user();
        UserDeviceToken::query()
            ->where('user_id', $user->id)
            ->where('device_id', $validated['device_id'])
            ->update([
                'last_seen_at' => now(),
            ]);

        return response()->json([
            'success' => true,
            'message' => 'Heartbeat perangkat diterima.',
        ]);
    }
}
