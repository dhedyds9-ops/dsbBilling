<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class Alarm extends Model
{
    use HasUuids;
    
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
    
    public function source()
    {
        return $this->morphTo();
    }
    
    public function acknowledgedBy()
    {
        return $this->belongsTo(User::class, 'acknowledged_by');
    }
}
