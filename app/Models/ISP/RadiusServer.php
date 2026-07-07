<?php

namespace App\Models\ISP;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class RadiusServer extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'radius_servers';

    protected $fillable = [
        'pop_id',
        'code',
        'name',
        'description',
        'ip_address',
        'auth_port',
        'acct_port',
        'secret',
        'status',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'auth_port' => 'integer',
        'acct_port' => 'integer',
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
