<?php

namespace App\Livewire\ISP\ServiceProfile;

use App\Livewire\AdminComponent;
use App\Models\ISP\ServiceProfile as ServiceProfileModel;
use App\Services\ISP\ServiceProfileService;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Log;

class Create extends AdminComponent
{
    // Main fields
    public $code = '';
    public $name = '';
    public $description = '';
    public $service_type = 'pppoe';
    public $status = 'active';
    
    // Package type
    public $package_type = 'unlimited'; // unlimited, time_based, quota_based
    public $duration_value = 30;
    public $duration_unit = 'days'; // hours, days
    public $quota_value = 100;
    public $quota_unit = 'GB'; // MB, GB
    
    // Bandwidth
    public $download_speed = 10;
    public $upload_speed = 10;
    
    // Burst Settings
    public $enable_burst = false;
    public $burst_limit_download;
    public $burst_limit_upload;
    public $burst_threshold_download;
    public $burst_threshold_upload;
    public $burst_time_download = 60;
    public $burst_time_upload = 60;

    // Billing
    public $base_price = 150000;
    public $owner_price = 0;
    public $reseller_price = 120000;
    public $is_free = false;
    
    // Validity
    public $validity_value = 30;
    public $validity_unit = 'days'; // days, months, hours
    
    // Login
    public $max_devices = 1;
    
    // Visibility
    public $visibility = 'private';
    public $owner_id;
    
    // Advanced
    public $tax_enabled = false;
    public $tax_percentage = 11;
    public $technical_notes = '';
    public $login_start_time = '00:00';
    public $login_end_time = '23:59';
    
    // Voucher
    public $voucher_prefix = '';
    public $voucher_validity_after_activation = 30;

    public function mount()
    {
        parent::mount();

        Gate::authorize('create', ServiceProfileModel::class);

        $this->activeModule = 'isp';
        $this->activePage = 'service-profiles';
        $this->breadcrumbs = [
            ['label' => 'Dashboard', 'url' => route('dashboard')],
            ['label' => 'Paket Internet', 'url' => route('isp.service-profiles.index')],
            ['label' => 'Tambah Paket'],
        ];
    }

    public function updated($property)
    {
        if ($property === 'is_free' && $this->is_free) {
            $this->base_price = 0;
            $this->owner_price = 0;
            $this->reseller_price = 0;
        }

        if ($property === 'enable_burst' && $this->enable_burst) {
            $this->calculateAutoBurst();
        }
    }

    public function calculateAutoBurst()
    {
        // Auto hitung burst: Limit = 2x Speed, Threshold = 80% Speed
        if ($this->download_speed) {
            $this->burst_limit_download = $this->download_speed * 2;
            $this->burst_threshold_download = round($this->download_speed * 0.8);
        }
        
        if ($this->upload_speed) {
            $this->burst_limit_upload = $this->upload_speed * 2;
            $this->burst_threshold_upload = round($this->upload_speed * 0.8);
        }

        $this->burst_time_download = 60;
        $this->burst_time_upload = 60;
    }

