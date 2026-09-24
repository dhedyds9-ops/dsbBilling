<?php

namespace App\Livewire\Crm;

use App\Livewire\AdminComponent;
use Livewire\WithPagination;

abstract class BaseCrmComponent extends AdminComponent
{
    use WithPagination;

    #[Livewire\Attributes\Url]
    public $search = '';
    
    #[Livewire\Attributes\Url]
    public $sortField = 'created_at';
    
    #[Livewire\Attributes\Url]
    public $sortDirection = 'desc';
    
    public $perPage = 10;
    
    #[Livewire\Attributes\Url]
    public $filters = [];
    
    public $showFilters = false;

    public function sortBy($field)
    {
        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortDirection = 'asc';
        }
        $this->sortField = $field;
    }

    public function resetFilters()
    {
        $this->filters = [];
        $this->search = '';
        $this->resetPage();
    }

    public function updatedSearch()
    {
        $this->resetPage();
    }
}
