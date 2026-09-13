<?php
foreach(\App\Models\Billing\InvoiceItem::all() as $item) { 
    $invoice = $item->invoice; 
    if ($invoice && $invoice->customer && $invoice->customer->serviceProfile) { 
        $item->reseller_settlement_price = $invoice->customer->serviceProfile->reseller_price; 
        $item->save(); 
    } 
}
echo "Done";
?>
