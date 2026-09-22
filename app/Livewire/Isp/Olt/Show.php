<?php

namespace App\Livewire\Isp\Olt;

use App\Livewire\AdminComponent;
use App\Models\ISP\Olt as OltModel;

class Show extends AdminComponent
{
    public $oltId;
    public $olt;

    public function mount($id = null)
    {
        parent::mount();
        $this->activeModule = 'isp';
        $this->activePage = 'olts';
        $this->oltId = $id;
        $this->olt = OltModel::with(['pop', 'vendor', 'ponPorts', 'onus'])->findOrFail($id);
        $this->breadcrumbs = [
            ['label' => 'Dashboard', 'url' => route('dashboard')],
            ['label' => 'Network', 'url' => route('isp.olts.index')],
            ['label' => 'OLT', 'url' => route('isp.olts.index')],
            ['label' => $this->olt->name],
        ];
    }

        public function testConnection()
    {
        try {
            $driver = $this->olt->driver();
            $sysInfo = $driver->getSystemInfo();

            $isOffline = ($sysInfo['status'] ?? 'offline') === 'offline' || $sysInfo['uptime'] === 'N/A';

            if ($isOffline) {
                $reason = $sysInfo['error'] ?? 'Tidak ada respons SNMP.';
                throw new \Exception("SNMP Timeout/Ditolak. {$reason}");
            }

            session()->flash('success', 'Test Koneksi Berhasil! Uptime: ' . $sysInfo['uptime']);
        } catch (\Throwable $th) {
            $safeMsg = str_replace(["\r", "\n"], ' A ', $th->getMessage());
            session()->flash('error', 'Test Koneksi Gagal: ' . $safeMsg);
        }
    }

    public function syncOlt()
    {
        try {
            $service = app(\App\Services\ISP\OltPollingService::class);
            $service->pollOlt($this->olt);
            $this->olt->refresh();
            $this->olt->load(['ponPorts', 'onus']);
            session()->flash('success', 'Berhasil sinkronisasi dengan OLT.');
        } catch (\Exception $e) {
            session()->flash('error', 'Gagal sinkronisasi: ' . $e->getMessage());
        }
    }

    public function render()
    {
        return view('livewire.isp.olt.show');
    }
}



