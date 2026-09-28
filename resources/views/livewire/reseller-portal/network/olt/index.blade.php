@section('page_title')
    <span class="material-symbols-outlined notranslate text-indigo-500" translate="no" style="font-size:24px">dns</span>
    <span class="text-lg">Data OLT (Read-Only)</span>
@endsection

<div class="space-y-5 pb-10">
    <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-sm border border-slate-200 dark:border-slate-700">
        <div class="p-5 flex flex-col md:flex-row md:items-center justify-between gap-4 border-b border-slate-100 dark:border-slate-700/50">
            <h2 class="font-bold text-slate-800 dark:text-slate-100 text-lg flex items-center gap-2">
                Daftar OLT di Wilayah Anda
            </h2>
            <div class="flex items-center gap-2 w-full md:w-auto">
                <input type="text" wire:model.live.debounce.500ms="search" placeholder="Cari OLT..." class="w-full md:w-64 px-4 py-2 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
            </div>
        </div>
        
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-50/50 dark:bg-slate-900/50 text-slate-500 dark:text-slate-400 text-xs uppercase tracking-wider">
                        <th class="px-5 py-4 font-semibold border-b border-slate-200 dark:border-slate-700">Nama OLT</th>
                        <th class="px-5 py-4 font-semibold border-b border-slate-200 dark:border-slate-700">IP Address</th>
                        <th class="px-5 py-4 font-semibold border-b border-slate-200 dark:border-slate-700">Model</th>
                        <th class="px-5 py-4 font-semibold border-b border-slate-200 dark:border-slate-700 text-center">ONU (Aktif/Total)</th>
                        <th class="px-5 py-4 font-semibold border-b border-slate-200 dark:border-slate-700">Status</th>
                        <th class="px-5 py-4 font-semibold border-b border-slate-200 dark:border-slate-700">Aksi</th>
                    </tr>
                </thead>
                <tbody class="text-sm divide-y divide-slate-100 dark:divide-slate-700/50">
                    @forelse($olts as $olt)
                        <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/50 transition-colors">
                            <td class="px-5 py-4 text-slate-800 dark:text-slate-200 font-medium">{{ $olt->name }}</td>
                            <td class="px-5 py-4 text-slate-600 dark:text-slate-400">{{ $olt->ip_address }}</td>
                            <td class="px-5 py-4 text-slate-600 dark:text-slate-400">{{ $olt->model ?? '-' }}</td>
                            <td class="px-5 py-4 text-center text-slate-600 dark:text-slate-400">
                                @php
                                    $aktif = $olt->onu_active_count ?? 0;
                                    $kapasitas = $olt->onu_capacity ?? ($olt->pon_port_count * 64) ?? 0;
                                    $percent = $kapasitas > 0 ? ($aktif / $kapasitas) * 100 : 0;
                                    $color = $percent > 80 ? 'text-red-500' : 'text-emerald-500';
                                @endphp
                                <span class="{{ $color }} font-bold">{{ $aktif }}</span> / {{ $kapasitas }}
                            </td>
                            <td class="px-5 py-4">
                                @if($olt->status == 'active')
                                    <span class="px-2 py-1 bg-emerald-100 dark:bg-emerald-900/30 text-emerald-600 dark:text-emerald-400 text-xs font-semibold rounded-lg">Aktif</span>
                                @else
                                    <span class="px-2 py-1 bg-red-100 dark:bg-red-900/30 text-red-600 dark:text-red-400 text-xs font-semibold rounded-lg">Nonaktif</span>
                                @endif
                            </td>
                            <td class="px-5 py-4">
                                <a href="{{ route('reseller-portal.network.olts.show', $olt->id) }}" class="inline-flex items-center gap-1 px-3 py-1.5 bg-indigo-50 hover:bg-indigo-100 text-indigo-600 rounded-lg text-xs font-medium transition-colors">
                                    <span class="material-symbols-outlined notranslate" style="font-size: 16px" translate="no">visibility</span>
                                    Lihat ONU
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-5 py-8 text-center text-slate-500">Tidak ada data OLT di wilayah ini.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        @if($olts->hasPages())
            <div class="p-5 border-t border-slate-100 dark:border-slate-700/50">
                {{ $olts->links() }}
            </div>
        @endif
    </div>
</div>
