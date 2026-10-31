<?php

namespace App\Models\CRM;

use App\Models\Billing\Invoice;
use App\Models\CRM\Installation;
use App\Models\CRM\Contract;
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
    use SoftDeletes, \App\Traits\HasResellerScope, \App\Traits\HasBranchScope;

    protected $table = 'customers';

    protected $fillable = [
        'user_id',
        'code',
        'reseller_id',
        'latitude',
        'longitude',
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

        static::saved(function ($customer) {
            if ($customer->reseller_id) {
                \App\Models\Customer\CustomerService::withoutGlobalScopes()
                    ->where('customer_id', $customer->id)
                    ->where(function($q) use ($customer) {
                        $q->whereNull('reseller_id')->orWhere('reseller_id', '!=', $customer->reseller_id);
                    })
                    ->update(['reseller_id' => $customer->reseller_id]);

                $csIds = \App\Models\Customer\CustomerService::withoutGlobalScopes()
                    ->where('customer_id', $customer->id)
                    ->pluck('id');

                if ($csIds->isNotEmpty()) {
                    \App\Models\ISP\PPPoEUser::withoutGlobalScopes()
                        ->whereIn('customer_service_id', $csIds)
                        ->where(function($q) use ($customer) {
                            $q->whereNull('reseller_id')->orWhere('reseller_id', '!=', $customer->reseller_id);
                        })
                        ->update(['reseller_id' => $customer->reseller_id]);

                    \App\Models\ISP\HotspotUser::withoutGlobalScopes()
                        ->whereIn('customer_service_id', $csIds)
                        ->where(function($q) use ($customer) {
                            $q->whereNull('reseller_id')->orWhere('reseller_id', '!=', $customer->reseller_id);
                        })
                        ->update(['reseller_id' => $customer->reseller_id]);
                }
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

    public function tickets(): HasMany
    {
        return $this->hasMany(\App\Models\Support\Ticket::class, 'customer_id');
    }

    public function getCustomerCodeAttribute(): string
    {
        return (string) ($this->code ?? '');
    }

    public function setCustomerCodeAttribute($value): void
    {
        $this->attributes['code'] = $value;
    }

    public function getDisplayIdAttribute(): string
    {
        if (!empty($this->code)) {
            return (string) $this->code;
        }

        $date = $this->created_at ? $this->created_at->format('Ymd') : date('Ymd');
        return $date . str_pad((string) ($this->id % 100), 2, '0', STR_PAD_LEFT);
    }

    public function getOnuDeviceAttribute(): ?array
    {
        $services = $this->relationLoaded('customerServices')
            ? $this->customerServices
            : $this->customerServices()->with(['onu.acsDevice', 'acsDevice'])->get();

        foreach ($services as $cs) {
            if ($cs->onu && !empty(trim((string)$cs->onu->serial_number))) {
                return [
                    'sn' => trim((string)$cs->onu->serial_number),
                    'acs_device_id' => $cs->acsDevice?->id ?? $cs->onu->acsDevice?->id,
                    'onu' => $cs->onu,
                ];
            }
            if ($cs->acsDevice && !empty(trim((string)$cs->acsDevice->serial_number))) {
                return [
                    'sn' => trim((string)$cs->acsDevice->serial_number),
                    'acs_device_id' => $cs->acsDevice->id,
                    'onu' => $cs->onu,
                ];
            }
        }
        return null;
    }

    public function scopeWithoutGuests($query)
    {
        return $query->where(function ($q) {
            $q->where(function ($sq) {
                $sq->whereNull('code')
                  ->orWhere('code', 'not like', 'GST-%');
            })->orWhereHas('customerServices');
        });
    }
}
