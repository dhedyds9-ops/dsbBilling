<div class="space-y-6 pb-10" wire:poll.30s="loadData">
    {{-- A. HEADER SECTION --}}
    <div class="flex flex-col md:flex-row items-start md:items-center justify-between gap-4">
        <div class="flex items-center gap-3">
            <div class="hidden md:flex items-center gap-2 px-3 py-1.5 bg-green-50 dark:bg-green-900/20 text-green-700 dark:text-green-400 rounded-lg text-sm font-medium border border-green-200 dark:border-green-800">
                <span class="relative flex h-2 w-2">
                  <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-green-400 opacity-75"></span>
                  <span class="relative inline-flex rounded-full h-2 w-2 bg-green-500"></span>
                </span>
                System Healthy
            </div>
            
            <select wire:model="dateRange" wire:change="setDateRange($event.target.value)" class="px-4 py-2 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-primary-500 dark:bg-slate-900 dark:text-slate-100">
                <option value="today">Hari Ini</option>
                <option value="yesterday">Kemarin</option>
                <option value="this_week">Minggu Ini</option>
                <option value="last_week">Minggu Lalu</option>
                <option value="this_month">Bulan Ini</option>
                <option value="last_month">Bulan Lalu</option>
            </select>
            <div class="px-4 py-2 bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 rounded-lg text-sm font-medium border border-slate-200 dark:border-slate-700 flex items-center gap-2"
                x-data="{
                    time: '',
                    fmt() {
                        const d = new Date();
                        const days = ['Minggu','Senin','Selasa','Rabu','Kamis','Jumat','Sabtu'];
                        const months = ['Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'];
                        const hh = String(d.getHours()).padStart(2,'0');
                        const mm = String(d.getMinutes()).padStart(2,'0');
                        return days[d.getDay()] + ', ' + d.getDate() + ' ' + months[d.getMonth()] + ' ' + d.getFullYear() + ' ' + hh + ':' + mm;
                    }
                }"
                x-init="time = fmt(); setInterval(() => time = fmt(), 10000)">
                <span class="material-symbols-outlined" style="font-size: 18px;">schedule</span>
                <span x-text="time">{{ date('l, d F Y H:i') }}</span>
            </div>
        </div>
    </div>

    {{-- QUICK ACTION BAR --}}
    <div class="flex flex-wrap items-center gap-2 p-3 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl shadow-sm">
        <span class="text-xs font-semibold text-slate-400 uppercase tracking-wider ml-2 mr-1 flex items-center gap-1">
            <span class="material-symbols-outlined notranslate" translate="no" style="font-size:14px">bolt</span>
            Aksi Cepat
        </span>
        <a href="{{ route('crm.customers.index') }}" class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-indigo-50 hover:bg-indigo-100 dark:bg-indigo-900/30 dark:hover:bg-indigo-800/50 text-indigo-700 dark:text-indigo-300 rounded-lg text-sm font-semibold transition-all duration-200 cursor-pointer hover:shadow-sm hover:-translate-y-px">
            <span class="material-symbols-outlined notranslate" translate="no" style="font-size:15px">person_add</span>
            Pelanggan
        </a>
        <a href="{{ route('isp.pppoe-users.index') }}" class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-sky-50 hover:bg-sky-100 dark:bg-sky-900/30 dark:hover:bg-sky-800/50 text-sky-700 dark:text-sky-300 rounded-lg text-sm font-semibold transition-all duration-200 cursor-pointer hover:shadow-sm hover:-translate-y-px">
            <span class="material-symbols-outlined notranslate" translate="no" style="font-size:15px">router</span>
            PPPoE
        </a>
        <a href="{{ route('isp.hotspot-users.index') }}" class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-orange-50 hover:bg-orange-100 dark:bg-orange-900/30 dark:hover:bg-orange-800/50 text-orange-700 dark:text-orange-300 rounded-lg text-sm font-semibold transition-all duration-200 cursor-pointer hover:shadow-sm hover:-translate-y-px">
            <span class="material-symbols-outlined notranslate" translate="no" style="font-size:15px">wifi_tethering</span>
            Hotspot
        </a>
        <a href="{{ route('billing.invoices.create') }}" class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-emerald-50 hover:bg-emerald-100 dark:bg-emerald-900/30 dark:hover:bg-emerald-800/50 text-emerald-700 dark:text-emerald-300 rounded-lg text-sm font-semibold transition-all duration-200 cursor-pointer hover:shadow-sm hover:-translate-y-px">
            <span class="material-symbols-outlined notranslate" translate="no" style="font-size:15px">receipt_long</span>
            Buat Tagihan
        </a>
        <a href="{{ route('isp.routers.index') }}" class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-purple-50 hover:bg-purple-100 dark:bg-purple-900/30 dark:hover:bg-purple-800/50 text-purple-700 dark:text-purple-300 rounded-lg text-sm font-semibold transition-all duration-200 cursor-pointer hover:shadow-sm hover:-translate-y-px">
            <span class="material-symbols-outlined notranslate" translate="no" style="font-size:15px">device_hub</span>
            Router
        </a>
    </div>

    {{-- B. KPI CARDS --}}
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-4">

        {{-- Card: Pelanggan --}}
        <div class="relative overflow-x-auto rounded-xl border border-indigo-200 dark:border-indigo-800/60 shadow-md bg-gradient-to-br from-indigo-50 to-white dark:from-indigo-950/50 dark:to-slate-800 group hover:shadow-lg transition-all">
            <div class="absolute top-0 left-0 right-0 h-1 bg-gradient-to-r from-indigo-500 to-blue-400"></div>
            <div class="absolute top-3 right-3 opacity-10 text-indigo-400 group-hover:opacity-20 group-hover:scale-110 transition-all">
                <span class="material-symbols-outlined" style="font-size:56px">group</span>
            </div>
            <div class="p-4 pt-5">
                <h3 class="text-xs font-bold text-indigo-500 dark:text-indigo-400 uppercase tracking-widest mb-2">Pelanggan</h3>
                <div class="text-4xl font-black text-indigo-700 dark:text-indigo-300 mb-3">{{ number_format($kpi['customer']['total'] ?? 0) }}</div>
                <div class="grid grid-cols-3 gap-1.5">
                    <a href="{{ route('crm.customers.index') }}" class="rounded-lg px-2 py-1.5 bg-emerald-100 dark:bg-emerald-900/50 hover:bg-emerald-200 dark:hover:bg-emerald-800/70 transition-colors text-center">
                        <span class="block text-emerald-700 dark:text-emerald-400 font-semibold text-xs">Aktif</span>
                        <span class="text-emerald-800 dark:text-emerald-300 font-black text-sm">{{ $kpi['customer']['active'] ?? 0 }}</span>
                    </a>
                    <a href="{{ route('crm.customers.index') }}" class="rounded-lg px-2 py-1.5 bg-red-100 dark:bg-red-900/50 hover:bg-red-200 dark:hover:bg-red-800/70 transition-colors text-center">
                        <span class="block text-red-600 dark:text-red-400 font-semibold text-xs">Suspend</span>
                        <span class="text-red-700 dark:text-red-300 font-black text-sm">{{ $kpi['customer']['suspend'] ?? 0 }}</span>
                    </a>
                    <div class="rounded-lg px-2 py-1.5 bg-blue-100 dark:bg-blue-900/50 text-center">
                        <span class="block text-blue-600 dark:text-blue-400 font-semibold text-xs">Baru</span>
                        <span class="text-blue-700 dark:text-blue-300 font-black text-sm">+{{ $kpi['customer']['new'] ?? 0 }}</span>
                    </div>
                </div>
            </div>
        </div>

        {{-- Card: Layanan Internet --}}
        <div class="relative overflow-x-auto rounded-xl border border-sky-200 dark:border-sky-800/60 shadow-md bg-gradient-to-br from-sky-50 to-white dark:from-sky-950/50 dark:to-slate-800 group hover:shadow-lg transition-all">
            <div class="absolute top-0 left-0 right-0 h-1 bg-gradient-to-r from-sky-500 to-cyan-400"></div>
            <div class="absolute top-3 right-3 opacity-10 text-sky-400 group-hover:opacity-20 group-hover:scale-110 transition-all">
                <span class="material-symbols-outlined" style="font-size:56px">wifi</span>
            </div>
            <div class="p-4 pt-5">
                <h3 class="text-xs font-bold text-sky-500 dark:text-sky-400 uppercase tracking-widest mb-2">Layanan Internet</h3>
                <div class="text-4xl font-black text-sky-700 dark:text-sky-300 mb-3">
                    {{ number_format(($kpi['service']['pppoe_online'] ?? 0) + ($kpi['service']['hotspot_online'] ?? 0)) }}
                    <span class="text-sm font-normal text-sky-500">online</span>
                </div>
                <div class="grid grid-cols-3 gap-1.5">
                    <a href="{{ route('isp.pppoe-users.index') }}" class="rounded-lg px-2 py-1.5 bg-blue-100 dark:bg-blue-900/50 hover:bg-blue-200 dark:hover:bg-blue-800/70 transition-colors text-center">
                        <span class="block text-blue-600 dark:text-blue-400 font-semibold text-xs">PPPoE</span>
                        <span class="text-blue-700 dark:text-blue-300 font-black text-sm">{{ $kpi['service']['pppoe_online'] ?? 0 }}</span>
                    </a>
                    <a href="{{ route('isp.pppoe-users.index') }}" class="rounded-lg px-2 py-1.5 bg-slate-200 dark:bg-slate-700/60 hover:bg-slate-300 dark:hover:bg-slate-600/70 transition-colors text-center">
                        <span class="block text-slate-500 dark:text-slate-400 font-semibold text-xs">Off</span>
                        <span class="text-slate-700 dark:text-slate-300 font-black text-sm">{{ $kpi['service']['pppoe_offline'] ?? 0 }}</span>
                    </a>
                    <a href="{{ route('isp.hotspot-users.index') }}" class="rounded-lg px-2 py-1.5 bg-orange-100 dark:bg-orange-900/50 hover:bg-orange-200 dark:hover:bg-orange-800/70 transition-colors text-center">
                        <span class="block text-orange-600 dark:text-orange-400 font-semibold text-xs">Hotspot</span>
                        <span class="text-orange-700 dark:text-orange-300 font-black text-sm">{{ $kpi['service']['hotspot_session'] ?? 0 }}</span>
                    </a>
                </div>
            </div>
        </div>

        {{-- Card: Billing --}}
        <div class="relative overflow-x-auto rounded-xl border border-emerald-200 dark:border-emerald-800/60 shadow-md bg-gradient-to-br from-emerald-50 to-white dark:from-emerald-950/50 dark:to-slate-800 group hover:shadow-lg transition-all">
            <div class="absolute top-0 left-0 right-0 h-1 bg-gradient-to-r from-emerald-500 to-teal-400"></div>
            <div class="absolute top-3 right-3 opacity-10 text-emerald-400 group-hover:opacity-20 group-hover:scale-110 transition-all">
                <span class="material-symbols-outlined" style="font-size:56px">account_balance_wallet</span>
            </div>
            <div class="p-4 pt-5">
                <h3 class="text-xs font-bold text-emerald-500 dark:text-emerald-400 uppercase tracking-widest mb-2">Billing Bulan Ini</h3>
                <div class="text-2xl font-black text-emerald-700 dark:text-emerald-300 mb-3">Rp {{ number_format($kpi['billing']['tagihan'] ?? 0, 0, ',', '.') }}</div>
                <div class="grid grid-cols-3 gap-1.5">
                    <a href="{{ route('billing.invoices.index') }}" class="rounded-lg px-2 py-1.5 bg-emerald-100 dark:bg-emerald-900/50 hover:bg-emerald-200 dark:hover:bg-emerald-800/70 transition-colors text-center">
                        <span class="block text-emerald-700 dark:text-emerald-400 font-semibold text-xs">Lunas</span>
                        <span class="text-emerald-800 dark:text-emerald-300 font-black text-sm">@if(($kpi['billing']['paid'] ?? 0) >= 1000000){{ number_format(($kpi['billing']['paid'] ?? 0)/1000000, 1) }}Jt@else{{ number_format($kpi['billing']['paid'] ?? 0, 0, ',', '.') }}@endif</span>
                    </a>
                    <a href="{{ route('billing.invoices.index') }}" class="rounded-lg px-2 py-1.5 bg-amber-100 dark:bg-amber-900/50 hover:bg-amber-200 dark:hover:bg-amber-800/70 transition-colors text-center">
                        <span class="block text-amber-600 dark:text-amber-400 font-semibold text-xs">Belum</span>
                        <span class="text-amber-700 dark:text-amber-300 font-black text-sm">@if(($kpi['billing']['unpaid'] ?? 0) >= 1000000){{ number_format(($kpi['billing']['unpaid'] ?? 0)/1000000, 1) }}Jt@else{{ number_format($kpi['billing']['unpaid'] ?? 0, 0, ',', '.') }}@endif</span>
                    </a>
                    <a href="{{ route('billing.invoices.index') }}" class="rounded-lg px-2 py-1.5 bg-red-100 dark:bg-red-900/50 hover:bg-red-200 dark:hover:bg-red-800/70 transition-colors text-center">
                        <span class="block text-red-600 dark:text-red-400 font-semibold text-xs">Overdue</span>
                        <span class="text-red-700 dark:text-red-300 font-black text-sm">@if(($kpi['billing']['overdue'] ?? 0) >= 1000000){{ number_format(($kpi['billing']['overdue'] ?? 0)/1000000, 1) }}Jt@else{{ number_format($kpi['billing']['overdue'] ?? 0, 0, ',', '.') }}@endif</span>
                    </a>
                </div>
            </div>
        </div>

        {{-- Card: Voucher --}}
        <div class="relative overflow-x-auto rounded-xl border border-orange-200 dark:border-orange-800/60 shadow-md bg-gradient-to-br from-orange-50 to-white dark:from-orange-950/50 dark:to-slate-800 group hover:shadow-lg transition-all">
            <div class="absolute top-0 left-0 right-0 h-1 bg-gradient-to-r from-orange-500 to-amber-400"></div>
            <div class="absolute top-3 right-3 opacity-10 text-orange-400 group-hover:opacity-20 group-hover:scale-110 transition-all">
                <span class="material-symbols-outlined" style="font-size:56px">confirmation_number</span>
            </div>
            <div class="p-4 pt-5">
                <h3 class="text-xs font-bold text-orange-500 dark:text-orange-400 uppercase tracking-widest mb-2">Penjualan Voucher</h3>
                <div class="text-2xl font-black text-orange-700 dark:text-orange-300 mb-3">Rp {{ number_format($kpi['voucher']['revenue'] ?? 0, 0, ',', '.') }}</div>
                <div class="grid grid-cols-2 gap-1.5">
                    <a href="{{ route('isp.vouchers.index') }}" class="rounded-lg px-2 py-1.5 bg-orange-100 dark:bg-orange-900/50 hover:bg-orange-200 dark:hover:bg-orange-800/70 transition-colors text-center">
                        <span class="block text-orange-700 dark:text-orange-400 font-semibold text-xs">Terjual</span>
                        <span class="text-orange-800 dark:text-orange-300 font-black text-sm">{{ number_format($kpi['voucher']['sold'] ?? 0, 0, ',', '.') }}</span>
                    </a>
                    <a href="{{ route('isp.vouchers.index') }}" class="rounded-lg px-2 py-1.5 bg-slate-100 dark:bg-slate-700/50 hover:bg-slate-200 dark:hover:bg-slate-600/70 transition-colors text-center">
                        <span class="block text-slate-700 dark:text-slate-400 font-semibold text-xs">Tersedia</span>
                        <span class="text-slate-800 dark:text-slate-300 font-black text-sm">{{ number_format($kpi['voucher']['available'] ?? 0, 0, ',', '.') }}</span>
                    </a>
                </div>
            </div>
        </div>

        {{-- Card: PON & Akses --}}
        <div class="relative overflow-x-auto rounded-xl border border-purple-200 dark:border-purple-800/60 shadow-md bg-gradient-to-br from-purple-50 to-white dark:from-purple-950/50 dark:to-slate-800 group hover:shadow-lg transition-all">
            <div class="absolute top-0 left-0 right-0 h-1 bg-gradient-to-r from-purple-500 to-violet-400"></div>
            <div class="absolute top-3 right-3 opacity-10 text-purple-400 group-hover:opacity-20 group-hover:scale-110 transition-all">
                <span class="material-symbols-outlined" style="font-size:56px">hub</span>
            </div>
            <div class="p-4 pt-5">
                <h3 class="text-xs font-bold text-purple-500 dark:text-purple-400 uppercase tracking-widest mb-2">PON & Akses</h3>
                <div class="text-4xl font-black text-purple-700 dark:text-purple-300 mb-3">
                    {{ number_format($kpi['network']['onu_online'] ?? 0) }}
                    <span class="text-sm font-normal text-purple-500">ONU</span>
                </div>
                <div class="grid grid-cols-3 gap-1.5">
                    <a href="{{ route('isp.routers.index') }}" class="rounded-lg px-2 py-1.5 bg-violet-100 dark:bg-violet-900/50 hover:bg-violet-200 dark:hover:bg-violet-800/70 transition-colors text-center">
                        <span class="block text-violet-600 dark:text-violet-400 font-semibold text-xs">Router</span>
                        <span class="text-violet-700 dark:text-violet-300 font-black text-sm">{{ $kpi['network']['router_online'] ?? 0 }}/{{ ($kpi['network']['router_online'] ?? 0) + ($kpi['network']['router_offline'] ?? 0) }}</span>
                    </a>
                    <a href="#" class="rounded-lg px-2 py-1.5 bg-emerald-100 dark:bg-emerald-900/50 hover:bg-emerald-200 dark:hover:bg-emerald-800/70 transition-colors text-center">
                        <span class="block text-emerald-600 dark:text-emerald-400 font-semibold text-xs">OLT</span>
                        <span class="text-emerald-700 dark:text-emerald-300 font-black text-sm">{{ $kpi['network']['olt_online'] ?? 0 }}</span>
                    </a>
                    <a href="#" class="rounded-lg px-2 py-1.5 bg-red-100 dark:bg-red-900/50 hover:bg-red-200 dark:hover:bg-red-800/70 transition-colors text-center">
                        <span class="block text-red-600 dark:text-red-400 font-semibold text-xs">LOS</span>
                        <span class="text-red-700 dark:text-red-300 font-black text-sm">{{ $kpi['network']['onu_los'] ?? 0 }}</span>
                    </a>
                </div>
            </div>
        </div>
    </div>
    {{-- C. NETWORK TRAFFIC & HEALTH --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        
        <!-- Main Traffic Chart -->
        <div class="lg:col-span-2 bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 shadow-sm p-5">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-lg font-bold text-slate-900 dark:text-slate-100">Network Traffic Monitoring</h2>
                <div class="flex gap-2">
                    <span class="px-2 py-1 bg-slate-100 dark:bg-slate-700 text-slate-600 dark:text-slate-300 text-xs rounded">Live</span>
                    <span class="px-2 py-1 bg-slate-100 dark:bg-slate-700 text-slate-600 dark:text-slate-300 text-xs rounded">1H</span>
                    <span class="px-2 py-1 bg-slate-100 dark:bg-slate-700 text-slate-600 dark:text-slate-300 text-xs rounded">24H</span>
                </div>
            </div>
            <div id="trafficChart" class="w-full h-72"></div>
        </div>

        <!-- Network Health & RADIUS Performance -->
        <div class="space-y-6">
            <div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 shadow-sm p-5">
                <h2 class="text-base font-bold text-slate-900 dark:text-slate-100 mb-4">Network Health</h2>
                <div class="space-y-4">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <span class="w-2 h-2 rounded-full bg-green-500 shadow-[0_0_8px_rgba(34,197,94,0.8)]"></span>
                            <span class="text-sm font-medium text-slate-700 dark:text-slate-300">FreeRADIUS</span>
                        </div>
                        <span class="text-xs text-slate-500 dark:text-slate-400 font-mono">{{ $systemInfo['radius_uptime'] ?? 'Unknown' }}</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <span class="w-2 h-2 rounded-full bg-green-500 shadow-[0_0_8px_rgba(34,197,94,0.8)]"></span>
                            <span class="text-sm font-medium text-slate-700 dark:text-slate-300">MikroTik Routers</span>
                        </div>
                        <span class="text-xs text-slate-500 dark:text-slate-400 font-mono">15ms avg ping</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <span class="w-2 h-2 rounded-full bg-green-500 shadow-[0_0_8px_rgba(34,197,94,0.8)]"></span>
                            <span class="text-sm font-medium text-slate-700 dark:text-slate-300">GenieACS</span>
                        </div>
                        <span class="text-xs text-slate-500 dark:text-slate-400 font-mono">ONLINE</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <span class="w-2 h-2 rounded-full bg-green-500 shadow-[0_0_8px_rgba(34,197,94,0.8)]"></span>
                            <span class="text-sm font-medium text-slate-700 dark:text-slate-300">Database</span>
                        </div>
                        <span class="text-xs text-slate-500 dark:text-slate-400 font-mono">{{ $systemInfo['database_status'] ?? 'HEALTHY' }}</span>
                    </div>
                </div>
            </div>

            <div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 shadow-sm p-5">
                <h2 class="text-base font-bold text-slate-900 dark:text-slate-100 mb-4">RADIUS Performance</h2>
                <div class="space-y-3">
                    <div>
                        <div class="flex justify-between text-xs mb-1">
                            <span class="text-slate-600 dark:text-slate-400">Authentication Success</span>
                            <span class="text-green-600 font-medium">98.5%</span>
                        </div>
                        <div class="w-full bg-slate-100 dark:bg-slate-700 rounded-full h-1.5">
                            <div class="bg-green-500 h-1.5 rounded-full" style="width: 98.5%"></div>
                        </div>
                    </div>
                    <div>
                        <div class="flex justify-between text-xs mb-1">
                            <span class="text-slate-600 dark:text-slate-400">Authentication Reject</span>
                            <span class="text-red-500 font-medium">1.5%</span>
                        </div>
                        <div class="w-full bg-slate-100 dark:bg-slate-700 rounded-full h-1.5">
                            <div class="bg-red-500 h-1.5 rounded-full" style="width: 1.5%"></div>
                        </div>
                    </div>
                    <div class="pt-2 grid grid-cols-2 gap-4">
                        <div>
                            <span class="block text-xs text-slate-500 dark:text-slate-400">Avg Resp. Time</span>
                            <span class="block text-sm font-semibold text-slate-900 dark:text-slate-100">12 ms</span>
                        </div>
                        <div>
                            <span class="block text-xs text-slate-500 dark:text-slate-400">Req/sec</span>
                            <span class="block text-sm font-semibold text-slate-900 dark:text-slate-100">45</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- D. BILLING & ANALYTICS --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Revenue Overview -->
        <div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 shadow-sm p-5">
            <h2 class="text-base font-bold text-slate-900 dark:text-slate-100 mb-4">Billing & Revenue Overview</h2>
            <div id="revenueChart" class="w-full h-64"></div>
        </div>

        <!-- Top Users & Activity -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 shadow-sm p-5">
                <h2 class="text-base font-bold text-slate-900 dark:text-slate-100 mb-4">Customer Distribution</h2>
                <div id="customerChart" class="w-full h-48"></div>
            </div>

            <div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 shadow-sm p-5 flex flex-col">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-base font-bold text-slate-900 dark:text-slate-100">System Alerts</h2>
                    <span class="px-2 py-0.5 bg-red-100 dark:bg-red-900/50 text-red-600 rounded text-xs font-medium">{{ $kpi['network']['onu_los'] ?? 0 }} New</span>
                </div>
                <div class="flex-1 space-y-3 overflow-y-auto pr-1">
                    @if(($kpi['network']['onu_los'] ?? 0) > 0)
                    <div class="p-3 bg-red-50 dark:bg-red-900/10 border border-red-100 dark:border-red-900/30 rounded-lg">
                        <div class="flex items-start gap-2">
                            <svg class="w-4 h-4 text-red-500 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                            <div>
                                <p class="text-sm font-medium text-red-800 dark:text-red-400">{{ $kpi['network']['onu_los'] }} ONU mengalami LOS</p>
                                <p class="text-xs text-red-600 dark:text-red-500 mt-0.5">Prioritas Tinggi &middot; Cek Segera</p>
                            </div>
                        </div>
                    </div>
                    @endif
                    <div class="p-3 bg-amber-50 dark:bg-amber-900/10 border border-amber-100 dark:border-amber-900/30 rounded-lg">
                        <div class="flex items-start gap-2">
                            <svg class="w-4 h-4 text-amber-500 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            <div>
                                <p class="text-sm font-medium text-amber-800 dark:text-amber-400">Payment Gateway Degraded</p>
                                <p class="text-xs text-amber-600 dark:text-amber-500 mt-0.5">Callback mutasi BCA terlambat</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    {{-- E. RECENT PAYMENTS & ACTIVITY TIMELINE --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2 bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 shadow-sm overflow-hidden">
            <div class="p-5 border-b border-slate-200 dark:border-slate-700 flex justify-between items-center">
                <h2 class="text-base font-bold text-slate-900 dark:text-slate-100">Top Traffic Users</h2>
                <a href="{{ route('isp.user-online.index') }}" class="text-sm text-primary-600 hover:text-primary-700">View All &rarr;</a>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-slate-50 dark:bg-slate-800/50 text-xs uppercase tracking-wider text-slate-500 dark:text-slate-400 border-b border-slate-200 dark:border-slate-700">
                            <th class="p-4 font-medium">Customer</th>
                            <th class="p-4 font-medium">Service</th>
                            <th class="p-4 font-medium">IP Address</th>
                            <th class="p-4 font-medium text-right">Download</th>
                            <th class="p-4 font-medium text-right">Upload</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-700/50 text-sm">
                        @forelse($topTraffic ?? [] as $tt)
                        <tr class="hover:bg-slate-50 dark:bg-slate-900/50 dark:hover:bg-slate-800/50">
                            <td class="p-4 font-medium text-slate-900 dark:text-slate-100">{{ $tt['name'] }}</td>
                            <td class="p-4 text-slate-600 dark:text-slate-400">
                                <span class="px-2 py-0.5 {{ $tt['service'] == 'PPPOE' ? 'bg-blue-100 dark:bg-blue-900/50 text-blue-700' : 'bg-orange-100 dark:bg-orange-900/50 text-orange-700' }} rounded text-xs">{{ $tt['service'] }}</span>
                            </td>
                            <td class="p-4 font-mono text-slate-500 dark:text-slate-400">{{ $tt['ip'] }}</td>
                            <td class="p-4 text-right font-medium text-emerald-600">{{ $tt['download'] }}</td>
                            <td class="p-4 text-right font-medium text-amber-600">{{ $tt['upload'] }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="p-4 text-center text-slate-500">Belum ada data traffic</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 shadow-sm p-5">
            <h2 class="text-base font-bold text-slate-900 dark:text-slate-100 mb-4">Recent Activity</h2>
            <div class="relative border-l border-slate-200 dark:border-slate-700 ml-3 space-y-6">
                @foreach($activities ?? [] as $act)
                <div class="relative pl-6">
                    <span class="absolute -left-3 top-1 w-6 h-6 rounded-full bg-{{ $act['color'] ?? 'blue' }}-100 flex items-center justify-center text-{{ $act['color'] ?? 'blue' }}-600 shadow-sm ring-4 ring-white dark:ring-slate-800">
                        <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                    </span>
                    <p class="text-sm font-medium text-slate-900 dark:text-slate-100">{{ $act['text'] ?? '' }}</p>
                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">{{ $act['time'] ?? '' }}</p>
                </div>
                @endforeach
            </div>
        </div>
    </div>

    @push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
    <script>
        document.addEventListener('livewire:init', () => {
            // Traffic Chart (Area)
            var trafficOptions = {
                series: [{
                    name: 'Download',
                    data: [31, 40, 28, 51, 42, 109, 100, 120, 150, 110, 95]
                }, {
                    name: 'Upload',
                    data: [11, 32, 45, 32, 34, 52, 41, 60, 45, 30, 25]
                }],
                chart: {
                    type: 'area',
                    height: 280,
                    fontFamily: 'inherit',
                    toolbar: { show: false },
                    animations: { enabled: false },
                    background: 'transparent'
                },
                colors: ['#0ea5e9', '#8b5cf6'],
                fill: {
                    type: 'gradient',
                    gradient: {
                        shadeIntensity: 1,
                        opacityFrom: 0.4,
                        opacityTo: 0.05,
                        stops: [0, 90, 100]
                    }
                },
                dataLabels: { enabled: false },
                stroke: { curve: 'smooth', width: 2 },
                xaxis: {
                    categories: ['00:00', '02:00', '04:00', '06:00', '08:00', '10:00', '12:00', '14:00', '16:00', '18:00', '20:00'],
                    axisBorder: { show: false },
                    axisTicks: { show: false },
                    labels: { style: { colors: '#94a3b8' } }
                },
                yaxis: {
                    labels: { style: { colors: '#94a3b8' } }
                },
                grid: { borderColor: 'rgba(148, 163, 184, 0.1)' },
                theme: { mode: (localStorage.getItem('darkMode') === 'true') ? 'dark' : 'light' }
            };
            var trafficChart = new ApexCharts(document.querySelector("#trafficChart"), trafficOptions);
            trafficChart.render();

            // Revenue Chart (Bar)
            const isDark = (localStorage.getItem('darkMode') === 'true');
        var revenueOptions = {
            theme: { mode: isDark ? 'dark' : 'light' },
                series: [{
                    name: 'Paid',
                    data: @json($chartData['revenue']['paid'] ?? [])
                }, {
                    name: 'Unpaid',
                    data: @json($chartData['revenue']['unpaid'] ?? [])
                }],
                chart: {
                    type: 'bar',
                    height: 256,
                    stacked: true,
                    toolbar: { show: false },
                    fontFamily: 'inherit',
                    background: 'transparent'
                },
                colors: ['#10b981', '#f59e0b'],
                plotOptions: {
                    bar: { horizontal: false, borderRadius: 4, columnWidth: '40%' },
                },
                dataLabels: { enabled: false },
                xaxis: {
                    categories: @json($chartData['revenue']['categories'] ?? []),
                    labels: { style: { colors: '#94a3b8' } }
                },
                yaxis: {
                    labels: { style: { colors: '#94a3b8' } }
                },
                grid: { borderColor: 'rgba(148, 163, 184, 0.1)' },
                theme: { mode: (localStorage.getItem('darkMode') === 'true') ? 'dark' : 'light' }
            };
            var revenueChart = new ApexCharts(document.querySelector("#revenueChart"), revenueOptions);
            revenueChart.render();

            // Customer Distribution (Donut)
            var custOptions = {
                series: @json($chartData['distribution'] ?? []),
                chart: {
                    type: 'donut',
                    height: 200,
                    fontFamily: 'inherit',
                    background: 'transparent'
                },
                labels: ['PPPoE', 'Hotspot', 'Dedicated'],
                colors: ['#3b82f6', '#f97316', '#8b5cf6'],
                stroke: { show: false },
                dataLabels: { enabled: false },
                plotOptions: {
                    pie: {
                        donut: { size: '75%' }
                    }
                },
                legend: { position: 'right', fontSize: '12px' },
                theme: { mode: (localStorage.getItem('darkMode') === 'true') ? 'dark' : 'light' }
            };
            var custChart = new ApexCharts(document.querySelector("#customerChart"), custOptions);
            custChart.render();
        });
    </script>
    @endpush
</div>

