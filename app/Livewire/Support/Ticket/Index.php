<?php

namespace App\Livewire\Support\Ticket;

use App\Livewire\BaseEnterpriseList;
use App\Services\Support\TicketService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Throwable;

class Index extends BaseEnterpriseList
{
    public string $activeModule = 'support';
    public string $activePage = 'ticket';

    public array $tabs = [
        'kanban' => 'Kanban',
        'table' => 'Table',
        'timeline' => 'Timeline',
    ];

    public ?array $summary = null;
    public ?array $filterOptions = null;
    public ?array $kanbanData = null;
    public ?array $timelineData = null;
    public ?int $timelineTicketId = null;
    public ?int $bulkAssigneeId = null;
    public bool $showBulkAssign = false;
    public bool $showCreateForm = false;
    public array $newTicket = [];
    public string $resolveNote = '';

    protected TicketService $ticketService;

    public function boot(TicketService $ticketService): void
    {
        $this->ticketService = $ticketService;
    }

    public function mount(): void
    {
        parent::mount();
        $this->activeTab = 'kanban';
        $this->filters = [
            'status' => '',
            'priority' => '',
            'assigned_to' => '',
            'customer_id' => '',
            'category' => '',
            'due_from' => '',
            'due_to' => '',
        ];
        $this->newTicket = [
            'customer_id' => '',
            'title' => '',
            'description' => '',
            'category' => 'internet',
            'priority' => 'medium',
            'assigned_to' => '',
            'due_date' => '',
        ];
        $this->loadAll();
    }

    public function loadAll(): void
    {
        $this->summary = $this->ticketService->summary();
        $this->filterOptions = [
            'statuses' => [
                'open' => 'Open',
                'in_progress' => 'In Progress',
                'pending_customer' => 'Pending Customer',
                'resolved' => 'Resolved',
                'closed' => 'Closed',
            ],
            'priorities' => [
                'low' => 'Low',
                'medium' => 'Medium',
                'high' => 'High',
                'critical' => 'Critical',
            ],
            'assignees' => $this->ticketService->getAssigneeOptions(),
            'customers' => $this->ticketService->getCustomerOptions(),
            'categories' => [
                'billing' => 'Billing',
                'internet' => 'Internet',
                'installation' => 'Installation',
                'other' => 'Other',
            ],
        ];
        if ($this->activeTab === 'kanban') {
            $this->kanbanData = $this->ticketService->kanbanByStatus();
        }
    }

    public function getRowsQuery()
    {
        return $this->ticketService->list($this->filters, $this->search, $this->sortField, $this->sortDirection, 0)->getQuery();
    }

    public function getRows()
    {
        return $this->withLoading(function () {
            return $this->ticketService->list(
                $this->filters,
                $this->search,
                $this->sortField,
                $this->sortDirection,
                $this->perPage
            );
        }, 'Gagal memuat tiket');
    }

    public function handleBulkAction(string $action, array $ids): int
    {
        $userId = Auth::id() ?? 0;
        return match ($action) {
            'assign' => $this->ticketService->bulkAssign($ids, (int) $this->bulkAssigneeId, $userId),
            'resolve' => $this->ticketService->bulkResolve($ids, null, $userId),
            'close' => $this->ticketService->bulkClose($ids, $userId),
            'delete' => $this->ticketService->bulkDelete($ids),
            'export' => $this->doBulkExport($ids),
            default => 0,
        };
    }

    public function applyBulk(string $action): void
    {
        if ($action === 'assign') {
            if (count($this->selected) === 0) {
                $this->errorMessage = 'Pilih data terlebih dahulu.';
                return;
            }
            $this->showBulkAssign = true;
            return;
        }
        parent::applyBulk($action);
    }

    public function submitBulkAssign(): void
    {
        $userId = Auth::id() ?? 0;
        if (!$this->bulkAssigneeId) {
            $this->errorMessage = 'Pilih petugas.';
            return;
        }
        $count = $this->ticketService->bulkAssign($this->selected, (int) $this->bulkAssigneeId, $userId);
        session()->flash('success', "Berhasil assign {$count} tiket.");
        $this->showBulkAssign = false;
        $this->bulkAssigneeId = null;
        $this->selected = [];
        $this->loadAll();
    }

