<div class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-800 dark:text-slate-100">Perangkat GenieACS (TR-069)</h1>
            <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">Pantau dan kelola CPE/Modem pelanggan di wilayah jaringan Anda secara remote.</p>
        </div>
        <div class="flex items-center gap-3">
            <a href="{{ route('reseller-portal.network.onus.index') }}" class="px-4 py-2 border border-slate-200 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-800 text-slate-700 dark:text-slate-300 rounded-xl text-sm font-semibold transition-colors flex items-center gap-2">
                <span class="material-symbols-outlined notranslate text-[18px]">router</span>
                Lihat Data ONU
            </a>
            <a href="{{ route('reseller-portal.network.olts.index') }}" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-sm font-semibold shadow-sm transition-colors flex items-center gap-2">
                <span class="material-symbols-outlined notranslate text-[18px]">dns</span>
                Lihat OLT
            </a>
        </div>
    </div>

    @if (session()->has('success'))
        <div class="p-4 bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-200 dark:border-emerald-800 text-emerald-700 dark:text-emerald-300 rounded-xl text-sm flex items-center gap-2">
            <span class="material-symbols-outlined notranslate text-[20px]">check_circle</span>
            {{ session('success') }}
        </div>
    @endif
    @if (session()->has('error'))
        <div class="p-4 bg-red-50 dark:bg-red-950/40 border border-red-200 dark:border-red-800 text-red-700 dark:text-red-300 rounded-xl text-sm flex items-center gap-2">
            <span class="material-symbols-outlined notranslate text-[20px]">error</span>
            {{ session('error') }}
        </div>
    @endif

    <!-- Filter Card -->
    <div class="bg-white dark:bg-slate-800 rounded-xl p-4 shadow-sm border border-slate-200 dark:border-slate-700 flex flex-col sm:flex-row gap-4 items-center justify-between">
        <div class="relative w-full sm:w-80">
            <span class="material-symbols-outlined notranslate absolute left-3 top-2.5 text-slate-400 text-[20px]">search</span>
            <input type="text" wire:model.live.debounce.300ms="search" placeholder="Cari SN, IP, Model, Pelanggan..." class="w-full pl-10 pr-4 py-2 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 dark:text-slate-100 placeholder-slate-400">
        </div>
        <div class="w-full sm:w-auto flex items-center gap-2">
            <select wire:model.live="status" class="px-3 py-2 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 dark:text-slate-100">
                <option value="">Semua Status</option>
                <option value="online">Online</option>
                <option value="offline">Offline</option>
            </select>
        </div>
    </div>

    <!-- Table Card -->
    <div class="bg-white dark:bg-slate-800 rounded-xl shadow-sm border border-slate-200 dark:border-slate-700 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-slate-600 dark:text-slate-300">
                <thead class="bg-slate-50 dark:bg-slate-900/50 text-slate-500 dark:text-slate-400 text-xs font-semibold uppercase tracking-wider border-b border-slate-200 dark:border-slate-700">
                    <tr>
                        <th class="px-6 py-3.5">Perangkat CPE</th>
                        <th class="px-6 py-3.5">Pelanggan Terkait</th>
                        <th class="px-6 py-3.5">Alamat IP & MAC</th>
                        <th class="px-6 py-3.5">Status TR-069</th>
                        <th class="px-6 py-3.5">Last Inform</th>
                        <th class="px-6 py-3.5 text-right">Aksi Remote</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-700/60">
                    @forelse ($devices as $device)
                        <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-700/30 transition-colors">
                            <td class="px-6 py-4">
                                <div class="font-semibold text-slate-800 dark:text-slate-100 font-mono text-xs">{{ $device->serial_number }}</div>
                                <div class="text-[11px] text-slate-400">{{ $device->manufacturer ?? 'Vendor' }} - {{ $device->model ?? 'Model' }}</div>
                            </td>
                            <td class="px-6 py-4">
                                @if($device->customerService?->customer)
                                    <div class="font-medium text-slate-800 dark:text-slate-200">{{ $device->customerService->customer->name }}</div>
                                    <div class="text-xs text-indigo-600 dark:text-indigo-400 font-mono">{{ $device->customerService->customer->customer_code }}</div>
                                @elseif($device->onu?->customerService?->customer)
                                    <div class="font-medium text-slate-800 dark:text-slate-200">{{ $device->onu->customerService->customer->name }}</div>
                                    <div class="text-xs text-indigo-600 dark:text-indigo-400 font-mono">{{ $device->onu->customerService->customer->customer_code }}</div>
                                @else
                                    <span class="text-xs text-slate-400">Tidak tertaut</span>
                                @endif
                            </td>
                            <td class="px-6 py-4">
                                <div class="font-mono text-xs text-slate-700 dark:text-slate-200">{{ $device->ip_address ?? '-' }}</div>
                                <div class="font-mono text-[11px] text-slate-400">{{ $device->mac_address ?? '-' }}</div>
                            </td>
                            <td class="px-6 py-4">
                                @if($device->status === 'online')
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-semibold bg-emerald-50 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400">
                                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                        Online
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-semibold bg-slate-100 text-slate-600 dark:bg-slate-700 dark:text-slate-400">
                                        <span class="w-1.5 h-1.5 rounded-full bg-slate-400"></span>
                                        Offline
                                    </span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-xs text-slate-500">
                                {{ $device->last_inform ? $device->last_inform->diffForHumans() : '-' }}
                            </td>
                            <td class="px-6 py-4 text-right">
                                <button type="button" wire:click="rebootDevice({{ $device->id }})" wire:confirm="Apakah Anda yakin ingin me-reboot modem {{ $device->serial_number }} ini secara remote?" class="inline-flex items-center gap-1 px-3 py-1 bg-amber-50 hover:bg-amber-100 text-amber-700 dark:bg-amber-950/40 dark:text-amber-400 rounded-lg text-xs font-medium transition-colors">
                                    <span class="material-symbols-outlined notranslate text-[14px]">restart_alt</span>
                                    Reboot
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-10 text-center text-slate-400">
                                <span class="material-symbols-outlined notranslate text-4xl mb-2 text-slate-300">devices</span>
                                <p>Tidak ada perangkat TR-069 ditemukan di wilayah Anda.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($devices->hasPages())
            <div class="px-6 py-4 border-t border-slate-200 dark:border-slate-700">
                {{ $devices->links() }}
            </div>
        @endif
    </div>
</div>
