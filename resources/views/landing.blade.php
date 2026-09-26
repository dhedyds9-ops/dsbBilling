<!DOCTYPE html>
<html class="dark" lang="{{ str_replace('_', '-', app()->getLocale()) }}" style="scroll-behavior: smooth;">
<head>
    <meta charset="utf-8"/>
    <meta content="width=device-width, initial-scale=1.0" name="viewport"/>
    <title>{{ config('app.name', 'dsBilling') }} - Reliable Fiber Optic Connection</title>
    
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
    
    <link href="https://fonts.googleapis.com/css2?family=Hanken+Grotesk:wght@400;700;800;900&amp;family=Inter:wght@400;500;700&amp;display=swap" rel="stylesheet"/>
    <link href="https://fonts.googleapis.com/css2?family=Caveat:wght@400..700&amp;display=swap" rel="stylesheet"/>
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&amp;display=swap" rel="stylesheet"/>
    
    <script id="tailwind-config">
        tailwind.config = {
            darkMode: "class",
            theme: {
                extend: {
                    "colors": {
                        "primary-fixed-dim": "#4cd7f6",
                        "secondary-fixed-dim": "#adc6ff",
                        "surface": "#0b1326",
                        "surface-container": "#171f33",
                        "on-primary": "#003640",
                        "secondary-container": "#0566d9",
                        "surface-bright": "#31394d",
                        "inverse-on-surface": "#283044",
                        "on-primary-container": "#00424f",
                        "tertiary-container": "#1bbd85",
                        "primary": "#4cd7f6",
                        "tertiary-fixed-dim": "#4edea3",
                        "primary-container": "#06b6d4",
                        "tertiary": "#4edea3",
                        "error": "#ffb4ab",
                        "surface-container-lowest": "#060e20",
                        "surface-container-highest": "#2d3449",
                        "on-tertiary": "#003824",
                        "on-tertiary-fixed-variant": "#005236",
                        "on-secondary-fixed": "#001a42",
                        "secondary": "#adc6ff",
                        "outline-variant": "#3d494c",
                        "inverse-primary": "#00687a",
                        "tertiary-fixed": "#6ffbbe",
                        "primary-fixed": "#acedff",
                        "on-error": "#690005",
                        "surface-container-high": "#222a3d",
                        "inverse-surface": "#dae2fd",
                        "on-primary-fixed": "#001f26",
                        "on-surface": "#dae2fd",
                        "on-error-container": "#ffdad6",
                        "on-secondary": "#002e6a",
                        "on-secondary-fixed-variant": "#004395",
                        "on-background": "#dae2fd",
                        "on-surface-variant": "#bcc9cd",
                        "outline": "#869397",
                        "on-secondary-container": "#e6ecff",
                        "background": "#0b1326",
                        "secondary-fixed": "#d8e2ff",
                        "on-primary-fixed-variant": "#004e5c",
                        "surface-dim": "#0b1326",
                        "on-tertiary-container": "#00452e",
                        "surface-variant": "#2d3449",
                        "error-container": "#93000a",
                        "on-tertiary-fixed": "#002113",
                        "surface-tint": "#4cd7f6",
                        "surface-container-low": "#131b2e"
                    },
                    "borderRadius": {
                        "DEFAULT": "1rem",
                        "lg": "0.75rem",
                        "xl": "1rem",
                        "full": "9999px"
                    },
                    "spacing": {
                        "stack-sm": "8px",
                        "container-max": "1280px",
                        "unit": "4px",
                        "margin-desktop": "48px",
                        "margin-mobile": "20px",
                        "gutter": "24px",
                        "stack-md": "16px",
                        "stack-lg": "32px"
                    },
                    "fontFamily": {
                        "label-sm": ["Inter"],
                        "display-lg": ["Hanken Grotesk"],
                        "body-md": ["Inter"],
                        "headline-md": ["Hanken Grotesk"],
                        "headline-lg": ["Hanken Grotesk"],
                        "headline-lg-mobile": ["Hanken Grotesk"],
                        "label-bold": ["Inter"],
                        "body-lg": ["Inter"]
                    },
                    "fontSize": {
                        "label-sm": ["12px", { "lineHeight": "1.2", "fontWeight": "500" }],
                        "display-lg": ["48px", { "lineHeight": "1.1", "letterSpacing": "-0.04em", "fontWeight": "900" }],
                        "body-md": ["16px", { "lineHeight": "1.5", "fontWeight": "400" }],
                        "headline-md": ["24px", { "lineHeight": "1.3", "fontWeight": "700" }],
                        "headline-lg": ["32px", { "lineHeight": "1.2", "letterSpacing": "-0.02em", "fontWeight": "800" }],
                        "headline-lg-mobile": ["28px", { "lineHeight": "1.2", "fontWeight": "800" }],
                        "label-bold": ["14px", { "lineHeight": "1.2", "letterSpacing": "0.05em", "fontWeight": "700" }],
                        "body-lg": ["18px", { "lineHeight": "1.6", "fontWeight": "400" }]
                    }
                }
            }
        }
    </script>
    <style>
        body { background-color: #020617; } /* Deep Slate Level 0 */
        .glass-card {
            background-color: rgba(15, 23, 42, 0.6);
            backdrop-filter: blur(24px);
            border: 1px solid transparent;
            background-image: linear-gradient(rgba(15, 23, 42, 0.6), rgba(15, 23, 42, 0.6)), linear-gradient(135deg, rgba(255,255,255,0.1) 0%, rgba(255,255,255,0.01) 100%);
            background-origin: border-box;
            background-clip: padding-box, border-box;
        }
        .btn-primary {
            background: linear-gradient(135deg, #06b6d4, #0566d9);
            transition: all 0.3s ease;
        }
        .btn-primary:hover {
            box-shadow: 0 0 20px rgba(6, 182, 212, 0.4);
            transform: translateY(-2px);
        }
        .btn-secondary {
            border: 1.5px solid #06b6d4;
            background: transparent;
            transition: all 0.3s ease;
        }
        .btn-secondary:hover {
            background: rgba(6, 182, 212, 0.1);
            transform: translateY(-2px);
        }
        
        /* Subtle glow for interactive elements */
        .glow-hover:hover {
            box-shadow: 0 0 40px 10px rgba(6, 182, 212, 0.1);
            border-color: rgba(6, 182, 212, 0.3);
        }
        /* Hide scrollbar for mobile */
        .hide-scrollbar::-webkit-scrollbar { display: none; }
        .hide-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }
    </style>
</head>
<body class="text-on-background font-body-md antialiased overflow-x-hidden relative" x-data="{ 
    mobileMenuOpen: false, 
    regOpen: false, 
    pkgName: '', 
    vouchOpen: false, 
    vouchId: '', 
    vouchName: '', 
    vouchPrice: '' 
}">

    @php
        $companyName = \App\Models\Setting::getValue('company.name', 'dsBilling');
        $companyLogo = \App\Models\Setting::getValue('company.logo_url', null);
        $companyPhone = \App\Models\Setting::getValue('company.phone', '0800-123-4567');
        $companyEmail = \App\Models\Setting::getValue('company.email', 'support@dsbilling.id');

        $showPppoe = \Illuminate\Support\Collection::wrap($packagesPppoe ?? []);
        $showMemberHotspot = \Illuminate\Support\Collection::wrap($packagesHotspot ?? []);
        $showHotspotVoucher = \Illuminate\Support\Collection::wrap($packagesVoucher ?? []);
    @endphp

