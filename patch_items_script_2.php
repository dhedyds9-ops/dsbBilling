<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

foreach(\App\Models\Billing\InvoiceItem::all() as $item) { 
    $invoice = $item->invoice; 
    if ($invoice && $invoice->customer) {
        $cs = $invoice->customer->customerServices()->with('serviceProfile')->first();
        if ($cs && $cs->serviceProfile) {
            $item->reseller_settlement_price = $cs->serviceProfile->reseller_price; 
            $item->save(); 
        }
    } 
}
echo "Done items";
?>
