<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);

$user = App\Models\User::whereHas('roles', function($q) { $q->where('name', 'manager'); })->where('job_function', 'TECHNICIAN')->first();
if ($user) {
    Auth::login($user);
    $request = Illuminate\Http\Request::create('/technician/dashboard', 'GET');
    $response = $kernel->handle($request);
    echo 'Technician Dashboard Status: ' . $response->status() . PHP_EOL;
} else {
    echo 'No technician user found.' . PHP_EOL;
}
