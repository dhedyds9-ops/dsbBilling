<?php
$driver = app(\App\Services\Adapters\Monitoring\GenieACSDriver::class);
try {
    $existing = \Illuminate\Support\Facades\Http::withBasicAuth('admin', 'rahasia123')
        ->get("http://192.168.150.15:7557/provisions/dsBilling_Setup_WAN");
    echo "GET existing status: " . $existing->status() . "\n";
    echo "GET existing body: " . $existing->body() . "\n";
    
    $payload = ['_id' => 'dsBilling_Setup_WAN', 'script' => 'return true;'];
    $post = \Illuminate\Support\Facades\Http::withBasicAuth('admin', 'rahasia123')
        ->post("http://192.168.150.15:7557/provisions/dsBilling_Setup_WAN", $payload);
    echo "POST status: " . $post->status() . "\n";
    echo "POST body: " . $post->body() . "\n";
} catch (\Exception $e) {
    echo "Exception: " . $e->getMessage() . "\n";
}
