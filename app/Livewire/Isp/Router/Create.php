<?php

namespace App\Livewire\Isp\Router;

use App\Livewire\AdminComponent;
use App\Models\ISP\Pop;
use App\Models\ISP\Vendor;
use App\Services\ISP\RouterService;
use Illuminate\Support\Facades\Auth;

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
    public $routeros_version;
    public $status = 'active';

    public function mount()
    {
        parent::mount();
        $this->activeModule = 'isp';
        $this->activePage = 'routers';
        $this->breadcrumbs = [
            ['label' => 'Dashboard', 'url' => route('dashboard')],
            ['label' => 'MikroTik', 'url' => route('isp.routers.index')],
            ['label' => 'Router', 'url' => route('isp.routers.index')],
            ['label' => 'Buat Baru'],
        ];
    }

    // duplicate save removed
    public $currentStep = 1;
    public $radius_secret;
    public $diagnosticResults = [];
    public $testPassed = false;
    public $scriptVersion = 'ros7'; // Default to ROS7

    public function generateCredentials()
    {
        $this->validate([
            'name' => 'required|string',
            'ip_address' => 'required',
            'api_port' => 'required|numeric',
        ]);

        // Generate recognizable username
        $cleanName = preg_replace('/[^a-zA-Z0-9]/', '', strtolower($this->name));
        $this->username = 'api.dsb.' . $cleanName;
        
        // Generate random passwords
        $this->password = \Illuminate\Support\Str::random(32);
        $this->radius_secret = \Illuminate\Support\Str::random(32);
        
        if (empty($this->code)) {
            $this->code = strtoupper($cleanName) . '-' . rand(100, 999);
        }

        $this->currentStep = 2;
    }

    public function goToStep($step)
    {
        $this->currentStep = $step;
    }

    public function getDiagnosticChecklist()
    {
        return [
            'api_reachable' => 'API Port Reachable',
            'login_success' => 'API Login Success',
            'identity' => 'Identity: ',
            'os_version' => 'RouterOS Version: ',
            'board_name' => 'Board Name: ',
            'cpu' => 'CPU: ',
            'radius_available' => 'RADIUS Configured',
            'user_found' => 'API User Found',
        ];
    }

    public function runDiagnosticTest()
    {
        $this->diagnosticResults = [
            'api_reachable' => ['status' => 'pending', 'message' => ''],
            'login_success' => ['status' => 'pending', 'message' => ''],
            'identity' => ['status' => 'pending', 'message' => ''],
            'os_version' => ['status' => 'pending', 'message' => ''],
            'board_name' => ['status' => 'pending', 'message' => ''],
            'cpu' => ['status' => 'pending', 'message' => ''],
            'radius_available' => ['status' => 'pending', 'message' => ''],
            'user_found' => ['status' => 'pending', 'message' => ''],
        ];
        $this->testPassed = false;

        try {
            $tempRouter = new \App\Models\ISP\Router();
            $tempRouter->ip_address = $this->ip_address;
            $tempRouter->username = $this->username;
            $tempRouter->password = $this->password;
            $tempRouter->api_port = $this->api_port;
            $tempRouter->use_ssl = $this->use_ssl;
            $tempRouter->timeout = 5; 

            // 1. Check API Port (Socket Check)
            $fp = @fsockopen($this->ip_address, $this->api_port, $errno, $errstr, 2);
            if (!$fp) {
                $this->diagnosticResults['api_reachable'] = ['status' => 'failed', 'message' => '❌ API Port tertutup atau terblokir Firewall. Pastikan port ' . $this->api_port . ' terbuka.'];
                throw new \Exception("Port unreachable");
            }
            fclose($fp);
            $this->diagnosticResults['api_reachable'] = ['status' => 'success', 'message' => '✅ Port ' . $this->api_port . ' Terbuka'];

            // 2. Login
            $service = app(\App\Integration\MikroTik\Services\RouterOSService::class);
            $driver = $service->getDriver($tempRouter);
            
            if (!$driver->connect()) {
                $this->diagnosticResults['login_success'] = ['status' => 'failed', 'message' => '❌ Login Gagal. Username/Password salah atau API dinonaktifkan (/ip service enable api)'];
                throw new \Exception("Login failed");
            }
            $this->diagnosticResults['login_success'] = ['status' => 'success', 'message' => '✅ Login Berhasil'];

            // 3. Get System Resources
            $sysRes = $driver->query('/system/resource/print');
            if (isset($sysRes[0])) {
                $this->routeros_version = $sysRes[0]['version'] ?? 'Unknown';
                $this->diagnosticResults['os_version'] = ['status' => 'success', 'message' => '✅ ' . ($sysRes[0]['version'] ?? '')];
                $this->diagnosticResults['board_name'] = ['status' => 'success', 'message' => '✅ ' . ($sysRes[0]['board-name'] ?? '')];
                $this->diagnosticResults['cpu'] = ['status' => 'success', 'message' => '✅ ' . ($sysRes[0]['cpu'] ?? '')];
            }

            // 4. Get Identity
            $sysIdent = $driver->query('/system/identity/print');
            if (isset($sysIdent[0])) {
                $this->diagnosticResults['identity'] = ['status' => 'success', 'message' => '✅ ' . ($sysIdent[0]['name'] ?? '')];
            }

            // 5. Check API User
            $users = $driver->query('/user/print', [['name', '=', $this->username]]);
            if (count($users) > 0) {
                $this->diagnosticResults['user_found'] = ['status' => 'success', 'message' => '✅ User ' . $this->username . ' terdaftar.'];
            } else {
                $this->diagnosticResults['user_found'] = ['status' => 'failed', 'message' => '❌ User API tidak ditemukan di MikroTik!'];
                throw new \Exception("User not found");
            }

            // 6. Check Radius
            $radius = $driver->query('/radius/print');
            $billingIp = request()->getHost() === 'localhost' ? '127.0.0.1' : request()->getHost();
            $radiusFound = false;
            foreach($radius as $r) {
                if (isset($r['address']) && $r['address'] === $billingIp) {
                    $radiusFound = true;
                    break;
                }
            }
            if ($radiusFound) {
                $this->diagnosticResults['radius_available'] = ['status' => 'success', 'message' => '✅ RADIUS Server dsBilling ditemukan.'];
            } else {
                $this->diagnosticResults['radius_available'] = ['status' => 'warning', 'message' => '⚠️ RADIUS Server belum ditambahkan di MikroTik.'];
            }

            $driver->disconnect();
            
            // If we reach here without exceptions and user is found, test passed
            $this->testPassed = true;
            $this->status = 'active';

        } catch (\Exception $e) {
            $this->testPassed = false;
            // Catch all if it broke early
        }
    }

    public function save()
    {
        $this->validate([
            'code' => 'required|unique:routers,code',
            'name' => 'required|string|max:255',
            'status' => 'required|in:active,inactive',
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

        session()->flash('success', 'Router berhasil di-onboarding!');
        return redirect()->route('isp.routers.index');
    }

    public function render()
    {
        $pops = Pop::active()->get();
        $vendors = Vendor::active()->get();
        return view('livewire.isp.router.create', compact('pops', 'vendors'));
    }
}
