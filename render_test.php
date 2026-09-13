<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

$html = view('livewire.isp.technician.attendance.index', [
    'attendance' => null, 
    'history' => [],
    'errorMessage' => null,
])->render();

preg_match('/gpsLoading: true[\s\S]*?init\(\)/', $html, $matches);
if ($matches) echo $matches[0];
else echo "No match";
