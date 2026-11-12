<?php

namespace App\Livewire\Crm\Customer;

use App\Livewire\Crm\BaseCrmComponent;
use App\Models\CRM\Customer;

class Index extends BaseCrmComponent
{
    public $statusFilter = '';
    public $resellerFilter = '';
    public $packageFilter = '';
    public $serviceTypeFilter = '';
    public $selectedCustomers = [];
    public $selectAll = false;

    public ?int $forceDeleteCustomerId = null;
    public ?string $forceDeleteCustomerName = null;
    public array $forceDeleteInvoices = [];
    public bool $showForceDeleteModal = false;

    public function mount()
    {
        parent::mount();
        $this->activeModule = 'crm';
        $this->activePage = 'customers';
        $this->filters = ['status' => ''];
    }

    public function updatedSelectAll($value)
    {
        if ($value) {
            $this->selectedCustomers = $this->getCustomersQuery()->pluck('id')->map(fn($id) => (string) $id)->toArray();
        } else {
            $this->selectedCustomers = [];
        }
    }

    public function updatedResellerFilter()    { $this->resetPage(); }
    public function updatedPackageFilter()     { $this->resetPage(); }
    public function updatedServiceTypeFilter() { $this->resetPage(); }
    public function updatedStatusFilter()      { $this->resetPage(); }

    public function resetFilters(): void
    {
        $this->search = '';
        $this->statusFilter = '';
        $this->resellerFilter = '';
        $this->packageFilter = '';
        $this->serviceTypeFilter = '';
        $this->perPage = 20;
        $this->resetPage();
    }

    public function bulkDelete(bool $force = false): void
    {
        if (empty($this->selectedCustomers)) return;

        $hasActiveInvoices = Customer::whereIn('id', $this->selectedCustomers)
            ->whereHas('invoices', function($q) {
                $q->whereIn('status', ['unpaid', 'overdue', 'pending']);
            })
            ->exists();

        if ($hasActiveInvoices && !$force) {
            session()->flash('error', 'Beberapa pelanggan tidak dapat dihapus karena masih memiliki tagihan aktif/belum lunas.');
            return;
        }

        $customers = Customer::whereIn('id', $this->selectedCustomers)->get();
        foreach ($customers as $customer) {
            if ($force) {
                $customer->invoices()->whereIn('status', ['unpaid', 'overdue', 'pending'])->update(['status' => 'cancelled']);
                $customer->invoices()->whereIn('status', ['cancelled'])->delete();
            }
            $customer->delete(); // This triggers Eloquent deleted events
        }

        $this->selectedCustomers = [];
        $this->selectAll = false;
        
        session()->flash('success', 'Pelanggan terpilih berhasil dihapus!');
    }

    public function delete($id, bool $force = false): void
    {
        $customer = Customer::findOrFail($id);

        $activeInvoices = $customer->invoices()
            ->withoutGlobalScopes()
            ->whereIn('status', ['unpaid', 'overdue', 'pending'])
            ->get();

        if ($activeInvoices->isNotEmpty()) {
            if ($force) {
                foreach ($activeInvoices as $inv) {
                    $inv->update(['status' => 'cancelled']);
                    $inv->delete();
                }
            } else {
                $this->forceDeleteCustomerId = $customer->id;
                $this->forceDeleteCustomerName = $customer->name;
                $this->forceDeleteInvoices = $activeInvoices->map(fn($inv) => [
                    'id' => $inv->id,
                    'invoice_number' => $inv->invoice_number,
                    'total_amount' => (int) $inv->total_amount,
                    'status' => $inv->status,
                    'due_date' => $inv->due_date ? $inv->due_date->format('d/m/Y') : '-',
                    'period' => $inv->issue_date ? $inv->issue_date->format('M Y') : '-',
                ])->toArray();

                $invSummary = collect($this->forceDeleteInvoices)->pluck('invoice_number')->implode(', ');
                $this->showForceDeleteModal = true;

                session()->flash('error', "Pelanggan {$customer->name} memiliki " . count($activeInvoices) . " tagihan belum lunas: [{$invSummary}]. Silakan batalkan tagihan atau konfirmasi Hapus Paksa.");
                return;
            }
        }

        $customer->delete();
        $this->showForceDeleteModal = false;
        $this->forceDeleteCustomerId = null;
        $this->forceDeleteInvoices = [];
        session()->flash('success', 'Pelanggan berhasil dihapus!');
    }

