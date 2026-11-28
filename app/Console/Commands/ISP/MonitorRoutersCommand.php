<?php

declare(strict_types=1);

namespace App\Console\Commands\ISP;

use App\Models\ISP\Router;
use App\Services\Monitoring\Collectors\RouterCollector;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class MonitorRoutersCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'dsbilling:monitor-routers {--tenant= : Nama tenant spesifik jika multi-tenant (contoh: winets)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Pindai status online/offline router MikroTik dan kirim peringatan otomatis ke grup Telegram';

    /**
     * Execute the console command.
     */
    public function handle(RouterCollector $collector): int
    {
        $tenantName = $this->option('tenant');
        if ($tenantName) {
            $_SERVER['TENANT_MODE'] = true;
            $_SERVER['TENANT_NAME'] = $tenantName;

            $dbConnection = config('database.default');
            if ($dbConnection === 'sqlite') {
                $dbPath = database_path("tenants/{$tenantName}.sqlite");
                if (file_exists($dbPath)) {
                    config(['database.connections.sqlite.database' => $dbPath]);
                }
            } elseif ($dbConnection === 'mysql') {
                $dbName = env('DB_TENANT_PREFIX', 'dsbilling_') . $tenantName;
                config(['database.connections.mysql.database' => $dbName]);
            }
            DB::purge($dbConnection);
            DB::reconnect($dbConnection);
            $this->line("<fg=cyan>[TENANT ACTIVE]</> Menggunakan database tenant: <fg=yellow>{$tenantName}</>");
        }

        $this->info('[' . now()->toDateTimeString() . '] Memulai pemindaian status router MikroTik...');

        $routers = Router::active()->get();
        $total = $routers->count();

        if ($total === 0) {
            $this->warn('Tidak ada router aktif yang terdaftar untuk dipindai.');
            return self::SUCCESS;
        }

        $this->line("Ditemukan {$total} router aktif. Menjalankan ping dan sinkronisasi status...");

        $collector->collect();

        // Tampilkan hasil akhir
        $rows = [];
        foreach ($routers as $r) {
            $r->refresh();
            $isOnline = $r->is_online ?? false;
            $statusText = $isOnline ? '<fg=green>ONLINE</>' : '<fg=red>OFFLINE</>';
            $rows[] = [
                $r->id,
                $r->name,
                $r->ip_address,
                $statusText,
                $r->last_seen_at ? $r->last_seen_at->format('Y-m-d H:i:s') : '-',
            ];
        }

        $this->table(['ID', 'Nama Router', 'IP Address', 'Status', 'Last Seen'], $rows);
        $this->info('✔ Pemindaian router selesai.');

        return self::SUCCESS;
    }
}
