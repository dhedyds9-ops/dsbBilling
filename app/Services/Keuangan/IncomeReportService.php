<?php

namespace App\Services\Keuangan;

use App\Models\Billing\Invoice;
use App\Models\CRM\Customer;
use App\Models\Payment\Payment;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Src\Domain\Billing\Events\IncomeReportExportedEvent;

class IncomeReportService
{

    public function getTransactions(array $filters = []): array
    {
        $query = Invoice::whereIn('status', ['paid', 'success', 'lunas'])
            ->with(['customer.customerServices.serviceProfile', 'customer.reseller', 'customer.createdBy', 'items', 'payments' => function($q) {
                $q->where('status', 'success');
            }]);

        if (!empty($filters['start_date']) && !empty($filters['end_date'])) {
            $start = \Carbon\Carbon::parse($filters['start_date'])->startOfDay();
            $end = \Carbon\Carbon::parse($filters['end_date'])->endOfDay();
            $query->whereHas('payments', function($q) use ($start, $end) {
                $q->where('status', 'success')
                  ->whereBetween('paid_at', [$start, $end]);
            });
        }

        if (!empty($filters['reseller_id']) && $filters['reseller_id'] !== 'all') {
            $resellerId = $filters['reseller_id'];
            $query->whereHas('customer', function($q) use ($resellerId) {
                $q->where('reseller_id', $resellerId)
                  ->orWhere('created_by', $resellerId);
            });
        }

        if (!empty($filters['user_type']) && $filters['user_type'] !== 'all') {
            $userType = $filters['user_type'];
            $query->whereHas('customer', function($q) use ($userType) {
                if ($userType === 'customer') {
                    $q->where('role', 'customer');
                } else if ($userType === 'voucher') {
                    $q->where('role', 'voucher');
                }
            });
        }

        $invoices = $query->latest('updated_at')->get();

        $rows = collect();
        $totalProfit = 0;
        $totalFeeSeller = 0;
        $totalPlusPpn = 0;

        $calc = new \App\Services\Keuangan\SettlementCalculator();

        foreach ($invoices as $invoice) {
            $payment = $invoice->payments->sortByDesc('paid_at')->first();
            $paidAt = $payment ? $payment->paid_at : $invoice->updated_at;

            $hargaPpn = (float)$invoice->total_amount;
            $feeSeller = 0;
            
            foreach ($invoice->items as $item) {
                $qty = max(1, (int) $item->quantity);
                $unitPrice = (float) $item->unit_price;
                $ownerPrice = (float) $item->owner_settlement_price;
                $branchPrice = (float) $item->branch_settlement_price;
                $resellerPrice = (float) $item->reseller_settlement_price;

                $allocation = $calc->calculateItem(
                    $unitPrice,
                    $ownerPrice,
                    $branchPrice,
                    $resellerPrice,
                    $qty,
                    1.0
                );
                $feeSeller += $allocation['reseller_margin'];
            }

            $profit = $hargaPpn - $feeSeller;
            
            $serviceType = 'POST';
            $profilePaketId = null;

            if ($invoice->customer && $invoice->customer->customerServices->isNotEmpty()) {
                $plan = $invoice->customer->customerServices->first()->serviceProfile;
                if ($plan) {
                    $profilePaketId = $plan->id;
                    if ($plan->service_type === 'hotspot') {
                        $serviceType = 'PRE HOTSPOT';
                    } elseif ($plan->service_type === 'pppoe') {
                        $serviceType = 'POST PPPOE';
                    } else {
                        $serviceType = strtoupper($plan->service_type);
                    }
                }
            }

            if (!empty($filters['service_type']) && $filters['service_type'] !== 'all') {
                if (stripos($serviceType, $filters['service_type']) === false) {
                    continue;
                }
            }
            if (!empty($filters['profile_paket']) && $filters['profile_paket'] !== 'all') {
                if ($profilePaketId != $filters['profile_paket']) {
                    continue;
                }
            }

            $totalProfit += $profit;
            $totalFeeSeller += $feeSeller;
            $totalPlusPpn += $hargaPpn;

            $resellerName = '-';
            if ($invoice->customer) {
                if ($invoice->customer->reseller) {
                    $resellerName = $invoice->customer->reseller->name;
                } elseif ($invoice->customer->createdBy) {
                    $resellerName = $invoice->customer->createdBy->name;
                }
            }

            $rows->push([
                'id' => $invoice->id,
                'invoice_number' => $invoice->invoice_number,
                'customer_id' => $invoice->customer?->code ?? 'n/a',
                'customer_name' => $invoice->customer?->name ?? 'N/A',
                'reseller_name' => $resellerName,
                'service_type' => $serviceType,
                'package_name' => $invoice->items->first()?->description ?? 'Unknown',
                'harga_ppn' => $hargaPpn,
                'fee_seller' => $feeSeller,
                'profit' => $profit,
                'paid_at' => $paidAt ? $paidAt->format('Y-m-d H:i:s') : '-',
            ]);
        }

        return [
            'summary' => [
                'profit' => $totalProfit,
                'fee_seller' => $totalFeeSeller,
                'total_ppn' => $totalPlusPpn,
            ],
            'rows' => $rows
        ];
    }
    public function daily(array $filters = []): array
    {
        $startDate = !empty($filters['start_date']) ? now()->parse($filters['start_date']) : now()->subDays(6);
        $endDate = !empty($filters['end_date']) ? now()->parse($filters['end_date']) : now();

        $payments = Payment::where('status', 'success')
            ->whereBetween('paid_at', [$startDate->startOfDay(), $endDate->endOfDay()])
            ->when(!empty($filters['method']), fn($q) => $q->where('method', $filters['method']))
            ->when(!empty($filters['gateway']), fn($q) => $q->where('gateway', $filters['gateway']))
            ->when(!empty($filters['sales_id']), function ($q) use ($filters) {
                $q->whereHas('customer', function ($sq) use ($filters) {
                    $sq->where('sales_id', $filters['sales_id']);
                });
            })
            ->get();

        $daily = [];
        $dates = [];
        for ($d = $startDate->copy(); $d->lte($endDate); $d->addDay()) {
            $key = $d->format('Y-m-d');
            $dates[$key] = $d->format('d/m');
            $daily[$key] = [
                'date' => $key,
                'date_label' => $d->format('d/m'),
                'payment_count' => 0,
                'total_idr' => 0,
                'avg_idr' => 0,
                'customer_count' => 0,
                'method_top' => '-',
                'pppoe' => 0,
                'hotspot' => 0,
                'voucher' => 0,
                'other' => 0,
                'total' => 0,
                'customers' => collect(),
                'methods' => [],
            ];
        }

        foreach ($payments as $p) {
            $key = $p->paid_at ? $p->paid_at->format('Y-m-d') : $p->created_at->format('Y-m-d');
            if (!isset($daily[$key])) {
                continue;
            }
            $daily[$key]['payment_count']++;
            $daily[$key]['total_idr'] += (float) $p->amount;
            $daily[$key]['customers']->push($p->customer_id);
            $method = $p->method ?? 'other';
            $daily[$key]['methods'][$method] = ($daily[$key]['methods'][$method] ?? 0) + 1;

            $category = 'other';
            $gw = strtolower($p->gateway ?? '');
            if (str_contains($gw, 'pppoe') || str_contains($gw, 'isp')) {
                $category = 'pppoe';
            } elseif (str_contains($gw, 'hotspot')) {
                $category = 'hotspot';
            } elseif (str_contains($gw, 'voucher')) {
                $category = 'voucher';
            }
            $daily[$key][$category] += (float) $p->amount;
            $daily[$key]['total'] += (float) $p->amount;
        }

        foreach ($daily as $k => $d) {
            $daily[$k]['customer_count'] = $d['customers']->unique()->count();
            $daily[$k]['avg_idr'] = $d['payment_count'] > 0 ? round($d['total_idr'] / $d['payment_count'], 2) : 0;
            unset($daily[$k]['customers']);
            if (count($d['methods']) > 0) {
                arsort($daily[$k]['methods']);
                $top = array_key_first($daily[$k]['methods']);
                $daily[$k]['method_top'] = ucfirst(str_replace('_', ' ', $top));
            }
        }

        return array_values($daily);
    }

