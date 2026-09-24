<?php
$payload = ['script' => 'let a = 1;'];
$put = \Illuminate\Support\Facades\Http::withBasicAuth('admin', 'rahasia123')
    ->put("http://192.168.150.15:7557/provisions/dsBilling_Setup_WAN", $payload);
echo "PUT status: " . $put->status() . "\n";
echo "PUT body: " . $put->body() . "\n";
