<?php

namespace App\Console\Commands\ISP;

use App\Models\ISP\HotspotUser;
use App\Models\ISP\PPPoEUser;
use App\Models\ISP\Router;
use App\Models\ISP\Voucher;
use App\Services\ISP\Radius\RadiusAuthService;
use App\Services\ISP\Radius\RadiusAuthorizationService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class TestRadiusAuthCommand extends Command
{
    protected $signature = 'isp:test-radius {username : Username, Kode Voucher, atau Akun PPPoE} {password? : Password (opsional jika voucher)} {--tenant= : Nama tenant jika multi-tenant (default: auto)} {--nas-ip=127.0.0.1 : IP NAS/Router}';

    protected $description = 'Uji coba autentikasi RADIUS untuk user PPPoE, Hotspot, atau Voucher secara langsung';

    public function handle(RadiusAuthService $authService, RadiusAuthorizationService $authorizationService): int
    {
        $username = (string)$this->argument('username');
        $password = (string)($this->argument('password') ?? '');
        $nasIp = (string)$this->option('nas-ip');

        $this->info("=================================================");
        $this->info("      dsBilling RADIUS Authentication Tester     ");
        $this->info("=================================================");
        $this->line("Target Username/Code : <fg=yellow>{$username}</>");
        $this->line("Target Password      : <fg=yellow>" . ($password !== '' ? $password : '(kosong/voucher)') . "</>");

        // 1. Resolve Tenant
        $tenantName = $this->option('tenant');
        if (!$tenantName) {
            $tenantsDir = database_path('tenants');
            if (is_dir($tenantsDir)) {
                $files = glob($tenantsDir . '/*.sqlite');
                if (!empty($files)) {
                    $tenantName = basename($files[0], '.sqlite');
                }
            }
        }

        if ($tenantName) {
            $this->line("Tenant Database      : <fg=cyan>{$tenantName}.sqlite</>");
            $dbPath = database_path("tenants/{$tenantName}.sqlite");
            if (file_exists($dbPath)) {
                config(['database.connections.sqlite.database' => $dbPath]);
                DB::purge('sqlite');
                DB::reconnect('sqlite');
                $_SERVER['TENANT_NAME'] = $tenantName;
            }
        }

        // 2. Direct Model Lookup
        $this->newLine();
        $this->info("[1/3] Memeriksa Database...");

        $voucher = Voucher::where('code', $username)->first();
        $hotspot = HotspotUser::where('username', $username)->first();
        $pppoe = PPPoEUser::where('username', $username)->first();

        if ($voucher) {
            $this->line("  ✓ Ditemukan sebagai: <fg=green>Hotspot Voucher</>");
            $this->line("    - Kode     : {$voucher->code}");
            $this->line("    - Password : " . ($voucher->password ?: '(kosong - login pakai kode voucher)'));
            $this->line("    - Status   : {$voucher->status}");
            $this->line("    - Profile  : " . ($voucher->profile_name ?? '-'));
        } elseif ($hotspot) {
            $this->line("  ✓ Ditemukan sebagai: <fg=green>Hotspot User</>");
            $this->line("    - Username : {$hotspot->username}");
            $this->line("    - Status   : {$hotspot->status}");
            $this->line("    - Profile  : " . ($hotspot->profile_name ?? '-'));
        } elseif ($pppoe) {
            $this->line("  ✓ Ditemukan sebagai: <fg=green>PPPoE User</>");
            $this->line("    - Username : {$pppoe->username}");
            $this->line("    - Status   : {$pppoe->status}");
        } else {
            $this->error("  ✗ Username/Kode '{$username}' TIDAK DITEMUKAN di database!");
            $this->line("    Cek apakah Anda membuat voucher ini di tenant yang benar.");
            return 1;
        }

        // 3. RadiusAuthService Test
        $this->newLine();
        $this->info("[2/3] Menguji Autentikasi dsBilling...");
        $authResult = $authService->authenticate(
            username: $username,
            password: $password,
            nasIp: $nasIp,
            protocol: $voucher || $hotspot ? 'hotspot' : 'pppoe',
            authorizeOnly: false,
        );

        if ($authResult['ok']) {
            $this->info("  ✓ Autentikasi: SUCCESS (BERHASIL)");
            $this->line("    - Metode     : " . ($authResult['auth_method'] ?? '-'));
            $this->line("    - Cleartext  : " . ($authResult['cleartext_password'] ?? '-'));
        } else {
            $this->error("  ✗ Autentikasi GAGAL: " . ($authResult['reason'] ?? 'Unknown error'));
            return 1;
        }

        // 4. Policy & Reply Attributes Test
        $this->newLine();
        $this->info("[3/3] Menghitung Atribut RADIUS (MikroTik-Rate-Limit, dll)...");
        $ctx = $authResult['context'] ?? null;
        if ($ctx) {
            $authz = $authorizationService->authorize($ctx);
            if ($authz['accept']) {
                $this->info("  ✓ Authorization: ACCESS-ACCEPT");
                $this->line("    Reply Attributes:");
                foreach ($authz['reply_attributes'] as $k => $v) {
                    $this->line("      • <fg=yellow>{$k}</> = <fg=white>{$v}</>");
                }
            } else {
                $this->error("  ✗ Authorization: ACCESS-REJECT ({$authz['reject_reason']})");
            }
        }

        $this->newLine();
        $this->info("=================================================");
        $this->info("🎉 Akun ini VALID dan SIAP DIGUNAKAN di MikroTik!");
        $this->info("=================================================");
        return 0;
    }
}
