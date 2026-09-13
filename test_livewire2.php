<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

$html = view('layouts.technician-app', ['slot' => 'TEST'])->render();
echo $html;
