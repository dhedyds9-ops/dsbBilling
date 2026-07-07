<?php

namespace App\Models\Workforce;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Src\Domain\Workforce\Enums\SyncConflictResolutionType;

class SyncConflict extends Model
{
    use HasFactory;

    protected $fillable = [
        'uuid',
        'task_id',
        'user_id',
        'model_type',
        'model_id',
        'server_data',
        'client_data',
        'resolution_type',
        'resolved_data',
        'is_resolved',
        'resolved_by',
        'resolved_at',
    ];

    protected $casts = [
        'server_data' => 'array',
        'client_data' => 'array',
        'resolved_data' => 'array',
        'resolution_type' => SyncConflictResolutionType::class,
        'is_resolved' => 'boolean',
        'resolved_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function resolvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'resolved_by');
    }

    public function task(): BelongsTo
    {
        return $this->belongsTo(SyncTask::class, 'task_id', 'uuid');
    }
}
