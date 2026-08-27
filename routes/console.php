<?php

use App\Jobs\ISP\CheckOverdueInvoicesJob;
use App\Services\ISP\FiberLinkStatusService;
use App\Services\ISP\OdpOccupancyService;
use App\Services\ISP\OltPollingService;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(\Illuminate\Foundation\Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::command('billing:run-automation')
    ->dailyAt('08:00')
    ->timezone('Asia/Jakarta');

Schedule::command('billing:run-automation')
    ->dailyAt('18:00')
    ->timezone('Asia/Jakarta');

Schedule::job(new CheckOverdueInvoicesJob(graceDays: 0))
    ->dailyAt('09:00')
    ->timezone('Asia/Jakarta')
    ->name('Check Overdue Invoices and Isolate Users');

Schedule::command('radius:reap-dead-sessions --timeout=3600')
    ->everyTenMinutes()
    ->timezone('Asia/Jakarta')
    ->withoutOverlapping(10)
    ->name('Reap Dead Radius Accounting Sessions');

Schedule::command('radius:resolve-lifecycle-fk --limit=5000')
    ->everyFiveMinutes()
    ->timezone('Asia/Jakarta')
    ->withoutOverlapping(5)
    ->name('Resolve Radius Accounting Lifecycle FK Backfill');

Schedule::command('radius:nas-map-device')
    ->hourly()
    ->timezone('Asia/Jakarta')
    ->withoutOverlapping(20)
    ->name('Map radius_nas to nas_devices by IP address');

Schedule::command('voucher:reap-expired')
    ->everyFiveMinutes()
    ->timezone('Asia/Jakarta')
    ->withoutOverlapping(5)
    ->name('Reap Expired Vouchers');

// =================== ISP FIBER POLLING ===================
Schedule::call(function (OltPollingService $polling) {
    $polling->pollAll();
})
    ->everyFiveMinutes()
    ->timezone('Asia/Jakarta')
    ->name('OLT / ONU SNMP Signal Polling')
    ->withoutOverlapping(5);

Schedule::call(function (OdpOccupancyService $svc) {
    $svc->recalculateAll();
})
    ->everyThirtyMinutes()
    ->timezone('Asia/Jakarta')
    ->name('ODP Occupancy Recalculator')
    ->withoutOverlapping(15);

Schedule::call(function (FiberLinkStatusService $svc) {
    $problems = $svc->getProblematicOdps(100);
    if (count($problems) > 0) {
        \Illuminate\Support\Facades\Log::warning('ODP problem detected', [
            'count' => count($problems),
            'top5' => array_slice($problems, 0, 5),
        ]);
    }
})
    ->hourly()
    ->timezone('Asia/Jakarta')
    ->name('Fiber Link Quality Alerting (Problem ODP)')
    ->withoutOverlapping(30);
