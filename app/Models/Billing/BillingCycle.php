<?php

namespace App\Models\Billing;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BillingCycle extends Model
{
    use HasFactory;

    protected $fillable = [
        'uuid',
        'name',
        'cycle_type',
        'cycle_start',
        'cycle_end',
        'invoice_due_days',
        'status',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'cycle_start' => 'datetime',
        'cycle_end' => 'datetime',
    ];

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updatedBy()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
}
