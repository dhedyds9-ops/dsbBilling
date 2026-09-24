<?php
$put = \Illuminate\Support\Facades\Http::withBasicAuth('admin', 'rahasia123')
    ->withBody('let a = 1;', 'text/plain')
    ->put("http://192.168.150.15:7557/provisions/dsBilling_Setup_WAN");
echo "PUT status: " . $put->status() . "\n";
echo "PUT body: " . $put->body() . "\n";
