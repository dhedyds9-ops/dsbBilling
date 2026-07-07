<?php

namespace App\Models\Billing;

use App\Models\CRM\Customer;
use App\Models\Customer\Contract;
use App\Models\Customer\CustomerService;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Subscription extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'uuid',
        'customer_id',
        'contract_id',
        'customer_service_id',
        'status',
        'start_date',
        'end_date',
        'billing_cycle',
        'recurring_price',
        'next_billing_date',
        'last_billing_date',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'start_date' => 'datetime',
        'end_date' => 'datetime',
        'next_billing_date' => 'datetime',
        'last_billing_date' => 'datetime',
        'recurring_price' => 'decimal:2',
    ];

    public function customer()
    {
        return $this->belongsTo(Customer::class, 'customer_id');
    }

    public function contract()
    {
        return $this->belongsTo(Contract::class);
    }

    public function customerService()
    {
        return $this->belongsTo(CustomerService::class);
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
