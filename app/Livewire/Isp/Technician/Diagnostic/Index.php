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

    // Link Customer Modal State
    public bool $showLinkCustomerModal = false;
    public string $customerSearchQuery = '';
    public array $customerSearchResults = [];

    // OLT Selection & Sync State (Untuk ONT baru colok di lapangan)
    #[Url]
    public ?int $selectedOltId = null;
    public bool $isSyncingOlt = false;
    public array $newlySyncedOnus = [];

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

        if (!$this->selectedOltId) {
            $this->selectedOltId = $this->onu?->olt_id 
                ?? \App\Models\ISP\Olt::withoutGlobalScopes()->where('status', '!=', 'inactive')->value('id')
                ?? \App\Models\ISP\Olt::withoutGlobalScopes()->value('id');
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

            // Verifikasi status koneksi aktual (apakah benar-benar sedang online di OLT / GenieACS)
            $isLive = false;
            if (in_array(strtolower((string)$o->status), ['active', 'online'])) {
                if ($o->rx_power_dbm !== null && (float)$o->rx_power_dbm < 0 && (float)$o->rx_power_dbm > -40) {
                    $isLive = true;
                } elseif (!empty($o->last_seen_at) && \Carbon\Carbon::parse($o->last_seen_at)->diffInMinutes(now()) <= 15) {
                    $isLive = true;
                } elseif (!empty($o->acsDevice?->last_inform) && \Carbon\Carbon::parse($o->acsDevice->last_inform)->diffInMinutes(now()) <= 15) {
                    $isLive = true;
                }
            }

            $displayStatus = $isLive ? 'online' : 'offline';

            return [
                'onu_id' => $o->id,
                'serial_number' => $o->serial_number,
                'model' => $o->model ?: 'ONT',
                'mac_address' => $o->mac_address ?: '-',
                'customer_name' => $cust?->name ?? 'Belum Terikat Pelanggan',
                'customer_code' => $cust?->code ?? ('ID #' . ($cust?->id ?? '-')),
                'pppoe_username' => $cs?->pppoeUser?->username ?? '-',
                'package' => $cs?->serviceProfile?->name ?? 'Layanan',
                'status' => $displayStatus,
                'rx_power' => $isLive ? $o->rx_power_dbm : null,
            ];
        })->toArray();
    }

    public function deleteOrphanOnu(int $onuId): void
    {
        $onu = Onu::withoutGlobalScopes()->with('customerService')->find($onuId);
        if (!$onu) return;

        if ($onu->customer_id || $onu->customerService) {
            $this->errorMessage = "Tidak dapat menghapus: Modem ini terikat dengan data pelanggan aktif.";
            return;
        }

        $sn = $onu->serial_number;
        try {
            if (!empty($sn)) {
                \App\Models\ACS\ACSDevice::withoutGlobalScopes()
                    ->where('serial_number', $sn)
                    ->whereNull('customer_service_id')
                    ->forceDelete();
            }
            $onu->forceDelete();
            $this->searchResults = array_values(array_filter($this->searchResults, fn($r) => $r['onu_id'] !== $onuId));
            $this->successMessage = "Data modem hantu (SN: {$sn}) berhasil dihapus permanen dari database.";
        } catch (\Throwable $e) {
            $this->errorMessage = "Gagal menghapus modem: " . $e->getMessage();
        }
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
        if ($onu->olt_id) {
            $this->selectedOltId = $onu->olt_id;
        }

        // Resolusi multi-layer data pelanggan
        $this->resolveCustomerForOnu($onu);

        $this->searchResults = [];
        $this->searchQuery = '';

        $this->loadDiagnostics(false);
    }

    public function resolveCustomerForOnu(Onu $onu): void
    {
        // 1. Cek langsung dari relasi CustomerService pada ONU
        $cs = CustomerService::withoutGlobalScopes()
            ->with([
                'customer' => fn($q) => $q->withoutGlobalScopes(),
                'serviceProfile' => fn($q) => $q->withoutGlobalScopes(),
                'pppoeUser' => fn($q) => $q->withoutGlobalScopes(),
            ])
            ->where('onu_id', $onu->id)
            ->latest('id')
            ->first();

        // 2. Cek via ACSDevice relation / tabel acs_devices
        if (!$cs) {
            $acsId = $onu->acsDevice?->customer_service_id;
            if (!$acsId && !empty($onu->serial_number)) {
                $acsId = \App\Models\ACS\ACSDevice::withoutGlobalScopes()
                    ->where('serial_number', $onu->serial_number)
                    ->orWhere('mac_address', $onu->mac_address)
                    ->value('customer_service_id');
            }
            if ($acsId) {
                $cs = CustomerService::withoutGlobalScopes()
                    ->with([
                        'customer' => fn($q) => $q->withoutGlobalScopes(),
                        'serviceProfile' => fn($q) => $q->withoutGlobalScopes(),
                        'pppoeUser' => fn($q) => $q->withoutGlobalScopes(),
                    ])
                    ->find($acsId);
            }
        }

        // 3. Cek via customer_id di ONU
        if (!$cs && !empty($onu->customer_id)) {
            $cs = CustomerService::withoutGlobalScopes()
                ->with([
                    'customer' => fn($q) => $q->withoutGlobalScopes(),
                    'serviceProfile' => fn($q) => $q->withoutGlobalScopes(),
                    'pppoeUser' => fn($q) => $q->withoutGlobalScopes(),
                ])
                ->where('customer_id', $onu->customer_id)
                ->latest('id')
                ->first();
        }

        // 4. Cek via MAC Address di PPPoEUser
        if (!$cs && !empty($onu->mac_address)) {
            $cleanMac = strtoupper(preg_replace('/[^A-F0-9]/i', '', (string)$onu->mac_address));
            $formattedMac = strlen($cleanMac) === 12 ? implode(':', str_split($cleanMac, 2)) : $onu->mac_address;

            $pppoe = \App\Models\ISP\PPPoEUser::withoutGlobalScopes()
                ->where(function ($mq) use ($cleanMac, $formattedMac) {
                    $mq->where('mac_address', $formattedMac)
                       ->orWhere('mac_address', $cleanMac)
                       ->orWhere('mac_address', 'like', "%{$cleanMac}%");
                })
                ->latest('id')
                ->first();

            if ($pppoe && $pppoe->customer_service_id) {
                $cs = CustomerService::withoutGlobalScopes()
                    ->with([
                        'customer' => fn($q) => $q->withoutGlobalScopes(),
                        'serviceProfile' => fn($q) => $q->withoutGlobalScopes(),
                        'pppoeUser' => fn($q) => $q->withoutGlobalScopes(),
                    ])
                    ->find($pppoe->customer_service_id);
            }
        }

        if ($cs) {
            $this->customerService = $cs;
            $this->customer = $cs->customer;

            // Auto-link ONU ke CustomerService jika belum terhubung
            if (empty($cs->onu_id)) {
                try {
                    $cs->update(['onu_id' => $onu->id]);
                } catch (\Throwable) {}
            }
            if (empty($onu->customer_id) && !empty($cs->customer_id)) {
                try {
                    $onu->update(['customer_id' => $cs->customer_id]);
                } catch (\Throwable) {}
            }
        } else {
            $this->customerService = null;
            $this->customer = $onu->customer_id ? Customer::withoutGlobalScopes()->find($onu->customer_id) : null;
        }
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

            // Cek apakah PPPoE TR-069 dapat membantu identifikasi customer yang belum tertaut
            if (!$this->customerService && !empty($this->diagnostics['pppoe_username']) && $this->diagnostics['pppoe_username'] !== '-') {
                $uname = trim($this->diagnostics['pppoe_username']);
                $matchedCs = CustomerService::withoutGlobalScopes()
                    ->with([
                        'customer' => fn($q) => $q->withoutGlobalScopes(),
                        'serviceProfile' => fn($q) => $q->withoutGlobalScopes(),
                        'pppoeUser' => fn($q) => $q->withoutGlobalScopes(),
                    ])
                    ->where('username', $uname)
                    ->orWhereHas('pppoeUser', fn($pu) => $pu->withoutGlobalScopes()->where('username', $uname))
                    ->first();

                if ($matchedCs) {
                    $this->customerService = $matchedCs;
                    $this->customer = $matchedCs->customer;

                    try {
                        $matchedCs->update(['onu_id' => $this->onu->id]);
                        if (empty($this->onu->customer_id)) {
                            $this->onu->update(['customer_id' => $matchedCs->customer_id]);
                        }
                    } catch (\Throwable) {}
                }
            }

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

    public function openLinkCustomerModal()
    {
        $this->customerSearchQuery = '';
        $this->customerSearchResults = [];
        $this->showLinkCustomerModal = true;
    }

    public function updatedCustomerSearchQuery()
    {
        $q = trim($this->customerSearchQuery);
        if (strlen($q) < 2) {
            $this->customerSearchResults = [];
            return;
        }

        $this->customerSearchResults = CustomerService::withoutGlobalScopes()
            ->with([
                'customer' => fn($cq) => $cq->withoutGlobalScopes(),
                'serviceProfile' => fn($sp) => $sp->withoutGlobalScopes(),
                'pppoeUser' => fn($pu) => $pu->withoutGlobalScopes(),
            ])
            ->where(function ($sub) use ($q) {
                $sub->whereHas('customer', function ($cq) use ($q) {
                    $cq->withoutGlobalScopes()->where('name', 'like', "%{$q}%")
                       ->orWhere('phone', 'like', "%{$q}%")
                       ->orWhere('code', 'like', "%{$q}%");
                })
                ->orWhere('username', 'like', "%{$q}%")
                ->orWhereHas('pppoeUser', function ($pu) use ($q) {
                    $pu->withoutGlobalScopes()->where('username', 'like', "%{$q}%");
                });
            })
            ->limit(10)
            ->get()
            ->map(fn($cs) => [
                'id' => $cs->id,
                'customer_id' => $cs->customer_id,
                'customer_name' => $cs->customer?->name ?? 'Pelanggan #' . $cs->customer_id,
                'customer_code' => $cs->customer?->code ?? ('ID #' . $cs->customer_id),
                'phone' => $cs->customer?->phone ?? '-',
                'pppoe_username' => $cs->pppoeUser?->username ?? $cs->username ?? '-',
                'package' => $cs->serviceProfile?->name ?? 'Layanan',
                'is_linked' => !empty($cs->onu_id),
            ])
            ->toArray();
    }

    public function linkCustomer(int $customerServiceId)
    {
        if (!$this->onu) return;

        $cs = CustomerService::withoutGlobalScopes()
            ->with([
                'customer' => fn($cq) => $cq->withoutGlobalScopes(),
                'serviceProfile' => fn($sp) => $sp->withoutGlobalScopes(),
                'pppoeUser' => fn($pu) => $pu->withoutGlobalScopes(),
            ])
            ->find($customerServiceId);

        if (!$cs) {
            $this->errorMessage = 'Data layanan pelanggan tidak ditemukan.';
            return;
        }

        try {
            $cs->update(['onu_id' => $this->onu->id]);
            $this->onu->update(['customer_id' => $cs->customer_id]);

            $ad = \App\Models\ACS\ACSDevice::withoutGlobalScopes()
                ->where('serial_number', $this->onu->serial_number)
                ->orWhere('mac_address', $this->onu->mac_address)
                ->first();
            if ($ad) {
                $ad->update(['customer_service_id' => $cs->id, 'onu_id' => $this->onu->id]);
            }

            $this->customerService = $cs;
            $this->customer = $cs->customer;
            $this->showLinkCustomerModal = false;
            $this->successMessage = "Berhasil menautkan ONT dengan pelanggan: " . ($cs->customer?->name ?? 'Pelanggan');
        } catch (\Throwable $e) {
            $this->errorMessage = 'Gagal menautkan pelanggan: ' . $e->getMessage();
        }
    }

    public function syncSelectedOlt()
    {
        if (!$this->selectedOltId) {
            $this->errorMessage = 'Silakan pilih OLT terlebih dahulu untuk disinkronisasi.';
            return;
        }

        $olt = \App\Models\ISP\Olt::withoutGlobalScopes()->find($this->selectedOltId);
        if (!$olt) {
            $this->errorMessage = 'Perangkat OLT tidak ditemukan.';
            return;
        }

        // Rate limit: 15 detik per OLT agar tidak membebani CPU perangkat OLT
        $lockKey = "rate_limit_olt_sync_{$olt->id}";
        if (\Illuminate\Support\Facades\Cache::has($lockKey)) {
            $this->errorMessage = "Sinkronisasi OLT {$olt->name} sedang dalam masa cooldown. Silakan tunggu 15 detik.";
            return;
        }
        \Illuminate\Support\Facades\Cache::put($lockKey, true, 15);

        $this->isSyncingOlt = true;
        $this->errorMessage = null;
        $this->successMessage = null;

        // Ambil daftar ID ONU lama sebelum sync untuk membedakan ONT yang baru terdeteksi
        $previousOnuIds = \App\Models\ISP\Onu::withoutGlobalScopes()
            ->where('olt_id', $olt->id)
            ->pluck('id')
            ->toArray();

        try {
            $poller = app(\App\Services\ISP\OltPollingService::class);
            $res = $poller->pollOlt($olt);

            if (isset($res['success']) && !$res['success']) {
                throw new \Exception($res['error'] ?? $res['message'] ?? 'Gagal menghubungi perangkat OLT.');
            }

            // Cari ONT yang baru masuk atau baru terupdate dari OLT ini
            $recentOnus = \App\Models\ISP\Onu::withoutGlobalScopes()
                ->where('olt_id', $olt->id)
                ->where(function ($q) use ($previousOnuIds) {
                    $q->whereNotIn('id', $previousOnuIds)
                      ->orWhere('updated_at', '>=', now()->subMinutes(2));
                })
                ->latest('updated_at')
                ->limit(6)
                ->get()
                ->map(fn($o) => [
                    'id' => $o->id,
                    'serial_number' => $o->serial_number,
                    'model' => $o->model ?: 'ONT',
                    'status' => $o->status,
                    'rx_power' => $o->rx_power_dbm,
                    'is_new' => !in_array($o->id, $previousOnuIds),
                ])
                ->toArray();

            $this->newlySyncedOnus = $recentOnus;

            $newCount = collect($recentOnus)->where('is_new', true)->count();
            $newMsg = $newCount > 0 ? " Ditemukan {$newCount} ONT baru terdeteksi!" : " Data ONT diperbarui dari OLT.";
            $this->successMessage = "✅ Sinkronisasi OLT {$olt->name} berhasil.{$newMsg}";

            // Jika belum ada target yang dipilih dan ada ONT baru, otomatis pilih yang terbaru
            if (!$this->selectedOnuId && !empty($recentOnus)) {
                $firstTarget = collect($recentOnus)->firstWhere('is_new', true) ?? $recentOnus[0];
                if (!empty($firstTarget['id'])) {
                    $this->selectOnu($firstTarget['id']);
                }
            } elseif ($this->selectedOnuId) {
                // Refresh data target yang sedang dibuka
                $this->selectOnu($this->selectedOnuId);
            }
        } catch (\Throwable $e) {
            $this->errorMessage = "Gagal sinkronisasi OLT {$olt->name}: " . $e->getMessage();
        } finally {
            $this->isSyncingOlt = false;
        }
    }

    public function resetSelection()
    {
        $this->reset([
            'selectedOnuId', 'onu', 'customer', 'customerService', 'ticket',
            'diagnostics', 'errorMessage', 'successMessage', 'showRebootModal', 'showWifiModal',
            'showLinkCustomerModal', 'customerSearchQuery', 'customerSearchResults'
        ]);
    }

    public function render()
    {
        $olts = \App\Models\ISP\Olt::withoutGlobalScopes()
            ->where('status', '!=', 'inactive')
            ->orderBy('name')
            ->get(['id', 'name', 'code', 'ip_address']);

        return view('livewire.isp.technician.diagnostic.index', [
            'olts'                    => $olts,
            'selectedOltId'           => $this->selectedOltId,
            'isSyncingOlt'            => $this->isSyncingOlt,
            'newlySyncedOnus'         => $this->newlySyncedOnus,
            'selectedOnuId'           => $this->selectedOnuId,
            'onu'                     => $this->onu,
            'customer'                => $this->customer,
            'customerService'         => $this->customerService,
            'ticket'                  => $this->ticket,
            'diagnostics'             => $this->diagnostics,
            'isLoading'               => $this->isLoading,
            'searchQuery'             => $this->searchQuery,
            'searchResults'           => $this->searchResults,
            'showRebootModal'         => $this->showRebootModal,
            'isRebooting'             => $this->isRebooting,
            'rebootStatusMessage'     => $this->rebootStatusMessage,
            'rebootVerified'          => $this->rebootVerified,
            'showWifiModal'           => $this->showWifiModal,
            'wifi_ssid'               => $this->wifi_ssid,
            'wifi_password'           => $this->wifi_password,
            'isSavingWifi'            => $this->isSavingWifi,
            'showLinkCustomerModal'   => $this->showLinkCustomerModal,
            'customerSearchQuery'     => $this->customerSearchQuery,
            'customerSearchResults'   => $this->customerSearchResults,
            'successMessage'          => $this->successMessage,
            'errorMessage'            => $this->errorMessage,
        ]);
    }
}
