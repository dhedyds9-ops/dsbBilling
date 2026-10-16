<div class="p-6 bg-slate-50 dark:bg-slate-900/30 min-h-screen">

    <!-- Page Header -->
    @section('page_title')
    <div class="flex items-center justify-between w-full">
        <div>
            <h1 class="text-xl font-bold text-slate-900 dark:text-white">Dashboard Reseller</h1>
            <div class="flex items-center gap-1.5 mt-0.5 text-xs text-slate-500 dark:text-slate-400">
                <span>Update Terakhir:</span>
                <span class="font-semibold text-slate-700 dark:text-slate-300">{{ now()->format('d/m/Y H:i') }}</span>
            </div>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('reseller-portal.finance.expenses') }}" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-xs font-semibold text-slate-700 dark:text-slate-200 hover:bg-slate-50 dark:hover:bg-slate-700 transition-colors shadow-sm">
                <span class="material-symbols-outlined notranslate text-base text-rose-500" translate="no">receipt_long</span>
                Catat Pengeluaran
            </a>
            <a href="{{ route('reseller-portal.finance.topup') }}" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-semibold transition-colors shadow-sm">
                <span class="material-symbols-outlined notranslate text-base" translate="no">add_card</span>
                Top Up Saldo
            </a>
        </div>
    </div>
    @endsection

    <!-- Tab Navigation -->
    <div class="mb-6 flex items-center gap-1 bg-white dark:bg-slate-800 p-1 rounded-lg border border-slate-200 dark:border-slate-700 inline-flex overflow-x-auto shadow-sm">
        <button wire:click="setTab('ringkasan')" type="button"
                class="px-4 py-2 rounded-md text-sm font-semibold transition-colors whitespace-nowrap
                       {{ $activeTab === 'ringkasan' ? 'bg-primary-600 text-white shadow-sm' : 'text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-700' }}">
            Ringkasan
        </button>
        <button wire:click="setTab('tagihan')" type="button"
                class="px-4 py-2 rounded-md text-sm font-semibold transition-colors whitespace-nowrap
                       {{ $activeTab === 'tagihan' ? 'bg-primary-600 text-white shadow-sm' : 'text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-700' }}">
            Tagihan
        </button>
        <button wire:click="setTab('aktivitas')" type="button"
                class="px-4 py-2 rounded-md text-sm font-semibold transition-colors whitespace-nowrap
                       {{ $activeTab === 'aktivitas' ? 'bg-primary-600 text-white shadow-sm' : 'text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-700' }}">
            Aktivitas
        </button>
    </div>

    <!-- Top Stat Cards (Pelanggan vs Voucher Breakdown) -->
    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-5 gap-4 mb-6">
        <!-- Pendapatan Pelanggan -->
        <div class="bg-white dark:bg-slate-800 rounded-xl shadow-sm border border-slate-100 dark:border-slate-700 p-4 relative overflow-hidden group hover:shadow-md transition-all">
            <div class="absolute top-0 left-0 right-0 h-1 bg-gradient-to-r from-indigo-500 to-blue-500"></div>
            <div class="flex items-start justify-between">
                <div>
                    <span class="inline-flex items-center gap-1 text-[11px] font-bold text-indigo-600 dark:text-indigo-400 uppercase tracking-wider">
                        <span class="material-symbols-outlined notranslate text-sm" translate="no">account_circle</span>
                        Pelanggan
                    </span>
                    <p class="text-xl font-extrabold text-indigo-700 dark:text-indigo-300 mt-2">
                        Rp {{ number_format($mixData['income_pelanggan_hari_ini'] ?? 0, 0, ',', '.') }}
                    </p>
                    <p class="text-[11px] text-slate-500 dark:text-slate-400 mt-1">
                        Bulan ini: <span class="font-bold text-slate-700 dark:text-slate-200">Rp {{ number_format($mixData['income_pelanggan_bulan_ini'] ?? 0, 0, ',', '.') }}</span>
                    </p>
                </div>
                <div class="p-2.5 rounded-xl bg-indigo-50 dark:bg-indigo-900/40 text-indigo-600 dark:text-indigo-400">
                    <span class="material-symbols-outlined notranslate text-2xl" translate="no">supervised_user_circle</span>
                </div>
            </div>
            <div class="mt-2 pt-2 border-t border-slate-100 dark:border-slate-700/60 text-[10px] text-slate-400 flex items-center justify-between">
                <span>Tagihan PPPoE & Hotspot</span>
                <span class="font-semibold text-indigo-500">Hari ini</span>
            </div>
        </div>

        <!-- Pendapatan Voucher -->
        <div class="bg-white dark:bg-slate-800 rounded-xl shadow-sm border border-slate-100 dark:border-slate-700 p-4 relative overflow-hidden group hover:shadow-md transition-all">
            <div class="absolute top-0 left-0 right-0 h-1 bg-gradient-to-r from-emerald-500 to-teal-500"></div>
            <div class="flex items-start justify-between">
                <div>
                    <span class="inline-flex items-center gap-1 text-[11px] font-bold text-emerald-600 dark:text-emerald-400 uppercase tracking-wider">
                        <span class="material-symbols-outlined notranslate text-sm" translate="no">confirmation_number</span>
                        Voucher
                    </span>
                    <p class="text-xl font-extrabold text-emerald-600 dark:text-emerald-400 mt-2">
                        Rp {{ number_format($mixData['income_voucher_hari_ini'] ?? 0, 0, ',', '.') }}
                    </p>
                    <p class="text-[11px] text-slate-500 dark:text-slate-400 mt-1">
                        Bulan ini: <span class="font-bold text-slate-700 dark:text-slate-200">Rp {{ number_format($mixData['income_voucher_bulan_ini'] ?? 0, 0, ',', '.') }}</span>
                    </p>
                </div>
                <div class="p-2.5 rounded-xl bg-emerald-50 dark:bg-emerald-900/40 text-emerald-600 dark:text-emerald-400">
                    <span class="material-symbols-outlined notranslate text-2xl" translate="no">confirmation_number</span>
                </div>
            </div>
            <div class="mt-2 pt-2 border-t border-slate-100 dark:border-slate-700/60 text-[10px] text-slate-400 flex items-center justify-between">
                <span>Voucher & E-Voucher</span>
                <span class="font-semibold text-emerald-500">Hari ini</span>
            </div>
        </div>

        <!-- Tagihan Tertunggak -->
        <div class="bg-white dark:bg-slate-800 rounded-xl shadow-sm border border-slate-100 dark:border-slate-700 p-4 relative overflow-hidden group hover:shadow-md transition-all">
            <div class="absolute top-0 left-0 right-0 h-1 bg-gradient-to-r from-amber-500 to-orange-500"></div>
            <div class="flex items-start justify-between">
                <div>
                    <span class="inline-flex items-center gap-1 text-[11px] font-bold text-amber-600 dark:text-amber-400 uppercase tracking-wider">
                        <span class="material-symbols-outlined notranslate text-sm" translate="no">receipt_long</span>
                        Tagihan
                    </span>
                    <p class="text-xl font-extrabold text-amber-600 dark:text-amber-400 mt-2">
                        {{ number_format($mixData['invoice_data_tagihan'] ?? 0, 0, ',', '.') }}
                        <span class="text-xs font-medium text-slate-400 ml-1">unit</span>
                    </p>
                    <p class="text-[11px] text-amber-500 mt-1 font-semibold">
                        Lewat tempo: {{ number_format($mixData['jatuh_tempo'] ?? 0, 0, ',', '.') }}
                    </p>
                </div>
                <div class="p-2.5 rounded-xl bg-amber-50 dark:bg-amber-900/40 text-amber-600 dark:text-amber-400">
                    <span class="material-symbols-outlined notranslate text-2xl" translate="no">receipt_long</span>
                </div>
            </div>
            <div class="mt-2 pt-2 border-t border-slate-100 dark:border-slate-700/60 text-[10px] text-slate-400">
                <span>Total tagihan belum lunas</span>
            </div>
        </div>

        <!-- Total Pelanggan -->
        <div class="bg-white dark:bg-slate-800 rounded-xl shadow-sm border border-slate-100 dark:border-slate-700 p-4 relative overflow-hidden group hover:shadow-md transition-all">
            <div class="absolute top-0 left-0 right-0 h-1 bg-gradient-to-r from-sky-500 to-cyan-500"></div>
            <div class="flex items-start justify-between">
                <div>
                    <span class="inline-flex items-center gap-1 text-[11px] font-bold text-sky-600 dark:text-sky-400 uppercase tracking-wider">
                        <span class="material-symbols-outlined notranslate text-sm" translate="no">group</span>
                        Total Pelanggan
                    </span>
                    <p class="text-xl font-extrabold text-sky-600 dark:text-sky-400 mt-2">
                        {{ number_format(($mixData['hotspot_user'] ?? 0) + ($mixData['pppoe_user'] ?? 0), 0, ',', '.') }}
                    </p>
                    <div class="flex flex-col gap-0.5 mt-1 text-[11px] font-medium">
                        <span class="text-slate-500">PPPoE: <b class="text-slate-700 dark:text-slate-200">{{ number_format($mixData['pppoe_user'] ?? 0) }}</b> ({{ number_format($mixData['ppp_online'] ?? 0) }} On)</span>
                        <span class="text-slate-500">Hotspot: <b class="text-slate-700 dark:text-slate-200">{{ number_format($mixData['hotspot_user'] ?? 0) }}</b> ({{ number_format($mixData['hotspot_online'] ?? 0) }} On)</span>
                    </div>
                </div>
                <div class="p-2.5 rounded-xl bg-sky-50 dark:bg-sky-900/40 text-sky-600 dark:text-sky-400">
                    <span class="material-symbols-outlined notranslate text-2xl" translate="no">lan</span>
                </div>
            </div>
        </div>

        <!-- Expired / Suspended -->
        <div class="bg-white dark:bg-slate-800 rounded-xl shadow-sm border border-slate-100 dark:border-slate-700 p-4 relative overflow-hidden group hover:shadow-md transition-all">
            <div class="absolute top-0 left-0 right-0 h-1 bg-gradient-to-r from-rose-500 to-pink-500"></div>
            <div class="flex items-start justify-between">
                <div>
                    <span class="inline-flex items-center gap-1 text-[11px] font-bold text-rose-600 dark:text-rose-400 uppercase tracking-wider">
                        <span class="material-symbols-outlined notranslate text-sm" translate="no">warning</span>
                        Expired / Suspend
                    </span>
                    <p class="text-xl font-extrabold text-rose-600 dark:text-rose-400 mt-2">
                        {{ number_format(($mixData['exp_voucher'] ?? 0) + ($mixData['exp_customer'] ?? 0), 0, ',', '.') }}
                    </p>
                    <div class="flex gap-2 mt-1 text-[11px] font-medium">
                        <span class="text-slate-500">Vc: <b class="text-slate-700 dark:text-slate-200">{{ number_format($mixData['exp_voucher'] ?? 0) }}</b></span>
                        <span class="text-slate-500">Cust: <b class="text-slate-700 dark:text-slate-200">{{ number_format($mixData['exp_customer'] ?? 0) }}</b></span>
                    </div>
                </div>
                <div class="p-2.5 rounded-xl bg-rose-50 dark:bg-rose-900/40 text-rose-600 dark:text-rose-400">
                    <span class="material-symbols-outlined notranslate text-2xl" translate="no">warning</span>
                </div>
            </div>
            <div class="mt-2 pt-2 border-t border-slate-100 dark:border-slate-700/60 text-[10px] text-slate-400">
                <span>Perlu tindak lanjut / isi ulang</span>
            </div>
        </div>
    </div>

    <!-- Voucher Grid -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 mb-6">
        <div class="bg-white dark:bg-slate-800 rounded-xl p-4 flex items-center gap-3 border border-slate-100 dark:border-slate-700 shadow-sm">
            <div class="p-2.5 bg-indigo-100 dark:bg-indigo-900/40 text-indigo-600 dark:text-indigo-400 rounded-lg">
                <span class="material-symbols-outlined notranslate text-2xl" translate="no">confirmation_number</span>
            </div>
            <div class="flex-1 min-w-0">
                <p class="text-[11px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wide">TOTAL VOUCHER</p>
                <p class="text-xl font-extrabold text-slate-900 dark:text-white mt-0.5">
                    {{ number_format($mixData['total_voucher'] ?? 0, 0, ',', '.') }}
                </p>
            </div>
        </div>
        <div class="bg-white dark:bg-slate-800 rounded-xl p-4 flex items-center gap-3 border border-slate-100 dark:border-slate-700 shadow-sm">
            <div class="p-2.5 bg-amber-100 dark:bg-amber-900/40 text-amber-600 dark:text-amber-400 rounded-lg">
                <span class="material-symbols-outlined notranslate text-2xl" translate="no">print</span>
            </div>
            <div class="flex-1 min-w-0">
                <p class="text-[11px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wide">Voucher Dibuat Hari Ini</p>
                <p class="text-xl font-extrabold text-slate-900 dark:text-white mt-0.5">
                    {{ number_format($mixData['vc_created_today'] ?? 0, 0, ',', '.') }}
                </p>
            </div>
        </div>
        <div class="bg-white dark:bg-slate-800 rounded-xl p-4 flex items-center gap-3 border border-slate-100 dark:border-slate-700 shadow-sm">
            <div class="p-2.5 bg-emerald-100 dark:bg-emerald-900/40 text-emerald-600 dark:text-emerald-400 rounded-lg">
                <span class="material-symbols-outlined notranslate text-2xl" translate="no">login</span>
            </div>
            <div class="flex-1 min-w-0">
                <p class="text-[11px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wide">Voucher Online (Aktif)</p>
                <p class="text-xl font-extrabold text-emerald-600 dark:text-emerald-400 mt-0.5">
                    {{ number_format($mixData['vc_login_today'] ?? 0, 0, ',', '.') }}
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
                <span class="text-[11px] px-2 py-1 rounded-full bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400 font-semibold">
                    Paid / Success
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
                                @php
                                    $isVoucher = in_array($p->id, $voucherPaymentIds ?? []);
                                @endphp
                                <tr class="hover:bg-slate-50 dark:hover:bg-slate-700/30">
                                    <td class="px-4 py-3 text-xs text-slate-500 dark:text-slate-400 whitespace-nowrap">
                                        {{ $p->paid_at?->format('d M, H:i') ?? $p->created_at?->format('d M, H:i') }}
                                    </td>
                                    <td class="px-4 py-3 min-w-0">
                                        <div class="flex items-center gap-1.5">
                                            <p class="text-sm font-semibold text-slate-800 dark:text-white truncate">
                                                {{ $p->customer?->name ?? ($isVoucher ? 'Voucher Hotspot' : '-') }}
                                            </p>
                                            @if($isVoucher)
                                                <span class="px-1.5 py-0.5 rounded text-[9px] font-bold bg-emerald-100 text-emerald-700 dark:bg-emerald-900/50 dark:text-emerald-300">Voucher</span>
                                            @else
                                                <span class="px-1.5 py-0.5 rounded text-[9px] font-bold bg-indigo-100 text-indigo-700 dark:bg-indigo-900/50 dark:text-indigo-300">Pelanggan</span>
                                            @endif
                                        </div>
                                        <p class="text-[11px] text-slate-500 dark:text-slate-400 truncate">
                                            #{{ $p->reference_number ?? ($p->customer?->customer_id ?? '-') }} · {{ strtoupper($p->customer?->service_type ?? ($isVoucher ? 'VOUCHER' : 'HOTSPOT')) }}
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
                        <span class="material-symbols-outlined notranslate text-5xl text-slate-300 dark:text-slate-600" translate="no">inbox</span>
                        <p class="mt-3 text-sm font-semibold text-slate-600 dark:text-slate-400">Belum ada pendapatan yang tercatat</p>
                    </div>
                @endif
            </div>
        </div>

        <div class="bg-white dark:bg-slate-800 rounded-xl shadow-sm border border-slate-100 dark:border-slate-700">
            <div class="p-4 border-b border-slate-100 dark:border-slate-700 flex items-center justify-between">
                <h2 class="text-sm font-bold text-slate-800 dark:text-white">Aktivitas Terbaru</h2>
                <span class="text-[11px] px-2 py-1 rounded-full bg-primary-100 text-primary-700 dark:bg-primary-900/30 dark:text-primary-400 font-semibold">
                    Live Feed
                </span>
            </div>
            <div class="max-h-[420px] overflow-y-auto p-4 space-y-3">
                @if($activities && count($activities) > 0)
                    @foreach($activities as $act)
                        <div class="flex gap-3">
                            <div class="mt-0.5 w-8 h-8 rounded-full bg-emerald-100 dark:bg-emerald-900/30 text-emerald-600 dark:text-emerald-400 flex items-center justify-center shrink-0">
                                <span class="material-symbols-outlined notranslate text-base" translate="no">check_circle</span>
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="text-sm text-slate-700 dark:text-slate-300 leading-relaxed">{!! $act['message'] !!}</p>
                                <p class="text-[11px] text-slate-400 dark:text-slate-500 mt-0.5">{{ $act['time'] }}</p>
                            </div>
                        </div>
                    @endforeach
                @else
                    <div class="py-10 text-center">
                        <span class="material-symbols-outlined notranslate text-5xl text-slate-300 dark:text-slate-600" translate="no">hourglass_empty</span>
                        <p class="mt-3 text-sm font-semibold text-slate-600 dark:text-slate-400">Belum ada aktivitas pelanggan</p>
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
                {{ number_format($recentInvoices?->count() ?? 0, 0, ',', '.') }} Unit
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
                            <tr class="hover:bg-slate-50 dark:hover:bg-slate-700/30">
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
                    <span class="material-symbols-outlined notranslate text-5xl text-slate-300 dark:text-slate-600" translate="no">verified</span>
                    <p class="mt-3 text-sm font-semibold text-slate-600 dark:text-slate-400">Tidak ada tagihan tertunggak. Semua lunas!</p>
                </div>
            @endif
        </div>
    </div>
    @endif

    @if($activeTab === 'aktivitas')
    <!-- Aktivitas: Full activity feed -->
    <div class="bg-white dark:bg-slate-800 rounded-xl shadow-sm border border-slate-100 dark:border-slate-700">
        <div class="p-4 border-b border-slate-100 dark:border-slate-700">
            <h2 class="text-sm font-bold text-slate-800 dark:text-white">Feed Aktivitas Pelanggan</h2>
        </div>
        <div class="p-6 max-h-[600px] overflow-y-auto space-y-4">
            @if($activities && count($activities) > 0)
                @foreach($activities as $act)
                    <div class="flex gap-4 p-3 rounded-lg hover:bg-slate-50 dark:hover:bg-slate-700/30 transition-colors">
                        <div class="w-10 h-10 rounded-full bg-emerald-100 dark:bg-emerald-900/30 text-emerald-600 dark:text-emerald-400 flex items-center justify-center shrink-0">
                            <span class="material-symbols-outlined notranslate text-xl" translate="no">wifi</span>
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-sm text-slate-800 dark:text-slate-200 leading-relaxed">{!! $act['message'] !!}</p>
                            <p class="text-xs text-slate-400 dark:text-slate-500 mt-1">{{ $act['time'] }}</p>
                        </div>
                    </div>
                @endforeach
            @else
                <div class="py-12 text-center">
                    <span class="material-symbols-outlined notranslate text-5xl text-slate-300 dark:text-slate-600" translate="no">history</span>
                    <p class="mt-3 text-sm font-semibold text-slate-600 dark:text-slate-400">Belum ada aktivitas yang tercatat</p>
                </div>
            @endif
        </div>
    </div>
    @endif

</div>
