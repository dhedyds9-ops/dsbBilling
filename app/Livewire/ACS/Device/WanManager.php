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
    public $isLoading = false;

    // Form Edit State
    public $isEditing = false;
    public $editFullPath = '';
    public $formName = '';
    public $formType = '';
    public $formVlan = '';
    public $formNat = false;
    public $formUsername = '';
    public $formPassword = '';

    public function mount(ACSDevice $device)
    {
        $this->device = $device;
    }

    public function openModal()
    {
        $this->showWanModal = true;
        $this->isLoading = true;
    }

    public function loadWans()
    {
        try {
            $driver = new GenieACSDriver();
            $params = $driver->getDeviceParameters($this->device->uuid);

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
            $vendor = strtolower($this->device->vendor ?? 'default');
            
            $this->wanConnections = $this->parseWanConnections($params, $vendor);
        } catch (\Exception $e) {
            session()->flash('error', 'Gagal memuat WAN: ' . $e->getMessage());
        }
        $this->isLoading = false;
    }

    private function parseWanConnections($params, $vendor)
    {
        $wans = [];
        // Navigate TR-098 WANDevice
        $wanDevices = $params['InternetGatewayDevice']['WANDevice'] ?? [];
        if (!is_array($wanDevices)) return $wans;

        foreach ($wanDevices as $wdIndex => $wdNode) {
            if (!is_numeric($wdIndex) || !is_array($wdNode)) continue;
            
            $connDevices = $wdNode['WANConnectionDevice'] ?? [];
            if (!is_array($connDevices)) continue;

            foreach ($connDevices as $cdIndex => $cdNode) {
                if (!is_numeric($cdIndex) || !is_array($cdNode)) continue;

                // Cek PPPoE
                $ppp = $cdNode['WANPPPConnection'] ?? [];
                if (is_array($ppp)) {
                    foreach ($ppp as $pIndex => $pNode) {
                        if (!is_numeric($pIndex) || !is_array($pNode)) continue;
                        $fullPath = "InternetGatewayDevice.WANDevice.$wdIndex.WANConnectionDevice.$cdIndex.WANPPPConnection.$pIndex";
                        $wans[] = $this->extractWanData($pNode, 'PPPoE', "$wdIndex.$cdIndex.$pIndex", $fullPath);
                    }
                }

                // Cek IPoE/Static/Bridge
                $ip = $cdNode['WANIPConnection'] ?? [];
                if (is_array($ip)) {
                    foreach ($ip as $iIndex => $iNode) {
                        if (!is_numeric($iIndex) || !is_array($iNode)) continue;
                        $fullPath = "InternetGatewayDevice.WANDevice.$wdIndex.WANConnectionDevice.$cdIndex.WANIPConnection.$iIndex";
                        $wans[] = $this->extractWanData($iNode, 'IPoE', "$wdIndex.$cdIndex.$iIndex", $fullPath);
                    }
                }
            }
        }
        return $wans;
    }

    private function extractWanData($node, $type, $pathIndex, $fullPath = '')
    {
        // Extract basic data
        $name = $node['Name']['_value'] ?? "WAN $pathIndex";
        $nat = $node['NATEnabled']['_value'] ?? false;
        
        // Find VLAN ID (usually inside WANEthernetLinkConfig of the parent WANConnectionDevice)
        // But for UI display simplicity, we can fetch it if it's stored inside the node (ZTE sometimes puts it in X_ZTE-COM_VLANID)
        // For now, we'll try common paths
        $vlan = $node['VLANID']['_value'] ??
                $node['X_BROADCOM_COM_VLANID']['_value'] ?? 
                $node['X_ZTE-COM_VLANID']['_value'] ?? 
                $node['X_HW_VLAN']['_value'] ?? 
                null;
                
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
        }
                
        // Username for PPPoE
        $username = $node['Username']['_value'] ?? null;

        return [
            'id' => $pathIndex,
            'name' => $name,
            'type' => $type,
            'nat' => $nat,
            'vlan' => $vlan,
            'username' => $username,
            'service_list' => $serviceList,
            'port_bind' => $portBind,
            'fullPath' => $fullPath,
        ];
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
        $this->editFullPath = $fullPath;
        $this->formName = $wan['name'];
        $this->formType = $wan['type'];
        $this->formVlan = $wan['vlan'] ?? '';
        $this->formNat = $wan['nat'] ? true : false;
        $this->formUsername = $wan['username'] ?? '';
        $this->formPassword = ''; // Password selalu kosong demi keamanan
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
        $this->formType = 'PPPoE';
        $this->formVlan = '';
        $this->formUsername = '';
        $this->formPassword = '';
        $this->formNat = true;
        $this->bindLan = [1,2,3,4]; // Default select all LAN
        $this->bindWlan = [1,5]; // Default select SSID1 and SSID5 (5Ghz)
    }

    public function cancelCreate()
    {
        $this->isCreating = false;
    }

    public function saveNewWan()
    {
        $this->validate([
            'formType' => 'required|in:PPPoE,IP_Routed',
        ]);
        
        try {
            $driver = new \App\Services\Adapters\Monitoring\GenieACSDriver();
            $vendor = strtolower($this->device->vendor->name ?? '');
            
            // Step 1: Tell GenieACS to run a custom provision script that adds the WAN dynamically
            // This is 100x safer than guessing instance indexes via REST API
            
            $vlanId = (int) $this->formVlan;
            $isPppoe = $this->formType === 'PPPoE' ? 'true' : 'false';
            $natEnabled = $this->formNat ? 'true' : 'false';
            $username = $this->formUsername;
            $password = $this->formPassword;
            
            // Build LAN/WLAN Bindings
            $bindPaths = [];
            foreach ($this->bindLan as $l) {
                $bindPaths[] = "InternetGatewayDevice.LANDevice.1.LANEthernetInterfaceConfig.{$l}";
            }
            foreach ($this->bindWlan as $w) {
                $bindPaths[] = "InternetGatewayDevice.LANDevice.1.WLANConfiguration.{$w}";
            }
            $bindString = implode(',', $bindPaths);
            
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
if ("{$vendor}".includes("zte")) {
    declare(basePath + ".X_ZTE-COM_VLANIDMark", {value: now}, {value: {$vlanId}});
} else if ("{$vendor}".includes("fiberhome")) {
    declare(basePath + ".X_FH_WANGponLinkConfig.VLANIDMark", {value: now}, {value: {$vlanId}});
    declare(basePath + ".X_FH_WANGponLinkConfig.VLANID", {value: now}, {value: {$vlanId}});
} else {
    declare(basePath + ".VLANID", {value: now}, {value: {$vlanId}});
}

// Add Connection Object
let connType = {$isPppoe} ? "PPPoE_Bridged" : "IP_Routed";
let connObj = {$isPppoe} ? "WANPPPConnection" : "WANIPConnection";
let pppPath = basePath + "." + connObj + ".1";

declare(pppPath, {path: 1}, {path: 1});

declare(pppPath + ".ConnectionType", {value: now}, {value: connType});
declare(pppPath + ".NATEnabled", {value: now}, {value: {$natEnabled}});
declare(pppPath + ".Enable", {value: now}, {value: true});
declare(pppPath + ".Name", {value: now}, {value: "dsBilling_" + connType + "_{$vlanId}"});

if (!{$isPppoe}) {
    declare(pppPath + ".AddressingType", {value: now}, {value: "DHCP"});
}

// Port Binding (Huawei & Fiberhome)
if ("{$bindString}" !== "") {
    if ("{$vendor}".includes("fiberhome")) {
        declare(pppPath + ".X_FH_LanInterface", {value: now}, {value: "{$bindString}"});
    } else if ("{$vendor}".includes("huawei") || "{$vendor}".includes("ecomtech")) {
        declare(pppPath + ".X_HW_LANBIND", {value: now}, {value: "{$bindString}"});
    }
}

if ("{$vendor}".includes("huawei") || "{$vendor}".includes("ecomtech")) {
    declare(pppPath + ".X_HW_VLAN", {value: now}, {value: {$vlanId}});
    declare(pppPath + ".X_HW_SERVICELIST", {value: now}, {value: "INTERNET"});
}

if ({$isPppoe}) {
    declare(pppPath + ".Username", {value: now}, {value: "{$username}"});
    declare(pppPath + ".Password", {value: now}, {value: "{$password}"});
}
JS;

            // Upsert a temporary provision
            $provName = "temp_add_wan_" . $this->device->uuid;
            $driver->upsertProvision($provName, $script);
            
            // Queue the provision task
            $driver->addProvisionTask($this->device->uuid, $provName);
            
            session()->flash('success', 'Perintah pembuatan WAN berhasil dikirim ke GenieACS. Tunggu beberapa saat agar 
modem terkonfigurasi.');
            $this->isCreating = false;
        } catch (\Exception $e) {
            session()->flash('error', 'Gagal membuat WAN: ' . $e->getMessage());
        }
    }

    public function saveWan()
    {
        if (!$this->editFullPath) return;

        $parameters = [];

        // Note: For ZTE, VLAN is often in X_ZTE-COM_VLANIDMark at the parent WANConnectionDevice level
        // Path Example: InternetGatewayDevice.WANDevice.1.WANConnectionDevice.1.WANPPPConnection.1
        $pathParts = explode('.', $this->editFullPath);
        array_pop($pathParts); array_pop($pathParts);
        $parentPath = implode('.', $pathParts); // InternetGatewayDevice.WANDevice.1.WANConnectionDevice.1

        $vendor = strtolower($this->device->vendor->name ?? '');

        // === Username & Password (Only for PPPoE) ===
        if ($this->formType === 'PPPoE') {
            if (!empty($this->formUsername)) {
                $parameters["{$this->editFullPath}.Username"] = $this->formUsername;
            }
            if (!empty($this->formPassword)) {
                $parameters["{$this->editFullPath}.Password"] = $this->formPassword;
            }
        }

        // === NAT ===
        $parameters["{$this->editFullPath}.NATEnabled"] = $this->formNat ? true : false;

        // === VLAN ===
        if ($this->formVlan !== '') {
            $vlanId = (int) $this->formVlan;
            if (strpos($vendor, 'zte') !== false) {
                $parameters["{$parentPath}.X_ZTE-COM_VLANIDMark"] = $vlanId;
            } elseif (strpos($vendor, 'huawei') !== false || strpos($vendor, 'ecomtech') !== false) {
                $parameters["{$this->editFullPath}.X_HW_VLAN"] = $vlanId;
            } elseif (strpos($vendor, 'fiberhome') !== false) {
                $parameters["{$parentPath}.X_FH_WANGponLinkConfig.VLANIDMark"] = $vlanId;
            } else {
                // Generic fallback if standard VLANID is supported directly
                $parameters["{$this->editFullPath}.VLANID"] = $vlanId;
            }
        }

        try {
            $driver = new GenieACSDriver();
            $success = $driver->setParameterValues($this->device->uuid, $parameters);

            if ($success) {
                session()->flash('success', 'Task pembaruan WAN berhasil dikirim ke perangkat. Perubahan akan berlaku sebentar lagi.');
                $this->isEditing = false;
                $this->loadWans(); // Reload
            } else {
                session()->flash('error', 'Gagal mengirim task pembaruan WAN ke GenieACS.');
            }
        } catch (\Exception $e) {
            session()->flash('error', 'Error: ' . $e->getMessage());
        }
    }
}