    public function confirmForceDelete(): void
    {
        if ($this->forceDeleteCustomerId) {
            $this->delete($this->forceDeleteCustomerId, true);
        }
    }

    public function cancelForceDelete(): void
    {
        $this->showForceDeleteModal = false;
        $this->forceDeleteCustomerId = null;
        $this->forceDeleteInvoices = [];
    }

    public function toggleStatus($id): void
    {
        $customer = Customer::with(['customerServices.pppoeUser', 'customerServices.hotspotUser'])->findOrFail($id);
        
        // Cek apakah saat ini pelanggan berstatus suspend (baik dari customer, customerService, atau pppoe/hotspot)
        $isCurrentlySuspended = in_array($customer->status, ['suspend', 'suspended'])
            || $customer->customerServices->contains(function ($cs) {
                return $cs->status === 'suspended'
                    || $cs->suspended_at !== null
                    || ($cs->pppoeUser && $cs->pppoeUser->status === 'suspended')
                    || ($cs->hotspotUser && $cs->hotspotUser->status === 'suspended');
            });

        $newCustomerStatus = $isCurrentlySuspended ? 'active' : 'suspend';
        $customer->update(['status' => $newCustomerStatus]);

        $userId = auth()->id() ?? 0;

        foreach ($customer->customerServices as $cs) {
            $cs->update([
                'status' => $newCustomerStatus === 'suspend' ? 'suspended' : 'active',
                'suspended_at' => $newCustomerStatus === 'suspend' ? now() : null,
            ]);

            if ($cs->pppoeUser) {
                if ($newCustomerStatus === 'suspend') {
                    app(\App\Services\ISP\PPPoEService::class)->suspendPPPoEUser($cs->pppoeUser->id, $userId);
                } else {
                    app(\App\Services\ISP\PPPoEService::class)->reactivatePPPoEUser($cs->pppoeUser->id, $userId);
                }
            }

            if ($cs->hotspotUser) {
                if ($newCustomerStatus === 'suspend') {
                    app(\App\Services\ISP\HotspotService::class)->suspendHotspotUser($cs->hotspotUser->id, $userId);
                } else {
                    app(\App\Services\ISP\HotspotService::class)->activateHotspotUser($cs->hotspotUser->id, $userId);
                }
            }
        }

        $label = $newCustomerStatus === 'suspend' ? 'dinonaktifkan (diisolir)' : 'diaktifkan kembali';
        session()->flash('success', "Status pelanggan {$customer->name} beserta layanannya berhasil {$label}!");
    }

    public function impersonate($id)
    {
        $customer = Customer::findOrFail($id);
        if (!$customer->user_id) {
            session()->flash('error', 'Pelanggan ini belum memiliki akun user login portal.');
            return;
        }

        $user = \App\Models\User::find($customer->user_id);
        if (!$user) {
            session()->flash('error', 'Akun user login portal tidak ditemukan.');
            return;
        }

        session(['impersonator_admin_id' => auth()->id()]);
        \Illuminate\Support\Facades\Auth::login($user);

        return redirect()->route('customer-portal.dashboard');
    }

