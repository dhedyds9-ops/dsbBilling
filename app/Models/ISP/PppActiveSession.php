<?php

namespace App\Models\ISP;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PppActiveSession extends Model
{
    use HasFactory;

    protected $fillable = [
        'router_id',
        'name',
        'service',
        'caller_id',
        'address',
        'uptime',
        'bytes_in',
        'bytes_out',
        'packets_in',
        'packets_out',
        'rate_up',
        'rate_down',
        'session_started_at',
    ];
    
    protected $casts = [
        'bytes_in' => 'integer',
        'bytes_out' => 'integer',
        'packets_in' => 'integer',
        'packets_out' => 'integer',
        'session_started_at' => 'datetime',
    ];

    public function router()
    {
        return $this->belongsTo(Router::class);
    }
}
