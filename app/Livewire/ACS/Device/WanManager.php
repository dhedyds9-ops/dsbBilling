<?php

namespace App\Livewire\ACS\Device;

use Livewire\Component;
use App\Models\ACS\ACSDevice;
use App\Services\Adapters\Monitoring\GenieACSDriver;

class WanManager extends Component
{
    public ACSDevice $device;
    public $showWanModal = false;
    public $wanConnections = [];
    public $zteBindings = [];
    public $isLoading = false;
    public $rawParams = [];
    public string $detectedVendor = 'generic';

    // Form Edit & Create State
    public $isEditing = false;
    public $isCreating = false;
    public $editFullPath = '';
    public $formName = '';

    // Modes:
    // 'pppoe_route' => PPPoE Dial-up Route (ZTE/Huawei: IP_Routed, FiberHome: PPPoE_Routed, Slot: WANPPPConnection)
    // 'dhcp_route'  => IPoE / DHCP Route (IP_Routed, Slot: WANIPConnection)
    // 'bridge_ppp'  => WAN Bridge on WAN PPP Slot (PPPoE_Bridged, Slot: WANPPPConnection)
    // 'bridge_ip'   => WAN Bridge on WAN IP Slot (IP_Bridged, Slot: WANIPConnection)
    public $formMode = 'pppoe_route';
    public $formType = 'PPPoE';
    public $formServiceList = 'INTERNET';
    public $customServiceList = '';
    public $formVlan = '';
    public $formNat = true;
    public $formUsername = '';
    public $formPassword = '';

    // Port Binding:
    public $bindLan = [1, 2, 3, 4];
    public $bindWlan = [1, 5];
    public $useManualBinding = false;
    public $customBindString = '';
    public bool $isNocLayout = false;

    public function mount(ACSDevice $device)
    {
        $this->device = $device;
        $this->isNocLayout = request()->routeIs('noc.*') || request()->is('noc/*');
    }

    public function openModal()
    {
        $this->showWanModal = true;
        $this->isLoading = true;
    }

    public function detectVendor(array $params = []): string
    {
        $candidates = [
            $this->device->vendor?->name ?? '',
            $this->device->manufacturer ?? '',
            $this->device->product_class ?? '',
            $this->device->model ?? '',
            $this->device->serial_number ?? '',
            $this->device->onu?->vendor?->name ?? '',
            $this->device->onu?->serial_number ?? '',
            $params['DeviceID']['Manufacturer']['_value'] ?? '',
            $params['_deviceId']['_Manufacturer'] ?? '',
            $params['InternetGatewayDevice']['DeviceInfo']['Manufacturer']['_value'] ?? '',
            $params['Device']['DeviceInfo']['Manufacturer']['_value'] ?? '',
        ];

        $combined = strtolower(implode(' ', array_filter($candidates)));

        if (str_contains($combined, 'huawei') || str_contains($combined, 'hwtc') || str_contains($combined, 'eg8') || str_contains($combined, 'hg8') || str_contains($combined, 'ws70')) {
            return 'huawei';
        }
        if (str_contains($combined, 'zte') || str_contains($combined, 'zteg') || str_contains($combined, 'f609') || str_contains($combined, 'f670') || str_contains($combined, 'f660')) {
            return 'zte';
        }
        if (str_contains($combined, 'fiberhome') || str_contains($combined, 'fhtt') || str_contains($combined, 'an5506') || str_contains($combined, 'hg6')) {
            return 'fiberhome';
        }
        if (str_contains($combined, 'ecomtech')) {
            return 'ecomtech';
        }
        if (str_contains($combined, 'alcatel') || str_contains($combined, 'nokia') || str_contains($combined, 'alcl')) {
            return 'nokia';
        }

        return 'generic';
    }

    public function pathExistsInTree(array $tree, string $path): bool
    {
        $keys = explode('.', $path);
        $curr = $tree;
        foreach ($keys as $k) {
            if (!is_array($curr) || !array_key_exists($k, $curr)) {
                return false;
            }
            $curr = $curr[$k];
        }
        return true;
    }

