<?php

namespace App\Models\Workflow;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WorkflowAction extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'workflow_id',
        'node_id',
        'type',
        'name',
        'config',
        'order',
        'is_async',
        'retry_attempts',
        'retry_delay_seconds',
        'failure_action',
        'is_active',
    ];

    protected $casts = [
        'config' => 'array',
        'order' => 'integer',
        'is_async' => 'boolean',
        'retry_attempts' => 'integer',
        'retry_delay_seconds' => 'integer',
        'is_active' => 'boolean',
    ];

    /**
     * Action type constants
     */
    const TYPE_NOTIFY = 'notify';
    const TYPE_EMAIL = 'email';
    const TYPE_SMS = 'sms';
    const TYPE_WEBHOOK = 'webhook';
    const TYPE_API_CALL = 'api_call';
    const TYPE_UPDATE_FIELD = 'update_field';
    const TYPE_CREATE_RECORD = 'create_record';
    const TYPE_ASSIGN_TASK = 'assign_task';
    const TYPE_ESCALATE = 'escalate';
    const TYPE_APPROVE = 'approve';
    const TYPE_REJECT = 'reject';
    const TYPE_CUSTOM = 'custom';
    const TYPE_DELAY = 'delay';
    const TYPE_CONDITION = 'condition';
    const TYPE_PARALLEL = 'parallel';
    const TYPE_SUBWORKFLOW = 'subworkflow';

    /**
     * Failure action constants
     */
    const FAILURE_STOP = 'stop';
    const FAILURE_SKIP = 'skip';
    const FAILURE_RETRY = 'retry';
    const FAILURE_ROLLBACK = 'rollback';

    /**
     * Relasi ke workflow
     */
    public function workflow(): BelongsTo
    {
        return $this->belongsTo(Workflow::class);
    }

    /**
     * Relasi ke node
     */
    public function node(): BelongsTo
    {
        return $this->belongsTo(WorkflowNode::class);
    }

    /**
     * Scope untuk action berdasarkan type
     */
    public function scopeByType($query, string $type)
    {
        return $query->where('type', $type);
    }

    /**
     * Scope untuk active actions
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope untuk async actions
     */
    public function scopeAsync($query)
    {
        return $query->where('is_async', true);
    }

    /**
     * Scope untuk actions terurut
     */
    public function scopeOrderByOrder($query)
    {
        return $query->orderBy('order');
    }

    /**
     * Scope untuk notification actions
     */
    public function scopeNotifications($query)
    {
        return $query->whereIn('type', [self::TYPE_NOTIFY, self::TYPE_EMAIL, self::TYPE_SMS]);
    }

    /**
     * Scope untuk integration actions
     */
    public function scopeIntegrations($query)
    {
        return $query->whereIn('type', [self::TYPE_WEBHOOK, self::TYPE_API_CALL]);
    }

    /**
     * Cek apakah action adalah notification type
     */
    public function isNotification(): bool
    {
        return in_array($this->type, [self::TYPE_NOTIFY, self::TYPE_EMAIL, self::TYPE_SMS]);
    }

    /**
     * Cek apakah action adalah integration type
     */
    public function isIntegration(): bool
    {
        return in_array($this->type, [self::TYPE_WEBHOOK, self::TYPE_API_CALL]);
    }

    /**
     * Cek apakah action perlu di-retry
     */
    public function canRetry(): bool
    {
        return $this->retry_attempts !== null && $this->retry_attempts > 0;
    }

    /**
     * Get max retry attempts
     */
    public function getMaxRetries(): int
    {
        return $this->retry_attempts ?? 0;
    }

    /**
     * Get retry delay dalam detik
     */
    public function getRetryDelay(): int
    {
        return $this->retry_delay_seconds ?? 60;
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

    /**
     * Get action data untuk execution
     */
    public function getExecutionData(array $contextData = []): array
    {
        return [
            'id' => $this->id,
            'type' => $this->type,
            'name' => $this->name,
            'config' => $this->config,
            'is_async' => $this->is_async,
            'context_data' => $contextData,
        ];
    }

    /**
     * Determine failure action based on config
     */
    public function getOnFailureAction(): string
    {
        return $this->failure_action ?? self::FAILURE_STOP;
    }

    /**
     * Execute the action (placeholder untuk service integration)
     */
    public function execute(array $contextData = []): array
    {
        // This should be called by the WorkflowService or ActionHandler
        // Placeholder return untuk testing
        return [
            'success' => true,
            'action_id' => $this->id,
            'type' => $this->type,
            'executed_at' => now()->toIso8601String(),
            'result' => null,
        ];
    }
}
