<?php

namespace App\Models\ISP;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class IpPool extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'ip_pools';

    protected $fillable = [
        'pop_id',
        'code',
        'name',
        'description',
        'network',
        'netmask',
        'gateway',
        'dns_servers',
        'start_ip',
        'end_ip',
        'total_ips',
        'used_ips',
        'status',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'total_ips' => 'integer',
        'used_ips' => 'integer',
    ];

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function pop()
    {
        return $this->belongsTo(Pop::class);
    }

    public function createdBy()
    {
        return $this->belongsTo(\App\Models\User::class, 'created_by');
    }

    public function updatedBy()
    {
        return $this->belongsTo(\App\Models\User::class, 'updated_by');
    }
}
