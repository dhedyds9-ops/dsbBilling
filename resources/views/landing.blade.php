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
    vouchPrice: '',
    faqOpen: null
}">

    @php
        $host = request()->getHost();
        $subdomain = explode('.', $host)[0];
        $defaultTenantName = ($subdomain && !in_array($subdomain, ['localhost', '127', 'dsbilling', 'www'])) ? ucfirst($subdomain) : config('app.name', 'dsBilling');

        $companyName = \App\Models\Setting::getValue('company.name') ?: $defaultTenantName;
        $companyLegal = \App\Models\Setting::getValue('company.legal_name') ?: $companyName;
        $companyLogo = \App\Models\Setting::getValue('company.logo_url', null);
        $companyPhone = \App\Models\Setting::getValue('company.phone') ?: \App\Models\Setting::getValue('company.whatsapp') ?: \App\Models\Setting::getValue('company.mobile');
        $companyEmail = \App\Models\Setting::getValue('company.email') ?: \App\Models\Setting::getValue('company.support_email') ?: ('admin@' . (str_contains($host, '.') ? $host : 'dsbilling.id'));
        $companyAddress = \App\Models\Setting::getValue('company.address');
        $companyCity = \App\Models\Setting::getValue('company.city');
        $companyProvince = \App\Models\Setting::getValue('company.province');
        $companyPostal = \App\Models\Setting::getValue('company.postal_code');
        $companyHours = \App\Models\Setting::getValue('company.operational_hours') ?: 'Senin - Minggu: 24 Jam (Teknis) / 08:00 - 21:00 WIB (Administrasi)';

        $showPppoe = \Illuminate\Support\Collection::wrap($packagesPppoe ?? []);
        $showMemberHotspot = \Illuminate\Support\Collection::wrap($packagesHotspot ?? []);
        $showHotspotVoucher = \Illuminate\Support\Collection::wrap($packagesVoucher ?? []);

        $formatSpeed = function($pkg) {
            $dl = $pkg->download_speed;
            $up = $pkg->upload_speed;
            if ($dl && $dl > 0) {
                $dlText = ($dl >= 1000) ? (round($dl / 1000, 1) . ' Mbps') : ($dl . ' Kbps');
                if ($up && $up > 0 && $up != $dl) {
                    $upText = ($up >= 1000) ? (round($up / 1000, 1) . ' Mbps') : ($up . ' Kbps');
                    return $dlText . ' (Up ' . $upText . ')';
                }
                return $dlText;
            }
            preg_match('/(\d+)\s*(mbps|kbps|m|k)/i', ($pkg->name . ' ' . ($pkg->description ?? '')), $matches);
            if (!empty($matches[0])) {
                return strtoupper($matches[0]);
            }
            return 'Up to 20 Mbps';
        };

        $formatType = function($pkg) {
            $type = strtolower($pkg->package_type ?? 'unlimited');
            if ($type === 'time_based') return 'Berbasis Waktu';
            if ($type === 'quota_based') return 'Berbasis Kuota';
            return 'Unlimited (Tanpa FUP)';
        };

        $formatServiceType = function($pkg) {
            $st = strtolower($pkg->service_type ?? '');
            if (str_contains($st, 'voucher')) return 'Voucher Hotspot';
            if (str_contains($st, 'hotspot')) return 'Hotspot Member';
            if (str_contains($st, 'pppoe') || str_contains($st, 'ftth')) return 'PPPoE Fiber Home';
            return strtoupper($pkg->service_type ?? 'Internet');
        };
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
        <nav class="hidden md:flex items-center gap-5 text-sm font-semibold">
            <a class="text-on-surface-variant hover:text-primary transition-colors py-1 px-2 rounded" href="#keunggulan">Keunggulan</a>
            <a class="text-on-surface-variant hover:text-primary transition-colors py-1 px-2 rounded" href="#paket">Paket Internet</a>
            <a class="text-on-surface-variant hover:text-primary transition-colors py-1 px-2 rounded" href="#hotspot">Hotspot</a>
            <a class="text-on-surface-variant hover:text-primary transition-colors py-1 px-2 rounded" href="#pembayaran">Pembayaran</a>
            <a class="text-on-surface-variant hover:text-primary transition-colors py-1 px-2 rounded" href="#faq">FAQ</a>
            <a class="text-on-surface-variant hover:text-primary transition-colors py-1 px-2 rounded" href="{{ route('legal.contact') }}">Kontak</a>
        </nav>
        
        <!-- Actions -->
        <div class="hidden md:flex items-center gap-3">
            <a href="{{ route('admin.login') }}" class="flex items-center gap-2 px-4 py-2 rounded-full text-xs font-bold bg-white/5 hover:bg-white/10 border border-white/10 text-slate-300 transition-all shadow-sm">
                <span class="material-symbols-outlined text-[16px]">admin_panel_settings</span>
                Admin
            </a>
            <a href="{{ route('customer.login') }}" class="flex items-center gap-2 px-5 py-2 rounded-full text-xs font-bold bg-gradient-to-r from-cyan-500 to-blue-500 hover:from-cyan-400 hover:to-blue-400 text-white transition-all shadow-lg shadow-cyan-500/25">
                <span class="material-symbols-outlined text-[16px]">account_circle</span>
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
        <div class="flex flex-col gap-3 text-sm font-semibold">
            <a href="#keunggulan" @click="mobileMenuOpen = false" class="text-on-surface py-1">Keunggulan</a>
            <a href="#paket" @click="mobileMenuOpen = false" class="text-on-surface py-1">Paket Internet</a>
            <a href="#hotspot" @click="mobileMenuOpen = false" class="text-on-surface py-1">Hotspot</a>
            <a href="#pembayaran" @click="mobileMenuOpen = false" class="text-on-surface py-1">Metode Pembayaran</a>
            <a href="#faq" @click="mobileMenuOpen = false" class="text-on-surface py-1">FAQ & Bantuan</a>
            <a href="{{ route('legal.contact') }}" class="text-on-surface py-1">Hubungi Kami</a>
            <a href="{{ route('legal.about') }}" class="text-on-surface py-1">Tentang Kami</a>
            <hr class="border-white/10 my-2">
            <a href="{{ route('admin.login') }}" class="flex items-center justify-center gap-2 px-6 py-2.5 rounded-xl text-xs font-bold bg-white/5 hover:bg-white/10 border border-white/10 text-slate-300 transition-all">
                <span class="material-symbols-outlined text-[18px]">admin_panel_settings</span>
                Login Admin
            </a>
            <a href="{{ route('customer.login') }}" class="flex items-center justify-center gap-2 px-6 py-2.5 rounded-xl text-xs font-bold bg-gradient-to-r from-cyan-500 to-blue-500 hover:from-cyan-400 hover:to-blue-400 text-white transition-all shadow-lg shadow-cyan-500/25">
                <span class="material-symbols-outlined text-[18px]">account_circle</span>
                Portal Pelanggan
            </a>
        </div>
    </div>