    public function chart30Days(): array
    {
        $start = now()->subDays(29);
        $end = now();

        $payments = Payment::where('status', 'success')
            ->whereBetween('paid_at', [$start->startOfDay(), $end->endOfDay()])
            ->select(
                DB::raw('DATE(paid_at) as date_key'),
                DB::raw('SUM(amount) as total'),
                'gateway',
            )
            ->groupBy('date_key', 'gateway')
            ->get();

        $series = [
            'labels' => [],
            'pppoe' => [],
            'hotspot' => [],
            'voucher' => [],
            'other' => [],
            'total' => [],
        ];

        $dataByDate = [];
        for ($d = $start->copy(); $d->lte($end); $d->addDay()) {
            $key = $d->format('Y-m-d');
            $series['labels'][] = $d->format('d');
            $dataByDate[$key] = ['pppoe' => 0, 'hotspot' => 0, 'voucher' => 0, 'other' => 0, 'total' => 0];
        }

        foreach ($payments as $p) {
            $key = $p->date_key;
            if (!isset($dataByDate[$key])) {
                continue;
            }
            $amount = (float) $p->total;
            $gw = strtolower($p->gateway ?? '');
            if (str_contains($gw, 'pppoe') || str_contains($gw, 'isp')) {
                $dataByDate[$key]['pppoe'] += $amount;
            } elseif (str_contains($gw, 'hotspot')) {
                $dataByDate[$key]['hotspot'] += $amount;
            } elseif (str_contains($gw, 'voucher')) {
                $dataByDate[$key]['voucher'] += $amount;
            } else {
                $dataByDate[$key]['other'] += $amount;
            }
            $dataByDate[$key]['total'] += $amount;
        }

        foreach ($dataByDate as $row) {
            $series['pppoe'][] = $row['pppoe'];
            $series['hotspot'][] = $row['hotspot'];
            $series['voucher'][] = $row['voucher'];
            $series['other'][] = $row['other'];
            $series['total'][] = $row['total'];
        }

        return $series;
    }

