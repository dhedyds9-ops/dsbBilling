<div class="p-4 sm:p-4 min-h-[calc(100vh-4rem)] relative pb-24">
    <div class="mb-5">
        <h1 class="text-xl font-bold mb-1 text-slate-800 dark:text-slate-200">Kredensial Hotspot</h1>
        <p class="text-xs text-slate-500 dark:text-slate-400">Ubah username dan password login Hotspot Anda.</p>
    </div>

    @if ($message)
    <div class="mb-5 p-4 rounded-xl flex items-start gap-3 {{ $messageType === 'success' ? 'bg-emerald-50 text-emerald-700 border border-emerald-100 dark:bg-emerald-900/30 dark:border-emerald-800 dark:text-emerald-400' : 'bg-red-50 text-red-700 border border-red-100 dark:bg-red-900/30 dark:border-red-800 dark:text-red-400' }}">
        <span class="material-symbols-outlined shrink-0">{{ $messageType === 'success' ? 'check_circle' : 'error' }}</span>
        <div class="text-sm">{{ $message }}</div>
    </div>
    @endif

    @if ($hotspotUsers->isEmpty())
        <div class="text-center text-slate-500 dark:text-slate-400 py-10">Anda tidak memiliki layanan Hotspot aktif.</div>
    @else
        <form wire:submit="saveCredentials" class="space-y-5">
            {{-- Layanan Hotspot --}}
            <div>
                <label for="hotspotUserId" class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-2">Pilih Layanan</label>
                <select id="hotspotUserId" wire:model.live="hotspotUserId" class="w-full px-4 py-3 border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-slate-100 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-orange-500 cursor-pointer" required>
                    @foreach ($hotspotUsers as $user)
                        <option value="{{ $user->id }}">
                            {{ $user->username }} ({{ $user->customerService?->serviceProfile?->name ?? 'Hotspot' }})
                        </option>
                    @endforeach
                </select>
            </div>

            {{-- Tipe Autentikasi --}}
            <div>
                <label class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-2">Tipe Autentikasi</label>
                <div class="grid grid-cols-2 gap-3">
                    <label class="relative flex flex-col p-3 border-2 rounded-xl cursor-pointer transition-all {{ $authType === 'up' ? 'border-orange-500 bg-orange-50 dark:bg-orange-900/20 dark:border-orange-600' : 'border-slate-200 dark:border-slate-700 hover:border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-800' }}">
                        <input type="radio" wire:model.live="authType" value="up" class="sr-only">
                        <span class="text-sm font-bold text-slate-800 dark:text-slate-200 mb-0.5">User & Pass</span>
                        <span class="text-[10px] text-slate-500 dark:text-slate-400">Username & Password dibedakan</span>
                    </label>
                    <label class="relative flex flex-col p-3 border-2 rounded-xl cursor-pointer transition-all {{ $authType === 'vc' ? 'border-orange-500 bg-orange-50 dark:bg-orange-900/20 dark:border-orange-600' : 'border-slate-200 dark:border-slate-700 hover:border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-800' }}">
                        <input type="radio" wire:model.live="authType" value="vc" class="sr-only">
                        <span class="text-sm font-bold text-slate-800 dark:text-slate-200 mb-0.5">Voucher Mode</span>
                        <span class="text-[10px] text-slate-500 dark:text-slate-400">Username = Password</span>
                    </label>
                </div>
            </div>

            {{-- Username --}}
            <div>
                <label for="newUsername" class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-2">Username Baru</label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                        <span class="material-symbols-outlined text-[20px] notranslate" translate="no">person</span>
                    </div>
                    <input type="text" id="newUsername" wire:model.live.debounce.300ms="newUsername" class="w-full pl-11 pr-4 py-3 border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-slate-100 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-orange-500" minlength="3">
                </div>
                @error('newUsername') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
            </div>

            {{-- Password --}}
            @if($authType === 'up')
            <div x-data="{ show: false }">
                <label for="newPassword" class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-2">Password Baru</label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                        <span class="material-symbols-outlined text-[20px] notranslate" translate="no">lock</span>
                    </div>
                    <input :type="show ? 'text' : 'password'" id="newPassword" wire:model="newPassword" class="w-full pl-11 pr-12 py-3 border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-slate-100 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-orange-500 font-mono" minlength="3">
                    <div class="absolute inset-y-0 right-0 pr-3 flex items-center">
                        <button type="button" @click="show = !show" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 focus:outline-none p-1 flex items-center justify-center">
                            <span class="material-symbols-outlined text-[20px] notranslate" translate="no" x-show="!show">visibility</span>
                            <span class="material-symbols-outlined text-[20px] notranslate" translate="no" x-show="show" x-cloak>visibility_off</span>
                        </button>
                    </div>
                </div>
                @error('newPassword') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
            </div>

            <div x-data="{ show: false }">
                <label for="confirmPassword" class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-2">Konfirmasi Password Baru</label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                        <span class="material-symbols-outlined text-[20px] notranslate" translate="no">lock</span>
                    </div>
                    <input :type="show ? 'text' : 'password'" id="confirmPassword" wire:model="confirmPassword" class="w-full pl-11 pr-12 py-3 border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-slate-100 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-orange-500 font-mono" minlength="3">
                    <div class="absolute inset-y-0 right-0 pr-3 flex items-center">
                        <button type="button" @click="show = !show" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 focus:outline-none p-1 flex items-center justify-center">
                            <span class="material-symbols-outlined text-[20px] notranslate" translate="no" x-show="!show">visibility</span>
                            <span class="material-symbols-outlined text-[20px] notranslate" translate="no" x-show="show" x-cloak>visibility_off</span>
                        </button>
                    </div>
                </div>
                @error('confirmPassword') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
            </div>
            @endif

            <button type="submit" wire:loading.attr="disabled"
                class="w-full mt-4 py-3.5 bg-orange-600 text-white font-bold rounded-xl hover:bg-orange-700 transition-colors focus:outline-none focus:ring-2 focus:ring-orange-500 focus:ring-offset-2 disabled:opacity-50 flex items-center justify-center gap-2 shadow-md shadow-orange-500/20">
                <span class="material-symbols-outlined text-[18px] notranslate" translate="no" wire:loading.remove>save</span>
                <span class="material-symbols-outlined text-[18px] animate-spin notranslate" translate="no" wire:loading>sync</span>
                <span wire:loading.remove>Simpan Kredensial</span>
                <span wire:loading>Menyimpan...</span>
            </button>
        </form>
    @endif
</div>
