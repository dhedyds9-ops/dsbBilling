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
                        $wans[] = $this->extractWanData($pNode, 'PPPoE', "$wdIndex.$cdIndex.$pIndex");
                    }
                }

                // Cek IPoE/Static/Bridge
                $ip = $cdNode['WANIPConnection'] ?? [];
                if (is_array($ip)) {
                    foreach ($ip as $iIndex => $iNode) {
                        if (!is_numeric($iIndex) || !is_array($iNode)) continue;
                        $wans[] = $this->extractWanData($iNode, 'IPoE', "$wdIndex.$cdIndex.$iIndex");
                    }
                }
            }
        }
        return $wans;
    }

    private function extractWanData($node, $type, $pathIndex)
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
                       '-';
                       
        $portBind = $node['X_FH_LanInterface']['_value'] ?? 
                    $node['X_ZTE-COM_PortBind']['_value'] ?? 
                    $node['X_HW_LANBinding']['_value'] ?? 
                    '';
                
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
