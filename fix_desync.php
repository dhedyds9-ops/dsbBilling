<?php
$customers = \App\Models\CRM\Customer::all();
$fixed = 0;
foreach($customers as $customer) {
    if (!$customer->reseller_id) continue;
    
    $services = \App\Models\Customer\CustomerService::withoutGlobalScope('branch_isolation')
        ->where('customer_id', $customer->id)
        ->get();
        
    foreach($services as $svc) {
        if ($svc->reseller_id !== $customer->reseller_id) {
            $svc->update(['reseller_id' => $customer->reseller_id]);
            $fixed++;
        }
        
        $pppoe = \App\Models\ISP\PPPoEUser::withoutGlobalScope('branch_isolation')
            ->where('customer_service_id', $svc->id)
            ->first();
        if ($pppoe && $pppoe->reseller_id !== $customer->reseller_id) {
            $pppoe->update(['reseller_id' => $customer->reseller_id]);
            $fixed++;
        }
        
        $hotspot = \App\Models\ISP\HotspotUser::withoutGlobalScope('branch_isolation')
            ->where('customer_service_id', $svc->id)
            ->first();
        if ($hotspot && $hotspot->reseller_id !== $customer->reseller_id) {
            $hotspot->update(['reseller_id' => $customer->reseller_id]);
            $fixed++;
        }
    }
}
echo "Fixed $fixed desynced records.\n";
