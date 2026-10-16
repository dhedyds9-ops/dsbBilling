<!DOCTYPE html>
<html class="dark" lang="{{ str_replace('_', '-', app()->getLocale()) }}" style="scroll-behavior: smooth;">
<head>
    <meta charset="utf-8"/>
    <meta content="width=device-width, initial-scale=1.0" name="viewport"/>
    @php
        $companyName = \App\Models\Setting::getValue('company.name', config('app.name', 'dsBilling'));
        $companyLogo = \App\Models\Setting::getValue('company.logo_url', null);
        $companyPhone = \App\Models\Setting::getValue('company.phone', '0812-3456-7890');
        $companyEmail = \App\Models\Setting::getValue('company.email', 'support@dsbilling.id');
        $companyAddress = \App\Models\Setting::getValue('company.address', 'Jl. Merdeka No. 45');
        $companyCity = \App\Models\Setting::getValue('company.city', 'Jakarta');
        $companyProvince = \App\Models\Setting::getValue('company.province', 'DKI Jakarta');
        $companyPostal = \App\Models\Setting::getValue('company.postal_code', '10110');
        $companyHours = \App\Models\Setting::getValue('company.operational_hours', 'Senin - Minggu: 24 Jam (Teknis) / 08:00 - 21:00 WIB (Administrasi)');
    @endphp
    <title>{{ $title ?? 'Informasi Legal' }} - {{ $companyName }}</title>
    
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
    
    <link href="https://fonts.googleapis.com/css2?family=Hanken+Grotesk:wght@400;700;800;900&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet"/>
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet"/>
    
    <style>
        body { background-color: #020617; font-family: 'Inter', sans-serif; }
        .glass-card {
            background-color: rgba(15, 23, 42, 0.7);
            backdrop-filter: blur(24px);
            border: 1px solid rgba(255, 255, 255, 0.08);
        }
    </style>
</head>
<body class="text-slate-100 antialiased overflow-x-hidden min-h-screen flex flex-col justify-between">

    <!-- Header Navigation -->
    <header class="bg-slate-950/80 backdrop-blur-3xl border-b border-white/10 sticky top-0 w-full z-50">
        <div class="flex justify-between items-center px-6 md:px-12 py-4 max-w-7xl mx-auto">
            <a href="{{ route('home') }}" class="flex items-center gap-3 font-black text-2xl text-cyan-400 tracking-tight">
                @if($companyLogo)
                    <img src="{{ asset($companyLogo) }}" alt="{{ $companyName }}" class="h-10 w-auto object-contain">
                @else
                    <span class="material-symbols-outlined text-3xl text-cyan-400">wifi</span>
                    <span>{{ $companyName }}</span>
                @endif
            </a>
            
            <nav class="hidden md:flex items-center gap-6 text-sm font-semibold text-slate-300">
                <a href="{{ route('home') }}#paket" class="hover:text-cyan-400 transition-colors">Paket Internet</a>
                <a href="{{ route('home') }}#hotspot" class="hover:text-cyan-400 transition-colors">Voucher</a>
                <a href="{{ route('legal.about') }}" class="hover:text-cyan-400 transition-colors">Tentang Kami</a>
                <a href="{{ route('legal.contact') }}" class="hover:text-cyan-400 transition-colors">Kontak</a>
                <a href="{{ route('legal.terms') }}" class="hover:text-cyan-400 transition-colors">Syarat & Ketentuan</a>
            </nav>
            
            <div class="flex items-center gap-3">
                <a href="{{ route('customer.login') }}" class="px-5 py-2 rounded-full text-xs font-bold bg-gradient-to-r from-cyan-500 to-blue-600 hover:from-cyan-400 hover:to-blue-500 text-white shadow-lg shadow-cyan-500/20 transition-all">
                    Portal Pelanggan
                </a>
            </div>
        </div>
    </header>

    <!-- Main Content Area -->
    <main class="py-12 md:py-16 px-6 md:px-12 max-w-5xl mx-auto flex-grow w-full">
        <div class="mb-8">
            <a href="{{ route('home') }}" class="inline-flex items-center gap-2 text-xs font-semibold text-cyan-400 hover:text-cyan-300 transition-colors mb-4">
                <span class="material-symbols-outlined text-[16px]">arrow_back</span> Kembali ke Beranda
            </a>
            <h1 class="text-3xl md:text-4xl font-extrabold text-white tracking-tight mb-2">{{ $title ?? 'Informasi Legal' }}</h1>
            <p class="text-sm text-slate-400">Terakhir diperbarui: {{ date('d F Y') }} &bull; {{ $companyName }}</p>
        </div>

        <div class="glass-card rounded-2xl p-6 md:p-10 shadow-2xl border border-white/10 prose prose-invert max-w-none text-slate-300 leading-relaxed">
            {{ $slot }}
        </div>
    </main>

    <!-- Footer -->
    <footer class="bg-slate-950 border-t border-slate-800 py-12 px-6 md:px-12 mt-16 w-full">
        <div class="max-w-7xl mx-auto grid grid-cols-1 md:grid-cols-4 gap-8 mb-8 text-sm">
            <div class="md:col-span-1">
                <div class="text-xl font-black text-cyan-400 mb-3">{{ $companyName }}</div>
                <p class="text-slate-400 text-xs leading-relaxed mb-4">
                    Penyedia layanan internet berbasis Full Fiber Optic berkecepatan tinggi, stabil, dan tanpa batas kuota untuk rumah dan bisnis Anda.
                </p>
                <div class="flex items-center gap-2 text-xs text-emerald-400 bg-emerald-950/40 border border-emerald-800/40 px-3 py-1.5 rounded-lg w-fit">
                    <span class="material-symbols-outlined text-[16px]">verified_user</span>
                    <span>Transaksi Aman & Terenkripsi SSL</span>
                </div>
            </div>

            <div>
                <h4 class="text-white font-bold mb-3 text-xs uppercase tracking-wider">Layanan</h4>
                <ul class="space-y-2 text-xs text-slate-400">
                    <li><a href="{{ route('home') }}#paket" class="hover:text-cyan-400 transition-colors">Internet Rumah (PPPoE)</a></li>
                    <li><a href="{{ route('home') }}#hotspot" class="hover:text-cyan-400 transition-colors">Voucher Prabayar Hotspot</a></li>
                    <li><a href="{{ route('home') }}#member-hotspot" class="hover:text-cyan-400 transition-colors">Member Hotspot</a></li>
                    <li><a href="{{ route('guest.payment') }}" class="hover:text-cyan-400 transition-colors">Cek & Bayar Tagihan</a></li>
                </ul>
            </div>

            <div>
                <h4 class="text-white font-bold mb-3 text-xs uppercase tracking-wider">Legalitas & Kepatuhan</h4>
                <ul class="space-y-2 text-xs text-slate-400">
                    <li><a href="{{ route('legal.terms') }}" class="hover:text-cyan-400 transition-colors">Syarat & Ketentuan (TOS)</a></li>
                    <li><a href="{{ route('legal.privacy') }}" class="hover:text-cyan-400 transition-colors">Kebijakan Privasi</a></li>
                    <li><a href="{{ route('legal.refund') }}" class="hover:text-cyan-400 transition-colors">Kebijakan Refund & Pembatalan</a></li>
                    <li><a href="{{ route('legal.about') }}" class="hover:text-cyan-400 transition-colors">Tentang Kami</a></li>
                    <li><a href="{{ route('legal.contact') }}" class="hover:text-cyan-400 transition-colors">Hubungi Kami & Dukungan</a></li>
                </ul>
            </div>

            <div>
                <h4 class="text-white font-bold mb-3 text-xs uppercase tracking-wider">Kontak & Operasional</h4>
                <div class="space-y-2 text-xs text-slate-400 leading-relaxed">
                    <p><strong class="text-slate-300">Alamat:</strong><br>{{ $companyAddress }}, {{ $companyCity }}, {{ $companyProvince }} {{ $companyPostal }}</p>
                    <p><strong class="text-slate-300">WhatsApp/Telp:</strong><br>{{ $companyPhone }}</p>
                    <p><strong class="text-slate-300">Email:</strong><br>{{ $companyEmail }}</p>
                    <p><strong class="text-slate-300">Jam Operasional:</strong><br>{{ $companyHours }}</p>
                </div>
            </div>
        </div>

        <div class="max-w-7xl mx-auto pt-6 border-t border-slate-900 flex flex-col md:flex-row items-center justify-between text-xs text-slate-500 gap-4">
            <p>&copy; {{ date('Y') }} {{ $companyName }}. Hak Cipta Dilindungi Undang-Undang.</p>
            <div class="flex items-center gap-4">
                <span>Didukung oleh Payment Gateway Berizin Bank Indonesia</span>
            </div>
        </div>
    </footer>

</body>
</html>