    public function save(ServiceProfileService $service)
    {
        Log::info('Create service profile: Starting process', ['user_id' => auth()->id()]);
        
        // Auto generate code - handled in ServiceProfileService
        // Pastikan visibility selalu punya nilai valid
        if (empty($this->visibility) || !in_array($this->visibility, ['private', 'shared', 'global'])) {
            $this->visibility = 'private';
        }

        $this->validate($this->rules(), [
            'name.required' => 'Nama paket harus diisi',
            'name.max' => 'Nama paket tidak boleh lebih dari 255 karakter',
            'service_type.required' => 'Jenis service harus dipilih',
            'status.required' => 'Status harus dipilih',
            'download_speed.required' => 'Download speed harus diisi',
            'download_speed.min' => 'Download speed minimal 1',
            'upload_speed.required' => 'Upload speed harus diisi',
            'upload_speed.min' => 'Upload speed minimal 1',
            'base_price.required' => 'Harga jual harus diisi',
            'base_price.min' => 'Harga tidak boleh negatif',
            'owner_price.required' => 'Harga owner harus diisi',
            'owner_price.min' => 'Harga tidak boleh negatif',
            'reseller_price.required' => 'Harga reseller harus diisi',
            'reseller_price.min' => 'Harga tidak boleh negatif',
            'validity_value.required' => 'Masa aktif harus diisi',
            'max_devices.required' => 'Shared user harus diisi',
            'duration_value.required' => 'Durasi harus diisi untuk paket time based',
            'quota_value.required' => 'Kuota harus diisi untuk paket quota based',
        ]);
        
        Log::info('Create service profile: Validation passed');

        try {
            $data = $this->only([
                'name', 'description',
                'service_type', 'status', 'download_speed', 'upload_speed',
                'base_price', 'owner_price', 'reseller_price', 'is_free',
                'validity_value', 'validity_unit', 
                'package_type', 'duration_value', 'duration_unit', 
                'quota_value', 'quota_unit',
                'max_devices',
                'visibility', 'owner_id',
                'tax_enabled', 'tax_percentage', 'technical_notes',
                'login_start_time', 'login_end_time',
                'voucher_prefix', 'voucher_validity_after_activation',
            ]);

            // Sertakan burst hanya jika diaktifkan
            if ($this->enable_burst) {
                $data['burst_limit_download'] = $this->burst_limit_download;
                $data['burst_limit_upload'] = $this->burst_limit_upload;
                $data['burst_threshold_download'] = $this->burst_threshold_download;
                $data['burst_threshold_upload'] = $this->burst_threshold_upload;
                $data['burst_time_download'] = $this->burst_time_download;
                $data['burst_time_upload'] = $this->burst_time_upload;
            } else {
                // Reset burst jika tidak diaktifkan
                $data['burst_limit_download'] = null;
                $data['burst_limit_upload'] = null;
                $data['burst_threshold_download'] = null;
                $data['burst_threshold_upload'] = null;
                $data['burst_time_download'] = null;
                $data['burst_time_upload'] = null;
            }

            Log::info('Create service profile: Calling service layer', ['data' => $data]);
            
            $profile = $service->createProfile($data, auth()->user());

            Log::info('Create service profile: Success', ['profile_id' => $profile->id]);

            session()->flash('success', 'Paket berhasil dibuat');
            return redirect()->route('isp.service-profiles.index');
            
        } catch (\Exception $e) {
            Log::error('Create service profile: Failed', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'user_id' => auth()->id(),
            ]);
            
            $this->addError('general', $e->getMessage());
            session()->flash('error', 'Terjadi kesalahan saat menyimpan paket: ' . $e->getMessage());
        }
    }
    
    protected function rules()
    {
        $rules = [
            'name' => 'required|string|max:255',
            'service_type' => 'required|in:pppoe,hotspot,voucher',
            'status' => 'required|in:active,inactive',
            'download_speed' => 'required|integer|min:1|max:10000',
            'upload_speed' => 'required|integer|min:1|max:10000',
            'package_type' => 'required|in:unlimited,time_based,quota_based',
            'base_price' => 'required|numeric|min:0',
            'validity_value' => 'nullable|integer|min:1',
            'validity_unit' => 'nullable|in:days,months,hours',
            'max_devices' => 'nullable|integer|min:1|max:10',
            'visibility' => 'nullable|sometimes|in:private,shared,global',
        ];
        
        // Validate duration if time_based
        if ($this->package_type === 'time_based') {
            $rules['duration_value'] = 'required|integer|min:1';
            $rules['duration_unit'] = 'required|in:hours,days';
        }
        
        // Validate quota if quota_based
        if ($this->package_type === 'quota_based') {
            $rules['quota_value'] = 'required|integer|min:1';
            $rules['quota_unit'] = 'required|in:MB,GB';
        }
        
        if (auth()->user()->hasRole(\App\Enums\UserRole::Administrator->value)) {
            $rules['owner_price'] = 'required|numeric|min:0';
            $rules['reseller_price'] = 'required|numeric|min:0';
        }
        
        return $rules;
    }
    
    public function render()
    {
        $isAdministrator = auth()->user()->hasRole(\App\Enums\UserRole::Administrator->value) ?? false;
        $users = $isAdministrator
            ? \App\Models\User::select('id', 'name')
                ->whereHas('roles', fn($q) => $q->whereIn('name', \App\Enums\UserRole::backofficeRoles()))
                ->orderBy('name')
                ->limit(100)
                ->get()
            : [];
        
        return view('livewire.isp.service-profile.form', compact('isAdministrator', 'users'));
    }
}
