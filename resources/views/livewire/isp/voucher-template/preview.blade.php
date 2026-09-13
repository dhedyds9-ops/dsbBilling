<div>
    <div class="mb-6">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-slate-800 dark:text-slate-200 leading-tight">
                Preview: {{ $template->name }}
            </h2>
            <div class="flex space-x-2">
                <a href="{{ route('isp.voucher-templates.edit', $template->id) }}" class="inline-flex items-center px-4 py-2 bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-600 rounded-md font-semibold text-xs text-slate-700 dark:text-slate-300 uppercase tracking-widest hover:bg-slate-50 dark:bg-slate-900/50">
                    Kembali ke Editor
                </a>
                <button onclick="window.print()" class="inline-flex items-center px-4 py-2 bg-primary-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-primary-700">
                    <span class="material-symbols-outlined mr-2 text-sm">print</span> Print
                </button>
            </div>
        </div>
    </div>

    <div class="py-6 print:py-0">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 print:max-w-none print:px-0">
            
            <div class="bg-white dark:bg-slate-800 p-4 mb-6 rounded-lg shadow print:hidden flex flex-wrap gap-4 items-center">
                <div class="flex items-center space-x-2">
                    <label class="text-sm font-medium text-slate-700 dark:text-slate-300">Jumlah Batch:</label>
                    <select wire:model.live="batchCount" class="border-slate-300 dark:border-slate-600 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm py-1 dark:bg-slate-900 dark:text-slate-100">
                        <option value="1">1 Voucher</option>
                        <option value="6">6 Voucher</option>
                        <option value="12">12 Voucher</option>
                        <option value="18">18 Voucher</option>
                        <option value="24">24 Voucher</option>
                    </select>
                </div>
                
                <div class="flex items-center space-x-2 ml-4">
                    <label class="text-sm font-medium text-slate-700 dark:text-slate-300">Test Harga (Conditional):</label>
                    <select wire:model.live="selectedPrice" class="border-slate-300 dark:border-slate-600 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm py-1 dark:bg-slate-900 dark:text-slate-100">
                        <option value="2000">Rp 2.000</option>
                        <option value="3000">Rp 3.000</option>
                        <option value="5000">Rp 5.000</option>
                        <option value="7000">Rp 7.000</option>
                        <option value="25000">Rp 25.000</option>
                        <option value="65000">Rp 65.000</option>
                        <option value="70000">Rp 70.000</option>
                    </select>
                </div>
            </div>

            <!-- Preview Area -->
            <div class="bg-white dark:bg-slate-800 p-8 rounded-lg shadow min-h-[500px] overflow-auto print:shadow-none print:p-0 print-area">
                {!! $previewHtml !!}
            </div>

        </div>
    </div>
</div>

@push('scripts')
<style>
    @media print {
        body * {
            visibility: hidden;
        }
        .print-area, .print-area * {
            visibility: visible;
        }
        .print-area {
            position: absolute;
            left: 0;
            top: 0;
            width: 100%;
            margin: 0;
            padding: 0;
        }
        /* Hide scrollbars during print */
        ::-webkit-scrollbar {
            display: none;
        }
    }
</style>
@endpush








