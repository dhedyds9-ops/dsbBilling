<?php

namespace App\Livewire\Admin\Settings;

use App\Livewire\AdminComponent;

class Index extends AdminComponent
{
    public function mount()
    {
        $this->activeModule = 'admin';
        $this->activePage = 'settings';
        $this->breadcrumbs = [
            ['label' => 'Dashboard', 'url' => route('dashboard')],
            ['label' => 'Administration', 'url' => route('admin.users.index')],
            ['label' => 'Settings'],
        ];
    }

    public function render()
    {
        return view('livewire.admin.settings.index');
    }
}
