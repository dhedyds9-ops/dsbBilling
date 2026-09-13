<?php
$data = \App\Models\Billing\Invoice::with('customer.serviceProfile')->whereIn('id', [36,37,38,39])->get()->map(function($i) { 
    return [
        'invoice_id' => $i->id, 
        'has_customer' => $i->customer != null, 
        'has_sp' => $i->customer && $i->customer->serviceProfile != null, 
        'sp_reseller_price' => $i->customer && $i->customer->serviceProfile ? $i->customer->serviceProfile->reseller_price : null
    ]; 
})->toArray();
dump($data);
?>
