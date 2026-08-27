<?php

namespace App\Services\Laporan;

use App\Models\CRM\Customer;
use App\Models\CRM\Activation;
use App\Models\Customer\CustomerService;
use App\Models\Billing\Invoice;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;

class CustomerReportService
{
    public function growth(array $filters = []): array
    {
        $now = Carbon::now();
        $months = 12;
        $labels = [];
        $awal = [];
        $add = [];
        $suspend = [];
        $akhir = [];
        $growthPct = [];
        $lineTotal = [];

        $runningTotal = Customer::whereDate('created_at', '<', $now->copy()->subMonths($months - 1)->startOfMonth())
            ->whereNot('status', 'terminated')
            ->count();

        for ($i = $months - 1; $i >= 0; $i--) {
            $monthDate = $now->copy()->subMonths($i);
            $startM = $monthDate->copy()->startOfMonth();
            $endM = $monthDate->copy()->endOfMonth();
            $labels[] = $monthDate->format('M Y');

            $newCustomers = Customer::whereBetween('created_at', [$startM, $endM])->count();
            $suspended = Customer::whereBetween('updated_at', [$startM, $endM])
                ->where('status', 'suspended')
                ->count();

            $mStart = $runningTotal;
            $mEnd = $mStart + $newCustomers - $suspended;
            $mEnd = max(0, $mEnd);

            $awal[] = $mStart;
            $add[] = $newCustomers;
            $suspend[] = $suspended;
            $akhir[] = $mEnd;
            $lineTotal[] = $mEnd;
            $growthPct[] = $mStart > 0 ? round((($mEnd - $mStart) / $mStart) * 100, 2) : ($mEnd > 0 ? 100 : 0);

            $runningTotal = $mEnd;
        }

        $rows = [];
        foreach ($labels as $i => $lbl) {
            $rows[] = [
                'bulan' => $lbl,
                'awal' => $awal[$i],
                'add' => $add[$i],
                'suspend' => $suspend[$i],
                'akhir' => $akhir[$i],
                'growth_pct' => $growthPct[$i],
            ];
        }

        return [
            'labels' => $labels,
            'awal' => $awal,
            'add' => $add,
            'suspend' => $suspend,
            'akhir' => $akhir,
            'line_total' => $lineTotal,
            'growth_pct' => $growthPct,
            'rows' => $rows,
            'max_bar' => max(1, ...array_map(fn($a, $s) => $a + $s, $add, $suspend)),
            'max_line' => max(1, ...$lineTotal),
        ];
    }

    public function activations(array $filters = []): array
    {
        $now = Carbon::now();
        $days = 30;
        $labels = [];
        $counts = [];
        $tableRows = [];

        for ($i = $days - 1; $i >= 0; $i--) {
            $date = $now->copy()->subDays($i);
            $dateStr = $date->toDateString();
            $labels[] = $date->format('d/m');

            $acts = Activation::whereDate('activated_at', $dateStr)
                ->orWhereDate('created_at', $dateStr)
                ->with(['customer', 'createdBy'])
                ->get();

            $count = $acts->count();
            $counts[] = $count;

            $paketTop = $acts->pluck('package_name')->filter()->countBy()->take(3)->all();
            $salesTop = $acts->pluck('createdBy.name')->filter()->countBy()->take(3)->all();
            $avgKontrak = count($acts) > 0 ? round($acts->avg('contract_value') ?? 0, 2) : 0;

            $tableRows[] = [
                'tanggal' => $date->format('d/m/Y'),
                'jumlah_aktivasi' => $count,
                'paket_top' => $paketTop,
                'sales_top' => $salesTop,
                'avg_nilai_kontrak' => $avgKontrak,
            ];
        }

        return [
            'labels' => $labels,
            'counts' => $counts,
            'max_val' => max(1, ...$counts),
            'rows' => $tableRows,
        ];
    }

