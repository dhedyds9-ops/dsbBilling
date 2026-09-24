<?php

namespace App\Imports;

use App\Models\ISP\Vendor;
use App\Services\ISP\VendorService;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;

class VendorImport implements ToModel, WithHeadingRow, WithValidation
{
    protected $user;
    
    public function __construct($user)
    {
        $this->user = $user;
    }

    public function model(array $row)
    {
        $service = app(VendorService::class);
        
        $data = [
            'code' => $row['kode_vendor'] ?? $row['code'] ?? null,
            'name' => $row['nama_vendor'] ?? $row['name'] ?? null,
            'description' => $row['deskripsi'] ?? $row['description'] ?? null,
            'phone' => $row['telepon'] ?? $row['phone'] ?? null,
            'email' => $row['email'] ?? null,
            'address' => $row['alamat'] ?? $row['address'] ?? null,
            'contact_person' => $row['kontak_person'] ?? $row['contact_person'] ?? null,
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
            'kode_vendor' => 'required_without:code|string|max:255|unique:vendors,code',
            'code' => 'required_without:kode_vendor|string|max:255|unique:vendors,code',
            'nama_vendor' => 'required_without:name|string|max:255',
            'name' => 'required_without:nama_vendor|string|max:255',
        ];
    }
}
