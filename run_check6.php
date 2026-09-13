<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$custs = App\Models\CRM\Customer::where('code', 'like', '%2026082302%')->orWhere('phone', 'like', '%2026082302%')->get();
foreach($custs as $c) {
    echo "CRM Name: {$c->name}, Code: {$c->code}, Phone: {$c->phone}\n";
}
?>
