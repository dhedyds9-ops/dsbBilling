<?php

namespace App\Livewire\Admin\Employee;

use App\Models\Employee;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;

    public $search = '';

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function delete(Employee $employee)
    {
        $employee->delete();
        session()->flash('message', 'Employee deleted successfully.');
    }

    public function render()
    {
        $employees = Employee::where('name', 'like', '%' . $this->search . '%')
            ->orWhere('nik', 'like', '%' . $this->search . '%')
            ->paginate(10);

        return view('livewire.admin.employee.index', [
            'employees' => $employees,
        ]);
    }
}
