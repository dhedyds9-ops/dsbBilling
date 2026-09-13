<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

$a = App\Models\Workforce\Attendance::first();
echo 'columns: ' . implode(', ', array_keys($a->getAttributes())) . "\n";
$att = App\Models\Workforce\Attendance::whereDate('date', now()->toDateString())->first();
echo 'today: ' . ($att ? json_encode($att->only(['status','checked_in_at','checked_out_at'])) : 'null') . "\n";
?>
