<div class="h-full flex flex-col noc-bg w-full" style="background-color: #0a0e1a !important; min-height: 100vh;">
    {{-- TOP BAR --}}
    <div class="flex-none px-4 py-3 border-b noc-border flex items-center justify-between gap-3 noc-panel-bg shadow-sm">
        <div class="flex items-center gap-3">
            <span class="material-symbols-outlined notranslate text-indigo-400" translate="no" style="font-size:24px">settings</span>
            <h1 class="text-lg font-bold noc-text">Pengaturan Sistem NOC</h1>
        </div>
    </div>

    {{-- FLASH MESSAGES --}}
    <div class="px-6 mt-4">
        @if(session()->has('success'))
            <div class="p-3 mb-4 text-sm rounded bg-emerald-900/30 text-emerald-400 border border-emerald-800" role="alert">
                <span class="font-medium">Berhasil!</span> {{ session('success') }}
            </div>
        @endif
        @if(session()->has('error'))
            <div class="p-3 mb-4 text-sm rounded bg-rose-900/30 text-rose-400 border border-rose-800" role="alert">
                <span class="font-medium">Gagal!</span> {{ session('error') }}
            </div>
        @endif
    </div>

    {{-- MAIN CONTENT --}}
    <div class="flex-1 overflow-auto noc-scroll p-6">
        <div class="max-w-4xl space-y-6">
            
            {{-- ZABBIX SETTINGS CARD --}}
            <div class="noc-panel-bg border noc-border rounded-lg shadow overflow-hidden">
                <div class="px-5 py-4 border-b noc-border bg-black/20 flex items-center gap-2">
                    <span class="material-symbols-outlined notranslate text-red-500" translate="no">monitor_heart</span>
                    <h2 class="text-base font-semibold noc-text">Integrasi Zabbix API</h2>
                </div>
                
                <div class="p-5 space-y-5">
                    <p class="text-sm text-gray-400">
                        Hubungkan portal NOC ini dengan server Zabbix Anda untuk menampilkan grafik bandwidth dan metrik uptime secara real-time.
                    </p>

                    <form wire:submit="save" class="space-y-4">
                        <div class="grid grid-cols-1 md:grid-cols-12 gap-4">
                            <div class="md:col-span-3">
                                <label class="block text-sm font-medium text-gray-300 mb-1">URL API Zabbix</label>
                            </div>
                            <div class="md:col-span-9">
                                <input type="url" wire:model="zabbixUrl" class="w-full px-3 py-2 bg-[#0d1326] border noc-border text-gray-200 rounded focus:outline-none focus:ring-1 focus:ring-cyan-500" placeholder="http://ip-zabbix/zabbix/api_jsonrpc.php" required>
                                <p class="mt-1 text-xs text-gray-500">Contoh: http://192.168.100.2/zabbix/api_jsonrpc.php</p>
                                @error('zabbixUrl') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-12 gap-4">
                            <div class="md:col-span-3">
                                <label class="block text-sm font-medium text-gray-300 mb-1">Username</label>
                            </div>
                            <div class="md:col-span-9">
                                <input type="text" wire:model="zabbixUsername" class="w-full px-3 py-2 bg-[#0d1326] border noc-border text-gray-200 rounded focus:outline-none focus:ring-1 focus:ring-cyan-500" placeholder="Admin" required>
                                @error('zabbixUsername') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-12 gap-4">
                            <div class="md:col-span-3">
                                <label class="block text-sm font-medium text-gray-300 mb-1">Password</label>
                            </div>
                            <div class="md:col-span-9">
                                <input type="password" wire:model="zabbixPassword" class="w-full px-3 py-2 bg-[#0d1326] border noc-border text-gray-200 rounded focus:outline-none focus:ring-1 focus:ring-cyan-500" placeholder="zabbix" required>
                                @error('zabbixPassword') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                            </div>
                        </div>

                        <div class="pt-4 flex items-center justify-end gap-3 border-t noc-border">
                            <button type="button" wire:click="testConnection" wire:loading.attr="disabled" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded text-sm transition-colors flex items-center gap-2">
                                <span wire:loading.remove wire:target="testConnection" class="material-symbols-outlined notranslate" translate="no" style="font-size:18px">api</span>
                                <span wire:loading wire:target="testConnection" class="material-symbols-outlined notranslate animate-spin" translate="no" style="font-size:18px">autorenew</span>
                                Test Koneksi
                            </button>
                            <button type="submit" wire:loading.attr="disabled" wire:target="save" class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded text-sm transition-colors flex items-center gap-2">
                                <span wire:loading.remove wire:target="save" class="material-symbols-outlined notranslate" translate="no" style="font-size:18px">save</span>
                                <span wire:loading wire:target="save" class="material-symbols-outlined notranslate animate-spin" translate="no" style="font-size:18px">autorenew</span>
                                Simpan Pengaturan
                            </button>
                        </div>
                    </form>
                </div>
            </div>

        </div>
    </div>
</div>