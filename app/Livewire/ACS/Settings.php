<?php

namespace App\Livewire\ACS;

use App\Livewire\AdminComponent;
use App\Services\Pengaturan\ConnectionSettingsService;
use App\Models\Setting;

class Settings extends AdminComponent
{
    public array $genieAcsForm = [
        'base_url' => 'http://127.0.0.1:7557',
        'fs_url' => 'http://127.0.0.1:7567',
        'username' => '',
        'password' => '',
        'tr069_port' => 7547,
        'cwmp_version' => '1-0',
        'default_oui' => '',
        'default_product_class' => '',
        'default_software_version' => '',
        'timeout' => 10,
        'retry_count' => 3,
        'ssl_verify' => false,
        'webhook_url' => '',
        'webhook_enabled' => false,
    ];

    public string $genieAcsTestResult = '';

    public function mount()
    {
        parent::mount();
        $this->activeTab = 'genieacs';
        $acsConfig = Setting::getValue('connection.acs', []);
        if (!empty($acsConfig)) {
            $this->genieAcsForm = array_merge($this->genieAcsForm, $acsConfig);
        }
    }

    public function testGenieAcs()
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
        } catch (\Throwable $e) {
            $this->genieAcsTestResult = 'ERROR: ' . $e->getMessage();
        }
    }

    public function saveGenieAcs(ConnectionSettingsService $service)
    {
        try {
            if (empty($this->genieAcsForm['base_url'])) {
                $this->addError('genieAcsForm.base_url', 'Base URL wajib diisi.');
                return;
            }
            
            $service->saveGenieAcsSettings($this->genieAcsForm);
            $this->dispatch('toast', type: 'success', message: 'Konfigurasi GenieACS/TR-069 disimpan.');
        } catch (\Throwable $e) {
            $this->addError('genieAcsForm', 'Gagal simpan: ' . $e->getMessage());
        }
    }

    public function render()
    {
        return view('livewire.acs.settings');
    }
}
