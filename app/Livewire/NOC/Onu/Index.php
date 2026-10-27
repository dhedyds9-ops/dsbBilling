<?php

namespace App\Livewire\NOC\Onu;

use App\Livewire\AdminComponent;
use App\Models\ISP\Onu;
use App\Models\ISP\Olt;
use App\Models\ISP\Odp;
use Livewire\WithPagination;
use Livewire\Attributes\Computed;

class Index extends AdminComponent
{
    use WithPagination;

    #[\Livewire\Attributes\Url]
    public string $search = '';
    
    #[\Livewire\Attributes\Url]
    public string $statusFilter = 'all';
    
    #[\Livewire\Attributes\Url]
    public string $oltFilter   = '';
    
    #[\Livewire\Attributes\Url]
    public string $ponFilter   = '';
    public string $sortField   = 'serial_number';
    public string $sortDirection = 'asc';
    #[\Livewire\Attributes\Url]
    public int $perPage = 25;

    public $editingOnuId = null;
    public $selectedCustomerId = '';

    #[Computed]
    public function allCustomers()
    {
        return \App\Models\CRM\Customer::select('id', 'name', 'code')->orderBy('name')->get();
    }

    public function editCustomer($onuId, $currentCustomerId = null)
    {
        $this->editingOnuId = $onuId;
        $this->selectedCustomerId = $currentCustomerId ?? '';
    }

    public function cancelEditCustomer()
    {
        $this->editingOnuId = null;
        $this->selectedCustomerId = '';
    }

    public function assignCustomer($onuId)
    {
        try {
            $onu = \App\Models\ISP\Onu::findOrFail($onuId);
            
            // Unassign current service if exists
            if ($onu->customerService) {
                $onu->customerService->onu_id = null;
                $onu->customerService->save();
            }

            if ($this->selectedCustomerId) {
                // Assign to the first active service of the selected customer
                $service = \App\Models\Customer\CustomerService::where('customer_id', $this->selectedCustomerId)
                    ->orderBy('id', 'desc')
                    ->first();
                
                if ($service) {
                    $service->onu_id = $onu->id;
                    $service->save();
                    session()->flash('success', 'ONU berhasil dipasangkan ke pelanggan ' . $service->customer->name);
                } else {
                    throw new \Exception('Pelanggan ini belum memiliki Layanan (Internet/Hotspot). Silakan buat layanan untuk pelanggan ini terlebih dahulu di menu Pelanggan.');
                }
            } else {
                session()->flash('success', 'ONU berhasil dilepas dari pelanggan');
            }
            
            $this->editingOnuId = null;
            $this->selectedCustomerId = '';
            
        } catch (\Throwable $e) {
            session()->flash('error', 'Gagal mengupdate pelanggan: ' . $e->getMessage());
        }
    }

    public function syncFromOlt(): void
    {
        if (!empty($this->oltFilter) && $this->oltFilter !== 'all') {
            $this->syncOlt((int) $this->oltFilter);
        } else {
            $this->syncAll();
        }
    }

    public function syncAll(): void
    {
        try {
            $olts = \App\Models\ISP\Olt::whereNotNull('ip_address')->get();
            if ($olts->isEmpty()) {
                session()->flash('info', 'Tidak ada OLT yang memiliki IP Address terdaftar.');
                return;
            }

            $service = app(\App\Services\ISP\OltPollingService::class);
            $successCount = 0;
            $failMessages = [];

            foreach ($olts as $olt) {
                try {
                    $res = $service->pollOlt($olt);
                    if (!empty($res['success'])) {
                        $successCount++;
                    } else {
                        $err = $res['error'] ?? $res['message'] ?? 'Gagal komunikasi OLT';
                        $failMessages[] = "{$olt->name}: {$err}";
                    }
                } catch (\Throwable $e) {
                    $failMessages[] = "{$olt->name}: " . $e->getMessage();
                }
            }

            if ($successCount > 0) {
                $msg = "Sinkronisasi ONU berhasil untuk {$successCount} OLT.";
                if (!empty($failMessages)) {
                    $msg .= " (Kendala: " . implode(' | ', $failMessages) . ")";
                    session()->flash('warning', $msg);
                } else {
                    session()->flash('success', $msg);
                }
            } else {
                session()->flash('error', "Gagal sinkronisasi OLT: " . implode(' | ', $failMessages));
            }
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('ONU syncAll failed: ' . $e->getMessage());
            session()->flash('error', 'Gagal sinkronisasi ONU: ' . $e->getMessage());
        }
    }

