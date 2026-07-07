<?php

namespace App\Models\CRM;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CoverageCheck extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'uuid',
        'prospect_id',
        'odp_id',
        'distance',
        'is_available',
        'notes',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'distance' => 'decimal:2',
        'is_available' => 'boolean',
    ];

    public function prospect()
    {
        return $this->belongsTo(Prospect::class);
    }

    public function odp()
    {
        return $this->belongsTo(\App\Models\ISP\Odp::class);
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
