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
    public $selectedRoles = '';
    
    public $selectedPermissions = [];

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
                // Ensure IDs are strings for Livewire checkbox binding
                $this->selectedPermissions = $role->permissions->pluck('id')->map(fn($id) => (string)$id)->toArray();
            }
        } else {
            $this->selectedPermissions = [];
        }
    }

    public function checkAllPermissions()
    {
        $this->selectedPermissions = Permission::pluck('id')->map(fn($id) => (string)$id)->toArray();
    }

    public function uncheckAllPermissions()
    {
        $this->selectedPermissions = [];
    }

    public function toggleGroup($groupName)
    {
        $permsInGroup = Permission::all()->filter(function($p) use ($groupName) {
            $name = $p->display_name ?? $p->name;
            $parts = explode(' ', $name, 2);
            $group = count($parts) > 1 ? $parts[1] : 'Lainnya';
            return $group === $groupName;
        })->pluck('id')->map(fn($id) => (string)$id)->toArray();

        $allSelected = true;
        foreach ($permsInGroup as $id) {
            if (!in_array($id, $this->selectedPermissions)) {
                $allSelected = false;
                break;
            }
        }

        if ($allSelected) {
            $this->selectedPermissions = array_values(array_diff($this->selectedPermissions, $permsInGroup));
        } else {
            $this->selectedPermissions = array_unique(array_merge($this->selectedPermissions, $permsInGroup));
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
            'selectedRoles' => 'nullable|exists:roles,id',
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
        
        $user->directPermissions()->sync($this->selectedPermissions);
        $newPermissionIds = array_map('intval', $this->selectedPermissions);

        // WRITE AUDIT LOGS untuk create user + role/permission assignment
        $auditService = app(\App\Services\Auth\AuditLogService::class);
        $auditService->auditUserCreated($user, $createData);
        $auditService->auditRoleSync($user, [], $newRoleIds);
        $auditService->auditUserPermissionSync($user, [], $newPermissionIds);

        // Bust cache navigation global (agar role baru langsung tercermin)
        foreach (\App\Enums\UserRole::allValues() as $roleValue) {
            \Illuminate\Support\Facades\Cache::forget("dsbilling_navigation.{$roleValue}.v3");
        }

        session()->flash('success', 'User berhasil dibuat!');
        return redirect()->route('admin.users.index');
    }

    public function render()
    {
        $roles = Role::whereIn('name', [
            \App\Enums\UserRole::Administrator->value,
            \App\Enums\UserRole::Manager->value,
            \App\Enums\UserRole::Reseller->value,
        ])->get();
        $permissionsGrouped = Permission::all()->groupBy(function($p) {
            $name = $p->display_name ?? $p->name;
            $parts = explode(' ', $name, 2);
            return count($parts) > 1 ? $parts[1] : 'Lainnya';
        });
        
        return view('livewire.admin.user.create', compact('roles', 'permissionsGrouped'));
    }
}



