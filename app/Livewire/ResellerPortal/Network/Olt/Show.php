<?php

namespace App\Livewire\ResellerPortal\Network\Olt;

use App\Livewire\AdminComponent;
use App\Models\ISP\Olt;
use App\Models\ISP\Onu;
use Livewire\WithPagination;

class Show extends AdminComponent
{
    use WithPagination;

    public $olt;
    public $search = '';
    
    // For editing ONU Name
    public $editOnuId = null;
    public $editOnuName = '';

    public function mount($id)
    {
        parent::mount();
        $this->olt = Olt::forReseller()->findOrFail($id);
        
        $this->breadcrumbs = [
            ['label' => 'Reseller Portal', 'url' => route('reseller-portal.dashboard')],
            ['label' => 'Network OLT', 'url' => route('reseller-portal.network.olts.index')],
            ['label' => $this->olt->name, 'url' => '#'],
        ];
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }
    
    public function startEditName($id, $currentName)
    {
        $this->editOnuId = $id;
        $this->editOnuName = $currentName;
    }
    
    public function saveName()
    {
        $this->validate([
            'editOnuName' => 'required|string|max:100',
        ]);
        
        $onu = Onu::forReseller()->where('olt_id', $this->olt->id)->findOrFail($this->editOnuId);
        $onu->name = $this->editOnuName;
        $onu->save();
        
        $this->editOnuId = null;
        session()->flash('success', 'Nama pelanggan ONU berhasil diperbarui.');
    }
    
    public function cancelEdit()
    {
        $this->editOnuId = null;
    }

    public function render()
    {
        $onus = Onu::forReseller()
            ->where('olt_id', $this->olt->id)
            ->where(function($q) {
                $q->where('name', 'like', '%' . $this->search . '%')
                  ->orWhere('mac_address', 'like', '%' . $this->search . '%')
                  ->orWhere('pon_port', 'like', '%' . $this->search . '%');
            })
            ->paginate(15);

        return view('livewire.reseller-portal.network.olt.show', [
            'onus' => $onus
        ]);
    }
}
