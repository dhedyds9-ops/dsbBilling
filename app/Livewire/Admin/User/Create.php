<?php

namespace App\Livewire\Admin\User;

use App\Livewire\AdminComponent;
use App\Models\User as UserModel;
use App\Models\Role;
use App\Models\Permission;
use Illuminate\Support\Facades\Hash;
use Livewire\WithFileUploads;

class Create extends AdminComponent
{
    use WithFileUploads;

    public $avatar;
    public $name;
    public $job_title;
    public $division;
    public $job_function;
    public $identity_number;
    public $whatsapp;
    public $address;
    public $email;
    public $username;
    public $is_active = 1;
    public $password;
    public $password_confirmation;
    public $notes;
    public $branch_id = null;
    public $wilayah = '';
    public $selectedRoles = '';
    
    public bool $custom_permissions = false;
    public array $selectedPermissions = [];
    public string $permissionSearch = '';

    // Profil Kepegawaian Otomatis (HR)
    public bool $create_employee_profile = true;
    public $employee_join_date = null;
    public $employee_base_salary = null;
    public $employee_bank_name = '';
    public $employee_bank_account = '';

    public function updatedJobTitle($value)
    {
        if ($value) {
            $this->job_function = \App\Enums\JobFunction::resolveJobFunction($value)->value;
            $this->division = \App\Enums\JobFunction::resolveDivision($value);
            $this->updatedJobFunction($this->job_function);

            $target = strtolower((string)$value);
            $roleNameToSelect = null;

            if (str_contains($target, 'gudang') || str_contains($target, 'logistik')) {
                $roleNameToSelect = \App\Enums\UserRole::AdminGudang->value;
            } elseif (str_contains($target, 'kasir') || str_contains($target, 'billing')) {
                $roleNameToSelect = \App\Enums\UserRole::Kasir->value;
            } elseif (str_contains($target, 'customer service') || str_contains($target, 'cs')) {
                $roleNameToSelect = \App\Enums\UserRole::CustomerService->value;
            } elseif (str_contains($target, 'noc') || str_contains($target, 'monitoring')) {
                $roleNameToSelect = \App\Enums\UserRole::Noc->value;
            } elseif (str_contains($target, 'teknisi') || str_contains($target, 'technician')) {
                $roleNameToSelect = \App\Enums\UserRole::Teknisi->value;
            } elseif (str_contains($target, 'marketing') || str_contains($target, 'sales')) {
                $roleNameToSelect = \App\Enums\UserRole::Marketing->value;
            }

            if ($roleNameToSelect) {
                $matchedRole = Role::where('name', $roleNameToSelect)->first();
                if ($matchedRole) {
                    $this->selectedRoles = (string)$matchedRole->id;
                    $this->updatedSelectedRoles($this->selectedRoles);
                    return;
                }
            }

            // Fallback jika belum ada role yang cocok
            if (empty($this->selectedRoles)) {
                $managerRole = Role::where('name', \App\Enums\UserRole::Manager->value)->first();
                if ($managerRole) {
                    $this->selectedRoles = (string)$managerRole->id;
                    $this->updatedSelectedRoles($this->selectedRoles);
                }
            }
        }
    }

    public function updatedJobFunction($value)
    {
        if ($value === \App\Enums\JobFunction::TECHNICIAN->value) {
            $recommended = [
                \App\Enums\UserPermission::TechnicianPortal->value,
                \App\Enums\UserPermission::WorkforceJobsView->value,
                \App\Enums\UserPermission::WorkforceJobsExecute->value,
                \App\Enums\UserPermission::WorkforceQcView->value,
                \App\Enums\UserPermission::WorkforceQcExecute->value,
                \App\Enums\UserPermission::WorkforceMaterialView->value,
                \App\Enums\UserPermission::WorkforceMaterialExecute->value,
                \App\Enums\UserPermission::WorkforceAttendanceExecute->value,
            ];
            
            $perms = Permission::whereIn('name', $recommended)->get();
            foreach ($perms as $p) {
                if (!in_array((string)$p->id, $this->selectedPermissions)) {
                    $this->selectedPermissions[] = (string)$p->id;
                }
            }
        }
    }