    public function topCustomers(array $filters = [], int $limit = 10): array
    {
        $startDate = !empty($filters['start_date']) ? now()->parse($filters['start_date']) : now()->subDays(30);
        $endDate = !empty($filters['end_date']) ? now()->parse($filters['end_date']) : now();

        $rows = Payment::where('status', 'success')
            ->whereBetween('paid_at', [$startDate->startOfDay(), $endDate->endOfDay()])
            ->select('customer_id', DB::raw('SUM(amount) as total_spent'), DB::raw('COUNT(*) as payment_count'))
            ->with('customer:id,name,email')
            ->groupBy('customer_id')
            ->orderByDesc('total_spent')
            ->limit($limit)
            ->get();

        return $rows->map(fn($r) => [
            'id' => $r->customer_id,
            'name' => $r->customer?->name ?? '-',
            'email' => $r->customer?->email ?? '-',
            'total_spent' => (float) $r->total_spent,
            'payment_count' => (int) $r->payment_count,
        ])->toArray();
    }

    public function topSales(array $filters = [], int $limit = 10): array
    {
        $startDate = !empty($filters['start_date']) ? now()->parse($filters['start_date']) : now()->subDays(30);
        $endDate = !empty($filters['end_date']) ? now()->parse($filters['end_date']) : now();

        $customerIds = Payment::where('status', 'success')
            ->whereBetween('paid_at', [$startDate->startOfDay(), $endDate->endOfDay()])
            ->pluck('customer_id');

        $sales = Customer::whereIn('id', $customerIds)
            ->whereNotNull('sales_id')
            ->select('sales_id', DB::raw('COUNT(*) as customer_count'))
            ->with(['salesUser:id,name'])
            ->groupBy('sales_id')
            ->orderByDesc('customer_count')
            ->limit($limit)
            ->get();

        return $sales->map(fn($r) => [
            'id' => $r->sales_id,
            'name' => $r->salesUser?->name ?? '-',
            'customer_count' => (int) $r->customer_count,
        ])->toArray();
    }

    public function paymentMethodBreakdown(array $filters = []): array
    {
        $startDate = !empty($filters['start_date']) ? now()->parse($filters['start_date']) : now()->subDays(30);
        $endDate = !empty($filters['end_date']) ? now()->parse($filters['end_date']) : now();

        $rows = Payment::where('status', 'success')
            ->whereBetween('paid_at', [$startDate->startOfDay(), $endDate->endOfDay()])
            ->select('method', DB::raw('SUM(amount) as total'), DB::raw('COUNT(*) as count'))
            ->groupBy('method')
            ->orderByDesc('total')
            ->get();

        $grandTotal = $rows->sum('total');

        return $rows->map(fn($r) => [
            'method' => $r->method ?? 'other',
            'label' => ucfirst(str_replace('_', ' ', $r->method ?? 'Other')),
            'total' => (float) $r->total,
            'count' => (int) $r->count,
            'percent' => $grandTotal > 0 ? round(((float) $r->total / $grandTotal) * 100, 1) : 0,
        ])->toArray();
    }

