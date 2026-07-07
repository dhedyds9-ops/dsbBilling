<?php

namespace App\Livewire\ISP\ServiceProfile;

use App\Livewire\AdminComponent;
use App\Models\AuditLog;
use App\Models\ISP\ServiceProfile as ServiceProfileModel;
use App\Services\ISP\ServiceProfileService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class Show extends AdminComponent
{
    public $profileId;
    public $profile;
    public $auditLogs;
    public $showCloneModal = false;
    public $cloneOptions = [
        'name' => true,
        'description' => true,
        'service_type' => true,
        'package_type' => true,
        'bandwidth' => true,
        'prices' => true,
        'validity' => true,
        'max_devices' => true,
        'technical' => true,
    ];
    public $cloneName = '';

    public function mount($id = null)
    {
        parent::mount();
        $this->activeModule = 'isp';
        $this->activePage = 'service-profiles';
        $this->profileId = $id;
        $this->profile = ServiceProfileModel::with(['serviceProfileType', 'createdBy', 'updatedBy'])->findOrFail($id);
        $this->auditLogs = AuditLog::where('auditable_type', ServiceProfileModel::class)
            ->where('auditable_id', $id)
            ->latest()
            ->get();
        $this->cloneName = $this->profile->name . ' (Copy)';
        $this->breadcrumbs = [
            ['label' => 'Dashboard', 'url' => route('dashboard')],
            ['label' => 'Network', 'url' => route('isp.service-profiles.index')],
            ['label' => 'Paket Internet', 'url' => route('isp.service-profiles.index')],
            ['label' => $this->profile->name],
        ];
    }

    public function toggleStatus()
    {
        $service = app(ServiceProfileService::class);
        $user = Auth::user();
        
        try {
            $newStatus = $this->profile->status === 'active' ? 'inactive' : 'active';
            $service->updateProfile($this->profile, ['status' => $newStatus], $user);
            
            $this->profile->refresh();
            $this->auditLogs = AuditLog::where('auditable_type', ServiceProfileModel::class)
                ->where('auditable_id', $this->profileId)
                ->latest()
                ->get();
                
            session()->flash('success', 'Status paket berhasil diubah!');
        } catch (\Exception $e) {
            Log::error('Toggle status failed', ['error' => $e->getMessage()]);
            session()->flash('error', 'Gagal mengubah status: ' . $e->getMessage());
        }
    }

    public function openCloneModal()
    {
        $this->showCloneModal = true;
    }

    public function closeCloneModal()
    {
        $this->showCloneModal = false;
        $this->cloneName = $this->profile->name . ' (Copy)';
        $this->cloneOptions = [
            'name' => true,
            'description' => true,
            'service_type' => true,
            'package_type' => true,
            'bandwidth' => true,
            'prices' => true,
            'validity' => true,
            'max_devices' => true,
            'technical' => true,
        ];
    }

    public function cloneProfile()
    {
        $service = app(ServiceProfileService::class);
        $user = Auth::user();
        
        try {
            $newData = [];
            
            // Always set name
            $newData['name'] = $this->cloneName;
            $newData['status'] = 'inactive';
            
            // Copy selected fields
            if ($this->cloneOptions['description']) $newData['description'] = $this->profile->description;
            if ($this->cloneOptions['service_type']) $newData['service_type'] = $this->profile->service_type;
            if ($this->cloneOptions['package_type']) {
                $newData['package_type'] = $this->profile->package_type;
                $newData['duration_value'] = $this->profile->duration_value;
                $newData['duration_unit'] = $this->profile->duration_unit;
                $newData['quota_value'] = $this->profile->quota_value;
                $newData['quota_unit'] = $this->profile->quota_unit;
            }
            if ($this->cloneOptions['bandwidth']) {
                $newData['download_speed'] = $this->profile->download_speed;
                $newData['upload_speed'] = $this->profile->upload_speed;
            }
            if ($this->cloneOptions['prices']) {
                $newData['base_price'] = $this->profile->base_price;
                $newData['owner_price'] = $this->profile->owner_price;
                $newData['reseller_price'] = $this->profile->reseller_price;
                $newData['is_free'] = $this->profile->is_free;
            }
            if ($this->cloneOptions['validity']) {
                $newData['validity_value'] = $this->profile->validity_days ?: ($this->profile->validity_hours ?: 30);
                $newData['validity_unit'] = $this->profile->validity_unit ?: 'days';
            }
            if ($this->cloneOptions['max_devices']) $newData['max_devices'] = $this->profile->max_devices;
            
            $newProfile = $service->createProfile($newData, $user);
            
            session()->flash('success', 'Paket berhasil di-duplikasi!');
            $this->closeCloneModal();
            
            return redirect()->route('isp.service-profiles.show', $newProfile->id);
        } catch (\Exception $e) {
            Log::error('Clone profile failed', ['error' => $e->getMessage()]);
            session()->flash('error', 'Gagal menduplikasi paket: ' . $e->getMessage());
        }
    }

    public function render()
    {
        return view('livewire.isp.service-profiles.detail');
    }
}
