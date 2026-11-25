<?php

namespace App\Livewire\Isp\HotspotUser;

use App\Livewire\AdminComponent;
use App\Models\ISP\HotspotUser;

class Show extends AdminComponent
{
    public $hotspotUserId;
    public $hotspotUser;

    public $showEditDueDateModal = false;
    public $edit_due_date;
    public $edit_due_date_reason;

    public function mount($id = null)
    {
        parent::mount();
        $this->activeModule = 'isp';
        $this->activePage = 'hotspot-users';
        $this->hotspotUserId = $id;
        $this->hotspotUser = HotspotUser::with(['customerService', 'serviceProfile', 'voucherPool', 'createdBy', 'updatedBy'])->findOrFail($id);
        $this->breadcrumbs = [
            ['label' => 'Dashboard', 'url' => route('dashboard')],
            ['label' => 'ISP', 'url' => route('isp.hotspot-users.index')],
            ['label' => 'Hotspot Users', 'url' => route('isp.hotspot-users.index')],
            ['label' => $this->hotspotUser->username],
        ];
    }

    public function openEditDueDateModal()
    {
        $dueDateService = app(\App\Services\Billing\CustomerBillingDueDateService::class);
        $this->edit_due_date = $this->hotspotUser->customerService ? $dueDateService->getDueDate($this->hotspotUser->customerService) : null;
        $this->edit_due_date_reason = '';
        $this->resetErrorBag();
        $this->showEditDueDateModal = true;
    }

    public function closeEditDueDateModal()
    {
        $this->showEditDueDateModal = false;
        $this->resetErrorBag();
    }

    public function saveDueDate()
    {
        $this->validate([
            'edit_due_date' => 'required|date',
            'edit_due_date_reason' => 'nullable|string|max:255',
        ]);

        if (!$this->hotspotUser->customerService) {
            $this->dispatch('toast', type: 'error', message: 'Layanan pelanggan tidak ditemukan.');
            return;
        }

        try {
            $dueDateService = app(\App\Services\Billing\CustomerBillingDueDateService::class);
            $dueDateService->updateDueDate(
                $this->hotspotUser->customerService,
                $this->edit_due_date,
                $this->edit_due_date_reason ?: 'Diperbarui melalui halaman Detail Hotspot User',
                auth()->id()
            );

            $this->hotspotUser->load('customerService');
            $this->showEditDueDateModal = false;
            $this->dispatch('toast', type: 'success', message: 'Tanggal jatuh tempo berhasil diperbarui!');
        } catch (\Illuminate\Auth\Access\AuthorizationException $e) {
            $this->addError('edit_due_date', $e->getMessage());
            $this->dispatch('toast', type: 'error', message: $e->getMessage());
        } catch (\Throwable $e) {
            $this->dispatch('toast', type: 'error', message: 'Gagal memperbarui: ' . $e->getMessage());
        }
    }

    public function render()
    {
        return view('livewire.isp.hotspot-user.show');
    }
}