    public function cashFlowMini(array $filters = []): array
    {
        $startDate = !empty($filters['start_date']) ? now()->parse($filters['start_date']) : now()->subDays(30);
        $endDate = !empty($filters['end_date']) ? now()->parse($filters['end_date']) : now();

        $income = Payment::where('status', 'success')
            ->whereBetween('paid_at', [$startDate->startOfDay(), $endDate->endOfDay()])
            ->sum('amount');

        if (class_exists(\App\Models\Keuangan\Expense::class)) {
            try {
                $expense = \App\Models\Keuangan\Expense::approved()
                    ->betweenDates($startDate->toDateString(), $endDate->toDateString())
                    ->sum('amount');
            } catch (\Throwable) {
                $expense = 0;
            }
        } else {
            $expense = 0;
        }

        return [
            'income' => (float) $income,
            'expense' => (float) $expense,
            'net' => (float) $income - (float) $expense,
        ];
    }

    public function dailySummary(array $filters = []): array
    {
        $today = now();
        $income = Payment::where('status', 'success')
            ->whereBetween('paid_at', [$today->startOfDay(), $today->endOfDay()]);
        $paymentsCount = (clone $income)->count();
        $total = (clone $income)->sum('amount');
        $customerCount = (clone $income)->distinct('customer_id')->count('customer_id');
        $avg = $paymentsCount > 0 ? round($total / $paymentsCount, 2) : 0;
        $ytd = Payment::where('status', 'success')
            ->whereBetween('paid_at', [$today->copy()->startOfYear(), $today->endOfDay()])
            ->sum('amount');

        return [
            'today_total' => (float) $total,
            'today_count' => $paymentsCount,
            'today_customers' => $customerCount,
            'today_avg' => (float) $avg,
            'ytd' => (float) $ytd,
        ];
    }

    public function periodComparison(string $type = 'monthly', array $filters = []): array
    {
        $year = (int) ($filters['year'] ?? now()->year);
        $month = (int) ($filters['month'] ?? now()->month);

        $labels = [];
        $currentData = [];
        $prevData = [];

        if ($type === 'monthly') {
            $currentStart = now()->setYear($year)->setMonth($month)->startOfMonth();
            $currentEnd = $currentStart->copy()->endOfMonth();
            $prevStart = $currentStart->copy()->subMonth()->startOfMonth();
            $prevEnd = $prevStart->copy()->endOfMonth();

            for ($d = 1; $d <= $currentStart->daysInMonth; $d++) {
                $labels[] = $d;
                $currentData[] = 0;
                $prevData[] = 0;
            }

            $currentPayments = Payment::where('status', 'success')
                ->whereBetween('paid_at', [$currentStart, $currentEnd])
                ->select(DB::raw('DAY(paid_at) as day_num'), DB::raw('SUM(amount) as total'))
                ->groupBy('day_num')
                ->pluck('total', 'day_num');

            $prevPayments = Payment::where('status', 'success')
                ->whereBetween('paid_at', [$prevStart, $prevEnd])
                ->select(DB::raw('DAY(paid_at) as day_num'), DB::raw('SUM(amount) as total'))
                ->groupBy('day_num')
                ->pluck('total', 'day_num');

            foreach ($currentPayments as $day => $tot) {
                if (isset($currentData[$day - 1])) {
                    $currentData[$day - 1] = (float) $tot;
                }
            }
            foreach ($prevPayments as $day => $tot) {
                if (isset($prevData[$day - 1])) {
                    $prevData[$day - 1] = (float) $tot;
                }
            }

            $currentTotal = array_sum($currentData);
            $prevTotal = array_sum($prevData);
            $growth = $prevTotal > 0 ? round((($currentTotal - $prevTotal) / $prevTotal) * 100, 2) : 0;
            $periodLabel = $currentStart->translatedFormat('F Y');
            $prevPeriodLabel = $prevStart->translatedFormat('F Y');
        } else {
            for ($m = 1; $m <= 12; $m++) {
                $labels[] = now()->setMonth($m)->translatedFormat('M');
                $currentData[] = 0;
                $prevData[] = 0;
            }

            $currentStart = now()->setYear($year)->startOfYear();
            $currentEnd = $currentStart->copy()->endOfYear();
            $prevStart = $currentStart->copy()->subYear()->startOfYear();
            $prevEnd = $prevStart->copy()->endOfYear();

            $currentPayments = Payment::where('status', 'success')
                ->whereBetween('paid_at', [$currentStart, $currentEnd])
                ->select(DB::raw('MONTH(paid_at) as month_num'), DB::raw('SUM(amount) as total'))
                ->groupBy('month_num')
                ->pluck('total', 'month_num');

            $prevPayments = Payment::where('status', 'success')
                ->whereBetween('paid_at', [$prevStart, $prevEnd])
                ->select(DB::raw('MONTH(paid_at) as month_num'), DB::raw('SUM(amount) as total'))
                ->groupBy('month_num')
                ->pluck('total', 'month_num');

            foreach ($currentPayments as $m => $tot) {
                if (isset($currentData[$m - 1])) {
                    $currentData[$m - 1] = (float) $tot;
                }
            }
            foreach ($prevPayments as $m => $tot) {
                if (isset($prevData[$m - 1])) {
                    $prevData[$m - 1] = (float) $tot;
                }
            }

            $currentTotal = array_sum($currentData);
            $prevTotal = array_sum($prevData);
            $growth = $prevTotal > 0 ? round((($currentTotal - $prevTotal) / $prevTotal) * 100, 2) : 0;
            $periodLabel = 'Tahun ' . $year;
            $prevPeriodLabel = 'Tahun ' . ($year - 1);
        }

        $periodRows = $this->buildPeriodRows($type, $filters);

        return [
            'type' => $type,
            'period_label' => $periodLabel,
            'prev_period_label' => $prevPeriodLabel,
            'current_total' => $currentTotal,
            'prev_total' => $prevTotal,
            'growth_percent' => $growth,
            'labels' => $labels,
            'current_data' => $currentData,
            'prev_data' => $prevData,
            'rows' => $periodRows,
        ];
    }

