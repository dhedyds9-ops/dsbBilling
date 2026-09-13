<div>
    @section('page_title')
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl bg-emerald-500/10 flex items-center justify-center text-emerald-600 dark:text-emerald-400">
                <span class="material-symbols-outlined notranslate" translate="no">payments</span>
            </div>
            <div>
                <h1 class="text-xl font-bold text-slate-900 dark:text-slate-100 leading-tight">Data Pembayaran</h1>
                <p class="text-sm text-slate-500 dark:text-slate-400">Riwayat dan pencatatan pembayaran dari pelanggan</p>
            </div>
        </div>
    @endsection

    <div class="space-y-4">
        {{-- KPI CARDS --}}
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            {{-- Total Pemasukan --}}
            <div wire:click="$set('filters.status', '')" class="relative overflow-x-auto rounded-xl border border-emerald-200 dark:border-emerald-800/60 shadow-sm bg-gradient-to-br from-emerald-50 to-white dark:from-emerald-950/50 dark:to-slate-800 group hover:shadow-md transition-all cursor-pointer">
                <div class="absolute top-0 left-0 right-0 h-1 bg-gradient-to-r from-emerald-500 to-green-400 rounded-t-xl"></div>
                <div class="absolute top-3 right-3 opacity-10 text-emerald-500 group-hover:opacity-20 group-hover:scale-110 transition-all">
                    <span class="material-symbols-outlined notranslate" translate="no" style="font-size:56px">account_balance_wallet</span>
                </div>
                <div class="p-4 pt-5">
                    <h3 class="text-xs font-bold text-emerald-600 dark:text-emerald-500 uppercase tracking-widest mb-2">Total Penerimaan</h3>
                    <div class="flex items-baseline gap-2">
                        <span class="text-2xl font-black text-slate-800 dark:text-slate-100 tracking-tight">Rp {{ number_format($stats['total_amount'] ?? 0, 0, ',', '.') }}</span>
                    </div>
                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">{{ number_format($stats['total'] ?? 0) }} Transaksi</p>
                </div>
            </div>

            {{-- Sukses --}}
            <div wire:click="$set('filters.status', 'success')" class="relative overflow-x-auto rounded-xl border border-blue-200 dark:border-blue-800/60 shadow-sm bg-gradient-to-br from-blue-50 to-white dark:from-blue-950/50 dark:to-slate-800 group hover:shadow-md transition-all cursor-pointer">
                <div class="absolute top-0 left-0 right-0 h-1 bg-gradient-to-r from-blue-500 to-cyan-400 rounded-t-xl"></div>
                <div class="absolute top-3 right-3 opacity-10 text-blue-500 group-hover:opacity-20 group-hover:scale-110 transition-all">
                    <span class="material-symbols-outlined notranslate" translate="no" style="font-size:56px">check_circle</span>
                </div>
                <div class="p-4 pt-5">
                    <h3 class="text-xs font-bold text-blue-600 dark:text-blue-500 uppercase tracking-widest mb-2">Berhasil (Success)</h3>
                    <div class="flex items-baseline gap-2">
                        <span class="text-2xl font-black text-slate-800 dark:text-slate-100 tracking-tight">{{ number_format($stats['success'] ?? 0) }}</span>
                        <span class="text-sm font-medium text-slate-500 dark:text-slate-400">Transaksi</span>
                    </div>
                </div>
            </div>

            {{-- Menunggu --}}
            <div wire:click="$set('filters.status', 'pending')" class="relative overflow-x-auto rounded-xl border border-amber-200 dark:border-amber-800/60 shadow-sm bg-gradient-to-br from-amber-50 to-white dark:from-amber-950/50 dark:to-slate-800 group hover:shadow-md transition-all cursor-pointer">
                <div class="absolute top-0 left-0 right-0 h-1 bg-gradient-to-r from-amber-500 to-orange-400 rounded-t-xl"></div>
                <div class="absolute top-3 right-3 opacity-10 text-amber-500 group-hover:opacity-20 group-hover:scale-110 transition-all">
                    <span class="material-symbols-outlined notranslate" translate="no" style="font-size:56px">hourglass_empty</span>
                </div>
                <div class="p-4 pt-5">
                    <h3 class="text-xs font-bold text-amber-600 dark:text-amber-500 uppercase tracking-widest mb-2">Menunggu (Pending)</h3>
                    <div class="flex items-baseline gap-2">
                        <span class="text-2xl font-black text-slate-800 dark:text-slate-100 tracking-tight">{{ number_format($stats['pending'] ?? 0) }}</span>
                        <span class="text-sm font-medium text-slate-500 dark:text-slate-400">Transaksi</span>
                    </div>
                </div>
            </div>

            {{-- Gagal --}}
            <div wire:click="$set('filters.status', 'failed')" class="relative overflow-x-auto rounded-xl border border-red-200 dark:border-red-800/60 shadow-sm bg-gradient-to-br from-red-50 to-white dark:from-red-950/50 dark:to-slate-800 group hover:shadow-md transition-all cursor-pointer">
                <div class="absolute top-0 left-0 right-0 h-1 bg-gradient-to-r from-red-500 to-rose-400 rounded-t-xl"></div>
                <div class="absolute top-3 right-3 opacity-10 text-red-500 group-hover:opacity-20 group-hover:scale-110 transition-all">
                    <span class="material-symbols-outlined notranslate" translate="no" style="font-size:56px">cancel</span>
                </div>
                <div class="p-4 pt-5">
                    <h3 class="text-xs font-bold text-red-600 dark:text-red-500 uppercase tracking-widest mb-2">Gagal / Dibatalkan</h3>
                    <div class="flex items-baseline gap-2">
                        <span class="text-2xl font-black text-slate-800 dark:text-slate-100 tracking-tight">{{ number_format($stats['failed'] ?? 0) }}</span>
                        <span class="text-sm font-medium text-slate-500 dark:text-slate-400">Transaksi</span>
                    </div>
                </div>
            </div>
        </div>

        {{-- TOOLBAR --}}
        <div class="flex flex-col sm:flex-row gap-3 items-center justify-between bg-white dark:bg-slate-800 p-4 rounded-xl shadow-sm border border-slate-200 dark:border-slate-700">
            <div class="flex-1 w-full relative max-w-md">
                <span class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400">
                    <span class="material-symbols-outlined notranslate" translate="no" style="font-size: 20px">search</span>
                </span>
                <input type="text" wire:model.live.debounce.300ms="search" placeholder="Cari No. Referensi atau Pelanggan..." class="w-full pl-9 pr-4 py-2 bg-slate-50 dark:bg-slate-900/50 border border-slate-200 dark:border-slate-700 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 transition-all dark:bg-slate-900 dark:text-slate-100">
            </div>
            <div class="flex items-center gap-2">
                <select wire:model.live="filters.status" class="bg-slate-50 dark:bg-slate-900/50 border border-slate-200 dark:border-slate-700 text-slate-700 dark:text-slate-300 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block pl-3 pr-8 py-2 dark:bg-slate-900 dark:text-slate-100">
                    <option value="">Semua Status</option>
                    <option value="success">Berhasil (Success)</option>
                    <option value="pending">Menunggu (Pending)</option>
                    <option value="failed">Gagal (Failed)</option>
                </select>
                <select wire:model.live="filters.payment_method" class="bg-slate-50 dark:bg-slate-900/50 border border-slate-200 dark:border-slate-700 text-slate-700 dark:text-slate-300 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block pl-3 pr-8 py-2 dark:bg-slate-900 dark:text-slate-100">
                    <option value="">Semua Metode</option>
                    <option value="cash">Tunai (Cash)</option>
                    <option value="transfer">Transfer Bank</option>
                    <option value="qris">QRIS</option>
                    <option value="payment_gateway">Payment Gateway</option>
                </select>
                                <select wire:model.live="perPage" class="bg-slate-50 dark:bg-slate-900/50 border border-slate-200 dark:border-slate-700 text-slate-700 dark:text-slate-300 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block pl-3 pr-8 py-2 dark:bg-slate-900 dark:text-slate-100">
                    <option value="10">10 Baris</option>
                    <option value="25">25 Baris</option>
                    <option value="50">50 Baris</option>
                    <option value="100">100 Baris</option>
                </select>
                <button wire:click="export" class="px-4 py-2 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-700 dark:text-slate-300 rounded-lg text-sm font-medium hover:bg-slate-50 dark:bg-slate-900/50 dark:hover:bg-slate-700 transition-colors flex items-center gap-2">
                    <span class="material-symbols-outlined notranslate" translate="no" style="font-size:18px">download</span> Export
                </button>
                <a href="{{ route('billing.payments.create') }}" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg text-sm font-medium transition-colors flex items-center gap-2">
                    <span class="material-symbols-outlined notranslate" translate="no" style="font-size:18px">add</span> Catat Pembayaran
                </a>
            </div>
        </div>

        {{-- DATA TABLE --}}
        <div class="bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl shadow-sm overflow-hidden overflow-x-auto relative">
            
            {{-- Bulk Actions Bar --}}
            @if(count($selected) > 0)
            <div class="absolute top-0 left-0 w-full h-12 bg-indigo-50 dark:bg-indigo-900/30 border-b border-indigo-100 dark:border-indigo-800 flex items-center justify-between px-4 z-10">
                <div class="flex items-center gap-3">
                    <span class="text-sm font-semibold text-indigo-700 dark:text-indigo-300">
                        <span class="w-5 h-5 inline-flex items-center justify-center bg-indigo-600 text-white rounded-full text-xs mr-1">{{ count($selected) }}</span> Terpilih
                    </span>
                </div>
                <div class="flex items-center gap-2">
                    <button wire:click="bulkDelete" wire:confirm="Yakin ingin menghapus {{ count($selected) }} pembayaran terpilih secara permanen?" class="px-3 py-1.5 bg-red-600 hover:bg-red-700 text-white rounded-lg text-xs font-medium transition-colors shadow-sm flex items-center gap-1.5">
                        <span class="material-symbols-outlined" style="font-size:16px">delete</span> Hapus
                    </button>
                </div>
            </div>
            @endif

            <table class="w-full text-sm text-left">
                <thead>
                    <tr class="border-b border-slate-200 dark:border-slate-700 text-xs uppercase tracking-wider text-slate-500 dark:text-slate-400 bg-slate-50 dark:bg-slate-900/80">
                        <th class="px-4 py-3 w-10 text-center">
                            <input type="checkbox" wire:model.live="selectAll" class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500 bg-slate-50 dark:bg-slate-900/50 dark:border-slate-600 cursor-pointer dark:bg-slate-900 dark:text-slate-100">
                        </th>
                        <th class="px-4 py-3 font-semibold whitespace-nowrap">No. Referensi</th>
                        <th class="px-4 py-3 font-semibold whitespace-nowrap">Pelanggan</th>
                        <th class="px-4 py-3 font-semibold whitespace-nowrap">Invoice</th>
                        <th class="px-4 py-3 font-semibold whitespace-nowrap">Tanggal Bayar</th>
                        <th class="px-4 py-3 font-semibold whitespace-nowrap">Metode</th>
                        <th class="px-4 py-3 font-semibold whitespace-nowrap">Status</th>
                        <th class="px-4 py-3 font-semibold whitespace-nowrap text-right">Nominal</th>
                        <th class="px-4 py-3 font-semibold whitespace-nowrap text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-700">
                    @forelse($payments as $payment)
                        <tr class="hover:bg-slate-50 dark:bg-slate-900/50 dark:hover:bg-slate-800/50 transition-colors">
                            <td class="px-4 py-3 text-center">
                                <input type="checkbox" wire:model.live="selected" value="{{ $payment->id }}" class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500 bg-slate-50 dark:bg-slate-900/50 dark:border-slate-600 cursor-pointer">
                            </td>
                            <td class="px-4 py-3 font-medium text-slate-900 dark:text-slate-100">
                                {{ $payment->reference_number ?? '-' }}
                            </td>
                            <td class="px-4 py-3 text-slate-700 dark:text-slate-300">
                                <div class="font-medium">{{ $payment->customer->name ?? '-' }}</div>
                            </td>
                            <td class="px-4 py-3 text-slate-600 dark:text-slate-400">
    @if($payment->invoices->count() > 0)
        <a href="{{ route('billing.invoices.show', $payment->invoices->first()->id) }}" class="text-blue-600 dark:text-blue-400 hover:underline">
            {{ $payment->invoices->first()->invoice_number }}
        </a>
    @else
        -
    @endif
