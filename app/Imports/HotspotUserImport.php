<?php

namespace App\Imports;

use App\Models\ISP\HotspotUser;
use App\Models\ISP\ServiceProfile;
use App\Services\ISP\HotspotService;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;

class HotspotUserImport implements ToModel, WithHeadingRow, WithValidation
{
    protected $user;
    
    public function __construct($user)
    {
        $this->user = $user;
    }

    public function model(array $row)
    {
        $service = app(HotspotService::class);
        
        // Find service profile
        $serviceProfile = ServiceProfile::where('name', $row['service_profile'] ?? $row['paket_langganan'] ?? null)->first();
        if (!$serviceProfile) {
            throw new \Exception('Service profile not found: ' . ($row['service_profile'] ?? $row['paket_langganan'] ?? 'empty'));
        }

        try {
            return $service->createHotspotUser(
                customerService: null,
                serviceProfileId: $serviceProfile->id,
                voucherPoolId: null,
                userId: $this->user->id,
                username: $row['username'] ?? null,
                password: $row['password'] ?? null
            );
        } catch (\Exception $e) {
            Log::error('Import failed for hotspot user row', [
                'row' => $row,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    public function rules(): array
    {
        return [
            'username' => 'required|string|max:255|unique:hotspot_users,username',
            'service_profile' => 'required|string',
        ];
    }
}