<!-- TopNavBar -->
<header class="bg-surface/60 dark:bg-surface/60 backdrop-blur-3xl border-b border-white/10 shadow-[0_8px_32px_0_rgba(0,0,0,0.36)] fixed top-0 w-full z-50">
    <div class="flex justify-between items-center px-margin-mobile md:px-margin-desktop py-4 max-w-container-max mx-auto">
        <!-- Brand -->
        <a href="{{ route('home') }}" class="flex items-center gap-2 font-display-lg text-headline-lg font-black text-primary tracking-tighter">
            @if($companyLogo)
                <img src="{{ asset($companyLogo) }}" alt="{{ $companyName }}" class="h-10 w-auto object-contain">
            @else
                <x-application-logo class="h-10 w-auto" />
            @endif
        </a>
        
        <!-- Navigation Links (Desktop) -->
        <nav class="hidden md:flex items-center gap-gutter">
            <a class="text-on-surface-variant hover:text-primary transition-colors font-label-bold text-label-bold duration-200 hover:scale-95 hover:bg-primary/10 hover:shadow-[0_0_15px_rgba(76,215,246,0.3)] rounded px-2 pb-1" href="#keunggulan">Keunggulan</a>
            <a class="text-on-surface-variant hover:text-primary transition-colors font-label-bold text-label-bold duration-200 hover:scale-95 hover:bg-primary/10 hover:shadow-[0_0_15px_rgba(76,215,246,0.3)] rounded px-2 pb-1" href="#paket">Paket Internet</a>
            <a class="text-on-surface-variant hover:text-primary transition-colors font-label-bold text-label-bold duration-200 hover:scale-95 hover:bg-primary/10 hover:shadow-[0_0_15px_rgba(76,215,246,0.3)] rounded px-2 pb-1" href="#hotspot">Hotspot</a>
        </nav>
        
        <!-- Actions -->
        <div class="hidden md:flex items-center gap-3">
            <a href="{{ route('admin.login') }}" class="flex items-center gap-2 px-5 py-2 rounded-full text-sm font-bold bg-white/5 hover:bg-white/10 border border-white/10 text-slate-300 transition-all shadow-sm">
                <span class="material-symbols-outlined text-[18px]">admin_panel_settings</span>
                Admin
            </a>
            <a href="{{ route('guest.payment') }}" class="flex items-center gap-2 px-5 py-2 rounded-full text-sm font-bold bg-cyan-500/10 hover:bg-cyan-500/20 border border-cyan-500/30 text-cyan-400 transition-all shadow-sm">
                Cek Tagihan
            </a>
            <a href="{{ route('customer.login') }}" class="flex items-center gap-2 px-6 py-2 rounded-full text-sm font-bold bg-gradient-to-r from-cyan-500 to-blue-500 hover:from-cyan-400 hover:to-blue-400 text-white transition-all shadow-lg shadow-cyan-500/25">
                <span class="material-symbols-outlined text-[18px]">account_circle</span>
                Portal Pelanggan
            </a>
        </div>
        
        <!-- Mobile menu button -->
        <button @click="mobileMenuOpen = !mobileMenuOpen" class="md:hidden text-primary p-2">
            <span class="material-symbols-outlined" x-show="!mobileMenuOpen">menu</span>
            <span class="material-symbols-outlined" x-show="mobileMenuOpen" x-cloak>close</span>
        </button>
                </div>
    
    <!-- Mobile Menu -->
    <div x-show="mobileMenuOpen" x-collapse x-cloak class="md:hidden bg-surface-container-high border-t border-white/10 px-margin-mobile py-4 shadow-xl">
        <div class="flex flex-col gap-4">
            <a href="#keunggulan" @click="mobileMenuOpen = false" class="text-on-surface font-label-bold">Keunggulan</a>
            <a href="#paket" @click="mobileMenuOpen = false" class="text-on-surface font-label-bold">Paket Internet</a>
            <a href="#hotspot" @click="mobileMenuOpen = false" class="text-on-surface font-label-bold">Hotspot</a>
            <hr class="border-white/10 my-2">
            <a href="{{ route('admin.login') }}" class="flex items-center justify-center gap-2 px-6 py-3 rounded-xl text-sm font-bold bg-white/5 hover:bg-white/10 border border-white/10 text-slate-300 transition-all">
                <span class="material-symbols-outlined text-[18px]">admin_panel_settings</span>
                Login Admin
            </a>
            <a href="{{ route('guest.payment') }}" class="flex items-center justify-center gap-2 px-6 py-3 rounded-xl text-sm font-bold bg-cyan-500/10 hover:bg-cyan-500/20 border border-cyan-500/30 text-cyan-400 transition-all">
                Cek Tagihan
            </a>
            <a href="{{ route('customer.login') }}" class="flex items-center justify-center gap-2 px-6 py-3 rounded-xl text-sm font-bold bg-gradient-to-r from-cyan-500 to-blue-500 hover:from-cyan-400 hover:to-blue-400 text-white transition-all shadow-lg shadow-cyan-500/25">
                <span class="material-symbols-outlined text-[18px]">account_circle</span>
                Portal Pelanggan
            </a>
        </div>
    </div>
