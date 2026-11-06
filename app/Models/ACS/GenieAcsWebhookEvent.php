<?php

namespace App\Models\ACS;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GenieAcsWebhookEvent extends Model
{
    use HasFactory;

    protected $table = 'genieacs_webhook_events';

    protected $fillable = [
        'event_id',
        'device_id',
        'serial_number',
        'event_type',
        'event_codes',
        'manufacturer',
        'product_class',
        'software_version',
        'ip_address',
        'last_inform_at',
        'received_at',
        'processing_status',
        'processed_at',
        'error_message',
        'retry_count',
        'payload',
    ];

    protected $casts = [
        'event_codes' => 'array',
        'payload' => 'array',
        'last_inform_at' => 'datetime',
        'received_at' => 'datetime',
        'processed_at' => 'datetime',
        'retry_count' => 'integer',
    ];

    public const STATUS_RECEIVED = 'received';
    public const STATUS_QUEUED = 'queued';
    public const STATUS_PROCESSING = 'processing';
    public const STATUS_PROCESSED = 'processed';
    public const STATUS_FAILED = 'failed';
    public const STATUS_IGNORED = 'ignored';

    public function scopeUnprocessed($query)
    {
        return $query->whereIn('processing_status', [self::STATUS_RECEIVED, self::STATUS_QUEUED]);
    }

    public function scopeProcessed($query)
    {
        return $query->where('processing_status', self::STATUS_PROCESSED);
    }

    public function scopeFailed($query)
    {
        return $query->where('processing_status', self::STATUS_FAILED);
    }

    public function scopeIgnored($query)
    {
        return $query->where('processing_status', self::STATUS_IGNORED);
    }
}
