<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Services\Notifications\FieldServiceNotificationService;
use App\Services\Support\SlaTrackingService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class MonitorSlaCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'dsbilling:monitor-sla {--force : Paksa evaluasi ulang seluruh pekerjaan aktif}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Monitor SLA status across Work Orders, Tickets, and Installations, and trigger warnings/escalations';

    /**
     * Execute the console command.
     */
    public function handle(
        SlaTrackingService $slaService,
        FieldServiceNotificationService $notificationService
    ): int {
        $this->info('[' . now()->toDateTimeString() . '] Memulai pemindaian SLA operasional dsBilling...');

        $jobs = $slaService->getUniqueActiveJobs();
        $totalJobs = $jobs->count();
        $warningCount = 0;
        $breachedCount = 0;
        $escalatedCount = 0;

        $this->line("Ditemukan {$totalJobs} pekerjaan aktif unik untuk dievaluasi.");

        foreach ($jobs as $jobItem) {
            $model = $jobItem->model;

            // Inisialisasi snapshot SLA jika belum pernah diinisialisasi
            if (empty($model->sla_due_at) || empty($model->sla_target_minutes)) {
                $slaService->initializeSla($model);
                $model->refresh();
            }

            $metrics = $slaService->calculateMetrics($model);
            $status = $metrics['status'];

            // Sinkronkan status ke model jika berubah
            if ($model->sla_status !== $status) {
                $model->sla_status = $status;
                $model->saveQuietly();
            }

            // 1. Tangani Status WARNING (>= 80% SLA)
            if ($status === 'warning') {
                $warningCount++;
                if (empty($model->sla_warning_sent_at)) {
                    $model->sla_warning_sent_at = now();
                    $model->saveQuietly();

                    $notificationService->notifySlaWarning($model, $metrics);
                    $this->warn(" [WARNING 80%] {$jobItem->code} - {$jobItem->title} ({$metrics['percentage_used']}%, sisa: {$metrics['remaining_formatted']})");
                }
            }

            // 2. Tangani Status BREACHED (> 100% SLA)
            elseif ($status === 'breached') {
                $breachedCount++;
                if (empty($model->sla_breached_sent_at)) {
                    $model->sla_breached_sent_at = now();
                    $model->saveQuietly();

                    $notificationService->notifySlaBreached($model, $metrics);
                    $escalatedCount++;
                    $this->error(" [ESKALASI BREACHED] {$jobItem->code} - {$jobItem->title} (Terlambat: {$metrics['remaining_formatted']})");
                }
            }
        }

        $this->info("Pemindaian selesai. Total: {$totalJobs} | Warning: {$warningCount} | Breached: {$breachedCount} | Eskalasi Baru: {$escalatedCount}");
        Log::info("[MonitorSlaCommand] Evaluasi selesai", [
            'total'     => $totalJobs,
            'warning'   => $warningCount,
            'breached'  => $breachedCount,
            'escalated' => $escalatedCount,
        ]);

        return Command::SUCCESS;
    }
}
