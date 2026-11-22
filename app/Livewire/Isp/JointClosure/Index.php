<?php

namespace App\Livewire\Isp\JointClosure;

use App\Livewire\Isp\BaseNetworkComponent;
use App\Models\ISP\JointClosure;
use App\Services\ISP\JointClosureService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Throwable;

class Index extends BaseNetworkComponent
{
    public bool $showTrashed = false;
    public array $selectedClosures = [];
    public bool $selectAll = false;
    public bool $showDeleteModal = false;

    public function mount()
    {
        parent::mount();
        $this->activeModule = 'isp';
        $this->activePage = 'joint-closures';
        $this->filters = [
            'status' => '',
            'type' => '',
            'port_count' => '',
        ];
        $this->breadcrumbs = [
            ['label' => 'Dashboard', 'url' => route('dashboard')],
            ['label' => 'Infrastruktur Fiber'],
            ['label' => 'Joint Closure'],
        ];
    }

    public function updatedSelectAll($value)
    {
        if ($value) {
            $query = JointClosure::query()
                ->when($this->showTrashed, fn($q) => $q->withTrashed())
                ->when($this->search, function ($q) {
                    $q->where(function ($sq) {
                        $sq->where('code', 'like', '%' . $this->search . '%')
                           ->orWhere('name', 'like', '%' . $this->search . '%')
                           ->orWhere('address', 'like', '%' . $this->search . '%');
                    });
                })
                ->when(!empty($this->filters['status']), fn($q) => $q->where('status', $this->filters['status']))
                ->when(!empty($this->filters['type']), fn($q) => $q->where('type', $this->filters['type']))
                ->when(!empty($this->filters['port_count']), fn($q) => $q->where('port_count', (int)$this->filters['port_count']));

            $this->selectedClosures = $query->pluck('id')->toArray();
        } else {
            $this->selectedClosures = [];
        }
    }

    private function resetActionState()
    {
        $this->selectedClosures = [];
        $this->selectAll = false;
        $this->showDeleteModal = false;
    }

    public function delete($id)
    {
        $service = app(JointClosureService::class);
        $user = Auth::user();

        try {
            $closure = JointClosure::findOrFail($id);
            $service->delete($closure, $user);
            session()->flash('success', 'Joint Closure berhasil dihapus!');
            $this->resetActionState();
        } catch (Throwable $e) {
            Log::error('Delete JointClosure failed', ['closure_id' => $id, 'message' => $e->getMessage()]);
            session()->flash('error', 'Gagal menghapus Joint Closure: ' . $e->getMessage());
        }
    }

    public function restore($id)
    {
        $service = app(JointClosureService::class);
        $user = Auth::user();

        try {
            $closure = JointClosure::withTrashed()->findOrFail($id);
            $service->restore($closure, $user);
            session()->flash('success', 'Joint Closure berhasil direstore!');
            $this->resetActionState();
        } catch (Throwable $e) {
            Log::error('Restore JointClosure failed', ['closure_id' => $id, 'message' => $e->getMessage()]);
            session()->flash('error', 'Gagal merestore Joint Closure: ' . $e->getMessage());
        }
    }

    public function toggleStatus($id)
    {
        $service = app(JointClosureService::class);
        $user = Auth::user();

        try {
            $closure = JointClosure::findOrFail($id);
            $newStatus = $closure->status === 'active' ? 'inactive' : 'active';
            $service->update($closure, ['status' => $newStatus], $user);

            $statusText = $newStatus === 'active' ? 'diaktifkan' : 'dinonaktifkan';
            session()->flash('success', 'Joint Closure berhasil ' . $statusText . '!');
        } catch (Throwable $e) {
            Log::error('Toggle JointClosure status failed', ['closure_id' => $id, 'message' => $e->getMessage()]);
            session()->flash('error', 'Gagal mengubah status Joint Closure: ' . $e->getMessage());
        }
    }