</header>

<main class="pt-24 md:pt-32 pb-24">
    <!-- Hero Section -->
    <section class="relative min-h-[80vh] flex items-center justify-center px-margin-mobile md:px-margin-desktop max-w-container-max mx-auto mb-32">
        <div class="absolute inset-0 z-[-1] overflow-hidden rounded-3xl">
            <div class="absolute top-1/4 left-1/4 w-96 h-96 bg-primary/20 rounded-full blur-[120px] mix-blend-screen"></div>
            <div class="absolute bottom-1/4 right-1/4 w-[30rem] h-[30rem] bg-secondary-container/20 rounded-full blur-[150px] mix-blend-screen"></div>
            <div class="w-full h-full bg-cover bg-center opacity-30" style="background-image: url('{{ asset('images/fiber_network.jpg') }}')"></div>
                </div>
        
        <div class="text-center z-10 max-w-4xl">
            <h1 class="font-display-lg text-headline-lg-mobile md:text-display-lg text-on-surface mb-stack-md">
                Koneksi Terbaik Untuk Keluarga Anda
            </h1>
            <p class="font-body-lg text-body-lg text-on-surface-variant mb-stack-lg max-w-2xl mx-auto">
                Rasakan kecepatan internet tanpa batas dengan teknologi Full Fiber Optic dari {{ $companyName }}. Stabil, cepat, dan dapat diandalkan untuk segala kebutuhan digital Anda.
            </p>
            <div class="flex flex-col sm:flex-row justify-center gap-4 mt-8">
                <a class="flex items-center justify-center gap-2 px-8 py-4 rounded-full text-base font-bold bg-gradient-to-r from-cyan-500 to-blue-500 hover:from-cyan-400 hover:to-blue-400 text-white transition-all shadow-lg shadow-cyan-500/25 hover:-translate-y-1" href="#paket">
                    <span class="material-symbols-outlined">rocket_launch</span>
                    Lihat Paket Internet
                </a>
                <a class="flex items-center justify-center gap-2 px-8 py-4 rounded-full text-base font-bold bg-cyan-500/10 hover:bg-cyan-500/20 border border-cyan-500/30 text-cyan-400 transition-all shadow-sm hover:-translate-y-1" href="https://wa.me/{{ preg_replace('/\D/', '', $companyPhone) }}" target="_blank">
                    <span class="material-symbols-outlined">forum</span>
                    Hubungi Kami
                </a>
            </div>
        </div>
    </section>

    <!-- Features (Keunggulan) -->
    <section class="px-margin-mobile md:px-margin-desktop max-w-container-max mx-auto mb-32" id="keunggulan">
        <div class="text-center mb-16">
            <h2 class="font-headline-lg text-headline-lg text-primary mb-2" style="font-family: Caveat, cursive; font-size: 3.5rem; line-height: 1;">Mengapa Memilih Kami?</h2>
            <p class="text-on-surface-variant font-body-md text-body-md">Kualitas layanan premium yang tidak kompromi pada performa.</p>
                    </div>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-gutter">
            <!-- Feature 1 -->
            <div class="glass-card p-8 rounded-xl flex flex-col items-center text-center glow-hover transition-all duration-300">
                <div class="w-16 h-16 rounded-full bg-primary/10 flex items-center justify-center mb-6 shadow-[0_0_20px_rgba(76,215,246,0.2)]">
                    <span class="material-symbols-outlined text-primary text-3xl" style="font-variation-settings: 'FILL' 1;">speed</span>
                </div>
                <h3 class="font-headline-md text-headline-md text-on-surface mb-4">Super Cepat</h3>
                <p class="text-on-surface-variant font-body-md text-body-md">Didukung infrastruktur modern menjamin kecepatan maksimal dengan latensi rendah untuk gaming dan streaming.</p>
            </div>
            <!-- Feature 2 -->
            <div class="glass-card p-8 rounded-xl flex flex-col items-center text-center glow-hover transition-all duration-300">
                <div class="w-16 h-16 rounded-full bg-secondary-container/20 flex items-center justify-center mb-6 shadow-[0_0_20px_rgba(5,102,217,0.2)]">
                    <span class="material-symbols-outlined text-primary text-3xl" style="font-variation-settings: 'FILL' 1;">all_inclusive</span>
                </div>
                <h3 class="font-headline-md text-headline-md text-on-surface mb-4">Unlimited Quota</h3>
                <p class="text-on-surface-variant font-body-md text-body-md">Tanpa FUP. Bebas akses internet sepuasnya tanpa khawatir kecepatan diturunkan di akhir bulan.</p>
            </div>
            <!-- Feature 3 -->
            <div class="glass-card p-8 rounded-xl flex flex-col items-center text-center glow-hover transition-all duration-300">
                <div class="w-16 h-16 rounded-full bg-tertiary-container/20 flex items-center justify-center mb-6 shadow-[0_0_20px_rgba(27,189,133,0.2)]">
                    <span class="material-symbols-outlined text-primary text-3xl" style="font-variation-settings: 'FILL' 1;">support_agent</span>
                </div>
                <h3 class="font-headline-md text-headline-md text-on-surface mb-4">Support 24/7</h3>
                <p class="text-on-surface-variant font-body-md text-body-md">Tim teknisi kami siap sedia melayani Anda kapanpun kendala terjadi, memastikan koneksi Anda selalu on.</p>
            </div>
        </div>
    </section>

    <!-- Internet Packages -->
    @if($showPppoe->isNotEmpty())
    <section class="px-margin-mobile md:px-margin-desktop max-w-container-max mx-auto mb-32" id="paket">
            <div class="text-center mb-16">
            <h2 class="font-headline-lg text-headline-lg text-primary mb-2" style="font-family: Caveat, cursive; font-size: 3.5rem; line-height: 1;">Paket Internet Rumah</h2>
            <p class="text-on-surface-variant font-body-md text-body-md">Pilih paket yang sesuai dengan kebutuhan digital keluarga Anda.</p>
        </div>
        <div class="flex  lg:grid lg:grid-cols-3 gap-gutter items-center pb-8 snap-x snap-mandatory hide-scrollbar -mx-margin-mobile px-margin-mobile lg:mx-0 lg:px-0">
            @foreach($showPppoe as $index => $pkg)
                @if($index == 1 || (count($showPppoe) == 1))
                    <!-- Package 2 (Popular) -->
                    <div class="w-[85vw] sm:w-[350px] lg:w-auto shrink-0 snap-center glass-card p-10 rounded-[2rem] flex flex-col border-[1.5px] border-primary/50 shadow-[0_0_30px_rgba(6,182,212,0.15)] relative transform lg:-translate-y-4 z-10 bg-surface-container-high/80 h-full">
                        <div class="absolute -top-4 left-1/2 -translate-x-1/2 bg-primary text-on-primary px-4 py-1 rounded-full font-label-bold text-label-bold text-sm whitespace-nowrap shadow-[0_0_15px_rgba(6,182,212,0.5)]">
                            Paling Populer
                        </div>
                        <div class="mb-8 mt-2">
                            <span class="inline-block px-3 py-1 bg-primary/20 text-primary font-label-sm text-label-sm rounded-full mb-4">{{ $pkg->name }}</span>
                            <h3 class="font-display-lg text-[48px] text-on-surface leading-none mb-2 text-transparent bg-clip-text bg-gradient-to-r from-primary to-secondary">{{ preg_replace('/[^0-9]/', '', $pkg->description ?? $pkg->name ?? '30') }} <span class="text-xl font-normal text-on-surface-variant">Mbps</span></h3>
                            <p class="font-headline-md text-headline-md text-primary">Rp {{ number_format($pkg->price ?? $pkg->base_price ?? 0, 0, ',', '.') }}<span class="text-sm font-normal text-on-surface-variant">/bln</span></p>
                        </div>
                        <ul class="space-y-4 mb-8 flex-grow">
                            <li class="flex items-center gap-3 text-on-surface font-body-md text-body-md">
                                <span class="material-symbols-outlined text-primary text-sm" style="font-variation-settings: 'FILL' 1;">check_circle</span>
                                {{ $pkg->feature1 ?? 'Sangat Cepat & Stabil' }}
                            </li>
                            <li class="flex items-center gap-3 text-on-surface font-body-md text-body-md">
                                <span class="material-symbols-outlined text-primary text-sm" style="font-variation-settings: 'FILL' 1;">check_circle</span>
                                {{ $pkg->feature2 ?? 'Cocok Untuk Keluarga' }}
                            </li>
                            <li class="flex items-center gap-3 text-on-surface font-body-md text-body-md">
                                <span class="material-symbols-outlined text-primary text-sm" style="font-variation-settings: 'FILL' 1;">check_circle</span>
                                {{ $pkg->feature3 ?? 'Unlimited Quota' }}
                            </li>
                        </ul>
                        <button @click.prevent="pkgName = '{{ addslashes($pkg->name) }}'; regOpen = true" class="w-full btn-primary text-center text-white font-label-bold text-label-bold py-4 rounded-xl mt-auto transition-all">
                            Berlangganan Sekarang
                        </button>
                    </div>
                @else
                    <!-- Package Standard -->
                    <div class="w-[85vw] sm:w-[350px] lg:w-auto shrink-0 snap-center glass-card p-8 rounded-[2rem] flex flex-col glow-hover transition-all duration-300 lg:translate-y-4 h-full">
                        <div class="mb-8">
                            <span class="inline-block px-3 py-1 bg-surface-variant text-on-surface-variant font-label-sm text-label-sm rounded-full mb-4">{{ $pkg->name }}</span>
                            <h3 class="font-display-lg text-[40px] text-on-surface leading-none mb-2">{{ preg_replace('/[^0-9]/', '', $pkg->description ?? $pkg->name ?? '10') }} <span class="text-xl font-normal text-on-surface-variant">Mbps</span></h3>
                            <p class="font-headline-md text-headline-md text-primary">Rp {{ number_format($pkg->price ?? $pkg->base_price ?? 0, 0, ',', '.') }}<span class="text-sm font-normal text-on-surface-variant">/bln</span></p>
                        </div>
                        <ul class="space-y-4 mb-8 flex-grow">
                            <li class="flex items-center gap-3 text-on-surface-variant font-body-md text-body-md">
                                <span class="material-symbols-outlined text-tertiary-fixed-dim text-sm" style="font-variation-settings: 'FILL' 1;">check_circle</span>
                                {{ $pkg->feature1 ?? 'Koneksi Stabil' }}
                            </li>
                            <li class="flex items-center gap-3 text-on-surface-variant font-body-md text-body-md">
                                <span class="material-symbols-outlined text-tertiary-fixed-dim text-sm" style="font-variation-settings: 'FILL' 1;">check_circle</span>
                                {{ $pkg->feature2 ?? 'Browsing Lancar' }}
                            </li>
                            <li class="flex items-center gap-3 text-on-surface-variant font-body-md text-body-md">
                                <span class="material-symbols-outlined text-tertiary-fixed-dim text-sm" style="font-variation-settings: 'FILL' 1;">check_circle</span>
                                {{ $pkg->feature3 ?? 'Tanpa FUP' }}
                            </li>
                        </ul>
                        <button @click.prevent="pkgName = '{{ addslashes($pkg->name) }}'; regOpen = true" class="w-full btn-secondary text-center text-primary font-label-bold text-label-bold py-3 rounded-xl mt-auto transition-all">
                            Pilih Paket
                        </button>
                    </div>
                @endif
            @endforeach
        </div>
    </section>
    @endif

    <!-- Hotspot Section -->
    @if($showHotspotVoucher->isNotEmpty())
    <section class="px-margin-mobile md:px-margin-desktop max-w-container-max mx-auto mb-32 bg-surface-container/30 rounded-3xl p-8 md:p-16 border border-white/5" id="hotspot">
        <div class="flex flex-col md:flex-row justify-between items-center mb-12">
            <div>
                <h2 class="font-headline-lg text-headline-lg text-primary mb-2" style="font-family: Caveat, cursive; font-size: 3.5rem; line-height: 1;">Voucher Hotspot</h2>
                <p class="text-on-surface-variant font-body-md text-body-md">Akses internet cepat di area publik dengan sistem voucher prabayar.</p>
            </div>
            <div class="mt-6 md:mt-0">
                <span class="material-symbols-outlined text-5xl text-primary/50" style="font-variation-settings: 'FILL' 0;">wifi_tethering</span>
            </div>
        </div>
        <div class="flex  lg:grid lg:grid-cols-4 gap-4 sm:gap-6 pb-8 snap-x snap-mandatory hide-scrollbar -mx-margin-mobile px-margin-mobile lg:mx-0 lg:px-0">
            @foreach($showHotspotVoucher as $hot)
            <div class="w-[75vw] sm:w-[280px] lg:w-auto shrink-0 snap-center relative bg-surface-container-high/80 rounded-xl overflow-hidden flex flex-col border border-white/10 shadow-lg h-full" style="mask-composite: source-in;">
                <div class="p-6 flex-grow bg-gradient-to-br from-primary/10 to-secondary/5">
                    <div class="flex justify-between items-start mb-4">
                        <div class="flex flex-col">
                            <span class="text-on-surface font-headline-md text-headline-md">{{ $hot->name }}</span>
                            <span class="text-primary/70 text-label-sm">{{ $hot->description ?? 'Akses Unlimited' }}</span>
                        </div>
                        <span class="material-symbols-outlined text-primary/70 text-3xl">{{ $hot->icon ?? 'timer' }}</span>
                    </div>
                    <div class="flex items-center gap-2 mt-4 opacity-40">
                        <span class="material-symbols-outlined text-4xl">qr_code_2</span>
                        <div class="h-px flex-grow border-t border-dashed border-white/20"></div>
                    </div>
                </div>
                <div class="border-t border-dashed border-white/20 mx-4"></div>
                <div class="p-6 bg-surface-container-highest/50">
                    <div class="flex justify-between items-center mb-4">
                        <span class="text-primary font-display-lg text-headline-md">Rp {{ number_format($hot->price ?? $hot->base_price ?? 0, 0, ',', '.') }}</span>
                        <span class="material-symbols-outlined text-on-surface-variant">confirmation_number</span>
                    </div>
                    <button @click.prevent="vouchId = '{{ $hot->id }}'; vouchName = '{{ addslashes($hot->name) }}'; vouchPrice = 'Rp {{ number_format($hot->price ?? $hot->base_price ?? 0, 0, ',', '.') }}'; vouchOpen = true" class="w-full btn-primary text-white py-3 rounded-lg text-sm font-label-bold transition-all mt-auto">Beli Voucher</button>
                </div>
            </div>
            @endforeach
        </div>
    </section>
    @endif

    <!-- Member Hotspot Section -->
    @if($showMemberHotspot->isNotEmpty())
    <section class="px-margin-mobile md:px-margin-desktop max-w-container-max mx-auto mb-32" id="member-hotspot">
            <div class="text-center mb-16">
            <h2 class="font-headline-lg text-headline-lg text-primary mb-2" style="font-family: Caveat, cursive; font-size: 3.5rem; line-height: 1;">Member Hotspot</h2>
            <p class="text-on-surface-variant font-body-md text-body-md">Langganan bulanan untuk akses tanpa batas di seluruh jaringan hotspot kami.</p>
            </div>
        <div class="flex  lg:grid lg:grid-cols-3 gap-gutter pb-8 snap-x snap-mandatory hide-scrollbar -mx-margin-mobile px-margin-mobile lg:mx-0 lg:px-0">
            @foreach($showMemberHotspot as $index => $mem)
                @if($index == 1 || (count($showMemberHotspot) == 1))
                    <div class="w-[85vw] sm:w-[350px] lg:w-auto shrink-0 snap-center glass-card p-8 rounded-[2rem] flex flex-col glow-hover transition-all duration-300 border-[1.5px] border-primary/30 bg-surface-container-high/80 shadow-[0_0_30px_rgba(6,182,212,0.1)] h-full">
                        <div class="mb-8">
                            <span class="inline-block px-3 py-1 bg-primary/20 text-primary font-label-sm text-label-sm rounded-full mb-4">{{ $mem->description ?? 'Pro Member' }}</span>
                            <h3 class="font-headline-md text-headline-md text-on-surface mb-2">{{ $mem->name }}</h3>
                            <p class="font-headline-md text-headline-md text-primary">Rp {{ number_format($mem->price ?? $mem->base_price ?? 0, 0, ',', '.') }}<span class="text-sm font-normal text-on-surface-variant">/bln</span></p>
                    </div>
                        <ul class="space-y-4 mb-8 flex-grow">
                            <li class="flex items-center gap-3 text-on-surface font-body-md text-body-md">
                                <span class="material-symbols-outlined text-primary text-sm" style="font-variation-settings: 'FILL' 1;">check_circle</span>
                                {{ $mem->feature1 ?? '2 Devices Concurrent' }}
                            </li>
                            <li class="flex items-center gap-3 text-on-surface font-body-md text-body-md">
                                <span class="material-symbols-outlined text-primary text-sm" style="font-variation-settings: 'FILL' 1;">check_circle</span>
                                {{ $mem->feature2 ?? 'Priority Bandwidth' }}
                            </li>
                            <li class="flex items-center gap-3 text-on-surface font-body-md text-body-md">
                                <span class="material-symbols-outlined text-primary text-sm" style="font-variation-settings: 'FILL' 1;">check_circle</span>
                                Automatic Reconnect
                            </li>
                    </ul>
                        <button @click.prevent="pkgName = '{{ addslashes($mem->name) }}'; regOpen = true" class="w-full btn-primary text-white font-label-bold text-label-bold py-3 rounded-xl mt-auto">Daftar Member</button>
                    </div>
                @else
                    <div class="w-[85vw] sm:w-[350px] lg:w-auto shrink-0 snap-center glass-card p-8 rounded-[2rem] flex flex-col glow-hover transition-all duration-300 border border-white/10 h-full">
                        <div class="mb-8">
                            <span class="inline-block px-3 py-1 bg-surface-variant text-on-surface-variant font-label-sm text-label-sm rounded-full mb-4">{{ $mem->description ?? 'Starter Member' }}</span>
                            <h3 class="font-headline-md text-headline-md text-on-surface mb-2">{{ $mem->name }}</h3>
                            <p class="font-headline-md text-headline-md text-primary">Rp {{ number_format($mem->price ?? $mem->base_price ?? 0, 0, ',', '.') }}<span class="text-sm font-normal text-on-surface-variant">/bln</span></p>
                </div>
                        <ul class="space-y-4 mb-8 flex-grow">
                            <li class="flex items-center gap-3 text-on-surface-variant font-body-md text-body-md">
                                <span class="material-symbols-outlined text-primary text-sm" style="font-variation-settings: 'FILL' 1;">check_circle</span>
                                {{ $mem->feature1 ?? 'Single Device Login' }}
                            </li>
                            <li class="flex items-center gap-3 text-on-surface-variant font-body-md text-body-md">
                                <span class="material-symbols-outlined text-primary text-sm" style="font-variation-settings: 'FILL' 1;">check_circle</span>
                                {{ $mem->feature2 ?? 'Standard Bandwidth' }}
                            </li>
                            <li class="flex items-center gap-3 text-on-surface-variant font-body-md text-body-md">
                                <span class="material-symbols-outlined text-primary text-sm" style="font-variation-settings: 'FILL' 1;">check_circle</span>
                                Automatic Reconnect
                            </li>
                        </ul>
                        <button @click.prevent="pkgName = '{{ addslashes($mem->name) }}'; regOpen = true" class="w-full btn-secondary text-primary font-label-bold text-label-bold py-3 rounded-xl mt-auto">Daftar Member</button>
                    </div>
                @endif
            @endforeach
        </div>
    </section>
    @endif

    <!-- Billing CTA -->
    <section class="px-margin-mobile md:px-margin-desktop max-w-container-max mx-auto">
        <div class="relative overflow-hidden rounded-[2rem] bg-gradient-to-r from-surface-container-high to-surface p-8 md:p-12 border border-primary/20 shadow-[0_0_40px_rgba(6,182,212,0.1)] flex flex-col md:flex-row items-center justify-between gap-8">
            <div class="absolute -right-20 -top-20 w-64 h-64 bg-primary/10 rounded-full blur-[60px]"></div>
            <div class="z-10 text-center md:text-left">
                <h2 class="font-headline-lg text-headline-lg text-on-surface mb-2">Sudah Waktunya Bayar Tagihan?</h2>
                <p class="text-on-surface-variant font-body-md text-body-md max-w-xl">Cek status tagihan Anda dengan mudah dan lakukan pembayaran secara online melalui portal pelanggan kami yang aman.</p>
                </div>
            <div class="z-10 flex-shrink-0">
                <a href="{{ route('guest.payment') }}" class="bg-[#1bbd85] hover:bg-[#4edea3] text-on-tertiary font-label-bold text-label-bold px-8 py-4 rounded-xl flex items-center gap-3 transition-colors shadow-[0_0_20px_rgba(27,189,133,0.3)] animate-pulse hover:animate-none">
                    <span class="material-symbols-outlined" style="font-variation-settings: 'FILL' 1;">receipt_long</span>
                    Cek &amp; Bayar Tagihan
                </a>
            </div>
        </div>
    </section>
