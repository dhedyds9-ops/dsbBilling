<?php

namespace App\Models\ISP;

use App\Models\Master\Branch;
use App\Models\User;
use App\Traits\HasBranchScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class VoucherRequest extends Model
{
    use HasFactory, SoftDeletes, HasBranchScope;

    protected $table = 'voucher_requests';

    protected $fillable = [
        'request_no',
        'reseller_id',
        'branch_id',
        'service_profile_id',
        'voucher_template_id',
        'quantity',
        'unit_price',
        'cost_price',
        'total_amount',
        'delivery_type',
        'payment_method',
        'payment_status',
        'status',
        'batch_pool_id',
        'generated_voucher_ids',
        'rejection_reason',
        'notes',
        'approved_by',
        'approved_at',
        'completed_at',
        'created_by',
    ];

    protected $casts = [
        'quantity' => 'integer',
        'unit_price' => 'decimal:2',
        'cost_price' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'generated_voucher_ids' => 'array',
        'approved_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    // Status Constants
    public const STATUS_PENDING = 'pending';
    public const STATUS_APPROVED = 'approved';
    public const STATUS_PRINTING = 'printing';
    public const STATUS_READY_FOR_PICKUP = 'ready_for_pickup';
    public const STATUS_COMPLETED = 'completed';
    public const STATUS_REJECTED = 'rejected';
    public const STATUS_CANCELLED = 'cancelled';

    // Delivery Type Constants
    public const DELIVERY_PHYSICAL_PRINT = 'physical_print';
    public const DELIVERY_DIGITAL = 'digital';

    // Payment Method Constants
    public const PAYMENT_DEPOSIT = 'deposit';
    public const PAYMENT_CASH = 'cash';
    public const PAYMENT_TEMPO = 'tempo';

    // Payment Status Constants
    public const PAYMENT_STATUS_UNPAID = 'unpaid';
    public const PAYMENT_STATUS_PAID = 'paid';

    public function reseller(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reseller_id');
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class, 'branch_id');
    }

    public function serviceProfile(): BelongsTo
    {
        return $this->belongsTo(ServiceProfile::class, 'service_profile_id');
    }

    public function voucherTemplate(): BelongsTo
    {
        return $this->belongsTo(VoucherTemplate::class, 'voucher_template_id');
    }

    public function batchPool(): BelongsTo
    {
        return $this->belongsTo(VoucherPool::class, 'batch_pool_id');
    }

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function scopeForReseller($query, $userOrId)
    {
        $id = $userOrId instanceof User ? $userOrId->getEffectiveResellerId() : $userOrId;
        return $query->where('reseller_id', $id);
    }

    public function getEstimatedProfitAttribute(): float
    {
        return (float) (($this->unit_price - $this->cost_price) * $this->quantity);
    }

    public function getDeliveryTypeLabelAttribute(): string
    {
        return match ($this->delivery_type) {
            self::DELIVERY_PHYSICAL_PRINT => 'Cetak Fisik',
            self::DELIVERY_DIGITAL => 'Softcopy Digital',
            default => ucfirst(str_replace('_', ' ', $this->delivery_type ?? '')),
        };
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            self::STATUS_PENDING => 'Menunggu Review',
            self::STATUS_APPROVED => 'Disetujui',
            self::STATUS_PRINTING => 'Sedang Dicetak',
            self::STATUS_READY_FOR_PICKUP => 'Siap Diambil/Kirim',
            self::STATUS_COMPLETED => 'Selesai',
            self::STATUS_REJECTED => 'Ditolak',
            self::STATUS_CANCELLED => 'Dibatalkan',
            default => ucfirst(str_replace('_', ' ', $this->status ?? '')),
        };
    }

    public function getStatusColorAttribute(): string
    {
        return match ($this->status) {
            self::STATUS_PENDING => 'amber',
            self::STATUS_APPROVED => 'blue',
            self::STATUS_PRINTING => 'indigo',
            self::STATUS_READY_FOR_PICKUP => 'cyan',
            self::STATUS_COMPLETED => 'emerald',
            self::STATUS_REJECTED => 'rose',
            self::STATUS_CANCELLED => 'slate',
            default => 'slate',
        };
    }
}
