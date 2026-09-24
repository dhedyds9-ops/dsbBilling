<?php

namespace App\Models\Finance;

use App\Models\Master\Member;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class RevenueShareBatch extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'batch_number',
        'accounting_period_id',
        'period',
        'total_revenue',
        'total_expense',
        'total_distributed',
        'status',
        'notes',
        'generated_by',
        'generated_at',
        'approved_by',
        'approved_at',
        'locked_by',
        'locked_at',
        'transactions_changed',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'transactions_changed' => 'boolean',
        'generated_at' => 'datetime',
        'approved_at' => 'datetime',
        'locked_at' => 'datetime',
    ];

    public function accountingPeriod(): BelongsTo
    {
        return $this->belongsTo(AccountingPeriod::class);
    }

    public function generatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'generated_by');
    }

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function lockedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'locked_by');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function items(): HasMany
    {
        return $this->hasMany(RevenueShareItem::class, 'batch_id');
    }
}
