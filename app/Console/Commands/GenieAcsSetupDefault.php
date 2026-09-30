<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\Adapters\Monitoring\GenieACSDriver;

class GenieAcsSetupDefault extends Command
{
    protected $signature = 'genieacs:setup-default';
    protected $description = 'Setup default Virtual Parameters and Provisions for dsBilling in GenieACS';

    public function handle(GenieACSDriver $driver)
    {
        $this->info('Starting GenieACS Setup for dsBilling...');

        $vps = [
            'RXPower' => '
let paths = [
    "InternetGatewayDevice.WANDevice.*.WANPONInterfaceConfig.*.X_ZTE-COM_RxPower",
    "InternetGatewayDevice.WANDevice.*.WANPONInterfaceConfig.*.X_HW_RxPower",
    "InternetGatewayDevice.WANDevice.*.WANEponInterfaceConfig.*.RxPower",
    "InternetGatewayDevice.WANDevice.*.X_FH_GponInterfaceConfig.RXPower",
    "InternetGatewayDevice.WANDevice.*.X_ZTE-COM_WANPONInterfaceConfig.RXPower",
    "Device.Optical.1.Transceiver.RxPower"
];
for (let p of paths) {
    let d = declare(p, {value: 1});
    for (let item of d) {
        if (item.value && item.value[0]) {
            return {writable: false, value: [item.value[0], "xsd:string"]};
        }
    }
}
return null;',
            'TXPower' => '
let paths = [
    "InternetGatewayDevice.WANDevice.*.WANPONInterfaceConfig.*.X_ZTE-COM_TxPower",
    "InternetGatewayDevice.WANDevice.*.WANPONInterfaceConfig.*.X_HW_TxPower",
    "InternetGatewayDevice.WANDevice.*.WANEponInterfaceConfig.*.TxPower",
    "InternetGatewayDevice.WANDevice.*.X_FH_GponInterfaceConfig.TXPower",
    "InternetGatewayDevice.WANDevice.*.X_ZTE-COM_WANPONInterfaceConfig.TXPower",
    "Device.Optical.1.Transceiver.TxPower"
];
for (let p of paths) {
    let d = declare(p, {value: 1});
    for (let item of d) {
        if (item.value && item.value[0]) {
            return {writable: false, value: [item.value[0], "xsd:string"]};
        }
    }
}
return null;',
            'pppoeUsername' => '
let paths = [
    "InternetGatewayDevice.WANDevice.*.WANConnectionDevice.*.WANPPPConnection.*.Username",
    "Device.WANDevice.*.WANConnectionDevice.*.WANPPPConnection.*.Username"
];
for (let p of paths) {
    let d = declare(p, {value: 1});
    for (let item of d) {
        if (item.value && item.value[0]) {
            return {writable: false, value: [item.value[0], "xsd:string"]};
        }
    }
}
return null;',
            'pppoeMac' => '
let paths = [
    "InternetGatewayDevice.WANDevice.*.WANConnectionDevice.*.WANPPPConnection.*.MACAddress",
    "Device.WANDevice.*.WANConnectionDevice.*.WANPPPConnection.*.MACAddress",
    "InternetGatewayDevice.WANDevice.*.WANConnectionDevice.*.WANIPConnection.*.MACAddress"
];
for (let p of paths) {
    let d = declare(p, {value: 1});
    for (let item of d) {
        if (item.value && item.value[0]) {
            return {writable: false, value: [item.value[0], "xsd:string"]};
        }
    }
}
return null;',
            'pppoeIP' => '
let paths = [
    "InternetGatewayDevice.WANDevice.*.WANConnectionDevice.*.WANPPPConnection.*.ExternalIPAddress",
    "Device.WANDevice.*.WANConnectionDevice.*.WANPPPConnection.*.ExternalIPAddress"
];
for (let p of paths) {
    let d = declare(p, {value: 1});
    for (let item of d) {
        if (item.value && item.value[0] && item.value[0] !== "0.0.0.0") {
            return {writable: false, value: [item.value[0], "xsd:string"]};
        }
    }
}
return null;',
        ];

        $this->info("Pushing Virtual Parameters...");
        foreach ($vps as $name => $script) {
            $this->line(" - Uploading VP: {$name}");
            if (!$driver->upsertVirtualParameter($name, $script)) {
                $this->error("   Failed to upload Virtual Parameter: {$name}");
            }
        }

        $this->info("Pushing Default Inform Provision...");
        $provisionScript = '
// Force device to inform every 300 seconds (5 minutes)
declare("InternetGatewayDevice.ManagementServer.PeriodicInformInterval", {value: 1}, {value: 300});
declare("Device.ManagementServer.PeriodicInformInterval", {value: 1}, {value: 300});

// Force refresh of essential parameters when device informs
declare("InternetGatewayDevice.WANDevice.*", {path: 1, value: 1});
declare("Device.WANDevice.*", {path: 1, value: 1});
declare("Device.Optical.*", {path: 1, value: 1});
';
        if ($driver->upsertProvision('dsBilling_Inform', $provisionScript)) {
            $this->info(' - Provision dsBilling_Inform successfully uploaded.');
        } else {
            $this->error(' - Failed to upload provision dsBilling_Inform.');
        }

        $this->info('');
        $this->info('Setup Complete!');
        $this->line('================================================');
        $this->line('IMPORTANT NEXT STEPS (DO IN GENIEACS UI):');
        $this->line('1. Go to Admin -> Presets in your GenieACS UI.');
        $this->line('2. Click "New" to create a new Preset.');
        $this->line('3. Set Name to: "Inform_Preset" (or any name)');
        $this->line('4. Set Events to: "1 BOOT, 2 PERIODIC" (without quotes)');
        $this->line('5. Under Provision, select "dsBilling_Inform"');
        $this->line('6. Save the Preset.');
        $this->line('================================================');
    }
}
