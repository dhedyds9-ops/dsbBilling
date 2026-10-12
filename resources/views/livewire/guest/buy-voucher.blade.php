<div>
    @php
        $durationText = '-';
        if (!empty($serviceProfile->validity_days) && $serviceProfile->validity_days > 0) {
            $durationText = $serviceProfile->validity_days . ' Hari';
        } elseif (!empty($serviceProfile->validity_hours) && $serviceProfile->validity_hours > 0) {
            $durationText = $serviceProfile->validity_hours . ' Jam';
        } elseif (!empty($serviceProfile->duration_value)) {
            $unit = $serviceProfile->duration_unit === 'hours' ? 'Jam' : ($serviceProfile->duration_unit === 'days' ? 'Hari' : $serviceProfile->duration_unit);
            $durationText = $serviceProfile->duration_value . ' ' . $unit;
        }

        $speedText = null;
        if (!empty($serviceProfile->download_speed)) {
            $speedText = $serviceProfile->download_speed >= 1024 
                ? round($serviceProfile->download_speed / 1024) . ' Mbps' 
                : $serviceProfile->download_speed . ' Kbps';
        }
    @endphp

    <!-- Header Section -->
    <div class="text-center mb-6">
        <div class="inline-flex items-center justify-center w-12 h-12 rounded-2xl bg-gradient-to-tr from-cyan-500 to-blue-600 text-white shadow-lg shadow-cyan-500/25 mb-3">
            <span class="material-symbols-outlined text-[24px]">wifi</span>
        </div>
        <h2 class="text-2xl font-black text-slate-800 dark:text-slate-100 tracking-tight">
            Konfirmasi Pembelian
        </h2>
        <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">
            Voucher Hotspot Instan &bull; Otomatis Dikirim ke WhatsApp
        </p>
    </div>

    @if (session()->has('error'))
        <div class="rounded-xl bg-rose-50 dark:bg-rose-950/40 border border-rose-200 dark:border-rose-800 p-4 mb-5 animate-shake">
            <div class="flex items-start gap-3">
                <span class="material-symbols-outlined text-rose-500 text-[20px] shrink-0 mt-0.5">error</span>
                <div class="text-xs font-medium text-rose-700 dark:text-rose-300 leading-relaxed">
                    {{ session('error') }}
                </div>
            </div>
        </div>
    @endif

    <form wire:submit="checkout" class="space-y-5">
        
        <!-- Voucher Card Summary -->
        <div class="relative overflow-hidden rounded-2xl border border-cyan-100 dark:border-cyan-900/40 bg-gradient-to-br from-cyan-50/70 via-blue-50/40 to-slate-50 dark:from-slate-800/80 dark:via-slate-800/60 dark:to-slate-900/80 p-5 shadow-sm">
            <div class="absolute -right-6 -bottom-6 w-28 h-28 bg-cyan-400/10 dark:bg-cyan-400/5 rounded-full blur-xl pointer-events-none"></div>

            <div class="flex items-start justify-between gap-3 mb-3">
                <div>
                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold tracking-wider uppercase bg-cyan-100 dark:bg-cyan-950/60 text-cyan-800 dark:text-cyan-300 mb-1.5">
                        <span class="w-1.5 h-1.5 rounded-full bg-cyan-500 animate-pulse"></span>
                        Paket Pilihan
                    </span>
                    <h3 class="text-base font-bold text-slate-900 dark:text-slate-100 leading-snug">
                        {{ $serviceProfile->name }}
                    </h3>
                </div>
                <div class="text-right shrink-0">
                    <span class="text-xs text-slate-400 dark:text-slate-500 block">Total Bayar</span>
                    <span class="text-lg font-black text-cyan-600 dark:text-cyan-400 font-mono">
                        Rp {{ number_format($serviceProfile->base_price, 0, ',', '.') }}
                    </span>
                </div>
            </div>

            <!-- Features Grid -->
            <div class="grid grid-cols-2 gap-2 pt-3 border-t border-slate-200/60 dark:border-slate-700/60 text-xs">
                <div class="flex items-center gap-2 text-slate-600 dark:text-slate-300">
                    <span class="material-symbols-outlined text-[16px] text-cyan-600 dark:text-cyan-400">schedule</span>
                    <span>Durasi: <strong class="text-slate-800 dark:text-slate-100">{{ $durationText }}</strong></span>
                </div>
                @if($speedText)
                <div class="flex items-center gap-2 text-slate-600 dark:text-slate-300">
                    <span class="material-symbols-outlined text-[16px] text-cyan-600 dark:text-cyan-400">speed</span>
                    <span>Speed: <strong class="text-slate-800 dark:text-slate-100">{{ $speedText }}</strong></span>
                </div>
                @else
                <div class="flex items-center gap-2 text-slate-600 dark:text-slate-300">
                    <span class="material-symbols-outlined text-[16px] text-cyan-600 dark:text-cyan-400">devices</span>
                    <span>Max: <strong class="text-slate-800 dark:text-slate-100">{{ $serviceProfile->max_devices ?? 1 }} Device</strong></span>
                </div>
                @endif
            </div>

            @if(!empty($serviceProfile->description))
            <div class="mt-2 text-[11px] text-slate-500 dark:text-slate-400 italic">
                {{ $serviceProfile->description }}
            </div>
            @endif
        </div>

        <!-- WhatsApp Input Field -->
        <div>
            <div class="flex items-center justify-between mb-1.5">
                <label for="wa_number" class="text-xs font-bold text-slate-700 dark:text-slate-300 flex items-center gap-1">
                    <span>Nomor WhatsApp Aktif</span>
                    <span class="text-rose-500">*</span>
                </label>
                <span class="text-[11px] text-slate-400">Untuk kirim username & sandi</span>
            </div>
            
            <div class="relative rounded-xl shadow-sm">
                <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400 dark:text-slate-500">
                    <span class="material-symbols-outlined text-[19px]">chat</span>
                </div>
                <input wire:model="wa_number" id="wa_number" type="tel" required
                    class="block w-full pl-10 pr-4 py-3 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50/70 dark:bg-slate-900/80 text-slate-900 dark:text-slate-100 text-sm placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-cyan-500 focus:border-cyan-500 transition-all font-mono"
                    placeholder="Contoh: 081234567890">
            </div>
            @error('wa_number')
                <p class="mt-1.5 text-xs text-rose-500 font-medium">{{ $message }}</p>
            @enderror
            <p class="mt-1.5 text-[11px] text-slate-500 dark:text-slate-400 flex items-center gap-1">
                <span class="material-symbols-outlined text-[14px] text-emerald-500">verified</span>
                Format otomatis disesuaikan (08xx / 628xx).
            </p>
        </div>

        <!-- Checkout Button -->
        <div class="pt-2">
            <button type="submit" 
                class="w-full relative group overflow-hidden py-3 px-4 rounded-xl text-sm font-bold text-white bg-gradient-to-r from-cyan-600 via-blue-600 to-indigo-600 hover:from-cyan-500 hover:to-indigo-500 shadow-lg shadow-cyan-500/25 active:scale-[0.99] transition-all disabled:opacity-70 disabled:cursor-not-allowed cursor-pointer"
                wire:loading.attr="disabled">
                <div class="flex items-center justify-center gap-2">
                    <span wire:loading.remove wire:target="checkout" class="flex items-center gap-2">
                        <span>Lanjut ke Pembayaran</span>
                        <span class="material-symbols-outlined text-[18px] group-hover:translate-x-1 transition-transform">arrow_forward</span>
                    </span>
                    <span wire:loading wire:target="checkout" class="flex items-center gap-2">
                        <svg class="animate-spin h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        <span>Menyiapkan Invoice...</span>
                    </span>
                </div>
            </button>
        </div>
    </form>
    
    <!-- Trust Badges & Guarantees -->
    <div class="mt-6 pt-5 border-t border-slate-200/80 dark:border-slate-700/80">
        <div class="flex items-center justify-center gap-6 text-[11px] text-slate-500 dark:text-slate-400">
            <div class="flex items-center gap-1.5">
                <span class="material-symbols-outlined text-[16px] text-emerald-500">lock</span>
                <span>Enkripsi 256-bit</span>
            </div>
            <div class="flex items-center gap-1.5">
                <span class="material-symbols-outlined text-[16px] text-cyan-500">bolt</span>
                <span>Proses Instan</span>
            </div>
            <div class="flex items-center gap-1.5">
                <span class="material-symbols-outlined text-[16px] text-indigo-500">qr_code_2</span>
                <span>QRIS & E-Wallet</span>
            </div>
        </div>
    </div>
</div>