    public function suspensions(array $filters = []): array
    {
        $now = Carbon::now();
        $days = 30;
        $tableRows = [];
        $alasanAll = [];

        for ($i = $days - 1; $i >= 0; $i--) {
            $date = $now->copy()->subDays($i);
            $dateStr = $date->toDateString();

            $suspensions = CustomerService::whereDate('suspended_at', $dateStr)
                ->with('customer')
                ->get();

            $count = $suspensions->count();
            $alasanTop = $suspensions->pluck('suspend_reason')->filter()->countBy()->take(3)->all();
            foreach ($alasanTop as $a => $c) {
                $alasanAll[$a] = ($alasanAll[$a] ?? 0) + $c;
            }

            $aktif = Customer::whereDate('created_at', '<=', $dateStr)
                ->where(function ($q) {
                    $q->where('status', 'active')
                        ->orWhereNull('status');
                })
                ->count();
            $pct = $aktif > 0 ? round(($count / $aktif) * 100, 3) : 0;

            $tableRows[] = [
                'tanggal' => $date->format('d/m/Y'),
                'jumlah_suspend' => $count,
                'alasan_top' => $alasanTop,
                'pct_aktif_suspend' => $pct,
            ];
        }

        arsort($alasanAll);
        $pieLabels = array_keys($alasanAll);
        $pieValues = array_values($alasanAll);
        if (count($pieLabels) === 0) {
            $pieLabels = ['Belum Ada Data'];
            $pieValues = [1];
        }
        $totalAlasan = array_sum($pieValues) ?: 1;

        return [
            'rows' => $tableRows,
            'pie_labels' => $pieLabels,
            'pie_values' => $pieValues,
            'pie_pct' => array_map(fn($v) => round(($v / $totalAlasan) * 100, 1), $pieValues),
        ];
    }

    public function terminations(array $filters = []): array
    {
        $now = Carbon::now();
        $days = 30;
        $tableRows = [];
        $totalTerminasi = 0;
        $totalRecovery = 0;
        $alasanAll = [];

        for ($i = $days - 1; $i >= 0; $i--) {
            $date = $now->copy()->subDays($i);
            $dateStr = $date->toDateString();

            $terms = Customer::whereDate('updated_at', $dateStr)
                ->where('status', 'terminated')
                ->get();

            $count = $terms->count();
            $totalTerminasi += $count;
            $alasanTop = $terms->pluck('terminate_reason')->filter()->countBy()->take(3)->all();
            foreach ($alasanTop as $a => $c) {
                $alasanAll[$a] = ($alasanAll[$a] ?? 0) + $c;
            }

            $churnPct = 0;
            $awal = Customer::whereDate('created_at', '<=', $dateStr)
                ->whereNot('status', 'terminated')
                ->count() + $count;
            if ($awal > 0) {
                $churnPct = round(($count / $awal) * 100, 2);
            }

            $recovery = Customer::whereDate('updated_at', $dateStr)
                ->where('status', 'active')
                ->where('reactivated_at', $dateStr)
                ->count();
            $totalRecovery += $recovery;

            $tableRows[] = [
                'tanggal' => $date->format('d/m/Y'),
                'jumlah_nonaktif' => $count,
                'alasan_top' => $alasanTop,
                'churn_pct' => $churnPct,
                'recovery' => $recovery,
            ];
        }

        arsort($alasanAll);

        return [
            'rows' => $tableRows,
            'summary' => [
                'total_terminasi' => $totalTerminasi,
                'total_recovery' => $totalRecovery,
                'recovery_rate_pct' => $totalTerminasi > 0 ? round(($totalRecovery / $totalTerminasi) * 100, 2) : 0,
                'top_alasan' => $alasanAll,
            ],
        ];
    }

    public function excelExport(string $tab = 'growth'): array
    {
        $data = match ($tab) {
            'growth' => $this->growth(),
            'activations' => $this->activations(),
            'suspensions' => $this->suspensions(),
            'terminations' => $this->terminations(),
            default => $this->growth(),
        };

        return [
            'tab' => $tab,
            'data' => $data,
            'generated_at' => now()->format('d/m/Y H:i:s'),
        ];
    }
}
