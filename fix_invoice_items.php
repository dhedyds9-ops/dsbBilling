<?php
use App\Models\Billing\InvoiceItem;
use Illuminate\Support\Facades\DB;

$items = InvoiceItem::whereNull('reseller_settlement_price')->orWhere('reseller_settlement_price', 0)->get();
$count = 0;
foreach ($items as $item) {
    if ($item->invoice && $item->invoice->customer && $item->invoice->customer->customerServices->isNotEmpty()) {
        $profile = $item->invoice->customer->customerServices->first()->serviceProfile;
        if ($profile) {
            $item->owner_settlement_price = $profile->owner_settlement_price ?: $profile->owner_price;
            $item->reseller_settlement_price = $profile->reseller_settlement_price ?: $profile->reseller_price;
            $item->branch_settlement_price = $profile->branch_settlement_price ?: 0;
            $item->save();
            $count++;
        }
    }
}
echo 'Fixed ' . $count . ' invoice items.';
