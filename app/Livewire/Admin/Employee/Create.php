<?php

namespace App\Livewire\Admin\Employee;

use App\Models\Employee;
use Livewire\Component;

class Create extends Component
{
    public $nik;
    public $name;
    public $position;
    public $department;
    public $phone;
    public $email;
    public $join_date;
    public $base_salary;
    public $bank_name;
    public $bank_account;
    public $status = 'active';
    public $user_id;

    public function rules() { return [
        'nik' => 'required|unique:employees,nik',
        'name' => 'required|string|max:255',
        'position' => 'nullable|string|max:255',
        'department' => ['nullable', new \Illuminate\Validation\Rules\Enum(\App\Enums\JobFunction::class)],
        'phone' => 'nullable|string|max:20',
        'email' => 'nullable|email|max:255',
        'join_date' => 'nullable|date',
        'base_salary' => 'nullable|numeric',
        'bank_name' => 'nullable|string|max:255',
        'bank_account' => 'nullable|string|max:255',
        'status' => 'required|string|in:active,inactive',
        'user_id' => 'nullable|exists:users,id',
    ]; }

    public function save()
    {
        $this->validate();

        Employee::create([
            'nik' => $this->nik,
            'name' => $this->name,
            'position' => $this->position,
            'department' => $this->department ?: null,
            'phone' => $this->phone,
            'email' => $this->email,
            'join_date' => $this->join_date ?: null,
            'base_salary' => $this->base_salary ?: 0,
            'bank_name' => $this->bank_name,
            'bank_account' => $this->bank_account,
            'status' => $this->status,
            'user_id' => $this->user_id ?: null,
        ]);

        session()->flash('message', 'Employee created successfully.');

        return redirect('/admin/employee');
    }

    public function render()
    {
        $users = \App\Models\User::whereHas('roles', function($query) {
            $query->whereNotIn('name', ['customer', 'reseller']);
        })->get();
        return view('livewire.admin.employee.create', compact('users'));
    }
}
