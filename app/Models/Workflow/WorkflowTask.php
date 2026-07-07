<?php

namespace App\Models\Workflow;

use App\Models\User;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class WorkflowTask extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'instance_id',
        'node_id',
        'name',
        'description',
        'status',
        'assigned_to',
        'assigned_to_type',
        'assigned_at',
        'started_at',
        'completed_at',
        'deadline',
        'priority',
        'outputs',
        'retry_count',
        'parent_task_id',
        'notes',
        'metadata',
    ];

    protected $casts = [
        'assigned_at' => 'datetime',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
        'deadline' => 'datetime',
        'outputs' => 'array',
        'metadata' => 'array',
        'priority' => 'integer',
        'retry_count' => 'integer',
    ];

    /**
     * Task status constants
     */
    const STATUS_PENDING = 'pending';
    const STATUS_ASSIGNED = 'assigned';
    const STATUS_IN_PROGRESS = 'in_progress';
    const STATUS_COMPLETED = 'completed';
    const STATUS_SKIPPED = 'skipped';
    const STATUS_CANCELLED = 'cancelled';
    const STATUS_REJECTED = 'rejected';

    /**
     * Priority constants
     */
    const PRIORITY_LOW = 1;
    const PRIORITY_NORMAL = 5;
    const PRIORITY_HIGH = 7;
    const PRIORITY_URGENT = 10;

    /**
     * Relasi ke workflow instance
     */
    public function instance(): BelongsTo
    {
        return $this->belongsTo(WorkflowInstance::class, 'instance_id');
    }

    /**
     * Relasi ke node
     */
    public function node(): BelongsTo
    {
        return $this->belongsTo(WorkflowNode::class, 'node_id');
    }

    /**
     * Relasi ke assignee (polymorphic)
     */
    public function assignedTo(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    /**
     * Relasi ke parent task (untuk subtasks)
     */
    public function parentTask(): BelongsTo
    {
        return $this->belongsTo(WorkflowTask::class, 'parent_task_id');
    }

    /**
     * Relasi ke subtasks
     */
    public function subtasks(): HasMany
    {
        return $this->hasMany(WorkflowTask::class, 'parent_task_id');
    }

    /**
     * Scope untuk task berdasarkan status
     */
    public function scopeByStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope untuk task yang pending
     */
    public function scopePending($query)
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    /**
     * Scope untuk task yang sedang dikerjakan
     */
    public function scopeInProgress($query)
    {
        return $query->where('status', self::STATUS_IN_PROGRESS);
    }

    /**
     * Scope untuk task yang sudah completed
     */
    public function scopeCompleted($query)
    {
        return $query->where('status', self::STATUS_COMPLETED);
    }

    /**
     * Scope untuk task yang overdue
     */
    public function scopeOverdue($query)
    {
        return $query->whereNotNull('deadline')
            ->where('deadline', '<', now())
            ->whereNotIn('status', [self::STATUS_COMPLETED, self::STATUS_CANCELLED, self::STATUS_SKIPPED]);
    }

    /**
     * Scope untuk task berdasarkan assignee
     */
    public function scopeAssignedTo($query, string $userId)
    {
        return $query->where('assigned_to', $userId);
    }

    /**
     * Scope untuk task berdasarkan priority
     */
    public function scopeByPriority($query, int $priority)
    {
        return $query->where('priority', $priority);
    }

    /**
     * Scope untuk task terurut berdasarkan priority
     */
    public function scopeOrderByPriority($query)
    {
        return $query->orderByDesc('priority');
    }

    /**
     * Cek apakah task sudah selesai
     */
    public function isCompleted(): bool
    {
        return $this->status === self::STATUS_COMPLETED;
    }

    /**
     * Cek apakah task overdue
     */
    public function isOverdue(): bool
    {
        if (!$this->deadline) {
            return false;
        }
        return $this->deadline < now() && !$this->isCompleted();
    }

    /**
     * Cek apakah task bisa di-assign
     */
    public function canBeAssigned(): bool
    {
        return in_array($this->status, [self::STATUS_PENDING, self::STATUS_ASSIGNED]);
    }

    /**
     * Cek apakah task bisa di-cancel
     */
    public function canBeCancelled(): bool
    {
        return !in_array($this->status, [self::STATUS_COMPLETED, self::STATUS_CANCELLED, self::STATUS_SKIPPED]);
    }

    /**
     * Get remaining time sampai deadline
     */
    public function getRemainingTime(): ?\DateInterval
    {
        if (!$this->deadline) {
            return null;
        }
        return now()->diff($this->deadline);
    }

    /**
     * Get SLA status (green, yellow, red)
     */
    public function getSlaStatus(): string
    {
        if (!$this->deadline) {
            return 'green';
        }

        $remaining = $this->getRemainingTime();

        if ($remaining->invert) {
            return 'red'; // overdue
        }

        // Less than 25% time remaining
        $totalSeconds = $this->started_at ? $this->started_at->diffInSeconds($this->deadline) : null;
        if ($totalSeconds && $remaining->s < ($totalSeconds * 0.25)) {
            return 'red';
        }

        // Less than 50% time remaining
        if ($totalSeconds && $remaining->s < ($totalSeconds * 0.5)) {
            return 'yellow';
        }

        return 'green';
    }

    /**
     * Increment retry count
     */
    public function incrementRetry(): void
    {
        $this->retry_count++;
        $this->save();
    }

    /**
     * Get output value dengan dot notation
     */
    public function getOutputValue(string $key, $default = null)
    {
        return data_get($this->outputs, $key, $default);
    }

    /**
     * Set output value dengan dot notation
     */
    public function setOutputValue(string $key, $value): void
    {
        $outputs = $this->outputs ?? [];
        data_set($outputs, $key, $value);
        $this->outputs = $outputs;
    }
}
