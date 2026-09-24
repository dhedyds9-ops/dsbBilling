@section('header_title', 'Perangkat Aktif')

<div class="p-4 sm:p-6 min-h-[calc(100vh-4rem)] pb-24">
    <div class="mb-5">
        <h1 class="text-xl font-bold text-slate-900 dark:text-slate-100">Perangkat Aktif (Online)</h1>
        <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">Daftar perangkat yang sedang terhubung ke internet saat ini.</p>
    </div>

    @if($message)
        <div class="mb-4 p-3 rounded-xl text-sm font-medium \{{ $messageType === 'success' ? 'bg-emerald-50 dark:bg-emerald-900/30 text-emerald-600 border border-emerald-100' : 'bg-red-50 dark:bg-red-900/30 text-red-600 border border-red-100' }}">
            {{ $message }}
        </div>
    @endif

    <!-- Search Input -->
    <div class="mb-4 relative">
        <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-lg">search</span>
        <input type="text" wire:model.live.debounce.300ms="search" placeholder="Cari IP, MAC, atau Username..." class="w-full pl-10 pr-4 py-3 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700/60 rounded-xl text-sm focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all dark:text-white shadow-sm dark:bg-slate-900 dark:text-slate-100">
    </div>

    <!-- Active Sessions List -->
    <div class="space-y-4">
        @forelse($sessions as $session)
            <div class="bg-white dark:bg-slate-800 rounded-2xl p-4 shadow-sm border border-slate-100 dark:border-slate-700/60 relative overflow-hidden">
                <div class="absolute left-0 top-0 bottom-0 w-1 bg-emerald-500"></div>
                
                <div class="pl-2">
                    <div class="flex justify-between items-start mb-3">
                        <div class="flex items-center gap-2">
                            <span class="shrink-0 px-2 py-0.5 rounded-full text-[10px] font-bold bg-indigo-100 dark:bg-indigo-900/50 text-indigo-700 uppercase">
                                {{ $session->type }}
                            </span>
                            <span class="font-bold text-sm text-slate-900 dark:text-slate-100">{{ $session->username }}</span>
                        </div>
                        <span class="shrink-0 px-2.5 py-1 rounded-full text-[10px] font-bold bg-emerald-100 dark:bg-emerald-900/50 text-emerald-700 flex items-center gap-1">
                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span> ONLINE
                        </span>
                    </div>

                    <div class="grid grid-cols-2 gap-y-3 gap-x-2 bg-slate-50 dark:bg-slate-900/50 rounded-xl p-3 mb-4">
                        <div>
                            <p class="text-[10px] text-slate-500 dark:text-slate-400">IP Address</p>
                            <p class="text-xs font-semibold text-slate-700 dark:text-slate-300 font-mono">{{ $session->ip_address }}</p>
                        </div>
                        <div>
                            <p class="text-[10px] text-slate-500 dark:text-slate-400">MAC Address</p>
                            <p class="text-xs font-semibold text-slate-700 dark:text-slate-300 font-mono">{{ $session->mac_address }}</p>
                        </div>
                        <div>
                            <p class="text-[10px] text-slate-500 dark:text-slate-400">Uptime</p>
                            <p class="text-xs font-semibold text-slate-700 dark:text-slate-300">{{ $session->uptime }}</p>
                        </div>
                        <div>
                            <p class="text-[10px] text-slate-500 dark:text-slate-400">Router</p>
                            <p class="text-xs font-semibold text-slate-700 dark:text-slate-300">{{ $session->router }}</p>
                        </div>
                    </div>

                    <button 
                        wire:click="kickSession('{{ $session->type }}', {{ $session->id }})"
                        wire:confirm="Anda yakin ingin memutus koneksi perangkat ini? Perangkat mungkin akan terhubung kembali secara otomatis jika kata sandi tersimpan."
                        class="w-full flex items-center justify-center gap-1.5 bg-red-50 hover:bg-red-100 text-red-600 dark:bg-red-500/10 dark:hover:bg-red-500/20 py-2.5 rounded-xl text-xs font-semibold transition-colors"
                    >
                        <span class="material-symbols-outlined text-[16px]">logout</span>
                        Putuskan (Kick)
                    </button>
                </div>
            </div>
        @empty
            <div class="text-center py-12 bg-white dark:bg-slate-800 rounded-2xl border border-slate-100 dark:border-slate-700/60 shadow-sm">
                <div class="w-16 h-16 rounded-full bg-slate-50 dark:bg-slate-700 flex items-center justify-center mx-auto mb-3">
                    <span class="material-symbols-outlined text-3xl text-slate-300 dark:text-slate-500 dark:text-slate-400">wifi_off</span>
                </div>
                <p class="text-slate-900 dark:text-slate-100 font-semibold mb-1">Tidak ada perangkat aktif</p>
                <p class="text-slate-500 dark:text-slate-400 text-xs px-6">Saat ini tidak ada koneksi online yang menggunakan layanan Anda.</p>
            </div>
        @endforelse
    </div>
</div>