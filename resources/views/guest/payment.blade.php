<!DOCTYPE html>
<html class="dark" lang="{{ str_replace('_', '-', app()->getLocale()) }}" style="scroll-behavior: smooth;">
<head>
    <meta charset="utf-8"/>
    <meta content="width=device-width, initial-scale=1.0" name="viewport"/>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'dsBilling') }} - Guest Payment</title>
    
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
    
    <link href="https://fonts.googleapis.com/css2?family=Hanken+Grotesk:wght@400;700;800;900&amp;family=Inter:wght@400;500;700&amp;display=swap" rel="stylesheet"/>
    <link href="https://fonts.googleapis.com/css2?family=Caveat:wght@400..700&amp;display=swap" rel="stylesheet"/>
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&amp;display=swap" rel="stylesheet"/>
    <link rel="icon" href="/favicon.png?v=2" type="image/png">

    <script id="tailwind-config">
        tailwind.config = {
            darkMode: "class",
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Inter', 'sans-serif'],
                        heading: ['Hanken Grotesk', 'sans-serif'],
                        cursive: ['Caveat', 'cursive']
                    },
                    colors: {
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
                        "on-error-container": "#ffdad6"
                    }
                }
            }
        }
    </script>
    <style>
        body {
            background-color: #0b1326;
            background-image: 
                radial-gradient(at 0% 0%, hsla(253,16%,7%,1) 0, transparent 50%), 
                radial-gradient(at 50% 0%, hsla(225,39%,30%,0.2) 0, transparent 50%), 
                radial-gradient(at 100% 0%, hsla(339,49%,30%,0.1) 0, transparent 50%);
            background-attachment: fixed;
        }
    </style>
