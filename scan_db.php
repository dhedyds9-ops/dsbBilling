<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

function checkModel($model, $path) {
    if (!$model) return;
    foreach ($model->getAttributes() as $k => $v) {
        if (is_string($v) && !mb_check_encoding($v, 'UTF-8')) {
            echo "BAD UTF-8 IN $path -> $k\n";
        }
    }
}

$c = \App\Models\CRM\Customer::find(34);
checkModel($c, "Customer 34");

foreach ($c->customerServices as $i => $s) {
    checkModel($s, "Service $i");
    if ($s->serviceProfile) checkModel($s->serviceProfile, "Service $i Profile");
    if ($s->acsDevice) checkModel($s->acsDevice, "Service $i ACS");
    if ($s->onu) {
        checkModel($s->onu, "Service $i ONU");
        if ($s->onu->odp) checkModel($s->onu->odp, "Service $i ODP");
        if ($s->onu->olt) checkModel($s->onu->olt, "Service $i OLT");
    }
}
foreach ($c->invoices as $i => $inv) checkModel($inv, "Invoice $i");
foreach ($c->payments as $i => $pay) checkModel($pay, "Payment $i");
foreach ($c->tickets as $i => $t) checkModel($t, "Ticket $i");

echo "Done scanning DB.\n";
?>
