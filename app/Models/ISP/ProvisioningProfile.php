<?php

namespace App\Models\ISP;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProvisioningProfile extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'service_type',
        'wan_mode',
        'vlan_id',
        'vlan_mode',
        'vlan_priority',
        'nat',
        'dhcp_server',
        'lan_mapping',
        'wifi_mapping',
        'is_active',
        'description',
    ];

    protected $casts = [
        'nat' => 'boolean',
        'dhcp_server' => 'boolean',
        'is_active' => 'boolean',
        'lan_mapping' => 'array',
        'wifi_mapping' => 'array',
    ];
}
