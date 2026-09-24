<?php

namespace App\Livewire\Pengaturan\License;

use App\Livewire\AdminComponent;
use App\Services\License\LicenseManager;

class Index extends AdminComponent
{
    public string $activeModule = 'pengaturan';
    public string $activePage = 'lisensi';

    public string $licenseKey = '';
    public string $hardwareId = '';
    public bool $isValid = false;

    public function mount()
    {
        parent::mount();
        
        $licenseManager = app(LicenseManager::class);
        $this->hardwareId = $licenseManager->getHardwareId();
        $this->licenseKey = config('app.license_key') ?? '';
        $this->isValid = $licenseManager->isValid();
        
        $this->breadcrumbs = [
            ['label' => 'Dashboard', 'url' => route('dashboard')],
            ['label' => 'Pengaturan', 'url' => '#'],
            ['label' => 'Aktivasi Lisensi', 'url' => route('pengaturan.license.index')],
        ];
    }

    public function activate(LicenseManager $licenseManager)
    {
        $this->validate([
            'licenseKey' => 'required|min:10'
        ], [
            'licenseKey.required' => 'License Key wajib diisi.',
            'licenseKey.min' => 'Format License Key tidak valid.'
        ]);

        try {
            $licenseManager->saveLicenseKey($this->licenseKey);
            
            if ($licenseManager->isValid()) {
                $this->isValid = true;
                session()->flash('success', 'Lisensi berhasil diverifikasi dan diaktifkan!');
                return redirect()->route('dashboard');
            } else {
                $this->addError('licenseKey', 'Lisensi tidak valid atau belum terdaftar di sistem kami.');
            }
        } catch (\Exception $e) {
            $this->addError('licenseKey', 'Terjadi kesalahan saat memverifikasi lisensi: ' . $e->getMessage());
        }
    }

    public function render()
    {
        return view('livewire.pengaturan.license.index');
    }
}
