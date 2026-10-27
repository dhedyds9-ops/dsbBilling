<?php

namespace App\Console\Commands\ISP;

use App\Models\ISP\Olt;
use App\Services\ISP\OltPollingService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class PollOltCommand extends Command
{
    protected $signature = 'olt:poll {id? : ID OLT yang ingin disinkronkan} {--tenant=mstore : Nama tenant database}';
    protected $description = 'Sinkronkan data status ONU, RX/TX Power, dan MAC dari OLT';

    public function handle(OltPollingService $pollingService): int
    {
        $tenantName = $this->option('tenant') ?: 'mstore';
        $this->switchTenantDatabase($tenantName);

        $id = $this->argument('id');

        if ($id) {
            $olt = Olt::find($id) ?? Olt::where('name', 'like', "%{$id}%")->first();
            if (!$olt) {
                $this->error("OLT dengan ID/Nama '{$id}' tidak ditemukan di database!");
                return 1;
            }
            $olts = collect([$olt]);
        } else {
            $olts = Olt::whereNotNull('ip_address')->get();
            if ($olts->isEmpty()) {
                $this->warn("Tidak ada data OLT dengan IP Address.");
                return 0;
            }
        }

        foreach ($olts as $olt) {
            $this->info("==================================================");
            $this->info("MEMULAI SINKRONISASI OLT: [ID: {$olt->id}] {$olt->name}");
            $this->info("IP: {$olt->ip_address} | Port PON: " . ($olt->pon_port_count ?? 'N/A'));
            $this->info("==================================================");

            $startTime = microtime(true);
            try {
                $res = $pollingService->pollOlt($olt);
                $duration = round(microtime(true) - $startTime, 2);

                if (!empty($res['success'])) {
                    $online = (int)($res['onu_online'] ?? 0);
                    $offline = (int)($res['onu_offline'] ?? 0);
                    $total = $online + $offline;

                    $this->info("✓ Berhasil disinkronkan dalam {$duration} detik!");
                    $this->line("  - Total ONU  : {$total}");
                    $this->line("  - Online     : {$online}");
                    $this->line("  - Offline    : {$offline}");

                    // Cek sample ONU dengan RX Power
                    $withRx = \App\Models\ISP\Onu::where('olt_id', $olt->id)->whereNotNull('rx_power_dbm')->count();
                    $withMac = \App\Models\ISP\Onu::where('olt_id', $olt->id)->whereNotNull('mac_address')->count();
                    $this->line("  - ONU dengan RX Power : {$withRx}");
                    $this->line("  - ONU dengan MAC      : {$withMac}");

                    // Cek sample 5 ONU pertama
                    $samples = \App\Models\ISP\Onu::where('olt_id', $olt->id)->orderBy('serial_number')->take(5)->get();
                    $this->line("  --- SAMPLE 5 ONU PERTAMA DI DATABASE ---");
                    foreach ($samples as $s) {
                        $this->line("    SN: {$s->serial_number} | Port: {$s->pon_port} | RX: " . var_export($s->rx_power_dbm, true) . " | TX: " . var_export($s->tx_power_dbm, true) . " | Status: {$s->status}");
                    }
                } else {
                    $err = $res['error'] ?? $res['message'] ?? 'Unknown error';
                    $this->error("✗ Gagal sinkronisasi: {$err}");
                }
            } catch (\Throwable $e) {
                $this->error("✗ Error: " . $e->getMessage());
            }
            $this->newLine();
        }

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
}
