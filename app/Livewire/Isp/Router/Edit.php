<?php

namespace App\Livewire\Isp\Router;

use App\Livewire\AdminComponent;
use App\Models\ISP\Router as RouterModel;
use App\Models\ISP\Pop;
use App\Models\ISP\Vendor;
use App\Services\ISP\RouterService;
use Illuminate\Support\Facades\Auth;

class Edit extends AdminComponent
{
    public $routerId;
    public $router;
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
    public $timeout;
    public $username;
    public $password;
    public $radius_secret;
    public $routeros_version;
    public $status;

    public function mount($id = null)
    {
        parent::mount();
        $this->activeModule = 'isp';
        $this->activePage = 'routers';
        $this->routerId = $id;
        
        $this->router = \App\Models\ISP\Router::findOrFail($id);

        $this->pop_id = $this->router->pop_id;
        $this->vendor_id = $this->router->vendor_id;
        $this->code = $this->router->code;
        $this->name = $this->router->name;
        $this->description = $this->router->description;
        $this->model = $this->router->model;
        $this->serial_number = $this->router->serial_number;
        $this->ip_address = $this->router->ip_address;
        $this->api_port = $this->router->api_port;
        $this->use_ssl = $this->router->use_ssl;
        $this->timeout = $this->router->timeout;
        $this->username = $this->router->username;
        $this->password = $this->router->password;
        $this->radius_secret = $this->router->radius_secret;
        $this->routeros_version = $this->router->routeros_version;
        $this->status = $this->router->status;

        $this->breadcrumbs = [
            ['label' => 'Dashboard', 'url' => route('dashboard')],
            ['label' => 'MikroTik', 'url' => route('isp.routers.index')],
            ['label' => 'Router', 'url' => route('isp.routers.index')],
            ['label' => $this->router->name, 'url' => route('isp.routers.show', $this->routerId)],
            ['label' => 'Edit'],
        ];
    }

    public function save()
    {
        $this->validate([
            'code' => 'required|unique:routers,code,' . $this->router->id,
            'name' => 'required|string|max:255',
            'status' => 'required|in:active,inactive',
        ]);

        $service = app(RouterService::class);
        $service->update($this->router, [
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

        session()->flash('success', 'Router berhasil diperbarui!');
        return redirect()->route('isp.routers.show', $this->routerId);
    }

    public $testConnectionStatus = null;
    public $testConnectionMessage = null;
    public $connectedDevice = null;

    public function testConnection()
    {
        $this->validate([
            'ip_address' => 'required',
            'username' => 'required',
            'password' => 'required',
            'api_port' => 'required|numeric',
        ]);

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

            $service = app(\App\Integration\MikroTik\Services\RouterOSService::class);
            $driver = $service->getDriver($tempRouter);
            
            if ($driver->connect()) {
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

                // Auto-detect ROS version
                if (!empty($version)) {
                    if (str_starts_with($version, '7.')) {
                        $this->routeros_version = 'v7';
                    } elseif (str_starts_with($version, '6.')) {
                        $this->routeros_version = 'v6';
                    }
                }

                $this->testConnectionStatus = 'success';
                $details = array_filter([$identity, $boardName, $version ? "v{$version}" : null]);
                $this->testConnectionMessage = 'Berhasil terhubung ke Router! ' . ($details ? '(' . implode(' - ', $details) . ')' : '');
                $this->connectedDevice = [
                    'identity' => $identity,
                    'board' => $boardName,
                    'version' => $version,
                ];
            } else {
                $this->testConnectionStatus = 'error';
                $this->testConnectionMessage = "Port {$this->api_port} terbuka, tetapi autentikasi API ditolak. Pastikan Username dan Password benar, serta service API diaktifkan.";
            }
        } catch (\Throwable $e) {
            $this->testConnectionStatus = 'error';
            $this->testConnectionMessage = 'Koneksi gagal: ' . $e->getMessage();
        }
    }

    public function render()
    {
        $pops = Pop::active()->get();
        $vendors = Vendor::active()->get();
        return view('livewire.isp.router.edit', compact('pops', 'vendors'));
    }
}
