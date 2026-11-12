<?php

namespace App\Livewire\ResellerPortal\Customer;

use App\Livewire\AdminComponent;
use App\Models\CRM\Customer;
use Livewire\WithPagination;

class Index extends AdminComponent
{
    use WithPagination;

    public $search = '';
    public $statusFilter = '';
    public $perPage = 15;
    public $sortField = 'created_at';
    public $sortDirection = 'desc';

    // Modal Edit Tanggal Isolir & Janji Bayar
    public bool $showEditIsolirModal = false;
    public ?int $selectedCustomerId = null;
    public ?string $selectedCustomerName = null;
    public ?string $selectedCustomerUsername = null;
    public ?string $editIsolirDate = null;
    public int $graceDays = 3;
    public string $isolirModalTab = 'date'; // 'date' | 'grace'

    public function mount()
    {
        parent::mount();
        $this->activeModule = 'reseller-portal';
        $this->activePage = 'customers.index';
        $this->breadcrumbs = [
            ['label' => 'Reseller Portal', 'url' => route('reseller-portal.dashboard')],
            ['label' => 'Pelanggan Saya', 'url' => ''],
        ];
    }

    public function openEditIsolirModal($customerId): void
    {
        $ownerId = auth()->user()->getEffectiveResellerId();
        $customer = Customer::where(function($q) use ($ownerId) {
            $q->where('reseller_id', $ownerId)->orWhere('created_by', $ownerId);
        })->with(['customerServices.pppoeUser', 'customerServices.hotspotUser'])->findOrFail($customerId);

        $svc = $customer->customerServices->first();
        $username = $svc?->pppoeUser?->username ?? $svc?->hotspotUser?->username ?? $svc?->username ?? '-';

        $this->selectedCustomerId = $customer->id;
        $this->selectedCustomerName = $customer->name;
        $this->selectedCustomerUsername = $username;
        $this->editIsolirDate = $svc?->suspended_at ? $svc->suspended_at->format('Y-m-d') : now()->format('Y-m-d');
        $this->graceDays = 3;
        $this->isolirModalTab = 'date';
        $this->showEditIsolirModal = true;
    }

    public function closeEditIsolirModal(): void
    {
        $this->showEditIsolirModal = false;
        $this->selectedCustomerId = null;
        $this->selectedCustomerName = null;
        $this->selectedCustomerUsername = null;
        $this->editIsolirDate = null;
    }

    public function saveIsolirDate(): void
    {
        $this->validate([
            'editIsolirDate' => 'required|date',
        ]);

        $ownerId = auth()->user()->getEffectiveResellerId();
        $userId = auth()->id() ?? 1;

        $customer = Customer::where(function($q) use ($ownerId) {
            $q->where('reseller_id', $ownerId)->orWhere('created_by', $ownerId);
        })->with(['customerServices.pppoeUser', 'customerServices.hotspotUser'])->findOrFail($this->selectedCustomerId);

        $date = \Carbon\Carbon::parse($this->editIsolirDate);
        $customer->update(['status' => 'suspend']);

        $pppoeService = app(\App\Services\ISP\PPPoEService::class);
        $hotspotService = app(\App\Services\ISP\HotspotService::class);

        foreach ($customer->customerServices as $cs) {
            $cs->update([
                'status' => 'suspended',
                'suspended_at' => $date,
            ]);
            if ($cs->pppoeUser) {
                try {
                    $pppoeService->suspendPPPoEUser($cs->pppoeUser->id, $userId, $date);
                } catch (\Throwable $e) {}
            }
            if ($cs->hotspotUser) {
                try {
                    $hotspotService->suspendHotspotUser($cs->hotspotUser->id, $userId, $date);
                } catch (\Throwable $e) {}
            }
        }

        session()->flash('success', "Tanggal isolir pelanggan {$customer->name} berhasil disimpan: " . $date->format('d/m/Y') . "!");
        $this->closeEditIsolirModal();
    }

    public function applyJanjiBayar(): void
    {
        $this->validate([
            'graceDays' => 'required|integer|min:1|max:30',
        ]);

        $ownerId = auth()->user()->getEffectiveResellerId();
        $userId = auth()->id() ?? 1;

        $customer = Customer::where(function($q) use ($ownerId) {
            $q->where('reseller_id', $ownerId)->orWhere('created_by', $ownerId);
        })->with(['customerServices.pppoeUser', 'customerServices.hotspotUser'])->findOrFail($this->selectedCustomerId);

        $invoice = \App\Models\Billing\Invoice::where('customer_id', $customer->id)
            ->whereIn('status', ['unpaid', 'overdue', 'partial', 'pending'])
            ->latest('due_date')
            ->first();

        $days = (int) $this->graceDays;
        if ($invoice) {
            $invoice->grace_period_until = now()->addDays($days);
            $invoice->save();
        }

        // Aktifkan kembali pelanggan karena diberikan janji bayar (dispensasi)
        $customer->update(['status' => 'active']);

        $pppoeService = app(\App\Services\ISP\PPPoEService::class);
        $hotspotService = app(\App\Services\ISP\HotspotService::class);

        foreach ($customer->customerServices as $cs) {
            $cs->update(['status' => 'active', 'suspended_at' => null]);
            if ($cs->pppoeUser) {
                try {
                    $pppoeService->reactivatePPPoEUser($cs->pppoeUser->id, $userId);
                } catch (\Throwable $e) {}
            }
            if ($cs->hotspotUser) {
                try {
                    $hotspotService->activateHotspotUser($cs->hotspotUser->id, $userId);
                } catch (\Throwable $e) {}
            }
        }

        session()->flash('success', "Janji bayar {$days} hari berhasil diberikan untuk {$customer->name}. Layanan telah diaktifkan kembali!");
        $this->closeEditIsolirModal();
    }

