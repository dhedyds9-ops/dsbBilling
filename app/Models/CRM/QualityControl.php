<?php

namespace App\Models\CRM;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class QualityControl extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'uuid',
        'installation_id',
        'technician_id',
        'status',
        'notes',
        'photos',
        'checked_at',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'photos' => 'array',
        'checked_at' => 'datetime',
    ];

    public function installation()
    {
        return $this->belongsTo(Installation::class);
    }

    public function technician()
    {
        return $this->belongsTo(User::class, 'technician_id');
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updatedBy()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
}
