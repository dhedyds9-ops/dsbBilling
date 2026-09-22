<?php

namespace App\Livewire\ACS\Device;

use App\Livewire\ACS\BaseACSComponent;
use App\Models\ACS\ACSDevice;
use App\Models\Customer\CustomerService;
use App\Models\Inventory\Asset;
use App\Models\ISP\Onu;
use App\Models\ISP\Olt;
use App\Models\ISP\Pop;
use App\Models\ISP\Odp;
use App\Models\ISP\Vendor;
use Illuminate\Support\Carbon;

class Edit extends BaseACSComponent
{
    public $deviceId;
    public $device;

    public $serial_number;
    public $mac_address;
    public $oui;
    public $manufacturer;
    public $vendor_id;
    public $model;
    public $product_class;
    public $hardware_version;
    public $software_version;
    public $firmware_version;
    public $ip_address;
    public $connection_request_url;
    public $status;
    public $customer_service_id;
    public $asset_id;
    public $onu_id;
    public $olt_id;
    public $pop_id;
    public $odp_id;
    public $latitude;
    public $longitude;
    public $signal;
    public $uptime;
    public $cpu;
    public $memory;
    public $temperature;
    public $notes;

    // Data yang diambil otomatis dari GenieACS (read-only)
    public $acs_online = false;
    public $acs_ip_address = '';
    public $acs_mac_address = '';
    public $acs_last_inform = '';
    public $acs_error = '';

    public $wifi_ssid;
    public $wifi_password;

    public function mount($id = null)
    {
        parent::mount();
        $this->deviceId = $id;
        $this->device = ACSDevice::findOrFail($id);
        $this->fill($this->device->toArray());
        $this->activeModule = 'acs';
        $this->activePage = 'devices';
        $this->breadcrumbs = [
            ['label' => 'Dashboard', 'url' => route('dashboard')],
            ['label' => 'ACS', 'url' => route('acs.dashboard')],
            ['label' => 'Devices', 'url' => route('acs.devices.index')],
            ['label' => $this->device->serial_number, 'url' => route('acs.devices.show', $id)],
            ['label' => 'Edit'],
        ];

        // Auto-fetch status, IP, dan MAC dari GenieACS
        $this->syncFromGenieACS();
    }

    /**
     * Mengambil status online/offline, IP address, dan MAC address
     * secara otomatis dari GenieACS berdasarkan _lastInform dan parameter device.
     */
    public function syncFromGenieACS()
    {
        try {
            $driver = new \App\Services\Adapters\Monitoring\GenieACSDriver();
            $params = $driver->getDeviceParameters($this->device->uuid);

            $extract = function ($path) use ($params) {
                $parts = explode('.', $path);
                $node = $params;
                foreach ($parts as $p) {
                    if (!is_array($node) || !array_key_exists($p, $node)) {
                        return null;
                    }
                    $node = $node[$p];
                }
                return isset($node['_value']) ? $node['_value'] : null;
            };

            // === STATUS ONLINE/OFFLINE ===
            // Ditentukan dari _lastInform: jika < 5 menit lalu = online
            $lastInform = $params['_lastInform'] ?? null;
            if ($lastInform) {
                $informTime = Carbon::parse($lastInform);
                $this->acs_online = abs(now()->diffInMinutes($informTime)) < 5;
                $this->acs_last_inform = $informTime->format('d/m/Y H:i:s');
            } else {
                $this->acs_online = false;
                $this->acs_last_inform = '-';
            }

            // Update status di database secara otomatis
            $newStatus = $this->acs_online ? 'online' : 'offline';
            $this->status = $newStatus;

            // === IP & MAC ADDRESS (Smart Dynamic Extraction) ===
            $ip = null;
            $mac = null;
            $wanDevices = $params['InternetGatewayDevice']['WANDevice'] ?? $params['Device']['WANDevice'] ?? [];
            if (is_array($wanDevices)) {
                foreach ($wanDevices as $wdNode) {
                    if (!is_array($wdNode)) continue;
                    $connDevices = $wdNode['WANConnectionDevice'] ?? [];
                    if (is_array($connDevices)) {
                        foreach ($connDevices as $connNode) {
                            if (!is_array($connNode)) continue;
                            
                            $pppConns = $connNode['WANPPPConnection'] ?? [];
                            if (is_array($pppConns)) {
                                foreach ($pppConns as $ppp) {
                                    if (is_array($ppp) && !empty($ppp['ExternalIPAddress']['_value'])) {
                                        $ip = $ppp['ExternalIPAddress']['_value'];
                                    }
                                    if (is_array($ppp) && !empty($ppp['MACAddress']['_value'])) {
                                        $mac = $ppp['MACAddress']['_value'];
                                    }
                                }
                            }
                            
                            $ipConns = $connNode['WANIPConnection'] ?? [];
                            if (is_array($ipConns)) {
                                foreach ($ipConns as $ipc) {
                                    if (is_array($ipc) && !empty($ipc['ExternalIPAddress']['_value'])) {
                                        $ip = $ip ?: $ipc['ExternalIPAddress']['_value'];
                                    }
                                    if (is_array($ipc) && !empty($ipc['MACAddress']['_value'])) {
                                        $mac = $mac ?: $ipc['MACAddress']['_value'];
                                    }
                                }
                            }
                        }
                    }
                }
            }

            // Fallback
            $ip = $ip ?? $extract('VirtualParameters.pppoeIP') ?? $extract('Device.DHCPv4.Client.1.IPAddress');
            $mac = $mac ?? $extract('VirtualParameters.pppoeMac') ?? $extract('InternetGatewayDevice.LANDevice.1.LANEthernetInterfaceConfig.1.MACAddress');

            if ($ip && $ip !== '0.0.0.0') {
                $this->acs_ip_address = $ip;
                $this->ip_address = $ip;
            } else {
                $this->acs_ip_address = $this->ip_address ?: '-';
            }

            if ($mac) {
                $this->acs_mac_address = strtoupper($mac);
                $this->mac_address = strtoupper($mac);
            } else {
                $this->acs_mac_address = $this->mac_address ?: '-';
            }

                        $this->acs_error = '';

            // === WIFI CREDENTIALS ===
            try {
                $vendorName = $this->device->vendor?->name ?? 'default';
                $creds = $driver->getWifiCredentials($this->device->uuid, $vendorName);
                if (!empty($creds['ssid'])) {
                    $this->wifi_ssid = $creds['ssid'];
                }
                if (!empty($creds['password'])) {
                    $this->wifi_password = $creds['password'];
                }
            } catch (\Exception $e) {
                // Ignore wifi fetch errors
            }

        } catch (\Exception $e) {
            $this->acs_error = 'Tidak dapat terhubung ke GenieACS: ' . $e->getMessage();
            // Tetap gunakan data dari database jika GenieACS tidak bisa dihubungi
        }
    }

