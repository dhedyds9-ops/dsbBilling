<?php

namespace App\Models\Finance;

use App\Models\Master\Member;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class RevenueShareItem extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'batch_id',
        'member_id',
        'member_revenue',
        'percentage',
        'expense_share',
        'net_share',
        'created_by',
        'updated_by',
    ];

    public function batch(): BelongsTo
    {
        return $this->belongsTo(RevenueShareBatch::class);
    }

    public function member(): BelongsTo
    {
        return $this->belongsTo(Member::class);
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
