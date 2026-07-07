<?php

namespace App\Livewire\ACS\Provisioning\Template;

use App\Livewire\ACS\BaseACSComponent;
use App\Models\ACS\ProvisionTemplate;

class Index extends BaseACSComponent
{
    public function mount()
    {
        parent::mount();
        $this->activeModule = 'acs';
        $this->activePage = 'provisioning';
        $this->filters = ['status' => ''];
        $this->breadcrumbs = [
            ['label' => 'Dashboard', 'url' => route('dashboard')],
            ['label' => 'ACS', 'url' => route('acs.dashboard')],
            ['label' => 'Provisioning Templates'],
        ];
    }

    public function delete($id)
    {
        $template = ProvisionTemplate::findOrFail($id);
        $template->delete();
        session()->flash('success', 'Template berhasil dihapus!');
    }

    public function render()
    {
        $query = ProvisionTemplate::query();

        if ($this->search) {
            $query->where('name', 'like', '%' . $this->search . '%');
        }

        if ($this->filters['status']) {
            $query->where('status', $this->filters['status']);
        }

        $templates = $query->orderBy($this->sortField, $this->sortDirection)->paginate($this->perPage);

        return view('livewire.acs.provisioning.template.index', compact('templates'));
    }
}