    public function syncOlt(int $oltId): void
    {
        try {
            $olt = \App\Models\ISP\Olt::findOrFail($oltId);
            $service = app(\App\Services\ISP\OltPollingService::class);
            $res = $service->pollOlt($olt);

            if (!empty($res['success'])) {
                session()->flash('success', "Sinkronisasi ONU dari OLT {$olt->name} berhasil diselesaikan!");
            } else {
                $err = $res['error'] ?? $res['message'] ?? 'Gagal komunikasi OLT';
                session()->flash('error', "Gagal sinkronisasi OLT {$olt->name}: {$err}");
            }
        } catch (\Throwable $e) {
            session()->flash('error', "Gagal sinkronisasi OLT: " . $e->getMessage());
        }
    }

    public function syncSingleOnu(int $onuId): void
    {
        try {
            $onu = Onu::with('olt')->findOrFail($onuId);
            if (!$onu->olt) {
                session()->flash('error', 'ONU ini tidak terhubung ke OLT manapun.');
                return;
            }

            $service = app(\App\Services\ISP\OltPollingService::class);
            $res = $service->pollOlt($onu->olt);

            if (!empty($res['success'])) {
                session()->flash('success', "Data ONU {$onu->serial_number} berhasil diperbarui dari OLT {$onu->olt->name}!");
            } else {
                $err = $res['error'] ?? $res['message'] ?? 'Gagal membaca status OLT';
                session()->flash('error', "Gagal sinkronisasi ONU: {$err}");
            }
        } catch (\Throwable $e) {
            session()->flash('error', 'Gagal sinkronisasi ONU: ' . $e->getMessage());
        }
    }

    public function configure(): void
    {
    }

    public function mount(): void
    {
        $this->activeModule = 'noc';
        $this->activePage   = 'onus';
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedStatusFilter(): void
    {
        $this->resetPage();
    }

    public function updatedOltFilter(): void
    {
        $this->resetPage();
    }

    public function resetFilters(): void
    {
        $this->search = '';
        $this->statusFilter = 'all';
        $this->oltFilter = '';
        $this->ponFilter = '';
        $this->resetPage();
    }

    public function sort(string $field): void
    {
        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortField     = $field;
            $this->sortDirection = 'asc';
        }
    }

    #[Computed]
    public function onus(): \Illuminate\Pagination\LengthAwarePaginator
    {
        $staleAt  = now()->subMinutes(5);
        $rxLow    = -27.0;
        $rxCrit   = -30.0;

        return Onu::withoutTrashed()
            ->with([
                'olt:id,name',
                'odp:id,name,code',
                'ponPort:id,name,port_number',
                'customerService:id,onu_id,customer_id',
                'customerService.customer:id,name,code',
                'customerService.pppoeUser:id,customer_service_id,static_ip,ip_allocation_id',
                'customerService.pppoeUser.ipAllocation:id,ip_address',
                'acsDevice:id,serial_number,ip_address,mac_address'
            ])
            ->select([
                'id', 'name', 'serial_number', 'mac_address', 'status',
                'rx_power_dbm', 'tx_power_dbm', 'temperature',
                'last_seen_at', 'olt_id', 'pon_port_id', 'odp_id',
                'pon_port',
            ])
            ->when($this->oltFilter, fn ($q) => $q->where('olt_id', $this->oltFilter))
            ->when($this->ponFilter, fn ($q) => $q->where('pon_port_id', $this->ponFilter))
            ->when($this->statusFilter === 'online', fn ($q) => $q->where('status', 'active'))
            ->when($this->statusFilter === 'offline', fn ($q) => $q->where('status', 'inactive'))
            ->when($this->statusFilter === 'los', fn ($q) => $q->where(function ($q) use ($rxCrit) {
                $q->where('status', 'los')->orWhere('rx_power_dbm', '<', $rxCrit);
            }))
            ->when($this->statusFilter === 'low_rx', fn ($q) => $q->whereBetween('rx_power_dbm', [$rxCrit, $rxLow]))
            ->when(!empty($this->search), function ($q) {
                $q->where(function ($sq) {
                    $sq->where('serial_number', 'like', "%{$this->search}%")
                       ->orWhere('name', 'like', "%{$this->search}%")
                       ->orWhere('mac_address', 'like', "%{$this->search}%")
                       ->orWhereHas('customerService.customer', function ($cq) {
                           $cq->where('name', 'like', "%{$this->search}%")
                              ->orWhere('code', 'like', "%{$this->search}%");
                       })
                       ->orWhereHas('acsDevice', function ($aq) {
                           $aq->where('ip_address', 'like', "%{$this->search}%");
                       })
                       ->orWhereHas('olt', function ($oq) {
                           $oq->where('name', 'like', "%{$this->search}%");
                       })
                       ->orWhereHas('odp', function ($odpq) {
                           $odpq->where('name', 'like', "%{$this->search}%");
                       })
                       ->orWhereHas('ponPort', function ($pq) {
                           $pq->where('name', 'like', "%{$this->search}%");
                       });
                });
            })
            ->when(
                in_array($this->sortField, ['serial_number', 'status', 'rx_power_dbm', 'last_seen_at', 'name']),
                fn ($q) => $q->orderBy($this->sortField, $this->sortDirection)
            )
            ->paginate($this->perPage === 0 ? 1000000 : $this->perPage);
    }

