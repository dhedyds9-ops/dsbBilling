<div class="space-y-6">

    <!-- Page Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-900 dark:text-slate-100 tracking-tight flex items-center gap-2">
                <span class="material-symbols-outlined notranslate text-primary-600 text-3xl" translate="no">history_edu</span>
                Riwayat Instalasi & Pekerjaan Lapangan
            </h1>
            <p class="text-xs sm:text-sm text-slate-500 dark:text-slate-400 mt-1">
                Rekap lengkap seluruh histori pemasangan pelanggan baru (PSB), aktivasi teknisi, dan penyelesaian tiket perbaikan.
            </p>
        </div>

        <!-- Filter Periode (Bulan & Tahun) -->
        <div class="grid grid-cols-2 sm:flex sm:items-center gap-2 w-full sm:w-auto">
            <select wire:model.live="selectedMonth" class="w-full sm:w-auto px-3 py-2 text-xs font-semibold rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-700 dark:text-slate-200 focus:ring-2 focus:ring-primary-500 shadow-sm">
                @for($m = 1; $m <= 12; $m++)
                    <option value="{{ $m }}">{{ \Carbon\Carbon::create(null, $m, 1)->translatedFormat('F') }}</option>
                @endfor
            </select>
            <select wire:model.live="selectedYear" class="w-full sm:w-auto px-3 py-2 text-xs font-semibold rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-700 dark:text-slate-200 focus:ring-2 focus:ring-primary-500 shadow-sm">
                @for($y = now()->year - 2; $y <= now()->year + 1; $y++)
                    <option value="{{ $y }}">{{ $y }}</option>
                @endfor
            </select>
        </div>
    </div>

    <!-- Summary KPI Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="bg-white dark:bg-slate-800 rounded-2xl p-5 border border-slate-200 dark:border-slate-700 shadow-sm flex items-start justify-between relative overflow-hidden">
            <div class="absolute top-0 left-0 right-0 h-1 bg-gradient-to-r from-blue-500 to-indigo-500"></div>
            <div>
                <p class="text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Total Pasang Baru</p>
                <p class="text-2xl font-black text-slate-900 dark:text-white mt-2">{{ number_format($totalInstallations) }}</p>
                <p class="text-[11px] text-slate-400 dark:text-slate-500 mt-1">Seluruh pelanggan aktif/terpasang</p>
            </div>
            <div class="w-12 h-12 rounded-xl bg-blue-50 dark:bg-blue-900/30 flex items-center justify-center text-blue-600 dark:text-blue-400 shrink-0">
                <span class="material-symbols-outlined notranslate text-2xl" translate="no">build_circle</span>
            </div>
        </div>

        <div class="bg-white dark:bg-slate-800 rounded-2xl p-5 border border-slate-200 dark:border-slate-700 shadow-sm flex items-start justify-between relative overflow-hidden">
            <div class="absolute top-0 left-0 right-0 h-1 bg-gradient-to-r from-emerald-500 to-teal-500"></div>
            <div>
                <p class="text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">PSB Bulan Ini</p>
                <p class="text-2xl font-black text-emerald-600 dark:text-emerald-400 mt-2">{{ number_format($monthInstallations) }}</p>
                <p class="text-[11px] text-slate-400 dark:text-slate-500 mt-1">Periode {{ $selectedMonthName }}</p>
            </div>
            <div class="w-12 h-12 rounded-xl bg-emerald-50 dark:bg-emerald-900/30 flex items-center justify-center text-emerald-600 dark:text-emerald-400 shrink-0">
                <span class="material-symbols-outlined notranslate text-2xl" translate="no">verified</span>
            </div>
        </div>

        <div class="bg-white dark:bg-slate-800 rounded-2xl p-5 border border-slate-200 dark:border-slate-700 shadow-sm flex items-start justify-between relative overflow-hidden">
            <div class="absolute top-0 left-0 right-0 h-1 bg-gradient-to-r from-amber-500 to-orange-500"></div>
            <div>
                <p class="text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Total Perbaikan</p>
                <p class="text-2xl font-black text-amber-600 dark:text-amber-400 mt-2">{{ number_format($totalRepairs) }}</p>
                <p class="text-[11px] text-slate-400 dark:text-slate-500 mt-1">Total penanganan kendala</p>
            </div>
            <div class="w-12 h-12 rounded-xl bg-amber-50 dark:bg-amber-900/30 flex items-center justify-center text-amber-600 dark:text-amber-400 shrink-0">
                <span class="material-symbols-outlined notranslate text-2xl" translate="no">home_repair_service</span>
            </div>
        </div>

        <div class="bg-white dark:bg-slate-800 rounded-2xl p-5 border border-slate-200 dark:border-slate-700 shadow-sm flex items-start justify-between relative overflow-hidden">
            <div class="absolute top-0 left-0 right-0 h-1 bg-gradient-to-r from-purple-500 to-pink-500"></div>
            <div>
                <p class="text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Perbaikan Bulan Ini</p>
                <p class="text-2xl font-black text-purple-600 dark:text-purple-400 mt-2">{{ number_format($monthRepairs) }}</p>
                <p class="text-[11px] text-slate-400 dark:text-slate-500 mt-1">Periode {{ $selectedMonthName }}</p>
            </div>
            <div class="w-12 h-12 rounded-xl bg-purple-50 dark:bg-purple-900/30 flex items-center justify-center text-purple-600 dark:text-purple-400 shrink-0">
                <span class="material-symbols-outlined notranslate text-2xl" translate="no">published_with_changes</span>
            </div>
        </div>
    </div>

    <!-- Filters & Search Toolbar -->
    <div class="bg-white dark:bg-slate-800 p-4 rounded-2xl border border-slate-200 dark:border-slate-700 shadow-sm space-y-3">
        <div class="flex flex-col lg:flex-row items-stretch lg:items-center justify-between gap-3">
            {{-- Tipe Tabs --}}
            <div class="flex items-center gap-1 overflow-x-auto pb-1 lg:pb-0">
                <button wire:click="setTypeFilter('all')" type="button"
                        class="px-3.5 py-1.5 rounded-xl text-xs font-bold transition-all
                               {{ $typeFilter === 'all' ? 'bg-primary-600 text-white shadow-sm' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-700' }}">
                    Semua Pekerjaan
                </button>
                <button wire:click="setTypeFilter('qc_approval')" type="button"
                        class="px-3.5 py-1.5 rounded-xl text-xs font-bold transition-all inline-flex items-center gap-1.5
                               {{ $typeFilter === 'qc_approval' ? 'bg-amber-600 text-white shadow-sm' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-700' }}">
                    <span class="material-symbols-outlined notranslate text-xs" translate="no">verified</span>
                    Persetujuan QC
                    @if($pendingQcCount > 0)
                        <span class="px-1.5 py-0.5 text-[10px] font-black rounded-full {{ $typeFilter === 'qc_approval' ? 'bg-white text-amber-700' : 'bg-amber-500 text-white' }}">
                            {{ $pendingQcCount }}
                        </span>
                    @endif
                </button>
                <button wire:click="setTypeFilter('installation')" type="button"
                        class="px-3.5 py-1.5 rounded-xl text-xs font-bold transition-all inline-flex items-center gap-1
                               {{ $typeFilter === 'installation' ? 'bg-blue-600 text-white shadow-sm' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-700' }}">
                    <span class="material-symbols-outlined notranslate text-xs" translate="no">build_circle</span>
                    Pasang Baru (PSB)
                </button>
                <button wire:click="setTypeFilter('repair')" type="button"
                        class="px-3.5 py-1.5 rounded-xl text-xs font-bold transition-all inline-flex items-center gap-1
                               {{ $typeFilter === 'repair' ? 'bg-amber-600 text-white shadow-sm' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-700' }}">
                    <span class="material-symbols-outlined notranslate text-xs" translate="no">build</span>
                    Perbaikan Lapangan
                </button>
                <button wire:click="setTypeFilter('maintenance')" type="button"
                        class="px-3.5 py-1.5 rounded-xl text-xs font-bold transition-all inline-flex items-center gap-1
                               {{ $typeFilter === 'maintenance' ? 'bg-purple-600 text-white shadow-sm' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-700' }}">
                    <span class="material-symbols-outlined notranslate text-xs" translate="no">home_repair_service</span>
                    Maintenance
                </button>
                <button wire:click="setTypeFilter('activation')" type="button"
                        class="px-3.5 py-1.5 rounded-xl text-xs font-bold transition-all inline-flex items-center gap-1
                               {{ $typeFilter === 'activation' ? 'bg-emerald-600 text-white shadow-sm' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-700' }}">
                    <span class="material-symbols-outlined notranslate text-xs" translate="no">verified</span>
                    Aktivasi
                </button>
                <a href="{{ route('support.sla.report') }}"
                   class="px-3 py-1.5 rounded-xl text-xs font-bold transition-all inline-flex items-center gap-1 text-indigo-600 dark:text-indigo-400 hover:bg-indigo-50 dark:hover:bg-indigo-950/40 border border-indigo-200 dark:border-indigo-800 whitespace-nowrap">
                    <span class="material-symbols-outlined notranslate text-xs" translate="no">analytics</span>
                    Laporan SLA
                </a>
            </div>

            {{-- Search Input --}}
            <div class="relative w-full lg:w-72">
                <span class="material-symbols-outlined notranslate absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-sm" translate="no">search</span>
                <input type="text" wire:model.live.debounce.300ms="search" placeholder="Cari pelanggan, SN ONU, username..."
                       class="w-full pl-9 pr-8 py-2 text-xs rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-900 text-slate-900 dark:text-slate-100 focus:ring-2 focus:ring-primary-500">
                @if($search)
                    <button wire:click="$set('search', '')" class="absolute right-2.5 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600">
                        <span class="material-symbols-outlined notranslate text-xs" translate="no">close</span>
                    </button>
                @endif
            </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3 pt-2 border-t border-slate-100 dark:border-slate-700/60">
            {{-- Teknisi Filter --}}
            <div>
                <label class="block text-[11px] font-semibold text-slate-500 dark:text-slate-400 mb-1">Filter Teknisi</label>
                <select wire:model.live="technicianFilter" class="w-full px-3 py-1.5 text-xs rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 text-slate-800 dark:text-slate-200 focus:ring-2 focus:ring-primary-500">
                    <option value="">Semua Teknisi</option>
                    @foreach($technicians as $tech)
                        <option value="{{ $tech->id }}">{{ $tech->name }} ({{ $tech->email }})</option>
                    @endforeach
                </select>
            </div>

            {{-- Reseller Filter --}}
            <div>
                <label class="block text-[11px] font-semibold text-slate-500 dark:text-slate-400 mb-1">Filter Reseller / Kantor</label>
                <select wire:model.live="resellerFilter" class="w-full px-3 py-1.5 text-xs rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 text-slate-800 dark:text-slate-200 focus:ring-2 focus:ring-primary-500">
                    <option value="">Semua (Direct & Reseller)</option>
                    <option value="direct">Pelanggan Kantor / Direct Saja</option>
                    @foreach($resellers as $reseller)
                        <option value="{{ $reseller->id }}">{{ $reseller->name }}</option>
                    @endforeach
                </select>
            </div>

            {{-- Status Filter --}}
            <div>
                <label class="block text-[11px] font-semibold text-slate-500 dark:text-slate-400 mb-1">Status Pekerjaan</label>
                <select wire:model.live="statusFilter" class="w-full px-3 py-1.5 text-xs rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 text-slate-800 dark:text-slate-200 focus:ring-2 focus:ring-primary-500">
                    <option value="all">Semua Status</option>
                    <option value="pending_approval">Menunggu Persetujuan QC</option>
                    <option value="completed">Selesai / Aktif</option>
                    <option value="pending">Dalam Proses / Pending</option>
                </select>
            </div>
        </div>
    </div>

    <!-- Data Table -->
    <div class="bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-2xl shadow-sm overflow-hidden">
        <div class="px-6 py-4 border-b border-slate-200 dark:border-slate-700 flex justify-between items-center bg-slate-50 dark:bg-slate-900/50">
            <h3 class="font-bold text-sm text-slate-800 dark:text-slate-200 flex items-center gap-2">
                <span class="material-symbols-outlined notranslate text-primary-600 text-lg" translate="no">assignment_turned_in</span>
                Daftar Riwayat Instalasi, Perbaikan & QC
            </h3>
            <span class="text-xs text-slate-500 dark:text-slate-400">Total: {{ $items->total() }} record</span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-slate-200 dark:border-slate-700 text-xs uppercase tracking-wider text-slate-500 dark:text-slate-400 bg-white dark:bg-slate-800">
                        <th class="px-6 py-3.5 text-left font-semibold">Tgl / Jam</th>
                        <th class="px-6 py-3.5 text-left font-semibold">Tipe Pekerjaan</th>
                        <th class="px-6 py-3.5 text-left font-semibold">Pelanggan</th>
                        <th class="px-6 py-3.5 text-left font-semibold">Kategori / Reseller</th>
                        <th class="px-6 py-3.5 text-left font-semibold">Teknisi</th>
                        <th class="px-6 py-3.5 text-left font-semibold">Detail Perangkat & Layanan</th>
                        <th class="px-6 py-3.5 text-center font-semibold">Status</th>
                        <th class="px-6 py-3.5 text-center font-semibold">SLA Countdown</th>
                        <th class="px-6 py-3.5 text-center font-semibold">Pemeriksaan QC</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-700">
                    @forelse($items as $item)
                        <tr class="hover:bg-slate-50 dark:bg-slate-900/50 dark:hover:bg-slate-800/50 transition-colors">
                            <td class="px-6 py-4 text-xs text-slate-600 dark:text-slate-400 whitespace-nowrap">
                                <div class="font-semibold text-slate-800 dark:text-slate-200">
                                    {{ $item->date ? \Carbon\Carbon::parse($item->date)->format('d M Y') : '-' }}
                                </div>
                                <div class="text-[11px] text-slate-400">
                                    {{ $item->date ? \Carbon\Carbon::parse($item->date)->format('H:i') : '' }}
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @if($item->type === 'installation')
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[11px] font-bold bg-blue-50 text-blue-700 dark:bg-blue-900/40 dark:text-blue-300 border border-blue-200 dark:border-blue-800">
                                        <span class="material-symbols-outlined notranslate text-xs" translate="no">build_circle</span>
                                        {{ $item->type_badge }}
                                    </span>
                                @elseif($item->type === 'repair')
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[11px] font-bold bg-amber-50 text-amber-700 dark:bg-amber-900/40 dark:text-amber-300 border border-amber-200 dark:border-amber-800">
                                        <span class="material-symbols-outlined notranslate text-xs" translate="no">build</span>
                                        {{ $item->type_badge }}
                                    </span>
                                @elseif($item->type === 'maintenance')
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[11px] font-bold bg-purple-50 text-purple-700 dark:bg-purple-900/40 dark:text-purple-300 border border-purple-200 dark:border-purple-800">
                                        <span class="material-symbols-outlined notranslate text-xs" translate="no">home_repair_service</span>
                                        {{ $item->type_badge }}
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[11px] font-bold bg-emerald-50 text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800">
                                        <span class="material-symbols-outlined notranslate text-xs" translate="no">verified</span>
                                        {{ $item->type_badge }}
                                    </span>
                                @endif
                            </td>
                            <td class="px-6 py-4">
                                <div class="font-bold text-xs text-slate-900 dark:text-white">{{ $item->customer_name }}</div>
                                <div class="text-[11px] text-slate-400 dark:text-slate-500 flex items-center gap-2 mt-0.5">
                                    <span>{{ $item->customer_phone }}</span>
                                    <span>·</span>
                                    <span class="truncate max-w-[180px]" title="{{ $item->customer_address }}">{{ $item->customer_address }}</span>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @if(!empty($item->reseller_name) || $item->reseller_id)
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md text-[11px] font-semibold bg-emerald-50 text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800" title="{{ $item->reseller_id ? 'ID Reseller: #' . $item->reseller_id : '' }}">
                                        <span class="material-symbols-outlined notranslate text-xs" translate="no">storefront</span>
                                        {{ $item->reseller_name ?: ('Reseller #' . $item->reseller_id) }}
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md text-[11px] font-semibold bg-slate-100 text-slate-700 dark:bg-slate-700 dark:text-slate-300">
                                        <span class="material-symbols-outlined notranslate text-xs" translate="no">domain</span>
                                        Direct (Kantor)
                                    </span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center gap-1.5">
                                    <div class="w-6 h-6 rounded-full bg-slate-100 dark:bg-slate-700 flex items-center justify-center text-slate-600 dark:text-slate-300 text-[10px] font-bold">
                                        <span class="material-symbols-outlined notranslate text-xs" translate="no">person</span>
                                    </div>
                                    <span class="text-xs font-semibold text-slate-700 dark:text-slate-300">{{ $item->technician_name }}</span>
                                </div>
                            </td>
                            <td class="px-6 py-4 text-xs">
                                <div class="font-medium text-slate-800 dark:text-slate-200">
                                    {{ $item->package_name }}
                                </div>
                                <div class="text-[11px] text-slate-400 font-mono mt-0.5">
                                    {{ $item->device_info }}
                                </div>
                            </td>
                            <td class="px-6 py-4 text-center whitespace-nowrap">
                                @if($item->is_completed)
                                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800 dark:bg-emerald-900/50 dark:text-emerald-300">
                                        <span class="material-symbols-outlined notranslate text-xs" translate="no">check_circle</span>
                                        {{ $item->status_label }}
                                    </span>
                                @elseif($item->status === 'pending_approval')
                                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-amber-100 text-amber-800 dark:bg-amber-900/50 dark:text-amber-300">
                                        <span class="material-symbols-outlined notranslate text-xs" translate="no">verified</span>
                                        Menunggu QC
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-slate-100 text-slate-800 dark:bg-slate-700 dark:text-slate-300">
                                        <span class="material-symbols-outlined notranslate text-xs" translate="no">schedule</span>
                                        {{ $item->status_label }}
                                    </span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-center whitespace-nowrap">
                                @if(!empty($item->sla_due_at))
                                    <x-sla.countdown 
                                        :due-at="$item->sla_due_at" 
                                        :started-at="$item->sla_started_at" 
                                        :sla-status="$item->sla_status ?? 'normal'" 
                                        :is-completed="$item->is_completed ?? false" 
                                    />
                                @else
                                    <span class="text-xs text-slate-400 dark:text-slate-500 font-mono">-</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-center whitespace-nowrap">
                                @if(!empty($item->is_qc_pending) || $item->status === 'pending_approval')
                                    <button wire:click="openQcInspection({{ $item->work_order_id ?? $item->raw_id }})" type="button"
                                            class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-bold bg-amber-500 hover:bg-amber-600 text-white shadow-sm transition-all focus:ring-2 focus:ring-amber-400">
                                        <span class="material-symbols-outlined notranslate text-xs" translate="no">verified</span>
                                        Periksa QC
                                    </button>
                                @elseif(!empty($item->work_order_id))
                                    <button wire:click="openQcInspection({{ $item->work_order_id }})" type="button"
                                            class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-[11px] font-semibold text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-700 transition-all">
                                        <span class="material-symbols-outlined notranslate text-xs" translate="no">visibility</span>
                                        Detail QC
                                    </button>
                                @else
                                    <span class="text-xs text-slate-300 dark:text-slate-600">-</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="px-6 py-12 text-center text-slate-500 dark:text-slate-400">
                                <span class="material-symbols-outlined notranslate text-4xl text-slate-300 dark:text-slate-600 mb-2 block" translate="no">history_edu</span>
                                Belum ada riwayat pemasangan atau perbaikan yang sesuai filter.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($items->hasPages())
            <div class="px-6 py-4 border-t border-slate-200 dark:border-slate-700 bg-slate-50/50 dark:bg-slate-900/30">
                {{ $items->links() }}
            </div>
        @endif
    </div>

    <!-- Modal Inspeksi QC (Unified Quality Control Modal) -->
    @if($showQcModal && $selectedWo)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/70 backdrop-blur-sm overflow-y-auto">
            <div class="bg-white dark:bg-slate-800 rounded-3xl shadow-2xl border border-slate-200 dark:border-slate-700 max-w-4xl w-full max-h-[90vh] flex flex-col overflow-hidden animate-in fade-in zoom-in-95 duration-150">
                
                {{-- Modal Header --}}
                <div class="px-6 py-4 border-b border-slate-200 dark:border-slate-700 flex items-center justify-between bg-slate-50/80 dark:bg-slate-900/50">
                    <div class="flex items-center gap-2.5">
                        <div class="w-10 h-10 rounded-2xl bg-amber-500/10 text-amber-600 dark:text-amber-400 flex items-center justify-center shrink-0">
                            <span class="material-symbols-outlined notranslate text-2xl" translate="no">verified</span>
                        </div>
                        <div>
                            <div class="flex items-center gap-2">
                                <h3 class="font-bold text-base text-slate-900 dark:text-white">
                                    Inspeksi QC SPK #{{ $selectedWo->id }}
                                </h3>
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-black uppercase tracking-wider
                                    {{ $selectedWo->status === 'pending_approval' ? 'bg-amber-100 text-amber-800 dark:bg-amber-900/50 dark:text-amber-300' : ($selectedWo->status === 'completed' ? 'bg-emerald-100 text-emerald-800 dark:bg-emerald-900/50 dark:text-emerald-300' : 'bg-slate-100 text-slate-700') }}">
                                    {{ $selectedWo->status }}
                                </span>
                                @if(!empty($selectedWo->sla_due_at))
                                    <x-sla.countdown 
                                        :due-at="$selectedWo->sla_due_at" 
                                        :started-at="$selectedWo->sla_started_at ?: $selectedWo->created_at" 
                                        :sla-status="$selectedWo->sla_status ?? 'normal'" 
                                        :is-completed="in_array($selectedWo->status, ['completed', 'cancelled'])" 
                                    />
                                @endif
                            </div>
                            <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">
                                {{ $selectedWo->title }} · {{ $selectedWo->created_at->format('d/m/Y H:i') }} WIB
                            </p>
                        </div>
                    </div>
                    <button wire:click="closeQcModal" type="button" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 p-2 rounded-xl hover:bg-slate-100 dark:hover:bg-slate-700 transition-colors">
                        <span class="material-symbols-outlined notranslate" translate="no">close</span>
                    </button>
                </div>

                {{-- Modal Body (Scrollable) --}}
                <div class="p-6 overflow-y-auto space-y-6 flex-1 text-slate-800 dark:text-slate-200">
                    
                    {{-- 1. Pelanggan & Teknisi Overview Grid --}}
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        {{-- Data Pelanggan --}}
                        <div class="bg-slate-50 dark:bg-slate-900/50 p-4 rounded-2xl border border-slate-200/80 dark:border-slate-700/60">
                            <h4 class="text-xs font-bold uppercase tracking-wider text-slate-400 mb-2 flex items-center gap-1.5">
                                <span class="material-symbols-outlined notranslate text-xs text-primary-500" translate="no">person</span>
                                Informasi Pelanggan & Lokasi
                            </h4>
                            <p class="font-bold text-sm text-slate-900 dark:text-white">
                                {{ $selectedWo->customer?->name ?? 'Pelanggan SPK #' . $selectedWo->id }}
                            </p>
                            <div class="mt-1 space-y-1 text-xs text-slate-600 dark:text-slate-300">
                                <div class="flex items-center gap-2">
                                    <span class="material-symbols-outlined notranslate text-xs text-slate-400" translate="no">call</span>
                                    <span>{{ $selectedWo->customer?->phone ?? '-' }}</span>
                                    @if($selectedWo->customer?->phone)
                                        <a href="https://wa.me/{{ preg_replace('/\D/', '', $selectedWo->customer->phone) }}" target="_blank" class="text-emerald-600 dark:text-emerald-400 font-bold hover:underline ml-1">WhatsApp</a>
                                    @endif
                                </div>
                                <div class="flex items-start gap-2">
                                    <span class="material-symbols-outlined notranslate text-xs text-slate-400 shrink-0 mt-0.5" translate="no">location_on</span>
                                    <span>{{ $selectedWo->customer?->address ?? ($selectedWo->installation?->installation_address ?? 'Alamat tidak tersedia') }}</span>
                                </div>
                                <div class="flex items-center gap-2 text-[11px] text-slate-400 pt-1">
                                    <span>Cabang: <b>{{ $selectedWo->branch?->name ?? 'Pusat' }}</b></span>
                                    @if($selectedWo->reseller)
                                        <span>·</span>
                                        <span>Reseller: <b>{{ $selectedWo->reseller->name }}</b></span>
                                    @endif
                                </div>
                            </div>
                        </div>

                        {{-- Data Teknisi & Pengukuran Optik --}}
                        <div class="bg-slate-50 dark:bg-slate-900/50 p-4 rounded-2xl border border-slate-200/80 dark:border-slate-700/60">
                            <h4 class="text-xs font-bold uppercase tracking-wider text-slate-400 mb-2 flex items-center gap-1.5">
                                <span class="material-symbols-outlined notranslate text-xs text-primary-500" translate="no">engineering</span>
                                Teknisi & Pengukuran Optik
                            </h4>
                            <div class="space-y-2 text-xs">
                                <div>
                                    <span class="text-slate-500 dark:text-slate-400">Teknisi Pelaksana:</span>
                                    <span class="font-bold text-slate-900 dark:text-white block">{{ $selectedWo->technician?->name ?? 'Teknisi Lapangan' }}</span>
                                </div>
                                <div class="p-3 rounded-xl bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700">
                                    <div class="text-[11px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-1">Hasil Optical Power Meter (OPM)</div>
                                    <div class="flex items-center justify-between">
                                        <div>
                                            <span class="text-xs text-slate-400">RX Power:</span>
                                            <span class="font-black text-sm ml-1 {{ ($selectedWo->optical_rx_power <= -16 && $selectedWo->optical_rx_power >= -24) ? 'text-emerald-600' : 'text-rose-600' }}">
                                                {{ $selectedWo->optical_rx_power ?? ($selectedWo->installation?->optical_power ?? '-') }} dBm
                                            </span>
                                        </div>
                                        <div>
                                            <span class="text-xs text-slate-400">TX Power:</span>
                                            <span class="font-black text-sm ml-1 text-slate-700 dark:text-slate-200">
                                                {{ $selectedWo->optical_tx_power ?? '-' }} dBm
                                            </span>
                                        </div>
                                    </div>
                                    @if(!empty($selectedWo->optical_rx_power))
                                        <div class="mt-1 text-[11px] font-semibold {{ ($selectedWo->optical_rx_power <= -16 && $selectedWo->optical_rx_power >= -24) ? 'text-emerald-600' : 'text-amber-600' }}">
                                            @if($selectedWo->optical_rx_power <= -16 && $selectedWo->optical_rx_power >= -24)
                                                ✓ Redaman Sesuai Standar (-16 s/d -24 dBm)
                                            @elseif($selectedWo->optical_rx_power < -24)
                                                ⚠️ Redaman Marginal/Tinggi (Perlu dicek ulang)
                                            @endif
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- 2. Checklist Pekerjaan Lapangan --}}
                    <div>
                        <h4 class="text-xs font-bold uppercase tracking-wider text-slate-400 mb-2 flex items-center gap-1.5">
                            <span class="material-symbols-outlined notranslate text-xs text-primary-500" translate="no">checklist</span>
                            Checklist Kelayakan Pekerjaan
                        </h4>
                        @if($selectedWo->checklists && $selectedWo->checklists->count() > 0)
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                                @foreach($selectedWo->checklists as $chk)
                                    <div class="flex items-center gap-2 p-2.5 rounded-xl bg-slate-50 dark:bg-slate-900/50 border border-slate-200/60 dark:border-slate-700/60 text-xs">
                                        <span class="material-symbols-outlined notranslate text-sm {{ $chk->completed ? 'text-emerald-500' : 'text-slate-400' }}" translate="no">
                                            {{ $chk->completed ? 'check_circle' : 'radio_button_unchecked' }}
                                        </span>
                                        <span class="{{ $chk->completed ? 'font-semibold text-slate-800 dark:text-slate-200' : 'text-slate-400 line-through' }}">
                                            {{ $chk->item }}
                                        </span>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <div class="p-3 rounded-xl bg-slate-50 dark:bg-slate-900/50 border border-slate-200/60 dark:border-slate-700/60 text-xs text-slate-400">
                                Checklist default terverifikasi oleh sistem.
                            </div>
                        @endif
                    </div>

                    {{-- 3. Galeri Foto Eviden --}}
                    <div>
                        <h4 class="text-xs font-bold uppercase tracking-wider text-slate-400 mb-2 flex items-center gap-1.5">
                            <span class="material-symbols-outlined notranslate text-xs text-primary-500" translate="no">photo_camera</span>
                            Dokumentasi & Foto Eviden Lapangan
                        </h4>
                        @php
                            $photos = is_array($selectedWo->evidence_photos) ? $selectedWo->evidence_photos : (is_array($selectedWo->installation?->photos) ? $selectedWo->installation->photos : []);
                        @endphp
                        @if(count($photos) > 0)
                            <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-3">
                                @foreach($photos as $idx => $photo)
                                    <a href="{{ asset('storage/' . $photo) }}" target="_blank" class="group relative rounded-2xl overflow-hidden border border-slate-200 dark:border-slate-700 aspect-video bg-slate-100 dark:bg-slate-900 block hover:shadow-md transition-all">
                                        <img src="{{ asset('storage/' . $photo) }}" alt="Eviden {{ $idx+1 }}" class="w-full h-full object-cover group-hover:scale-105 transition-transform" onerror="this.src='https://placehold.co/400x300?text=Eviden+Foto';">
                                        <div class="absolute inset-0 bg-slate-900/40 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center text-white text-xs font-bold gap-1">
                                            <span class="material-symbols-outlined notranslate text-sm" translate="no">zoom_in</span>
                                            Lihat Foto
                                        </div>
                                    </a>
                                @endforeach
                            </div>
                        @else
                            <div class="p-4 rounded-2xl bg-slate-50 dark:bg-slate-900/50 border border-slate-200/60 dark:border-slate-700/60 text-xs text-slate-400 text-center">
                                <span class="material-symbols-outlined notranslate text-2xl text-slate-300 dark:text-slate-600 mb-1 block" translate="no">no_photography</span>
                                Belum ada foto eviden yang diunggah oleh teknisi.
                            </div>
                        @endif
                    </div>

                    {{-- 4. Catatan Teknisi & Riwayat Audit Log --}}
                    <div class="space-y-3">
                        @if($selectedWo->notes)
                            <div class="p-3.5 rounded-2xl bg-amber-50 dark:bg-amber-950/30 border border-amber-200 dark:border-amber-900/50 text-xs">
                                <span class="font-bold text-amber-900 dark:text-amber-200 block mb-1">Catatan Penyelesaian Teknisi / Histori:</span>
                                <p class="text-amber-800 dark:text-amber-300 whitespace-pre-line">{{ $selectedWo->notes }}</p>
                            </div>
                        @endif

                        @if($selectedWo->auditLogs && $selectedWo->auditLogs->count() > 0)
                            <div class="border-t border-slate-100 dark:border-slate-700/60 pt-3">
                                <h5 class="text-[11px] font-bold uppercase tracking-wider text-slate-400 mb-2">Riwayat Inspeksi / Audit Log:</h5>
                                <div class="space-y-1.5">
                                    @foreach($selectedWo->auditLogs->take(3) as $log)
                                        <div class="text-[11px] text-slate-500 dark:text-slate-400 flex items-center justify-between bg-slate-50 dark:bg-slate-900/30 px-3 py-1.5 rounded-xl">
                                            <span><b>{{ $log->event }}</b>: {{ $log->notes }}</span>
                                            <span>{{ $log->created_at->format('d/m/Y H:i') }} WIB</span>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endif
                    </div>

                    {{-- 5. Form Aksi Approval / Catatan Revisi --}}
                    @if($selectedWo->status === 'pending_approval' || $selectedWo->status === 'in_progress')
                        <div class="p-4 rounded-2xl bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 space-y-3">
                            <label class="block text-xs font-bold text-slate-700 dark:text-slate-200">
                                Catatan Persetujuan / Alasan Penolakan:
                            </label>
                            <textarea wire:model="qcNotes" rows="2" placeholder="Tuliskan catatan verifikasi QC atau instruksi perbaikan jika ditolak..."
                                      class="w-full px-3 py-2 text-xs rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-200 focus:ring-2 focus:ring-primary-500"></textarea>
                            @error('qcNotes')
                                <span class="text-rose-500 text-xs font-semibold">{{ $message }}</span>
                            @enderror

                            <div class="flex items-center justify-end gap-3 pt-2">
                                <button wire:click="rejectQc" wire:loading.attr="disabled" type="button"
                                        class="px-4 py-2 rounded-xl text-xs font-bold bg-rose-600 hover:bg-rose-700 text-white transition-all inline-flex items-center gap-1.5 shadow-sm disabled:opacity-50">
                                    <span class="material-symbols-outlined notranslate text-xs" translate="no">cancel</span>
                                    Tolak & Minta Revisi
                                </button>
                                <button wire:click="approveQc" wire:loading.attr="disabled" type="button"
                                        class="px-5 py-2 rounded-xl text-xs font-bold bg-emerald-600 hover:bg-emerald-700 text-white transition-all inline-flex items-center gap-1.5 shadow-sm disabled:opacity-50">
                                    <span class="material-symbols-outlined notranslate text-xs" translate="no">check_circle</span>
                                    Setujui Pekerjaan (QC Passed)
                                </button>
                            </div>
                        </div>
                    @endif
                </div>

                {{-- Modal Footer --}}
                <div class="px-6 py-3 border-t border-slate-200 dark:border-slate-700 flex justify-end bg-slate-50 dark:bg-slate-900/50">
                    <button wire:click="closeQcModal" type="button" class="px-4 py-2 text-xs font-bold text-slate-600 dark:text-slate-300 hover:bg-slate-200 dark:hover:bg-slate-700 rounded-xl transition-colors">
                        Tutup
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>
