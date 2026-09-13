<?php

namespace App\Models\ISP;

use App\Models\Customer\CustomerService;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DeviceCorrelation extends Model
{
    use HasFactory;

    protected $fillable = [
        'onu_id',
        'genieacs_device_id',
        'customer_service_id',
        'match_method',
        'confidence_score',
        'matched_by',
        'matched_at',
        'status',
    ];

    protected $casts = [
        'matched_at' => 'datetime',
        'confidence_score' => 'integer',
    ];

    public function onu(): BelongsTo
    {
        return $this->belongsTo(Onu::class);
    }

    public function customerService(): BelongsTo
    {
        return $this->belongsTo(CustomerService::class);
    }
}
