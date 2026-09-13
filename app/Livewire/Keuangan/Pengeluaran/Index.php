<?php

namespace App\Livewire\Keuangan\Pengeluaran;

use App\Livewire\BaseEnterpriseList;
use App\Services\Keuangan\ExpenseService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Livewire\Attributes\Computed;
use Livewire\WithFileUploads;

class Index extends BaseEnterpriseList
{
    use WithFileUploads;
    public string $activeModule = 'keuangan';
    public string $activePage = 'pengeluaran';

    public string $rejectReason = '';

    public bool $showCreateModal = false;
    public $form_date = '';
    public string $form_category = '';
    public string $form_description = '';
    public $form_amount = 0;
    public $form_attachment;
    public string $form_status = 'pending_approval';

    protected ExpenseService $service;

    public function boot(ExpenseService $service): void
    {
        $this->service = $service;
    }

    public function mount(): void
    {
        parent::mount();
        $this->activeModule = 'keuangan';
        $this->activePage = 'pengeluaran';
        $this->tabs = [
            'all' => 'Semua',
            'draft' => 'Draft',
            'approval' => 'Approval',
            'approved' => 'Disetujui',
        ];
        $this->filters = [
            'category' => '',
            'status' => '',
            'start_date' => '',
            'end_date' => '',
            'approved_by' => '',
        ];
    }

    public function openCreateModal(): void
    {
        $this->reset(['form_date', 'form_category', 'form_description', 'form_amount', 'form_attachment']);
        $this->form_date = now()->toDateString();
        $this->form_status = 'pending_approval';
        $this->showCreateModal = true;
    }

    public function closeCreateModal(): void
    {
        $this->showCreateModal = false;
    }

    public function saveExpense(): void
    {
        $this->validate([
            'form_reseller_id' => 'nullable|exists:users,id',
            'form_date' => 'required|date',
            'form_category' => 'required|string',
            'form_description' => 'required|string|max:255',
            'form_amount' => 'required|numeric|min:1',
            'form_attachment' => 'nullable|image|max:2048',
            'form_status' => 'required|in:draft,pending_approval',
        ]);

        try {
            $data = [
                'expense_date' => $this->form_date,
                'category' => $this->form_category,
                'description' => $this->form_description,
                'amount' => $this->form_amount,
                'status' => $this->form_status,
            ];

            if ($this->form_attachment) {
                $data['attachment_file'] = $this->form_attachment->store('expenses', 'public');
            }

            $this->service->create($data, auth()->id());
            
            $this->dispatch('notify', message: 'Pengeluaran berhasil ditambahkan.', type: 'success');
            $this->closeCreateModal();
        } catch (\Exception $e) {
            Log::error('Gagal menyimpan pengeluaran', ['error' => $e->getMessage()]);
            $this->dispatch('notify', message: 'Gagal menyimpan data pengeluaran.', type: 'error');
        }
    }

    public function setActiveTab(string $tab): void
    {
        $this->activeTab = $tab;
    }

    public function getRowsQuery()
    {
        return $this->service->list($this->filters, $this->search, $this->sortField, $this->sortDirection, $this->activeTab);
    }

    public function getRows()
    {
        return $this->withLoading(function () {
            return $this->getRowsQuery()->paginate($this->perPage);
        }, 'Gagal memuat data Pengeluaran');
    }

    #[Computed]
    public function getSummaryProperty(): array
    {
        try {
            return $this->service->summary();
        } catch (\Throwable $e) {
            Log::error('Pengeluaran summary failed', ['e' => $e->getMessage()]);
            return [
                'monthly_total' => 0,
                'needs_approval' => 0,
                'approved' => 0,
                'budget_remaining' => 0,
            ];
        }
    }

    #[Computed]
    public function getResellerOptionsProperty()
    {
        return app(\App\Services\Auth\UserQueryService::class)->getResellersForDropdown();
    }

    public function getCategoryOptionsProperty(): array
    {
        return $this->service->getCategoryOptions();
    }

    #[Computed]
    public function getApproverOptionsProperty(): array
    {
        return $this->service->getApproverOptions();
    }

    #[Computed]
    public function getFilterConfigProperty(): array
    {
        return [
            ['key' => 'category', 'label' => 'Kategori', 'type' => 'select', 'options' => $this->categoryOptions],
            ['key' => 'status', 'label' => 'Status', 'type' => 'select', 'options' => [
                'draft' => 'Draft',
                'pending_approval' => 'Butuh Approval',
                'approved' => 'Disetujui',
                'rejected' => 'Ditolak',
            ]],
            ['key' => 'start_date', 'label' => 'Tgl Mulai', 'type' => 'date'],
            ['key' => 'end_date', 'label' => 'Tgl Selesai', 'type' => 'date'],
            ['key' => 'approved_by', 'label' => 'Approval', 'type' => 'select', 'options' => $this->approverOptions],
        ];
    }

    #[Computed]
    public function getBulkActionsProperty(): array
    {
        return [
            ['key' => 'approve', 'label' => 'Setujui', 'variant' => 'bg-emerald-600 text-white hover:bg-emerald-700'],
            ['key' => 'export', 'label' => 'Export', 'variant' => 'bg-white border border-slate-200 text-slate-700 hover:bg-slate-50 dark:bg-slate-800 dark:border-slate-700 dark:text-slate-200 dark:hover:bg-slate-700'],
            ['key' => 'delete', 'label' => 'Hapus', 'variant' => 'bg-red-600 text-white hover:bg-red-700'],
        ];
    }

