<?php

namespace App\Imports;

use App\Models\ISP\Olt;
use App\Services\ISP\OltService;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;

class OltImport implements ToModel, WithHeadingRow, WithValidation
{
    protected $user;
    
    public function __construct($user)
    {
        $this->user = $user;
    }

    public function model(array $row)
    {
        $service = app(OltService::class);
        
        $data = [
            'code' => $row['kode_olt'] ?? $row['code'] ?? null,
            'name' => $row['nama_olt'] ?? $row['name'] ?? null,
            'description' => $row['deskripsi'] ?? $row['description'] ?? null,
            'model' => $row['model'] ?? null,
            'serial_number' => $row['serial_number'] ?? null,
            'ip_address' => $row['ip_address'] ?? $row['ip'] ?? null,
            'port_count' => $row['port_count'] ?? null,
            'active_port_count' => $row['active_port_count'] ?? null,
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
            'kode_olt' => 'required_without:code|string|max:255|unique:olts,code',
            'code' => 'required_without:kode_olt|string|max:255|unique:olts,code',
            'nama_olt' => 'required_without:name|string|max:255',
            'name' => 'required_without:nama_olt|string|max:255',
            'ip_address' => 'nullable|string',
        ];
    }
}
