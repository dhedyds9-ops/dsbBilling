<div class="space-y-6">
    {{-- Header & Filters --}}
    <div class="bg-white dark:bg-slate-800 rounded-3xl p-6 border border-slate-200 dark:border-slate-700 shadow-sm space-y-4">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <h1 class="text-xl sm:text-2xl font-black text-slate-900 dark:text-white flex items-center gap-2">
                    <span class="material-symbols-outlined notranslate text-indigo-600 dark:text-indigo-400 text-3xl" translate="no">timer</span>
                    Laporan Kinerja SLA Operasional
                </h1>
                <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">
                    Evaluasi kepatuhan Service Level Agreement (SLA) untuk Tiket, Work Order, dan Instalasi Lapangan.
                </p>
            </div>
            <div class="flex items-center gap-2">
                <a href="{{ route('support.work-orders.history') }}" 
                   class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl text-xs font-bold bg-slate-100 hover:bg-slate-200 text-slate-700 dark:bg-slate-700 dark:hover:bg-slate-600 dark:text-slate-200 transition-all">
                    <span class="material-symbols-outlined notranslate text-sm" translate="no">assignment</span>
                    Riwayat Work Order
                </a>
            </div>
        </div>

        {{-- Filters Form --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3 pt-3 border-t border-slate-100 dark:border-slate-700/60">
            <div>
                <label class="block text-[11px] font-bold text-slate-500 uppercase tracking-wider mb-1">Mulai Tanggal</label>
                <input type="date" wire:model.live="startDate" 
                       class="w-full px-3 py-2 text-xs rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50/50 dark:bg-slate-900 text-slate-800 dark:text-slate-200 focus:ring-2 focus:ring-indigo-500">
            </div>
            <div>
                <label class="block text-[11px] font-bold text-slate-500 uppercase tracking-wider mb-1">Sampai Tanggal</label>
                <input type="date" wire:model.live="endDate" 
                       class="w-full px-3 py-2 text-xs rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50/50 dark:bg-slate-900 text-slate-800 dark:text-slate-200 focus:ring-2 focus:ring-indigo-500">
            </div>
            <div>
                <label class="block text-[11px] font-bold text-slate-500 uppercase tracking-wider mb-1">Cabang</label>
                <select wire:model.live="branchFilter" 
                        class="w-full px-3 py-2 text-xs rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50/50 dark:bg-slate-900 text-slate-800 dark:text-slate-200 focus:ring-2 focus:ring-indigo-500">
                    <option value="">Semua Cabang</option>
                    @foreach($branches as $b)
                        <option value="{{ $b->id }}">{{ $b->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-[11px] font-bold text-slate-500 uppercase tracking-wider mb-1">Reseller</label>
                <select wire:model.live="resellerFilter" 
                        class="w-full px-3 py-2 text-xs rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50/50 dark:bg-slate-900 text-slate-800 dark:text-slate-200 focus:ring-2 focus:ring-indigo-500">
                    <option value="">Semua (Direct & Reseller)</option>
                    <option value="direct">Direct (Non-Reseller)</option>
                    @foreach($resellers as $r)
                        <option value="{{ $r->id }}">{{ $r->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-[11px] font-bold text-slate-500 uppercase tracking-wider mb-1">Prioritas</label>
                <select wire:model.live="priorityFilter" 
                        class="w-full px-3 py-2 text-xs rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50/50 dark:bg-slate-900 text-slate-800 dark:text-slate-200 focus:ring-2 focus:ring-indigo-500">
                    <option value="">Semua Prioritas</option>
                    <option value="urgent">Urgent / Critical (2 jam)</option>
                    <option value="high">High (8 jam)</option>
                    <option value="medium">Medium / Normal (24 jam)</option>
                    <option value="low">Low (48 jam)</option>
                </select>
            </div>
        </div>
    </div>

    {{-- KPI Cards --}}
    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-4">
        {{-- Total --}}
        <div class="bg-white dark:bg-slate-800 rounded-3xl p-5 border border-slate-200 dark:border-slate-700 shadow-sm">
            <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400 block mb-1">Total Pekerjaan</span>
            <div class="text-2xl font-black text-slate-900 dark:text-white">{{ $report['total_jobs'] }}</div>
            <span class="text-[10px] text-slate-400 mt-1 block">Unik operasional</span>
        </div>

        {{-- Met SLA --}}
        <div class="bg-white dark:bg-slate-800 rounded-3xl p-5 border border-emerald-200 dark:border-emerald-800/40 shadow-sm bg-emerald-50/20">
            <span class="text-[11px] font-bold uppercase tracking-wider text-emerald-600 dark:text-emerald-400 block mb-1">Tepat Waktu (Met)</span>
            <div class="text-2xl font-black text-emerald-600 dark:text-emerald-400">{{ $report['met_sla'] }}</div>
            <span class="text-[10px] text-emerald-600/70 mt-1 block">Memenuhi target</span>
        </div>

        {{-- Warning --}}
        <div class="bg-white dark:bg-slate-800 rounded-3xl p-5 border border-amber-200 dark:border-amber-800/40 shadow-sm bg-amber-50/20">
            <span class="text-[11px] font-bold uppercase tracking-wider text-amber-600 dark:text-amber-400 block mb-1">Warning (&ge;80%)</span>
            <div class="text-2xl font-black text-amber-600 dark:text-amber-400">{{ $report['warning_jobs'] }}</div>
            <span class="text-[10px] text-amber-600/70 mt-1 block">Mendekati deadline</span>
        </div>

        {{-- Breached --}}
        <div class="bg-white dark:bg-slate-800 rounded-3xl p-5 border border-rose-200 dark:border-rose-800/40 shadow-sm bg-rose-50/20">
            <span class="text-[11px] font-bold uppercase tracking-wider text-rose-600 dark:text-rose-400 block mb-1">Breached (Terlambat)</span>
            <div class="text-2xl font-black text-rose-600 dark:text-rose-400">{{ $report['breached_jobs'] }}</div>
            <span class="text-[10px] text-rose-600/70 mt-1 block">Melewati SLA</span>
        </div>

        {{-- Compliance Rate --}}
        <div class="bg-white dark:bg-slate-800 rounded-3xl p-5 border border-indigo-200 dark:border-indigo-800/40 shadow-sm bg-indigo-50/20">
            <span class="text-[11px] font-bold uppercase tracking-wider text-indigo-600 dark:text-indigo-400 block mb-1">Kepatuhan SLA</span>
            <div class="text-2xl font-black text-indigo-600 dark:text-indigo-400">{{ $report['compliance_rate'] }}%</div>
            <span class="text-[10px] text-indigo-600/70 mt-1 block">Target kepatuhan &ge; 95%</span>
        </div>

        {{-- Avg Resolution Time --}}
        <div class="bg-white dark:bg-slate-800 rounded-3xl p-5 border border-slate-200 dark:border-slate-700 shadow-sm">
            <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400 block mb-1">Rata-rata Selesai</span>
            <div class="text-2xl font-black text-slate-900 dark:text-white">{{ $report['avg_resolution_hours'] }}j</div>
            <span class="text-[10px] text-slate-400 mt-1 block">Keterlambatan: {{ $report['avg_overdue_hours'] }}j</span>
        </div>
    </div>

    {{-- Breakdown Grids --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        {{-- Performa per Prioritas --}}
        <div class="bg-white dark:bg-slate-800 rounded-3xl border border-slate-200 dark:border-slate-700 p-6 shadow-sm">
            <h3 class="font-bold text-sm text-slate-900 dark:text-white uppercase tracking-wider mb-4 flex items-center gap-2">
                <span class="material-symbols-outlined text-indigo-600 text-lg" translate="no">flag</span>
                Kepatuhan Berdasarkan Prioritas
            </h3>
            <div class="overflow-x-auto">
                <table class="w-full text-xs">
                    <thead>
                        <tr class="border-b border-slate-100 dark:border-slate-700 text-slate-400 text-left">
                            <th class="pb-2 font-bold uppercase">Prioritas</th>
                            <th class="pb-2 font-bold uppercase text-center">Total</th>
                            <th class="pb-2 font-bold uppercase text-center">Met</th>
                            <th class="pb-2 font-bold uppercase text-center">Breached</th>
                            <th class="pb-2 font-bold uppercase text-right">% Kepatuhan</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-700/60 font-semibold">
                        @forelse($report['by_priority'] as $prio => $data)
                            @php
                                $rate = $data['total'] > 0 ? round(($data['met'] / $data['total']) * 100, 1) : 100;
                            @endphp
                            <tr>
                                <td class="py-2.5 text-slate-800 dark:text-slate-200 uppercase">{{ $prio }}</td>
                                <td class="py-2.5 text-center text-slate-700 dark:text-slate-300">{{ $data['total'] }}</td>
                                <td class="py-2.5 text-center text-emerald-600">{{ $data['met'] }}</td>
                                <td class="py-2.5 text-center text-rose-600">{{ $data['breached'] }}</td>
                                <td class="py-2.5 text-right font-black {{ $rate >= 90 ? 'text-emerald-600' : ($rate >= 80 ? 'text-amber-600' : 'text-rose-600') }}">
                                    {{ $rate }}%
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="py-4 text-center text-slate-400">Belum ada data untuk periode ini</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Performa per Cabang --}}
        <div class="bg-white dark:bg-slate-800 rounded-3xl border border-slate-200 dark:border-slate-700 p-6 shadow-sm">
            <h3 class="font-bold text-sm text-slate-900 dark:text-white uppercase tracking-wider mb-4 flex items-center gap-2">
                <span class="material-symbols-outlined text-indigo-600 text-lg" translate="no">apartment</span>
                Kepatuhan Berdasarkan Cabang
            </h3>
            <div class="overflow-x-auto">
                <table class="w-full text-xs">
                    <thead>
                        <tr class="border-b border-slate-100 dark:border-slate-700 text-slate-400 text-left">
                            <th class="pb-2 font-bold uppercase">Cabang</th>
                            <th class="pb-2 font-bold uppercase text-center">Total</th>
                            <th class="pb-2 font-bold uppercase text-center">Met</th>
                            <th class="pb-2 font-bold uppercase text-center">Breached</th>
                            <th class="pb-2 font-bold uppercase text-right">% Kepatuhan</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-700/60 font-semibold">
                        @forelse($report['by_branch'] as $branchName => $data)
                            @php
                                $rate = $data['total'] > 0 ? round(($data['met'] / $data['total']) * 100, 1) : 100;
                            @endphp
                            <tr>
                                <td class="py-2.5 text-slate-800 dark:text-slate-200">{{ $branchName }}</td>
                                <td class="py-2.5 text-center text-slate-700 dark:text-slate-300">{{ $data['total'] }}</td>
                                <td class="py-2.5 text-center text-emerald-600">{{ $data['met'] }}</td>
                                <td class="py-2.5 text-center text-rose-600">{{ $data['breached'] }}</td>
                                <td class="py-2.5 text-right font-black {{ $rate >= 90 ? 'text-emerald-600' : ($rate >= 80 ? 'text-amber-600' : 'text-rose-600') }}">
                                    {{ $rate }}%
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="py-4 text-center text-slate-400">Belum ada data untuk periode ini</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Performa per Teknisi --}}
        <div class="bg-white dark:bg-slate-800 rounded-3xl border border-slate-200 dark:border-slate-700 p-6 shadow-sm">
            <h3 class="font-bold text-sm text-slate-900 dark:text-white uppercase tracking-wider mb-4 flex items-center gap-2">
                <span class="material-symbols-outlined text-indigo-600 text-lg" translate="no">engineering</span>
                Kepatuhan Berdasarkan Teknisi Lapangan
            </h3>
            <div class="overflow-x-auto max-h-72">
                <table class="w-full text-xs">
                    <thead>
                        <tr class="border-b border-slate-100 dark:border-slate-700 text-slate-400 text-left">
                            <th class="pb-2 font-bold uppercase">Nama Teknisi</th>
                            <th class="pb-2 font-bold uppercase text-center">Total</th>
                            <th class="pb-2 font-bold uppercase text-center">Met</th>
                            <th class="pb-2 font-bold uppercase text-center">Breached</th>
                            <th class="pb-2 font-bold uppercase text-right">% Kepatuhan</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-700/60 font-semibold">
                        @forelse($report['by_technician'] as $techName => $data)
                            @php
                                $rate = $data['total'] > 0 ? round(($data['met'] / $data['total']) * 100, 1) : 100;
                            @endphp
                            <tr>
                                <td class="py-2.5 text-slate-800 dark:text-slate-200">{{ $techName }}</td>
                                <td class="py-2.5 text-center text-slate-700 dark:text-slate-300">{{ $data['total'] }}</td>
                                <td class="py-2.5 text-center text-emerald-600">{{ $data['met'] }}</td>
                                <td class="py-2.5 text-center text-rose-600">{{ $data['breached'] }}</td>
                                <td class="py-2.5 text-right font-black {{ $rate >= 90 ? 'text-emerald-600' : ($rate >= 80 ? 'text-amber-600' : 'text-rose-600') }}">
                                    {{ $rate }}%
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="py-4 text-center text-slate-400">Belum ada data untuk periode ini</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Performa per Reseller --}}
        <div class="bg-white dark:bg-slate-800 rounded-3xl border border-slate-200 dark:border-slate-700 p-6 shadow-sm">
            <h3 class="font-bold text-sm text-slate-900 dark:text-white uppercase tracking-wider mb-4 flex items-center gap-2">
                <span class="material-symbols-outlined text-indigo-600 text-lg" translate="no">storefront</span>
                Kepatuhan Berdasarkan Kemitraan Reseller
            </h3>
            <div class="overflow-x-auto max-h-72">
                <table class="w-full text-xs">
                    <thead>
                        <tr class="border-b border-slate-100 dark:border-slate-700 text-slate-400 text-left">
                            <th class="pb-2 font-bold uppercase">Reseller</th>
                            <th class="pb-2 font-bold uppercase text-center">Total</th>
                            <th class="pb-2 font-bold uppercase text-center">Met</th>
                            <th class="pb-2 font-bold uppercase text-center">Breached</th>
                            <th class="pb-2 font-bold uppercase text-right">% Kepatuhan</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-700/60 font-semibold">
                        @forelse($report['by_reseller'] as $resellerName => $data)
                            @php
                                $rate = $data['total'] > 0 ? round(($data['met'] / $data['total']) * 100, 1) : 100;
                            @endphp
                            <tr>
                                <td class="py-2.5 text-slate-800 dark:text-slate-200">{{ $resellerName }}</td>
                                <td class="py-2.5 text-center text-slate-700 dark:text-slate-300">{{ $data['total'] }}</td>
                                <td class="py-2.5 text-center text-emerald-600">{{ $data['met'] }}</td>
                                <td class="py-2.5 text-center text-rose-600">{{ $data['breached'] }}</td>
                                <td class="py-2.5 text-right font-black {{ $rate >= 90 ? 'text-emerald-600' : ($rate >= 80 ? 'text-amber-600' : 'text-rose-600') }}">
                                    {{ $rate }}%
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="py-4 text-center text-slate-400">Belum ada data untuk periode ini</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
