<?php

declare(strict_types=1);

namespace App\Services\ISP\Radius;

use App\Models\Customer\CustomerService;
use App\Models\ISP\HotspotUser;
use App\Models\ISP\PPPoEUser;
use App\Models\ISP\RadiusNas;
use App\Models\ISP\ServiceProfile;
use App\Models\ISP\Voucher;
use App\Services\ISP\Radius\ValueObjects\RadiusAccessContext;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

/**
 * SSOT: Radius Authentication Service (Credential Verification).
 *
 * Tanggung jawab TUNGGAL: memverifikasi username + password cocok dengan record PPPoE / Hotspot / Voucher.
 * Tidak menjalankan business policy (itu job RadiusAuthorizationService).
 *
 * Alur:
 *   1. Resolve username -> PPPoEUser | HotspotUser | Voucher (cascade lookup)
 *   2. Verify password plaintext vs hash (bcrypt / argon2 / md5 radius)
 *   3. Hydrate RadiusAccessContext dengan semua relasi yang dibutuhkan policy
 *   4. Return Result: success + context, atau fail + reason
 */
final class RadiusAuthService
{
    public function __construct(
        private readonly PerformanceMetricsService $metrics,
    ) {}

    /**
     * @param  bool  $authorizeOnly  Jika true, skip verifikasi password (untuk authorize-only endpoint).
     *                                Gunakan flag ini — JANGAN gunakan password dummy string.
     * @return array{ok: bool, username: string, reason: string, context?: RadiusAccessContext, auth_method?: string}
     */
    public function authenticate(
        string $username,
        string $password,
        ?RadiusNas $nas = null,
        ?string $nasIp = null,
        ?string $callingStationId = null,
        ?string $calledStationId = null,
        ?string $framedIp = null,
        string $protocol = 'pppoe',
        bool $authorizeOnly = false,
    ): array {
        $t0 = microtime(true);
        $username = trim($username);
        $password = (string)$password;

        try {
            // Validasi username: wajib ada
            if ($username === '') {
                return $this->fail($username, 'Username tidak boleh kosong', $t0);
            }

            // Validasi password hanya jika bukan mode authorize-only
            if (!$authorizeOnly && $password === '') {
                return $this->fail($username, 'Password tidak boleh kosong', $t0);
            }

            // Cascade lookup: PPPoEUser -> HotspotUser -> VoucherCode
            $lookup = $this->lookupIdentity($username, $protocol);

            if ($lookup === null) {
                return $this->fail($username, 'Username tidak terdaftar di sistem', $t0);
            }

            // Jika authorizeOnly=true, skip verifikasi password — context sudah cukup untuk policy evaluation
            if (!$authorizeOnly) {
                $authed = $this->verifyPassword($lookup['identity'], $password);
                if (!$authed) {
                    return $this->fail($username, 'Password salah', $t0, $lookup['method']);
                }
            }

            $ctx = $this->hydrateContext(
                username: $username,
                password: $password,
                nas: $nas,
                nasIp: $nasIp,
                callingStationId: $callingStationId,
                calledStationId: $calledStationId,
                framedIp: $framedIp,
                protocol: $protocol,
                lookup: $lookup,
            );

            $latMs = (microtime(true) - $t0) * 1000.0;
            $this->metrics->recordLatency(PerformanceMetricsService::OP_AUTH, $latMs, true);

            $cleartextPassword = $this->extractCleartextPassword($lookup['identity']);

            return [
                'ok' => true,
                'username' => $username,
                'reason' => '',
                'auth_method' => $lookup['method'],
                'cleartext_password' => $cleartextPassword,
                'context' => $ctx,
            ];
        } catch (\Throwable $e) {
            Log::warning('RadiusAuthService exception', ['u' => $username, 'err' => $e->getMessage()]);
            return $this->fail($username, 'Internal auth error: ' . $e->getMessage(), $t0);
        }
    }

    /**
     * @return ?array{identity: PPPoEUser|HotspotUser|Voucher, method: string, type: string}
     */
    private function lookupIdentity(string $username, string $protocol): ?array
    {
        // 1. Cek Voucher (Hotspot Voucher / PPPoE Voucher)
        $v = Voucher::query()
            ->where('code', $username)
            ->whereIn('status', ['active', 'used', 'issued'])
            ->first();
        if ($v) {
            return ['identity' => $v, 'method' => 'voucher_code', 'type' => 'voucher'];
        }

        // 2. Cek Hotspot User
        $hu = HotspotUser::query()
            ->where('username', $username)
            ->where('status', '!=', 'deleted')
            ->first();
        if ($hu) {
            return ['identity' => $hu, 'method' => 'hotspot_user', 'type' => 'hotspot'];
        }

        // 3. Cek PPPoE User
        $pu = PPPoEUser::query()
            ->where('username', $username)
            ->where('status', '!=', 'deleted')
            ->first();
        if ($pu) {
            return ['identity' => $pu, 'method' => 'pppoe_user', 'type' => 'pppoe'];
        }

        return null;
    }

