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
        $autoDetected = false;

        $tenantsDir = database_path('tenants');
        $sqliteFiles = is_dir($tenantsDir) ? glob($tenantsDir . '/*.sqlite') : [];

        if (!$tenantName) {
            // Cek apakah username ada di database default terlebih dahulu
            $foundInDefault = false;
            try {
                $foundInDefault = Voucher::withoutGlobalScopes()->where('code', $username)->exists()
                    || HotspotUser::withoutGlobalScopes()->where('username', $username)->exists()
                    || PPPoEUser::withoutGlobalScopes()->where('username', $username)->exists();
            } catch (\Throwable $e) {
                $foundInDefault = false;
            }

            if (!$foundInDefault && !empty($sqliteFiles)) {
                // Auto-detect dari file tenant sqlite yang ada
                foreach ($sqliteFiles as $file) {
                    $tName = basename($file, '.sqlite');
                    try {
                        $pdo = new \PDO('sqlite:' . $file);
                        $pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_SILENT);
                        
                        $stmt = $pdo->prepare('SELECT 1 FROM vouchers WHERE code = :u LIMIT 1');
                        $stmt->execute([':u' => $username]);
                        if ($stmt->fetch()) {
                            $tenantName = $tName;
                            $autoDetected = true;
                            break;
                        }

                        $stmt = $pdo->prepare('SELECT 1 FROM hotspot_users WHERE username = :u LIMIT 1');
                        $stmt->execute([':u' => $username]);
                        if ($stmt->fetch()) {
                            $tenantName = $tName;
                            $autoDetected = true;
                            break;
                        }

                        $stmt = $pdo->prepare('SELECT 1 FROM pppoe_users WHERE username = :u LIMIT 1');
                        $stmt->execute([':u' => $username]);
                        if ($stmt->fetch()) {
                            $tenantName = $tName;
                            $autoDetected = true;
                            break;
                        }
                    } catch (\Throwable $e) {
                        // ignore and continue
                    }
                }

                // Jika tetap tidak ditemukan dan hanya ada 1 tenant, pakai tenant tersebut
                if (!$tenantName && count($sqliteFiles) === 1) {
                    $tenantName = basename($sqliteFiles[0], '.sqlite');
                }
            }
        }

        if ($tenantName) {
            $label = $autoDetected ? "{$tenantName}.sqlite (Otomatis terdeteksi)" : "{$tenantName}.sqlite";
            $this->line("Tenant Database      : <fg=cyan>{$label}</>");
            $dbPath = database_path("tenants/{$tenantName}.sqlite");
            if (file_exists($dbPath)) {
                config([
                    'database.default' => 'sqlite',
                    'database.connections.sqlite.database' => $dbPath,
                ]);
                DB::purge();
                DB::reconnect('sqlite');
                $_SERVER['TENANT_MODE'] = true;
                $_SERVER['TENANT_NAME'] = $tenantName;
            }
        } else {
            $defaultDb = config('database.default');
            $dbName = config("database.connections.{$defaultDb}.database", 'default');
            $this->line("Database             : <fg=cyan>{$defaultDb} ({$dbName})</>");
        }

        // 2. Direct Model Lookup
        $this->newLine();
        $this->info("[1/3] Memeriksa Database...");

        $voucher = Voucher::withoutGlobalScopes()->where('code', $username)->first();
        $hotspot = HotspotUser::withoutGlobalScopes()->where('username', $username)->first();
        $pppoe = PPPoEUser::withoutGlobalScopes()->where('username', $username)->first();

        if ($voucher) {
            $this->line("  ✓ Ditemukan sebagai: <fg=green>Hotspot Voucher</>");
            $this->line("    - Kode     : {$voucher->code}");
            $this->line("    - Password : " . ($voucher->password ?: '(kosong - login pakai kode voucher)'));
            $this->line("    - Status   : {$voucher->status}");
            $profileName = $voucher->serviceProfile?->name 
                ?? $voucher->voucherPool?->serviceProfile?->name 
                ?? $voucher->profile_name 
                ?? '-';
            $this->line("    - Profile  : {$profileName}");
        } elseif ($hotspot) {
            $this->line("  ✓ Ditemukan sebagai: <fg=green>Hotspot User</>");
            $this->line("    - Username : {$hotspot->username}");
            $this->line("    - Status   : {$hotspot->status}");
            $profileName = $hotspot->serviceProfile?->name ?? $hotspot->profile_name ?? '-';
            $this->line("    - Profile  : {$profileName}");
        } elseif ($pppoe) {
            $this->line("  ✓ Ditemukan sebagai: <fg=green>PPPoE User</>");
            $this->line("    - Username : {$pppoe->username}");
            $this->line("    - Status   : {$pppoe->status}");
            $profileName = $pppoe->serviceProfile?->name ?? $pppoe->profile_name ?? '-';
            $this->line("    - Profile  : {$profileName}");
        } else {
            $this->error("  ✗ Username/Kode '{$username}' TIDAK DITEMUKAN di database!");
            if (!empty($sqliteFiles)) {
                $available = array_map(fn($f) => basename($f, '.sqlite'), $sqliteFiles);
                $this->line("    Tenant SQLite yang tersedia: " . implode(', ', $available));
                $this->line("    Gunakan opsi: <fg=yellow>--tenant=<nama_tenant></> jika voucher berada di tenant spesifik.");
            }
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