    public function cancelBulkAssign(): void
    {
        $this->showBulkAssign = false;
        $this->bulkAssigneeId = null;
    }

    protected function doBulkExport(array $ids): int
    {
        $rows = $this->ticketService->list($this->filters, $this->search, $this->sortField, $this->sortDirection, 0);
        if ($rows instanceof \Illuminate\Pagination\LengthAwarePaginator) {
            $rows = $rows->getCollection();
        }
        $filtered = $rows->whereIn('id', $ids)->all();
        $response = $this->ticketService->exportCsv($filtered);
        $response->send();
        exit;
        return count($ids);
    }

    public function exportCsv(): StreamedResponse|BinaryFileResponse|\Illuminate\Http\RedirectResponse
    {
        $rows = $this->ticketService->list($this->filters, $this->search, $this->sortField, $this->sortDirection, 0);
        if ($rows instanceof \Illuminate\Pagination\LengthAwarePaginator) {
            $rows = $rows->getCollection()->all();
        }
        return $this->ticketService->exportCsv($rows);
    }

    public function setActiveTab(string $tab): void
    {
        if (isset($this->tabs[$tab])) {
            $this->activeTab = $tab;
            if ($tab === 'kanban') {
                $this->kanbanData = $this->ticketService->kanbanByStatus();
            }
        }
    }

    public function getToolbarActions(): array
    {
        return [
            ['label' => 'Ticket Baru', 'icon' => 'play', 'action' => 'openCreateForm()'],
            ['label' => 'Export', 'icon' => 'download', 'action' => 'exportCsv()'],
            ['label' => 'Bulk Assign', 'icon' => 'users', 'action' => "applyBulk('assign')"],
        ];
    }

    public function getBulkActions(): array
    {
        return [
            ['key' => 'assign', 'label' => 'Assign'],
            ['key' => 'resolve', 'label' => 'Resolve'],
            ['key' => 'close', 'label' => 'Close'],
            ['key' => 'export', 'label' => 'Export'],
            ['key' => 'delete', 'label' => 'Delete', 'variant' => 'bg-red-600 text-white hover:bg-red-700'],
        ];
    }

    public function getFilterConfig(): array
    {
        $opts = $this->filterOptions ?? [];
        return [
            ['key' => 'status', 'label' => 'Status', 'type' => 'select', 'options' => $opts['statuses'] ?? []],
            ['key' => 'priority', 'label' => 'Priority', 'type' => 'select', 'options' => $opts['priorities'] ?? []],
            ['key' => 'assigned_to', 'label' => 'Assignee', 'type' => 'select', 'options' => $opts['assignees'] ?? []],
            ['key' => 'customer_id', 'label' => 'Customer', 'type' => 'select', 'options' => $opts['customers'] ?? []],
            ['key' => 'category', 'label' => 'Kategori', 'type' => 'select', 'options' => $opts['categories'] ?? []],
            ['key' => 'due_from', 'label' => 'Due Date (Awal)', 'type' => 'date'],
            ['key' => 'due_to', 'label' => 'Due Date (Akhir)', 'type' => 'date'],
        ];
    }

    public function getSummaryItems(): array
    {
        $s = $this->summary ?? [];
        return [
            ['label' => 'Open', 'value' => $s['open'] ?? 0, 'color' => 'blue', 'icon' => 'ticket'],
            ['label' => 'In Progress', 'value' => $s['in_progress'] ?? 0, 'color' => 'amber', 'icon' => 'activity'],
            ['label' => 'Pending Customer', 'value' => $s['pending_customer'] ?? 0, 'color' => 'purple', 'icon' => 'clock'],
            ['label' => 'Resolved', 'value' => $s['resolved'] ?? 0, 'color' => 'green', 'icon' => 'check-circle'],
            ['label' => 'Closed', 'value' => $s['closed'] ?? 0, 'color' => 'slate', 'icon' => 'file-text'],
        ];
    }

