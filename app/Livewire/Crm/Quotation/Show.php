<?php

namespace App\Livewire\Crm\Quotation;

use App\Livewire\AdminComponent;
use App\Models\CRM\Quotation;

class Show extends AdminComponent
{
    public $quotationId;
    public $quotation;

    public function mount($id = null)
    {
        parent::mount();
        $this->activeModule = 'crm';
        $this->activePage = 'quotations';
        $this->quotationId = $id;
        $this->quotation = Quotation::findOrFail($id);
    }

    public function render()
    {
        $timeline = [['date' => now(), 'title' => 'Quotation Dibuat', 'description' => 'Quotation baru ditambahkan', 'type' => 'create']];
        $activities = [['user' => 'Admin', 'action' => 'Membuat quotation baru', 'module' => 'CRM', 'time' => '2 jam lalu']];
        return view('livewire.crm.quotation.show', compact('timeline', 'activities'));
    }
}
