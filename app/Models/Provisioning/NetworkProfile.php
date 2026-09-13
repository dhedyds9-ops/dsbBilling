<?php

namespace App\Models\Provisioning;

use App\Models\Customer\CustomerService;
use App\Models\ISP\Router;
use App\Models\User;
use App\Models\ISP\IpPool;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class NetworkProfile extends Model
{
    use \App\Traits\HasBranchScope;
    use HasFactory, SoftDeletes;

    protected $table = 'network_profiles';

    protected static function boot()
    {
        parent::boot();
        static::creating(function ($model) {
            if (empty($model->uuid)) {
                $model->uuid = (string) \Illuminate\Support\Str::uuid();
            }
        });
    }

    protected $fillable = [
        'uuid',
        'name',
        'description',
        'type',
        'router_id',
        'vlan_id',
        'ip_pool_id',
        'created_by',
        'updated_by',
    ];

    public function router()
    {
        return $this->belongsTo(Router::class);
    }

    public function ipPool()
    {
        return $this->belongsTo(IpPool::class);
    }

    public function customerServices()
    {
        return $this->hasMany(CustomerService::class);
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


