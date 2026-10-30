@section('page_title')
    <div class="flex items-center gap-3">
        <a href="{{ route('reseller-portal.network.olts.index') }}" class="text-slate-500 dark:text-slate-400 hover:text-indigo-600 dark:hover:text-indigo-400 transition-colors">
            <span class="material-symbols-outlined notranslate" translate="no" style="font-size:24px">arrow_back</span>
        </a>
        <span class="material-symbols-outlined notranslate text-indigo-500" translate="no" style="font-size:24px">router</span>
        <div class="flex flex-col">
            <span class="text-lg">Daftar ONU (Pelanggan)</span>
            <span class="text-xs text-slate-500 font-normal">OLT: {{ $olt->name }} ({{ $olt->ip_address ?: ($olt->host ?: '-') }})</span>
        </div>
    </div>
@endsection

<div class="space-y-5 pb-10">
    @if(session('success'))
        <div class="flex items-center gap-3 px-4 py-3 bg-emerald-50 dark:bg-emerald-900/30 border border-emerald-200 dark:border-emerald-700 rounded-xl text-emerald-700 dark:text-emerald-400 text-sm font-medium">
            <span class="material-symbols-outlined notranslate" translate="no" style="font-size:18px">check_circle</span>
            {{ session('success') }}
        </div>
    @endif

    <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-sm border border-slate-200 dark:border-slate-700">
        <div class="p-3 sm:p-5 flex flex-col sm:flex-row sm:items-center justify-between gap-3 border-b border-slate-100 dark:border-slate-700/50">
            <h2 class="font-bold text-slate-800 dark:text-slate-100 text-lg">
                ONU / Modem Terhubung
            </h2>
            <div class="relative w-full sm:w-64">
                <span class="material-symbols-outlined notranslate absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-[18px] pointer-events-none" translate="no">search</span>
                <input type="text" wire:model.live.debounce.500ms="search" placeholder="Cari Nama, SN, MAC, PON..." class="w-full pl-9 pr-4 py-2 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl text-sm text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-indigo-500">
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-50/50 dark:bg-slate-900/50 text-slate-500 dark:text-slate-400 text-xs uppercase tracking-wider">
                        <th class="px-5 py-4 font-semibold border-b border-slate-200 dark:border-slate-700">Port PON</th>
                        <th class="px-5 py-4 font-semibold border-b border-slate-200 dark:border-slate-700">SN / MAC</th>
                        <th class="px-5 py-4 font-semibold border-b border-slate-200 dark:border-slate-700">Nama / Pelanggan</th>
                        <th class="px-5 py-4 font-semibold border-b border-slate-200 dark:border-slate-700">Sinyal (RX)</th>
                        <th class="px-5 py-4 font-semibold border-b border-slate-200 dark:border-slate-700">Status</th>
                        <th class="px-5 py-4 font-semibold border-b border-slate-200 dark:border-slate-700 text-right">TR-069</th>
                    </tr>
                </thead>
                <tbody class="text-sm divide-y divide-slate-100 dark:divide-slate-700/50">
                    @forelse($onus as $onu)
                        <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/50 transition-colors">
                            <td class="px-5 py-4 text-slate-800 dark:text-slate-200 font-bold font-mono text-xs">
                                {{ $onu->formatted_pon_port ?: ($onu->pon_port ?? '-') }}{{ $onu->onu_id_on_olt !== null ? ':' . $onu->onu_id_on_olt : '' }}
                            </td>
                            <td class="px-5 py-4 text-slate-600 dark:text-slate-400 font-mono text-xs">
                                <div class="font-bold text-slate-700 dark:text-slate-200">{{ $onu->serial_number ?: '-' }}</div>
                                @if($onu->mac_address)
                                    <div class="text-[11px] text-slate-400">{{ $onu->mac_address }}</div>
                                @endif
                            </td>
                            <td class="px-5 py-4 text-slate-800 dark:text-slate-200 font-medium w-1/3">
                                @if($editOnuId === $onu->id)
                                    <div class="flex items-center gap-2">
                                        <input type="text" wire:model="editOnuName" class="w-full px-3 py-1.5 bg-white dark:bg-slate-900 border border-indigo-300 dark:border-indigo-700 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500" @keydown.enter="$wire.saveName()" @keydown.escape="$wire.cancelEdit()">
                                        <button wire:click="saveName" class="p-1.5 bg-emerald-100 text-emerald-600 rounded-md hover:bg-emerald-200" title="Simpan">
                                            <span class="material-symbols-outlined notranslate" style="font-size: 16px" translate="no">check</span>
                                        </button>
                                        <button wire:click="cancelEdit" class="p-1.5 bg-red-100 text-red-600 rounded-md hover:bg-red-200" title="Batal">
                                            <span class="material-symbols-outlined notranslate" style="font-size: 16px" translate="no">close</span>
                                        </button>
                                    </div>
                                @else
                                    <div class="flex items-center justify-between group">
                                        <div>
                                            <div>{{ $onu->name ?: '(Tanpa Nama)' }}</div>
                                            @if($onu->customerService?->customer)
                                                <div class="text-xs text-indigo-600 dark:text-indigo-400">{{ $onu->customerService->customer->name }}</div>
                                            @endif
                                        </div>
                                        <button wire:click="startEditName({{ $onu->id }}, '{{ addslashes((string) $onu->name) }}')" class="opacity-0 group-hover:opacity-100 p-1 text-slate-400 hover:text-indigo-500 transition-opacity" title="Edit Nama ONU">
                                            <span class="material-symbols-outlined notranslate" style="font-size: 16px" translate="no">edit</span>
                                        </button>
                                    </div>
                                @endif
                            </td>
                            <td class="px-5 py-4">
                                @php
                                    $rx = $onu->rx_power_dbm !== null ? (float) $onu->rx_power_dbm : null;
                                    $rxColor = 'text-slate-400';
                                    if ($rx !== null && $rx < 0 && $rx >= -25) $rxColor = 'text-emerald-500 font-bold';
                                    elseif ($rx !== null && $rx < -25 && $rx >= -28) $rxColor = 'text-amber-500 font-bold';
                                    elseif ($rx !== null && $rx < -28) $rxColor = 'text-red-500 font-bold';
                                @endphp
                                <span class="{{ $rxColor }}">{{ $onu->rx_power_dbm !== null ? $onu->rx_power_dbm . ' dBm' : '-' }}</span>
                            </td>
                            <td class="px-5 py-4">
                                @if($onu->status === 'active' && $onu->is_online)
                                    <span class="px-2 py-1 bg-emerald-100 dark:bg-emerald-900/30 text-emerald-600 dark:text-emerald-400 text-xs font-semibold rounded-lg">Online</span>
                                @elseif($onu->status === 'active')
                                    <span class="px-2 py-1 bg-amber-100 dark:bg-amber-900/30 text-amber-700 dark:text-amber-400 text-xs font-semibold rounded-lg">Aktif</span>
                                @else
                                    <span class="px-2 py-1 bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400 text-xs font-semibold rounded-lg">Nonaktif</span>
                                @endif
                            </td>
                            <td class="px-5 py-4 text-right">
                                @if($onu->serial_number)
                                    <a href="{{ route('reseller-portal.network.acs-devices.index', ['search' => $onu->serial_number]) }}" class="inline-flex items-center gap-1 px-2.5 py-1 bg-slate-100 hover:bg-slate-200 dark:bg-slate-700 dark:hover:bg-slate-600 text-slate-700 dark:text-slate-200 rounded-lg text-xs font-medium transition-colors">
                                        <span class="material-symbols-outlined notranslate" style="font-size: 14px" translate="no">settings_remote</span>
                                        GenieACS
                                    </a>
                                @else
                                    <span class="text-xs text-slate-400">-</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-5 py-8 text-center text-slate-500">Tidak ada data ONU yang terhubung ke OLT ini di cakupan Anda.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($onus->hasPages())
            <div class="p-3 sm:p-5 border-t border-slate-100 dark:border-slate-700/50 flex justify-center sm:justify-start overflow-x-auto">
                {{ $onus->links() }}
            </div>
        @endif
    </div>
</div>
