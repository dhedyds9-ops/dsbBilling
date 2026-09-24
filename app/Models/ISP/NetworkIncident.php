<?php

namespace App\Models\ISP;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NetworkIncident extends Model
{
    use HasFactory;

    protected $fillable = [
        'type',
        'reference_id',
        'impacted_customers_count',
        'status',
        'description',
        'started_at',
        'resolved_at',
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'resolved_at' => 'datetime',
    ];
}
