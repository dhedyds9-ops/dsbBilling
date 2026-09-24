<?php

namespace App\Models\Workflow;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WorkflowTransition extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'workflow_id',
        'from_node_id',
        'to_node_id',
        'type',
        'name',
        'description',
        'priority',
        'conditions',
        'approval_config',
        'actions',
        'rollback_to_node_id',
        'is_default',
    ];

    protected $casts = [
        'conditions' => 'array',
        'approval_config' => 'array',
        'actions' => 'array',
        'priority' => 'integer',
        'is_default' => 'boolean',
    ];

    /**
     * Transition types
     */
    const TYPE_APPROVAL = 'approval';
    const TYPE_AUTOMATIC = 'automatic';
    const TYPE_MANUAL = 'manual';
    const TYPE_CONDITIONAL = 'conditional';
    const TYPE_PARALLEL = 'parallel';
    const TYPE_ROLLBACK = 'rollback';
    const TYPE_ESCALATION = 'escalation';

    /**
     * Relasi ke workflow
     */
    public function workflow(): BelongsTo
    {
        return $this->belongsTo(Workflow::class);
    }

    /**
     * Relasi ke source node
     */
    public function fromNode(): BelongsTo
    {
        return $this->belongsTo(WorkflowNode::class, 'from_node_id');
    }

    /**
     * Relasi ke target node
     */
    public function toNode(): BelongsTo
    {
        return $this->belongsTo(WorkflowNode::class, 'to_node_id');
    }

    /**
     * Relasi ke rollback node
     */
    public function rollbackToNode(): BelongsTo
    {
        return $this->belongsTo(WorkflowNode::class, 'rollback_to_node_id');
    }

    /**
     * Scope untuk transitions berdasarkan type
     */
    public function scopeByType($query, string $type)
    {
        return $query->where('type', $type);
    }

    /**
     * Scope untuk default transition
     */
    public function scopeDefault($query)
    {
        return $query->where('is_default', true);
    }

    /**
     * Scope untuk conditional transitions
     */
    public function scopeConditional($query)
    {
        return $query->where('type', self::TYPE_CONDITIONAL);
    }

    /**
     * Scope untuk automatic transitions
     */
    public function scopeAutomatic($query)
    {
        return $query->where('type', self::TYPE_AUTOMATIC);
    }

    /**
     * Scope untuk rollback transitions
     */
    public function scopeRollback($query)
    {
        return $query->where('type', self::TYPE_ROLLBACK);
    }

    /**
     * Cek apakah transition memerlukan approval
     */
    public function requiresApproval(): bool
    {
        return $this->type === self::TYPE_APPROVAL;
    }

    /**
     * Cek apakah transition adalah conditional
     */
    public function isConditional(): bool
    {
        return $this->type === self::TYPE_CONDITIONAL;
    }

    /**
     * Cek apakah transition adalah automatic
     */
    public function isAutomatic(): bool
    {
        return $this->type === self::TYPE_AUTOMATIC;
    }

    /**
     * Cek apakah transition adalah rollback
     */
    public function isRollback(): bool
    {
        return $this->type === self::TYPE_ROLLBACK;
    }

    /**
     * Cek apakah transition bisa di-execute berdasarkan kondisi
     */
    public function canExecute(array $contextData = []): bool
    {
        if (!$this->isConditional()) {
            return true;
        }

        $conditions = $this->conditions ?? [];
        foreach ($conditions as $condition) {
            $field = $condition['field'] ?? null;
            $operator = $condition['operator'] ?? null;
            $value = $condition['value'] ?? null;

            if (!$field || !$operator) {
                continue;
            }

            $actualValue = data_get($contextData, $field);

            switch ($operator) {
                case 'equals':
                case '==':
                    if ($actualValue != $value) return false;
                    break;
                case 'not_equals':
                case '!=':
                    if ($actualValue == $value) return false;
                    break;
                case 'contains':
                    if (!str_contains((string)$actualValue, (string)$value)) return false;
                    break;
                case 'greater_than':
                case '>':
                    if (!($actualValue > $value)) return false;
                    break;
                case 'less_than':
                case '<':
                    if (!($actualValue < $value)) return false;
                    break;
                case 'in':
                    if (!in_array($actualValue, (array)$value)) return false;
                    break;
                case 'not_in':
                    if (in_array($actualValue, (array)$value)) return false;
                    break;
            }
        }

        return true;
    }

    /**
     * Get approval config
     */
    public function getApprovalConfig(): array
    {
        return $this->approval_config ?? [];
    }

    /**
     * Get actions yang perlu di-execute
     */
    public function getActions(): array
    {
        return $this->actions ?? [];
    }
}
