<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

foreach(\App\Models\User::with('roles')->get() as $u) {
    echo $u->name . ' - Roles: ' . $u->roles->pluck('name')->join(', ') . "\n";
}
