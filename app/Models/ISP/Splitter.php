<?php

namespace App\Models\ISP;

use App\Models\Master\Branch;
use App\Models\User;
use App\Traits\HasBranchScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Splitter extends Model
{
    use HasBranchScope;
    use HasFactory, SoftDeletes;

    public const TYPE_PLC = 'plc';
    public const TYPE_FBT = 'fbt';

    public const PACKAGE_TRAY = 'tray';
    public const PACKAGE_CASSETTE = 'cassette';
    public const PACKAGE_MINI_TUBE = 'mini_tube';
    public const PACKAGE_ABS_BOX = 'abs_box';
    public const PACKAGE_RACK_MOUNT = 'rack_mount';

    public const STANDARD_LOSSES = [
        '1:2' => 3.60,
        '1:4' => 7.20,
        '1:8' => 10.50,
        '1:16' => 13.70,
        '1:32' => 17.00,
        '1:64' => 20.50,
        '2:4' => 7.80,
        '2:8' => 11.00,
        '2:16' => 14.20,
        '2:32' => 17.50,
        '50:50' => 3.40,
        '60:40' => 4.50,
        '70:30' => 5.60,
        '80:20' => 7.50,
        '85:15' => 8.80,
        '90:10' => 10.80,
    ];

    protected $fillable = [
        'odp_id',
        'odc_id',
        'vendor_id',
        'code',
        'name',
        'description',
        'model',
        'serial_number',
        'type',
        'ratio_label',
        'package_type',
        'input_port_count',
        'ratio',
        'port_count',
        'active_port_count',
        'insertion_loss_db',
        'status',
        'branch_id',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'ratio' => 'integer',
        'port_count' => 'integer',
        'active_port_count' => 'integer',
        'input_port_count' => 'integer',
        'insertion_loss_db' => 'float',
    ];

    protected static function booted()
    {
        static::saving(function ($model) {
            // Auto inherit branch_id
            if (empty($model->branch_id)) {
                if (!empty($model->odc_id)) {
                    $model->branch_id = Odc::withoutGlobalScopes()->where('id', $model->odc_id)->value('branch_id');
                } elseif (!empty($model->odp_id)) {
                    $model->branch_id = Odp::withoutGlobalScopes()->where('id', $model->odp_id)->value('branch_id');
                }
            }

            // Default ratio label
            if (empty($model->ratio_label)) {
                $model->ratio_label = $model->ratio ? "1:{$model->ratio}" : ($model->port_count ? "1:{$model->port_count}" : '1:8');
            }

            // Sync integer ratio from ratio label if numeric
            if (empty($model->ratio) && preg_match('/^[12]:(\d+)$/', $model->ratio_label, $m)) {
                $model->ratio = (int) $m[1];
            }

            // Auto set nominal insertion loss if empty
            if (empty($model->insertion_loss_db) && isset(self::STANDARD_LOSSES[$model->ratio_label])) {
                $model->insertion_loss_db = self::STANDARD_LOSSES[$model->ratio_label];
            }
        });

        static::saved(function ($model) {
            // Auto generate ports if missing
            $model->syncPorts();
        });
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function odc(): BelongsTo
    {
        return $this->belongsTo(Odc::class);
    }

    public function odp(): BelongsTo
    {
        return $this->belongsTo(Odp::class);
    }

    public function vendor(): BelongsTo
    {
        return $this->belongsTo(Vendor::class);
    }

    public function onus(): HasMany
    {
        return $this->hasMany(Onu::class);
    }

    public function ports(): HasMany
    {
        return $this->hasMany(SplitterPort::class, 'splitter_id');
    }

    public function inputPorts(): HasMany
    {
        return $this->hasMany(SplitterPort::class, 'splitter_id')->where('port_type', SplitterPort::TYPE_INPUT);
    }

    public function outputPorts(): HasMany
    {
        return $this->hasMany(SplitterPort::class, 'splitter_id')->where('port_type', SplitterPort::TYPE_OUTPUT);
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

    /**
     * Ensure all physical ports (input & output) exist in database
     */
    public function syncPorts(): void
    {
        $inputCount = max(1, (int) ($this->input_port_count ?: 1));
        $outputCount = max(1, (int) ($this->port_count ?: ($this->ratio ?: 8)));

        // 1. Generate Input Ports
        for ($i = 1; $i <= $inputCount; $i++) {
            $this->ports()->firstOrCreate(
                [
                    'port_type' => SplitterPort::TYPE_INPUT,
                    'port_number' => $i,
                ],
                [
                    'status' => SplitterPort::STATUS_AVAILABLE,
                    'attenuation_db' => 0.00,
                    'branch_id' => $this->branch_id,
                ]
            );
        }

        // 2. Generate Output Ports
        for ($o = 1; $o <= $outputCount; $o++) {
            $color = SplitterPort::TIA_598_COLORS[(($o - 1) % 12) + 1] ?? null;
            $nominalLoss = $this->insertion_loss_db ?: (self::STANDARD_LOSSES[$this->ratio_label] ?? 10.50);

            $this->ports()->firstOrCreate(
                [
                    'port_type' => SplitterPort::TYPE_OUTPUT,
                    'port_number' => $o,
                ],
                [
                    'status' => SplitterPort::STATUS_AVAILABLE,
                    'attenuation_db' => $nominalLoss,
                    'core_color' => $color,
                    'branch_id' => $this->branch_id,
                ]
            );
        }

        // 3. Recount active ports without recursion
        $usedCount = $this->outputPorts()->where('status', SplitterPort::STATUS_USED)->count();
        if ($this->active_port_count !== $usedCount) {
            $this->newQuery()->where('id', $this->id)->update(['active_port_count' => $usedCount]);
        }
    }
}
