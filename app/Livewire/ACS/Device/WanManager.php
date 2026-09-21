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
        ];
    }

    public function render()
    {
        return view('livewire.acs.device.wan-manager');
    }
}
