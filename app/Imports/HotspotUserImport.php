<?php

namespace App\Imports;

use App\Models\ISP\HotspotUser;
use App\Models\ISP\Router;
use App\Models\ISP\ServiceProfile;
use App\Models\User;
use App\Models\CRM\Customer;
use App\Models\Customer\CustomerService;
use App\Services\ISP\HotspotService;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class HotspotUserImport implements ToCollection, WithHeadingRow
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
        $hotspotService = app(HotspotService::class);

        // Cek jika operator yang login adalah Reseller
        $enforcedResellerId = null;
        if (method_exists($this->user, 'getEffectiveResellerId')) {
            $enforcedResellerId = $this->user->getEffectiveResellerId();
        }

        foreach ($rows as $index => $row) {
            $username = $cleanStr($row['username'] ?? $row['login'] ?? $row['user'] ?? $row['user_id'] ?? $row['kode'] ?? null);
            $packageName = $cleanStr($row['service_profile'] ?? $row['paket_langganan'] ?? $row['paket'] ?? $row['profile'] ?? $row['plan'] ?? null);

            // Lewati baris kosong
            if (empty($username) && empty($packageName)) {
                $skippedCount++;
                continue;
            }

            if (empty($username)) {
                $errorMessages[] = "Baris " . ($index + 2) . ": Username wajib diisi.";
                continue;
            }

            if (empty($packageName)) {
                $errorMessages[] = "Baris " . ($index + 2) . " ($username): Service Profile / Paket wajib diisi.";
                continue;
            }

            // Cek duplikasi username
            if (HotspotUser::where('username', $username)->exists()) {
                $errorMessages[] = "Baris " . ($index + 2) . " ($username): Username sudah terdaftar di sistem.";
                continue;
            }

            try {
                $serviceProfile = ServiceProfile::where('name', $packageName)->first();
                if (!$serviceProfile) {
                    throw new \Exception("Paket '$packageName' tidak ditemukan di sistem.");
                }

                $routerName = $cleanStr($row['router'] ?? $row['nas'] ?? $row['mikrotik'] ?? $row['router_nas'] ?? null);
                $router = $routerName ? Router::where('name', $routerName)->first() : null;

                $resellerId = $enforcedResellerId;
                if (!$resellerId) {
                    $resellerName = $cleanStr($row['reseller'] ?? $row['owner'] ?? $row['mitra'] ?? null);
                    if ($resellerName) {
                        $reseller = User::where('name', $resellerName)->first();
                        $resellerId = $reseller?->id;
                    }
                }

                $customerName = $cleanStr($row['customer'] ?? $row['nama_pelanggan'] ?? $row['name'] ?? $row['nama'] ?? null);
                $phone = $cleanStr($row['phone'] ?? $row['no_hp'] ?? $row['whatsapp'] ?? null);
                $password = $cleanStr($row['password'] ?? $row['pass'] ?? $row['sandi'] ?? null) ?? Str::random(8);
                $macAddress = $cleanStr($row['mac_address'] ?? $row['mac'] ?? $row['macaddress'] ?? null);
                $staticIp = $cleanStr($row['static_ip'] ?? $row['ip'] ?? $row['ip_address'] ?? null);
                $rawStatus = strtolower($cleanStr($row['status'] ?? 'active') ?? 'active');
                $status = in_array($rawStatus, ['active', 'inactive', 'suspended', 'pending']) ? $rawStatus : 'active';

                DB::transaction(function () use (
                    $username,
                    $password,
                    $serviceProfile,
                    $router,
                    $resellerId,
                    $customerName,
                    $phone,
                    $macAddress,
                    $staticIp,
                    $status
                ) {
                    $customerServiceId = null;

                    // Hubungkan atau buat customer jika nama pelanggan diisi
                    if ($customerName) {
                        $customer = Customer::where('name', $customerName)
                            ->when($resellerId, fn($q) => $q->where('reseller_id', $resellerId))
                            ->first();

                        if (!$customer) {
                            $customer = Customer::create([
                                'uuid' => (string) Str::uuid(),
                                'code' => \App\Services\CRM\CustomerCodeGenerator::generate(),
                                'name' => $customerName,
                                'phone' => $phone ?? '0000000000',
                                'status' => $status === 'active' ? 'active' : 'inactive',
                                'reseller_id' => $resellerId,
                                'created_by' => $this->user->id,
                                'updated_by' => $this->user->id,
                            ]);
                        }

                        $cs = CustomerService::create([
                            'uuid' => (string) Str::uuid(),
                            'customer_id' => $customer->id,
                            'service_profile_id' => $serviceProfile->id,
                            'username' => $username,
                            'password' => $password,
                            'reseller_id' => $resellerId,
                            'status' => $status === 'active' ? 'active' : 'inactive',
                            'created_by' => $this->user->id,
                            'updated_by' => $this->user->id,
                        ]);

                        $customerServiceId = $cs->id;
                    }

                    HotspotUser::create([
                        'uuid' => (string) Str::uuid(),
                        'username' => $username,
                        'password' => $password,
                        'customer_service_id' => $customerServiceId,
                        'service_profile_id' => $serviceProfile->id,
                        'router_id' => $router?->id,
                        'reseller_id' => $resellerId,
                        'mac_address' => $macAddress,
                        'static_ip' => $staticIp,
                        'status' => $status,
                        'activated_at' => $status === 'active' ? now() : null,
                        'created_by' => $this->user->id,
                        'updated_by' => $this->user->id,
                    ]);
                });

                $successCount++;
            } catch (\Exception $e) {
                Log::error('Import failed for hotspot user row', [
                    'username' => $username,
                    'error' => $e->getMessage(),
                ]);
                $errorMessages[] = "Baris " . ($index + 2) . " ($username): " . $e->getMessage();
            }
        }

        if ($successCount === 0) {
            $msg = "Gagal mengimpor data Hotspot User! Tidak ada baris yang valid.";
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
            "$successCount Hotspot User berhasil diimpor." .
            (count($errorMessages) > 0 ? " Beberapa baris gagal: " . implode('; ', array_slice($errorMessages, 0, 3)) : "")
        );
    }
}
