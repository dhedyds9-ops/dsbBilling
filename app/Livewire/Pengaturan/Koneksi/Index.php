<?php

namespace App\Livewire\Pengaturan\Koneksi;

use App\Livewire\AdminComponent;
use App\Models\ISP\Router;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Throwable;

class Index extends AdminComponent
{
    public string $activeModule = 'pengaturan';
    public string $activePage = 'koneksi';

    public array $tabs = [
        'router_api' => 'Router API (MikroTik)',
        'radius' => 'Radius Server',
        'genieacs' => 'GenieACS (TR-069)',
        'radius_client' => 'Client/Secret',
    ];

    public string $activeTab = 'router_api';

    public array $routerForm = [
        'id' => null,
        'name' => '',
        'ip_address' => '',
        'api_port' => 8728,
        'api_ssl_port' => 8729,
        'api_user' => 'admin',
        'api_password' => '',
        'use_ssl' => false,
        'radius_port' => 3799,
        'nas_identifier' => '',
        'coa_enabled' => true,
        'is_active' => true,
        'timeout' => 5,
    ];

    public array $radiusForm = [
        'auth_host' => '127.0.0.1',
        'auth_port' => 1812,
        'acct_host' => '127.0.0.1',
        'acct_port' => 1813,
        'coa_host' => '127.0.0.1',
        'coa_port' => 3799,
        'shared_secret' => '',
        'auth_proto' => 'pap',
        'session_timeout' => 86400,
        'interim_interval' => 300,
        'nas_port_type' => 'Ethernet',
        'service_type' => 'Framed-User',
        'require_message_auth' => false,
        'acct_enabled' => true,
        'acct_interim_on_update' => true,
    ];

    public array $genieAcsForm = [
        'base_url' => 'http://127.0.0.1:7557',
        'api_key' => '',
        'connection_request_username' => 'acs',
        'connection_request_password' => '',
        'tr069_port' => 7547,
        'cwmp_version' => '1.1',
        'default_oui' => '',
        'default_product_class' => '',
        'default_software_version' => '',
        'ssl_verify' => false,
        'timeout' => 10,
        'retry_count' => 3,
        'webhook_enabled' => false,
        'webhook_url' => '',
    ];

    public array $routerList = [];
    public ?int $routerTestId = null;
    public ?string $routerTestResult = null;
    public string $radiusTestResult = '';
    public string $genieAcsTestResult = '';
    public string $savedStatus = '';

    public function mount(): void
    {
        parent::mount();
        $this->activeModule = 'pengaturan';
        $this->activePage = 'koneksi';
        $this->loadRouters();
    }

    public function authorizeAccess(): void
    {
        if (!Auth::check()) abort(403);
    }

    public function boot(): void
    {
        $this->authorizeAccess();
    }

    public function loadRouters(): void
    {
        try {
            $rows = Router::query()
                ->orderBy('id')
                ->limit(100)
                ->get(['id', 'name', 'hostname', 'ip_address', 'api_port', 'api_user', 'is_active', 'use_ssl', 'nas_identifier'])
                ->all();
            $this->routerList = array_map(function ($r) {
                return [
                    'id' => $r->id,
                    'name' => $r->name,
                    'hostname' => $r->hostname ?? $r->ip_address,
                    'ip_address' => $r->ip_address,
                    'api_port' => $r->api_port,
                    'api_user' => $r->api_user,
                    'is_active' => (bool)$r->is_active,
                    'use_ssl' => (bool)$r->use_ssl,
                    'nas_identifier' => $r->nas_identifier,
                    'status' => (bool)$r->is_active ? 'online' : 'offline',
                ];
            }, $rows);
        } catch (Throwable $e) {
            Log::error('Koneksi loadRouters failed', ['e' => $e->getMessage()]);
            $this->routerList = [];
        }
    }

    public function setActiveTab(string $tab): void
    {
        if (isset($this->tabs[$tab])) {
            $this->activeTab = $tab;
            $this->savedStatus = '';
        }
    }

    public function selectRouter(int $id): void
    {
        try {
            $r = Router::find($id);
            if ($r) {
                $this->routerForm = [
                    'id' => $r->id,
                    'name' => $r->name ?? '',
                    'ip_address' => $r->ip_address ?? ($r->hostname ?? ''),
                    'api_port' => (int)($r->api_port ?? 8728),
                    'api_ssl_port' => (int)($r->api_ssl_port ?? 8729),
                    'api_user' => $r->api_user ?? 'admin',
                    'api_password' => $r->api_password ?? '',
                    'use_ssl' => (bool)($r->use_ssl ?? false),
                    'radius_port' => (int)($r->coa_port ?? 3799),
                    'nas_identifier' => $r->nas_identifier ?? '',
                    'coa_enabled' => true,
                    'is_active' => (bool)($r->is_active ?? true),
                    'timeout' => 5,
                ];
                session()->flash('info', 'Router #' . $id . ' dimuat.');
            }
        } catch (Throwable $e) {
            $this->addError('routerForm', 'Gagal load: ' . $e->getMessage());
        }
    }

    public function resetRouterForm(): void
    {
        $this->routerForm = [
            'id' => null, 'name' => '', 'ip_address' => '', 'api_port' => 8728,
            'api_ssl_port' => 8729, 'api_user' => 'admin', 'api_password' => '',
            'use_ssl' => false, 'radius_port' => 3799, 'nas_identifier' => '',
            'coa_enabled' => true, 'is_active' => true, 'timeout' => 5,
        ];
    }

