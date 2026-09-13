<?php

namespace App\Livewire\Pengaturan\WhatsApp;

use App\Livewire\AdminComponent;
use App\Models\Integration\WaMessageHistory;
use App\Models\User;
use App\Services\Auth\UserQueryService;
use Livewire\WithPagination;

class MessageHistory extends AdminComponent
{
    use WithPagination;

    public int $perPage = 15;
    public string $activeModule = 'pengaturan';
    public string $activePage = 'whatsapp';

    public $statusFilter = '';
    public $search = '';

    public $selectedMessage = null;
    public $showDetailModal = false;

    public $showAssignModal = false;
    public $assigneeId = '';
    public $assignMessageId = null;

    public $selected = [];
    public $selectAll = false;

    public $showSendModal = false;
    public $newPhone = '';
    public $newMessage = '';

    public function mount()
    {
        parent::mount();
        $this->breadcrumbs = [
            ['label' => 'Dashboard', 'url' => route('dashboard')],
            ['label' => 'Pengaturan', 'url' => '#'],
            ['label' => 'WhatsApp Gateway', 'url' => route('pengaturan.whatsapp')],
            ['label' => 'Message History'],
        ];
    }

    public function updatedSelectAll($value)
    {
        if ($value) {
            $this->selected = WaMessageHistory::pluck('id')->map(fn($id) => (string)$id)->toArray();
        } else {
            $this->selected = [];
        }
    }

    public function resendSelected()
    {
        if (empty($this->selected)) {
            return;
        }

        $messages = WaMessageHistory::whereIn('id', $this->selected)->get();
        $count = 0;
        foreach ($messages as $msg) {
            // Re-dispatch job here
            \App\Jobs\Notifications\SendWaMessageJob::dispatch($msg->recipient_number, $msg->message, [], $msg->category ?? 'info');
            $msg->update(['status' => 'pending']);
            $count++;
        }

        $this->selected = [];
        $this->selectAll = false;
        session()->flash('success', "$count pesan berhasil diantrikan ulang.");
    }

    public function openDetail($id)
    {
        $this->selectedMessage = WaMessageHistory::with('assignee')->find($id);
        $this->showDetailModal = true;
    }

    public function closeDetail()
    {
        $this->showDetailModal = false;
        $this->selectedMessage = null;
    }

    public function openAssign($id)
    {
        $msg = WaMessageHistory::find($id);
        $this->assignMessageId = $id;
        $this->assigneeId = $msg->assigned_to ?? '';
        $this->showAssignModal = true;
    }

    public function saveAssign()
    {
        if ($this->assignMessageId) {
            $msg = WaMessageHistory::find($this->assignMessageId);
            $msg->update(['assigned_to' => $this->assigneeId ?: null]);
            session()->flash('success', 'Berhasil menetapkan penanggung jawab pesan.');
        }
        $this->showAssignModal = false;
        $this->assignMessageId = null;
    }

    public function openSendModal()
    {
        $this->newPhone = '';
        $this->newMessage = '';
        $this->showSendModal = true;
    }

    public function sendManualMessage()
    {
        $this->validate([
            'newPhone' => 'required',
            'newMessage' => 'required',
        ]);

        \App\Jobs\Notifications\SendWaMessageJob::dispatch($this->newPhone, $this->newMessage, [], 'manual');
        session()->flash('success', 'Pesan berhasil diantrikan.');
        $this->showSendModal = false;
    }

    public function resend($id)
    {
        $msg = WaMessageHistory::find($id);
        if ($msg) {
            \App\Jobs\Notifications\SendWaMessageJob::dispatch($msg->recipient_number, $msg->message, [], $msg->category ?? 'info');
            $msg->update(['status' => 'pending']);
            session()->flash('success', 'Pesan berhasil diantrikan ulang.');
        }
    }

    public function render()
    {
        $query = WaMessageHistory::query()->with('assignee')->orderBy('created_at', 'desc');
        
        if ($this->statusFilter) {
            $query->where('status', $this->statusFilter);
        }

        if ($this->search) {
            $query->where(function($q) {
                $q->where('recipient_number', 'like', '%' . $this->search . '%')
                  ->orWhere('recipient_name', 'like', '%' . $this->search . '%')
                  ->orWhere('message', 'like', '%' . $this->search . '%');
            });
        }

        $messages = $query->paginate($this->perPage);
        // Dapatkan eligible assignees (admin + manager) untuk dropdown
        // DILARANG: User::all() — menampilkan customer dan semua user
        $users = app(UserQueryService::class)->getEligibleAssignees();

        return view('livewire.pengaturan.whatsapp.message-history', [
            'messages' => $messages,
            'users'    => $users,
        ]);
    }
}
