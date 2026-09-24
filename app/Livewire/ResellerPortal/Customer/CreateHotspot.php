<?php
namespace App\Livewire\ResellerPortal\Customer;

use App\Livewire\AdminComponent;
use App\Models\CRM\Customer;
use App\Models\ISP\Router;
use App\Models\ISP\ServiceProfile;
use App\Services\Provisioning\ProvisioningService;

class CreateHotspot extends AdminComponent
{
    public $name;
    public $customer_code;
    public $phone;
    public $email;
    public $address;
    public $notes;

    public $username;
    public $password;
    public $login_method = 'username_and_password';
    public $router_id;
    public $service_profile_id;
    public $network_profile_id;

    public $mac_address;
    public $static_ip;
    public $genieacs_device_id;
    public $odp_id;
    public $port_number;
    public $onu_id;
    public $latitude;
    public $longitude;

    public $billing_cycle = 'monthly';
    public $setup_fee;
    public $payment_status = 'unpaid';
    public $reseller_id;

    public $activation_date;
    public $status = 'active';

    public function mount()
    {
        parent::mount();
        $this->activeModule = 'reseller-portal';
        $this->activePage = 'customers.index';
        $this->reseller_id = auth()->id();
        $this->activation_date = date('Y-m-d');
        
        $this->breadcrumbs = [
            ['label' => 'Reseller Portal', 'url' => route('reseller-portal.dashboard')],
            ['label' => 'Pelanggan Saya', 'url' => route('reseller-portal.customers.index')],
            ['label' => 'Tambah Pelanggan Hotspot'],
        ];
    }

    public function save()
    {
        if ($this->login_method === 'username_only') {
            $this->password = $this->username;
        }

        $provisioningService = app(ProvisioningService::class);
        try {
            $this->validate([
            'name'               => 'required|string|max:255',
            'phone'              => 'required|string|max:20',
            'email'              => 'nullable|email|max:255',
            'username'           => 'required|string|unique:hotspot_users,username',
            'password'           => 'required|string',
            'service_profile_id' => 'required|exists:service_profiles,id',
            'router_id'          => 'nullable|exists:routers,id',
        ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            $errors = $e->validator->errors()->all();
            $errorMsg = implode('<br>', $errors);
            $this->dispatch('toast', [
                'type' => 'error',
                'message' => $errorMsg ?: 'Ada kolom wajib yang belum diisi atau salah!'
            ]);
            throw $e;
        }

        try {
            $ownerId = auth()->id();
            $ownershipData = [
                'reseller_id' => $ownerId,
                'branch_id'   => null,
            ];

            // Generate customer code
            $code = $this->customer_code ?: $this->generateCustomerCode();

            $result = $provisioningService->activateHotspotService(array_merge([
                'name'               => $this->name,
                'customer_code'      => $code,
                'phone'              => $this->phone,
                'email'              => $this->email,
                'address'            => $this->address,
                'username'           => $this->username,
                'password'           => $this->password,
                'service_profile_id' => $this->service_profile_id,
                'router_id'          => $this->router_id,
                'status'             => $this->status,
                'static_ip'          => $this->static_ip,
                'mac_address'        => $this->mac_address,
                'onu_id'             => $this->onu_id,
                'odp_id'             => $this->odp_id,
                'port_number'        => $this->port_number,
                'billing_cycle'      => $this->billing_cycle,
                'setup_fee'          => $this->setup_fee,
                'notes'              => json_encode(['notes' => $this->notes, 'lat' => $this->latitude, 'lng' => $this->longitude]),
            ], $ownershipData), $ownerId);

            if ($result['customer'] ?? null) {
                $result['customer']->update($ownershipData);
            }

            $this->dispatch('toast', [
                'type' => 'success',
                'message' => 'Pelanggan & Hotspot User berhasil dibuat!',
                'invoice_id' => $result['invoice']->id ?? null
            ]);
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Reseller Customer Hotspot create failed: ' . $e->getMessage());
            $this->dispatch('toast', type: 'error', message: 'Gagal: ' . $e->getMessage());
        }
    }

    public function render()
    {
        $serviceProfiles = ServiceProfile::active()->whereIn('service_type', ['hotspot', 'combined'])->get();
        $routers = Router::active()->get();
        $networkProfiles = \App\Models\Provisioning\NetworkProfile::all();
        $odps = \App\Models\ISP\Odp::all();
        $onus = \App\Models\ISP\Onu::all();
        
        return view('livewire.reseller-portal.customer.create-hotspot', compact('serviceProfiles', 'routers', 'networkProfiles', 'odps', 'onus'));
    }

    private function generateCustomerCode()
    {
        return \App\Services\CRM\CustomerCodeGenerator::generate();
    }
}
