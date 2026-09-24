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
        $ownerId = Auth::id();
        return HotspotUser::where(function($q) use ($ownerId) {
            $q->where('reseller_id', $ownerId)->orWhere('created_by', $ownerId);
        });
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
                        $q->where('status', 'active')->whereHas('radiusAccountings', function($sq) {
                            $sq->whereNull('acct_stop_time');
                        });
                    } elseif ($this->filters['status'] === 'offline') {
                        $q->where('status', 'active')->whereDoesntHave('radiusAccountings', function($sq) {
                            $sq->whereNull('acct_stop_time');
                        });
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
            $service->suspendHotspotUser($hotspotUser->id, Auth::id());
            session()->flash('success', "Hotspot berhasil di-suspend!");
        } else if ($hotspotUser->status === 'suspended') {
            $service->reactivateHotspotUser($hotspotUser->id, Auth::id());
            session()->flash('success', "Hotspot berhasil diaktifkan kembali!");
        }
    }

    public function delete($id, HotspotService $service)
    {
        $hotspotUser = $this->baseQuery()->findOrFail($id);
        $service->terminateHotspotUser($hotspotUser->id, Auth::id());
        $hotspotUser->delete();
        session()->flash('success', 'Hotspot berhasil dihapus!');
        $this->selectedIds = [];
        $this->showDeleteModal = false;
    }
    
    public function render()
    {
        $query = $this->baseQuery()->with(['customer', 'serviceProfile'])
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
                    $q->where('status', 'active')->whereHas('radiusAccountings', function($sq) {
                        $sq->whereNull('acct_stop_time');
                    });
                } elseif ($this->filters['status'] === 'offline') {
                    $q->where('status', 'active')->whereDoesntHave('radiusAccountings', function($sq) {
                        $sq->whereNull('acct_stop_time');
                    });
                } else {
                    $q->where('status', $this->filters['status']);
                }
            });

        $hotspotUsers = $query->orderBy($this->sortField, $this->sortDirection)->paginate($this->perPage);

        $stats = [
            'total' => $this->baseQuery()->count(),
            'active' => $this->baseQuery()->where('status', 'active')->count(),
            'inactive' => $this->baseQuery()->where('status', 'inactive')->count(),
            'suspended' => $this->baseQuery()->where('status', 'suspended')->count(),
            'online' => $this->baseQuery()->where('status', 'active')->whereHas('radiusAccountings', function($q) { $q->whereNull('acct_stop_time'); })->count(),
        ];

        return view('livewire.reseller-portal.customer.hotspot', compact('hotspotUsers', 'stats'));
    }
}

