<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

foreach(\App\Models\Billing\InvoiceItem::all() as $item) { 
    $invoice = $item->invoice; 
    if ($invoice && $invoice->customer && $invoice->customer->serviceProfile) { 
        $item->reseller_settlement_price = $invoice->customer->serviceProfile->reseller_price; 
        $item->save(); 
    } 
}

foreach(\App\Models\ISP\ServiceProfile::all() as $sp) {
    if ($sp->reseller_price) {
        $sp->reseller_settlement_price = $sp->reseller_price;
        $sp->save();
    }
}
echo "Done";
?>
