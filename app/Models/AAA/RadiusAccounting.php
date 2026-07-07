<?php

namespace App\Models\AAA;

use App\Models\Customer\CustomerService;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RadiusAccounting extends Model
{
    use HasFactory;

    protected $table = 'radius_accounting';

    protected $fillable = [
        'uuid',
        'acct_session_id',
        'username',
        'nas_ip_address',
        'nas_port_id',
        'framed_ip_address',
        'framed_protocol',
        'acct_start_time',
        'acct_stop_time',
        'acct_input_octets',
        'acct_output_octets',
        'acct_input_packets',
        'acct_output_packets',
        'acct_session_time',
        'acct_terminate_cause',
        'pppoe_user_id',
        'hotspot_user_id',
        'customer_service_id',
    ];

    protected $casts = [
        'acct_start_time' => 'datetime',
        'acct_stop_time' => 'datetime',
    ];

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
        return $this->belongsTo(CustomerService::class);
    }
}
