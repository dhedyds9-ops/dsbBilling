<?php

namespace App\Services\Laporan;

use App\Models\Billing\Invoice;
use App\Models\Payment\Payment;
use App\Models\ISP\ServiceProfile;
use App\Models\CRM\Customer;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Carbon;
use Src\Domain\Laporan\Events\PendapatanReportExportedEvent;

class PendapatanService
{
    protected function applyFilters($query, array $filters)
    {
        if (!empty($filters['tahun'])) {
            $query->whereYear('paid_at', $filters['tahun']);
        }
        if (!empty($filters['bulan'])) {
            $query->whereMonth('paid_at', $filters['bulan']);
        }
        if (!empty($filters['start_date'])) {
            $query->whereDate('paid_at', '>=', $filters['start_date']);
        }
        if (!empty($filters['end_date'])) {
            $query->whereDate('paid_at', '<=', $filters['end_date']);
        }
        if (!empty($filters['router_id'])) {
            $query->whereHas('customer.customerServices', function ($q) use ($filters) {
                $q->where('router_id', $filters['router_id']);
            });
        }
        if (!empty($filters['paket_id'])) {
            $query->whereHas('invoices.items', function ($q) use ($filters) {
                $q->where('package_id', $filters['paket_id']);
            });
        }
        if (!empty($filters['reseller_id'])) {
            $query->whereHas('customer', function ($q) use ($filters) {
                $q->where('reseller_id', $filters['reseller_id']);
            });
        }
        if (\Illuminate\Support\Facades\Auth::check() && \Illuminate\Support\Facades\Auth::user()->hasRole('reseller')) {
            $query->whereHas('customer', function ($q) {
                $q->where('reseller_id', \Illuminate\Support\Facades\Auth::id());
            });
        }
        return $query;
    }

    public function summary(array $filters = []): array
    {
        $now = Carbon::now();
        $bulanIni = $now->copy()->startOfMonth();
        $bulanLalu = $now->copy()->subMonth()->startOfMonth();
        $bulanLaluEnd = $now->copy()->subMonth()->endOfMonth();
        $ytdStart = $now->copy()->startOfYear();

        $paymentsQuery = Payment::where('status', 'success');

        $pendapatanBulanIni = (clone $paymentsQuery)
            ->whereBetween('paid_at', [$bulanIni, $now])
            ->sum('amount');

        $pendapatanBulanLalu = (clone $paymentsQuery)
            ->whereBetween('paid_at', [$bulanLalu, $bulanLaluEnd])
            ->sum('amount');

        $pctVsLalu = $pendapatanBulanLalu > 0
            ? round((($pendapatanBulanIni - $pendapatanBulanLalu) / $pendapatanBulanLalu) * 100, 2)
            : ($pendapatanBulanIni > 0 ? 100 : 0);

        $ytd = (clone $paymentsQuery)
            ->whereBetween('paid_at', [$ytdStart, $now])
            ->sum('amount');

        $daysInMonth = $now->day;
        $avgPerHari = $daysInMonth > 0 ? round($pendapatanBulanIni / $daysInMonth, 2) : 0;

        return [
            'pendapatan_bulan_ini' => (float) $pendapatanBulanIni,
            'pendapatan_bulan_lalu' => (float) $pendapatanBulanLalu,
            'vs_lalu_bulan_pct' => $pctVsLalu,
            'ytd' => (float) $ytd,
            'avg_per_hari' => (float) $avgPerHari,
        ];
    }

