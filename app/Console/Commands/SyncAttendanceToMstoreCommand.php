<?php

namespace App\Console\Commands;

use App\Models\Workforce\Attendance;
use App\Services\Workforce\MstoreAttendanceSyncService;
use Carbon\Carbon;
use Illuminate\Console\Command;

class SyncAttendanceToMstoreCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'attendance:sync-mstore 
                            {--date= : Tanggal absensi format Y-m-d (contoh: 2026-11-14)} 
                            {--days= : Sinkronkan N hari ke belakang (contoh: 7)} 
                            {--force : Paksa sinkronisasi bypass filter host/tenant}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Kirim dan sinkronkan ulang data absensi teknisi dsBilling ke aplikasi MStore (ms.mstore.id)';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $dateParam = $this->option('date');
        $daysParam = (int) $this->option('days');
        $force = (bool) $this->option('force');

        $query = Attendance::with(['technician.employee']);

        if ($dateParam) {
            $date = Carbon::parse($dateParam)->toDateString();
            $query->whereDate('date', $date);
            $this->info("Menyiapkan sinkronisasi absensi untuk tanggal: {$date}");
        } elseif ($daysParam > 0) {
            $from = Carbon::today()->subDays($daysParam)->toDateString();
            $query->whereDate('date', '>=', $from);
            $this->info("Menyiapkan sinkronisasi absensi {$daysParam} hari terakhir (sejak {$from})");
        } else {
            $date = Carbon::today()->toDateString();
            $query->whereDate('date', $date);
            $this->info("Menyiapkan sinkronisasi absensi hari ini ({$date})");
        }

        $attendances = $query->orderBy('date')->get();

        if ($attendances->isEmpty()) {
            $this->warn('Tidak ditemukan data absensi untuk kriteria yang dipilih.');
            return Command::SUCCESS;
        }

        $this->line("Ditemukan {$attendances->count()} data absensi. Mulai proses sinkronisasi ke MStore...");
        $bar = $this->output->createProgressBar($attendances->count());
        $bar->start();

        $success = 0;
        $failed = 0;

        foreach ($attendances as $att) {
            $result = MstoreAttendanceSyncService::sync($att, $force);
            if ($result) {
                $success++;
            } else {
                $failed++;
            }
            $bar->advance();
        }

        $bar->finish();
        $this->newLine(2);

        $this->info("Hasil Sinkronisasi ke MStore:");
        $this->line("✅ Berhasil terkirim: {$success}");
        if ($failed > 0) {
            $this->warn("⚠️  Gagal / Dilewati: {$failed} (Periksa file storage/logs/laravel.log untuk detailnya)");
            if (! $force) {
                $this->comment("Tips: Jika domain saat ini bukan mstore.mstore.id, gunakan flag --force untuk memaksa pengiriman.");
            }
        }

        return Command::SUCCESS;
    }
}
