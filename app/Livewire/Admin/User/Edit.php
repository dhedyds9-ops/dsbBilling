<?php

namespace App\Livewire\Admin\User;

use App\Livewire\AdminComponent;
use App\Models\User as UserModel;
use App\Models\Role;
use App\Models\Permission;
use Illuminate\Support\Facades\Hash;
use Livewire\WithFileUploads;

class Edit extends AdminComponent
{
    use WithFileUploads;

    public UserModel $user;
    public $userId;
    
    public $avatar;
    public $name;
    public $job_title;
    public $job_function;
    public $identity_number;
    public $whatsapp;
    public $address;
    public $email;
    public $username;
    public $is_active;
    public $password;
    public $password_confirmation;
    public $notes;
    public $selectedRoles = '';
    
    public $balance;
    public $is_balance_active;
    public $is_topup_enabled;
    public $ubah_saldo;
    
    public $selectedPermissions = [];
    
    public $showReportSummary = false;

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

    public function mount($id = null)
    {
        parent::mount();
        $this->activeModule = 'admin';
        $this->activePage = 'users';
        $this->userId = $id;
        $this->user = UserModel::findOrFail($id);

        $actor = \Illuminate\Support\Facades\Auth::user();
        
        if ($actor->hasRole(\App\Enums\UserRole::Reseller->value)) {
            if ($this->user->reseller_id !== $actor->getEffectiveResellerId()) {
                abort(403, 'Resellers can only edit users within their own tenant.');
            }
            if ($this->user->hasRole(\App\Enums\UserRole::Administrator->value) || $this->user->hasRole(\App\Enums\UserRole::Manager->value)) {
                abort(403, 'Resellers cannot edit Administrator or Manager accounts.');
            }
        }
        
        if ($actor->hasRole(\App\Enums\UserRole::Manager->value) && !$actor->hasRole(\App\Enums\UserRole::Administrator->value)) {
            if ($this->user->hasRole(\App\Enums\UserRole::Administrator->value)) {
                abort(403, 'Managers cannot edit Administrator accounts.');
            }
        }

        $this->name = $this->user->name;
        $this->job_title = $this->user->job_title;
        $this->job_function = $this->user->job_function;
        $this->identity_number = $this->user->identity_number;
        $this->whatsapp = $this->user->whatsapp;
        $this->address = $this->user->address;
        $this->email = $this->user->email;
        $this->username = $this->user->username ?? $this->user->name;
        $this->is_active = $this->user->is_active;
        $this->notes = $this->user->notes;
        
        $this->balance = $this->user->balance;
        $this->is_balance_active = $this->user->is_balance_active;
        $this->is_topup_enabled = $this->user->is_topup_enabled;
        
        $this->selectedRoles = $this->user->roles->first()->id ?? '';
        $this->selectedPermissions = $this->user->directPermissions->pluck('id')->map(fn($id) => (string)$id)->toArray();
        
        $this->breadcrumbs = [
            ['label' => 'Dashboard', 'url' => route('dashboard')],
            ['label' => 'Administration', 'url' => route('admin.users.index')],
            ['label' => 'Users', 'url' => route('admin.users.index')],
            ['label' => $this->user->name, 'url' => route('admin.users.show', $this->userId)],
            ['label' => 'Edit'],
        ];
    }
    
