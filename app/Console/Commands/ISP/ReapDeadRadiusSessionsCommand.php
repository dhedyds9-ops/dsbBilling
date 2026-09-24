<?php

namespace App\Console\Commands\ISP;

use App\Models\ISP\RadiusAccounting;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Throwable;

class ReapDeadRadiusSessionsCommand extends Command
{
    protected $signature = 'radius:reap-dead-sessions
                            {--timeout=3600 : Maximum session age in seconds before considered dead (default 1h)}
                            {--chunk=500}
                            {--dry-run}';
    protected $description = 'Close Radius Accounting sessions that have no stop packet and are older than timeout';

    public function handle(): int
    {
        $timeoutSec = (int)$this->option('timeout');
        $chunk = (int)$this->option('chunk');
        $dry = (bool)$this->option('dry-run');

        $total = RadiusAccounting::query()
            ->whereNull('acct_stop_time')
            ->where(function ($q) use ($timeoutSec) {
                $q->whereNotNull('acct_start_time')
                    ->whereRaw("TIMESTAMPDIFF(SECOND, acct_start_time, NOW()) >= {$timeoutSec}");
            })
            ->orWhere(function ($q) use ($timeoutSec) {
                $q->whereNull('acct_start_time')
                    ->whereRaw("TIMESTAMPDIFF(SECOND, received_at, NOW()) >= {$timeoutSec}");
            })
            ->count();

        $this->info("Dead sessions (timeout={$timeoutSec}s) count: {$total}");

        if ($dry || $total === 0) {
            return self::SUCCESS;
        }

        $updated = 0;
        RadiusAccounting::query()
            ->select(['id', 'acct_start_time', 'received_at', 'acct_session_time'])
            ->whereNull('acct_stop_time')
            ->where(function ($q) use ($timeoutSec) {
                $q->whereNotNull('acct_start_time')
                    ->whereRaw("TIMESTAMPDIFF(SECOND, acct_start_time, NOW()) >= {$timeoutSec}");
            })
            ->orWhere(function ($q) use ($timeoutSec) {
                $q->whereNull('acct_start_time')
                    ->whereRaw("TIMESTAMPDIFF(SECOND, received_at, NOW()) >= {$timeoutSec}");
            })
            ->chunkById($chunk, function ($rows) use (&$updated, $timeoutSec) {
                foreach ($rows as $row) {
                    try {
                        $start = $row->acct_start_time ?? $row->created_at ?? now();
                        $estSessionSec = $row->acct_session_time ?? max(0, $timeoutSec);
                        DB::table('radius_accounting')
                            ->where('id', $row->id)
                            ->whereNull('acct_stop_time')
                            ->update([
                                'acct_stop_time' => now(),
                                'acct_terminate_cause' => 'NAS-Timeout',
                                'terminate_cause_id' => 10,
                                'acct_session_time' => $estSessionSec,
                                'updated_at' => now(),
                            ]);
                        $updated++;
                    } catch (Throwable $e) {
                        $this->error("Reap gagal #{$row->id}: {$e->getMessage()}");
                    }
                }
            });

        $this->info("Sessions ditutup paksa: {$updated}");

        return self::SUCCESS;
    }
}
