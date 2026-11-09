<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'job_function',
        'branch_id',
        'reseller_id',
        'username', 'job_title',
        'email',
        'password',
        'uuid',
        'whatsapp',
        'wilayah',
        'customer_code',
        'pppoe_username',
        'onu_sn',
        'is_active',
        'avatar',
        'identity_number',
        'address',
        'balance',
        'is_balance_active',
        'is_topup_enabled',
        'notes',
        'password_changed_at',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($user) {
            if (empty($user->uuid)) {
                $user->uuid = (string) Str::uuid();
            }

            if (empty($user->customer_code)) {
                $user->customer_code = \App\Services\CRM\CustomerCodeGenerator::generate();
            }

            if (!isset($user->is_active)) {
                $user->is_active = true;
            }
        });
    }

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password_changed_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function employee()
    {
        return $this->hasOne(\App\Models\Employee::class);
    }

    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class);
    }

    public function permissions()
    {
        return Permission::whereHas('roles', function ($query) {
            $query->whereIn('roles.id', $this->roles()->select('roles.id'));
        });
    }
    
    public function directPermissions(): BelongsToMany
    {
        return $this->belongsToMany(Permission::class);
    }

    public function isRole($roleName)
    {
        return $this->roles->contains('name', $roleName);
    }

    public function attachRole(string $roleName)
    {
        $role = \App\Models\Role::where('name', $roleName)->first();
        if ($role) {
            $this->roles()->syncWithoutDetaching([$role->id]);
        }
        return $this;
    }

    public function customer()
    {
        return $this->hasOne(\App\Models\CRM\Customer::class, 'user_id', 'id');
    }

    public function isSuperAdmin(): bool
    {
        return $this->hasRole(\App\Enums\UserRole::Administrator->value) || $this->hasRole('superadmin');
    }

    public function isTechnician(): bool
    {
        return $this->hasRole(['technician', 'teknisi']) 
            || strtoupper($this->job_function ?? '') === 'TECHNICIAN'
            || strtoupper($this->job_function ?? '') === 'TEKNISI'
            || str_contains(strtoupper($this->job_title ?? ''), 'TEKNISI')
            || str_contains(strtoupper($this->job_title ?? ''), 'TECHNICIAN');
    }

    public function isReseller(): bool
    {
        return $this->hasRole(['reseller', 'Reseller']) 
            || strtoupper($this->job_function ?? '') === 'RESELLER'
            || !empty($this->reseller_id);
    }

    public function isWarehouseAdmin(): bool
    {
        return $this->hasRole(['admin_gudang', 'gudang'])
            || strtoupper($this->job_function ?? '') === 'WAREHOUSE'
            || strtoupper($this->job_function ?? '') === 'GUDANG'
            || str_contains(strtoupper($this->job_title ?? ''), 'GUDANG');
    }

    public function scopeTechnicians($query)
    {
        return $query->where(function ($q) {
            $q->whereHas('roles', function ($rq) {
                $rq->whereIn('name', ['technician', 'Technician', 'teknisi', 'Teknisi']);
            })
            ->orWhereRaw('UPPER(COALESCE(job_function, "")) IN (?, ?)', ['TECHNICIAN', 'TEKNISI'])
            ->orWhereRaw('UPPER(COALESCE(job_title, "")) LIKE ?', ['%TEKNISI%'])
            ->orWhereRaw('UPPER(COALESCE(job_title, "")) LIKE ?', ['%TECHNICIAN%'])
            ->orWhereRaw('UPPER(COALESCE(name, "")) LIKE ?', ['%TEKNISI%'])
            ->orWhereRaw('UPPER(COALESCE(name, "")) LIKE ?', ['%TECHNICIAN%']);
        });
    }

    public static function getEligibleTechnicians()
    {
        $techs = static::technicians()->orderBy('name')->get();
        if ($techs->isEmpty()) {
            $techs = static::whereDoesntHave('roles', function($q) {
                $q->where('name', 'customer');
            })->where('name', '!=', 'Administrator')->orderBy('name')->get();
        }
        return $techs;
    }

    public function scopeResellers($query)
    {
        return $query->where(function ($q) {
            $q->whereHas('roles', function ($rq) {
                $rq->whereIn('name', ['reseller', 'Reseller']);
            })
            ->orWhereRaw('UPPER(COALESCE(job_function, "")) = ?', ['RESELLER'])
            ->orWhereNotNull('reseller_id');
        });
    }

    public function scopeInventoryRecipients($query)
    {
        return $query->where(function ($q) {
            $q->whereHas('roles', function ($rq) {
                $rq->whereIn('name', ['technician', 'Technician', 'reseller', 'Reseller', 'administrator', 'manager']);
            })
            ->orWhereRaw('UPPER(COALESCE(job_function, "")) IN (?, ?)', ['TECHNICIAN', 'RESELLER'])
            ->orWhereRaw('UPPER(COALESCE(job_title, "")) LIKE ?', ['%TEKNISI%'])
            ->orWhereRaw('UPPER(COALESCE(job_title, "")) LIKE ?', ['%TECHNICIAN%'])
            ->orWhereNotNull('reseller_id');
        });
    }

    public function isStaffRole(): bool
    {
        $staffRoles = [
            'manager',
            'admin_gudang',
            'kasir',
            'customer_service',
            'noc',
            'teknisi',
            'marketing',
        ];
        return $this->roles->whereIn('name', $staffRoles)->isNotEmpty();
    }

    public function hasRole(string|array $role): bool
    {
        if (is_array($role)) {
            if ($this->roles->whereIn('name', $role)->isNotEmpty()) {
                return true;
            }
            // Backward-compatibility: staf backoffice otomatis memenuhi izin level manager
            if (in_array('manager', $role, true) && $this->isStaffRole()) {
                return true;
            }
            return false;
        }

        if ($this->roles->contains('name', $role)) {
            return true;
        }

        if ($role === 'manager' && $this->isStaffRole()) {
            return true;
        }

        return false;
    }

    public function hasAnyRole(...$roles): bool
    {
        $flattened = [];
        foreach ($roles as $r) {
            if (is_array($r)) {
                $flattened = array_merge($flattened, $r);
            } else {
                $flattened[] = $r;
            }
        }
        return $this->hasRole($flattened);
    }

    public function hasPermission(string $permission): bool
    {
        // 1. Bypass untuk administrator (Akses penuh seluruh sistem)
        if ($this->hasRole(\App\Enums\UserRole::Administrator->value)) {
            return true;
        }

        // 2. Proteksi Pembatasan Izin Berdasarkan Jabatan (Job Title) & Role Spesifik
        // Mencegah kebocoran menu superadmin/router/keuangan pada staf gudang, kasir, teknisi, dll.
        $title = strtolower((string)($this->job_title ?? ''));
        if (str_contains($title, 'gudang') || str_contains($title, 'logistik') || $this->hasRole('admin_gudang')) {
            return str_starts_with($permission, 'inventory.') || str_starts_with($permission, 'warehouse.');
        }

        if (str_contains($title, 'kasir') || str_contains($title, 'billing') || $this->hasRole('kasir')) {
            return str_starts_with($permission, 'invoice.') || str_starts_with($permission, 'payment.') || $permission === 'customer.view';
        }

        if (str_contains($title, 'customer service') || $title === 'cs' || $this->hasRole('customer_service')) {
            return str_starts_with($permission, 'customer.') || str_starts_with($permission, 'ticket.');
        }

        if (str_contains($title, 'teknisi') || str_contains($title, 'technician') || $this->hasRole('teknisi')) {
            return str_starts_with($permission, 'technician.') 
                || str_starts_with($permission, 'workforce.') 
                || in_array($permission, ['onu.view', 'onu.diagnose', 'inventory.custody', 'inventory.usage', 'inventory.return', 'inventory.requisition']);
        }

        if (str_contains($title, 'marketing') || str_contains($title, 'sales') || $this->hasRole('marketing')) {
            return str_starts_with($permission, 'crm.') || str_starts_with($permission, 'customer.');
        }

        // 3. Direct permissions spesifik diutamakan jika ada
        if ($this->directPermissions->isNotEmpty()) {
            return $this->directPermissions->contains('name', $permission);
        }

        // 4. Fallback permissions dari roles jika staf umum (General Manager)
        foreach ($this->roles as $role) {
            if ($role->permissions->contains('name', $permission)) {
                return true;
            }
        }

        return false;
    }

    public function getAvatarUrlAttribute()
    {
        if ($this->avatar) {
            return \Illuminate\Support\Facades\Storage::url($this->avatar);
        }
        return null;
    }

    public function branch()
    {
        return $this->belongsTo(\App\Models\Master\Branch::class, 'branch_id');
    }

    public function parentReseller()
    {
        return $this->belongsTo(self::class, 'reseller_id');
    }

    public function subStaff()
    {
        return $this->hasMany(self::class, 'reseller_id');
    }

    /**
     * Resolve effective reseller id based on job_function and role.
     */
    public function getEffectiveResellerId()
    {
        if ($this->hasRole(\App\Enums\UserRole::Reseller->value) || strtoupper($this->job_function ?? '') === 'RESELLER' || !empty($this->reseller_id)) {
            // Jika reseller utama, kembalikan ID-nya sendiri
            if (empty($this->reseller_id)) {
                return $this->id;
            }
            // Jika dia sub-staff (PENGURUS / SALES), kembalikan parent reseller_id
            return $this->reseller_id;
        }
        return null;
    }

    /**
     * Resolve effective branch_id (inherits from parent reseller if sub-staff has none).
     */
    public function getEffectiveBranchId(): ?int
    {
        if (!empty($this->branch_id)) {
            return (int) $this->branch_id;
        }
        if (!empty($this->reseller_id) && $this->parentReseller && !empty($this->parentReseller->branch_id)) {
            return (int) $this->parentReseller->branch_id;
        }
        return null;
    }

    /**
     * Resolve effective wilayah text (inherits from parent reseller if sub-staff has none).
     */
    public function getEffectiveWilayah(): string
    {
        $raw = $this->wilayah;
        if ((empty($raw) || trim((string) $raw) === '') && !empty($this->reseller_id) && $this->parentReseller) {
            $raw = $this->parentReseller->wilayah;
        }
        return trim((string) ($raw ?? ''));
    }

    /**
     * Extract normalized territory keywords from wilayah string (supports comma/slash/semicolon lists).
     *
     * @return array<int, string>
     */
    public function getEffectiveTerritoryKeywords(): array
    {
        $wilayah = $this->getEffectiveWilayah();
        if ($wilayah === '') {
            return [];
        }

        $keywords = [$wilayah];
        $parts = preg_split('/[,;\/|]+/', $wilayah) ?: [];
        foreach ($parts as $part) {
            $cleaned = trim((string) preg_replace('/^(kec\.?|kecamatan|kel\.?|kelurahan|desa|ds\.?|kab\.?|kabupaten|kota|wilayah|area|cabang)\s+/i', '', trim($part)));
            if (mb_strlen($cleaned) >= 2) {
                $keywords[] = $cleaned;
            }
            $rawPart = trim($part);
            if (mb_strlen($rawPart) >= 2) {
                $keywords[] = $rawPart;
            }
        }

        return array_values(array_unique($keywords));
    }

    /**
     * Divisi bisnis yang diasosiasikan dengan posisi/job_title pengguna.
     */
    public function getDivisionAttribute(): string
    {
        return \App\Enums\JobFunction::resolveDivision($this->job_title);
    }
}


