<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserDeviceToken extends Model
{
    use HasFactory;

    protected $table = 'device_tokens';

    protected $fillable = [
        'user_id',
        'company_id',
        'device_id',
        'fcm_token',
        'platform',
        'app_version',
        'device_name',
        'is_active',
        'last_seen_at',
        'revoked_at',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'last_seen_at' => 'datetime',
        'revoked_at' => 'datetime',
    ];

    /**
     * Sembunyikan token dari serialisasi JSON default agar tidak bocor ke endpoint daftar notifikasi.
     */
    protected $hidden = [
        'fcm_token',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Scope untuk token aktif.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true)->whereNull('revoked_at');
    }
}
