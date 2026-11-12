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
    
    <link rel="icon" href="/favicon.png?v=2" type="image/png">

    <style>
        body {
            background-color: #0b1329;
            background-image: 
                radial-gradient(at 0% 0%, rgba(37, 99, 235, 0.15) 0, transparent 50%), 
                radial-gradient(at 100% 0%, rgba(225, 29, 72, 0.12) 0, transparent 50%),
                radial-gradient(at 50% 100%, rgba(15, 23, 42, 0.9) 0, transparent 60%);
            background-attachment: fixed;
            min-height: 100vh;
            font-family: system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
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
<body class="text-slate-100 antialiased min-h-screen py-6 px-3 sm:px-6 lg:px-8 selection:bg-rose-500 selection:text-white">
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
                        <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><line x1="1" y1="1" x2="23" y2="23"/><path d="M16.72 11.06A10.94 10.94 0 0 1 19 12.55"/><path d="M5 12.55a10.94 10.94 0 0 1 5.17-2.39"/><path d="M10.71 5.05A16 16 0 0 1 22.58 9"/><path d="M1.42 9a15.91 15.91 0 0 1 4.7-2.88"/><path d="M8.53 16.11a6 6 0 0 1 6.95 0"/><line x1="12" y1="20" x2="12.01" y2="20"/></svg>
                    </div>
                @endif
                <h1 class="text-2xl sm:text-3xl font-black text-white tracking-tight mt-1">{{ $companyName }}</h1>
                <p class="text-xs sm:text-sm text-slate-400 font-medium">Portal Resmi Informasi Tagihan & Reaktivasi Layanan</p>
            </div>
        </header>

        <!-- Isolir Notice Banner -->
        <div class="relative overflow-hidden rounded-2xl border border-rose-500/40 bg-gradient-to-br from-rose-950/60 via-rose-900/30 to-slate-900/80 p-5 sm:p-6 mb-6 shadow-2xl shadow-rose-950/40">
            <div class="absolute -right-6 -bottom-6 opacity-10 text-rose-400 pointer-events-none">
                <svg class="w-32 h-32" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path d="M22.61 16.95A5 5 0 0 0 18 10h-1.26a8 8 0 0 0-7.05-6M5 5a8 8 0 0 0-4 7h1a5 5 0 0 0 4.54 4.95"/><line x1="2" y1="2" x2="22" y2="22"/></svg>
            </div>
            <div class="flex items-start gap-4 relative z-10">
                <div class="w-12 h-12 rounded-xl bg-rose-500/20 border border-rose-500/40 flex items-center justify-center text-rose-400 shrink-0 shadow-inner">
                    <svg class="w-6 h-6 text-rose-400" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><line x1="4.93" y1="4.93" x2="19.07" y2="19.07"/></svg>
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
                        <svg class="w-5 h-5 absolute top-1/2 -translate-y-1/2 text-slate-400" style="left: 0.875rem;" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/></svg>
                        <input type="text" name="q" value="{{ $query }}" placeholder="No WhatsApp / ID Pelanggan / Username PPPoE / No Invoice" 
                               class="w-full bg-slate-900/90 border border-slate-700 text-white placeholder-slate-500 rounded-xl pl-10 pr-4 py-3 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all">
                    </div>
                    <button type="submit" class="px-6 py-3 bg-gradient-to-r from-indigo-600 to-blue-600 hover:from-indigo-500 hover:to-blue-500 text-white font-bold rounded-xl shadow-lg shadow-indigo-600/30 transition-all flex items-center justify-center gap-2 text-sm shrink-0">
                        <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/></svg>
                        Cari Tagihan
                    </button>
                </div>
            </form>
        </div>

        @if($error)
            <div class="glass-panel border-rose-500/40 bg-rose-950/40 text-rose-200 px-5 py-4 rounded-2xl mb-6 flex items-start gap-3 shadow-lg">
                <svg class="w-5 h-5 text-rose-400 shrink-0 mt-0.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
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
                            @if($pppoe)
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><rect width="20" height="8" x="2" y="14" rx="2"/><path d="M6 14V6"/><path d="M18 14V6"/></svg>
                            @else
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M5 13a10 10 0 0 1 14 0"/><path d="M8.5 16.5a5 5 0 0 1 7 0"/><path d="M12 20h.01"/></svg>
                            @endif
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
                        <svg class="w-5 h-5 text-rose-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M4 2v20l2-1 2 1 2-1 2 1 2-1 2 1 2-1 2 1V2l-2 1-2-1-2 1-2-1-2 1-2-1-2 1Z"/><path d="M8 7h8"/><path d="M8 11h8"/><path d="M8 15h5"/></svg>
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
                                    <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><rect width="20" height="14" x="2" y="5" rx="2"/><line x1="2" x2="22" y1="10" y2="10"/></svg>
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
                    <svg class="w-5 h-5 text-indigo-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><rect width="20" height="14" x="2" y="5" rx="2"/><line x1="2" x2="22" y1="10" y2="10"/></svg>
                    Cara Pembayaran &amp; Reaktivasi
                </h3>
                <span class="text-xs text-emerald-400 font-bold flex items-center gap-1">
                    <svg class="w-4 h-4 text-emerald-400" fill="currentColor" viewBox="0 0 24 24"><polygon points="13 2 3 14 12 14 11 22 21 10 12 10 13 2"/></svg>
                    Otomatis Aktif
                </span>
            </div>

            <!-- Tab Buttons -->
            <div class="flex gap-2 p-1 bg-slate-900/80 rounded-xl mb-4 text-xs font-semibold">
                <button type="button" id="btn-tab-instan" onclick="switchTab('instan')" class="flex-1 py-2 px-3 rounded-lg transition-all text-center bg-indigo-600 text-white shadow-md">
                    QRIS &amp; Otomatis
                </button>
                <button type="button" id="btn-tab-manual" onclick="switchTab('manual')" class="flex-1 py-2 px-3 rounded-lg transition-all text-center text-slate-400 hover:text-white">
                    Transfer &amp; E-Wallet
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
                            <svg class="w-5 h-5 text-emerald-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><rect width="5" height="5" x="3" y="3" rx="1"/><rect width="5" height="5" x="16" y="3" rx="1"/><rect width="5" height="5" x="3" y="16" rx="1"/><path d="M21 16v3a2 2 0 0 1-2 2h-3"/><path d="M3 12h5"/><path d="M12 3v5"/><path d="M12 16v5"/><path d="M16 12h5"/></svg>
                        </div>
                        <div>
                            <h4 class="text-sm font-bold text-white">QRIS (Semua Pembayaran Digital)</h4>
                            <p class="text-xs text-slate-400 mt-1 leading-relaxed">
                                Klik tombol <strong>"Bayar Sekarang"</strong> pada tagihan Anda. Scan QRIS menggunakan aplikasi perbankan (BCA Mobile, Livin Mandiri, BRImo, BNI) atau dompet digital (GoPay, OVO, DANA, ShopeePay).
                            </p>
                            <div class="mt-2.5 inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md bg-emerald-500/10 text-emerald-300 text-[11px] font-semibold">
                                <svg class="w-4 h-4 text-emerald-300" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><path d="m9 12 2 2 4-4"/></svg>
                                Pembayaran terbaca otomatis dalam 1-3 menit
                            </div>
                        </div>
                    </div>
                </div>

                <div class="glass-card rounded-xl p-4 border border-slate-800">
                    <div class="flex items-start gap-3">
                        <div class="w-9 h-9 rounded-lg bg-blue-500/20 text-blue-400 flex items-center justify-center shrink-0">
                            <svg class="w-5 h-5 text-blue-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="m3 9 9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>
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
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><rect width="13" height="13" x="9" y="9" rx="2"/><path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"/></svg>
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
                                <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 24 24"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/></svg>
                                Tanya Rekening
                            </a>
                        </div>
                    </div>
                @endif

                @if(!empty($manualEwallets))
                    <div class="pt-2">
                        <span class="block text-xs font-semibold text-slate-400 uppercase tracking-wider mb-2">E-Wallet Resmi</span>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            @foreach($manualEwallets as $ew)
                                <div class="glass-card rounded-xl p-4 border border-slate-800 relative group">
                                    <div class="flex items-center justify-between mb-1.5">
                                        <span class="font-bold text-sm text-cyan-400 uppercase">{{ $ew['name'] ?? 'E-Wallet' }}</span>
                                        <button type="button" 
                                                onclick="copyToClipboard('{{ $ew['number'] ?? ($ew['account_number'] ?? '') }}', 'Nomor {{ $ew['name'] ?? 'E-Wallet' }} berhasil disalin')"
                                                class="text-xs font-semibold px-2 py-0.5 rounded bg-slate-700 hover:bg-cyan-600 text-slate-200 transition-colors flex items-center gap-1">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><rect width="13" height="13" x="9" y="9" rx="2"/><path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"/></svg>
                                            Salin
                                        </button>
                                    </div>
                                    <div class="font-mono text-base font-bold text-white tracking-wider">
                                        {{ $ew['number'] ?? ($ew['account_number'] ?? '-') }}
                                    </div>
                                    <div class="text-xs text-slate-400 mt-1">
                                        a.n. <strong class="text-slate-200">{{ $ew['holder'] ?? ($ew['account_name'] ?? $companyName) }}</strong>
                                    </div>
                                </div>
                            @endforeach
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
                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/></svg>
                        Konfirmasi Pembayaran via WhatsApp
                    </a>
                </div>
            </div>

            <!-- Tab 3: Langkah Aktivasi -->
            <div id="content-tab-panduan" class="space-y-4" style="display: none;">
                <div class="space-y-4">
                    <div class="flex items-start gap-3.5 glass-card p-3.5 rounded-xl border border-slate-800">
                        <div class="w-7 h-7 rounded-full bg-indigo-600 text-white text-xs font-black flex items-center justify-center shrink-0 mt-0.5 shadow-md shadow-indigo-600/40">1</div>
                        <div class="flex-1 min-w-0">
                            <h4 class="text-sm font-bold text-white">Pilih &amp; Selesaikan Pembayaran</h4>
                            <p class="text-xs text-slate-400 mt-1 leading-relaxed">Gunakan tombol online bayar (QRIS/VA) atau transfer bank / e-wallet ke rekening resmi yang tertera.</p>
                        </div>
                    </div>
                    <div class="flex items-start gap-3.5 glass-card p-3.5 rounded-xl border border-slate-800">
                        <div class="w-7 h-7 rounded-full bg-indigo-600 text-white text-xs font-black flex items-center justify-center shrink-0 mt-0.5 shadow-md shadow-indigo-600/40">2</div>
                        <div class="flex-1 min-w-0">
                            <h4 class="text-sm font-bold text-white">Sistem Memverifikasi Otomatis</h4>
                            <p class="text-xs text-slate-400 mt-1 leading-relaxed">Begitu transaksi berhasil, status tagihan langsung terupdate di sistem tanpa delay.</p>
                        </div>
                    </div>
                    <div class="flex items-start gap-3.5 glass-card p-3.5 rounded-xl border border-slate-800">
                        <div class="w-7 h-7 rounded-full bg-emerald-600 text-white text-xs font-black flex items-center justify-center shrink-0 mt-0.5 shadow-md shadow-emerald-600/40">3</div>
                        <div class="flex-1 min-w-0">
                            <h4 class="text-sm font-bold text-white">Internet Langsung Aktif</h4>
                            <p class="text-xs text-slate-400 mt-1 leading-relaxed">MikroTik Router memulihkan jalur internet Anda secara otomatis dalam 1-3 menit. Tidak perlu mematikan atau merestart modem Anda.</p>
                        </div>
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
                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/></svg>
                    WhatsApp Billing: {{ $companyWhatsapp }}
                </a>
                <a href="{{ route('home') }}" class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-200 text-xs font-bold transition-all border border-slate-700">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="m3 9 9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>
                    Kembali ke Beranda
                </a>
            </div>
        </div>

        <!-- Toast Notification (Vanilla JS) -->
        <div id="toast" class="fixed bottom-6 right-6 z-50 px-4 py-2.5 rounded-xl bg-emerald-600 text-white text-xs font-bold shadow-2xl transition-all transform translate-y-20 opacity-0 pointer-events-none flex items-center gap-2">
            <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><path d="m9 12 2 2 4-4"/></svg>
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
                    <svg class="w-5 h-5 text-indigo-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><rect width="20" height="14" x="2" y="5" rx="2"/><line x1="2" x2="22" y1="10" y2="10"/></svg>
                    Pilih Metode Pembayaran
                </h3>
                <button type="button" onclick="closePaymentModal()" class="text-slate-400 hover:text-white p-1 rounded-lg hover:bg-slate-800 transition">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M18 6 6 18"/><path d="m6 6 12 12"/></svg>
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
                            @php
                                $gwName = match(strtolower($key)) {
                                    'ipaymu' => 'iPaymu (QRIS & Virtual Account)',
                                    'tripay' => 'Tripay (Multi-Channel)',
                                    'midtrans' => 'Midtrans (Snap & GoPay)',
                                    'xendit' => 'Xendit (QRIS & VA)',
                                    'duitku' => 'Duitku Payment',
                                    default => ($g['label'] ?? strtoupper($key))
                                };
                                $gwDesc = match(strtolower($key)) {
                                    'ipaymu' => 'QRIS Semua E-Wallet (DANA/GoPay/OVO) & VA Bank',
                                    'tripay' => 'QRIS, E-Wallet, VA Bank & Gerai Retail',
                                    'midtrans' => 'GoPay, ShopeePay, QRIS & VA Semua Bank',
                                    'xendit' => 'QRIS, OVO, DANA & VA Bank Otomatis',
                                    'duitku' => 'QRIS, E-Wallet & Gerai Retail',
                                    default => 'QRIS / VA / Instant Checkout Otomatis'
                                };
                            @endphp
                            <label class="flex items-center justify-between p-3.5 border border-slate-800 rounded-xl cursor-pointer hover:bg-slate-800/60 transition-all group has-[:checked]:border-indigo-500 has-[:checked]:bg-indigo-950/30">
                                <div class="flex items-center gap-3">
                                    <input type="radio" name="gateway" value="{{ $key }}" class="text-indigo-600 focus:ring-indigo-500 border-slate-700 bg-slate-900 w-4 h-4" {{ $loop->first ? 'checked' : '' }}>
                                    <div>
                                        <span class="font-bold text-sm text-white group-has-[:checked]:text-indigo-300 block">{{ $gwName }}</span>
                                        <span class="text-[11px] text-slate-400">{{ $gwDesc }}</span>
                                    </div>
                                </div>
                                <svg class="w-5 h-5 text-indigo-400 hidden group-has-[:checked]:block" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><path d="m9 12 2 2 4-4"/></svg>
                            </label>
                            @endforeach
                        </div>
                        <button type="submit" class="w-full py-3.5 bg-gradient-to-r from-indigo-600 to-blue-600 hover:from-indigo-500 hover:to-blue-500 text-white font-bold rounded-xl shadow-lg shadow-indigo-600/30 transition-all flex items-center justify-center gap-2 text-sm">
                            Lanjutkan ke Pembayaran 
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M5 12h14"/><path d="m12 5 7 7-7 7"/></svg>
                        </button>
                    @else
                        <div class="p-3.5 bg-amber-500/10 border border-amber-500/30 rounded-xl mb-4 text-xs text-amber-300">
                            Payment Gateway otomatis belum aktif. Silakan pilih opsi Transfer Bank Manual atau konfirmasi via WhatsApp CS di bawah.
                        </div>
                        <a href="https://wa.me/{{ $cleanWa }}?text={{ urlencode($waText) }}" target="_blank" class="w-full py-3 bg-emerald-600 hover:bg-emerald-500 text-white font-bold rounded-xl transition-colors shadow-lg flex items-center justify-center gap-2 text-sm">
                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/></svg>
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
