<?php

namespace App\Models\Workflow;

use App\Models\User;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class WorkflowInstance extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    protected $fillable = [
        'workflow_id',
        'entity_type',
        'entity_id',
        'current_node_id',
        'status',
        'execution_path',
        'context_data',
        'variables',
        'initiated_by',
        'started_at',
        'completed_at',
        'parent_instance_id',
    ];

    protected $casts = [
        'execution_path' => 'array',
        'context_data' => 'array',
        'variables' => 'array',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    /**
     * Relasi ke workflow definition
     */
    public function workflow(): BelongsTo
    {
        return $this->belongsTo(Workflow::class);
    }

    /**
     * Relasi ke current node
     */
    public function currentNode(): BelongsTo
    {
        return $this->belongsTo(WorkflowNode::class, 'current_node_id');
    }

    /**
     * Relasi ke user yang memulai instance
     */
    public function initiatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'initiated_by');
    }

    /**
     * Relasi ke parent instance (untuk parallel workflow)
     */
    public function parentInstance(): BelongsTo
    {
        return $this->belongsTo(WorkflowInstance::class, 'parent_instance_id');
    }

    /**
     * Relasi ke child instances (untuk parallel workflow)
     */
    public function childInstances(): HasMany
    {
        return $this->hasMany(WorkflowInstance::class, 'parent_instance_id');
    }

    /**
     * Relasi ke tasks
     */
    public function tasks(): HasMany
    {
        return $this->hasMany(WorkflowTask::class, 'instance_id');
    }

    /**
     * Relasi ke approvals
     */
    public function approvals(): HasMany
    {
        return $this->hasMany(WorkflowApproval::class, 'instance_id');
    }

    /**
     * Relasi ke histories (audit trail)
     */
    public function histories(): HasMany
    {
        return $this->hasMany(WorkflowHistory::class, 'instance_id');
    }

    /**
     * Scope untuk instance berdasarkan status
     */
    public function scopeByStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope untuk instance berdasarkan entity
     */
    public function scopeForEntity($query, string $entityType, string $entityId)
    {
        return $query->where('entity_type', $entityType)->where('entity_id', $entityId);
    }

    /**
     * Scope untuk instance yang sedang berjalan
     */
    public function scopeRunning($query)
    {
        return $query->whereIn('status', ['running', 'waiting']);
    }

    /**
     * Scope untuk instance yang overdue (melewati deadline)
     */
    public function scopeOverdue($query)
    {
        return $query->whereHas('tasks', function ($q) {
            $q->whereNotNull('deadline')
              ->where('deadline', '<', now())
              ->whereNotIn('status', ['completed', 'cancelled', 'skipped']);
        });
    }

    /**
     * Cek apakah instance sudah selesai
     */
    public function isCompleted(): bool
    {
        return in_array($this->status, ['completed', 'cancelled', 'failed']);
    }

    /**
     * Cek apakah instance sedang berjalan
     */
    public function isRunning(): bool
    {
        return in_array($this->status, ['running', 'waiting']);
    }

    /**
     * Cek apakah instance overdue
     */
    public function isOverdue(): bool
    {
        return $this->tasks()
            ->whereNotNull('deadline')
            ->where('deadline', '<', now())
            ->whereNotIn('status', ['completed', 'cancelled', 'skipped'])
            ->exists();
    }

    /**
     * Get elapsed time since started
     */
    public function getElapsedTime(): ?int
    {
        if (!$this->started_at) {
            return null;
        }
        return $this->started_at->diffInSeconds($this->completed_at ?? now());
    }

    /**
     * Get active task
     */
    public function getActiveTask(): ?WorkflowTask
    {
        return $this->tasks()
            ->whereIn('status', ['pending', 'assigned', 'in_progress'])
            ->orderBy('priority', 'desc')
            ->first();
    }
}
