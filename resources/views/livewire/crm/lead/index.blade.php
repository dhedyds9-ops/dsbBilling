@section('page_title')
    <div class="flex items-center gap-2">
        <div class="w-8 h-8 rounded-full bg-indigo-100 dark:bg-indigo-900/50 flex items-center justify-center text-indigo-600 dark:text-indigo-400 font-bold text-sm">
            <span class="material-symbols-outlined notranslate" translate="no" style="font-size:18px">person_add</span>
        </div>
        <span class="text-lg">Calon Pelanggan</span>
    </div>
@endsection

<div class="space-y-6 pb-10">
    {{-- KPI Cards --}}
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
        {{-- Total Leads --}}
        <div class="relative overflow-x-auto rounded-xl border border-indigo-200 dark:border-indigo-800/60 shadow-md bg-gradient-to-br from-indigo-50 to-white dark:from-indigo-950/50 dark:to-slate-800 group hover:shadow-lg transition-all">
            <div class="absolute top-0 left-0 right-0 h-1 bg-gradient-to-r from-indigo-500 to-blue-400 rounded-t-xl"></div>
            <div class="absolute top-3 right-3 opacity-10 text-indigo-400 group-hover:opacity-20 group-hover:scale-110 transition-all">
                <span class="material-symbols-outlined notranslate" translate="no" style="font-size:56px">groups</span>
            </div>
            <div class="p-4 pt-5">
                <h3 class="text-xs font-bold text-indigo-500 dark:text-indigo-400 uppercase tracking-widest mb-2">Total Leads</h3>
                <div class="text-2xl font-black text-indigo-700 dark:text-indigo-300 mb-1">{{ number_format($stats['total']) }}</div>
                <div class="text-xs text-slate-500 dark:text-slate-400">Semua prospek terdaftar</div>
            </div>
        </div>

        {{-- Lead Baru --}}
        <div class="relative overflow-x-auto rounded-xl border border-blue-200 dark:border-blue-800/60 shadow-md bg-gradient-to-br from-blue-50 to-white dark:from-blue-950/50 dark:to-slate-800 group hover:shadow-lg transition-all">
            <div class="absolute top-0 left-0 right-0 h-1 bg-gradient-to-r from-blue-500 to-cyan-400 rounded-t-xl"></div>
            <div class="absolute top-3 right-3 opacity-10 text-blue-400 group-hover:opacity-20 group-hover:scale-110 transition-all">
                <span class="material-symbols-outlined notranslate" translate="no" style="font-size:56px">fiber_new</span>
            </div>
            <div class="p-4 pt-5">
                <h3 class="text-xs font-bold text-blue-500 dark:text-blue-400 uppercase tracking-widest mb-2">Lead Baru</h3>
                <div class="text-2xl font-black text-blue-700 dark:text-blue-300 mb-1">{{ number_format($stats['new']) }}</div>
                <div class="text-xs text-slate-500 dark:text-slate-400">Belum dihubungi</div>
            </div>
        </div>

        {{-- Follow Up --}}
        <div class="relative overflow-x-auto rounded-xl border border-amber-200 dark:border-amber-800/60 shadow-md bg-gradient-to-br from-amber-50 to-white dark:from-amber-950/50 dark:to-slate-800 group hover:shadow-lg transition-all">
            <div class="absolute top-0 left-0 right-0 h-1 bg-gradient-to-r from-amber-500 to-orange-400 rounded-t-xl"></div>
            <div class="absolute top-3 right-3 opacity-10 text-amber-400 group-hover:opacity-20 group-hover:scale-110 transition-all">
                <span class="material-symbols-outlined notranslate" translate="no" style="font-size:56px">support_agent</span>
            </div>
            <div class="p-4 pt-5">
                <h3 class="text-xs font-bold text-amber-500 dark:text-amber-400 uppercase tracking-widest mb-2">Follow Up</h3>
                <div class="text-2xl font-black text-amber-700 dark:text-amber-300 mb-1">{{ number_format($stats['followup']) }}</div>
                <div class="text-xs text-slate-500 dark:text-slate-400">Sedang diproses / negosiasi</div>
            </div>
        </div>

        {{-- Konversi Berhasil --}}
        <div class="relative overflow-x-auto rounded-xl border border-emerald-200 dark:border-emerald-800/60 shadow-md bg-gradient-to-br from-emerald-50 to-white dark:from-emerald-950/50 dark:to-slate-800 group hover:shadow-lg transition-all">
            <div class="absolute top-0 left-0 right-0 h-1 bg-gradient-to-r from-emerald-500 to-teal-400 rounded-t-xl"></div>
            <div class="absolute top-3 right-3 opacity-10 text-emerald-400 group-hover:opacity-20 group-hover:scale-110 transition-all">
                <span class="material-symbols-outlined notranslate" translate="no" style="font-size:56px">verified</span>
            </div>
            <div class="p-4 pt-5">
                <h3 class="text-xs font-bold text-emerald-500 dark:text-emerald-400 uppercase tracking-widest mb-2">Dikonversi</h3>
                <div class="text-2xl font-black text-emerald-700 dark:text-emerald-300 mb-1">{{ number_format($stats['converted']) }}</div>
                <div class="text-xs text-slate-500 dark:text-slate-400">Menjadi prospek / pelanggan</div>
            </div>
        </div>
    </div>

    {{-- Toolbar --}}
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-white dark:bg-slate-800 p-4 rounded-xl shadow-sm border border-slate-200 dark:border-slate-700">
        <div class="flex flex-wrap items-center gap-3">
            {{-- Search --}}
            <div class="relative w-full sm:w-64">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <span class="material-symbols-outlined notranslate text-slate-400" translate="no" style="font-size:18px">search</span>
                </div>
                <input wire:model.live.debounce.300ms="search" type="text" 
                    class="block w-full pl-10 pr-3 py-2 border border-slate-200 dark:border-slate-700 rounded-lg text-sm bg-slate-50 dark:bg-slate-900/50 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 dark:text-slate-100 placeholder-slate-400 transition-colors dark:bg-slate-900 dark:text-slate-100" 
                    placeholder="Cari nama, telp, email...">
            </div>

            {{-- Filter Status --}}
            <select wire:model.live="filters.status" class="py-2 pl-3 pr-8 border border-slate-200 dark:border-slate-700 rounded-lg text-sm bg-slate-50 dark:bg-slate-900/50 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 dark:text-slate-100 dark:bg-slate-900 dark:text-slate-100">
                <option value="">Semua Status</option>
                <option value="new">Baru</option>
                <option value="contacted">Sudah Dihubungi</option>
                <option value="qualified">Qualified</option>
                <option value="converted">Dikonversi</option>
                <option value="lost">Hilang (Lost)</option>
            </select>
        </div>

        <div class="flex items-center gap-3">
            <select wire:model.live="perPage" class="py-2 pl-3 pr-8 border border-slate-200 dark:border-slate-700 rounded-lg text-sm bg-slate-50 dark:bg-slate-900/50 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 dark:text-slate-100 dark:bg-slate-900 dark:text-slate-100">
                <option value="20">20 / halaman</option>
                <option value="50">50 / halaman</option>
                <option value="100">100 / halaman</option>
                <option value="500">500 / halaman</option>
                <option value="all">Semua</option>
            </select>
            
            <button wire:click="export" class="inline-flex items-center gap-2 px-4 py-2 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-700 dark:text-slate-300 rounded-lg text-sm font-medium hover:bg-slate-50 dark:bg-slate-900/50 dark:hover:bg-slate-700 transition-colors">
                <span class="material-symbols-outlined notranslate" translate="no" style="font-size:18px">download</span>
                Export
            </button>

            <a href="{{ route('crm.leads.create') }}" class="inline-flex items-center gap-2 px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg text-sm font-medium shadow-sm transition-colors">
                <span class="material-symbols-outlined notranslate" translate="no" style="font-size:18px">add</span>
                Tambah Lead
            </a>
        </div>
    </div>

    {{-- Data Table --}}
    <div class="bg-white dark:bg-slate-800 rounded-xl shadow-sm border border-slate-200 dark:border-slate-700 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm text-left">
                <thead class="bg-slate-50 dark:bg-slate-900/50 text-slate-600 dark:text-slate-400 font-semibold border-b border-slate-200 dark:border-slate-700">
                    <tr>
                        <th class="px-6 py-4 whitespace-nowrap">Nama Lead</th>
                        <th class="px-6 py-4 whitespace-nowrap">Kontak</th>
                        <th class="px-6 py-4 whitespace-nowrap">Alamat / Sumber</th>
                        <th class="px-6 py-4 whitespace-nowrap">Status</th>
                        <th class="px-6 py-4 whitespace-nowrap">Tgl Dibuat</th>
                        <th class="px-6 py-4 whitespace-nowrap text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-700/50">
                    @forelse($leads as $lead)
                    <tr class="hover:bg-slate-50 dark:bg-slate-900/50 dark:hover:bg-slate-800/50 transition-colors group">
                        <td class="px-6 py-4">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-full {{ $lead->status === 'converted' ? 'bg-emerald-100 text-emerald-600 dark:bg-emerald-900/50 dark:text-emerald-400' : 'bg-indigo-100 text-indigo-600 dark:bg-indigo-900/50 dark:text-indigo-400' }} flex items-center justify-center font-bold flex-shrink-0">
                                    {{ substr($lead->name ?? 'L', 0, 1) }}
                                </div>
                                <div>
                                    <a href="{{ route('crm.leads.show', $lead->id) }}" class="font-bold text-slate-900 dark:text-slate-100 hover:text-indigo-600 dark:hover:text-indigo-400 transition-colors">
                                        {{ $lead->name }}
                                    </a>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4">
                            <div class="flex flex-col gap-1">
                                @if($lead->phone)
                                <div class="flex items-center gap-1.5 text-slate-600 dark:text-slate-400">
                                    <span class="material-symbols-outlined notranslate text-emerald-500" translate="no" style="font-size:16px">call</span>
                                    {{ $lead->phone }}
                                </div>
                                @endif
                                @if($lead->email)
                                <div class="flex items-center gap-1.5 text-slate-600 dark:text-slate-400">
                                    <span class="material-symbols-outlined notranslate text-sky-500" translate="no" style="font-size:16px">mail</span>
                                    {{ $lead->email }}
                                </div>
                                @endif
                            </div>
                        </td>
                        <td class="px-6 py-4">
                            <div class="text-slate-600 dark:text-slate-400 max-w-xs truncate" title="{{ $lead->address }}">
                                {{ $lead->address ?? '-' }}
                            </div>
                            <div class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">
                                Sumber: <span class="font-medium">{{ $lead->source ?? 'Manual' }}</span>
                            </div>
                        </td>
                        <td class="px-6 py-4">
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-xs font-semibold
                                @if($lead->status === 'new') bg-blue-100 text-blue-700 dark:bg-blue-900/50 dark:text-blue-400
                                @elseif($lead->status === 'contacted') bg-amber-100 text-amber-700 dark:bg-amber-900/50 dark:text-amber-400
                                @elseif($lead->status === 'qualified') bg-purple-100 text-purple-700 dark:bg-purple-900/50 dark:text-purple-400
                                @elseif($lead->status === 'converted') bg-emerald-100 text-emerald-700 dark:bg-emerald-900/50 dark:text-emerald-400
                                @else bg-red-100 text-red-700 dark:bg-red-900/50 dark:text-red-400
                                @endif
                            ">
                                <span class="w-1.5 h-1.5 rounded-full 
                                    @if($lead->status === 'new') bg-blue-500 animate-pulse
                                    @elseif($lead->status === 'contacted') bg-amber-500
                                    @elseif($lead->status === 'qualified') bg-purple-500
                                    @elseif($lead->status === 'converted') bg-emerald-500
                                    @else bg-red-500
                                    @endif
                                "></span>
                                {{ ucfirst($lead->status) }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-slate-600 dark:text-slate-400 whitespace-nowrap">
                            {{ $lead->created_at?->format('d/m/Y') ?? '-' }}
                        </td>
                        <td class="px-6 py-4 text-right">
                            <div class="flex items-center justify-end gap-2 opacity-0 group-hover:opacity-100 transition-opacity">
                                @if($lead->status !== 'converted' && $lead->status !== 'lost')
                                <button wire:click="convertToProspect({{ $lead->id }})" wire:confirm="Konversi lead ini menjadi Prospek untuk disurvei?" class="w-8 h-8 rounded-lg bg-emerald-50 dark:bg-emerald-900/30 text-emerald-600 dark:text-emerald-400 hover:bg-emerald-100 dark:bg-emerald-900/50 dark:hover:bg-emerald-900/50 flex items-center justify-center transition-colors tooltip" title="Konversi ke Prospek">
                                    <span class="material-symbols-outlined notranslate" translate="no" style="font-size:18px">trending_up</span>
                                </button>
                                @endif

                                <a href="{{ route('crm.leads.edit', $lead->id) }}" class="w-8 h-8 rounded-lg bg-slate-50 dark:bg-slate-900/50 text-slate-500 dark:text-slate-400 hover:text-indigo-600 hover:bg-indigo-50 dark:bg-indigo-900/30 dark:hover:bg-indigo-900/50 flex items-center justify-center transition-colors" title="Edit Lead">
                                    <span class="material-symbols-outlined notranslate" translate="no" style="font-size:18px">edit</span>
                                </a>

                                <button wire:click="delete({{ $lead->id }})" wire:confirm="Yakin ingin menghapus data lead ini secara permanen?" class="w-8 h-8 rounded-lg bg-slate-50 dark:bg-slate-900/50 text-slate-500 dark:text-slate-400 hover:text-red-600 hover:bg-red-50 dark:bg-red-900/30 dark:hover:bg-red-900/50 flex items-center justify-center transition-colors" title="Hapus Lead">
                                    <span class="material-symbols-outlined notranslate" translate="no" style="font-size:18px">delete</span>
                                </button>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-6 py-12 text-center text-slate-500 dark:text-slate-400">
                            <span class="material-symbols-outlined notranslate block mx-auto text-slate-300 dark:text-slate-600 dark:text-slate-400 mb-2" translate="no" style="font-size:48px">search_off</span>
                            <p class="font-medium text-slate-900 dark:text-slate-100">Tidak ada data lead ditemukan</p>
                            <p class="text-sm mt-1">Coba sesuaikan kata kunci pencarian atau filter status Anda.</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($leads->hasPages())
        <div class="px-6 py-4 border-t border-slate-200 dark:border-slate-700 bg-slate-50/50 dark:bg-slate-900/20">
            {{ $leads->links() }}
        </div>
        @endif
    </div>
</div>