    protected function buildPeriodRows(string $type, array $filters): array
    {
        $year = (int) ($filters['year'] ?? now()->year);
        $rows = [];

        if ($type === 'monthly') {
            $month = (int) ($filters['month'] ?? now()->month);
            $start = now()->setYear($year)->setMonth($month)->startOfMonth();
            $end = $start->copy()->endOfMonth();

            $payments = Payment::where('status', 'success')
                ->whereBetween('paid_at', [$start, $end])
                ->get();

            $invoiceCount = Invoice::whereBetween('created_at', [$start, $end])->count();
            $topPaket = $this->detectTopPackage($payments);

            $rows[] = [
                'period' => $start->translatedFormat('F Y'),
                'invoice_count' => $invoiceCount,
                'payment_count' => $payments->count(),
                'total' => (float) $payments->sum('amount'),
                'growth' => 0,
                'top_package' => $topPaket,
            ];
        } else {
            for ($m = 1; $m <= 12; $m++) {
                $start = now()->setYear($year)->setMonth($m)->startOfMonth();
                $end = $start->copy()->endOfMonth();

                $payments = Payment::where('status', 'success')
                    ->whereBetween('paid_at', [$start, $end])
                    ->get();

                $prevStart = $start->copy()->subMonth();
                $prevEnd = $prevStart->copy()->endOfMonth();
                $prevTotal = (float) Payment::where('status', 'success')
                    ->whereBetween('paid_at', [$prevStart, $prevEnd])
                    ->sum('amount');

                $currentTotal = (float) $payments->sum('amount');
                $growth = $prevTotal > 0 ? round((($currentTotal - $prevTotal) / $prevTotal) * 100, 2) : 0;

                $invoiceCount = Invoice::whereBetween('created_at', [$start, $end])->count();
                $topPaket = $this->detectTopPackage($payments);

                $rows[] = [
                    'period' => $start->translatedFormat('M Y'),
                    'invoice_count' => $invoiceCount,
                    'payment_count' => $payments->count(),
                    'total' => $currentTotal,
                    'growth' => $growth,
                    'top_package' => $topPaket,
                ];
            }
        }

        return $rows;
    }

