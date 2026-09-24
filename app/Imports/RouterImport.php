<?php

namespace App\Imports;

use App\Models\ISP\Router;
use App\Services\ISP\RouterService;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;

class RouterImport implements ToModel, WithHeadingRow, WithValidation
{
    protected $user;
    
    public function __construct($user)
    {
        $this->user = $user;
    }

    public function model(array $row)
    {
        $service = app(RouterService::class);
        
        $data = [
            'code' => $row['kode_router'] ?? $row['code'] ?? null,
            'name' => $row['nama_router'] ?? $row['name'] ?? null,
            'description' => $row['deskripsi'] ?? $row['description'] ?? null,
            'model' => $row['model'] ?? null,
            'serial_number' => $row['serial_number'] ?? null,
            'ip_address' => $row['ip_address'] ?? $row['ip'] ?? null,
            'api_port' => $row['api_port'] ?? 8728,
            'use_ssl' => isset($row['use_ssl']) || isset($row['pakai_ssl']) ? (bool)($row['use_ssl'] ?? $row['pakai_ssl']) : false,
            'timeout' => $row['timeout'] ?? 30,
            'username' => $row['username'] ?? null,
            'password' => $row['password'] ?? null,
            'routeros_version' => $row['routeros_version'] ?? null,
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
            'kode_router' => 'required_without:code|string|max:255|unique:routers,code',
            'code' => 'required_without:kode_router|string|max:255|unique:routers,code',
            'nama_router' => 'required_without:name|string|max:255',
            'name' => 'required_without:nama_router|string|max:255',
            'ip_address' => 'nullable|string',
        ];
    }
}
