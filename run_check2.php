<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$customer = App\Models\CRM\Customer::where('code', '000013')->first();
echo "Name: " . $customer->name . "\n";
echo "Phone: " . $customer->phone . "\n";
echo "Code: " . $customer->code . "\n";
?>
