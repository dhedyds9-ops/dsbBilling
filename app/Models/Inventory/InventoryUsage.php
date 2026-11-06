<?php

namespace App\Models\Inventory;

use App\Models\CRM\Customer;
use App\Models\CRM\Installation;
use App\Models\Master\Branch;
use App\Models\Support\Ticket;
use App\Models\User;
use App\Traits\HasBranchScope;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class InventoryUsage extends Model
{
    use HasBranchScope, SoftDeletes;

    protected $fillable = [
        'uuid',
        'usage_number',
        'technician_id',
        'customer_id',
        'installation_id',
        'ticket_id',
        'work_order_type',
        'usage_date',
        'optical_power',
        'notes',
        'branch_id',
    ];

    protected $casts = [
        'usage_date' => 'date',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->uuid)) {
                $model->uuid = (string) Str::uuid();
            }

            if (empty($model->usage_number)) {
                $prefix = 'USG-' . date('Ym') . '-';
                $latest = static::withoutGlobalScopes()
                    ->where('usage_number', 'like', $prefix . '%')
                    ->orderBy('id', 'desc')
                    ->first();

                $nextNum = 1;
                if ($latest && preg_match('/-(\d+)$/', $latest->usage_number, $matches)) {
                    $nextNum = (int) $matches[1] + 1;
                }

                $model->usage_number = $prefix . str_pad($nextNum, 5, '0', STR_PAD_LEFT);
            }

            if (empty($model->usage_date)) {
                $model->usage_date = now()->toDateString();
            }
        });
    }

    public function technician(): BelongsTo
    {
        return $this->belongsTo(User::class, 'technician_id');
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class, 'customer_id');
    }

    public function installation(): BelongsTo
    {
        return $this->belongsTo(Installation::class, 'installation_id');
    }

    public function ticket(): BelongsTo
    {
        return $this->belongsTo(Ticket::class, 'ticket_id');
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class, 'branch_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(InventoryUsageItem::class, 'usage_id');
    }
}