    /**
     * Tombol refresh manual untuk mengambil data terbaru dari GenieACS
     */
    public function refreshFromACS()
    {
        $this->syncFromGenieACS();
        if ($this->acs_error) {
            session()->flash('error', $this->acs_error);
        } else {
            session()->flash('success', 'Data berhasil diperbarui dari GenieACS.');
        }
    }

    public function save()
    {
                $validated = $this->validate([
            'serial_number' => 'nullable|string|max:255',
            'vendor_id' => 'nullable|exists:vendors,id',
            'customer_service_id' => 'nullable|exists:customer_services,id',
            'model' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
            'wifi_ssid' => 'nullable|string|max:255',
            'wifi_password' => 'nullable|string|max:255',
        ]);

        // Status, IP, dan MAC diisi otomatis dari GenieACS, bukan manual
        $validated['status'] = $this->status; // dari syncFromGenieACS
        $validated['ip_address'] = $this->ip_address;
        $validated['mac_address'] = $this->mac_address;
        $validated['updated_by'] = auth()->id();

        $this->device->update([
            'serial_number' => $validated['serial_number'],
            'vendor_id' => $validated['vendor_id'],
            'customer_service_id' => $validated['customer_service_id'],
            'model' => $validated['model'],
            'notes' => $validated['notes'],
            'status' => $validated['status'],
            'ip_address' => $validated['ip_address'],
            'mac_address' => $validated['mac_address'],
            'updated_by' => $validated['updated_by'],
        ]);
        
        // Push to GenieACS if changed
        try {
            $driver = new \App\Services\Adapters\Monitoring\GenieACSDriver();
            $vendorName = $this->device->vendor?->name ?? 'default';
            
            catch (\Exception $e) {
            session()->flash('error', 'Device tersimpan, tapi gagal mengirim task WiFi ke GenieACS: ' . $e->getMessage());
            return redirect()->route('acs.devices.show', $this->deviceId);
        }

        session()->flash('success', 'Device dan pengaturan WiFi berhasil diperbarui!');
        return redirect()->route('acs.devices.show', $this->deviceId);
    }

    public function render()
    {
        $vendors = Vendor::all();
        $customerServices = CustomerService::all();
        $assets = Asset::all();
        $onus = Onu::all();
        $olts = Olt::all();
        $pops = Pop::all();
        $odps = Odp::all();

        return view('livewire.acs.device.edit', compact('vendors', 'customerServices', 'assets', 'onus', 'olts', 'pops', 'odps'));
    }
}
