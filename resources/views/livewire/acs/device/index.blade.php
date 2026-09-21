<div>
    <!-- Flash Messages -->
    <div class="mb-4 px-4 sm:px-0">
        @if(session()->has('success'))
            <div class="p-4 mb-4 text-sm text-emerald-800 rounded-lg bg-emerald-50 dark:bg-emerald-900/30 dark:text-emerald-400 border border-emerald-200 dark:border-emerald-800" role="alert">
                <span class="font-medium">Berhasil!</span> {{ session('success') }}
            </div>
        @endif
        @if(session()->has('error'))
            <div class="p-4 mb-4 text-sm text-rose-800 rounded-lg bg-rose-50 dark:bg-rose-900/30 dark:text-rose-400 border border-rose-200 dark:border-rose-800" role="alert">
                <span class="font-medium">Gagal!</span> {{ session('error') }}
            </div>
        @endif
        @if(session()->has('info'))
            <div class="p-4 mb-4 text-sm text-blue-800 rounded-lg bg-blue-50 dark:bg-blue-900/30 dark:text-blue-400 border border-blue-200 dark:border-blue-800" role="alert">
                <span class="font-medium">Info:</span> {{ session('info') }}
            </div>
        @endif
    </div>
  @include('livewire.acs._tabs')

  <div class="space-y-5 pb-10">
    
    {{-- TOOLBAR & FILTER --}}
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div class="flex items-center gap-2">
            <button wire:click="syncDevices" wire:loading.attr="disabled" class="inline-flex items-center justify-center px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium rounded-lg shadow-sm shadow-indigo-200 dark:shadow-none transition-all">
                <span wire:loading.remove wire:target="syncDevices" class="material-symbols-outlined notranslate mr-1.5" translate="no" style="font-size:18px">sync</span>
                <span wire:loading wire:target="syncDevices" class="material-symbols-outlined notranslate mr-1.5 animate-spin" translate="no" style="font-size:18px">sync</span>
                Sinkronisasi
            </button>
            <button wire:click="export" class="inline-flex items-center justify-center px-4 py-2 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 hover:bg-slate-50 dark:bg-slate-900/50 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 text-sm font-medium rounded-lg shadow-sm transition-all">
                <span class="material-symbols-outlined notranslate mr-1.5" translate="no" style="font-size:18px">download</span>
                Export
            </button>
            <button wire:click="deleteAllOfflineDevices" wire:confirm="Yakin ingin menghapus semua device ACS yang berstatus offline secara permanen dari aplikasi?" class="inline-flex items-center justify-center px-4 py-2 bg-rose-600 hover:bg-rose-700 text-white text-sm font-medium rounded-lg shadow-sm shadow-rose-200 dark:shadow-none transition-all">
                <span class="material-symbols-outlined notranslate mr-1.5" translate="no" style="font-size:18px">delete_sweep</span>
                Hapus Offline
            </button>
        </div>
        
        <div class="flex items-center gap-2 flex-wrap sm:flex-nowrap">
            {{-- Search --}}
            <div class="flex-1 relative min-w-[250px]">
                <span class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400">
                    <span class="material-symbols-outlined notranslate" translate="no" style="font-size:18px">search</span>
                </span>
                <input type="text" wire:model.live.debounce.300ms="search"
                       placeholder="Cari SN, MAC, Model, IP PPPoE/TR069..."
                       class="w-full pl-9 pr-4 py-2 bg-slate-50 dark:bg-slate-900/50 border border-slate-200 dark:border-slate-700 rounded-lg text-sm text-slate-700 dark:text-slate-300 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all dark:bg-slate-900 dark:text-slate-100">
            </div>
            
            {{-- Status Filter --}}
            <select wire:model.live="filters.status"
                    class="pl-3 pr-8 py-2 bg-slate-50 dark:bg-slate-900/50 border border-slate-200 dark:border-slate-700 rounded-lg text-sm text-slate-700 dark:text-slate-300 focus:outline-none focus:ring-2 focus:ring-indigo-500 cursor-pointer dark:bg-slate-900 dark:text-slate-100">
                <option value="">Semua Status</option>
                <option value="online">Online</option>
                <option value="offline">Offline</option>
            </select>
            
            {{-- Per Page --}}
            <select wire:model.live="perPage"
                    class="pl-3 pr-8 py-2 bg-slate-50 dark:bg-slate-900/50 border border-slate-200 dark:border-slate-700 rounded-lg text-sm text-slate-700 dark:text-slate-300 focus:outline-none focus:ring-2 focus:ring-indigo-500 cursor-pointer dark:bg-slate-900 dark:text-slate-100">
                <option value="10">10 / halaman</option>
                <option value="25">25 / halaman</option>
                <option value="50">50 / halaman</option>
                <option value="100">100 / halaman</option>
            </select>
            
            @if($search || count(array_filter($filters)) > 0)
            <button wire:click="resetFilters"
                    class="inline-flex items-center gap-1.5 px-3 py-2 bg-red-50 dark:bg-red-900/30 hover:bg-red-100 dark:bg-red-900/50 dark:hover:bg-red-800/50 text-red-600 dark:text-red-400 rounded-lg text-sm font-medium transition-colors cursor-pointer">
                <span class="material-symbols-outlined notranslate" translate="no" style="font-size:16px">filter_alt_off</span>
                Reset
            </button>
            @endif
        </div>
    </div>

    {{-- DATA TABLE --}}
    <div class="bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm text-left">
                <thead>
                    <tr class="border-b border-slate-200 dark:border-slate-700 text-xs uppercase tracking-wider text-slate-500 dark:text-slate-400 bg-slate-50/80 dark:bg-slate-800/80">
                        <th class="px-4 py-3 font-semibold whitespace-nowrap">Status</th>
                        <th class="px-4 py-3 font-semibold whitespace-nowrap">Serial Number</th>
                        <th class="px-4 py-3 font-semibold whitespace-nowrap">MAC Address</th>
                        <th class="px-4 py-3 font-semibold whitespace-nowrap">Model / Vendor</th>
                        <th class="px-4 py-3 font-semibold whitespace-nowrap">IP Address</th>
                        <th class="px-4 py-3 font-semibold whitespace-nowrap">Optical Power</th>
                        <th class="px-4 py-3 font-semibold whitespace-nowrap">Software Ver.</th>
                        <th class="px-4 py-3 font-semibold whitespace-nowrap text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-700/50 text-slate-700 dark:text-slate-300">
                    @forelse($devices as $device)
                        <tr class="hover:bg-slate-50 dark:bg-slate-900/50/50 dark:hover:bg-slate-800/50 transition-colors">
                            <td class="px-4 py-3">
                                <span class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded-md text-[10px] font-medium uppercase tracking-wider {{ $device->status === 'online' ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-400' : 'bg-rose-100 text-rose-700 dark:bg-rose-900/40 dark:text-rose-400' }}">
                                    <span class="w-1.5 h-1.5 rounded-full {{ $device->status === 'online' ? 'bg-emerald-500' : 'bg-rose-500' }}"></span>
                                    {{ $device->status }}
                                </span>
                            </td>
                            <td class="px-4 py-3 font-medium text-slate-900 dark:text-slate-100">{{ $device->serial_number }}</td>
                            <td class="px-4 py-3 font-mono text-xs">{{ $device->mac_address ?? '-' }}</td>
                            <td class="px-4 py-3">
                                <div class="font-medium text-slate-900 dark:text-slate-100">{{ $device->model ?? '-' }}</div>
                                <div class="text-[11px] text-slate-500 dark:text-slate-400">{{ $device->vendor->name ?? $device->vendor ?? '-' }}</div>
                            </td>
                            <td class="px-4 py-3">
                                <div class="font-mono text-xs text-slate-900 dark:text-slate-100" title="PPPoE IP">{{ $device->ip_address ?? '-' }}</div>
                                @if($device->connection_request_url)
                                    @php
                                        $tr069Ip = parse_url($device->connection_request_url, PHP_URL_HOST);
                                    @endphp
                                    @if($tr069Ip && $tr069Ip !== $device->ip_address)
                                        <div class="font-mono text-[11px] text-slate-500 dark:text-slate-400" title="TR069 IP (Connection Request)">{{ $tr069Ip }}</div>
                                    @endif
                                @endif
                            </td>
                            <td class="px-4 py-3 font-mono text-xs">
                                @if($device->signal)
                                    @php
                                        $sigVal = (float) $device->signal;
                                        $sigBg = "bg-slate-100 text-slate-700 dark:bg-slate-800 dark:text-slate-300";
                                        if ($sigVal < -26) {
                                            $sigBg = "bg-rose-100 text-rose-700 dark:bg-rose-900/40 dark:text-rose-400 border border-rose-200 dark:border-rose-800"; // Buruk
                                        } elseif ($sigVal < -23) {
                                            $sigBg = "bg-amber-100 text-amber-700 dark:bg-amber-900/40 dark:text-amber-400 border border-amber-200 dark:border-amber-800"; // Sedang
                                        } elseif ($sigVal <= -8) {
                                            $sigBg = "bg-emerald-100 text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-400 border border-emerald-200 dark:border-emerald-800"; // Bagus/Baik
                                        }
                                    @endphp
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-semibold {{ $sigBg }}">
                                        {{ $device->signal }} dBm
                                    </span>
                                @else
                                    <span class="text-slate-400">-</span>
                                @endif
                            </td>
                            <td class="px-4 py-3">{{ $device->software_version ?? '-' }}</td>
                            <td class="px-4 py-3 text-right">
                                <div class="flex justify-end gap-1">
                                    <a href="{{ route('acs.devices.show', $device->id) }}" class="inline-flex items-center justify-center w-8 h-8 rounded-md bg-indigo-50 hover:bg-indigo-100 dark:bg-indigo-900/30 dark:hover:bg-indigo-900/50 text-indigo-600 dark:text-indigo-400 transition-colors" title="Detail">
                                        <span class="material-symbols-outlined notranslate" translate="no" style="font-size:18px">info</span>
                                    </a>
                                    <a href="{{ route('acs.devices.edit', $device->id) }}" class="inline-flex items-center justify-center w-8 h-8 rounded-md bg-emerald-50 hover:bg-emerald-100 dark:bg-emerald-900/30 dark:hover:bg-emerald-900/50 text-emerald-600 dark:text-emerald-400 transition-colors" title="Edit">
                                        <span class="material-symbols-outlined notranslate" translate="no" style="font-size:18px">edit</span>
                                    </a>
                                    <button wire:click="delete({{ $device->id }})" wire:confirm="Yakin ingin menghapus device ini?" class="inline-flex items-center justify-center w-8 h-8 rounded-md bg-rose-50 hover:bg-rose-100 dark:bg-rose-900/30 dark:hover:bg-rose-900/50 text-rose-600 dark:text-rose-400 transition-colors" title="Hapus">
                                        <span class="material-symbols-outlined notranslate" translate="no" style="font-size:18px">delete</span>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-4 py-12 text-center">
                                <div class="flex flex-col items-center justify-center text-slate-500 dark:text-slate-400">
                                    <span class="material-symbols-outlined notranslate text-slate-300 dark:text-slate-600 dark:text-slate-400 mb-3" translate="no" style="font-size:48px">router</span>
                                    <div class="text-sm font-medium text-slate-900 dark:text-slate-100">Belum ada data Device</div>
                                    <div class="text-xs mt-1">Tambahkan device TR-069 baru untuk mulai monitoring.</div>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($devices->hasPages())
            <div class="px-4 py-3 border-t border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800/80">
                {{ $devices->links() }}
            </div>
        @endif
    </div>
  </div>
</div>

