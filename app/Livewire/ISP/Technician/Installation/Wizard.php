<?php

namespace App\Livewire\ISP\Technician\Installation;


use Livewire\Component;
use Livewire\Attributes\Layout;
use App\Models\CRM\Customer;
use App\Models\ISP\ServiceProfile;
use App\Models\ISP\Odp;
use App\Models\ISP\Olt;
use App\Models\Provisioning\NetworkProfile;
use App\Services\Provisioning\ProvisioningService;
use Illuminate\Support\Str;

#[Layout('layouts.technician-app')]
class Wizard extends Component
{
    public $currentStep = 1;

    // Search Fields
    public $searchCustomer = '';
    public $searchOdp = '';

    // Form State
    public $is_new_customer = false;
    public $new_customer_name;
    public $new_customer_phone;
    public $new_customer_address;
    public $new_customer_reseller_id;

    public $customer_id;
    public $service_profile_id;
    public $service_type = 'pppoe'; // Default to PPPoE
    public $odp_id;
    public $olt_id;
    public $pon_port;
    public $onu_sn;
    public $onu_vendor_id;
    public $onu_model;

    public $network_profile_id;

    public function mount()
    {
        $this->breadcrumbs = [
            ['label' => 'Technician Dashboard', 'url' => route('technician.dashboard')],
            ['label' => 'Pemasangan Baru', 'url' => '#'],
        ];

        $this->network_profile_id = NetworkProfile::where('type', 'pppoe')->first()?->id;
    }

    public function updatedServiceProfileId($value)
    {
        if ($value) {
            $profile = ServiceProfile::find($value);
            $this->service_type = $profile ? $profile->service_type : 'pppoe';
            $this->network_profile_id = NetworkProfile::where('type', $this->service_type)->first()?->id;
        }
    }

    public function nextStep()
    {
        $this->validateStep();
        if ($this->currentStep < 6) {
            $this->currentStep++;
        }
    }

    public function previousStep()
    {
        if ($this->currentStep > 1) {
            $this->currentStep--;
        }
    }

    protected function validateStep()
    {
        if ($this->currentStep === 1) {
            if ($this->is_new_customer) {
                $this->validate([
                    'new_customer_name' => 'required|string|max:255',
                    'new_customer_phone' => 'required|numeric',
                    'new_customer_address' => 'required|string',
                    'new_customer_reseller_id' => 'nullable|exists:users,id',
                ]);
            } else {
                $this->validate(['customer_id' => 'required|exists:members,id']);
            }
        } elseif ($this->currentStep === 2) {
            $this->validate(['service_profile_id' => 'required|exists:service_profiles,id']);
        } elseif ($this->currentStep === 3) {
            $this->validate(['odp_id' => 'required|exists:odps,id']);
        } elseif ($this->currentStep === 4) {
            $this->validate([
                'olt_id' => 'required|exists:olts,id',
                'pon_port' => 'required|numeric|min:1',
            ]);
        } elseif ($this->currentStep === 5) {
            $this->validate([
                'onu_sn' => 'required|string|min:4',
                'onu_vendor_id' => 'required|exists:vendors,id',
                'onu_model' => 'required|string|max:255',
            ]);
        }
    }

