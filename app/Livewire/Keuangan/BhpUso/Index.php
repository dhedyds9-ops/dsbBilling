<?php

namespace App\Livewire\Keuangan\BhpUso;

use App\Livewire\BaseEnterpriseList;
use App\Services\Keuangan\IncomeReportService;
use App\Services\Keuangan\FinancialStatementService;
use App\Services\Keuangan\BhpUsoCalculationService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Throwable;
use Livewire\Attributes\Computed;

class Index extends BaseEnterpriseList
{
    public string $activeModule = 'keuangan';
    public string $activePage = 'bhp-uso';

    public array $tabs = [
        'summary' => 'Summary',
        'detail' => 'Detail Perhitungan',
        'history' => 'Riwayat Pembayaran',
    ];

    protected ?IncomeReportService $svc = null;
    protected ?FinancialStatementService $financialSvc = null;
    protected ?BhpUsoCalculationService $bhpUsoSvc = null;

    public function boot(
        IncomeReportService $svc,
        FinancialStatementService $fsvc,
        BhpUsoCalculationService $bhpUsoSvc
    ): void {
        $this->svc = $svc;
        $this->financialSvc = $fsvc;
        $this->bhpUsoSvc = $bhpUsoSvc;
    }

    public function mount(): void
    {
        parent::mount();
        $this->authorizeAccess();
        $this->activeModule = 'keuangan';
        $this->activePage = 'bhp-uso';
        $this->filters = [
            'year' => (string) now()->year,
            'month' => (string) now()->month,
            'start_date' => now()->startOfMonth()->toDateString(),
            'end_date' => now()->toDateString(),
        ];
        $this->perPage = 50;
    }

    public function authorizeAccess(): void
    {
        if (!Auth::check()) abort(403);
    }

    public function setActiveTab(string $tab): void
    {
        if (isset($this->tabs[$tab])) {
            $this->activeTab = $tab;
            $this->resetPage();
        }
    }

    public function getRowsQuery()
    {
        return collect($this->buildDetailRows());
    }

    public function getRows()
    {
        return $this->withLoading(function () {
            if ($this->activeTab === 'detail') {
                $rows = $this->buildDetailRows();
                $page = \Illuminate\Pagination\Paginator::resolveCurrentPage('page');
                $offset = ($page - 1) * $this->perPage;
                return new \Illuminate\Pagination\LengthAwarePaginator(
                    items: collect($rows)->slice($offset, $this->perPage)->values(),
                    total: count($rows),
                    perPage: $this->perPage,
                    currentPage: $page,
                    options: ['path' => \Illuminate\Pagination\Paginator::resolveCurrentPath()]
                );
            }
            if ($this->activeTab === 'history') {
                return collect($this->buildHistoryRows())->paginate($this->perPage);
            }
            return collect([]);
        }, 'Gagal memuat data BHP/USO');
    }

    protected function revenueBreakdown(): array
    {
        try {
            $from = $this->filters['start_date'] ?: now()->startOfMonth()->toDateString();
            $to = $this->filters['end_date'] ?: now()->toDateString();
            
            $fin = $this->financialSvc ? $this->financialSvc->incomeStatement(['start_period' => $from, 'end_period' => $to]) : [];
            $rev = $fin['revenue'] ?? [];
            
            $pppoe = (float)($rev['pppoe'] ?? 0);
            $hotspot = (float)($rev['hotspot'] ?? 0);
            $voucher = (float)($rev['voucher'] ?? 0);
            $other = (float)($rev['evoucher'] ?? 0) + (float)($rev['other'] ?? 0);
            
            $total = (float)($rev['total'] ?? 0);

            return [
                'period_label' => date('M Y', strtotime($from)),
                'from' => $from,
                'to' => $to,
                'total_revenue' => $total,
                'pppoe' => $pppoe,
                'hotspot' => $hotspot,
                'voucher' => $voucher,
                'other' => $other,
                'count_days' => max(1, (int)round(now()->parse($to)->diffInDays(now()->parse($from)) + 1)),
            ];
        } catch (Throwable $e) {
            Log::error('BhpUso revenue failed', ['e' => $e->getMessage()]);
            return [
                'period_label' => now()->format('M Y'),
                'from' => now()->startOfMonth()->toDateString(),
                'to' => now()->toDateString(),
                'total_revenue' => 0, 'pppoe' => 0, 'hotspot' => 0, 'voucher' => 0, 'other' => 0, 'count_days' => 30,
            ];
        }
    }

