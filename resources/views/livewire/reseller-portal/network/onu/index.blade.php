<div class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-800 dark:text-slate-100">Data ONU / ONT (Modem)</h1>
            <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">Daftar perangkat modem pelanggan di wilayah jaringan Anda.</p>
        </div>
        <div class="flex items-center gap-3">
            <a href="{{ route('reseller-portal.network.olts.index') }}" class="px-4 py-2 border border-slate-200 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-800 text-slate-700 dark:text-slate-300 rounded-xl text-sm font-semibold transition-colors flex items-center gap-2">
                <span class="material-symbols-outlined notranslate text-[18px]">dns</span>
                Lihat OLT
            </a>
            <a href="{{ route('reseller-portal.network.acs-devices.index') }}" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-sm font-semibold shadow-sm transition-colors flex items-center gap-2">
                <span class="material-symbols-outlined notranslate text-[18px]">router</span>
                Kelola GenieACS
            </a>
        </div>
    </div>

    <!-- Filter Card -->
    <div class="bg-white dark:bg-slate-800 rounded-xl p-4 shadow-sm border border-slate-200 dark:border-slate-700 flex flex-col sm:flex-row gap-4 items-center justify-between">
        <div class="relative w-full sm:w-80">
            <span class="material-symbols-outlined notranslate absolute left-3 top-2.5 text-slate-400 text-[20px]">search</span>
            <input type="text" wire:model.live.debounce.300ms="search" placeholder="Cari SN, MAC, Nama, Pelanggan..." class="w-full pl-10 pr-4 py-2 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 dark:text-slate-100 placeholder-slate-400">
        </div>
        <div class="w-full sm:w-auto flex items-center gap-2">
            <select wire:model.live="status" class="px-3 py-2 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 dark:text-slate-100">
                <option value="">Semua Status</option>
                <option value="active">Active</option>
                <option value="inactive">Inactive</option>
            </select>
        </div>
    </div>

    <!-- Table Card -->
    <div class="bg-white dark:bg-slate-800 rounded-xl shadow-sm border border-slate-200 dark:border-slate-700 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-slate-600 dark:text-slate-300">
                <thead class="bg-slate-50 dark:bg-slate-900/50 text-slate-500 dark:text-slate-400 text-xs font-semibold uppercase tracking-wider border-b border-slate-200 dark:border-slate-700">
                    <tr>
                        <th class="px-6 py-3.5">Perangkat / Pelanggan</th>
                        <th class="px-6 py-3.5">Serial Number & MAC</th>
                        <th class="px-6 py-3.5">Koneksi Fiber (OLT / ODP)</th>
                        <th class="px-6 py-3.5">Redaman (Rx Power)</th>
                        <th class="px-6 py-3.5">Status</th>
                        <th class="px-6 py-3.5 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-700/60">
                    @forelse ($onus as $onu)
                        <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-700/30 transition-colors">
                            <td class="px-6 py-4">
                                <div class="font-semibold text-slate-800 dark:text-slate-100">{{ $onu->name }}</div>
                                <div class="text-xs text-indigo-600 dark:text-indigo-400 mt-0.5">
                                    {{ $onu->customerService?->customer?->name ?? 'Belum ada pelanggan' }}
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="font-mono text-xs font-bold text-slate-700 dark:text-slate-200">{{ $onu->serial_number ?? '-' }}</div>
                                <div class="font-mono text-[11px] text-slate-400">{{ $onu->mac_address ?? '-' }}</div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="text-xs text-slate-700 dark:text-slate-200 font-medium">OLT: {{ $onu->olt?->name ?? '-' }} (PON: {{ $onu->pon_port ?? '-' }})</div>
                                <div class="text-[11px] text-slate-400">ODP: {{ $onu->odp?->name ?? '-' }}</div>
                            </td>
                            <td class="px-6 py-4">
                                @if($onu->rx_power_dbm !== null)
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-mono font-bold {{ $onu->rx_power_dbm <= -27 ? 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400' : 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400' }}">
                                        {{ $onu->rx_power_dbm }} dBm
                                    </span>
                                @else
                                    <span class="text-xs text-slate-400">-</span>
                                @endif
                            </td>
                            <td class="px-6 py-4">
                                @if($onu->status === 'active')
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-emerald-50 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400">
                                        Aktif
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-slate-100 text-slate-600 dark:bg-slate-700 dark:text-slate-400">
                                        Nonaktif
                                    </span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-right">
                                <a href="{{ route('reseller-portal.network.acs-devices.index', ['search' => $onu->serial_number]) }}" class="inline-flex items-center gap-1 px-3 py-1 bg-slate-100 hover:bg-slate-200 dark:bg-slate-700 dark:hover:bg-slate-600 text-slate-700 dark:text-slate-200 rounded-lg text-xs font-medium transition-colors">
                                    <span class="material-symbols-outlined notranslate text-[14px]">tune</span>
                                    TR-069
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-10 text-center text-slate-400">
                                <span class="material-symbols-outlined notranslate text-4xl mb-2 text-slate-300">router</span>
                                <p>Tidak ada data ONU ditemukan di wilayah Anda.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($onus->hasPages())
            <div class="px-6 py-4 border-t border-slate-200 dark:border-slate-700">
                {{ $onus->links() }}
            </div>
        @endif
    </div>
</div>
