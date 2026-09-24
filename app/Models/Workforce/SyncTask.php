<?php

namespace App\Models\Workforce;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Src\Domain\Workforce\Enums\SyncTaskStatus;
use Src\Domain\Workforce\Enums\SyncTaskType;

class SyncTask extends Model
{
    use HasFactory;

    protected $fillable = [
        'uuid',
        'user_id',
        'type',
        'status',
        'payload',
        'model_type',
        'model_id',
        'retry_count',
        'last_retry_at',
        'error_message',
        'is_compressed',
    ];

    protected $casts = [
        'type' => SyncTaskType::class,
        'status' => SyncTaskStatus::class,
        'payload' => 'array',
        'retry_count' => 'integer',
        'last_retry_at' => 'datetime',
        'is_compressed' => 'boolean',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
