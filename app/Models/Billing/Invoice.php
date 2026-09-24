<?php

namespace App\Models\Billing;

use App\Models\CRM\Customer;
use App\Models\User;
use App\Models\Payment\Payment;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Invoice extends Model
{
    use \App\Traits\HasBranchScope;
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'uuid',
        'customer_id',
        'contract_id',
        'invoice_number',
        'issue_date',
        'due_date',
        'total_amount',
        'paid_amount',
        'currency',
        'status',
        'item_details',
        'reseller_id',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'issue_date' => 'datetime',
        'due_date' => 'datetime',
        'total_amount' => 'decimal:2',
        'paid_amount' => 'decimal:2',
        'item_details' => 'array',
    ];

    public function customer()
    {
        return $this->belongsTo(Customer::class, 'customer_id');
    }

    public function payments()
    {
        return $this->belongsToMany(Payment::class, 'invoice_payment')->withTimestamps();
    }

    public function items()
    {
        return $this->hasMany(InvoiceItem::class);
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updatedBy()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    /**
     * Cek apakah invoice masih dalam grace period
     * (due_date + x hari masih >= hari ini)
     */
    public function hasActiveGracePeriod(): bool
    {
        $graceDays = (int) \App\Models\Setting::getValue('billing.grace_period_days', 0);
        if ($graceDays <= 0) {
            return false;
        }
        
        $graceEnd = $this->due_date->copy()->addDays($graceDays)->endOfDay();
        return now()->lessThanOrEqualTo($graceEnd);
    }
}