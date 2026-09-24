<?php

namespace App\Models\ACS;

use App\Models\Customer\CustomerService;
use App\Models\Inventory\Asset;
use App\Models\ISP\Onu;
use App\Models\ISP\Olt;
use App\Models\ISP\Pop;
use App\Models\ISP\Odp;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ACSDevice extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'acs_devices';

    protected $fillable = [
        'uuid',
        'serial_number',
        'mac_address',
        'oui',
        'manufacturer',
        'vendor_id',
        'model',
        'product_class',
        'hardware_version',
        'software_version',
        'firmware_version',
        'ip_address',
        'connection_request_url',
        'last_inform',
        'last_contact',
        'status',
        'customer_service_id',
        'asset_id',
        'onu_id',
        'olt_id',
        'pop_id',
        'odp_id',
        'latitude',
        'longitude',
        'signal',
        'uptime',
        'cpu',
        'memory',
        'temperature',
        'notes',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'last_inform' => 'datetime',
        'last_contact' => 'datetime',
        'uptime' => 'integer',
        'cpu' => 'float',
        'memory' => 'float',
        'temperature' => 'float',
        'latitude' => 'float',
        'longitude' => 'float',
    ];

    public function scopeOnline($query)
    {
        return $query->where('status', 'online');
    }

    public function scopeOffline($query)
    {
        return $query->where('status', 'offline');
    }

    public function customerService()
    {
        return $this->belongsTo(CustomerService::class);
    }

    public function asset()
    {
        return $this->belongsTo(Asset::class);
    }

    public function onu()
    {
        return $this->belongsTo(Onu::class);
    }

    public function olt()
    {
        return $this->belongsTo(Olt::class);
    }

    public function pop()
    {
        return $this->belongsTo(Pop::class);
    }

    public function odp()
    {
        return $this->belongsTo(Odp::class);
    }

    public function vendor()
    {
        return $this->belongsTo(\App\Models\ISP\Vendor::class);
    }

    public function tasks()
    {
        return $this->hasMany(DeviceTask::class, 'acs_device_id');
    }

    public function alarms()
    {
        return $this->hasMany(ACSAlarm::class, 'acs_device_id');
    }

    public function logs()
    {
        return $this->hasMany(ACSLog::class, 'acs_device_id');
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updatedBy()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
}
