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
    public ?string $sn = null;

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
        } elseif (!empty($this->sn)) {
            $cleaned = strtoupper(trim($this->sn));
            $foundOnu = Onu::withoutGlobalScopes()
                ->where('serial_number', 'like', "%{$cleaned}%")
                ->orWhere('mac_address', 'like', "%{$cleaned}%")
                ->first();
            if ($foundOnu) {
                $this->selectOnu($foundOnu->id);
            } else {
                $ad = \App\Models\ACS\ACSDevice::withoutGlobalScopes()
                    ->where('serial_number', 'like', "%{$cleaned}%")
                    ->orWhere('mac_address', 'like', "%{$cleaned}%")
                    ->first();
                if ($ad) {
                    try {
                        $o = Onu::create([
                            'serial_number' => $ad->serial_number,
                            'mac_address'   => $ad->mac_address,
                            'model'         => $ad->model ?: 'ONT',
                            'status'        => $ad->status ?: 'active',
                            'rx_power_dbm'  => $ad->signal,
                            'olt_id'        => $ad->olt_id ?: \App\Models\ISP\Olt::first()?->id,
                            'customer_id'   => $ad->customer_id,
                        ]);
                        $this->selectOnu($o->id);
                    } catch (\Throwable) {}
                }
            }
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

        $candidates = [$q, strtoupper($q)];
        $upper = strtoupper($q);
        if (str_starts_with($upper, 'HWTC')) {
            $candidates[] = '48575443' . substr($upper, 4);
        } elseif (str_starts_with($upper, '48575443')) {
            $candidates[] = 'HWTC' . substr($upper, 8);
        } elseif (str_starts_with($upper, 'ZTEG')) {
            $candidates[] = '5A544547' . substr($upper, 4);
        } elseif (str_starts_with($upper, '5A544547')) {
            $candidates[] = 'ZTEG' . substr($upper, 8);
        }

        $results = Onu::withoutGlobalScopes()
            ->with([
                'customerService' => fn($sq) => $sq->withoutGlobalScopes(),
                'customerService.customer' => fn($cq) => $cq->withoutGlobalScopes(),
                'customerService.serviceProfile' => fn($pq) => $pq->withoutGlobalScopes(),
                'customerService.pppoeUser' => fn($puq) => $puq->withoutGlobalScopes(),
                'acsDevice' => fn($aq) => $aq->withoutGlobalScopes(),
                'olt', 'odp'
            ])
            ->where(function ($sub) use ($q, $candidates) {
                foreach ($candidates as $cand) {
                    $sub->orWhere('serial_number', 'like', "%{$cand}%")
                        ->orWhere('mac_address', 'like', "%{$cand}%");
                }
                $sub->orWhereHas('customerService', function ($csq) use ($q) {
                    $csq->withoutGlobalScopes()->whereHas('customer', function ($cq) use ($q) {
                        $cq->withoutGlobalScopes()->where('name', 'like', "%{$q}%")
                           ->orWhere('phone', 'like', "%{$q}%")
                           ->orWhere('code', 'like', "%{$q}%");
                    })
                    ->orWhereHas('pppoeUser', function ($pu) use ($q) {
                        $pu->withoutGlobalScopes()->where('username', 'like', "%{$q}%");
                    });
                });
            })
            ->limit(8)
            ->get();

        // Fallback pencarian langsung ke ACSDevice jika belum terindeks di tabel Onu
        if ($results->isEmpty()) {
            $acsMatches = \App\Models\ACS\ACSDevice::withoutGlobalScopes()
                ->with(['customerService.customer', 'customerService.serviceProfile', 'customerService.pppoeUser', 'onu'])
                ->where(function ($aq) use ($candidates) {
                    foreach ($candidates as $cand) {
                        $aq->orWhere('serial_number', 'like', "%{$cand}%")
                           ->orWhere('mac_address', 'like', "%{$cand}%");
                    }
                })
                ->limit(5)
                ->get();

            foreach ($acsMatches as $ad) {
                if ($ad->onu) {
                    $results->push($ad->onu);
                } else {
                    $o = Onu::withoutGlobalScopes()->where('serial_number', $ad->serial_number)->first();
                    if (!$o && !empty($ad->serial_number)) {
                        try {
                            $o = Onu::create([
                                'serial_number' => $ad->serial_number,
                                'mac_address'   => $ad->mac_address,
                                'model'         => $ad->model ?: 'ONT',
                                'status'        => $ad->status ?: 'active',
                                'rx_power_dbm'  => $ad->signal,
                                'olt_id'        => $ad->olt_id ?: \App\Models\ISP\Olt::first()?->id,
                                'customer_id'   => $ad->customer_id,
                            ]);
                        } catch (\Throwable) {}
                    }
                    if ($o) {
                        $results->push($o);
                    }
                }
            }
        }

        $this->searchResults = $results->map(function ($o) {
            $cs = $o->customerService;
            if (!$cs && $o->acsDevice?->customer_service_id) {
                $cs = CustomerService::withoutGlobalScopes()
                    ->with(['customer' => fn($q) => $q->withoutGlobalScopes(), 'serviceProfile' => fn($q) => $q->withoutGlobalScopes(), 'pppoeUser' => fn($q) => $q->withoutGlobalScopes()])
                    ->find($o->acsDevice->customer_service_id);
            }
            $cust = $cs?->customer ?? ($o->customer_id ? Customer::withoutGlobalScopes()->find($o->customer_id) : null);
            return [
                'onu_id' => $o->id,
                'serial_number' => $o->serial_number,
                'model' => $o->model ?: 'ONT',
                'mac_address' => $o->mac_address ?: '-',
                'customer_name' => $cust?->name ?? 'Belum Terikat Pelanggan',
                'customer_code' => $cust?->code ?? ('ID #' . ($cust?->id ?? '-')),
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
        $onu = Onu::withoutGlobalScopes()
            ->with(['customerService.customer', 'customerService.serviceProfile', 'customerService.pppoeUser', 'olt', 'odp', 'vendor', 'acsDevice'])
            ->find($onuId);

        if (!$onu) {
            $this->selectedOnuId = null;
            $this->onu = null;
            $this->errorMessage = "Perangkat ONT ID #{$onuId} tidak ditemukan.";
            return;
        }

        $this->selectedOnuId = $onu->id;
        $this->onu = $onu;

        // Resolusi CustomerService tanpa terhalang isolasi branch/reseller
        $this->customerService = CustomerService::withoutGlobalScopes()
            ->with([
                'customer' => fn($q) => $q->withoutGlobalScopes(),
                'serviceProfile' => fn($q) => $q->withoutGlobalScopes(),
                'pppoeUser' => fn($q) => $q->withoutGlobalScopes(),
            ])
            ->where('onu_id', $onu->id)
            ->latest('id')
            ->first();

        if (!$this->customerService && $onu->acsDevice?->customer_service_id) {
            $this->customerService = CustomerService::withoutGlobalScopes()
                ->with([
                    'customer' => fn($q) => $q->withoutGlobalScopes(),
                    'serviceProfile' => fn($q) => $q->withoutGlobalScopes(),
                    'pppoeUser' => fn($q) => $q->withoutGlobalScopes(),
                ])
                ->find($onu->acsDevice->customer_service_id);
        }

        $this->customer = $this->customerService?->customer
            ?? ($onu->customer_id ? Customer::withoutGlobalScopes()->find($onu->customer_id) : null)
            ?? ($onu->acsDevice?->resolved_customer ?? null);

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
        return view('livewire.isp.technician.diagnostic.index', [
            'selectedOnuId'       => $this->selectedOnuId,
            'onu'                 => $this->onu,
            'customer'            => $this->customer,
            'customerService'     => $this->customerService,
            'ticket'              => $this->ticket,
            'diagnostics'         => $this->diagnostics,
            'isLoading'           => $this->isLoading,
            'searchQuery'         => $this->searchQuery,
            'searchResults'       => $this->searchResults,
            'showRebootModal'     => $this->showRebootModal,
            'isRebooting'         => $this->isRebooting,
            'rebootStatusMessage' => $this->rebootStatusMessage,
            'rebootVerified'      => $this->rebootVerified,
            'showWifiModal'       => $this->showWifiModal,
            'wifi_ssid'           => $this->wifi_ssid,
            'wifi_password'       => $this->wifi_password,
            'isSavingWifi'        => $this->isSavingWifi,
            'successMessage'      => $this->successMessage,
            'errorMessage'        => $this->errorMessage,
        ]);
    }
}
