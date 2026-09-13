<?php

namespace App\Livewire\Keuangan\LabaRugi;

use App\Livewire\BaseEnterpriseList;
use App\Services\Keuangan\IncomeReportService;
use App\Services\Keuangan\ExpenseService;
use App\Services\Keuangan\FinancialStatementService;
use App\Services\Auth\UserQueryService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Throwable;
use Livewire\Attributes\Computed;

class Index extends BaseEnterpriseList
{
    public string $activeModule = 'keuangan';
    public string $activePage = 'laba-rugi';

    public array $tabs = [
        'income_statement' => 'Income Statement',
        'cash_flow' => 'Cash Flow',
        'expense_detail' => 'Expense Detail',
        'top_revenue' => 'Top Revenue',
        'ar_aging' => 'AR Aging',
    ];

    protected ?IncomeReportService $incomeSvc = null;
    protected ?ExpenseService $expenseSvc = null;
    protected ?FinancialStatementService $financialSvc = null;

    public function boot(IncomeReportService $isvc, ExpenseService $esvc, FinancialStatementService $fsvc): void
    {
        $this->incomeSvc = $isvc;
        $this->expenseSvc = $esvc;
        $this->financialSvc = $fsvc;
    }

    public function mount(): void
    {
        parent::mount();
        $this->authorizeAccess();
        $this->activeModule = 'keuangan';
        $this->activePage = 'laba-rugi';
        $this->filters = [
            'year' => (string) now()->year,
            'month' => (string) now()->month,
            'start_date' => now()->startOfMonth()->toDateString(),
            'end_date' => now()->toDateString(),
        ];
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
        return collect($this->getTopRevenueData()['rows'] ?? []);
    }

    public function getRows()
    {
        return $this->withLoading(function () {
            if ($this->activeTab === 'expense_detail') {
                return $this->getExpenseRows();
            }
            if ($this->activeTab === 'top_revenue') {
                return collect($this->getTopRevenueData()['rows'] ?? []);
            }
            if ($this->activeTab === 'ar_aging') {
                return collect($this->getArAgingData()['rows'] ?? []);
            }
            return collect([]);
        }, 'Gagal memuat data Laba Rugi');
    }

    protected function getExpenseRows()
    {
        try {
            return $this->expenseSvc->list($this->filters, $this->search, $this->sortField, $this->sortDirection, $this->activeTab)
                ->paginate($this->perPage);
        } catch (Throwable $e) {
            Log::error('LabaRugi expense rows failed', ['e' => $e->getMessage()]);
            return collect([])->paginate($this->perPage);
        }
    }

