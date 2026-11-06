<?php

namespace App\Livewire\Isp\Technician\Diagnostic;

use App\Models\CRM\Customer;
use App\Models\Customer\CustomerService;
use App\Models\ISP\Onu;
use App\Models\Support\Ticket;
use App\Services\ISP\GenieAcsDiagnosticService;
use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

#[Layout('layouts.technician-app')]
class Index extends Component
{
    // URL Parameters
    #[Url]
    public ?int $onu_id = null;

    #[Url]
    public ?int $customer_id = null;

    #[Url]
    public ?int $ticket_id = null;

    // Search
    public string $searchQuery = '';
    public array $searchResults = [];

    // Selected Target Data
    public ?int $selectedOnuId = null;
    public ?Onu $onu = null;
    public ?Customer $customer = null;
    public ?CustomerService $customerService = null;
    public ?Ticket $ticket = null;

    // Live Diagnostics State
    public ?array $diagnostics = null;
    public bool $isLoading = false;
    public ?string $errorMessage = null;
    public ?string $successMessage = null;

    // Reboot Action State
    public bool $showRebootModal = false;
    public bool $isRebooting = false;
    public ?string $rebootStatusMessage = null;
    public bool $rebootVerified = false;
    public ?string $priorLastInform = null;
    public ?int $priorUptime = null;

    // Wi-Fi Action State
    public bool $showWifiModal = false;
    public string $wifi_ssid = '';
    public string $wifi_password = '';
    public bool $isSavingWifi = false;

    public function mount()
    {
        if ($this->onu_id) {
            $this->selectOnu($this->onu_id);
        } elseif ($this->customer_id) {
            $this->selectCustomer($this->customer_id);
        } elseif ($this->ticket_id) {
            $this->loadTicket($this->ticket_id);
        }
    }

    public function updatedSearchQuery()
    {
        $q = trim($this->searchQuery);
        if (strlen($q) < 2) {
            $this->searchResults = [];
            return;
        }

        $results = Onu::withoutGlobalScopes()
            ->with(['customerService.customer', 'customerService.serviceProfile', 'customerService.pppoeUser', 'olt', 'odp'])
            ->where(function ($sub) use ($q) {
                $sub->where('serial_number', 'like', "%{$q}%")
                    ->orWhere('mac_address', 'like', "%{$q}%")
                    ->orWhereHas('customerService.customer', function ($cq) use ($q) {
                        $cq->where('name', 'like', "%{$q}%")
                           ->orWhere('phone', 'like', "%{$q}%")
                           ->orWhere('customer_code', 'like', "%{$q}%");
                    })
                    ->orWhereHas('customerService.pppoeUser', function ($pu) use ($q) {
                        $pu->where('username', 'like', "%{$q}%");
                    });
            })
            ->limit(8)
            ->get();

        $this->searchResults = $results->map(function ($o) {
            $cs = $o->customerService;
            $cust = $cs?->customer;
            return [
                'onu_id' => $o->id,
                'serial_number' => $o->serial_number,
                'model' => $o->model ?: 'ONT',
                'mac_address' => $o->mac_address ?: '-',
                'customer_name' => $cust?->name ?? 'Belum Terikat Pelanggan',
                'customer_code' => $cust?->customer_code ?? ('ID #' . $cust?->id),
                'pppoe_username' => $cs?->pppoeUser?->username ?? '-',
                'package' => $cs?->serviceProfile?->name ?? 'Layanan',
                'status' => $o->status,
                'rx_power' => $o->rx_power_dbm,
            ];
        })->toArray();
    }

    public function loadTicket(int $ticketId)
    {
        $this->ticket = Ticket::withoutGlobalScopes()->with(['customer.customerServices.onu'])->find($ticketId);
        if ($this->ticket && $this->ticket->customer_id) {
            $this->selectCustomer($this->ticket->customer_id);
        }
    }

    public function selectCustomer(int $customerId)
    {
        $this->customer = Customer::withoutGlobalScopes()->find($customerId);
        if (!$this->customer) return;

        $cs = CustomerService::withoutGlobalScopes()
            ->where('customer_id', $customerId)
            ->whereNotNull('onu_id')
            ->latest('id')
            ->first();

        if ($cs && $cs->onu_id) {
            $this->selectOnu($cs->onu_id);
        }
    }

    public function selectOnu(int $onuId)
    {
        $this->selectedOnuId = $onuId;
        $this->onu = Onu::withoutGlobalScopes()
            ->with(['customerService.customer', 'customerService.serviceProfile', 'customerService.pppoeUser', 'olt', 'odp', 'vendor', 'acsDevice'])
            ->find($onuId);

        if (!$this->onu) {
            $this->errorMessage = "Perangkat ONT ID #{$onuId} tidak ditemukan.";
            return;
        }

        $this->customerService = $this->onu->customerService;
        $this->customer = $this->customerService?->customer;
        $this->searchResults = [];
        $this->searchQuery = '';

        $this->loadDiagnostics(false);
    }

