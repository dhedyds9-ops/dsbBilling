<div class="p-6 bg-slate-50 dark:bg-slate-900/30 min-h-screen">

    <!-- Page Header -->
          @section('page_title')
        <div>
            <h1 class="text-1xl font-bold text-slate-900 dark:text-white">Dashboard</h1>
            <span class="text-xs text-slate-500 dark:text-slate-400">Last Update:</span>
            <span class="text-xs font-semibold text-slate-700 dark:text-slate-300">{{ now()->format('d/m/Y H:i') }}</span>

        </div>
        @endsection
    

    <!-- Tab Navigation -->
    <div class="mb-6 flex items-center gap-1 bg-white dark:bg-slate-800 p-1 rounded-lg border border-slate-200 dark:border-slate-700 inline-flex overflow-x-auto">
        <button wire:click="setTab('ringkasan')" type="button"
                class="px-4 py-2 rounded-md text-sm font-semibold transition-colors whitespace-nowrap
                       {{ $activeTab === 'ringkasan' ? 'bg-primary-600 text-white shadow-sm' : 'text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:bg-slate-800 dark:hover:bg-slate-700' }}">
            Ringkasan
        </button>
        <button wire:click="setTab('tagihan')" type="button"
                class="px-4 py-2 rounded-md text-sm font-semibold transition-colors whitespace-nowrap
                       {{ $activeTab === 'tagihan' ? 'bg-primary-600 text-white shadow-sm' : 'text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:bg-slate-800 dark:hover:bg-slate-700' }}">
            Tagihan
        </button>
        <button wire:click="setTab('aktivitas')" type="button"
                class="px-4 py-2 rounded-md text-sm font-semibold transition-colors whitespace-nowrap
                       {{ $activeTab === 'aktivitas' ? 'bg-primary-600 text-white shadow-sm' : 'text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:bg-slate-800 dark:hover:bg-slate-700' }}">
            Aktivitas
        </button>
    </div>

    <!-- Top Stat Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        <!-- Income Hari Ini -->
        <div class="bg-white dark:bg-slate-800 rounded-xl shadow-sm border border-slate-100 dark:border-slate-700 p-5">
            <div class="flex items-start justify-between">
                <div>
                    <p class="text-[11px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Pendapatan Hari Ini</p>
                    <p class="text-2xl font-extrabold text-emerald-600 dark:text-emerald-400 mt-2">
                        Rp {{ number_format($mixData['income_hari_ini'] ?? 0, 0, ',', '.') }}
                    </p>
                    <p class="text-xs text-slate-400 dark:text-slate-500 dark:text-slate-400 mt-1">Tanggal: {{ today()->format('d M Y') }}</p>
                </div>
                <div class="p-3 rounded-xl bg-emerald-100 dark:bg-emerald-900/30 text-emerald-600 dark:text-emerald-400">
                    <span class="material-symbols-outlined text-2xl">payments</span>
                </div>
            </div>
        </div>

        <!-- Tagihan Belum Dibayar -->
        <div class="bg-white dark:bg-slate-800 rounded-xl shadow-sm border border-slate-100 dark:border-slate-700 p-5">
            <div class="flex items-start justify-between">
                <div>
                    <p class="text-[11px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Tagihan Tertunggak</p>
                    <p class="text-2xl font-extrabold text-amber-600 dark:text-amber-400 mt-2">
                        {{ $mixData['invoice_data_tagihan'] ?? 0 }}
                        <span class="text-sm font-medium text-slate-400 ml-1">unit</span>
                    </p>
                    <p class="text-xs text-amber-500 mt-1 font-semibold">
                        Lewat tempo: {{ $mixData['jatuh_tempo'] ?? 0 }}
                    </p>
                </div>
                <div class="p-3 rounded-xl bg-amber-100 dark:bg-amber-900/30 text-amber-600 dark:text-amber-400">
                    <span class="material-symbols-outlined text-2xl">receipt_long</span>
                </div>
            </div>
        </div>

        <!-- Pelanggan Aktif -->
        <div class="bg-white dark:bg-slate-800 rounded-xl shadow-sm border border-slate-100 dark:border-slate-700 p-5">
            <div class="flex items-start justify-between">
                <div>
                    <p class="text-[11px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Total Pelanggan</p>
                    <p class="text-2xl font-extrabold text-sky-600 dark:text-sky-400 mt-2">
                        {{ ($mixData['hotspot_user'] ?? 0) + ($mixData['pppoe_user'] ?? 0) }}
                    </p>
                    <div class="flex flex-col gap-1 mt-1 text-xs font-medium">
                        <div class="flex items-center gap-2">
                            <span class="text-slate-500">PPPoE: <span class="text-slate-700 dark:text-slate-200">{{ $mixData['pppoe_user'] ?? 0 }}</span></span>
                            <span class="px-1.5 py-0.5 rounded-full bg-emerald-100 text-emerald-700 dark:bg-emerald-900/50 dark:text-emerald-400 text-[9px] font-bold">{{ $mixData['ppp_online'] ?? 0 }} Online</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="text-slate-500">Hotspot: <span class="text-slate-700 dark:text-slate-200">{{ $mixData['hotspot_user'] ?? 0 }}</span></span>
                            <span class="px-1.5 py-0.5 rounded-full bg-emerald-100 text-emerald-700 dark:bg-emerald-900/50 dark:text-emerald-400 text-[9px] font-bold">{{ $mixData['hotspot_online'] ?? 0 }} Online</span>
                        </div>
                    </div>
                </div>
                <div class="p-3 rounded-xl bg-sky-100 dark:bg-sky-900/30 text-sky-600 dark:text-sky-400">
                    <span class="material-symbols-outlined text-2xl">group</span>
                </div>
            </div>
        </div>

        <!-- Expired / Suspended -->
        <div class="bg-white dark:bg-slate-800 rounded-xl shadow-sm border border-slate-100 dark:border-slate-700 p-5">
            <div class="flex items-start justify-between">
                <div>
                    <p class="text-[11px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Expired / Suspend</p>
                    <p class="text-2xl font-extrabold text-rose-600 dark:text-rose-400 mt-2">
                        {{ ($mixData['exp_voucher'] ?? 0) + ($mixData['exp_customer'] ?? 0) }}
                    </p>
                    <div class="flex gap-3 mt-1 text-xs font-medium">
                        <span class="text-slate-500">Voucher: <span class="text-slate-700 dark:text-slate-200">{{ $mixData['exp_voucher'] ?? 0 }}</span></span>
                        <span class="text-slate-500">Cust: <span class="text-slate-700 dark:text-slate-200">{{ $mixData['exp_customer'] ?? 0 }}</span></span>
                    </div>
                </div>
                <div class="p-3 rounded-xl bg-rose-100 dark:bg-rose-900/30 text-rose-600 dark:text-rose-400">
                    <span class="material-symbols-outlined text-2xl">warning</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Voucher Grid (sesuai blade lama yang dipertahankan) -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 mb-6">
        <div class="bg-slate-50 dark:bg-slate-800/50 rounded-xl p-4 flex items-center gap-3 border border-slate-100 dark:border-slate-700">
            <div class="p-2.5 bg-slate-200 dark:bg-slate-700 rounded-lg">
                <x-icon name="ticket" class="w-6 h-6 text-slate-600 dark:text-slate-300" />
            </div>
            <div class="flex-1 min-w-0">
                <p class="text-[11px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wide">TOTAL VOUCHER</p>
                <p class="text-lg font-bold flex items-center gap-1 mt-1">
                    <x-icon name="ticket" class="w-4 h-4 text-slate-400" /> {{ $mixData['total_voucher'] ?? 0 }}
                </p>
            </div>
        </div>
        <div class="bg-slate-50 dark:bg-slate-800/50 rounded-xl p-4 flex items-center gap-3 border border-slate-100 dark:border-slate-700">
            <div class="p-2.5 bg-slate-200 dark:bg-slate-700 rounded-lg">
                <x-icon name="printer" class="w-6 h-6 text-slate-600 dark:text-slate-300" />
            </div>
            <div class="flex-1 min-w-0">
                <p class="text-[11px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wide">Voucher Dibuat Hari Ini</p>
                <p class="text-lg font-bold flex items-center gap-1 mt-1">
                    <x-icon name="document-plus" class="w-4 h-4 text-slate-400" /> {{ $mixData['vc_created_today'] ?? 0 }}
                </p>
            </div>
        </div>
        <div class="bg-slate-50 dark:bg-slate-800/50 rounded-xl p-4 flex items-center gap-3 border border-slate-100 dark:border-slate-700">
            <div class="p-2.5 bg-slate-200 dark:bg-slate-700 rounded-lg">
                <x-icon name="arrow-right-on-rectangle" class="w-6 h-6 text-slate-600 dark:text-slate-300" />
            </div>
            <div class="flex-1 min-w-0">
                <p class="text-[11px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wide">Voucher Aktif (Online)</p>
                <p class="text-lg font-bold flex items-center gap-1 mt-1">
                    <x-icon name="arrow-right-on-rectangle" class="w-4 h-4 text-slate-400" /> {{ $mixData['vc_login_today'] ?? 0 }}
                </p>
            </div>
        </div>
    </div>

    <!-- Tab Content -->
    @if($activeTab === 'ringkasan')
    <!-- Ringkasan: Recent Incomes -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <div class="bg-white dark:bg-slate-800 rounded-xl shadow-sm border border-slate-100 dark:border-slate-700">
            <div class="p-4 border-b border-slate-100 dark:border-slate-700 flex items-center justify-between">
                <h2 class="text-sm font-bold text-slate-800 dark:text-white">Pendapatan Terbaru</h2>
                <span class="text-[11px] px-2 py-1 rounded-full bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400">
                    Paid/Success
                </span>
            </div>
            <div class="max-h-[420px] overflow-y-auto">
                @if($recentIncomes && $recentIncomes->count() > 0)
                    <table class="w-full text-sm">
                        <thead class="bg-slate-50 dark:bg-slate-700/40 sticky top-0">
                            <tr>
                                <th class="text-left px-4 py-2.5 text-[11px] font-bold text-slate-500 dark:text-slate-400 uppercase">Waktu</th>
                                <th class="text-left px-4 py-2.5 text-[11px] font-bold text-slate-500 dark:text-slate-400 uppercase">Pelanggan</th>
                                <th class="text-right px-4 py-2.5 text-[11px] font-bold text-slate-500 dark:text-slate-400 uppercase">Jumlah</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-slate-700/60">
                            @foreach($recentIncomes as $p)
                                <tr class="hover:bg-slate-50 dark:bg-slate-900/50/70 dark:hover:bg-slate-700/30">
                                    <td class="px-4 py-3 text-xs text-slate-500 dark:text-slate-400 whitespace-nowrap">
                                        {{ $p->paid_at?->format('d M, H:i') ?? $p->created_at?->format('d M, H:i') }}
                                    </td>
                                    <td class="px-4 py-3 min-w-0">
                                        <p class="text-sm font-semibold text-slate-800 dark:text-white truncate">
                                            {{ $p->customer?->name ?? '-' }}
                                        </p>
                                        <p class="text-[11px] text-slate-500 dark:text-slate-400 truncate">
                                            #{{ $p->customer?->customer_id ?? '-' }} · {{ $p->customer?->service_type ?? 'hotspot' }}
                                        </p>
                                    </td>
                                    <td class="px-4 py-3 text-sm font-bold text-emerald-600 dark:text-emerald-400 whitespace-nowrap text-right">
                                        Rp {{ number_format($p->amount ?? 0, 0, ',', '.') }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @else
                    <div class="p-10 text-center">
                        <span class="material-symbols-outlined text-5xl text-slate-300 dark:text-slate-600 dark:text-slate-400">inbox</span>
                        <p class="mt-3 text-sm font-semibold text-slate-600 dark:text-slate-400">Belum ada pendapatan hari ini</p>
                    </div>
                @endif
            </div>
        </div>

        <div class="bg-white dark:bg-slate-800 rounded-xl shadow-sm border border-slate-100 dark:border-slate-700">
            <div class="p-4 border-b border-slate-100 dark:border-slate-700 flex items-center justify-between">
                <h2 class="text-sm font-bold text-slate-800 dark:text-white">Aktivitas Terbaru</h2>
                <span class="text-[11px] px-2 py-1 rounded-full bg-primary-100 text-primary-700 dark:bg-primary-900/30 dark:text-primary-400">
                    Live Feed
                </span>
            </div>
            <div class="max-h-[420px] overflow-y-auto p-4 space-y-3">
                @if($activities && count($activities) > 0)
                    @foreach($activities as $act)
                        <div class="flex gap-3">
                            <div class="mt-0.5 w-8 h-8 rounded-full bg-emerald-100 dark:bg-emerald-900/30 text-emerald-600 dark:text-emerald-400 flex items-center justify-center shrink-0">
                                <span class="material-symbols-outlined text-base">check_circle</span>
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="text-sm text-slate-700 dark:text-slate-300 leading-relaxed">{!! $act['message'] !!}</p>
                                <p class="text-[11px] text-slate-400 dark:text-slate-500 dark:text-slate-400 mt-0.5">{{ $act['time'] }}</p>
                            </div>
                        </div>
                    @endforeach
                @else
                    <div class="py-10 text-center">
                        <span class="material-symbols-outlined text-5xl text-slate-300 dark:text-slate-600 dark:text-slate-400">hourglass_empty</span>
                        <p class="mt-3 text-sm font-semibold text-slate-600 dark:text-slate-400">Belum ada aktivitas</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
    @endif

    @if($activeTab === 'tagihan')
    <!-- Tagihan: Recent Unpaid Invoices -->
    <div class="bg-white dark:bg-slate-800 rounded-xl shadow-sm border border-slate-100 dark:border-slate-700">
        <div class="p-4 border-b border-slate-100 dark:border-slate-700 flex items-center justify-between">
            <h2 class="text-sm font-bold text-slate-800 dark:text-white">Tagihan Belum Dibayar</h2>
            <span class="text-[11px] px-2 py-1 rounded-full bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-400 font-semibold">
                {{ $recentInvoices?->count() ?? 0 }} Unit
            </span>
        </div>
        <div class="overflow-x-auto">
            @if($recentInvoices && $recentInvoices->count() > 0)
                <table class="w-full text-sm">
                    <thead class="bg-slate-50 dark:bg-slate-700/40">
                        <tr>
                            <th class="text-left px-4 py-2.5 text-[11px] font-bold text-slate-500 dark:text-slate-400 uppercase">Invoice</th>
                            <th class="text-left px-4 py-2.5 text-[11px] font-bold text-slate-500 dark:text-slate-400 uppercase">Pelanggan</th>
                            <th class="text-left px-4 py-2.5 text-[11px] font-bold text-slate-500 dark:text-slate-400 uppercase">Periode</th>
                            <th class="text-left px-4 py-2.5 text-[11px] font-bold text-slate-500 dark:text-slate-400 uppercase">Jatuh Tempo</th>
                            <th class="text-right px-4 py-2.5 text-[11px] font-bold text-slate-500 dark:text-slate-400 uppercase">Total</th>
                            <th class="text-center px-4 py-2.5 text-[11px] font-bold text-slate-500 dark:text-slate-400 uppercase">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-700/60">
                        @foreach($recentInvoices as $inv)
                            @php
                                $isOverdue = $inv->due_date && $inv->due_date->isPast();
                            @endphp
                            <tr class="hover:bg-slate-50 dark:bg-slate-900/50/70 dark:hover:bg-slate-700/30">
                                <td class="px-4 py-3 font-mono text-xs font-bold text-slate-700 dark:text-slate-200 whitespace-nowrap">
                                    {{ $inv->invoice_number ?? '-' }}
                                </td>
                                <td class="px-4 py-3 min-w-0">
                                    <p class="text-sm font-semibold text-slate-800 dark:text-white truncate">
                                        {{ $inv->customer?->name ?? '-' }}
                                    </p>
                                    <p class="text-[11px] text-slate-500 dark:text-slate-400 truncate">
                                        #{{ $inv->customer?->customer_id ?? '-' }}
                                    </p>
                                </td>
                                <td class="px-4 py-3 text-xs text-slate-600 dark:text-slate-300 whitespace-nowrap">
                                    {{ $inv->period_start?->format('M Y') ?? '-' }}
                                </td>
                                <td class="px-4 py-3 text-xs whitespace-nowrap">
                                    <span class="{{ $isOverdue ? 'text-rose-600 dark:text-rose-400 font-semibold' : 'text-slate-600 dark:text-slate-300' }}">
                                        {{ $inv->due_date?->format('d M Y') ?? '-' }}
                                    </span>
                                    @if($isOverdue)
                                        <p class="text-[10px] font-bold text-rose-500 mt-0.5">TERLAMBAT</p>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-sm font-bold text-slate-800 dark:text-white whitespace-nowrap text-right">
                                    Rp {{ number_format($inv->total_amount ?? 0, 0, ',', '.') }}
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap text-center">
                                    <span class="inline-flex text-[11px] px-2 py-1 rounded-full font-bold
                                        {{ $isOverdue ? 'bg-rose-100 text-rose-700 dark:bg-rose-900/30 dark:text-rose-400' : 'bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-400' }}">
                                        {{ ucfirst($inv->status ?? 'unpaid') }}
                                    </span>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @else
                <div class="p-12 text-center">
                    <span class="material-symbols-outlined text-5xl text-slate-300 dark:text-slate-600 dark:text-slate-400">verified</span>
                    <p class="mt-3 text-sm font-semibold text-slate-600 dark:text-slate-400">Tidak ada tagihan tertunggak. Bagus!</p>
                </div>
            @endif
        </div>
    </div>
    @endif

    @if($activeTab === 'aktivitas')
    <!-- Aktivitas: Full activity feed -->
    <div class="bg-white dark:bg-slate-800 rounded-xl shadow-sm border border-slate-100 dark:border-slate-700">
        <div class="p-4 border-b border-slate-100 dark:border-slate-700">
            <h2 class="text-sm font-bold text-slate-800 dark:text-white">Feed Aktivitas</h2>
        </div>
        <div class="p-6 max-h-[600px] overflow-y-auto space-y-4">
            @if($activities && count($activities) > 0)
                @foreach($activities as $act)
                    <div class="flex gap-4 p-3 rounded-lg hover:bg-slate-50 dark:bg-slate-900/50 dark:hover:bg-slate-700/30 transition-colors">
                        <div class="w-10 h-10 rounded-full bg-emerald-100 dark:bg-emerald-900/30 text-emerald-600 dark:text-emerald-400 flex items-center justify-center shrink-0">
                            <span class="material-symbols-outlined text-xl">wifi</span>
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-sm text-slate-800 dark:text-slate-200 leading-relaxed">{!! $act['message'] !!}</p>
                            <p class="text-xs text-slate-400 dark:text-slate-500 dark:text-slate-400 mt-1">{{ $act['time'] }}</p>
                        </div>
                    </div>
                @endforeach
            @else
                <div class="py-12 text-center">
                    <span class="material-symbols-outlined text-5xl text-slate-300 dark:text-slate-600 dark:text-slate-400">activity</span>
                    <p class="mt-3 text-sm font-semibold text-slate-600 dark:text-slate-400">Belum ada aktivitas yang tercatat</p>
                </div>
            @endif
        </div>
    </div>
    @endif

</div>