</header>

<main class="pt-24 md:pt-32 pb-24">
    @if(session('success'))
    <div class="max-w-container-max mx-auto px-margin-mobile md:px-margin-desktop mb-8" x-data="{ show: true }" x-show="show">
        <div class="p-4 md:p-5 rounded-2xl bg-emerald-950/80 border border-emerald-500/40 text-emerald-200 flex items-center justify-between gap-4 shadow-xl backdrop-blur-md">
            <div class="flex items-center gap-3">
                <span class="material-symbols-outlined text-emerald-400 text-2xl">check_circle</span>
                <span class="text-sm font-semibold">{{ session('success') }}</span>
            </div>
            <button @click="show = false" class="text-emerald-400 hover:text-white transition-colors p-1 rounded-lg">
                <span class="material-symbols-outlined text-lg">close</span>
            </button>
        </div>
    </div>
    @endif
    @if(session('error'))
    <div class="max-w-container-max mx-auto px-margin-mobile md:px-margin-desktop mb-8" x-data="{ show: true }" x-show="show">
        <div class="p-4 md:p-5 rounded-2xl bg-rose-950/80 border border-rose-500/40 text-rose-200 flex items-center justify-between gap-4 shadow-xl backdrop-blur-md">
            <div class="flex items-center gap-3">
                <span class="material-symbols-outlined text-rose-400 text-2xl">error</span>
                <span class="text-sm font-semibold">{{ session('error') }}</span>
            </div>
            <button @click="show = false" class="text-rose-400 hover:text-white transition-colors p-1 rounded-lg">
                <span class="material-symbols-outlined text-lg">close</span>
            </button>
        </div>
    </div>
    @endif

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
            
            <form action="{{ route('guest.payment') }}" method="GET" class="mt-12 max-w-xl mx-auto">
                <div class="relative flex items-center group">
                    <span class="material-symbols-outlined absolute left-5 text-on-surface-variant group-focus-within:text-primary transition-colors">receipt_long</span>
                    <input type="text" name="q" placeholder="Cek tagihan (Ketik ID / Email / No. HP)..." required class="w-full pl-14 pr-36 py-4 bg-surface-container/50 border border-outline-variant/30 text-on-surface rounded-full focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary backdrop-blur-md transition-all shadow-xl placeholder:text-on-surface-variant/50">
                    <button type="submit" class="absolute right-2 top-2 bottom-2 px-6 bg-primary text-on-primary rounded-full font-bold hover:bg-primary-fixed-dim transition-colors shadow-md">Cari</button>
                </div>
            </form>
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
            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-primary/10 border border-primary/20 text-primary mb-3">
                <span class="material-symbols-outlined text-[14px]">router</span> KONEKSI FIBER OPTIC RUMAH
            </span>
            <h2 class="font-headline-lg text-headline-lg text-primary mb-2" style="font-family: Caveat, cursive; font-size: 3.5rem; line-height: 1;">Paket Internet Rumah</h2>
            <p class="text-on-surface-variant font-body-md text-body-md max-w-2xl mx-auto">Koneksi internet dedicated unlimited tanpa batas kuota FUP, stabil 24 jam untuk seluruh anggota keluarga.</p>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 items-stretch">
            @foreach($showPppoe as $index => $pkg)
                @php
                    $isPopular = ($index == 1 || count($showPppoe) == 1);
                    $speed = $formatSpeed($pkg);
                    $type = $formatType($pkg);
                    $service = $formatServiceType($pkg);
                @endphp
                <div class="glass-card p-8 rounded-[2rem] flex flex-col transition-all duration-300 relative {{ $isPopular ? 'border-[1.5px] border-primary/60 shadow-[0_0_35px_rgba(6,182,212,0.2)] bg-surface-container-high/90 lg:-translate-y-2' : 'border border-white/10 hover:border-primary/40 glow-hover' }} h-full">
                    @if($isPopular)
                        <div class="absolute -top-3.5 left-1/2 -translate-x-1/2 bg-gradient-to-r from-primary to-secondary text-on-primary px-4 py-0.5 rounded-full font-label-bold text-[11px] uppercase tracking-wider whitespace-nowrap shadow-[0_0_15px_rgba(6,182,212,0.5)]">
                            Paling Populer
                        </div>
                    @endif

                    <div class="flex items-center justify-between gap-2 mb-4">
                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[11px] font-bold bg-primary/10 text-primary border border-primary/20">
                            <span class="material-symbols-outlined text-[13px]">home</span> {{ $service }}
                        </span>
                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[11px] font-bold bg-surface-container text-cyan-300 border border-cyan-500/20">
                            <span class="material-symbols-outlined text-[13px]">bolt</span> {{ $speed }}
                        </span>
                    </div>

                    <h3 class="font-display-lg text-2xl font-black text-on-surface mb-1 tracking-tight">{{ $pkg->name }}</h3>
                    <p class="text-xs text-on-surface-variant line-clamp-2 min-h-[36px] mb-4">{{ $pkg->description ?: 'Solusi internet rumah berkecepatan tinggi dengan serat optik murni bebas gangguan cuaca.' }}</p>

                    <div class="mb-5 pb-4 border-b border-white/10 flex items-baseline gap-1.5">
                        <span class="text-3xl font-black text-transparent bg-clip-text bg-gradient-to-r from-primary to-secondary tracking-tight">Rp {{ number_format($pkg->price ?? $pkg->base_price ?? 0, 0, ',', '.') }}</span>
                        <span class="text-xs font-semibold text-on-surface-variant">/bulan</span>
                    </div>

                    <!-- Spesifikasi Paket Lengkap -->
                    <div class="grid grid-cols-2 gap-2 mb-6 text-xs">
                        <div class="p-2.5 rounded-xl bg-surface-container-lowest/60 border border-white/5">
                            <span class="text-[9px] font-bold uppercase text-slate-400 block mb-0.5">Bandwidth</span>
                            <strong class="text-on-surface text-[11px] flex items-center gap-1"><span class="material-symbols-outlined text-primary text-[14px]">speed</span> {{ $speed }}</strong>
                        </div>
                        <div class="p-2.5 rounded-xl bg-surface-container-lowest/60 border border-white/5">
                            <span class="text-[9px] font-bold uppercase text-slate-400 block mb-0.5">Tipe Kuota</span>
                            <strong class="text-emerald-400 text-[11px] flex items-center gap-1"><span class="material-symbols-outlined text-emerald-400 text-[14px]">all_inclusive</span> {{ $type }}</strong>
                        </div>
                        <div class="p-2.5 rounded-xl bg-surface-container-lowest/60 border border-white/5">
                            <span class="text-[9px] font-bold uppercase text-slate-400 block mb-0.5">Shared Perangkat</span>
                            <strong class="text-on-surface text-[11px] flex items-center gap-1"><span class="material-symbols-outlined text-purple-400 text-[14px]">devices</span> {{ $pkg->max_devices ? $pkg->max_devices . ' Device' : 'Multi-Device' }}</strong>
                        </div>
                        <div class="p-2.5 rounded-xl bg-surface-container-lowest/60 border border-white/5">
                            <span class="text-[9px] font-bold uppercase text-slate-400 block mb-0.5">Jenis Layanan</span>
                            <strong class="text-on-surface text-[11px] flex items-center gap-1"><span class="material-symbols-outlined text-amber-400 text-[14px]">lan</span> PPPoE FTTH</strong>
                        </div>
                    </div>

                    <!-- Fitur Unggulan -->
                    <ul class="space-y-2.5 mb-6 text-xs text-on-surface-variant flex-grow">
                        <li class="flex items-center gap-2 text-on-surface">
                            <span class="material-symbols-outlined text-primary text-[16px]" style="font-variation-settings: 'FILL' 1;">check_circle</span>
                            <span>Unlimited tanpa batasan FUP / kuota</span>
                        </li>
                        <li class="flex items-center gap-2 text-on-surface">
                            <span class="material-symbols-outlined text-primary text-[16px]" style="font-variation-settings: 'FILL' 1;">check_circle</span>
                            <span>Koneksi simetris & latensi sangat rendah</span>
                        </li>
                        <li class="flex items-center gap-2 text-on-surface">
                            <span class="material-symbols-outlined text-primary text-[16px]" style="font-variation-settings: 'FILL' 1;">check_circle</span>
                            <span>Optimal streaming 4K & game online tanpa lag</span>
                        </li>
                        <li class="flex items-center gap-2 text-on-surface">
                            <span class="material-symbols-outlined text-primary text-[16px]" style="font-variation-settings: 'FILL' 1;">check_circle</span>
                            <span>Dukungan teknisi standby & SLA prioritas</span>
                        </li>
                    </ul>

                    <button @click.prevent="pkgName = '{{ addslashes($pkg->name) }}'; regOpen = true" class="w-full {{ $isPopular ? 'btn-primary text-white' : 'btn-secondary text-primary' }} text-center font-label-bold py-3.5 rounded-xl mt-auto transition-all flex items-center justify-center gap-2">
                        <span>Berlangganan Sekarang</span>
                        <span class="material-symbols-outlined text-sm">arrow_forward</span>
                    </button>
                </div>
            @endforeach
        </div>
    </section>
    @endif

    <!-- Hotspot Section -->
    @if($showHotspotVoucher->isNotEmpty())
    <section class="px-margin-mobile md:px-margin-desktop max-w-container-max mx-auto mb-32 bg-surface-container/30 rounded-3xl p-6 sm:p-10 md:p-14 border border-white/5" id="hotspot">
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-10 gap-4">
            <div>
                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-amber-500/15 border border-amber-500/30 text-amber-300 mb-2">
                    <span class="material-symbols-outlined text-[14px]">confirmation_number</span> PRABAYAR CEPAT & MUDAH
                </span>
                <h2 class="font-headline-lg text-headline-lg text-primary mb-1" style="font-family: Caveat, cursive; font-size: 3.5rem; line-height: 1;">Voucher Hotspot</h2>
                <p class="text-on-surface-variant font-body-md text-body-md">Akses internet cepat di area publik dengan sistem voucher instan tanpa ribet registrasi.</p>
            </div>
            <div>
                <span class="material-symbols-outlined text-5xl text-amber-400/40" style="font-variation-settings: 'FILL' 0;">wifi_tethering</span>
            </div>
        </div>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
            @foreach($showHotspotVoucher as $hot)
                @php
                    $speed = $formatSpeed($hot);
                    $type = $formatType($hot);
                    $validity = $hot->duration_value ? ($hot->duration_value . ' ' . ($hot->duration_unit ?: 'Jam')) : ($hot->validity_hours ? $hot->validity_hours . ' Jam' : ($hot->validity_days ? $hot->validity_days . ' Hari' : '1 Sesi'));
                @endphp
                <div class="bg-surface-container-high/80 rounded-2xl overflow-hidden flex flex-col border border-white/10 hover:border-amber-400/40 transition-all duration-300 shadow-lg h-full">
                    <div class="p-5 flex-grow bg-gradient-to-br from-amber-500/10 via-primary/5 to-transparent">
                        <div class="flex justify-between items-center mb-3">
                            <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-amber-400/15 text-amber-300 border border-amber-400/30">
                                <span class="material-symbols-outlined text-[12px]">local_activity</span> Voucher
                            </span>
                            <span class="inline-flex items-center gap-1 text-[11px] font-extrabold text-cyan-300 bg-cyan-500/10 px-2 py-0.5 rounded-full border border-cyan-500/20">
                                <span class="material-symbols-outlined text-[12px]">bolt</span> {{ $speed }}
                            </span>
                        </div>

                        <h3 class="text-lg font-black text-on-surface mb-1 tracking-tight">{{ $hot->name }}</h3>
                        <p class="text-xs text-on-surface-variant line-clamp-2 min-h-[32px] mb-3">{{ $hot->description ?: 'Akses internet instan kecepatan tinggi langsung dari smartphone atau laptop Anda.' }}</p>

                        <!-- Specs Grid -->
                        <div class="grid grid-cols-2 gap-1.5 text-[11px] my-3">
                            <div class="p-2 rounded-lg bg-surface-container-lowest/80 border border-white/5">
                                <span class="text-[9px] uppercase font-bold text-slate-400 block">Masa Aktif</span>
                                <strong class="text-on-surface text-[11px]">{{ $validity }}</strong>
                            </div>
                            <div class="p-2 rounded-lg bg-surface-container-lowest/80 border border-white/5">
                                <span class="text-[9px] uppercase font-bold text-slate-400 block">Kuota</span>
                                <strong class="text-emerald-400 text-[11px]">{{ $hot->quota_value ? $hot->quota_value . ' ' . ($hot->quota_unit ?: 'MB') : 'Unlimited' }}</strong>
                            </div>
                            <div class="p-2 rounded-lg bg-surface-container-lowest/80 border border-white/5">
                                <span class="text-[9px] uppercase font-bold text-slate-400 block">Shared</span>
                                <strong class="text-on-surface text-[11px]">{{ $hot->max_devices ? $hot->max_devices . ' Perangkat' : '1 Device' }}</strong>
                            </div>
                            <div class="p-2 rounded-lg bg-surface-container-lowest/80 border border-white/5">
                                <span class="text-[9px] uppercase font-bold text-slate-400 block">Tipe</span>
                                <strong class="text-on-surface text-[11px]">{{ $type }}</strong>
                            </div>
                        </div>
                    </div>

                    <div class="border-t border-dashed border-white/20 mx-3"></div>

                    <div class="p-5 bg-surface-container-highest/60 flex flex-col gap-3 mt-auto">
                        <div class="flex justify-between items-center">
                            <div>
                                <span class="text-[10px] text-on-surface-variant uppercase font-bold block leading-none">Harga Jual</span>
                                <span class="text-2xl font-black text-primary tracking-tight">Rp {{ number_format($hot->price ?? $hot->base_price ?? 0, 0, ',', '.') }}</span>
                            </div>
                            <span class="material-symbols-outlined text-2xl text-on-surface-variant/60">confirmation_number</span>
                        </div>
                        <button @click.prevent="vouchId = '{{ $hot->id }}'; vouchName = '{{ addslashes($hot->name) }}'; vouchPrice = 'Rp {{ number_format($hot->price ?? $hot->base_price ?? 0, 0, ',', '.') }}'; vouchOpen = true" class="w-full btn-primary text-white py-2.5 rounded-xl text-xs font-label-bold transition-all flex items-center justify-center gap-1.5 shadow-md">
                            <span class="material-symbols-outlined text-sm">shopping_cart</span>
                            <span>Beli Voucher</span>
                        </button>
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
            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-secondary-container/30 border border-secondary/30 text-secondary mb-3">
                <span class="material-symbols-outlined text-[14px]">wifi</span> LANGGANAN HOTSPOT BULANAN
            </span>
            <h2 class="font-headline-lg text-headline-lg text-primary mb-2" style="font-family: Caveat, cursive; font-size: 3.5rem; line-height: 1;">Member Hotspot</h2>
            <p class="text-on-surface-variant font-body-md text-body-md max-w-2xl mx-auto">Langganan bulanan untuk akses tanpa batas di seluruh jaringan hotspot kami tanpa perlu gonta-ganti kode voucher.</p>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 items-stretch">
            @foreach($showMemberHotspot as $index => $mem)
                @php
                    $isPopular = ($index == 1 || count($showMemberHotspot) == 1);
                    $speed = $formatSpeed($mem);
                    $type = $formatType($mem);
                    $service = $formatServiceType($mem);
                @endphp
                <div class="glass-card p-8 rounded-[2rem] flex flex-col transition-all duration-300 relative {{ $isPopular ? 'border-[1.5px] border-secondary/60 shadow-[0_0_35px_rgba(173,198,255,0.15)] bg-surface-container-high/90 lg:-translate-y-2' : 'border border-white/10 hover:border-secondary/40 glow-hover' }} h-full">
                    @if($isPopular)
                        <div class="absolute -top-3.5 left-1/2 -translate-x-1/2 bg-gradient-to-r from-secondary-container to-secondary text-on-secondary px-4 py-0.5 rounded-full font-label-bold text-[11px] uppercase tracking-wider whitespace-nowrap shadow-lg">
                            Pilihan Terbaik
                        </div>
                    @endif

                    <div class="flex items-center justify-between gap-2 mb-4">
                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[11px] font-bold bg-secondary/15 text-secondary border border-secondary/25">
                            <span class="material-symbols-outlined text-[13px]">wifi_protected_setup</span> {{ $service }}
                        </span>
                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[11px] font-bold bg-surface-container text-secondary border border-secondary/20">
                            <span class="material-symbols-outlined text-[13px]">bolt</span> {{ $speed }}
                        </span>
                    </div>

                    <h3 class="font-display-lg text-2xl font-black text-on-surface mb-1 tracking-tight">{{ $mem->name }}</h3>
                    <p class="text-xs text-on-surface-variant line-clamp-2 min-h-[36px] mb-4">{{ $mem->description ?: 'Solusi internet hotspot berlangganan bulanan tanpa batasan kuota untuk mobilitas Anda.' }}</p>

                    <div class="mb-5 pb-4 border-b border-white/10 flex items-baseline gap-1.5">
                        <span class="text-3xl font-black text-transparent bg-clip-text bg-gradient-to-r from-secondary to-primary tracking-tight">Rp {{ number_format($mem->price ?? $mem->base_price ?? 0, 0, ',', '.') }}</span>
                        <span class="text-xs font-semibold text-on-surface-variant">/bulan</span>
                    </div>

                    <!-- Spesifikasi Paket -->
                    <div class="grid grid-cols-2 gap-2 mb-6 text-xs">
                        <div class="p-2.5 rounded-xl bg-surface-container-lowest/60 border border-white/5">
                            <span class="text-[9px] font-bold uppercase text-slate-400 block mb-0.5">Bandwidth</span>
                            <strong class="text-on-surface text-[11px] flex items-center gap-1"><span class="material-symbols-outlined text-secondary text-[14px]">speed</span> {{ $speed }}</strong>
                        </div>
                        <div class="p-2.5 rounded-xl bg-surface-container-lowest/60 border border-white/5">
                            <span class="text-[9px] font-bold uppercase text-slate-400 block mb-0.5">Tipe Kuota</span>
                            <strong class="text-emerald-400 text-[11px] flex items-center gap-1"><span class="material-symbols-outlined text-emerald-400 text-[14px]">all_inclusive</span> {{ $type }}</strong>
                        </div>
                        <div class="p-2.5 rounded-xl bg-surface-container-lowest/60 border border-white/5">
                            <span class="text-[9px] font-bold uppercase text-slate-400 block mb-0.5">Shared Perangkat</span>
                            <strong class="text-on-surface text-[11px] flex items-center gap-1"><span class="material-symbols-outlined text-purple-400 text-[14px]">devices</span> {{ $mem->max_devices ? $mem->max_devices . ' Device' : '1 Device' }}</strong>
                        </div>
                        <div class="p-2.5 rounded-xl bg-surface-container-lowest/60 border border-white/5">
                            <span class="text-[9px] font-bold uppercase text-slate-400 block mb-0.5">Masa Aktif</span>
                            <strong class="text-on-surface text-[11px] flex items-center gap-1"><span class="material-symbols-outlined text-amber-400 text-[14px]">calendar_today</span> 30 Hari</strong>
                        </div>
                    </div>

                    <!-- Fitur Unggulan -->
                    <ul class="space-y-2.5 mb-6 text-xs text-on-surface-variant flex-grow">
                        <li class="flex items-center gap-2 text-on-surface">
                            <span class="material-symbols-outlined text-secondary text-[16px]" style="font-variation-settings: 'FILL' 1;">check_circle</span>
                            <span>Login otomatis sekali klik tanpa ribet</span>
                        </li>
                        <li class="flex items-center gap-2 text-on-surface">
                            <span class="material-symbols-outlined text-secondary text-[16px]" style="font-variation-settings: 'FILL' 1;">check_circle</span>
                            <span>Akses 30 hari penuh unlimited tanpa FUP</span>
                        </li>
                        <li class="flex items-center gap-2 text-on-surface">
                            <span class="material-symbols-outlined text-secondary text-[16px]" style="font-variation-settings: 'FILL' 1;">check_circle</span>
                            <span>Prioritas bandwidth stabil di seluruh area</span>
                        </li>
                        <li class="flex items-center gap-2 text-on-surface">
                            <span class="material-symbols-outlined text-secondary text-[16px]" style="font-variation-settings: 'FILL' 1;">check_circle</span>
                            <span>Bisa digunakan hingga {{ $mem->max_devices ?? 1 }} perangkat bersamaan</span>
                        </li>
                    </ul>

                    <button @click.prevent="pkgName = '{{ addslashes($mem->name) }}'; regOpen = true" class="w-full {{ $isPopular ? 'btn-primary text-white' : 'btn-secondary text-primary' }} text-center font-label-bold py-3.5 rounded-xl mt-auto transition-all flex items-center justify-center gap-2 shadow-lg">
                        <span>Daftar Member</span>
                        <span class="material-symbols-outlined text-sm">arrow_forward</span>
                    </button>
                </div>
            @endforeach
        </div>
    </section>
    @endif

    <!-- Billing CTA -->
    <section class="px-margin-mobile md:px-margin-desktop max-w-container-max mx-auto mb-28">
        <div class="relative overflow-hidden rounded-[2rem] bg-gradient-to-r from-surface-container-high to-surface p-8 md:p-12 border border-primary/20 shadow-[0_0_40px_rgba(6,182,212,0.1)] flex flex-col md:flex-row items-center justify-between gap-8">
            <div class="absolute -right-20 -top-20 w-64 h-64 bg-primary/10 rounded-full blur-[60px]"></div>
            <div class="z-10 text-center md:text-left">
                <h2 class="font-headline-lg text-headline-lg text-on-surface mb-2">Sudah Waktunya Bayar Tagihan?</h2>
                <p class="text-on-surface-variant font-body-md text-body-md max-w-xl">Cek status tagihan Anda dengan mudah dan lakukan pembayaran secara online melalui portal pelanggan kami yang aman dan otomatis.</p>
            </div>
            <div class="z-10 flex-shrink-0">
                <a href="{{ route('guest.payment') }}" class="bg-[#1bbd85] hover:bg-[#4edea3] text-on-tertiary font-label-bold text-label-bold px-8 py-4 rounded-xl flex items-center gap-3 transition-colors shadow-[0_0_20px_rgba(27,189,133,0.3)] animate-pulse hover:animate-none">
                    <span class="material-symbols-outlined" style="font-variation-settings: 'FILL' 1;">receipt_long</span>
                    Cek &amp; Bayar Tagihan
                </a>
            </div>
        </div>
    </section>

    <!-- Supported Payment Methods Section (Payment Gateway Merchant Compliance) -->
    <section class="px-margin-mobile md:px-margin-desktop max-w-container-max mx-auto mb-28" id="pembayaran">
        <div class="glass-card rounded-[2rem] p-8 md:p-14 border border-white/10 shadow-2xl relative overflow-hidden">
            <div class="text-center max-w-3xl mx-auto mb-12">
                <div class="inline-flex items-center gap-2 px-4 py-1.5 rounded-full text-xs font-bold bg-cyan-500/10 text-cyan-400 border border-cyan-500/30 mb-4">
                    <span class="material-symbols-outlined text-[16px]">verified</span>
                    Metode Pembayaran Resmi & Terpercaya
                </div>
                <h2 class="text-2xl md:text-4xl font-extrabold text-white mb-4">Bayar Tagihan & Beli Voucher Makin Mudah</h2>
                <p class="text-slate-400 text-sm md:text-base leading-relaxed">
                    Kami mendukung berbagai kanal pembayaran digital otomatis 24 jam dengan verifikasi instan. Transaksi Anda dijamin aman, cepat, dan tanpa ribet konfirmasi manual.
                </p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-10">
                <!-- QRIS -->
                <div class="p-6 rounded-2xl bg-slate-900/60 border border-white/5 flex flex-col items-center text-center">
                    <div class="w-12 h-12 rounded-xl bg-red-500/10 text-red-400 flex items-center justify-center mb-3">
                        <span class="material-symbols-outlined text-2xl">qr_code_scanner</span>
                    </div>
                    <h3 class="font-bold text-white text-base mb-1">QRIS Standar</h3>
                    <p class="text-xs text-slate-400 mb-3">Scan cepat dari semua aplikasi e-wallet & mobile banking di Indonesia</p>
                    <span class="text-[11px] font-semibold text-slate-300 bg-white/5 px-2.5 py-1 rounded-md">BCA, Mandiri, GoPay, OVO, DANA, ShopeePay, LinkAja</span>
                </div>

                <!-- Virtual Account -->
                <div class="p-6 rounded-2xl bg-slate-900/60 border border-white/5 flex flex-col items-center text-center">
                    <div class="w-12 h-12 rounded-xl bg-blue-500/10 text-blue-400 flex items-center justify-center mb-3">
                        <span class="material-symbols-outlined text-2xl">account_balance</span>
                    </div>
                    <h3 class="font-bold text-white text-base mb-1">Virtual Account Bank</h3>
                    <p class="text-xs text-slate-400 mb-3">Nomor VA unik untuk setiap invoice, verifikasi otomatis dalam hitungan detik</p>
                    <span class="text-[11px] font-semibold text-slate-300 bg-white/5 px-2.5 py-1 rounded-md">BCA, Mandiri, BNI, BRI, Permata, CIMB Niaga</span>
                </div>

                <!-- E-Wallet -->
                <div class="p-6 rounded-2xl bg-slate-900/60 border border-white/5 flex flex-col items-center text-center">
                    <div class="w-12 h-12 rounded-xl bg-cyan-500/10 text-cyan-400 flex items-center justify-center mb-3">
                        <span class="material-symbols-outlined text-2xl">account_balance_wallet</span>
                    </div>
                    <h3 class="font-bold text-white text-base mb-1">Dompet Digital (E-Wallet)</h3>
                    <p class="text-xs text-slate-400 mb-3">Pembayaran praktis langsung dialihkan ke aplikasi favorit di ponsel Anda</p>
                    <span class="text-[11px] font-semibold text-slate-300 bg-white/5 px-2.5 py-1 rounded-md">GoPay, OVO, DANA, ShopeePay</span>
                </div>

                <!-- Gerai Retail -->
                <div class="p-6 rounded-2xl bg-slate-900/60 border border-white/5 flex flex-col items-center text-center">
                    <div class="w-12 h-12 rounded-xl bg-emerald-500/10 text-emerald-400 flex items-center justify-center mb-3">
                        <span class="material-symbols-outlined text-2xl">storefront</span>
                    </div>
                    <h3 class="font-bold text-white text-base mb-1">Gerai Retail</h3>
                    <p class="text-xs text-slate-400 mb-3">Bagi Anda yang lebih nyaman bayar tunai di gerai minimarket terdekat</p>
                    <span class="text-[11px] font-semibold text-slate-300 bg-white/5 px-2.5 py-1 rounded-md">Indomaret & Alfamart</span>
                </div>
            </div>

            <!-- Security & Compliance Badge -->
            <div class="p-4 md:p-6 rounded-2xl bg-gradient-to-r from-cyan-950/40 via-slate-900/60 to-blue-950/40 border border-cyan-500/20 flex flex-col sm:flex-row items-center justify-between gap-4">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-full bg-emerald-500/20 text-emerald-400 flex items-center justify-center shrink-0">
                        <span class="material-symbols-outlined text-xl">lock</span>
                    </div>
                    <div>
                        <div class="text-white text-xs font-bold">Keamanan Transaksi Terjamin (PCI-DSS & SSL 256-Bit)</div>
                        <div class="text-slate-400 text-[11px]">Seluruh transaksi diproses melalui Payment Gateway resmi berizin dan diawasi oleh Bank Indonesia.</div>
                    </div>
                </div>
                <div class="flex items-center gap-4 text-xs font-bold text-slate-300 shrink-0">
                    <span class="flex items-center gap-1 text-emerald-400"><span class="material-symbols-outlined text-[16px]">check_circle</span> Instant Verifikasi</span>
                    <span class="flex items-center gap-1 text-cyan-400"><span class="material-symbols-outlined text-[16px]">bolt</span> Buka Isolir Otomatis</span>
                </div>
            </div>
        </div>
    </section>

    <!-- FAQ Section -->
    <section class="px-margin-mobile md:px-margin-desktop max-w-4xl mx-auto mb-28" id="faq">
        <div class="text-center mb-12">
            <h2 class="font-headline-lg text-headline-lg text-primary mb-2" style="font-family: Caveat, cursive; font-size: 3.5rem; line-height: 1;">Pertanyaan Umum (FAQ)</h2>
            <p class="text-on-surface-variant font-body-md text-body-md">Jawaban seputar pemasangan, cara bayar, dan layanan {{ $companyName }}.</p>
        </div>

        <div class="space-y-4">
            <!-- FAQ 1 -->
            <div class="glass-card rounded-2xl border border-white/10 overflow-hidden transition-all">
                <button @click="faqOpen = (faqOpen === 1 ? null : 1)" class="w-full p-6 text-left flex justify-between items-center gap-4 text-white font-bold text-base hover:text-cyan-400 transition-colors">
                    <span>1. Bagaimana cara mendaftar pasang baru internet rumah?</span>
                    <span class="material-symbols-outlined text-cyan-400 transition-transform" :class="faqOpen === 1 ? 'rotate-180' : ''">expand_more</span>
                </button>
                <div x-show="faqOpen === 1" x-collapse x-cloak class="px-6 pb-6 text-sm text-slate-300 border-t border-white/5 pt-4 leading-relaxed">
                    Cukup pilih paket yang Anda inginkan di halaman utama, klik <strong>"Pilih Paket"</strong> atau <strong>"Berlangganan Sekarang"</strong>, lalu isi form pendaftaran (Nama, Nomor WhatsApp, dan Alamat Pemasangan). Tim teknisi kami akan segera memverifikasi ketersediaan jaringan di lokasi Anda dan menjadwalkan instalasi.
                </div>
            </div>

            <!-- FAQ 2 -->
            <div class="glass-card rounded-2xl border border-white/10 overflow-hidden transition-all">
                <button @click="faqOpen = (faqOpen === 2 ? null : 2)" class="w-full p-6 text-left flex justify-between items-center gap-4 text-white font-bold text-base hover:text-cyan-400 transition-colors">
                    <span>2. Bagaimana cara membayar tagihan bulanan?</span>
                    <span class="material-symbols-outlined text-cyan-400 transition-transform" :class="faqOpen === 2 ? 'rotate-180' : ''">expand_more</span>
                </button>
                <div x-show="faqOpen === 2" x-collapse x-cloak class="px-6 pb-6 text-sm text-slate-300 border-t border-white/5 pt-4 leading-relaxed">
                    Anda dapat membayar tagihan dengan mudah tanpa login melalui menu <strong>Cek & Bayar Tagihan</strong> di web ini dengan memasukkan ID Pelanggan, Nomor HP, atau Email Anda. Kami menyediakan kanal QRIS otomatis, Virtual Account semua bank utama, e-wallet, hingga minimarket. Verifikasi terjadi secara realtime tanpa perlu kirim bukti transfer.
                </div>
            </div>

            <!-- FAQ 3 -->
            <div class="glass-card rounded-2xl border border-white/10 overflow-hidden transition-all">
                <button @click="faqOpen = (faqOpen === 3 ? null : 3)" class="w-full p-6 text-left flex justify-between items-center gap-4 text-white font-bold text-base hover:text-cyan-400 transition-colors">
                    <span>3. Apakah layanan internet memiliki batasan kuota (FUP)?</span>
                    <span class="material-symbols-outlined text-cyan-400 transition-transform" :class="faqOpen === 3 ? 'rotate-180' : ''">expand_more</span>
                </button>
                <div x-show="faqOpen === 3" x-collapse x-cloak class="px-6 pb-6 text-sm text-slate-300 border-t border-white/5 pt-4 leading-relaxed">
                    Tidak ada batasan FUP (Fair Usage Policy) untuk paket internet rumah fiber optic kami. Kecepatan internet Anda stabil 24 jam penuh sepanjang bulan tanpa ada penurunan kecepatan di akhir bulan.
                </div>
            </div>

            <!-- FAQ 4 -->
            <div class="glass-card rounded-2xl border border-white/10 overflow-hidden transition-all">
                <button @click="faqOpen = (faqOpen === 4 ? null : 4)" class="w-full p-6 text-left flex justify-between items-center gap-4 text-white font-bold text-base hover:text-cyan-400 transition-colors">
                    <span>4. Apa yang harus dilakukan jika koneksi internet terputus atau lampu LOS merah?</span>
                    <span class="material-symbols-outlined text-cyan-400 transition-transform" :class="faqOpen === 4 ? 'rotate-180' : ''">expand_more</span>
                </button>
                <div x-show="faqOpen === 4" x-collapse x-cloak class="px-6 pb-6 text-sm text-slate-300 border-t border-white/5 pt-4 leading-relaxed">
                    Pertama, coba matikan modem selama 30 detik lalu nyalakan kembali (restart). Jika lampu indikator LOS pada modem masih berkedip merah (menandakan kabel optik terganggu), segera buat tiket gangguan melalui Portal Pelanggan atau hubungi layanan teknis kami via WhatsApp CS di nomor <strong>{{ $companyPhone }}</strong>.
                </div>
            </div>

            <!-- FAQ 5 -->
            <div class="glass-card rounded-2xl border border-white/10 overflow-hidden transition-all">
                <button @click="faqOpen = (faqOpen === 5 ? null : 5)" class="w-full p-6 text-left flex justify-between items-center gap-4 text-white font-bold text-base hover:text-cyan-400 transition-colors">
                    <span>5. Bagaimana kebijakan pengembalian dana (refund) jika terjadi kendala?</span>
                    <span class="material-symbols-outlined text-cyan-400 transition-transform" :class="faqOpen === 5 ? 'rotate-180' : ''">expand_more</span>
                </button>
                <div x-show="faqOpen === 5" x-collapse x-cloak class="px-6 pb-6 text-sm text-slate-300 border-t border-white/5 pt-4 leading-relaxed">
                    Kami memberlakukan jaminan pengembalian dana 100% jika terjadi kelebihan transfer / double payment atau jika pendaftaran baru gagal diaktifkan karena lokasi Anda belum terjangkau jaringan fiber optik kami. Informasi detail dapat dibaca di halaman <a href="{{ route('legal.refund') }}" class="text-cyan-400 hover:underline">Kebijakan Pengembalian Dana & Pembatalan</a>.
                </div>
            </div>
        </div>
    </section>

    <!-- Contact & Location Section -->
    <section class="px-margin-mobile md:px-margin-desktop max-w-container-max mx-auto mb-20" id="kontak">
        <div class="glass-card rounded-[2rem] p-8 md:p-12 border border-white/10 shadow-xl">
            <div class="text-center max-w-2xl mx-auto mb-10">
                <h2 class="text-2xl md:text-3xl font-extrabold text-white mb-2">Informasi Kontak & Kantor Operasional</h2>
                <p class="text-slate-400 text-xs md:text-sm">Silakan hubungi kami untuk informasi kemitraan, kendala teknis, atau kunjungi kantor layanan kami.</p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-4 gap-6 text-xs text-slate-300">
                <div class="p-5 rounded-xl bg-slate-900/60 border border-white/5 flex flex-col items-center text-center">
                    <span class="material-symbols-outlined text-cyan-400 text-3xl mb-2">location_on</span>
                    <h4 class="font-bold text-white text-sm mb-1">Alamat Kantor</h4>
                    <p class="text-slate-400 leading-relaxed">{{ $companyAddress }}<br>{{ $companyCity }}, {{ $companyProvince }} {{ $companyPostal }}</p>
                </div>

                <div class="p-5 rounded-xl bg-slate-900/60 border border-white/5 flex flex-col items-center text-center">
                    <span class="material-symbols-outlined text-emerald-400 text-3xl mb-2">chat</span>
                    <h4 class="font-bold text-white text-sm mb-1">WhatsApp / Telepon</h4>
                    <p class="text-slate-400 mb-2">Customer Care & Pemasangan</p>
                    <a href="https://wa.me/{{ preg_replace('/\D/', '', $companyPhone) }}" target="_blank" class="font-bold text-emerald-400 hover:underline text-sm">
                        {{ $companyPhone }}
                    </a>
                </div>

                <div class="p-5 rounded-xl bg-slate-900/60 border border-white/5 flex flex-col items-center text-center">
                    <span class="material-symbols-outlined text-blue-400 text-3xl mb-2">mail</span>
                    <h4 class="font-bold text-white text-sm mb-1">Email Resmi</h4>
                    <p class="text-slate-400 mb-2">Billing & Support</p>
                    <a href="mailto:{{ $companyEmail }}" class="font-bold text-blue-400 hover:underline text-sm">
                        {{ $companyEmail }}
                    </a>
                </div>

                <div class="p-5 rounded-xl bg-slate-900/60 border border-white/5 flex flex-col items-center text-center">
                    <span class="material-symbols-outlined text-amber-400 text-3xl mb-2">schedule</span>
                    <h4 class="font-bold text-white text-sm mb-1">Jam Operasional</h4>
                    <p class="text-slate-400 leading-relaxed">{{ $companyHours }}</p>
                </div>
            </div>
        </div>
    </section>
