<?php

namespace App\Services\CRM;

use App\Models\User;
use App\Models\CRM\Customer;

class CustomerCodeGenerator
{
    public static function generate(): string
    {
        $prefix = date("Ymd");
        
        // Dapatkan jumlah total customer untuk base sequence
        $count = max(User::count(), Customer::count()) + 1;
        
        do {
            $candidate = $prefix . str_pad((string)$count, 2, "0", STR_PAD_LEFT);
            $exists = User::where("customer_code", $candidate)->exists() || Customer::where("code", $candidate)->exists();
            if ($exists) {
                $count++;
            }
        } while ($exists);

        return $candidate;
    }
}
