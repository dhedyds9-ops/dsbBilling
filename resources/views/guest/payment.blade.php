<!DOCTYPE html>
<html class="dark" lang="{{ str_replace('_', '-', app()->getLocale()) }}" style="scroll-behavior: smooth;">
<head>
    <meta charset="utf-8"/>
    <meta content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no" name="viewport"/>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $companyName }} - Halaman Isolir & Pembayaran Tagihan</title>
    
    @if(file_exists(public_path('build/manifest.json')))
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @else
        <link rel="stylesheet" href="/build/assets/app-B3A3qm5F.css">
    @endif
    
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800;900&display=swap" media="print" onload="this.media='all'"/>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" media="print" onload="this.media='all'"/>
    <link rel="icon" href="/favicon.png?v=2" type="image/png">

    <script id="tailwind-config">
        tailwind.config = {
            darkMode: "class",
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['"Plus Jakarta Sans"', 'sans-serif'],
                    },
                    colors: {
                        brand: {
                            50: '#eff6ff',
                            100: '#dbeafe',
                            500: '#3b82f6',
                            600: '#2563eb',
                            700: '#1d4ed8',
                        }
                    }
                }
            }
        }
    </script>
    <style>
        body {
            background-color: #0b1329;
            background-image: 
                radial-gradient(at 0% 0%, rgba(37, 99, 235, 0.15) 0, transparent 50%), 
                radial-gradient(at 100% 0%, rgba(225, 29, 72, 0.12) 0, transparent 50%),
                radial-gradient(at 50% 100%, rgba(15, 23, 42, 0.9) 0, transparent 60%);
            background-attachment: fixed;
            min-height: 100vh;
        }
        .glass-panel {
            background: rgba(17, 24, 39, 0.75);
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
            border: 1px solid rgba(255, 255, 255, 0.08);
        }
        .glass-card {
            background: rgba(30, 41, 59, 0.6);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            border: 1px solid rgba(255, 255, 255, 0.06);
        }
    </style>
