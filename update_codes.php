<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$customers = App\Models\CRM\Customer::all();
foreach($customers as $c) {
    // Generate the dynamic ID requested by the user
    $dynamicId = ($c->created_at ? $c->created_at->format('Ymd') : date('Ymd')) . str_pad($c->id % 100, 2, '0', STR_PAD_LEFT);
    
    // Check if the current code is different
    if ($c->code !== $dynamicId) {
        echo "Updating customer {$c->id} code from {$c->code} to {$dynamicId}\n";
        $c->code = $dynamicId;
        $c->save();
        
        // Also update the User's customer_code if exists
        if ($c->user_id) {
            $u = App\Models\User::find($c->user_id);
            if ($u) {
                $u->customer_code = $dynamicId;
                $u->save();
            }
        }
    }
}
echo "All customer codes updated to the requested format!\n";
?>
