<div>
    @if (!$voucherOrder)
        <!-- Order Not Found -->
        <div class="text-center py-6">
            <div class="inline-flex items-center justify-center w-14 h-14 rounded-2xl bg-rose-50 dark:bg-rose-950/40 text-rose-500 mb-3 border border-rose-200 dark:border-rose-800">
                <span class="material-symbols-outlined text-[30px]">receipt_long</span>
            </div>
            <h2 class="text-xl font-black text-slate-800 dark:text-slate-100">Pesanan Tidak Ditemukan</h2>
            <p class="text-xs text-slate-500 dark:text-slate-400 mt-1 mb-5">
                Nomor pesanan voucher tidak valid atau telah kedaluwarsa.
            </p>
            <a href="{{ route('home') }}" class="inline-flex items-center justify-center gap-2 px-5 py-2.5 rounded-xl bg-slate-900 dark:bg-slate-100 text-white dark:text-slate-900 text-xs font-bold hover:opacity-90 transition-all">
                <span class="material-symbols-outlined text-[18px]">home</span>
                Kembali ke Beranda
            </a>
        </div>
    @else
        @php
            $isCompleted = ($voucherOrder->status === \App\Models\VoucherOrder::STATUS_COMPLETED) || !empty($voucherOrder->voucher_username);
            $isPaid = in_array($voucherOrder->status, [\App\Models\VoucherOrder::STATUS_PAID, \App\Models\VoucherOrder::STATUS_VOUCHER_GENERATING]);
            $isFailed = in_array($voucherOrder->status, [\App\Models\VoucherOrder::STATUS_FAILED, \App\Models\VoucherOrder::STATUS_CANCELLED, \App\Models\VoucherOrder::STATUS_EXPIRED]);
        @endphp

        @if ($isCompleted)
            <!-- Sukses & Voucher Siap -->
            <div class="text-center mb-6">
                <div class="inline-flex items-center justify-center w-14 h-14 rounded-2xl bg-emerald-500 text-white shadow-lg shadow-emerald-500/25 mb-3">
                    <span class="material-symbols-outlined text-[30px]">check_circle</span>
                </div>
                <h2 class="text-2xl font-black text-slate-800 dark:text-slate-100 tracking-tight">
                    Pembayaran Berhasil!
                </h2>
                <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">
                    Voucher internet Anda telah aktif dan siap digunakan.
                </p>
            </div>

            <!-- Kartu Voucher -->
            <div class="rounded-2xl border-2 border-dashed border-emerald-300 dark:border-emerald-700/60 bg-gradient-to-br from-emerald-50/60 via-slate-50 to-emerald-50/30 dark:from-slate-800/80 dark:to-slate-900/80 p-5 mb-5 shadow-sm relative overflow-hidden">
                <div class="flex items-center justify-between pb-3 border-b border-emerald-100 dark:border-emerald-900/50 mb-4">
                    <div>
                        <span class="text-[10px] font-bold uppercase tracking-wider text-emerald-700 dark:text-emerald-400">Paket Internet</span>
                        <h3 class="text-base font-extrabold text-slate-900 dark:text-white">{{ $voucherOrder->service_profile_name }}</h3>
                    </div>
                    <span class="px-2.5 py-1 rounded-full text-[11px] font-bold bg-emerald-100 dark:bg-emerald-900/50 text-emerald-800 dark:text-emerald-300">
                        AKTIF
                    </span>
                </div>

                <div class="space-y-3">
                    <!-- Username -->
                    <div class="bg-white dark:bg-slate-900/90 rounded-xl p-3 border border-slate-200/80 dark:border-slate-700 flex items-center justify-between">
                        <div>
                            <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Username / Kode Voucher</p>
                            <p class="text-lg font-black text-slate-900 dark:text-white tracking-widest font-mono select-all">{{ $voucherOrder->voucher_username }}</p>
                        </div>
                        <button type="button" 
                            onclick="navigator.clipboard.writeText('{{ $voucherOrder->voucher_username }}'); alert('Kode Voucher disalin!');"
                            class="p-2 rounded-lg bg-slate-100 dark:bg-slate-800 hover:bg-emerald-50 hover:text-emerald-600 dark:hover:bg-slate-700 text-slate-500 transition-colors"
                            title="Salin Kode Voucher">
                            <span class="material-symbols-outlined text-[20px]">content_copy</span>
                        </button>
                    </div>

                    @if(!empty($voucherOrder->voucher_password))
                    <!-- Password -->
                    <div class="bg-white dark:bg-slate-900/90 rounded-xl p-3 border border-slate-200/80 dark:border-slate-700 flex items-center justify-between">
                        <div>
                            <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Password / Sandi</p>
                            <p class="text-lg font-black text-slate-900 dark:text-white tracking-widest font-mono select-all">{{ $voucherOrder->voucher_password }}</p>
                        </div>
                        <button type="button" 
                            onclick="navigator.clipboard.writeText('{{ $voucherOrder->voucher_password }}'); alert('Password disalin!');"
                            class="p-2 rounded-lg bg-slate-100 dark:bg-slate-800 hover:bg-emerald-50 hover:text-emerald-600 dark:hover:bg-slate-700 text-slate-500 transition-colors"
                            title="Salin Password">
                            <span class="material-symbols-outlined text-[20px]">content_copy</span>
                        </button>
                    </div>
                    @endif
                </div>

                <div class="mt-4 pt-3 border-t border-slate-200/60 dark:border-slate-700 text-[11px] text-slate-500 dark:text-slate-400 flex items-center justify-between">
                    <span>No. WhatsApp: {{ $voucherOrder->wa_number }}</span>
                    <span>Rp {{ number_format($voucherOrder->total_amount, 0, ',', '.') }}</span>
                </div>
            </div>

            <!-- Petunjuk Login -->
            <div class="bg-slate-50 dark:bg-slate-800/40 rounded-xl p-4 border border-slate-200/80 dark:border-slate-700/80 mb-5">
                <h4 class="text-xs font-bold text-slate-800 dark:text-slate-200 mb-2 flex items-center gap-1.5">
                    <span class="material-symbols-outlined text-[16px] text-blue-500">info</span>
                    Cara Menggunakan Voucher
                </h4>
                <ol class="text-xs text-slate-600 dark:text-slate-400 space-y-1.5 list-decimal list-inside leading-relaxed">
                    <li>Sambungkan perangkat Anda ke jaringan WiFi Hotspot.</li>
                    <li>Buka browser dan buka halaman login WiFi yang muncul.</li>
                    <li>Masukkan Username dan Password di atas lalu klik Login.</li>
                </ol>
            </div>

            <div class="flex flex-col gap-2.5">
                <a href="{{ route('home') }}" class="w-full py-3 px-4 rounded-xl text-center text-xs font-bold bg-slate-900 hover:bg-slate-800 dark:bg-slate-100 dark:hover:bg-white text-white dark:text-slate-900 transition-all flex items-center justify-center gap-2">
                    <span class="material-symbols-outlined text-[18px]">home</span>
                    Kembali ke Beranda
                </a>
            </div>

        @elseif ($isFailed)
            <!-- Pembayaran / Pemesanan Gagal -->
            <div class="text-center py-5">
                <div class="inline-flex items-center justify-center w-14 h-14 rounded-2xl bg-rose-50 dark:bg-rose-950/40 text-rose-500 mb-3 border border-rose-200 dark:border-rose-800">
                    <span class="material-symbols-outlined text-[30px]">cancel</span>
                </div>
                <h2 class="text-xl font-black text-slate-800 dark:text-slate-100">Pembayaran Tidak Berhasil</h2>
                <p class="text-xs text-slate-500 dark:text-slate-400 mt-1 mb-5">
                    {{ $voucherOrder->failure_reason ?? 'Pesanan telah dibatalkan atau waktu pembayaran telah kedaluwarsa.' }}
                </p>
                <a href="{{ route('buy-voucher.page', ['paket' => $voucherOrder->service_profile_id, 'phone' => $voucherOrder->wa_number]) }}" class="inline-flex items-center justify-center gap-2 px-5 py-2.5 rounded-xl bg-blue-600 text-white text-xs font-bold hover:bg-blue-700 transition-all">
                    <span class="material-symbols-outlined text-[18px]">replay</span>
                    Coba Beli Lagi
                </a>
            </div>

        @else
            <!-- Menunggu Konfirmasi / Sedang Diproses (Auto Poll setiap 3 detik) -->
            <div wire:poll.3000ms="checkStatus" class="text-center py-6">
                <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-blue-50 dark:bg-blue-950/50 text-blue-500 mb-4 border border-blue-200 dark:border-blue-800 relative">
                    <svg class="animate-spin w-8 h-8 text-blue-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                </div>
                <h2 class="text-xl font-black text-slate-800 dark:text-slate-100 tracking-tight">
                    Menunggu Konfirmasi Pembayaran...
                </h2>
                <p class="text-xs text-slate-500 dark:text-slate-400 mt-1 max-w-sm mx-auto">
                    Halaman ini akan otomatis diperbarui begitu pembayaran Anda selesai diverifikasi oleh sistem.
                </p>

                <div class="mt-6 p-4 rounded-xl bg-slate-50 dark:bg-slate-800/40 border border-slate-200/80 dark:border-slate-700/80 text-left text-xs space-y-2">
                    <div class="flex justify-between">
                        <span class="text-slate-400">Paket:</span>
                        <span class="font-bold text-slate-700 dark:text-slate-200">{{ $voucherOrder->service_profile_name }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-slate-400">Total:</span>
                        <span class="font-bold text-emerald-600">Rp {{ number_format($voucherOrder->total_amount, 0, ',', '.') }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-slate-400">WhatsApp:</span>
                        <span class="font-mono text-slate-700 dark:text-slate-200">{{ $voucherOrder->wa_number }}</span>
                    </div>
                </div>

                <div class="mt-6">
                    <button wire:click="checkStatus" type="button" class="inline-flex items-center gap-1.5 text-xs text-blue-600 dark:text-blue-400 font-bold hover:underline cursor-pointer">
                        <span class="material-symbols-outlined text-[16px]">refresh</span>
                        Periksa Status Sekarang
                    </button>
                </div>
            </div>
        @endif
    @endif
</div>
