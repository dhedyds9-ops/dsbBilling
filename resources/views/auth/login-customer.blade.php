<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'dsBilling') }} - Customer Portal</title>
    
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Hanken+Grotesk:wght@400;500;600;700;800&family=Inter:wght@400;500;600&display=swap" rel="stylesheet" />
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet"/>
    
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Inter', 'sans-serif'],
                        heading: ['Hanken Grotesk', 'sans-serif'],
                    },
                    colors: {
                        slate: {
                            850: '#151e2e',
                            900: '#0f172a',
                        },
                        blue: {
                            400: '#60a5fa',
                            500: '#3b82f6',
                            600: '#2563eb',
                        }
                    }
                }
            }
        }
    </script>
    <style>
        body { 
            background-color: #0f172a;
            background-image: 
                radial-gradient(at 100% 100%, hsla(253,16%,7%,1) 0, transparent 50%), 
                radial-gradient(at 0% 100%, hsla(225,39%,30%,0.2) 0, transparent 50%), 
                radial-gradient(at 50% 50%, hsla(339,49%,30%,0.05) 0, transparent 50%);
            background-attachment: fixed;
        }
    </style>
</head>
<body class="font-sans text-slate-300 antialiased min-h-screen flex items-center justify-center p-4">

    <div class="w-full max-w-md relative z-10">
        
        <!-- Login Card -->
        <div class="bg-slate-800/80 backdrop-blur-xl border border-slate-700/50 rounded-3xl shadow-2xl p-8 relative overflow-hidden">
            <!-- Glow effect -->
            <div class="absolute top-0 left-1/2 -translate-x-1/2 w-64 h-32 bg-blue-500/20 rounded-full blur-3xl pointer-events-none"></div>
            
            <div class="text-center mb-8">
                <a href="{{ route('home') }}" class="inline-block text-4xl font-heading font-black tracking-tight text-transparent bg-clip-text bg-gradient-to-r from-emerald-400 to-teal-500 hover:from-emerald-300 hover:to-teal-400 transition-all">
                    {{ \App\Models\Setting::getValue('company.name', 'dsBilling') }}
                </a>
                <h1 class="text-xl font-heading font-semibold text-blue-400">Customer Portal</h1>
                <p class="text-slate-400 mt-2 text-sm">Manage your billing and services</p>
            </div>

            <!-- Session Status -->
            @if (session('status'))
                <div class="mb-4 p-4 rounded-xl bg-emerald-500/10 border border-emerald-500/20 text-emerald-400 text-sm text-center">
                    {{ session('status') }}
                </div>
            @endif

            <form method="POST" action="{{ route('login') }}" x-data="{ showPassword: false }">
                @csrf
                <input type="hidden" name="login_type" value="customer">

                <!-- Identity / Login -->
                <div class="mb-5">
                    <label for="login" class="block text-sm font-medium text-slate-300 mb-2">Email, Username, or Phone</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                            <span class="material-symbols-outlined text-slate-500 text-xl">person</span>
                        </div>
                        <input id="login" type="text" name="login" value="{{ old('login') }}" required autofocus autocomplete="username"
                            class="block w-full pl-12 pr-4 py-3.5 bg-slate-900/60 border border-slate-700 rounded-2xl text-white placeholder-slate-500 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors sm:text-sm"
                            placeholder="Your credential">
                    </div>
                    @error('login')
                        <p class="mt-2 text-sm text-red-400 pl-2">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Password -->
                <div class="mb-6">
                    <div class="flex justify-between items-center mb-2">
                        <label for="password" class="block text-sm font-medium text-slate-300">Password</label>
                        @if (Route::has('password.request'))
                            <a href="{{ route('password.request') }}" class="text-xs font-medium text-blue-400 hover:text-blue-300 transition-colors">
                                Forgot password?
                            </a>
                        @endif
                    </div>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                            <span class="material-symbols-outlined text-slate-500 text-xl">lock</span>
                        </div>
                        <input id="password" x-bind:type="showPassword ? 'text' : 'password'" name="password" required autocomplete="current-password"
                            class="block w-full pl-12 pr-12 py-3.5 bg-slate-900/60 border border-slate-700 rounded-2xl text-white placeholder-slate-500 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors sm:text-sm"
                            placeholder="••••••••">
                        <button type="button" @click="showPassword = !showPassword" class="absolute inset-y-0 right-0 pr-4 flex items-center text-slate-500 hover:text-slate-300 transition-colors focus:outline-none">
                            <span class="material-symbols-outlined" x-text="showPassword ? 'visibility_off' : 'visibility'">visibility</span>
                        </button>
                    </div>
                    @error('password')
                        <p class="mt-2 text-sm text-red-400 pl-2">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Remember Me -->
                <div class="flex items-center justify-between mb-8">
                    <div class="flex items-center">
                        <input id="remember_me" type="checkbox" name="remember" class="w-4 h-4 rounded border-slate-600 bg-slate-900/60 text-blue-500 focus:ring-blue-500 focus:ring-offset-slate-800">
                        <label for="remember_me" class="ml-2 block text-sm text-slate-400 cursor-pointer">
                            Keep me signed in
                        </label>
                    </div>
                </div>

                <button type="submit" class="w-full flex justify-center items-center py-3.5 px-4 border border-transparent rounded-2xl shadow-sm text-sm font-semibold text-white bg-gradient-to-r from-emerald-500 to-teal-500 hover:from-emerald-400 hover:to-teal-400 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-offset-slate-800 focus:ring-emerald-500 transition-all">
                    Sign In
                    <span class="material-symbols-outlined ml-2 text-lg">login</span>
                </button>
            </form>
        </div>

        <div class="mt-8 flex flex-col gap-4 text-center">
            <p class="text-sm text-slate-500">
                Are you an admin? 
                <a href="{{ route('admin.login') }}" class="font-medium text-slate-300 hover:text-white transition-colors underline decoration-slate-600 hover:decoration-slate-400 underline-offset-4">
                    Admin Portal
                </a>
            </p>
            
            <a href="{{ route('home') }}" class="inline-flex items-center justify-center gap-2 text-sm text-slate-400 hover:text-blue-400 transition-colors mx-auto mt-4 px-4 py-2 rounded-full border border-slate-700 hover:border-blue-500/50 hover:bg-blue-500/10">
                <span class="material-symbols-outlined text-lg">arrow_back</span>
                Kembali ke Halaman Utama
            </a>
        </div>
    </div>

</body>
</html>
