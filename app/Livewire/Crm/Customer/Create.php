<?php

namespace App\Livewire\Crm\Customer;

use App\Livewire\AdminComponent;
use App\Models\CRM\Customer;
use App\Models\ISP\Router;
use App\Models\ISP\ServiceProfile;
use App\Services\Provisioning\ProvisioningService;
use Illuminate\Support\Str;

class Create extends AdminComponent
{
    public $name = '';
    public $customer_code = '';
    public $email = '';
    public $phone = '';
    public $address = '';
    public $latitude = '';
    public $longitude = '';
    public $status = 'active';

    // Layanan & Sistem
    public $service_profile_id;
    public $router_id;
    public $pppoe_username = '';
    public $pppoe_password = '';

    // Kepemilikan
    public $reseller_id = '';
    public $branch_id = '';
    public $notes = '';

    public function mount()
    {
        parent::mount();
        $this->activeModule = 'crm';
        $this->activePage = 'customers';

        // Jika yang login adalah Reseller, otomatis kunci ke parent/effective reseller-nya
        if (auth()->user()->hasRole('reseller')) {
            $this->reseller_id = auth()->user()->getEffectiveResellerId();
        }
    }

    public function save(\App\Services\Provisioning\ProvisioningService $provisioningService)
    {
        $this->validate([
            'name'               => 'required|string|max:255',
            'customer_code'      => 'nullable|string|unique:members,code',
            'phone'              => 'required|numeric|digits_between:10,15',
            'email'              => 'nullable|email|max:255',
            'address'            => 'required|string',
            'status'             => 'required|in:active,inactive,suspended',
            'service_profile_id' => 'nullable|exists:service_profiles,id',
            'router_id'          => 'nullable|exists:routers,id',
            'pppoe_username'     => 'nullable|string|unique:pppoe_users,username',
            'pppoe_password'     => 'nullable|string',
            'reseller_id'        => 'nullable|exists:users,id',
            'branch_id'          => 'nullable|exists:branches,id',
        ]);

        try {
            $code = $this->customer_code;
            if (empty($code)) {
                $code = \App\Services\CRM\CustomerCodeGenerator::generate();
            }

            $ownershipData = [
                'reseller_id' => $this->reseller_id ?: null,
                'branch_id'   => $this->branch_id   ?: null,
            ];

            // Jika user memilih Paket Layanan → buat Customer + Layanan aktif
            if ($this->service_profile_id) {
                $this->validate([
                    'pppoe_username' => 'required|string|unique:pppoe_users,username',
                    'pppoe_password' => 'required|string',
                ], [
                    'pppoe_username.required' => 'Username PPPoE wajib diisi jika Anda memilih Paket Layanan.',
                    'pppoe_password.required' => 'Password PPPoE wajib diisi jika Anda memilih Paket Layanan.',
                ]);

                $result = $provisioningService->activatePPPoEService(array_merge([
                    'name'               => $this->name,
                    'customer_code'      => $code,
                    'phone'              => $this->phone,
                    'email'              => $this->email,
                    'address'            => $this->address,
                    'username'           => $this->pppoe_username,
                    'password'           => $this->pppoe_password,
                    'service_profile_id' => $this->service_profile_id,
                    'router_id'          => $this->router_id,
                    'status'             => $this->status,
                    'notes'              => json_encode(['lat' => $this->latitude, 'lng' => $this->longitude]),
                ], $ownershipData), auth()->id());

                // Update reseller/branch setelah customer terbuat
                if ($result['customer'] ?? null) {
                    $result['customer']->update($ownershipData);
                }

                session()->flash('success', 'Customer berhasil dibuat beserta layanan aktif!');
            } else {
                // Mode Prospect: hanya buat data Customer (belum ada layanan)
                $customer = \App\Models\CRM\Customer::create(array_merge([
                    'code'       => $code,
                    'name'       => $this->name,
                    'phone'      => $this->phone,
                    'email'      => $this->email,
                    'address'    => $this->address,
                    'status'     => 'active',
                    'latitude'   => $this->latitude,
                    'longitude'  => $this->longitude,
                    'notes'      => $this->notes ?: null,
                    'created_by' => auth()->id(),
                ], $ownershipData));

                session()->flash('success', 'Customer (Calon Pelanggan) berhasil disimpan!');
            }

            $this->dispatch('customer-created');
            return redirect()->route('crm.customers.index');
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Customer create failed: ' . $e->getMessage());
            session()->flash('error', 'Gagal menyimpan: ' . $e->getMessage());
        }
    }

    public function render()
    {
        $serviceProfiles = ServiceProfile::active()->get();
        $routers         = Router::active()->get();

        // Daftar Reseller (user dengan role reseller)
        $resellers = \App\Models\User::whereHas('roles', fn($q) => $q->where('name', 'reseller'))
            ->orderBy('name')
            ->get(['id', 'name', 'email']);

        // Daftar Branch/Area
        $branches = \App\Models\Master\Branch::where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name', 'code']);

        return view('livewire.crm.customer.create', compact('serviceProfiles', 'routers', 'resellers', 'branches'));
    }
}
