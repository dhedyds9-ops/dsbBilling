<?php

namespace App\Imports;

use App\Models\ISP\PPPoEUser;
use App\Models\ISP\ServiceProfile;
use App\Models\Customer\CustomerService;
use App\Models\CRM\Customer;
use App\Models\ISP\Router;
use App\Models\User;
use App\Services\Provisioning\ProvisioningService;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class PPPoEUserImport implements ToCollection, WithHeadingRow
{
    protected $user;
    protected $provisioningService;

    public function __construct($user)
    {
        $this->user = $user;
        $this->provisioningService = app(ProvisioningService::class);
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
        $skippedCount = 0;
        $updatedCount = 0;
        $errorMessages = [];

        // Deteksi jika user yang login adalah Reseller
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

            // 2. Deteksi Username PPPoE (mendukung standar ekspor MikroTik, MSRadius, & template)
            $username = $norm['username'] 
                ?? $norm['login'] 
                ?? $norm['user'] 
                ?? $norm['userid'] 
                ?? $norm['user_id'] ?? null
                ?? $norm['loginid'] 
                ?? $norm['account'] 
                ?? $norm['kode'] 
                ?? null;

            // Jika username belum ketemu, tapi ada kolom 'name' (format standar /ppp secret print MikroTik)
            if (empty($username) && !empty($norm['name'])) {
                $username = $norm['name'];
            }

            // 3. Deteksi Nama Pelanggan
            $namaPelanggan = $norm['namapelanggan'] 
                ?? $norm['fullname'] 
                ?? $norm['customer'] 
                ?? $norm['customername'] 
                ?? $norm['nama'] 
                ?? $norm['comment'] // MikroTik meletakkan nama pelanggan di comment
                ?? null;

            // Jika nama pelanggan masih kosong tapi kolom 'name' belum dipakai sebagai username
            if (empty($namaPelanggan) && !empty($norm['name']) && $username !== $norm['name']) {
                $namaPelanggan = $norm['name'];
            }

            // Lewati baris kosong
            if (empty($username) && empty($namaPelanggan)) {
                $skippedCount++;
                continue;
            }

            if (empty($username)) {
                $errorMessages[] = "Baris " . ($index + 2) . ": Kolom Username tidak ditemukan / kosong.";
                continue;
            }

            // Fallback nama pelanggan jika kosong di Excel: gunakan format rapi dari username
            if (empty($namaPelanggan)) {
                $namaPelanggan = ucwords(str_replace(['.', '_', '-'], ' ', $username));
            }

            try {
                // 4. Deteksi Paket Langganan / Service Profile
                $paketRaw = $norm['paketlangganan'] 
                    ?? $norm['paket'] 
                    ?? $norm['serviceprofile'] 
                    ?? $norm['profile'] 
                    ?? $norm['plan'] 
                    ?? $norm['groupname'] // MSRadius / FreeRADIUS radusergroup
                    ?? $norm['group'] 
                    ?? $norm['profilename'] 
                    ?? $norm['namapaket'] 
                    ?? $norm['service'] 
                    ?? null;

                $serviceProfile = null;
                if (!empty($paketRaw)) {
                    // Coba exact match
                    $serviceProfile = ServiceProfile::where('name', $paketRaw)->first();

                    // Coba case-insensitive dan trim
                    if (!$serviceProfile) {
                        $serviceProfile = ServiceProfile::whereRaw('LOWER(TRIM(name)) = ?', [strtolower(trim($paketRaw))])->first();
                    }

                    // Coba perbandingan tanpa spasi dan strip (misal: "20Mbps" vs "20 Mbps")
                    if (!$serviceProfile) {
                        $strippedSearch = strtolower(preg_replace('/[^a-zA-Z0-9]/', '', $paketRaw));
                        $serviceProfile = ServiceProfile::all()->first(function($sp) use ($strippedSearch) {
                            return strtolower(preg_replace('/[^a-zA-Z0-9]/', '', $sp->name)) === $strippedSearch;
                        });
                    }
                }

                // Fallback jika paket tidak ditemukan di sistem: gunakan paket PPPoE pertama yang aktif
                if (!$serviceProfile) {
                    $serviceProfile = ServiceProfile::where('service_type', 'pppoe')->first() 
                        ?? ServiceProfile::first();
                }

                // 5. Deteksi Router / NAS
                $routerName = $norm['router'] 
                    ?? $norm['mikrotiknas'] 
                    ?? $norm['nas'] 
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

                    if (!empty($resellerName) && $resellerName !== '-') {
                        $reseller = User::withoutGlobalScopes()->where('name', $resellerName)->first()
                            ?? User::withoutGlobalScopes()->where('email', $resellerName)->first();
                        $resellerId = $reseller?->id;
                    }
                }

                // 7. Data Teknis (MAC, Static IP, Password, Status)
                $mac = $norm['macaddress'] 
                    ?? $norm['mac'] 
                    ?? $norm['callerid'] 
                    ?? $norm['callingstationid'] 
                    ?? null;

                $ip = $norm['ipaddress'] 
                    ?? $norm['ip'] 
                    ?? $norm['staticip'] 
                    ?? $norm['framedip'] 
                    ?? $norm['framedipaddress'] 
                    ?? null;

                $password = $norm['password'] 
                    ?? $norm['pass'] 
                    ?? $norm['sandi'] 
                    ?? $norm['katasandi'] 
                    ?? $norm['value'] // MSRadius radcheck
                    ?? null;

                $email = $norm['email'] ?? null;
                if (empty($password) && !empty($email) && !str_contains($email, '@')) {
                    $password = $email;
                    $email = null;
                }
                $password = $password ?: Str::random(8);

                $statusRaw = $norm['status'] ?? $norm['state'] ?? 'active';
                $status = in_array(strtolower($statusRaw), ['active', 'inactive', 'suspended', 'pending'], true) ? strtolower($statusRaw) : 'active';

                // 8. Cek Duplikasi Username: Jika sudah ada, perbarui data secara aman (Upsert)
                $existingPPPoE = PPPoEUser::withoutGlobalScopes()->where('username', $username)->first();
                if ($existingPPPoE) {
                    $existingPPPoE->update([
                        'password' => $password,
                        'service_profile_id' => $serviceProfile?->id ?? $existingPPPoE->service_profile_id,
                        'router_id' => $router?->id ?? $existingPPPoE->router_id,
                        'reseller_id' => $resellerId ?? $existingPPPoE->reseller_id,
                        'mac_address' => $mac ?? $existingPPPoE->mac_address,
                        'static_ip' => $ip ?? $existingPPPoE->static_ip,
                        'status' => $status,
                        'updated_by' => $this->user->id,
                    ]);

                    if ($existingPPPoE->customerService) {
                        $existingPPPoE->customerService->update([
                            'password' => $password,
                            'service_profile_id' => $serviceProfile?->id ?? $existingPPPoE->customerService->service_profile_id,
                            'reseller_id' => $resellerId ?? $existingPPPoE->customerService->reseller_id,
                            'status' => $status,
                            'updated_by' => $this->user->id,
                        ]);
                    }

                    $successCount++;
                    $updatedCount++;
                    continue;
                }

                // 9. Penanganan Nomor Telepon & Pengikatan ke Pelanggan Eksisting
                $rawPhone = $norm['phone'] 
                    ?? $norm['whatsapp'] 
                    ?? $norm['nohp'] 
                    ?? $norm['notelp'] 
                    ?? $norm['telp'] 
                    ?? $norm['telepon'] 
                    ?? null;

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

                if (!$existingCustomer && !empty($namaPelanggan)) {
                    $existingCustomer = Customer::withoutGlobalScopes()
                        ->when($resellerId, fn($q) => $q->where('reseller_id', $resellerId))
                        ->where('name', $namaPelanggan)
                        ->first();
                }

                // Jika nomor HP kosong di Excel, buat dummy placeholder unik untuk mencegah bentrok validasi keunikan nomor HP
                if (empty($rawPhone) || in_array($rawPhone, ['0', '0000', '0000000000'], true)) {
                    $phoneToUse = '0899' . str_pad((string)($index + 1), 5, '0', STR_PAD_LEFT) . mt_rand(100, 999);
                } else {
                    $phoneToUse = $rawPhone;
                }

                // 10. Siapkan Payload Provisioning
                $data = [
                    'name' => $namaPelanggan,
                    'phone' => $phoneToUse,
                    'email' => $email,
                    'address' => $norm['address'] ?? $norm['alamat'] ?? null,
                    'username' => $username,
                    'password' => $password,
                    'service_profile_id' => $serviceProfile?->id,
                    'status' => $status,
                    'activation_date' => now()->format('Y-m-d'),
                    'notes' => 'Imported via Excel (Auto-Mapped)',
                    'router_id' => $router?->id,
                    'mac_address' => $mac,
                    'static_ip' => $ip,
                    'reseller_id' => $resellerId,
                    'billing_cycle' => 'monthly',
                    'setup_fee' => 0,
                    'is_import' => true,
                    'ignore_hardware_errors' => true, // Cegah rollback jika hardware router offline / pakai RADIUS
                ];

                if ($existingCustomer) {
                    $data['customer_id'] = $existingCustomer->id;
                }

                $this->provisioningService->activatePPPoEService($data, $this->user->id);
                $successCount++;

            } catch (\Throwable $e) {
                Log::error('PPPoE Import failed for row', [
                    'username' => $username,
                    'error' => $e->getMessage()
                ]);
                $errorMessages[] = "Baris " . ($index + 2) . " ($username): " . $e->getMessage();
            }
        }

        if ($successCount === 0) {
            $msg = "Gagal mengimpor data PPPoE! Tidak ada baris yang valid.";
            if ($skippedCount > 0) {
                $msg .= " Sistem melewatkan $skippedCount baris karena kolom 'username' atau data kosong.";
            }
            if (count($errorMessages) > 0) {
                $msg .= " Error: " . implode('; ', array_slice($errorMessages, 0, 3));
            }
            throw new \Exception($msg);
        }

        // Tampilkan hasil sukses informatif
        $infoMsg = "$successCount akun PPPoE berhasil diproses" . ($updatedCount > 0 ? " ($updatedCount diperbarui)" : "") . ".";
        if (count($errorMessages) > 0) {
            $infoMsg .= " Beberapa baris dilewati: " . implode('; ', array_slice($errorMessages, 0, 3));
        }
        session()->flash('import_result', $infoMsg);
    }
}
