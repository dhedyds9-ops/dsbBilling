<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);

$user = App\Models\User::whereHas('roles', function($q) { $q->where('name', 'customer'); })->first();
if ($user) {
    Auth::login($user);
    $request2 = Illuminate\Http\Request::create('/customer-portal/dashboard', 'GET');
    $response2 = $kernel->handle($request2);
    echo 'Customer Dashboard Status: ' . $response2->status() . PHP_EOL;
}
