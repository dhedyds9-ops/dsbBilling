<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'dsBilling') }} - Admin Login</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        body { background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%); }
    </style>
</head>
<body class="font-sans text-gray-200 antialiased">
    <div class="min-h-screen flex flex-col sm:justify-center items-center pt-6 sm:pt-0">
        <div>
            <a href="{{ route('home') }}" class="text-3xl font-bold text-blue-500">dsBilling</a>
        </div>

        <div class="w-full sm:max-w-md mt-6 px-6 py-8 bg-slate-800 border border-slate-700 shadow-md overflow-hidden sm:rounded-xl">
            <div class="text-center mb-8">
                <h1 class="text-2xl font-bold text-white">Admin Login</h1>
                <p class="text-slate-400 mt-2">Masuk ke dashboard admin</p>
            </div>
            <!-- Session Status -->
            <x-auth-session-status class="mb-4" :status="session('status')" />

            <form method="POST" action="{{ route('login') }}">
                @csrf
                <input type="hidden" name="login_type" value="admin">

                <!-- Email Address -->
                <div>
                    <x-input-label for="email" :value="__('Email')" class="text-slate-300" />
                    <x-text-input id="email" class="block mt-1 w-full bg-slate-700 border-slate-600 text-white" type="email" name="email" :value="old('email')" required autofocus autocomplete="username" />
                    <x-input-error :messages="$errors->get('email')" class="mt-2" />
                </div>

                <!-- Password -->
                <div class="mt-4">
                    <x-input-label for="password" :value="__('Password')" class="text-slate-300" />
                    <x-text-input id="password" class="block mt-1 w-full bg-slate-700 border-slate-600 text-white" type="password" name="password" required autocomplete="current-password" />
                    <x-input-error :messages="$errors->get('password')" class="mt-2" />
                </div>

                <!-- Remember Me -->
                <div class="block mt-4">
                    <label for="remember_me" class="inline-flex items-center">
                        <input id="remember_me" type="checkbox" class="rounded border-slate-500 bg-slate-700 text-blue-500 focus:ring-blue-500" name="remember">
                        <span class="ms-2 text-sm text-slate-400">{{ __('Remember me') }}</span>
                    </label>
                </div>

                <div class="flex flex-col mt-6">
                    @if (Route::has('password.request'))
                        <a class="underline text-sm text-slate-400 hover:text-white rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500" href="{{ route('password.request') }}">
                            {{ __('Forgot your password?') }}
                        </a>
                    @endif

                    <button type="submit" class="mt-6 inline-flex items-center justify-center w-full px-4 py-3 bg-blue-600 border border-transparent rounded-xl font-semibold text-sm text-white uppercase tracking-widest hover:bg-blue-700 focus:bg-blue-700 active:bg-blue-900 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 focus:ring-offset-slate-800 transition ease-in-out duration-150">
                        {{ __('Log in') }}
                    </button>
                </div>
            </form>
        </div>

        <div class="mt-6 text-center">
            <a href="{{ route('customer.login') }}" class="text-blue-400 hover:text-blue-300 text-sm font-medium transition">Login Pelanggan</a>
        </div>
    </div>
</body>
</html>
