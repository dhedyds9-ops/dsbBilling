<?php
$file = 'D:/dsBilling/app/Http/Controllers/Reseller/ReportPrintController.php';
$content = file_get_contents($file);

$searchMeth = "}
}";
$replaceMeth = <<<PHP

    public function printCommission(Request \$request)
    {
        \$resellerId = Auth::id();
        \$currentMonth = now()->month;
        \$currentYear = now()->year;

        \$query = Invoice::whereHas('customer', function (\$q) use (\$resellerId) {
            \$q->where('reseller_id', \$resellerId);
        })
        ->where('status', 'paid')
        ->whereMonth('updated_at', \$currentMonth)
        ->whereYear('updated_at', \$currentYear);

        \$invoiceIds = (clone \$query)->pluck('id');

        \$grossRevenue = 0;
        \$totalCost = 0;

        if (\$invoiceIds->count() > 0) {
            \$grossRevenue = InvoiceItem::whereIn('invoice_id', \$invoiceIds)->sum('subtotal');
            \$totalCost = InvoiceItem::whereIn('invoice_id', \$invoiceIds)->sum(DB::raw('COALESCE(reseller_settlement_price, 0)'));
        }

        \$netMargin = \$grossRevenue - \$totalCost;
        if (\$totalCost == 0 && \$grossRevenue > 0) {
            \$netMargin = \$grossRevenue * 0.15;
            \$totalCost = \$grossRevenue - \$netMargin;
        }

        \$profitMarginPercent = \$grossRevenue > 0 ? (\$netMargin / \$grossRevenue) * 100 : 0;

        \$details = (clone \$query)->with(['customer', 'items'])->orderBy('updated_at', 'desc')->get();

        \$data = [
            'reseller' => Auth::user(),
            'monthName' => now()->translatedFormat('F Y'),
            'grossRevenue' => \$grossRevenue,
            'totalCost' => \$totalCost,
            'netMargin' => \$netMargin,
            'profitMarginPercent' => \$profitMarginPercent,
            'details' => \$details,
        ];

        \$pdf = Pdf::loadView('reports.reseller.commission-pdf', \$data);
        return \$pdf->stream('Laporan_Komisi_' . now()->format('Y-m') . '.pdf');
    }
}
PHP;

$content = str_replace($searchMeth, $replaceMeth, $content);
file_put_contents($file, $content);
echo "Added printCommission method.\n";
?>
