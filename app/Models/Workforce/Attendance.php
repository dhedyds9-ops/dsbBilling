<?php

namespace App\Models\Workforce;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Src\Domain\Workforce\Enums\AttendanceStatus;

class Attendance extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'id',
        'technician_id',
        'date',
        'status',
        'check_in_latitude',
        'check_in_longitude',
        'checked_in_at',
        'check_out_latitude',
        'check_out_longitude',
        'checked_out_at',
        'notes',
    ];

    protected $casts = [
        'date' => 'date',
        'status' => AttendanceStatus::class,
        'check_in_latitude' => 'float',
        'check_in_longitude' => 'float',
        'check_out_latitude' => 'float',
        'check_out_longitude' => 'float',
        'checked_in_at' => 'datetime',
        'checked_out_at' => 'datetime',
    ];

    public function technician(): BelongsTo
    {
        return $this->belongsTo(User::class, 'technician_id');
    }
}