</head>
<body class="font-sans text-on-surface antialiased min-h-screen py-10 px-4 sm:px-6 lg:px-8">
    <div class="max-w-3xl mx-auto relative z-10">
        <!-- Logo -->
        <div class="text-center mb-12">
            <a href="{{ route('home') }}" class="inline-flex flex-col items-center gap-4">
                @php
                    $logoUrl = \App\Models\Setting::getValue('company.logo_url', null);
                    $companyName = \App\Models\Setting::getValue('company.name', 'dsBilling');
                @endphp
                @if($logoUrl)
                    <div class="bg-white dark:bg-slate-800 px-8 py-4 rounded-2xl shadow-2xl border border-white/20">
                        <img src="{{ $logoUrl }}" class="w-auto h-12 object-contain" alt="Logo">
                    </div>
                @else
                    <div class="w-16 h-16 rounded-2xl bg-gradient-to-br from-primary-container to-secondary-container flex items-center justify-center shadow-lg shadow-primary-container/40 border border-white/20">
                        <span class="material-symbols-outlined text-white text-4xl">rocket_launch</span>
                    </div>
                @endif
                <span class="text-3xl font-heading font-extrabold text-white tracking-tight drop-shadow-md">{{ $companyName }}</span>
            </a>
            <p class="text-primary-fixed-dim/80 mt-2 font-medium">Portal Pembayaran Tagihan & Cek Layanan</p>
        </div>

        <!-- Search Box -->
        <div class="bg-surface-container/60 backdrop-blur-2xl rounded-[24px] p-6 sm:p-10 mb-10 border border-primary/20 shadow-[0_0_25px_rgba(76,215,246,0.15)]">
            <form action="{{ route('guest.payment') }}" method="GET">
                <div class="flex flex-col sm:flex-row gap-5">
                    <div class="relative flex-1">
                        <span class="material-symbols-outlined absolute top-1/2 -translate-y-1/2 text-gray-400" style="left: 1.25rem;">search</span>
                        <input type="text" name="q" value="{{ $query }}" placeholder="No Tagihan / Kode Voucher / No HP Pelanggan" class="w-full bg-surface-container-highest border border-outline-variant text-white placeholder-gray-400 rounded-xl pl-12 pr-4 py-4 focus:ring-2 focus:ring-primary focus:border-primary transition-all text-base dark:bg-slate-900 dark:text-slate-100">
                    </div>
                    <button type="submit" class="px-8 py-4 bg-gradient-to-r from-primary-container to-secondary-container hover:from-primary hover:to-secondary-container text-white font-heading font-bold rounded-xl shadow-lg shadow-primary-container/30 transition-all flex items-center justify-center gap-2 text-base">
                        Cari Data
                    </button>
                </div>
            </form>
        </div>

        @if($query)
            @if($error)
                <div class="bg-error/10 border border-error/20 text-error px-6 py-4 rounded-xl mb-8 flex items-center gap-3 backdrop-blur-md">
                    <span class="material-symbols-outlined shrink-0">error</span>
                    <p class="font-medium">{{ $error }}</p>
                </div>
            @else
                
                @if($hotspot || $voucher)
                    <div class="bg-surface-container/60 backdrop-blur-2xl rounded-[24px] p-6 sm:p-8 mb-8 border border-outline-variant shadow-xl">
                        <div class="flex items-center gap-3 mb-6">
                            <div class="w-10 h-10 rounded-full bg-secondary-container/20 flex items-center justify-center">
                                <span class="material-symbols-outlined text-secondary">wifi</span>
                            </div>
                            <h2 class="text-xl font-heading font-bold text-white">Status Layanan Internet</h2>
                        </div>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                            <div class="bg-surface-container-highest rounded-xl p-5 border border-outline-variant/50">
                                <p class="text-sm text-gray-400 mb-1">Username / Kode</p>
                                <p class="text-lg font-bold text-white">{{ $hotspot->username ?? $voucher->code }}</p>
                            </div>
                            <div class="bg-surface-container-highest rounded-xl p-5 border border-outline-variant/50">
                                <p class="text-sm text-gray-400 mb-1">Paket</p>
                                <p class="text-lg font-bold text-white">{{ $hotspot->serviceProfile->name ?? $voucher->serviceProfile->name ?? '-' }}</p>
                            </div>
                            <div class="bg-surface-container-highest rounded-xl p-5 border border-outline-variant/50">
                                <p class="text-sm text-gray-400 mb-1">Status Sesi</p>
                                @if($activeSessions > 0)
                                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-md bg-tertiary-container/20 text-tertiary border border-tertiary-container/30 text-sm font-bold">
                                        <span class="w-2 h-2 rounded-full bg-tertiary animate-pulse"></span> ONLINE (Terhubung)
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-md bg-gray-500/20 text-gray-400 border border-gray-500/30 text-sm font-bold">
                                        OFFLINE
                                    </span>
                                @endif
                            </div>
                            <div class="bg-surface-container-highest rounded-xl p-5 border border-outline-variant/50">
                                <p class="text-sm text-gray-400 mb-1">Batas Waktu / Kuota</p>
                                <p class="text-base font-bold text-white">
                                    {{ $hotspot->uptime_limit ?? $voucher->uptime_limit ?? 'Unlimited' }}
                                    @if($hotspot->bytes_out_limit || $voucher->bytes_out_limit)
                                        / {{ $hotspot->bytes_out_limit ?? $voucher->bytes_out_limit }}
                                    @endif
                                </p>
                            </div>
                        </div>

                        @if($activeSessions > 0)
                        <div class="p-5 bg-error/10 border border-error/20 rounded-xl">
                            <div class="flex items-start gap-4">
                                <span class="material-symbols-outlined text-error shrink-0 mt-0.5">devices</span>
                                <div class="flex-1">
                                    <h4 class="font-bold text-white mb-1">Tidak bisa login di perangkat baru?</h4>
                                    <p class="text-sm text-gray-300 mb-4">Hal ini biasanya terjadi karena Anda belum logout dari perangkat sebelumnya atau sesi internet masih nyangkut di sistem.</p>
                                    
                                    <form method="POST" action="{{ route('guest.payment.reset') }}">
                                        @csrf
                                        <input type="hidden" name="hotspot_user_id" value="{{ $hotspot->id }}">
                                        <input type="hidden" name="q" value="{{ $query }}">
                                        <button type="submit" class="px-5 py-2.5 bg-error/20 hover:bg-error/30 text-error font-bold rounded-lg transition-colors border border-error/30 text-sm flex items-center gap-2">
                                            <span class="material-symbols-outlined text-sm">restart_alt</span>
                                            Reset Sesi Sekarang
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                        @endif
                    </div>
                @endif

                @if($customer && $invoices->isNotEmpty())
                    <div class="mb-6 flex items-center gap-3">
                        <div class="w-10 h-10 rounded-full bg-primary-container/20 flex items-center justify-center">
                            <span class="material-symbols-outlined text-primary">person</span>
                        </div>
                        <div>
                            <p class="text-sm text-gray-400">Tagihan atas nama</p>
                            <h2 class="text-xl font-heading font-bold text-white">{{ $customer->name }}</h2>
                        </div>
                    </div>
                @endif

                <div class="space-y-6">
                    @foreach($invoices as $inv)
                        @php
                            $sisa = $inv->total_amount - ($inv->paid_amount ?? 0);
                        @endphp
                        <div class="bg-surface-container/60 backdrop-blur-2xl rounded-[24px] p-8 flex flex-col sm:flex-row items-center justify-between gap-8 hover:bg-surface-container-highest transition-all duration-300 border border-primary/20 shadow-[0_0_25px_rgba(76,215,246,0.15)]">
                            <div class="flex-1 w-full text-center sm:text-left">
                                <div class="flex flex-col sm:flex-row items-center gap-4 mb-4">
                                    <span class="px-4 py-1.5 rounded-lg text-xs font-black bg-error/20 text-error border border-error/30">BELUM LUNAS</span>
                                    <span class="text-base font-bold text-white">{{ $inv->invoice_number }}</span>
                                </div>
                                <h3 class="text-4xl font-heading font-black text-white mb-3 tracking-tight">Rp {{ number_format($sisa, 0, ',', '.') }}</h3>
                                <p class="text-base font-medium text-gray-400">
                                    Jatuh Tempo: <span class="{{ \Carbon\Carbon::parse($inv->due_date)->isPast() ? 'text-error font-bold' : 'text-white font-bold' }}">{{ \Carbon\Carbon::parse($inv->due_date)->translatedFormat('d F Y') }}</span>
                                </p>
                            </div>
                            <div class="w-full sm:w-auto shrink-0 flex flex-col items-center">
                                <button type="button" onclick="openPaymentModal('{{ $inv->invoice_number }}', {{ $sisa }})" class="w-full sm:w-auto px-6 py-4 sm:px-8 sm:py-5 bg-gradient-to-r from-primary-container to-secondary-container hover:from-primary hover:to-secondary-container text-white font-heading font-bold rounded-xl shadow-lg shadow-primary-container/30 transition-all active:scale-95 flex items-center justify-center gap-2 sm:gap-3 text-base sm:text-lg whitespace-nowrap">
                                    <span class="material-symbols-outlined">account_balance_wallet</span>
                                    Bayar Tagihan
                                </button>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        @endif

        <div class="text-center mt-16 mb-12 mt-4">
            <a href="{{ route('home') }}" class="inline-flex items-center gap-2 px-6 py-3 rounded-full bg-surface-container-highest hover:bg-surface-bright backdrop-blur-md text-sm font-bold text-white transition-all hover:scale-105 border border-outline-variant shadow-[0_0_15px_rgba(76,215,246,0.1)]">
                <span class="material-symbols-outlined text-sm">arrow_back</span>
                Kembali ke Beranda
            </a>
        </div>
    </div>

    <!-- Payment Gateway Modal (Vanilla JS) -->
    <div id="paymentModal" class="fixed inset-0 z-50 flex items-center justify-center px-4" style="display: none;">
        <div class="fixed inset-0 bg-surface/80 backdrop-blur-sm transition-opacity cursor-pointer" onclick="closePaymentModal()"></div>
        
        <div class="bg-surface-container rounded-[24px] shadow-2xl w-full max-w-md relative z-10 overflow-hidden flex flex-col transform transition-all border border-outline-variant">
            <div class="p-6 border-b border-outline-variant flex items-center justify-between bg-surface-container-highest">
                <h3 class="text-lg font-heading font-bold text-white">Pilih Metode Pembayaran</h3>
                <button type="button" onclick="closePaymentModal()" class="text-gray-400 hover:text-white bg-surface-container hover:bg-surface-bright rounded-full p-1.5 transition">
                    <span class="material-symbols-outlined block">close</span>
                </button>
            </div>
            
            <div class="p-6">
                <div class="mb-6 bg-surface-container-lowest p-4 rounded-2xl border border-outline-variant">
                    <p class="text-sm font-medium text-gray-400 mb-1">Tagihan <span class="font-bold text-white" id="modalInvoiceText"></span></p>
                    <p class="text-3xl font-heading font-black text-white" id="modalAmountText"></p>
                </div>

                <form method="POST" action="{{ route('guest.payment.checkout') }}">
                    @csrf
                    <input type="hidden" name="invoice_number" id="modalInvoiceInput">
                    
                    @if(!empty($activeGateways))
                        <div class="space-y-3 mb-8 max-h-[40vh] overflow-y-auto pr-2 custom-scrollbar">
                            @foreach($activeGateways as $key => $g)
                            <label class="flex items-center justify-between p-4 border border-outline-variant rounded-xl cursor-pointer hover:bg-surface-container-highest transition-all group has-[:checked]:border-primary has-[:checked]:bg-primary/10 has-[:checked]:ring-1 has-[:checked]:ring-primary">
                                <div class="flex items-center gap-3">
                                    <input type="radio" name="gateway" value="{{ $key }}" class="text-primary focus:ring-primary border-outline-variant bg-surface-container-highest w-5 h-5 dark:bg-slate-900 dark:text-slate-100" {{ $loop->first ? 'checked' : '' }}>
                                    <span class="font-bold text-white group-has-[:checked]:text-primary">{{ strtoupper($key) }}</span>
                                </div>
                                <span class="material-symbols-outlined text-primary hidden group-has-[:checked]:block">check_circle</span>
                            </label>
                            @endforeach
                        </div>
                        <button type="submit" class="w-full py-4 bg-gradient-to-r from-primary-container to-secondary-container hover:from-primary hover:to-secondary-container text-white font-heading font-bold rounded-xl shadow-lg shadow-primary-container/30 transition-all flex items-center justify-center gap-2 text-base">
                            Lanjutkan Pembayaran <span class="material-symbols-outlined">arrow_forward</span>
                        </button>
                    @else
                        <div class="p-4 bg-error/10 border border-error/20 rounded-xl mb-6 flex items-start gap-3">
                            <span class="material-symbols-outlined text-error">warning</span>
                            <p class="text-error text-sm mt-0.5 font-medium">Payment Gateway belum dikonfigurasi secara penuh. Silakan hubungi admin.</p>
                        </div>
                        <button type="button" onclick="handlePayManual()" class="w-full py-4 bg-surface-bright hover:bg-inverse-surface hover:text-surface text-white font-heading font-bold rounded-xl transition-colors shadow-lg">
                            Konfirmasi via WhatsApp
                        </button>
                    @endif
                </form>
            </div>
        </div>
    </div>
    
    <script>
        function openPaymentModal(invoiceNumber, amount) {
            document.getElementById('modalInvoiceText').innerText = invoiceNumber;
            document.getElementById('modalInvoiceInput').value = invoiceNumber;
            document.getElementById('modalAmountText').innerText = 'Rp ' + new Intl.NumberFormat('id-ID').format(amount);
            document.getElementById('paymentModal').style.display = 'flex';
        }

        function closePaymentModal() {
            document.getElementById('paymentModal').style.display = 'none';
        }

        @if(empty($activeGateways))
        function handlePayManual() {
            const invoice = document.getElementById('modalInvoiceInput').value;
            const phone = "{{ preg_replace('/[^0-9]/', '', \App\Models\Setting::getValue('company.phone', '081234567890')) }}";
            let formattedPhone = phone;
            if (formattedPhone.startsWith('0')) {
                formattedPhone = '62' + formattedPhone.substring(1);
            }
            const text = `Halo Admin, saya ingin konfirmasi pembayaran Tagihan ${invoice}. Mohon info rekening.`;
            window.open(`https://wa.me/${formattedPhone}?text=${encodeURIComponent(text)}`, '_blank');
        }
        @endif
    </script>
</body>
</html>
