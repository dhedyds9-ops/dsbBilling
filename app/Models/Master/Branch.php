<?php

namespace App\Models\Master;

use App\Models\CRM\Customer;
use App\Models\ISP\ServiceProfile;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

/**
 * Branch — Business entity/organizational scope untuk dsBilling.
 *
 * PRINSIP:
 *  - Branch adalah BUSINESS SCOPE, bukan application role
 *  - Jangan membuat role: branch, branch_manager, branch_operator
 *  - User (manager/reseller) memiliki branch_id sebagai scope
 *
 * Struktur:
 *   ISP
 *    ├── Branch Sukabumi  (branches.id = 1)
 *    │    ├── Manager (user dengan role=manager, branch_id=1)
 *    │    ├── Reseller A (user dengan role=reseller, branch_id=1)
 *    │    └── Customer (members dengan branch_id=1)
 *    └── Branch Cianjur   (branches.id = 2)
 *         ├── Manager (user dengan role=manager, branch_id=2)
 *         └── Customer (members dengan branch_id=2)
 */
class Branch extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'branches';

    protected $fillable = [
        'uuid',
        'code',
        'name',
        'address',
        'city',
        'province',
        'phone',
        'email',
        'latitude',
        'longitude',
        'is_active',
        'notes',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'latitude'  => 'decimal:7',
        'longitude' => 'decimal:7',
    ];

    protected static function boot(): void
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->uuid)) {
                $model->uuid = (string) Str::uuid();
            }
        });
    }

    // =============================================
    // SCOPES
    // =============================================

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    // =============================================
    // RELATIONSHIPS
    // =============================================

    /**
     * Customer yang berada dalam cabang ini.
     */
    public function customers(): HasMany
    {
        return $this->hasMany(Customer::class, 'branch_id');
    }

    /**
     * Service profiles yang dimiliki cabang ini.
     */
    public function serviceProfiles(): HasMany
    {
        return $this->hasMany(ServiceProfile::class, 'branch_id');
    }

    /**
     * User (manager/reseller) yang memiliki scope ke branch ini.
     */
    public function users(): HasMany
    {
        return $this->hasMany(User::class, 'branch_id');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    // =============================================
    // HELPERS
    // =============================================

    /**
     * Dapatkan dropdown format untuk Branch.
     * Gunakan di Livewire render() untuk dropdown branch.
     */
    public static function forDropdown(bool $activeOnly = true): array
    {
        return static::when($activeOnly, fn($q) => $q->active())
            ->orderBy('name')
            ->pluck('name', 'id')
            ->toArray();
    }
}
