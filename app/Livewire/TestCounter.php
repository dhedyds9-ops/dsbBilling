<?php

namespace App\Livewire;

use App\Livewire\AdminComponent;

class TestCounter extends AdminComponent
{
    public $count = 0;

    public function mount()
    {
        parent::mount();
        $this->activeModule = 'isp';
        $this->activePage = 'test';
    }

    public function increment()
    {
        $this->count++;
    }

    public function render()
    {
        return view('livewire.test-counter');
    }
}
