<?php

namespace App\Models\Workforce;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Src\Domain\Workforce\Enums\QCApprovalStatus;

class QCApproval extends Model
{
    use HasFactory;

    protected $fillable = [
        'uuid',
        'inspection_id',
        'approver_id',
        'status',
        'notes',
        'approved_at',
    ];

    protected $casts = [
        'status' => QCApprovalStatus::class,
        'approved_at' => 'datetime',
    ];

    public function inspection(): BelongsTo
    {
        return $this->belongsTo(QCInspection::class, 'inspection_id', 'uuid');
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approver_id');
    }
}
