<?php

namespace App\Models\Keuangan;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class ResellerTopup extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'reseller_topups';

    protected $fillable = [
        'uuid',
        'user_id',
        'reference_code',
        'amount',
        'method',
        'status',
        'proof_file',
        'reject_reason',
        'submitted_by',
        'verified_by',
        'verified_at',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'verified_at' => 'datetime',
    ];

    protected static function boot(): void
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->uuid)) {
                $model->uuid = (string) Str::uuid();
            }
            if (empty($model->reference_code)) {
                $model->reference_code = 'RT-' . now()->format('YmdHis') . '-' . strtoupper(Str::random(6));
            }
        });
    }

    public function reseller()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function submittedBy()
    {
        return $this->belongsTo(User::class, 'submitted_by');
    }

    public function verifiedBy()
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
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
        return $query->whereBetween('created_at', [now()->startOfMonth(), now()->endOfMonth()]);
    }

    public function scopeBetweenDates($query, $startDate, $endDate)
    {
        return $query->whereBetween('created_at', [
            $startDate ? now()->parse($startDate)->startOfDay() : now()->subYears(10),
            $endDate ? now()->parse($endDate)->endOfDay() : now(),
        ]);
    }
}