</main>

<!-- Footer (Full Compliant with Payment Gateway Standards) -->
<footer class="bg-surface-container-lowest dark:bg-surface-container-lowest border-t border-outline-variant w-full py-12 px-margin-mobile md:px-margin-desktop text-sm">
    <div class="max-w-container-max mx-auto grid grid-cols-1 md:grid-cols-4 gap-8 mb-8">
        <!-- Col 1: Identity & Description -->
        <div class="flex flex-col gap-3 md:col-span-1">
            <div class="font-headline-md text-headline-md font-extrabold text-primary">
                {{ $companyName }}
            </div>
            @if($companyLegal && $companyLegal !== $companyName)
                <div class="text-xs text-slate-400 font-semibold">{{ $companyLegal }}</div>
            @endif
            <p class="text-xs text-on-surface-variant leading-relaxed">
                Penyedia solusi akses internet berkecepatan tinggi dengan infrastruktur Full Fiber Optic untuk rumah, bisnis, dan ruang publik.
            </p>
            <div class="flex items-center gap-2 mt-2 text-xs text-emerald-400">
                <span class="material-symbols-outlined text-sm">security</span>
                <span>PCI-DSS Compliant &amp; SSL 256-Bit</span>
            </div>
        </div>

        <!-- Col 2: Services -->
        <div>
            <h4 class="text-white font-bold mb-3 text-xs uppercase tracking-wider">Layanan Kami</h4>
            <ul class="space-y-2 text-xs text-on-surface-variant">
                <li><a href="#paket" class="hover:text-primary transition-colors">Internet Rumah (PPPoE)</a></li>
                <li><a href="#hotspot" class="hover:text-primary transition-colors">Voucher Prabayar Hotspot</a></li>
                <li><a href="#member-hotspot" class="hover:text-primary transition-colors">Member Hotspot</a></li>
                <li><a href="{{ route('guest.payment') }}" class="hover:text-primary transition-colors">Cek &amp; Bayar Tagihan Online</a></li>
            </ul>
        </div>

        <!-- Col 3: Legal & Payment Gateway Compliance Links -->
        <div>
            <h4 class="text-white font-bold mb-3 text-xs uppercase tracking-wider">Legalitas &amp; Kebijakan</h4>
            <ul class="space-y-2 text-xs text-on-surface-variant">
                <li><a href="{{ route('legal.terms') }}" class="hover:text-secondary transition-colors">Syarat &amp; Ketentuan (TOS)</a></li>
                <li><a href="{{ route('legal.privacy') }}" class="hover:text-secondary transition-colors">Kebijakan Privasi</a></li>
                <li><a href="{{ route('legal.refund') }}" class="hover:text-secondary transition-colors">Kebijakan Pengembalian Dana (Refund)</a></li>
                <li><a href="{{ route('legal.about') }}" class="hover:text-secondary transition-colors">Tentang Kami</a></li>
                <li><a href="{{ route('legal.contact') }}" class="hover:text-secondary transition-colors">Kontak &amp; Dukungan Pelanggan</a></li>
                <li><a href="#faq" class="hover:text-secondary transition-colors">Pertanyaan Umum (FAQ)</a></li>
            </ul>
        </div>

        <!-- Col 4: Contact & Operations -->
        <div>
            <h4 class="text-white font-bold mb-3 text-xs uppercase tracking-wider">Kontak Kantor</h4>
            <div class="space-y-2 text-xs text-on-surface-variant leading-relaxed">
                <p><strong class="text-slate-300">Alamat:</strong><br>{{ $companyAddress }}, {{ $companyCity }}, {{ $companyProvince }} {{ $companyPostal }}</p>
                <p><strong class="text-slate-300">WhatsApp/Telp:</strong><br>{{ $companyPhone }}</p>
                <p><strong class="text-slate-300">Email:</strong><br>{{ $companyEmail }}</p>
                <p><strong class="text-slate-300">Jam Operasional:</strong><br>{{ $companyHours }}</p>
            </div>
        </div>
    </div>

    <!-- Bottom Copyright & Admin link -->
    <div class="max-w-container-max mx-auto pt-6 border-t border-white/10 flex flex-col md:flex-row items-center justify-between text-xs text-slate-500 gap-4">
        <p>&copy; {{ date('Y') }} {{ $companyName }}. Hak Cipta Dilindungi Undang-Undang.</p>
        <div class="flex items-center gap-4">
            <span>Didukung oleh Payment Gateway Berizin Bank Indonesia</span>
            <a href="{{ route('admin.login') }}" class="text-on-surface-variant hover:text-primary transition-colors flex items-center gap-1 opacity-40 hover:opacity-100" title="Admin Login">
                <span class="material-symbols-outlined text-sm">admin_panel_settings</span>
            </a>
        </div>
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