    public function loadWans()
    {
        try {
            $driver = new GenieACSDriver();
            $params = $driver->getDeviceParameters($this->device->uuid);
            $this->rawParams = $params;

            $this->detectedVendor = $this->detectVendor($params);

            // Pre-process ZTE Port Bindings
            $this->zteBindings = [];
            if (isset($params['InternetGatewayDevice']['X_ZTE-COM_PortBinding']) && is_array($params['InternetGatewayDevice']['X_ZTE-COM_PortBinding'])) {
                foreach ($params['InternetGatewayDevice']['X_ZTE-COM_PortBinding'] as $key => $bindingNode) {
                    if ($key === '_object' || !is_array($bindingNode)) continue;
                    $wanIf = $bindingNode['WANInterface']['_value'] ?? '';
                    $lanIf = $bindingNode['LANInterface']['_value'] ?? '';
                    if ($wanIf && $lanIf) {
                        $this->zteBindings[$wanIf] = $lanIf;
                    }
                }
            }

            $this->wanConnections = $this->parseWanConnections($params, $this->detectedVendor);
        } catch (\Throwable $e) {
            session()->flash('error', 'Gagal memuat WAN: ' . $e->getMessage());
        }
        $this->isLoading = false;
    }

    private function parseWanConnections($params, $vendor)
    {
        $wans = [];
        $wanDevices = $params['InternetGatewayDevice']['WANDevice'] ?? [];
        if (!is_array($wanDevices)) return $wans;

        foreach ($wanDevices as $wdIndex => $wdNode) {
            if (!is_numeric($wdIndex) || !is_array($wdNode)) continue;
            
            $connDevices = $wdNode['WANConnectionDevice'] ?? [];
            if (!is_array($connDevices)) continue;

            foreach ($connDevices as $cdIndex => $cdNode) {
                if (!is_numeric($cdIndex) || !is_array($cdNode)) continue;

                $parentVlan = $cdNode['X_FH_WANGponLinkConfig']['VLANIDMark']['_value'] ??
                              $cdNode['X_FH_WANGponLinkConfig']['VLANID']['_value'] ??
                              $cdNode['WANEthernetLinkConfig']['X_HW_VLAN']['_value'] ??
                              $cdNode['WANEthernetLinkConfig']['VLANIDMark']['_value'] ??
                              $cdNode['VLANID']['_value'] ??
                              null;

                // Cek PPPoE
                $ppp = $cdNode['WANPPPConnection'] ?? [];
                if (is_array($ppp)) {
                    foreach ($ppp as $pIndex => $pNode) {
                        if (!is_numeric($pIndex) || !is_array($pNode)) continue;
                        $fullPath = "InternetGatewayDevice.WANDevice.$wdIndex.WANConnectionDevice.$cdIndex.WANPPPConnection.$pIndex";
                        $wans[] = $this->extractWanData($pNode, 'PPPoE', "$wdIndex.$cdIndex.$pIndex", $fullPath, $parentVlan);
                    }
                }

                // Cek IPoE/Static/Bridge
                $ip = $cdNode['WANIPConnection'] ?? [];
                if (is_array($ip)) {
                    foreach ($ip as $iIndex => $iNode) {
                        if (!is_numeric($iIndex) || !is_array($iNode)) continue;
                        $fullPath = "InternetGatewayDevice.WANDevice.$wdIndex.WANConnectionDevice.$cdIndex.WANIPConnection.$iIndex";
                        $wans[] = $this->extractWanData($iNode, 'IPoE', "$wdIndex.$cdIndex.$iIndex", $fullPath, $parentVlan);
                    }
                }
            }
        }
        return $wans;
    }