    public function toggleReportSummary()
    {
        $this->showReportSummary = !$this->showReportSummary;
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

        $validation = [
            'avatar' => 'nullable|image|max:1024',
            'name' => 'required|string|max:255',
            'job_title' => 'nullable|string|max:100',
            'job_function' => ['nullable', new \Illuminate\Validation\Rules\Enum(\App\Enums\JobFunction::class)],
            'identity_number' => 'required|string|max:50',
            'whatsapp' => 'nullable|string|max:255',
            'address' => 'nullable|string',
            'email' => 'required|email|unique:users,email,' . $this->userId,
            'username' => 'required|string|unique:users,username,' . $this->userId,
            'is_active' => 'required|boolean',
            'notes' => 'nullable|string',
            'selectedRoles' => 'nullable|exists:roles,id',
            'is_balance_active' => 'boolean',
            'is_topup_enabled' => 'boolean',
            'ubah_saldo' => 'nullable|numeric',
        ];

        if ($this->password) {
            $validation['password'] = 'min:8|confirmed';
        }

        $this->validate($validation);

        $actor = \Illuminate\Support\Facades\Auth::user();
        $requestedRole = $this->selectedRoles ? \App\Models\Role::find($this->selectedRoles) : null;
        
        if ($actor->hasRole(\App\Enums\UserRole::Reseller->value)) {
            if ($this->user->reseller_id !== $actor->getEffectiveResellerId()) {
                abort(403, 'Resellers can only edit users within their own tenant.');
            }
            if ($this->user->hasRole(\App\Enums\UserRole::Administrator->value) || $this->user->hasRole(\App\Enums\UserRole::Manager->value)) {
                abort(403, 'Resellers cannot edit Administrator or Manager accounts.');
            }
            if ($requestedRole && in_array($requestedRole->name, [\App\Enums\UserRole::Administrator->value, \App\Enums\UserRole::Manager->value])) {
                abort(403, 'Resellers cannot assign Administrator or Manager roles.');
            }
        }
        
        if ($actor->hasRole(\App\Enums\UserRole::Manager->value) && !$actor->hasRole(\App\Enums\UserRole::Administrator->value)) {
            if ($this->user->hasRole(\App\Enums\UserRole::Administrator->value)) {
                abort(403, 'Managers cannot edit Administrator accounts.');
            }
            if ($requestedRole && $requestedRole->name === \App\Enums\UserRole::Administrator->value) {
                abort(403, 'Managers cannot assign Administrator roles.');
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

        $data = [
            'name' => $this->name,
            'job_title' => $this->job_title,
            'job_function' => $this->job_function,
            'identity_number' => $this->identity_number,
            'whatsapp' => $this->whatsapp,
            'address' => $this->address,
            'email' => $this->email,
            'username' => $this->username,
            'is_active' => $this->is_active,
            'notes' => $this->notes,
            'is_balance_active' => $this->is_balance_active,
            'is_topup_enabled' => $this->is_topup_enabled,
        ];

        if ($this->avatar) {
            $data['avatar'] = $this->avatar->store('avatars', 'public');
        }

        if ($this->password) {
            $data['password'] = Hash::make($this->password);
        }
        
        if (!empty($this->ubah_saldo)) {
            $data['balance'] = $this->balance + $this->ubah_saldo;
        }

        // Capture BEFORE values untuk audit
        $auditService = app(\App\Services\Auth\AuditLogService::class);
        $oldProfileValues = $this->user->only(array_keys($data));
        $oldRoleIds = $this->user->roles()->pluck('roles.id')->toArray();
        $oldPermissionIds = $this->user->directPermissions()->pluck('permissions.id')->toArray();

        $this->user->update($data);
        
        $newRoleIds = [];
        if (!empty($this->selectedRoles)) {
            $this->user->roles()->sync([$this->selectedRoles]);
            $newRoleIds = [(int) $this->selectedRoles];
        } else {
            $this->user->roles()->detach();
        }
        
        $this->user->directPermissions()->sync($this->selectedPermissions);
        $newPermissionIds = array_map('intval', $this->selectedPermissions);

        // WRITE AUDIT LOGS (role.manage gate tidak enforced di sini karena AdminComponent
        // sudah diakses hanya oleh administrator via route middleware role:administrator,manager
        // dan index/settings update adalah admin-only)
        $auditService->auditUserUpdated($this->user, $oldProfileValues, $data);
        $auditService->auditRoleSync($this->user, $oldRoleIds, $newRoleIds);
        $auditService->auditUserPermissionSync($this->user, $oldPermissionIds, $newPermissionIds);

        // Bust cache navigation karena role/permission berubah
        $roleName = $this->user->roles->first()->name ?? 'guest';
        \Illuminate\Support\Facades\Cache::forget("dsbilling_navigation.{$roleName}.v3");
        \Illuminate\Support\Facades\Cache::forget("dsbilling_navigation.{$roleName}.{$this->user->id}.v3");
        \Illuminate\Support\Facades\Cache::forget("dsbilling_navigation.administrator.v3");
        \Illuminate\Support\Facades\Cache::forget("dsbilling_navigation.manager.v3");
        \Illuminate\Support\Facades\Cache::forget("dsbilling_navigation.reseller.v3");

        session()->flash('success', 'User berhasil diperbarui!');
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

        return view('livewire.admin.user.edit', compact('roles', 'permissionsGrouped'));
    }
}