    #[Computed]
    public function olts(): \Illuminate\Database\Eloquent\Collection
    {
        return Olt::withoutTrashed()->select('id', 'name')->orderBy('name')->get();
    }

    #[Computed]
    public function summary(): array
    {
        $baseQuery = Onu::withoutTrashed()
            ->when($this->oltFilter, fn ($q) => $q->where('olt_id', $this->oltFilter))
            ->when($this->ponFilter, fn ($q) => $q->where('pon_port_id', $this->ponFilter));

        $total = (clone $baseQuery)->count();
        $online = (clone $baseQuery)->where('status', 'active')->count();

        $los = (clone $baseQuery)->where(function ($q) {
            $q->where('status', 'los')->orWhere('rx_power_dbm', '<', -30.0);
        })->count();

        $lowRx = (clone $baseQuery)->whereBetween('rx_power_dbm', [-30.0, -27.0])->count();
        $offline = max(0, $total - $online);

        return [
            'total'   => $total,
            'online'  => $online,
            'offline' => $offline,
            'los'     => $los,
            'low_rx'  => $lowRx,
        ];
    }

    public function getOnuStatus(Onu $onu): string
    {
        if ($onu->status === 'los' || ($onu->rx_power_dbm !== null && $onu->rx_power_dbm < -30)) return 'LOS';
        if ($onu->status === 'inactive') return 'OFFLINE';
        // If an ONU has no optical signal and has never registered on a PON port, it is OFFLINE
        if ($onu->rx_power_dbm === null && ($onu->pon_port === null || empty($onu->last_seen_at))) {
            return 'OFFLINE';
        }
        if ($onu->rx_power_dbm !== null && $onu->rx_power_dbm < -27) return 'LOW_RX';
        return 'ONLINE';
    }

    #[Computed]
    public function ponPorts()
    {
        if (empty($this->oltFilter) || $this->oltFilter === 'all') {
            return collect();
        }
        $query = \App\Models\ISP\PonPort::where('olt_id', $this->oltFilter);
        if (\Illuminate\Support\Facades\Schema::hasColumn('pon_ports', 'port_number')) {
            $query->orderBy('port_number', 'asc');
        } elseif (\Illuminate\Support\Facades\Schema::hasColumn('pon_ports', 'port_index')) {
            $query->orderBy('port_index', 'asc');
        } else {
            $query->orderBy('id', 'asc');
        }
        return $query->get();
    }

    #[\Livewire\Attributes\Layout('layouts.noc')]
    public function render()
    {
        return view('livewire.noc.onu.index', [
            'onus'    => $this->onus,
            'olts'    => $this->olts,
            'ponPorts' => $this->ponPorts,
            'summary' => $this->summary,
        ]);
    }
}








