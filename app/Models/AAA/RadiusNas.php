<?php

namespace App\Models\AAA;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class RadiusNas extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'radius_nas';

    protected $fillable = [
        'uuid',
        'nas_name',
        'nas_ip_address',
        'nas_secret',
        'nas_type',
        'nas_port',
        'community',
        'description',
        'status',
        'created_by',
        'updated_by',
    ];

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updatedBy()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
}
