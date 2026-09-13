@section('header_title', 'Profil Saya')

<div class="p-4 sm:p-6 min-h-[calc(100vh-4rem)] space-y-6 pb-20">

    @if($message)
        <div class="p-3 rounded-xl text-sm font-medium \{{ $messageType === 'success' ? 'bg-emerald-50 dark:bg-emerald-900/30 text-emerald-600 border border-emerald-100' : 'bg-red-50 dark:bg-red-900/30 text-red-600 border border-red-100' }}">
            {{ $message }}
        </div>
    @endif

    <!-- Data Profil -->
    <div class="bg-white dark:bg-slate-800 rounded-2xl p-5 shadow-sm border border-slate-100 dark:border-slate-700/60">
        <div class="flex items-center gap-3 mb-5">
            <div class="w-10 h-10 rounded-full bg-indigo-50 dark:bg-indigo-900/30 flex items-center justify-center text-indigo-500">
                <span class="material-symbols-outlined">person</span>
            </div>
            <h2 class="text-base font-bold text-slate-900 dark:text-slate-100">Informasi Pribadi</h2>
        </div>

        <form wire:submit="updateProfile" class="space-y-4">
            <div>
                <label class="block text-[11px] font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-1">Nama Lengkap</label>
                <input type="text" wire:model="name" class="w-full bg-slate-50 dark:bg-slate-900/50 border border-slate-200 dark:border-slate-700 rounded-xl px-4 py-3 text-sm focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all dark:text-white dark:bg-slate-900 dark:text-slate-100" required>
            </div>
            <div>
                <label class="block text-[11px] font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-1">Alamat Email</label>
                <input type="email" wire:model="email" class="w-full bg-slate-50 dark:bg-slate-900/50 border border-slate-200 dark:border-slate-700 rounded-xl px-4 py-3 text-sm focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all dark:text-white dark:bg-slate-900 dark:text-slate-100" required>
            </div>
            <div>
                <label class="block text-[11px] font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-1">Nomor WhatsApp</label>
                <input type="tel" wire:model="phone" class="w-full bg-slate-50 dark:bg-slate-900/50 border border-slate-200 dark:border-slate-700 rounded-xl px-4 py-3 text-sm focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all dark:text-white dark:bg-slate-900 dark:text-slate-100">
            </div>
            <div class="pt-2">
                <button type="submit" class="w-full bg-indigo-600 hover:bg-indigo-700 active:bg-indigo-800 text-white font-semibold py-3.5 rounded-xl text-sm transition-all shadow-md shadow-indigo-500/20">
                    Simpan Perubahan
                </button>
            </div>
        </form>
    </div>

    <!-- Keamanan -->
    <div class="bg-white dark:bg-slate-800 rounded-2xl p-5 shadow-sm border border-slate-100 dark:border-slate-700/60">
        <div class="flex items-center gap-3 mb-5">
            <div class="w-10 h-10 rounded-full bg-red-50 dark:bg-red-900/30 flex items-center justify-center text-red-500">
                <span class="material-symbols-outlined">lock</span>
            </div>
            <h2 class="text-base font-bold text-slate-900 dark:text-slate-100">Keamanan Akun</h2>
        </div>

        <form wire:submit="changePassword" class="space-y-4">
            <div>
                <label class="block text-[11px] font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-1">Password Saat Ini</label>
                <input type="password" wire:model="currentPassword" class="w-full bg-slate-50 dark:bg-slate-900/50 border border-slate-200 dark:border-slate-700 rounded-xl px-4 py-3 text-sm focus:ring-2 focus:ring-red-500/20 focus:border-red-500 transition-all dark:text-white dark:bg-slate-900 dark:text-slate-100" required>
            </div>
            <div>
                <label class="block text-[11px] font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-1">Password Baru</label>
                <input type="password" wire:model="newPassword" class="w-full bg-slate-50 dark:bg-slate-900/50 border border-slate-200 dark:border-slate-700 rounded-xl px-4 py-3 text-sm focus:ring-2 focus:ring-red-500/20 focus:border-red-500 transition-all dark:text-white dark:bg-slate-900 dark:text-slate-100" required>
            </div>
            <div>
                <label class="block text-[11px] font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-1">Konfirmasi Password Baru</label>
                <input type="password" wire:model="confirmPassword" class="w-full bg-slate-50 dark:bg-slate-900/50 border border-slate-200 dark:border-slate-700 rounded-xl px-4 py-3 text-sm focus:ring-2 focus:ring-red-500/20 focus:border-red-500 transition-all dark:text-white dark:bg-slate-900 dark:text-slate-100" required>
            </div>
            <div class="pt-2">
                <button type="submit" class="w-full bg-slate-800 hover:bg-slate-900 dark:bg-slate-700 dark:hover:bg-slate-600 text-white font-semibold py-3.5 rounded-xl text-sm transition-all shadow-sm">
                    Ganti Password
                </button>
            </div>
        </form>
    </div>
</div>