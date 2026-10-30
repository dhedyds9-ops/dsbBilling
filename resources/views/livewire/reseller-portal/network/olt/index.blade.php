@section('page_title')
    <span class="material-symbols-outlined notranslate text-indigo-500" translate="no" style="font-size:24px">dns</span>
    <span class="text-lg">Data OLT (Read-Only)</span>
@endsection

<div class="space-y-5 pb-10">
    @if(isset($territory) && !$territory['has_territory'])
        <div class="p-4 bg-amber-50 dark:bg-amber-950/30 border border-amber-200 dark:border-amber-800/60 rounded-2xl text-amber-800 dark:text-amber-300 text-sm flex items-start gap-3">
            <span class="material-symbols-outlined notranslate text-amber-500 shrink-0 mt-0.5" translate="no">info</span>
            <div>
                <p class="font-semibold">Cakupan Wilayah Belum Diatur pada Akun Anda</p>
                <p class="text-xs opacity-90 mt-0.5">Saat ini hanya OLT yang melayani pelanggan Anda secara langsung yang ditampilkan. Hubungi Administrator untuk mengatur <strong>Cabang Kantor</strong> atau <strong>Wilayah Operasional</strong> pada profil akun Reseller Anda agar seluruh OLT di wilayah Anda otomatis terbaca.</p>
            </div>
        </div>
    @endif

    <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-sm border border-slate-200 dark:border-slate-700">
        <div class="p-3 sm:p-5 flex flex-col lg:flex-row lg:items-center justify-between gap-3 border-b border-slate-100 dark:border-slate-700/50">
            <div class="flex flex-col sm:flex-row sm:items-center gap-2.5">
                <h2 class="font-bold text-slate-800 dark:text-slate-100 text-lg flex items-center gap-2">
                    Daftar OLT di Wilayah Anda
                </h2>
                @if(!empty($territory['label']))
                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-xs font-medium {{ ($territory['has_territory'] ?? false) ? 'bg-indigo-50 text-indigo-700 dark:bg-indigo-900/30 dark:text-indigo-300' : 'bg-slate-100 text-slate-500 dark:bg-slate-700 dark:text-slate-400' }}">
                        <span class="material-symbols-outlined notranslate" style="font-size: 14px" translate="no">location_on</span>
                        {{ $territory['label'] }}
                    </span>
                @endif
            </div>
            <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-2.5 w-full lg:w-auto">
                <div class="relative w-full sm:w-64">
                    <span class="material-symbols-outlined notranslate absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-[18px] pointer-events-none" translate="no">search</span>
                    <input type="text" wire:model.live.debounce.500ms="search" placeholder="Cari Nama, Kode, IP, Lokasi..." class="w-full pl-9 pr-4 py-2 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl text-sm text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                </div>
                <div class="flex items-center gap-2">
                    <a href="{{ route('reseller-portal.network.onus.index') }}" class="flex-1 sm:flex-none justify-center px-3.5 py-2 border border-slate-200 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 rounded-xl text-xs font-semibold transition-colors inline-flex items-center gap-1.5 whitespace-nowrap">
                        <span class="material-symbols-outlined notranslate" style="font-size: 16px" translate="no">router</span>
                        Data ONU
                    </a>
                    <a href="{{ route('reseller-portal.network.acs-devices.index') }}" class="flex-1 sm:flex-none justify-center px-3.5 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-xs font-semibold transition-colors inline-flex items-center gap-1.5 whitespace-nowrap">
                        <span class="material-symbols-outlined notranslate" style="font-size: 16px" translate="no">settings_remote</span>
                        GenieACS
                    </a>
                </div>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-50/50 dark:bg-slate-900/50 text-slate-500 dark:text-slate-400 text-xs uppercase tracking-wider">
                        <th class="px-5 py-4 font-semibold border-b border-slate-200 dark:border-slate-700">Nama & Kode OLT</th>
                        <th class="px-5 py-4 font-semibold border-b border-slate-200 dark:border-slate-700">Cabang / POP</th>
                        <th class="px-5 py-4 font-semibold border-b border-slate-200 dark:border-slate-700">IP Address & Model</th>
                        <th class="px-5 py-4 font-semibold border-b border-slate-200 dark:border-slate-700 text-center">ONU (Aktif/Kapasitas)</th>
                        <th class="px-5 py-4 font-semibold border-b border-slate-200 dark:border-slate-700">Status</th>
                        <th class="px-5 py-4 font-semibold border-b border-slate-200 dark:border-slate-700 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="text-sm divide-y divide-slate-100 dark:divide-slate-700/50">
                    @forelse($olts as $olt)
                        <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/50 transition-colors">
                            <td class="px-5 py-4">
                                <div class="text-slate-800 dark:text-slate-200 font-semibold">{{ $olt->name }}</div>
                                <div class="text-xs text-slate-500 font-mono">{{ $olt->code ?: '-' }}</div>
                            </td>
                            <td class="px-5 py-4">
                                <div class="text-slate-700 dark:text-slate-300 font-medium text-xs">{{ $olt->branch?->name ?? 'Global / Belum Dikaitkan' }}</div>
                                <div class="text-[11px] text-slate-400">{{ $olt->pop?->name ?? ($olt->address ?: '-') }}</div>
                            </td>
                            <td class="px-5 py-4">
                                <div class="font-mono text-xs text-slate-700 dark:text-slate-300">{{ $olt->ip_address ?: ($olt->host ?: '-') }}</div>
                                <div class="text-xs text-slate-400">{{ $olt->model ?: '-' }}</div>
                            </td>
                            <td class="px-5 py-4 text-center text-slate-600 dark:text-slate-400">
                                @php
                                    $aktif = $olt->onu_active_count ?? 0;
                                    $kapasitas = $olt->onu_capacity ?: (($olt->pon_port_count ?? 0) * 64);
                                    $percent = $kapasitas > 0 ? ($aktif / $kapasitas) * 100 : 0;
                                    $color = $percent > 80 ? 'text-red-500' : 'text-emerald-500';
                                @endphp
                                <span class="{{ $color }} font-bold">{{ $aktif }}</span> / {{ $kapasitas ?: '-' }}
                            </td>
                            <td class="px-5 py-4">
                                @if($olt->status === 'active')
                                    <span class="px-2.5 py-1 bg-emerald-100 dark:bg-emerald-900/30 text-emerald-600 dark:text-emerald-400 text-xs font-semibold rounded-lg">Aktif</span>
                                @else
                                    <span class="px-2.5 py-1 bg-red-100 dark:bg-red-900/30 text-red-600 dark:text-red-400 text-xs font-semibold rounded-lg">{{ ucfirst($olt->status ?: 'Nonaktif') }}</span>
                                @endif
                            </td>
                            <td class="px-5 py-4 text-right">
                                <a href="{{ route('reseller-portal.network.olts.show', $olt->id) }}" class="inline-flex items-center gap-1 px-3 py-1.5 bg-indigo-50 hover:bg-indigo-100 dark:bg-indigo-900/30 dark:hover:bg-indigo-900/50 text-indigo-600 dark:text-indigo-300 rounded-lg text-xs font-medium transition-colors">
                                    <span class="material-symbols-outlined notranslate" style="font-size: 16px" translate="no">visibility</span>
                                    Lihat ONU
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-5 py-10 text-center text-slate-500">
                                <span class="material-symbols-outlined notranslate text-4xl mb-2 text-slate-300 block" translate="no">dns</span>
                                <p class="font-medium text-slate-600 dark:text-slate-300">Belum ada perangkat OLT yang terbaca untuk wilayah Anda.</p>
                                <p class="text-xs text-slate-400 mt-1">Pastikan Cabang Kantor pada profil Reseller Anda sama dengan Cabang Kantor pada perangkat OLT, atau kata kunci Wilayah cocok dengan lokasi OLT/POP.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($olts->hasPages())
            <div class="p-3 sm:p-5 border-t border-slate-100 dark:border-slate-700/50 flex justify-center sm:justify-start overflow-x-auto">
                {{ $olts->links() }}
            </div>
        @endif
    </div>
</div>
