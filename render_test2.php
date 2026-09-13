<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

$html = view('livewire.isp.technician.attendance.index', [
    'attendance' => null, 
    'history' => [],
    'errorMessage' => null,
])->render();

if (strpos($html, '@this') !== false) {
    echo "Found literal @this ! This means the Blade directive is ignored and causes JS SyntaxError!\n";
} else {
    echo "Compiled correctly!\n";
}