    public function chartDaily(array $filters = []): array
    {
        $now = Carbon::now();
        $start = !empty($filters['start_date']) ? Carbon::parse($filters['start_date']) : $now->copy()->startOfMonth();
        $end = !empty($filters['end_date']) ? Carbon::parse($filters['end_date']) : $now->copy();
        $days = $start->diffInDays($end) + 1;
        if ($days > 31) {
            $days = 31;
            $start = $end->copy()->subDays(30);
        }

        $labels = [];
        $pppoe = [];
        $hotspot = [];
        $voucher = [];
        $total = [];

        for ($i = 0; $i < $days; $i++) {
            $date = $start->copy()->addDays($i);
            $labels[] = $date->format('d/m');
            $dateStr = $date->toDateString();

            $q = Payment::where('status', 'success')
                ->whereDate('paid_at', $dateStr)
                ->with(['invoices.items']);
            
            if (\Illuminate\Support\Facades\Auth::check() && \Illuminate\Support\Facades\Auth::user()->hasRole('reseller')) {
                $q->whereHas('customer', function ($cq) {
                    $cq->where('reseller_id', \Illuminate\Support\Facades\Auth::id());
                });
            }
            
            $payments = $q->get();

            $pTot = 0; $hTot = 0; $vTot = 0; $evTot = 0;
            foreach ($payments as $pay) {
                $cat = $this->categorizePayment($pay);
                if ($cat === 'pppoe') $pTot += $pay->amount;
                elseif ($cat === 'hotspot') $hTot += $pay->amount;
                elseif ($cat === 'e-voucher') $evTot += $pay->amount;
                else $vTot += $pay->amount;
            }
            $pppoe[] = $pTot;
            $hotspot[] = $hTot;
            $voucher[] = $vTot;
            $evoucher[] = $evTot;
            $total[] = $pTot + $hTot + $vTot + $evTot;
        }

        return [
            'labels' => $labels,
            'pppoe' => $pppoe,
            'hotspot' => $hotspot,
            'voucher' => $voucher,
            'evoucher' => $evoucher,
            'total' => $total,
            'max_val' => max(1, ...$total),
        ];
    }

    protected function categorizePayment(Payment $payment): string
    {
        $method = strtolower($payment->method ?? '');
        if (str_contains($method, 'evoucher') || str_contains($method, 'e-voucher')) return 'e-voucher';
        if (str_contains($method, 'voucher')) return 'voucher';
        if (str_contains($method, 'hotspot')) return 'hotspot';
        $firstInvoice = $payment->invoices->first();
        if ($firstInvoice && $firstInvoice->items) {
            foreach ($firstInvoice->items as $item) {
                $label = strtolower($item['label'] ?? '');
                if (str_contains($label, 'evoucher') || str_contains($label, 'e-voucher')) return 'e-voucher';
                if (str_contains($label, 'hotspot')) return 'hotspot';
                if (str_contains($label, 'voucher')) return 'voucher';
            }
        }
        return 'pppoe';
    }

    public function comparison(array $filters = []): array
    {
        $now = Carbon::now();
        $endN = !empty($filters['end_date']) ? Carbon::parse($filters['end_date']) : $now->copy();
        $periodDays = 30;
        if (!empty($filters['start_date'])) {
            $periodDays = max(1, Carbon::parse($filters['start_date'])->diffInDays($endN) + 1);
        }
        $startN = $endN->copy()->subDays($periodDays - 1);
        $endN1 = $startN->copy()->subDay();
        $startN1 = $endN1->copy()->subDays($periodDays - 1);

        $build = function ($s, $e) {
            $payments = Payment::where('status', 'success')
                ->whereBetween('paid_at', [$s->startOfDay(), $e->endOfDay()])
                ->get();
            $total = $payments->sum('amount');
            $countPay = $payments->count();
            $custIds = $payments->pluck('customer_id')->unique()->count();
            $payChurn = Customer::where('status', 'terminated')
                ->whereBetween('updated_at', [$s, $e])
                ->count();
            $custAwal = Customer::whereDate('created_at', '<=', $s)->where('status', '!=', 'terminated')->count();
            $churnPct = $custAwal > 0 ? round(($payChurn / $custAwal) * 100, 2) : 0;
            return [
                'total_pendapatan' => (float) $total,
                'jumlah_payment' => $countPay,
                'jumlah_customer' => $custIds,
                'churn_pct' => $churnPct,
            ];
        };

        $periodeN = $build($startN, $endN);
        $periodeN1 = $build($startN1, $endN1);

        $metrics = [
            'total_pendapatan' => 'Total Pendapatan',
            'jumlah_payment' => 'Jumlah Payment',
            'jumlah_customer' => 'Customer Unik',
            'churn_pct' => 'Churn Rate (%)',
        ];

        $rows = [];
        foreach ($metrics as $k => $label) {
            $vN = $periodeN[$k];
            $vN1 = $periodeN1[$k];
            $diff = is_numeric($vN) && is_numeric($vN1) ? $vN - $vN1 : 0;
            $pct = ($vN1 && is_numeric($vN1) && $vN1 > 0) ? round(($diff / $vN1) * 100, 2) : 0;
            $rows[] = [
                'metric' => $label,
                'periode_n' => $vN,
                'periode_n1' => $vN1,
                'diff' => $diff,
                'pct' => $pct,
            ];
        }

        return [
            'period_label_n' => $startN->format('d/m/Y') . ' - ' . $endN->format('d/m/Y'),
            'period_label_n1' => $startN1->format('d/m/Y') . ' - ' . $endN1->format('d/m/Y'),
            'periode_n' => $periodeN,
            'periode_n1' => $periodeN1,
            'rows' => $rows,
        ];
    }

