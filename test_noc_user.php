<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();
$u = \App\Models\User::where('name', 'NOC')->first();
if ($u) {
    echo "Name: " . $u->name . "\nJob Function: " . $u->job_function . "\n";
}
