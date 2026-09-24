<?php

namespace App\Imports;

use App\Models\ISP\Tower;
use App\Services\ISP\TowerService;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;

class TowerImport implements ToModel, WithHeadingRow, WithValidation
{
    protected $user;

    public function __construct($user)
    {
        $this->user = $user;
    }

    public function model(array $row)
    {
        $service = app(TowerService::class);

        $data = [
            'code' => $row['kode_tower'] ?? $row['code'] ?? null,
            'name' => $row['nama_tower'] ?? $row['name'] ?? null,
            'description' => $row['deskripsi'] ?? $row['description'] ?? null,
            'address' => $row['alamat'] ?? $row['address'] ?? null,
            'province' => $row['provinsi'] ?? $row['province'] ?? null,
            'city' => $row['kota'] ?? $row['city'] ?? null,
            'district' => $row['kecamatan'] ?? $row['district'] ?? null,
            'village' => $row['desa'] ?? $row['village'] ?? null,
            'latitude' => $row['latitude'] ?? null,
            'longitude' => $row['longitude'] ?? null,
            'height' => $row['tinggi'] ?? $row['height'] ?? null,
            'type' => $row['tipe'] ?? $row['type'] ?? null,
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
            'kode_tower' => 'required_without:code|string|max:255|unique:towers,code',
            'code' => 'required_without:kode_tower|string|max:255|unique:towers,code',
            'nama_tower' => 'required_without:name|string|max:255',
            'name' => 'required_without:nama_tower|string|max:255',
        ];
    }
}