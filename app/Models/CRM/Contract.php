<?php

namespace App\Models\CRM;

use App\Models\Billing\Subscription;
use App\Models\Customer\CustomerService;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Contract extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'contracts';

    protected $fillable = [
        'uuid',
        'customer_id',
        'customer_name',
        'contract_number',
        'start_date',
        'end_date',
        'status',
        'terms',
        'notes',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'start_date' => 'datetime',
        'end_date'   => 'datetime',
        'terms'      => 'array',
    ];

    protected static function booted()
    {
        static::creating(function ($contract) {
            if (empty($contract->uuid)) {
                $contract->uuid = (string) Str::uuid();
            }
            if (empty($contract->contract_number)) {
                $contract->contract_number = 'CONTRACT-' . strtoupper(Str::random(10));
            }
        });
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class, 'customer_id');
    }

    public function installations(): HasMany
    {
        return $this->hasMany(Installation::class, 'contract_id');
    }

    public function subscriptions(): HasMany
    {
        return $this->hasMany(Subscription::class, 'contract_id');
    }

    public function customerServices(): HasMany
    {
        return $this->hasMany(CustomerService::class, 'contract_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
}
