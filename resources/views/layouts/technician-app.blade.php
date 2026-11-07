@php
    $__isDashboard = request()->routeIs('technician.dashboard');
    $__hasCheckedIn = false;
    if (auth()->check()) {
        $__isAdmin = auth()->user()->isSuperAdmin() || auth()->user()->hasRole(['administrator', 'superadmin', 'admin']);
        $__hasCheckedIn = $__isAdmin || \App\Models\Workforce\Attendance::where('technician_id', auth()->id())
            ->whereDate('date', \Carbon\Carbon::today())
            ->whereNotNull('checked_in_at')
            ->exists();
    }
@endphp

<x-layouts.base
    html-class="h-full"
    body-class="antialiased text-slate-800 dark:text-slate-200 bg-slate-50 dark:bg-slate-900 min-h-screen"
>

    <x-slot:bodyAttributes>
        x-data="{ darkMode: localStorage.getItem('dsb_dark_mode') === '1' }" :class="darkMode ? 'dark' : ''"
    </x-slot:bodyAttributes>

    <x-slot:head>
        <meta name="theme-color" content="#ffffff">
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

    <x-slot:title>@yield('title', $title ?? 'Technician Portal')</x-slot:title>

    <!-- App Shell (max-w-md like Customer Portal) -->
    <div class="max-w-md print:max-w-none mx-auto min-h-screen relative bg-slate-50 dark:bg-slate-900 print:bg-white shadow-[0_0_40px_rgba(0,0,0,0.05)] sm:border-x border-slate-200 dark:border-slate-800 print:border-none print:shadow-none flex flex-col">

        <!-- Sticky Top Header (Solid background to prevent text leak on scroll) -->
        <header class="sticky top-0 z-40 bg-white dark:bg-slate-900 border-b border-slate-200 dark:border-slate-800 px-4 h-14 flex items-center justify-between print-hide shadow-xs">
            <div class="flex items-center gap-3 min-w-0 flex-1">
                @if(!$__isDashboard)
                    <button onclick="history.back()" class="p-1.5 -ml-1.5 shrink-0 text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:bg-slate-800 dark:hover:bg-slate-800 rounded-full transition-colors">
                        <span class="material-symbols-outlined" style="font-size:24px">arrow_back</span>
                    </button>
                @endif

                
                    <div class="flex items-center gap-2.5 min-w-0">
                        <div class="w-9 h-9 shrink-0 bg-gradient-to-br from-indigo-500 to-blue-600 text-white inline-flex items-center justify-center rounded-xl shadow-sm">
                            <span class="material-symbols-outlined" style="font-size:20px">engineering</span>
                        </div>
                        <div class="flex flex-col leading-tight min-w-0">
                            <span class="text-base font-extrabold text-indigo-800 dark:text-indigo-400 truncate">Technician</span>
                            <span class="text-[10px] font-medium text-slate-400 dark:text-slate-500 dark:text-slate-400 truncate">Portal Teknisi</span>
                        </div>
                    </div></div>

            <div class="flex items-center gap-1 shrink-0 -mr-1">
                <livewire:notification-center />
                <button @click="darkMode = !darkMode; localStorage.setItem('dsb_dark_mode', darkMode ? '1' : '0')" class="p-1.5 text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:bg-slate-800 dark:hover:bg-slate-800 rounded-full transition-colors">
                    <span class="material-symbols-outlined" style="font-size:22px" x-text="darkMode ? 'light_mode' : 'dark_mode'"></span>
                </button>
                <div class="w-8 h-8 bg-indigo-100 dark:bg-indigo-900 text-indigo-700 dark:text-indigo-300 rounded-full flex items-center justify-center font-bold text-sm">
                    {{ strtoupper(mb_substr(auth()->user()->name ?? 'T', 0, 1)) }}
                </div>
            </div>
        </header>

        @if(!$__hasCheckedIn && !request()->routeIs('technician.attendance'))
            <div class="bg-amber-500 text-white px-4 py-2 text-xs flex items-center justify-between shadow-sm z-30">
                <div class="flex items-center gap-2">
                    <span class="material-symbols-outlined" style="font-size:18px">lock</span>
                    <span class="font-semibold">Wajib Absen Masuk untuk membuka menu pekerjaan</span>
                </div>
                <a href="{{ route('technician.attendance') }}" class="px-2.5 py-1 bg-white text-amber-900 font-bold rounded-lg text-[11px] shadow hover:bg-amber-50 transition-colors shrink-0">
                    Absen Sekarang
                </a>
            </div>
        @endif

        <!-- Main Content -->
        <main class="flex-1 overflow-y-auto pb-20">
            {{ $slot ?? '' }}
            @yield('content')
        </main>

        <!-- Bottom Navigation Bar (Responsive 6-Col Grid, No Overlap) -->
        <nav class="fixed bottom-0 left-1/2 -translate-x-1/2 w-full max-w-md bg-white dark:bg-slate-900 border-t border-slate-200 dark:border-slate-800 px-1 py-1 grid grid-cols-6 items-center justify-items-center z-40 shadow-[0_-4px_24px_rgba(0,0,0,0.06)] print-hide">
            <a href="{{ route('technician.dashboard') }}" class="w-full flex flex-col items-center justify-center py-1 px-0.5 rounded-xl transition-colors {{ request()->routeIs('technician.dashboard') ? 'text-indigo-600 dark:text-indigo-400 bg-indigo-50 dark:bg-indigo-900/40' : 'text-slate-400 dark:text-slate-500 hover:text-slate-700' }}">
                <span class="material-symbols-outlined notranslate shrink-0 {{ request()->routeIs('technician.dashboard') ? 'fill' : '' }}" translate="no" style="font-size:22px; line-height: 1;">home</span>
                <span class="text-[9px] font-semibold truncate max-w-full leading-tight mt-0.5">Home</span>
            </a>
            @if(!$__hasCheckedIn)
                <button type="button" onclick="alert('Silakan lakukan Absen Masuk terlebih dahulu pada menu Absensi.'); window.location.href='{{ route('technician.attendance') }}';" class="w-full flex flex-col items-center justify-center py-1 px-0.5 rounded-xl text-slate-300 dark:text-slate-600 relative" title="Menu Terkunci - Wajib Absen">
                    <span class="material-symbols-outlined notranslate shrink-0" translate="no" style="font-size:22px; line-height: 1;">lock</span>
                    <span class="text-[9px] font-semibold truncate max-w-full leading-tight mt-0.5">PSB</span>
                </button>
                <button type="button" onclick="alert('Silakan lakukan Absen Masuk terlebih dahulu pada menu Absensi.'); window.location.href='{{ route('technician.attendance') }}';" class="w-full flex flex-col items-center justify-center py-1 px-0.5 rounded-xl text-slate-300 dark:text-slate-600 relative" title="Menu Terkunci - Wajib Absen">
                    <span class="material-symbols-outlined notranslate shrink-0" translate="no" style="font-size:22px; line-height: 1;">lock</span>
                    <span class="text-[9px] font-semibold truncate max-w-full leading-tight mt-0.5">Gangguan</span>
                </button>
                <button type="button" onclick="alert('Silakan lakukan Absen Masuk terlebih dahulu pada menu Absensi.'); window.location.href='{{ route('technician.attendance') }}';" class="w-full flex flex-col items-center justify-center py-1 px-0.5 rounded-xl text-slate-300 dark:text-slate-600 relative" title="Menu Terkunci - Wajib Absen">
                    <span class="material-symbols-outlined notranslate shrink-0" translate="no" style="font-size:22px; line-height: 1;">lock</span>
                    <span class="text-[9px] font-semibold truncate max-w-full leading-tight mt-0.5">Instalasi</span>
                </button>
            @else
                <a href="{{ route('technician.my-jobs.index') }}" class="w-full flex flex-col items-center justify-center py-1 px-0.5 rounded-xl transition-colors {{ request()->routeIs('technician.my-jobs.index') ? 'text-indigo-600 dark:text-indigo-400 bg-indigo-50 dark:bg-indigo-900/40' : 'text-slate-400 dark:text-slate-500 hover:text-slate-700' }}">
                    <span class="material-symbols-outlined notranslate shrink-0 {{ request()->routeIs('technician.my-jobs.index') ? 'fill' : '' }}" translate="no" style="font-size:22px; line-height: 1;">assignment</span>
                    <span class="text-[9px] font-semibold truncate max-w-full leading-tight mt-0.5">PSB</span>
                </a>
                <a href="{{ route('technician.my-jobs.troubleshooting') }}" class="w-full flex flex-col items-center justify-center py-1 px-0.5 rounded-xl transition-colors {{ request()->routeIs('technician.my-jobs.troubleshooting') ? 'text-indigo-600 dark:text-indigo-400 bg-indigo-50 dark:bg-indigo-900/40' : 'text-slate-400 dark:text-slate-500 hover:text-slate-700' }}">
                    <span class="material-symbols-outlined notranslate shrink-0 {{ request()->routeIs('technician.my-jobs.troubleshooting') ? 'fill' : '' }}" translate="no" style="font-size:22px; line-height: 1;">build</span>
                    <span class="text-[9px] font-semibold truncate max-w-full leading-tight mt-0.5">Gangguan</span>
                </a>
                <a href="{{ route('technician.installation.wizard') }}" class="w-full flex flex-col items-center justify-center py-1 px-0.5 rounded-xl transition-colors {{ request()->routeIs('technician.installation.*') ? 'text-indigo-600 dark:text-indigo-400 bg-indigo-50 dark:bg-indigo-900/40' : 'text-slate-400 dark:text-slate-500 hover:text-slate-700' }}">
                    <span class="material-symbols-outlined notranslate shrink-0 {{ request()->routeIs('technician.installation.*') ? 'fill' : '' }}" translate="no" style="font-size:22px; line-height: 1;">build_circle</span>
                    <span class="text-[9px] font-semibold truncate max-w-full leading-tight mt-0.5">Instalasi</span>
                </a>
            @endif
            <a href="{{ route('technician.attendance') }}" class="w-full flex flex-col items-center justify-center py-1 px-0.5 rounded-xl transition-colors relative {{ request()->routeIs('technician.attendance') ? 'text-indigo-600 dark:text-indigo-400 bg-indigo-50 dark:bg-indigo-900/40' : ($__hasCheckedIn ? 'text-slate-400 dark:text-slate-500 hover:text-slate-700' : 'text-amber-500 font-bold') }}">
                @if(!$__hasCheckedIn)
                    <span class="absolute top-0 right-3 flex h-2 w-2">
                        <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-red-400 opacity-75"></span>
                        <span class="relative inline-flex rounded-full h-2 w-2 bg-red-500"></span>
                    </span>
                @endif
                <span class="material-symbols-outlined notranslate shrink-0 {{ request()->routeIs('technician.attendance') ? 'fill' : '' }}" translate="no" style="font-size:22px; line-height: 1;">fingerprint</span>
                <span class="text-[9px] font-semibold truncate max-w-full leading-tight mt-0.5">{{ $__hasCheckedIn ? 'Absensi' : 'Absen' }}</span>
            </a>
            <form method="POST" action="{{ route('logout') }}" class="w-full m-0 flex justify-center">
                @csrf
                <button type="submit" class="w-full flex flex-col items-center justify-center py-1 px-0.5 rounded-xl text-slate-400 dark:text-slate-500 hover:text-red-500 transition-colors">
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



