<?php

namespace App\Livewire\ISP\VoucherTemplate;

use App\Models\ISP\VoucherTemplate;
use App\Services\ISP\VoucherTemplate\Actions\DuplicateVoucherTemplateAction;
use App\Services\ISP\VoucherTemplate\Actions\DeleteVoucherTemplateAction;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;

    public $search = '';
    public $category = '';
    public $type = ''; // system or custom
    public $status = ''; // active or inactive

    protected $queryString = [
        'search' => ['except' => ''],
        'category' => ['except' => ''],
        'type' => ['except' => ''],
        'status' => ['except' => ''],
    ];

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function duplicate(int $id, DuplicateVoucherTemplateAction $action)
    {
        $template = VoucherTemplate::findOrFail($id);
        try {
            $newTemplate = $action->execute($template, $template->name . ' (Copy)', Auth::id() ?? 1);
            $this->dispatch('notify', ['type' => 'success', 'message' => 'Template duplicated successfully.']);
            return redirect()->route('isp.voucher-templates.edit', $newTemplate->id);
        } catch (\Throwable $e) {
            $this->dispatch('notify', ['type' => 'error', 'message' => $e->getMessage()]);
        }
    }

    public function delete(int $id, DeleteVoucherTemplateAction $action)
    {
        $template = VoucherTemplate::findOrFail($id);
        try {
            $action->execute($template, Auth::id() ?? 1);
            $this->dispatch('notify', ['type' => 'success', 'message' => 'Template deleted successfully.']);
        } catch (\Throwable $e) {
            $this->dispatch('notify', ['type' => 'error', 'message' => $e->getMessage()]);
        }
    }

    public function toggleActive(int $id)
    {
        $template = VoucherTemplate::findOrFail($id);
        if ($template->is_system) {
            $this->dispatch('notify', ['type' => 'error', 'message' => 'Cannot modify system template status.']);
            return;
        }
        
        if ($template->is_active) {
            $template->deactivate();
        } else {
            $template->activate();
        }
        $this->dispatch('notify', ['type' => 'success', 'message' => 'Status updated.']);
    }

    public function setAsDefault(int $id)
    {
        $template = VoucherTemplate::findOrFail($id);
        $template->setAsDefault();
        $this->dispatch('notify', ['type' => 'success', 'message' => 'Template set as default.']);
    }

    public function render()
    {
        $query = VoucherTemplate::query()->with('latestVersion');

        if ($this->search) {
            $query->where('name', 'like', '%' . $this->search . '%');
        }

        if ($this->category) {
            $query->where('category', $this->category);
        }

        if ($this->type === 'system') {
            $query->where('is_system', true);
        } elseif ($this->type === 'custom') {
            $query->where('is_system', false);
        }

        if ($this->status === 'active') {
            $query->where('is_active', true);
        } elseif ($this->status === 'inactive') {
            $query->where('is_active', false);
        }

        $templates = $query->orderBy('is_system', 'desc')
                           ->orderBy('name', 'asc')
                           ->paginate(12);

        return view('livewire.isp.voucher-template.index', [
            'templates' => $templates,
        ])->layout('layouts.enterprise');
    }
}
