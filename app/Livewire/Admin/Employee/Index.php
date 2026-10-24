<?php

namespace App\Livewire\Admin\Employee;

use App\Livewire\Admin\BaseAdminComponent;
use App\Models\Employee;

class Index extends BaseAdminComponent
{
    public $filterStatus = '';
    public $filterDepartment = '';
    public array $selectedIds = [];
    public bool $selectAll = false;
    public bool $showDeleteModal = false;
    public $deleteId = null;

    public function mount()
    {
        parent::mount();
        $this->activeModule = 'admin';
        $this->activePage = 'employee';
        $this->sortField = 'id';
        $this->sortDirection = 'desc';
        $this->breadcrumbs = [
            ['label' => 'Dashboard', 'url' => route('dashboard')],
            ['label' => 'Kepegawaian', 'url' => '#'],
            ['label' => 'Data Pegawai', 'url' => route('admin.employee.index')],
        ];
    }

    public function updatedSelectAll($value)
    {
        if ($value) {
            $this->selectedIds = $this->getFilteredQuery()->pluck('id')->map(fn($id) => (string)$id)->toArray();
        } else {
            $this->selectedIds = [];
        }
    }

    public function resetFilters()
    {
        $this->search = '';
        $this->filterStatus = '';
        $this->filterDepartment = '';
        $this->selectedIds = [];
        $this->selectAll = false;
        $this->resetPage();
    }

    public function confirmDelete($id)
    {
        $this->deleteId = $id;
        $this->showDeleteModal = true;
    }

    public function deleteSingle()
    {
        if ($this->deleteId) {
            $emp = Employee::find($this->deleteId);
            if ($emp) {
                $emp->delete();
                $this->dispatch('toast', ['type' => 'success', 'message' => 'Data pegawai berhasil dihapus.']);
            }
        }
        $this->showDeleteModal = false;
        $this->deleteId = null;
    }

    public function bulkDelete()
    {
        if (!empty($this->selectedIds)) {
            Employee::whereIn('id', $this->selectedIds)->delete();
            $this->dispatch('toast', ['type' => 'success', 'message' => count($this->selectedIds) . ' data pegawai berhasil dihapus.']);
            $this->selectedIds = [];
            $this->selectAll = false;
        }
    }

    private function getFilteredQuery()
    {
        return Employee::with('user')
            ->when($this->search, function ($query) {
                $query->where(function ($q) {
                    $q->where('name', 'like', '%' . $this->search . '%')
                      ->orWhere('nik', 'like', '%' . $this->search . '%')
                      ->orWhere('phone', 'like', '%' . $this->search . '%')
                      ->orWhere('position', 'like', '%' . $this->search . '%');
                });
            })
            ->when($this->filterStatus !== '', fn($q) => $q->where('status', $this->filterStatus))
            ->when($this->filterDepartment !== '', fn($q) => $q->where('department', $this->filterDepartment));
    }

    public function render()
    {
        $employees = $this->getFilteredQuery()
            ->orderBy($this->sortField, $this->sortDirection)
            ->paginate($this->perPage);

        $stats = [
            'total' => Employee::count(),
            'active' => Employee::where('status', 'active')->count(),
            'inactive' => Employee::where('status', 'inactive')->count(),
            'with_user' => Employee::whereNotNull('user_id')->count(),
        ];

        return view('livewire.admin.employee.index', [
            'employees' => $employees,
            'stats' => $stats,
        ]);
    }
}