    public function mount()
    {
        parent::mount();
        $this->activeModule = 'admin';
        $this->activePage = 'users';
        $this->employee_join_date = date('Y-m-d');

        // Auto-seed: pastikan seluruh role standar ISP sinkron ke database
        if (Role::whereIn('name', ['admin_gudang', 'kasir', 'customer_service', 'noc', 'teknisi', 'marketing'])->count() < 6) {
            \App\Services\Auth\PermissionRegistry::syncAllPermissionsToDatabase();
        }

        $this->breadcrumbs = [
            ['label' => 'Dashboard', 'url' => route('dashboard')],
            ['label' => 'Administration', 'url' => route('admin.users.index')],
            ['label' => 'Users', 'url' => route('admin.users.index')],
            ['label' => 'Create'],
        ];
    }
    
    public function updatedSelectedRoles($roleId)
    {
        if ($roleId) {
            $role = Role::with('permissions')->find($roleId);
            if ($role) {
                // If reseller, do not create employee profile by default
                if ($role->name === \App\Enums\UserRole::Reseller->value) {
                    $this->create_employee_profile = false;
                } else {
                    $this->create_employee_profile = true;
                }

                // Ensure IDs are strings for Livewire checkbox binding
                $this->selectedPermissions = $role->permissions->pluck('id')->map(fn($id) => (string)$id)->toArray();

                // Sinkronkan Posisi/Jabatan sesuai role jika belum spesifik
                if ($role->name === \App\Enums\UserRole::AdminGudang->value) {
                    $this->job_title = 'Admin Gudang / Logistik';
                    $this->job_function = \App\Enums\JobFunction::PENGURUS->value;
                    $this->division = 'Logistik & Gudang';
                } elseif ($role->name === \App\Enums\UserRole::Kasir->value) {
                    $this->job_title = 'Kasir Billing';
                    $this->job_function = \App\Enums\JobFunction::PENGURUS->value;
                    $this->division = 'Layanan Internet & Jaringan';
                } elseif ($role->name === \App\Enums\UserRole::CustomerService->value) {
                    $this->job_title = 'Customer Service';
                    $this->job_function = \App\Enums\JobFunction::PENGURUS->value;
                    $this->division = 'Layanan Internet & Jaringan';
                } elseif ($role->name === \App\Enums\UserRole::Noc->value) {
                    $this->job_title = 'NOC / Monitoring';
                    $this->job_function = \App\Enums\JobFunction::NOC->value;
                    $this->division = 'Layanan Internet & Jaringan';
                } elseif ($role->name === \App\Enums\UserRole::Teknisi->value) {
                    $this->job_title = 'Teknisi Lapangan';
                    $this->job_function = \App\Enums\JobFunction::TECHNICIAN->value;
                    $this->division = 'Layanan Internet & Jaringan';
                } elseif ($role->name === \App\Enums\UserRole::Marketing->value) {
                    $this->job_title = 'Marketing Internet';
                    $this->job_function = \App\Enums\JobFunction::SALES->value;
                    $this->division = 'Layanan Internet & Jaringan';
                } elseif ($role->name === \App\Enums\UserRole::Administrator->value) {
                    $this->job_title = 'Super Administrator';
                    $this->job_function = \App\Enums\JobFunction::PENGURUS->value;
                    $this->division = 'Manajemen & IT';
                }
            }
        } else {
            $this->selectedPermissions = [];
        }
    }

    public function updatedCustomPermissions($value)
    {
        if ($value && empty($this->selectedPermissions)) {
            $this->resetToRoleDefault();
        }
    }

    public function selectAllRolePermissions()
    {
        if ($this->selectedRoles) {
            $role = Role::find($this->selectedRoles);
            if ($role && $role->name === \App\Enums\UserRole::Reseller->value) {
                $resellerPermNames = \App\Enums\UserPermission::defaultResellerPermissions();
                $this->selectedPermissions = Permission::whereIn('name', $resellerPermNames)->pluck('id')->map(fn($id) => (string)$id)->toArray();
                return;
            }
        }
        $this->selectedPermissions = Permission::pluck('id')->map(fn($id) => (string)$id)->toArray();
    }

