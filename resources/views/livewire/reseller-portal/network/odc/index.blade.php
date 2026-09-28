@section('page_title')
    <span class="material-symbols-outlined notranslate text-indigo-500" translate="no" style="font-size:24px">hub</span>
    <span class="text-lg">Data ODC (Read-Only)</span>
@endsection

<div class="space-y-5 pb-10">
    <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-sm border border-slate-200 dark:border-slate-700">
        <div class="p-5 flex flex-col md:flex-row md:items-center justify-between gap-4 border-b border-slate-100 dark:border-slate-700/50">
            <h2 class="font-bold text-slate-800 dark:text-slate-100 text-lg flex items-center gap-2">
                Daftar ODC di Wilayah Anda
            </h2>
            <div class="flex items-center gap-2 w-full md:w-auto">
                <input type="text" wire:model.live.debounce.500ms="search" placeholder="Cari ODC..." class="w-full md:w-64 px-4 py-2 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
            </div>
        </div>
        
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-50/50 dark:bg-slate-900/50 text-slate-500 dark:text-slate-400 text-xs uppercase tracking-wider">
                        <th class="px-5 py-4 font-semibold border-b border-slate-200 dark:border-slate-700">Kode / Nama</th>
                        <th class="px-5 py-4 font-semibold border-b border-slate-200 dark:border-slate-700">OLT</th>
                        <th class="px-5 py-4 font-semibold border-b border-slate-200 dark:border-slate-700">Port (Aktif/Total)</th>
                        <th class="px-5 py-4 font-semibold border-b border-slate-200 dark:border-slate-700">Status</th>
                    </tr>
                </thead>
                <tbody class="text-sm divide-y divide-slate-100 dark:divide-slate-700/50">
                    @forelse($odcs as $odc)
                        <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/50 transition-colors">
                            <td class="px-5 py-4 text-slate-800 dark:text-slate-200 font-medium">
                                {{ $odc->code }}<br>
                                <span class="text-xs text-slate-500">{{ $odc->name }}</span>
                            </td>
                            <td class="px-5 py-4 text-slate-600 dark:text-slate-400">{{ $odc->olt->name ?? '-' }}</td>
                            <td class="px-5 py-4 text-slate-600 dark:text-slate-400">{{ $odc->active_port_count }} / {{ $odc->port_count }}</td>
                            <td class="px-5 py-4">
                                @if($odc->status == 'active')
                                    <span class="px-2 py-1 bg-emerald-100 dark:bg-emerald-900/30 text-emerald-600 dark:text-emerald-400 text-xs font-semibold rounded-lg">Aktif</span>
                                @else
                                    <span class="px-2 py-1 bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400 text-xs font-semibold rounded-lg">{{ ucfirst($odc->status) }}</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-5 py-8 text-center text-slate-500">Tidak ada data ODC di wilayah ini.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        @if($odcs->hasPages())
            <div class="p-5 border-t border-slate-100 dark:border-slate-700/50">
                {{ $odcs->links() }}
            </div>
        @endif
    </div>
</div>
