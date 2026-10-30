<?php

namespace App\Livewire\ResellerPortal\Network\Onu;

use App\Livewire\AdminComponent;
use App\Models\CRM\Customer;
use App\Models\Customer\CustomerService;
use App\Models\ISP\Olt;
use App\Models\ISP\Onu;
use App\Models\ISP\PonPort;
use App\Services\Auth\ResellerScopeHelper;
use Illuminate\Support\Facades\Schema;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Url;
use Livewire\WithPagination;

class Index extends AdminComponent
{
    use WithPagination;

    #[Url]
    public string $search = '';

    #[Url]
    public string $statusFilter = 'all';

    #[Url]
    public string $oltFilter = '';

    #[Url]
    public string $ponFilter = '';

    #[Url]
    public int $perPage = 25;

    public string $sortField = 'serial_number';
    public string $sortDirection = 'asc';

    // Inline edit nama ONU
    public $editOnuId = null;
    public string $editOnuName = '';

    // Inline tautkan pelanggan milik reseller
    public $editingCustomerOnuId = null;
    public $selectedCustomerId = '';

    public function mount()
    {
        parent::mount();
        $this->activeModule = 'reseller';
        $this->activePage = 'network-onus';
        $this->breadcrumbs = [
            ['label' => 'Reseller Portal', 'url' => route('reseller-portal.dashboard')],
            ['label' => 'Network & GIS', 'url' => route('reseller-portal.network.map')],
            ['label' => 'Data ONU', 'url' => '#'],
        ];
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingStatusFilter(): void
    {
        $this->resetPage();
    }

    public function updatingOltFilter(): void
    {
        $this->ponFilter = '';
        $this->resetPage();
    }

    public function updatingPonFilter(): void
    {
        $this->resetPage();
    }

    public function updatingPerPage(): void
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
            $this->sortField = $field;
            $this->sortDirection = 'asc';
        }
    }

    #[Computed]
    public function myCustomers()
    {
        // Global scope HasResellerScope secara otomatis membatasi hanya ke pelanggan milik reseller ini
        return Customer::select('id', 'name', 'code')->orderBy('name')->get();
    }

    public function startEditName(int $id, string $currentName = ''): void
    {
        $this->editOnuId = $id;
        $this->editOnuName = $currentName;
    }

    public function saveName(): void
    {
        $this->validate([
            'editOnuName' => 'required|string|max:100',
        ]);

        $onu = Onu::forReseller()->findOrFail($this->editOnuId);
        $onu->name = trim($this->editOnuName);
        $onu->save();

        $this->editOnuId = null;
        session()->flash('success', 'Nama ONU berhasil diperbarui.');
    }

    public function cancelEditName(): void
    {
        $this->editOnuId = null;
        $this->editOnuName = '';
    }

    public function editCustomer(int $onuId, $currentCustomerId = null): void
    {
        $this->editingCustomerOnuId = $onuId;
        $this->selectedCustomerId = $currentCustomerId ?? '';
    }

    public function cancelEditCustomer(): void
    {
        $this->editingCustomerOnuId = null;
        $this->selectedCustomerId = '';
    }

    public function assignCustomer(int $onuId): void
    {
        try {
            $onu = Onu::forReseller()->findOrFail($onuId);
            $user = auth()->user();
            $resellerId = $user?->getEffectiveResellerId() ?: $user?->id;

            // Lepaskan service milik reseller ini jika sebelumnya terpasang
            $existingCs = CustomerService::withoutGlobalScopes(['reseller_isolation', 'branch_isolation'])
                ->where('onu_id', $onu->id)
                ->first();

            if ($existingCs) {
                $existingCust = Customer::withoutGlobalScopes(['reseller_isolation', 'branch_isolation'])->find($existingCs->customer_id);
                $ownedByOther = (!empty($existingCs->reseller_id) && (int) $existingCs->reseller_id !== (int) $resellerId)
                    || ($existingCust && !empty($existingCust->reseller_id) && (int) $existingCust->reseller_id !== (int) $resellerId);
                if ($ownedByOther) {
                    throw new \Exception('ONU ini sudah terikat pada pelanggan mitra lain.');
                }
                if ((int) ($existingCs->reseller_id ?? 0) === (int) $resellerId || ($existingCust && (int) ($existingCust->reseller_id ?? 0) === (int) $resellerId)) {
                    $existingCs->onu_id = null;
                    $existingCs->save();
                } elseif (!$this->selectedCustomerId) {
                    throw new \Exception('Pelanggan ini dikelola oleh Admin Pusat dan tidak dapat dilepas dari Portal Reseller.');
                }
            }

            if ($this->selectedCustomerId) {
                // Pastikan pelanggan yang dipilih benar-benar milik reseller ini (melalui HasResellerScope)
                $customer = Customer::findOrFail($this->selectedCustomerId);
                $service = CustomerService::where('customer_id', $customer->id)->orderByDesc('id')->first();
                if (!$service) {
                    throw new \Exception('Pelanggan ini belum memiliki Layanan (PPPoE/Hotspot). Buat layanan pelanggan terlebih dahulu.');
                }
                $service->onu_id = $onu->id;
                $service->save();
                session()->flash('success', 'ONU berhasil ditautkan ke pelanggan ' . $customer->name);
            } else {
                session()->flash('success', 'Tautan pelanggan pada ONU berhasil dilepas.');
            }

            $this->editingCustomerOnuId = null;
            $this->selectedCustomerId = '';
        } catch (\Throwable $e) {
            session()->flash('error', 'Gagal memperbarui pelanggan: ' . $e->getMessage());
        }
    }

    public function getOnuStatus(Onu $onu): string
    {
        if ($onu->status === 'los' || ($onu->rx_power_dbm !== null && $onu->rx_power_dbm < -30)) {
            return 'LOS';
        }
        if ($onu->status === 'inactive') {
            return 'OFFLINE';
        }
        if ($onu->rx_power_dbm === null && ($onu->pon_port === null || empty($onu->last_seen_at))) {
            return 'OFFLINE';
        }
        if ($onu->rx_power_dbm !== null && $onu->rx_power_dbm < -27) {
            return 'LOW_RX';
        }
        return 'ONLINE';
    }

    public function render()
    {
        $rxLow = -27.0;
        $rxCrit = -30.0;

        $olts = Olt::forReseller()->select('id', 'name')->orderBy('name')->get();

        $ponPorts = collect();
        if (!empty($this->oltFilter) && $this->oltFilter !== 'all') {
            $pq = PonPort::where('olt_id', $this->oltFilter);
            if (Schema::hasColumn('pon_ports', 'port_number')) {
                $pq->orderBy('port_number', 'asc');
            } else {
                $pq->orderBy('id', 'asc');
            }
            $ponPorts = $pq->get();
        }

        $baseSummary = Onu::forReseller()
            ->when($this->oltFilter !== '' && $this->oltFilter !== 'all', fn ($q) => $q->where('olt_id', $this->oltFilter))
            ->when($this->ponFilter !== '', fn ($q) => $q->where('pon_port_id', $this->ponFilter));

        $total = (clone $baseSummary)->count();
        $online = (clone $baseSummary)->where('status', 'active')->count();
        $los = (clone $baseSummary)->where(function ($q) use ($rxCrit) {
            $q->where('status', 'los')->orWhere('rx_power_dbm', '<', $rxCrit);
        })->count();
        $lowRx = (clone $baseSummary)->whereBetween('rx_power_dbm', [$rxCrit, $rxLow])->count();
        $offline = max(0, $total - $online);

        $summary = [
            'total'   => $total,
            'online'  => $online,
            'offline' => $offline,
            'los'     => $los,
            'low_rx'  => $lowRx,
        ];

        $onus = Onu::forReseller()
            ->with([
                'olt:id,name',
                'branch:id,name,code',
                'odp:id,name,code',
                'ponPort:id,name,port_number',
                'acsDevice:id,serial_number,ip_address,mac_address',
                'customerService' => function ($csQ) {
                    $csQ->withoutGlobalScopes(['reseller_isolation', 'branch_isolation'])
                        ->select('id', 'onu_id', 'customer_id', 'reseller_id', 'username')
                        ->with([
                            'customer' => fn ($cq) => $cq->withoutGlobalScopes(['reseller_isolation', 'branch_isolation'])->select('id', 'name', 'code', 'reseller_id'),
                            'pppoeUser' => fn ($pq) => $pq->withoutGlobalScopes(['reseller_isolation', 'branch_isolation'])->select('id', 'customer_service_id', 'static_ip', 'ip_allocation_id')->with('ipAllocation:id,ip_address'),
                        ]);
                },
            ])
            ->when($this->oltFilter !== '' && $this->oltFilter !== 'all', fn ($q) => $q->where('olt_id', $this->oltFilter))
            ->when($this->ponFilter !== '', fn ($q) => $q->where('pon_port_id', $this->ponFilter))
            ->when($this->statusFilter === 'online', fn ($q) => $q->where('status', 'active'))
            ->when($this->statusFilter === 'offline', fn ($q) => $q->where('status', 'inactive'))
            ->when($this->statusFilter === 'los', fn ($q) => $q->where(function ($sub) use ($rxCrit) {
                $sub->where('status', 'los')->orWhere('rx_power_dbm', '<', $rxCrit);
            }))
            ->when($this->statusFilter === 'low_rx', fn ($q) => $q->whereBetween('rx_power_dbm', [$rxCrit, $rxLow]))
            ->when(trim($this->search) !== '', function ($q) {
                $like = '%' . trim($this->search) . '%';
                $q->where(function ($sq) use ($like) {
                    $sq->where('serial_number', 'like', $like)
                       ->orWhere('name', 'like', $like)
                       ->orWhere('code', 'like', $like)
                       ->orWhere('mac_address', 'like', $like)
                       ->orWhereHas('customerService', function ($csQ) use ($like) {
                           $csQ->withoutGlobalScopes(['reseller_isolation', 'branch_isolation'])
                               ->whereHas('customer', function ($cq) use ($like) {
                                   $cq->withoutGlobalScopes(['reseller_isolation', 'branch_isolation'])
                                      ->where(function ($m) use ($like) {
                                          $m->where('name', 'like', $like)->orWhere('code', 'like', $like);
                                      });
                               });
                       })
                       ->orWhereHas('acsDevice', fn ($aq) => $aq->where('ip_address', 'like', $like))
                       ->orWhereHas('olt', fn ($oq) => $oq->where('name', 'like', $like))
                       ->orWhereHas('odp', fn ($odpq) => $odpq->where('name', 'like', $like))
                       ->orWhereHas('ponPort', fn ($pq) => $pq->where('name', 'like', $like));
                });
            })
            ->when(
                in_array($this->sortField, ['serial_number', 'status', 'rx_power_dbm', 'last_seen_at', 'name'], true),
                fn ($q) => $q->orderBy($this->sortField, $this->sortDirection)
            )
            ->paginate($this->perPage === 0 ? 100000 : $this->perPage);

        return view('livewire.reseller-portal.network.onu.index', [
            'onus'      => $onus,
            'olts'      => $olts,
            'ponPorts'  => $ponPorts,
            'summary'   => $summary,
            'territory' => ResellerScopeHelper::describeTerritory(),
        ]);
    }
}
