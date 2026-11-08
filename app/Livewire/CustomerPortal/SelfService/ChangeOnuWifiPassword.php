<?php

namespace App\Livewire\CustomerPortal\SelfService;

use App\Models\Customer\CustomerService;
use App\Services\CustomerPortal\CustomerSelfServiceService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
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

    // Multi-WLAN & Advanced WiFi Properties (matching genieacs-mobile)
    public int $activeWlanIndex = 1; // 1 = 2.4 GHz, 5 = 5 GHz
    public string $securityMode = 'WPA_WPA2'; // OPEN, WPA, WPA2, WPA_WPA2
    public bool $isHidden = false;
    public bool $isEnabled = true;

    public $liveSsid = null;
    public $livePassword = null;
    public $connectedDevices = [];
    public $isLoadingLive = false;

    protected function rules()
    {
        return [
            'onuId' => 'required|integer',
            'newSsid' => 'required|string|min:3|max:32',
            'newPassword' => $this->securityMode === 'OPEN' ? 'nullable' : 'nullable|min:8',
        ];
    }

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
            ->with([
                'onu' => function ($q) {
                    $q->withoutGlobalScopes();
                },
                'acsDevice' => function ($q) {
                    $q->withoutGlobalScopes();
                }
            ])
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

    public function switchWlan(int $index)
    {
        if (!in_array($index, [1, 2, 5])) return;
        $this->activeWlanIndex = $index;
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
            $this->newPassword = '';
        } catch (\Exception $e) {
            Log::warning("fetchLiveCredentials fallback for ONU {$this->onuId}: " . $e->getMessage());
            $onu = \App\Models\ISP\Onu::withoutGlobalScopes()->find($this->onuId);
            $this->liveSsid = $onu?->wifi_ssid ?: 'Belum diatur';
            $this->livePassword = $onu?->wifi_password ?: 'Belum diatur';
            if (!in_array($this->liveSsid, ['Belum diatur', 'Tidak diketahui', 'Gagal memuat'])) {
                $this->newSsid = $this->liveSsid;
            }
        }
        
        $this->isLoadingLive = false;
    }

    /**
     * Simpan Nama SSID saja
     */
    public function saveSsidOnly(CustomerSelfServiceService $selfService)
    {
        $this->validate([
            'onuId' => 'required|integer',
            'newSsid' => 'required|string|min:3|max:32',
        ]);

        $customerId = $this->resolveCustomerId();

        $result = $selfService->changeOnuWifiCredentials(
            $customerId,
            $this->onuId,
            $this->newSsid,
            null, // password null = do not change
            $this->activeWlanIndex,
            $this->securityMode,
            $this->isHidden,
            $this->isEnabled
        );

        $this->handleResult($result, "Nama WiFi (SSID {$this->activeWlanIndex}) berhasil diubah menjadi \"{$this->newSsid}\"");
    }

    /**
     * Simpan Sandi & Mode Keamanan WiFi saja
     */
    public function saveSecurityOnly(CustomerSelfServiceService $selfService)
    {
        if ($this->securityMode !== 'OPEN' && strlen($this->newPassword) < 8) {
            $this->addError('newPassword', 'Password minimal 8 karakter untuk mode keamanan ini.');
            return;
        }

        $customerId = $this->resolveCustomerId();
        $targetSsid = !empty($this->newSsid) ? $this->newSsid : ($this->liveSsid ?: 'WiFi-Rumah');

        $result = $selfService->changeOnuWifiCredentials(
            $customerId,
            $this->onuId,
            $targetSsid,
            $this->securityMode === 'OPEN' ? '' : $this->newPassword,
            $this->activeWlanIndex,
            $this->securityMode,
            $this->isHidden,
            $this->isEnabled
        );

        $this->handleResult($result, "Pengaturan Keamanan & Sandi WiFi berhasil diperbarui");
    }

    /**
     * Simpan Semua Sekaligus
     */
    public function savePassword(CustomerSelfServiceService $selfService)
    {
        $this->validate();
        $customerId = $this->resolveCustomerId();

        $result = $selfService->changeOnuWifiCredentials(
            $customerId, 
            $this->onuId, 
            $this->newSsid, 
            empty($this->newPassword) ? null : $this->newPassword,
            $this->activeWlanIndex,
            $this->securityMode,
            $this->isHidden,
            $this->isEnabled
        );
        
        $this->handleResult($result, "Konfigurasi WiFi berhasil disimpan");
    }

    private function resolveCustomerId(): int
    {
        $customer = auth()->user()->customer;
        if (!$customer) {
            $customer = \App\Models\CRM\Customer::withoutGlobalScopes()->where('user_id', auth()->id())->first();
        }
        return $customer?->id ?? 0;
    }

    private function handleResult(array $result, string $successFallback)
    {
        $this->message = $result['message'] ?? ($result['success'] ? $successFallback : 'Gagal memperbarui konfigurasi WiFi');
        $this->messageType = $result['success'] ? 'success' : 'error';
        
        if ($result['success']) {
            $this->liveSsid = $this->newSsid;
            if (!empty($this->newPassword)) {
                $this->livePassword = $this->newPassword;
            }
            $this->newPassword = '';
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
