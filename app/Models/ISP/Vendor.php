<?php

namespace App\Models\ISP;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Vendor extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'code',
        'name',
        'description',
        'phone',
        'email',
        'address',
        'contact_person',
        'status',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        //
    ];

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function olts()
    {
        return $this->hasMany(Olt::class);
    }

    public function splitters()
    {
        return $this->hasMany(Splitter::class);
    }

    public function onus()
    {
        return $this->hasMany(Onu::class);
    }

    public function routers()
    {
        return $this->hasMany(Router::class);
    }

    public function switches()
    {
        return $this->hasMany(Switcher::class);
    }

    public function accessPoints()
    {
        return $this->hasMany(AccessPoint::class);
    }

    public function nasDevices()
    {
        return $this->hasMany(NasDevice::class);
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
