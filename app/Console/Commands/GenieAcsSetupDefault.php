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
    "InternetGatewayDevice.WANDevice.1.WANConnectionDevice.1.WANPPPConnection.1.Username",
    "InternetGatewayDevice.WANDevice.1.WANConnectionDevice.2.WANPPPConnection.1.Username",
    "InternetGatewayDevice.WANDevice.1.WANConnectionDevice.3.WANPPPConnection.1.Username",
    "InternetGatewayDevice.WANDevice.1.WANConnectionDevice.4.WANPPPConnection.1.Username",
    "InternetGatewayDevice.WANDevice.*.WANConnectionDevice.*.WANPPPConnection.*.Username",
    "Device.PPP.Interface.1.Username",
    "Device.PPP.Interface.2.Username",
    "Device.PPP.Interface.*.Username",
    "Device.IP.Interface.*.X_WANPPPConnection.Username"
];
for (let p of paths) {
    let d = declare(p, {value: 1});
    for (let item of d) {
        if (item.value && item.value[0] && item.value[0].trim() !== "") {
            return {writable: true, value: [item.value[0].trim(), "xsd:string"]};
        }
    }
}
return null;',
            'pppoePassword' => '
let paths = [
    "InternetGatewayDevice.WANDevice.1.WANConnectionDevice.1.WANPPPConnection.1.Password",
    "InternetGatewayDevice.WANDevice.1.WANConnectionDevice.2.WANPPPConnection.1.Password",
    "InternetGatewayDevice.WANDevice.1.WANConnectionDevice.3.WANPPPConnection.1.Password",
    "InternetGatewayDevice.WANDevice.1.WANConnectionDevice.4.WANPPPConnection.1.Password",
    "InternetGatewayDevice.WANDevice.*.WANConnectionDevice.*.WANPPPConnection.*.Password",
    "Device.PPP.Interface.1.Password",
    "Device.PPP.Interface.2.Password",
    "Device.PPP.Interface.*.Password"
];
for (let p of paths) {
    let d = declare(p, {value: 1});
    for (let item of d) {
        if (item.value && item.value[0] && item.value[0].trim() !== "") {
            return {writable: true, value: [item.value[0].trim(), "xsd:string"]};
        }
    }
}
return null;',
            'superAdmin' => '
// 1. FiberHome Web SuperAdmin Username
let fhUser = declare("InternetGatewayDevice.DeviceInfo.X_FH_Account.X_FH_WebUserInfo.WebSuperUsername", {value: 1});
if (fhUser.value && fhUser.value[0] && fhUser.value[0].trim() !== "" && fhUser.value[0].trim() !== "%0|F?H@f!berh03e") {
    return {writable: true, value: [fhUser.value[0].trim(), "xsd:string"]};
}

// 2. Huawei Web SuperAdmin Username
let hwUser = declare("InternetGatewayDevice.UserInterface.X_HW_WebUserInfo.2.UserName", {value: 1});
if (hwUser.value && hwUser.value[0] && hwUser.value[0].trim() !== "") {
    return {writable: true, value: [hwUser.value[0].trim(), "xsd:string"]};
}

// 3. ZTE Web SuperAdmin Username
let zteUser = declare("InternetGatewayDevice.UserInterface.X_ZTE-COM_WebUserInfo.AdminName", {value: 1});
if (zteUser.value && zteUser.value[0] && zteUser.value[0].trim() !== "") {
    return {writable: true, value: [zteUser.value[0].trim(), "xsd:string"]};
}

// 4. Default / Standard Admin User
let stdUser = declare("InternetGatewayDevice.UserInterface.X_CT-COM_TeleComAccount.Username", {value: 1});
if (stdUser.value && stdUser.value[0] && stdUser.value[0].trim() !== "") {
    return {writable: true, value: [stdUser.value[0].trim(), "xsd:string"]};
}

return {writable: true, value: ["admin", "xsd:string"]};',
            'superPassword' => '
// 1. FiberHome Web SuperAdmin Password
let fhPass = declare("InternetGatewayDevice.DeviceInfo.X_FH_Account.X_FH_WebUserInfo.WebSuperPassword", {value: 1});
if (fhPass.value && fhPass.value[0] && fhPass.value[0].trim() !== "") {
    return {writable: true, value: [fhPass.value[0].trim(), "xsd:string"]};
}

// 2. Huawei Web SuperAdmin Password
let hwPass = declare("InternetGatewayDevice.UserInterface.X_HW_WebUserInfo.2.Password", {value: 1});
if (hwPass.value && hwPass.value[0] && hwPass.value[0].trim() !== "") {
    return {writable: true, value: [hwPass.value[0].trim(), "xsd:string"]};
}

// 3. ZTE Web SuperAdmin Password
let ztePass = declare("InternetGatewayDevice.UserInterface.X_ZTE-COM_WebUserInfo.AdminPassword", {value: 1});
if (ztePass.value && ztePass.value[0] && ztePass.value[0].trim() !== "") {
    return {writable: true, value: [ztePass.value[0].trim(), "xsd:string"]};
}