    public function topPackages(array $filters = [], int $limit = 10): array
    {
        $q1 = DB::table('invoice_items as ii')
            ->join('invoices as i', 'i.id', '=', 'ii.invoice_id')
            ->join('payments_invoices as pi', 'pi.invoice_id', '=', 'i.id')
            ->join('payments as p', 'p.id', '=', 'pi.payment_id')
            ->where('p.status', 'success')
            ->when(!empty($filters['tahun']), fn($q) => $q->whereYear('p.paid_at', $filters['tahun']))
            ->when(!empty($filters['bulan']), fn($q) => $q->whereMonth('p.paid_at', $filters['bulan']));

        if (auth()->check() && auth()->user()->hasRole(\App\Enums\UserRole::Reseller->value)) {
            $effective = auth()->user()->getEffectiveResellerId();
            if ($effective) $q1->where('i.reseller_id', $effective);
        }

        $invoiceItems = $q1->select(
                DB::raw('0 as package_id'),
                'ii.description as package_name',
                DB::raw('COUNT(DISTINCT p.customer_id) as jumlah_pelanggan'),
                DB::raw('SUM(ii.subtotal) as total_pendapatan')
            )
            ->groupBy('ii.description')
            ->orderByDesc('total_pendapatan')
            ->limit($limit)
            ->get();

        $grandTotal = Payment::where('status', 'success')->sum('amount');

        $prevMonth = Carbon::now()->subMonth();
        $q2 = DB::table('invoice_items as ii')
            ->join('invoices as i', 'i.id', '=', 'ii.invoice_id')
            ->join('payments_invoices as pi', 'pi.invoice_id', '=', 'i.id')
            ->join('payments as p', 'p.id', '=', 'pi.payment_id')
            ->where('p.status', 'success')
            ->whereMonth('p.paid_at', $prevMonth->month)
            ->whereYear('p.paid_at', $prevMonth->year);

        if (auth()->check() && auth()->user()->hasRole(\App\Enums\UserRole::Reseller->value)) {
            $effective = auth()->user()->getEffectiveResellerId();
            if ($effective) $q2->where('i.reseller_id', $effective);
        }

        $prevItems = $q2->select('ii.description as package_name', DB::raw('SUM(ii.subtotal) as prev_total'))
            ->groupBy('ii.description')
            ->pluck('prev_total', 'package_name')
            ->all();

        $ranked = [];
        foreach ($invoiceItems as $idx => $row) {
            $rev = (float) $row->total_pendapatan;
            $prev = (float) ($prevItems[$row->package_name] ?? 0);
            $kontribusi = $grandTotal > 0 ? round(($rev / $grandTotal) * 100, 2) : 0;
            $trend = $prev > 0 ? round((($rev - $prev) / $prev) * 100, 2) : ($rev > 0 ? 100 : 0);
            $ranked[] = [
                'rank' => $idx + 1,
                'package_id' => $row->package_id ?? 0,
                'nama_paket' => $row->package_name ?? 'Unknown',
                'jumlah_pelanggan' => $row->jumlah_pelanggan,
                'total_pendapatan' => $rev,
                'kontribusi_pct' => $kontribusi,
                'trend_pct' => $trend,
            ];
        }

        return $ranked;
    }

    public function excelExport(array $filters = []): array
    {
        $userId = auth()->id() ?? 0;
        $summary = $this->summary($filters);
        $daily = $this->chartDaily($filters);
        $topPackages = $this->topPackages($filters);
        $comparison = $this->comparison($filters);

        Event::dispatch(new PendapatanReportExportedEvent(
            userId: $userId,
            format: 'excel',
            filters: $filters,
            generatedAt: now()->toIso8601String(),
        ));

        return [
            'summary' => $summary,
            'daily' => $daily,
            'top_packages' => $topPackages,
            'comparison' => $comparison,
            'generated_at' => now()->format('d/m/Y H:i:s'),
        ];
    }

    public function pdfExport(array $filters = []): array
    {
        $userId = auth()->id() ?? 0;
        $result = $this->excelExport($filters);

        Event::dispatch(new PendapatanReportExportedEvent(
            userId: $userId,
            format: 'pdf',
            filters: $filters,
            generatedAt: now()->toIso8601String(),
        ));

        return $result;
    }
}
