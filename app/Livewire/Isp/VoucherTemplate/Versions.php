<?php

namespace App\Livewire\Isp\VoucherTemplate;

use App\Models\ISP\VoucherTemplate;
use App\Models\ISP\VoucherTemplateVersion;
use App\Services\ISP\VoucherTemplate\Actions\RollbackVoucherTemplateAction;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class Versions extends Component
{
    public VoucherTemplate $template;

    public function mount($id = null, ?VoucherTemplate $template = null)
    {
        if ($template) {
            $this->template = $template;
        } elseif ($id) {
            $this->template = VoucherTemplate::findOrFail($id);
        } else {
            abort(404, 'Template not found');
        }

        $this->template->load(['versions' => function ($q) {
            $q->orderByDesc('version');
        }]);
    }

    public function rollback(int $versionId, RollbackVoucherTemplateAction $action)
    {
        $version = VoucherTemplateVersion::findOrFail($versionId);
        
        try {
            $action->execute($this->template, $version, Auth::id() ?? 1);
            $this->dispatch('notify', ['type' => 'success', 'message' => 'Template rolled back successfully.']);
            return redirect()->route('isp.voucher-templates.edit', $this->template->id);
        } catch (\Throwable $e) {
            $this->dispatch('notify', ['type' => 'error', 'message' => $e->getMessage()]);
        }
    }

    public function render()
    {
        return view('livewire.isp.voucher-template.versions')->layout('layouts.enterprise');
    }
}

