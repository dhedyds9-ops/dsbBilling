<?php

namespace App\Services\ISP;

use App\Models\ISP\Onu;
use App\Models\User;
use App\Services\Adapters\Monitoring\GenieACSDriver;
use Carbon\Carbon;
use Exception;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class GenieAcsDiagnosticService
{
    public function __construct(
        protected GenieACSDriver $driver,
        protected GenieAcsService $acsService,
    ) {
    }

    /**
     * Memeriksa otorisasi teknisi terhadap ONT berdasarkan Company & Branch Isolation.
     *
     * @throws AuthorizationException
     */
    public function authorizeTechnician(Onu $onu, ?User $user = null): void
    {
        $user = $user ?? Auth::user();
        if (!$user) {
            throw new AuthorizationException('Autentikasi diperlukan untuk mengakses perangkat ini.');
        }

        // Administrator dan Superadmin memiliki akses oversight global
        if ($user->hasRole(['administrator', 'superadmin'])) {
            return;
        }

        // 1. Validasi Company / Tenant Isolation
        $userTenant = $user->company_id ?? $user->reseller_id ?? null;
        $onuTenant = $onu->company_id
            ?? $onu->reseller_id
            ?? \App\Models\Customer\CustomerService::withoutGlobalScopes()->where('onu_id', $onu->id)->value('reseller_id')
            ?? null;
        if (!empty($userTenant) && !empty($onuTenant) && (int) $userTenant !== (int) $onuTenant) {
            throw new AuthorizationException('Akses ditolak: ONT ini milik perusahaan/tenant yang berbeda (Tenant Isolation).');
        }

        // 2. Validasi Branch Isolation (Cabang)
        if (!empty($user->branch_id) && !empty($onu->branch_id) && (int) $user->branch_id !== (int) $onu->branch_id) {
            throw new AuthorizationException('Akses ditolak: Anda tidak memiliki wewenang untuk perangkat ONT di cabang lain (Branch Isolation).');
        }
    }

    /**
     * Mengambil ID perangkat GenieACS dari database atau serial number.
     */
    public function resolveDeviceId(Onu $onu): ?string
    {
        $resolved = $this->acsService->resolveGenieAcsDeviceId($onu);
        if (!empty($resolved)) {
            return $resolved;
        }

        return !empty($onu->serial_number) ? trim($onu->serial_number) : null;
    }

    /**
     * Membaca kondisi live diagnostik ONT dari GenieACS dengan dukungan multi-vendor dan caching.
     */
    public function getLiveDiagnostics(Onu $onu, bool $forceFresh = false): array
    {
        $this->authorizeTechnician($onu);

        $cacheKey = "ont_live_diagnostics_{$onu->id}";

        if (!$forceFresh) {
            $cached = Cache::get($cacheKey);
            if (is_array($cached)) {
                $cached['data_state'] = 'cached';
                return $cached;
            }
        }

        $deviceId = $this->resolveDeviceId($onu);
        if (empty($deviceId)) {
            return $this->formatUnregisteredDiagnostic($onu);
        }

        try {
            $params = $this->driver->getDeviceParameters($deviceId);
        } catch (\Throwable $e) {
            Log::error("GenieACS NBI API Error on ONU {$onu->id}: " . $e->getMessage());
            throw new Exception("GenieACS NBI REST API Error: " . $e->getMessage(), 0, $e);
        }

        if (empty($params)) {
            return $this->formatNotFoundDiagnostic($onu, $deviceId);
        }

        $diagnostics = $this->parseDeviceParameters($onu, $deviceId, $params);
        $diagnostics['data_state'] = 'fresh';

        // Cache hasil diagnostik selama 30 detik untuk menghindari flood NBI
        Cache::put($cacheKey, $diagnostics, 30);

        return $diagnostics;
    }

    /**
     * Mengirimkan task refresh (Summon) ke GenieACS dan memperbarui cache diagnostik.
     */
    public function refreshParameters(Onu $onu, ?User $user = null): array
    {
        $this->authorizeTechnician($onu, $user);

        // Rate limiting: 15 detik per ONT
        $lockKey = "rate_limit_refresh:{$onu->id}";
        if (Cache::has($lockKey)) {
            return [
                'status' => 'throttled',
                'message' => 'Refresh terlalu sering. Harap tunggu 15 detik sebelum melakukan refresh kembali.',
            ];
        }
        Cache::put($lockKey, true, 15);

        $deviceId = $this->resolveDeviceId($onu);
        if (empty($deviceId)) {
            throw new Exception('Perangkat ONT belum terdaftar di GenieACS.');
        }

        $summonSuccess = $this->driver->summonDevice($deviceId);
        Cache::forget("ont_live_diagnostics_{$onu->id}");

        $freshDiagnostics = $this->getLiveDiagnostics($onu, true);

        $actor = $user ?? Auth::user();
        Log::info("FIELD_ACTION [REFRESH_ONT]: User #{$actor?->id} ({$actor?->name}) refreshed parameters for ONT SN {$onu->serial_number}");

        return [
            'status' => 'success',
            'task_accepted' => $summonSuccess,
            'message' => 'Task refresh parameter berhasil dikirim ke GenieACS. Data diagnostik diperbarui.',
            'refreshed_at' => now()->toIso8601String(),
            'diagnostics' => $freshDiagnostics,
        ];
    }

    /**
     * Mengirimkan task reboot ke ONT melalui GenieACS dengan baseline untuk verifikasi.
     */
    public function rebootOnt(Onu $onu, ?User $user = null): array
    {
        $this->authorizeTechnician($onu, $user);

        // Rate limiting: 30 detik per ONT
        $lockKey = "rate_limit_reboot:{$onu->id}";
        if (Cache::has($lockKey)) {
            return [
                'status' => 'throttled',
                'message' => 'Reboot ONT sedang dalam masa cooldown. Silakan tunggu 30 detik.',
            ];
        }

        $deviceId = $this->resolveDeviceId($onu);
        if (empty($deviceId)) {
            throw new Exception('Perangkat ONT belum terdaftar di GenieACS.');
        }

        // Ambil baseline lastInform dan uptime sebelum reboot
        $baselineParams = [];
        try {
            $baselineParams = $this->driver->getDeviceParameters($deviceId);
        } catch (\Throwable) {}

        $priorLastInform = $baselineParams['_lastInform'] ?? null;
        $priorUptime = $this->extractValue($baselineParams, [
            'InternetGatewayDevice.DeviceInfo.UpTime',
            'Device.DeviceInfo.UpTime',
        ]);

        $dispatched = $this->driver->rebootDevice($deviceId);
        if (!$dispatched) {
            throw new Exception('GenieACS menolak pengiriman task reboot.');
        }

        Cache::put($lockKey, true, 30);
        Cache::forget("ont_live_diagnostics_{$onu->id}");

        $actor = $user ?? Auth::user();
        Log::info("FIELD_ACTION [REBOOT_ONT]: User #{$actor?->id} ({$actor?->name}) dispatched reboot on ONT SN {$onu->serial_number}");

        return [
            'status' => 'task_dispatched',
            'verified' => false,
            'message' => 'Task reboot berhasil dikirim ke GenieACS. ONT sedang memulai ulang (reboot).',
            'dispatched_at' => now()->toIso8601String(),
            'prior_last_inform' => $priorLastInform,
            'prior_uptime' => is_numeric($priorUptime) ? (int) $priorUptime : null,
        ];
    }

    /**
     * Memverifikasi apakah ONT sudah selesai reboot berdasarkan pembaruan Last Inform atau reset Uptime.
     */
    public function verifyReboot(Onu $onu, ?string $priorLastInform = null, ?int $priorUptime = null): array
    {
        $deviceId = $this->resolveDeviceId($onu);
        if (empty($deviceId)) {
            return ['verified' => false, 'status' => 'not_registered', 'message' => 'ONT belum terdaftar.'];
        }

        $params = $this->driver->getDeviceParameters($deviceId);
        if (empty($params)) {
            return ['verified' => false, 'status' => 'pending', 'message' => 'Perangkat belum merespons.'];
        }

        $currentLastInform = $params['_lastInform'] ?? null;
        $currentUptimeRaw = $this->extractValue($params, [
            'InternetGatewayDevice.DeviceInfo.UpTime',
            'Device.DeviceInfo.UpTime',
        ]);
        $currentUptime = is_numeric($currentUptimeRaw) ? (int) $currentUptimeRaw : null;

        $verified = false;
        $reason = '';

        // 1. Verifikasi via Uptime reset (< 180 detik atau lebih kecil dari uptime sebelum reboot)
        if ($currentUptime !== null) {
            if ($priorUptime !== null && $currentUptime < $priorUptime) {
                $verified = true;
                $reason = "Uptime ter-reset dari {$priorUptime}s ke {$currentUptime}s.";
            } elseif ($currentUptime <= 180) {
                $verified = true;
                $reason = "Uptime baru terdeteksi: {$currentUptime} detik.";
            }
        }

        // 2. Verifikasi via Last Inform lebih baru dari baseline
        if (!$verified && $priorLastInform && $currentLastInform) {
            try {
                if (Carbon::parse($currentLastInform)->gt(Carbon::parse($priorLastInform))) {
                    $verified = true;
                    $reason = "Inform baru diterima pada {$currentLastInform}.";
                }
            } catch (\Throwable) {}
        }

        return [
            'verified' => $verified,
            'status' => $verified ? 'completed' : 'pending',
            'message' => $verified
                ? "Reboot ONT berhasil diverifikasi ({$reason})"
                : 'Menunggu perangkat menyelesaikan siklus booting...',
            'current_uptime' => $currentUptime,
            'current_last_inform' => $currentLastInform,
        ];
    }

    /**
     * Memperbarui SSID dan Password Wi-Fi ONT melalui GenieACS dengan validasi ketat dan logging tersanitasi.
     */
    public function updateWifi(Onu $onu, array $data, ?User $user = null): array
    {
        $this->authorizeTechnician($onu, $user);

        // Rate limiting: 20 detik per ONT
        $lockKey = "rate_limit_wifi:{$onu->id}";
        if (Cache::has($lockKey)) {
            return [
                'status' => 'throttled',
                'message' => 'Perubahan Wi-Fi terlalu sering. Harap tunggu beberapa saat.',
            ];
        }

        $ssid = trim($data['ssid_24'] ?? '');
        if (strlen($ssid) < 1 || strlen($ssid) > 32) {
            throw new Exception('SSID Wi-Fi 2.4 GHz harus antara 1 sampai 32 karakter.');
        }

        $password = $data['password_24'] ?? null;
        if (!empty($password) && strlen($password) < 8) {
            throw new Exception('Password Wi-Fi minimal 8 karakter (standar WPA2-PSK).');
        }

        $deviceId = $this->resolveDeviceId($onu);
        if (empty($deviceId)) {
            throw new Exception('Perangkat ONT belum terdaftar di GenieACS.');
        }

        $vendor = strtolower(trim((string) ($onu->vendor?->name ?? 'default')));

        // SANITIZED AUDIT TRAIL: Jangan mencatat password ke log!
        $actor = $user ?? Auth::user();
        Log::info("FIELD_ACTION [UPDATE_WIFI]: User #{$actor?->id} ({$actor?->name}) updated Wi-Fi for ONT SN: {$onu->serial_number}. SSID: {$ssid}, Password: [REDACTED]");

        $dispatched = $this->driver->updateWifiSsidAndPassword($deviceId, $ssid, $password, $vendor);
        if (!$dispatched) {
            throw new Exception('GenieACS gagal memproses task pembaruan Wi-Fi.');
        }

        // Simpan perubahan ke database
        $onu->wifi_ssid = $ssid;
        if (!empty($password)) {
            $onu->wifi_password = $password;
        }
        $onu->save();

        Cache::put($lockKey, true, 20);
        Cache::forget("ont_live_diagnostics_{$onu->id}");

        return [
            'status' => 'success',
            'verified' => true,
            'message' => "Pengaturan Wi-Fi '{$ssid}' berhasil dikirim ke ONT.",
            'ssid' => $ssid,
            // Password sengaja tidak disertakan dalam response ke frontend
        ];
    }

    /**
     * Parsing menyeluruh parameter GenieACS ke dalam struktur Live Diagnostics.
     */
    protected function parseDeviceParameters(Onu $onu, string $deviceId, array $params): array
    {
        $lastInformRaw = $params['_lastInform'] ?? null;
        $onlineStatus = GenieACSDriver::calculateStatus($params);
        $isOnline = ($onlineStatus !== 'offline');

        $statusBadge = match ($onlineStatus) {
            'online' => 'green',
            'stale' => 'yellow',
            default => 'red',
        };

        $connectionStatus = match ($onlineStatus) {
            'online' => 'Connected (Online)',
            'stale' => 'Stale (Koneksi Terlambat)',
            default => 'Disconnected (Offline)',
        };

        // Last Inform formatting
        $lastInformDisplay = 'Tidak Pernah';
        if ($lastInformRaw) {
            try {
                $lastInformDisplay = Carbon::parse($lastInformRaw)->format('d M Y, H:i:s') . ' (' . Carbon::parse($lastInformRaw)->diffForHumans() . ')';
            } catch (\Throwable) {
                $lastInformDisplay = (string) $lastInformRaw;
            }
        }

        // Uptime & Last Boot
        $uptimeRaw = $this->extractValue($params, [
            'InternetGatewayDevice.DeviceInfo.UpTime',
            'Device.DeviceInfo.UpTime',
            'InternetGatewayDevice.DeviceInfo.X_ZTE-COM_UpTime',
        ]);
        $uptimeDisplay = 'Tidak Didukung';
        $lastBootDisplay = 'Tidak Didukung';
        $uptimeSeconds = null;

        if (is_numeric($uptimeRaw) && (int) $uptimeRaw > 0) {
            $uptimeSeconds = (int) $uptimeRaw;
            $uptimeDisplay = $this->formatUptime($uptimeSeconds);
            $lastBootDisplay = now()->subSeconds($uptimeSeconds)->format('d M Y, H:i:s');
        }

        // Optical Diagnostics
        $opticalData = $this->parseOpticalDiagnostics($params);

        // Hardware Information
        $hardwareData = $this->parseHardwareInfo($onu, $params);

        // Wi-Fi Diagnostics
        $wifiData = $this->parseWifiDiagnostics($onu, $deviceId, $params);

        return [
            'device_id' => $deviceId,
            'onu_id' => $onu->id,
            'serial_number' => $onu->serial_number,
            'is_online' => $isOnline,
            'online_status' => $onlineStatus,
            'status_badge' => $statusBadge,
            'last_inform' => $lastInformDisplay,
            'last_inform_raw' => $lastInformRaw,
            'last_boot' => $lastBootDisplay,
            'uptime' => $uptimeDisplay,
            'uptime_seconds' => $uptimeSeconds,
            'connection_status' => $connectionStatus,
            'updated_at' => now()->format('d M Y, H:i:s'),
            'optical' => $opticalData,
            'hardware' => $hardwareData,
            'wifi' => $wifiData,
        ];
    }

    /**
     * Ekstraksi dan kalkulasi diagnostik optik (RX/TX Power, Temperature, LOS).
     */
    protected function parseOpticalDiagnostics(array $params): array
    {
        $rxCandidates = [
            'InternetGatewayDevice.WANDevice.1.WANPONInterfaceConfig.1.X_ZTE-COM_RxPower',
            'InternetGatewayDevice.WANDevice.1.WANPONInterfaceConfig.1.RxPower',
            'InternetGatewayDevice.WANDevice.1.WANPONInterfaceConfig.1.X_HW_RxPower',
            'InternetGatewayDevice.WANDevice.1.WANConnectionDevice.1.WANIPConnection.1.X_HW_RxPower',
            'InternetGatewayDevice.WANDevice.1.WANPONInterfaceConfig.1.X_FH_RxPower',
            'Device.Optical.1.Transceiver.RxPower',
            'Device.Optical.Interface.1.RxPower',
            'InternetGatewayDevice.WANDevice.1.WANConnectionDevice.1.WANIPConnection.1.X_ALG-COM_RxPower',
            'VirtualParameters.rxPower',
        ];

        $txCandidates = [
            'InternetGatewayDevice.WANDevice.1.WANPONInterfaceConfig.1.X_ZTE-COM_TxPower',
            'InternetGatewayDevice.WANDevice.1.WANPONInterfaceConfig.1.TxPower',
            'InternetGatewayDevice.WANDevice.1.WANPONInterfaceConfig.1.X_HW_TxPower',
            'InternetGatewayDevice.WANDevice.1.WANConnectionDevice.1.WANIPConnection.1.X_HW_TxPower',
            'InternetGatewayDevice.WANDevice.1.WANPONInterfaceConfig.1.X_FH_TxPower',
            'Device.Optical.1.Transceiver.TxPower',
            'Device.Optical.Interface.1.TxPower',
            'InternetGatewayDevice.WANDevice.1.WANConnectionDevice.1.WANIPConnection.1.X_ALG-COM_TxPower',
            'VirtualParameters.txPower',
        ];

        $optTempCandidates = [
            'InternetGatewayDevice.WANDevice.1.WANPONInterfaceConfig.1.X_ZTE-COM_TransceiverTemperature',
            'InternetGatewayDevice.WANDevice.1.WANPONInterfaceConfig.1.TransceiverTemperature',
            'InternetGatewayDevice.WANDevice.1.WANPONInterfaceConfig.1.X_HW_TransceiverTemperature',
            'Device.Optical.1.Transceiver.Temperature',
            'VirtualParameters.opticalTemperature',
        ];

        $losCandidates = [
            'InternetGatewayDevice.WANDevice.1.WANPONInterfaceConfig.1.X_ZTE-COM_LOSStatus',
            'InternetGatewayDevice.WANDevice.1.WANPONInterfaceConfig.1.LOSStatus',
            'InternetGatewayDevice.WANDevice.1.WANPONInterfaceConfig.1.X_HW_LOS',
            'InternetGatewayDevice.WANDevice.1.WANPONInterfaceConfig.1.X_FH_LOS',
            'Device.Optical.1.Transceiver.Status',
            'VirtualParameters.losStatus',
        ];

        $rawRx = $this->extractValue($params, $rxCandidates);
        $rawTx = $this->extractValue($params, $txCandidates);
        $rawOptTemp = $this->extractValue($params, $optTempCandidates);
        $rawLos = $this->extractValue($params, $losCandidates);

        // Parse RX Power
        $rxPower = null;
        $rxDisplay = 'Tidak Didukung';
        $rxStatus = 'unknown';

        if ($rawRx !== null && trim((string) $rawRx) !== '' && is_numeric($rawRx)) {
            $num = (float) $rawRx;
            // Angka 0 pada pembacaan sensor optik GPON menandakan tidak terbaca / tidak ada sensor
            if ($num !== 0.0) {
                if (abs($num) > 1000) {
                    $rxPower = round($num / 1000, 2);
                } elseif (abs($num) > 100) {
                    $rxPower = round($num / 100, 2);
                } else {
                    $rxPower = round($num, 2);
                }

                $rxDisplay = number_format($rxPower, 2) . ' dBm';
                if ($rxPower >= -24.0 && $rxPower <= -15.0) {
                    $rxStatus = 'normal'; // Optimal
                } elseif (($rxPower >= -27.0 && $rxPower < -24.0) || ($rxPower > -15.0 && $rxPower <= -10.0)) {
                    $rxStatus = 'warning'; // Marginal
                } else {
                    $rxStatus = 'critical'; // Buruk / Redaman Tinggi
                }
            }
        }

        // Parse TX Power
        $txPower = null;
        $txDisplay = 'Tidak Didukung';
        if ($rawTx !== null && trim((string) $rawTx) !== '' && is_numeric($rawTx)) {
            $num = (float) $rawTx;
            if ($num !== 0.0) {
                if (abs($num) > 1000) {
                    $txPower = round($num / 1000, 2);
                } elseif (abs($num) > 100) {
                    $txPower = round($num / 100, 2);
                } else {
                    $txPower = round($num, 2);
                }
                $txDisplay = number_format($txPower, 2) . ' dBm';
            }
        }

        // Parse Optical Temperature
        $opticalTempDisplay = 'Tidak Didukung';
        if ($rawOptTemp !== null && trim((string) $rawOptTemp) !== '' && is_numeric($rawOptTemp)) {
            $num = (float) $rawOptTemp;
            if ($num !== 0.0) {
                $t = (abs($num) > 1000) ? round($num / 1000, 1) : ((abs($num) > 100) ? round($num / 10, 1) : round($num, 1));
                $opticalTempDisplay = $t . ' °C';
            }
        }

        // Parse LOS Status
        $losDisplay = 'Tidak Didukung';
        if ($rawLos !== null && trim((string) $rawLos) !== '') {
            $lower = strtolower(trim((string) $rawLos));
            if (in_array($lower, ['1', 'true', 'alarm', 'los', 'down', 'loss'], true)) {
                $losDisplay = 'LOS Alarm (Kabel Putus / Tidak Terdeteksi)';
            } elseif (in_array($lower, ['0', 'false', 'normal', 'up', 'ok', 'sync'], true)) {
                $losDisplay = 'Normal (Tersambung)';
            } else {
                $losDisplay = (string) $rawLos;
            }
        }

        return [
            'rx_power' => $rxPower,
            'rx_power_display' => $rxDisplay,
            'rx_power_status' => $rxStatus,
            'tx_power' => $txPower,
            'tx_power_display' => $txDisplay,
            'optical_temperature' => $opticalTempDisplay,
            'los_status' => $losDisplay,
        ];
    }

    /**
     * Ekstraksi informasi perangkat keras ONT.
     */
    protected function parseHardwareInfo(Onu $onu, array $params): array
    {
        $manufacturer = $this->extractValue($params, [
            'InternetGatewayDevice.DeviceInfo.Manufacturer',
            'Device.DeviceInfo.Manufacturer',
            '_deviceId._Manufacturer',
        ]) ?? ($onu->vendor?->name ?? 'Tidak Didukung');

        $productClass = $this->extractValue($params, [
            'InternetGatewayDevice.DeviceInfo.ProductClass',
            'Device.DeviceInfo.ProductClass',
            '_deviceId._ProductClass',
        ]) ?? ($onu->model ?? 'Tidak Didukung');

        $serialNumber = $this->extractValue($params, [
            'InternetGatewayDevice.DeviceInfo.SerialNumber',
            'Device.DeviceInfo.SerialNumber',
            '_deviceId._SerialNumber',
        ]) ?? ($onu->serial_number ?: 'Tidak Didukung');

        $hwVersion = $this->extractValue($params, [
            'InternetGatewayDevice.DeviceInfo.HardwareVersion',
            'Device.DeviceInfo.HardwareVersion',
        ]) ?? ($onu->hardware_version ?: 'Tidak Didukung');

        $swVersion = $this->extractValue($params, [
            'InternetGatewayDevice.DeviceInfo.SoftwareVersion',
            'Device.DeviceInfo.SoftwareVersion',
            'InternetGatewayDevice.DeviceInfo.AdditionalSoftwareVersion',
        ]) ?? ($onu->firmware_version ?: 'Tidak Didukung');

        $rawTemp = $this->extractValue($params, [
            'InternetGatewayDevice.DeviceInfo.X_ZTE-COM_Temperature',
            'InternetGatewayDevice.DeviceInfo.X_HW_Temperature',
            'InternetGatewayDevice.DeviceInfo.X_FH_Temperature',
            'InternetGatewayDevice.DeviceInfo.Temperature',
            'Device.DeviceInfo.Temperature',
        ]);
        $tempDisplay = 'Tidak Didukung';
        if ($rawTemp !== null && trim((string) $rawTemp) !== '' && is_numeric($rawTemp)) {
            $num = (float) $rawTemp;
            if ($num !== 0.0) {
                $t = (abs($num) > 1000) ? round($num / 1000, 1) : ((abs($num) > 100) ? round($num / 10, 1) : round($num, 1));
                $tempDisplay = $t . ' °C';
            }
        }

        return [
            'manufacturer' => $manufacturer,
            'product_class' => $productClass,
            'serial_number' => $serialNumber,
            'hardware_version' => $hwVersion,
            'software_version' => $swVersion,
            'temperature' => $tempDisplay,
        ];
    }

    /**
     * Ekstraksi informasi Wi-Fi dan daftar klien terhubung.
     */
    protected function parseWifiDiagnostics(Onu $onu, string $deviceId, array $params): array
    {
        $ssid24 = $this->extractValue($params, [
            'InternetGatewayDevice.LANDevice.1.WLANConfiguration.1.SSID',
            'Device.WiFi.SSID.1.SSID',
            'Device.WiFi.AccessPoint.1.SSID',
            'VirtualParameters.wifiSsid',
        ]) ?? ($onu->wifi_ssid ?: 'Tidak Didukung');

        $ssid5 = $this->extractValue($params, [
            'InternetGatewayDevice.LANDevice.1.WLANConfiguration.5.SSID',
            'InternetGatewayDevice.LANDevice.1.WLANConfiguration.2.SSID',
            'Device.WiFi.SSID.2.SSID',
            'Device.WiFi.AccessPoint.2.SSID',
        ]) ?? 'Tidak Didukung';

        $securityMode = $this->extractValue($params, [
            'InternetGatewayDevice.LANDevice.1.WLANConfiguration.1.BeaconType',
            'Device.WiFi.AccessPoint.1.Security.ModeEnabled',
            'InternetGatewayDevice.LANDevice.1.WLANConfiguration.1.Standard',
        ]) ?? 'WPA2-PSK';

        $channel = $this->extractValue($params, [
            'InternetGatewayDevice.LANDevice.1.WLANConfiguration.1.Channel',
            'Device.WiFi.Radio.1.Channel',
        ]) ?? 'Auto';

        $radioRaw = $this->extractValue($params, [
            'InternetGatewayDevice.LANDevice.1.WLANConfiguration.1.RadioEnabled',
            'InternetGatewayDevice.LANDevice.1.WLANConfiguration.1.Enable',
            'Device.WiFi.Radio.1.Enable',
        ]);
        $radioStatus = ($radioRaw === '1' || $radioRaw === 'true' || $radioRaw === true)
            ? 'Aktif'
            : (($radioRaw === '0' || $radioRaw === 'false' || $radioRaw === false) ? 'Non-Aktif' : 'Aktif');

        // Wi-Fi Clients
        $clients = [];
        try {
            $rawHosts = $this->driver->getConnectedDevices($deviceId);
            foreach ($rawHosts as $host) {
                $mac = strtoupper(trim((string) ($host['MACAddress'] ?? '')));
                if (strlen(str_replace([':', '-', '.'], '', $mac)) === 12 && !str_contains($mac, ':')) {
                    $mac = implode(':', str_split(str_replace([':', '-', '.'], '', $mac), 2));
                }

                $ifType = (string) ($host['InterfaceType'] ?? $host['Layer1Interface'] ?? 'Wi-Fi');
                if (str_contains(strtoupper($ifType), '802.11') || str_contains(strtoupper($ifType), 'WLAN') || str_contains(strtoupper($ifType), 'WIFI')) {
                    $ifType = 'Wi-Fi';
                } elseif (str_contains(strtoupper($ifType), 'ETH')) {
                    $ifType = 'Ethernet';
                }

                $signal = 'Tidak Didukung';
                if (!empty($host['SignalStrength']) && is_numeric($host['SignalStrength'])) {
                    $signal = $host['SignalStrength'] . ' dBm';
                }

                $clients[] = [
                    'mac_address' => $mac ?: 'Tidak Diketahui',
                    'ip_address' => (string) ($host['IPAddress'] ?? 'Unknown'),
                    'host_name' => (string) ($host['HostName'] ?? 'Unknown Device'),
                    'active' => (bool) ($host['Active'] ?? false),
                    'interface_type' => $ifType,
                    'signal_strength' => $signal,
                    'connection_duration' => 'Tidak Didukung',
                ];
            }
        } catch (\Throwable $e) {
            Log::warning("GenieACS getConnectedDevices error on ONU {$onu->id}: " . $e->getMessage());
        }

        return [
            'ssid_24' => $ssid24,
            'ssid_5' => $ssid5,
            'security_mode' => $securityMode,
            'channel' => $channel,
            'radio_status' => $radioStatus,
            'has_password' => !empty($onu->wifi_password),
            'password_masked' => '********', // Password tidak pernah dibocorkan ke teknisi
            'clients' => $clients,
            'client_count' => count($clients),
        ];
    }

    /**
     * Format respons jika perangkat belum terdaftar di GenieACS.
     */
    protected function formatUnregisteredDiagnostic(Onu $onu): array
    {
        return [
            'device_id' => null,
            'onu_id' => $onu->id,
            'serial_number' => $onu->serial_number,
            'is_online' => false,
            'online_status' => 'offline',
            'status_badge' => 'gray',
            'last_inform' => 'Belum Terdaftar',
            'last_inform_raw' => null,
            'last_boot' => 'Tidak Didukung',
            'uptime' => 'Tidak Didukung',
            'uptime_seconds' => null,
            'connection_status' => 'Belum Terdaftar di GenieACS',
            'data_state' => 'unavailable',
            'updated_at' => now()->format('d M Y, H:i:s'),
            'optical' => [
                'rx_power' => null,
                'rx_power_display' => 'Tidak Didukung',
                'rx_power_status' => 'unknown',
                'tx_power' => null,
                'tx_power_display' => 'Tidak Didukung',
                'optical_temperature' => 'Tidak Didukung',
                'los_status' => 'Tidak Didukung',
            ],
            'hardware' => [
                'manufacturer' => $onu->vendor?->name ?? 'Tidak Didukung',
                'product_class' => $onu->model ?? 'Tidak Didukung',
                'serial_number' => $onu->serial_number,
                'hardware_version' => $onu->hardware_version ?: 'Tidak Didukung',
                'software_version' => $onu->firmware_version ?: 'Tidak Didukung',
                'temperature' => 'Tidak Didukung',
            ],
            'wifi' => [
                'ssid_24' => $onu->wifi_ssid ?: 'Tidak Didukung',
                'ssid_5' => 'Tidak Didukung',
                'security_mode' => 'WPA2-PSK',
                'channel' => 'Auto',
                'radio_status' => 'Tidak Didukung',
                'has_password' => !empty($onu->wifi_password),
                'password_masked' => '********',
                'clients' => [],
                'client_count' => 0,
            ],
        ];
    }

    /**
     * Format respons jika perangkat tidak ditemukan (404) di GenieACS.
     */
    protected function formatNotFoundDiagnostic(Onu $onu, string $deviceId): array
    {
        return [
            'device_id' => $deviceId,
            'onu_id' => $onu->id,
            'serial_number' => $onu->serial_number,
            'is_online' => false,
            'online_status' => 'offline',
            'status_badge' => 'red',
            'last_inform' => 'Perangkat Tidak Ditemukan',
            'last_inform_raw' => null,
            'last_boot' => 'Tidak Didukung',
            'uptime' => 'Tidak Didukung',
            'uptime_seconds' => null,
            'connection_status' => 'Perangkat Tidak Ditemukan di GenieACS (404)',
            'data_state' => 'unavailable',
            'updated_at' => now()->format('d M Y, H:i:s'),
            'optical' => [
                'rx_power' => null,
                'rx_power_display' => 'Tidak Didukung',
                'rx_power_status' => 'unknown',
                'tx_power' => null,
                'tx_power_display' => 'Tidak Didukung',
                'optical_temperature' => 'Tidak Didukung',
                'los_status' => 'Tidak Didukung',
            ],
            'hardware' => [
                'manufacturer' => $onu->vendor?->name ?? 'Tidak Didukung',
                'product_class' => $onu->model ?? 'Tidak Didukung',
                'serial_number' => $onu->serial_number,
                'hardware_version' => $onu->hardware_version ?: 'Tidak Didukung',
                'software_version' => $onu->firmware_version ?: 'Tidak Didukung',
                'temperature' => 'Tidak Didukung',
            ],
            'wifi' => [
                'ssid_24' => $onu->wifi_ssid ?: 'Tidak Didukung',
                'ssid_5' => 'Tidak Didukung',
                'security_mode' => 'WPA2-PSK',
                'channel' => 'Auto',
                'radio_status' => 'Tidak Didukung',
                'has_password' => !empty($onu->wifi_password),
                'password_masked' => '********',
                'clients' => [],
                'client_count' => 0,
            ],
        ];
    }

    /**
     * Helper untuk mengekstrak nilai parameter dari berbagai variasi path TR-069.
     */
    protected function extractValue(array $params, array $candidates): ?string
    {
        foreach ($candidates as $path) {
            // 1. data_get dot notation pada array bertingkat
            $val = data_get($params, $path . '._value') ?? data_get($params, $path);
            if ($val !== null && is_scalar($val) && (string) $val !== '') {
                return (string) $val;
            }

            // 2. Direct key pada flat map
            if (isset($params[$path])) {
                $node = $params[$path];
                if (is_array($node) && isset($node['_value']) && is_scalar($node['_value'])) {
                    return (string) $node['_value'];
                }
                if (is_scalar($node) && (string) $node !== '') {
                    return (string) $node;
                }
            }
        }

        return null;
    }

    /**
     * Helper untuk format detik uptime menjadi hari, jam, menit.
     */
    protected function formatUptime(int $seconds): string
    {
        $days = floor($seconds / 86400);
        $hours = floor(($seconds % 86400) / 3600);
        $minutes = floor(($seconds % 3600) / 60);

        $parts = [];
        if ($days > 0) {
            $parts[] = "{$days} hari";
        }
        if ($hours > 0 || $days > 0) {
            $parts[] = "{$hours} jam";
        }
        $parts[] = "{$minutes} menit";

        return implode(' ', $parts);
    }
}