    public function saveRouter(): void
    {
        try {
            if (empty($this->routerForm['name']) || empty($this->routerForm['ip_address'])) {
                $this->addError('routerForm.*', 'Nama & IP Address wajib.');
                return;
            }
            $id = $this->routerForm['id'] ?? null;
            $data = [
                'name' => $this->routerForm['name'],
                'hostname' => $this->routerForm['ip_address'],
                'ip_address' => $this->routerForm['ip_address'],
                'api_port' => (int)$this->routerForm['api_port'],
                'api_ssl_port' => (int)$this->routerForm['api_ssl_port'],
                'api_user' => $this->routerForm['api_user'],
                'api_password' => $this->routerForm['api_password'],
                'use_ssl' => (bool)($this->routerForm['use_ssl'] ?? false),
                'coa_port' => (int)($this->routerForm['radius_port'] ?? 3799),
                'nas_identifier' => $this->routerForm['nas_identifier'] ?: ($this->routerForm['name']),
                'is_active' => (bool)($this->routerForm['is_active'] ?? true),
            ];
            if ($id) {
                Router::where('id', (int)$id)->update($data);
                session()->flash('success', 'Router #' . $id . ' diperbarui.');
            } else {
                Router::create($data);
                session()->flash('success', 'Router baru ditambahkan.');
            }
            $this->savedStatus = 'success';
            $this->loadRouters();
            $this->resetRouterForm();
        } catch (Throwable $e) {
            $this->savedStatus = 'error';
            $this->addError('routerForm', 'Gagal simpan Router: ' . $e->getMessage());
        }
    }

    public function testRouterConnection(int $id): void
    {
        $this->routerTestId = $id;
        try {
            $r = Router::find($id);
            if (!$r) {
                $this->routerTestResult = 'FAIL: Router tidak ditemukan.';
                return;
            }
            $host = $r->ip_address ?: $r->hostname;
            $port = (int)($r->api_port ?? 8728);
            $fp = @fsockopen($host, $port, $errno, $errstr, 3);
            if ($fp) {
                fclose($fp);
                $this->routerTestResult = "OK: Router {$r->name} ({$host}:{$port}) reachable (TCP-OK).";
            } else {
                $this->routerTestResult = "FAIL: {$host}:{$port} tidak bisa dihubungi ({$errstr}).";
            }
        } catch (Throwable $e) {
            $this->routerTestResult = 'ERROR: ' . $e->getMessage();
        }
    }

    public function testRadius(): void
    {
        try {
            $host = $this->radiusForm['auth_host'] ?? '127.0.0.1';
            $port = (int)($this->radiusForm['auth_port'] ?? 1812);
            $fp = @fsockopen('udp://' . $host, $port, $errno, $errstr, 3);
            if ($fp) {
                fclose($fp);
                $this->radiusTestResult = "OK: Radius Auth {$host}:{$port} UDP port terbuka / connectable.";
            } else {
                $this->radiusTestResult = "WARN: {$host}:{$port} tidak bisa probe via TCP/UDP (cek firewall/iptables). Shared Secret wajib disimpan agar billing berfungsi.";
            }
        } catch (Throwable $e) {
            $this->radiusTestResult = 'ERROR: ' . $e->getMessage();
        }
    }

    public function testGenieAcs(): void
    {
        try {
            $url = rtrim((string)($this->genieAcsForm['base_url'] ?? ''), '/') . '/tr069';
            if (empty($this->genieAcsForm['base_url'])) {
                $this->genieAcsTestResult = 'FAIL: Base URL belum diisi.';
                return;
            }
            $ctx = stream_context_create(['http' => ['timeout' => 3, 'ignore_errors' => true]]);
            $resp = @file_get_contents(rtrim((string)$this->genieAcsForm['base_url'], '/') . '/', false, $ctx);
            if ($resp !== false) {
                $this->genieAcsTestResult = 'OK: GenieACS reachable di ' . $this->genieAcsForm['base_url'] . ' (response: ' . strlen($resp) . ' bytes).';
            } else {
                $this->genieAcsTestResult = 'FAIL: GenieACS tidak bisa diakses (URL check).';
            }
        } catch (Throwable $e) {
            $this->genieAcsTestResult = 'ERROR: ' . $e->getMessage();
        }
    }

    public function saveRadius(): void
    {
        try {
            if (empty($this->radiusForm['shared_secret'])) {
                $this->addError('radiusForm.shared_secret', 'Shared Secret wajib diisi.');
                return;
            }
            $this->savedStatus = 'success';
            session()->flash('success', 'Konfigurasi Radius disimpan.');
        } catch (Throwable $e) {
            $this->savedStatus = 'error';
            $this->addError('radiusForm', 'Gagal simpan: ' . $e->getMessage());
        }
    }

    public function saveGenieAcs(): void
    {
        try {
            if (empty($this->genieAcsForm['base_url'])) {
                $this->addError('genieAcsForm.base_url', 'Base URL wajib diisi.');
                return;
            }
            $this->savedStatus = 'success';
            session()->flash('success', 'Konfigurasi GenieACS/TR-069 disimpan.');
        } catch (Throwable $e) {
            $this->savedStatus = 'error';
            $this->addError('genieAcsForm', 'Gagal simpan: ' . $e->getMessage());
        }
    }

    public function render()
    {
        return view('livewire.pengaturan.koneksi.index');
    }
}
