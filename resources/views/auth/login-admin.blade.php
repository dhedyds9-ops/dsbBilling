@php
    $companyName = \App\Models\Setting::getValue('company.name', config('app.name', 'dsBilling'));
    $companyLogo = \App\Models\Setting::getValue('company.logo_url', null);
    $logoSrc = $companyLogo ? (\Illuminate\Support\Str::startsWith($companyLogo, ['http://', 'https://']) ? $companyLogo : asset($companyLogo)) : null;
@endphp
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $companyName }} - Admin Access</title>
    
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Hanken+Grotesk:wght@400;500;600;700;800;900&family=Inter:wght@400;500;600&display=swap" rel="stylesheet" />
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet"/>
    
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans text-slate-300 antialiased bg-slate-950 min-h-screen flex">

    <!-- Left Side: Branding / Visual (Hidden on Mobile) -->
    <div class="hidden lg:flex w-1/2 relative overflow-hidden bg-slate-900 flex-col justify-between p-12 border-r border-white/5">
        <!-- Abstract Background Effects -->
        <div class="absolute -top-40 -left-40 w-96 h-96 bg-cyan-600/20 rounded-full blur-[100px] pointer-events-none"></div>
        <div class="absolute bottom-0 right-0 w-full h-1/2 bg-gradient-to-t from-cyan-900/40 to-transparent pointer-events-none"></div>
        
        <!-- Logo -->
        <div class="relative z-10">
            <a href="{{ route('home') }}" class="inline-flex items-center gap-2 group transition-transform hover:scale-[1.02] focus:outline-none">
                @if($logoSrc)
                    <img src="{{ $logoSrc }}" alt="{{ $companyName }}" class="h-11 w-auto max-w-[240px] object-contain drop-shadow-md"
                         onerror="this.style.display='none'; if(this.nextElementSibling){ this.nextElementSibling.classList.remove('hidden'); }">
                    <div class="hidden">
                        <x-application-logo class="h-11 w-auto max-w-[240px]" />
                    </div>
                @else
                    <x-application-logo class="h-11 w-auto max-w-[240px]" />
                @endif
            </a>
            <div class="mt-3 text-cyan-400/80 font-medium tracking-widest text-xs uppercase letter-spacing-2">ISP Management System</div>
        </div>

        <!-- Hero Text -->
        <div class="relative z-10 mb-10">
            <h1 class="text-5xl font-heading font-extrabold text-white leading-tight mb-4">
                Pusat Kendali<br>
                <span class="text-transparent bg-clip-text bg-gradient-to-r from-cyan-400 to-blue-400">Jaringan Anda.</span>
            </h1>
            <p class="text-slate-400 text-lg max-w-md">
                Kelola infrastruktur jaringan, tagihan pelanggan, dan operasional teknis dalam satu ekosistem terpadu yang cepat dan andal.
            </p>
        </div>
        
        <!-- Footer Info -->
        <div class="relative z-10 flex items-center justify-between text-sm text-slate-500">
            <span>&copy; {{ date('Y') }} {{ $companyName }}</span>
            <span class="flex items-center gap-1"><span class="material-symbols-outlined text-[16px]">verified_user</span> Secure Access</span>
        </div>
    </div>

    <!-- Right Side: Login Form -->
    <div class="w-full lg:w-1/2 flex items-center justify-center p-6 sm:p-12 relative min-h-screen">
        <div class="w-full max-w-md py-6">
            <!-- Mobile Brand Header (Logo & Role Badge) -->
            <div class="lg:hidden flex flex-col items-center text-center mb-8">
                <a href="{{ route('home') }}" class="inline-flex items-center justify-center focus:outline-none transition-transform hover:scale-105">
                    @if($logoSrc)
                        <img src="{{ $logoSrc }}" alt="{{ $companyName }}" class="h-12 w-auto max-w-[220px] object-contain drop-shadow-md"
                             onerror="this.style.display='none'; if(this.nextElementSibling){ this.nextElementSibling.classList.remove('hidden'); }">
                        <div class="hidden">
                            <x-application-logo class="h-10 w-auto max-w-[220px]" />
                        </div>
                    @else
                        <x-application-logo class="h-10 w-auto max-w-[220px]" />
                    @endif
                </a>
                <div class="mt-3 inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-cyan-500/10 border border-cyan-500/20 text-cyan-400 text-xs font-semibold tracking-wide">
                    <span class="material-symbols-outlined text-[18px]">admin_panel_settings</span>
                    <span>Admin Panel</span>
                </div>
            </div>

            <div class="text-center lg:text-left mb-8">
                <h2 class="text-2xl sm:text-3xl font-heading font-bold text-white mb-2">Welcome Back, Admin</h2>
                <p class="text-slate-400 text-sm sm:text-base">Silakan masuk ke ruang kendali Anda.</p>
            </div>

            <!-- Session Status -->
            @if (session('status'))
                <div class="mb-6 p-4 rounded-xl bg-emerald-500/10 border border-emerald-500/20 text-emerald-400 text-sm flex items-start gap-3">
                    <span class="material-symbols-outlined text-[20px]">check_circle</span>
                    <div>{{ session('status') }}</div>
                </div>
            @endif

            <form method="POST" action="{{ route('login') }}" x-data="{ showPassword: false }" class="space-y-6">
                @csrf
                <input type="hidden" name="login_type" value="admin">

                <!-- Identity / Login -->
                <div>
                    <label for="login" class="block text-sm font-semibold text-slate-300 mb-2">Email / Username / Nomor HP</label>
                    <div class="relative group">
                        <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-slate-500 group-focus-within:text-cyan-400 transition-colors">
                            <span class="material-symbols-outlined text-[20px]">person</span>
                        </div>
                        <input id="login" type="text" name="login" value="{{ old('login') }}" required autofocus autocomplete="username"
                            class="block w-full pl-12 pr-4 py-4 bg-slate-900/50 border border-slate-700/50 rounded-2xl text-white placeholder-slate-600 focus:ring-2 focus:ring-cyan-500 focus:border-cyan-500 transition-all shadow-sm"
                            placeholder="Masukkan kredensial Anda">
                    </div>
                    @error('login')
                        <p class="mt-2 text-sm text-red-400 flex items-center gap-1"><span class="material-symbols-outlined text-[16px]">error</span> {{ $message }}</p>
                    @enderror
                </div>

                <!-- Password -->
                <div>
                    <label for="password" class="block text-sm font-semibold text-slate-300 mb-2">Kata Sandi</label>
                    <div class="relative group">
                        <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-slate-500 group-focus-within:text-cyan-400 transition-colors">
                            <span class="material-symbols-outlined text-[20px]">lock</span>
                        </div>
                        <input id="password" x-bind:type="showPassword ? 'text' : 'password'" name="password" required autocomplete="current-password"
                            class="block w-full pl-12 pr-12 py-4 bg-slate-900/50 border border-slate-700/50 rounded-2xl text-white placeholder-slate-600 focus:ring-2 focus:ring-cyan-500 focus:border-cyan-500 transition-all shadow-sm"
                            placeholder="••••••••">
                        <button type="button" @click="showPassword = !showPassword" class="absolute inset-y-0 right-0 pr-4 flex items-center text-slate-500 hover:text-white transition-colors focus:outline-none">
                            <span class="material-symbols-outlined text-[20px]" x-text="showPassword ? 'visibility_off' : 'visibility'">visibility</span>
                        </button>
                    </div>
                    @error('password')
                        <p class="mt-2 text-sm text-red-400 flex items-center gap-1"><span class="material-symbols-outlined text-[16px]">error</span> {{ $message }}</p>
                    @enderror
                </div>

                <!-- Remember Me & Forgot Password -->
                <div class="flex items-center justify-between">
                    <div class="flex items-center">
                        <input id="remember_me" type="checkbox" name="remember" class="w-5 h-5 rounded border-slate-700 bg-slate-900/50 text-cyan-500 focus:ring-cyan-500 focus:ring-offset-slate-950 transition-colors cursor-pointer">
                        <label for="remember_me" class="ml-3 block text-sm text-slate-400 cursor-pointer hover:text-slate-300 transition-colors">
                            Ingat sesi saya
                        </label>
                    </div>
                    @if (Route::has('password.request'))
                        <a href="{{ route('password.request') }}" class="text-sm font-medium text-cyan-400 hover:text-cyan-300 transition-colors flex items-center gap-1">
                            <span class="material-symbols-outlined text-[16px]">chat</span>
                            <span>Lupa Kata Sandi?</span>
                        </a>
                    @endif
                </div>

                <button type="submit" class="w-full flex justify-center items-center gap-2 py-4 px-4 border border-transparent rounded-2xl shadow-lg shadow-cyan-500/20 text-sm font-bold text-white bg-gradient-to-r from-cyan-500 to-blue-600 hover:from-cyan-400 hover:to-blue-500 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-offset-slate-950 focus:ring-cyan-500 transition-all transform hover:-translate-y-0.5">
                    Masuk ke Dashboard
                    <span class="material-symbols-outlined text-[18px]">arrow_forward</span>
                </button>
            </form>

            <div class="mt-12 pt-8 border-t border-slate-800 flex flex-col sm:flex-row items-center justify-between gap-4">
                <a href="{{ route('customer.login') }}" class="text-sm font-medium text-slate-400 hover:text-cyan-400 transition-colors flex items-center gap-1.5">
                    <span class="material-symbols-outlined text-[18px]">account_circle</span>
                    Portal Pelanggan
                </a>
                <a href="{{ route('home') }}" class="text-sm font-medium text-slate-400 hover:text-white transition-colors flex items-center gap-1.5">
                    <span class="material-symbols-outlined text-[18px]">home</span>
                    Kembali ke Beranda
                </a>
            </div>
        </div>
    </div>
</body>
</html>
