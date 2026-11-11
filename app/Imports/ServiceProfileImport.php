<?php

namespace App\Imports;

use App\Models\ISP\ServiceProfile;
use App\Services\ISP\ServiceProfileService;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class ServiceProfileImport implements ToCollection, WithHeadingRow
{
    protected $user;
    
    public function __construct($user)
    {
        $this->user = $user;
    }

    public function collection(Collection $rows)
    {
        $cleanStr = function($val) {
            if (!$val) return null;
            $trimmed = trim((string)$val);
            $cleaned = ltrim($trimmed, "`'");
            return $cleaned === '' || $cleaned === '-' ? null : $cleaned;
        };

        $service = app(ServiceProfileService::class);
        $successCount = 0;
        $skippedCount = 0;
        $errorMessages = [];

        foreach ($rows as $index => $row) {
            $name = $cleanStr($row['nama_paket'] ?? $row['name'] ?? $row['paket'] ?? $row['profile'] ?? null);

            // Lewati baris kosong
            if (empty($name)) {
                $skippedCount++;
                continue;
            }

            if (ServiceProfile::where('name', $name)->exists()) {
                $errorMessages[] = "Baris " . ($index + 2) . " ($name): Nama paket sudah ada.";
                continue;
            }

            try {
                $serviceType = strtolower($cleanStr($row['jenis_layanan'] ?? $row['service_type'] ?? 'pppoe') ?? 'pppoe');
                if (!in_array($serviceType, ['pppoe', 'hotspot', 'dedicated', 'ipoe'])) {
                    $serviceType = 'pppoe';
                }

                $packageType = strtolower($cleanStr($row['tipe_paket'] ?? $row['package_type'] ?? 'unlimited') ?? 'unlimited');
                if (!in_array($packageType, ['unlimited', 'quota', 'time', 'prepaid'])) {
                    $packageType = 'unlimited';
                }

                $data = [
                    'name' => $name,
                    'description' => $cleanStr($row['deskripsi'] ?? $row['description'] ?? null),
                    'service_type' => $serviceType,
                    'package_type' => $packageType,
                    'download_speed' => $this->parseSpeed($row['download_speed'] ?? $row['download'] ?? 10),
                    'upload_speed' => $this->parseSpeed($row['upload_speed'] ?? $row['upload'] ?? 10),
                    'base_price' => $this->parseCurrency($row['harga_dasar'] ?? $row['base_price'] ?? 0),
                    'owner_price' => $this->parseCurrency($row['harga_owner'] ?? $row['owner_price'] ?? 0),
                    'reseller_price' => $this->parseCurrency($row['harga_reseller'] ?? $row['reseller_price'] ?? 0),
                    'is_free' => isset($row['gratis']) || isset($row['is_free']) ? (bool)($row['gratis'] ?? $row['is_free']) : false,
                    'validity_value' => (int)($row['masa_aktif'] ?? $row['validity_value'] ?? 30),
                    'validity_unit' => $cleanStr($row['satuan_masa_aktif'] ?? $row['validity_unit'] ?? 'days') ?? 'days',
                    'max_devices' => (int)($row['shared_user'] ?? $row['max_devices'] ?? 1),
                    'status' => in_array(strtolower($cleanStr($row['status'] ?? 'active') ?? 'active'), ['active', 'inactive']) ? strtolower($cleanStr($row['status'])) : 'active',
                ];

                $service->createProfile($data, $this->user);
                $successCount++;
            } catch (\Exception $e) {
                Log::error('Import failed for service profile row', [
                    'row' => $row,
                    'error' => $e->getMessage()
                ]);
                $errorMessages[] = "Baris " . ($index + 2) . " ($name): " . $e->getMessage();
            }
        }

        if ($successCount === 0) {
            $msg = "Gagal mengimpor Paket Internet! Tidak ada baris yang valid.";
            if ($skippedCount > 0) {
                $msg .= " Sistem melewatkan $skippedCount baris kosong.";
            }
            if (count($errorMessages) > 0) {
                $msg .= " Error: " . implode('; ', array_slice($errorMessages, 0, 3));
            }
            throw new \Exception($msg);
        }

        session()->flash(
            'import_result',
            "$successCount Paket Internet berhasil diimpor." .
            (count($errorMessages) > 0 ? " Beberapa baris gagal: " . implode('; ', array_slice($errorMessages, 0, 3)) : "")
        );
    }
    
    protected function parseCurrency($value)
    {
        if (is_numeric($value)) {
            return (float)$value;
        }
        
        $value = preg_replace('/[^\d.,]/', '', (string)$value);
        $value = str_replace(',', '.', $value);
        
        return (float)$value;
    }

    protected function parseSpeed($value)
    {
        if (is_numeric($value)) {
            return (int)$value;
        }

        $numeric = (int)preg_replace('/[^\d]/', '', (string)$value);
        return $numeric > 0 ? $numeric : 10;
    }
}
