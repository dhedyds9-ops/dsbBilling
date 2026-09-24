<?php

namespace App\Imports;

use App\Models\ISP\PPPoEUser;
use App\Models\ISP\ServiceProfile;
use App\Models\Customer\CustomerService;
use Illuminate\Support\Facades\Log;
use App\Models\ISP\Router;
use App\Models\User;
use App\Services\Provisioning\ProvisioningService;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Illuminate\Support\Str;

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
        $successCount = 0;
        $skippedCount = 0;
        $errorMessages = [];

        foreach ($rows as $index => $row) {
            // Helper function to clean backticks/quotes from MSRadius Excel
            $cleanStr = function($val) {
                if (!$val) return $val;
                return ltrim(trim($val), "`'"); 
            };

            // Bersihkan spasi atau karakter tak terlihat pada header
            $username = $cleanStr($row['login'] ?? $row['username'] ?? $row['kode'] ?? $row['user_id'] ?? $row['user'] ?? null);
            $namaPelanggan = $cleanStr($row['fullname'] ?? $row['full_name'] ?? $row['nama_pelanggan'] ?? $row['name'] ?? $row['nama'] ?? null);
            
            // Skip empty rows
            if (empty($username) || empty($namaPelanggan)) {
                $skippedCount++;
                continue;
            }

            try {
                $paket = $cleanStr($row['plan'] ?? $row['paket_langganan'] ?? $row['profile'] ?? $row['paket'] ?? $row['service'] ?? '');
                $routerName = $cleanStr($row['mikrotik_nas'] ?? $row['mikrotiknas'] ?? $row['router'] ?? $row['nas'] ?? $row['router_nas'] ?? '');
                $resellerName = $cleanStr($row['reseller'] ?? $row['owner'] ?? $row['mitra'] ?? '-');

                $serviceProfile = ServiceProfile::where('name', $paket)->first();
                $router = Router::where('name', $routerName)->first();
                $reseller = null;
                if (!empty($resellerName) && $resellerName !== '-') {
                    $reseller = User::where('name', $resellerName)->first();
                }

                $mac = $cleanStr($row['macaddress'] ?? $row['mac_address'] ?? $row['mac'] ?? $row['caller_id'] ?? null);
                $ip = $cleanStr($row['ipaddress'] ?? $row['ip_address'] ?? $row['static_ip'] ?? $row['ip'] ?? $row['framed_ip'] ?? null);

                // MSRadius sometimes puts the password in the 'email' column if it's randomly generated
                $password = $cleanStr($row['password'] ?? $row['pass'] ?? null);
                $email = $cleanStr($row['email'] ?? null);
                if (empty($password) && !empty($email) && !str_contains($email, '@')) {
                    $password = $email;
                    $email = null;
                }

                $data = [
                    'name' => $namaPelanggan,
                    'phone' => $cleanStr($row['phone'] ?? $row['whatsapp'] ?? $row['no_hp'] ?? $row['no_telp'] ?? '0000'),
                    'email' => $email,
                    'address' => $cleanStr($row['address'] ?? $row['alamat'] ?? null),
                    'username' => $username,
                    'password' => $password ?? Str::random(8),
                    'service_profile_id' => $serviceProfile?->id,
                    'status' => in_array(strtolower($row['status'] ?? ''), ['active', 'inactive']) ? strtolower($row['status']) : 'active',
                    'activation_date' => now()->format('Y-m-d'),
                    'notes' => 'Imported via Excel (Auto-Mapped)',
                    'router_id' => $router?->id,
                    'mac_address' => ($mac === '-') ? null : $mac,
                    'static_ip' => ($ip === '-') ? null : $ip,
                    'reseller_id' => $reseller?->id,
                    'billing_cycle' => 'monthly', // Paksa default monthly
                    'setup_fee' => 0,
                ];

                $this->provisioningService->activatePPPoEService($data, $this->user->id);
                $successCount++;
            } catch (\Exception $e) {
                Log::error('Import failed for row', [
                    'username' => $username,
                    'error' => $e->getMessage()
                ]);
                $errorMessages[] = "Baris " . ($index + 2) . " ($username): " . $e->getMessage();
            }
        }

        if ($successCount === 0) {
            $msg = "Gagal mengimpor data! Tidak ada baris yang valid.";
            if ($skippedCount > 0) {
                $msg .= " Sistem melewatkan $skippedCount baris karena kolom 'username' atau 'nama_pelanggan' (atau aliasnya) kosong/tidak terdeteksi di Excel.";
            }
            if (count($errorMessages) > 0) {
                $msg .= " Error: " . implode(', ', array_slice($errorMessages, 0, 2));
            }
            throw new \Exception($msg);
        }

        // Store result in session so component can read it
        session()->flash('import_result', "$successCount data berhasil diimpor." . (count($errorMessages) > 0 ? " Beberapa data gagal: " . implode(', ', array_slice($errorMessages, 0, 2)) : ""));
    }
}
