<?php

namespace App\Models\ISP;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class InternetPackage extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'code',
        'name',
        'description',
        'price',
        'bandwidth_download',
        'bandwidth_upload',
        'quota_mb', // Kuota dalam MB, null untuk unlimited
        'validity_days', // Durasi aktif dalam hari, null untuk bulanan
        'is_active',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'quota_mb' => 'integer',
        'validity_days' => 'integer',
        'is_active' => 'boolean',
    ];

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
}
