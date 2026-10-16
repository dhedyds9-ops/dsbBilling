<?php

namespace App\Livewire\CustomerPortal\SelfService;

use App\Models\Customer\CustomerService;
use App\Services\CustomerPortal\CustomerSelfServiceService;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\Attributes\Layout;

#[Layout('layouts.customer-app')]
class ChangeOnuWifiPassword extends Component
{
    public $newSsid;
    public $newPassword;
    public $message;
    public $messageType;
    public $onuId;
    public $customerServices;

    public $liveSsid = null;
    public $livePassword = null;
    public $connectedDevices = [];
    public $isLoadingLive = false;

    protected $rules = [
        'onuId' => 'required|integer',
        'newSsid' => 'required|string|min:3|max:32',
        'newPassword' => 'nullable|min:8',
    ];

    public function mount()
    {
        $customer = auth()->user()->customer;
        if (!$customer) {
            $customer = \App\Models\CRM\Customer::withoutGlobalScopes()->where('user_id', auth()->id())->first();
        }
        $customerId = $customer?->id ?? 0;

        // Load customer's active PPPoE services with ONUs
        $this->customerServices = CustomerService::withoutGlobalScopes()
            ->where('customer_id', $customerId)
            ->whereNotNull('onu_id')
            ->whereNotIn('status', ['cancelled', 'terminated', 'deleted'])
            ->with(['onu' => function ($q) {
                $q->withoutGlobalScopes();
            }])
            ->get();
        
        // Set default ONU if available
        if ($this->customerServices->isNotEmpty()) {
            $this->onuId = $this->customerServices->first()->onu_id;
            $this->fetchLiveCredentials();
        }
    }

    public function updatedOnuId()
    {
        $this->fetchLiveCredentials();
    }

    public function fetchLiveCredentials()
    {
        if (!$this->onuId) return;

        $this->isLoadingLive = true;
        $this->liveSsid = null;
        $this->livePassword = null;
        $this->connectedDevices = [];
        $this->message = null;

        try {
            $customer = auth()->user()->customer;
            if (!$customer) {
                $customer = \App\Models\CRM\Customer::withoutGlobalScopes()->where('user_id', auth()->id())->first();
            }
            $customerId = $customer?->id ?? 0;

            $selfService = app(CustomerSelfServiceService::class);
            $credentials = $selfService->getOnuWifiCredentials($customerId, $this->onuId);
            
            try {
                $devices = $selfService->getOnuConnectedDevices($customerId, $this->onuId);
                $this->connectedDevices = $devices;
            } catch (\Throwable) {
                $this->connectedDevices = [];
            }
            
            $this->liveSsid = !empty($credentials['ssid']) ? $credentials['ssid'] : 'Belum diatur';
            $this->livePassword = !empty($credentials['password']) ? $credentials['password'] : 'Belum diatur';
            
            // Pre-fill the form with current values
            $this->newSsid = (!in_array($this->liveSsid, ['Belum diatur', 'Tidak diketahui', 'Gagal memuat'])) ? $this->liveSsid : '';
            // We don't pre-fill password for security and to allow empty string = no change
            $this->newPassword = '';
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::warning("fetchLiveCredentials fallback for ONU {$this->onuId}: " . $e->getMessage());
            $onu = \App\Models\ISP\Onu::withoutGlobalScopes()->find($this->onuId);
            $this->liveSsid = $onu?->wifi_ssid ?: 'Belum diatur';
            $this->livePassword = $onu?->wifi_password ?: 'Belum diatur';
            if (!in_array($this->liveSsid, ['Belum diatur', 'Tidak diketahui', 'Gagal memuat'])) {
                $this->newSsid = $this->liveSsid;
            }
        }
        
        $this->isLoadingLive = false;
    }

    public function savePassword(CustomerSelfServiceService $selfService)
    {
        $this->validate();
        
        $customer = auth()->user()->customer;
        if (!$customer) {
            $customer = \App\Models\CRM\Customer::withoutGlobalScopes()->where('user_id', auth()->id())->first();
        }
        $customerId = $customer?->id ?? 0;

        $result = $selfService->changeOnuWifiCredentials(
            $customerId, 
            $this->onuId, 
            $this->newSsid, 
            empty($this->newPassword) ? null : $this->newPassword
        );
        
        $this->message = $result['message'] ?? ($result['success'] ? 'Berhasil mengubah konfigurasi WiFi' : 'Gagal mengubah konfigurasi WiFi');
        $this->messageType = $result['success'] ? 'success' : 'error';
        
        if ($result['success']) {
            $this->liveSsid = $this->newSsid;
            if (!empty($this->newPassword)) {
                $this->livePassword = $this->newPassword;
            }
            $this->newPassword = ''; // Reset password field
            $this->dispatch('alert', type: 'success', message: $this->message);
        } else {
            $this->dispatch('alert', type: 'error', message: $this->message);
        }
    }

    public function render()
    {
        return view('livewire.customer-portal.self-service.change-onu-wifi-password');
    }
}
