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
        $roles = Role::whereIn('name', [
            UserRole::Manager->value,
            UserRole::Administrator->value,
        ])->get();

        $users = User::whereHas('roles', function($query) {
            $query->whereNotIn('name', ['customer', 'reseller']);
        })->get();

        return view('livewire.admin.employee.create', compact('users', 'roles'));
    }
}
