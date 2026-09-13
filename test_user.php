<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();
$u = \App\Models\User::first();
if ($u) {
    echo implode(', ', array_keys($u->getAttributes()));
}
