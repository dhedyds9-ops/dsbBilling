<?php

namespace App\Imports;

use App\Models\ISP\Odc;
use App\Services\ISP\OdcService;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;

class OdcImport implements ToModel, WithHeadingRow, WithValidation
{
    protected $user;
    
    public function __construct($user)
    {
        $this->user = $user;
    }

    public function model(array $row)
    {
        $service = app(OdcService::class);
        
        $data = [
            'olt_id' => $row['olt_id'] ?? $row['olt'] ?? null,
            'pop_id' => $row['pop_id'] ?? $row['pop'] ?? null,
            'code' => $row['kode_odc'] ?? $row['code'] ?? null,
            'name' => $row['nama_odc'] ?? $row['name'] ?? null,
            'description' => $row['deskripsi'] ?? $row['description'] ?? null,
            'address' => $row['alamat'] ?? $row['address'] ?? null,
            'latitude' => $row['latitude'] ?? null,
            'longitude' => $row['longitude'] ?? null,
            'port_count' => $row['jumlah_port'] ?? $row['port_count'] ?? 0,
            'active_port_count' => $row['jumlah_port_aktif'] ?? $row['active_port_count'] ?? 0,
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
            'kode_odc' => 'required_without:code|string|max:255|unique:odcs,code',
            'code' => 'required_without:kode_odc|string|max:255|unique:odcs,code',
            'nama_odc' => 'required_without:name|string|max:255',
            'name' => 'required_without:nama_odc|string|max:255',
        ];
    }
}
