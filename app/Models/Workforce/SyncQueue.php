<?php

namespace App\Models\Workforce;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SyncQueue extends Model
{
    use HasFactory;

    protected $fillable = [
        'uuid',
        'user_id',
        'task_ids',
        'last_synced_at',
        'pending_count',
        'is_online',
    ];

    protected $casts = [
        'task_ids' => 'array',
        'pending_count' => 'integer',
        'last_synced_at' => 'datetime',
        'is_online' => 'boolean',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function tasks(): HasMany
    {
        return $this->hasMany(SyncTask::class, 'user_id', 'user_id');
    }
}
