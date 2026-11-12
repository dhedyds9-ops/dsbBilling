<?php

namespace App\Console\Commands\ISP;

use App\Models\ISP\Onu;
use App\Models\ACS\ACSDevice;
use App\Services\Adapters\Monitoring\GenieACSDriver;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class PurgeGhostOnusCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'onu:purge-ghosts {--force : Hapus langsung tanpa konfirmasi interaktif}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Bersihkan data modem/ONU hantu (ghost/orphan) yang tidak terikat pelanggan dan tidak ada di OLT maupun GenieACS';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('Memulai pemindaian data modem hantu / orphan di database...');

        // 1. Ambil daftar serial number yang saat ini benar-benar ada di GenieACS
        $activeGenieAcsSns = [];
        try {
            $acsDriver = new GenieACSDriver();
            $devices = $acsDriver->listDevices([], 5000);
            foreach ($devices as $d) {
                $sn = $d['_deviceId']['_SerialNumber'] ?? null;
                if (!empty($sn)) {
                    $activeGenieAcsSns[strtoupper(trim($sn))] = true;
                }
            }
            $this->info('GenieACS terhubung: ' . count($activeGenieAcsSns) . ' perangkat terdeteksi.');
        } catch (\Throwable $e) {
            $this->warn('GenieACS tidak dapat dijangkau (' . $e->getMessage() . '). Melanjutkan dengan verifikasi OLT.');
        }

        // 2. Ambil semua ONU yang:
        //    - Tidak terikat ke pelanggan manapun (tidak memiliki relasi customerService)
        //    - Tidak memiliki redaman optik aktif (rx_power_dbm null atau tidak valid)
        //    - Tidak terlihat di OLT dalam 24 jam terakhir (last_seen_at null atau > 24 jam)
        $ghostCandidates = Onu::withoutGlobalScopes()
            ->whereDoesntHave('customerService')
            ->where(function ($q) {
                $q->whereNull('last_seen_at')
                  ->orWhere('last_seen_at', '<', now()->subHours(24));
            })
            ->where(function ($q) {
                $q->whereNull('rx_power_dbm')
                  ->orWhere('rx_power_dbm', '>=', 0)
                  ->orWhere('rx_power_dbm', '<=', -45);
            })
            ->get();

        if ($ghostCandidates->isEmpty()) {
            $this->info('Tidak ditemukan data modem hantu. Database dalam kondisi bersih.');
            return 0;
        }

        $this->line("Ditemukan {$ghostCandidates->count()} kandidat modem yang tidak terikat pelanggan dan tidak aktif di OLT.");

        $toDelete = [];
        foreach ($ghostCandidates as $onu) {
            $snClean = strtoupper(trim((string)$onu->serial_number));

            // Jika modem ini masih terdaftar di GenieACS aktif, jangan dihapus
            if (!empty($snClean) && isset($activeGenieAcsSns[$snClean])) {
                continue;
            }

            $toDelete[] = $onu;
        }

        if (empty($toDelete)) {
            $this->info('Semua kandidat masih terdeteksi aktif di GenieACS. Tidak ada yang dihapus.');
            return 0;
        }

        $this->warn('Ditemukan ' . count($toDelete) . ' modem hantu (tidak ada di OLT & tidak ada di GenieACS):');
        foreach ($toDelete as $item) {
            $this->line(" - ID #{$item->id} | SN: {$item->serial_number} | Model: {$item->model} | Status: {$item->status}");
        }

        if (!$this->option('force') && !$this->confirm('Apakah Anda yakin ingin menghapus data-data modem di atas secara permanen?', true)) {
            $this->info('Operasi dibatalkan oleh pengguna.');
            return 0;
        }

        $deletedCount = 0;
        foreach ($toDelete as $onu) {
            try {
                $sn = $onu->serial_number;

                // Hapus juga record orphan di acs_devices jika tidak terikat pelanggan
                if (!empty($sn)) {
                    ACSDevice::withoutGlobalScopes()
                        ->where('serial_number', $sn)
                        ->whereNull('customer_service_id')
                        ->forceDelete();
                }

                $onu->forceDelete();
                $deletedCount++;
            } catch (\Throwable $e) {
                $this->error("Gagal menghapus ONU #{$onu->id}: " . $e->getMessage());
            }
        }

        $this->info("Berhasil membersihkan {$deletedCount} data modem hantu dari database.");
        Log::info("PurgeGhostOnusCommand: Dihapus {$deletedCount} data modem hantu dari database.");

        return 0;
    }
}
