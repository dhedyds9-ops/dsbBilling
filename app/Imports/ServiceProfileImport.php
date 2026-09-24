<?php

namespace App\Imports;

use App\Models\ISP\ServiceProfile;
use App\Services\ISP\ServiceProfileService;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;

class ServiceProfileImport implements ToModel, WithHeadingRow, WithValidation
{
    protected $user;
    
    public function __construct($user)
    {
        $this->user = $user;
    }

    public function model(array $row)
    {
        $service = app(ServiceProfileService::class);
        
        $data = [
            'name' => $row['nama_paket'] ?? $row['name'] ?? null,
            'description' => $row['deskripsi'] ?? $row['description'] ?? null,
            'service_type' => $row['jenis_layanan'] ?? $row['service_type'] ?? 'pppoe',
            'package_type' => $row['tipe_paket'] ?? $row['package_type'] ?? 'unlimited',
            'download_speed' => $row['download_speed'] ?? $row['download'] ?? 10,
            'upload_speed' => $row['upload_speed'] ?? $row['upload'] ?? 10,
            'base_price' => $this->parseCurrency($row['harga_dasar'] ?? $row['base_price'] ?? 0),
            'owner_price' => $this->parseCurrency($row['harga_owner'] ?? $row['owner_price'] ?? 0),
            'reseller_price' => $this->parseCurrency($row['harga_reseller'] ?? $row['reseller_price'] ?? 0),
            'is_free' => isset($row['gratis']) || isset($row['is_free']) ? (bool)($row['gratis'] ?? $row['is_free']) : false,
            'validity_value' => $row['masa_aktif'] ?? $row['validity_value'] ?? 30,
            'validity_unit' => $row['satuan_masa_aktif'] ?? $row['validity_unit'] ?? 'days',
            'max_devices' => $row['shared_user'] ?? $row['max_devices'] ?? 1,
            'status' => $row['status'] ?? 'active',
        ];
        
        try {
            return $service->createProfile($data, $this->user);
        } catch (\Exception $e) {
            Log::error('Import failed for row', [
                'row' => $row,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }
    
    protected function parseCurrency($value)
    {
        if (is_numeric($value)) {
            return (float)$value;
        }
        
        $value = preg_replace('/[^\d.,]/', '', $value);
        $value = str_replace(',', '.', $value);
        return (float)$value;
    }

    public function rules(): array
    {
        return [
            'nama_paket' => 'required_without:name|string|max:255',
            'name' => 'required_without:nama_paket|string|max:255',
            'jenis_layanan' => 'nullable|in:pppoe,hotspot,voucher',
            'service_type' => 'nullable|in:pppoe,hotspot,voucher',
            'download_speed' => 'nullable|integer|min:1',
            'upload_speed' => 'nullable|integer|min:1',
        ];
    }
}
