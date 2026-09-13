<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$custs = App\Models\CRM\Customer::where('name', 'like', '%nanang%')->get();
foreach($custs as $c) {
    echo "CRM Name: {$c->name}, Code: {$c->code}, Phone: {$c->phone}, UserID: {$c->user_id}\n";
    if ($c->user_id) {
        $u = App\Models\User::find($c->user_id);
        if ($u) {
            echo " -> User Name: {$u->name}, Code: {$u->customer_code}, Username: {$u->username}, PPPoE: {$u->pppoe_username}, Whatsapp: {$u->whatsapp}\n";
        }
    }
}
?>
