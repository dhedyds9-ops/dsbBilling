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
        'rt' => '',
        'rw' => '',
        'village' => '',
        'district' => '',
        'city' => '',
        'province' => '',
        'postal_code' => '',
        'phone' => '',
        'mobile' => '',
        'email' => '',
        'website' => '',
        'billing_email' => '',
        'support_email' => '',
        'ceo_name' => '',
        'ceo_nik' => '',
        'director_name' => '',
        'finance_name' => '',
        'finance_email' => '',
        'head_noc_name' => '',
        'established_date' => '',
        'operational_hours' => 'Senin - Jumat 08:00 - 17:00',
        'bank_1_name' => '',
        'bank_1_account' => '',
        'bank_1_holder' => '',
        'bank_2_name' => '',
        'bank_2_account' => '',
        'bank_2_holder' => '',
        'bank_3_name' => '',
        'bank_3_account' => '',
        'bank_3_holder' => '',
        'tax_office' => '',
        'signature_name' => '',
        'signature_title' => '',
        'signature_text' => '',
        'logo_url' => '',
        'stamp_url' => '',
        'partner_name' => '',
        'partner_legal_name' => '',
        'partner_npwp' => '',
        'partner_address' => '',
        'partner_rt' => '',
        'partner_rw' => '',
        'partner_village' => '',
        'partner_district' => '',
        'partner_city' => '',
        'partner_province' => '',
        'partner_postal_code' => '',
        'partner_phone' => '',
        'partner_mobile' => '',
        'partner_email' => '',
        'partner_website' => '',
        'partner_logo_url' => '',
    ];

    public $logoFile;
    public $stampFile;
    public $partnerLogoFile;

    public string $savedStatus = '';

    public function mount(): void
    {
        parent::mount();
        $this->activeModule = 'pengaturan';
        $this->activePage = 'perusahaan';

        try {
            $companyService = app(\App\Services\Pengaturan\CompanySettingsService::class);
            $this->company = array_merge($this->company, $companyService->getAll());

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
        if (!$user?->hasRole(\App\Enums\UserRole::Administrator->value) && !\Gate::allows('*') && !\Gate::allows('pengaturan.perusahaan')) {
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
            'company.sppl' => ['nullable', 'string', 'max:100'],
            'company.address' => ['nullable', 'string', 'max:500'],
            'company.rt' => ['nullable', 'string', 'max:10'],
            'company.rw' => ['nullable', 'string', 'max:10'],
            'company.village' => ['nullable', 'string', 'max:100'],
            'company.district' => ['nullable', 'string', 'max:100'],
            'company.city' => ['nullable', 'string', 'max:100'],
            'company.province' => ['nullable', 'string', 'max:100'],
            'company.postal_code' => ['nullable', 'string', 'max:10'],
            'company.phone' => ['nullable', 'string', 'max:50'],
            'company.mobile' => ['nullable', 'string', 'max:50'],
            'company.email' => ['nullable', 'email', 'max:150'],
            'company.website' => ['nullable', 'url', 'max:200'],
            'company.billing_email' => ['nullable', 'email', 'max:150'],
            'company.support_email' => ['nullable', 'email', 'max:150'],
            'company.ceo_name' => ['nullable', 'string', 'max:150'],
            'company.ceo_nik' => ['nullable', 'string', 'max:30'],
            'company.director_name' => ['nullable', 'string', 'max:150'],
            'company.finance_name' => ['nullable', 'string', 'max:150'],
            'company.finance_email' => ['nullable', 'email', 'max:150'],
            'company.head_noc_name' => ['nullable', 'string', 'max:150'],
            'company.established_date' => ['nullable', 'string', 'max:20'],
            'company.operational_hours' => ['nullable', 'string', 'max:200'],
            'company.bank_1_name' => ['nullable', 'string', 'max:100'],
            'company.bank_1_account' => ['nullable', 'string', 'max:50'],
            'company.bank_1_holder' => ['nullable', 'string', 'max:150'],
            'company.bank_2_name' => ['nullable', 'string', 'max:100'],
            'company.bank_2_account' => ['nullable', 'string', 'max:50'],
            'company.bank_2_holder' => ['nullable', 'string', 'max:150'],
            'company.bank_3_name' => ['nullable', 'string', 'max:100'],
            'company.bank_3_account' => ['nullable', 'string', 'max:50'],
            'company.bank_3_holder' => ['nullable', 'string', 'max:150'],
            'company.tax_office' => ['nullable', 'string', 'max:200'],
            'company.signature_name' => ['nullable', 'string', 'max:150'],
            'company.signature_title' => ['nullable', 'string', 'max:100'],
            'company.signature_text' => ['nullable', 'string', 'max:500'],
            'company.partner_name' => ['nullable', 'string', 'max:200'],
            'company.partner_legal_name' => ['nullable', 'string', 'max:200'],
            'company.partner_npwp' => ['nullable', 'string', 'max:30'],
            'company.partner_address' => ['nullable', 'string', 'max:500'],
            'company.partner_rt' => ['nullable', 'string', 'max:10'],
            'company.partner_rw' => ['nullable', 'string', 'max:10'],
            'company.partner_village' => ['nullable', 'string', 'max:100'],
            'company.partner_district' => ['nullable', 'string', 'max:100'],
            'company.partner_city' => ['nullable', 'string', 'max:100'],
            'company.partner_province' => ['nullable', 'string', 'max:100'],
            'company.partner_postal_code' => ['nullable', 'string', 'max:10'],
            'company.partner_phone' => ['nullable', 'string', 'max:50'],
            'company.partner_mobile' => ['nullable', 'string', 'max:50'],
            'company.partner_email' => ['nullable', 'email', 'max:150'],
            'company.partner_website' => ['nullable', 'url', 'max:200'],
            'logoFile' => ['nullable', 'image', 'max:2048'],
            'stampFile' => ['nullable', 'image', 'max:2048'],
            'partnerLogoFile' => ['nullable', 'image', 'max:2048'],
        ];
    }

        public function messages(): array
    {
        return [
            'logoFile.image' => 'Logo utama harus berupa file gambar (JPG, PNG, GIF, WebP).',
            'logoFile.max' => 'Ukuran file logo utama tidak boleh lebih dari 2MB.',
            'logoFile.uploaded' => 'Sistem server menolak file logo utama. Kemungkinan karena setelan web server (Nginx client_max_body_size) atau PHP (upload_max_filesize) terlalu kecil, atau folder temporary penuh.',
            
            'stampFile.image' => 'Cap/Stempel harus berupa file gambar (JPG, PNG, GIF, WebP).',
            'stampFile.max' => 'Ukuran file Cap/Stempel tidak boleh lebih dari 2MB.',
            'stampFile.uploaded' => 'Sistem server menolak file Cap/Stempel. Periksa kapasitas atau izin folder temporary server.',
            
            'partnerLogoFile.image' => 'Logo mitra harus berupa file gambar (JPG, PNG, GIF, WebP).',
            'partnerLogoFile.max' => 'Ukuran file logo mitra tidak boleh lebih dari 2MB.',
            'partnerLogoFile.uploaded' => 'Sistem server menolak file logo mitra. Periksa kapasitas atau izin folder temporary server.',
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
            if ($this->partnerLogoFile) {
                $path = $this->partnerLogoFile->storePublicly('company/partner', 'public');
                $this->company['partner_logo_url'] = Storage::url($path);
            }

            app(\App\Services\Pengaturan\CompanySettingsService::class)->save($this->company);

            $this->savedStatus = 'saved';
            $this->dispatch('toast', type: 'success', message: 'Data Perusahaan berhasil disimpan.');
        } catch (\Throwable $e) {
            $this->savedStatus = 'error';
            $this->dispatch('toast', type: 'error', message: 'Gagal menyimpan: ' . $e->getMessage());
            $this->addError('company', 'Gagal menyimpan: ' . $e->getMessage());
        }
    }

    public function resetLogo(): void
    {
        $this->company['logo_url'] = '';
        app(\App\Services\Pengaturan\CompanySettingsService::class)->save(['logo_url' => '']);
        session()->flash('info', 'Logo di-reset ke default.');
    }

    public function resetStamp(): void
    {
        $this->company['stamp_url'] = '';
        app(\App\Services\Pengaturan\CompanySettingsService::class)->save(['stamp_url' => '']);
        session()->flash('info', 'Stamp di-reset.');
    }

    public function resetPartnerLogo(): void
    {
        $this->company['partner_logo_url'] = '';
        app(\App\Services\Pengaturan\CompanySettingsService::class)->save(['partner_logo_url' => '']);
        session()->flash('info', 'Logo Mitra di-reset.');
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
