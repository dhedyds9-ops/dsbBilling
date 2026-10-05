<div>
    <div class="mb-4 flex flex-col sm:flex-row justify-between sm:items-center gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Hotspot Cookies</h1>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Daftar active MAC-Cookies dari Router MikroTik</p>
        </div>
        <div class="flex items-center gap-2">
            <select wire:model.live="selectedRouter" class="rounded-lg border-gray-300 dark:border-slate-600 dark:bg-slate-800 text-sm focus:ring-primary-500 focus:border-primary-500">
                <option value="">Pilih Router...</option>
                @foreach($routers as $r)
                    <option value="{{ $r->id }}">{{ $r->name }} ({{ $r->ip_address }})</option>
                @endforeach
            </select>
            <button wire:click="loadCookies" class="px-3 py-2 bg-white dark:bg-slate-800 border border-gray-300 dark:border-slate-600 rounded-lg hover:bg-gray-50 dark:hover:bg-slate-700 transition-colors">
                <span class="material-symbols-outlined notranslate text-lg" translate="no">refresh</span>
            </button>
        </div>
    </div>

    <div class="bg-white dark:bg-slate-800 rounded-xl shadow-sm border border-gray-200 dark:border-slate-700 overflow-hidden relative">
        <div wire:loading wire:target="loadCookies" class="absolute inset-0 bg-white/50 dark:bg-slate-800/50 backdrop-blur-sm z-10 flex items-center justify-center">
            <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-primary-600"></div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-gray-50 dark:bg-slate-900/50 border-b border-gray-200 dark:border-slate-700">
                        <th class="px-4 py-3 text-xs font-semibold text-gray-600 dark:text-gray-300 uppercase tracking-wider">User</th>
                        <th class="px-4 py-3 text-xs font-semibold text-gray-600 dark:text-gray-300 uppercase tracking-wider">MAC Address</th>
                        <th class="px-4 py-3 text-xs font-semibold text-gray-600 dark:text-gray-300 uppercase tracking-wider">Domain</th>
                        <th class="px-4 py-3 text-xs font-semibold text-gray-600 dark:text-gray-300 uppercase tracking-wider">Expires In</th>
                        <th class="px-4 py-3 text-xs font-semibold text-gray-600 dark:text-gray-300 uppercase tracking-wider text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-slate-700">
                    @forelse($cookies as $cookie)
                        <tr class="hover:bg-gray-50 dark:hover:bg-slate-700/50 transition-colors">
                            <td class="px-4 py-3 text-sm text-gray-900 dark:text-white font-medium">
                                {{ $cookie["user"] ?? "-" }}
                            </td>
                            <td class="px-4 py-3 text-sm font-mono text-gray-600 dark:text-gray-400">
                                {{ $cookie["mac-address"] ?? "-" }}
                            </td>
                            <td class="px-4 py-3 text-sm text-gray-600 dark:text-gray-400">
                                {{ $cookie["domain"] ?? "-" }}
                            </td>
                            <td class="px-4 py-3 text-sm text-gray-600 dark:text-gray-400">
                                {{ $cookie["expires-in"] ?? "-" }}
                            </td>
                            <td class="px-4 py-3 text-sm text-right">
                                <button wire:click="removeCookie('{{ $cookie["mac-address"] ?? "" }}')" wire:confirm="Yakin ingin menghapus MAC-Cookie ini? Pelanggan mungkin harus login ulang." class="text-red-600 hover:text-red-700 dark:text-red-400 dark:hover:text-red-300 p-1 rounded hover:bg-red-50 dark:hover:bg-red-900/30 transition-colors" title="Hapus / Kick">
                                    <span class="material-symbols-outlined notranslate text-lg" translate="no">delete</span>
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-4 py-12 text-center text-gray-500 dark:text-gray-400">
                                <div class="flex flex-col items-center justify-center">
                                    <span class="material-symbols-outlined notranslate text-4xl mb-2 opacity-50" translate="no">cookie</span>
                                    <p>Tidak ada cookie aktif yang ditemukan di router ini.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
