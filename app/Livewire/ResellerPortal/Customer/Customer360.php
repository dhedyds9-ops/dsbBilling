<?php

namespace App\Livewire\ResellerPortal\Customer;

use App\Livewire\AdminComponent;
use App\Models\CRM\Customer;

class Customer360 extends AdminComponent
{
    public $customerId;
    protected $customer;
    public string $activeTab = 'profile';
    
    // Edit Customer State
    public $showEditModal = false;
    public $edit_name, $edit_phone, $edit_email, $edit_address, $edit_latitude, $edit_longitude;
    public $edit_reseller_id = '', $edit_branch_id = '', $edit_notes = '', $edit_status = 'active';
    
    // Edit Service State
    public $showEditServiceModal = false;
    public $edit_service_id;
    public $edit_service_type = 'pppoe';
    public $edit_service_profile_id = '';
    public $edit_service_username = '';
    public $edit_service_password = '';
    public $edit_service_password_mode = 'custom';

    // ACS WiFi Modal state
    public $showWifiModal = false;
    public $acsDeviceIdForWifi = null;
    public $wlanTarget = '1';
    public $wifiSsid = '';
    public $wifiPassword = '';

    protected function getCustomer()
    {
        if (!$this->customer) {
            $this->customer = \App\Models\CRM\Customer::with([
                'customerServices' => function ($query) {
                    $query->withoutGlobalScope('branch_isolation')
                          ->with([
                              'service', 'serviceProfile', 'onu.odp.odc', 'onu.ponPort', 'onu.olt', 'pppoeUser', 'hotspotUser'
                          ]);
                },
                'invoices',
                'payments',
                'contracts',
                'installations', 'tickets',
            ])->findOrFail($this->customerId);
        }
        return $this->customer;
    }

    protected $tabs = [
        'profile' => 'Profil & Lokasi',
        'finance' => 'Keuangan & Tagihan',
        'device'  => 'Perangkat & Jaringan',
        'support' => 'Support & Teknis',
        'history' => 'Riwayat & Log',
    ];

    public function mount($id = null)
    {
        parent::mount();
        $this->activeModule = 'crm';
        $this->activePage = 'customers';
        $this->customerId = $id;
        $this->customer = Customer::findOrFail($this->customerId);
    }

    public function setActiveTab($tab)
    {
        $this->activeTab = $tab;
    }

    public function openEditModal()
    {
        $this->edit_name       = $this->getCustomer()->name;
        $this->edit_phone      = $this->getCustomer()->phone;
        $this->edit_email      = $this->getCustomer()->email;
        $this->edit_address    = $this->getCustomer()->address;
        $this->edit_latitude   = $this->getCustomer()->latitude;
        $this->edit_longitude  = $this->getCustomer()->longitude;
        $this->edit_reseller_id = $this->getCustomer()->reseller_id ?? '';
        $this->edit_branch_id  = $this->getCustomer()->branch_id ?? '';
        $this->edit_notes      = $this->getCustomer()->notes ?? '';
        $this->edit_status     = $this->getCustomer()->status ?? 'active';
        $this->showEditModal   = true;
    }

    public function updateCustomer()
    {
        $this->validate([
            'edit_name'        => 'required|string|max:255',
            'edit_phone'       => 'nullable|string|max:50',
            'edit_email'       => 'nullable|email|max:255',
            'edit_address'     => 'nullable|string',
            'edit_latitude'    => 'nullable|string',
            'edit_longitude'   => 'nullable|string',
            'edit_reseller_id' => 'nullable|exists:users,id',
            'edit_branch_id'   => 'nullable|exists:branches,id',
        ]);

        $this->getCustomer()->update([
            'name'        => $this->edit_name,
            'phone'       => $this->edit_phone,
            'email'       => $this->edit_email,
            'address'     => $this->edit_address,
            'latitude'    => $this->edit_latitude,
            'longitude'   => $this->edit_longitude,
            'status'      => $this->edit_status,
            'reseller_id' => $this->edit_reseller_id ?: null,
            'branch_id'   => $this->edit_branch_id   ?: null,
            'notes'       => $this->edit_notes,
            'updated_by'  => auth()->id(),
        ]);

        $this->showEditModal = false;
        $this->getCustomer()->refresh();
        session()->flash('success', 'Data pelanggan berhasil diperbarui!');
    }

