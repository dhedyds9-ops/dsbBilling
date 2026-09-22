<div>
    <button wire:click="openModal" class="inline-flex items-center justify-center px-4 py-2 bg-purple-50 dark:bg-purple-900/30 text-purple-700 dark:text-purple-300 hover:bg-purple-100 dark:bg-purple-900/50 border border-purple-200 rounded-lg text-sm font-semibold transition-colors">
        <span class="material-symbols-outlined notranslate mr-2" translate="no" style="font-size:18px">settings_ethernet</span>
        Pengaturan Lanjutan
    </button>

    @if($showWanModal)
    <div class="fixed inset-0 z-[60] flex items-center justify-center p-4 bg-slate-900/50 backdrop-blur-sm" x-data="{ init() { $wire.loadWans() } }">
        <div class="bg-white dark:bg-slate-800 rounded-xl shadow-xl w-full max-w-4xl overflow-hidden flex flex-col max-h-[90vh]">
            <div class="px-5 py-4 border-b border-slate-100 dark:border-slate-700 flex items-center justify-between shrink-0">
                <h3 class="text-lg font-bold text-slate-900 dark:text-slate-100">Manajemen Koneksi WAN & Binding</h3>
                <button wire:click="$set('showWanModal', false)" class="text-slate-400 hover:text-slate-600 dark:text-slate-400">
                    <span class="material-symbols-outlined notranslate" translate="no">close</span>
                </button>
            </div>
            
            <div class="p-5 overflow-y-auto">
                @if(session()->has('error'))
                    <div class="p-3 mb-4 text-sm text-red-700 bg-red-100 rounded-lg dark:bg-red-900/30 dark:text-red-400">
                        {{ session('error') }}
                    </div>
                @endif
                @if(session()->has('success'))
                    <div class="p-3 mb-4 text-sm text-green-700 bg-green-100 rounded-lg dark:bg-green-900/30 dark:text-green-400">
                        {{ session('success') }}
                    </div>
                @endif

                @if($isLoading)
                    <div class="flex flex-col items-center justify-center py-12">
                        <div class="animate-spin rounded-full h-10 w-10 border-b-2 border-indigo-600 mb-4"></div>
                        <p class="text-slate-500">Menyinkronkan data WAN dengan GenieACS...</p>
                    </div>
                @else
                    <div class="mb-4 flex justify-between items-center">
                        <h4 class="font-semibold text-slate-700 dark:text-slate-300">Daftar Koneksi WAN Aktif</h4>
                        <button wire:click="createWan" class="px-3 py-1.5 text-xs font-medium text-white bg-indigo-600 rounded shadow hover:bg-indigo-700">
                            + Tambah WAN Baru
                        </button>
                    </div>
                    
                    <div class="overflow-x-auto border border-slate-200 dark:border-slate-700 rounded-lg">
                        <table class="w-full text-sm text-left text-slate-500 dark:text-slate-400">
                            <thead class="text-xs text-slate-700 uppercase bg-slate-50 dark:bg-slate-700/50 dark:text-slate-300">
                                <tr>
                                    <th class="px-4 py-3">Nama Koneksi</th>
                                    <th class="px-4 py-3">Tipe</th>
                                    <th class="px-4 py-3 text-center">VLAN</th>
                                    <th class="px-4 py-3 text-center">NAT</th>
                                    <th class="px-4 py-3">Keterangan</th>
                                    <th class="px-4 py-3 text-right">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($wanConnections as $wan)
                                <tr class="border-b border-slate-100 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-800/50">
                                    <td class="px-4 py-3 font-medium text-slate-900 dark:text-slate-100">{{ $wan['name'] }}</td>
                                    <td class="px-4 py-3">
                                        <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-indigo-100 text-indigo-700 dark:bg-indigo-900/30 dark:text-indigo-400">
                                            {{ $wan['type'] }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-3 text-center font-mono text-xs">{{ $wan['vlan'] ?? '-' }}</td>
                                    <td class="px-4 py-3 text-center">
                                        @if($wan['nat'])
                                            <span class="text-emerald-500 material-symbols-outlined text-sm" translate="no">check_circle</span>
                                        @else
                                            <span class="text-slate-300 material-symbols-outlined text-sm" translate="no">cancel</span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3 text-xs">
                                        @if($wan['username'])
                                            <div><span class="font-semibold">User:</span> {{ $wan['username'] }}</div>
                                        @endif
                                        <div><span class="font-semibold">Service:</span> {{ $wan['service_list'] }}</div>
                                        @if($wan['port_bind'])
                                            <div class="truncate max-w-[200px]" title="{{ $wan['port_bind'] }}"><span class="font-semibold">Bind:</span> {{ $wan['port_bind'] }}</div>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3 text-right">
                                        <button wire:click="editWan('{{ $wan['fullPath'] }}')" class="text-indigo-600 hover:text-indigo-800 dark:text-indigo-400 text-xs font-medium">Edit Profil</button>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="6" class="px-4 py-8 text-center text-slate-500">
                                        Tidak ada koneksi WAN yang terdeteksi atau format tidak didukung (Bukan TR-098).
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    @if($isEditing)
                    <div class="mt-6 border border-slate-200 dark:border-slate-700 rounded-lg p-5 bg-slate-50 dark:bg-slate-800/50" id="wanEditForm">
                        <div class="flex items-center justify-between mb-4">
                            <h4 class="font-bold text-slate-800 dark:text-slate-100 flex items-center gap-2">
                                <span class="material-symbols-outlined notranslate text-indigo-500" translate="no" style="font-size: 20px;">edit_square</span>
                                Edit WAN: {{ $formName }}
                            </h4>
                            <span class="px-2 py-0.5 rounded text-xs font-bold bg-slate-200 text-slate-700 dark:bg-slate-700 dark:text-slate-300">
                                {{ $formType }}
                            </span>
                        </div>

                        <form wire:submit.prevent="saveWan" class="space-y-4">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <!-- VLAN & NAT -->
                                <div>
                                    <label class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1">VLAN ID</label>
                                    <input type="number" wire:model="formVlan" class="w-full px-3 py-2 bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-600 rounded text-sm focus:ring-2 focus:ring-indigo-500" placeholder="Kosongkan jika Untagged">
                                </div>
                                <div class="flex items-end pb-2">
                                    <label class="flex items-center gap-2 cursor-pointer">
                                        <input type="checkbox" wire:model="formNat" class="w-4 h-4 text-indigo-600 border-slate-300 rounded focus:ring-indigo-500 dark:bg-slate-700 dark:border-slate-600">
                                        <span class="text-sm font-medium text-slate-700 dark:text-slate-300">Aktifkan NAT (Internet)</span>
                                    </label>
                                </div>

                                <!-- Credentials (PPPoE only) -->
                                @if($formType === 'PPPoE')
                                <div>
                                    <label class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1">Username PPPoE</label>
                                    <input type="text" wire:model="formUsername" class="w-full px-3 py-2 bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-600 rounded text-sm focus:ring-2 focus:ring-indigo-500" required>
                                </div>
                                <div>
                                    <label class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1">Password PPPoE</label>
                                    <input type="password" wire:model="formPassword" class="w-full px-3 py-2 bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-600 rounded text-sm focus:ring-2 focus:ring-indigo-500" placeholder="Kosongkan jika tidak ingin mengubah password">
                                </div>
                                @endif
                            </div>

                            <div class="flex justify-end gap-3 pt-4 mt-2 border-t border-slate-200 dark:border-slate-700">
                                <button type="button" wire:click="cancelEdit" class="px-4 py-2 text-sm font-medium text-slate-600 bg-white border border-slate-300 rounded hover:bg-slate-50 dark:bg-slate-700 dark:text-slate-300 dark:border-slate-600 dark:hover:bg-slate-600">Batal</button>
                                <button type="submit" class="px-4 py-2 text-sm font-medium text-white bg-indigo-600 rounded shadow hover:bg-indigo-700">Simpan Perubahan & Kirim ke ACS</button>
                            </div>
                        </form>
                    </div>
                    @endif
                @endif
            </div>
        </div>
    </div>
    @endif
</div>
