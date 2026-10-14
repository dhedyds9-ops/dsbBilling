<?php

namespace App\Services\CRM;

use App\Models\User;
use App\Models\CRM\Customer;

class CustomerCodeGenerator
{
    public static function generate(): string
    {
        $prefix = date("Ymd");
        
        // Cari kode tertinggi yang sudah pernah dipakai untuk prefix hari ini (termasuk record yang di-soft-delete)
        $latestCustomerCode = Customer::withTrashed()
            ->where('code', 'like', $prefix . '%')
            ->orderBy('code', 'desc')
            ->value('code');

        $latestUserCode = User::where('customer_code', 'like', $prefix . '%')
            ->orderBy('customer_code', 'desc')
            ->value('customer_code');

        $highestCode = max((string)$latestCustomerCode, (string)$latestUserCode);

        if (!empty($highestCode) && str_starts_with($highestCode, $prefix)) {
            $lastSequence = (int) substr($highestCode, strlen($prefix));
            $count = $lastSequence + 1;
        } else {
            $count = max(
                User::where('customer_code', 'like', $prefix . '%')->count(),
                Customer::withTrashed()->where('code', 'like', $prefix . '%')->count()
            ) + 1;
        }
        
        do {
            $candidate = $prefix . str_pad((string)$count, 2, "0", STR_PAD_LEFT);
            $exists = User::where("customer_code", $candidate)->exists() 
                   || Customer::withTrashed()->where("code", $candidate)->exists();
            if ($exists) {
                $count++;
            }
        } while ($exists);

        return $candidate;
    }
}
