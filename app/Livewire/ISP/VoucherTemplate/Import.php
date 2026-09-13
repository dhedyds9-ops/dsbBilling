<?php

namespace App\Livewire\ISP\VoucherTemplate;

use App\Services\ISP\VoucherTemplate\Actions\ImportVoucherTemplateAction;
use App\Services\VoucherTemplate\LegacyCompatibilityLayer;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class Import extends Component
{
    public $name = '';
    public $category = 'wifi';
    public $legacyContent = '';
    public $convertedContent = '';
    public $warnings = [];

    public function previewConversion(LegacyCompatibilityLayer $legacyLayer)
    {
        $this->validate([
            'legacyContent' => 'required|string',
        ]);

        try {
            $this->convertedContent = $legacyLayer->translate($this->legacyContent)['source'];
            
            // Basic detection of legacy variables for warnings
            $this->warnings = [];
            if (str_contains($this->legacyContent, '$vs[')) {
                $this->warnings[] = 'Legacy array syntax ($vs["key"]) detected. It will be converted to object syntax ($key).';
            }
            if (str_contains($this->legacyContent, '{include file=')) {
                $this->warnings[] = 'Legacy Smarty {include} detected. It may not work if the included file does not exist.';
            }
        } catch (\Throwable $e) {
            throw $e;
        }
    }

    public function import(ImportVoucherTemplateAction $action)
    {
        $this->validate([
            'name' => 'required|string|max:255',
            'category' => 'required|string',
            'legacyContent' => 'required|string',
        ]);

        try {
            $template = $action->execute($this->name, $this->legacyContent, $this->category, Auth::id() ?? 1);
            $this->dispatch('notify', ['type' => 'success', 'message' => 'Legacy template imported successfully.']);
            return redirect()->route('isp.voucher-templates.edit', $template->id);
        } catch (\Throwable $e) {
            throw $e;
        }
    }

    public function render()
    {
        return view('livewire.isp.voucher-template.import')->layout('layouts.enterprise');
    }
}
