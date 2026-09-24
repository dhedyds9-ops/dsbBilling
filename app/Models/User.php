<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

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
        'username',
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

    public function hasRole(string|array $role): bool
    {
        if (is_array($role)) {
            return $this->roles->whereIn('name', $role)->isNotEmpty();
        }
        return $this->roles->contains('name', $role);
    }

    public function hasPermission(string $permission): bool
    {
        // Bypass untuk administrator (Akses penuh)
        if ($this->hasRole(\App\Enums\UserRole::Administrator->value)) {
            return true;
        }

        // Cek direct permissions
        if ($this->directPermissions->contains('name', $permission)) {
            return true;
        }

        // Cek permissions dari roles
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
        if ($this->hasRole(\App\Enums\UserRole::Reseller->value)) {
            // Jika reseller utama, kembalikan ID-nya sendiri
            if (empty($this->reseller_id)) {
                return $this->id;
            }
            // Jika dia sub-staff (PENGURUS / SALES), kembalikan parent reseller_id
            return $this->reseller_id;
        }
        return null;
    }
}
