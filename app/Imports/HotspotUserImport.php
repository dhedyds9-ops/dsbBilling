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
            if ($val === null) return null;
            $trimmed = trim((string)$val);
            $cleaned = ltrim($trimmed, "`'");
            return in_array($cleaned, ['', '-'], true) ? null : $cleaned;
        };

        $successCount = 0;
        $updatedCount = 0;
        $skippedCount = 0;
        $errorMessages = [];
        $hotspotService = app(HotspotService::class);

        // Cek jika operator yang login adalah Reseller
        $enforcedResellerId = null;
        if (method_exists($this->user, 'getEffectiveResellerId')) {
            $enforcedResellerId = $this->user->getEffectiveResellerId();
        }

        foreach ($rows as $index => $row) {
            // 1. Buat peta kolom ternormalisasi (lowercase, tanpa spasi, tanpa simbol)
            $norm = [];
            foreach ($row as $k => $v) {
                $cleanK = strtolower(preg_replace('/[^a-zA-Z0-9]/', '', (string)$k));
                if ($cleanK !== '') {
                    $norm[$cleanK] = $cleanStr($v);
                }
            }

            // 2. Deteksi Username Hotspot
            $username = $norm['username'] 
                ?? $norm['login'] 
                ?? $norm['user'] 
                ?? $norm['userid'] 
                ?? $norm['user_id'] ?? null
                ?? $norm['kode'] 
                ?? $norm['account'] 
                ?? null;

            // Jika belum ketemu, cek kolom 'name' (standar ekspor MikroTik)
            if (empty($username) && !empty($norm['name'])) {
                $username = $norm['name'];
            }

            // 3. Deteksi Paket / Service Profile
            $packageName = $norm['serviceprofile'] 
                ?? $norm['paketlangganan'] 
                ?? $norm['paket'] 
                ?? $norm['profile'] 
                ?? $norm['plan'] 
                ?? $norm['groupname'] // MSRadius radusergroup
                ?? $norm['group'] 
                ?? $norm['profilename'] 
                ?? $norm['namapaket'] 
                ?? null;

            // Lewati baris kosong
            if (empty($username) && empty($packageName)) {
                $skippedCount++;
                continue;
            }

            if (empty($username)) {
                $errorMessages[] = "Baris " . ($index + 2) . ": Username wajib diisi.";
                continue;
            }

            try {
                // 4. Pencarian Paket dengan Toleransi Format (Fuzzy & Case-Insensitive)
                $serviceProfile = null;
                if (!empty($packageName)) {
                    $serviceProfile = ServiceProfile::where('name', $packageName)->first();

                    if (!$serviceProfile) {
                        $serviceProfile = ServiceProfile::whereRaw('LOWER(TRIM(name)) = ?', [strtolower(trim($packageName))])->first();
                    }

                    if (!$serviceProfile) {
                        $strippedSearch = strtolower(preg_replace('/[^a-zA-Z0-9]/', '', $packageName));
                        $serviceProfile = ServiceProfile::all()->first(function($sp) use ($strippedSearch) {
                            return strtolower(preg_replace('/[^a-zA-Z0-9]/', '', $sp->name)) === $strippedSearch;
                        });
                    }
                }

                // Fallback jika tidak ditemukan: gunakan profil hotspot aktif pertama
                if (!$serviceProfile) {
                    $serviceProfile = ServiceProfile::where('service_type', 'hotspot')->first() 
                        ?? ServiceProfile::first();
                }

                if (!$serviceProfile) {
                    throw new \Exception("Paket '$packageName' tidak ditemukan di sistem.");
                }

                // 5. Deteksi Router / NAS
                $routerName = $norm['router'] 
                    ?? $norm['nas'] 
                    ?? $norm['mikrotik'] 
                    ?? $norm['mikrotiknas'] 
                    ?? $norm['routernas'] 
                    ?? $norm['nasname'] 
                    ?? null;

                $router = null;
                if (!empty($routerName)) {
                    $router = Router::withoutGlobalScopes()->where('name', $routerName)->first()
                        ?? Router::withoutGlobalScopes()->whereRaw('LOWER(TRIM(name)) = ?', [strtolower(trim($routerName))])->first();
                }

                // 6. Deteksi Reseller
                $resellerId = $enforcedResellerId;
                if (!$resellerId) {
                    $resellerName = $norm['reseller'] 
                        ?? $norm['mitra'] 
                        ?? $norm['owner'] 
                        ?? $norm['resellername'] 
                        ?? null;

                    if ($resellerName) {
                        $reseller = User::withoutGlobalScopes()->where('name', $resellerName)->first()
                            ?? User::withoutGlobalScopes()->where('email', $resellerName)->first();
                        $resellerId = $reseller?->id;
                    }
                }

                // 7. Data Pelanggan & Kredensial
                $customerName = $norm['customer'] 
                    ?? $norm['customername'] 
                    ?? $norm['namapelanggan'] 
                    ?? $norm['fullname'] 
                    ?? $norm['nama'] 
                    ?? $norm['comment'] 
                    ?? null;

                if (empty($customerName) && !empty($norm['name']) && $username !== $norm['name']) {
                    $customerName = $norm['name'];
                }

                if (empty($customerName)) {
                    $customerName = 'Pelanggan ' . ucwords(str_replace(['.', '_', '-'], ' ', $username));
                }

                $rawPhone = $norm['phone'] 
                    ?? $norm['nohp'] 
                    ?? $norm['whatsapp'] 
                    ?? $norm['notelp'] 
                    ?? $norm['telp'] 
                    ?? null;

                $password = $norm['password'] 
                    ?? $norm['pass'] 
                    ?? $norm['sandi'] 
                    ?? $norm['katasandi'] 
                    ?? $norm['value'] 
                    ?? null;
                $password = $password ?: Str::random(8);

                $macAddress = $norm['macaddress'] 
                    ?? $norm['mac'] 
                    ?? $norm['callerid'] 
                    ?? null;

                $staticIp = $norm['staticip'] 
                    ?? $norm['ip'] 
                    ?? $norm['ipaddress'] 
                    ?? null;

                $rawStatus = strtolower($norm['status'] ?? $norm['state'] ?? 'active');
                $status = in_array($rawStatus, ['active', 'inactive', 'suspended', 'pending'], true) ? $rawStatus : 'active';

                $isUpdate = false;

                DB::transaction(function () use (
                    $username,
                    $password,
                    $serviceProfile,
                    $router,
                    $resellerId,
                    $customerName,
                    $rawPhone,
                    $macAddress,
                    $staticIp,
                    $status,
                    $index,
                    &$isUpdate
                ) {
                    $customerServiceId = null;

                    // 8. Penanganan Pelanggan & Nomor HP
                    $existingCustomer = null;
                    if (!empty($rawPhone) && !in_array($rawPhone, ['0', '0000', '0000000000'], true)) {
                        $cleanPhone = preg_replace('/[^0-9]/', '', $rawPhone);
                        $p0 = str_starts_with($cleanPhone, '62') ? ('0' . substr($cleanPhone, 2)) : $cleanPhone;
                        $p62 = str_starts_with($cleanPhone, '0') ? ('62' . substr($cleanPhone, 1)) : $cleanPhone;

                        $existingCustomer = Customer::withoutGlobalScopes()
                            ->when($resellerId, fn($q) => $q->where('reseller_id', $resellerId))
                            ->where(function($q) use ($rawPhone, $p0, $p62) {
                                $q->where('phone', $rawPhone)
                                  ->orWhere('phone', $p0)
                                  ->orWhere('phone', $p62);
                            })
                            ->first();
                    }

                    if (!$existingCustomer && !empty($customerName)) {
                        $existingCustomer = Customer::withoutGlobalScopes()
                            ->when($resellerId, fn($q) => $q->where('reseller_id', $resellerId))
                            ->where('name', $customerName)
                            ->first();
                    }

                    if (empty($rawPhone) || in_array($rawPhone, ['0', '0000', '0000000000'], true)) {
                        $phoneToUse = '0899' . str_pad((string)($index + 1), 5, '0', STR_PAD_LEFT) . mt_rand(100, 999);
                    } else {
                        $phoneToUse = $rawPhone;
                    }

                    $customer = $existingCustomer;
                    if (!$customer) {
                        $customer = Customer::create([
                            'code' => \App\Services\CRM\CustomerCodeGenerator::generate(),
                            'name' => $customerName,
                            'phone' => $phoneToUse,
                            'status' => $status === 'active' ? 'active' : 'inactive',
                            'reseller_id' => $resellerId,
                            'created_by' => $this->user->id,
                            'updated_by' => $this->user->id,
                        ]);
                    }

                    $cs = CustomerService::withoutGlobalScopes()->withTrashed()->where('username', $username)->first();
                    if ($cs) {
                        if ($cs->trashed()) {
                            $cs->restore();
                        }
                        $cs->update([
                            'customer_id' => $customer->id,
                            'service_profile_id' => $serviceProfile->id,
                            'password' => $password,
                            'reseller_id' => $resellerId ?? $cs->reseller_id,
                            'status' => $status === 'active' ? 'active' : $cs->status,
                            'updated_by' => $this->user->id,
                        ]);
                    } else {
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
                    }

                    $customerServiceId = $cs->id;

                    // 9. Simpan atau Perbarui Hotspot User
                    $existingHotspotUser = HotspotUser::withoutGlobalScopes()->withTrashed()->where('username', $username)->first();
                    if ($existingHotspotUser) {
                        $isUpdate = true;
                        if ($existingHotspotUser->trashed()) {
                            $existingHotspotUser->restore();
                        }
                        $existingHotspotUser->update([
                            'password' => $password,
                            'customer_service_id' => $customerServiceId,
                            'service_profile_id' => $serviceProfile->id,
                            'router_id' => $router?->id ?? $existingHotspotUser->router_id,
                            'reseller_id' => $resellerId ?? $existingHotspotUser->reseller_id,
                            'mac_address' => $macAddress ?? $existingHotspotUser->mac_address,
                            'static_ip' => $staticIp ?? $existingHotspotUser->static_ip,
                            'status' => $status,
                            'activated_at' => $status === 'active' ? ($existingHotspotUser->activated_at ?? now()) : null,
                            'updated_by' => $this->user->id,
                        ]);
                    } else {
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
                    }
                });

                $successCount++;
                if ($isUpdate) {
                    $updatedCount++;
                }

            } catch (\Throwable $e) {
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

        $infoMsg = "$successCount Hotspot User berhasil diproses" . ($updatedCount > 0 ? " ($updatedCount diperbarui)" : "") . ".";
        if (count($errorMessages) > 0) {
            $infoMsg .= " Beberapa baris dilewati: " . implode('; ', array_slice($errorMessages, 0, 3));
        }

        session()->flash('import_result', $infoMsg);
    }
}