            public function updatedEditServicePasswordMode($val) {
        if ($val === 'same') $this->edit_service_password = $this->edit_service_username;
    }
    public function updatedEditServiceUsername($val) {
        if ($this->edit_service_password_mode === 'same') $this->edit_service_password = $val;
    }

    public function openEditServiceModal($serviceId)
    {
        $cs = \App\Models\Customer\CustomerService::with(['pppoeUser', 'hotspotUser'])->find($serviceId);
        if ($cs) {
            $this->edit_service_id = $cs->id;
            $this->edit_service_type = $cs->pppoeUser ? 'pppoe' : ($cs->hotspotUser ? 'hotspot' : 'pppoe');
            $this->edit_service_profile_id = $cs->service_profile_id ?? '';
            $this->edit_service_username = $cs->username ?? '';
            $this->edit_service_password = $cs->password ?? '';
            $this->edit_service_password_mode = ($this->edit_service_username === $this->edit_service_password && $this->edit_service_username !== '') ? 'same' : 'custom';
            $this->showEditServiceModal = true;
        }
    }

    public function updateService()
    {
        $this->validate([
            'edit_service_type' => 'required|in:pppoe,hotspot',
            'edit_service_profile_id' => 'required|exists:service_profiles,id',
            'edit_service_username' => 'required|string',
            'edit_service_password' => 'nullable|string',
        ]);

        $cs = \App\Models\Customer\CustomerService::with(['pppoeUser', 'hotspotUser'])->find($this->edit_service_id);
        if ($cs) {
            $newType = $this->edit_service_type;
            $oldType = $cs->pppoeUser ? 'pppoe' : ($cs->hotspotUser ? 'hotspot' : null);

            // Fetch the new profile to get details like billing_cycle
            $profile = \App\Models\ISP\ServiceProfile::find($this->edit_service_profile_id);

            $userUpdateData = [
                'username' => $this->edit_service_username,
                'service_profile_id' => $this->edit_service_profile_id,
            ];
            
            // Jika password diisi, gunakan yang baru. Jika kosong, gunakan password lama.
            $password = $this->edit_service_password ?: ($cs->password ?? '123456');
            $userUpdateData['password'] = $password;

            if ($newType !== $oldType) {
                // Change type
                if ($oldType === 'pppoe') $cs->pppoeUser()->delete();
                if ($oldType === 'hotspot') $cs->hotspotUser()->delete();

                // Create new
                $userUpdateData['billing_cycle'] = 'monthly';
                $userUpdateData['status'] = $cs->status ?? 'active';
                $userUpdateData['uuid'] = (string) \Illuminate\Support\Str::uuid();
                $userUpdateData['created_by'] = auth()->id();
                $userUpdateData['updated_by'] = auth()->id();
                $userUpdateData['reseller_id'] = $cs->customer->reseller_id ?? null;
                
                if ($newType === 'pppoe') {
                    $cs->pppoeUser()->create($userUpdateData);
                } else {
                    $cs->hotspotUser()->create($userUpdateData);
                }
            } else {
                // Update existing
                if ($newType === 'pppoe') {
                    $cs->pppoeUser()->update($userUpdateData);
                } else {
                    $cs->hotspotUser()->update($userUpdateData);
                }
            }
            
            // Update CustomerService itself
            $cs->update([
                'service_profile_id' => $this->edit_service_profile_id,
                'username' => $this->edit_service_username,
                'password' => $this->edit_service_password ?: $cs->password,
            ]);

            $this->showEditServiceModal = false;
            $this->getCustomer()->refresh();
            session()->flash('success', 'Layanan dan Paket berhasil diperbarui!');
        }
    }

        // === ACS / TR-069 FUNCTIONS ===
    public function rebootModem($deviceId)
    {
        try {
            $device = \App\Models\ACS\ACSDevice::findOrFail($deviceId);
            $driver = new \App\Services\Adapters\Monitoring\GenieACSDriver();
            $driver->rebootDevice($device->uuid);
            session()->flash('success', 'Perintah reboot telah dikirim ke modem.');
        } catch (\Exception $e) {
            session()->flash('error', 'Gagal mengirim perintah reboot: ' . $e->getMessage());
        }
    }