    private function extractWanData($node, $type, $pathIndex, $fullPath = '', $parentVlan = null)
    {
        $name = $node['Name']['_value'] ?? "WAN $pathIndex";
        $nat = $node['NATEnabled']['_value'] ?? false;
        $connectionType = $node['ConnectionType']['_value'] ?? null;
        
        $vlan = $node['VLANID']['_value'] ??
                $node['X_BROADCOM_COM_VLANID']['_value'] ?? 
                $node['X_ZTE-COM_VLANID']['_value'] ?? 
                $node['X_HW_VLAN']['_value'] ?? 
                $parentVlan;
                
        $serviceList = $node['X_FH_ServiceList']['_value'] ?? 
                       $node['X_ZTE-COM_ServiceList']['_value'] ?? 
                       $node['X_HW_ServiceList']['_value'] ?? 
                       $node['X_HW_SERVICELIST']['_value'] ?? 
                       '-';
                       
        $portBind = '';
        if (isset($node['X_FH_LanInterface']['_value'])) {
            $portBind = $node['X_FH_LanInterface']['_value'];
        } elseif (isset($node['X_ZTE-COM_PortBind']['_value'])) {
            $portBind = $node['X_ZTE-COM_PortBind']['_value'];
        } elseif ($fullPath && isset($this->zteBindings[$fullPath])) {
            $portBind = $this->zteBindings[$fullPath];
        } elseif (isset($node['X_HW_LANBinding']['_value'])) {
            $portBind = $node['X_HW_LANBinding']['_value'];
        } elseif (isset($node['X_HW_LANBIND']) && is_array($node['X_HW_LANBIND'])) {
            $binds = [];
            foreach ($node['X_HW_LANBIND'] as $k => $v) {
                if ($k === '_object' || $k === '_writable' || $k === '_timestamp') continue;
                if (isset($v['_value']) && ($v['_value'] == 1 || strtolower($v['_value']) == 'true')) {
                    $binds[] = $k;
                }
            }
            $portBind = implode(', ', $binds);
        } elseif (isset($node['X_HW_LANBIND']['_value'])) {
            $portBind = $node['X_HW_LANBIND']['_value'];
        }
                
        $username = $node['Username']['_value'] ?? null;

        // Friendly label
        $modeLabel = $type;
        if ($connectionType === 'PPPoE_Bridged') {
            $modeLabel = 'Bridge (PPP)';
        } elseif ($connectionType === 'IP_Bridged') {
            $modeLabel = 'Bridge (IP)';
        } elseif ($connectionType === 'PPPoE_Routed') {
            $modeLabel = 'PPPoE Route';
        } elseif ($connectionType === 'IP_Routed') {
            $modeLabel = $type === 'PPPoE' ? 'PPPoE Route' : 'IP Route (DHCP)';
        }

        return [
            'id' => $pathIndex,
            'name' => $name,
            'type' => $type,
            'mode_label' => $modeLabel,
            'connection_type' => $connectionType,
            'nat' => $nat,
            'vlan' => $vlan,
            'username' => $username,
            'service_list' => $serviceList,
            'port_bind' => $portBind,
            'fullPath' => $fullPath,
        ];
    }

    public function generateBindString(): string
    {
        if ($this->useManualBinding && !empty(trim($this->customBindString))) {
            return trim($this->customBindString);
        }

        $paths = [];
        $lans = (array) $this->bindLan;
        sort($lans);
        foreach ($lans as $l) {
            if ($l) $paths[] = "InternetGatewayDevice.LANDevice.1.LANEthernetInterfaceConfig.{$l}";
        }

        $wlans = (array) $this->bindWlan;
        sort($wlans);
        foreach ($wlans as $w) {
            if ($w) $paths[] = "InternetGatewayDevice.LANDevice.1.WLANConfiguration.{$w}";
        }

        return implode(',', $paths);
    }

    public function syncBindStringToInputs()
    {
        $this->customBindString = $this->generateBindString();
    }

    public function applyPresetFiberhome()
    {
        $this->bindLan = [1];
        $this->bindWlan = [1, 5];
        $this->useManualBinding = false;
        $this->formServiceList = 'TR069,INTERNET';
        $this->customBindString = "InternetGatewayDevice.LANDevice.1.LANEthernetInterfaceConfig.1,InternetGatewayDevice.LANDevice.1.WLANConfiguration.1,InternetGatewayDevice.LANDevice.1.WLANConfiguration.5";
    }

    public function applyPresetAllPorts()
    {
        $this->bindLan = [1, 2, 3, 4];
        $this->bindWlan = [1, 5, 6];
        $this->useManualBinding = false;
        $this->customBindString = $this->generateBindString();
    }

    public function updatedFormMode($value)
    {
        $vendor = $this->detectedVendor;
        $isFiberHome = str_contains($vendor, 'fiberhome');

        if ($value === 'pppoe_route') {
            $this->formType = 'PPPoE';
            $this->formNat = true;
            $this->formServiceList = $isFiberHome ? 'TR069,INTERNET' : 'INTERNET';
        } elseif ($value === 'dhcp_route') {
            $this->formType = 'IP_Routed';
            $this->formNat = true;
            $this->formServiceList = 'INTERNET';
        } elseif ($value === 'bridge_ppp') {
            $this->formType = 'PPPoE_Bridged';
            $this->formNat = false;
            $this->formServiceList = 'INTERNET';
        } elseif ($value === 'bridge_ip') {
            $this->formType = 'IP_Bridged';
            $this->formNat = false;
            $this->formServiceList = 'INTERNET';
        }
    }

