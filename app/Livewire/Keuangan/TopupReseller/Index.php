<?php

namespace App\Livewire\Keuangan\TopupReseller;

use App\Livewire\BaseEnterpriseList;
use App\Services\Keuangan\ResellerTopupService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class Index extends BaseEnterpriseList
{
    public string $activeModule = 'keuangan';
    public string $activePage = 'topup-reseller';

    public string $rejectReason = '';

    protected ResellerTopupService $service;

    public function boot(ResellerTopupService $service): void
    {
        $this->service = $service;
    }

    public function mount(): void
    {
        parent::mount();
        $this->activeModule = 'keuangan';
        $this->activePage = 'topup-reseller';
        $this->tabs = [
            'all' => 'Semua',
            'pending' => 'Pending Approval',
            'approved' => 'Disetujui',
            'rejected' => 'Ditolak',
        ];
        $this->filters = [
            'user_id' => '',
            'status' => '',
            'start_date' => '',
            'end_date' => '',
            'method' => '',
        ];
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
        }, 'Gagal memuat data Topup Reseller');
    }

    public function getSummaryProperty(): array
    {
        try {
            return $this->service->summary();
        } catch (\Throwable $e) {
            Log::error('TopupReseller summary failed', ['e' => $e->getMessage()]);
            return [
                'monthly_total' => 0,
                'pending_count' => 0,
                'approved_count' => 0,
                'rejected_count' => 0,
                'total_reseller_balance' => 0,
            ];
        }
    }

    public function getResellerOptionsProperty(): array
    {
        return $this->service->getResellerOptions();
    }

    public function getFilterConfigProperty(): array
    {
        return [
            ['key' => 'user_id', 'label' => 'Reseller', 'type' => 'select', 'options' => $this->resellerOptions],
            ['key' => 'status', 'label' => 'Status', 'type' => 'select', 'options' => [
                'pending' => 'Pending Approval',
                'approved' => 'Disetujui',
                'rejected' => 'Ditolak',
            ]],
            ['key' => 'start_date', 'label' => 'Tanggal Mulai', 'type' => 'date'],
            ['key' => 'end_date', 'label' => 'Tanggal Selesai', 'type' => 'date'],
            ['key' => 'method', 'label' => 'Metode', 'type' => 'select', 'options' => [
                'bank_transfer' => 'Bank Transfer',
                'cash' => 'Cash',
                'e_wallet' => 'E-Wallet',
            ]],
        ];
    }

    public function getBulkActionsProperty(): array
    {
        return [
            ['key' => 'approve', 'label' => 'Setujui', 'variant' => 'bg-emerald-600 text-white hover:bg-emerald-700'],
            ['key' => 'reject', 'label' => 'Tolak', 'variant' => 'bg-red-600 text-white hover:bg-red-700'],
            ['key' => 'export', 'label' => 'Export', 'variant' => 'bg-white border border-slate-200 text-slate-700 hover:bg-slate-50 dark:bg-slate-800 dark:border-slate-700 dark:text-slate-200 dark:hover:bg-slate-700'],
        ];
    }

    public function handleBulkAction(string $action, array $ids): int
    {
        $userId = Auth::id() ?? 1;
        return match ($action) {
            'approve' => $this->service->bulkApprove($ids, $userId),
            'reject' => $this->service->bulkReject($ids, $userId, $this->rejectReason ?: 'Ditolak via bulk action'),
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
            session()->flash('success', 'Topup berhasil disetujui.');
        } catch (\Throwable $e) {
            $this->errorMessage = 'Gagal menyetujui: ' . $e->getMessage();
        }
    }

    public function confirmReject(int $id): void
    {
        $this->confirmTitle = 'Tolak Topup';
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
            session()->flash('success', 'Topup berhasil ditolak.');
        } catch (\Throwable $e) {
            $this->errorMessage = 'Gagal menolak: ' . $e->getMessage();
        }
    }

    public function previewProof(int $id): void
    {
        try {
            $row = $this->service->findById($id);
            $url = $row->proof_file ?? '';
            if ($url) {
                $this->dispatch('open-new-tab', url: $url);
            } else {
                $this->errorMessage = 'Bukti transfer tidak tersedia.';
            }
        } catch (\Throwable $e) {
            $this->errorMessage = 'Gagal memuat bukti: ' . $e->getMessage();
        }
    }

    public function exportProof(int $id): \Symfony\Component\HttpFoundation\BinaryFileResponse|\Illuminate\Http\RedirectResponse
    {
        try {
            $row = $this->service->findById($id);
            $url = $row->proof_file ?? '';
            if ($url) {
                return response()->redirectTo($url);
            }
            session()->flash('error', 'Bukti transfer tidak tersedia.');
            return back();
        } catch (\Throwable $e) {
            session()->flash('error', 'Gagal export: ' . $e->getMessage());
            return back();
        }
    }

    public function viewDetail(int $id): void
    {
        $this->confirmTitle = 'Detail Topup';
        try {
            $row = $this->service->findById($id);
            $this->confirmMessage = 'Reseller: ' . ($row->reseller->name ?? '-') . ' | Jumlah: ' . number_format((float) $row->amount, 0, ',', '.') . ' | Status: ' . strtoupper($row->status ?? '');
        } catch (\Throwable) {
            $this->confirmMessage = 'Data tidak ditemukan.';
        }
        $this->confirmAction = '';
        $this->confirmBtnText = 'Tutup';
        $this->confirmBtnClass = 'bg-slate-600 hover:bg-slate-700 text-white';
        $this->dispatch('open-modal', name: $this->confirmModal);
    }

    public function exportCsv(): \Symfony\Component\HttpFoundation\BinaryFileResponse|\Illuminate\Http\RedirectResponse
    {
        return $this->service->exportCsv($this->filters, $this->search, $this->activeTab);
    }

    public function render()
    {
        $rows = $this->getRows();
        return view('livewire.keuangan.topup-reseller.index', [
            'rows' => $rows,
            'summary' => $this->summary,
            'filterConfig' => $this->filterConfig,
            'bulkActions' => $this->bulkActions,
        ]);
    }
}