    #[Computed]
    public function getIncomeStatementProperty(): array
    {
        try {
            $from = $this->filters['start_date'] ?: now()->startOfMonth()->toDateString();
            $to = $this->filters['end_date'] ?: now()->toDateString();
            $filters = array_merge($this->filters, ['start_period' => $from, 'end_period' => $to]);
            
            $fin = $this->financialSvc ? $this->financialSvc->incomeStatement($filters) : [];
            
            $rev = $fin['revenue'] ?? [];
            $cogsFin = $fin['cogs'] ?? [];
            
            $totalIncome = (float)($rev['total'] ?? 0);
            
            try {
                $expTotal = method_exists($this->expenseSvc, 'summary') ? ($this->expenseSvc->summary()['monthly_total'] ?? 0) : 0;
            } catch (Throwable) {
                $expTotal = 0;
            }

            $hppInternet = (float)($cogsFin['hpp_internet'] ?? 0);
            $feeReseller = (float)($cogsFin['fee_reseller'] ?? 0);
            $feeBranch = (float)($cogsFin['fee_branch'] ?? 0);
            $totalCogs = $hppInternet + $feeReseller + $feeBranch;
            
            $gross = $totalIncome - $totalCogs;
            $opex = $expTotal; // Treat external expenses as OPEX
            $ebitda = $gross - $opex;
            $depreciation = 0;
            $ebit = $ebitda - $depreciation;
            $tax = max(0, $ebit * 0.11);
            $net_after_tax = $ebit - $tax;

            return [
                'period_label' => date('M Y', strtotime($from)),
                'start_date' => $from,
                'end_date' => $to,
                'revenue' => [
                    ['label' => 'PPPoE', 'amount' => $rev['pppoe'] ?? 0],
                    ['label' => 'Hotspot', 'amount' => $rev['hotspot'] ?? 0],
                    ['label' => 'Voucher', 'amount' => $rev['voucher'] ?? 0],
                    ['label' => 'Lainnya', 'amount' => ($rev['evoucher'] ?? 0) + ($rev['other'] ?? 0)],
                ],
                'total_revenue' => $totalIncome,
                'cogs' => [
                    ['label' => 'HPP Internet (Pusat/Provider)', 'amount' => $hppInternet],
                    ['label' => 'Bagi Hasil Reseller', 'amount' => $feeReseller],
                    ['label' => 'Bagi Hasil Branch', 'amount' => $feeBranch],
                ],
                'gross_profit' => $gross,
                'gross_margin_pct' => $totalIncome > 0 ? round(($gross / $totalIncome) * 100, 2) : 0,
                'opex' => [
                    ['label' => 'Beban Operasional (OPEX)', 'amount' => $opex],
                ],
                'operating_expenses' => [
                    'total' => $opex,
                ],
                'depreciation' => $depreciation,
                'ebitda' => $ebitda,
                'ebit' => $ebit,
                'tax' => $tax,
                'net_income' => $net_after_tax,
                'net_margin_pct' => $totalIncome > 0 ? round(($net_after_tax / $totalIncome) * 100, 2) : 0,
            ];
        } catch (Throwable $e) {
            Log::error('LabaRugi income statement failed', ['e' => $e->getMessage()]);
            return [
                'period_label' => now()->format('M Y'),
                'revenue' => [],
                'total_revenue' => 0,
                'cogs' => [], 'gross_profit' => 0, 'gross_margin_pct' => 0,
                'opex' => [],
                'operating_expenses' => ['total' => 0],
                'depreciation' => 0,
                'ebitda' => 0, 'ebit' => 0, 'tax' => 0, 'net_income' => 0, 'net_margin_pct' => 0,
            ];
        }
    }

    #[Computed]
    public function getCashFlowProperty(): array
    {
        try {
            $from = $this->filters['start_date'] ?: now()->startOfMonth()->toDateString();
            $to = $this->filters['end_date'] ?: now()->toDateString();
            
            $is = $this->incomeStatement;
            $operating_in = $is['total_revenue'] ?? 0;
            
            // Outflows
            $cogsTotal = array_sum(array_column($is['cogs'] ?? [], 'amount'));
            $opexTotal = array_sum(array_column($is['opex'] ?? [], 'amount'));
            
            $operating_out = $cogsTotal + $opexTotal;
            $investing_out = 0;
            $financing_in = 0;
            $financing_out = 0;
            $beginning = 0;
            $net = ($operating_in - $operating_out) + (0 - $investing_out) + ($financing_in - $financing_out);

            return [
                'from' => $from,
                'to' => $to,
                'operating' => [
                    'in' => $operating_in,
                    'out' => $operating_out,
                    'net' => $operating_in - $operating_out,
                ],
                'investing' => [
                    'in' => 0,
                    'out' => $investing_out,
                    'net' => 0 - $investing_out,
                ],
                'financing' => [
                    'in' => $financing_in,
                    'out' => $financing_out,
                    'net' => $financing_in - $financing_out,
                ],
                'beginning_balance' => $beginning,
                'net_change' => $net,
                'ending_balance' => $beginning + $net,
                'monthly' => $this->buildMonthlyCashflow(),
            ];
        } catch (Throwable $e) {
            Log::error('LabaRugi cashflow failed', ['e' => $e->getMessage()]);
            return [
                'operating' => ['in' => 0, 'out' => 0, 'net' => 0],
                'investing' => ['in' => 0, 'out' => 0, 'net' => 0],
                'financing' => ['in' => 0, 'out' => 0, 'net' => 0],
                'beginning_balance' => 0, 'net_change' => 0, 'ending_balance' => 0,
                'monthly' => ['labels' => [], 'in' => [], 'out' => [], 'net' => []],
            ];
        }
    }