    public function resetToRoleDefault()
    {
        if ($this->selectedRoles) {
            $role = Role::with('permissions')->find($this->selectedRoles);
            if ($role) {
                $this->selectedPermissions = $role->permissions->pluck('id')->map(fn($id) => (string)$id)->toArray();
                return;
            }
        }
        $this->selectedPermissions = [];
    }

    public function clearAllPermissions()
    {
        $this->selectedPermissions = [];
    }

    public function toggleCategory(string $categoryKey)
    {
        $grouped = \App\Services\Auth\PermissionRegistry::getGroupedPermissions();
        if (!isset($grouped[$categoryKey])) {
            return;
        }

        $categoryPermIds = $grouped[$categoryKey]['permissions']->pluck('id')->map(fn($id) => (string)$id)->toArray();
        $isAllSelected = count(array_intersect($categoryPermIds, $this->selectedPermissions)) === count($categoryPermIds);

        if ($isAllSelected) {
            $this->selectedPermissions = array_values(array_diff($this->selectedPermissions, $categoryPermIds));
        } else {
            $this->selectedPermissions = array_values(array_unique(array_merge($this->selectedPermissions, $categoryPermIds)));
        }
    }

    public function save()
    {
        \Illuminate\Support\Facades\Gate::authorize('role.manage');

        $this->validate([
            'avatar' => 'nullable|image|max:1024',
            'name' => 'required|string|max:255',
            'job_title' => 'nullable|string|max:100',
            'job_function' => ['nullable', new \Illuminate\Validation\Rules\Enum(\App\Enums\JobFunction::class)],
            'identity_number' => 'required|string|max:50',
            'whatsapp' => 'nullable|string|max:255',
            'address' => 'nullable|string',
            'email' => 'required|email|unique:users,email',
            'username' => 'required|string|unique:users,username',
            'is_active' => 'required|boolean',
            'password' => 'required|min:8|confirmed',
            'notes' => 'nullable|string',
            'selectedRoles' => 'required|exists:roles,id',
        ]);

        $actor = \Illuminate\Support\Facades\Auth::user();
        $requestedRole = $this->selectedRoles ? \App\Models\Role::find($this->selectedRoles) : null;
        
        if ($requestedRole) {
            if ($actor->hasRole(\App\Enums\UserRole::Reseller->value)) {
                if (in_array($requestedRole->name, [\App\Enums\UserRole::Administrator->value, \App\Enums\UserRole::Manager->value])) {
                    abort(403, 'Resellers cannot create Administrator or Manager accounts.');
                }
            }
            
            if ($actor->hasRole(\App\Enums\UserRole::Manager->value) && !$actor->hasRole(\App\Enums\UserRole::Administrator->value)) {
                if ($requestedRole->name === \App\Enums\UserRole::Administrator->value) {
                    abort(403, 'Managers cannot create Administrator accounts.');
                }
            }
        }

        // PERMISSION AUTHORIZATION (SVA-001)
        if (is_array($this->selectedPermissions) && count($this->selectedPermissions) > 0) {
            $requestedPermNames = \App\Models\Permission::whereIn('id', $this->selectedPermissions)->pluck('name')->toArray();
            
            if (!$actor->hasRole(\App\Enums\UserRole::Administrator->value)) {
                $allowedPerms = [];
                if ($actor->hasRole(\App\Enums\UserRole::Reseller->value)) {
                    $allowedPerms = \App\Enums\UserPermission::defaultResellerPermissions();
                } elseif ($actor->hasRole(\App\Enums\UserRole::Manager->value)) {
                    $allowedPerms = \App\Enums\UserPermission::defaultManagerPermissions();
                }
                
                foreach ($requestedPermNames as $permName) {
                    if (!in_array($permName, $allowedPerms)) {
                        abort(403, 'Unauthorized permission requested: ' . $permName);
                    }
                }
            }
        }

        $resellerId = null;
        if ($actor->hasRole(\App\Enums\UserRole::Reseller->value)) {
            $resellerId = $actor->getEffectiveResellerId();
        }

        $avatarPath = null;
        if ($this->avatar) {
            $avatarPath = $this->avatar->store('avatars', 'public');
        }

        $createData = [
            'name' => $this->name,
            'job_title' => $this->job_title,
            'job_function' => $this->job_function,
            'reseller_id' => $resellerId,
            'email' => $this->email,
            'username' => $this->username,
            'whatsapp' => $this->whatsapp,
            'identity_number' => $this->identity_number,
            'address' => $this->address,
            'is_active' => $this->is_active,
            'notes' => $this->notes,
            'branch_id' => $this->branch_id ?: null,
            'wilayah' => $this->wilayah ? trim($this->wilayah) : null,
            'avatar' => $avatarPath,
            'balance' => 0,
            'is_balance_active' => 1,
            'is_topup_enabled' => 1,
        ];

        $user = UserModel::create(array_merge($createData, [
            'password' => Hash::make($this->password),
        ]));

        $newRoleIds = [];
        if (!empty($this->selectedRoles)) {
            $user->roles()->sync([$this->selectedRoles]);
            $newRoleIds = [(int) $this->selectedRoles];
        }
        
        $newPermissionIds = [];
        if ($this->custom_permissions) {
            $user->directPermissions()->sync($this->selectedPermissions);
            $newPermissionIds = array_map('intval', $this->selectedPermissions);
        } else {
            $user->directPermissions()->detach();
        }

        // WRITE AUDIT LOGS untuk create user + role/permission assignment
        $auditService = app(\App\Services\Auth\AuditLogService::class);
        $auditService->auditUserCreated($user, $createData);
        $auditService->auditRoleSync($user, [], $newRoleIds);
        $auditService->auditUserPermissionSync($user, [], $newPermissionIds);

        // Otomatis buatkan data Employee jika ini akun internal staf (bukan reseller / customer)
        if ($this->create_employee_profile && !$user->isReseller() && !$user->hasRole('customer')) {
            $existingEmp = \App\Models\Employee::where('nik', $this->identity_number)->first();
            if (!$existingEmp) {
                \App\Models\Employee::create([
                    'nik' => $this->identity_number,
                    'name' => $this->name,
                    'position' => $this->job_title ?: ($requestedRole?->display_name ?? $requestedRole?->name ?? 'Staff'),
                    'department' => $this->job_function ?: null,
                    'phone' => $this->whatsapp,
                    'email' => $this->email,
                    'join_date' => $this->employee_join_date ?: date('Y-m-d'),
                    'base_salary' => $this->employee_base_salary ?: 0,
                    'bank_name' => $this->employee_bank_name ?: null,
                    'bank_account' => $this->employee_bank_account ?: null,
                    'status' => $this->is_active ? 'active' : 'inactive',
                    'user_id' => $user->id,
                ]);
            } else {
                if (empty($existingEmp->user_id)) {
                    $existingEmp->update(['user_id' => $user->id]);
                }
            }
        }

        // Bust cache navigation global (agar role baru langsung tercermin)
        foreach (\App\Enums\UserRole::allValues() as $roleValue) {
            \Illuminate\Support\Facades\Cache::forget("dsbilling_navigation.{$roleValue}.v3");
        }

        session()->flash('success', 'User & profil pegawai berhasil dibuat!');
        return redirect()->route('admin.users.index');
    }

    public function render()
    {
        $roles = Role::whereNotIn('name', Role::hiddenInUserManagement())->get();
        $branches = \App\Models\Master\Branch::where('is_active', true)->get();
        $permissionCategories = \App\Services\Auth\PermissionRegistry::getGroupedPermissions($this->permissionSearch);
        
        return view('livewire.admin.user.create', compact('roles', 'branches', 'permissionCategories'));
    }
}