</main>

    <!-- Footer -->
<footer class="bg-surface-container-lowest dark:bg-surface-container-lowest border-t border-outline-variant w-full py-stack-lg px-margin-mobile md:px-margin-desktop grid grid-cols-1 md:grid-cols-4 gap-gutter items-center">
    <div class="flex flex-col gap-2 md:col-span-2">
        <div class="font-headline-md text-headline-md font-bold text-primary mb-2">
            {{ $companyName }}
                </div>
        <p class="font-body-md text-body-md text-on-surface-variant flex items-center gap-4">
            <span>&copy; {{ date('Y') }} {{ $companyName }}. Reliable Fiber Optic Connection.</span>
            <a href="{{ route('admin.login') }}" class="text-on-surface-variant hover:text-primary transition-colors flex items-center gap-1 opacity-50 hover:opacity-100" title="Admin Login">
                <span class="material-symbols-outlined text-sm">admin_panel_settings</span>
            </a>
        </p>
    </div>
    <div class="flex flex-col sm:flex-row gap-6 md:col-span-2 md:justify-end">
        <a class="font-label-sm text-label-sm text-on-surface-variant hover:text-secondary transition-all hover:translate-x-1 duration-300" href="#">Tentang Kami</a>
        <a class="font-label-sm text-label-sm text-on-surface-variant hover:text-secondary transition-all hover:translate-x-1 duration-300" href="#">Syarat &amp; Ketentuan</a>
        <a class="font-label-sm text-label-sm text-on-surface-variant hover:text-secondary transition-all hover:translate-x-1 duration-300" href="#">Kebijakan Privasi</a>
        <a class="font-label-sm text-label-sm text-on-surface-variant hover:text-secondary transition-all hover:translate-x-1 duration-300" href="#">Bantuan</a>
    </div>