    protected function buildMonthlyCashflow(): array
    {
        $labels = []; $in = []; $out = []; $net = [];
        $year = (int) ($this->filters['year'] ?? now()->year);
        
        for ($m = 1; $m <= 12; $m++) {
            $labels[] = now()->setMonth($m)->translatedFormat('M');
            
            $start = now()->setYear($year)->setMonth($m)->startOfMonth()->toDateString();
            $end = now()->setYear($year)->setMonth($m)->endOfMonth()->toDateString();
            
            try {
                $fin = $this->financialSvc ? $this->financialSvc->incomeStatement(['start_period' => $start, 'end_period' => $end]) : [];
                $income = (float)($fin['revenue']['total'] ?? 0);
                
                $cogs = (float)($fin['cogs']['total'] ?? 0);
                $expSummary = $this->expenseSvc ? $this->expenseSvc->summary(['start_date' => $start, 'end_date' => $end]) : [];
                $opex = (float)($expSummary['monthly_total'] ?? 0);
                $expense = $cogs + $opex;
            } catch (Throwable) {
                $income = 0; $expense = 0;
            }
            
            $in[] = $income;
            $out[] = $expense;
            $net[] = $income - $expense;
        }
        return compact('labels', 'in', 'out', 'net');
    }

    protected function getTopRevenueData(): array
    {
        try {
            $from = $this->filters['start_date'] ?: now()->startOfMonth()->toDateString();
            $to = $this->filters['end_date'] ?: now()->toDateString();
            $filters = array_merge($this->filters, ['start_period' => $from, 'end_period' => $to]);
            
            $tr = $this->financialSvc ? $this->financialSvc->topRevenue($filters, 20) : [];
            $custs = $tr['top_customers'] ?? [];
            
            $rowsOut = [];
            $topSum = collect($custs)->sum('total_spent');
            foreach ($custs as $c) {
                $rowsOut[] = [
                    'id' => $c['id'] ?? 0,
                    'name' => $c['name'] ?? '-',
                    'amount' => $c['total_spent'] ?? 0,
                    'count' => $c['payment_count'] ?? 0,
                    'code' => $c['phone'] ?? '',
                    'pct' => $topSum > 0 ? (($c['total_spent'] ?? 0) / $topSum) * 100 : 0
                ];
            }
            
            return ['rows' => $rowsOut, 'top_sum' => collect($rowsOut)->sum('amount')];
        } catch (Throwable $e) {
            Log::error('LabaRugi top revenue failed', ['e' => $e->getMessage()]);
            return ['rows' => [], 'top_sum' => 0];
        }
    }

