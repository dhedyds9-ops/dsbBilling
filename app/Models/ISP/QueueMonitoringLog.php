<?php

namespace App\Models\ISP;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class QueueMonitoringLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'router_id',
        'queue_name',
        'target',
        'max_limit',
        'burst_limit',
        'limit_at',
        'bytes_in',
        'bytes_out',
        'packets_in',
        'packets_out',
        'rate_up',
        'rate_down',
    ];
    
    protected $casts = [
        'bytes_in' => 'integer',
        'bytes_out' => 'integer',
        'packets_in' => 'integer',
        'packets_out' => 'integer',
    ];

    public function router()
    {
        return $this->belongsTo(Router::class);
    }
}