    public function duplicate($id)
    {
        $service = app(JointClosureService::class);
        $user = Auth::user();

        try {
            $original = JointClosure::findOrFail($id);
            $cloneData = $original->toArray();
            $cloneData['name'] = $original->name . ' (Copy)';
            $cloneData['code'] = $original->code . '_COPY_' . rand(100, 999);
            unset($cloneData['id'], $cloneData['created_at'], $cloneData['updated_at'], $cloneData['deleted_at'], $cloneData['created_by'], $cloneData['updated_by']);
            $service->create($cloneData, $user);

            session()->flash('success', 'Joint Closure berhasil diduplikasi!');
        } catch (Throwable $e) {
            Log::error('Duplicate JointClosure failed', ['original_id' => $id, 'message' => $e->getMessage()]);
            session()->flash('error', 'Gagal menduplikasi Joint Closure: ' . $e->getMessage());
        }
    }

    public function bulkActivate()
    {
        $service = app(JointClosureService::class);
        $user = Auth::user();

        try {
            if (empty($this->selectedClosures)) {
                session()->flash('error', 'Silakan pilih setidaknya satu Joint Closure!');
                return;
            }

            $service->bulkActivate($this->selectedClosures, $user);
            session()->flash('success', count($this->selectedClosures) . ' Joint Closure berhasil diaktifkan!');
            $this->resetActionState();
        } catch (Throwable $e) {
            session()->flash('error', 'Gagal mengaktifkan: ' . $e->getMessage());
        }
    }

    public function bulkDeactivate()
    {
        $service = app(JointClosureService::class);
        $user = Auth::user();

        try {
            if (empty($this->selectedClosures)) {
                session()->flash('error', 'Silakan pilih setidaknya satu Joint Closure!');
                return;
            }

            JointClosure::whereIn('id', $this->selectedClosures)->update(['status' => 'inactive', 'updated_by' => $user->id]);
            session()->flash('success', count($this->selectedClosures) . ' Joint Closure berhasil dinonaktifkan!');
            $this->resetActionState();
        } catch (Throwable $e) {
            session()->flash('error', 'Gagal menonaktifkan: ' . $e->getMessage());
        }
    }

    public function bulkDelete()
    {
        $service = app(JointClosureService::class);
        $user = Auth::user();

        try {
            if (empty($this->selectedClosures)) {
                session()->flash('error', 'Silakan pilih setidaknya satu Joint Closure!');
                return;
            }

            $service->bulkDelete($this->selectedClosures, $user);
            session()->flash('success', count($this->selectedClosures) . ' Joint Closure berhasil dihapus!');
            $this->resetActionState();
        } catch (Throwable $e) {
            session()->flash('error', 'Gagal menghapus: ' . $e->getMessage());
        }
    }

    public function render()
    {
        $query = JointClosure::query()
            ->with(['olt', 'odc', 'parentClosure', 'branch'])
            ->withCount(['odps', 'odcs', 'childClosures'])
            ->when($this->showTrashed, fn($q) => $q->withTrashed())
            ->when($this->search, function ($q) {
                $q->where(function ($sq) {
                    $sq->where('code', 'like', '%' . $this->search . '%')
                       ->orWhere('name', 'like', '%' . $this->search . '%')
                       ->orWhere('address', 'like', '%' . $this->search . '%');
                });
            })
            ->when(!empty($this->filters['status']), fn($q) => $q->where('status', $this->filters['status']))
            ->when(!empty($this->filters['type']), fn($q) => $q->where('type', $this->filters['type']))
            ->when(!empty($this->filters['port_count']), fn($q) => $q->where('port_count', (int)$this->filters['port_count']))
            ->orderBy($this->sortField, $this->sortDirection);

        $closures = $query->paginate($this->perPage);

        // Stats summary
        $stats = [
            'total' => JointClosure::count(),
            'active' => JointClosure::where('status', 'active')->count(),
            'total_cores' => JointClosure::sum('port_count'),
            'dome' => JointClosure::where('type', 'Dome')->count(),
            'inline' => JointClosure::where('type', 'Inline')->count(),
            'wall' => JointClosure::where('type', 'Wall Mount')->count(),
        ];

        return view('livewire.isp.joint-closure.index', [
            'closures' => $closures,
            'stats' => $stats,
        ])->layout('layouts.admin');
    }
}
