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
        $customerId = auth()->user()->customer?->id ?? 0;
        // Load customer's active PPPoE services with ONUs
        $this->customerServices = CustomerService::where('customer_id', $customerId)
            ->where('status', 'active')
            ->whereNotNull('onu_id')
            ->with('onu')
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
            $selfService = app(CustomerSelfServiceService::class);
            $credentials = $selfService->getOnuWifiCredentials(auth()->user()->customer?->id ?? 0, $this->onuId);
            $devices = $selfService->getOnuConnectedDevices(auth()->user()->customer?->id ?? 0, $this->onuId);
            
            $this->liveSsid = $credentials['ssid'] ?? 'Tidak diketahui';
            $this->livePassword = $credentials['password'] ?? 'Tidak diketahui';
            $this->connectedDevices = $devices;
            
            // Pre-fill the form with current values
            $this->newSsid = $this->liveSsid !== 'Tidak diketahui' ? $this->liveSsid : '';
            // We don't pre-fill password for security and to allow empty string = no change
            $this->newPassword = '';
        } catch (\Exception $e) {
            $this->liveSsid = 'Gagal memuat';
            $this->livePassword = 'Gagal memuat';
        }
        
        $this->isLoadingLive = false;
    }

    public function savePassword(CustomerSelfServiceService $selfService)
    {
        $this->validate();
        
        $result = $selfService->changeOnuWifiCredentials(
            auth()->user()->customer?->id ?? 0, 
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
