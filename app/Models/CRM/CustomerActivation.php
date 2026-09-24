<?php

namespace App\Models\CRM;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CustomerActivation extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'uuid',
        'quality_control_id',
        'customer_service_id',
        'invoice_id',
        'activated_at',
        'notes',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'activated_at' => 'datetime',
    ];

    public function qualityControl()
    {
        return $this->belongsTo(QualityControl::class);
    }

    public function customerService()
    {
        return $this->belongsTo(\App\Models\Customer\CustomerService::class);
    }

    public function invoice()
    {
        return $this->belongsTo(\App\Models\Billing\Invoice::class);
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
