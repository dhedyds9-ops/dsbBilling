<?php

namespace App\Models\Workforce;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class PhotoDocumentation extends Model {
    use HasUuids;

    protected $fillable = [
        'task_id',
        'photo_path',
        'description',
        'latitude',
        'longitude',
        'taken_at',
    ];

    protected $casts = [
        'taken_at' => 'datetime',
    ];
}
