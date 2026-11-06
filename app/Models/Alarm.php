<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Alarm extends Model
{
    protected $fillable = [
        'uuid',
        'level',
        'source_type',
        'source_id',
        'source_name',
        'title',
        'description',
        'metadata',
        'started_at',
        'resolved_at',
        'resolved_by',
        'status',
        'acknowledged_by',
        'acknowledged_note',
        'acknowledged_at',
    ];
    
    protected $casts = [
        'metadata' => 'array',
        'started_at' => 'datetime',
        'resolved_at' => 'datetime',
        'acknowledged_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::creating(function (Alarm $alarm) {
            if (empty($alarm->uuid)) {
                $alarm->uuid = (string) Str::uuid();
            }
        });
    }
    
    public function source()
    {
        return $this->morphTo();
    }
    
    public function acknowledgedBy()
    {
        return $this->belongsTo(User::class, 'acknowledged_by');
    }
}
