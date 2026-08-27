<?php

namespace App\Imports;

use App\Models\ISP\Voucher;
use App\Services\ISP\VoucherService;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;

class VoucherImport implements ToModel, WithHeadingRow, WithValidation
{
    protected $user;
    
    public function __construct($user)
    {
        $this->user = $user;
    }

    public function model(array $row)
    {
        $service = app(VoucherService::class);
        
        $data = [
            'code' => $row['kode_voucher'] ?? $row['code'] ?? null,
            'voucher_pool_id' => $row['voucher_pool_id'] ?? null,
            'service_profile_id' => $row['service_profile_id'] ?? null,
            'nas_device_id' => $row['nas_device_id'] ?? null,
            'owner_id' => $row['owner_id'] ?? null,
            'status' => $row['status'] ?? 'available',
            'type' => $row['type'] ?? null,
            'fee_seller' => $row['fee_seller'] ?? null,
            'validity_days' => $row['validity_days'] ?? null,
            'notes' => $row['notes'] ?? null,
        ];
        
        try {
            return $service->create($data, $this->user);
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
            'kode_voucher' => 'required_without:code|string|max:255|unique:vouchers,code',
            'code' => 'required_without:kode_voucher|string|max:255|unique:vouchers,code',
        ];
    }
}
