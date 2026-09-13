<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
$role = \App\Models\Role::where('name', 'technician')->first();
if ($role) {
    echo 'Technician role exists!';
} else {
    echo 'Technician role DOES NOT exist.';
}
