<div>
    <div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-900 dark:text-slate-100 tracking-tight">Mutasi Saldo</h1>
            <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">Lacak riwayat pemasukan (Kredit) dan pengeluaran (Debit) dompet Anda.</p>
        </div>
    </div>

    <!-- Filter & Toolbar -->
    <div class="flex flex-col sm:flex-row gap-3 items-center justify-between bg-white dark:bg-slate-800 p-4 rounded-xl shadow-sm border border-slate-200 dark:border-slate-700 mb-6">
        <div class="flex-1 w-full relative">
            <span class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400">
                <span class="material-symbols-outlined notranslate" translate="no" style="font-size: 20px">search</span>
            </span>
            <input type="text" wire:model.live.debounce.300ms="search" placeholder="Cari deskripsi atau referensi..." class="w-full pl-9 pr-4 py-2 bg-slate-50 dark:bg-slate-900/50 border border-slate-200 dark:border-slate-700 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 transition-all dark:bg-slate-900 dark:text-slate-100">
        </div>
        <div class="flex items-center gap-3 w-full sm:w-auto">
            <select wire:model.live="type" class="pl-3 pr-8 py-2 bg-slate-50 dark:bg-slate-900/50 border border-slate-200 dark:border-slate-700 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 cursor-pointer dark:bg-slate-900 dark:text-slate-100">
                <option value="">Semua Mutasi</option>
                <option value="in">Pemasukan (Top Up)</option>
                <option value="out">Pengeluaran (Potongan Saldo)</option>
            </select>
            <select wire:model.live="perPage" class="pl-3 pr-8 py-2 bg-slate-50 dark:bg-slate-900/50 border border-slate-200 dark:border-slate-700 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 cursor-pointer dark:bg-slate-900 dark:text-slate-100">
                <option value="10">10 / halaman</option>
                <option value="25">25 / halaman</option>
                <option value="50">50 / halaman</option>
            </select>
        </div>
    </div>

    <!-- Data Table -->
    <div class="bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-slate-200 dark:border-slate-700 text-xs uppercase tracking-wider text-slate-500 dark:text-slate-400 bg-slate-50 dark:bg-slate-900/80">
                        <th class="px-4 py-3 text-left font-semibold">Tanggal</th>
                        <th class="px-4 py-3 text-left font-semibold">Deskripsi & Referensi</th>
                        <th class="px-4 py-3 text-left font-semibold">Tipe</th>
                        <th class="px-4 py-3 text-left font-semibold">Debit (Keluar)</th>
                        <th class="px-4 py-3 text-left font-semibold">Kredit (Masuk)</th>
                        <th class="px-4 py-3 text-left font-semibold">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200 dark:divide-slate-700">
                    @forelse($mutations as $row)
                        @php
                            $isIn = $row->gateway === 'reseller_topup';
                        @endphp
                        <tr class="hover:bg-slate-50 dark:bg-slate-900/50 dark:hover:bg-slate-800/50 transition-colors">
                            <td class="px-4 py-3 text-slate-600 dark:text-slate-300">
                                {{ $row->created_at->format('d M Y') }}<br>
                                <span class="text-xs text-slate-400">{{ $row->created_at->format('H:i') }}</span>
                            </td>
                            <td class="px-4 py-3">
                                <div class="font-medium text-slate-800 dark:text-slate-200">
                                    {{ $isIn ? 'Top Up Saldo' : 'Pemotongan Layanan' }}
                                </div>
                                <div class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">Ref: {{ $row->reference_number }}</div>
                            </td>
                            <td class="px-4 py-3">
                                @if($isIn)
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-emerald-100 text-emerald-800 dark:bg-emerald-900/30 dark:text-emerald-400">
                                        Pemasukan
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-rose-100 text-rose-800 dark:bg-rose-900/30 dark:text-rose-400">
                                        Pengeluaran
                                    </span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-slate-700 dark:text-slate-300 font-mono">
                                @if(!$isIn)
                                    - Rp {{ number_format($row->amount, 0, ',', '.') }}
                                @else
                                    -
                                @endif
                            </td>
                            <td class="px-4 py-3 text-slate-700 dark:text-slate-300 font-mono">
                                @if($isIn)
                                    + Rp {{ number_format($row->amount, 0, ',', '.') }}
                                @else
                                    -
                                @endif
                            </td>
                            <td class="px-4 py-3">
                                @if($row->status === 'success' || $row->status === 'approved')
                                    <span class="text-emerald-600 dark:text-emerald-400 font-medium"><span class="material-symbols-outlined notranslate text-sm align-text-bottom" translate="no">check_circle</span> Sukses</span>
                                @elseif($row->status === 'rejected')
                                    <span class="text-red-600 dark:text-red-400 font-medium"><span class="material-symbols-outlined notranslate text-sm align-text-bottom" translate="no">cancel</span> Ditolak</span>
                                @else
                                    <span class="text-amber-600 dark:text-amber-400 font-medium"><span class="material-symbols-outlined notranslate text-sm align-text-bottom" translate="no">pending</span> Pending</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-4 py-8 text-center text-slate-500 dark:text-slate-400">
                                <div class="flex flex-col items-center justify-center">
                                    <span class="material-symbols-outlined notranslate text-4xl mb-2 opacity-50" translate="no">receipt_long</span>
                                    <p>Belum ada riwayat mutasi.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($mutations->hasPages())
            <div class="px-4 py-3 border-t border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-900/50">
                {{ $mutations->links(data: ['scrollTo' => false]) }}
            </div>
        @endif
    </div>
</div>