    private function verifyPassword(object $identity, string $plain): bool
    {
        if ($identity instanceof Voucher) {
            // Voucher: jika password kosong atau sama dengan code (login mode voucher di MikroTik), langsung accept
            $storedRaw = (string)($identity->password ?? '');
            try {
                $stored = str_starts_with($storedRaw, 'eyJ') ? \Illuminate\Support\Facades\Crypt::decryptString($storedRaw) : $storedRaw;
            } catch (\Exception $e) {
                $stored = $storedRaw;
            }
            if ($stored === '' || $plain === '' || $plain === (string)$identity->code) {
                return true;
            }
            return Hash::check($plain, $stored) || $plain === $stored;
        }

        /** @var PPPoEUser|HotspotUser $identity */
        $storedRaw = (string)($identity->password ?? '');
        try {
            $stored = str_starts_with($storedRaw, 'eyJ') ? \Illuminate\Support\Facades\Crypt::decryptString($storedRaw) : $storedRaw;
        } catch (\Exception $e) {
            $stored = $storedRaw;
        }
        if ($stored === '') return false;

        // Hash Laravel
        if (Hash::isHashed($stored)) {
            return Hash::check($plain, $stored);
        }

        // Radius MD5-PAP / CHAP-MD5 compatibility: raw compare
        if (str_starts_with($stored, 'md5:') && strlen($stored) === 36) {
            return hash_equals(substr($stored, 4), md5($plain));
        }

        // Plaintext fallback (legacy)
        return hash_equals($stored, $plain);
    }

    public function extractCleartextPassword(object $identity): string
    {
        if ($identity instanceof Voucher) {
            $storedRaw = (string)($identity->password ?? '');
            try {
                $stored = str_starts_with($storedRaw, 'eyJ') ? \Illuminate\Support\Facades\Crypt::decryptString($storedRaw) : $storedRaw;
            } catch (\Exception $e) {
                $stored = $storedRaw;
            }
            if ($stored !== '') {
                return $stored;
            }
            return (string)($identity->code ?? '');
        }

        /** @var PPPoEUser|HotspotUser $identity */
        $storedRaw = (string)($identity->password ?? '');
        try {
            $stored = str_starts_with($storedRaw, 'eyJ') ? \Illuminate\Support\Facades\Crypt::decryptString($storedRaw) : $storedRaw;
        } catch (\Exception $e) {
            $stored = $storedRaw;
        }
        return $stored;
    }

    /**
     * @param array{identity: PPPoEUser|HotspotUser|Voucher, method: string, type: string} $lookup
     */
    private function hydrateContext(
        string $username,
        string $password,
        ?RadiusNas $nas,
        ?string $nasIp,
        ?string $callingStationId,
        ?string $calledStationId,
        ?string $framedIp,
        string $protocol,
        array $lookup,
    ): RadiusAccessContext {
        $identity = $lookup['identity'];

        $pppoeUser = null;
        $hotspotUser = null;
        $voucher = null;
        $customerService = null;
        $serviceProfile = null;

        if ($identity instanceof PPPoEUser) {
            $pppoeUser = $identity->loadMissing(['customerService', 'customerService.customer', 'customerService.serviceProfile']);
            $customerService = $pppoeUser->customerService;
            $serviceProfile = $customerService?->serviceProfile;
        } elseif ($identity instanceof HotspotUser) {
            $hotspotUser = $identity->loadMissing(['customerService', 'customerService.customer', 'customerService.serviceProfile']);
            $customerService = $hotspotUser->customerService;
            $serviceProfile = $customerService?->serviceProfile;
        } elseif ($identity instanceof Voucher) {
            $voucher = $identity;
            if (!empty($voucher->internet_package_id)) {
                $serviceProfile = ServiceProfile::query()
                    ->where('internet_package_id', $voucher->internet_package_id)
                    ->first();
            }
            if (!empty($voucher->customer_service_id)) {
                $customerService = CustomerService::query()->find($voucher->customer_service_id);
            }
        }

        return new RadiusAccessContext(
            username: $username,
            password: $password,
            nas: $nas,
            nasIp: $nasIp,
            callingStationId: $callingStationId,
            calledStationId: $calledStationId,
            framedIp: $framedIp,
            pppoeUser: $pppoeUser,
            hotspotUser: $hotspotUser,
            voucher: $voucher,
            customerService: $customerService,
            serviceProfile: $serviceProfile,
            protocol: $protocol,
            extras: [
                'auth_method' => $lookup['method'],
                'identity_type' => $lookup['type'],
                'nas_id' => $nas?->id,
            ],
        );
    }

    /**
     * @return array{ok: false, username: string, reason: string}
     */
    private function fail(string $username, string $reason, float $t0, ?string $method = null): array
    {
        $latMs = (microtime(true) - $t0) * 1000.0;
        $this->metrics->recordLatency(PerformanceMetricsService::OP_AUTH, $latMs, false);
        return [
            'ok' => false,
            'username' => $username,
            'reason' => $reason,
            'auth_method' => $method,
        ];
    }
}