    public function openCreateForm(): void
    {
        $this->newTicket = [
            'customer_id' => '',
            'title' => '',
            'description' => '',
            'category' => 'internet',
            'priority' => 'medium',
            'assigned_to' => '',
            'due_date' => '',
        ];
        $this->showCreateForm = true;
    }

    public function closeCreateForm(): void
    {
        $this->showCreateForm = false;
    }

    public function submitCreate(): void
    {
        $userId = Auth::id() ?? 0;
        try {
            $data = $this->newTicket;
            if (empty($data['customer_id']) || empty($data['title'])) {
                $this->errorMessage = 'Customer dan Title wajib.';
                return;
            }
            $this->ticketService->create($data, $userId);
            session()->flash('success', 'Tiket berhasil dibuat.');
            $this->showCreateForm = false;
            $this->loadAll();
        } catch (Throwable $e) {
            $this->errorMessage = 'Gagal buat tiket: ' . $e->getMessage();
        }
    }

    public function rowDetail(int $id): void
    {
        $this->timelineTicketId = $id;
        $this->timelineData = $this->ticketService->timeline($id);
        $this->activeTab = 'timeline';
    }

    public function rowEdit(int $id): void
    {
        session()->flash('info', 'Edit Ticket ID ' . $id);
    }

    public function rowAssign(int $id): void
    {
        $this->selected = [(string) $id];
        $this->showBulkAssign = true;
    }

    public function rowResolve(int $id): void
    {
        $userId = Auth::id() ?? 0;
        try {
            $this->ticketService->resolve($id, null, $userId);
            session()->flash('success', 'Tiket di-resolve.');
            $this->loadAll();
        } catch (Throwable $e) {
            $this->errorMessage = 'Gagal resolve: ' . $e->getMessage();
        }
    }

    public function rowClose(int $id): void
    {
        $this->confirmTitle = 'Close Ticket';
        $this->confirmMessage = "Anda akan menutup Ticket ID #{$id}. Lanjutkan?";
        $this->confirmAction = 'close-ticket';
        $this->confirmParams = ['id' => $id];
        $this->confirmBtnText = 'Close';
        $this->confirmBtnClass = 'bg-slate-700 hover:bg-slate-800 text-white';
        $this->dispatch('open-modal', name: 'confirm');
    }

    public function rowDelete(int $id): void
    {
        $this->confirmTitle = 'Hapus Ticket';
        $this->confirmMessage = "Anda akan menghapus Ticket ID #{$id}. Lanjutkan?";
        $this->confirmAction = 'delete-ticket';
        $this->confirmParams = ['id' => $id];
        $this->confirmBtnText = 'Hapus';
        $this->confirmBtnClass = 'bg-red-600 hover:bg-red-700 text-white';
        $this->dispatch('open-modal', name: 'confirm');
    }

    public function handleConfirm(): void
    {
        $userId = Auth::id() ?? 0;
        if ($this->confirmAction === 'close-ticket') {
            try {
                $this->ticketService->close((int) ($this->confirmParams['id'] ?? 0), $userId);
                session()->flash('success', 'Tiket ditutup.');
                $this->loadAll();
            } catch (Throwable $e) {
                $this->errorMessage = 'Gagal close: ' . $e->getMessage();
            }
            $this->dispatch('close-modal', name: 'confirm');
            $this->confirmAction = '';
            $this->confirmParams = [];
            return;
        }
        if ($this->confirmAction === 'delete-ticket') {
            try {
                $ticket = \App\Models\Support\Ticket::findOrFail((int) ($this->confirmParams['id'] ?? 0));
                $this->ticketService->delete($ticket);
                session()->flash('success', 'Tiket dihapus.');
                $this->loadAll();
            } catch (Throwable $e) {
                $this->errorMessage = 'Gagal delete: ' . $e->getMessage();
            }
            $this->dispatch('close-modal', name: 'confirm');
            $this->confirmAction = '';
            $this->confirmParams = [];
            return;
        }
        parent::handleConfirm();
    }

    public function updatedActiveTab(string $value = ''): void
    {
        parent::updatedActiveTab($value);
        $this->loadAll();
    }

    public function render()
    {
        $rows = $this->getRows();
        return view('livewire.support.ticket.index', compact('rows'));
    }
}
