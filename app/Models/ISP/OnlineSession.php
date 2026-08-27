<?php

namespace App\Models\ISP;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class OnlineSession extends Model
{
    use HasFactory;

    protected $table = 'online_sessions';

    protected $fillable = [
        'session_key',
        'protocol',
        'username',
        'router_id',
        'nas_device_id',
        'radius_nas_id',
        'pppoe_user_id',
        'hotspot_user_id',
        'customer_service_id',
        'service',
        'caller_id',
        'mac_address',
        'address',
        'server',
        'login_by',
        'uptime',
        'bytes_in',
        'bytes_out',
        'packets_in',
        'packets_out',
        'rate_up',
        'rate_down',
        'acct_session_id',
        'source',
        'session_started_at',
        'last_seen_at',
    ];

    protected $casts = [
        'bytes_in' => 'integer',
        'bytes_out' => 'integer',
        'packets_in' => 'integer',
        'packets_out' => 'integer',
        'session_started_at' => 'datetime',
        'last_seen_at' => 'datetime',
    ];

    public function scopePppoe($query)
    {
        return $query->where('protocol', 'pppoe');
    }

    public function scopeHotspot($query)
    {
        return $query->where('protocol', 'hotspot');
    }

    public function scopeSourcePoller($query)
    {
        return $query->where('source', 'router_poller');
    }

    public function scopeSourceRadius($query)
    {
        return $query->where('source', 'radius_accounting');
    }

    public static function buildSessionKey(string $protocol, $nasIp, string $username, ?string $caller, ?string $address): string
    {
        $payload = implode('|', [
            strtolower($protocol),
            (string)$nasIp,
            strtolower($username),
            strtolower((string)$caller),
            (string)$address,
        ]);
        return hash('md5', $payload);
    }

    public function router()
    {
        return $this->belongsTo(Router::class);
    }

    public function nasDevice()
    {
        return $this->belongsTo(NasDevice::class);
    }

    public function radiusNas()
    {
        return $this->belongsTo(RadiusNas::class);
    }

    public function pppoeUser()
    {
        return $this->belongsTo(PPPoEUser::class);
    }

    public function hotspotUser()
    {
        return $this->belongsTo(HotspotUser::class);
    }

    public function customerService()
    {
        return $this->belongsTo(\App\Models\Customer\CustomerService::class);
    }
}
