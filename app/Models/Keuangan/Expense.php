<?php

namespace App\Models\Keuangan;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Expense extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'expenses';

    protected $fillable = [
        'uuid',
        'code',
        'description',
        'category',
        'amount',
        'status',
        'attachment_file',
        'reject_reason',
        'requested_by',
        'approved_by',
        'approved_at',
        'expense_date',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'approved_at' => 'datetime',
        'expense_date' => 'date',
    ];

    protected static function boot(): void
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->uuid)) {
                $model->uuid = (string) Str::uuid();
            }
            if (empty($model->code)) {
                $model->code = 'EXP-' . now()->format('YmdHis') . '-' . strtoupper(Str::random(4));
            }
            if (empty($model->expense_date)) {
                $model->expense_date = now()->toDateString();
            }
        });
    }

    public function requestedBy()
    {
        return $this->belongsTo(User::class, 'requested_by');
    }

    public function approvedBy()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function scopeDraft($query)
    {
        return $query->where('status', 'draft');
    }

    public function scopeNeedsApproval($query)
    {
        return $query->where('status', 'pending_approval');
    }

    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    public function scopeRejected($query)
    {
        return $query->where('status', 'rejected');
    }

    public function scopeThisMonth($query)
    {
        return $query->whereBetween('expense_date', [now()->startOfMonth(), now()->endOfMonth()]);
    }

    public function scopeBetweenDates($query, $startDate, $endDate)
    {
        return $query->whereBetween('expense_date', [
            $startDate ? now()->parse($startDate)->toDateString() : now()->subYears(10)->toDateString(),
            $endDate ? now()->parse($endDate)->toDateString() : now()->toDateString(),
        ]);
    }
}