    protected function detectTopPackage($payments): string
    {
        $gatewayCounts = [];
        foreach ($payments as $p) {
            $gw = strtolower($p->gateway ?? 'other');
            if (str_contains($gw, 'pppoe') || str_contains($gw, 'isp')) {
                $name = 'PPPoE';
            } elseif (str_contains($gw, 'hotspot')) {
                $name = 'Hotspot';
            } elseif (str_contains($gw, 'voucher')) {
                $name = 'Voucher';
            } else {
                $name = 'Lainnya';
            }
            $gatewayCounts[$name] = ($gatewayCounts[$name] ?? 0) + 1;
        }
        if (count($gatewayCounts) === 0) {
            return '-';
        }
        arsort($gatewayCounts);
        return (string) array_key_first($gatewayCounts);
    }

    public function exportCsvDaily(array $rows): \Symfony\Component\HttpFoundation\BinaryFileResponse
    {
        $headers = [
            'Content-Type' => 'text/csv; charset=utf-8',
            'Content-Disposition' => 'attachment; filename="income-harian-' . now()->format('YmdHis') . '.csv"',
        ];

        $callback = function () use ($rows) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['Tanggal', 'Jumlah Payment', 'Total IDR', 'Avg IDR', 'Jumlah Customer', 'Method Teratas']);
            foreach ($rows as $r) {
                fputcsv($handle, [
                    $r['date'] ?? '',
                    $r['payment_count'] ?? 0,
                    $r['total_idr'] ?? 0,
                    $r['avg_idr'] ?? 0,
                    $r['customer_count'] ?? 0,
                    $r['method_top'] ?? '-',
                ]);
            }
            fclose($handle);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function excelExport(array $params, int $userId): \Symfony\Component\HttpFoundation\BinaryFileResponse
    {
        $type = $params['type'] ?? 'monthly';
        $filters = $params['filters'] ?? [];
        $data = $this->periodComparison($type, $filters);

        Event::dispatch(new IncomeReportExportedEvent(
            'excel',
            $type,
            $filters,
            $userId,
            now()->toISOString(),
        ));

        $headers = [
            'Content-Type' => 'text/csv; charset=utf-8',
            'Content-Disposition' => 'attachment; filename="income-periode-' . $type . '-' . now()->format('YmdHis') . '.csv"',
        ];

        $rows = $data['rows'] ?? [];
        $callback = function () use ($rows, $data) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['Periode: ' . ($data['period_label'] ?? '')]);
            fputcsv($handle, ['Total Pendapatan', $data['current_total'] ?? 0]);
            fputcsv($handle, ['Growth (%)', $data['growth_percent'] ?? 0]);
            fputcsv($handle, []);
            fputcsv($handle, ['Periode', 'Jumlah Invoice', 'Jumlah Payment', 'Total Pendapatan', 'Growth (%)', 'Top Paket']);
            foreach ($rows as $r) {
                fputcsv($handle, [
                    $r['period'] ?? '',
                    $r['invoice_count'] ?? 0,
                    $r['payment_count'] ?? 0,
                    $r['total'] ?? 0,
                    $r['growth'] ?? 0,
                    $r['top_package'] ?? '-',
                ]);
            }
            fclose($handle);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function pdfExport(array $params, int $userId): \Symfony\Component\HttpFoundation\BinaryFileResponse
    {
        $type = $params['type'] ?? 'monthly';
        $filters = $params['filters'] ?? [];

        Event::dispatch(new IncomeReportExportedEvent(
            'pdf',
            $type,
            $filters,
            $userId,
            now()->toISOString(),
        ));

        $headers = [
            'Content-Type' => 'text/html; charset=utf-8',
            'Content-Disposition' => 'attachment; filename="income-periode-' . $type . '-' . now()->format('YmdHis') . '.html"',
        ];

        $data = $this->periodComparison($type, $filters);
        $html = '<html><head><meta charset="utf-8"><title>Laporan Pendapatan Periode</title></head><body><h1>Laporan Pendapatan ' . ($data['period_label'] ?? '') . '</h1><p>Total: ' . ($data['current_total'] ?? 0) . '</p><p>Growth: ' . ($data['growth_percent'] ?? 0) . '%</p></body></html>';

        $callback = function () use ($html) {
            echo $html;
        };

        return response()->stream($callback, 200, $headers);
    }

    public function getSalesOptions(): array
    {
        return User::whereHas('roles', function ($q) {
            $q->whereIn('name', ['sales', 'admin', 'owner']);
        })->pluck('name', 'id')->toArray();
    }
}

