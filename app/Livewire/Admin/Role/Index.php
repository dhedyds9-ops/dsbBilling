<?php

namespace App\Livewire\Admin\Role;

use App\Enums\UserRole;
use App\Livewire\AdminComponent;
use App\Models\Role;
use App\Models\User;
use App\Services\Auth\AuditLogService;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;
use Livewire\WithPagination;

class Index extends AdminComponent
{
    use WithPagination;

    public string $search = '';
    public string $filterType = 'all'; // all, system, custom

    // Modal Create / Edit Role
    public bool $showRoleModal = false;
    public ?int $editingRoleId = null;
    public string $roleName = '';
    public string $roleDisplayName = '';
    public string $roleDescription = '';

    // Modal Delete Confirmation
    public bool $showDeleteModal = false;
    public ?int $deletingRoleId = null;
    public ?string $deletingRoleName = '';
    public int $deletingRoleUserCount = 0;

    protected $queryString = [
        'search' => ['except' => ''],
        'filterType' => ['except' => 'all'],
    ];

    public function mount()
    {
        parent::mount();
        $this->activeModule = 'admin';
        $this->activePage = 'roles';
        $this->breadcrumbs = [
            ['label' => 'Dashboard', 'url' => route('dashboard')],
            ['label' => 'Administration', 'url' => route('admin.users.index')],
            ['label' => 'Role & Hak Akses'],
        ];
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingFilterType()
    {
        $this->resetPage();
    }

    public function openCreateModal(): void
    {
        Gate::authorize('role.manage');
        $this->resetValidation();
        $this->editingRoleId = null;
        $this->roleName = '';
        $this->roleDisplayName = '';
        $this->roleDescription = '';
        $this->showRoleModal = true;
    }

    public function openEditModal(int $id): void
    {
        Gate::authorize('role.manage');
        $this->resetValidation();
        $role = Role::findOrFail($id);
        $this->editingRoleId = $role->id;
        $this->roleName = $role->name;
        $this->roleDisplayName = $role->display_name;
        $this->roleDescription = $role->description ?? '';
        $this->showRoleModal = true;
    }

    public function updatedRoleDisplayName($value): void
    {
        // Auto-generate slug jika membuat role baru
        if (!$this->editingRoleId && empty($this->roleName)) {
            $this->roleName = Str::slug($value, '_');
        }
    }

    public function saveRole(): void
    {
        Gate::authorize('role.manage');

        $isEditing = !empty($this->editingRoleId);
        $rules = [
            'roleDisplayName' => 'required|string|max:100',
            'roleDescription' => 'nullable|string|max:255',
        ];

        if ($isEditing) {
            $role = Role::findOrFail($this->editingRoleId);
            // System roles tidak boleh diubah slug name-nya
            if (!$role->is_system) {
                $rules['roleName'] = 'required|string|max:50|alpha_dash|unique:roles,name,' . $this->editingRoleId;
            }
        } else {
            $rules['roleName'] = 'required|string|max:50|alpha_dash|unique:roles,name';
        }

        $this->validate($rules);

        if ($isEditing) {
            $role = Role::findOrFail($this->editingRoleId);
            $role->display_name = $this->roleDisplayName;
            $role->description = $this->roleDescription;
            if (!$role->is_system) {
                $role->name = strtolower(trim($this->roleName));
            }
            $role->save();
            session()->flash('success', "Role '{$role->display_name}' berhasil diperbarui.");
        } else {
            $slug = strtolower(trim($this->roleName));
            $role = Role::create([
                'name' => $slug,
                'display_name' => $this->roleDisplayName,
                'description' => $this->roleDescription,
            ]);
            session()->flash('success', "Role baru '{$role->display_name}' berhasil dibuat.");
        }

        // Invalidate navigation cache
        foreach (UserRole::allValues() as $roleVal) {
            \Illuminate\Support\Facades\Cache::forget("dsbilling_navigation.{$roleVal}.v3");
        }

        $this->showRoleModal = false;
    }

    public function confirmDelete(int $id): void
    {
        Gate::authorize('role.manage');
        $role = Role::withCount('users')->findOrFail($id);

        if ($role->is_system) {
            session()->flash('error', "Role sistem '{$role->display_name}' dilindungi dan tidak dapat dihapus.");
            return;
        }

        $this->deletingRoleId = $role->id;
        $this->deletingRoleName = $role->display_name;
        $this->deletingRoleUserCount = $role->users_count;
        $this->showDeleteModal = true;
    }

    public function deleteRole(): void
    {
        Gate::authorize('role.manage');
        if (!$this->deletingRoleId) {
            return;
        }

        $role = Role::withCount('users')->findOrFail($this->deletingRoleId);
        if ($role->is_system) {
            session()->flash('error', "Role sistem tidak dapat dihapus.");
            $this->showDeleteModal = false;
            return;
        }

        if ($role->users_count > 0) {
            session()->flash('error', "Role '{$role->display_name}' masih digunakan oleh {$role->users_count} user. Pindahkan user ke role lain terlebih dahulu.");
            $this->showDeleteModal = false;
            return;
        }

        $name = $role->display_name;
        $role->permissions()->detach();
        $role->delete();

        // Invalidate navigation cache
        foreach (UserRole::allValues() as $roleVal) {
            \Illuminate\Support\Facades\Cache::forget("dsbilling_navigation.{$roleVal}.v3");
        }

        session()->flash('success', "Role '{$name}' berhasil dihapus.");
        $this->showDeleteModal = false;
        $this->deletingRoleId = null;
    }

    public function render()
    {
        $query = Role::query()->withCount(['users', 'permissions']);

        if (!empty($this->search)) {
            $s = trim($this->search);
            $query->where(function ($q) use ($s) {
                $q->where('name', 'like', "%{$s}%")
                  ->orWhere('display_name', 'like', "%{$s}%")
                  ->orWhere('description', 'like', "%{$s}%");
            });
        }

        if ($this->filterType === 'system') {
            $query->whereIn('name', Role::allSystemRoleNames());
        } elseif ($this->filterType === 'custom') {
            $query->whereNotIn('name', Role::allSystemRoleNames());
        }

        // Hide customer from role management by default unless specifically searched
        if (empty($this->search)) {
            $query->whereNotIn('name', Role::hiddenInUserManagement());
        }

        $roles = $query->orderBy('id', 'asc')->paginate(10);

        return view('livewire.admin.role.index', [
            'roles' => $roles,
            'totalRolesCount' => Role::whereNotIn('name', Role::hiddenInUserManagement())->count(),
            'systemRolesCount' => Role::whereIn('name', Role::allSystemRoleNames())->whereNotIn('name', Role::hiddenInUserManagement())->count(),
            'customRolesCount' => Role::whereNotIn('name', Role::allSystemRoleNames())->count(),
        ]);
    }
}
