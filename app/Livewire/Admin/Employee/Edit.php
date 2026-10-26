<?php

namespace App\Livewire\Admin\Employee;

use App\Livewire\AdminComponent;
use App\Models\Employee;

class Edit extends AdminComponent
{
    public Employee $employee;
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
    public $status;
    public $user_id;



    public function rules()
    {
        return [
            'nik' => 'required|unique:employees,nik,' . $this->employee->id,
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
        ];
    }

    public function mount($id = null)
    {
        parent::mount();
        $this->activeModule = 'admin';
        $this->activePage = 'employee';
        $this->breadcrumbs = [
            ['label' => 'Dashboard', 'url' => route('dashboard')],
            ['label' => 'Kepegawaian', 'url' => '#'],
            ['label' => 'Data Pegawai', 'url' => route('admin.employee.index')],
            ['label' => 'Edit Pegawai'],
        ];

        $this->employee = \App\Models\Employee::findOrFail($id);
        $employee = $this->employee;
        $this->nik = $employee->nik;
        $this->name = $employee->name;
        $this->position = $employee->position;
        $this->department = $employee->department;
        $this->phone = $employee->phone;
        $this->email = $employee->email;
        $this->join_date = $employee->join_date;
        $this->base_salary = $employee->base_salary;
        $this->bank_name = $employee->bank_name;
        $this->bank_account = $employee->bank_account;
        $this->status = $employee->status;
        $this->user_id = $employee->user_id;
    }

    public function save()
    {
        $this->validate();

        $this->employee->update([
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

        if ($this->user_id && $this->department) {
            \App\Models\User::where('id', $this->user_id)->update(['job_function' => $this->department]);
        }

        session()->flash('success', 'Data pegawai berhasil diperbarui.');

        return redirect()->route('admin.employee.index');
    }

    public function createUserAccount()
    {
        try {
            $baseName = strtolower(preg_replace('/[^a-zA-Z0-9]/', '', str_replace(' ', '', $this->name)));
            $username = $baseName ?: 'user' . rand(100, 999);
            $counter = 1;
            while (\App\Models\User::where('username', $username)->exists()) {
                $username = $baseName . $counter++;
            }

            $userEmail = ! empty($this->email) ? strtolower(trim($this->email)) : ($username . '@mstore.local');
            $defaultPassword = 'Password123!';

            $newUser = \App\Models\User::create([
                'name' => $this->name,
                'email' => $userEmail,
                'username' => $username,
                'password' => \Illuminate\Support\Facades\Hash::make($defaultPassword),
                'job_title' => $this->position ?: 'Staff',
                'job_function' => $this->department ?: \App\Enums\JobFunction::TECHNICIAN->value,
                'whatsapp' => $this->phone,
                'identity_number' => $this->nik ?: '-',
                'is_active' => true,
            ]);

            $managerRole = \App\Models\Role::where('name', \App\Enums\UserRole::Manager->value ?? 'manager')->first();
            if ($managerRole) {
                $newUser->roles()->sync([$managerRole->id]);
            }

            $this->user_id = $newUser->id;
            $this->employee->update(['user_id' => $newUser->id]);

            session()->flash('success', "Akun login berhasil dibuat! Username: {$username} | Password: {$defaultPassword}");
        } catch (\Throwable $e) {
            session()->flash('error', 'Gagal membuat akun login: ' . $e->getMessage());
        }
    }

    public function render()
    {
        $users = \App\Models\User::whereHas('roles', function($query) {
            $query->whereNotIn('name', ['customer', 'reseller']);
        })->get();
        return view('livewire.admin.employee.edit', compact('users'));
    }
}
