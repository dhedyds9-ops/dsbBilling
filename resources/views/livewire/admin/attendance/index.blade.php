<div>
    <x-admin.page-header title="Rekap Absensi">
        <x-slot name="description">
            Pantau kehadiran, lokasi, dan jam kerja Teknisi serta Staf NOC secara real-time.
        </x-slot>
    </x-admin.page-header>

    <div class="max-w-6xl mx-auto space-y-6">
        
        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-5 shadow-sm flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div class="flex items-center gap-4 w-full sm:w-auto">
                <div class="flex-1 sm:flex-none">
                    <label class="block text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-1">Tanggal</label>
                    <input type="date" wire:model.live="dateFilter" class="w-full px-4 py-2 border border-slate-200 dark:border-slate-700 rounded-xl text-sm focus:ring-2 focus:ring-indigo-500 bg-slate-50 dark:bg-slate-800 dark:text-slate-100 dark:bg-slate-900 dark:text-slate-100">
                </div>
            </div>
            <div class="w-full sm:w-72 relative">
                <span class="material-symbols-outlined notranslate absolute left-3 top-1/2 -translate-y-1/2 text-slate-400" translate="no" style="font-size:20px">search</span>
                <input type="text" wire:model.live.debounce.300ms="search" class="w-full pl-10 pr-4 py-2 border border-slate-200 dark:border-slate-700 rounded-xl text-sm focus:ring-2 focus:ring-indigo-500 bg-slate-50 dark:bg-slate-800 dark:text-slate-100 dark:bg-slate-900 dark:text-slate-100" placeholder="Cari nama teknisi...">
            </div>
        </div>

        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm whitespace-nowrap">
                    <thead class="bg-slate-50 dark:bg-slate-900/50 text-slate-600 dark:text-slate-400 font-semibold border-b border-slate-200 dark:border-slate-800">
                        <tr>
                            <th class="px-6 py-4">Pegawai</th>
                            <th class="px-6 py-4">Status</th>
                            <th class="px-6 py-4 text-center">Check-In</th>
                            <th class="px-6 py-4 text-center">Check-Out</th>
                            <th class="px-6 py-4">Lokasi GPS</th>
                            <th class="px-6 py-4">Catatan</th>
                            <th class="px-6 py-4">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                        @forelse($attendances as $att)
                            <tr class="hover:bg-slate-50 dark:bg-slate-900/50 dark:hover:bg-slate-800/50">
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-3">
                                        <x-display.avatar :name="$att->technician->name ?? 'Unknown'" size="sm" />
                                        <div>
                                            <p class="font-bold text-slate-900 dark:text-slate-100">{{ $att->technician->name ?? 'Unknown' }}</p>
                                            <p class="text-[10px] font-black uppercase text-indigo-500 tracking-wider">{{ $att->technician->job_function ?? 'STAF' }}</p>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    @if($att->status->value === 'checked_in')
                                        <span class="px-3 py-1 bg-blue-50 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400 rounded-lg text-xs font-bold uppercase">Bekerja</span>
                                    @elseif($att->status->value === 'checked_out')
                                        <span class="px-3 py-1 bg-emerald-50 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400 rounded-lg text-xs font-bold uppercase">Selesai</span>
                                    @elseif($att->status->value === 'late')
                                        <span class="px-3 py-1 bg-amber-50 text-amber-700 dark:bg-amber-900/30 dark:text-amber-400 rounded-lg text-xs font-bold uppercase">Terlambat</span>
                                    @elseif($att->status->value === 'sick')
                                        <span class="px-3 py-1 bg-red-50 text-red-700 dark:bg-red-900/30 dark:text-red-400 rounded-lg text-xs font-bold uppercase">Sakit</span>
                                    @elseif($att->status->value === 'leave')
                                        <span class="px-3 py-1 bg-purple-50 text-purple-700 dark:bg-purple-900/30 dark:text-purple-400 rounded-lg text-xs font-bold uppercase">Cuti/Izin</span>
                                    @else
                                        <span class="px-3 py-1 bg-slate-100 text-slate-600 dark:bg-slate-800 dark:text-slate-400 rounded-lg text-xs font-bold uppercase">{{ $att->status->value }}</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <div class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-slate-100 dark:bg-slate-800 rounded-lg text-slate-700 dark:text-slate-300 font-mono font-bold">
                                        <span class="material-symbols-outlined notranslate text-emerald-500" translate="no" style="font-size:16px">login</span>
                                        {{ $att->checked_in_at ? $att->checked_in_at->format('H:i') : '-' }}
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-center">
                                    @if($att->checked_out_at)
                                        <div class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-slate-100 dark:bg-slate-800 rounded-lg text-slate-700 dark:text-slate-300 font-mono font-bold">
                                            <span class="material-symbols-outlined notranslate text-red-500" translate="no" style="font-size:16px">logout</span>
                                            {{ $att->checked_out_at->format('H:i') }}
                                        </div>
                                    @else
                                        <span class="text-slate-400 italic">-</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4">
                                    @if($att->check_in_latitude)
                                        <a href="https://maps.google.com/?q={{ $att->check_in_latitude }},{{ $att->check_in_longitude }}" target="_blank" class="inline-flex items-center gap-1 text-xs text-indigo-600 hover:text-indigo-700 font-bold bg-indigo-50 dark:bg-indigo-900/30 px-2 py-1 rounded">
                                            <span class="material-symbols-outlined notranslate" translate="no" style="font-size:14px">map</span> Map
                                        </a>
                                    @else
                                        <span class="text-slate-400 italic text-xs">No GPS</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4">
                                    <span class="text-xs text-slate-600 dark:text-slate-400 truncate max-w-[150px] inline-block" title="{{ $att->notes }}">
                                        {{ $att->notes ?: '-' }}
                                    </span>
                                </td>
                                <td class="px-6 py-4">
                                    <div x-data="{ open: false }" class="relative">
                                        <button @click="open = !open" @click.away="open = false" class="p-1.5 text-slate-400 hover:text-indigo-600 rounded-lg hover:bg-slate-100 dark:bg-slate-800 transition-colors">
                                            <span class="material-symbols-outlined notranslate" style="font-size:18px">more_vert</span>
                                        </button>
                                        <div x-show="open" x-cloak class="absolute right-0 mt-1 w-36 bg-white dark:bg-slate-800 rounded-xl shadow-lg border border-slate-100 dark:border-slate-700 z-50 overflow-hidden text-xs">
                                            <button wire:click="updateStatus('{{ $att->id }}', 'sick')" @click="open = false" class="w-full text-left px-4 py-2 hover:bg-slate-50 dark:bg-slate-900/50 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300">Set Sakit</button>
                                            <button wire:click="updateStatus('{{ $att->id }}', 'leave')" @click="open = false" class="w-full text-left px-4 py-2 hover:bg-slate-50 dark:bg-slate-900/50 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300">Set Izin/Cuti</button>
                                            <button wire:click="updateStatus('{{ $att->id }}', 'absent')" @click="open = false" class="w-full text-left px-4 py-2 hover:bg-slate-50 dark:bg-slate-900/50 dark:hover:bg-slate-700 text-red-600 dark:text-red-400">Set Alpa (Absent)</button>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-6 py-12 text-center text-slate-500 dark:text-slate-400">
                                    <span class="material-symbols-outlined notranslate block mx-auto text-slate-300 mb-2" translate="no" style="font-size:48px">group_off</span>
                                    <p class="font-bold text-slate-900 dark:text-slate-100">Tidak ada data absensi</p>
                                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">Belum ada teknisi yang absen pada tanggal ini.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