    public function loadDiagnostics(bool $forceFresh = false)
    {
        if (!$this->onu) return;

        $this->isLoading = true;
        $this->errorMessage = null;
        $this->successMessage = null;

        $service = app(GenieAcsDiagnosticService::class);

        try {
            $service->authorizeTechnician($this->onu, Auth::user());
            $this->diagnostics = $service->getLiveDiagnostics($this->onu, $forceFresh);

            $this->wifi_ssid = (string) ($this->diagnostics['wifi']['ssid_24'] !== 'Tidak Didukung'
                ? $this->diagnostics['wifi']['ssid_24']
                : ($this->onu->wifi_ssid ?: ''));
            $this->wifi_password = '';

            if ($forceFresh) {
                $this->successMessage = "Data diagnostik berhasil diperbarui langsung dari perangkat.";
            }
        } catch (\Throwable $e) {
            Log::warning("GenieACS Diagnostic error on ONU {$this->onu->id}: " . $e->getMessage());
            $this->errorMessage = "Gagal membaca diagnostik ONT: " . $e->getMessage();
            $this->diagnostics = null;
        } finally {
            $this->isLoading = false;
        }
    }

    public function refreshSignal()
    {
        if (!$this->onu) return;

        $this->isLoading = true;
        $this->errorMessage = null;
        $this->successMessage = null;

        $service = app(GenieAcsDiagnosticService::class);

        try {
            $result = $service->refreshParameters($this->onu, Auth::user());
            if ($result['status'] === 'throttled') {
                $this->errorMessage = $result['message'];
                return;
            }

            $this->diagnostics = $result['diagnostics'];
            $this->successMessage = "Parameter sinyal ONT berhasil di-refresh dari GenieACS.";
        } catch (\Throwable $e) {
            $this->errorMessage = "Gagal refresh sinyal: " . $e->getMessage();
        } finally {
            $this->isLoading = false;
        }
    }

    public function openRebootModal()
    {
        $this->showRebootModal = true;
        $this->isRebooting = false;
        $this->rebootVerified = false;
        $this->rebootStatusMessage = null;
    }

    public function executeReboot()
    {
        if (!$this->onu) return;

        $this->isRebooting = true;
        $this->errorMessage = null;
        $this->successMessage = null;

        $service = app(GenieAcsDiagnosticService::class);

        try {
            $res = $service->rebootOnt($this->onu, Auth::user());

            if ($res['status'] === 'throttled') {
                $this->errorMessage = $res['message'];
                $this->showRebootModal = false;
                $this->isRebooting = false;
                return;
            }

            $this->priorLastInform = $res['prior_last_inform'] ?? null;
            $this->priorUptime = $res['prior_uptime'] ?? null;
            $this->rebootStatusMessage = $res['message'];
            $this->successMessage = $res['message'];
        } catch (\Throwable $e) {
            $this->errorMessage = "Perintah reboot gagal: " . $e->getMessage();
            $this->showRebootModal = false;
        } finally {
            $this->isRebooting = false;
        }
    }

    public function verifyReboot()
    {
        if (!$this->onu) return;

        $service = app(GenieAcsDiagnosticService::class);

        try {
            $res = $service->verifyReboot($this->onu, $this->priorLastInform, $this->priorUptime);
            $this->rebootVerified = $res['verified'] ?? false;
            $this->rebootStatusMessage = $res['message'] ?? '';

            if ($this->rebootVerified) {
                $this->successMessage = $res['message'];
                $this->diagnostics = $service->getLiveDiagnostics($this->onu, true);
            }
        } catch (\Throwable $e) {
            $this->rebootStatusMessage = "Pengecekan reboot gagal: " . $e->getMessage();
        }
    }

    public function openWifiModal()
    {
        $this->showWifiModal = true;
        $this->wifi_password = '';
    }

    public function saveWifi()
    {
        if (!$this->onu) return;

        $this->validate([
            'wifi_ssid' => 'required|string|min:1|max:32',
            'wifi_password' => 'nullable|string|min:8|max:63',
        ], [
            'wifi_ssid.required' => 'Nama Wi-Fi (SSID) wajib diisi.',
            'wifi_password.min' => 'Password Wi-Fi minimal 8 karakter.',
        ]);

        $this->isSavingWifi = true;
        $this->errorMessage = null;
        $this->successMessage = null;

        $service = app(GenieAcsDiagnosticService::class);

        try {
            $res = $service->updateWifi($this->onu, [
                'ssid_24' => $this->wifi_ssid,
                'password_24' => $this->wifi_password ?: null,
            ], Auth::user());

            if (($res['status'] ?? '') === 'throttled') {
                $this->errorMessage = $res['message'];
                return;
            }

            $this->showWifiModal = false;
            $this->wifi_password = '';
            $this->successMessage = $res['message'] ?? 'Konfigurasi Wi-Fi berhasil dikirim ke ONT.';

            // Refresh data diagnostik
            $this->diagnostics = $service->getLiveDiagnostics($this->onu, true);
        } catch (\Throwable $e) {
            $this->errorMessage = "Gagal mengubah Wi-Fi: " . $e->getMessage();
        } finally {
            $this->isSavingWifi = false;
        }
    }

    public function resetSelection()
    {
        $this->reset([
            'selectedOnuId', 'onu', 'customer', 'customerService', 'ticket',
            'diagnostics', 'errorMessage', 'successMessage', 'showRebootModal', 'showWifiModal'
        ]);
    }

    public function render()
    {
        return view('livewire.isp.technician.diagnostic.index');
    }
}
