<x-layouts.base
    :title="config('app.name', 'dsBilling') . ' - Login'"
    body-class="font-sans text-slate-900 dark:text-slate-100 antialiased bg-slate-50 dark:bg-slate-900/50 relative flex flex-col items-center justify-center min-h-screen py-10 overflow-y-auto"
>
    <x-slot:head>
        <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700&display=swap" rel="stylesheet" />
        <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@24,400,0,0" />
    </x-slot:head>

    <div class="fixed -top-40 -right-40 w-96 h-96 bg-blue-400 rounded-full mix-blend-multiply filter blur-3xl opacity-20 animate-blob z-0 pointer-events-none"></div>
    <div class="fixed top-20 -left-20 w-72 h-72 bg-indigo-400 rounded-full mix-blend-multiply filter blur-3xl opacity-20 animate-blob animation-delay-2000 z-0 pointer-events-none"></div>
    <div class="fixed -bottom-40 left-20 w-80 h-80 bg-purple-400 rounded-full mix-blend-multiply filter blur-3xl opacity-20 animate-blob animation-delay-4000 z-0 pointer-events-none"></div>

    <div class="relative z-10 w-full max-w-md px-6 py-8">
        <div class="mb-8 text-center">
            <a href="{{ route('home') }}" class="inline-flex items-center gap-3 group">
                @php
                    $logoUrl = \App\Models\Setting::getValue('company.logo_url', null);
                    $companyName = \App\Models\Setting::getValue('company.name', 'dsBilling');
                @endphp
                @if($logoUrl)
                    <img src="{{ $logoUrl }}" class="w-auto h-12 object-contain" alt="Logo">
                @else
                    <x-application-logo class="w-auto h-14" />
                @endif
            </a>
        </div>

        <div class="bg-white dark:bg-slate-800/80 backdrop-blur-xl border border-slate-200 dark:border-slate-700 shadow-xl rounded-2xl overflow-hidden p-8 relative">
            <div class="absolute top-0 left-0 w-full h-1 bg-gradient-to-r from-blue-500 to-indigo-500"></div>
            {{ $slot }}
        </div>

        <div class="mt-8 text-center text-sm text-slate-500 dark:text-slate-400">
            <a href="{{ route('home') }}" class="hover:text-primary-600 transition-colors inline-flex items-center gap-1">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                Kembali ke Beranda
            </a>
            <p class="mt-3">&copy; {{ date('Y') }} dsBilling Enterprise. All rights reserved.</p>
        </div>
    </div>

</x-layouts.base>






