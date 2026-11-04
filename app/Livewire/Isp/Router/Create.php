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
        $this->radius_secret = env('RADIUS_SECRET', 'dsBillingSecret123');
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
                $sysInfo = $driver->getSystemInfo();
                if (!empty($sysInfo['identity']) && $sysInfo['identity'] !== 'unknown') {
                    $identity = $sysInfo['identity'];
                }
                if (!empty($sysInfo['version']) && $sysInfo['version'] !== 'unknown') {
                    $version = $sysInfo['version'];
                }
                if (!empty($sysInfo['board_name']) && $sysInfo['board_name'] !== '-') {
                    $boardName = $sysInfo['board_name'];
                }
            } catch (\Throwable $e) {}

            // Fallback manual query jika belum lengkap
            if (empty($identity)) {
                try {
                    $ident = $driver->query('/system/identity/print');
                    if (isset($ident[0]['name'])) {
                        $identity = $ident[0]['name'];
                    } elseif (isset($ident['name'])) {
                        $identity = $ident['name'];
                    }
                } catch (\Throwable $e) {}
            }

            if (empty($version) || empty($boardName)) {
                try {
                    $sysRes = $driver->query('/system/resource/print');
                    $row = $sysRes[0] ?? (is_array($sysRes) ? $sysRes : []);
                    if (!empty($row)) {
                        $version = $version ?: ($row['version'] ?? '');
                        $boardName = $boardName ?: ($row['board-name'] ?? ($row['platform'] ?? ''));
                    }
                } catch (\Throwable $e) {}
            }

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
            $this->radius_secret = env('RADIUS_SECRET', 'dsBillingSecret123');
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
        $this->radius_secret = env('RADIUS_SECRET', 'dsBillingSecret123');
        $this->buildScript();
    }

    public function buildScript()
    {
        $tempRouter = new \App\Models\ISP\Router([
            'name' => $this->name ?: 'Router',
            'username' => $this->username ?: 'api.dsb.router',
            'password' => $this->password ?: Str::random(16),
            'radius_secret' => $this->radius_secret ?: env('RADIUS_SECRET', 'dsBillingSecret123'),
            'ip_address' => $this->ip_address ?: '127.0.0.1',
        ]);
        $generator = app(\App\Services\Provisioning\RouterProvisioningScriptGenerator::class);
        $options = [
            'connection_mode' => $this->connectionMode,
            'host' => $this->connectionMode === 'vpn' ? '10.200.0.1' : (trim($this->customRadiusIp) ?: null),
        ];
        $this->generatedScript = $generator->generateScript($tempRouter, 'ether1', $options);
        $this->isolirScript = $this->generatedScript;
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
            $this->radius_secret = env('RADIUS_SECRET', 'dsBillingSecret123');
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



