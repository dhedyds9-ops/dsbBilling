<?php
$file = 'resources/views/livewire/billing/payment/index.blade.php';
$content = file_get_contents($file);

$content = str_replace('$payment->payment_date', '$payment->paid_at', $content);
$content = str_replace('$payment->payment_method', '$payment->method', $content);

file_put_contents($file, $content);
echo "Fixed payment variables in blade\n";
