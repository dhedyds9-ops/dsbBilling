<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

$html = view('livewire.isp.technician.attendance.index', [
    'attendance' => null, 
    'history' => [],
    'errorMessage' => null,
])->render();
echo "Length: " . strlen($html) . "\n";
if (strpos($html, 'x-data="{') !== false) {
    echo "x-data IS rendered.\n";
} else {
    echo "x-data IS NOT rendered!\n";
}
