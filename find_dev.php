<?php
$devices = \App\Models\ACS\ACSDevice::all();
foreach($devices as $d) {
    echo $d->serial_number . " (ID: " . $d->id . ")\n";
}
