<?php

namespace App\Imports;

use App\Models\ISP\Onu;
use App\Services\ISP\OnuService;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;

class OnuImport implements ToModel, WithHeadingRow, WithValidation
{
    protected $user;
    
    public function __construct($user)
    {
        $this->user = $user;
    }

    public function model(array $row)
    {
        $service = app(OnuService::class);
        
        $data = [
            'code' => $row['kode_onu'] ?? $row['code'] ?? null,
            'name' => $row['nama_onu'] ?? $row['name'] ?? null,
            'description' => $row['deskripsi'] ?? $row['description'] ?? null,
            'model' => $row['model'] ?? null,
            'serial_number' => $row['serial_number'] ?? null,
            'mac_address' => $row['mac_address'] ?? null,
            'pon_port' => $row['pon_port'] ?? null,
            'status' => $row['status'] ?? 'active',
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
            'kode_onu' => 'required_without:code|string|max:255|unique:onus,code',
            'code' => 'required_without:kode_onu|string|max:255|unique:onus,code',
            'nama_onu' => 'required_without:name|string|max:255',
            'name' => 'required_without:nama_onu|string|max:255',
            'mac_address' => 'nullable|string|max:255',
        ];
    }
}
