<?php

namespace App\Livewire\Admin\Role;

use App\Enums\UserRole;
use App\Livewire\AdminComponent;
use App\Models\Permission;
use App\Models\Role;
use App\Services\Auth\AuditLogService;
use App\Services\Auth\PermissionRegistry;
use Illuminate\Support\Facades\Gate;

class Permissions extends AdminComponent
{
    public int $roleId;
    public Role $role;
    
    /** @var array<int|string> ID permission yang terpilih */
    public array $selectedPermissions = [];

    public string $search = '';
    public string $activeCategory = 'all';

    public function mount($id = null)
    {
        parent::mount();
        $this->activeModule = 'admin';
        $this->activePage = 'roles';

        $this->roleId = (int) $id;

        // Auto-heal: Jika jumlah permission di DB belum lengkap (misal di production), sinkronkan otomatis
        if (Permission::count() < 190) {
            PermissionRegistry::syncAllPermissionsToDatabase();
        }

        $this->role = Role::with('permissions')->findOrFail($this->roleId);

        $this->breadcrumbs = [
            ['label' => 'Dashboard', 'url' => route('dashboard')],
            ['label' => 'Administration', 'url' => route('admin.users.index')],
            ['label' => 'Role & Hak Akses', 'url' => route('admin.roles.index')],
            ['label' => $this->role->display_name, 'url' => route('admin.roles.permissions', $this->roleId)],
            ['label' => 'Matriks Hak Akses'],
        ];

        $rolePermissions = $this->role->permissions;
        if ($this->role->name === 'reseller') {
            $categories = PermissionRegistry::getCategorizedPermissions('reseller');
            $allowedNames = [];
            foreach ($categories as $cat) {
                foreach ($cat['modules'] as $modPrefix => $modConfig) {
                    $actions = $modConfig['actions'] ?? ['view'];
                    foreach ($actions as $act) {
                        $allowedNames[] = "{$modPrefix}.{$act}";
                    }
                }
            }
            $rolePermissions = $rolePermissions->filter(fn($p) => in_array($p->name, $allowedNames, true));
        }

        // Ambil permission saat ini (konversi ke string untuk konsistensi Livewire checkbox)
        $this->selectedPermissions = $rolePermissions
            ->pluck('id')
            ->map(fn($id) => (string)$id)
            ->toArray();
    }

    public function selectAll(): void
    {
        Gate::authorize('role.manage');
        if ($this->role->name === 'reseller') {
            $categories = PermissionRegistry::getCategorizedPermissions('reseller');
            $allowedNames = [];
            foreach ($categories as $cat) {
                foreach ($cat['modules'] as $modPrefix => $modConfig) {
                    $actions = $modConfig['actions'] ?? ['view'];
                    foreach ($actions as $act) {
                        $allowedNames[] = "{$modPrefix}.{$act}";
                    }
                }
            }
            $this->selectedPermissions = Permission::whereIn('name', $allowedNames)
                ->pluck('id')->map(fn($id) => (string)$id)->toArray();
        } else {
            $this->selectedPermissions = Permission::pluck('id')->map(fn($id) => (string)$id)->toArray();
        }
    }

    public function deselectAll(): void
    {
        Gate::authorize('role.manage');
        $this->selectedPermissions = [];
    }

    /**
     * Centang seluruh permission di kategori/seksi tertentu.
     */
    public function selectCategory(array $permissionIds): void
    {
        Gate::authorize('role.manage');
        $stringIds = array_map('strval', $permissionIds);
        $this->selectedPermissions = array_values(array_unique(array_merge($this->selectedPermissions, $stringIds)));
    }

    /**
     * Hapus seluruh centang di kategori/seksi tertentu.
     */
    public function deselectCategory(array $permissionIds): void
    {
        Gate::authorize('role.manage');
        $stringIds = array_map('strval', $permissionIds);
        $this->selectedPermissions = array_values(array_diff($this->selectedPermissions, $stringIds));
    }