    public function render()
    {
        return view('livewire.acs.device.wan-manager');
    }

    public function editWan($fullPath)
    {
        $wan = collect($this->wanConnections)->firstWhere('fullPath', $fullPath);
        if (!$wan) {
            session()->flash('error', 'Koneksi WAN tidak ditemukan.');
            return;
        }

        $this->isEditing = true;
        $this->isCreating = false;
        $this->editFullPath = $fullPath;
        $this->formName = $wan['name'];
        $this->formType = $wan['type'];
        $this->formVlan = $wan['vlan'] !== null ? (string)$wan['vlan'] : '';
        $this->formNat = (bool) $wan['nat'];
        $this->formUsername = $wan['username'] ?? '';
        $this->formPassword = ''; // Password selalu kosong demi keamanan
        
        $svc = $wan['service_list'] ?? '';
        if (in_array($svc, ['INTERNET', 'TR069,INTERNET', 'OTHER', 'TR069'])) {
            $this->formServiceList = $svc;
            $this->customServiceList = '';
        } else {
            $this->formServiceList = 'CUSTOM';
            $this->customServiceList = $svc !== '-' ? $svc : '';
        }

        $this->customBindString = $wan['port_bind'] ?? '';
        $this->useManualBinding = !empty($this->customBindString);
    }

    public function cancelEdit()
    {
        $this->isEditing = false;
        $this->editFullPath = '';
    }

    public function createWan()
    {
        $this->isEditing = false;
        $this->isCreating = true;
        
        $vendor = $this->detectedVendor;
        $isFiberHome = str_contains($vendor, 'fiberhome');

        $this->formMode = 'pppoe_route';
        $this->formType = 'PPPoE';
        $this->formVlan = '';
        $this->formUsername = '';
        $this->formPassword = '';
        $this->formNat = true;
        $this->formServiceList = $isFiberHome ? 'TR069,INTERNET' : 'INTERNET';
        $this->customServiceList = '';

        if ($isFiberHome) {
            $this->applyPresetFiberhome();
        } else {
            $this->bindLan = [1, 2, 3, 4];
            $this->bindWlan = [1, 5, 6];
            $this->useManualBinding = false;
            $this->customBindString = $this->generateBindString();
        }
    }

    public function cancelCreate()
    {
        $this->isCreating = false;
    }

