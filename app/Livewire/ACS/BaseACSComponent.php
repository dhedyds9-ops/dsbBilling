<?php

namespace App\Livewire\ACS;

use App\Livewire\AdminComponent;
use Livewire\WithPagination;

abstract class BaseACSComponent extends AdminComponent
{
    use WithPagination;

    public $search = '';
    public $sortField = 'created_at';
    public $sortDirection = 'desc';
    public $perPage = 10;
    public $filters = [];
    public $showFilters = false;
    public bool $isNocLayout = false;

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

    public function updatedFilters()
    {
        $this->resetPage();
    }

    public function updatedPerPage()
    {
        $this->resetPage();
    }

    public function mount()
    {
        parent::mount();
        $this->isNocLayout = request()->routeIs('noc.*') || request()->is('noc/*');
    }

    public function rendering($view, $data)
    {
        if (request()->routeIs('noc.*') || request()->is('noc/*')) {
            $view->layout('layouts.noc');
        } else {
            $view->layout('layouts.enterprise');
        }
    }
}
