<?php

namespace App\Livewire\ISP\VoucherTemplate;

use App\Models\ISP\VoucherTemplate;
use App\Services\ISP\VoucherTemplate\Actions\CreateVoucherTemplateAction;
use App\Services\ISP\VoucherTemplate\Actions\UpdateVoucherTemplateAction;
use App\Services\VoucherTemplate\TemplateValidator;
use App\Services\VoucherTemplate\VariableRegistry;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class Editor extends Component
{
    public ?VoucherTemplate $template = null;
    public $name = '';
    public $category = 'wifi';
    public $description = '';
    public $template_code = '';
    public $settings = [];
    public $changelog = '';

    public $is_active = true;

    public $validationErrors = [];

    public function mount($id = null)
    {
        if ($id) {
            $this->template = VoucherTemplate::with('latestVersion')->findOrFail($id);
            if ($this->template->is_system) {
                session()->flash('error', 'System templates cannot be edited directly. Please duplicate it first.');
                return redirect()->route('isp.voucher-templates.index');
            }
            $this->name = $this->template->name;
            $this->category = $this->template->category->value;
            $this->description = $this->template->description;
            $this->is_active = $this->template->is_active;

            if ($this->template->latestVersion) {
                $this->template_code = $this->template->latestVersion->template_code;
                $this->settings = $this->template->latestVersion->settings ?? [];
            }
        } else {
            $this->template_code = '<div style="width:200px; padding:10px; border:1px solid #000;">
    <h4>Voucher</h4>
    <p>Username: {{voucher.username}}</p>
    <p>Password: {{voucher.password}}</p>
</div>';
        }
    }

    public function validateContent(TemplateValidator $validator)
    {
        $result = $validator->validate($this->template_code);
        if (!$result['is_valid']) {
            $this->validationErrors = $result['errors'];
            $this->dispatch('notify', ['type' => 'error', 'message' => 'Template has validation errors.']);
            session()->flash('error', 'Sintaks template tidak valid!');
        } else {
            $this->validationErrors = [];
            $this->dispatch('notify', ['type' => 'success', 'message' => 'Template syntax is valid.']);
            session()->flash('success', 'Sintaks template valid dan siap digunakan!');
        }
    }

    public function save(CreateVoucherTemplateAction $createAction, UpdateVoucherTemplateAction $updateAction)
    {
        $this->validate([
            'name' => 'required|string|max:255',
            'category' => 'required|string',
            'template_code' => 'required|string',
        ]);

        try {
            $settingsData = is_array($this->settings) ? $this->settings : [];
            if ($this->changelog !== '') {
                $settingsData['changelog'] = $this->changelog;
            }
            $createNewVersion = !empty($settingsData) || $this->changelog !== '';

            if ($this->template) {
                $updateAction->execute($this->template, [
                    'name' => $this->name,
                    'category' => $this->category,
                    'description' => $this->description,
                    'is_active' => $this->is_active,
                    'template_code' => $this->template_code,
                    'settings' => $settingsData,
                ], Auth::id() ?? 1, $createNewVersion);

                $this->dispatch('notify', ['type' => 'success', 'message' => 'Template saved.']);
                session()->flash('success', 'Template berhasil disimpan!');
                return redirect()->route('isp.voucher-templates.edit', $this->template->id);
            } else {
                $template = $createAction->execute([
                    'name' => $this->name,
                    'category' => $this->category,
                    'description' => $this->description,
                    'is_active' => $this->is_active,
                    'template_code' => $this->template_code,
                    'settings' => $settingsData,
                ], Auth::id() ?? 1);

                $this->dispatch('notify', ['type' => 'success', 'message' => 'Template created.']);
                session()->flash('success', 'Template baru berhasil dibuat!');
                return redirect()->route('isp.voucher-templates.edit', $template->id);
            }
        } catch (\Throwable $e) {
            $this->dispatch('notify', ['type' => 'error', 'message' => $e->getMessage()]);
            session()->flash('error', 'Gagal menyimpan: ' . $e->getMessage());
        }
    }

    public function render()
    {
        $allVars = \App\Services\VoucherTemplate\VariableRegistry::all();
        $grouped = [];
        foreach ($allVars as $key => $data) {
            $cat = $data['category'] ?? 'lainnya';
            $grouped[$cat][] = [
                'key' => $key,
                'description' => $data['label'] ?? ''
            ];
        }

        return view('livewire.isp.voucher-template.editor', [
            'variables' => $grouped
        ])->layout('layouts.enterprise');
    }
}