</td>
                            <td class="px-4 py-3 text-slate-600 dark:text-slate-400">
                                {{ $payment->paid_at?->format('d M Y, H:i') ?? '-' }}
                            </td>
                            <td class="px-4 py-3 text-slate-600 dark:text-slate-400 uppercase text-xs font-semibold">
                                {{ str_replace('_', ' ', $payment->method) }}
                            </td>
                            <td class="px-4 py-3">
                                @if($payment->status === 'success')
                                    <span class="inline-flex items-center gap-1.5 px-2 py-1 rounded-md text-xs font-medium bg-emerald-100 text-emerald-700 dark:bg-emerald-900/50 dark:text-emerald-400"><span class="material-symbols-outlined notranslate" style="font-size:14px" translate="no">check_circle</span> Sukses</span>
                                @elseif($payment->status === 'pending')
                                    <span class="inline-flex items-center gap-1.5 px-2 py-1 rounded-md text-xs font-medium bg-amber-100 text-amber-700 dark:bg-amber-900/50 dark:text-amber-400"><span class="material-symbols-outlined notranslate" style="font-size:14px" translate="no">schedule</span> Menunggu</span>
                                @elseif($payment->status === 'failed')
                                    <span class="inline-flex items-center gap-1.5 px-2 py-1 rounded-md text-xs font-medium bg-red-100 text-red-700 dark:bg-red-900/50 dark:text-red-400"><span class="material-symbols-outlined notranslate" style="font-size:14px" translate="no">cancel</span> Gagal</span>
                                @else
                                    <span class="inline-flex items-center gap-1.5 px-2 py-1 rounded-md text-xs font-medium bg-slate-100 text-slate-700 dark:bg-slate-800 dark:text-slate-400">{{ ucfirst($payment->status) }}</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-right font-mono font-medium text-emerald-600 dark:text-emerald-400">
                                Rp {{ number_format($payment->amount, 0, ',', '.') }}
                            </td>
                            <td class="px-4 py-3 text-center whitespace-nowrap">
    @if($payment->invoices->count() > 0)
        <a href="{{ route('billing.invoices.show', $payment->invoices->first()->id) }}" class="inline-flex items-center justify-center w-8 h-8 rounded-md bg-blue-50 hover:bg-blue-100 text-blue-600 dark:bg-blue-900/20 dark:hover:bg-blue-900/40 dark:text-blue-400 transition-colors" title="Print Invoice">
            <span class="material-symbols-outlined notranslate" translate="no" style="font-size:18px">print</span>
        </a>
    @else
        <span class="text-xs text-slate-400 italic">Tanpa Invoice</span>
    @endif
    @if(auth()->user()->hasRole('administrator'))
        <button wire:click="delete({{ $payment->id }})" onclick="confirm('Yakin ingin menghapus data pembayaran ini?') || event.stopImmediatePropagation()" class="inline-flex items-center justify-center w-8 h-8 rounded-md bg-red-50 hover:bg-red-100 text-red-600 dark:bg-red-900/20 dark:hover:bg-red-900/40 dark:text-red-400 transition-colors" title="Hapus">
            <span class="material-symbols-outlined notranslate" translate="no" style="font-size:18px">delete</span>
        </button>
    @endif
</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="px-4 py-12 text-center text-slate-500 dark:text-slate-400">
                                <div class="flex flex-col items-center justify-center">
                                    <span class="material-symbols-outlined notranslate mb-2 text-slate-300 dark:text-slate-600 dark:text-slate-400" translate="no" style="font-size:48px">payments</span>
                                    <p class="text-lg font-medium text-slate-900 dark:text-slate-100 mt-2">Tidak Ada Data</p>
                                    <p class="text-sm mt-1">Belum ada riwayat pembayaran yang ditemukan.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
            @if($payments->hasPages())
                <div class="px-4 py-3 border-t border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800/50">
                    {{ $payments->links() }}
                </div>
            @endif
        </div>
    </div>
</div>