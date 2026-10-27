<?php

namespace App\Console\Commands\ISP;

use App\Models\ISP\Olt;
use App\Services\Adapters\Provisioning\Support\SnmpClient;
use App\Services\Adapters\Provisioning\Support\TelnetSshClient;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class DebugCdataCommand extends Command
{
    protected $signature = 'olt:debug-cdata {id=1} {--tenant=mstore}';
    protected $description = 'Diagnosa mendalam koneksi SNMP & CLI OLT C-Data';

    public function handle(): int
    {
        $tenantName = $this->option('tenant') ?: 'mstore';
        $this->switchTenantDatabase($tenantName);

        $id = $this->argument('id');
        $olt = Olt::find($id) 
            ?? Olt::where('name', 'like', '%CDATA%')->first() 
            ?? Olt::where('ip_address', '192.168.100.2')->first() 
            ?? Olt::first();

        // Jika di tenant mstore belum ketemu, coba scan database tenant lain
        if (!$olt) {
            $foundTenant = $this->autoDiscoverTenantWithOlt();
            if ($foundTenant) {
                $olt = Olt::find($id) ?? Olt::where('name', 'like', '%CDATA%')->first() ?? Olt::first();
            }
        }

        if (!$olt) {
            $this->error("Tidak ditemukan data OLT di database (sudah memeriksa tenant: {$tenantName})!");
            $this->warn("Gunakan: php artisan olt:debug-cdata 1 --tenant=NAMA_TENANT");
            return 1;
        }

        $this->info("==================================================");
        $this->info("DIAGNOSA OLT CDATA: [ID: {$olt->id}] {$olt->name}");
        $this->info("==================================================");
        $this->line("Tenant Database    : " . config('database.connections.' . config('database.default') . '.database'));
        $this->line("IP Address         : " . ($olt->ip_address ?? '-'));
        $this->line("SNMP Community     : " . ($olt->snmp_community_read ?: 'public'));
        $this->line("SNMP Port          : " . ($olt->snmp_port ?? 161));
        $this->line("SNMP Version       : " . ($olt->snmp_version ?? '2c'));
        $this->line("Telnet Port        : " . ($olt->cli_port ?? 23));
        $this->line("Telnet User        : " . ($olt->username ?? '-'));
        $this->line("Telnet Password    : " . ($olt->password ? '***TERISI***' : 'KOSONG'));
        $this->newLine();

        $community = $olt->snmp_community_read ?: 'public';
        $port = (int)($olt->snmp_port ?? 161);
        $version = $olt->snmp_version ?? '2c';

        $snmp = new SnmpClient($olt->ip_address, $community, $version, 3000000, 1, $port);

        // 1. SysDescr
        $this->info("[1/5] Menguji Koneksi Dasar SNMP (sysDescr)...");
        $sysDescr = $snmp->get('.1.3.6.1.2.1.1.1.0');
        if ($sysDescr) {
            $this->line("  ✓ sysDescr: {$sysDescr}");
        } else {
            $this->error("  ✗ sysDescr gagal / timeout! Pastikan Community String dan IP benar.");
        }

        // 2. PON Ports
        $this->info("[2/5] Membaca Port PON (.1.3.6.1.4.1.34592.1.3.100.2.1.1.4)...");
        $ports = $snmp->walk('.1.3.6.1.4.1.34592.1.3.100.2.1.1.4');
        $this->line("  Ditemukan " . count($ports) . " port PON.");
        foreach ($ports as $k => $v) {
            $this->line("    Port {$k} => Status: {$v}");
        }

        // 3. Scan ONU Tables & Standard MIBs
        $this->info("[3/5] Memindai Tabel SNMP (Walk)...");
        $candidates = [
            'FD1608S GPON Config SN (.1.3.6.1.4.1.34592.1.5.1.1.2.18.1.1.4)' => '.1.3.6.1.4.1.34592.1.5.1.1.2.18.1.1.4',
            'FD-ONU-MIB Base Entry (.1.3.6.1.4.1.34592.1.3.4.1.1)'              => '.1.3.6.1.4.1.34592.1.3.4.1.1',
            'Standard Bridge MAC dot1dTpFdb (.1.3.6.1.2.1.17.4.3.1.1)'         => '.1.3.6.1.2.1.17.4.3.1.1',
            'Standard ARP / IP NetToMedia (.1.3.6.1.2.1.4.22.1.2)'             => '.1.3.6.1.2.1.4.22.1.2',
            'IP NetToMedia NetAddress (.1.3.6.1.2.1.4.22.1.3)'                 => '.1.3.6.1.2.1.4.22.1.3',
            'FD1608S GPON Optical Rx (.1.3.6.1.4.1.34592.1.5.1.1.2.19.1.1.3)' => '.1.3.6.1.4.1.34592.1.5.1.1.2.19.1.1.3',
            'FD1608S GPON Optical Rx Alt (.1.3.6.1.4.1.34592.1.5.1.1.2.20.1.1.3)' => '.1.3.6.1.4.1.34592.1.5.1.1.2.20.1.1.3',
        ];

        $foundAny = false;
        foreach ($candidates as $label => $oid) {
            $res = $snmp->walk($oid);
            if (!empty($res)) {
                $foundAny = true;
                $this->info("  [HASIL DITEMUKAN] {$label}: " . count($res) . " baris");
                foreach (array_slice($res, 0, 5, true) as $k => $v) {
                    $this->line("    {$k} => {$v}");
                }
            } else {
                $this->line("  - {$label}: kosong");
            }
        }

        // 4. Test Telnet CLI
        $this->newLine();
        $this->info("[4/5] Menguji Koneksi Telnet CLI & Perintah Detil...");
        if (empty($olt->username) || empty($olt->password)) {
            $this->warn("  Username/Password Telnet di database KOSONG. Lewati pengujian Telnet.");
            $this->warn("  Saran: Masukkan Username & Password Telnet di Web billing menu Edit OLT (default biasanya admin/admin).");
        } else {
            try {
                $cli = new TelnetSshClient();
                $cli->connect($olt->ip_address, $olt->username, $olt->password, 'telnet', (int)($olt->cli_port ?? 23));
                $this->info("  ✓ Berhasil login Telnet!");

                // Test berbagai variasi perintah optical-info
                $testCmds = [
                    'show ont optical-info 1 all',
                    'show ont optical-info 1',
                    'show mac-address port gpon 0/0/1',
                    'show mac-address dynamic',
                    'show arp dynamic',
                ];

                foreach ($testCmds as $cmd) {
                    $this->line("  --- TEST CMD: '{$cmd}' ---");
                    $out = $cli->execute("{$cmd}\r\n", false);
                    $lines = array_slice(explode("\n", trim((string)$out)), 0, 8);
                    $this->line(implode("\n", $lines) ?: '  (kosong)');
                }

                // Test masuk ke interface gpon 0/0 dan jalankan show ont optical-info <port> all
                $this->line("  --- TEST OPTICAL INFO DALAM INTERFACE: 'interface gpon 0/0' ---");
                $cli->execute("config\r\n", false);
                $cli->execute("interface gpon 0/0\r\n", false);
                
                $inOpt1 = $cli->execute("show ont optical-info 1 all\r\n", false);
                $this->line("  Output 'show ont optical-info 1 all':");
                $lines1 = array_slice(explode("\n", trim((string)$inOpt1)), 0, 10);
                $this->line(implode("\n", $lines1) ?: '  (kosong)');

                $inOpt3 = $cli->execute("show ont optical-info 3 all\r\n", false);
                $this->line("  Output 'show ont optical-info 3 all':");
                $lines3 = array_slice(explode("\n", trim((string)$inOpt3)), 0, 10);
                $this->line(implode("\n", $lines3) ?: '  (kosong)');

                $cli->execute("exit\r\n", false);
                $cli->execute("exit\r\n", false);

                // Test parse MAC Address
                $this->newLine();
                $this->line("  --- TEST PARSE 'show mac-address dynamic' ---");
                $macOut = $cli->execute("show mac-address dynamic\r\n", false);
                $macCount = 0;
                foreach (explode("\n", (string)$macOut) as $mLine) {
                    $mLine = trim($mLine);
                    if (preg_match('/^([0-9A-Fa-f]{2}[:-][0-9A-Fa-f]{2}[:-][0-9A-Fa-f]{2}[:-][0-9A-Fa-f]{2}[:-][0-9A-Fa-f]{2}[:-][0-9A-Fa-f]{2})\s+\d+\s+\S+\s+\S+\s+gpon0\/0\/(\d+)\s+(\d+)/', $mLine, $matches)) {
                        $macCount++;
                        if ($macCount <= 5) {
                            $this->line("    Port {$matches[2]} | ONU {$matches[3]} => MAC {$matches[1]}");
                        }
                    }
                }
                $this->info("  Total MAC berhasil diparsing: {$macCount} MAC.");

            } catch (\Throwable $e) {
                $this->error("  ✗ Telnet CLI Gagal: " . $e->getMessage());
            }
        }

        $this->newLine();
        $this->info("[5/5] Diagnosa Selesai.");
        return 0;
    }

    protected function switchTenantDatabase(string $tenantName): void
    {
        $dbConnection = config('database.default');

        if ($dbConnection === 'sqlite') {
            $dbPath = database_path("tenants/{$tenantName}.sqlite");
            if (file_exists($dbPath)) {
                config(['database.connections.sqlite.database' => $dbPath]);
                DB::purge('sqlite');
                DB::reconnect('sqlite');
            }
        } elseif ($dbConnection === 'mysql') {
            $prefix = env('DB_TENANT_PREFIX', 'dsbilling_');
            $candidates = [$prefix . $tenantName, $tenantName, $prefix . 'mstore', 'mstore'];
            foreach ($candidates as $cand) {
                try {
                    $exists = DB::select("SELECT SCHEMA_NAME FROM INFORMATION_SCHEMA.SCHEMATA WHERE SCHEMA_NAME = ? LIMIT 1", [$cand]);
                    if (!empty($exists)) {
                        config(['database.connections.mysql.database' => $cand]);
                        DB::purge('mysql');
                        DB::reconnect('mysql');
                        break;
                    }
                } catch (\Throwable) {}
            }
        }
    }

    protected function autoDiscoverTenantWithOlt(): bool
    {
        $dbConnection = config('database.default');

        if ($dbConnection === 'sqlite') {
            $files = glob(database_path('tenants/*.sqlite'));
            foreach ($files as $file) {
                config(['database.connections.sqlite.database' => $file]);
                DB::purge('sqlite');
                DB::reconnect('sqlite');
                if (Olt::count() > 0) {
                    return true;
                }
            }
        } elseif ($dbConnection === 'mysql') {
            try {
                $databases = DB::select("SELECT SCHEMA_NAME FROM INFORMATION_SCHEMA.SCHEMATA WHERE SCHEMA_NAME LIKE '%dsbilling%' OR SCHEMA_NAME LIKE '%mstore%'");
                foreach ($databases as $dbRow) {
                    $dbName = $dbRow->SCHEMA_NAME;
                    config(['database.connections.mysql.database' => $dbName]);
                    DB::purge('mysql');
                    DB::reconnect('mysql');
                    try {
                        if (Olt::count() > 0) {
                            return true;
                        }
                    } catch (\Throwable) {}
                }
            } catch (\Throwable) {}
        }

        return false;
    }
}
