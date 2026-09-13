<?php
namespace App\Livewire\ResellerPortal;

use App\Livewire\AdminComponent;

class ComingSoon extends AdminComponent
{
    public $pageName;
    
    public function mount($pageName = 'Modul')
    {
        parent::mount();
        $this->pageName = $pageName;
        $this->activeModule = 'reseller-portal';
        $this->activePage = 'coming-soon';
        
        $this->breadcrumbs = [
            ['label' => 'Reseller Portal', 'url' => '#'],
            ['label' => $pageName, 'url' => '#'],
        ];
    }

    public function render()
    {
        return view('livewire.reseller-portal.coming-soon');
    }
}
