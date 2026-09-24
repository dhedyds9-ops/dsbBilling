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

class Workflow extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    protected $fillable = [
        'name',
        'description',
        'entity_type',
        'trigger_type',
        'status',
        'initial_context',
        'version',
        'previous_version_id',
        'parent_workflow_id',
        'effective_from',
        'effective_to',
        'module',
        'metadata',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'initial_context' => 'array',
        'metadata' => 'array',
        'effective_from' => 'datetime',
        'effective_to' => 'datetime',
        'version' => 'integer',
    ];

    /**
     * Relasi ke user yang membuat workflow
     */
    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Relasi ke user yang terakhir update workflow
     */
    public function updatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    /**
     * Relasi ke versi workflow sebelumnya
     */
    public function previousVersion(): BelongsTo
    {
        return $this->belongsTo(Workflow::class, 'previous_version_id');
    }

    /**
     * Relasi ke versi workflow berikutnya
     */
    public function nextVersion(): HasOne
    {
        return $this->hasOne(Workflow::class, 'previous_version_id');
    }

    /**
     * Relasi ke parent workflow (jika ini adalah sub-workflow)
     */
    public function parentWorkflow(): BelongsTo
    {
        return $this->belongsTo(Workflow::class, 'parent_workflow_id');
    }

    /**
     * Relasi ke sub-workflows
     */
    public function childWorkflows(): HasMany
    {
        return $this->hasMany(Workflow::class, 'parent_workflow_id');
    }

    /**
     * Relasi ke semua instances dari workflow ini
     */
    public function instances(): HasMany
    {
        return $this->hasMany(WorkflowInstance::class);
    }

    /**
     * Relasi ke nodes (start, end, task, approval, dll)
     */
    public function nodes(): HasMany
    {
        return $this->hasMany(WorkflowNode::class);
    }

    /**
     * Relasi ke transitions
     */
    public function transitions(): HasMany
    {
        return $this->hasMany(WorkflowTransition::class);
    }

    /**
     * Relasi ke actions
     */
    public function actions(): HasMany
    {
        return $this->hasMany(WorkflowAction::class);
    }

    /**
     * Scope untuk workflow yang aktif
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Scope untuk workflow berdasarkan module
     */
    public function scopeByModule($query, string $module)
    {
        return $query->where('module', $module);
    }

    /**
     * Scope untuk workflow berdasarkan entity type
     */
    public function scopeByEntityType($query, string $entityType)
    {
        return $query->where('entity_type', $entityType);
    }

    /**
     * Scope untuk workflow berdasarkan trigger type
     */
    public function scopeByTriggerType($query, string $triggerType)
    {
        return $query->where('trigger_type', $triggerType);
    }

    /**
     * Cek apakah workflow efektif pada tanggal tertentu
     */
    public function isEffectiveAt(\DateTimeInterface $date): bool
    {
        if ($this->effective_from && $date < $this->effective_from) {
            return false;
        }
        if ($this->effective_to && $date > $this->effective_to) {
            return false;
        }
        return true;
    }

    /**
     * Cek apakah workflow sudah versi terbaru
     */
    public function isLatestVersion(): bool
    {
        return !$this->nextVersion()->exists();
    }

    /**
     * Get the latest active version of this workflow
     */
    public function getLatestActiveVersion(): ?Workflow
    {
        return self::where('name', $this->name)
            ->where('status', 'active')
            ->whereNull('effective_to')
            ->orderByDesc('version')
            ->first();
    }
}
