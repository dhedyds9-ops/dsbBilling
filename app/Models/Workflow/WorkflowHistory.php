<?php

namespace App\Models\Workflow;

use App\Models\User;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Builder;

class WorkflowHistory extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'instance_id',
        'node_id',
        'action',
        'performed_by',
        'performed_by_type',
        'previous_state',
        'new_state',
        'comment',
        'metadata',
        'performed_at',
    ];

    protected $casts = [
        'previous_state' => 'array',
        'new_state' => 'array',
        'metadata' => 'array',
        'performed_at' => 'datetime',
    ];

    /**
     * Action type constants
     */
    const ACTION_STARTED = 'started';
    const ACTION_NODE_ENTERED = 'node_entered';
    const ACTION_NODE_EXITED = 'node_exited';
    const ACTION_TASK_ASSIGNED = 'task_assigned';
    const ACTION_TASK_STARTED = 'task_started';
    const ACTION_TASK_COMPLETED = 'task_completed';
    const ACTION_TASK_REJECTED = 'task_rejected';
    const ACTION_APPROVAL_REQUESTED = 'approval_requested';
    const ACTION_APPROVAL_APPROVED = 'approval_approved';
    const ACTION_APPROVAL_REJECTED = 'approval_rejected';
    const ACTION_ESCALATED = 'escalated';
    const ACTION_REMINDER_SENT = 'reminder_sent';
    const ACTION_DEADLINE_PASSED = 'deadline_passed';
    const ACTION_TRANSITIONED = 'transitioned';
    const ACTION_ROLLBACK = 'rollback';
    const ACTION_COMPLETED = 'completed';
    const ACTION_CANCELLED = 'cancelled';
    const ACTION_FAILED = 'failed';
    const ACTION_RETRY = 'retry';
    const ACTION_COMMENT = 'comment';

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
     * Relasi ke user yang melakukan action
     */
    public function performedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'performed_by');
    }

    /**
     * Scope untuk history berdasarkan instance
     */
    public function scopeForInstance($query, string $instanceId)
    {
        return $query->where('instance_id', $instanceId);
    }

    /**
     * Scope untuk history berdasarkan action
     */
    public function scopeByAction($query, string $action)
    {
        return $query->where('action', $action);
    }

    /**
     * Scope untuk history berdasarkan user
     */
    public function scopeByUser($query, string $userId)
    {
        return $query->where('performed_by', $userId);
    }

    /**
     * Scope untuk history pada node tertentu
     */
    public function scopeAtNode($query, string $nodeId)
    {
        return $query->where('node_id', $nodeId);
    }

    /**
     * Scope untuk history dalam rentang waktu
     */
    public function scopeBetween($query, $startDate, $endDate)
    {
        return $query->whereBetween('performed_at', [$startDate, $endDate]);
    }

    /**
     * Scope untuk recent history
     */
    public function scopeRecent($query, int $days = 7)
    {
        return $query->where('performed_at', '>=', now()->subDays($days));
    }

    /**
     * Scope untuk history terurut terbaru
     */
    public function scopeLatest($query)
    {
        return $query->orderByDesc('performed_at');
    }

    /**
     * Scope untuk start events
     */
    public function scopeStarts($query)
    {
        return $query->where('action', self::ACTION_STARTED);
    }

    /**
     * Scope untuk completion events
     */
    public function scopeCompletions($query)
    {
        return $query->whereIn('action', [self::ACTION_COMPLETED, self::ACTION_CANCELLED, self::ACTION_FAILED]);
    }

    /**
     * Scope untuk task events
     */
    public function scopeTaskEvents($query)
    {
        return $query->whereIn('action', [
            self::ACTION_TASK_ASSIGNED,
            self::ACTION_TASK_STARTED,
            self::ACTION_TASK_COMPLETED,
            self::ACTION_TASK_REJECTED,
        ]);
    }

    /**
     * Scope untuk approval events
     */
    public function scopeApprovalEvents($query)
    {
        return $query->whereIn('action', [
            self::ACTION_APPROVAL_REQUESTED,
            self::ACTION_APPROVAL_APPROVED,
            self::ACTION_APPROVAL_REJECTED,
        ]);
    }

    /**
     * Get state changes
     */
    public function getStateChanges(): array
    {
        return [
            'previous' => $this->previous_state,
            'new' => $this->new_state,
        ];
    }

    /**
     * Check if this is a status change
     */
    public function isStatusChange(): bool
    {
        return isset($this->previous_state['status']) && isset($this->new_state['status']);
    }

    /**
     * Get previous status
     */
    public function getPreviousStatus(): ?string
    {
        return $this->previous_state['status'] ?? null;
    }

    /**
     * Get new status
     */
    public function getNewStatus(): ?string
    {
        return $this->new_state['status'] ?? null;
    }

    /**
     * Get metadata value
     */
    public function getMetadataValue(string $key, $default = null)
    {
        return data_get($this->metadata, $key, $default);
    }

    /**
     * Create a history record for workflow start
     */
    public static function recordStart(WorkflowInstance $instance, User $user): self
    {
        return self::create([
            'instance_id' => $instance->id,
            'node_id' => $instance->current_node_id,
            'action' => self::ACTION_STARTED,
            'performed_by' => $user->id,
            'performed_by_type' => get_class($user),
            'new_state' => ['status' => $instance->status],
            'performed_at' => now(),
        ]);
    }

    /**
     * Create a history record for workflow completion
     */
    public static function recordCompletion(WorkflowInstance $instance, User $user, string $comment = null): self
    {
        return self::create([
            'instance_id' => $instance->id,
            'node_id' => $instance->current_node_id,
            'action' => self::ACTION_COMPLETED,
            'performed_by' => $user->id,
            'performed_by_type' => get_class($user),
            'previous_state' => ['status' => $instance->status],
            'new_state' => ['status' => 'completed'],
            'comment' => $comment,
            'performed_at' => now(),
        ]);
    }

    /**
     * Create a history record for node transition
     */
    public static function recordTransition(
        WorkflowInstance $instance,
        WorkflowNode $fromNode,
        WorkflowNode $toNode,
        User $user,
        ?string $action = null
    ): self {
        return self::create([
            'instance_id' => $instance->id,
            'node_id' => $toNode->id,
            'action' => $action ?? self::ACTION_TRANSITIONED,
            'performed_by' => $user->id,
            'performed_by_type' => get_class($user),
            'previous_state' => ['node_id' => $fromNode->id, 'node_name' => $fromNode->name],
            'new_state' => ['node_id' => $toNode->id, 'node_name' => $toNode->name],
            'performed_at' => now(),
        ]);
    }

    /**
     * Create a history record for task assignment
     */
    public static function recordTaskAssigned(WorkflowTask $task, User $assignedBy, User $assignee): self
    {
        return self::create([
            'instance_id' => $task->instance_id,
            'node_id' => $task->node_id,
            'action' => self::ACTION_TASK_ASSIGNED,
            'performed_by' => $assignedBy->id,
            'performed_by_type' => get_class($assignedBy),
            'new_state' => [
                'task_id' => $task->id,
                'task_name' => $task->name,
                'assigned_to' => $assignee->id,
            ],
            'performed_at' => now(),
        ]);
    }
}