    protected function getArAgingData(): array
    {
        try {
            $dateDiff = \App\Services\Support\DbCompat::dateDiffDays(
                \App\Services\Support\DbCompat::currentDate(),
                'due_date'
            );
            $agingOrder = \App\Services\Support\DbCompat::fieldOrder(
                'aging_bucket',
                ['Current', '0-30', '31-60', '61-90', '91-180', '>180']
            );

            $rows = \App\Models\Billing\Invoice::query()
                ->select([
                    DB::raw('COUNT(*) as count'),
                    DB::raw('COALESCE(SUM(total_amount - paid_amount), 0) as total'),
                    DB::raw("CASE
                        WHEN {$dateDiff} BETWEEN 0 AND 30 THEN '0-30'
                        WHEN {$dateDiff} BETWEEN 31 AND 60 THEN '31-60'
                        WHEN {$dateDiff} BETWEEN 61 AND 90 THEN '61-90'
                        WHEN {$dateDiff} BETWEEN 91 AND 180 THEN '91-180'
                        WHEN {$dateDiff} > 180 THEN '>180'
                        ELSE 'Current' END AS aging_bucket"),
                ])
                ->whereRaw('(total_amount - paid_amount) > 0')
                ->groupBy('aging_bucket')
                ->orderByRaw($agingOrder)
                ->get()
                ->all();
            $rowsOut = [];
            $order = ['Current', '0-30', '31-60', '61-90', '91-180', '>180'];
            $dict = [];
            foreach ($rows as $r) $dict[$r->aging_bucket] = $r;
            foreach ($order as $b) {
                if (isset($dict[$b])) {
                    $r = $dict[$b];
                    $rowsOut[] = ['bucket' => $b, 'count' => (int)$r->count, 'total' => (float)$r->total];
                } else {
                    $rowsOut[] = ['bucket' => $b, 'count' => 0, 'total' => 0];
                }
            }
            $grand = array_sum(array_column($rowsOut, 'total'));
            return ['rows' => $rowsOut, 'grand_total' => $grand];
        } catch (Throwable $e) {
            Log::error('LabaRugi AR Aging failed', ['e' => $e->getMessage()]);
            return [
                'rows' => [
                    ['bucket' => 'Current', 'count' => 0, 'total' => 0],
                    ['bucket' => '0-30', 'count' => 0, 'total' => 0],
                    ['bucket' => '31-60', 'count' => 0, 'total' => 0],
                    ['bucket' => '61-90', 'count' => 0, 'total' => 0],
                    ['bucket' => '91-180', 'count' => 0, 'total' => 0],
                    ['bucket' => '>180', 'count' => 0, 'total' => 0],
                ],
                'grand_total' => 0,
            ];
        }
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
            $is = $this->incomeStatement;
            $cf = $this->cashFlow;
            $ar = $this->getArAgingData();
            $filename = 'laba_rugi_' . now()->format('Ymd_His') . '.csv';
            return response()->stream(function () use ($is, $cf, $ar) {
                $fh = fopen('php://output', 'wb');
                fputcsv($fh, ['=== LAPORAN LABA RUGI ===']);
                fputcsv($fh, ['Periode', $is['period_label'] ?? '']);
                fputcsv($fh, []);
                fputcsv($fh, ['INCOME STATEMENT']);
                fputcsv($fh, ['Revenue Total', $is['revenue']['total'] ?? 0]);
                fputcsv($fh, ['- PPPoE', $is['revenue']['pppoe'] ?? 0]);
                fputcsv($fh, ['- Hotspot', $is['revenue']['hotspot'] ?? 0]);
                fputcsv($fh, ['- Voucher', $is['revenue']['voucher'] ?? 0]);
                fputcsv($fh, ['Gross Profit', $is['gross_profit'] ?? 0]);
                fputcsv($fh, ['Opex Total', $is['operating_expenses']['total'] ?? 0]);
                fputcsv($fh, ['EBITDA', $is['ebitda'] ?? 0]);
                fputcsv($fh, ['Net Income', $is['net_after_tax'] ?? 0]);
                fputcsv($fh, []);
                fputcsv($fh, ['AR AGING']);
                foreach (($ar['rows'] ?? []) as $r) {
                    fputcsv($fh, [$r['bucket'] ?? '-', $r['count'] ?? 0, $r['total'] ?? 0]);
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

    public function exportPdf(): void
    {
        try {
            session()->flash('success', 'Export PDF Laporan Keuangan diproses.');
            $this->dispatch('refreshPage');
        } catch (Throwable $e) {
            $this->errorMessage = 'Export PDF gagal: ' . $e->getMessage();
        }
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
        
        $resellers = ['' => 'Semua Reseller/Global'];
        try {
            $userQuery = app(UserQueryService::class);
            foreach ($userQuery->getResellers() as $r) {
                $resellers[(string)$r->id] = $r->name;
            }
        } catch (\Throwable $e) {}

        return [
            ['key' => 'reseller_id', 'label' => 'Reseller', 'type' => 'select', 'options' => $resellers],
            ['key' => 'year', 'label' => 'Tahun', 'type' => 'select', 'options' => $years],
            ['key' => 'month', 'label' => 'Bulan', 'type' => 'select', 'options' => $months],
            ['key' => 'start_date', 'label' => 'Tgl Mulai', 'type' => 'date'],
            ['key' => 'end_date', 'label' => 'Tgl Selesai', 'type' => 'date'],
        ];
    }

    public function getBulkActionsProperty(): array
    {
        return [
            ['key' => 'export', 'label' => 'Export CSV', 'variant' => 'bg-white border border-slate-200 text-slate-700 hover:bg-slate-50 dark:bg-slate-800 dark:border-slate-700 dark:text-slate-200 dark:hover:bg-slate-700'],
        ];
    }

    public function getToolbarActionsProperty(): array
    {
        return [
            ['label' => 'Export CSV', 'icon' => 'download', 'action' => 'exportCsv()'],
            ['label' => 'Export PDF', 'icon' => 'printer', 'action' => 'exportPdf()'],
        ];
    }

    public function render()
    {
        if (!Auth::check()) abort(403);
        $rows = $this->getRows();
        return view('livewire.keuangan.laba-rugi.index', [
            'rows' => $rows,
            'incomeStatement' => $this->incomeStatement,
            'cashFlow' => $this->cashFlow,
            'topRevenue' => $this->getTopRevenueData(),
            'arAging' => $this->getArAgingData(),
            'filterConfig' => $this->filterConfig,
            'bulkActions' => $this->bulkActions,
            'toolbarActions' => $this->toolbarActions,
        ]);
    }
}
