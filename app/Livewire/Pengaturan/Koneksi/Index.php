<?php

namespace App\Livewire\Pengaturan\Koneksi;

use App\Livewire\AdminComponent;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Throwable;

class Index extends AdminComponent
{
    public string $activeModule = 'pengaturan';
    public string $activePage = 'koneksi';

    public array $tabs = [
        'radius' => 'Radius Server',
        'radius_client' => 'Client/Secret',
    ];

    public string $activeTab = 'radius';

    public array $radiusForm = [
        'auth_host' => '127.0.0.1',
        'auth_port' => 1812,
        'acct_host' => '127.0.0.1',
        'acct_port' => 1813,
        'coa_host' => '127.0.0.1',
        'coa_port' => 3799,
        'shared_secret' => '',
        'proto' => 'pap',
        'nas_port_type' => 'PPPoE',
        'session_timeout' => 86400,
        'interim_interval' => 300,
        'service_type' => 'Framed-User',
        'require_message_auth' => false,
        'acct_enabled' => true,
        'acct_interim' => true,
        'webhook_url' => '',
    ];

    public array $radiusClient = [
        'identifier' => 'dsBilling-CLIENT-01',
        'realm' => '@billing.local',
        'nas_ip' => '',
        'secret' => '',
        'acct_interim_on_update' => true,
    ];

    public string $radiusTestResult = '';
    public string $savedStatus = '';

    public function mount(): void
    {
        parent::mount();
        $this->authorizeAccess();

        try {
            // Muat konfigurasi yang tersimpan
            $service = app(\App\Services\Pengaturan\ConnectionSettingsService::class);
            
            $radiusConfig = \App\Models\Setting::getValue('connection.radius_settings', []);
            if (!empty($radiusConfig)) {
                $this->radiusForm = array_merge($this->radiusForm, $radiusConfig);
            }
            
            $radiusClientConfig = \App\Models\Setting::getValue('connection.radius_client_settings', []);
            if (!empty($radiusClientConfig)) {
                $this->radiusClient = array_merge($this->radiusClient, $radiusClientConfig);
            }
        } catch (Throwable $e) {
            Log::error('Koneksi loadSettings failed', ['e' => $e->getMessage()]);
        }
    }

    public function authorizeAccess(): void
    {
        if (!Auth::check()) abort(403);
    }

    public function boot(): void
    {
        $this->authorizeAccess();
    }

    public function setActiveTab(string $tab): void
    {
        if (isset($this->tabs[$tab])) {
            $this->activeTab = $tab;
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

    public function saveRadius(\App\Services\Pengaturan\ConnectionSettingsService $service): void
    {
        try {
            if (empty($this->radiusForm['shared_secret'])) {
                $this->addError('radiusForm.shared_secret', 'Shared Secret wajib diisi.');
                return;
            }
            
            $service->saveRadiusSettings($this->radiusForm);
            $this->savedStatus = 'saved';
            $this->dispatch('toast', type: 'success', message: 'Konfigurasi Radius disimpan.');
        } catch (Throwable $e) {
            $this->savedStatus = 'error';
            $this->addError('radiusForm', 'Gagal simpan: ' . $e->getMessage());
        }
    }

    public function saveRadiusClient(\App\Services\Pengaturan\ConnectionSettingsService $service): void
    {
        try {
            if (empty($this->radiusClient['identifier'])) {
                $this->addError('radiusClient.identifier', 'Identifier wajib diisi.');
                return;
            }
            
            $service->saveRadiusClientSettings($this->radiusClient);
            $this->savedStatus = 'saved';
            $this->dispatch('toast', type: 'success', message: 'Konfigurasi Radius Client (Outbound) disimpan.');
        } catch (Throwable $e) {
            $this->savedStatus = 'error';
            $this->addError('radiusClient', 'Gagal simpan: ' . $e->getMessage());
        }
    }

    public function save(\App\Services\Pengaturan\ConnectionSettingsService $service): void
    {
        if ($this->activeTab === 'radius') {
            $this->saveRadius($service);
        } elseif ($this->activeTab === 'radius_client') {
            $this->saveRadiusClient($service);
        }
    }

    public function render()
    {
        return view('livewire.pengaturan.koneksi.index');
    }
}
