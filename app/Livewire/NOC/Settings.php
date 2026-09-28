<?php

namespace App\Livewire\NOC;

use App\Livewire\AdminComponent;
use App\Models\Setting;
use App\Services\ZabbixService;
use Livewire\Attributes\Layout;

#[Layout('layouts.noc')]
class Settings extends AdminComponent
{
    public string $zabbixUrl = 'http://127.0.0.1/zabbix/api_jsonrpc.php';
    public string $zabbixUsername = 'Admin';
    public string $zabbixPassword = 'zabbix';

    public function mount()
    {
        parent::mount();
        $this->activeModule = 'noc';
        $this->activePage = 'settings';

        $this->zabbixUrl = Setting::getValue('zabbix.url', 'http://127.0.0.1/zabbix/api_jsonrpc.php');
        $this->zabbixUsername = Setting::getValue('zabbix.username', 'Admin');
        $this->zabbixPassword = Setting::getValue('zabbix.password', 'zabbix');
    }

    public function save()
    {
        $this->validate([
            'zabbixUrl' => 'required|url',
            'zabbixUsername' => 'required|string',
            'zabbixPassword' => 'required|string',
        ]);

        Setting::setValue('zabbix.url', $this->zabbixUrl);
        Setting::setValue('zabbix.username', $this->zabbixUsername);
        Setting::setValue('zabbix.password', $this->zabbixPassword);

        session()->flash('success', 'Pengaturan Zabbix berhasil disimpan.');
    }

    public function testConnection()
    {
        // Save current form values temporarily to test them (without overriding DB yet, or just override DB)
        $this->save();

        $service = new ZabbixService();
        if ($service->testConnection()) {
            session()->flash('success', 'Koneksi ke Zabbix API Berhasil!');
        } else {
            session()->flash('error', 'Koneksi ke Zabbix API Gagal. Periksa URL dan Kredensial.');
        }
    }

    public function render()
    {
        return view('livewire.noc.settings');
    }
}