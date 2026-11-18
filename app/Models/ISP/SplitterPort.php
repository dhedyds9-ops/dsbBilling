<?php

namespace App\Models\ISP;

use App\Models\CRM\Customer;
use App\Models\Master\Branch;
use App\Models\User;
use App\Traits\HasBranchScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class SplitterPort extends Model
{
    use HasBranchScope;
    use HasFactory, SoftDeletes;

    public const TYPE_INPUT = 'input';
    public const TYPE_OUTPUT = 'output';

    public const STATUS_AVAILABLE = 'available';
    public const STATUS_USED = 'used';
    public const STATUS_RESERVED = 'reserved';
    public const STATUS_FAULT = 'fault';

    // Standard 12 TIA-598 Fiber Core Colors
    public const TIA_598_COLORS = [
        1 => 'Blue',
        2 => 'Orange',
        3 => 'Green',
        4 => 'Brown',
        5 => 'Slate',
        6 => 'White',
        7 => 'Red',
        8 => 'Black',
        9 => 'Yellow',
        10 => 'Violet',
        11 => 'Rose',
        12 => 'Aqua',
    ];

    protected $fillable = [
        'splitter_id',
        'port_number',
        'port_type',
        'status',
        'attenuation_db',
        'target_type',
        'target_id',
        'target_port_number',
        'target_label',
        'customer_id',
        'source_type',
        'source_id',
        'source_port_number',
        'core_color',
        'notes',
        'branch_id',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'port_number' => 'integer',
        'target_port_number' => 'integer',
        'source_port_number' => 'integer',
        'attenuation_db' => 'float',
    ];

    protected static function booted()
    {
        static::saving(function ($model) {
            if (empty($model->branch_id) && !empty($model->splitter_id)) {
                $splitter = Splitter::withoutGlobalScopes()->find($model->splitter_id);
                if ($splitter) {
                    $model->branch_id = $splitter->branch_id;
                }
            }
        });
    }

    public function splitter(): BelongsTo
    {
        return $this->belongsTo(Splitter::class, 'splitter_id');
    }

    public function target(): MorphTo
    {
        return $this->morphTo();
    }

    public function source(): MorphTo
    {
        return $this->morphTo();
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class, 'customer_id');
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class, 'branch_id');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    // Scopes
    public function scopeInputs($query)
    {
        return $query->where('port_type', self::TYPE_INPUT);
    }

    public function scopeOutputs($query)
    {
        return $query->where('port_type', self::TYPE_OUTPUT);
    }

    public function scopeAvailable($query)
    {
        return $query->where('status', self::STATUS_AVAILABLE);
    }

    public function scopeUsed($query)
    {
        return $query->where('status', self::STATUS_USED);
    }

    public function scopeReserved($query)
    {
        return $query->where('status', self::STATUS_RESERVED);
    }

    public function scopeFault($query)
    {
        return $query->where('status', self::STATUS_FAULT);
    }

    /**
     * Assign a destination target to this output port
     */
    public function assignTarget(Model $target, ?int $targetPort = null, ?string $label = null, ?int $customerId = null): void
    {
        $this->target_type = get_class($target);
        $this->target_id = $target->getKey();
        $this->target_port_number = $targetPort;
        $this->target_label = $label ?: ($target->name ?? ($target->code ?? class_basename($target) . ' #' . $target->getKey()));
        $this->customer_id = $customerId;
        $this->status = self::STATUS_USED;
        $this->save();
    }

    /**
     * Disconnect this port from any downstream target
     */
    public function disconnect(): void
    {
        $this->target_type = null;
        $this->target_id = null;
        $this->target_port_number = null;
        $this->target_label = null;
        $this->customer_id = null;
        $this->status = self::STATUS_AVAILABLE;
        $this->save();
    }
}
