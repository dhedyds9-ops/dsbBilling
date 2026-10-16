<div class="space-y-6">

    <!-- Page Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-900 dark:text-slate-100 tracking-tight flex items-center gap-2">
                <span class="material-symbols-outlined notranslate text-primary-600 text-3xl" translate="no">history_edu</span>
                Riwayat Pemasangan & Perbaikan
            </h1>
            <p class="text-xs sm:text-sm text-slate-500 dark:text-slate-400 mt-1">
                Catatan pemasangan pelanggan baru (PSB) dan penanganan kendala teknis oleh teknisi di area Anda.
            </p>
        </div>

        <!-- Filter Periode (Bulan & Tahun) -->
        <div class="flex items-center gap-2">
            <select wire:model.live="selectedMonth" class="px-3 py-2 text-xs font-semibold rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-700 dark:text-slate-200 focus:ring-2 focus:ring-primary-500 shadow-sm">
                @for($m = 1; $m <= 12; $m++)
                    <option value="{{ $m }}">{{ \Carbon\Carbon::create(null, $m, 1)->translatedFormat('F') }}</option>
                @endfor
            </select>
            <select wire:model.live="selectedYear" class="px-3 py-2 text-xs font-semibold rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-700 dark:text-slate-200 focus:ring-2 focus:ring-primary-500 shadow-sm">
                @for($y = now()->year - 2; $y <= now()->year + 1; $y++)
                    <option value="{{ $y }}">{{ $y }}</option>
                @endfor
            </select>
        </div>
    </div>

    <!-- Summary KPI Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div class="bg-white dark:bg-slate-800 rounded-2xl p-5 border border-slate-200 dark:border-slate-700 shadow-sm flex items-start justify-between relative overflow-hidden">
            <div class="absolute top-0 left-0 right-0 h-1 bg-gradient-to-r from-blue-500 to-indigo-500"></div>
            <div>
                <p class="text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Total Pemasangan (PSB)</p>
                <p class="text-2xl font-black text-slate-900 dark:text-white mt-2">{{ number_format($totalInstallations) }}</p>
                <p class="text-[11px] text-slate-400 dark:text-slate-500 mt-1">Pelanggan terpasang</p>
            </div>
            <div class="w-12 h-12 rounded-xl bg-blue-50 dark:bg-blue-900/30 flex items-center justify-center text-blue-600 dark:text-blue-400 shrink-0">
                <span class="material-symbols-outlined notranslate text-2xl" translate="no">build_circle</span>
            </div>
        </div>

        <div class="bg-white dark:bg-slate-800 rounded-2xl p-5 border border-slate-200 dark:border-slate-700 shadow-sm flex items-start justify-between relative overflow-hidden">
            <div class="absolute top-0 left-0 right-0 h-1 bg-gradient-to-r from-emerald-500 to-teal-500"></div>
            <div>
                <p class="text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Pemasangan Bulan Ini</p>
                <p class="text-2xl font-black text-emerald-600 dark:text-emerald-400 mt-2">{{ number_format($monthInstallations) }}</p>
                <p class="text-[11px] text-slate-400 dark:text-slate-500 mt-1">Periode {{ $selectedMonthName }}</p>
            </div>
            <div class="w-12 h-12 rounded-xl bg-emerald-50 dark:bg-emerald-900/30 flex items-center justify-center text-emerald-600 dark:text-emerald-400 shrink-0">
                <span class="material-symbols-outlined notranslate text-2xl" translate="no">verified</span>
            </div>
        </div>

        <div class="bg-white dark:bg-slate-800 rounded-2xl p-5 border border-slate-200 dark:border-slate-700 shadow-sm flex items-start justify-between relative overflow-hidden">
            <div class="absolute top-0 left-0 right-0 h-1 bg-gradient-to-r from-amber-500 to-orange-500"></div>
            <div>
                <p class="text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Perbaikan & Tiket</p>
                <p class="text-2xl font-black text-amber-600 dark:text-amber-400 mt-2">{{ number_format($totalRepairs) }}</p>
                <p class="text-[11px] text-slate-400 dark:text-slate-500 mt-1">Total tiket diselesaikan</p>
            </div>
            <div class="w-12 h-12 rounded-xl bg-amber-50 dark:bg-amber-900/30 flex items-center justify-center text-amber-600 dark:text-amber-400 shrink-0">
                <span class="material-symbols-outlined notranslate text-2xl" translate="no">home_repair_service</span>
            </div>
        </div>
    </div>

    <!-- Filters & Search Toolbar -->
    <div class="flex flex-col sm:flex-row items-stretch sm:items-center justify-between gap-3 bg-white dark:bg-slate-800 p-3 rounded-2xl border border-slate-200 dark:border-slate-700 shadow-sm">
        {{-- Tipe Tabs --}}
        <div class="flex items-center gap-1 overflow-x-auto pb-1 sm:pb-0">
            <button wire:click="setTypeFilter('all')" type="button"
                    class="px-3.5 py-1.5 rounded-xl text-xs font-bold transition-all
                           {{ $typeFilter === 'all' ? 'bg-primary-600 text-white shadow-sm' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-700' }}">
                Semua Pekerjaan
            </button>
            <button wire:click="setTypeFilter('installation')" type="button"
                    class="px-3.5 py-1.5 rounded-xl text-xs font-bold transition-all inline-flex items-center gap-1
                           {{ $typeFilter === 'installation' ? 'bg-blue-600 text-white shadow-sm' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-700' }}">
                <span class="material-symbols-outlined notranslate text-xs" translate="no">build_circle</span>
                Pasang Baru (PSB)
            </button>
            <button wire:click="setTypeFilter('repair')" type="button"
                    class="px-3.5 py-1.5 rounded-xl text-xs font-bold transition-all inline-flex items-center gap-1
                           {{ $typeFilter === 'repair' ? 'bg-amber-600 text-white shadow-sm' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-700' }}">
                <span class="material-symbols-outlined notranslate text-xs" translate="no">build</span>
                Perbaikan
            </button>
        </div>

        {{-- Search Input --}}
        <div class="relative w-full sm:w-72">
            <span class="material-symbols-outlined notranslate absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-sm" translate="no">search</span>
            <input type="text" wire:model.live.debounce.300ms="search" placeholder="Cari pelanggan, SN ONU, teknisi..."
                   class="w-full pl-9 pr-8 py-2 text-xs rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-900 text-slate-900 dark:text-slate-100 focus:ring-2 focus:ring-primary-500">
            @if($search)
                <button wire:click="$set('search', '')" class="absolute right-2.5 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600">
                    <span class="material-symbols-outlined notranslate text-xs" translate="no">close</span>
                </button>
            @endif
        </div>
    </div>

    <!-- Data Table -->
    <div class="bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-2xl shadow-sm overflow-hidden">
        <div class="px-6 py-4 border-b border-slate-200 dark:border-slate-700 flex justify-between items-center bg-slate-50 dark:bg-slate-900/50">
            <h3 class="font-bold text-sm text-slate-800 dark:text-slate-200 flex items-center gap-2">
                <span class="material-symbols-outlined notranslate text-primary-600 text-lg" translate="no">assignment_turned_in</span>
                Daftar Riwayat Lapangan
            </h3>
            <span class="text-xs text-slate-500 dark:text-slate-400">Total: {{ $items->total() }} pekerjaan</span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-slate-200 dark:border-slate-700 text-xs uppercase tracking-wider text-slate-500 dark:text-slate-400 bg-white dark:bg-slate-800">
                        <th class="px-6 py-3.5 text-left font-semibold">Tgl Selesai</th>
                        <th class="px-6 py-3.5 text-left font-semibold">Tipe Pekerjaan</th>
                        <th class="px-6 py-3.5 text-left font-semibold">Pelanggan</th>
                        <th class="px-6 py-3.5 text-left font-semibold">Teknisi</th>
                        <th class="px-6 py-3.5 text-left font-semibold">Detail Perangkat / Layanan</th>
                        <th class="px-6 py-3.5 text-center font-semibold">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-700">
                    @forelse($items as $item)
                        <tr class="hover:bg-slate-50 dark:bg-slate-900/50 dark:hover:bg-slate-800/50 transition-colors">
                            <td class="px-6 py-4 text-xs text-slate-600 dark:text-slate-400 whitespace-nowrap">
                                <div class="font-semibold text-slate-800 dark:text-slate-200">
                                    {{ $item->date ? \Carbon\Carbon::parse($item->date)->format('d M Y') : '-' }}
                                </div>
                                <div class="text-[11px] text-slate-400">
                                    {{ $item->date ? \Carbon\Carbon::parse($item->date)->format('H:i') : '' }}
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @if($item->type === 'installation')
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[11px] font-bold bg-blue-50 text-blue-700 dark:bg-blue-900/40 dark:text-blue-300 border border-blue-200 dark:border-blue-800">
                                        <span class="material-symbols-outlined notranslate text-xs" translate="no">build_circle</span>
                                        {{ $item->type_badge }}
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[11px] font-bold bg-amber-50 text-amber-700 dark:bg-amber-900/40 dark:text-amber-300 border border-amber-200 dark:border-amber-800">
                                        <span class="material-symbols-outlined notranslate text-xs" translate="no">build</span>
                                        {{ $item->type_badge }}
                                    </span>
                                @endif
                            </td>
                            <td class="px-6 py-4">
                                <div class="font-bold text-xs text-slate-900 dark:text-white">{{ $item->customer_name }}</div>
                                <div class="text-[11px] text-slate-400 dark:text-slate-500 flex items-center gap-2 mt-0.5">
                                    <span>{{ $item->customer_phone }}</span>
                                    <span>·</span>
                                    <span class="truncate max-w-[200px]" title="{{ $item->customer_address }}">{{ $item->customer_address }}</span>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center gap-1.5">
                                    <div class="w-6 h-6 rounded-full bg-slate-100 dark:bg-slate-700 flex items-center justify-center text-slate-600 dark:text-slate-300 text-[10px] font-bold">
                                        <span class="material-symbols-outlined notranslate text-xs" translate="no">person</span>
                                    </div>
                                    <span class="text-xs font-semibold text-slate-700 dark:text-slate-300">{{ $item->technician_name }}</span>
                                </div>
                            </td>
                            <td class="px-6 py-4 text-xs">
                                <div class="font-medium text-slate-800 dark:text-slate-200">
                                    {{ $item->package_name }}
                                </div>
                                <div class="text-[11px] text-slate-400 font-mono mt-0.5">
                                    {{ $item->device_info }}
                                </div>
                            </td>
                            <td class="px-6 py-4 text-center whitespace-nowrap">
                                @if($item->is_completed)
                                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800 dark:bg-emerald-900/50 dark:text-emerald-300">
                                        <span class="material-symbols-outlined notranslate text-xs" translate="no">check_circle</span>
                                        {{ $item->status_label }}
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-amber-100 text-amber-800 dark:bg-amber-900/50 dark:text-amber-300">
                                        <span class="material-symbols-outlined notranslate text-xs" translate="no">schedule</span>
                                        {{ $item->status_label }}
                                    </span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-12 text-center text-slate-500 dark:text-slate-400">
                                <span class="material-symbols-outlined notranslate text-4xl text-slate-300 dark:text-slate-600 mb-2 block" translate="no">history_edu</span>
                                Belum ada riwayat pemasangan atau perbaikan pelanggan.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($items->hasPages())
            <div class="px-6 py-4 border-t border-slate-200 dark:border-slate-700 bg-slate-50/50 dark:bg-slate-900/30">
                {{ $items->links() }}
            </div>
        @endif
    </div>
</div>
