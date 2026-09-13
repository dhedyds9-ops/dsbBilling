<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$c1 = App\Models\CRM\Customer::where('code', '2026082302')->first();
if ($c1) { echo "Found Customer: " . $c1->name . " with user_id: " . $c1->user_id . "\n"; }

$u1 = App\Models\User::where('customer_code', '2026082302')->first();
if ($u1) { echo "Found User: " . $u1->name . " with id: " . $u1->id . "\n"; }
?>
