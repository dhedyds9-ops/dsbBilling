<?php
namespace App\Livewire\ResellerPortal\Customer;

use App\Livewire\AdminComponent;
use App\Models\ISP\HotspotUser;
use App\Services\ISP\HotspotService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Livewire\WithPagination;
use Throwable;

class Hotspot extends AdminComponent
{
    use WithPagination;

    public $search = '';
    public $sortField = 'created_at';
    public $sortDirection = 'desc';
    public $perPage = 10;
    public $filters = ['status' => ''];
    public $showTrashed = false;

    public array $selectedIds = [];
    public bool $selectAll = false;
    public bool $showDeleteModal = false;

    public function mount()
    {
        parent::mount();
        $this->activeModule = 'reseller-portal';
        $this->activePage = 'customers.hotspot';
        $this->breadcrumbs = [
            ['label' => 'Reseller Portal', 'url' => route('reseller-portal.dashboard')],
            ['label' => 'Hotspot', 'url' => ''],
        ];
    }
    
    public function sortBy($field)
    {
        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortDirection = 'asc';
        }
        $this->sortField = $field;
    }

    private function baseQuery()
    {
        $ownerId = Auth::user()->getEffectiveResellerId();
        return HotspotUser::withoutGlobalScopes(['branch_isolation', 'reseller_isolation'])
            ->where(function($q) use ($ownerId) {
                $q->where('hotspot_users.reseller_id', $ownerId)
                  ->orWhere('hotspot_users.created_by', $ownerId)
                  ->orWhereHas('customer', function($cq) use ($ownerId) {
                      $cq->withoutGlobalScopes(['branch_isolation', 'reseller_isolation'])->where(function($sq) use ($ownerId) {
                          $sq->where('customers.reseller_id', $ownerId)->orWhere('customers.created_by', $ownerId);
                      });
                  })
                  ->orWhereHas('customerService', function($csq) use ($ownerId) {
                      $csq->withoutGlobalScopes(['branch_isolation', 'reseller_isolation'])->where(function($sq) use ($ownerId) {
                          $sq->where('customer_services.reseller_id', $ownerId)->orWhere('customer_services.created_by', $ownerId);
                      });
                  });
            });
    }

    private function applyOnlineFilter($query, bool $isOnline)
    {
        if ($isOnline) {
            $query->where('status', 'active')->where(function($oq) {
                $oq->whereHas('activeSession')
                   ->orWhereHas('radiusAccountings', fn($sq) => $sq->whereNull('acct_stop_time'))
                   ->orWhereExists(function($sub) {
                       $sub->selectRaw(1)->from('hotspot_active_sessions')
                           ->whereRaw('LOWER(hotspot_active_sessions.user) = LOWER(hotspot_users.username) OR LOWER(hotspot_active_sessions.user) LIKE LOWER(hotspot_users.username) || \'@%\'');
                   })
                   ->orWhereExists(function($sub) {
                       $sub->selectRaw(1)->from('radius_accounting')
                           ->whereNull('acct_stop_time')
                           ->whereRaw('LOWER(radius_accounting.username) = LOWER(hotspot_users.username) OR LOWER(radius_accounting.username) LIKE LOWER(hotspot_users.username) || \'@%\'');
                   });
            });
        } else {
            $query->where('status', 'active')->where(function($oq) {
                $oq->whereDoesntHave('activeSession')
                   ->whereDoesntHave('radiusAccountings', fn($sq) => $sq->whereNull('acct_stop_time'))
                   ->whereNotExists(function($sub) {
                       $sub->selectRaw(1)->from('hotspot_active_sessions')
                           ->whereRaw('LOWER(hotspot_active_sessions.user) = LOWER(hotspot_users.username) OR LOWER(hotspot_active_sessions.user) LIKE LOWER(hotspot_users.username) || \'@%\'');
                   })
                   ->whereNotExists(function($sub) {
                       $sub->selectRaw(1)->from('radius_accounting')
                           ->whereNull('acct_stop_time')
                           ->whereRaw('LOWER(radius_accounting.username) = LOWER(hotspot_users.username) OR LOWER(radius_accounting.username) LIKE LOWER(hotspot_users.username) || \'@%\'');
                   });
            });
        }
    }

    public function updatedSelectAll($value)
    {
        if ($value) {
            $query = $this->baseQuery()
                ->when($this->showTrashed, fn($q) => $q->withTrashed())
                ->when($this->search, function($q) {
                    $q->where(function($sq) {
                        $sq->where('username', 'like', '%' . $this->search . '%')
                            ->orWhereHas('customer', function($cq) {
                                $cq->where('name', 'like', '%' . $this->search . '%');
                            });
                    });
                })
                ->when(!empty($this->filters['status']), function($q) {
                    if ($this->filters['status'] === 'online') {
                        $this->applyOnlineFilter($q, true);
                    } elseif ($this->filters['status'] === 'offline') {
                        $this->applyOnlineFilter($q, false);
                    } else {
                        $q->where('status', $this->filters['status']);
                    }
                });

            $this->selectedIds = $query->pluck('id')->toArray();
        } else {
            $this->selectedIds = [];
        }
    }

    public function closeDeleteModal()
    {
        $this->showDeleteModal = false;
    }
    
    public function confirmBulkDelete()
    {
        if (empty($this->selectedIds)) {
            session()->flash('error', 'Pilih setidaknya satu user!');
            return;
        }
        $this->showDeleteModal = true;
    }
    
    public function toggleStatus($id, HotspotService $service)
    {
        $hotspotUser = $this->baseQuery()->findOrFail($id);
        if ($hotspotUser->status === 'active') {
            $service->suspendHotspotUser($hotspotUser->id, Auth::user()->getEffectiveResellerId());
            session()->flash('success', "Hotspot berhasil di-suspend!");
        } else if ($hotspotUser->status === 'suspended') {
            $service->reactivateHotspotUser($hotspotUser->id, Auth::user()->getEffectiveResellerId());
            session()->flash('success', "Hotspot berhasil diaktifkan kembali!");
        }
    }

    public function delete($id, HotspotService $service)
    {
        $hotspotUser = $this->baseQuery()->findOrFail($id);
        $service->terminateHotspotUser($hotspotUser->id, Auth::user()->getEffectiveResellerId());
        $hotspotUser->delete();
        session()->flash('success', 'Hotspot berhasil dihapus!');
        $this->selectedIds = [];
        $this->showDeleteModal = false;
    }
    
    public function render()
    {
        $query = $this->baseQuery()->with([
            'customer' => fn($q) => $q->withoutGlobalScopes(['branch_isolation', 'reseller_isolation']),
            'customerService.customer' => fn($q) => $q->withoutGlobalScopes(['branch_isolation', 'reseller_isolation']),
            'serviceProfile' => fn($q) => $q->withoutGlobalScopes(['branch_isolation', 'reseller_isolation']),
            'activeSession',
            'latestAccounting',
        ])
            ->when($this->showTrashed, fn($q) => $q->withTrashed())
            ->when($this->search, function($q) {
                $q->where(function($sq) {
                    $sq->where('username', 'like', '%' . $this->search . '%')
                        ->orWhereHas('customer', function($cq) {
                            $cq->withoutGlobalScopes(['branch_isolation', 'reseller_isolation'])->where('name', 'like', '%' . $this->search . '%');
                        });
                });
            })
            ->when(!empty($this->filters['status']), function($q) {
                if ($this->filters['status'] === 'online') {
                    $this->applyOnlineFilter($q, true);
                } elseif ($this->filters['status'] === 'offline') {
                    $this->applyOnlineFilter($q, false);
                } else {
                    $q->where('status', $this->filters['status']);
                }
            });

        $hotspotUsers = $query->orderBy($this->sortField, $this->sortDirection)->paginate($this->perPage);

        $onlineCountQuery = $this->baseQuery();
        $this->applyOnlineFilter($onlineCountQuery, true);

        $stats = [
            'total' => $this->baseQuery()->count(),
            'active' => $this->baseQuery()->where('status', 'active')->count(),
            'inactive' => $this->baseQuery()->where('status', 'inactive')->count(),
            'suspended' => $this->baseQuery()->where('status', 'suspended')->count(),
            'online' => $onlineCountQuery->count(),
        ];

        return view('livewire.reseller-portal.customer.hotspot', compact('hotspotUsers', 'stats'));
    }
}

