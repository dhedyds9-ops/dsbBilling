<?php

namespace App\Livewire\Isp\Router;

use App\Livewire\AdminComponent;
use App\Models\ISP\Pop;
use App\Models\ISP\Vendor;
use App\Services\ISP\RouterService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class Create extends AdminComponent
{
    public $pop_id;
    public $vendor_id;
    public $code;
    public $name;
    public $description;
    public $model;
    public $serial_number;
    public $ip_address;
    public $api_port = 8728;
    public $use_ssl = false;
    public $timeout = 30;
    public $username;
    public $password;
    public $radius_secret;
    public $routeros_version;
    public $status = 'active';

    // Live Test Connection
    public $testConnectionStatus = null;
    public $testConnectionMessage = null;
    public $connectedDevice = null;

    // Quick Script Generator Modal
    public $showScriptModal = false;
    public $scriptTab = 'radius'; // 'radius' or 'isolir'
    public $connectionMode = 'public'; // 'public' or 'vpn'
    public $vpnAssignedIp = '10.200.0.10';
    public $scriptVersion = 'v7';
    public $customRadiusIp = '';
    public $generatedScript = '';
    public $isolirScript = '';

    // Accordion Toggle
    public $showAdvanced = false;

    public function toggleAdvanced()
    {
        $this->showAdvanced = !$this->showAdvanced;
    }

    public function updatedName($value)
    {
        if (empty($this->code) && !empty($value)) {
            $this->generateCode();
        }
    }

    public function generateCode()
    {
        $clean = preg_replace('/[^a-zA-Z0-9]/', '', strtoupper($this->name ?: 'RTR'));
        $prefix = substr($clean, 0, 4) ?: 'RTR';
        $this->code = 'RTR-' . $prefix . '-' . rand(100, 999);
    }

    public function mount()
    {
        parent::mount();
        $this->activeModule = 'isp';
        $this->activePage = 'routers';
        $this->breadcrumbs = [
            ['label' => 'Dashboard', 'url' => route('dashboard')],
            ['label' => 'MikroTik', 'url' => route('isp.routers.index')],
            ['label' => 'Router', 'url' => route('isp.routers.index')],
            ['label' => 'Tambah Baru'],
        ];

        $this->customRadiusIp = request()->getHost() === 'localhost' ? '127.0.0.1' : request()->getHost();
    }

    public function testConnection()
    {
        $this->validate([
            'ip_address' => 'required',
            'api_port' => 'required|numeric',
        ]);

        if (empty($this->username) || empty($this->password)) {
            $this->testConnectionStatus = 'error';
            $this->testConnectionMessage = 'Username dan Password API harus diisi terlebih dahulu untuk menguji koneksi.';
            return;
        }

        $this->testConnectionStatus = null;
        $this->testConnectionMessage = null;
        $this->connectedDevice = null;

        try {
            $tempRouter = new \App\Models\ISP\Router();
            $tempRouter->ip_address = $this->ip_address;
            $tempRouter->username = $this->username;
            $tempRouter->password = $this->password;
            $tempRouter->api_port = (int) $this->api_port;
            $tempRouter->use_ssl = (bool) $this->use_ssl;
            $tempRouter->timeout = 5;

            // 1. Socket Ping Check
            $fp = @fsockopen($this->ip_address, (int) $this->api_port, $errno, $errstr, 3);
            if (!$fp) {
                $this->testConnectionStatus = 'error';
                $this->testConnectionMessage = "Gagal menjangkau host pada port {$this->api_port}. Pastikan IP benar, router aktif, dan port API tidak terblokir firewall.";
                return;
            }
            fclose($fp);

            // 2. Connect via API Driver
            $service = app(\App\Integration\MikroTik\Services\RouterOSService::class);
            $driver = $service->getDriver($tempRouter);

            if (!$driver->connect()) {
                $this->testConnectionStatus = 'error';
                $this->testConnectionMessage = "Port {$this->api_port} terbuka, tetapi autentikasi API ditolak. Pastikan Username dan Password benar, serta service API diaktifkan (/ip service enable api).";
                return;
            }

            // 3. Ambil data sistem & deteksi versi ROS
            $identity = '';
            $version = '';
            $boardName = '';

            try {
                $ident = $driver->query('/system/identity/print');
                if (isset($ident[0]['name'])) {
                    $identity = $ident[0]['name'];
                }
            } catch (\Throwable $e) {}

            try {
                $sysRes = $driver->query('/system/resource/print');
                if (isset($sysRes[0])) {
                    $version = $sysRes[0]['version'] ?? '';
                    $boardName = $sysRes[0]['board-name'] ?? '';
                }
            } catch (\Throwable $e) {}

            $driver->disconnect();

            // Auto-detect ROS version (v6 / v7)
            if (!empty($version)) {
                if (str_starts_with($version, '7.')) {
                    $this->routeros_version = 'v7';
                } elseif (str_starts_with($version, '6.')) {
                    $this->routeros_version = 'v6';
                }
            }

            // Jika nama belum diisi, otomatis gunakan identity MikroTik
            if (empty($this->name) && !empty($identity)) {
                $this->name = $identity;
            }

            $this->testConnectionStatus = 'success';
            $details = array_filter([$identity, $boardName, $version ? "v{$version}" : null]);
            $this->testConnectionMessage = "Koneksi berhasil! Terhubung ke: " . implode(' - ', $details);
            $this->connectedDevice = [
                'identity' => $identity,
                'board' => $boardName,
                'version' => $version,
            ];

        } catch (\Throwable $e) {
            $this->testConnectionStatus = 'error';
            $this->testConnectionMessage = 'Koneksi gagal: ' . $e->getMessage();
        }
    }

    public function openScriptModal()
    {
        if (empty($this->username)) {
            $cleanName = preg_replace('/[^a-zA-Z0-9]/', '', strtolower($this->name ?: 'router'));
            $this->username = 'api.dsb.' . $cleanName;
        }

        if (empty($this->password)) {
            $this->password = Str::random(16);
        }

        if (empty($this->radius_secret)) {
            $this->radius_secret = Str::random(24);
        }

        if (empty($this->customRadiusIp)) {
            $this->customRadiusIp = request()->getHost() === 'localhost' ? '127.0.0.1' : request()->getHost();
        }

        $this->buildScript();
        $this->showScriptModal = true;
    }

    public function closeScriptModal()
    {
        $this->showScriptModal = false;
    }

    public function updatedConnectionMode()
    {
        $this->buildScript();
    }

    public function updatedScriptVersion()
    {
        $this->buildScript();
    }

    public function updatedCustomRadiusIp()
    {
        $this->buildScript();
    }

    public function regenerateCredentials()
    {
        $cleanName = preg_replace('/[^a-zA-Z0-9]/', '', strtolower($this->name ?: 'router'));
        $this->username = 'api.dsb.' . $cleanName;
        $this->password = Str::random(16);
        $this->radius_secret = Str::random(24);
        $this->buildScript();
    }

    public function buildScript()
    {
        $username = $this->username ?: 'api.dsb.router';
        $password = $this->password ?: Str::random(16);
        $radiusSecret = $this->radius_secret ?: Str::random(24);
        $rawRadiusHost = trim($this->customRadiusIp) ?: (request()->getHost() === 'localhost' ? '127.0.0.1' : request()->getHost());
        // MikroTik /radius add address= strictly requires an IPv4/IPv6 address, not a domain name.
        $radiusIp = filter_var($rawRadiusHost, FILTER_VALIDATE_IP) ? $rawRadiusHost : gethostbyname($rawRadiusHost);
        $port = (int) ($this->api_port ?: 8728);

        $isVpn = ($this->connectionMode === 'vpn');
        $effectiveRadiusIp = $isVpn ? '10.200.0.1' : $radiusIp;

        $lines = [];
        $lines[] = "# =================================================================";
        $lines[] = "# dsBilling MikroTik Quick Setup Script (" . strtoupper($this->scriptVersion) . ")" . ($isVpn ? " [MODE VPN]" : "");
        $lines[] = "# Salin dan tempel perintah ini di menu: WinBox -> New Terminal";
        $lines[] = "# =================================================================";
        $lines[] = "";

        if ($isVpn) {
            $lines[] = "# 0. Terowongan VPN ke Server dsBilling (Untuk Router Tanpa IP Publik)";
            $lines[] = ":do { /interface pptp-client remove [find name~\"DSB-VPN\"] } on-error={ :nothing }";
            $lines[] = ":do { /interface sstp-client remove [find name~\"DSB-VPN\"] } on-error={ :nothing }";
            $lines[] = ":do { /interface ovpn-client remove [find name~\"DSB-VPN\"] } on-error={ :nothing }";
            $lines[] = ":do { /ppp profile add name=DSBVPN use-encryption=yes change-tcp-mss=yes comment=\"DSB VPN Profile\" } on-error={ :nothing }";
            $lines[] = ":do { /interface sstp-client add connect-to=\"{$rawRadiusHost}\" port=1443 name=\"DSB-VPN-SSTP\" profile=DSBVPN user=\"{$username}\" password=\"{$password}\" verify-server-certificate=no disabled=no comment=\"IP VPN: {$this->vpnAssignedIp}\" } on-error={ :nothing }";
            $lines[] = ":do { /interface pptp-client add connect-to=\"{$rawRadiusHost}\" name=\"DSB-VPN-PPTP\" profile=DSBVPN user=\"{$username}\" password=\"{$password}\" disabled=yes comment=\"Cadangan PPTP IP: {$this->vpnAssignedIp}\" } on-error={ :nothing }";
            $lines[] = "";
        }

        $lines[] = "# 1. Bersihkan Konfigurasi Lama dsBilling (Anti-Duplikasi)";
        $lines[] = ":do { /radius remove [find comment~\"dsbilling\"] } on-error={ :nothing }";
        $lines[] = ":do { /user remove [find comment~\"dsbilling\"] } on-error={ :nothing }";
        $lines[] = ":do { /user group remove [find comment~\"dsbilling\"] } on-error={ :nothing }";
        $lines[] = "";
        $lines[] = "# 2. Aktifkan Service API MikroTik";
        $lines[] = "/ip service set api disabled=no port={$port}";
        $lines[] = "";
        $lines[] = "# 3. Buat Group dan User API Khusus dsBilling";
        $lines[] = ":do { /user group add name=dsb-api policy=api,read,write,policy,test,sensitive comment=\"managed-by=dsbilling\" } on-error={ :nothing }";
        $lines[] = ":do { /user add name=\"{$username}\" password=\"{$password}\" group=dsb-api comment=\"managed-by=dsbilling\" } on-error={ :nothing }";
        $lines[] = "";
        $lines[] = "# 4. Konfigurasi RADIUS Client (PPPoE, Hotspot & Login)";
        if ($isVpn) {
            $lines[] = ":do { /radius add address=\"10.200.0.1\" secret=\"{$radiusSecret}\" service=ppp,hotspot,login timeout=3000ms comment=\"managed-by=dsbilling\" } on-error={ :put \"Gagal menambahkan RADIUS\" }";
        } else {
            $lines[] = ":do { /radius add address={$radiusIp} secret=\"{$radiusSecret}\" service=ppp,hotspot,login timeout=3000ms comment=\"managed-by=dsbilling\" } on-error={ :put \"Gagal menambahkan RADIUS\" }";
        }
        $lines[] = ":do { /radius incoming set accept=yes port=3799 } on-error={ :nothing }";
        $lines[] = "";
        $lines[] = "# 5. Otomatis Aktifkan RADIUS & Seamless MAC-Cookie di Semua Profil Hotspot & PPPoE";
        $lines[] = ":do { /ppp aaa set use-radius=yes interim-update=5m accounting=yes } on-error={ :nothing }";
        $lines[] = ":do { /ip hotspot profile set login-by=http-chap,http-pap,cookie,mac-cookie http-cookie-lifetime=4w2d use-radius=yes radius-accounting=yes radius-interim-update=5m [find] } on-error={ :nothing }";
        $lines[] = ":do { /ip hotspot user profile set mac-cookie-timeout=1w [find] } on-error={ :nothing }";
        $lines[] = "";
        $lines[] = "# 6. Buka Port Firewall Input (API & CoA Disconnect)";
        $lines[] = ":do { /ip firewall filter add chain=input protocol=tcp dst-port={$port} action=accept comment=\"Accept dsBilling API\" place-before=0 } on-error={ :nothing }";
        $lines[] = ":do { /ip firewall filter add chain=input protocol=udp dst-port=3799 action=accept comment=\"Accept dsBilling CoA/Disconnect\" place-before=0 } on-error={ :nothing }";
        $lines[] = "";
        $lines[] = "# 7. Tes Ping ke Server dsBilling RADIUS";
        $lines[] = "/ping {$effectiveRadiusIp} count=3";
        $lines[] = "";
        $lines[] = ":put \"==============================================\"";
        $lines[] = ":put \" Konfigurasi dsBilling Berhasil Diterapkan!   \"";
        $lines[] = ":put \"==============================================\"";

        $this->generatedScript = implode("\r\n", $lines);

        // Skrip Walled Garden Payment Gateway & Isolir Pelanggan
        $appHost = request()->getHost() === 'localhost' ? '127.0.0.1' : request()->getHost();
        $iso = [];
        $iso[] = "# =================================================================";
        $iso[] = "# dsBilling Walled Garden (Payment Gateway) & Isolir Setup";
        $iso[] = "# Salin dan tempel di WinBox -> New Terminal";
        $iso[] = "# =================================================================";
        $iso[] = "";
        $iso[] = "# 1. Walled Garden Hotspot (Akses Payment Gateway & Billing Sebelum Login)";
        $iso[] = ":do { /ip hotspot walled-garden add dst-host=\"*midtrans.com\" comment=\"managed-by=dsbilling\" } on-error={ :nothing }";
        $iso[] = ":do { /ip hotspot walled-garden add dst-host=\"*duitku.com\" comment=\"managed-by=dsbilling\" } on-error={ :nothing }";
        $iso[] = ":do { /ip hotspot walled-garden add dst-host=\"*tripay.co.id\" comment=\"managed-by=dsbilling\" } on-error={ :nothing }";
        $iso[] = ":do { /ip hotspot walled-garden add dst-host=\"*ipaymu.com\" comment=\"managed-by=dsbilling\" } on-error={ :nothing }";
        $iso[] = ":do { /ip hotspot walled-garden add dst-host=\"*xendit.co\" comment=\"managed-by=dsbilling\" } on-error={ :nothing }";
        $iso[] = ":do { /ip hotspot walled-garden add dst-host=\"*whatsapp.com\" comment=\"managed-by=dsbilling\" } on-error={ :nothing }";
        $iso[] = ":do { /ip hotspot walled-garden add dst-host=\"*whatsapp.net\" comment=\"managed-by=dsbilling\" } on-error={ :nothing }";
        $iso[] = ":do { /ip hotspot walled-garden add dst-host=\"{$appHost}\" comment=\"managed-by=dsbilling\" } on-error={ :nothing }";
        $iso[] = ":do { /ip hotspot walled-garden ip add dst-address=\"{$radiusIp}\" comment=\"managed-by=dsbilling\" } on-error={ :nothing }";
        $iso[] = "";
        $iso[] = "# 2. Whitelist Firewall Isolir (Pelanggan PPPoE Menunggak)";
        $iso[] = ":do { /ip firewall address-list add list=ISOLIR_WHITELIST address=\"{$appHost}\" comment=\"managed-by=dsbilling: Server\" } on-error={ :nothing }";
        $iso[] = ":do { /ip firewall address-list add list=ISOLIR_WHITELIST address=\"app.midtrans.com\" comment=\"managed-by=dsbilling: Payment\" } on-error={ :nothing }";
        $iso[] = ":do { /ip firewall address-list add list=ISOLIR_WHITELIST address=\"api.midtrans.com\" comment=\"managed-by=dsbilling: Payment\" } on-error={ :nothing }";
        $iso[] = ":do { /ip firewall address-list add list=ISOLIR_WHITELIST address=\"api.duitku.com\" comment=\"managed-by=dsbilling: Payment\" } on-error={ :nothing }";
        $iso[] = ":do { /ip firewall address-list add list=ISOLIR_WHITELIST address=\"tripay.co.id\" comment=\"managed-by=dsbilling: Payment\" } on-error={ :nothing }";
        $iso[] = "";
        $iso[] = "# 3. Aturan Filter & Drop Traffic Pelanggan Menunggak (ISOLIR_LIST)";
        $iso[] = ":do { /ip firewall filter add chain=forward action=accept protocol=udp src-address-list=ISOLIR_LIST dst-port=53 comment=\"managed-by=dsbilling: Allow Isolir DNS\" place-before=0 } on-error={ :nothing }";
        $iso[] = ":do { /ip firewall filter add chain=forward action=accept protocol=tcp src-address-list=ISOLIR_LIST dst-port=53 comment=\"managed-by=dsbilling: Allow Isolir DNS\" place-before=0 } on-error={ :nothing }";
        $iso[] = ":do { /ip firewall filter add chain=forward action=accept src-address-list=ISOLIR_LIST dst-address-list=ISOLIR_WHITELIST comment=\"managed-by=dsbilling: Allow Whitelist\" place-before=1 } on-error={ :nothing }";
        $iso[] = ":do { /ip firewall filter add chain=forward action=drop src-address-list=ISOLIR_LIST comment=\"managed-by=dsbilling: Drop other Isolir Traffic\" place-before=2 } on-error={ :nothing }";
        $iso[] = "";
        $iso[] = ":put \"==================================================\"";
        $iso[] = ":put \" Walled Garden & Firewall Isolir Selesai Dipasang! \"";
        $iso[] = ":put \"==================================================\"";

        $this->isolirScript = implode("\r\n", $iso);
    }

    public function applyScriptCredentials()
    {
        $this->routeros_version = $this->scriptVersion;
        if ($this->connectionMode === 'vpn') {
            $this->ip_address = $this->vpnAssignedIp;
        }
        $this->showScriptModal = false;
        session()->flash('info', 'Kredensial dari skrip telah diterapkan ke form. Silakan jalankan skrip di WinBox lalu klik "Test Koneksi".');
    }

    public function save()
    {
        if (empty($this->code)) {
            $cleanName = preg_replace('/[^a-zA-Z0-9]/', '', strtolower($this->name ?? 'rt'));
            $this->code = strtoupper($cleanName) . '-' . rand(100, 999);
        }
        
        if (empty($this->username)) {
            $cleanName = preg_replace('/[^a-zA-Z0-9]/', '', strtolower($this->name ?? 'rt'));
            $this->username = 'api.dsb.' . $cleanName;
        }
        if (empty($this->password)) {
            $this->password = Str::random(16);
        }
        if (empty($this->radius_secret)) {
            $this->radius_secret = Str::random(32);
        }

        $this->validate([
            'name' => 'required|string|max:255',
            'status' => 'required|in:active,inactive',
            'ip_address' => 'required',
            'api_port' => 'required|numeric',
        ]);

        $service = app(RouterService::class);
        $service->create([
            'pop_id' => $this->pop_id,
            'vendor_id' => $this->vendor_id,
            'code' => $this->code,
            'name' => $this->name,
            'description' => $this->description,
            'model' => $this->model,
            'serial_number' => $this->serial_number,
            'ip_address' => $this->ip_address,
            'api_port' => $this->api_port,
            'use_ssl' => $this->use_ssl,
            'timeout' => $this->timeout,
            'username' => $this->username,
            'password' => $this->password,
            'radius_secret' => $this->radius_secret,
            'routeros_version' => $this->routeros_version,
            'status' => $this->status,
        ], Auth::user());

        session()->flash('success', 'Router berhasil ditambahkan!');
        return redirect()->route('isp.routers.index');
    }

    public function render()
    {
        $pops = Pop::active()->get();
        $vendors = Vendor::active()->get();
        return view('livewire.isp.router.create', compact('pops', 'vendors'));
    }
}
