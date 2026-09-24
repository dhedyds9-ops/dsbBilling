<?php

namespace App\Models\Finance;

use App\Models\Master\ExpenseCategory;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class CashTransaction extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'code',
        'date',
        'type',
        'cash_account_id',
        'related_cash_account_id',
        'expense_category_id',
        'amount',
        'description',
        'status',
        'approved_by',
        'approved_at',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'date' => 'date',
        'approved_at' => 'datetime',
        'amount' => 'decimal:2',
    ];

    /**
     * Cek apakah transaksi ini mempengaruhi Revenue Sharing
     */
    public function getAffectsRevenueSharingAttribute(): bool
    {
        // Hanya transaksi expense dengan kategori yang affects_revenue_sharing = true yang mempengaruhi
        if ($this->type !== 'expense' || !$this->expenseCategory) {
            return false;
        }

        return (bool) $this->expenseCategory->affects_revenue_sharing;
    }

    public function cashAccount(): BelongsTo
    {
        return $this->belongsTo(CashAccount::class);
    }

    public function relatedCashAccount(): BelongsTo
    {
        return $this->belongsTo(CashAccount::class, 'related_cash_account_id');
    }

    public function expenseCategory(): BelongsTo
    {
        return $this->belongsTo(ExpenseCategory::class);
    }

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
}
