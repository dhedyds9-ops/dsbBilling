<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$payments = \App\Models\Payment\Payment::with('invoice')->get();
$hasInvoice = 0;
$noInvoice = 0;
foreach($payments as $p) {
    if ($p->invoice) $hasInvoice++;
    else $noInvoice++;
}
echo "Has invoice: $hasInvoice\nNo invoice: $noInvoice\n";