    public function getCustomersQuery()
    {
        $query = Customer::withoutGuests()->with([
            'customerServices.serviceProfile',
            'customerServices.service',
            'customerServices.pppoeUser',
            'customerServices.hotspotUser',
            'customerServices.onu.acsDevice',
            'customerServices.acsDevice',
            'reseller',
        ]);

        if ($this->search) {
            $query->where(function ($q) {
                $q->where('name', 'like', '%' . $this->search . '%')
                  ->orWhere('email', 'like', '%' . $this->search . '%')
                  ->orWhere('phone', 'like', '%' . $this->search . '%')
                  ->orWhere('code', 'like', '%' . $this->search . '%');
            });
        }

        if (!empty($this->statusFilter)) {
            if ($this->statusFilter === 'suspend' || $this->statusFilter === 'suspended') {
                $query->where(function($q) {
                    $q->whereIn('status', ['suspend', 'suspended'])
                      ->orWhereHas('customerServices', function($cs) {
                          $cs->where('status', 'suspended')
                             ->orWhereNotNull('suspended_at')
                             ->orWhereHas('pppoeUser', fn($pu) => $pu->where('status', 'suspended'))
                             ->orWhereHas('hotspotUser', fn($hu) => $hu->where('status', 'suspended'));
                      });
                });
            } elseif ($this->statusFilter === 'active') {
                $query->where('status', 'active')
                      ->whereDoesntHave('customerServices', function($cs) {
                          $cs->where('status', 'suspended')
                             ->orWhereNotNull('suspended_at')
                             ->orWhereHas('pppoeUser', fn($pu) => $pu->where('status', 'suspended'))
                             ->orWhereHas('hotspotUser', fn($hu) => $hu->where('status', 'suspended'));
                      });
            } else {
                $query->where('status', $this->statusFilter);
            }
        }

        if (!empty($this->resellerFilter)) {
            if ($this->resellerFilter === 'pusat' || $this->resellerFilter === 'owner') {
                $query->whereNull('reseller_id');
            } elseif ($this->resellerFilter === 'reseller') {
                $query->whereNotNull('reseller_id');
            } else {
                $query->where('reseller_id', $this->resellerFilter);
            }
        }

        if (!empty($this->packageFilter)) {
            $query->whereHas('customerServices', function($cs) {
                $cs->where('service_profile_id', $this->packageFilter);
            });
        }

        if (!empty($this->serviceTypeFilter)) {
            if ($this->serviceTypeFilter === 'pppoe') {
                $query->whereHas('customerServices', function($cs) {
                    $cs->whereHas('pppoeUser')
                       ->orWhereHas('serviceProfile', fn($sp) => $sp->whereIn('service_type', ['pppoe', 'broadband', 'dedicated']));
                });
            } elseif ($this->serviceTypeFilter === 'hotspot') {
                $query->whereHas('customerServices', function($cs) {
                    $cs->whereHas('hotspotUser')
                       ->orWhereHas('serviceProfile', fn($sp) => $sp->whereIn('service_type', ['hotspot', 'member_hotspot', 'voucher']));
                });
            }
        }

        return $query;
    }

    public function render()
    {
        $query = $this->getCustomersQuery();

        // KPI (hanya pelanggan reguler, mengecualikan guest voucher)
        try {
            $baseKpi          = Customer::withoutGuests();
            $totalCustomers   = (clone $baseKpi)->count();
            
            $suspendCustomers = (clone $baseKpi)->where(function($q) {
                $q->whereIn('status', ['suspend', 'suspended'])
                  ->orWhereHas('customerServices', function($cs) {
                      $cs->where('status', 'suspended')
                         ->orWhereNotNull('suspended_at')
                         ->orWhereHas('pppoeUser', fn($pu) => $pu->where('status', 'suspended'))
                         ->orWhereHas('hotspotUser', fn($hu) => $hu->where('status', 'suspended'));
                  });
            })->count();

            $activeCustomers  = max(0, $totalCustomers - $suspendCustomers);
            $newCustomers     = (clone $baseKpi)->whereMonth('created_at', now()->month)
                                                ->whereYear('created_at', now()->year)
                                                ->count();
        } catch (\Exception $e) {
            $totalCustomers = $activeCustomers = $suspendCustomers = $newCustomers = 0;
        }

        $customers = $query
            ->orderBy($this->sortField, $this->sortDirection)
            ->paginate($this->perPage);

        $resellers = \App\Models\User::whereHas('roles', fn($q) => $q->where('name', 'reseller'))
            ->orderBy('name')
            ->get(['id', 'name']);

        $serviceProfiles = \App\Models\ISP\ServiceProfile::where(function($q) {
                $q->whereNull('status')->orWhere('status', 'active');
            })
            ->orderBy('name')
            ->get(['id', 'name', 'service_type']);

        return view('livewire.crm.customer.index', compact(
            'customers',
            'totalCustomers',
            'activeCustomers',
            'suspendCustomers',
            'newCustomers',
            'resellers',
            'serviceProfiles'
        ));
    }
}