    public function submit(ProvisioningService $provisioningService)
    {
        $this->validateStep();

        if ($this->is_new_customer) {
            $customerCode = \App\Services\CRM\CustomerCodeGenerator::generate();
            $customer = Customer::create([
                'code' => $customerCode,
                'name' => $this->new_customer_name,
                'phone' => $this->new_customer_phone,
                'address' => $this->new_customer_address,
                'reseller_id' => $this->new_customer_reseller_id,
                'status' => 'active',
                'created_by' => auth()->id(),
            ]);
            $this->customer_id = $customer->id;
        } else {
            $customer = Customer::find($this->customer_id);
        }
        
        $companyName = \App\Models\Setting::getValue('company.name', 'dsbilling');
        $companySuffix = strtolower(preg_replace('/[^a-zA-Z0-9]/', '', $companyName));
        
        $username = strtolower($customer->code) . ($this->service_type === 'pppoe' ? '@' . $companySuffix : '');
        $password = $this->service_type === 'hotspot' ? substr(str_shuffle('0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ'), 0, 6) : 'pppoe';

        // Selalu catat ONU ke inventory terlepas dari PPPoE atau Hotspot
        $onu = \App\Models\ISP\Onu::create([
            'olt_id' => $this->olt_id,
            'pon_port' => $this->pon_port,
            'serial_number' => $this->onu_sn,
            'vendor_id' => $this->onu_vendor_id,
            'model' => $this->onu_model,
            'name' => 'ONU ' . $customer->name,
            'status' => 'offline',
            'code' => 'ONU-' . substr(strtoupper(md5(time())), 0, 6),
        ]);

        $data = [
            'name' => $customer->name,
            'phone' => $customer->phone,
            'email' => $customer->email,
            'address' => $customer->address,
            'service_profile_id' => $this->service_profile_id,
            'network_profile_id' => $this->network_profile_id,
            'router_id' => $this->network_profile_id, // Compatibility for HotspotService
            'username' => $username,
            'password' => $password,
            'status' => 'active',
            'odp_id' => $this->odp_id,
            'onu_id' => $onu->id,
            'mac_address' => $this->onu_sn,
        ];

        if ($this->service_type === 'pppoe') {
            $result = $provisioningService->activatePPPoEService($data, auth()->id());
            $cs = $result['customer_service'];
            $serviceInstance = $provisioningService->startProvisioning($cs, auth()->id());

            session()->flash('success', 'Instalasi PPPoE dikirim ke sistem Orchestrator.');
            return redirect()->route('technician.provisioning.show', $serviceInstance->provisionPipeline->id);
            
        } else {
            $result = $provisioningService->activateHotspotService($data, auth()->id());
            
            // Otomatis menembak konfigurasi Bridge dan Wi-Fi (SSID Perusahaan, Open System) ke ONU
            try {
                $jobEngine = app(\App\Services\Provisioning\OnuConfigurationJobEngine::class);
                
                // 1. Set mode Bridge ke semua port LAN dan SSID 1
                $bridgeDesiredState = [
                    'bridge' => [
                        'lan' => ['1','2','3','4'],
                        'ssid' => ['1']
                    ]
                ];
                $jobEngine->dispatchProvisioningJob($onu, $bridgeDesiredState, 'BRIDGE', null, auth()->id());
                
                // 2. Set SSID 1 menjadi nama perusahaan tanpa password (Open System)
                $ssidName = strtoupper(\App\Models\Setting::getValue('company.name', 'WINETS.ID'));
                $wifiService = app(\App\Services\Provisioning\WifiConfigurationService::class);
                
                $wifiService->configureWifi($onu, [
                    1 => [
                        'ssid' => $ssidName,
                        'security' => 'None', // Open system tanpa enkripsi
                        'password' => '', 
                        'enable' => true
                    ]
                ], null, auth()->id());
                
            } catch (\Exception $e) {
                \Illuminate\Support\Facades\Log::error("Gagal push konfigurasi Hotspot ke ONU: " . $e->getMessage());
            }

            session()->flash('success', 'Layanan Hotspot Member dan konfigurasi Bridge (SSID Open) berhasil dieksekusi!');
            return redirect()->route('technician.my-jobs.index');
        }
    }

    public function render()
    {
        $customers = Customer::query()
            ->when($this->searchCustomer, function($q) {
                $q->where('name', 'like', '%' . $this->searchCustomer . '%')
                  ->orWhere('phone', 'like', '%' . $this->searchCustomer . '%');
            })
            ->take(15)
            ->get();

        $odps = Odp::query()
            ->when($this->searchOdp, function($q) {
                $q->where('name', 'like', '%' . $this->searchOdp . '%');
            })
            ->take(20)
            ->get();
            
        $resellers = \App\Models\User::whereHas('roles', function($q) {
            $q->where('name', 'reseller');
        })->get();
        
        $vendors = \App\Models\ISP\Vendor::all();

        return view('livewire.isp.technician.installation.wizard', [
            'customers' => $customers,
            'services' => ServiceProfile::whereIn('service_type', ['pppoe', 'hotspot'])->get(),
            'odps' => $odps,
            'olts' => Olt::all(),
            'resellers' => $resellers,
            'vendors' => $vendors,
        ]);
    }
}
