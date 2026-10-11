<div class="space-y-6 pb-10" wire:poll.300s="loadData">
    {{-- A. HEADER SECTION --}}
    <div class="flex flex-col md:flex-row items-start md:items-center justify-between gap-4">
        <div class="flex items-center gap-3">
            <div class="hidden md:flex items-center gap-2 px-3 py-1.5 {{ ($kpi['network']['router_offline'] ?? 0) > 0 || ($kpi['network']['onu_los'] ?? 0) > 0 ? 'bg-amber-50 dark:bg-amber-900/20 text-amber-700 dark:text-amber-400 border-amber-200 dark:border-amber-800' : 'bg-green-50 dark:bg-green-900/20 text-green-700 dark:text-green-400 border-green-200 dark:border-green-800' }} rounded-lg text-sm font-medium border">
                <span class="relative flex h-2 w-2">
                  <span class="animate-ping absolute inline-flex h-full w-full rounded-full {{ ($kpi['network']['router_offline'] ?? 0) > 0 || ($kpi['network']['onu_los'] ?? 0) > 0 ? 'bg-amber-400' : 'bg-green-400' }} opacity-75"></span>
                  <span class="relative inline-flex rounded-full h-2 w-2 {{ ($kpi['network']['router_offline'] ?? 0) > 0 || ($kpi['network']['onu_los'] ?? 0) > 0 ? 'bg-amber-500' : 'bg-green-500' }}"></span>
                </span>
                {{ ($kpi['network']['router_offline'] ?? 0) > 0 || ($kpi['network']['onu_los'] ?? 0) > 0 ? 'Perlu Perhatian' : 'System Healthy' }}
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
                    <a href="{{ route('isp.pppoe-users.index') }}" class="rounded-lg px-2 py-1.5 bg-blue-100 dark:bg-blue-900/50 hover:bg-blue-200 dark:hover:bg-blue-800/70 transition-colors text-center" title="PPPoE Online">
                        <span class="block text-blue-600 dark:text-blue-400 font-semibold text-xs">PPPoE</span>
                        <span class="text-blue-700 dark:text-blue-300 font-black text-sm">{{ $kpi['service']['pppoe_online'] ?? 0 }}</span>
                    </a>
                    <a href="{{ route('isp.pppoe-users.index') }}" class="rounded-lg px-2 py-1.5 bg-slate-200 dark:bg-slate-700/60 hover:bg-slate-300 dark:hover:bg-slate-600/70 transition-colors text-center" title="PPPoE Offline">
                        <span class="block text-slate-500 dark:text-slate-400 font-semibold text-xs">Off</span>
                        <span class="text-slate-700 dark:text-slate-300 font-black text-sm">{{ $kpi['service']['pppoe_offline'] ?? 0 }}</span>
                    </a>
                    <a href="{{ route('isp.hotspot-users.index') }}" class="rounded-lg px-2 py-1.5 bg-orange-100 dark:bg-orange-900/50 hover:bg-orange-200 dark:hover:bg-orange-800/70 transition-colors text-center" title="Hotspot Online">
                        <span class="block text-orange-600 dark:text-orange-400 font-semibold text-xs">Hotspot</span>
                        <span class="text-orange-700 dark:text-orange-300 font-black text-sm">{{ $kpi['service']['hotspot_online'] ?? 0 }}</span>
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
                <h3 class="text-xs font-bold text-emerald-500 dark:text-emerald-400 uppercase tracking-widest mb-2">Tagihan Periode Ini</h3>
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
                    <span class="text-sm font-normal text-purple-500">/ {{ number_format($kpi['network']['onu_total'] ?? 0) }} ONU</span>
                </div>
                <div class="grid grid-cols-3 gap-1.5">
                    <a href="{{ route('isp.routers.index') }}" class="rounded-lg px-2 py-1.5 bg-violet-100 dark:bg-violet-900/50 hover:bg-violet-200 dark:hover:bg-violet-800/70 transition-colors text-center" title="Router Online / Total">
                        <span class="block text-violet-600 dark:text-violet-400 font-semibold text-xs">Router</span>
                        <span class="text-violet-700 dark:text-violet-300 font-black text-sm">{{ $kpi['network']['router_online'] ?? 0 }}/{{ $kpi['network']['router_total'] ?? 0 }}</span>
                    </a>
                    <a href="{{ route('isp.olts.index') }}" class="rounded-lg px-2 py-1.5 bg-emerald-100 dark:bg-emerald-900/50 hover:bg-emerald-200 dark:hover:bg-emerald-800/70 transition-colors text-center" title="OLT Aktif / Total">
                        <span class="block text-emerald-600 dark:text-emerald-400 font-semibold text-xs">OLT</span>
                        <span class="text-emerald-700 dark:text-emerald-300 font-black text-sm">{{ $kpi['network']['olt_online'] ?? 0 }}/{{ $kpi['network']['olt_total'] ?? 0 }}</span>
                    </a>
                    <a href="{{ route('isp.onus.index') }}" class="rounded-lg px-2 py-1.5 bg-red-100 dark:bg-red-900/50 hover:bg-red-200 dark:hover:bg-red-800/70 transition-colors text-center" title="ONU Status LOS">
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
                <div>
                    <h2 class="text-lg font-bold text-slate-900 dark:text-slate-100">Live Traffic Monitoring</h2>
                    <p class="text-xs text-slate-500 dark:text-slate-400">Rerata lalu lintas throughput router MikroTik (Mbps)</p>
                </div>
                <div class="flex items-center gap-2">
                    <span class="px-2.5 py-1 bg-emerald-50 dark:bg-emerald-900/30 text-emerald-600 dark:text-emerald-400 text-xs font-semibold rounded-md border border-emerald-200 dark:border-emerald-800 flex items-center gap-1.5">
                        <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                        Realtime Log
                    </span>
                </div>
            </div>
            <div id="trafficChart" class="w-full h-72"></div>
        </div>

        <!-- Network Health & RADIUS Performance -->
        <div class="space-y-6">
            <div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 shadow-sm p-5">
                <h2 class="text-base font-bold text-slate-900 dark:text-slate-100 mb-4">Status Layanan & Core</h2>
                <div class="space-y-4">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <span class="w-2 h-2 rounded-full {{ ($systemInfo['radius_nas_count'] ?? 0) > 0 ? 'bg-green-500 shadow-[0_0_8px_rgba(34,197,94,0.8)]' : 'bg-amber-500' }}"></span>
                            <span class="text-sm font-medium text-slate-700 dark:text-slate-300">FreeRADIUS NAS</span>
                        </div>
                        <span class="text-xs text-slate-500 dark:text-slate-400 font-mono">{{ $systemInfo['radius_nas_count'] ?? 0 }} NAS Terdaftar</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <span class="w-2 h-2 rounded-full {{ ($kpi['network']['router_online'] ?? 0) > 0 ? 'bg-green-500 shadow-[0_0_8px_rgba(34,197,94,0.8)]' : 'bg-slate-400' }}"></span>
                            <span class="text-sm font-medium text-slate-700 dark:text-slate-300">MikroTik Routers</span>
                        </div>
                        <span class="text-xs text-slate-500 dark:text-slate-400 font-mono">{{ $systemInfo['router_status_text'] ?? '-' }}</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <span class="w-2 h-2 rounded-full {{ ($systemInfo['acs_device_count'] ?? 0) > 0 ? 'bg-green-500 shadow-[0_0_8px_rgba(34,197,94,0.8)]' : 'bg-slate-400' }}"></span>
                            <span class="text-sm font-medium text-slate-700 dark:text-slate-300">GenieACS TR-069</span>
                        </div>
                        <span class="text-xs text-slate-500 dark:text-slate-400 font-mono">{{ $systemInfo['acs_device_count'] ?? 0 }} CPE Terhubung</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <span class="w-2 h-2 rounded-full bg-green-500 shadow-[0_0_8px_rgba(34,197,94,0.8)]"></span>
                            <span class="text-sm font-medium text-slate-700 dark:text-slate-300">Database Engine</span>
                        </div>
                        <span class="text-xs text-slate-500 dark:text-slate-400 font-mono">{{ $systemInfo['database_status'] ?? 'HEALTHY' }}</span>
                    </div>
                </div>
            </div>

            <div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 shadow-sm p-5">
                <h2 class="text-base font-bold text-slate-900 dark:text-slate-100 mb-4">Statistik Sesi & Autentikasi</h2>
                <div class="space-y-4">
                    @php
                        $pppoePct = ($kpi['service']['pppoe_total'] ?? 0) > 0 
                            ? round((($kpi['service']['pppoe_online'] ?? 0) / $kpi['service']['pppoe_total']) * 100, 1) 
                            : 0;
                        $hotspotPct = ($kpi['service']['hotspot_total'] ?? 0) > 0 
                            ? round((($kpi['service']['hotspot_online'] ?? 0) / $kpi['service']['hotspot_total']) * 100, 1) 
                            : 0;
                    @endphp
                    <div>
                        <div class="flex justify-between text-xs mb-1">
                            <span class="text-slate-600 dark:text-slate-400">Sesi PPPoE Aktif</span>
                            <span class="text-blue-600 font-semibold">{{ $kpi['service']['pppoe_online'] ?? 0 }} / {{ $kpi['service']['pppoe_total'] ?? 0 }} ({{ $pppoePct }}%)</span>
                        </div>
                        <div class="w-full bg-slate-100 dark:bg-slate-700 rounded-full h-1.5">
                            <div class="bg-blue-500 h-1.5 rounded-full transition-all duration-500" style="width: {{ min(100, $pppoePct) }}%"></div>
                        </div>
                    </div>
                    <div>
                        <div class="flex justify-between text-xs mb-1">
                            <span class="text-slate-600 dark:text-slate-400">Sesi Hotspot Aktif</span>
                            <span class="text-orange-600 font-semibold">{{ $kpi['service']['hotspot_online'] ?? 0 }} / {{ $kpi['service']['hotspot_total'] ?? 0 }} ({{ $hotspotPct }}%)</span>
                        </div>
                        <div class="w-full bg-slate-100 dark:bg-slate-700 rounded-full h-1.5">
                            <div class="bg-orange-500 h-1.5 rounded-full transition-all duration-500" style="width: {{ min(100, $hotspotPct) }}%"></div>
                        </div>
                    </div>
                    <div class="pt-2 grid grid-cols-2 gap-4 border-t border-slate-100 dark:border-slate-700">
                        <div>
                            <span class="block text-xs text-slate-500 dark:text-slate-400">Total Sesi Aktif</span>
                            <span class="block text-base font-bold text-slate-900 dark:text-slate-100">{{ number_format($kpi['service']['total_online'] ?? 0) }}</span>
                        </div>
                        <div>
                            <span class="block text-xs text-slate-500 dark:text-slate-400">Paket Acct Hari Ini</span>
                            <span class="block text-base font-bold text-slate-900 dark:text-slate-100">{{ number_format($systemInfo['radius_acct_today'] ?? 0) }}</span>
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
            <h2 class="text-base font-bold text-slate-900 dark:text-slate-100 mb-1">Tren Tagihan & Pembayaran</h2>
            <p class="text-xs text-slate-500 dark:text-slate-400 mb-4">Perbandingan nominal lunas vs belum lunas (Rupiah)</p>
            <div id="revenueChart" class="w-full h-64"></div>
        </div>

        <!-- Distribution & System Alerts -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 shadow-sm p-5">
                <h2 class="text-base font-bold text-slate-900 dark:text-slate-100 mb-1">Distribusi Pelanggan</h2>
                <p class="text-xs text-slate-500 dark:text-slate-400 mb-4">Berdasarkan kategori layanan</p>
                <div id="customerChart" class="w-full h-48"></div>
            </div>

            <div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 shadow-sm p-5 flex flex-col">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-base font-bold text-slate-900 dark:text-slate-100">Peringatan Sistem</h2>
                    @if(count($alerts ?? []) > 0)
                        <span class="px-2 py-0.5 bg-red-100 dark:bg-red-900/50 text-red-600 rounded text-xs font-semibold">{{ count($alerts) }} Peringatan</span>
                    @else
                        <span class="px-2 py-0.5 bg-green-100 dark:bg-green-900/50 text-green-600 rounded text-xs font-semibold">Normal</span>
                    @endif
                </div>
                <div class="flex-1 space-y-3 overflow-y-auto pr-1">
                    @forelse($alerts ?? [] as $alert)
                    <div class="p-3 {{ ($alert['level'] ?? '') === 'red' ? 'bg-red-50 dark:bg-red-900/10 border-red-200 dark:border-red-900/30' : 'bg-amber-50 dark:bg-amber-900/10 border-amber-200 dark:border-amber-900/30' }} border rounded-lg">
                        <div class="flex items-start gap-2">
                            <span class="material-symbols-outlined notranslate text-{{ ($alert['level'] ?? '') === 'red' ? 'red' : 'amber' }}-500 text-[18px] mt-0.5" translate="no">
                                {{ ($alert['level'] ?? '') === 'red' ? 'error' : 'warning' }}
                            </span>
                            <div>
                                <p class="text-sm font-semibold {{ ($alert['level'] ?? '') === 'red' ? 'text-red-800 dark:text-red-400' : 'text-amber-800 dark:text-amber-400' }}">{{ $alert['title'] }}</p>
                                <p class="text-xs {{ ($alert['level'] ?? '') === 'red' ? 'text-red-600 dark:text-red-500' : 'text-amber-600 dark:text-amber-500' }} mt-0.5">{{ $alert['desc'] }}</p>
                            </div>
                        </div>
                    </div>
                    @empty
                    <div class="p-6 text-center flex flex-col items-center justify-center h-full">
                        <span class="material-symbols-outlined notranslate text-emerald-500 text-[36px] mb-2" translate="no">check_circle</span>
                        <p class="text-sm font-semibold text-slate-800 dark:text-slate-200">Semua Layanan Normal</p>
                        <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">Tidak ada gangguan jaringan atau tagihan tertunda kritis.</p>
                    </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
    
    {{-- E. RECENT PAYMENTS & TOP TRAFFIC USERS --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2 bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 shadow-sm overflow-hidden">
            <div class="p-5 border-b border-slate-200 dark:border-slate-700 flex justify-between items-center">
                <div>
                    <h2 class="text-base font-bold text-slate-900 dark:text-slate-100">Top Penggunaan Bandwidth</h2>
                    <p class="text-xs text-slate-500 dark:text-slate-400">Pengguna aktif dengan akumulasi kuota tertinggi</p>
                </div>
                <a href="{{ route('isp.user-online.index') }}" class="text-sm text-primary-600 hover:text-primary-700 font-medium flex items-center gap-1">
                    Lihat Semua
                    <span class="material-symbols-outlined text-[16px]">arrow_forward</span>
                </a>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-slate-50 dark:bg-slate-800/50 text-xs uppercase tracking-wider text-slate-500 dark:text-slate-400 border-b border-slate-200 dark:border-slate-700">
                            <th class="p-4 font-medium">Customer / User</th>
                            <th class="p-4 font-medium">Layanan</th>
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
                                <span class="px-2 py-0.5 {{ ($tt['service'] ?? '') == 'PPPOE' ? 'bg-blue-100 dark:bg-blue-900/50 text-blue-700 dark:text-blue-300' : 'bg-orange-100 dark:bg-orange-900/50 text-orange-700 dark:text-orange-300' }} rounded text-xs font-semibold">{{ $tt['service'] }}</span>
                            </td>
                            <td class="p-4 font-mono text-xs text-slate-500 dark:text-slate-400">{{ $tt['ip'] }}</td>
                            <td class="p-4 text-right font-medium text-emerald-600 dark:text-emerald-400">{{ $tt['download'] }}</td>
                            <td class="p-4 text-right font-medium text-amber-600 dark:text-amber-400">{{ $tt['upload'] }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="p-8 text-center text-slate-400 text-sm">
                                <span class="material-symbols-outlined text-[32px] text-slate-300 dark:text-slate-600 block mb-1">signal_cellular_off</span>
                                Belum ada statistik traffic sesi aktif yang terekam
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 shadow-sm p-5 flex flex-col">
            <h2 class="text-base font-bold text-slate-900 dark:text-slate-100 mb-4">Aktivitas Terkini</h2>
            <div class="relative border-l border-slate-200 dark:border-slate-700 ml-3 space-y-5 flex-1 overflow-y-auto">
                @forelse($activities ?? [] as $act)
                <div class="relative pl-6">
                    <span class="absolute -left-3 top-1 w-6 h-6 rounded-full bg-{{ $act['color'] ?? 'blue' }}-100 dark:bg-{{ $act['color'] ?? 'blue' }}-900/50 flex items-center justify-center text-{{ $act['color'] ?? 'blue' }}-600 dark:text-{{ $act['color'] ?? 'blue' }}-400 shadow-sm ring-4 ring-white dark:ring-slate-800">
                        <span class="material-symbols-outlined text-[14px]">notifications</span>
                    </span>
                    <p class="text-sm font-medium text-slate-900 dark:text-slate-100">{{ $act['text'] ?? '' }}</p>
                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">{{ $act['time'] ?? '' }}</p>
                </div>
                @empty
                <div class="py-10 text-center text-slate-400 text-sm">
                    Belum ada riwayat aktivitas terbaru
                </div>
                @endforelse
            </div>
        </div>
    </div>

    @push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
    <script>
        document.addEventListener('livewire:init', () => {
            const isDark = (localStorage.getItem('darkMode') === 'true');

            // 1. Traffic Chart (Live Area)
            var trafficCategories = @json($chartData['traffic']['categories'] ?? []);
            var downloadData = @json($chartData['traffic']['download'] ?? []);
            var uploadData = @json($chartData['traffic']['upload'] ?? []);

            var trafficOptions = {
                series: [{
                    name: 'Download (Mbps)',
                    data: downloadData
                }, {
                    name: 'Upload (Mbps)',
                    data: uploadData
                }],
                chart: {
                    type: 'area',
                    height: 280,
                    fontFamily: 'inherit',
                    toolbar: { show: false },
                    animations: { enabled: true },
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
                    categories: trafficCategories,
                    axisBorder: { show: false },
                    axisTicks: { show: false },
                    labels: { style: { colors: '#94a3b8' } }
                },
                yaxis: {
                    labels: { 
                        style: { colors: '#94a3b8' },
                        formatter: (val) => val + ' Mbps'
                    }
                },
                tooltip: {
                    y: {
                        formatter: (val) => val + ' Mbps'
                    }
                },
                grid: { borderColor: 'rgba(148, 163, 184, 0.1)' },
                theme: { mode: isDark ? 'dark' : 'light' }
            };
            var trafficChart = new ApexCharts(document.querySelector("#trafficChart"), trafficOptions);
            trafficChart.render();

            // 2. Revenue Chart (Bar)
            var revenueCategories = @json($chartData['revenue']['categories'] ?? []);
            var revenuePaid = @json($chartData['revenue']['paid'] ?? []);
            var revenueUnpaid = @json($chartData['revenue']['unpaid'] ?? []);

            var revenueOptions = {
                theme: { mode: isDark ? 'dark' : 'light' },
                series: [{
                    name: 'Lunas',
                    data: revenuePaid
                }, {
                    name: 'Belum Lunas / Overdue',
                    data: revenueUnpaid
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
                    categories: revenueCategories,
                    labels: { style: { colors: '#94a3b8' } }
                },
                yaxis: {
                    labels: { 
                        style: { colors: '#94a3b8' },
                        formatter: function(val) {
                            if (val >= 1000000) return 'Rp ' + (val/1000000).toFixed(1) + 'Jt';
                            if (val >= 1000) return 'Rp ' + (val/1000).toFixed(0) + 'Rb';
                            return 'Rp ' + val;
                        }
                    }
                },
                tooltip: {
                    y: {
                        formatter: (val) => 'Rp ' + new Intl.NumberFormat('id-ID').format(val)
                    }
                },
                grid: { borderColor: 'rgba(148, 163, 184, 0.1)' }
            };
            var revenueChart = new ApexCharts(document.querySelector("#revenueChart"), revenueOptions);
            revenueChart.render();

            // 3. Customer Distribution (Donut)
            var distData = @json($chartData['distribution'] ?? [0, 0, 0]);
            var custOptions = {
                series: distData,
                chart: {
                    type: 'donut',
                    height: 200,
                    fontFamily: 'inherit',
                    background: 'transparent'
                },
                labels: ['PPPoE', 'Hotspot', 'Voucher'],
                colors: ['#3b82f6', '#f97316', '#10b981'],
                stroke: { show: false },
                dataLabels: { enabled: false },
                plotOptions: {
                    pie: {
                        donut: { size: '75%' }
                    }
                },
                legend: { position: 'right', fontSize: '12px' },
                theme: { mode: isDark ? 'dark' : 'light' }
            };
            var custChart = new ApexCharts(document.querySelector("#customerChart"), custOptions);
            custChart.render();
        });
    </script>
    @endpush
</div>
