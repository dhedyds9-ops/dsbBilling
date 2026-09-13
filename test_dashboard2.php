<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

$html = view('livewire.customer-portal.dashboard', [])->render();
if (strpos($html, 'livewire.js') !== false || strpos($html, 'livewire/livewire') !== false) {
    echo "Livewire script is rendered!\n";
} else {
    echo "Livewire script is MISSING!\n";
}
