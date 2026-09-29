<?php
$keys = [
    'iso.3.6.1.4.1.17409.2.8.4.3.1.11.4718596.0',
    'SNMPv2-SMI::enterprises.17409.2.8.4.3.1.11.4743171.0',
    '4743171.0'
];

foreach ($keys as $fullIndex) {
    if (preg_match('/(\d+)\.\d+$/', $fullIndex, $m)) {
        $ifIndex = (int)$m[1];
        $pPort = (($ifIndex >> 12) & 0xFF) - 128;
        $oId = $ifIndex & 0xFFF;
        echo "Key: $fullIndex => ifIndex: $ifIndex, pPort: $pPort, oId: $oId\n";
    }
}
