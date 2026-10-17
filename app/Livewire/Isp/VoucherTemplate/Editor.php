<?php

namespace App\Livewire\Isp\VoucherTemplate;

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
    public $is_system = false;

    public $validationErrors = [];

    public function mount($id = null)
    {
        if ($id) {
            $this->template = VoucherTemplate::with('latestVersion')->findOrFail($id);
            $this->name = $this->template->name;
            $this->category = $this->template->category instanceof \BackedEnum ? $this->template->category->value : (string) $this->template->category;
            $this->description = $this->template->description ?? '';
            $this->is_active = (bool) $this->template->is_active;
            $this->is_system = (bool) $this->template->is_system;

            if ($this->template->latestVersion) {
                $this->template_code = $this->template->latestVersion->template_code ?? '';
                $this->settings = $this->template->latestVersion->settings ?? [];
            }
        } else {
            $this->template_code = '<div style="width:200px; padding:10px; border:1px solid #000; font-family:sans-serif;">
    <h4 style="margin:0 0 8px 0; font-size:14px; text-align:center;">Voucher Hotspot</h4>
    <p style="margin:4px 0; font-size:12px;">Kode: <strong>{{voucher.code}}</strong></p>
    <p style="margin:4px 0; font-size:12px;">Sandi: <strong>{{voucher.password}}</strong></p>
    <p style="margin:4px 0; font-size:12px;">Harga: {{format_currency(voucher.price)}}</p>
</div>';
        }
    }

    public function validateContent(TemplateValidator $validator)
    {
        $result = $validator->validate($this->template_code);
        if (!$result['is_valid']) {
            $this->validationErrors = array_map(function ($err) {
                return is_array($err) ? ($err['message'] ?? json_encode($err)) : (string) $err;
            }, $result['errors']);
            $this->dispatch('notify', ['type' => 'error', 'message' => 'Template memiliki kesalahan sintaks.']);
            session()->flash('error', 'Sintaks template tidak valid! Periksa rincian di bawah.');
        } else {
            $this->validationErrors = [];
            $this->dispatch('notify', ['type' => 'success', 'message' => 'Sintaks template valid dan siap digunakan!']);
            session()->flash('success', 'Sintaks template valid dan siap digunakan!');
        }
    }

    public function duplicateTemplate(\App\Services\ISP\VoucherTemplate\Actions\DuplicateVoucherTemplateAction $action)
    {
        if (!$this->template) {
            return;
        }

        try {
            $newTemplate = $action->execute($this->template, $this->template->name . ' (Salinan)', Auth::id() ?? 1);
            session()->flash('success', 'Template berhasil disalin! Silakan sesuaikan kode pada salinan ini.');
            return redirect()->route('isp.voucher-templates.edit', $newTemplate->id);
        } catch (\Throwable $e) {
            $this->dispatch('notify', ['type' => 'error', 'message' => $e->getMessage()]);
            session()->flash('error', 'Gagal menyalin: ' . $e->getMessage());
        }
    }

    public function save(CreateVoucherTemplateAction $createAction, UpdateVoucherTemplateAction $updateAction)
    {
        if ($this->template && $this->template->is_system) {
            $this->dispatch('notify', ['type' => 'error', 'message' => 'Template sistem tidak dapat diubah langsung. Silakan duplikat.']);
            session()->flash('error', 'Template sistem tidak dapat diubah langsung. Silakan gunakan tombol "Duplikat Salinan" untuk mengubahnya.');
            return;
        }

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
            'template' => $this->template,
            'is_system' => $this->is_system,
            'validationErrors' => $this->validationErrors,
            'variables' => $grouped
        ])->layout('layouts.enterprise');
    }
}
