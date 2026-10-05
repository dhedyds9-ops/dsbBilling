<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Role extends Model
{

    protected $fillable = [
        'name',
        'display_name',
        'description',
    ];

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class);
    }

    public function permissions(): BelongsToMany
    {
        return $this->belongsToMany(Permission::class);
    }

    public static function hiddenInUserManagement(): array
    {
        return config('roles.hidden_roles', ['customer']);
    }

    public static function allSystemRoleNames(): array
    {
        return config('roles.system_roles', ['administrator', 'manager', 'reseller', 'customer']);
    }

    public function getIsSystemAttribute(): bool
    {
        return in_array($this->name, self::allSystemRoleNames(), true);
    }
}
