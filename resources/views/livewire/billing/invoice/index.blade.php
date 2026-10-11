<div>
    @section('page_title')
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl bg-blue-500/10 flex items-center justify-center text-blue-600 dark:text-blue-400">
                <span class="material-symbols-outlined notranslate" translate="no">receipt_long</span>
            </div>
            <div>
                <h1 class="text-xl font-bold text-slate-900 dark:text-slate-100 leading-tight">Data Tagihan</h1>
                <p class="text-sm text-slate-500 dark:text-slate-400">Kelola semua tagihan pelanggan (Invoice)</p>
            </div>
        </div>
    @endsection

    <div class="space-y-4">
        {{-- KPI CARDS --}}
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            {{-- Outstanding / Belum Lunas --}}
            <div wire:click="$set('filters.status', 'unpaid')" class="relative overflow-x-auto rounded-xl border border-amber-200 dark:border-amber-800/60 shadow-sm bg-gradient-to-br from-amber-50 to-white dark:from-amber-950/50 dark:to-slate-800 group hover:shadow-md transition-all cursor-pointer">
                <div class="absolute top-0 left-0 right-0 h-1 bg-gradient-to-r from-amber-500 to-orange-400 rounded-t-xl"></div>
                <div class="absolute top-3 right-3 opacity-10 text-amber-500 group-hover:opacity-20 group-hover:scale-110 transition-all">
                    <span class="material-symbols-outlined notranslate" translate="no" style="font-size:56px">pending_actions</span>
                </div>
                <div class="p-4 pt-5">
                    <h3 class="text-xs font-bold text-amber-600 dark:text-amber-500 uppercase tracking-widest mb-2">Belum Dibayar</h3>
                    <div class="flex items-baseline gap-2">
                        <span class="text-2xl font-black text-slate-800 dark:text-slate-100 tracking-tight">Rp {{ number_format($stats['outstanding'], 0, ',', '.') }}</span>
                    </div>
                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">{{ number_format($stats['pending']) }} Invoice</p>
                </div>
            </div>

            {{-- Overdue --}}
            <div wire:click="$set('filters.status', 'overdue')" class="relative overflow-x-auto rounded-xl border border-red-200 dark:border-red-800/60 shadow-sm bg-gradient-to-br from-red-50 to-white dark:from-red-950/50 dark:to-slate-800 group hover:shadow-md transition-all cursor-pointer">
                <div class="absolute top-0 left-0 right-0 h-1 bg-gradient-to-r from-red-500 to-rose-400 rounded-t-xl"></div>
                <div class="absolute top-3 right-3 opacity-10 text-red-500 group-hover:opacity-20 group-hover:scale-110 transition-all">
                    <span class="material-symbols-outlined notranslate" translate="no" style="font-size:56px">warning</span>
                </div>
                <div class="p-4 pt-5">
                    <h3 class="text-xs font-bold text-red-600 dark:text-red-500 uppercase tracking-widest mb-2">Jatuh Tempo</h3>
                    <div class="flex items-baseline gap-2">
                        <span class="text-2xl font-black text-slate-800 dark:text-slate-100 tracking-tight">{{ number_format($stats['overdue']) }}</span>
                        <span class="text-sm font-medium text-slate-500 dark:text-slate-400">Invoice</span>
                    </div>
                </div>
            </div>

            {{-- Lunas --}}
            <div wire:click="$set('filters.status', 'paid')" class="relative overflow-x-auto rounded-xl border border-emerald-200 dark:border-emerald-800/60 shadow-sm bg-gradient-to-br from-emerald-50 to-white dark:from-emerald-950/50 dark:to-slate-800 group hover:shadow-md transition-all cursor-pointer">
                <div class="absolute top-0 left-0 right-0 h-1 bg-gradient-to-r from-emerald-500 to-green-400 rounded-t-xl"></div>
                <div class="absolute top-3 right-3 opacity-10 text-emerald-500 group-hover:opacity-20 group-hover:scale-110 transition-all">
                    <span class="material-symbols-outlined notranslate" translate="no" style="font-size:56px">check_circle</span>
                </div>
                <div class="p-4 pt-5">
                    <h3 class="text-xs font-bold text-emerald-600 dark:text-emerald-500 uppercase tracking-widest mb-2">Terbayar Lunas</h3>
                    <div class="flex items-baseline gap-2">
                        <span class="text-2xl font-black text-slate-800 dark:text-slate-100 tracking-tight">Rp {{ number_format($stats['paid_amount'], 0, ',', '.') }}</span>
                    </div>
                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">{{ number_format($stats['paid']) }} Invoice</p>
                </div>
            </div>

            {{-- Total Tagihan --}}
            <div wire:click="$set('filters.status', '')" class="relative overflow-x-auto rounded-xl border border-blue-200 dark:border-blue-800/60 shadow-sm bg-gradient-to-br from-blue-50 to-white dark:from-blue-950/50 dark:to-slate-800 group hover:shadow-md transition-all cursor-pointer">
                <div class="absolute top-0 left-0 right-0 h-1 bg-gradient-to-r from-blue-500 to-cyan-400 rounded-t-xl"></div>
                <div class="absolute top-3 right-3 opacity-10 text-blue-500 group-hover:opacity-20 group-hover:scale-110 transition-all">
                    <span class="material-symbols-outlined notranslate" translate="no" style="font-size:56px">account_balance_wallet</span>
                </div>
                <div class="p-4 pt-5">
                    <h3 class="text-xs font-bold text-blue-600 dark:text-blue-500 uppercase tracking-widest mb-2">Total Tagihan</h3>
                    <div class="flex items-baseline gap-2">
                        <span class="text-2xl font-black text-slate-800 dark:text-slate-100 tracking-tight">Rp {{ number_format($stats['total_amount'], 0, ',', '.') }}</span>
                    </div>
                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">{{ number_format($stats['total']) }} Keseluruhan</p>
                </div>
            </div>
        </div>

        {{-- TOOLBAR --}}
        <div class="flex flex-col sm:flex-row gap-3 items-center justify-between bg-white dark:bg-slate-800 p-4 rounded-xl shadow-sm border border-slate-200 dark:border-slate-700">
            <div class="flex-1 w-full relative max-w-md">
                <span class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400">
                    <span class="material-symbols-outlined notranslate" translate="no" style="font-size: 20px">search</span>
                </span>
                <input type="text" wire:model.live.debounce.300ms="search" placeholder="Cari No. Invoice atau Pelanggan..." class="w-full pl-9 pr-4 py-2 bg-slate-50 dark:bg-slate-900/50 border border-slate-200 dark:border-slate-700 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 transition-all dark:bg-slate-900 dark:text-slate-100">
            </div>
            <div class="flex items-center gap-2">
                <select wire:model.live="filters.status" class="bg-slate-50 dark:bg-slate-900/50 border border-slate-200 dark:border-slate-700 text-slate-700 dark:text-slate-300 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block pl-3 pr-8 py-2 dark:bg-slate-900 dark:text-slate-100">
                    <option value="">Semua Status</option>
                    <option value="unpaid">Belum Dibayar</option>
                    <option value="paid">Lunas</option>
                    <option value="overdue">Jatuh Tempo</option>
                </select>
                @if(auth()->user()->hasRole('administrator'))
                <select wire:model.live="filters.reseller_id" class="bg-slate-50 dark:bg-slate-900/50 border border-slate-200 dark:border-slate-700 text-slate-700 dark:text-slate-300 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block pl-3 pr-8 py-2 dark:bg-slate-900 dark:text-slate-100">
                    <option value="">Semua Reseller</option>
                    @foreach($resellers as $res)
                        <option value="{{ $res->id }}">{{ $res->name }}</option>
                    @endforeach
                </select>
                @endif
                                <select wire:model.live="perPage" class="bg-slate-50 dark:bg-slate-900/50 border border-slate-200 dark:border-slate-700 text-slate-700 dark:text-slate-300 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block pl-3 pr-8 py-2 dark:bg-slate-900 dark:text-slate-100">
                    <option value="10">10 Baris</option>
                    <option value="25">25 Baris</option>
                    <option value="50">50 Baris</option>
                    <option value="100">100 Baris</option>
                </select>
                <button wire:click="export" class="px-4 py-2 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-700 dark:text-slate-300 rounded-lg text-sm font-medium hover:bg-slate-50 dark:bg-slate-900/50 dark:hover:bg-slate-700 transition-colors flex items-center gap-2">
                    <span class="material-symbols-outlined notranslate" translate="no" style="font-size:18px">download</span> Export
                </button>
                <a href="{{ route('billing.invoices.create') }}" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg text-sm font-medium transition-colors flex items-center gap-2">
                    <span class="material-symbols-outlined notranslate" translate="no" style="font-size:18px">add</span> Buat Manual
                </a>
            </div>
        </div>

        {{-- BULK ACTIONS BAR --}}
        @if(count($selected) > 0)
        <div class="flex items-center justify-between p-4 bg-indigo-50 dark:bg-indigo-900/20 border border-indigo-200 dark:border-indigo-800 rounded-xl mb-4 mt-2 transition-all">
            <div class="flex items-center gap-3">
                <span class="flex items-center justify-center w-8 h-8 rounded-full bg-indigo-200 dark:bg-indigo-800 text-indigo-700 dark:text-indigo-300 font-bold">
                    {{ count($selected) }}
                </span>
                <span class="text-sm font-medium text-indigo-900 dark:text-indigo-200">Invoice terpilih</span>
            </div>
            <div class="flex items-center gap-2">
                <button wire:click="bulkPaymentModal" class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg text-sm font-medium transition-colors shadow-sm flex items-center gap-2">
                    <span class="material-symbols-outlined" style="font-size:18px">payments</span> Bayar Semua
                </button>
                <button wire:click="bulkDelete" wire:confirm="Yakin ingin menghapus {{ count($selected) }} invoice? Hanya invoice yang belum dibayar yang akan dihapus secara permanen." class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white rounded-lg text-sm font-medium transition-colors shadow-sm flex items-center gap-2">
                    <span class="material-symbols-outlined" style="font-size:18px">delete</span> Hapus ({{ count($selected) }})
                </button>
            </div>
        </div>
        @endif

        {{-- DATA TABLE --}}
        <div class="bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl shadow-sm overflow-hidden overflow-x-auto">
            <table class="w-full text-sm text-left">
                <thead>
                    <tr class="border-b border-slate-200 dark:border-slate-700 text-xs uppercase tracking-wider text-slate-500 dark:text-slate-400 bg-slate-50 dark:bg-slate-900/80">
                        <th class="px-4 py-3 w-10 text-center">
                            <input type="checkbox" wire:model.live="selectAll" class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500 cursor-pointer bg-white dark:bg-slate-900 dark:border-slate-600 dark:bg-slate-900 dark:text-slate-100">
                        </th>
                        <th class="px-4 py-3 font-semibold whitespace-nowrap text-center">Aksi</th>
                        <th class="px-4 py-3 font-semibold whitespace-nowrap">No. Invoice</th>
                        <th class="px-4 py-3 font-semibold whitespace-nowrap">Pelanggan</th>
                        <th class="px-4 py-3 font-semibold whitespace-nowrap">Tgl Terbit</th>
                        <th class="px-4 py-3 font-semibold whitespace-nowrap">Jatuh Tempo</th>
                        <th class="px-4 py-3 font-semibold whitespace-nowrap">Status</th>
                        <th class="px-4 py-3 font-semibold whitespace-nowrap text-right">Total</th>
                        <th class="px-4 py-3 font-semibold whitespace-nowrap text-right">Dibayar</th>
                        </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-700">
                    @forelse($invoices as $invoice)
                        <tr class="hover:bg-slate-50 dark:bg-slate-900/50 dark:hover:bg-slate-800/50 transition-colors">
                            <td class="px-4 py-3 whitespace-nowrap text-center">
                                <input type="checkbox" wire:model.live="selected" value="{{ $invoice->id }}" 
                                       class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500 cursor-pointer bg-white dark:bg-slate-900 dark:border-slate-600">
                            </td>
                            </tr>
                    @endforelse
                </tbody>
            </table>
            @if($invoices->hasPages())
                <div class="px-4 py-3 border-t border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800/50">
                    {{ $invoices->links() }}
                </div>
            @endif
        </div>
    </div>

    {{-- PAYMENT MODAL --}}
    @if($showPaymentModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center overflow-y-auto overflow-x-hidden bg-slate-900/50 backdrop-blur-sm">
            <div class="relative w-full max-w-md p-4">
                <div class="relative bg-white dark:bg-slate-800 rounded-xl shadow-2xl border border-slate-200 dark:border-slate-700 overflow-hidden">
                    <div class="flex items-center justify-between px-5 py-4 border-b border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-900/50">
                        <h3 class="text-lg font-bold text-slate-900 dark:text-slate-100">Proses Pembayaran Cepat</h3>
                        <button wire:click="closePaymentModal" class="text-slate-400 hover:text-slate-500 dark:text-slate-400 dark:hover:text-slate-300">
                            <span class="material-symbols-outlined notranslate" translate="no">close</span>
                        </button>
                    </div>
                    <div class="p-5 space-y-4">
                        <div class="bg-blue-50 dark:bg-blue-900/20 text-blue-700 dark:text-blue-400 p-3 rounded-lg text-sm border border-blue-100 dark:border-blue-800">
                            Sisa tagihan yang harus dibayar: <strong>Rp {{ number_format($paymentInvoiceTotal, 0, ',', '.') }}</strong>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Jumlah Bayar (Rp)</label>
                            <input type="number" wire:model="paymentAmount" class="w-full rounded-lg border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-900 dark:bg-slate-700 text-slate-900 dark:text-slate-100 focus:border-blue-500 focus:ring-blue-500 dark:bg-slate-900 dark:text-slate-100">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Metode Pembayaran</label>
                            <select wire:model="paymentMethod" class="w-full rounded-lg border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-900 dark:bg-slate-700 text-slate-900 dark:text-slate-100 focus:border-blue-500 focus:ring-blue-500 dark:bg-slate-900 dark:text-slate-100">
                                <option value="cash">Tunai / Cash</option>
                                <option value="bank_transfer">Transfer Bank</option>
                            </select>
                        </div>
                    </div>
                    <div class="px-5 py-4 border-t border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-900/50 flex justify-end gap-2">
                        <button wire:click="closePaymentModal" class="px-4 py-2 border border-slate-300 dark:border-slate-600 rounded-lg text-sm font-medium text-slate-700 dark:text-slate-300 bg-white dark:bg-slate-700 hover:bg-slate-50 dark:bg-slate-900/50 dark:hover:bg-slate-600 transition-colors">Batal</button>
                        <button wire:click="submitPayment" class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg text-sm font-medium transition-colors">Simpan Pembayaran</button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>