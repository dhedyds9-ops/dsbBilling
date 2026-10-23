@section('page_title')
    <div class="flex items-center gap-3">
        <div class="w-10 h-10 rounded-xl bg-amber-500/10 flex items-center justify-center text-amber-600 dark:text-amber-400">
            <span class="material-symbols-outlined notranslate" translate="no">cookie</span>
        </div>
        <div>
            <h1 class="text-xl font-bold text-slate-900 dark:text-slate-100 leading-tight">Cookies</h1>
            <p class="text-sm text-slate-500 dark:text-slate-400">Daftar active MAC-Cookies dari Router MikroTik</p>
        </div>
    </div>
@endsection

<div class="space-y-4">
    {{-- TOOLBAR & FILTER --}}
    <div class="bg-white dark:bg-slate-800 p-4 rounded-xl shadow-sm border border-slate-200 dark:border-slate-700 flex flex-col sm:flex-row items-stretch sm:items-center justify-between gap-3">
        <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-2.5 flex-1">
            {{-- Search Bar --}}
            <div class="relative w-full sm:max-w-md">
                <span class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 pointer-events-none">
                    <span class="material-symbols-outlined notranslate text-lg" translate="no">search</span>
                </span>
                <input type="text"
                       wire:model.live.debounce.300ms="search"
                       placeholder="Cari user, MAC address, domain..."
                       class="w-full pl-9 pr-9 py-2 bg-slate-50 dark:bg-slate-900/50 border border-slate-200 dark:border-slate-700 rounded-lg text-sm text-slate-700 dark:text-slate-200 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent transition-all">
                @if($search)
                    <button wire:click="resetSearch" type="button" class="absolute right-2.5 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 p-0.5 rounded transition-colors" title="Hapus pencarian">
                        <span class="material-symbols-outlined notranslate text-base block" translate="no">close</span>
                    </button>
                @endif
            </div>

            {{-- Router Select --}}
            <div class="w-full sm:w-auto">
                <select wire:model.live="selectedRouter" class="w-full sm:w-auto rounded-lg border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-900/50 text-slate-700 dark:text-slate-200 text-sm focus:ring-primary-500 focus:border-primary-500 py-2 cursor-pointer">
                    <option value="">Pilih Router...</option>
                    @foreach($routers as $r)
                        <option value="{{ $r->id }}">{{ $r->name }} ({{ $r->ip_address }})</option>
                    @endforeach
                </select>
            </div>
        </div>

        <div class="flex items-center justify-between sm:justify-end gap-2 shrink-0">
            <div class="text-xs text-slate-500 dark:text-slate-400">
                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-amber-50 text-amber-700 dark:bg-amber-900/30 dark:text-amber-300 border border-amber-200 dark:border-amber-800/50">
                    {{ count($filteredCookies) }} @if($search) / {{ count($cookies) }} @endif Active
                </span>
            </div>

            <button wire:click="loadCookies" wire:loading.attr="disabled" class="inline-flex items-center justify-center p-2 sm:px-3.5 sm:py-2 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 rounded-lg shadow-sm text-sm font-medium transition-colors disabled:opacity-50 cursor-pointer" title="Refresh Data">
                <span class="material-symbols-outlined notranslate text-lg sm:mr-1.5" wire:loading.class="animate-spin" translate="no">refresh</span>
                <span class="hidden sm:inline">Refresh</span>
            </button>
        </div>
    </div>

    {{-- TABLE --}}
    <div class="bg-white dark:bg-slate-800 rounded-xl shadow-sm border border-slate-200 dark:border-slate-700 overflow-hidden relative">
        <div wire:loading wire:target="loadCookies, selectedRouter" class="absolute inset-0 bg-white/60 dark:bg-slate-800/60 backdrop-blur-[2px] z-10 flex items-center justify-center">
            <div class="flex flex-col items-center gap-2">
                <div class="animate-spin rounded-full h-8 w-8 border-2 border-primary-600 border-t-transparent"></div>
                <span class="text-xs text-slate-600 dark:text-slate-300 font-medium">Memuat cookie dari MikroTik...</span>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-sm">
                <thead>
                    <tr class="bg-slate-50 dark:bg-slate-900/50 border-b border-slate-200 dark:border-slate-700">
                        <th class="px-4 py-3 text-xs font-semibold text-slate-600 dark:text-slate-300 uppercase tracking-wider">User</th>
                        <th class="px-4 py-3 text-xs font-semibold text-slate-600 dark:text-slate-300 uppercase tracking-wider">MAC Address</th>
                        <th class="px-4 py-3 text-xs font-semibold text-slate-600 dark:text-slate-300 uppercase tracking-wider">Domain</th>
                        <th class="px-4 py-3 text-xs font-semibold text-slate-600 dark:text-slate-300 uppercase tracking-wider">Expires In</th>
                        <th class="px-4 py-3 text-xs font-semibold text-slate-600 dark:text-slate-300 uppercase tracking-wider text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200 dark:divide-slate-700">
                    @forelse($filteredCookies as $cookie)
                        <tr class="hover:bg-slate-50 dark:hover:bg-slate-700/50 transition-colors">
                            <td class="px-4 py-3 font-medium text-slate-900 dark:text-white">
                                <div class="flex items-center gap-2">
                                    <span class="material-symbols-outlined notranslate text-slate-400 text-base" translate="no">person</span>
                                    <span>{{ $cookie["user"] ?? "-" }}</span>
                                </div>
                            </td>
                            <td class="px-4 py-3 font-mono text-xs text-slate-600 dark:text-slate-300">
                                {{ $cookie["mac-address"] ?? "-" }}
                            </td>
                            <td class="px-4 py-3 text-slate-600 dark:text-slate-300">
                                {{ $cookie["domain"] ?? "-" }}
                            </td>
                            <td class="px-4 py-3 text-slate-600 dark:text-slate-300">
                                <span class="inline-flex items-center gap-1 text-xs px-2 py-0.5 rounded bg-slate-100 dark:bg-slate-700 text-slate-700 dark:text-slate-300">
                                    <span class="material-symbols-outlined notranslate text-xs" translate="no">timer</span>
                                    {{ $cookie["expires-in"] ?? "-" }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-right">
                                <button wire:click="removeCookie('{{ $cookie["mac-address"] ?? "" }}')" wire:confirm="Yakin ingin menghapus MAC-Cookie ini? Pelanggan mungkin harus login ulang." class="inline-flex items-center justify-center p-1.5 rounded-lg text-red-600 hover:text-red-700 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-900/30 transition-colors cursor-pointer" title="Hapus / Kick">
                                    <span class="material-symbols-outlined notranslate text-lg" translate="no">delete</span>
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-4 py-12 text-center text-slate-500 dark:text-slate-400">
                                <div class="flex flex-col items-center justify-center">
                                    <span class="material-symbols-outlined notranslate text-4xl mb-2 opacity-50 text-amber-500" translate="no">cookie</span>
                                    @if($search)
                                        <p class="font-medium text-slate-700 dark:text-slate-300">Tidak ada cookie yang cocok dengan kata kunci "{{ $search }}"</p>
                                        <button wire:click="resetSearch" class="mt-2 text-xs text-primary-600 hover:text-primary-700 underline font-medium cursor-pointer">Reset Pencarian</button>
                                    @else
                                        <p class="font-medium text-slate-700 dark:text-slate-300">Tidak ada cookie aktif yang ditemukan di router ini.</p>
                                        <p class="text-xs text-slate-400 mt-1">Pastikan router terhubung dan fitur MAC-Cookie di hotspot aktif.</p>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
