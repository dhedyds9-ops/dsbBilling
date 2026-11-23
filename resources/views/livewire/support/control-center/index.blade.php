<div class="space-y-6" wire:poll.25s="refreshData">
    <!-- Header & Action Bar -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 bg-white dark:bg-gray-800 p-5 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700">
        <div>
            <div class="flex items-center gap-2.5">
                <div class="p-2.5 bg-blue-50 dark:bg-blue-900/40 text-blue-600 dark:text-blue-400 rounded-xl">
                    <span class="material-symbols-outlined text-2xl">monitoring</span>
                </div>
                <div>
                    <h1 class="text-xl font-bold text-gray-900 dark:text-white">Operational Control Center</h1>
                    <p class="text-xs text-gray-500 dark:text-gray-400">Pusat pemantauan real-time pekerjaan lapangan, lokasi teknisi, SLA countdown, dan antrean Quality Control.</p>
                </div>
            </div>
        </div>

        <!-- Filter Controls -->
        <div class="flex flex-wrap items-center gap-2.5">
            <!-- Branch Filter -->
            @if(auth()->user()->isSuperAdmin() || empty(auth()->user()->branch_id))
            <select wire:model.live="branchFilter" class="text-xs rounded-xl border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900 text-gray-800 dark:text-gray-200 focus:ring-blue-500 py-2 px-3">
                <option value="">Semua Cabang</option>
                @foreach($branches as $b)
                    <option value="{{ $b->id }}">{{ $b->name }}</option>
                @endforeach
            </select>
            @endif

            <!-- Date Range Filter -->
            <div class="inline-flex rounded-xl p-1 bg-gray-100 dark:bg-gray-900 text-xs font-medium">
                <button wire:click="setDateRange('today')" class="px-3 py-1.5 rounded-lg transition-colors {{ $dateRange === 'today' ? 'bg-white dark:bg-gray-800 text-blue-600 dark:text-blue-400 shadow-sm font-semibold' : 'text-gray-600 dark:text-gray-400 hover:text-gray-900' }}">
                    Hari Ini
                </button>
                <button wire:click="setDateRange('this_week')" class="px-3 py-1.5 rounded-lg transition-colors {{ $dateRange === 'this_week' ? 'bg-white dark:bg-gray-800 text-blue-600 dark:text-blue-400 shadow-sm font-semibold' : 'text-gray-600 dark:text-gray-400 hover:text-gray-900' }}">
                    Minggu Ini
                </button>
                <button wire:click="setDateRange('this_month')" class="px-3 py-1.5 rounded-lg transition-colors {{ $dateRange === 'this_month' ? 'bg-white dark:bg-gray-800 text-blue-600 dark:text-blue-400 shadow-sm font-semibold' : 'text-gray-600 dark:text-gray-400 hover:text-gray-900' }}">
                    Bulan Ini
                </button>
            </div>

            <!-- Toggle Map Button -->
            <button wire:click="toggleMap" class="inline-flex items-center gap-1.5 px-3 py-2 text-xs font-medium rounded-xl border border-gray-200 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-700 text-gray-700 dark:text-gray-300 transition-colors">
                <span class="material-symbols-outlined text-sm">{{ $showMap ? 'map' : 'explore' }}</span>
                <span>{{ $showMap ? 'Sembunyikan Peta' : 'Tampilkan Peta' }}</span>
            </button>

            <!-- Refresh Button -->
            <button wire:click="$refresh" title="Segarkan Data Real-Time" class="p-2 text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-xl transition-colors">
                <span class="material-symbols-outlined text-lg">refresh</span>
            </button>
        </div>
    </div>

    <!-- KPI Metric Cards Grid -->
    <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-3.5">
        <!-- 1. Total Pekerjaan Aktif -->
        <div class="bg-white dark:bg-gray-800 p-4 rounded-2xl border border-gray-100 dark:border-gray-700 shadow-sm">
            <div class="flex items-center justify-between text-gray-500 dark:text-gray-400 mb-2">
                <span class="text-xs font-medium">Pekerjaan Aktif</span>
                <div class="p-1.5 bg-blue-50 dark:bg-blue-900/30 text-blue-600 dark:text-blue-400 rounded-lg">
                    <span class="material-symbols-outlined text-base">assignment</span>
                </div>
            </div>
            <div class="text-2xl font-bold text-gray-900 dark:text-white">{{ $totalActiveJobs }}</div>
            <p class="text-[11px] text-gray-400 mt-1">Work Order, Tiket & PSB</p>
        </div>

        <!-- 2. Teknisi Bertugas -->
        <div class="bg-white dark:bg-gray-800 p-4 rounded-2xl border border-gray-100 dark:border-gray-700 shadow-sm">
            <div class="flex items-center justify-between text-gray-500 dark:text-gray-400 mb-2">
                <span class="text-xs font-medium">Teknisi Bertugas</span>
                <div class="p-1.5 bg-emerald-50 dark:bg-emerald-900/30 text-emerald-600 dark:text-emerald-400 rounded-lg">
                    <span class="material-symbols-outlined text-base">engineering</span>
                </div>
            </div>
            <div class="flex items-baseline gap-1.5">
                <span class="text-2xl font-bold text-emerald-600 dark:text-emerald-400">{{ $techsOnDutyCount }}</span>
                <span class="text-xs text-gray-400">/ {{ $techsTotalCount }} Teknisi</span>
            </div>
            <div class="w-full bg-gray-100 dark:bg-gray-700 h-1.5 rounded-full mt-2 overflow-hidden">
                <div class="bg-emerald-500 h-full rounded-full" style="width: {{ $techsTotalCount > 0 ? ($techsOnDutyCount / $techsTotalCount * 100) : 0 }}%"></div>
            </div>
        </div>

        <!-- 3. Menunggu QC Approval -->
        <div wire:click="setStatusTab('qc_pending')" class="cursor-pointer bg-white dark:bg-gray-800 p-4 rounded-2xl border {{ $activeStatusTab === 'qc_pending' ? 'ring-2 ring-purple-500' : 'border-gray-100 dark:border-gray-700' }} shadow-sm hover:border-purple-300 transition-all">
            <div class="flex items-center justify-between text-gray-500 dark:text-gray-400 mb-2">
                <span class="text-xs font-medium">Menunggu QC Approval</span>
                <div class="p-1.5 bg-purple-50 dark:bg-purple-900/30 text-purple-600 dark:text-purple-400 rounded-lg">
                    <span class="material-symbols-outlined text-base">fact_check</span>
                </div>
            </div>
            <div class="text-2xl font-bold text-purple-600 dark:text-purple-400">{{ $pendingQcCount }}</div>
            <p class="text-[11px] text-gray-400 mt-1">Siap diverifikasi pengawas</p>
        </div>

        <!-- 4. SLA Breached -->
        <div wire:click="setStatusTab('breached')" class="cursor-pointer bg-white dark:bg-gray-800 p-4 rounded-2xl border {{ $activeStatusTab === 'breached' ? 'ring-2 ring-rose-500' : 'border-gray-100 dark:border-gray-700' }} shadow-sm hover:border-rose-300 transition-all">
            <div class="flex items-center justify-between text-gray-500 dark:text-gray-400 mb-2">
                <span class="text-xs font-medium">SLA Breached</span>
                <div class="p-1.5 bg-rose-50 dark:bg-rose-900/30 text-rose-600 dark:text-rose-400 rounded-lg {{ $slaBreachedCount > 0 ? 'animate-pulse' : '' }}">
                    <span class="material-symbols-outlined text-base">alarm_off</span>
                </div>
            </div>
            <div class="text-2xl font-bold text-rose-600 dark:text-rose-400">{{ $slaBreachedCount }}</div>
            <p class="text-[11px] text-gray-400 mt-1">Terlambat dari batas waktu</p>
        </div>

        <!-- 5. SLA Warning (80%) -->
        <div wire:click="setStatusTab('warning')" class="cursor-pointer bg-white dark:bg-gray-800 p-4 rounded-2xl border {{ $activeStatusTab === 'warning' ? 'ring-2 ring-amber-500' : 'border-gray-100 dark:border-gray-700' }} shadow-sm hover:border-amber-300 transition-all">
            <div class="flex items-center justify-between text-gray-500 dark:text-gray-400 mb-2">
                <span class="text-xs font-medium">SLA Warning (80%)</span>
                <div class="p-1.5 bg-amber-50 dark:bg-amber-900/30 text-amber-600 dark:text-amber-400 rounded-lg">
                    <span class="material-symbols-outlined text-base">warning</span>
                </div>
            </div>
            <div class="text-2xl font-bold text-amber-600 dark:text-amber-400">{{ $slaWarningCount }}</div>
            <p class="text-[11px] text-gray-400 mt-1">Mendekati deadline SLA</p>
        </div>

        <!-- 6. SLA Compliance Rate -->
        <div class="bg-white dark:bg-gray-800 p-4 rounded-2xl border border-gray-100 dark:border-gray-700 shadow-sm">
            <div class="flex items-center justify-between text-gray-500 dark:text-gray-400 mb-2">
                <span class="text-xs font-medium">SLA Compliance</span>
                <div class="p-1.5 bg-teal-50 dark:bg-teal-900/30 text-teal-600 dark:text-teal-400 rounded-lg">
                    <span class="material-symbols-outlined text-base">verified</span>
                </div>
            </div>
            <div class="text-2xl font-bold {{ $slaComplianceRate >= 90 ? 'text-emerald-600' : ($slaComplianceRate >= 75 ? 'text-amber-600' : 'text-rose-600') }}">
                {{ $slaComplianceRate }}%
            </div>
            <p class="text-[11px] text-gray-400 mt-1">Target ISP: 90% On-Time</p>
        </div>
    </div>

    <!-- GIS Interactive Map Section (Leaflet) -->
    @if($showMap)
    <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700 shadow-sm overflow-hidden p-5">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pb-4 border-b border-gray-100 dark:border-gray-700">
            <div class="flex items-center gap-2">
                <span class="material-symbols-outlined text-blue-600 dark:text-blue-400">explore</span>
                <h2 class="text-sm font-bold text-gray-900 dark:text-white">Peta Terpadu Teknisi & Pekerjaan Lapangan (GIS)</h2>
            </div>
            <!-- Map Legend & Layer Toggles -->
            <div class="flex flex-wrap items-center gap-3 text-xs">
                <label class="inline-flex items-center gap-1.5 cursor-pointer">
                    <input type="checkbox" wire:model.live="layerTechnicians" class="rounded text-emerald-600 focus:ring-emerald-500">
                    <span class="flex items-center gap-1 text-gray-700 dark:text-gray-300">
                        <span class="w-2.5 h-2.5 rounded-full bg-emerald-500 inline-block"></span>
                        Teknisi On Duty ({{ $techsOnDutyCount }})
                    </span>
                </label>
                <label class="inline-flex items-center gap-1.5 cursor-pointer">
                    <input type="checkbox" wire:model.live="layerJobs" class="rounded text-blue-600 focus:ring-blue-500">
                    <span class="flex items-center gap-1 text-gray-700 dark:text-gray-300">
                        <span class="w-2.5 h-2.5 rounded-full bg-blue-500 inline-block"></span>
                        Pekerjaan Aktif ({{ $totalActiveJobs }})
                    </span>
                </label>
                <span class="text-gray-400">|</span>
                <span class="flex items-center gap-1 text-[11px] text-rose-600 font-medium">
                    <span class="w-2 h-2 rounded-full bg-rose-500 inline-block animate-ping"></span>
                    Merah: Breached
                </span>
                <span class="flex items-center gap-1 text-[11px] text-amber-600 font-medium">
                    <span class="w-2 h-2 rounded-full bg-amber-500 inline-block"></span>
                    Oranye: Warning 80%
                </span>
                <span class="flex items-center gap-1 text-[11px] text-purple-600 font-medium">
                    <span class="w-2 h-2 rounded-full bg-purple-500 inline-block"></span>
                    Ungu: QC Pending
                </span>
            </div>
        </div>

        <!-- Map Container -->
        <div class="mt-4 relative rounded-xl overflow-hidden border border-gray-100 dark:border-gray-700 bg-gray-50 dark:bg-gray-900" style="height: 440px;" wire:ignore>
            <div id="controlCenterMap" class="w-full h-full z-0"></div>
            <!-- Overlay controls -->
            <div class="absolute bottom-3 right-3 z-10 bg-white/90 dark:bg-gray-800/90 backdrop-blur-sm px-3 py-1.5 rounded-xl shadow border border-gray-200 dark:border-gray-700 text-[11px] text-gray-600 dark:text-gray-300">
                <span>Total Marker: </span>
                <span class="font-bold text-gray-900 dark:text-white" id="markerCountDisplay">{{ count($mapMarkers) }}</span>
            </div>
        </div>
    </div>
    @endif

    <!-- Real-Time Job Monitoring Table & Control Panels -->
    <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700 shadow-sm overflow-hidden">
        <!-- Table Filter Tabs & Search -->
        <div class="p-5 border-b border-gray-100 dark:border-gray-700 flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
            <!-- Tabs -->
            <div class="flex flex-wrap items-center gap-1.5 text-xs font-medium">
                <button wire:click="setStatusTab('all')" class="px-3.5 py-2 rounded-xl transition-all {{ $activeStatusTab === 'all' ? 'bg-gray-900 dark:bg-white text-white dark:text-gray-900 font-bold shadow-sm' : 'text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-700' }}">
                    Semua Aktif ({{ $totalActiveJobs }})
                </button>
                <button wire:click="setStatusTab('breached')" class="px-3.5 py-2 rounded-xl transition-all flex items-center gap-1.5 {{ $activeStatusTab === 'breached' ? 'bg-rose-600 text-white font-bold shadow-sm' : 'text-rose-600 hover:bg-rose-50 dark:hover:bg-rose-900/30' }}">
                    <span class="material-symbols-outlined text-sm">alarm_off</span>
                    <span>SLA Breached ({{ $slaBreachedCount }})</span>
                </button>
                <button wire:click="setStatusTab('warning')" class="px-3.5 py-2 rounded-xl transition-all flex items-center gap-1.5 {{ $activeStatusTab === 'warning' ? 'bg-amber-500 text-white font-bold shadow-sm' : 'text-amber-600 hover:bg-amber-50 dark:hover:bg-amber-900/30' }}">
                    <span class="material-symbols-outlined text-sm">warning</span>
                    <span>SLA Warning ({{ $slaWarningCount }})</span>
                </button>
                <button wire:click="setStatusTab('qc_pending')" class="px-3.5 py-2 rounded-xl transition-all flex items-center gap-1.5 {{ $activeStatusTab === 'qc_pending' ? 'bg-purple-600 text-white font-bold shadow-sm' : 'text-purple-600 hover:bg-purple-50 dark:hover:bg-purple-900/30' }}">
                    <span class="material-symbols-outlined text-sm">fact_check</span>
                    <span>Menunggu QC ({{ $pendingQcCount }})</span>
                </button>
            </div>

            <!-- Search Bar -->
            <div class="relative w-full sm:w-72">
                <span class="material-symbols-outlined absolute left-3 top-2.5 text-gray-400 text-sm">search</span>
                <input type="text" wire:model.live.debounce.300ms="searchQuery" placeholder="Cari SPK, teknisi, pelanggan..." class="w-full pl-9 pr-4 py-2 text-xs rounded-xl border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900 text-gray-900 dark:text-white focus:ring-blue-500">
            </div>
        </div>

        <!-- Table View -->
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-gray-700 dark:text-gray-300">
                <thead class="bg-gray-50 dark:bg-gray-900/50 text-[11px] font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400 border-b border-gray-100 dark:border-gray-700">
                    <tr>
                        <th class="py-3 px-4">No. Pekerjaan</th>
                        <th class="py-3 px-4">Pelanggan & Lokasi</th>
                        <th class="py-3 px-4">Teknisi Bertugas</th>
                        <th class="py-3 px-4">Prioritas</th>
                        <th class="py-3 px-4">Status & QC</th>
                        <th class="py-3 px-4">Status SLA</th>
                        <th class="py-3 px-4 text-right">Aksi Tindakan</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                    @forelse($jobs as $job)
                    @php
                        $metrics = $job->metrics ?? [];
                        $isBreached = !empty($metrics['is_breached']) || ($metrics['status'] ?? '') === 'breached';
                        $isWarning = !empty($metrics['is_warning']) || ($metrics['status'] ?? '') === 'warning';
                        $isQcPending = $job->status === 'pending_approval';

                        $badgeBg = match (true) {
                            $isBreached => 'bg-rose-50 text-rose-700 dark:bg-rose-900/30 dark:text-rose-400 border border-rose-200 dark:border-rose-800',
                            $isWarning => 'bg-amber-50 text-amber-700 dark:bg-amber-900/30 dark:text-amber-400 border border-amber-200 dark:border-amber-800',
                            $isQcPending => 'bg-purple-50 text-purple-700 dark:bg-purple-900/30 dark:text-purple-400 border border-purple-200 dark:border-purple-800',
                            default => 'bg-blue-50 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400 border border-blue-200 dark:border-blue-800',
                        };

                        $actionUrl = match ($job->entity_type) {
                            'ticket' => route('tickets.direct', $job->entity_id),
                            default => route('support.work-orders.history'),
                        };
                    @endphp
                    <tr class="hover:bg-gray-50/75 dark:hover:bg-gray-750/50 transition-colors {{ $isBreached ? 'bg-rose-50/20' : '' }}">
                        <!-- No & Tipe -->
                        <td class="py-3.5 px-4">
                            <div class="flex items-center gap-2">
                                <span class="p-1.5 rounded-lg {{ $badgeBg }}">
                                    <span class="material-symbols-outlined text-sm">
                                        {{ match($job->type) { 'installation' => 'router', 'troubleshoot' => 'build', 'maintenance' => 'handyman', default => 'assignment' } }}
                                    </span>
                                </span>
                                <div>
                                    <span class="font-bold text-gray-900 dark:text-white">{{ $job->code }}</span>
                                    <span class="block text-[11px] text-gray-400 capitalize">{{ $job->type }}</span>
                                </div>
                            </div>
                        </td>

                        <!-- Pelanggan & Alamat -->
                        <td class="py-3.5 px-4 max-w-xs">
                            <div class="font-medium text-gray-900 dark:text-white truncate">{{ $job->customer_name }}</div>
                            <div class="text-[11px] text-gray-400 truncate">{{ $job->title }}</div>
                            <div class="text-[10px] text-blue-600 dark:text-blue-400 font-mono">{{ $job->branch_name }}</div>
                        </td>

                        <!-- Teknisi -->
                        <td class="py-3.5 px-4">
                            <div class="flex items-center gap-2">
                                <span class="w-6 h-6 rounded-full bg-gray-200 dark:bg-gray-700 flex items-center justify-center text-[11px] font-bold text-gray-700 dark:text-gray-300">
                                    {{ strtoupper(substr($job->technician_name, 0, 1)) }}
                                </span>
                                <div>
                                    <span class="font-medium text-gray-900 dark:text-white">{{ $job->technician_name }}</span>
                                    @if($job->technician_id)
                                    <span class="block text-[10px] text-gray-400">ID: #{{ $job->technician_id }}</span>
                                    @endif
                                </div>
                            </div>
                        </td>

                        <!-- Prioritas -->
                        <td class="py-3.5 px-4">
                            @php
                                $pColor = match(strtolower($job->priority ?? 'normal')) {
                                    'urgent', 'critical' => 'text-rose-600 bg-rose-50 dark:bg-rose-900/30',
                                    'high' => 'text-amber-600 bg-amber-50 dark:bg-amber-900/30',
                                    default => 'text-gray-600 bg-gray-100 dark:bg-gray-700',
                                };
                            @endphp
                            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold uppercase {{ $pColor }}">
                                {{ $job->priority ?? 'NORMAL' }}
                            </span>
                        </td>

                        <!-- Status & QC -->
                        <td class="py-3.5 px-4">
                            @if($isQcPending)
                                <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[11px] font-semibold bg-purple-100 text-purple-700 dark:bg-purple-900/40 dark:text-purple-300">
                                    <span class="w-1.5 h-1.5 rounded-full bg-purple-500 animate-pulse"></span>
                                    Pending QC Approval
                                </span>
                            @else
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-medium bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300 capitalize">
                                    {{ str_replace('_', ' ', $job->status) }}
                                </span>
                            @endif
                        </td>

                        <!-- Status SLA Countdown -->
                        <td class="py-3.5 px-4">
                            <div class="flex items-center gap-1.5">
                                @if($isBreached)
                                    <span class="material-symbols-outlined text-rose-600 text-sm animate-bounce">alarm_off</span>
                                    <span class="text-rose-600 font-bold text-xs">{{ $metrics['remaining_formatted'] ?? 'Terlambat' }}</span>
                                @elseif($isWarning)
                                    <span class="material-symbols-outlined text-amber-500 text-sm animate-pulse">warning</span>
                                    <span class="text-amber-600 font-semibold text-xs">{{ $metrics['remaining_formatted'] ?? 'Sisa < 20%' }}</span>
                                @else
                                    <span class="material-symbols-outlined text-emerald-500 text-sm">schedule</span>
                                    <span class="text-emerald-600 dark:text-emerald-400 text-xs">{{ $metrics['remaining_formatted'] ?? 'Normal' }}</span>
                                @endif
                            </div>
                            @if(!empty($metrics['due_at']))
                                <span class="block text-[10px] text-gray-400">Batas: {{ $metrics['due_at']->format('d/m H:i') }}</span>
                            @endif
                        </td>

                        <!-- Aksi -->
                        <td class="py-3.5 px-4 text-right">
                            <div class="inline-flex items-center gap-1.5">
                                @if($isQcPending)
                                    <a href="{{ route('support.work-orders.history') }}?typeFilter=qc_approval" class="px-2.5 py-1 bg-purple-600 hover:bg-purple-700 text-white text-[11px] font-semibold rounded-lg shadow-sm transition-colors">
                                        Periksa QC
                                    </a>
                                @else
                                    <a href="{{ $actionUrl }}" class="px-2.5 py-1 bg-gray-100 hover:bg-gray-200 dark:bg-gray-700 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-200 text-[11px] font-medium rounded-lg transition-colors">
                                        Detail
                                    </a>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="py-12 text-center text-gray-400">
                            <span class="material-symbols-outlined text-4xl mb-2 text-gray-300">task_alt</span>
                            <p class="font-medium">Tidak ada pekerjaan lapangan aktif pada filter ini.</p>
                            <p class="text-xs text-gray-400">Seluruh tugas terselesaikan dengan baik atau gunakan filter tanggal lain.</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Bottom Grids: Leaderboard, Breakdown & Activity Feed -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- 1. Performa Cabang (SLA Compliance) -->
        <div class="bg-white dark:bg-gray-800 p-5 rounded-2xl border border-gray-100 dark:border-gray-700 shadow-sm">
            <h3 class="text-sm font-bold text-gray-900 dark:text-white mb-4 flex items-center gap-2">
                <span class="material-symbols-outlined text-blue-600 text-base">domain</span>
                <span>Breakdown Kinerja Per Cabang</span>
            </h3>
            <div class="space-y-3.5">
                @forelse($branchBreakdown as $bKey => $b)
                @php
                    $bName = $b['name'] ?? (is_string($bKey) ? $bKey : 'Cabang');
                    $bTotal = $b['total'] ?? 0;
                    $bBreached = $b['breached'] ?? 0;
                    $bMet = $b['met'] ?? max(0, $bTotal - $bBreached);
                    $bRate = $b['compliance_rate'] ?? ($bTotal > 0 ? round(($bMet / $bTotal) * 100, 1) : 100);
                @endphp
                <div class="p-3 bg-gray-50 dark:bg-gray-900/50 rounded-xl">
                    <div class="flex items-center justify-between text-xs mb-1.5">
                        <span class="font-bold text-gray-900 dark:text-white">{{ $bName }}</span>
                        <span class="font-bold {{ $bRate >= 90 ? 'text-emerald-600' : 'text-amber-600' }}">
                            {{ $bRate }}% On-Time
                        </span>
                    </div>
                    <div class="w-full bg-gray-200 dark:bg-gray-700 h-2 rounded-full overflow-hidden mb-1.5">
                        <div class="h-full rounded-full {{ $bRate >= 90 ? 'bg-emerald-500' : 'bg-amber-500' }}" style="width: {{ $bRate }}%"></div>
                    </div>
                    <div class="flex items-center justify-between text-[11px] text-gray-400">
                        <span>Total: {{ $bTotal }} Pekerjaan</span>
                        <span>Breached: <b class="{{ $bBreached > 0 ? 'text-rose-600' : '' }}">{{ $bBreached }}</b></span>
                    </div>
                </div>
                @empty
                <p class="text-xs text-gray-400 text-center py-4">Belum ada data cabang pada periode ini.</p>
                @endforelse
            </div>
        </div>

        <!-- 2. Leaderboard Teknisi Lapangan -->
        <div class="bg-white dark:bg-gray-800 p-5 rounded-2xl border border-gray-100 dark:border-gray-700 shadow-sm">
            <h3 class="text-sm font-bold text-gray-900 dark:text-white mb-4 flex items-center gap-2">
                <span class="material-symbols-outlined text-amber-500 text-base">military_tech</span>
                <span>Top Teknisi Lapangan (Penyelesaian & SLA)</span>
            </h3>
            <div class="space-y-3">
                @forelse($technicianLeaderboard as $rank => $tech)
                <div class="flex items-center justify-between p-2.5 hover:bg-gray-50 dark:hover:bg-gray-750 rounded-xl transition-colors">
                    <div class="flex items-center gap-3">
                        <span class="w-6 h-6 rounded-full bg-blue-100 dark:bg-blue-900/40 text-blue-600 dark:text-blue-400 flex items-center justify-center font-bold text-xs">
                            {{ $loop->iteration }}
                        </span>
                        <div>
                            <span class="font-bold text-xs text-gray-900 dark:text-white">{{ $tech['name'] }}</span>
                            <span class="block text-[10px] text-gray-400">Selesai: {{ $tech['completed'] }} • Aktif: {{ $tech['in_progress'] }}</span>
                        </div>
                    </div>
                    <div class="text-right">
                        <span class="text-xs font-bold {{ $tech['compliance_rate'] >= 90 ? 'text-emerald-600' : 'text-amber-600' }}">
                            {{ $tech['compliance_rate'] }}%
                        </span>
                        <span class="block text-[10px] text-gray-400">{{ $tech['total'] }} Total SPK</span>
                    </div>
                </div>
                @empty
                <p class="text-xs text-gray-400 text-center py-4">Belum ada riwayat pengerjaan teknisi.</p>
                @endforelse
            </div>
        </div>

        <!-- 3. Audit Trail & Log Aktivitas Lapangan -->
        <div class="bg-white dark:bg-gray-800 p-5 rounded-2xl border border-gray-100 dark:border-gray-700 shadow-sm">
            <h3 class="text-sm font-bold text-gray-900 dark:text-white mb-4 flex items-center gap-2">
                <span class="material-symbols-outlined text-purple-600 text-base">history</span>
                <span>Audit Trail & Aktivitas Terakhir</span>
            </h3>
            <div class="space-y-3.5 max-h-80 overflow-y-auto pr-1">
                @forelse($recentActivities as $act)
                <div class="border-l-2 border-blue-500 pl-3 py-0.5 text-xs">
                    <div class="flex items-center justify-between">
                        <span class="font-bold text-gray-900 dark:text-white capitalize">{{ str_replace('_', ' ', $act->event) }}</span>
                        <span class="text-[10px] text-gray-400">{{ $act->created_at->diffForHumans() }}</span>
                    </div>
                    <p class="text-[11px] text-gray-600 dark:text-gray-300 mt-0.5">{{ $act->notes ?: 'Pembaruan status operasional lapangan.' }}</p>
                    <span class="text-[10px] text-gray-400">Oleh: {{ $act->user?->name ?? 'Sistem / Otomatis' }}</span>
                </div>
                @empty
                <p class="text-xs text-gray-400 text-center py-4">Belum ada catatan aktivitas baru.</p>
                @endforelse
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        let map = null;
        let markersGroup = null;

        function initMap() {
            const container = document.getElementById('controlCenterMap');
            if (!container || map) return;

            // Default center: Indonesia coordinates
            map = L.map('controlCenterMap').setView([-7.2575, 112.7521], 11);

            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                maxZoom: 19,
                attribution: '&copy; OpenStreetMap contributors | dsBilling Enterprise'
            }).addTo(map);

            markersGroup = L.featureGroup().addTo(map);
            updateMapMarkers(@json($mapMarkers));
        }

        function updateMapMarkers(markers) {
            if (!map || !markersGroup) return;

            markersGroup.clearLayers();

            if (!markers || markers.length === 0) {
                return;
            }

            markers.forEach(m => {
                let iconHtml = '';
                if (m.type === 'technician') {
                    iconHtml = `
                        <div style="background-color: ${m.color}; width: 32px; height: 32px; border-radius: 50%; border: 3px solid white; box-shadow: 0 4px 6px rgba(0,0,0,0.3); display: flex; align-items: center; justify-content: center; color: white;">
                            <span class="material-symbols-outlined" style="font-size: 16px;">engineering</span>
                        </div>
                    `;
                } else {
                    iconHtml = `
                        <div style="background-color: ${m.color}; width: 30px; height: 30px; border-radius: 8px; border: 2px solid white; box-shadow: 0 3px 5px rgba(0,0,0,0.25); display: flex; align-items: center; justify-content: center; color: white;">
                            <span class="material-symbols-outlined" style="font-size: 16px;">${m.icon || 'assignment'}</span>
                        </div>
                    `;
                }

                const customIcon = L.divIcon({
                    html: iconHtml,
                    className: 'custom-leaflet-marker',
                    iconSize: [32, 32],
                    iconAnchor: [16, 16],
                    popupAnchor: [0, -16],
                });

                let popupContent = '';
                if (m.type === 'technician') {
                    popupContent = `
                        <div style="font-family: inherit; font-size: 12px; line-height: 1.4; min-width: 180px;">
                            <div style="font-weight: bold; font-size: 13px; color: #111827; margin-bottom: 2px;">👷 ${m.title}</div>
                            <div style="color: #4B5563;">Status: <b style="color: ${m.color};">${m.status.toUpperCase()}</b></div>
                            <div style="color: #4B5563;">Cabang: ${m.branch}</div>
                            <div style="color: #4B5563;">Pekerjaan Aktif: <b>${m.active_jobs}</b></div>
                            <div style="color: #9CA3AF; font-size: 10px; margin-top: 4px;">Update: ${m.last_seen}</div>
                            ${m.phone ? `<a href="https://wa.me/${m.phone.replace(/[^0-9]/g, '')}" target="_blank" style="display: inline-block; margin-top: 6px; padding: 3px 8px; background-color: #10B981; color: white; border-radius: 6px; text-decoration: none; font-size: 11px; font-weight: bold;">WhatsApp Teknisi</a>` : ''}
                        </div>
                    `;
                } else {
                    popupContent = `
                        <div style="font-family: inherit; font-size: 12px; line-height: 1.4; min-width: 200px;">
                            <div style="font-weight: bold; font-size: 13px; color: ${m.color}; margin-bottom: 2px;">📋 ${m.code}</div>
                            <div style="font-weight: bold; color: #111827;">${m.customer}</div>
                            <div style="color: #4B5563; font-size: 11px;">${m.title}</div>
                            <div style="color: #4B5563; margin-top: 4px;">Teknisi: <b>${m.technician}</b></div>
                            <div style="color: #4B5563;">SLA: <b style="color: ${m.color};">${m.sla_text}</b></div>
                            <a href="${m.action_url}" style="display: inline-block; margin-top: 6px; padding: 4px 10px; background-color: #0866F5; color: white; border-radius: 6px; text-decoration: none; font-size: 11px; font-weight: bold;">Buka Pekerjaan</a>
                        </div>
                    `;
                }

                const marker = L.marker([m.lat, m.lng], { icon: customIcon })
                    .bindPopup(popupContent);
                markersGroup.addLayer(marker);
            });

            const bounds = markersGroup.getBounds();
            if (bounds.isValid()) {
                map.fitBounds(bounds, { padding: [40, 40], maxZoom: 15 });
            }
        }

        // Initialize on load
        setTimeout(initMap, 200);

        // Listen to Livewire updates
        Livewire.hook('morph.updated', ({ el, component }) => {
            const displayEl = document.getElementById('markerCountDisplay');
            if (displayEl && map) {
                // Re-sync markers when Livewire polls
                setTimeout(() => {
                    const freshMarkers = @json($mapMarkers);
                    updateMapMarkers(freshMarkers);
                }, 100);
            }
        });
    });
</script>
@endpush
