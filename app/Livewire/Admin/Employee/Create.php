<?php

namespace App\Livewire\Admin\Employee;

use App\Livewire\AdminComponent;
use App\Models\Employee;
use App\Models\User;
use App\Models\Role;
use App\Enums\UserRole;
use App\Enums\JobFunction;
use Illuminate\Support\Facades\Hash;

class Create extends AdminComponent
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

    // Kredensial Akun User Login Baru
    public bool $create_user_account = true;
    public string $username = '';
    public string $password = 'Password123!';
    public string $role_id = '';

    public function mount()
    {
        parent::mount();
        $this->activeModule = 'admin';
        $this->activePage = 'employee';
        $this->breadcrumbs = [
            ['label' => 'Dashboard', 'url' => route('dashboard')],
            ['label' => 'Kepegawaian', 'url' => '#'],
            ['label' => 'Data Pegawai', 'url' => route('admin.employee.index')],
            ['label' => 'Tambah Pegawai'],
        ];

        // Default Role: Manager (Staff Internal)
        $managerRole = Role::where('name', UserRole::Manager->value ?? 'manager')->first();
        if ($managerRole) {
            $this->role_id = (string) $managerRole->id;
        }
    }

    public function updatedName($value)
    {
        if (empty($this->username) && !empty($value)) {
            $baseName = strtolower(preg_replace('/[^a-zA-Z0-9]/', '', str_replace(' ', '', $value)));
            $username = $baseName ?: 'user' . rand(100, 999);
            $counter = 1;
            while (User::where('username', $username)->exists()) {
                $username = $baseName . $counter++;
            }
            $this->username = $username;
        }
    }

    public function updatedPosition($value)
    {
        if ($value) {
            $this->department = JobFunction::resolveJobFunction($value)->value;

            // Auto-detect matching Role from Position
            $target = strtolower(trim((string)$value));
            $roleNameToSelect = null;

            if (str_contains($target, 'gudang') || str_contains($target, 'logistik')) {
                $roleNameToSelect = UserRole::AdminGudang->value;
            } elseif (str_contains($target, 'kasir') || str_contains($target, 'billing')) {
                $roleNameToSelect = UserRole::Kasir->value;
            } elseif (str_contains($target, 'customer service') || str_contains($target, 'cs')) {
                $roleNameToSelect = UserRole::CustomerService->value;
            } elseif (str_contains($target, 'noc') || str_contains($target, 'monitoring') || str_contains($target, 'network')) {
                $roleNameToSelect = UserRole::Noc->value;
            } elseif (str_contains($target, 'teknisi') || str_contains($target, 'technician')) {
                $roleNameToSelect = UserRole::Teknisi->value;
            } elseif (str_contains($target, 'marketing') || str_contains($target, 'sales')) {
                $roleNameToSelect = UserRole::Marketing->value;
            } elseif (str_contains($target, 'admin') || str_contains($target, 'administrator')) {
                $roleNameToSelect = UserRole::Administrator->value;
            }

            if ($roleNameToSelect) {
                $matchedRole = Role::where('name', $roleNameToSelect)->first();
                if ($matchedRole) {
                    $this->role_id = (string) $matchedRole->id;
                }
            }
        }
    }

    public function rules()
    {
        $rules = [
            'nik' => 'required|unique:employees,nik',
            'name' => 'required|string|max:255',
            'position' => 'nullable|string|max:255',
            'department' => ['nullable', new \Illuminate\Validation\Rules\Enum(JobFunction::class)],
            'phone' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'join_date' => 'nullable|date',
            'base_salary' => 'nullable|numeric',
            'bank_name' => 'nullable|string|max:255',
            'bank_account' => 'nullable|string|max:255',
            'status' => 'required|string|in:active,inactive',
        ];

        if ($this->create_user_account) {
            $rules['username'] = 'required|string|min:3|max:50|unique:users,username';
            $rules['password'] = 'required|string|min:6';
            $rules['role_id'] = 'required|exists:roles,id';
        } else {
            $rules['user_id'] = 'nullable|exists:users,id';
        }

        return $rules;
    }

    public function save()
    {
        $this->validate();

        $assignedUserId = null;

        if ($this->create_user_account) {
            $userEmail = !empty($this->email) ? strtolower(trim($this->email)) : ($this->username . '@mstore.local');

            $newUser = User::create([
                'name' => $this->name,
                'email' => $userEmail,
                'username' => $this->username,
                'password' => Hash::make($this->password ?: 'Password123!'),
                'job_title' => $this->position ?: 'Staff',
                'job_function' => $this->department ?: JobFunction::TECHNICIAN->value,
                'whatsapp' => $this->phone,
                'identity_number' => $this->nik ?: '-',
                'is_active' => ($this->status === 'active'),
            ]);

            if ($this->role_id) {
                $newUser->roles()->sync([(int)$this->role_id]);

                $selectedRole = Role::find($this->role_id);
                if ($selectedRole && ($selectedRole->name === UserRole::Teknisi->value || $newUser->job_function === JobFunction::TECHNICIAN->value)) {
                    $techPerms = \App\Models\Permission::whereIn('name', [
                        \App\Enums\UserPermission::TechnicianPortal->value,
                        \App\Enums\UserPermission::WorkforceJobsView->value,
                        \App\Enums\UserPermission::WorkforceJobsExecute->value,
                        \App\Enums\UserPermission::WorkforceQcView->value,
                        \App\Enums\UserPermission::WorkforceQcExecute->value,
                        \App\Enums\UserPermission::WorkforceMaterialView->value,
                        \App\Enums\UserPermission::WorkforceMaterialExecute->value,
                        \App\Enums\UserPermission::WorkforceAttendanceExecute->value,
                    ])->pluck('id')->toArray();
                    $newUser->directPermissions()->sync($techPerms);
                }
            }

            $assignedUserId = $newUser->id;
        } else {
            $assignedUserId = $this->user_id ?: null;
        }

        $emp = Employee::create([
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
            'user_id' => $assignedUserId,
        ]);

        if ($assignedUserId && $this->department) {
            User::where('id', $assignedUserId)->update(['job_function' => $this->department]);
        }

        if ($this->create_user_account) {
            session()->flash('success', "Pegawai berhasil ditambahkan & akun login dibuat! Username: {$this->username} | Password: {$this->password}");
        } else {
            session()->flash('success', 'Data pegawai berhasil ditambahkan.');
        }

        return redirect()->route('admin.employee.index');
    }

    public function render()
    {
        $roles = Role::whereNotIn('name', ['customer', 'reseller'])->orderBy('name')->get();

        $users = User::whereHas('roles', function($query) {
            $query->whereNotIn('name', ['customer', 'reseller']);
        })->get();

        return view('livewire.admin.employee.create', compact('users', 'roles'));
    }
}
