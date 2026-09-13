<div class="space-y-6">
    <x-admin.page-header title="Telegram Agents" subtitle="Manajemen Agen dan penerima notifikasi Telegram">
        <x-slot name="actions">
            <x-base.button href="{{ route('pengaturan.telegram') }}" variant="secondary">
                <x-icon name="arrow-left" class="w-4 h-4 mr-2" />
                Kembali
            </x-base.button>
        </x-slot>
    </x-admin.page-header>

    @if (session()->has('success'))
        <div class="p-4 bg-emerald-50 dark:bg-emerald-900/30 text-emerald-700 rounded-lg border border-emerald-200">
            {{ session('success') }}
        </div>
    @endif
    @if (session()->has('error'))
        <div class="p-4 bg-red-50 dark:bg-red-900/30 text-red-700 rounded-lg border border-red-200">
            {{ session('error') }}
        </div>
    @endif

    <div class="grid grid-cols-1 xl:grid-cols-4 gap-6">
        <div class="xl:col-span-3">
            <x-base.card>
                <div class="p-4 border-b border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800/50/50 flex flex-col md:flex-row md:items-center justify-between gap-4">
                    <div class="relative">
                        <svg class="w-4 h-4 text-slate-400 absolute left-3 top-1/2 -translate-y-1/2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                        <input type="text" wire:model.live.debounce.300ms="search" placeholder="Cari agen atau chat ID..." class="pl-9 pr-4 py-2 text-sm border border-slate-300 dark:border-slate-600 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-blue-500 bg-white dark:bg-slate-800 w-64 dark:bg-slate-900 dark:text-slate-100">
                    </div>
                    <div>
                        <button wire:click="create" class="px-4 py-2 bg-primary-600 text-white text-sm font-medium rounded-lg hover:bg-primary-700 transition-colors flex items-center gap-2 shadow-sm">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                            Tambah Agen
                        </button>
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-sm text-left">
                        <thead class="bg-slate-50 dark:bg-slate-800/50 border-b border-slate-200 dark:border-slate-700">
                            <tr>
                                <th class="p-3">Nama Agen</th>
                                <th class="p-3">Departemen</th>
                                <th class="p-3">Chat ID</th>
                                <th class="p-3">Token Bot</th>
                                <th class="p-3">Notifikasi (Events)</th>
                                <th class="p-3 text-center">Status</th>
                                <th class="p-3 text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                            @forelse($filteredAgents as $index => $agent)
                                <tr class="hover:bg-slate-50 dark:bg-slate-800/50 transition-colors">
                                    <td class="p-3 font-medium text-slate-900 dark:text-slate-100">{{ $agent['name'] }}</td>
                                    <td class="p-3 text-slate-600 dark:text-slate-400">{{ $agent['department'] }}</td>
                                    <td class="p-3 text-slate-600 dark:text-slate-400 font-mono">{{ $agent['chat_id'] }}</td>
                                    <td class="p-3 text-slate-500 dark:text-slate-400">
                                        @if($agent['token_type'] === 'default')
                                            <x-ui.badge variant="neutral">Default System Bot</x-ui.badge>
                                        @else
                                            <span class="px-2 py-0.5 bg-purple-100 text-purple-700 text-xs rounded">Custom Bot Token</span>
                                        @endif
                                    </td>
                                    <td class="p-3 text-slate-600 dark:text-slate-400">{{ $agent['events'] }}</td>
                                    <td class="p-3 text-center">
                                        @if($agent['status'] === 'active')
                                            <x-ui.badge variant="success">Aktif</x-ui.badge>
                                        @else
                                            <x-ui.badge variant="neutral">Nonaktif</x-ui.badge>
                                        @endif
                                    </td>
                                    <td class="p-3">
                                        <div class="flex items-center justify-center gap-2">
                                            <button wire:click="testChat({{ $index }})" class="p-1.5 text-primary-600 hover:bg-blue-50 dark:bg-blue-900/30 rounded" title="Test Chat">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>
                                            </button>
                                            <button wire:click="edit({{ $index }})" class="p-1.5 text-orange-500 hover:bg-orange-50 rounded" title="Edit Agen">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="p-8 text-center text-slate-500 dark:text-slate-400">Belum ada data Agen Telegram.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table></div>

        </x-base.card>
        </div>
        
        <div class="xl:col-span-1 space-y-4">
            <x-base.card>
                <div class="p-4 border-b border-slate-200 dark:border-slate-700">
                    <h3 class="font-semibold text-slate-900 dark:text-slate-100">Panduan Chat ID</h3>
                </div>
                <div class="p-4 text-sm text-slate-600 dark:text-slate-400 space-y-3">
                    <p><strong>Cara mendapatkan Chat ID:</strong></p>
                    <ul class="list-disc pl-4 space-y-1">
                        <li>Kirim pesan sembarang ke Bot Telegram Anda.</li>
                        <li>Buka URL: <code class="bg-slate-100 dark:bg-slate-700 text-pink-600 px-1 rounded">https://api.telegram.org/bot&lt;TOKEN&gt;/getUpdates</code></li>
                        <li>Cari objek <code class="bg-slate-100 dark:bg-slate-700 text-pink-600 px-1 rounded">"chat":{"id":123456...}</code></li>
                    </ul>
                    <p class="pt-2 border-t border-slate-100 dark:border-slate-700"><strong>Pencarian Otomatis:</strong><br>Agen dapat mengirim pesan <code>/myid</code> ke Bot, dan ID akan muncul secara otomatis di Log Telegram.</p>
                </div>
            </x-base.card>
        </div>
    </div>

    {{-- Modal Form --}}
    @if($showModal)
        <div class="fixed inset-0 bg-slate-900/50 backdrop-blur-sm z-50 flex items-center justify-center p-4">
            <div class="bg-white dark:bg-slate-800 rounded-xl shadow-xl w-full max-w-lg overflow-hidden">
                <div class="px-6 py-4 border-b border-slate-200 dark:border-slate-700 flex items-center justify-between">
                    <h3 class="text-lg font-bold text-slate-900 dark:text-slate-100">{{ $form['id'] ? 'Edit Agen' : 'Tambah Agen Baru' }}</h3>
                    <button wire:click="$set('showModal', false)" class="text-slate-400 hover:text-slate-500 dark:text-slate-400">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>
                <div class="p-6 space-y-4">
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Nama Agen *</label>
                            <input type="text" wire:model="form.name" class="w-full px-3 py-2 border border-slate-300 dark:border-slate-600 rounded-lg focus:ring-2 focus:ring-primary-500 text-sm dark:bg-slate-900 dark:text-slate-100">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Departemen</label>
                            <input type="text" wire:model="form.department" placeholder="Contoh: NOC, Billing" class="w-full px-3 py-2 border border-slate-300 dark:border-slate-600 rounded-lg focus:ring-2 focus:ring-primary-500 text-sm dark:bg-slate-900 dark:text-slate-100">
                        </div>
                        <div class="col-span-2">
                            <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Chat ID Telegram *</label>
                            <input type="text" wire:model="form.chat_id" placeholder="Contoh: 123456789 atau -10098765432" class="w-full px-3 py-2 font-mono border border-slate-300 dark:border-slate-600 rounded-lg focus:ring-2 focus:ring-primary-500 text-sm dark:bg-slate-900 dark:text-slate-100">
                        </div>
                        <div class="col-span-2">
                            <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Pilihan Bot Token</label>
                            <div class="flex items-center gap-4 mt-1">
                                <label class="inline-flex items-center">
                                    <input type="radio" wire:model.live="form.token_type" value="default" class="text-primary-600 focus:ring-primary-500 dark:bg-slate-900 dark:text-slate-100">
                                    <span class="ml-2 text-sm text-slate-700 dark:text-slate-300">Gunakan Default Bot Sistem</span>
                                </label>
                                <label class="inline-flex items-center">
                                    <input type="radio" wire:model.live="form.token_type" value="custom" class="text-primary-600 focus:ring-primary-500 dark:bg-slate-900 dark:text-slate-100">
                                    <span class="ml-2 text-sm text-slate-700 dark:text-slate-300">Token Sendiri (Custom Bot)</span>
                                </label>
                            </div>
                        </div>






