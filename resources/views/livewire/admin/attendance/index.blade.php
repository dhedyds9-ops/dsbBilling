<div class="space-y-6 pb-12">
    {{-- Header --}}
    <div class="flex flex-col sm:flex-row justify-between sm:items-center gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-900 dark:text-slate-100 flex items-center gap-2">
                <span class="material-symbols-outlined text-indigo-600 dark:text-indigo-400" style="font-size: 28px;">schedule</span>
                Rekap Absensi
            </h1>
            <p class="text-sm text-slate-500 dark:text-slate-400 mt-0.5">Pantau kehadiran, lokasi GPS, dan jam kerja teknisi serta staf secara real-time.</p>
        </div>
    </div>

    {{-- Kartu Ringkasan Statistik --}}
    <div class="grid grid-cols-2 lg:grid-cols-5 gap-4">
        <div class="bg-white dark:bg-slate-900 p-4 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-xs flex items-center gap-3">
            <div class="w-11 h-11 rounded-xl bg-indigo-50 dark:bg-indigo-900/40 text-indigo-600 dark:text-indigo-400 flex items-center justify-center shrink-0">
                <span class="material-symbols-outlined notranslate" translate="no">how_to_reg</span>
            </div>
            <div>
                <p class="text-xs text-slate-500 dark:text-slate-400 font-medium">Total Hadir</p>
                <p class="text-xl font-bold text-slate-900 dark:text-slate-100">{{ number_format($stats['total'] ?? 0) }}</p>
            </div>
        </div>

        <div class="bg-white dark:bg-slate-900 p-4 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-xs flex items-center gap-3">
            <div class="w-11 h-11 rounded-xl bg-blue-50 dark:bg-blue-900/40 text-blue-600 dark:text-blue-400 flex items-center justify-center shrink-0">
                <span class="material-symbols-outlined notranslate" translate="no">engineering</span>
            </div>
            <div>
                <p class="text-xs text-slate-500 dark:text-slate-400 font-medium">Sedang Bekerja</p>
                <p class="text-xl font-bold text-blue-600 dark:text-blue-400">{{ number_format($stats['working'] ?? 0) }}</p>
            </div>
        </div>

        <div class="bg-white dark:bg-slate-900 p-4 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-xs flex items-center gap-3">
            <div class="w-11 h-11 rounded-xl bg-emerald-50 dark:bg-emerald-900/40 text-emerald-600 dark:text-emerald-400 flex items-center justify-center shrink-0">
                <span class="material-symbols-outlined notranslate" translate="no">task_alt</span>
            </div>
            <div>
                <p class="text-xs text-slate-500 dark:text-slate-400 font-medium">Sudah Check-Out</p>
                <p class="text-xl font-bold text-emerald-600 dark:text-emerald-400">{{ number_format($stats['done'] ?? 0) }}</p>
            </div>
        </div>

        <div class="bg-white dark:bg-slate-900 p-4 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-xs flex items-center gap-3">
            <div class="w-11 h-11 rounded-xl bg-amber-50 dark:bg-amber-900/40 text-amber-600 dark:text-amber-400 flex items-center justify-center shrink-0">
                <span class="material-symbols-outlined notranslate" translate="no">warning</span>
            </div>
            <div>
                <p class="text-xs text-slate-500 dark:text-slate-400 font-medium">Terlambat</p>
                <p class="text-xl font-bold text-amber-600 dark:text-amber-400">{{ number_format($stats['late'] ?? 0) }}</p>
            </div>
        </div>

        <div class="bg-white dark:bg-slate-900 p-4 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-xs flex items-center gap-3 col-span-2 lg:col-span-1">
            <div class="w-11 h-11 rounded-xl bg-purple-50 dark:bg-purple-900/40 text-purple-600 dark:text-purple-400 flex items-center justify-center shrink-0">
                <span class="material-symbols-outlined notranslate" translate="no">sick</span>
            </div>
            <div>
                <p class="text-xs text-slate-500 dark:text-slate-400 font-medium">Sakit / Izin</p>
                <p class="text-xl font-bold text-purple-600 dark:text-purple-400">{{ number_format($stats['sick_leave'] ?? 0) }}</p>
            </div>
        </div>
    </div>

    {{-- Filter Bar --}}
    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-4 shadow-xs space-y-4">
        {{-- Row 1: Mode Tabs --}}
        <div class="flex items-center justify-between gap-3">
            {{-- Filter Mode Tabs --}}
            <div class="flex items-center bg-slate-100 dark:bg-slate-800 rounded-xl p-1 gap-1 flex-wrap">
                <button wire:click="$set('filterMode','today')"
                    class="px-3.5 py-1.5 rounded-lg text-xs font-semibold transition-all {{ $filterMode === 'today' ? 'bg-indigo-600 text-white shadow-sm' : 'text-slate-600 dark:text-slate-300 hover:bg-white dark:hover:bg-slate-700' }}">
                    <span class="material-symbols-outlined notranslate align-middle" style="font-size:14px" translate="no">today</span>
                    Hari Ini
                </button>
                <button wire:click="$set('filterMode','month')"
                    class="px-3.5 py-1.5 rounded-lg text-xs font-semibold transition-all {{ $filterMode === 'month' ? 'bg-indigo-600 text-white shadow-sm' : 'text-slate-600 dark:text-slate-300 hover:bg-white dark:hover:bg-slate-700' }}">
                    <span class="material-symbols-outlined notranslate align-middle" style="font-size:14px" translate="no">calendar_month</span>
                    Bulan Ini
                </button>
                <button wire:click="$set('filterMode','period')"
                    class="px-3.5 py-1.5 rounded-lg text-xs font-semibold transition-all {{ $filterMode === 'period' ? 'bg-indigo-600 text-white shadow-sm' : 'text-slate-600 dark:text-slate-300 hover:bg-white dark:hover:bg-slate-700' }}">
                    <span class="material-symbols-outlined notranslate align-middle" style="font-size:14px" translate="no">date_range</span>
                    Pilih Periode
                </button>
                <button wire:click="$set('filterMode','all')"
                    class="px-3.5 py-1.5 rounded-lg text-xs font-semibold transition-all {{ $filterMode === 'all' ? 'bg-indigo-600 text-white shadow-sm' : 'text-slate-600 dark:text-slate-300 hover:bg-white dark:hover:bg-slate-700' }}">
                    <span class="material-symbols-outlined notranslate align-middle" style="font-size:14px" translate="no">list</span>
                    Semua
                </button>
            </div>
        </div>

        {{-- Row 2: Date Range (period mode) + Karyawan + Status Kehadiran + Cari Pegawai + Reset --}}
        <div class="flex flex-col sm:flex-row items-start sm:items-end gap-3 flex-wrap">
            {{-- Period Date Range - tampil hanya saat mode 'period' --}}
            @if($filterMode === 'period')
            <div class="flex items-end gap-2">
                <div>
                    <label class="block text-[11px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-1">Dari Tanggal</label>
                    <input type="date" wire:model.live="periodStart"
                           class="px-3 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-sm text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-indigo-500 font-mono">
                </div>
                <span class="text-slate-400 text-sm mb-2.5 px-1">s/d</span>
                <div>
                    <label class="block text-[11px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-1">Sampai Tanggal</label>
                    <input type="date" wire:model.live="periodEnd"
                           class="px-3 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-sm text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-indigo-500 font-mono">
                </div>
            </div>
            @endif

            {{-- Filter Karyawan --}}
            <div class="w-full sm:w-auto">
                <label class="block text-[11px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-1">Karyawan</label>
                <select wire:model.live="employeeFilter"
                        class="w-full sm:w-auto px-3 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-sm text-slate-700 dark:text-slate-200 focus:outline-none focus:ring-2 focus:ring-indigo-500 min-w-[180px]">
                    <option value="">Semua Karyawan</option>
                    @foreach($employees as $emp)
                        <option value="{{ $emp->user_id }}">{{ $emp->name }}</option>
                    @endforeach
                </select>
            </div>

            {{-- Filter Status Kehadiran --}}
            <div class="w-full sm:w-auto">
                <label class="block text-[11px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-1">Status Kehadiran</label>
                <select wire:model.live="filterStatus"
                        class="w-full sm:w-auto px-3 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-sm text-slate-700 dark:text-slate-200 focus:outline-none focus:ring-2 focus:ring-indigo-500 min-w-[160px]">
                    <option value="">Semua Status</option>
                    <option value="checked_in">Bekerja (Checked In)</option>
                    <option value="checked_out">Selesai (Checked Out)</option>
                    <option value="late">Terlambat</option>
                    <option value="sick">Sakit</option>
                    <option value="permission">Izin</option>
                    <option value="absent">Alpa / Tidak Hadir</option>
                </select>
            </div>

            {{-- Cari Nama / Email Pegawai (Sejajar dengan Status Kehadiran) --}}
            <div class="w-full sm:w-64">
                <label class="block text-[11px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-1">Cari Teknisi / Staf</label>
                <div class="relative">
                    <span class="material-symbols-outlined notranslate absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 pointer-events-none" translate="no" style="font-size:18px">search</span>
                    <input type="text" wire:model.live.debounce.300ms="search" placeholder="Cari nama..."
                           class="w-full pl-9 pr-3 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-sm text-slate-900 dark:text-slate-100 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                </div>
            </div>

            {{-- Tombol Reset --}}
            @if($search || $filterStatus || $employeeFilter || $filterMode !== 'today')
            <div class="self-end pb-0.5">
                <button wire:click="$set('filterMode','today'); $set('search',''); $set('filterStatus',''); $set('employeeFilter','')"
                        class="px-3.5 py-2 text-xs font-semibold text-slate-600 dark:text-slate-300 bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 rounded-xl transition-colors flex items-center gap-1 whitespace-nowrap">
                    <span class="material-symbols-outlined notranslate" translate="no" style="font-size:16px">restart_alt</span>
                    Reset Filter
                </button>
            </div>
            @endif
        </div>
    </div>

    {{-- Tabel Absensi --}}
    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl shadow-xs overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm whitespace-nowrap">
                <thead class="bg-slate-50 dark:bg-slate-800/60 text-slate-600 dark:text-slate-400 font-semibold border-b border-slate-200 dark:border-slate-800">
                    <tr>
                        <th class="px-4 py-3.5 text-center w-28">Aksi</th>
                        <th class="px-4 py-3.5">Pegawai / Teknisi</th>
                        <th class="px-4 py-3.5 text-center">Status</th>
                        <th class="px-4 py-3.5 text-center">Check-In</th>
                        <th class="px-4 py-3.5 text-center">Check-Out</th>
                        <th class="px-4 py-3.5">Lokasi GPS</th>
                        <th class="px-4 py-3.5">Catatan</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                    @forelse($attendances as $att)
                        @php
                            $tech = $att->technician;
                            $emp = $tech?->employee;
                        @endphp
                        <tr class="hover:bg-slate-50/80 dark:hover:bg-slate-800/40 transition-colors">
                            {{-- Aksi di sisi KIRI --}}
                            <td class="px-4 py-3.5 text-center whitespace-nowrap">
                                <div x-data="{ open: false }" class="relative inline-block text-left">
                                    <button @click="open = !open" @click.away="open = false"
                                            class="inline-flex items-center gap-1 px-2.5 py-1.5 rounded-lg bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 text-xs font-semibold transition-colors cursor-pointer"
                                            title="Ubah Status">
                                        Status
                                        <span class="material-symbols-outlined notranslate" style="font-size:16px" translate="no">expand_more</span>
                                    </button>
                                    <div x-show="open" x-cloak
                                         class="absolute left-0 mt-1 w-40 bg-white dark:bg-slate-800 rounded-xl shadow-lg border border-slate-100 dark:border-slate-700 z-50 overflow-hidden text-xs py-1">
                                        <button wire:click="updateStatus('{{ $att->id }}', 'checked_in')" @click="open = false"
                                                class="w-full text-left px-3 py-2 hover:bg-blue-50 dark:hover:bg-blue-900/30 text-blue-700 dark:text-blue-300 flex items-center gap-2">
                                            <span class="material-symbols-outlined text-blue-500" style="font-size:16px">login</span> Set Bekerja
                                        </button>
                                        <button wire:click="updateStatus('{{ $att->id }}', 'checked_out')" @click="open = false"
                                                class="w-full text-left px-3 py-2 hover:bg-emerald-50 dark:hover:bg-emerald-900/30 text-emerald-700 dark:text-emerald-300 flex items-center gap-2">
                                            <span class="material-symbols-outlined text-emerald-500" style="font-size:16px">logout</span> Set Selesai
                                        </button>
                                        <button wire:click="updateStatus('{{ $att->id }}', 'late')" @click="open = false"
                                                class="w-full text-left px-3 py-2 hover:bg-amber-50 dark:hover:bg-amber-900/30 text-amber-700 dark:text-amber-300 flex items-center gap-2">
                                            <span class="material-symbols-outlined text-amber-500" style="font-size:16px">warning</span> Set Terlambat
                                        </button>
                                        <button wire:click="updateStatus('{{ $att->id }}', 'sick')" @click="open = false"
                                                class="w-full text-left px-3 py-2 hover:bg-red-50 dark:hover:bg-red-900/30 text-red-700 dark:text-red-300 flex items-center gap-2">
                                            <span class="material-symbols-outlined text-red-500" style="font-size:16px">sick</span> Set Sakit
                                        </button>
                                        <button wire:click="updateStatus('{{ $att->id }}', 'permission')" @click="open = false"
                                                class="w-full text-left px-3 py-2 hover:bg-purple-50 dark:hover:bg-purple-900/30 text-purple-700 dark:text-purple-300 flex items-center gap-2">
                                            <span class="material-symbols-outlined text-purple-500" style="font-size:16px">event_busy</span> Set Izin / Cuti
                                        </button>
                                    </div>
                                </div>
                            </td>

                            {{-- Pegawai / Teknisi --}}
                            <td class="px-4 py-3.5">
                                <div class="flex items-center gap-3">
                                    <div class="w-9 h-9 rounded-full bg-indigo-100 dark:bg-indigo-900/50 text-indigo-600 dark:text-indigo-300 font-bold flex items-center justify-center text-xs shrink-0">
                                        {{ strtoupper(substr($tech?->name ?? 'U', 0, 1)) }}
                                    </div>
                                    <div>
                                        <p class="font-semibold text-slate-900 dark:text-slate-100">{{ $tech?->name ?? 'Unknown' }}</p>
                                        <div class="flex items-center gap-1.5 mt-0.5">
                                            @if($emp)
                                                <a href="{{ route('admin.employee.edit', $emp->id) }}" class="font-mono text-[11px] text-indigo-600 dark:text-indigo-400 hover:underline font-semibold" title="Lihat Profil Pegawai">
                                                    {{ $emp->nik }}
                                                </a>
                                                <span class="text-slate-300 dark:text-slate-600">•</span>
                                            @endif
                                            <span class="text-[10px] font-bold uppercase text-slate-500 dark:text-slate-400">
                                                @php
                                                    $jf = $tech?->job_function;
                                                    $jfLabel = $jf instanceof \App\Enums\JobFunction
                                                        ? $jf->label()
                                                        : (is_string($jf) && $jf
                                                            ? (\App\Enums\JobFunction::tryFrom($jf)?->label() ?? $jf)
                                                            : ($emp?->department ?? 'STAF'));
                                                @endphp
                                                {{ $jfLabel }}
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </td>

                            {{-- Status --}}
                            <td class="px-4 py-3.5 text-center">
                                @if($att->status->value === 'checked_in')
                                    <span class="inline-flex items-center px-2.5 py-1 bg-blue-50 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400 rounded-full text-xs font-bold uppercase">
                                        Bekerja
                                    </span>
                                @elseif($att->status->value === 'checked_out')
                                    <span class="inline-flex items-center px-2.5 py-1 bg-emerald-50 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400 rounded-full text-xs font-bold uppercase">
                                        Selesai
                                    </span>
                                @elseif($att->status->value === 'late')
                                    <span class="inline-flex items-center px-2.5 py-1 bg-amber-50 text-amber-700 dark:bg-amber-900/30 dark:text-amber-400 rounded-full text-xs font-bold uppercase">
                                        Terlambat
                                    </span>
                                @elseif($att->status->value === 'sick')
                                    <span class="inline-flex items-center px-2.5 py-1 bg-red-50 text-red-700 dark:bg-red-900/30 dark:text-red-400 rounded-full text-xs font-bold uppercase">
                                        Sakit
                                    </span>
                                @elseif($att->status->value === 'permission' || $att->status->value === 'leave')
                                    <span class="inline-flex items-center px-2.5 py-1 bg-purple-50 text-purple-700 dark:bg-purple-900/30 dark:text-purple-400 rounded-full text-xs font-bold uppercase">
                                        Izin / Cuti
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2.5 py-1 bg-slate-100 text-slate-600 dark:bg-slate-800 dark:text-slate-400 rounded-full text-xs font-bold uppercase">
                                        {{ $att->status->value }}
                                    </span>
                                @endif
                            </td>

                            {{-- Check-In --}}
                            <td class="px-4 py-3.5 text-center">
                                @if($att->checked_in_at)
                                    <div class="inline-flex items-center gap-1 px-2.5 py-1 bg-slate-100 dark:bg-slate-800 rounded-lg text-slate-700 dark:text-slate-300 font-mono text-xs font-bold">
                                        <span class="material-symbols-outlined notranslate text-emerald-500" translate="no" style="font-size:15px">login</span>
                                        {{ $att->checked_in_at->format('H:i') }}
                                    </div>
                                @else
                                    <span class="text-slate-400 italic text-xs">-</span>
                                @endif
                            </td>

                            {{-- Check-Out --}}
                            <td class="px-4 py-3.5 text-center">
                                @if($att->checked_out_at)
                                    <div class="inline-flex items-center gap-1 px-2.5 py-1 bg-slate-100 dark:bg-slate-800 rounded-lg text-slate-700 dark:text-slate-300 font-mono text-xs font-bold">
                                        <span class="material-symbols-outlined notranslate text-rose-500" translate="no" style="font-size:15px">logout</span>
                                        {{ $att->checked_out_at->format('H:i') }}
                                    </div>
                                @else
                                    <span class="text-slate-400 italic text-xs">-</span>
                                @endif
                            </td>

                            {{-- Lokasi GPS --}}
                            <td class="px-4 py-3.5">
                                @if($att->check_in_latitude && $att->check_in_longitude)
                                    <a href="https://maps.google.com/?q={{ $att->check_in_latitude }},{{ $att->check_in_longitude }}" target="_blank"
                                       class="inline-flex items-center gap-1 text-xs text-indigo-600 dark:text-indigo-400 hover:text-indigo-700 font-semibold bg-indigo-50 dark:bg-indigo-900/30 px-2.5 py-1 rounded-lg transition-colors"
                                       title="Buka Google Maps">
                                        <span class="material-symbols-outlined notranslate" translate="no" style="font-size:15px">location_on</span>
                                        Buka Maps
                                    </a>
                                @else
                                    <span class="text-slate-400 italic text-xs">Tanpa GPS</span>
                                @endif
                            </td>

                            {{-- Catatan --}}
                            <td class="px-4 py-3.5 text-xs text-slate-600 dark:text-slate-400">
                                {{ $att->notes ?: '-' }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-6 py-12 text-center text-slate-500 dark:text-slate-400">
                                <div class="flex flex-col items-center justify-center">
                                    <span class="material-symbols-outlined notranslate text-slate-300 dark:text-slate-600 mb-2" style="font-size: 48px;" translate="no">event_busy</span>
                                    <p class="text-base font-semibold text-slate-800 dark:text-slate-200">Tidak Ada Data Absensi</p>
                                    <p class="text-xs text-slate-400 mt-1">Belum ada data absensi yang sesuai dengan filter yang dipilih.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($attendances->hasPages())
            <div class="px-4 py-3 border-t border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-800/40">
                {{ $attendances->links() }}
            </div>
        @endif
    </div>
</div>
