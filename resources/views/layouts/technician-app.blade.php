@php
    $__isDashboard = request()->routeIs('technician.dashboard');
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
            .material-symbols-outlined { font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24; }
            .material-symbols-outlined.fill { font-variation-settings: 'FILL' 1, 'wght' 400, 'GRAD' 0, 'opsz' 24; }
            @media print { .print-hide { display: none !important; } }
        </style>
    </x-slot:head>

    <x-slot:title>@yield('title', $title ?? 'Technician Portal')</x-slot:title>

    <!-- App Shell (max-w-md like Customer Portal) -->
    <div class="max-w-md print:max-w-none mx-auto min-h-screen relative bg-slate-50 dark:bg-slate-900 print:bg-white shadow-[0_0_40px_rgba(0,0,0,0.05)] sm:border-x border-slate-200 dark:border-slate-800 print:border-none print:shadow-none flex flex-col">

        <!-- Sticky Top Header -->
        <header class="sticky top-0 z-40 bg-white/80 dark:bg-slate-900/80 backdrop-blur-md border-b border-slate-100 dark:border-slate-800 px-4 h-14 flex items-center justify-between print-hide">
            <div class="flex items-center gap-3 min-w-0 flex-1">
                @if(!$__isDashboard)
                    <button onclick="history.back()" class="p-1.5 -ml-1.5 shrink-0 text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:bg-slate-800 dark:hover:bg-slate-800 rounded-full transition-colors">
                        <span class="material-symbols-outlined" style="font-size:24px">arrow_back</span>
                    </button>
                @endif

                @php $__headerTitle = trim($__env->yieldContent('header_title')); @endphp
                @if(!empty($__headerTitle))
                    <h1 class="text-lg font-bold text-indigo-800 dark:text-indigo-400 truncate">{{ $__headerTitle }}</h1>
                @else
                    <div class="flex items-center gap-2.5 min-w-0">
                        <div class="w-9 h-9 shrink-0 bg-gradient-to-br from-indigo-500 to-blue-600 text-white inline-flex items-center justify-center rounded-xl shadow-sm">
                            <span class="material-symbols-outlined" style="font-size:20px">engineering</span>
                        </div>
                        <div class="flex flex-col leading-tight min-w-0">
                            <span class="text-base font-extrabold text-indigo-800 dark:text-indigo-400 truncate">Technician</span>
                            <span class="text-[10px] font-medium text-slate-400 dark:text-slate-500 dark:text-slate-400 truncate">Portal Teknisi</span>
                        </div>
                    </div>
                @endif
            </div>

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

        <!-- Main Content -->
        <main class="flex-1 overflow-y-auto pb-20">
            {{ $slot ?? '' }}
            @yield('content')
        </main>

        <!-- Bottom Navigation Bar -->
        <nav class="fixed bottom-0 left-1/2 -translate-x-1/2 w-full max-w-md bg-white dark:bg-slate-900 border-t border-slate-100 dark:border-slate-800 px-2 py-1.5 flex items-center justify-around z-40 shadow-[0_-4px_24px_rgba(0,0,0,0.04)] print-hide">
            <a href="{{ route('technician.dashboard') }}" class="flex flex-col items-center gap-0.5 px-3 py-1.5 rounded-xl transition-colors {{ request()->routeIs('technician.dashboard') ? 'text-indigo-600 dark:text-indigo-400 bg-indigo-50 dark:bg-indigo-900/30' : 'text-slate-400 dark:text-slate-500 dark:text-slate-400' }}">
                <span class="material-symbols-outlined {{ request()->routeIs('technician.dashboard') ? 'fill' : '' }}" style="font-size:24px">home</span>
                <span class="text-[10px] font-semibold">Home</span>
            </a>
            <a href="{{ route('technician.my-jobs.index') }}" class="flex flex-col items-center gap-0.5 px-3 py-1.5 rounded-xl transition-colors {{ request()->routeIs('technician.my-jobs.index') ? 'text-indigo-600 dark:text-indigo-400 bg-indigo-50 dark:bg-indigo-900/30' : 'text-slate-400 dark:text-slate-500 dark:text-slate-400' }}">
                <span class="material-symbols-outlined {{ request()->routeIs('technician.my-jobs.index') ? 'fill' : '' }}" style="font-size:24px">assignment</span>
                <span class="text-[10px] font-semibold">PSB</span>
            </a>
            <a href="{{ route('technician.my-jobs.troubleshooting') }}" class="flex flex-col items-center gap-0.5 px-3 py-1.5 rounded-xl transition-colors {{ request()->routeIs('technician.my-jobs.troubleshooting') ? 'text-indigo-600 dark:text-indigo-400 bg-indigo-50 dark:bg-indigo-900/30' : 'text-slate-400 dark:text-slate-500 dark:text-slate-400' }}">
                <span class="material-symbols-outlined {{ request()->routeIs('technician.my-jobs.troubleshooting') ? 'fill' : '' }}" style="font-size:24px">build</span>
                <span class="text-[10px] font-semibold">Gangguan</span>
            </a>
            <a href="{{ route('technician.installation.wizard') }}" class="flex flex-col items-center gap-0.5 px-3 py-1.5 rounded-xl transition-colors {{ request()->routeIs('technician.installation.*') ? 'text-indigo-600 dark:text-indigo-400 bg-indigo-50 dark:bg-indigo-900/30' : 'text-slate-400 dark:text-slate-500 dark:text-slate-400' }}">
                <span class="material-symbols-outlined {{ request()->routeIs('technician.installation.*') ? 'fill' : '' }}" style="font-size:24px">build_circle</span>
                <span class="text-[10px] font-semibold">Instalasi</span>
            </a>
            <a href="{{ route('technician.attendance') }}" class="flex flex-col items-center gap-0.5 px-3 py-1.5 rounded-xl transition-colors {{ request()->routeIs('technician.attendance') ? 'text-indigo-600 dark:text-indigo-400 bg-indigo-50 dark:bg-indigo-900/30' : 'text-slate-400 dark:text-slate-500 dark:text-slate-400' }}">
                <span class="material-symbols-outlined {{ request()->routeIs('technician.attendance') ? 'fill' : '' }}" style="font-size:24px">fingerprint</span>
                <span class="text-[10px] font-semibold">Absensi</span>
            </a>
            <form method="POST" action="{{ route('logout') }}" class="m-0">
                @csrf
                <button type="submit" class="flex flex-col items-center gap-0.5 px-3 py-1.5 rounded-xl text-slate-400 dark:text-slate-500 dark:text-slate-400 hover:text-red-500 transition-colors">
                    <span class="material-symbols-outlined" style="font-size:24px">logout</span>
                    <span class="text-[10px] font-semibold">Keluar</span>
                </button>
            </form>
        </nav>
    </div>

    <x-ui.toast-manager />

    

</x-layouts.base>