    public function resetFilters(): void
    {
        $this->search = '';
        $this->statusFilter = '';
        $this->perPage = 15;
        $this->resetPage();
    }

    public function sortBy($field)
    {
        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortField = $field;
            $this->sortDirection = 'asc';
        }
        $this->resetPage();
    }

    public function delete($id): void
    {
        $ownerId = auth()->user()->getEffectiveResellerId();
        $customer = Customer::where(function($q) use ($ownerId) {
            $q->where('reseller_id', $ownerId)->orWhere('created_by', $ownerId);
        })->findOrFail($id);

        $hasActiveInvoices = $customer->invoices()
            ->whereIn('status', ['unpaid', 'overdue'])
            ->exists();

        if ($hasActiveInvoices) {
            session()->flash('error', 'Tidak dapat menghapus pelanggan karena ada tagihan aktif/belum lunas.');
            return;
        }

        $customer->delete();
        session()->flash('success', 'Pelanggan berhasil dihapus!');
    }

    public function toggleStatus($id): void
    {
        $ownerId = auth()->user()->getEffectiveResellerId();
        $userId = auth()->id() ?? 1;

        $customer = Customer::where(function($q) use ($ownerId) {
            $q->where('reseller_id', $ownerId)->orWhere('created_by', $ownerId);
        })->with([
            'customerServices.pppoeUser',
            'customerServices.hotspotUser'
        ])->findOrFail($id);

        $isCurrentlySuspended = in_array($customer->status, ['suspend', 'suspended'])
            || $customer->customerServices->contains(function ($cs) {
                return $cs->status === 'suspended'
                    || $cs->suspended_at !== null
                    || ($cs->pppoeUser && $cs->pppoeUser->status === 'suspended')
                    || ($cs->hotspotUser && $cs->hotspotUser->status === 'suspended');
            });

        $newCustomerStatus = $isCurrentlySuspended ? 'active' : 'suspend';
        $customer->update(['status' => $newCustomerStatus]);

        $pppoeService = app(\App\Services\ISP\PPPoEService::class);
        $hotspotService = app(\App\Services\ISP\HotspotService::class);

        foreach ($customer->customerServices as $cs) {
            $cs->update([
                'status' => $newCustomerStatus === 'suspend' ? 'suspended' : 'active',
                'suspended_at' => $newCustomerStatus === 'suspend' ? now() : null,
            ]);

            if ($cs->pppoeUser) {
                if ($newCustomerStatus === 'suspend') {
                    try {
                        $pppoeService->suspendPPPoEUser($cs->pppoeUser->id, $userId);
                    } catch (\Throwable $e) {
                        \Illuminate\Support\Facades\Log::warning('ResellerPortal suspend PPPoE failed', ['err' => $e->getMessage()]);
                    }
                } else {
                    try {
                        $pppoeService->reactivatePPPoEUser($cs->pppoeUser->id, $userId);
                    } catch (\Throwable $e) {
                        \Illuminate\Support\Facades\Log::warning('ResellerPortal reactivate PPPoE failed', ['err' => $e->getMessage()]);
                    }
                }
            }

            if ($cs->hotspotUser) {
                if ($newCustomerStatus === 'suspend') {
                    try {
                        $hotspotService->suspendHotspotUser($cs->hotspotUser->id, $userId);
                    } catch (\Throwable $e) {
                        \Illuminate\Support\Facades\Log::warning('ResellerPortal suspend Hotspot failed', ['err' => $e->getMessage()]);
                    }
                } else {
                    try {
                        $hotspotService->activateHotspotUser($cs->hotspotUser->id, $userId);
                    } catch (\Throwable $e) {
                        \Illuminate\Support\Facades\Log::warning('ResellerPortal reactivate Hotspot failed', ['err' => $e->getMessage()]);
                    }
                }
            }
        }

        $label = $newCustomerStatus === 'suspend' ? 'dinonaktifkan (diisolir)' : 'diaktifkan kembali';
        session()->flash('success', "Status pelanggan {$customer->name} beserta layanannya berhasil {$label}!");
    }

    public function render()
    {
        $ownerId = auth()->user()->getEffectiveResellerId();

        $query = Customer::withoutGuests()->with([
            'customerServices' => function ($query) {
                $query->withoutGlobalScopes(['branch_isolation', 'reseller_isolation'])
                      ->with([
                          'serviceProfile' => fn($q) => $q->withoutGlobalScopes(['branch_isolation', 'reseller_isolation']),
                          'service' => fn($q) => $q->withoutGlobalScopes(['branch_isolation', 'reseller_isolation']),
                          'pppoeUser' => fn($q) => $q->withoutGlobalScopes(['branch_isolation', 'reseller_isolation']),
                          'hotspotUser' => fn($q) => $q->withoutGlobalScopes(['branch_isolation', 'reseller_isolation']),
                          'onu.acsDevice' => fn($q) => $q->withoutGlobalScopes(['branch_isolation', 'reseller_isolation']),
                          'acsDevice' => fn($q) => $q->withoutGlobalScopes(['branch_isolation', 'reseller_isolation']),
                      ]);
            },
            'reseller' => fn($q) => $q->withoutGlobalScopes(['branch_isolation', 'reseller_isolation']),
        ])->where(function($q) use ($ownerId) {
            $q->where('reseller_id', $ownerId)
              ->orWhere('created_by', $ownerId);
        });

        if ($this->search) {
            $query->where(function ($q) {
                $q->where('name', 'like', '%' . $this->search . '%')
                  ->orWhere('email', 'like', '%' . $this->search . '%')
                  ->orWhere('phone', 'like', '%' . $this->search . '%')
                  ->orWhere('code', 'like', '%' . $this->search . '%');
            });
        }

        if (!empty($this->statusFilter)) {
            if (in_array($this->statusFilter, ['suspend', 'suspended'])) {
                $query->where(function($q) {
                    $q->whereIn('status', ['suspend', 'suspended'])
                      ->orWhereHas('customerServices', function($cs) {
                          $cs->withoutGlobalScopes()
                             ->where(function($sub) {
                                 $sub->whereIn('status', ['suspend', 'suspended'])
                                     ->orWhereNotNull('suspended_at')
                                     ->orWhereHas('pppoeUser', fn($pu) => $pu->withoutGlobalScopes()->whereIn('status', ['suspend', 'suspended']))
                                     ->orWhereHas('hotspotUser', fn($hu) => $hu->withoutGlobalScopes()->whereIn('status', ['suspend', 'suspended']));
                             });
                      });
                });
            } elseif ($this->statusFilter === 'active') {
                $query->where(function($q) {
                    $q->where('status', 'active')
                      ->whereDoesntHave('customerServices', function($cs) {
                          $cs->withoutGlobalScopes()
                             ->where(function($sub) {
                                 $sub->whereIn('status', ['suspend', 'suspended'])
                                     ->orWhereNotNull('suspended_at')
                                     ->orWhereHas('pppoeUser', fn($pu) => $pu->withoutGlobalScopes()->whereIn('status', ['suspend', 'suspended']))
                                     ->orWhereHas('hotspotUser', fn($hu) => $hu->withoutGlobalScopes()->whereIn('status', ['suspend', 'suspended']));
                             });
                      });
                });
            } else {
                $query->where('status', $this->statusFilter);
            }
        }

        // KPI (Filtered for Reseller without Guests - aligned with Admin CRM KPI)
        try {
            $baseKpi = Customer::withoutGuests()->where(function($q) use ($ownerId) {
                $q->where('reseller_id', $ownerId)->orWhere('created_by', $ownerId);
            });
            $totalCustomers   = (clone $baseKpi)->count();
            $suspendCustomers = (clone $baseKpi)->where(function($q) {
                $q->whereIn('status', ['suspend', 'suspended'])
                  ->orWhereHas('customerServices', function($cs) {
                      $cs->withoutGlobalScopes()
                         ->where(function($sub) {
                             $sub->whereIn('status', ['suspend', 'suspended'])
                                 ->orWhereNotNull('suspended_at')
                                 ->orWhereHas('pppoeUser', fn($pu) => $pu->withoutGlobalScopes()->whereIn('status', ['suspend', 'suspended']))
                                 ->orWhereHas('hotspotUser', fn($hu) => $hu->withoutGlobalScopes()->whereIn('status', ['suspend', 'suspended']));
                         });
                  });
            })->count();
            $activeCustomers  = max(0, $totalCustomers - $suspendCustomers);
            $newCustomers     = (clone $baseKpi)
                                        ->whereMonth('created_at', now()->month)
                                        ->whereYear('created_at', now()->year)
                                        ->count();
        } catch (\Exception $e) {
            $totalCustomers = $activeCustomers = $suspendCustomers = $newCustomers = 0;
        }

        $customers = $query
            ->orderBy($this->sortField, $this->sortDirection)
            ->paginate($this->perPage);

        return view('livewire.reseller-portal.customer.index', compact(
            'customers',
            'totalCustomers',
            'activeCustomers',
            'suspendCustomers',
            'newCustomers'
        ));
    }
}