</footer>

<!-- Registration Modal -->
<div x-show="regOpen" x-cloak class="fixed inset-0 z-[100] flex items-center justify-center p-4 sm:p-6" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <div x-show="regOpen" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" class="fixed inset-0 bg-background/80 backdrop-blur-sm transition-opacity" @click="regOpen = false"></div>

    <div x-show="regOpen" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100" x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" class="relative transform overflow-hidden rounded-[2rem] bg-surface-container-high border border-white/10 text-left shadow-2xl transition-all w-full max-w-md p-8">
        
        <div class="absolute right-4 top-4">
            <button @click="regOpen = false" class="text-on-surface-variant hover:text-white transition-colors">
                <span class="material-symbols-outlined">close</span>
            </button>
        </div>

        <div class="mb-6">
            <h3 class="font-headline-md text-headline-md text-primary mb-2" id="modal-title">Form Pendaftaran Baru</h3>
            <p class="text-on-surface-variant font-body-md text-sm">Anda memilih: <strong x-text="pkgName" class="text-on-surface"></strong></p>
                </div>

        <form action="{{ route('register.lead') }}" method="POST" class="space-y-4">
            @csrf
            <input type="hidden" name="package" x-model="pkgName">
            
                <div>
                <label for="name" class="block font-label-bold text-label-sm text-on-surface mb-1">Nama Lengkap</label>
                <input type="text" name="name" id="name" required class="w-full bg-surface/50 border border-outline-variant rounded-xl px-4 py-3 text-on-surface focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary transition-colors dark:bg-slate-900 dark:text-slate-100" placeholder="Masukkan nama Anda">
                </div>
            
            <div>
                <label for="phone" class="block font-label-bold text-label-sm text-on-surface mb-1">Nomor WhatsApp</label>
                <input type="tel" name="phone" id="phone" required class="w-full bg-surface/50 border border-outline-variant rounded-xl px-4 py-3 text-on-surface focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary transition-colors dark:bg-slate-900 dark:text-slate-100" placeholder="08xxxxxxxx">
            </div>
            
            <div>
                <label for="address" class="block font-label-bold text-label-sm text-on-surface mb-1">Alamat Pemasangan</label>
                <textarea name="address" id="address" rows="3" required class="w-full bg-surface/50 border border-outline-variant rounded-xl px-4 py-3 text-on-surface focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary transition-colors dark:bg-slate-900 dark:text-slate-100" placeholder="Detail alamat untuk pemasangan / survey"></textarea>
            </div>
            
            <button type="submit" class="w-full btn-primary text-white font-label-bold py-4 rounded-xl mt-4 flex justify-center items-center gap-2">
                <span class="material-symbols-outlined text-sm">send</span> Kirim Pendaftaran
            </button>
        </form>
    </div>
