<?php

namespace App\Livewire\Pengaturan\Perusahaan;

use App\Livewire\AdminComponent;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Livewire\WithFileUploads;

class Index extends AdminComponent
{
    use WithFileUploads;

    public string $activeModule = 'pengaturan';
    public string $activePage = 'perusahaan';

    public array $company = [
        'name' => '',
        'legal_name' => '',
        'npwp' => '',
        'nib' => '',
        'siup' => '',
        'sppl' => '',
        'address' => '',
        'province' => '',
        'city' => '',
        'district' => '',
        'village' => '',
        'postal_code' => '',
        'phone' => '',
        'mobile' => '',
        'email' => '',
        'website' => '',
        'ceo_name' =>
        '',
        'ceo_nik' => '',
        'director_name' => '',
        'finance_name' => '',
        'finance_email' => '',
        'billing_email' => '',
        'support_email' => '',
        'logo_url' => '',
        'stamp_url' => '',
        'signature_name' => '',
        'signature_title' => '',
        'bank_name' => '',
        'bank_account' => '',
        'bank_holder' => '',
        'tax_office' => '',
        'established_date' => '',
        'operational_hours' => 'Senin - Jumat 08:00 - 17:00',
    ];

    public $logoFile;
    public $stampFile;

    public string $savedStatus = '';

    public function mount(): void
    {
        parent::mount();
        $this->activeModule = 'pengaturan';
        $this->activePage = 'perusahaan';

        try {
            $defaults = [
                'name' => config('app.name', 'dsBilling Enterprise'),
                'email' => config('mail.from.address', 'info@dsbilling.id'),
                'phone' => '(021) 0000-0000',
                'established_date' => date('Y-m-d', strtotime('-5 years')),
            ];
            foreach ($defaults as $k => $v) {
                if (empty($this->company[$k])) $this->company[$k] = (string)$v;
            }
        } catch (\Throwable) {
        }
    }

    public function authorizeAccess(): void
    {
        if (!Auth::check()) abort(403);
        $user = Auth::user();
        if (!$user?->hasRole('super_admin') && !$user?->hasRole('admin') && !\Gate::allows('*') && !\Gate::allows('pengaturan.perusahaan')) {
            abort(403);
        }
    }

    public function boot(): void
    {
        $this->authorizeAccess();
    }

    public function rules(): array
    {
        return [
            'company.name' => ['required', 'string', 'max:200'],
            'company.legal_name' => ['nullable', 'string', 'max:200'],
            'company.npwp' => ['nullable', 'string', 'max:30'],
            'company.nib' => ['nullable', 'string', 'max:30'],
            'company.siup' => ['nullable', 'string', 'max:50'],
            'company.phone' => ['nullable', 'string', 'max:50'],
            'company.mobile' => ['nullable', 'string', 'max:50'],
            'company.email' => ['nullable', 'email', 'max:150'],
            'company.website' => ['nullable', 'url', 'max:200'],
            'company.address' => ['nullable', 'string', 'max:500'],
            'company.postal_code' => ['nullable', 'string', 'max:10'],
            'company.bank_account' => ['nullable', 'string', 'max:50'],
            'logoFile' => ['nullable', 'image', 'max:2048'],
            'stampFile' => ['nullable', 'image', 'max:2048'],
        ];
    }

    public function save(): void
    {
        $valid = $this->validate();
        try {
            if ($this->logoFile) {
                $path = $this->logoFile->storePublicly('company/logo', 'public');
                $this->company['logo_url'] = Storage::url($path);
            }
            if ($this->stampFile) {
                $path = $this->stampFile->storePublicly('company/stamp', 'public');
                $this->company['stamp_url'] = Storage::url($path);
            }
            $this->savedStatus = 'success';
            session()->flash('success', 'Data Perusahaan berhasil disimpan.');
        } catch (\Throwable $e) {
            $this->savedStatus = 'error';
            $this->addError('company', 'Gagal menyimpan: ' . $e->getMessage());
        }
    }

    public function resetLogo(): void
    {
        $this->company['logo_url'] = '';
        session()->flash('info', 'Logo di-reset ke default.');
    }

    public function resetStamp(): void
    {
        $this->company['stamp_url'] = '';
        session()->flash('info', 'Stamp di-reset.');
    }

    public function testEmail(): void
    {
        try {
            $to = $this->company['billing_email'] ?: $this->company['email'];
            if (!$to) {
                $this->addError('company.email', 'Isi dulu alamat email tujuan.');
                return;
            }
            session()->flash('info', 'Test email ke ' . $to . ' dikirim (demo).');
        } catch (\Throwable $e) {
            $this->addError('company', 'Gagal kirim test email: ' . $e->getMessage());
        }
    }

    public function render()
    {
        return view('livewire.pengaturan.perusahaan.index');
    }
}
