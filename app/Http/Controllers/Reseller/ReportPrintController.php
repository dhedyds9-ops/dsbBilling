<?php

namespace App\Http\Controllers\Reseller;

use App\Http\Controllers\Controller;
use App\Services\Keuangan\IncomeReportService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Barryvdh\DomPDF\Facade\Pdf;

class ReportPrintController extends Controller
{
    public function printSales(Request $request)
    {
        $reseller = Auth::user();
        $resellerId = $reseller->getEffectiveResellerId() ?? ($reseller->hasRole('reseller') ? $reseller->id : null);

        $month = (int) $request->input('month', now()->month);
        $year = (int) $request->input('year', now()->year);

        $startDate = Carbon::createFromDate($year, $month, 1)->startOfMonth()->format('Y-m-d');
        $endDate = Carbon::createFromDate($year, $month, 1)->endOfMonth()->format('Y-m-d');

        $incomeService = app(IncomeReportService::class);
        $reportData = $incomeService->getTransactions([
            'reseller_id' => $resellerId,
            'start_date' => $startDate,
            'end_date' => $endDate,
        ]);

        $sales = $reportData['rows'];
        $totalRevenue = (float) ($reportData['summary']['total_ppn'] ?? $reportData['summary']['total_plus_ppn'] ?? 0);
        $totalMargin = (float) ($reportData['summary']['fee_seller'] ?? 0);
        $totalCost = (float) ($reportData['summary']['profit'] ?? 0);

        $data = [
            'reseller' => $reseller,
            'sales' => $sales,
            'month' => $month,
            'year' => $year,
            'totalRevenue' => $totalRevenue,
            'totalMargin' => $totalMargin,
            'totalCost' => $totalCost,
            'datePrinted' => now()->format('d M Y H:i')
        ];

        $pdf = Pdf::loadView('reports.reseller.sales-pdf', $data);
        return $pdf->stream('Laporan_Penjualan_' . $reseller->name . '_' . $month . '_' . $year . '.pdf');
    }
    
    public function printCommission(Request $request)
    {
        $reseller = Auth::user();
        $resellerId = $reseller->getEffectiveResellerId() ?? ($reseller->hasRole('reseller') ? $reseller->id : null);

        $month = (int) $request->input('month', now()->month);
        $year = (int) $request->input('year', now()->year);

        $startDate = Carbon::createFromDate($year, $month, 1)->startOfMonth()->format('Y-m-d');
        $endDate = Carbon::createFromDate($year, $month, 1)->endOfMonth()->format('Y-m-d');

        $incomeService = app(IncomeReportService::class);
        $reportData = $incomeService->getTransactions([
            'reseller_id' => $resellerId,
            'start_date' => $startDate,
            'end_date' => $endDate,
        ]);

        $summary = $reportData['summary'];
        $grossRevenue = (float) ($summary['total_ppn'] ?? $summary['total_plus_ppn'] ?? 0);
        $netMargin = (float) ($summary['fee_seller'] ?? 0);
        $totalCost = (float) ($summary['profit'] ?? 0);
        $profitMarginPercent = $grossRevenue > 0 ? ($netMargin / $grossRevenue) * 100 : 0;

        $monthDate = Carbon::createFromDate($year, $month, 1);

        $data = [
            'reseller' => $reseller,
            'monthName' => $monthDate->translatedFormat('F Y'),
            'grossRevenue' => $grossRevenue,
            'totalCost' => $totalCost,
            'netMargin' => $netMargin,
            'profitMarginPercent' => $profitMarginPercent,
            'details' => $reportData['rows'],
        ];

        $pdf = Pdf::loadView('reports.reseller.commission-pdf', $data);
        return $pdf->stream('Laporan_Komisi_' . $reseller->name . '_' . $month . '_' . $year . '.pdf');
    }
}
