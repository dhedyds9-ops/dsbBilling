<?php

namespace App\Livewire\Crm\Lead;

use App\Livewire\AdminComponent;
use App\Models\CRM\Lead;

class Show extends AdminComponent
{
    public $leadId;
    public $lead;

    public function mount($id = null)
    {
        parent::mount();
        $this->activeModule = 'crm';
        $this->activePage = 'leads';
        $this->leadId = $id;
        $this->lead = Lead::findOrFail($id);
    }

    public function render()
    {
        $this->lead->loadMissing(['createdBy', 'updatedBy', 'prospect']);

        $timeline = [];
        $activities = [];

        if ($this->lead->created_at) {
            $creator = $this->lead->createdBy?->name ?? 'Sistem';
            $timeline[] = [
                'date' => $this->lead->created_at,
                'title' => 'Lead Dibuat',
                'description' => "Lead didaftarkan oleh {$creator}",
                'type' => 'create',
            ];
            $activities[] = [
                'user' => $creator,
                'action' => 'Mendaftarkan calon lead baru ke sistem',
                'module' => 'CRM',
                'time' => $this->lead->created_at->diffForHumans(),
            ];
        }

        if ($this->lead->updated_at && $this->lead->updated_at->gt($this->lead->created_at)) {
            $updater = $this->lead->updatedBy?->name ?? 'Sistem';
            $statusLabel = ucfirst($this->lead->status ?? 'Updated');
            $timeline[] = [
                'date' => $this->lead->updated_at,
                'title' => 'Status: ' . $statusLabel,
                'description' => "Lead diperbarui oleh {$updater}",
                'type' => 'contact',
            ];
            $activities[] = [
                'user' => $updater,
                'action' => "Memperbarui status lead menjadi {$statusLabel}",
                'module' => 'CRM',
                'time' => $this->lead->updated_at->diffForHumans(),
            ];
        }

        if ($this->lead->prospect) {
            $timeline[] = [
                'date' => $this->lead->prospect->created_at ?? now(),
                'title' => 'Dikonversi ke Prospek',
                'description' => "Lead berhasil dikonversi ke pipeline prospek survey",
                'type' => 'contact',
            ];
        }

        return view('livewire.crm.lead.show', compact('timeline', 'activities'));
    }
}