    public function handleBulkAction(string $action, array $ids): int
    {
        $userId = Auth::id() ?? 1;
        return match ($action) {
            'approve' => $this->service->bulkApprove($ids, $userId),
            'delete' => $this->service->bulkDelete($ids, $userId),
            'export' => (function () {
                $this->exportCsv();
                return count($ids);
            })(),
            default => 0,
        };
    }

    public function approve(int $id): void
    {
        $userId = Auth::id() ?? 1;
        try {
            $this->service->approve($id, $userId);
            session()->flash('success', 'Pengeluaran berhasil disetujui.');
        } catch (\Throwable $e) {
            $this->errorMessage = 'Gagal menyetujui: ' . $e->getMessage();
        }
    }

    public function confirmReject(int $id): void
    {
        $this->confirmTitle = 'Tolak Pengeluaran';
        $this->confirmMessage = 'Alasan penolakan:';
        $this->confirmAction = 'do-reject';
        $this->confirmParams = ['id' => $id];
        $this->confirmBtnText = 'Tolak';
        $this->confirmBtnClass = 'bg-red-600 hover:bg-red-700 text-white';
        $this->dispatch('open-modal', name: $this->confirmModal);
    }

    public function handleConfirm(): void
    {
        if ($this->confirmAction === 'do-reject') {
            $this->reject($this->confirmParams['id'] ?? 0);
        } elseif ($this->confirmAction === 'do-delete') {
            $this->delete($this->confirmParams['id'] ?? 0);
        } elseif ($this->confirmAction === 'execute-bulk') {
            $this->executeBulk($this->confirmParams['action'] ?? '');
        }
        $this->dispatch('close-modal', name: $this->confirmModal);
        $this->confirmAction = '';
        $this->confirmParams = [];
        $this->rejectReason = '';
    }

    public function reject(int $id): void
    {
        $userId = Auth::id() ?? 1;
        try {
            $this->service->reject($id, $userId, $this->rejectReason ?: 'Ditolak');
            session()->flash('success', 'Pengeluaran berhasil ditolak.');
        } catch (\Throwable $e) {
            $this->errorMessage = 'Gagal menolak: ' . $e->getMessage();
        }
    }

    public function edit(int $id): void
    {
        $this->confirmTitle = 'Edit Pengeluaran';
        try {
            $row = $this->service->findById($id);
            $this->confirmMessage = 'Kode: ' . ($row->code ?? '-') . ' | Deskripsi: ' . ($row->description ?? '-') . ' | Jumlah: Rp ' . number_format((float) ($row->amount ?? 0), 0, ',', '.');
        } catch (\Throwable) {
            $this->confirmMessage = 'Data tidak ditemukan.';
        }
        $this->confirmAction = '';
        $this->confirmBtnText = 'Tutup';
        $this->confirmBtnClass = 'bg-slate-600 hover:bg-slate-700 text-white';
        $this->dispatch('open-modal', name: $this->confirmModal);
    }

    public function viewAttachment(int $id): void
    {
        try {
            $row = $this->service->findById($id);
            $url = $row->attachment_file ?? '';
            if ($url) {
                $this->dispatch('open-new-tab', url: $url);
            } else {
                $this->errorMessage = 'Attachment tidak tersedia.';
            }
        } catch (\Throwable $e) {
            $this->errorMessage = 'Gagal memuat attachment: ' . $e->getMessage();
        }
    }

    public function confirmDelete(int $id): void
    {
        $this->confirmTitle = 'Hapus Pengeluaran';
        $this->confirmMessage = 'Apakah Anda yakin ingin menghapus pengeluaran ini? Aksi ini tidak dapat dibatalkan.';
        $this->confirmAction = 'do-delete';
        $this->confirmParams = ['id' => $id];
        $this->confirmBtnText = 'Hapus';
        $this->confirmBtnClass = 'bg-red-600 hover:bg-red-700 text-white';
        $this->dispatch('open-modal', name: $this->confirmModal);
    }

    public function delete(int $id): void
    {
        $userId = Auth::id() ?? 1;
        try {
            $row = $this->service->findById($id);
            $this->service->delete($row, $userId);
            session()->flash('success', 'Pengeluaran berhasil dihapus.');
        } catch (\Throwable $e) {
            $this->errorMessage = 'Gagal menghapus: ' . $e->getMessage();
        }
    }

    public function printReceipt(int $id): void
    {
        try {
            $row = $this->service->findById($id);
            $this->confirmTitle = 'Cetak Kwitansi';
            $this->confirmMessage = 'Kwitansi #' . ($row->code ?? '-') . ' siap dicetak (Rp ' . number_format((float) ($row->amount ?? 0), 0, ',', '.') . ')';
            $this->confirmAction = '';
            $this->confirmBtnText = 'Tutup';
            $this->confirmBtnClass = 'bg-slate-600 hover:bg-slate-700 text-white';
            $this->dispatch('open-modal', name: $this->confirmModal);
        } catch (\Throwable $e) {
            $this->errorMessage = 'Gagal menyiapkan kwitansi: ' . $e->getMessage();
        }
    }

    public function exportCsv(): \Symfony\Component\HttpFoundation\BinaryFileResponse|\Illuminate\Http\RedirectResponse
    {
        return $this->service->exportCsv($this->filters, $this->search, $this->activeTab);
    }

    public function render()
    {
        $rows = $this->getRows();
        return view('livewire.keuangan.pengeluaran.index', [
            'rows' => $rows,
            'summary' => $this->summary,
            'filterConfig' => $this->filterConfig,
            'bulkActions' => $this->bulkActions,
        ]);
    }
}
