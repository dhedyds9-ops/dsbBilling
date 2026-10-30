<div>
    {{-- Langkah 1: Input Identitas (No. WhatsApp / Username / Email) --}}
    @if ($step === 1)
        <div class="text-center mb-6">
            <div class="inline-flex items-center justify-center w-14 h-14 rounded-2xl bg-gradient-to-tr from-emerald-500 to-teal-600 text-white shadow-lg shadow-emerald-500/25 mb-3">
                <span class="material-symbols-outlined text-[28px]">lock_reset</span>
            </div>
            <h2 class="text-2xl font-black text-slate-800 dark:text-slate-100 tracking-tight">
                Lupa Kata Sandi?
            </h2>
            <p class="text-xs text-slate-500 dark:text-slate-400 mt-1 max-w-xs mx-auto">
                Masukkan No. WhatsApp, Username, atau Email akun Anda untuk menerima 6 digit kode OTP.
            </p>
        </div>

        @if (session()->has('error'))
            <div class="rounded-xl bg-rose-50 dark:bg-rose-950/40 border border-rose-200 dark:border-rose-800 p-4 mb-5">
                <div class="flex items-start gap-3">
                    <span class="material-symbols-outlined text-rose-500 text-[20px] shrink-0 mt-0.5">error</span>
                    <div class="text-xs font-medium text-rose-700 dark:text-rose-300 leading-relaxed">
                        {{ session('error') }}
                    </div>
                </div>
            </div>
        @endif

        <form wire:submit="sendOtp" class="space-y-4">
            <div>
                <label for="identifier" class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-2">
                    No. WhatsApp / Username / Email
                </label>
                <div class="relative group">
                    <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400 group-focus-within:text-emerald-500 transition-colors">
                        <span class="material-symbols-outlined text-[20px]">person_search</span>
                    </div>
                    <input wire:model="identifier" id="identifier" type="text" autofocus required
                        class="block w-full pl-11 pr-4 py-3 bg-slate-50 dark:bg-slate-900/60 border border-slate-200 dark:border-slate-700 rounded-xl text-sm text-slate-900 dark:text-slate-100 placeholder-slate-400 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition-all shadow-sm"
                        placeholder="Contoh: 08123456789 atau teknisi1">
                </div>
                @error('identifier')
                    <p class="mt-2 text-xs text-rose-600 dark:text-rose-400 flex items-center gap-1 font-medium">
                        <span class="material-symbols-outlined text-[16px]">error</span> {{ $message }}
                    </p>
                @enderror
            </div>

            <div class="pt-2">
                <button type="submit" wire:loading.attr="disabled"
                    class="w-full flex justify-center items-center gap-2 py-3.5 px-4 rounded-xl shadow-lg shadow-emerald-500/20 text-sm font-bold text-white bg-gradient-to-r from-emerald-500 to-teal-600 hover:from-emerald-400 hover:to-teal-500 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-emerald-500 transition-all transform hover:-translate-y-0.5 disabled:opacity-50">
                    <span wire:loading.remove wire:target="sendOtp" class="material-symbols-outlined text-[20px]">chat</span>
                    <span wire:loading wire:target="sendOtp" class="material-symbols-outlined text-[20px] animate-spin">autorenew</span>
                    <span wire:loading.remove wire:target="sendOtp">Kirim Kode OTP via WhatsApp</span>
                    <span wire:loading wire:target="sendOtp">Mengirim OTP...</span>
                </button>
            </div>
        </form>

        <div class="mt-6 text-center">
            <a href="{{ route('login') }}" class="text-xs font-semibold text-slate-500 hover:text-emerald-600 dark:text-slate-400 dark:hover:text-emerald-400 inline-flex items-center gap-1 transition-colors">
                <span class="material-symbols-outlined text-[16px]">arrow_back</span>
                Kembali ke Halaman Login
            </a>
        </div>

    {{-- Langkah 2: Input 6 Digit OTP dan Set Password Baru --}}
    @elseif ($step === 2)
        <div class="text-center mb-6">
            <div class="inline-flex items-center justify-center w-14 h-14 rounded-2xl bg-gradient-to-tr from-emerald-500 to-teal-600 text-white shadow-lg shadow-emerald-500/25 mb-3">
                <span class="material-symbols-outlined text-[28px]">mark_chat_unread</span>
            </div>
            <h2 class="text-2xl font-black text-slate-800 dark:text-slate-100 tracking-tight">
                Verifikasi OTP
            </h2>
            <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">
                Kode 6 digit telah dikirim ke WhatsApp:
            </p>
            <div class="mt-1.5 inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-200 dark:border-emerald-800/60 text-emerald-700 dark:text-emerald-300 text-xs font-bold">
                <span class="material-symbols-outlined text-[16px]">phone_iphone</span>
                <span>{{ $maskedPhone }}</span>
                <span class="text-slate-400">({{ $userName }})</span>
            </div>
        </div>

        @if (session()->has('otp_sent'))
            <div class="rounded-xl bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-200 dark:border-emerald-800 p-3.5 mb-5 flex items-start gap-2.5">
                <span class="material-symbols-outlined text-emerald-600 text-[20px] shrink-0 mt-0.5">check_circle</span>
                <div class="text-xs font-medium text-emerald-800 dark:text-emerald-300">
                    {{ session('otp_sent') }}
                </div>
            </div>
        @endif

        <form wire:submit="verifyAndResetPassword" x-data="{ showNewPass: false }" class="space-y-4">
            {{-- Input OTP --}}
            <div>
                <div class="flex items-center justify-between mb-2">
                    <label for="otpCode" class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider">
                        Kode OTP 6 Digit
                    </label>
                    <button type="button" wire:click="backToStepOne" class="text-[11px] font-semibold text-emerald-600 hover:text-emerald-500 dark:text-emerald-400 transition-colors">
                        Ganti No. WA
                    </button>
                </div>
                <div class="relative">
                    <input wire:model="otpCode" id="otpCode" type="text" maxlength="6" autofocus required
                        class="block w-full py-3.5 text-center tracking-[0.5em] font-mono text-2xl font-bold bg-slate-50 dark:bg-slate-900/60 border border-slate-200 dark:border-slate-700 rounded-xl text-slate-900 dark:text-slate-100 placeholder-slate-300 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition-all shadow-sm"
                        placeholder="••••••">
                </div>
                @error('otpCode')
                    <p class="mt-2 text-xs text-rose-600 dark:text-rose-400 flex items-center gap-1 font-medium">
                        <span class="material-symbols-outlined text-[16px]">error</span> {{ $message }}
                    </p>
                @enderror
            </div>

            {{-- Password Baru --}}
            <div>
                <label for="password" class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-2">
                    Kata Sandi Baru
                </label>
                <div class="relative group">
                    <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400 group-focus-within:text-emerald-500 transition-colors">
                        <span class="material-symbols-outlined text-[20px]">lock</span>
                    </div>
                    <input wire:model="password" id="password" x-bind:type="showNewPass ? 'text' : 'password'" required
                        class="block w-full pl-11 pr-11 py-3 bg-slate-50 dark:bg-slate-900/60 border border-slate-200 dark:border-slate-700 rounded-xl text-sm text-slate-900 dark:text-slate-100 placeholder-slate-400 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition-all shadow-sm"
                        placeholder="Minimal 8 karakter">
                    <button type="button" @click="showNewPass = !showNewPass" class="absolute inset-y-0 right-0 pr-3.5 flex items-center text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 transition-colors">
                        <span class="material-symbols-outlined text-[20px]" x-text="showNewPass ? 'visibility_off' : 'visibility'">visibility</span>
                    </button>
                </div>
                @error('password')
                    <p class="mt-2 text-xs text-rose-600 dark:text-rose-400 flex items-center gap-1 font-medium">
                        <span class="material-symbols-outlined text-[16px]">error</span> {{ $message }}
                    </p>
                @enderror
            </div>

            {{-- Konfirmasi Password Baru --}}
            <div>
                <label for="password_confirmation" class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-2">
                    Ulangi Kata Sandi Baru
                </label>
                <div class="relative group">
                    <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400 group-focus-within:text-emerald-500 transition-colors">
                        <span class="material-symbols-outlined text-[20px]">lock_clock</span>
                    </div>
                    <input wire:model="password_confirmation" id="password_confirmation" x-bind:type="showNewPass ? 'text' : 'password'" required
                        class="block w-full pl-11 pr-4 py-3 bg-slate-50 dark:bg-slate-900/60 border border-slate-200 dark:border-slate-700 rounded-xl text-sm text-slate-900 dark:text-slate-100 placeholder-slate-400 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition-all shadow-sm"
                        placeholder="Ulangi kata sandi baru">
                </div>
            </div>

            {{-- Tombol Simpan & Countdown Kirim Ulang --}}
            <div class="pt-3 space-y-3" x-data="{
                seconds: @entangle('resendCountdown'),
                timer: null,
                init() {
                    this.timer = setInterval(() => {
                        if (this.seconds > 0) {
                            this.seconds--;
                        }
                    }, 1000);
                }
            }">
                <button type="submit" wire:loading.attr="disabled"
                    class="w-full flex justify-center items-center gap-2 py-3.5 px-4 rounded-xl shadow-lg shadow-emerald-500/20 text-sm font-bold text-white bg-gradient-to-r from-emerald-500 to-teal-600 hover:from-emerald-400 hover:to-teal-500 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-emerald-500 transition-all transform hover:-translate-y-0.5 disabled:opacity-50">
                    <span wire:loading.remove wire:target="verifyAndResetPassword" class="material-symbols-outlined text-[20px]">save</span>
                    <span wire:loading wire:target="verifyAndResetPassword" class="material-symbols-outlined text-[20px] animate-spin">autorenew</span>
                    <span wire:loading.remove wire:target="verifyAndResetPassword">Simpan Kata Sandi Baru</span>
                    <span wire:loading wire:target="verifyAndResetPassword">Menyimpan...</span>
                </button>

                <div class="text-center pt-2">
                    <template x-if="seconds > 0">
                        <p class="text-xs text-slate-500 dark:text-slate-400">
                            Kirim ulang kode dalam <span class="font-bold text-emerald-600 dark:text-emerald-400" x-text="seconds + 's'"></span>
                        </p>
                    </template>
                    <template x-if="seconds <= 0">
                        <button type="button" wire:click="resendOtp" wire:loading.attr="disabled"
                            class="text-xs font-bold text-emerald-600 hover:text-emerald-500 dark:text-emerald-400 hover:underline inline-flex items-center gap-1 transition-colors">
                            <span wire:loading.remove wire:target="resendOtp" class="material-symbols-outlined text-[16px]">refresh</span>
                            <span wire:loading wire:target="resendOtp" class="material-symbols-outlined text-[16px] animate-spin">autorenew</span>
                            <span>Kirim Ulang Kode OTP</span>
                        </button>
                    </template>
                </div>
            </div>
        </form>

    {{-- Langkah 3: Berhasil / Sukses --}}
    @elseif ($step === 3)
        <div class="text-center py-4">
            <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-emerald-100 dark:bg-emerald-950/60 border border-emerald-300 dark:border-emerald-700 text-emerald-600 dark:text-emerald-400 mb-4 animate-bounce">
                <span class="material-symbols-outlined text-[36px]">verified</span>
            </div>
            <h2 class="text-2xl font-black text-slate-800 dark:text-slate-100 tracking-tight mb-2">
                Kata Sandi Berhasil Diperbarui!
            </h2>
            <p class="text-xs text-slate-500 dark:text-slate-400 max-w-xs mx-auto mb-6 leading-relaxed">
                Kata sandi baru Anda telah aktif. Anda sekarang dapat masuk kembali ke sistem dengan kata sandi yang baru.
            </p>

            <a href="{{ route('login') }}"
                class="w-full inline-flex justify-center items-center gap-2 py-3.5 px-4 rounded-xl shadow-lg shadow-emerald-500/20 text-sm font-bold text-white bg-gradient-to-r from-emerald-500 to-teal-600 hover:from-emerald-400 hover:to-teal-500 transition-all transform hover:-translate-y-0.5">
                <span>Masuk ke Akun Sekarang</span>
                <span class="material-symbols-outlined text-[20px]">login</span>
            </a>
        </div>
    @endif
</div>
