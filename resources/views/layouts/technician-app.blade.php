@php
    $__isDashboard = request()->routeIs('technician.dashboard');
    $__hasCheckedIn = false;
    $__user = auth()->user();
    if ($__user) {
        $__isAdmin = $__user->isSuperAdmin() || $__user->hasRole(['administrator', 'superadmin', 'admin', 'manager']);
        $__hasCheckedIn = $__isAdmin || \App\Models\Workforce\Attendance::where('technician_id', $__user->id)
            ->whereDate('date', \Carbon\Carbon::today())
            ->whereNotNull('checked_in_at')
            ->exists();
    }
@endphp

<x-layouts.base
    html-class="h-full"
    body-class="antialiased text-slate-800 dark:text-slate-200 bg-slate-100/70 dark:bg-slate-950 min-h-screen"
>

    <x-slot:bodyAttributes>
        x-data="{ 
            darkMode: localStorage.getItem('dsb_dark_mode') === '1' || localStorage.getItem('darkMode') === 'true',
            mobileMenuOpen: false 
        }"
        x-init="document.documentElement.classList.toggle('dark', darkMode); $watch('darkMode', val => { localStorage.setItem('dsb_dark_mode', val ? '1' : '0'); localStorage.setItem('darkMode', val ? 'true' : 'false'); document.documentElement.classList.toggle('dark', val); })"
        :class="darkMode ? 'dark' : ''"
    </x-slot:bodyAttributes>

    <x-slot:head>
        <meta name="theme-color" content="#4f46e5">
        <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Figtree:wght@300;400;500;600;700;800;900&family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200&display=swap" />
        <style>
            body { overscroll-behavior: none; }
            .material-symbols-outlined {
                font-family: 'Material Symbols Outlined' !important;
                font-weight: normal;
                font-style: normal;
                font-size: 24px;
                line-height: 1;
                letter-spacing: normal;
                text-transform: none !important;
                display: inline-flex;
                align-items: center;
                justify-content: center;
                white-space: nowrap;
                word-wrap: normal;
                direction: ltr;
                -webkit-font-feature-settings: 'liga';
                font-feature-settings: 'liga';
                -webkit-font-smoothing: antialiased;
                user-select: none;
                vertical-align: middle;
                font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24;
            }
            .material-symbols-outlined.fill {
                font-variation-settings: 'FILL' 1, 'wght' 400, 'GRAD' 0, 'opsz' 24 !important;
            }
            @media print { .print-hide { display: none !important; } }
        </style>
    </x-slot:head>

    <x-slot:title>@yield('title', $title ?? 'Technician Portal - dsBilling')</x-slot:title>

    <div class="min-h-screen flex flex-col lg:flex-row bg-slate-100/70 dark:bg-slate-950">

        <!-- ==================================================================== -->
        <!-- DESKTOP SIDEBAR (Visible on >= 1024px / lg:flex)                      -->
        <!-- ==================================================================== -->
        <aside class="hidden lg:flex lg:flex-col lg:w-72 lg:fixed lg:inset-y-0 bg-white dark:bg-slate-900 border-r border-slate-200 dark:border-slate-800 z-50 shadow-sm print-hide">
            
            <!-- Sidebar Header / Brand -->
            <div class="h-16 px-5 border-b border-slate-200 dark:border-slate-800 flex items-center justify-between">
                <a href="{{ route('technician.dashboard') }}" class="flex items-center gap-3 group">
                    <div class="w-10 h-10 rounded-xl bg-gradient-to-tr from-indigo-600 via-indigo-500 to-blue-500 flex items-center justify-center text-white shadow-md shadow-indigo-500/20 group-hover:scale-105 transition-transform">
                        <span class="material-symbols-outlined notranslate" translate="no" style="font-size:22px">engineering</span>
                    </div>
                    <div class="flex flex-col min-w-0">
                        <span class="text-base font-black tracking-tight text-slate-900 dark:text-white leading-tight">
                            ds<span class="text-indigo-600 dark:text-indigo-400">Billing</span>
                        </span>
                        <span class="text-[11px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Technician Portal</span>
                    </div>
                </a>
            </div>

            <!-- Technician User Card & Shift Pill -->
            <div class="p-4 border-b border-slate-100 dark:border-slate-800/80 bg-slate-50/70 dark:bg-slate-900/50">
                <div class="flex items-center gap-3">
                    <div class="w-11 h-11 rounded-2xl bg-indigo-100 dark:bg-indigo-900/60 text-indigo-700 dark:text-indigo-300 flex items-center justify-center font-black text-base shadow-xs shrink-0">
                        {{ strtoupper(mb_substr($__user->name ?? 'T', 0, 1)) }}
                    </div>
                    <div class="flex-1 min-w-0">
                        <h4 class="text-sm font-bold text-slate-900 dark:text-white truncate leading-tight">{{ $__user->name ?? 'Teknisi Lapangan' }}</h4>
                        <div class="flex items-center gap-1.5 mt-1">
                            <span class="px-2 py-0.5 rounded-md bg-indigo-500/10 text-indigo-700 dark:text-indigo-300 text-[10px] font-extrabold uppercase">
                                {{ strtoupper((string) ($__user->job_function ?? 'TECHNICIAN')) }}
                            </span>
                            <span class="text-[11px] text-slate-400 truncate">Cabang #{{ $__user->branch_id ?? 1 }}</span>
                        </div>
                    </div>
                </div>

                <!-- Live Shift Indicator -->
                <div class="mt-3 pt-3 border-t border-slate-200/60 dark:border-slate-800 flex items-center justify-between text-xs">
                    @if($__hasCheckedIn)
                        <div class="flex items-center gap-1.5 text-emerald-600 dark:text-emerald-400 font-semibold">
                            <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                            <span>Shift Masuk Aktif</span>
                        </div>
                        <a href="{{ route('technician.attendance') }}" class="text-[11px] text-slate-500 hover:text-indigo-600 font-medium">
                            Detail &rarr;
                        </a>
                    @else
                        <div class="flex items-center gap-1.5 text-amber-600 dark:text-amber-400 font-bold">
                            <span class="w-2 h-2 rounded-full bg-amber-500 animate-ping"></span>
                            <span>Belum Absen Masuk</span>
                        </div>
                        <a href="{{ route('technician.attendance') }}" class="px-2 py-0.5 rounded bg-amber-500 text-white font-bold text-[10px] hover:bg-amber-600 transition-colors">
                            Absen
                        </a>
                    @endif
                </div>
            </div>

            <!-- Navigation Links (Scrollable) -->
            <nav class="flex-1 overflow-y-auto p-3 space-y-1 text-sm font-semibold">
                
                <p class="px-3 pt-2 pb-1 text-[10px] font-extrabold uppercase tracking-wider text-slate-400 dark:text-slate-500">Operasional Utama</p>
                
                <a href="{{ route('technician.dashboard') }}" 
                   class="flex items-center gap-3 px-3 py-2.5 rounded-xl transition-colors {{ request()->routeIs('technician.dashboard') ? 'bg-indigo-600 text-white shadow-sm shadow-indigo-600/30' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800/80 hover:text-slate-900 dark:hover:text-white' }}">
                    <span class="material-symbols-outlined notranslate {{ request()->routeIs('technician.dashboard') ? 'fill' : '' }}" translate="no" style="font-size:20px">dashboard</span>
                    <span>Dashboard</span>
                </a>

                @if(!$__hasCheckedIn)
                    <div class="px-3 py-2 rounded-xl bg-amber-50 dark:bg-amber-950/30 border border-amber-200/60 dark:border-amber-900/50 text-xs text-amber-800 dark:text-amber-300 space-y-1">
                        <div class="flex items-center gap-1.5 font-bold">
                            <span class="material-symbols-outlined text-sm notranslate" translate="no">lock</span>
                            <span>Menu Terkunci</span>
                        </div>
                        <p class="text-[11px] text-amber-700 dark:text-amber-400 leading-tight">Lakukan Absen Masuk untuk membuka akses penugasan SPK & tools lapangan.</p>
                    </div>
                @else
                    <a href="{{ route('technician.my-jobs.index') }}" 
                       class="flex items-center justify-between px-3 py-2.5 rounded-xl transition-colors {{ request()->routeIs('technician.my-jobs.index') ? 'bg-indigo-600 text-white shadow-sm shadow-indigo-600/30' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800/80 hover:text-slate-900 dark:hover:text-white' }}">
                        <div class="flex items-center gap-3">
                            <span class="material-symbols-outlined notranslate {{ request()->routeIs('technician.my-jobs.index') ? 'fill' : '' }}" translate="no" style="font-size:20px">assignment</span>
                            <span>SPK Lapangan</span>
                        </div>
                    </a>

                    <a href="{{ route('technician.my-jobs.troubleshooting') }}" 
                       class="flex items-center gap-3 px-3 py-2.5 rounded-xl transition-colors {{ request()->routeIs('technician.my-jobs.troubleshooting') ? 'bg-indigo-600 text-white shadow-sm shadow-indigo-600/30' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800/80 hover:text-slate-900 dark:hover:text-white' }}">
                        <span class="material-symbols-outlined notranslate {{ request()->routeIs('technician.my-jobs.troubleshooting') ? 'fill' : '' }}" translate="no" style="font-size:20px">build</span>
                        <span>Tiket Gangguan</span>
                    </a>

                    <a href="{{ route('technician.my-jobs.maintenance') }}" 
                       class="flex items-center gap-3 px-3 py-2.5 rounded-xl transition-colors {{ request()->routeIs('technician.my-jobs.maintenance') ? 'bg-indigo-600 text-white shadow-sm shadow-indigo-600/30' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800/80 hover:text-slate-900 dark:hover:text-white' }}">
                        <span class="material-symbols-outlined notranslate {{ request()->routeIs('technician.my-jobs.maintenance') ? 'fill' : '' }}" translate="no" style="font-size:20px">handyman</span>
                        <span>Maintenance WO</span>
                    </a>

                    <a href="{{ route('technician.installation.wizard') }}" 
                       class="flex items-center gap-3 px-3 py-2.5 rounded-xl transition-colors {{ request()->routeIs('technician.installation.*') ? 'bg-indigo-600 text-white shadow-sm shadow-indigo-600/30' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800/80 hover:text-slate-900 dark:hover:text-white' }}">
                        <span class="material-symbols-outlined notranslate {{ request()->routeIs('technician.installation.*') ? 'fill' : '' }}" translate="no" style="font-size:20px">build_circle</span>
                        <span>Wizard PSB (Pasang)</span>
                    </a>

                    <p class="px-3 pt-4 pb-1 text-[10px] font-extrabold uppercase tracking-wider text-slate-400 dark:text-slate-500">Alat Lapangan & ONT</p>

                    <a href="{{ route('technician.diagnostic.index') }}" 
                       class="flex items-center gap-3 px-3 py-2 rounded-xl transition-colors {{ request()->routeIs('technician.diagnostic.*') ? 'bg-indigo-600 text-white shadow-sm' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800/80 hover:text-slate-900 dark:hover:text-white' }}">
                        <span class="material-symbols-outlined notranslate" translate="no" style="font-size:20px">troubleshoot</span>
                        <span>Diagnosa TR-069</span>
                    </a>

                    <a href="{{ route('technician.swap-ont.index') }}" 
                       class="flex items-center gap-3 px-3 py-2 rounded-xl transition-colors {{ request()->routeIs('technician.swap-ont.*') ? 'bg-indigo-600 text-white shadow-sm' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800/80 hover:text-slate-900 dark:hover:text-white' }}">
                        <span class="material-symbols-outlined notranslate" translate="no" style="font-size:20px">sync_alt</span>
                        <span>Ganti ONT (Swap)</span>
                    </a>

                    <a href="{{ route('technician.dismantle.index') }}" 
                       class="flex items-center gap-3 px-3 py-2 rounded-xl transition-colors {{ request()->routeIs('technician.dismantle.*') ? 'bg-indigo-600 text-white shadow-sm' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800/80 hover:text-slate-900 dark:hover:text-white' }}">
                        <span class="material-symbols-outlined notranslate" translate="no" style="font-size:20px">power_off</span>
                        <span>Cabut ONT (Dismantle)</span>
                    </a>

                    <a href="{{ route('technician.material.index') }}" 
                       class="flex items-center gap-3 px-3 py-2 rounded-xl transition-colors {{ request()->routeIs('technician.material.*') || request()->routeIs('technician.stock.*') ? 'bg-indigo-600 text-white shadow-sm' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800/80 hover:text-slate-900 dark:hover:text-white' }}">
                        <span class="material-symbols-outlined notranslate" translate="no" style="font-size:20px">inventory_2</span>
                        <span>Stok Material Saya</span>
                    </a>

                    <a href="{{ route('technician.odp.nearest') }}" 
                       class="flex items-center gap-3 px-3 py-2 rounded-xl transition-colors {{ request()->routeIs('technician.odp.*') ? 'bg-indigo-600 text-white shadow-sm' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800/80 hover:text-slate-900 dark:hover:text-white' }}">
                        <span class="material-symbols-outlined notranslate" translate="no" style="font-size:20px">map</span>
                        <span>Peta ODP Terdekat</span>
                    </a>

                    <a href="{{ route('technician.audit.form') }}" 
                       class="flex items-center gap-3 px-3 py-2 rounded-xl transition-colors {{ request()->routeIs('technician.audit.*') ? 'bg-indigo-600 text-white shadow-sm' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800/80 hover:text-slate-900 dark:hover:text-white' }}">
                        <span class="material-symbols-outlined notranslate" translate="no" style="font-size:20px">radar</span>
                        <span>Sensus & Audit Jalur</span>
                    </a>
                @endif

                <p class="px-3 pt-4 pb-1 text-[10px] font-extrabold uppercase tracking-wider text-slate-400 dark:text-slate-500">Karyawan & Presensi</p>

                <a href="{{ route('technician.attendance') }}" 
                   class="flex items-center gap-3 px-3 py-2 rounded-xl transition-colors {{ request()->routeIs('technician.attendance') ? 'bg-indigo-600 text-white shadow-sm' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800/80 hover:text-slate-900 dark:hover:text-white' }}">
                    <span class="material-symbols-outlined notranslate" translate="no" style="font-size:20px">fingerprint</span>
                    <span>Presensi & Shift Kerja</span>
                </a>

                <a href="{{ route('technician.payroll.index') }}" 
                   class="flex items-center gap-3 px-3 py-2 rounded-xl transition-colors {{ request()->routeIs('technician.payroll.*') ? 'bg-indigo-600 text-white shadow-sm' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800/80 hover:text-slate-900 dark:hover:text-white' }}">
                    <span class="material-symbols-outlined notranslate" translate="no" style="font-size:20px">request_quote</span>
                    <span>Slip Gaji & Insentif</span>
                </a>

                <a href="{{ route('technician.guide.index') }}" 
                   class="flex items-center gap-3 px-3 py-2 rounded-xl transition-colors {{ request()->routeIs('technician.guide.*') ? 'bg-indigo-600 text-white shadow-sm' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800/80 hover:text-slate-900 dark:hover:text-white' }}">
                    <span class="material-symbols-outlined notranslate" translate="no" style="font-size:20px">menu_book</span>
                    <span>Panduan SOP Lapangan</span>
                </a>
            </nav>

            <!-- Sidebar Footer / Logout -->
            <div class="p-3 border-t border-slate-200 dark:border-slate-800 flex items-center justify-between">
                <form method="POST" action="{{ route('logout') }}" class="w-full">
                    @csrf
                    <button type="submit" class="w-full flex items-center justify-center gap-2 px-3 py-2 rounded-xl text-slate-600 dark:text-slate-400 hover:bg-rose-50 dark:hover:bg-rose-950/40 hover:text-rose-600 dark:hover:text-rose-400 font-bold transition-colors">
                        <span class="material-symbols-outlined notranslate" translate="no" style="font-size:20px">logout</span>
                        <span>Keluar Akun</span>
                    </button>
                </form>
            </div>
        </aside>

        <!-- ==================================================================== -->
        <!-- MAIN CONTENT AREA (Full Width Desktop, Mobile-First Shell on Mobile) -->
        <!-- ==================================================================== -->
        <div class="flex-1 lg:pl-72 flex flex-col min-h-screen">
            
            <!-- STICKY TOP HEADER -->
            <header class="sticky top-0 z-40 bg-white/95 dark:bg-slate-900/95 backdrop-blur border-b border-slate-200 dark:border-slate-800 px-4 sm:px-6 lg:px-8 h-16 flex items-center justify-between shadow-xs print-hide">
                
                <!-- Left: Back Button & Title / Mobile Brand -->
                <div class="flex items-center gap-3 min-w-0 flex-1">
                    @if(!$__isDashboard)
                        <button onclick="history.back()" class="p-2 -ml-2 shrink-0 text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 rounded-xl transition-colors">
                            <span class="material-symbols-outlined notranslate" translate="no" style="font-size:22px">arrow_back</span>
                        </button>
                    @endif

                    <div class="flex items-center gap-2.5 min-w-0">
                        <div class="w-9 h-9 shrink-0 bg-gradient-to-br from-indigo-500 to-blue-600 text-white lg:hidden inline-flex items-center justify-center rounded-xl shadow-sm">
                            <span class="material-symbols-outlined notranslate" translate="no" style="font-size:20px">engineering</span>
                        </div>
                        <div class="flex flex-col leading-tight min-w-0">
                            <h1 class="text-base sm:text-lg font-black text-slate-900 dark:text-white truncate">
                                @yield('header_title', $title ?? 'Portal Teknisi')
                            </h1>
                            <span class="text-[11px] font-medium text-slate-400 dark:text-slate-500 truncate hidden sm:inline">
                                dsBilling Field Service Operations &bull; Cabang #{{ $__user->branch_id ?? 1 }}
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Right: Notification, Dark Mode, Profile -->
                <div class="flex items-center gap-2 shrink-0">
                    <livewire:notification-center />

                    <button @click="darkMode = !darkMode; localStorage.setItem('dsb_dark_mode', darkMode ? '1' : '0')" 
                            class="p-2 text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 rounded-xl transition-colors"
                            title="Ganti Mode Gelap / Terang">
                        <span class="material-symbols-outlined notranslate" translate="no" style="font-size:22px" x-text="darkMode ? 'light_mode' : 'dark_mode'"></span>
                    </button>

                    <div class="hidden sm:flex items-center gap-2 pl-2 border-l border-slate-200 dark:border-slate-800">
                        <div class="w-8 h-8 rounded-full bg-indigo-100 dark:bg-indigo-900/60 text-indigo-700 dark:text-indigo-300 flex items-center justify-center font-black text-xs">
                            {{ strtoupper(mb_substr($__user->name ?? 'T', 0, 1)) }}
                        </div>
                        <span class="text-xs font-bold text-slate-700 dark:text-slate-300 truncate max-w-[120px]">{{ $__user->name ?? 'Teknisi' }}</span>
                    </div>
                </div>
            </header>

            <!-- ALERT GATE: BELUM ABSEN (Sticky beneath header) -->
            @if(!$__hasCheckedIn && !request()->routeIs('technician.attendance'))
                <div class="bg-gradient-to-r from-amber-500 to-orange-500 text-white px-4 sm:px-6 lg:px-8 py-2.5 text-xs sm:text-sm font-semibold flex items-center justify-between shadow-sm z-30 print-hide">
                    <div class="flex items-center gap-2.5 min-w-0">
                        <span class="material-symbols-outlined notranslate text-lg shrink-0" translate="no">lock_clock</span>
                        <span class="truncate">Wajib melakukan Absen Masuk untuk membuka akses penugasan SPK & fitur lapangan hari ini.</span>
                    </div>
                    <a href="{{ route('technician.attendance') }}" class="px-3 py-1 bg-white text-amber-900 font-extrabold rounded-lg text-xs shadow hover:bg-amber-50 transition-colors shrink-0 ml-3">
                        Absen Masuk
                    </a>
                </div>
            @endif

            <!-- MAIN CONTAINER: WIDE ON DESKTOP, MAX-W-MD ON MOBILE -->
            <main class="flex-1 w-full max-w-md lg:max-w-7xl mx-auto px-3 sm:px-6 lg:px-8 py-4 sm:py-6 pb-24 lg:pb-12">
                {{ $slot ?? '' }}
                @yield('content')
            </main>
        </div>

        <!-- ==================================================================== -->
        <!-- MOBILE BOTTOM NAVIGATION (Hidden on Desktop >= 1024px)              -->
        <!-- ==================================================================== -->
        <nav class="lg:hidden fixed bottom-0 left-0 right-0 w-full bg-white/95 dark:bg-slate-900/95 backdrop-blur border-t border-slate-200 dark:border-slate-800 px-1 py-1 grid grid-cols-6 items-center justify-items-center z-40 shadow-[0_-4px_24px_rgba(0,0,0,0.06)] print-hide">
            <a href="{{ route('technician.dashboard') }}" class="w-full flex flex-col items-center justify-center py-1.5 px-0.5 rounded-xl transition-colors {{ request()->routeIs('technician.dashboard') ? 'text-indigo-600 dark:text-indigo-400 bg-indigo-50 dark:bg-indigo-900/40' : 'text-slate-400 dark:text-slate-500 hover:text-slate-700' }}">
                <span class="material-symbols-outlined notranslate shrink-0 {{ request()->routeIs('technician.dashboard') ? 'fill' : '' }}" translate="no" style="font-size:22px; line-height: 1;">home</span>
                <span class="text-[9px] font-semibold truncate max-w-full leading-tight mt-0.5">Home</span>
            </a>

            @if(!$__hasCheckedIn)
                <button type="button" onclick="alert('Silakan lakukan Absen Masuk terlebih dahulu.'); window.location.href='{{ route('technician.attendance') }}';" class="w-full flex flex-col items-center justify-center py-1.5 px-0.5 rounded-xl text-slate-300 dark:text-slate-600 relative" title="Menu Terkunci - Wajib Absen">
                    <span class="material-symbols-outlined notranslate shrink-0" translate="no" style="font-size:22px; line-height: 1;">lock</span>
                    <span class="text-[9px] font-semibold truncate max-w-full leading-tight mt-0.5">PSB</span>
                </button>
                <button type="button" onclick="alert('Silakan lakukan Absen Masuk terlebih dahulu.'); window.location.href='{{ route('technician.attendance') }}';" class="w-full flex flex-col items-center justify-center py-1.5 px-0.5 rounded-xl text-slate-300 dark:text-slate-600 relative" title="Menu Terkunci - Wajib Absen">
                    <span class="material-symbols-outlined notranslate shrink-0" translate="no" style="font-size:22px; line-height: 1;">lock</span>
                    <span class="text-[9px] font-semibold truncate max-w-full leading-tight mt-0.5">Gangguan</span>
                </button>
                <button type="button" onclick="alert('Silakan lakukan Absen Masuk terlebih dahulu.'); window.location.href='{{ route('technician.attendance') }}';" class="w-full flex flex-col items-center justify-center py-1.5 px-0.5 rounded-xl text-slate-300 dark:text-slate-600 relative" title="Menu Terkunci - Wajib Absen">
                    <span class="material-symbols-outlined notranslate shrink-0" translate="no" style="font-size:22px; line-height: 1;">lock</span>
                    <span class="text-[9px] font-semibold truncate max-w-full leading-tight mt-0.5">Instalasi</span>
                </button>
            @else
                <a href="{{ route('technician.my-jobs.index') }}" class="w-full flex flex-col items-center justify-center py-1.5 px-0.5 rounded-xl transition-colors {{ request()->routeIs('technician.my-jobs.index') ? 'text-indigo-600 dark:text-indigo-400 bg-indigo-50 dark:bg-indigo-900/40' : 'text-slate-400 dark:text-slate-500 hover:text-slate-700' }}">
                    <span class="material-symbols-outlined notranslate shrink-0 {{ request()->routeIs('technician.my-jobs.index') ? 'fill' : '' }}" translate="no" style="font-size:22px; line-height: 1;">assignment</span>
                    <span class="text-[9px] font-semibold truncate max-w-full leading-tight mt-0.5">PSB</span>
                </a>
                <a href="{{ route('technician.my-jobs.troubleshooting') }}" class="w-full flex flex-col items-center justify-center py-1.5 px-0.5 rounded-xl transition-colors {{ request()->routeIs('technician.my-jobs.troubleshooting') ? 'text-indigo-600 dark:text-indigo-400 bg-indigo-50 dark:bg-indigo-900/40' : 'text-slate-400 dark:text-slate-500 hover:text-slate-700' }}">
                    <span class="material-symbols-outlined notranslate shrink-0 {{ request()->routeIs('technician.my-jobs.troubleshooting') ? 'fill' : '' }}" translate="no" style="font-size:22px; line-height: 1;">build</span>
                    <span class="text-[9px] font-semibold truncate max-w-full leading-tight mt-0.5">Gangguan</span>
                </a>
                <a href="{{ route('technician.installation.wizard') }}" class="w-full flex flex-col items-center justify-center py-1.5 px-0.5 rounded-xl transition-colors {{ request()->routeIs('technician.installation.*') ? 'text-indigo-600 dark:text-indigo-400 bg-indigo-50 dark:bg-indigo-900/40' : 'text-slate-400 dark:text-slate-500 hover:text-slate-700' }}">
                    <span class="material-symbols-outlined notranslate shrink-0 {{ request()->routeIs('technician.installation.*') ? 'fill' : '' }}" translate="no" style="font-size:22px; line-height: 1;">build_circle</span>
                    <span class="text-[9px] font-semibold truncate max-w-full leading-tight mt-0.5">Instalasi</span>
                </a>
            @endif

            <a href="{{ route('technician.attendance') }}" class="w-full flex flex-col items-center justify-center py-1.5 px-0.5 rounded-xl transition-colors relative {{ request()->routeIs('technician.attendance') ? 'text-indigo-600 dark:text-indigo-400 bg-indigo-50 dark:bg-indigo-900/40' : ($__hasCheckedIn ? 'text-slate-400 dark:text-slate-500 hover:text-slate-700' : 'text-amber-500 font-bold') }}">
                @if(!$__hasCheckedIn)
                    <span class="absolute top-1 right-3 flex h-2 w-2">
                        <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-red-400 opacity-75"></span>
                        <span class="relative inline-flex rounded-full h-2 w-2 bg-red-500"></span>
                    </span>
                @endif
                <span class="material-symbols-outlined notranslate shrink-0 {{ request()->routeIs('technician.attendance') ? 'fill' : '' }}" translate="no" style="font-size:22px; line-height: 1;">fingerprint</span>
                <span class="text-[9px] font-semibold truncate max-w-full leading-tight mt-0.5">{{ $__hasCheckedIn ? 'Absensi' : 'Absen' }}</span>
            </a>

            <form method="POST" action="{{ route('logout') }}" class="w-full m-0 flex justify-center">
                @csrf
                <button type="submit" class="w-full flex flex-col items-center justify-center py-1.5 px-0.5 rounded-xl text-slate-400 dark:text-slate-500 hover:text-red-500 transition-colors">
                    <span class="material-symbols-outlined notranslate shrink-0" translate="no" style="font-size:22px; line-height: 1;">logout</span>
                    <span class="text-[9px] font-semibold truncate max-w-full leading-tight mt-0.5">Keluar</span>
                </button>
            </form>
        </nav>
    </div>

    <x-ui.toast-manager />

    <script>
        (function() {
            function protectIcons() {
                document.querySelectorAll('.material-symbols-outlined, .material-icons-outlined, .material-icons').forEach(function(el) {
                    el.setAttribute('translate', 'no');
                    el.classList.add('notranslate');
                });
            }
            document.addEventListener('DOMContentLoaded', protectIcons);
            document.addEventListener('livewire:navigated', protectIcons);
            document.addEventListener('livewire:update', protectIcons);
            if (window.MutationObserver) {
                new MutationObserver(protectIcons).observe(document.documentElement, { childList: true, subtree: true });
            }
        })();
    </script>

</x-layouts.base>
