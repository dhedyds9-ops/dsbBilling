<?php

namespace App\Imports;

use App\Models\ISP\Odp;
use App\Services\ISP\OdpService;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;

class OdpImport implements ToModel, WithHeadingRow, WithValidation
{
    protected $user;
    
    public function __construct($user)
    {
        $this->user = $user;
    }

    public function model(array $row)
    {
        $service = app(OdpService::class);
        
        $data = [
            'odc_id' => $row['odc_id'] ?? $row['odc'] ?? null,
            'code' => $row['kode_odp'] ?? $row['kode'] ?? $row['code'] ?? null,
            'name' => $row['nama_odp'] ?? $row['nama'] ?? $row['name'] ?? null,
            'description' => $row['deskripsi'] ?? $row['description'] ?? null,
            'address' => $row['alamat'] ?? $row['address'] ?? null,
            'latitude' => $row['latitude'] ?? null,
            'longitude' => $row['longitude'] ?? null,
            'port_count' => $row['jumlah_port'] ?? $row['port_count'] ?? null,
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
            'kode_odp' => 'required_without:kode,code|string|max:255|unique:odps,code',
            'kode' => 'required_without:kode_odp,code|string|max:255|unique:odps,code',
            'code' => 'required_without:kode_odp,kode|string|max:255|unique:odps,code',
            'nama_odp' => 'required_without:nama,name|string|max:255',
            'nama' => 'required_without:nama_odp,name|string|max:255',
            'name' => 'required_without:nama_odp,nama|string|max:255',
        ];
    }
}
