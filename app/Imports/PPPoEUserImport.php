<?php

namespace App\Imports;

use App\Models\ISP\PPPoEUser;
use App\Models\ISP\ServiceProfile;
use App\Models\Customer\CustomerService;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Illuminate\Support\Str;

class PPPoEUserImport implements ToModel, WithHeadingRow, WithValidation
{
    protected $user;

    public function __construct($user)
    {
        $this->user = $user;
    }

    public function model(array $row)
    {
        try {
            $serviceProfile = ServiceProfile::where('name', $row['paket_langganan'] ?? $row['service_profile'] ?? null)->first();
            $customerService = CustomerService::whereHas('customer', function($q) use ($row) {
                $q->where('name', $row['nama_pelanggan'] ?? $row['customer_name'] ?? null);
            })->first();

            return PPPoEUser::create([
                'uuid' => (string) Str::uuid(),
                'username' => $row['username'] ?? null,
                'password' => $row['password'] ?? Str::random(12),
                'customer_service_id' => $customerService?->id,
                'service_profile_id' => $serviceProfile?->id,
                'status' => $row['status'] ?? 'pending',
                'created_by' => $this->user->id,
                'updated_by' => $this->user->id,
            ]);
        } catch (\Exception $e) {
            Log::error('Import failed for row', [
                'row' => $row,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    public function rules(): array
    {
        return [
            'username' => 'required|string|max:255|unique:pppoe_users,username',
        ];
    }
}