    public function resetModem($deviceId)
    {
        try {
            $device = \App\Models\ACS\ACSDevice::findOrFail($deviceId);
            $driver = new \App\Services\Adapters\Monitoring\GenieACSDriver();
            $driver->factoryResetDevice($device->uuid);
            session()->flash('success', 'Perintah Factory Reset telah dikirim ke modem.');
        } catch (\Exception $e) {
            session()->flash('error', 'Gagal reset modem: ' . $e->getMessage());
        }
    }

    public function openWifiModal($deviceId)
    {
        $this->acsDeviceIdForWifi = $deviceId;
        $this->wlanTarget = '1';
        
        $this->showWifiModal = true;
        $this->loadWifiCredentials();
    }
    
    public function updatedWlanTarget()
    {
        $this->loadWifiCredentials();
    }

    private function extractParam(array $params, string $path)
    {
        $parts = explode('.', $path);
        $node = $params;
        foreach ($parts as $p) {
            if (!is_array($node) || !array_key_exists($p, $node)) return null;
            $node = $node[$p];
        }
        return isset($node['_value']) ? $node['_value'] : null;
    }

    private function pathExists(array $params, string $path): bool
    {
        $parts = explode('.', $path);
        $node = $params;
        foreach ($parts as $p) {
            if (!is_array($node) || !array_key_exists($p, $node)) return false;
            $node = $node[$p];
        }
        return true;
    }

    public function loadWifiCredentials()
    {
        try {
            $device = \App\Models\ACS\ACSDevice::findOrFail($this->acsDeviceIdForWifi);
            $driver = new \App\Services\Adapters\Monitoring\GenieACSDriver();
            
            if (!$this->cachedDeviceParams) {
                $this->cachedDeviceParams = $driver->getDeviceParameters($device->uuid);
            }
            $params = $this->cachedDeviceParams;
            $wlanIndex = $this->wlanTarget === '1' ? '1' : '5';
            
            $this->wifiSsid = 
                $this->extractParam($params, "InternetGatewayDevice.LANDevice.1.WLANConfiguration.{$wlanIndex}.SSID")
                ?? $this->extractParam($params, "Device.WiFi.SSID.{$wlanIndex}.SSID") ?? '';
            
            $this->wifiPassword = 
                $this->extractParam($params, "InternetGatewayDevice.LANDevice.1.WLANConfiguration.{$wlanIndex}.PreSharedKey.1.KeyPassphrase")
                ?? $this->extractParam($params, "InternetGatewayDevice.LANDevice.1.WLANConfiguration.{$wlanIndex}.KeyPassphrase")
                ?? $this->extractParam($params, "Device.WiFi.AccessPoint.{$wlanIndex}.Security.KeyPassphrase") ?? '';
            
        } catch (\Exception $e) {
            session()->flash('error', 'Gagal mengambil data WiFi: ' . $e->getMessage());
        }
    }

    public function saveWifi()
    {
        $this->validate([
            'wifiSsid' => 'required|string|min:3',
            'wifiPassword' => 'nullable|string|min:8',
        ]);

        try {
            $device = \App\Models\ACS\ACSDevice::findOrFail($this->acsDeviceIdForWifi);
            $driver = new \App\Services\Adapters\Monitoring\GenieACSDriver();
            $wlanIndex = $this->wlanTarget === '1' ? 1 : 5;
            
            if (!$this->cachedDeviceParams) {
                $this->cachedDeviceParams = $driver->getDeviceParameters($device->uuid);
            }
            $params = $this->cachedDeviceParams;
            $paramsToSet = [];
            
            $ssidCandidates = ["InternetGatewayDevice.LANDevice.1.WLANConfiguration.{$wlanIndex}.SSID", "Device.WiFi.SSID.{$wlanIndex}.SSID"];
            foreach ($ssidCandidates as $path) {
                if ($this->pathExists($params, $path)) {
                    $paramsToSet[$path] = $this->wifiSsid;
                    break;
                }
            }
            if (!isset($paramsToSet[$ssidCandidates[0]]) && !isset($paramsToSet[$ssidCandidates[1]])) {
                $paramsToSet[$ssidCandidates[0]] = $this->wifiSsid;
            }
            
            if (!empty($this->wifiPassword)) {
                $passCandidates = ["InternetGatewayDevice.LANDevice.1.WLANConfiguration.{$wlanIndex}.PreSharedKey.1.KeyPassphrase", "InternetGatewayDevice.LANDevice.1.WLANConfiguration.{$wlanIndex}.KeyPassphrase", "Device.WiFi.AccessPoint.{$wlanIndex}.Security.KeyPassphrase"];
                $passSet = false;
                foreach ($passCandidates as $path) {
                    if ($this->pathExists($params, $path)) {
                        $paramsToSet[$path] = $this->wifiPassword;
                        $passSet = true;
                        break;
                    }
                }
                if (!$passSet) $paramsToSet[$passCandidates[0]] = $this->wifiPassword;
            }
            
            $trueDeviceId = $params['_id'] ?? $device->uuid;
            $driver->setParameterValues($trueDeviceId, $paramsToSet);

            $this->showWifiModal = false;
            session()->flash('success', 'Task perubahan WiFi berhasil dikirim ke modem!');
        } catch (\Exception $e) {
            session()->flash('error', 'Gagal mengirim task WiFi: ' . $e->getMessage());
        }
    }

