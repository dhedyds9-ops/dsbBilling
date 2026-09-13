<?php
require "vendor/autoload.php";
$app = require_once "bootstrap/app.php";
$app->make("Illuminate\Contracts\Console\Kernel")->bootstrap();

echo "=== LANGKAH 1 — VERIFIKASI ===\n\n";

echo "QUEUE_CONNECTION: " . env('QUEUE_CONNECTION') . "\n";
echo "QUEUE_FAILED_DRIVER: " . env('QUEUE_FAILED_DRIVER', '(default)') . "\n";
echo "DB_CONNECTION: " . env('DB_CONNECTION') . "\n\n";

echo "--- Pending Jobs per Queue ---\n";
$jobs = DB::table('jobs')->select('queue', DB::raw('count(*) as cnt'), DB::raw('min(available_at) as oldest'), DB::raw('max(available_at) as newest'))->groupBy('queue')->get();
foreach ($jobs as $j) {
    $age = round((time() - $j->oldest) / 60, 1);
    echo "  Queue: {$j->queue} | Jobs: {$j->cnt} | Oldest: " . date('Y-m-d H:i:s', $j->oldest) . " ({$age} min ago)\n";
}

echo "\n--- Failed Jobs ---\n";
$failed = DB::table('failed_jobs')->orderByDesc('failed_at')->take(5)->get();
echo "Total failed: " . DB::table('failed_jobs')->count() . "\n";
foreach ($failed as $f) {
    $payload = json_decode($f->payload);
    echo "  Queue: {$f->queue} | Class: " . ($payload->displayName ?? '?') . " | Failed: {$f->failed_at}\n";
    echo "  Exception: " . substr($f->exception ?? '', 0, 200) . "\n";
}

echo "\n--- router_monitoring_logs — Last 5 ---\n";
$logs = DB::table('router_monitoring_logs')->orderByDesc('id')->take(5)->get(['id','router_id','is_online','cpu_load','rx_bps','tx_bps','created_at']);
foreach ($logs as $l) {
    echo "  ID:{$l->id} router_id:{$l->router_id} online:{$l->is_online} cpu:{$l->cpu_load}% rx:{$l->rx_bps} tx:{$l->tx_bps} at:{$l->created_at}\n";
}
$logsLast5min = DB::table('router_monitoring_logs')->where('created_at','>=', now()->subMinutes(5)->toDateTimeString())->count();
$logsLast1hr  = DB::table('router_monitoring_logs')->where('created_at','>=', now()->subHour()->toDateTimeString())->count();
echo "  Logs in last 5min: {$logsLast5min}\n";
echo "  Logs in last 1hr:  {$logsLast1hr}\n";

echo "\n--- monitoring-router queue contents ---\n";
$rJobs = DB::table('jobs')->where('queue','monitoring-router')->get(['id','available_at','attempts','payload']);
foreach ($rJobs as $j) {
    $payload = json_decode($j->payload);
    $age = round((time() - $j->available_at) / 60, 1);
    echo "  JobID:{$j->id} | Class:" . ($payload->displayName ?? '?') . " | Attempts:{$j->attempts} | Age:{$age} min\n";
}
echo "  Total monitoring-router pending: " . count($rJobs) . "\n";

echo "\n=== RouterCollector catch check ===\n";
$content = file_get_contents('app/Services/Monitoring/Collectors/RouterCollector.php');
preg_match_all('/catch\s*\(\\\\([\w]+)\s*/', $content, $matches);
echo "Catch types found: " . implode(', ', $matches[1]) . "\n";
if (strpos($content, 'catch (\Exception') !== false && strpos($content, 'catch (\Throwable') === false) {
    echo "  ⚠ HIGH: Only catching \\Exception — ArgumentCountError (\\Error) will NOT be caught!\n";
} else {
    echo "  OK: Throwable or Error catch found.\n";
}
