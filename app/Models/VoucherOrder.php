<?php

namespace App\Models;

use App\Models\Billing\Invoice;
use App\Models\ISP\ServiceProfile;
use App\Models\AAA\Voucher;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class VoucherOrder extends Model
{
    use HasFactory;

    protected $fillable = [
        'uuid',
        'invoice_id',
        'service_profile_id',
        'voucher_id',
        'wa_number',
        'service_profile_name',
        'unit_price',
        'quantity',
        'total_amount',
        'status',
        'payment_reference',
        'voucher_username',
        'voucher_password',
        'voucher_generated_at',
        'paid_at',
        'completed_at',
        'failure_reason',
    ];

    protected $casts = [
        'unit_price' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'voucher_generated_at' => 'datetime',
        'paid_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    const STATUS_PENDING = 'pending';
    const STATUS_PAYMENT_PROCESSING = 'payment_processing';
    const STATUS_PAID = 'paid';
    const STATUS_VOUCHER_GENERATING = 'voucher_generating';
    const STATUS_COMPLETED = 'completed';
    const STATUS_FAILED = 'failed';
    const STATUS_EXPIRED = 'expired';
    const STATUS_CANCELLED = 'cancelled';

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->uuid)) {
                $model->uuid = (string) Str::uuid();
            }
        });
    }

    public function invoice()
    {
        return $this->belongsTo(Invoice::class);
    }

    public function serviceProfile()
    {
        return $this->belongsTo(ServiceProfile::class);
    }

    public function voucher()
    {
        return $this->belongsTo(Voucher::class, 'voucher_id');
    }
}
