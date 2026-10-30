@section('page_title')
    <span class="material-symbols-outlined notranslate text-indigo-500" translate="no" style="font-size:24px">settings_remote</span>
    <span class="text-lg">Devices GenieACS</span>
@endsection

<div class="space-y-5 pb-10">
    @if (session()->has('success'))
        <div class="p-4 bg-emerald-50 dark:bg-emerald-900/30 border border-emerald-200 dark:border-emerald-800 text-emerald-700 dark:text-emerald-300 rounded-xl flex items-center gap-3">
            <span class="material-symbols-outlined notranslate text-emerald-600 dark:text-emerald-400" translate="no">check_circle</span>
            <p class="text-sm font-medium">{{ session('success') }}</p>
        </div>
    @endif

    @if (session()->has('error'))
        <div class="p-4 bg-rose-50 dark:bg-rose-900/30 border border-rose-200 dark:border-rose-800 text-rose-700 dark:text-rose-300 rounded-xl flex items-center gap-3">
            <span class="material-symbols-outlined notranslate text-rose-600 dark:text-rose-400" translate="no">error</span>
            <p class="text-sm font-medium">{{ session('error') }}</p>
        </div>
    @endif

    @if(isset($territory) && !$territory['has_territory'])
        <div class="p-4 bg-amber-50 dark:bg-amber-950/30 border border-amber-200 dark:border-amber-800/60 rounded-xl text-amber-800 dark:text-amber-300 text-sm flex items-start gap-3">
            <span class="material-symbols-outlined notranslate text-amber-500 shrink-0 mt-0.5" translate="no">info</span>
            <div>
                <p class="font-semibold">Cakupan Wilayah Belum Diatur pada Akun Anda</p>
                <p class="text-xs opacity-90 mt-0.5">Saat ini hanya modem TR-069 yang tertaut langsung ke pelanggan Anda yang ditampilkan. Hubungi Administrator untuk mengatur <strong>Cabang Kantor</strong> atau <strong>Wilayah Operasional</strong> pada profil akun Reseller Anda.</p>
            </div>
        </div>
    @endif

    {{-- TOOLBAR & FILTER (SERAGAM DENGAN PORTAL ADMIN) --}}
    <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-3 bg-white dark:bg-slate-800 p-3 sm:p-4 rounded-xl border border-slate-200 dark:border-slate-700 shadow-sm">
        <div class="flex flex-wrap items-center gap-2 w-full lg:w-auto shrink-0">
            @if(!empty($territory['label']))
                <span class="inline-flex items-center gap-1.5 px-3 py-2 rounded-lg text-xs font-semibold {{ ($territory['has_territory'] ?? false) ? 'bg-indigo-50 text-indigo-700 dark:bg-indigo-900/30 dark:text-indigo-300 border border-indigo-100 dark:border-indigo-800/50' : 'bg-slate-100 text-slate-500 dark:bg-slate-700 dark:text-slate-400' }}">
                    <span class="material-symbols-outlined notranslate" style="font-size: 16px" translate="no">location_on</span>
                    {{ $territory['label'] }}
                </span>
            @endif
            <a href="{{ route('reseller-portal.network.onus.index') }}" class="inline-flex items-center justify-center gap-1.5 px-3.5 py-2 bg-slate-50 hover:bg-slate-100 dark:bg-slate-700/50 dark:hover:bg-slate-700 border border-slate-200 dark:border-slate-600 text-slate-700 dark:text-slate-300 text-sm font-medium rounded-lg shadow-sm transition-all whitespace-nowrap">
                <span class="material-symbols-outlined notranslate text-indigo-500" translate="no" style="font-size:18px">router</span>
                <span>Data ONU</span>
            </a>
            <a href="{{ route('reseller-portal.network.olts.index') }}" class="inline-flex items-center justify-center gap-1.5 px-3.5 py-2 bg-slate-50 hover:bg-slate-100 dark:bg-slate-700/50 dark:hover:bg-slate-700 border border-slate-200 dark:border-slate-600 text-slate-700 dark:text-slate-300 text-sm font-medium rounded-lg shadow-sm transition-all whitespace-nowrap">
                <span class="material-symbols-outlined notranslate text-indigo-500" translate="no" style="font-size:18px">dns</span>
                <span>Data OLT</span>
            </a>
        </div>

        <div class="flex flex-col sm:flex-row sm:items-center gap-2.5 w-full lg:w-auto">
            <div class="relative w-full sm:w-64">
                <span class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 pointer-events-none">
                    <span class="material-symbols-outlined notranslate" translate="no" style="font-size:18px">search</span>
                </span>
                <input type="text" wire:model.live.debounce.400ms="search"
                       placeholder="Cari SN, MAC, Model, IP PPPoE/TR069..."
                       class="w-full pl-9 pr-4 py-2 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-lg text-sm text-slate-700 dark:text-slate-200 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all">
            </div>
            <div class="grid grid-cols-2 sm:flex sm:items-center gap-2 w-full sm:w-auto">
                <select wire:model.live="status"
                        class="w-full sm:w-auto pl-3 pr-8 py-2 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-lg text-sm text-slate-700 dark:text-slate-200 focus:outline-none focus:ring-2 focus:ring-indigo-500 cursor-pointer">
                    <option value="">Semua Status</option>
                    <option value="online">Online</option>
                    <option value="offline">Offline</option>
                </select>
                <select wire:model.live="perPage"
                        class="w-full sm:w-auto pl-3 pr-8 py-2 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-lg text-sm text-slate-700 dark:text-slate-200 focus:outline-none focus:ring-2 focus:ring-indigo-500 cursor-pointer">
                    <option value="20">20 / hal</option>
                    <option value="50">50 / hal</option>
                    <option value="100">100 / hal</option>
                    <option value="500">500 / hal</option>
                </select>
                @if($search !== '' || $status !== '')
                    <button type="button" wire:click="resetFilters"
                            title="Reset Filter"
                            class="col-span-2 sm:col-auto inline-flex items-center justify-center gap-1.5 px-3 py-2 bg-red-50 dark:bg-red-900/30 hover:bg-red-100 dark:hover:bg-red-900/50 text-red-600 dark:text-red-400 rounded-lg text-sm font-medium transition-colors cursor-pointer whitespace-nowrap">
                        <span class="material-symbols-outlined notranslate" translate="no" style="font-size:16px">filter_alt_off</span>
                        <span>Reset</span>
                    </button>
                @endif
            </div>
        </div>
    </div>

    {{-- DATA TABLE (SERAGAM DENGAN PORTAL ADMIN: 9 KOLOM) --}}
    <div class="bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm text-left">
                <thead>
                    <tr class="border-b border-slate-200 dark:border-slate-700 text-slate-500 dark:text-slate-400 bg-slate-50/80 dark:bg-slate-800/80 text-xs uppercase tracking-wider">
                        <th class="px-4 py-3 font-semibold whitespace-nowrap">Status</th>
                        <th class="px-4 py-3 font-semibold whitespace-nowrap">Nama Pelanggan</th>
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
                        @php
                            $cust = $device->resolved_customer ?? $device->customerService?->customer ?? $device->onu?->customerService?->customer;
                            $uname = $device->customerService?->username ?? $device->onu?->customerService?->username;
                            $rawVendor = $device->getRawOriginal('vendor') ?? null;
                            $vendorName = $device->vendor?->name ?? (is_string($rawVendor) && $rawVendor !== '' ? $rawVendor : null) ?? $device->manufacturer ?? '-';
                            $signalRaw = ($device->signal !== null && $device->signal !== '') ? $device->signal : $device->onu?->rx_power_dbm;
                        @endphp
                        <tr wire:key="acs-dev-{{ $device->id }}" class="hover:bg-slate-50 dark:hover:bg-slate-700/50 transition-colors">
                            <td class="px-4 py-3 whitespace-nowrap">
                                <span class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded-md text-[10px] font-medium uppercase tracking-wider {{ $device->status === 'online' ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-400' : 'bg-rose-100 text-rose-700 dark:bg-rose-900/40 dark:text-rose-400' }}">
                                    <span class="w-1.5 h-1.5 rounded-full {{ $device->status === 'online' ? 'bg-emerald-500' : 'bg-rose-500' }}"></span>
                                    {{ $device->status ?: 'offline' }}
                                </span>
                            </td>
                            <td class="px-4 py-3">
                                @if($cust)
                                    <div>
                                        <div class="font-semibold text-slate-800 dark:text-slate-100">
                                            {{ $cust->name }}
                                        </div>
                                        @if($uname)
                                            <div class="text-[11px] font-mono text-slate-500 dark:text-slate-400 flex items-center gap-1">
                                                <span class="material-symbols-outlined notranslate text-slate-400" style="font-size:12px" translate="no">person</span>
                                                {{ $uname }}
                                            </div>
                                        @endif
                                    </div>
                                @else
                                    <span class="inline-flex items-center gap-1 text-xs text-slate-400 dark:text-slate-500 italic">
                                        <span class="material-symbols-outlined notranslate text-slate-400" style="font-size:13px" translate="no">link_off</span>
                                        Belum terikat
                                    </span>
                                @endif
                            </td>
                            <td class="px-4 py-3 font-medium text-slate-900 dark:text-slate-100 whitespace-nowrap">
                                <a href="{{ route('reseller-portal.network.acs-devices.show', $device->id) }}" class="text-indigo-600 dark:text-indigo-400 hover:underline font-semibold flex items-center gap-1.5" title="Lihat Detail Device">
                                    {{ $device->serial_number }}
                                </a>
                            </td>
                            <td class="px-4 py-3 font-mono text-xs whitespace-nowrap">
                                {{ $device->mac_address ?? '-' }}
                            </td>
                            <td class="px-4 py-3">
                                <div class="font-medium text-slate-900 dark:text-slate-100">{{ $device->model ?? '-' }}</div>
                                <div class="text-[11px] text-slate-500 dark:text-slate-400">{{ $vendorName }}</div>
                            </td>
                            <td class="px-4 py-3 whitespace-nowrap">
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
                            <td class="px-4 py-3 font-mono text-xs whitespace-nowrap">
                                @if($signalRaw !== null && $signalRaw !== '')
                                    @php
                                        $sigVal = (float) $signalRaw;
                                        $sigBg = "bg-slate-100 text-slate-700 dark:bg-slate-800 dark:text-slate-300";
                                        if ($sigVal < -26) {
                                            $sigBg = "bg-rose-100 text-rose-700 dark:bg-rose-900/40 dark:text-rose-400 border border-rose-200 dark:border-rose-800";
                                        } elseif ($sigVal < -23) {
                                            $sigBg = "bg-amber-100 text-amber-700 dark:bg-amber-900/40 dark:text-amber-400 border border-amber-200 dark:border-amber-800";
                                        } elseif ($sigVal <= -8) {
                                            $sigBg = "bg-emerald-100 text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-400 border border-emerald-200 dark:border-emerald-800";
                                        }
                                    @endphp
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-semibold {{ $sigBg }}">
                                        {{ $signalRaw }} dBm
                                    </span>
                                @else
                                    <span class="text-slate-400">-</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 whitespace-nowrap">{{ $device->software_version ?? '-' }}</td>
                            <td class="px-4 py-3 text-right whitespace-nowrap">
                                <div class="flex justify-end gap-1">
                                    <button type="button"
                                            wire:click="summon({{ $device->id }})"
                                            wire:loading.attr="disabled"
                                            class="inline-flex items-center justify-center w-8 h-8 rounded-md bg-amber-50 hover:bg-amber-100 dark:bg-amber-900/30 dark:hover:bg-amber-900/50 text-amber-600 dark:text-amber-400 transition-colors cursor-pointer"
                                            title="Summon (Refresh Data)">
                                        <span wire:loading.remove wire:target="summon({{ $device->id }})" class="material-symbols-outlined notranslate" translate="no" style="font-size:18px">sensors</span>
                                        <span wire:loading wire:target="summon({{ $device->id }})" class="material-symbols-outlined notranslate animate-spin" translate="no" style="font-size:18px">hourglass_empty</span>
                                    </button>
                                    <a href="{{ route('reseller-portal.network.acs-devices.show', $device->id) }}"
                                       class="inline-flex items-center justify-center w-8 h-8 rounded-md bg-indigo-50 hover:bg-indigo-100 dark:bg-indigo-900/30 dark:hover:bg-indigo-900/50 text-indigo-600 dark:text-indigo-400 transition-colors cursor-pointer"
                                       title="Detail Perangkat">
                                        <span class="material-symbols-outlined notranslate" translate="no" style="font-size:18px">info</span>
                                    </a>
                                    <button type="button"
                                            wire:click="rebootDevice({{ $device->id }})"
                                            wire:confirm="Yakin ingin me-reboot modem {{ $device->serial_number }} via TR-069?"
                                            wire:loading.attr="disabled"
                                            class="inline-flex items-center justify-center w-8 h-8 rounded-md bg-rose-50 hover:bg-rose-100 dark:bg-rose-900/30 dark:hover:bg-rose-900/50 text-rose-600 dark:text-rose-400 transition-colors cursor-pointer"
                                            title="Reboot Modem (TR-069)">
                                        <span wire:loading.remove wire:target="rebootDevice({{ $device->id }})" class="material-symbols-outlined notranslate" translate="no" style="font-size:18px">restart_alt</span>
                                        <span wire:loading wire:target="rebootDevice({{ $device->id }})" class="material-symbols-outlined notranslate animate-spin" translate="no" style="font-size:18px">hourglass_empty</span>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="px-4 py-12 text-center">
                                <div class="flex flex-col items-center justify-center text-slate-500 dark:text-slate-400">
                                    <span class="material-symbols-outlined notranslate text-slate-300 dark:text-slate-600 mb-3" translate="no" style="font-size:48px">router</span>
                                    <div class="text-sm font-medium text-slate-900 dark:text-slate-100">Belum ada data Device</div>
                                    <div class="text-xs mt-1 max-w-md">Modem TR-069 yang terhubung ke OLT/ODP wilayah Anda atau tertaut ke pelanggan Anda akan otomatis tampil di sini.</div>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if(method_exists($devices, 'hasPages') && $devices->hasPages())
            <div class="px-4 py-3 border-t border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800/80">
                {{ $devices->links() }}
            </div>
        @endif
    </div>
</div>
