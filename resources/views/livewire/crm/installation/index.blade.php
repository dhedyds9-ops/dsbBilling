<div class="space-y-6">
    {{-- Header --}}
    <div class="flex flex-col md:flex-row items-start md:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold tracking-tight text-slate-900 dark:text-slate-100">Manajemen Instalasi (CRM)</h1>
            <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Kelola jadwal pemasangan, penugasan teknisi lapangan, dan pencatatan perangkat</p>
        </div>
        <div class="flex items-center gap-2.5 overflow-x-auto pb-1 sm:pb-0 w-full sm:w-auto">
            <button wire:click="export" class="inline-flex items-center justify-center px-4 py-2.5 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-700 dark:text-slate-300 rounded-xl text-sm font-semibold hover:bg-slate-50 dark:hover:bg-slate-700/50 transition-all shadow-xs gap-2 whitespace-nowrap">
                <span class="material-symbols-outlined notranslate text-lg" translate="no">download</span>
                Ekspor Data
            </button>
            <a href="{{ route('crm.installations.create') }}" class="inline-flex items-center justify-center px-4 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-sm font-semibold transition-all shadow-sm hover:shadow gap-2 whitespace-nowrap">
                <span class="material-symbols-outlined notranslate text-lg" translate="no">add_circle</span>
                Tambah Instalasi
            </a>
        </div>
    </div>

    {{-- Search & Filters Card --}}
    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-4 shadow-xs space-y-3">
        <div class="flex flex-col sm:flex-row gap-3 items-stretch sm:items-center justify-between">
            <div class="relative w-full sm:flex-1 sm:max-w-md">
                <span class="material-symbols-outlined notranslate absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 pointer-events-none" translate="no" style="font-size:20px">search</span>
                <input type="text" wire:model.live.debounce.300ms="search" placeholder="Cari nama pelanggan, SN perangkat, teknisi..." 
                       class="w-full pl-10 pr-4 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-sm text-slate-900 dark:text-slate-100 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-indigo-500">
            </div>
            <div class="flex items-center gap-2 w-full sm:w-auto">
                <button wire:click="$toggle('showFilters')" class="flex-1 sm:flex-none inline-flex items-center justify-center px-4 py-2 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-700 dark:text-slate-300 rounded-xl text-sm font-semibold hover:bg-slate-50 dark:hover:bg-slate-700/50 transition-colors whitespace-nowrap gap-1.5">
                    <span class="material-symbols-outlined notranslate text-lg text-slate-500" translate="no">tune</span>
                    Filter
                </button>
                @if($search || !empty($filters['status']))
                    <button wire:click="resetFilters" class="flex-1 sm:flex-none inline-flex items-center justify-center px-3.5 py-2 text-xs font-semibold text-slate-600 hover:text-slate-900 dark:text-slate-300 dark:hover:text-slate-100 bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 rounded-xl transition-colors whitespace-nowrap gap-1">
                        <span class="material-symbols-outlined notranslate text-sm" translate="no">restart_alt</span>
                        Reset
                    </button>
                @endif
            </div>
        </div>

        @if($showFilters)
            <div class="pt-3 border-t border-slate-100 dark:border-slate-800 grid grid-cols-1 sm:grid-cols-3 gap-3">
                <div>
                    <label class="block text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-1">Status Pengerjaan</label>
                    <select wire:model.live="filters.status" class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-sm text-slate-700 dark:text-slate-200 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        <option value="">Semua Status</option>
                        <option value="scheduled">Dijadwalkan (Scheduled)</option>
                        <option value="in_progress">Sedang Berlangsung (In Progress)</option>
                        <option value="completed">Selesai (Completed)</option>
                        <option value="canceled">Dibatalkan (Canceled)</option>
                    </select>
                </div>
            </div>
        @endif
    </div>

    {{-- Data Table (Aksi di sebelah KIRI) --}}
    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl shadow-xs overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm border-collapse">
                <thead>
                    <tr class="bg-slate-50/80 dark:bg-slate-800/50 border-b border-slate-200 dark:border-slate-800">
                        {{-- Aksi Column on LEFT --}}
                        <th class="px-4 py-3.5 text-center text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider whitespace-nowrap w-28">
                            Aksi
                        </th>
                        <th class="px-4 py-3.5 text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider whitespace-nowrap">
                            ID / Kode
                        </th>
                        <th class="px-4 py-3.5 text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider whitespace-nowrap">
                            Pelanggan
                        </th>
                        <th class="px-4 py-3.5 text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider whitespace-nowrap">
                            Jadwal & Waktu
                        </th>
                        <th class="px-4 py-3.5 text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider whitespace-nowrap">
                            Status
                        </th>
                        <th class="px-4 py-3.5 text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider whitespace-nowrap">
                            Prioritas
                        </th>
                        <th class="px-4 py-3.5 text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider whitespace-nowrap">
                            Teknisi
                        </th>
                        <th class="px-4 py-3.5 text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider whitespace-nowrap">
                            Perangkat (ONU / Router)
                        </th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800/80">
                    @forelse($installations as $installation)
                        <tr class="hover:bg-slate-50/70 dark:hover:bg-slate-800/40 transition-colors">
                            {{-- Action buttons on LEFT --}}
                            <td class="px-4 py-3.5 text-center whitespace-nowrap">
                                <div class="flex items-center justify-center gap-1.5">
                                    <a href="{{ route('crm.installations.show', $installation->id) }}" 
                                       title="Lihat Rincian"
                                       class="p-1.5 text-slate-500 hover:text-indigo-600 dark:text-slate-400 dark:hover:text-indigo-400 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors">
                                        <span class="material-symbols-outlined notranslate text-[18px]" translate="no">visibility</span>
                                    </a>
                                    <a href="{{ route('crm.installations.edit', $installation->id) }}" 
                                       title="Edit Instalasi"
                                       class="p-1.5 text-slate-500 hover:text-blue-600 dark:text-slate-400 dark:hover:text-blue-400 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors">
                                        <span class="material-symbols-outlined notranslate text-[18px]" translate="no">edit</span>
                                    </a>
                                    <button wire:click="delete({{ $installation->id }})" 
                                            wire:confirm="Yakin ingin menghapus data instalasi ini?" 
                                            title="Hapus"
                                            class="p-1.5 text-slate-500 hover:text-rose-600 dark:text-slate-400 dark:hover:text-rose-400 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors">
                                        <span class="material-symbols-outlined notranslate text-[18px]" translate="no">delete</span>
                                    </button>
                                </div>
                            </td>

                            {{-- ID / Kode --}}
                            <td class="px-4 py-3.5 whitespace-nowrap font-mono text-xs text-slate-500 dark:text-slate-400">
                                #{{ $installation->id }}
                            </td>

                            {{-- Pelanggan --}}
                            <td class="px-4 py-3.5 whitespace-nowrap">
                                <div class="font-semibold text-slate-900 dark:text-slate-100">
                                    {{ $installation->customer_display_name }}
                                </div>
                                @if($installation->customer?->code)
                                    <div class="text-[11px] font-mono text-slate-400">{{ $installation->customer->code }}</div>
                                @elseif($installation->survey_id)
                                    <div class="text-[11px] text-indigo-500 dark:text-indigo-400">Via Survey #{{ $installation->survey_id }}</div>
                                @endif
                            </td>

                            {{-- Jadwal & Waktu --}}
                            <td class="px-4 py-3.5 whitespace-nowrap text-xs text-slate-600 dark:text-slate-300">
                                <div class="font-medium text-slate-800 dark:text-slate-200">
                                    {{ $installation->scheduled_date ? $installation->scheduled_date->translatedFormat('d M Y') : ($installation->scheduled_at ? $installation->scheduled_at->translatedFormat('d M Y') : '-') }}
                                </div>
                                <div class="text-[11px] text-slate-400 font-mono">
                                    {{ $installation->scheduled_time ?? ($installation->scheduled_at ? $installation->scheduled_at->format('H:i') : '-') }} WIB
                                </div>
                            </td>

                            {{-- Status --}}
                            <td class="px-4 py-3.5 whitespace-nowrap">
                                @php
                                    $st = $installation->status;
                                    $badge = match($st) {
                                        'completed'   => 'bg-emerald-50 text-emerald-700 dark:bg-emerald-950/40 dark:text-emerald-400 border-emerald-200 dark:border-emerald-800',
                                        'in_progress' => 'bg-amber-50 text-amber-700 dark:bg-amber-950/40 dark:text-amber-400 border-amber-200 dark:border-amber-800',
                                        'canceled'    => 'bg-rose-50 text-rose-700 dark:bg-rose-950/40 dark:text-rose-400 border-rose-200 dark:border-rose-800',
                                        default       => 'bg-blue-50 text-blue-700 dark:bg-blue-950/40 dark:text-blue-400 border-blue-200 dark:border-blue-800',
                                    };
                                    $label = match($st) {
                                        'completed'   => 'Selesai',
                                        'in_progress' => 'Berlangsung',
                                        'canceled'    => 'Dibatalkan',
                                        default       => 'Dijadwalkan',
                                    };
                                @endphp
                                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold border {{ $badge }}">
                                    {{ $label }}
                                </span>
                            </td>

                            {{-- Prioritas --}}
                            <td class="px-4 py-3.5 whitespace-nowrap">
                                @php
                                    $pr = $installation->priority ?? 'medium';
                                    $prBadge = match($pr) {
                                        'critical' => 'text-rose-600 dark:text-rose-400 font-bold',
                                        'high'     => 'text-amber-600 dark:text-amber-400 font-semibold',
                                        'low'      => 'text-slate-400',
                                        default    => 'text-slate-700 dark:text-slate-300',
                                    };
                                @endphp
                                <span class="text-xs capitalize {{ $prBadge }}">
                                    {{ $pr }}
                                </span>
                            </td>

                            {{-- Teknisi --}}
                            <td class="px-4 py-3.5 whitespace-nowrap text-xs">
                                <div class="font-medium text-slate-800 dark:text-slate-200">
                                    {{ $installation->technician_display_name }}
                                </div>
                            </td>

                            {{-- Perangkat --}}
                            <td class="px-4 py-3.5 whitespace-nowrap text-xs font-mono text-slate-600 dark:text-slate-400">
                                @if($installation->onu_serial_number || $installation->router_model)
                                    <div><span class="text-slate-400">ONU:</span> {{ $installation->onu_serial_number ?: '-' }}</div>
                                    @if($installation->router_model)
                                        <div class="text-[11px] text-slate-400"><span class="text-slate-400">RT:</span> {{ $installation->router_model }}</div>
                                    @endif
                                @else
                                    <span class="text-slate-400 italic font-sans text-xs">Belum diinput</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-6 py-12 text-center text-slate-400 dark:text-slate-500">
                                <span class="material-symbols-outlined notranslate text-4xl mb-2 text-slate-300 dark:text-slate-600" translate="no">engineering</span>
                                <p class="text-sm font-medium">Belum ada data instalasi yang tercatat</p>
                                <p class="text-xs text-slate-400 mt-1">Gunakan tombol "Tambah Instalasi" untuk membuat jadwal baru</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($installations->hasPages())
            <div class="px-6 py-4 border-t border-slate-100 dark:border-slate-800">
                {{ $installations->links() }}
            </div>
        @endif
    </div>
</div>
