<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

$html = view('layouts.customer-app', ['slot' => 'TEST'])->render();
echo substr($html, -1000);
