<?php

namespace App\Models\Workforce;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class TroubleshootingTask extends Model {
    use HasUuids;

    protected $fillable = [
        'work_order_id',
        'assignment_id',
        'customer_id',
        'ticket_id',
        'status',
        'type',
        'notes',
        'resolution',
        'started_at',
        'completed_at',
        'qc_at',
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
        'qc_at' => 'datetime',
    ];
}
