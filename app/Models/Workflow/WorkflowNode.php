<?php

namespace App\Models\Workflow;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class WorkflowNode extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'workflow_id',
        'name',
        'type',
        'config',
        'position_x',
        'position_y',
    ];

    protected $casts = [
        'config' => 'array',
        'position_x' => 'integer',
        'position_y' => 'integer',
    ];

    /**
     * Node types constants
     */
    const TYPE_START = 'start';
    const TYPE_END = 'end';
    const TYPE_TASK = 'task';
    const TYPE_APPROVAL = 'approval';
    const TYPE_ACTION = 'action';
    const TYPE_CONDITION = 'condition';
    const TYPE_PARALLEL = 'parallel';
    const TYPE_MERGE = 'merge';
    const TYPE_SUBWORKFLOW = 'subworkflow';

    /**
     * Relasi ke workflow
     */
    public function workflow(): BelongsTo
    {
        return $this->belongsTo(Workflow::class);
    }

    /**
     * Relasi ke incoming transitions
     */
    public function incomingTransitions(): HasMany
    {
        return $this->hasMany(WorkflowTransition::class, 'to_node_id');
    }

    /**
     * Relasi ke outgoing transitions
     */
    public function outgoingTransitions(): HasMany
    {
        return $this->hasMany(WorkflowTransition::class, 'from_node_id');
    }

    /**
     * Scope untuk node berdasarkan type
     */
    public function scopeByType($query, string $type)
    {
        return $query->where('type', $type);
    }

    /**
     * Scope untuk start nodes
     */
    public function scopeStartNodes($query)
    {
        return $query->where('type', self::TYPE_START);
    }

    /**
     * Scope untuk end nodes
     */
    public function scopeEndNodes($query)
    {
        return $query->where('type', self::TYPE_END);
    }

    /**
     * Scope untuk task nodes
     */
    public function scopeTaskNodes($query)
    {
        return $query->where('type', self::TYPE_TASK);
    }

    /**
     * Scope untuk approval nodes
     */
    public function scopeApprovalNodes($query)
    {
        return $query->where('type', self::TYPE_APPROVAL);
    }

    /**
     * Cek apakah node adalah start node
     */
    public function isStartNode(): bool
    {
        return $this->type === self::TYPE_START;
    }

    /**
     * Cek apakah node adalah end node
     */
    public function isEndNode(): bool
    {
        return $this->type === self::TYPE_END;
    }

    /**
     * Cek apakah node adalah approval node
     */
    public function isApprovalNode(): bool
    {
        return $this->type === self::TYPE_APPROVAL;
    }

    /**
     * Cek apakah node adalah condition node
     */
    public function isConditionNode(): bool
    {
        return $this->type === self::TYPE_CONDITION;
    }

    /**
     * Get next nodes berdasarkan transitions
     */
    public function getNextNodes(): \Illuminate\Database\Eloquent\Collection
    {
        return WorkflowNode::whereIn('id', $this->outgoingTransitions()->pluck('to_node_id'))->get();
    }

    /**
     * Get previous nodes berdasarkan transitions
     */
    public function getPreviousNodes(): \Illuminate\Database\Eloquent\Collection
    {
        return WorkflowNode::whereIn('id', $this->incomingTransitions()->pluck('from_node_id'))->get();
    }

    /**
     * Get config value dengan dot notation
     */
    public function getConfigValue(string $key, $default = null)
    {
        return data_get($this->config, $key, $default);
    }

    /**
     * Set config value dengan dot notation
     */
    public function setConfigValue(string $key, $value): void
    {
        $config = $this->config ?? [];
        data_set($config, $key, $value);
        $this->config = $config;
    }
}