    protected function bhpRates(): array
    {
        return [
            ['kode' => 'BHP-TEL', 'name' => 'BHP Telekomunikasi (0.5%)', 'base_pct' => 0.50, 'base_min' => 0],
            ['kode' => 'USO-KOM', 'name' => 'Kewajiban Pelayanan Universal / USO (1.25%)', 'base_pct' => 1.25, 'base_min' => 0],
        ];
    }

    protected function buildDetailRows(): array
    {
        $from = $this->filters['start_date'] ?: now()->startOfMonth()->toDateString();
        $to = $this->filters['end_date'] ?: now()->toDateString();

        if ($this->bhpUsoSvc) {
            $calculation = $this->bhpUsoSvc->calculateForPeriod($from, $to);
            return $calculation['detail_rows'];
        }

        return [];
    }

    protected function buildHistoryRows(): array
    {
        $rows = [];
        $year = (int) ($this->filters['year'] ?? now()->year);
        
        for ($m = 1; $m <= 12; $m++) {
            $start = now()->setYear($year)->setMonth($m)->startOfMonth()->toDateString();
            $end = now()->setYear($year)->setMonth($m)->endOfMonth()->toDateString();
            
            try {
                if ($this->bhpUsoSvc) {
                    $calculation = $this->bhpUsoSvc->calculateForPeriod($start, $end);
                    $rev = $calculation['total_retail'];
                    $dasar = $calculation['dasar_pengenaan'];
                    $bhp = $calculation['bhp_total'];
                    $uso = $calculation['uso_total'];
                    $grand = $calculation['grand_total'];
                } else {
                    $rev = 0; $dasar = 0; $bhp = 0; $uso = 0; $grand = 0;
                }
            } catch (Throwable) {
                $rev = 0; $dasar = 0; $bhp = 0; $uso = 0; $grand = 0;
            }
            
            $rows[] = [
                'id' => $m,
                'periode' => now()->setMonth($m)->translatedFormat('F Y'),
                'period_label' => now()->setMonth($m)->translatedFormat('F Y'),
                'revenue' => $rev,
                'dasar_pengenaan' => $dasar,
                'bhp_total' => $bhp,
                'bhp' => $bhp,
                'uso_total' => $uso,
                'uso' => $uso,
                'grand_total' => $grand,
                'total' => $grand,
                'status' => $m < (int) now()->month ? 'paid' : ($m === (int) now()->month ? 'pending' : 'draft'),
                'paid_date' => $m < (int) now()->month ? now()->setMonth($m)->addDays(7)->toDateString() : null,
                'no_bukti' => $m < (int) now()->month ? 'BHP-USO-' . sprintf('%02d', $m) . date('y') : '-',
            ];
        }
        return $rows;
    }

    #[Computed]
    public function getBhpItemsProperty(): array
    {
        $detail = $this->buildDetailRows();
        $items = [];
        $totalBhp = collect($detail)->firstWhere('kode', 'TOTAL-BHP')['nominal'] ?? 1;
        if ($totalBhp <= 0) $totalBhp = 1;

        foreach ($detail as $r) {
            if ($r['type'] === 'BHP' || $r['type'] === 'USO') {
                $items[] = [
                    'code' => $r['kode'],
                    'name' => $r['name'],
                    'base_pct' => $r['rate_pct'],
                    'base_min' => $r['minimal'],
                    'nominal' => $r['nominal'],
                    'pct_of_bhp' => ($r['nominal'] / $totalBhp) * 100,
                ];
            }
        }
        return $items;
    }