    public function saveNewWan()
    {
        $this->validate([
            'formMode' => 'required|in:pppoe_route,dhcp_route,bridge_ppp,bridge_ip',
        ]);

        if ($this->formMode === 'pppoe_route') {
            $this->validate([
                'formUsername' => 'required|string',
            ]);
        }

        try {
            $driver = new \App\Services\Adapters\Monitoring\GenieACSDriver();
            $vendor = $this->detectedVendor;
            $isFiberHome = str_contains($vendor, 'fiberhome');
            $isZte = str_contains($vendor, 'zte');
            $isHuawei = str_contains($vendor, 'huawei') || str_contains($vendor, 'ecomtech');

            $vlanId = (int) $this->formVlan;
            $serviceList = $this->formServiceList === 'CUSTOM' ? $this->customServiceList : $this->formServiceList;
            if (empty($serviceList)) {
                $serviceList = $isFiberHome ? 'TR069,INTERNET' : 'INTERNET';
            }

            // Determine slot object and connection mode
            if ($this->formMode === 'pppoe_route') {
                $connObj = "WANPPPConnection";
                $connType = $isFiberHome ? "PPPoE_Routed" : "IP_Routed";
                $isPppoeRoute = true;
                $natEnabled = $this->formNat ? 'true' : 'false';
            } elseif ($this->formMode === 'dhcp_route') {
                $connObj = "WANIPConnection";
                $connType = "IP_Routed";
                $isPppoeRoute = false;
                $natEnabled = $this->formNat ? 'true' : 'false';
            } elseif ($this->formMode === 'bridge_ppp') {
                $connObj = "WANPPPConnection";
                $connType = "PPPoE_Bridged";
                $isPppoeRoute = false;
                $natEnabled = 'false';
            } elseif ($this->formMode === 'bridge_ip') {
                $connObj = "WANIPConnection";
                $connType = "IP_Bridged";
                $isPppoeRoute = false;
                $natEnabled = 'false';
            }

            $username = addslashes($this->formUsername);
            $password = addslashes($this->formPassword);
            $bindString = addslashes($this->generateBindString());

            $script = <<<JS
const now = Date.now();
// Find next available WANConnectionDevice instance or an empty slot
let wanConns = declare("InternetGatewayDevice.WANDevice.1.WANConnectionDevice.*", {path: 1});
let basePath = null;
let maxIdx = 0;

for (let p of wanConns) {
    let idx = parseInt(p.path.split(".").pop());
    if (idx > maxIdx) maxIdx = idx;
    
    // Check if slot is empty (no PPP and no IP connection)
    let ppps = declare(p.path + ".WANPPPConnection.*", {path: 1});
    let ips = declare(p.path + ".WANIPConnection.*", {path: 1});
    
    let hasPpp = false;
    let hasIp = false;
    for (let x of ppps) hasPpp = true;
    for (let x of ips) hasIp = true;
    
    if (!hasPpp && !hasIp && !basePath) {
        basePath = p.path;
    }
}

// If no empty slot is found, create a new one
if (!basePath) {
    basePath = "InternetGatewayDevice.WANDevice.1.WANConnectionDevice." + (maxIdx + 1);
    declare(basePath, {path: 1}, {path: 1});
}

// Vendor specific VLAN
if ({$vlanId} > 0) {
    if ("{$vendor}".includes("fiberhome")) {
        declare(basePath + ".X_FH_WANGponLinkConfig.VLANIDMark", {value: now}, {value: {$vlanId}});
        declare(basePath + ".X_FH_WANGponLinkConfig.VLANID", {value: now}, {value: {$vlanId}});
    } else if ("{$vendor}".includes("huawei") || "{$vendor}".includes("ecomtech")) {
        declare(basePath + ".WANEthernetLinkConfig.X_HW_VLAN", {value: now}, {value: {$vlanId}});
        declare(pppPath + ".X_HW_VLAN", {value: now}, {value: {$vlanId}});
    } else if ("{$vendor}".includes("zte")) {
        declare(pppPath + ".X_ZTE-COM_VLANID", {value: now}, {value: {$vlanId}});
        declare(pppPath + ".X_ZTE-COM_VLANEnable", {value: now}, {value: 1});
    } else {
        declare(basePath + ".WANEthernetLinkConfig.X_HW_VLAN", {value: now}, {value: {$vlanId}});
        declare(basePath + ".VLANID", {value: now}, {value: {$vlanId}});
    }
}

// Add Connection Object
let pppPath = basePath + ".{$connObj}.1";
declare(pppPath, {path: 1}, {path: 1});

declare(pppPath + ".ConnectionType", {value: now}, {value: "{$connType}"});
declare(pppPath + ".NATEnabled", {value: now}, {value: {$natEnabled}});
declare(pppPath + ".Enable", {value: now}, {value: true});
declare(pppPath + ".Name", {value: now}, {value: "dsBilling_{$connType}_{$vlanId}"});

if ("{$connObj}" === "WANIPConnection" && "{$connType}" === "IP_Routed") {
    declare(pppPath + ".AddressingType", {value: now}, {value: "DHCP"});
}

// Service List
if ("{$vendor}".includes("fiberhome")) {
    declare(pppPath + ".X_FH_ServiceList", {value: now}, {value: "{$serviceList}"});
} else if ("{$vendor}".includes("huawei") || "{$vendor}".includes("ecomtech")) {
    declare(pppPath + ".X_HW_SERVICELIST", {value: now}, {value: "{$serviceList}"});
    if ({$vlanId} > 0) {
        declare(pppPath + ".X_HW_VLAN", {value: now}, {value: {$vlanId}});
    }
} else if ("{$vendor}".includes("zte")) {
    declare(pppPath + ".X_ZTE-COM_ServiceList", {value: now}, {value: "{$serviceList}"});
    if ({$vlanId} > 0) {
        declare(pppPath + ".X_ZTE-COM_VLANID", {value: now}, {value: {$vlanId}});
        declare(pppPath + ".X_ZTE-COM_VLANEnable", {value: now}, {value: 1});
    }
}

// Port Binding
if ("{$bindString}" !== "") {
    if ("{$vendor}".includes("fiberhome")) {
        declare(pppPath + ".X_FH_LanInterface", {value: now}, {value: "{$bindString}"});
    } else if ("{$vendor}".includes("huawei") || "{$vendor}".includes("ecomtech")) {
        declare(pppPath + ".X_HW_LANBIND", {value: now}, {value: "{$bindString}"});
        declare(pppPath + ".X_HW_LANBinding", {value: now}, {value: "{$bindString}"});
    } else if ("{$vendor}".includes("zte")) {
        let bindings = declare("InternetGatewayDevice.X_ZTE-COM_PortBinding.*", {path: 1});
        let bindIdx = 1;
        for (let b of bindings) {
            let bIdx = parseInt(b.path.split(".").pop());
            if (bIdx >= bindIdx) bindIdx = bIdx + 1;
        }
        let bindPath = "InternetGatewayDevice.X_ZTE-COM_PortBinding." + bindIdx;
        declare(bindPath, {path: 1}, {path: 1});
        declare(bindPath + ".WANInterface", {value: now}, {value: pppPath});
        declare(bindPath + ".LANInterface", {value: now}, {value: "{$bindString}"});
        declare(pppPath + ".X_ZTE-COM_PortBind", {value: now}, {value: "{$bindString}"});
    }
}

// Credentials (only if PPPoE Route)
if ("{$formMode}" === "pppoe_route") {
    declare(pppPath + ".Username", {value: now}, {value: "{$username}"});
    declare(pppPath + ".Password", {value: now}, {value: "{$password}"});
}
JS;

            $provName = "temp_add_wan_" . $this->device->uuid;
            $driver->upsertProvision($provName, $script);
            $driver->addProvisionTask($this->device->uuid, $provName);

            session()->flash('success', 'Perintah pembuatan WAN (' . $connType . ') berhasil dikirim ke GenieACS. Tunggu modem tersinkronisasi.');
            $this->isCreating = false;
            $this->loadWans();
        } catch (\Throwable $e) {
            session()->flash('error', 'Gagal membuat WAN: ' . $e->getMessage());
        }
    }

