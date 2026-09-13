<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$p = \App\Models\Payment\Payment::find(26);
if ($p) {
    $i = \App\Models\Billing\Invoice::where('customer_id', $p->customer_id)->first();
    if ($i) {
        if (!$p->invoices->contains($i->id)) {
            $p->invoices()->attach($i->id);
            echo 'Attached to Invoice ' . $i->id;
        } else {
            echo 'Already attached';
        }
    } else {
        echo 'No invoice found for customer';
    }
}