// 4. Standard UserInterface Password
let stdPass = declare("InternetGatewayDevice.UserInterface.Password", {value: 1});
if (stdPass.value && stdPass.value[0] && stdPass.value[0].trim() !== "") {
    return {writable: true, value: [stdPass.value[0].trim(), "xsd:string"]};
}

return {writable: true, value: ["admin", "xsd:string"]};',
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
            'IPTR069' => '
// 1. Ambil dari ConnectionRequestURL
let cr = declare("InternetGatewayDevice.ManagementServer.ConnectionRequestURL", {value: 1});
if (!cr.value || !cr.value[0]) {
    cr = declare("Device.ManagementServer.ConnectionRequestURL", {value: 1});
}
if (cr.value && cr.value[0]) {
    let match = cr.value[0].match(/https?:\\/\\/([^\\/:]+)/i);
    if (match && match[1] && match[1] !== "0.0.0.0") {
        return {writable: false, value: [match[1], "xsd:string"]};
    }
}

// 2. Ambil dari WANIPConnection (IP Host / Management TR069)
let paths = [
    "InternetGatewayDevice.WANDevice.*.WANConnectionDevice.*.WANIPConnection.*.ExternalIPAddress",
    "Device.IP.Interface.*.IPv4Address.*.IPAddress"
];
for (let p of paths) {
    let d = declare(p, {value: 1});
    for (let item of d) {
        if (item.value && item.value[0] && item.value[0] !== "0.0.0.0") {
            return {writable: false, value: [item.value[0], "xsd:string"]};
        }
    }
}

// 3. Fallback ke IP inform socket
let devIp = declare("DeviceID.IP", {value: 1});
if (devIp.value && devIp.value[0] && devIp.value[0] !== "0.0.0.0") {
    return {writable: false, value: [devIp.value[0], "xsd:string"]};
}
return null;',
            'gponSN' => '
let paths = [
    "InternetGatewayDevice.DeviceInfo.X_HW_SerialNumber",
    "Device.DeviceInfo.X_HW_SerialNumber",
    "InternetGatewayDevice.DeviceInfo.X_HW_GponSN",
    "InternetGatewayDevice.WANDevice.*.WANPONInterfaceConfig.*.X_HW_SerialNumber",
    "InternetGatewayDevice.WANDevice.*.X_HW_PONInterfaceConfig.SerialNumber"
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
            'getSerialNumber' => '
// 1. Cek parameter khusus Huawei (X_HW_SerialNumber)
let hwPaths = [
    "InternetGatewayDevice.DeviceInfo.X_HW_SerialNumber",
    "Device.DeviceInfo.X_HW_SerialNumber",
    "InternetGatewayDevice.DeviceInfo.X_HW_GponSN"
];
for (let p of hwPaths) {
    let d = declare(p, {value: 1});
    if (d.value && d.value[0]) return {writable: false, value: [d.value[0].trim(), "xsd:string"]};
}

// 2. Cek DeviceID / DeviceInfo standar
let serial = declare("DeviceID.SerialNumber", {value: 1});
let raw = (serial.value && serial.value[0]) ? serial.value[0] : "";
if (!raw) {
    let d2 = declare("InternetGatewayDevice.DeviceInfo.SerialNumber", {value: 1});
    raw = (d2.value && d2.value[0]) ? d2.value[0] : "";
}

// 3. Normalisasi khusus VSOL (bersihkan karakter kontrol/non-printable dan format VSOL)
if (raw) {
    let clean = raw.replace(/[^\x20-\x7E]/g, "").trim();
    // Jika format VSOL diawali 12345 dan OUI B46415
    if (clean.startsWith("12345") && clean.length >= 13) {
        clean = "VSOL" + clean.slice(-8);
    }
    return {writable: false, value: [clean, "xsd:string"]};
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
declare("InternetGatewayDevice.ManagementServer.PeriodicInformEnable", {value: 1}, {value: true});
declare("InternetGatewayDevice.ManagementServer.PeriodicInformInterval", {value: 1}, {value: 300});
declare("Device.ManagementServer.PeriodicInformEnable", {value: 1}, {value: true});
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

        $this->info("Pushing Default Inform Preset to GenieACS...");
        $presetData = [
            'weight' => 10,
            'events' => [
                '0 BOOTSTRAP' => true,
                '1 BOOT' => true,
                '2 PERIODIC' => true,
            ],
            'precondition' => '',
            'configurations' => [
                [
                    'type' => 'provision',
                    'name' => 'dsBilling_Inform',
                ],
            ],
        ];
        if ($driver->upsertPreset('dsBilling_Inform_Preset', $presetData)) {
            $this->info(' - Preset dsBilling_Inform_Preset successfully created & deployed to GenieACS!');
        } else {
            $this->warn(' - Failed to auto-create preset via API. Please ensure it is created in GenieACS UI.');
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
