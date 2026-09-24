<?php

namespace App\Livewire\Isp\ServiceProfile;

use App\Livewire\AdminComponent;
use App\Models\ISP\ServiceProfile as ServiceProfileModel;
use App\Services\ISP\ServiceProfileService;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Log;

class Edit extends AdminComponent
{
    public $profileId;
    public $profile;

    // Main fields
    public $code = '';
    public $name = '';
    public $description = '';
    public $service_type = 'pppoe';
    public $status = 'active';
    
    // Package type
    public $package_type = 'unlimited'; // unlimited, time_based, quota_based
    public $duration_value = 2;
    public $duration_unit = 'hours'; // hours, days
    public $quota_value = 5;
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
    public $base_price = 0;
    public $owner_price = 0;
    public $reseller_price = 0;
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
    public $showAdvanced = false;
    public $tax_enabled = false;
    public $tax_percentage = 0;
    public $technical_notes = '';
    public $login_start_time = '00:00';
    public $login_end_time = '23:59';

    public function mount($id = null)
    {
        parent::mount();
        $this->activeModule = 'isp';
        $this->activePage = 'service-profiles';
        $this->profileId = $id;
        $this->profile = ServiceProfileModel::findOrFail($id);

        Gate::authorize('update', $this->profile);

        $this->code = $this->profile->code;
        $this->name = $this->profile->name;
        $this->description = $this->profile->description ?? '';
        $this->service_type = $this->profile->service_type;
        $this->status = $this->profile->status;
        $this->download_speed = $this->profile->download_speed;
        $this->upload_speed = $this->profile->upload_speed;
        $this->base_price = $this->profile->base_price;
        $this->owner_price = $this->profile->owner_price ?? 0;
        $this->reseller_price = $this->profile->reseller_price ?? 0;
        $this->is_free = $this->profile->is_free ?? false;
        
        // Package type
        $this->package_type = $this->profile->package_type ?? 'unlimited';
        $this->duration_value = $this->profile->duration_value ?? 2;
        $this->duration_unit = $this->profile->duration_unit ?? 'hours';
        $this->quota_value = $this->profile->quota_value ?? 5;
        $this->quota_unit = $this->profile->quota_unit ?? 'GB';
        
        // Set validity value and unit
        $this->validity_unit = $this->profile->validity_unit ?? 'days';
        if ($this->validity_unit === 'days') {
            $this->validity_value = $this->profile->validity_days ?? 30;
        } elseif ($this->validity_unit === 'hours') {
            $this->validity_value = $this->profile->validity_hours ?? 30;
        } else {
            $this->validity_value = floor(($this->profile->validity_days ?? 30) / 30);
        }
        
        $this->max_devices = $this->profile->max_devices ?? 1;
        $this->visibility = $this->profile->visibility ?? 'private';
        $this->owner_id = $this->profile->owner_id;
        
        // Advanced fields
        $this->tax_enabled = $this->profile->tax_enabled ?? false;
        $this->tax_percentage = $this->profile->tax_percentage ?? 0;
        $this->technical_notes = $this->profile->technical_notes ?? '';
        $this->login_start_time = $this->profile->login_start_time ?? '00:00';
        $this->login_end_time = $this->profile->login_end_time ?? '23:59';

        // Burst fields
        $this->burst_limit_download = $this->profile->burst_limit_download;
        $this->burst_limit_upload = $this->profile->burst_limit_upload;
        $this->burst_threshold_download = $this->profile->burst_threshold_download;
        $this->burst_threshold_upload = $this->profile->burst_threshold_upload;
        $this->burst_time_download = $this->profile->burst_time_download ?? 60;
        $this->burst_time_upload = $this->profile->burst_time_upload ?? 60;
        $this->enable_burst = !empty($this->profile->burst_limit_download);


        $this->breadcrumbs = [
            ['label' => 'Dashboard', 'url' => route('dashboard')],
            ['label' => 'Paket Internet', 'url' => route('isp.service-profiles.index')],
            ['label' => $this->profile->name, 'url' => '#'],
            ['label' => 'Edit'],
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
        Log::info('Update service profile: Starting process', ['user_id' => auth()->id(), 'profile_id' => $this->profileId]);
        
        // Pastikan visibility selalu punya nilai valid
        if (empty($this->visibility) || !in_array($this->visibility, ['private', 'shared', 'global'])) {
            $this->visibility = 'private';
        }

        $this->validate($this->rules(), [
            'name.required' => 'Nama paket harus diisi',
            'name.max' => 'Nama paket tidak boleh lebih dari 255 karakter',
            'service_type.required' => 'Jenis paket harus dipilih',
            'status.required' => 'Status harus dipilih',
            'download_speed.required' => 'Download speed harus diisi',
            'download_speed.min' => 'Download speed minimal 1',
            'upload_speed.required' => 'Upload speed harus diisi',
            'upload_speed.min' => 'Upload speed minimal 1',
            'base_price.required' => 'Harga harus diisi',
            'base_price.min' => 'Harga tidak boleh negatif',
            'validity_value.required' => 'Masa aktif harus diisi',
            'max_devices.required' => 'Shared user harus diisi',
            'duration_value.required' => 'Durasi harus diisi untuk paket time based',
            'quota_value.required' => 'Kuota harus diisi untuk paket quota based',
        ]);

        Log::info('Update service profile: Validation passed');

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
                $data['burst_limit_download'] = null;
                $data['burst_limit_upload'] = null;
                $data['burst_threshold_download'] = null;
                $data['burst_threshold_upload'] = null;
                $data['burst_time_download'] = null;
                $data['burst_time_upload'] = null;
            }

            Log::info('Update service profile: Calling service layer', ['data' => $data]);
            
            $service->updateProfile($this->profile, $data, auth()->user());

            Log::info('Update service profile: Success');

            session()->flash('success', 'Paket Internet berhasil diperbarui!');
            return redirect()->route('isp.service-profiles.index');
            
        } catch (\Exception $e) {
            Log::error('Update service profile: Failed', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'user_id' => auth()->id(),
                'profile_id' => $this->profileId,
            ]);
            
            $this->addError('general', $e->getMessage());
            session()->flash('error', 'Gagal memperbarui paket: ' . $e->getMessage());
        }
    }
    
    protected function rules()
    {
        $rules = [
            'name' => 'required|string|max:255',
            'service_type' => 'required|in:pppoe,hotspot,voucher,ftth,combined',
            'status' => 'required|in:active,inactive',
            'download_speed' => 'required|integer|min:1|max:10000',
            'upload_speed' => 'required|integer|min:1|max:10000',
            'base_price' => 'required|numeric|min:0',
            'validity_value' => 'required|integer|min:1',
            'validity_unit' => 'required|in:days,months,hours',
            'max_devices' => 'required|integer|min:1',
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

