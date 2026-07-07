<?php

namespace App\Livewire\ACS\Provisioning\Template;

use App\Livewire\ACS\BaseACSComponent;
use App\Models\ACS\ProvisionTemplate;

class Edit extends BaseACSComponent
{
    public $templateId;
    public $template;
    public $name;
    public $vendor;
    public $model;
    public $firmware;
    public $tr069_script = [];
    public $tr181_script = [];
    public $config_json = [];
    public $description;
    public $status;

    public function mount($id = null)
    {
        parent::mount();
        $this->templateId = $id;
        $this->template = ProvisionTemplate::findOrFail($id);
        $this->fill($this->template->toArray());
        $this->activeModule = 'acs';
        $this->activePage = 'provisioning';
        $this->breadcrumbs = [
            ['label' => 'Dashboard', 'url' => route('dashboard')],
            ['label' => 'ACS', 'url' => route('acs.dashboard')],
            ['label' => 'Templates', 'url' => route('acs.provisioning.templates.index')],
            ['label' => 'Edit'],
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

        $validated['updated_by'] = auth()->id();
        $this->template->update($validated);

        session()->flash('success', 'Template berhasil diperbarui!');
        return redirect()->route('acs.provisioning.templates.index');
    }

    public function render()
    {
        return view('livewire.acs.provisioning.template.edit');
    }
}
