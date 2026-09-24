<?php

namespace App\Livewire\ACS\Provisioning\Template;

use App\Livewire\ACS\BaseACSComponent;
use App\Models\ACS\ProvisionTemplate;

class Create extends BaseACSComponent
{
    public $name;
    public $vendor;
    public $model;
    public $firmware;
    public $tr069_script = [];
    public $tr181_script = [];
    public $config_json = [];
    public $description;
    public $status = 'active';

    public function mount()
    {
        parent::mount();
        $this->activeModule = 'acs';
        $this->activePage = 'provisioning';
        $this->breadcrumbs = [
            ['label' => 'Dashboard', 'url' => route('dashboard')],
            ['label' => 'ACS', 'url' => route('acs.dashboard')],
            ['label' => 'Templates', 'url' => route('acs.provisioning.templates.index')],
            ['label' => 'Create'],
        ];
    }

    public function save()
    {
        $validated = $this->validate([
            'name' => 'required|string|max:255',
            'vendor' => 'nullable|string|max:255',
            'model' => 'nullable|string|max:255',
            'status' => 'required|string|in:active,inactive',
        ]);

        $validated['uuid'] = (string) \Illuminate\Support\Str::uuid();
        $validated['created_by'] = auth()->id();
        $validated['updated_by'] = auth()->id();

        ProvisionTemplate::create($validated);

        session()->flash('success', 'Template berhasil dibuat!');
        return redirect()->route('acs.provisioning.templates.index');
    }

    public function render()
    {
        return view('livewire.acs.provisioning.template.create');
    }
}
