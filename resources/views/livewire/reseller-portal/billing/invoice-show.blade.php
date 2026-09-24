<div>
    <style>
        @media print {
            .no-print {
                display: none !important;
            }
            body {
                background-color: white !important;
            }
        }
    </style>
    <div class="no-print mb-4 flex justify-between items-center px-4 md:px-8">
        <a href="{{ route('reseller-portal.billing.invoices') }}" class="px-4 py-2 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-700 dark:text-slate-300 rounded-lg hover:bg-slate-50 dark:bg-slate-900/50 flex items-center gap-2 transition-colors">
            <span class="material-symbols-outlined notranslate" translate="no" style="font-size:18px">arrow_back</span>
            Kembali
        </a>
        <button onclick="window.print()" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 flex items-center gap-2 transition-colors shadow-sm">
            <span class="material-symbols-outlined notranslate" translate="no" style="font-size:18px">print</span>
            Print Kertas
        </button>
    </div>
    
    <div class="bg-slate-100 dark:bg-slate-900 p-4 md:p-8 min-h-screen flex justify-center hidden:no-print">
        <x-billing.invoice-document :invoice="$invoice" :company="$company" />
    </div>
</div>