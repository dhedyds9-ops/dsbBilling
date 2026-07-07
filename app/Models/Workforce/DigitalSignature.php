<?php

namespace App\Models\Workforce;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class DigitalSignature extends Model {
    use HasUuids;

    protected $fillable = [
        'task_id',
        'signer_id',
        'signer_name',
        'signature_path',
        'signed_at',
    ];

    protected $casts = [
        'signed_at' => 'datetime',
    ];
}
