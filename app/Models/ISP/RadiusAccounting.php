<?php

namespace App\Models\ISP;

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
        'acct_unique_session_id',
        'acct_status_type',
        'username',
        'nas_ip_address',
        'radius_nas_id',
        'nas_device_id',
        'nas_port_id',
        'calling_station_id',
        'called_station_id',
        'connect_info',
        'framed_ip_address',
        'framed_protocol',
        'acct_start_time',
        'acct_stop_time',
        'acct_input_octets',
        'acct_output_octets',
        'acct_input_gigawords',
        'acct_output_gigawords',
        'acct_input_packets',
        'acct_output_packets',
        'acct_session_time',
        'acct_delay_time',
        'acct_terminate_cause',
        'terminate_cause_id',
        'pppoe_user_id',
        'hotspot_user_id',
        'customer_service_id',
        'raw_payload',
        'ingest_source',
        'received_at',
    ];

    protected $casts = [
        'acct_start_time' => 'datetime',
        'acct_stop_time' => 'datetime',
        'received_at' => 'datetime',
        'raw_payload' => 'array',
        'acct_input_octets' => 'integer',
        'acct_output_octets' => 'integer',
        'acct_input_gigawords' => 'integer',
        'acct_output_gigawords' => 'integer',
        'acct_input_packets' => 'integer',
        'acct_output_packets' => 'integer',
        'acct_session_time' => 'integer',
        'acct_delay_time' => 'integer',
        'terminate_cause_id' => 'integer',
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

    public function radiusNas()
    {
        return $this->belongsTo(RadiusNas::class);
    }

    public function nasDevice()
    {
        return $this->belongsTo(NasDevice::class);
    }
}
