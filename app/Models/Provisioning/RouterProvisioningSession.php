<?php

namespace App\Models\Provisioning;

use App\Models\ISP\Router;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class RouterProvisioningSession extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'router_provisioning_sessions';

    protected $fillable = [
        'uuid',
        'router_id',
        'token_hash',
        'status',
        'error_message',
        'expires_at',
        'used_at',
        'revoked_at',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
        'used_at' => 'datetime',
        'revoked_at' => 'datetime',
    ];

    public function router()
    {
        return $this->belongsTo(Router::class);
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updatedBy()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function isValid(): bool
    {
        if ($this->status !== 'PENDING' && $this->status !== 'GENERATED') {
            return false;
        }

        if ($this->revoked_at !== null) {
            return false;
        }

        if ($this->used_at !== null) {
            return false;
        }

        if ($this->expires_at !== null && now()->isAfter($this->expires_at)) {
            return false;
        }

        return true;
    }
}