</head>
<body class="font-sans text-slate-100 antialiased min-h-screen py-6 px-3 sm:px-6 lg:px-8 selection:bg-rose-500 selection:text-white">
    <div class="max-w-3xl mx-auto relative z-10">

        <!-- Header & Logo -->
        <header class="text-center mb-6 pt-2">
            @php
                $logoUrl = \App\Models\Setting::getValue('company.logo_url', null);
            @endphp
            <div class="inline-flex flex-col items-center gap-2">
                @if($logoUrl)
                    <div class="bg-white/95 dark:bg-slate-900/90 px-6 py-3 rounded-2xl shadow-xl border border-white/20 inline-block">
                        <img src="{{ $logoUrl }}" class="w-auto h-10 object-contain mx-auto" alt="Logo">
                    </div>
                @else
                    <div class="w-14 h-14 rounded-2xl bg-gradient-to-tr from-rose-600 via-indigo-600 to-cyan-400 flex items-center justify-center shadow-lg shadow-indigo-500/30 border border-white/20">
                        <span class="material-symbols-outlined text-white text-3xl">wifi_off</span>
                    </div>
                @endif
                <h1 class="text-2xl sm:text-3xl font-black text-white tracking-tight mt-1">{{ $companyName }}</h1>
                <p class="text-xs sm:text-sm text-slate-400 font-medium">Portal Resmi Informasi Tagihan & Reaktivasi Layanan</p>
            </div>
        </header>

        <!-- Isolir Notice Banner -->
        <div class="relative overflow-hidden rounded-2xl border border-rose-500/40 bg-gradient-to-br from-rose-950/60 via-rose-900/30 to-slate-900/80 p-5 sm:p-6 mb-6 shadow-2xl shadow-rose-950/40">
            <div class="absolute -right-6 -bottom-6 opacity-10 text-rose-400 pointer-events-none">
                <span class="material-symbols-outlined" style="font-size: 140px;">cloud_off</span>
            </div>
            <div class="flex items-start gap-4 relative z-10">
                <div class="w-12 h-12 rounded-xl bg-rose-500/20 border border-rose-500/40 flex items-center justify-center text-rose-400 shrink-0 shadow-inner">
                    <span class="material-symbols-outlined text-2xl">block</span>
                </div>
                <div class="flex-1 min-w-0">
                    <div class="flex flex-wrap items-center gap-2 mb-1.5">
                        <span class="px-2.5 py-0.5 rounded-full text-xs font-bold uppercase tracking-wider bg-rose-500/30 text-rose-200 border border-rose-400/40">
                            Pemberitahuan Isolir
                        </span>
                        @if($autoDetected)
                            <span class="px-2 py-0.5 rounded-full text-[11px] font-semibold bg-emerald-500/20 text-emerald-300 border border-emerald-400/30 flex items-center gap-1">
                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 animate-ping"></span>
                                Perangkat Terdeteksi Otomatis
                            </span>
                        @endif
                    </div>
                    <h2 class="text-lg sm:text-xl font-bold text-white leading-snug">Layanan Internet Anda Sementara Ditangguhkan</h2>
                    <p class="text-xs sm:text-sm text-slate-300 mt-1 leading-relaxed">
                        Koneksi dialihkan ke portal ini karena terdapat tagihan bulanan yang belum diselesaikan. Segera lakukan pembayaran agar akses internet Anda <strong class="text-white">otomatis aktif kembali dalam 1-3 menit</strong> tanpa perlu restart modem.
                    </p>
                </div>
            </div>
        </div>

        <!-- Search Box (Cari Tagihan) -->
        <div class="glass-panel rounded-2xl p-4 sm:p-6 mb-6 shadow-xl">
            <form action="{{ route('guest.payment') }}" method="GET" class="space-y-2">
                <label class="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-2">
                    Cari Tagihan / Cek Status Pelanggan
                </label>
                <div class="flex flex-col sm:flex-row gap-2.5">
                    <div class="relative flex-1">
                        <span class="material-symbols-outlined absolute top-1/2 -translate-y-1/2 text-slate-400" style="left: 0.875rem; font-size: 20px;">search</span>
                        <input type="text" name="q" value="{{ $query }}" placeholder="No WhatsApp / ID Pelanggan / Username PPPoE / No Invoice" 
                               class="w-full bg-slate-900/90 border border-slate-700 text-white placeholder-slate-500 rounded-xl pl-10 pr-4 py-3 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all">
                    </div>
                    <button type="submit" class="px-6 py-3 bg-gradient-to-r from-indigo-600 to-blue-600 hover:from-indigo-500 hover:to-blue-500 text-white font-bold rounded-xl shadow-lg shadow-indigo-600/30 transition-all flex items-center justify-center gap-2 text-sm shrink-0">
                        <span class="material-symbols-outlined text-sm">search</span>
                        Cari Tagihan
                    </button>
                </div>
            </form>
        </div>

        @if($error)
            <div class="glass-panel border-rose-500/40 bg-rose-950/40 text-rose-200 px-5 py-4 rounded-2xl mb-6 flex items-start gap-3 shadow-lg">
                <span class="material-symbols-outlined text-rose-400 shrink-0">error</span>
                <div class="text-sm">
                    <p class="font-bold">Data Tidak Ditemukan</p>
                    <p class="text-xs text-rose-300/90 mt-0.5">{{ $error }}</p>
                    <p class="text-xs text-slate-400 mt-2">Pastikan nomor WhatsApp, kode pelanggan, atau username yang Anda masukkan sudah benar.</p>
                </div>
            </div>
        @endif

        <!-- Identity & Service Summary (If Found) -->
        @if($customer || $pppoe || $hotspot || $voucher)
            <div class="glass-panel rounded-2xl p-5 sm:p-6 mb-6 shadow-xl">
                <div class="flex items-center justify-between pb-4 mb-4 border-b border-slate-800">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-indigo-500/20 text-indigo-400 flex items-center justify-center border border-indigo-500/30">
                            <span class="material-symbols-outlined text-xl">{{ $pppoe ? 'router' : 'wifi' }}</span>
                        </div>
                        <div>
                            <span class="text-xs text-slate-400 block font-medium">Informasi Pelanggan</span>
                            <h3 class="text-base sm:text-lg font-black text-white">{{ $customer?->name ?? ($pppoe?->username ?? $hotspot?->username) }}</h3>
                        </div>
                    </div>
                    <span class="px-2.5 py-1 rounded-md text-xs font-bold bg-rose-500/20 text-rose-300 border border-rose-500/40 flex items-center gap-1.5">
                        <span class="w-2 h-2 rounded-full bg-rose-500 animate-pulse"></span>
                        ISOLIR
                    </span>
                </div>

                <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 text-xs">
                    <div class="glass-card rounded-xl p-3 border border-slate-800">
                        <span class="text-slate-400 block mb-0.5">ID Pelanggan</span>
                        <span class="font-mono font-bold text-white text-sm truncate block">{{ $customer?->customer_code ?? $customer?->code ?? '-' }}</span>
                    </div>
                    <div class="glass-card rounded-xl p-3 border border-slate-800">
                        <span class="text-slate-400 block mb-0.5">Username</span>
                        <span class="font-mono font-bold text-indigo-300 text-sm truncate block">{{ $pppoe?->username ?? $hotspot?->username ?? $voucher?->code ?? '-' }}</span>
                    </div>
                    <div class="glass-card rounded-xl p-3 border border-slate-800">
                        <span class="text-slate-400 block mb-0.5">Paket Layanan</span>
                        <span class="font-bold text-white text-sm truncate block">{{ $pppoe?->serviceProfile?->name ?? $hotspot?->serviceProfile?->name ?? '-' }}</span>
                    </div>
                    <div class="glass-card rounded-xl p-3 border border-slate-800">
                        <span class="text-slate-400 block mb-0.5">No WhatsApp</span>
                        <span class="font-bold text-white text-sm truncate block">{{ $customer?->phone ?? '-' }}</span>
                    </div>
                </div>

                @if($activeSessions > 0 && !empty($activeSessionData))
                    <div class="mt-4 pt-3 border-t border-slate-800/80 flex flex-wrap items-center justify-between gap-2 text-xs">
                        <span class="text-slate-400 flex items-center gap-1.5">
                            <span class="w-2 h-2 rounded-full bg-emerald-400"></span>
                            IP Perangkat: <strong class="text-white font-mono">{{ $activeSessionData['ip'] ?? '-' }}</strong>
                        </span>
                        <span class="text-slate-400">
                            MAC: <span class="font-mono text-slate-300">{{ $activeSessionData['mac'] ?? '-' }}</span>
                        </span>
                    </div>
                @endif
            </div>
        @endif

        <!-- Invoices List -->
        @if($invoices->isNotEmpty())
            <div class="mb-6 space-y-4">
                <div class="flex items-center justify-between">
                    <h3 class="text-sm font-bold text-slate-300 uppercase tracking-wider flex items-center gap-2">
                        <span class="material-symbols-outlined text-rose-400 text-lg">receipt_long</span>
                        Tagihan Belum Lunas ({{ $invoices->count() }})
                    </h3>
                </div>

                @foreach($invoices as $inv)
                    @php
                        $sisa = max(0, $inv->total_amount - ($inv->paid_amount ?? 0));
                        $isOverdue = \Carbon\Carbon::parse($inv->due_date)->isPast();
                    @endphp
                    <div class="glass-panel rounded-2xl p-5 sm:p-6 border border-slate-700/80 shadow-xl transition-all hover:border-indigo-500/50">
                        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                            <div class="space-y-1.5">
                                <div class="flex flex-wrap items-center gap-2">
                                    <span class="px-2.5 py-0.5 rounded text-[11px] font-black {{ $isOverdue ? 'bg-rose-500/20 text-rose-400 border border-rose-500/40' : 'bg-amber-500/20 text-amber-400 border border-amber-500/40' }}">
                                        {{ $isOverdue ? 'JATUH TEMPO' : 'BELUM LUNAS' }}
                                    </span>
                                    <span class="font-mono font-bold text-slate-300 text-sm">{{ $inv->invoice_number }}</span>
                                </div>
                                <div class="text-2xl sm:text-3xl font-black text-white tracking-tight">
                                    Rp {{ number_format($sisa, 0, ',', '.') }}
                                </div>
                                <p class="text-xs text-slate-400">
                                    Batas Pembayaran: 
                                    <span class="{{ $isOverdue ? 'text-rose-400 font-bold' : 'text-slate-200 font-semibold' }}">
                                        {{ \Carbon\Carbon::parse($inv->due_date)->translatedFormat('d F Y') }}
                                    </span>
                                </p>
                            </div>

                            <div class="shrink-0 flex flex-col sm:flex-row gap-2">
                                <button type="button" 
                                        onclick="openPaymentModal('{{ $inv->invoice_number }}', {{ $sisa }})" 
                                        class="w-full sm:w-auto px-6 py-3.5 bg-gradient-to-r from-emerald-600 via-teal-600 to-cyan-600 hover:from-emerald-500 hover:to-cyan-500 text-white font-bold rounded-xl shadow-lg shadow-emerald-600/30 transition-all flex items-center justify-center gap-2 text-sm active:scale-95">
                                    <span class="material-symbols-outlined text-lg">payments</span>
                                    Bayar Sekarang (Online)
                                </button>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif

        <!-- Cara Bayar & Reaktivasi Otomatis Guide -->
        <div class="glass-panel rounded-2xl p-5 sm:p-6 mb-6 shadow-xl">
            <div class="flex items-center justify-between pb-3 mb-4 border-b border-slate-800">
                <h3 class="text-sm font-bold text-slate-200 uppercase tracking-wider flex items-center gap-2">
                    <span class="material-symbols-outlined text-indigo-400 text-lg">credit_card</span>
                    Cara Pembayaran & Reaktivasi
                </h3>
                <span class="text-xs text-emerald-400 font-bold flex items-center gap-1">
                    <span class="material-symbols-outlined text-sm">flash_on</span>
                    Otomatis Aktif
                </span>
            </div>

            <!-- Tab Buttons -->
            <div class="flex gap-2 p-1 bg-slate-900/80 rounded-xl mb-4 text-xs font-semibold">
                <button type="button" id="btn-tab-instan" onclick="switchTab('instan')" class="flex-1 py-2 px-3 rounded-lg transition-all text-center bg-indigo-600 text-white shadow-md">
                    QRIS &amp; Otomatis
                </button>
                <button type="button" id="btn-tab-manual" onclick="switchTab('manual')" class="flex-1 py-2 px-3 rounded-lg transition-all text-center text-slate-400 hover:text-white">
                    Transfer Bank Manual
                </button>
                <button type="button" id="btn-tab-panduan" onclick="switchTab('panduan')" class="flex-1 py-2 px-3 rounded-lg transition-all text-center text-slate-400 hover:text-white">
                    Langkah Aktivasi
                </button>
            </div>

            <!-- Tab 1: QRIS / Instant Gateway -->
            <div id="content-tab-instan" class="space-y-3">
                <div class="glass-card rounded-xl p-4 border border-slate-800">
                    <div class="flex items-start gap-3">
                        <div class="w-9 h-9 rounded-lg bg-emerald-500/20 text-emerald-400 flex items-center justify-center shrink-0">
                            <span class="material-symbols-outlined text-xl">qr_code_scanner</span>
                        </div>
                        <div>
                            <h4 class="text-sm font-bold text-white">QRIS (Semua Pembayaran Digital)</h4>
                            <p class="text-xs text-slate-400 mt-1 leading-relaxed">
                                Klik tombol <strong>"Bayar Sekarang"</strong> pada tagihan Anda. Scan QRIS menggunakan aplikasi perbankan (BCA Mobile, Livin Mandiri, BRImo, BNI) atau dompet digital (GoPay, OVO, DANA, ShopeePay).
                            </p>
                            <div class="mt-2.5 inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md bg-emerald-500/10 text-emerald-300 text-[11px] font-semibold">
                                <span class="material-symbols-outlined text-sm">check_circle</span>
                                Pembayaran terbaca otomatis dalam 1-3 menit
                            </div>
                        </div>
                    </div>
                </div>

                <div class="glass-card rounded-xl p-4 border border-slate-800">
                    <div class="flex items-start gap-3">
                        <div class="w-9 h-9 rounded-lg bg-blue-500/20 text-blue-400 flex items-center justify-center shrink-0">
                            <span class="material-symbols-outlined text-xl">account_balance</span>
                        </div>
                        <div>
                            <h4 class="text-sm font-bold text-white">Virtual Account (VA)</h4>
                            <p class="text-xs text-slate-400 mt-1 leading-relaxed">
                                Pilih metode Virtual Account BCA, Mandiri, BRI, atau BNI saat checkout. Transfer ke nomor VA yang diberikan dengan nominal yang sesuai.
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tab 2: Manual Bank Transfer -->
            <div id="content-tab-manual" class="space-y-3" style="display: none;">
                <p class="text-xs text-slate-300">
                    Silakan transfer ke salah satu rekening resmi di bawah, lalu kirim bukti transfer ke WhatsApp Customer Service:
                </p>

                @if(!empty($bankAccounts))
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        @foreach($bankAccounts as $bank)
                            <div class="glass-card rounded-xl p-4 border border-slate-800 relative group">
                                <div class="flex items-center justify-between mb-1.5">
                                    <span class="font-bold text-sm text-indigo-400 uppercase">{{ $bank['bank_name'] ?? 'Bank' }}</span>
                                    <button type="button" 
                                            onclick="copyToClipboard('{{ $bank['account_number'] ?? '' }}', 'Nomor rekening {{ $bank['bank_name'] ?? '' }} berhasil disalin')"
                                            class="text-xs font-semibold px-2 py-0.5 rounded bg-slate-700 hover:bg-indigo-600 text-slate-200 transition-colors flex items-center gap-1">
                                        <span class="material-symbols-outlined text-xs">content_copy</span>
                                        Salin
                                    </button>
                                </div>
                                <div class="font-mono text-base font-bold text-white tracking-wider">
                                    {{ $bank['account_number'] ?? '-' }}
                                </div>
                                <div class="text-xs text-slate-400 mt-1">
                                    a.n. <strong class="text-slate-200">{{ $bank['account_name'] ?? $companyName }}</strong>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="glass-card rounded-xl p-4 border border-slate-800">
                        <div class="flex items-center justify-between">
                            <div>
                                <span class="font-bold text-sm text-indigo-400">Rekening Pembayaran Resmi</span>
                                <p class="text-xs text-slate-400 mt-0.5">Hubungi CS Billing untuk informasi rekening dan konfirmasi cepat.</p>
                            </div>
                            <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $companyWhatsapp) }}?text={{ urlencode('Halo Admin, mohon info rekening resmi untuk bayar tagihan.') }}" 
                               target="_blank"
                               class="px-3 py-1.5 rounded-lg bg-emerald-600 hover:bg-emerald-500 text-white text-xs font-bold flex items-center gap-1">
                                <span class="material-symbols-outlined text-sm">chat</span>
                                Tanya Rekening
                            </a>
                        </div>
                    </div>
                @endif

                @php
                    $rawUname = $pppoe?->username ?? $hotspot?->username ?? $query;
                    $firstInv = $invoices->first();
                    $waText = "Halo Admin " . $companyName . ", saya ingin konfirmasi pembayaran tagihan:\n"
                            . "- Nama: " . ($customer?->name ?? '-') . "\n"
                            . "- Username / ID: " . ($rawUname ?: '-') . "\n"
                            . ($firstInv ? "- No Invoice: " . $firstInv->invoice_number . "\n- Nominal: Rp " . number_format($firstInv->total_amount, 0, ',', '.') : "");
                    $cleanWa = preg_replace('/[^0-9]/', '', $companyWhatsapp);
                    if (str_starts_with($cleanWa, '0')) {
                        $cleanWa = '62' . substr($cleanWa, 1);
                    }
                @endphp
                <div class="pt-2">
                    <a href="https://wa.me/{{ $cleanWa }}?text={{ urlencode($waText) }}" 
                       target="_blank"
                       class="w-full py-3 bg-emerald-600 hover:bg-emerald-500 text-white font-bold rounded-xl shadow-lg shadow-emerald-600/30 transition-all flex items-center justify-center gap-2 text-sm">
                        <span class="material-symbols-outlined text-lg">chat</span>
                        Konfirmasi Pembayaran via WhatsApp
                    </a>
                </div>
            </div>

            <!-- Tab 3: Langkah Aktivasi -->
            <div id="content-tab-panduan" class="space-y-4" style="display: none;">
                <div class="relative pl-6 space-y-4 before:content-[''] before:absolute before:left-2.5 before:top-2 before:bottom-2 before:w-0.5 before:bg-slate-700">
                    <div class="relative">
                        <span class="absolute -left-6 top-0.5 w-5 h-5 rounded-full bg-indigo-600 text-white text-[11px] font-bold flex items-center justify-center">1</span>
                        <h4 class="text-sm font-bold text-white">Pilih &amp; Selesaikan Pembayaran</h4>
                        <p class="text-xs text-slate-400 mt-0.5">Gunakan tombol online bayar (QRIS/VA) atau transfer bank ke rekening resmi yang tertera.</p>
                    </div>
                    <div class="relative">
                        <span class="absolute -left-6 top-0.5 w-5 h-5 rounded-full bg-indigo-600 text-white text-[11px] font-bold flex items-center justify-center">2</span>
                        <h4 class="text-sm font-bold text-white">Sistem Memverifikasi Otomatis</h4>
                        <p class="text-xs text-slate-400 mt-0.5">Begitu transaksi berhasil, status tagihan langsung terupdate di sistem tanpa delay.</p>
                    </div>
                    <div class="relative">
                        <span class="absolute -left-6 top-0.5 w-5 h-5 rounded-full bg-emerald-600 text-white text-[11px] font-bold flex items-center justify-center">3</span>
                        <h4 class="text-sm font-bold text-white">Internet Langsung Aktif</h4>
                        <p class="text-xs text-slate-400 mt-0.5">MikroTik Router memulihkan jalur internet Anda secara otomatis dalam 1-3 menit. Tidak perlu mematikan atau merestart modem Anda.</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Helpdesk & CS Card -->
        <div class="glass-card rounded-2xl p-5 mb-8 border border-slate-800 text-center">
            <h4 class="text-sm font-bold text-white mb-1">Butuh Bantuan Lebih Lanjut?</h4>
            <p class="text-xs text-slate-400 mb-4">Tim support dan billing kami siap membantu Anda setiap hari.</p>
            <div class="flex flex-wrap items-center justify-center gap-3">
                <a href="https://wa.me/{{ $cleanWa }}?text={{ urlencode('Halo CS ' . $companyName . ', saya butuh bantuan terkait layanan internet.') }}" 
                   target="_blank"
                   class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-emerald-600/90 hover:bg-emerald-500 text-white text-xs font-bold transition-all shadow-md">
                    <span class="material-symbols-outlined text-sm">chat</span>
                    WhatsApp Billing: {{ $companyWhatsapp }}
                </a>
                <a href="{{ route('home') }}" class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-200 text-xs font-bold transition-all border border-slate-700">
                    <span class="material-symbols-outlined text-sm">home</span>
                    Kembali ke Beranda
                </a>
            </div>
        </div>

        <!-- Toast Notification (Vanilla JS) -->
        <div id="toast" class="fixed bottom-6 right-6 z-50 px-4 py-2.5 rounded-xl bg-emerald-600 text-white text-xs font-bold shadow-2xl transition-all transform translate-y-20 opacity-0 pointer-events-none flex items-center gap-2">
            <span class="material-symbols-outlined text-sm">check_circle</span>
            <span id="toastMessage">Teks berhasil disalin</span>
        </div>

        <!-- Compliance & Legal Links -->
        <footer class="pt-6 border-t border-slate-800 text-center text-xs text-slate-500 space-y-2">
            <div class="flex flex-wrap justify-center gap-x-5 gap-y-1">
                <a href="{{ route('home') }}" class="hover:text-slate-300 transition-colors">Beranda</a>
                <a href="{{ route('legal.terms') }}" class="hover:text-slate-300 transition-colors">Syarat &amp; Ketentuan</a>
                <a href="{{ route('legal.privacy') }}" class="hover:text-slate-300 transition-colors">Kebijakan Privasi</a>
                <a href="{{ route('legal.contact') }}" class="hover:text-slate-300 transition-colors">Kontak Support</a>
            </div>
            <p>&copy; {{ date('Y') }} {{ $companyName }}. Pembayaran aman dengan enkripsi SSL 256-Bit didukung Gateway resmi berizin Bank Indonesia.</p>
        </footer>
    </div>

    <!-- Payment Gateway Modal -->
    <div id="paymentModal" class="fixed inset-0 z-50 flex items-center justify-center px-4" style="display: none;">
        <div class="fixed inset-0 bg-slate-950/80 backdrop-blur-sm transition-opacity cursor-pointer" onclick="closePaymentModal()"></div>
        
        <div class="glass-panel rounded-2xl shadow-2xl w-full max-w-md relative z-10 overflow-hidden flex flex-col border border-slate-700 transform transition-all">
            <div class="p-5 border-b border-slate-800 flex items-center justify-between bg-slate-900/80">
                <h3 class="text-base font-bold text-white flex items-center gap-2">
                    <span class="material-symbols-outlined text-indigo-400">payments</span>
                    Pilih Metode Pembayaran
                </h3>
                <button type="button" onclick="closePaymentModal()" class="text-slate-400 hover:text-white p-1 rounded-lg hover:bg-slate-800 transition">
                    <span class="material-symbols-outlined text-lg">close</span>
                </button>
            </div>
            
            <div class="p-5">
                <div class="mb-5 bg-slate-900/90 p-4 rounded-xl border border-slate-800">
                    <p class="text-xs text-slate-400 mb-0.5">Tagihan <span class="font-mono font-bold text-white" id="modalInvoiceText"></span></p>
                    <p class="text-2xl font-black text-white" id="modalAmountText"></p>
                </div>

                <form method="POST" action="{{ route('guest.payment.checkout') }}">
                    @csrf
                    <input type="hidden" name="invoice_number" id="modalInvoiceInput">
                    
                    @if(!empty($activeGateways))
                        <label class="block text-xs font-semibold text-slate-400 uppercase tracking-wider mb-2">Pilih Saluran Pembayaran</label>
                        <div class="space-y-2 mb-6 max-h-[35vh] overflow-y-auto pr-1">
                            @foreach($activeGateways as $key => $g)
                            <label class="flex items-center justify-between p-3.5 border border-slate-800 rounded-xl cursor-pointer hover:bg-slate-800/60 transition-all group has-[:checked]:border-indigo-500 has-[:checked]:bg-indigo-950/30">
                                <div class="flex items-center gap-3">
                                    <input type="radio" name="gateway" value="{{ $key }}" class="text-indigo-600 focus:ring-indigo-500 border-slate-700 bg-slate-900 w-4 h-4" {{ $loop->first ? 'checked' : '' }}>
                                    <div>
                                        <span class="font-bold text-sm text-white group-has-[:checked]:text-indigo-300 block uppercase">{{ $key }}</span>
                                        <span class="text-[11px] text-slate-400">QRIS / VA / Instant Checkout</span>
                                    </div>
                                </div>
                                <span class="material-symbols-outlined text-indigo-400 hidden group-has-[:checked]:block text-lg">check_circle</span>
                            </label>
                            @endforeach
                        </div>
                        <button type="submit" class="w-full py-3.5 bg-gradient-to-r from-indigo-600 to-blue-600 hover:from-indigo-500 hover:to-blue-500 text-white font-bold rounded-xl shadow-lg shadow-indigo-600/30 transition-all flex items-center justify-center gap-2 text-sm">
                            Lanjutkan ke Pembayaran <span class="material-symbols-outlined text-base">arrow_forward</span>
                        </button>
                    @else
                        <div class="p-3.5 bg-amber-500/10 border border-amber-500/30 rounded-xl mb-4 text-xs text-amber-300">
                            Payment Gateway otomatis belum aktif. Silakan pilih opsi Transfer Bank Manual atau konfirmasi via WhatsApp CS di bawah.
                        </div>
                        <a href="https://wa.me/{{ $cleanWa }}?text={{ urlencode($waText) }}" target="_blank" class="w-full py-3 bg-emerald-600 hover:bg-emerald-500 text-white font-bold rounded-xl transition-colors shadow-lg flex items-center justify-center gap-2 text-sm">
                            <span class="material-symbols-outlined text-base">chat</span>
                            Konfirmasi ke WhatsApp Billing
                        </a>
                    @endif
                </form>
            </div>
        </div>
    </div>
    
    <script>
        function switchTab(name) {
            ['instan', 'manual', 'panduan'].forEach(function(t) {
                var content = document.getElementById('content-tab-' + t);
                var btn = document.getElementById('btn-tab-' + t);
                if (content) {
                    content.style.display = (t === name) ? 'block' : 'none';
                }
                if (btn) {
                    if (t === name) {
                        btn.className = 'flex-1 py-2 px-3 rounded-lg transition-all text-center bg-indigo-600 text-white shadow-md';
                    } else {
                        btn.className = 'flex-1 py-2 px-3 rounded-lg transition-all text-center text-slate-400 hover:text-white';
                    }
                }
            });
        }

        function openPaymentModal(invoiceNumber, amount) {
            document.getElementById('modalInvoiceText').innerText = invoiceNumber;
            document.getElementById('modalInvoiceInput').value = invoiceNumber;
            document.getElementById('modalAmountText').innerText = 'Rp ' + new Intl.NumberFormat('id-ID').format(amount);
            document.getElementById('paymentModal').style.display = 'flex';
        }

        function closePaymentModal() {
            document.getElementById('paymentModal').style.display = 'none';
        }

        function copyToClipboard(text, message) {
            if (navigator.clipboard && navigator.clipboard.writeText) {
                navigator.clipboard.writeText(text);
            } else {
                const input = document.createElement('textarea');
                input.value = text;
                document.body.appendChild(input);
                input.select();
                document.execCommand('copy');
                document.body.removeChild(input);
            }
            showToast(message || 'Berhasil disalin');
        }

        function showToast(msg) {
            const toast = document.getElementById('toast');
            const msgEl = document.getElementById('toastMessage');
            if (toast && msgEl) {
                msgEl.innerText = msg;
                toast.classList.remove('translate-y-20', 'opacity-0');
                toast.classList.add('translate-y-0', 'opacity-100');
                setTimeout(() => {
                    toast.classList.remove('translate-y-0', 'opacity-100');
                    toast.classList.add('translate-y-20', 'opacity-0');
                }, 2500);
            }
        }
    </script>
</body>
</html>