    /**
     * Toggle satu kategori (jika semua terpilih -> kosongkan, jika belum -> pilih semua).
     */
    public function toggleCategory(array $permissionIds): void
    {
        Gate::authorize('role.manage');
        $stringIds = array_map('strval', $permissionIds);
        $diff = array_diff($stringIds, $this->selectedPermissions);

        if (empty($diff)) {
            // Semua sudah terpilih -> hapus
            $this->deselectCategory($permissionIds);
        } else {
            // Masih ada yang belum terpilih -> pilih semua
            $this->selectCategory($permissionIds);
        }
    }

    public function save(): void
    {
        Gate::authorize('role.manage');

        $oldPermissionIds = $this->role->permissions->pluck('id')->toArray();
        $newPermissionIds = array_map('intval', $this->selectedPermissions);

        // Sync ke database
        $this->role->permissions()->sync($newPermissionIds);

        // Catat Audit Trail
        $auditService = app(AuditLogService::class);
        $auditService->auditRolePermissionSync($this->role, $oldPermissionIds, $newPermissionIds);

        // Invalidate navigation cache global
        foreach (UserRole::allValues() as $roleVal) {
            \Illuminate\Support\Facades\Cache::forget("dsbilling_navigation.{$roleVal}.v3");
        }

        // Refresh model relations
        $this->role->load('permissions');

        session()->flash('success', "Hak akses untuk role '{$this->role->display_name}' berhasil disimpan (" . count($newPermissionIds) . " izin aktif).");
    }

    public function render()
    {
        $allPermissions = Permission::all();
        $categories = PermissionRegistry::getCategorizedPermissions($this->role->name);

        // Siapkan struktur data hierarkis: Kategori -> Modul -> List Permission
        $structured = [];
        $searchLower = strtolower(trim($this->search));

        foreach ($categories as $catKey => $catData) {
            $catPermissions = [];
            $modulesData = [];

            foreach ($catData['modules'] as $modulePrefix => $moduleConfig) {
                $moduleTitle = is_array($moduleConfig) ? ($moduleConfig['title'] ?? $modulePrefix) : $moduleConfig;
                $allowedActions = is_array($moduleConfig) ? ($moduleConfig['actions'] ?? null) : null;

                // Filter permissions yang cocok dengan prefix modul ini
                $perms = $allPermissions->filter(function ($p) use ($modulePrefix, $allowedActions) {
                    $matches = str_starts_with($p->name, $modulePrefix . '.') || $p->name === $modulePrefix;
                    if (!$matches) return false;
                    if ($allowedActions !== null) {
                        $parts = explode('.', $p->name);
                        $act = $parts[1] ?? 'view';
                        return in_array($act, $allowedActions, true);
                    }
                    return true;
                });

                if (!empty($searchLower)) {
                    $perms = $perms->filter(function ($p) use ($searchLower, $moduleTitle) {
                        return str_contains(strtolower($p->name), $searchLower)
                            || str_contains(strtolower($p->display_name ?? ''), $searchLower)
                            || str_contains(strtolower($moduleTitle), $searchLower);
                    });
                }

                if ($perms->isNotEmpty()) {
                    $modulesData[$modulePrefix] = [
                        'title' => $moduleTitle,
                        'permissions' => $perms,
                    ];
                    foreach ($perms as $p) {
                        $catPermissions[] = $p->id;
                    }
                }
            }

            if (!empty($modulesData)) {
                $structured[$catKey] = [
                    'title' => $catData['title'],
                    'icon' => $catData['icon'],
                    'description' => $catData['description'],
                    'permission_ids' => $catPermissions,
                    'modules' => $modulesData,
                ];
            }
        }

        $totalApplicablePermissions = array_sum(array_map(fn($c) => count($c['permission_ids']), $structured));

        return view('livewire.admin.role.permissions', [
            'structuredCategories' => $structured,
            'totalSystemPermissions' => ($this->role->name === 'reseller' ? $totalApplicablePermissions : $allPermissions->count()),
            'totalSelected' => count($this->selectedPermissions),
        ]);
    }
}