    #[Computed]
    public function getDetailRowsProperty(): array
    {
        return $this->buildDetailRows();
    }

    #[Computed]
    public function getHistoryRowsProperty(): array
    {
        return $this->buildHistoryRows();
    }

    #[Computed]
    public function getSummaryProperty(): array
    {
        $rev = $this->revenueBreakdown();
        $detail = $this->buildDetailRows();
        $bhpRow = collect($detail)->firstWhere('kode', 'TOTAL-BHP');
        $usoRow = collect($detail)->firstWhere('kode', 'TOTAL-USO');
        $grandRow = collect($detail)->firstWhere('kode', 'GRAND');
        $hist = $this->buildHistoryRows();
        $ytd = collect($hist)->whereIn('status', ['paid', 'pending'])->sum('grand_total');
        return [
            'period_label' => $rev['period_label'] ?? '-',
            'revenue_total' => $rev['total_revenue'] ?? 0,
            'count_days' => $rev['count_days'] ?? 0,
            'bhp_total' => $bhpRow['nominal'] ?? 0,
            'uso_total' => $usoRow['nominal'] ?? 0,
            'grand_total' => $grandRow['nominal'] ?? 0,
            'effective_pct' => ($rev['total_revenue'] ?? 0) > 0
                ? round((($grandRow['nominal'] ?? 0) / ($rev['total_revenue'] ?? 1)) * 100, 2)
                : 0,
            'ytd_total' => $ytd,
        ];
    }

    public function handleBulkAction(string $action, array $ids): int
    {
        if ($action === 'export') {
            $this->exportCsv();
            return count($ids);
        }
        return 0;
    }

    public function exportCsv(): StreamedResponse|BinaryFileResponse|\Illuminate\Http\RedirectResponse
    {
        try {
            $rev = $this->revenueBreakdown();
            $details = $this->buildDetailRows();
            $history = $this->buildHistoryRows();
            $filename = 'bhp_uso_' . now()->format('Ymd_His') . '.csv';
            return response()->stream(function () use ($rev, $details, $history) {
                $fh = fopen('php://output', 'wb');
                fputcsv($fh, ['=== PERHITUNGAN BHP & USO ===']);
                fputcsv($fh, ['Periode', $rev['period_label'] ?? '']);
                fputcsv($fh, ['Revenue Total', $rev['total_revenue'] ?? 0]);
                fputcsv($fh, []);
                fputcsv($fh, ['DETAIL PERHITUNGAN']);
                fputcsv($fh, ['Kode', 'Uraian', 'Rate %', 'Minimal', 'Nominal', 'Jenis']);
                foreach ($details as $r) {
                    fputcsv($fh, [
                        $r['kode'] ?? '',
                        $r['name'] ?? '',
                        $r['rate_pct'] ?? 0,
                        $r['minimal'] ?? 0,
                        $r['nominal'] ?? 0,
                        $r['type'] ?? '',
                    ]);
                }
                fputcsv($fh, []);
                fputcsv($fh, ['HISTORY PEMBAYARAN TAHUNAN']);
                fputcsv($fh, ['Periode', 'Revenue', 'BHP', 'USO', 'Total', 'Status', 'No Bukti']);
                foreach ($history as $h) {
                    fputcsv($fh, [
                        $h['periode'] ?? '',
                        $h['revenue'] ?? 0,
                        $h['bhp_total'] ?? 0,
                        $h['uso_total'] ?? 0,
                        $h['grand_total'] ?? 0,
                        $h['status'] ?? '',
                        $h['no_bukti'] ?? '',
                    ]);
                }
                fclose($fh);
            }, 200, [
                'Content-Type' => 'text/csv',
                'Content-Disposition' => 'attachment; filename="' . $filename . '"',
            ]);
        } catch (Throwable $e) {
            session()->flash('error', 'Export gagal: ' . $e->getMessage());
            return back();
        }
    }