            public function render()
    {
        // Hydrate customer if not set
        if (!$this->customer) {
            $this->customer = \App\Models\CRM\Customer::with([
                'customerServices' => function ($query) {
                    $query->withoutGlobalScope('branch_isolation')
                          ->with([
                              'service', 'serviceProfile', 'onu.odp.odc', 'onu.ponPort', 'onu.olt', 'pppoeUser', 'hotspotUser'
                          ]);
                },
                'invoices',
                'payments',
                'contracts',
                'installations', 'tickets',
            ])->findOrFail($this->customerId);
        }
        
        $customer = $this->customer;
        // 1. Timeline Building
        $timeline = [];
        $timeline[] = ['date' => $this->getCustomer()->created_at, 'title' => 'Pendaftaran Akun', 'description' => 'Customer terdaftar di sistem', 'type' => 'create'];
        
        if ($this->getCustomer()->installations) {
            foreach ($this->getCustomer()->installations as $inst) {
                $timeline[] = ['date' => $inst->completed_at ?? $inst->scheduled_at ?? $inst->created_at, 'title' => 'Instalasi Jaringan', 'description' => $inst->notes ?? 'Instalasi pelanggan dilakukan', 'type' => 'installation'];
            }
        }
        
        if ($this->getCustomer()->tickets) {
            foreach ($this->getCustomer()->tickets as $tick) {
                $timeline[] = ['date' => $tick->created_at, 'title' => 'Komplain / Tiket', 'description' => $tick->subject ?? $tick->title ?? 'Tiket dibuat', 'type' => 'ticket'];
                if ($tick->resolved_at) {
                    $timeline[] = ['date' => $tick->resolved_at, 'title' => 'Tiket Diselesaikan', 'description' => 'Komplain telah diselesaikan', 'type' => 'ticket_resolved'];
                }
            }
        }
        
        if ($this->getCustomer()->invoices) {
            foreach ($this->getCustomer()->invoices as $inv) {
                if ($inv->status === 'paid' && $inv->paid_at) {
                    $timeline[] = ['date' => $inv->paid_at, 'title' => 'Pembayaran Tagihan', 'description' => 'Tagihan ' . $inv->invoice_number . ' Lunas', 'type' => 'payment'];
                }
            }
        }
        
        // Sort timeline descending by date
        usort($timeline, function($a, $b) {
            return $b['date'] <=> $a['date'];
        });

        // 2. Tickets
        $tickets = $this->getCustomer()->tickets ?? collect([]);

        // 3. Notifications (fetch if user_id exists)
        $notifications = collect([]);
        if ($this->getCustomer()->user_id) {
            $notifications = \App\Models\Notification\Notification::where('recipient_id', $this->getCustomer()->user_id)->orderBy('created_at', 'desc')->get()->map(function($notif) {
                return [
                    'title' => $notif->title,
                    'message' => $notif->message,
                    'created_at' => $notif->created_at,
                    'read' => $notif->status === 'read'
                ];
            });
        }

        // 4. Activities (Mock audit trail based on timeline but formatted for activities)
        $activities = [];
        foreach (array_slice($timeline, 0, 15) as $t) {
            $user = 'Sistem';
            $module = 'System';
            if ($t['type'] === 'create') { $user = $this->getCustomer()->createdBy->name ?? 'Admin'; $module = 'CRM'; }
            if ($t['type'] === 'installation') { $module = 'Teknisi'; }
            if ($t['type'] === 'ticket') { $module = 'Support'; }
            if ($t['type'] === 'payment') { $module = 'Finance'; }
            
            $activities[] = [
                'user' => $user,
                'action' => $t['title'] . ' - ' . $t['description'],
                'module' => $module,
                'time' => \Carbon\Carbon::parse($t['date'])->diffForHumans()
            ];
        }

        $invoices = $this->getCustomer()->invoices ?? collect([]);
        $payments = $this->getCustomer()->payments ?? collect([]);
        $installations = $this->getCustomer()->installations ?? collect([]);
        
        $devices = [];
        if ($this->getCustomer()->customerServices) {
            $this->getCustomer()->customerServices->each(function ($service) use (&$devices) {
                if ($service->onu) {
                    $devices[] = [
                        'id' => $service->onu->id, 
                        'type' => 'ONT', 
                        'brand' => $service->onu->brand ?? 'Unknown', 
                        'model' => $service->onu->model ?? 'Unknown', 
                        'serial' => $service->onu->serial_number ?? 'Unknown', 
                        'status' => $service->onu->status ?? 'active',
                        'wifi_ssid' => $service->onu->wifi_ssid ?? '-',
                        'wifi_password' => $service->onu->wifi_password ?? '-',
                        'topology' => [
                            'odp' => $service->onu->odp->name ?? 'Belum terhubung ODP',
                            'odc' => $service->onu->odp->odc->name ?? '-',
                            'pon_port' => $service->onu->formatted_pon_port ?? $service->onu->ponPort->name ?? '-',
                            'olt' => $service->onu->olt->name ?? 'Belum terhubung OLT',
                            'onu_id' => $service->onu->onu_id_on_olt ?? '-'
                        ]
                    ];
                }
            });
        }
        
        $monthlyBill = 0;
        if ($this->getCustomer()->customerServices) {
            $this->getCustomer()->customerServices->each(function ($service) use (&$monthlyBill) {
                if ($service->status === 'active' && $service->serviceProfile) {
                    $monthlyBill += (float) $service->serviceProfile->base_price;
                }
            });
        }

        $unpaidBill = 0;
        $earliestDueDate = null;
        if ($this->getCustomer()->invoices) {
            foreach ($this->getCustomer()->invoices as $inv) {
                if ($inv->status !== 'paid' && $inv->status !== 'cancelled') {
                    $unpaidBill += (float) $inv->total_amount;
                    if ($inv->due_date) {
                        if (!$earliestDueDate || $inv->due_date->lt($earliestDueDate)) {
                            $earliestDueDate = $inv->due_date;
                        }
                    }
                }
            }
        }

        $monitoring = [];
        if ($this->getCustomer()->customerServices) {
            $this->getCustomer()->customerServices->each(function ($service) use (&$monitoring) {
                $ip = 'Dynamic';
                $mac = 'N/A';
                if ($service->pppoeUser) {
                    $ip = $service->pppoeUser->static_ip ?? 'Dynamic';
                    $mac = $service->pppoeUser->mac_address ?? 'N/A';
                } elseif ($service->hotspotUser) {
                    $ip = $service->hotspotUser->static_ip ?? 'Dynamic';
                    $mac = $service->hotspotUser->mac_address ?? 'N/A';
                }
                
                $mon = [
                    'service' => $service->service->name ?? 'Unknown',
                    'status' => $service->status,
                    'onu_rx' => $service->onu->rx_power_dbm ?? 'N/A',
                    'onu_tx' => $service->onu->tx_power_dbm ?? 'N/A',
                    'onu_status' => $service->onu->status ?? 'Unknown',
                    'last_seen' => $service->onu->last_seen_at ?? 'Never',
                    'ip' => $ip,
                    'mac' => $mac,
                ];
                $monitoring[] = $mon;
            });
        }

        return view('livewire.reseller-portal.customer.customer360', compact(
            'customer', 'monthlyBill', 
            'unpaidBill', 
            'earliestDueDate', 
            'timeline', 
            'activities', 
            'invoices', 
            'payments', 
            'tickets', 
            'devices', 
            'installations',
            'notifications',
            'monitoring'
        ));
    }
}