</div>

<!-- Voucher Buy Modal -->
<div x-show="vouchOpen" x-cloak class="fixed inset-0 z-[100] flex items-center justify-center p-4 sm:p-6" aria-labelledby="modal-voucher" role="dialog" aria-modal="true">
    <div x-show="vouchOpen" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" class="fixed inset-0 bg-background/80 backdrop-blur-sm transition-opacity" @click="vouchOpen = false"></div>

    <div x-show="vouchOpen" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100" x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" class="relative transform overflow-hidden rounded-[2rem] bg-surface-container-high border border-white/10 text-left shadow-2xl transition-all w-full max-w-md p-8">
        
        <div class="absolute right-4 top-4">
            <button @click="vouchOpen = false" class="text-on-surface-variant hover:text-white transition-colors">
                <span class="material-symbols-outlined">close</span>
            </button>
        </div>

        <div class="mb-6">
            <h3 class="font-headline-md text-headline-md text-primary mb-2" id="modal-voucher">Beli Voucher</h3>
            <p class="text-on-surface-variant font-body-md text-sm">Voucher: <strong x-text="vouchName" class="text-on-surface"></strong> (<span x-text="vouchPrice"></span>)</p>
        </div>

        <form action="{{ route('buy-voucher-guest') }}" method="POST" class="space-y-4">
            @csrf
            <input type="hidden" name="service_profile_id" x-model="vouchId">
            
            <div>
                <label for="v_phone" class="block font-label-bold text-label-sm text-on-surface mb-1">Nomor HP</label>
                <input type="tel" name="phone" id="v_phone" required class="w-full bg-surface/50 border border-outline-variant rounded-xl px-4 py-3 text-on-surface focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary transition-colors dark:bg-slate-900 dark:text-slate-100" placeholder="08xxxxxxxx">
                <p class="text-xs text-on-surface-variant mt-2">Kode voucher akan diberikan setelah pembayaran berhasil.</p>
            </div>
            
            <button type="submit" class="w-full btn-primary text-white font-label-bold py-4 rounded-xl mt-4 flex justify-center items-center gap-2">
                Lanjut Pembayaran <span class="material-symbols-outlined text-sm">arrow_forward</span>
            </button>
        </form>
    </div>
</div>

</body>
</html>