    public function saveWan()
    {
        if (!$this->editFullPath) return;

        $parameters = [];
        $pathParts = explode('.', $this->editFullPath);
        array_pop($pathParts); array_pop($pathParts);
        $parentPath = implode('.', $pathParts);

        $vendor = $this->detectedVendor;

        // Username & Password for PPPoE
        if ($this->formType === 'PPPoE') {
            if (!empty($this->formUsername)) {
                $parameters["{$this->editFullPath}.Username"] = $this->formUsername;
            }
            if (!empty($this->formPassword)) {
                $parameters["{$this->editFullPath}.Password"] = $this->formPassword;
            }
        }

        // NAT
        $parameters["{$this->editFullPath}.NATEnabled"] = (bool) $this->formNat;

        // Service List
        $serviceVal = $this->formServiceList === 'CUSTOM' ? $this->customServiceList : $this->formServiceList;
        if (!empty($serviceVal)) {
            if (str_contains($vendor, 'fiberhome')) {
                $parameters["{$this->editFullPath}.X_FH_ServiceList"] = $serviceVal;
            } elseif (str_contains($vendor, 'zte')) {
                $parameters["{$this->editFullPath}.X_ZTE-COM_ServiceList"] = $serviceVal;
            } elseif (str_contains($vendor, 'huawei') || str_contains($vendor, 'ecomtech')) {
                $parameters["{$this->editFullPath}.X_HW_SERVICELIST"] = $serviceVal;
            }
        }

        // Port Binding
        $bindString = $this->generateBindString();
        if (!empty($bindString)) {
            if (str_contains($vendor, 'fiberhome')) {
                $parameters["{$this->editFullPath}.X_FH_LanInterface"] = $bindString;
            } elseif (str_contains($vendor, 'huawei') || str_contains($vendor, 'ecomtech')) {
                $parameters["{$this->editFullPath}.X_HW_LANBIND"] = $bindString;
                $parameters["{$this->editFullPath}.X_HW_LANBinding"] = $bindString;
            } elseif (str_contains($vendor, 'zte')) {
                $parameters["{$this->editFullPath}.X_ZTE-COM_PortBind"] = $bindString;
            }
        }

        // VLAN Handling
        if ($this->formVlan !== '') {
            $vlanId = (int) $this->formVlan;

            if ($vendor === 'huawei') {
                // Priority: check existing parameter in rawParams
                if ($this->pathExistsInTree($this->rawParams, "{$parentPath}.WANEthernetLinkConfig.X_HW_VLAN")) {
                    $parameters["{$parentPath}.WANEthernetLinkConfig.X_HW_VLAN"] = $vlanId;
                } elseif ($this->pathExistsInTree($this->rawParams, "{$this->editFullPath}.X_HW_VLAN")) {
                    $parameters["{$this->editFullPath}.X_HW_VLAN"] = $vlanId;
                } else {
                    // Standard Huawei WAN VLAN path
                    $parameters["{$parentPath}.WANEthernetLinkConfig.X_HW_VLAN"] = $vlanId;
                }
            } elseif ($vendor === 'zte') {
                if ($this->pathExistsInTree($this->rawParams, "{$this->editFullPath}.X_ZTE-COM_VLANID")) {
                    $parameters["{$this->editFullPath}.X_ZTE-COM_VLANID"] = $vlanId;
                    $parameters["{$this->editFullPath}.X_ZTE-COM_VLANEnable"] = 1;
                } elseif ($this->pathExistsInTree($this->rawParams, "{$parentPath}.X_ZTE-COM_VLANID")) {
                    $parameters["{$parentPath}.X_ZTE-COM_VLANID"] = $vlanId;
                    $parameters["{$parentPath}.X_ZTE-COM_VLANEnable"] = 1;
                } else {
                    $parameters["{$this->editFullPath}.X_ZTE-COM_VLANID"] = $vlanId;
                    $parameters["{$this->editFullPath}.X_ZTE-COM_VLANEnable"] = 1;
                }
            } elseif ($vendor === 'fiberhome') {
                $parameters["{$parentPath}.X_FH_WANGponLinkConfig.VLANIDMark"] = $vlanId;
                $parameters["{$parentPath}.X_FH_WANGponLinkConfig.VLANID"] = $vlanId;
            } else {
                // Generic: check rawParams first
                if ($this->pathExistsInTree($this->rawParams, "{$parentPath}.WANEthernetLinkConfig.X_HW_VLAN")) {
                    $parameters["{$parentPath}.WANEthernetLinkConfig.X_HW_VLAN"] = $vlanId;
                } elseif ($this->pathExistsInTree($this->rawParams, "{$this->editFullPath}.X_ZTE-COM_VLANID")) {
                    $parameters["{$this->editFullPath}.X_ZTE-COM_VLANID"] = $vlanId;
                    $parameters["{$this->editFullPath}.X_ZTE-COM_VLANEnable"] = 1;
                } elseif ($this->pathExistsInTree($this->rawParams, "{$parentPath}.X_FH_WANGponLinkConfig.VLANIDMark")) {
                    $parameters["{$parentPath}.X_FH_WANGponLinkConfig.VLANIDMark"] = $vlanId;
                } elseif ($this->pathExistsInTree($this->rawParams, "{$this->editFullPath}.VLANID")) {
                    $parameters["{$this->editFullPath}.VLANID"] = $vlanId;
                } else {
                    $parameters["{$parentPath}.WANEthernetLinkConfig.X_HW_VLAN"] = $vlanId;
                }
            }
        }

        try {
            $driver = new GenieACSDriver();
            $success = $driver->setParameterValues($this->device->uuid, $parameters);

            if ($success) {
                // Optimistically update local array so the table shows the new VLAN immediately
                foreach ($this->wanConnections as &$w) {
                    if ($w['fullPath'] === $this->editFullPath) {
                        $w['vlan'] = $this->formVlan !== '' ? (int) $this->formVlan : null;
                        $w['nat'] = (bool) $this->formNat;
                        $w['service_list'] = $serviceVal;
                        if (!empty($bindString)) $w['port_bind'] = $bindString;
                        if (!empty($this->formUsername)) $w['username'] = $this->formUsername;
                    }
                }
                unset($w);

                session()->flash('success', "Task pembaruan WAN (VLAN: {$this->formVlan}) berhasil dikirim ke perangkat ({$this->detectedVendor}).");
                $this->isEditing = false;
            } else {
                session()->flash('error', 'Gagal mengirim task pembaruan WAN ke GenieACS.');
            }
        } catch (\Throwable $e) {
            session()->flash('error', 'Error: ' . $e->getMessage());
        }
    }
}