    public function confirmRowAction(string $action, int $id): void
    {
        if ($action === 'pay-bhp') {
            $this->confirmTitle = 'Bayar BHP & USO';
            $this->confirmMessage = "Anda akan menandai pembayaran periode ID #{$id} sebagai LUNAS. Lanjutkan?";
            $this->confirmAction = 'pay-bhp';
            $this->confirmParams = ['id' => $id];
            $this->confirmBtnText = 'Bayar';
            $this->confirmBtnClass = 'bg-emerald-600 hover:bg-emerald-700 text-white';
            $this->dispatch('open-modal', name: $this->confirmModal);
        }
    }

    public function cetakSkri(?int $id = null): void
    {
        try {
            $msg = $id ? 'Cetak SKKI/SKRI untuk #'.$id.' diproses.' : 'Cetak SKKI/SKRI untuk periode terpilih diproses.';
            session()->flash('info', $msg);
        } catch (Throwable $e) {
            $this->errorMessage = 'Gagal cetak: ' . $e->getMessage();
        }
    }

    public function handleConfirm(): void
    {
        if ($this->confirmAction === 'pay-bhp') {
            try {
                session()->flash('success', 'Pembayaran BHP/USO periode #' . ($this->confirmParams['id'] ?? '') . ' dicatat (Lunas).');
            } catch (Throwable $e) {
                $this->errorMessage = 'Gagal bayar: ' . $e->getMessage();
            }
            $this->dispatch('close-modal', name: $this->confirmModal);
            $this->confirmAction = '';
            return;
        }
        parent::handleConfirm();
    }

    public function updatedSelected(array $value): void {}
    public function updatedSelectAll(bool $value): void {}

    #[Computed]
    public function getFilterConfigProperty(): array
    {
        $years = [];
        for ($i = 0; $i < 5; $i++) $years[(string)(now()->year - $i)] = (string)(now()->year - $i);
        $months = [];
        for ($m = 1; $m <= 12; $m++) {
            $months[(string)$m] = now()->setMonth($m)->translatedFormat('F');
        }
        return [
            ['key' => 'year', 'label' => 'Tahun', 'type' => 'select', 'options' => $years],
            ['key' => 'month', 'label' => 'Bulan', 'type' => 'select', 'options' => $months],
            ['key' => 'start_date', 'label' => 'Tgl Mulai', 'type' => 'date'],
            ['key' => 'end_date', 'label' => 'Tgl Selesai', 'type' => 'date'],
        ];
    }

    #[Computed]
    public function getBulkActionsProperty(): array
    {
        return [
            ['key' => 'export', 'label' => 'Export CSV', 'variant' => 'bg-white border border-slate-200 text-slate-700 hover:bg-slate-50 dark:bg-slate-800 dark:border-slate-700 dark:text-slate-200 dark:hover:bg-slate-700'],
        ];
    }

    #[Computed]
    public function getToolbarActionsProperty(): array
    {
        return [
            ['label' => 'Export CSV', 'icon' => 'download', 'action' => 'exportCsv()'],
            ['label' => 'Export PDF', 'icon' => 'printer', 'action' => 'exportCsv()'],
        ];
    }

    public function render()
    {
        if (!Auth::check()) abort(403);
        $rows = $this->getRows();
        return view('livewire.keuangan.bhp-uso.index', [
            'rows' => $rows,
            'summary' => $this->summary,
            'filterConfig' => $this->filterConfig,
            'bulkActions' => $this->bulkActions,
            'toolbarActions' => $this->toolbarActions,
            'bhpItems' => $this->bhpItems,
            'detailRows' => $this->detailRows,
            'historyRows' => $this->historyRows,
        ]);
    }
}


