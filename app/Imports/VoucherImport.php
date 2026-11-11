<?php

namespace App\Imports;

use App\Models\ISP\NasDevice;
use App\Models\ISP\Router;
use App\Models\ISP\ServiceProfile;
use App\Models\ISP\Voucher;
use App\Models\ISP\VoucherPool;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class VoucherImport implements ToCollection, WithHeadingRow
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

        $successCount = 0;
        $skippedCount = 0;
        $errorMessages = [];

        // Cek apakah user yang login adalah Reseller
        $enforcedResellerId = null;
        if (method_exists($this->user, 'getEffectiveResellerId')) {
            $enforcedResellerId = $this->user->getEffectiveResellerId();
        }

        foreach ($rows as $index => $row) {
            $code = $cleanStr($row['kode_voucher'] ?? $row['code'] ?? $row['voucher'] ?? $row['kode'] ?? null);
            $packageName = $cleanStr($row['service_profile'] ?? $row['paket'] ?? $row['profile'] ?? $row['paket_voucher'] ?? null);
            $poolName = $cleanStr($row['voucher_pool'] ?? $row['pool'] ?? $row['batch'] ?? $row['batch_voucher'] ?? null);

            // Lewati baris kosong
            if (empty($code) && empty($packageName) && empty($poolName)) {
                $skippedCount++;
                continue;
            }

            if (empty($code)) {
                $errorMessages[] = "Baris " . ($index + 2) . ": Kode Voucher wajib diisi.";
                continue;
            }

            // Cek duplikasi kode
            if (Voucher::where('code', $code)->exists()) {
                $errorMessages[] = "Baris " . ($index + 2) . " ($code): Kode voucher sudah ada di sistem.";
                continue;
            }

            try {
                // Resolusi Service Profile
                $serviceProfile = null;
                if (!empty($row['service_profile_id']) && is_numeric($row['service_profile_id'])) {
                    $serviceProfile = ServiceProfile::find($row['service_profile_id']);
                } elseif ($packageName) {
                    $serviceProfile = ServiceProfile::where('name', $packageName)->first();
                }

                if (!$serviceProfile && $packageName) {
                    throw new \Exception("Paket '$packageName' tidak ditemukan di sistem.");
                }

                // Resolusi Voucher Pool
                $voucherPool = null;
                if (!empty($row['voucher_pool_id']) && is_numeric($row['voucher_pool_id'])) {
                    $voucherPool = VoucherPool::find($row['voucher_pool_id']);
                } elseif ($poolName) {
                    $voucherPool = VoucherPool::where('name', $poolName)->first();
                    if (!$voucherPool) {
                        // Otomatis buat pool baru jika belum ada
                        $voucherPool = VoucherPool::create([
                            'uuid' => (string) Str::uuid(),
                            'name' => $poolName,
                            'service_profile_id' => $serviceProfile?->id ?? 1,
                            'total_vouchers' => 0,
                            'used_vouchers' => 0,
                            'active_vouchers' => 0,
                            'status' => 'active',
                            'created_by' => $this->user->id,
                            'updated_by' => $this->user->id,
                        ]);
                    }
                }

                // Resolusi Router / NAS Device
                $nasDeviceId = null;
                $routerName = $cleanStr($row['router'] ?? $row['nas'] ?? $row['nas_device'] ?? $row['nas_device_id'] ?? null);
                if ($routerName) {
                    if (is_numeric($routerName)) {
                        $nasDeviceId = (int)$routerName;
                    } else {
                        $nas = NasDevice::where('name', $routerName)->first();
                        if ($nas) {
                            $nasDeviceId = $nas->id;
                        } else {
                            $router = Router::where('name', $routerName)->first();
                            $nasDeviceId = $router?->id;
                        }
                    }
                }

                // Resolusi Reseller
                $resellerId = $enforcedResellerId;
                if (!$resellerId) {
                    $resellerName = $cleanStr($row['reseller'] ?? $row['owner'] ?? $row['mitra'] ?? $row['reseller_id'] ?? null);
                    if ($resellerName) {
                        if (is_numeric($resellerName)) {
                            $resellerId = (int)$resellerName;
                        } else {
                            $reseller = User::where('name', $resellerName)->first();
                            $resellerId = $reseller?->id;
                        }
                    }
                }

                $validityDays = $cleanStr($row['validity_days'] ?? $row['masa_aktif'] ?? $row['durasi'] ?? null);
                $status = strtolower($cleanStr($row['status'] ?? 'available') ?? 'available');
                $notes = $cleanStr($row['notes'] ?? $row['catatan'] ?? $row['keterangan'] ?? null);

                DB::transaction(function () use (
                    $code,
                    $voucherPool,
                    $serviceProfile,
                    $nasDeviceId,
                    $resellerId,
                    $status,
                    $validityDays,
                    $notes
                ) {
                    Voucher::create([
                        'uuid' => (string) Str::uuid(),
                        'code' => $code,
                        'voucher_pool_id' => $voucherPool?->id,
                        'service_profile_id' => $serviceProfile?->id,
                        'nas_device_id' => $nasDeviceId,
                        'reseller_id' => $resellerId,
                        'status' => in_array($status, ['available', 'used', 'expired']) ? $status : 'available',
                        'validity_days' => is_numeric($validityDays) ? (int)$validityDays : null,
                        'notes' => $notes,
                        'created_by' => $this->user->id,
                        'updated_by' => $this->user->id,
                    ]);

                    if ($voucherPool) {
                        $voucherPool->increment('total_vouchers');
                    }
                });

                $successCount++;
            } catch (\Exception $e) {
                Log::error('Import failed for voucher row', [
                    'code' => $code,
                    'error' => $e->getMessage(),
                ]);
                $errorMessages[] = "Baris " . ($index + 2) . " ($code): " . $e->getMessage();
            }
        }

        if ($successCount === 0) {
            $msg = "Gagal mengimpor data Voucher! Tidak ada baris yang valid.";
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
            "$successCount Voucher berhasil diimpor." .
            (count($errorMessages) > 0 ? " Beberapa baris gagal: " . implode('; ', array_slice($errorMessages, 0, 3)) : "")
        );
    }
}
