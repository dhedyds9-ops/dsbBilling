<?php

namespace App\Models\Workflow;

use App\Models\User;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WorkflowApproval extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'task_id',
        'instance_id',
        'title',
        'description',
        'config',
        'status',
        'requested_by',
        'deadline',
        'approvals',
        'completed_at',
        'final_decision',
        'summary',
    ];

    protected $casts = [
        'config' => 'array',
        'approvals' => 'array',
        'deadline' => 'datetime',
        'completed_at' => 'datetime',
    ];

    /**
     * Approval status constants
     */
    const STATUS_PENDING = 'pending';
    const STATUS_APPROVED = 'approved';
    const STATUS_REJECTED = 'rejected';
    const STATUS_SKIPPED = 'skipped';
    const STATUS_ESCALATED = 'escalated';

    /**
     * Approval levels
     */
    const LEVEL_SINGLE = 1;
    const LEVEL_MULTIPLE = 2;

    /**
     * Relasi ke task
     */
    public function task(): BelongsTo
    {
        return $this->belongsTo(WorkflowTask::class, 'task_id');
    }

    /**
     * Relasi ke workflow instance
     */
    public function instance(): BelongsTo
    {
        return $this->belongsTo(WorkflowInstance::class);
    }

    /**
     * Relasi ke user yang meminta approval
     */
    public function requestedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requested_by');
    }

    /**
     * Scope untuk approval berdasarkan status
     */
    public function scopeByStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope untuk approval yang pending
     */
    public function scopePending($query)
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    /**
     * Scope untuk approval yang overdue
     */
    public function scopeOverdue($query)
    {
        return $query->whereNotNull('deadline')
            ->where('deadline', '<', now())
            ->where('status', self::STATUS_PENDING);
    }

    /**
     * Scope untuk approval berdasarkan requester
     */
    public function scopeRequestedBy($query, string $userId)
    {
        return $query->where('requested_by', $userId);
    }

    /**
     * Cek apakah approval sudah selesai
     */
    public function isCompleted(): bool
    {
        return in_array($this->status, [
            self::STATUS_APPROVED,
            self::STATUS_REJECTED,
            self::STATUS_SKIPPED,
            self::STATUS_ESCALATED
        ]);
    }

    /**
     * Cek apakah approval pending
     */
    public function isPending(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }

    /**
     * Cek apakah approval overdue
     */
    public function isOverdue(): bool
    {
        if (!$this->deadline || $this->isCompleted()) {
            return false;
        }
        return $this->deadline < now();
    }

    /**
     * Get approval level dari config
     */
    public function getApprovalLevel(): int
    {
        return $this->config['approval_level'] ?? self::LEVEL_SINGLE;
    }

    /**
     * Get required approvers dari config
     */
    public function getRequiredApprovers(): array
    {
        return $this->config['required_approvers'] ?? [];
    }

    /**
     * Get approval threshold (jumlah minimum approval)
     */
    public function getThreshold(): int
    {
        return $this->config['threshold'] ?? 1;
    }

    /**
     * Get remaining approvals needed
     */
    public function getRemainingApprovals(): int
    {
        $approvals = $this->approvals ?? [];
        $approved = count(array_filter($approvals, fn($a) => ($a['status'] ?? '') === 'approved'));
        return max(0, $this->getThreshold() - $approved);
    }

    /**
     * Add approval decision
     */
    public function addApproval(string $approverId, string $status, ?string $comment = null): void
    {
        $approvals = $this->approvals ?? [];
        $approvals[] = [
            'approver_id' => $approverId,
            'status' => $status,
            'comment' => $comment,
            'decided_at' => now()->toIso8601String(),
        ];
        $this->approvals = $approvals;
        $this->save();
    }

    /**
     * Check if approver has already voted
     */
    public function hasApproverVoted(string $approverId): bool
    {
        $approvals = $this->approvals ?? [];
        foreach ($approvals as $approval) {
            if (($approval['approver_id'] ?? '') === $approverId) {
                return true;
            }
        }
        return false;
    }

    /**
     * Get approver's decision
     */
    public function getApproverDecision(string $approverId): ?string
    {
        $approvals = $this->approvals ?? [];
        foreach ($approvals as $approval) {
            if (($approval['approver_id'] ?? '') === $approverId) {
                return $approval['status'] ?? null;
            }
        }
        return null;
    }

    /**
     * Get all approvers yang sudah vote
     */
    public function getVotedApprovers(): array
    {
        $approvals = $this->approvals ?? [];
        return array_filter($approvals, fn($a) => isset($a['status']));
    }

    /**
     * Get all approvers yang belum vote
     */
    public function getPendingApprovers(): array
    {
        $required = $this->getRequiredApprovers();
        $voted = $this->getVotedApprovers();
        $votedIds = array_column($voted, 'approver_id');
        return array_diff($required, $votedIds);
    }

    /**
     * Determine final decision based on approvals
     */
    public function determineFinalDecision(): string
    {
        $approvals = $this->approvals ?? [];
        $approved = count(array_filter($approvals, fn($a) => ($a['status'] ?? '') === 'approved'));
        $rejected = count(array_filter($approvals, fn($a) => ($a['status'] ?? '') === 'rejected'));

        if ($approved >= $this->getThreshold()) {
            return self::STATUS_APPROVED;
        }

        if ($rejected >= $this->getThreshold()) {
            return self::STATUS_REJECTED;
        }

        // Check if all approvers have voted
        $totalRequired = count($this->getRequiredApprovers());
        $totalVoted = count($this->getVotedApprovers());

        if ($totalVoted >= $totalRequired) {
            return $approved > $rejected ? self::STATUS_APPROVED : self::STATUS_REJECTED;
        }

        return self::STATUS_PENDING;
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
}
