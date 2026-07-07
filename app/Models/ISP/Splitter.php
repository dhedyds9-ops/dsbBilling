<?php

namespace App\Models\ISP;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Splitter extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'odp_id',
        'vendor_id',
        'code',
        'name',
        'description',
        'model',
        'serial_number',
        'ratio',
        'port_count',
        'active_port_count',
        'status',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'ratio' => 'integer',
        'port_count' => 'integer',
        'active_port_count' => 'integer',
    ];

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function odp()
    {
        return $this->belongsTo(Odp::class);
    }

    public function vendor()
    {
        return $this->belongsTo(Vendor::class);
    }

    public function onus()
    {
        return $this->hasMany(Onu::class);
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
