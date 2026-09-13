<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$request = Illuminate\Http\Request::create('/login', 'GET');
$response = $kernel->handle($request);
echo 'Admin Login Status: ' . $response->status() . PHP_EOL;

$request2 = Illuminate\Http\Request::create('/customer/login', 'GET');
$response2 = $kernel->handle($request2);
echo 'Customer Login Status: ' . $response2->status() . PHP_EOL;

$request3 = Illuminate\Http\Request::create('/login', 'POST', ['login' => 'admin', 'password' => 'admin', 'login_type' => 'admin']);
$response3 = $kernel->handle($request3);
echo 'Admin Login Submit Status: ' . $response3->status() . PHP_EOL;
