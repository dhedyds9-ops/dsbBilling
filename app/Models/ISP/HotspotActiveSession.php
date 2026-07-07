<?php

namespace App\Models\ISP;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HotspotActiveSession extends Model
{
    use HasFactory;

    protected $fillable = [
        'router_id',
        'user',
        'mac_address',
        'address',
        'server',
        'login_by',
        'uptime',
        'bytes_in',
        'bytes_out',
        'session_started_at',
    ];
    
    protected $casts = [
        'bytes_in' => 'integer',
        'bytes_out' => 'integer',
        'session_started_at' => 'datetime',
    ];

    public function router()
    {
        return $this->belongsTo(Router::class);
    }
}
