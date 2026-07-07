<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;

DB::table('migrations')->insert([
    'migration' => '2026_06_30_040210_create_activations_table',
    'batch' => 7,
]);

echo "Migrasi berhasil ditandai sebagai ran!\n";
