<?php

namespace App\Models\ISP;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RouterMonitoringLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'router_id',
        'is_online',
        'identity',
        'version',
        'cpu',
        'cpu_load',
        'free_memory',
        'total_memory',
        'uptime',
        'error_message',
        'rx_bps',
        'tx_bps',
    ];
    
    protected $casts = [
        'is_online' => 'boolean',
        'cpu_load' => 'integer',
        'free_memory' => 'integer',
        'total_memory' => 'integer',
    ];

    public function router()
    {
        return $this->belongsTo(Router::class);
    }
}
