<?php

namespace App\Livewire\ISP\Router;

use App\Livewire\ISP\BaseNetworkComponent;
use App\Models\ISP\Router;

class Index extends BaseNetworkComponent
{
    public function mount()
    {
        parent::mount();
        $this->activeModule = 'isp';
        $this->activePage = 'routers';
        $this->breadcrumbs = [
            ['label' => 'Dashboard', 'url' => route('dashboard')],
            ['label' => 'MikroTik', 'url' => route('isp.routers.index')],
            ['label' => 'Router List'],
        ];
    }

    public function render()
    {
        $query = Router::query();

        if ($this->search) {
            $query->where('name', 'like', '%' . $this->search . '%')
                  ->orWhere('code', 'like', '%' . $this->search . '%');
        }

        $routers = $query->orderBy($this->sortField, $this->sortDirection)
                          ->paginate($this->perPage);

        return view('livewire.isp.router.index', compact('routers'));
    }
}
