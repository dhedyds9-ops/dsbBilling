<?php

namespace App\Livewire\Admin\Employee;

use App\Livewire\Admin\BaseAdminComponent;
use App\Models\Employee;

class Index extends BaseAdminComponent
{
    public function mount()
    {
        parent::mount();
        $this->activeModule = 'admin';
        $this->activePage = 'employee';
        $this->breadcrumbs = [
            ['label' => 'Dashboard', 'url' => route('dashboard')],
            ['label' => 'Administration', 'url' => route('admin.users.index')],
            ['label' => 'Employees'],
        ];
    }

    public function delete(Employee $employee)
    {
        $employee->delete();
        session()->flash('message', 'Employee deleted successfully.');
    }

    public function render()
    {
        $employees = Employee::when($this->search, function ($query) {
                $query->where(function ($q) {
                    $q->where('name', 'like', '%' . $this->search . '%')
                      ->orWhere('nik', 'like', '%' . $this->search . '%');
                });
            })
            ->orderBy($this->sortField, $this->sortDirection)
            ->paginate($this->perPage);

        return view('livewire.admin.employee.index', [
            'employees' => $employees,
        ]);
    }
}
