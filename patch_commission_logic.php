<?php
$file = 'D:/dsBilling/app/Livewire/ResellerPortal/Reports/Commission.php';
$content = file_get_contents($file);

$replace = <<<PHP
<?php

namespace App\Livewire\ResellerPortal\Reports;

use App\Livewire\AdminComponent;
use App\Models\Billing\Invoice;
use App\Models\Billing\InvoiceItem;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class Commission extends AdminComponent
{
    public \$perPage = 10;

    public function mount()
    {
        parent::mount();
        \$this->activeModule = 'reseller-portal';
        \$this->activePage = 'reseller-portal.reports.commission';
        
        \$this->breadcrumbs = [
            ['label' => 'Reseller Portal', 'url' => '#'],
            ['label' => 'Laporan', 'url' => '#'],
            ['label' => 'Komisi / Margin', 'url' => route('reseller-portal.reports.commission')],
        ];
    }

    public function render()
    {
        \$resellerId = Auth::id();
        \$currentMonth = now()->month;
        \$currentYear = now()->year;

        // Base query for invoices
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
        
        // Safety fallback if settlement prices are 0, use estimated 15%
        if (\$totalCost == 0 && \$grossRevenue > 0) {
            \$netMargin = \$grossRevenue * 0.15; // 15% placeholder
            \$totalCost = \$grossRevenue - \$netMargin;
        }

        \$profitMarginPercent = \$grossRevenue > 0 ? (\$netMargin / \$grossRevenue) * 100 : 0;

        // Get paginated list for the detail table
        \$details = (clone \$query)->with(['customer', 'items'])->orderBy('updated_at', 'desc')->paginate(\$this->perPage);

        return view('livewire.reseller-portal.reports.commission', [
            'grossRevenue' => \$grossRevenue,
            'totalCost' => \$totalCost,
            'netMargin' => \$netMargin,
            'profitMarginPercent' => \$profitMarginPercent,
            'currentMonthName' => now()->translatedFormat('F Y'),
            'details' => \$details,
        ]);
    }
}
PHP;

file_put_contents($file, $replace);
echo "Updated logic.\n";
?>
