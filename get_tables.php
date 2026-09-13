<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();
print_r(array_column(DB::select("SELECT name FROM sqlite_schema WHERE type='table' ORDER BY name"), 'name'));
