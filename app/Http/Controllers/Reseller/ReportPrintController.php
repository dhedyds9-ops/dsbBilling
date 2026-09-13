<?php

namespace App\Http\Controllers\Reseller;

use App\Http\Controllers\Controller;
use App\Models\Billing\Invoice;
use App\Models\Billing\InvoiceItem;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Barryvdh\DomPDF\Facade\Pdf;

class ReportPrintController extends Controller
{
    public function printSales(Request $request)
    {
        $reseller = Auth::user();
        $resellerId = $reseller->id;

        $month = $request->input('month', now()->month);
        $year = $request->input('year', now()->year);

        $sales = Invoice::whereHas('customer', function ($q) use ($resellerId) {
            $q->where('reseller_id', $resellerId);
        })
        ->where('status', 'paid')
        ->whereMonth('updated_at', $month)
        ->whereYear('updated_at', $year)
        ->with('customer')
        ->orderBy('updated_at', 'asc')
        ->get();

        $totalRevenue = $sales->sum('total_amount');

        $data = [
            'reseller' => $reseller,
            'sales' => $sales,
            'month' => $month,
            'year' => $year,
            'totalRevenue' => $totalRevenue,
            'datePrinted' => now()->format('d M Y H:i')
        ];

        $pdf = Pdf::loadView('reports.reseller.sales-pdf', $data);
        return $pdf->stream('Laporan_Penjualan_' . $reseller->name . '_' . $month . '_' . $year . '.pdf');
    }
    
    public function printCommission(Request $request)
    {
        $resellerId = Auth::id();
        $currentMonth = now()->month;
        $currentYear = now()->year;

        $query = Invoice::whereHas('customer', function ($q) use ($resellerId) {
            $q->where('reseller_id', $resellerId);
        })
        ->where('status', 'paid')
        ->whereMonth('updated_at', $currentMonth)
        ->whereYear('updated_at', $currentYear);

        $invoiceIds = (clone $query)->pluck('id');

        $grossRevenue = 0;
        $totalCost = 0;

        if ($invoiceIds->count() > 0) {
            $grossRevenue = InvoiceItem::whereIn('invoice_id', $invoiceIds)->sum('subtotal');
            $totalCost = InvoiceItem::whereIn('invoice_id', $invoiceIds)->sum(DB::raw('COALESCE(reseller_settlement_price, 0)'));
        }

        $netMargin = $grossRevenue - $totalCost;
        if ($totalCost == 0 && $grossRevenue > 0) {
            $netMargin = $grossRevenue * 0.15;
            $totalCost = $grossRevenue - $netMargin;
        }

        $profitMarginPercent = $grossRevenue > 0 ? ($netMargin / $grossRevenue) * 100 : 0;

        $details = (clone $query)->with(['customer', 'items'])->orderBy('updated_at', 'desc')->get();

        $data = [
            'reseller' => Auth::user(),
            'monthName' => now()->translatedFormat('F Y'),
            'grossRevenue' => $grossRevenue,
            'totalCost' => $totalCost,
            'netMargin' => $netMargin,
            'profitMarginPercent' => $profitMarginPercent,
            'details' => $details,
        ];

        $pdf = Pdf::loadView('reports.reseller.commission-pdf', $data);
        return $pdf->stream('Laporan_Komisi_' . now()->format('Y-m') . '.pdf');
    }
}
