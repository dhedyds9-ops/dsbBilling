<?php

namespace App\Models\CRM;

use App\Models\Billing\Invoice;
use App\Models\CRM\Installation;
use App\Models\Customer\Contract;
use App\Models\Customer\CustomerService;
use App\Models\Payment\Payment;
use App\Models\User;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Customer extends Model
{
    use SoftDeletes;

    protected $table = 'members';

    protected $fillable = [
        'code',
        'name',
        'phone',
        'email',
        'status',
        'address',
        'created_by',
        'updated_by',
    ];

    protected static function booted()
    {
        static::deleted(function ($customer) {
            // Deactivate associated login user
            if ($customer->user_id) {
                \App\Models\User::where('id', $customer->user_id)->update(['is_active' => false]);
            }

            // Cascade soft delete to customer services
            if (!$customer->isForceDeleting()) {
                foreach ($customer->customerServices()->get() as $cs) {
                    $cs->delete();
                }
            }
        });
        
        static::restoring(function ($customer) {
            // Reactivate associated login user
            if ($customer->user_id) {
                \App\Models\User::where('id', $customer->user_id)->update(['is_active' => true]);
            }

            // Cascade restore to customer services
            foreach ($customer->customerServices()->onlyTrashed()->get() as $cs) {
                $cs->restore();
            }
        });
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function reseller(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reseller_id');
    }

    public function customerServices(): HasMany
    {
        return $this->hasMany(CustomerService::class, 'customer_id');
    }

    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class, 'customer_id');
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class, 'customer_id');
    }

    public function contracts(): HasMany
    {
        return $this->hasMany(Contract::class, 'customer_id');
    }

    public function installations(): HasManyThrough
    {
        return $this->hasManyThrough(Installation::class, Contract::class, 'customer_id', 'contract_id');
    }
}
