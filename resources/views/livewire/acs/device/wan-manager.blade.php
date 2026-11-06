<div>
    <button wire:click="openModal" class="inline-flex items-center justify-center px-4 py-2 bg-purple-50 dark:bg-purple-900/30 text-purple-700 dark:text-purple-300 hover:bg-purple-100 dark:hover:bg-purple-900/50 border border-purple-200 dark:border-purple-800 rounded-lg text-sm font-semibold transition-colors">
        <span class="material-symbols-outlined notranslate mr-2" translate="no" style="font-size:18px">settings_ethernet</span>
        Pengaturan Lanjutan
    </button>

    @if($showWanModal)
    <div class="fixed inset-0 z-[60] overflow-y-auto bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-3 sm:p-4" x-data="{ init() { $wire.loadWans() } }">
        <div class="{{ $isNocLayout ? 'noc-panel-bg noc-border' : 'bg-white dark:bg-slate-800' }} rounded-2xl shadow-2xl w-full max-w-5xl overflow-hidden flex flex-col max-h-[92vh] border border-slate-200 dark:border-slate-700 my-auto">
            
            {{-- Header --}}
            <div class="px-6 py-4 border-b border-slate-100 dark:border-slate-700 flex items-center justify-between shrink-0 bg-slate-50 dark:bg-slate-900/50">
                <div class="flex items-center gap-2.5">
                    <span class="material-symbols-outlined text-purple-600 dark:text-purple-400 text-[24px]">settings_ethernet</span>
                    <div>
                        <h3 class="text-base font-bold text-slate-900 dark:text-slate-100">Manajemen Koneksi WAN & Binding (TR-069)</h3>
                        <p class="text-[11px] text-slate-500 dark:text-slate-400">
                            Vendor: <span class="font-semibold uppercase text-purple-600 dark:text-purple-400">{{ $detectedVendor !== 'generic' ? strtoupper($detectedVendor) : ($device->vendor?->name ?? 'Generic / Unknown') }}</span>
                            @if($device->product_class) &bull; Tipe: <span class="font-medium text-slate-700 dark:text-slate-300">{{ $device->product_class }}</span>@endif
                        </p>
                    </div>
                </div>
                <button wire:click="$set('showWanModal', false)" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 transition-colors">
                    <span class="material-symbols-outlined text-[20px]" translate="no">close</span>
                </button>
            </div>
            
            {{-- Body --}}
            <div class="p-6 overflow-y-auto flex-1 space-y-5">
                @if(session()->has('error'))
                    <div class="p-3.5 text-xs text-rose-700 bg-rose-50 border border-rose-200 rounded-xl dark:bg-rose-950/40 dark:border-rose-800 dark:text-rose-300 flex items-center gap-2">
                        <span class="material-symbols-outlined text-[18px]">error</span>
                        <span>{{ session('error') }}</span>
                    </div>
                @endif
                @if(session()->has('success'))
                    <div class="p-3.5 text-xs text-emerald-700 bg-emerald-50 border border-emerald-200 rounded-xl dark:bg-emerald-950/40 dark:border-emerald-800 dark:text-emerald-300 flex items-center gap-2">
                        <span class="material-symbols-outlined text-[18px]">check_circle</span>
                        <span>{{ session('success') }}</span>
                    </div>
                @endif

                @if($isLoading)
                    <div class="flex flex-col items-center justify-center py-16">
                        <div class="animate-spin rounded-full h-10 w-10 border-b-2 border-purple-600 mb-3"></div>
                        <p class="text-xs font-medium text-slate-500 dark:text-slate-400">Menyinkronkan data WAN dengan GenieACS...</p>
                    </div>
                @else
                    {{-- Active WANs Table Header --}}
                    <div class="flex justify-between items-center">
                        <div>
                            <h4 class="font-bold text-sm text-slate-800 dark:text-slate-200">Daftar Koneksi WAN Aktif</h4>
                            <p class="text-[11px] text-slate-400">Koneksi WAN yang terkonfigurasi pada perangkat modem ONT saat ini.</p>
                        </div>
                        <button wire:click="createWan" class="inline-flex items-center gap-1.5 px-3.5 py-1.5 text-xs font-bold text-white bg-purple-600 hover:bg-purple-700 rounded-xl shadow-sm transition-colors">
                            <span class="material-symbols-outlined text-[16px]">add</span>
                            <span>Tambah WAN Baru</span>
                        </button>
                    </div>
                    
                    {{-- Active WANs Table --}}
                    <div class="overflow-x-auto border border-slate-200 dark:border-slate-700 rounded-xl">
                        <table class="w-full text-left text-xs text-slate-600 dark:text-slate-300">
                            <thead class="uppercase tracking-wider font-bold text-[10px] {{ $isNocLayout ? 'noc-panel-bg noc-text' : 'bg-slate-50 dark:bg-slate-900/60' }} border-b border-slate-200 dark:border-slate-700 text-slate-500 dark:text-slate-400">
                                <tr>
                                    <th class="px-4 py-3">Nama Koneksi</th>
                                    <th class="px-4 py-3">Tipe & Mode</th>
                                    <th class="px-4 py-3 text-center">VLAN</th>
                                    <th class="px-4 py-3 text-center">NAT</th>
                                    <th class="px-4 py-3">Keterangan</th>
                                    <th class="px-4 py-3 text-right">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 dark:divide-slate-700/60">
                                @forelse($wanConnections as $wan)
                                <tr class="hover:bg-slate-50/70 dark:hover:bg-slate-900/30 transition-colors">
                                    <td class="px-4 py-3 font-medium text-slate-900 dark:text-slate-100">
                                        <div class="font-mono font-semibold">{{ $wan['name'] }}</div>
                                        <div class="text-[10px] text-slate-400 truncate max-w-[220px]" title="{{ $wan['fullPath'] }}">{{ $wan['fullPath'] }}</div>
                                    </td>
                                    <td class="px-4 py-3">
                                        @php
                                            $isBridge = str_contains($wan['mode_label'] ?? '', 'Bridge');
                                            $badgeColor = $isBridge 
                                                ? 'bg-amber-50 text-amber-700 border-amber-200 dark:bg-amber-950/50 dark:text-amber-400 dark:border-amber-800' 
                                                : 'bg-purple-50 text-purple-700 border-purple-200 dark:bg-purple-950/50 dark:text-purple-400 dark:border-purple-800';
                                        @endphp
                                        <span class="inline-block px-2 py-0.5 rounded-md text-[10px] font-bold border {{ $badgeColor }}">
                                            {{ $wan['mode_label'] ?? $wan['type'] }}
                                        </span>
                                        @if($wan['connection_type'] && $wan['connection_type'] !== '-')
                                            <div class="text-[10px] text-slate-400 font-mono mt-0.5">{{ $wan['connection_type'] }}</div>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3 text-center font-mono font-bold text-slate-800 dark:text-slate-200">
                                        {{ $wan['vlan'] ?? '-' }}
                                    </td>
                                    <td class="px-4 py-3 text-center">
                                        @if($wan['nat'])
                                            <span class="text-emerald-500 material-symbols-outlined text-[18px]" title="NAT Aktif">check_circle</span>
                                        @else
                                            <span class="text-slate-300 dark:text-slate-600 material-symbols-outlined text-[18px]" title="NAT Nonaktif">cancel</span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3 text-[11px] space-y-0.5">
                                        @if($wan['username'])
                                            <div><span class="text-slate-400 font-medium">User:</span> <span class="font-mono font-semibold text-slate-800 dark:text-slate-200">{{ $wan['username'] }}</span></div>
                                        @endif
                                        <div><span class="text-slate-400 font-medium">Service:</span> <span class="font-semibold text-purple-600 dark:text-purple-400">{{ $wan['service_list'] }}</span></div>
                                        @if($wan['port_bind'])
                                            <div class="truncate max-w-[260px]" title="{{ $wan['port_bind'] }}">
                                                <span class="text-slate-400 font-medium">Bind:</span> <span class="text-slate-600 dark:text-slate-300 font-mono">{{ $wan['port_bind'] }}</span>
                                            </div>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3 text-right">
                                        <button wire:click="editWan('{{ $wan['fullPath'] }}')" class="px-2.5 py-1 text-xs font-semibold text-purple-600 hover:text-purple-800 dark:text-purple-400 dark:hover:text-purple-300 rounded hover:bg-purple-50 dark:hover:bg-purple-950/40 transition-colors">
                                            Edit Profil
                                        </button>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="6" class="px-4 py-8 text-center text-slate-400">
                                        <span class="material-symbols-outlined text-[36px] text-slate-300 dark:text-slate-600 mb-1">wifi_off</span>
                                        <p class="font-medium text-slate-600 dark:text-slate-300">Tidak ada koneksi WAN aktif atau format belum tersinkron.</p>
                                        <p class="text-[11px] text-slate-400">Klik tombol <strong>+ Tambah WAN Baru</strong> untuk mengonfigurasi koneksi baru.</p>
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    {{-- Form Tambah / Edit WAN --}}
                    @if($isEditing || $isCreating)
                    <div class="border border-purple-200 dark:border-purple-800/60 rounded-2xl p-5 {{ $isNocLayout ? 'noc-panel-bg noc-border' : 'bg-purple-50/30 dark:bg-purple-950/20' }} space-y-4">
                        <div class="flex items-center justify-between pb-3 border-b border-purple-100 dark:border-purple-800/40">
                            <h4 class="font-bold text-sm text-slate-900 dark:text-slate-100 flex items-center gap-2">
                                <span class="material-symbols-outlined text-purple-600 text-[20px]">{{ $isCreating ? 'add_box' : 'edit_square' }}</span>
                                <span>{{ $isCreating ? 'Tambah Koneksi WAN Baru' : 'Edit Koneksi: ' . $formName }}</span>
                            </h4>
                            <span class="px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-purple-100 dark:bg-purple-900/50 text-purple-700 dark:text-purple-300">
                                {{ $isCreating ? 'Mode Pembuatan Otomatis' : 'Mode Edit Parameter' }}
                            </span>
                        </div>

                        <form wire:submit.prevent="{{ $isCreating ? 'saveNewWan' : 'saveWan' }}" class="space-y-4">
                            
                            {{-- Selection Mode (Available in Create & Edit) --}}
                            <div>
                                <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-2">
                                    Pilih Jenis Mode Koneksi & Slot WAN
                                </label>
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                    {{-- PPPoE Route --}}
                                    <label class="relative flex items-start p-3 rounded-xl border cursor-pointer transition-colors {{ $formMode === 'pppoe_route' ? 'border-purple-500 bg-purple-50/60 dark:bg-purple-950/40 ring-1 ring-purple-500' : 'border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900' }}">
                                        <input type="radio" wire:model.live="formMode" value="pppoe_route" class="mt-1 text-purple-600 focus:ring-purple-500">
                                        <div class="ml-2.5">
                                            <span class="block text-xs font-bold text-slate-900 dark:text-slate-100">PPPoE Dial-up (Route)</span>
                                            <span class="block text-[11px] text-slate-500 dark:text-slate-400 mt-0.5">
                                                Modem dial PPPoE sendiri. Wajib mengisi Username & Password PPPoE di bawah.
                                            </span>
                                        </div>
                                    </label>

                                    {{-- WAN Bridge - Slot PPP --}}
                                    <label class="relative flex items-start p-3 rounded-xl border cursor-pointer transition-colors {{ $formMode === 'bridge_ppp' ? 'border-amber-500 bg-amber-50/60 dark:bg-amber-950/40 ring-1 ring-amber-500' : 'border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900' }}">
                                        <input type="radio" wire:model.live="formMode" value="bridge_ppp" class="mt-1 text-amber-600 focus:ring-amber-500">
                                        <div class="ml-2.5">
                                            <span class="block text-xs font-bold text-slate-900 dark:text-slate-100">WAN Bridge (Slot WAN PPP)</span>
                                            <span class="block text-[11px] text-slate-500 dark:text-slate-400 mt-0.5">
                                                Conn Mode: <code class="text-amber-600">PPPoE_Bridged</code>. Dial PPPoE diteruskan ke Router / Mikrotik.
                                            </span>
                                        </div>
                                    </label>

                                    {{-- WAN Bridge - Slot IP --}}
                                    <label class="relative flex items-start p-3 rounded-xl border cursor-pointer transition-colors {{ $formMode === 'bridge_ip' ? 'border-amber-500 bg-amber-50/60 dark:bg-amber-950/40 ring-1 ring-amber-500' : 'border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900' }}">
                                        <input type="radio" wire:model.live="formMode" value="bridge_ip" class="mt-1 text-amber-600 focus:ring-amber-500">
                                        <div class="ml-2.5">
                                            <span class="block text-xs font-bold text-slate-900 dark:text-slate-100">WAN Bridge (Slot WAN IP)</span>
                                            <span class="block text-[11px] text-slate-500 dark:text-slate-400 mt-0.5">
                                                Conn Mode: <code class="text-amber-600">IP_Bridged</code>. Untuk Hotspot, Trunk LAN, atau Bridge IP.
                                            </span>
                                        </div>
                                    </label>

                                    {{-- IP Route (DHCP) --}}
                                    <label class="relative flex items-start p-3 rounded-xl border cursor-pointer transition-colors {{ $formMode === 'dhcp_route' ? 'border-purple-500 bg-purple-50/60 dark:bg-purple-950/40 ring-1 ring-purple-500' : 'border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900' }}">
                                        <input type="radio" wire:model.live="formMode" value="dhcp_route" class="mt-1 text-purple-600 focus:ring-purple-500">
                                        <div class="ml-2.5">
                                            <span class="block text-xs font-bold text-slate-900 dark:text-slate-100">DHCP / Hotspot (IP Route)</span>
                                            <span class="block text-[11px] text-slate-500 dark:text-slate-400 mt-0.5">
                                                Conn Mode: <code class="text-purple-600">IP_Routed</code>. Menerima IP dinamis via DHCP Client.
                                            </span>
                                        </div>
                                    </label>
                                </div>
                            </div>

                            {{-- VLAN Tagging Configuration Card --}}
                            <div class="p-3.5 rounded-2xl border {{ $formVlanEnabled ? 'bg-purple-50/50 dark:bg-purple-950/20 border-purple-200 dark:border-purple-800/50' : 'bg-slate-50/70 dark:bg-slate-900/40 border-slate-200 dark:border-slate-700' }} transition-colors">
                                <div class="flex items-center justify-between">
                                    <label class="flex items-center gap-2.5 cursor-pointer select-none">
                                        <input type="checkbox" wire:model.live="formVlanEnabled" class="w-4 h-4 text-purple-600 rounded border-slate-300 focus:ring-purple-500 dark:bg-slate-900 dark:border-slate-700">
                                        <div>
                                            <span class="text-xs font-bold text-slate-900 dark:text-slate-100 uppercase tracking-wider flex items-center gap-1.5">
                                                <span>Aktifkan VLAN Tagging (802.1Q)</span>
                                            </span>
                                            <span class="block text-[11px] text-slate-500 dark:text-slate-400">
                                                Aktifkan jika jalur koneksi WAN di OLT / Mikrotik menggunakan VLAN ID tertentu.
                                            </span>
                                        </div>
                                    </label>
                                    <span class="shrink-0 px-2.5 py-1 rounded-full text-[10px] font-bold uppercase tracking-wider {{ $formVlanEnabled ? 'bg-purple-100 text-purple-700 dark:bg-purple-900/60 dark:text-purple-300 border border-purple-200 dark:border-purple-800' : 'bg-slate-200 text-slate-600 dark:bg-slate-800 dark:text-slate-400 border border-slate-300 dark:border-slate-700' }}">
                                        {{ $formVlanEnabled ? 'VLAN ENABLED (TAGGED)' : 'VLAN DISABLED (UNTAGGED)' }}
                                    </span>
                                </div>

                                @if($formVlanEnabled)
                                <div class="mt-3 pt-3 border-t border-purple-100 dark:border-purple-800/40 grid grid-cols-1 sm:grid-cols-2 gap-3 items-center">
                                    <div>
                                        <label class="block text-xs font-bold uppercase tracking-wider text-purple-900 dark:text-purple-300 mb-1">
                                            VLAN ID (1 - 4094) <span class="text-rose-500">*</span>
                                        </label>
                                        <div class="relative">
                                            <input type="number" wire:model="formVlan" min="1" max="4094" placeholder="Contoh: 200" required
                                                class="w-full px-3.5 py-2 rounded-xl border border-purple-300 dark:border-purple-700 text-xs font-mono font-bold focus:ring-2 focus:ring-purple-500 bg-white dark:bg-slate-900 dark:text-slate-100">
                                            <span class="absolute right-3 top-2 text-[11px] text-slate-400 font-medium">802.1Q</span>
                                        </div>
                                        @error('formVlan') <p class="text-[11px] text-rose-500 mt-1">{{ $message }}</p> @enderror
                                    </div>
                                    <div class="text-[11px] text-slate-500 dark:text-slate-400 leading-relaxed bg-white/70 dark:bg-slate-900/60 p-2.5 rounded-xl border border-purple-100 dark:border-purple-900/40">
                                        <p class="font-semibold text-purple-700 dark:text-purple-300">Huawei / ZTE / FiberHome Tagging:</p>
                                        Sistem mengirim parameter VLAN dan mode Tagged (<code class="text-purple-600">Mode=2 / VLANEnable=1</code>) ke perangkat.
                                    </div>
                                </div>
                                @endif
                            </div>

                            {{-- Row: Service List & NAT Toggle --}}
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                {{-- Service List --}}
                                <div>
                                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1.5">
                                        Service List
                                    </label>
                                    <select wire:model.live="formServiceList" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 text-xs focus:ring-2 focus:ring-purple-500 bg-white dark:bg-slate-900 dark:text-slate-100 font-semibold">
                                        <option value="INTERNET">INTERNET (Standar Umum)</option>
                                        <option value="TR069,INTERNET">TR069,INTERNET (Wajib FiberHome)</option>
                                        <option value="OTHER">OTHER (Bridge / Hotspot)</option>
                                        <option value="TR069">TR069 (Khusus Management)</option>
                                        <option value="CUSTOM">Input Manual Custom...</option>
                                    </select>
                                    @if($formServiceList === 'CUSTOM')
                                        <input type="text" wire:model="customServiceList" placeholder="Ketik service list..." class="mt-2 w-full px-3 py-1.5 rounded-lg border border-slate-200 dark:border-slate-700 text-xs bg-white dark:bg-slate-900 font-mono">
                                    @endif
                                </div>

                                {{-- NAT Toggle --}}
                                <div class="flex flex-col justify-center p-3 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50/50 dark:bg-slate-900/30">
                                    <label class="flex items-center gap-2 cursor-pointer">
                                        <input type="checkbox" wire:model="formNat" class="w-4 h-4 text-purple-600 border-slate-300 rounded focus:ring-purple-500 dark:bg-slate-900 dark:border-slate-700">
                                        <span class="text-xs font-bold text-slate-700 dark:text-slate-300">Aktifkan NAT (Internet Routing)</span>
                                    </label>
                                    <p class="text-[10px] text-slate-400 mt-0.5 ml-6">Nonaktifkan untuk mode Bridge atau IP Passthrough.</p>
                                </div>
                            </div>

                            {{-- Credentials (Only for PPPoE Route) --}}
                            @if($formMode === 'pppoe_route' || $formType === 'PPPoE')
                            <div class="space-y-2 p-4 rounded-2xl bg-purple-50/50 dark:bg-purple-950/30 border border-purple-200/70 dark:border-purple-800/50">
                                @if(!$isCreating && str_contains($editFullPath, 'WANIPConnection'))
                                <div class="flex items-start gap-2 p-2.5 rounded-xl bg-amber-50 dark:bg-amber-950/50 border border-amber-200 dark:border-amber-800 text-[11px] text-amber-800 dark:text-amber-200 mb-2">
                                    <span class="material-symbols-outlined text-[18px] text-amber-600 shrink-0">info</span>
                                    <div>
                                        <span class="font-bold">Konversi Bridge ke PPPoE:</span> Slot ini saat ini adalah koneksi Bridge (<code class="font-mono text-amber-900 dark:text-amber-100">{{ $formName }}</code>). Saat Anda menyimpan dengan mode PPPoE, sistem akan otomatis membuat profil dial PPPoE di modem dengan akun di bawah ini.
                                    </div>
                                </div>
                                @endif

                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-xs font-bold uppercase tracking-wider text-purple-900 dark:text-purple-300 mb-1.5">
                                            Username PPPoE <span class="text-rose-500">*</span>
                                        </label>
                                        <input type="text" wire:model="formUsername" placeholder="username@isp" required
                                            class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 text-xs font-mono focus:ring-2 focus:ring-purple-500 bg-white dark:bg-slate-900 dark:text-slate-100">
                                        @error('formUsername') <p class="text-xs text-rose-500 mt-1">{{ $message }}</p> @enderror
                                    </div>
                                    <div>
                                        <label class="block text-xs font-bold uppercase tracking-wider text-purple-900 dark:text-purple-300 mb-1.5">
                                            Password PPPoE
                                        </label>
                                        <input type="password" wire:model="formPassword" placeholder="{{ $isEditing && !empty($formUsername) ? 'Biarkan kosong jika tidak diubah' : 'Sandi PPPoE' }}"
                                            class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 text-xs focus:ring-2 focus:ring-purple-500 bg-white dark:bg-slate-900 dark:text-slate-100">
                                    </div>
                                </div>
                            </div>
                            @endif

                            {{-- Port Binding (LAN & WLAN) --}}
                            <div class="p-4 rounded-2xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 space-y-3">
                                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 pb-2 border-b border-slate-100 dark:border-slate-800">
                                    <div>
                                        <h5 class="text-xs font-bold uppercase tracking-wider text-slate-800 dark:text-slate-200 flex items-center gap-1.5">
                                            <span class="material-symbols-outlined text-[16px] text-purple-600">hub</span>
                                            <span>Bind / Tag Interface LAN & WLAN</span>
                                        </h5>
                                        <p class="text-[11px] text-slate-400">Pilih port LAN dan SSID Wi-Fi yang diarahkan ke profil WAN ini.</p>
                                    </div>
                                    <div class="flex items-center gap-2">
                                        <button type="button" wire:click="applyPresetFiberhome" class="px-2.5 py-1 text-[11px] font-semibold text-purple-700 bg-purple-50 hover:bg-purple-100 dark:bg-purple-950/60 dark:text-purple-300 rounded-lg transition-colors border border-purple-200 dark:border-purple-800">
                                            Preset FiberHome (LAN1 + SSID1 + SSID5)
                                        </button>
                                        <button type="button" wire:click="applyPresetAllPorts" class="px-2.5 py-1 text-[11px] font-semibold text-slate-600 bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:text-slate-300 rounded-lg transition-colors">
                                            Pilih Semua
                                        </button>
                                    </div>
                                </div>

                                {{-- Checkboxes Grid --}}
                                @if(!$useManualBinding)
                                <div class="space-y-3">
                                    {{-- LAN Ports --}}
                                    <div>
                                        <span class="text-[11px] font-bold text-slate-500 dark:text-slate-400 uppercase">Port LAN Fisik:</span>
                                        <div class="grid grid-cols-2 sm:grid-cols-4 gap-2 mt-1.5">
                                            @for($i=1; $i<=4; $i++)
                                            <label class="flex items-center gap-2 p-2 rounded-lg border border-slate-200 dark:border-slate-800 hover:bg-slate-50 dark:hover:bg-slate-800/40 cursor-pointer">
                                                <input type="checkbox" wire:model.live="bindLan" value="{{ $i }}" class="w-4 h-4 text-purple-600 rounded border-slate-300 focus:ring-purple-500">
                                                <span class="text-xs font-semibold text-slate-800 dark:text-slate-200">LAN {{ $i }}</span>
                                            </label>
                                            @endfor
                                        </div>
                                    </div>

                                    {{-- 2.4 GHz SSIDs --}}
                                    <div>
                                        <span class="text-[11px] font-bold text-slate-500 dark:text-slate-400 uppercase">Wi-Fi 2.4 GHz (SSID 1 - 4):</span>
                                        <div class="grid grid-cols-2 sm:grid-cols-4 gap-2 mt-1.5">
                                            @for($i=1; $i<=4; $i++)
                                            <label class="flex items-center gap-2 p-2 rounded-lg border border-slate-200 dark:border-slate-800 hover:bg-slate-50 dark:hover:bg-slate-800/40 cursor-pointer">
                                                <input type="checkbox" wire:model.live="bindWlan" value="{{ $i }}" class="w-4 h-4 text-purple-600 rounded border-slate-300 focus:ring-purple-500">
                                                <span class="text-xs font-semibold text-slate-800 dark:text-slate-200">SSID {{ $i }}</span>
                                            </label>
                                            @endfor
                                        </div>
                                    </div>

                                    {{-- 5 GHz SSIDs --}}
                                    <div>
                                        <div class="flex items-center justify-between">
                                            <span class="text-[11px] font-bold text-slate-500 dark:text-slate-400 uppercase">Wi-Fi 5 GHz Dual-Band (SSID 5 - 8):</span>
                                            <span class="text-[10px] text-slate-400 italic">5G biasanya di SSID 5 (FH) atau SSID 6+ (ZTE)</span>
                                        </div>
                                        <div class="grid grid-cols-2 sm:grid-cols-4 gap-2 mt-1.5">
                                            @for($i=5; $i<=8; $i++)
                                            <label class="flex items-center gap-2 p-2 rounded-lg border border-slate-200 dark:border-slate-800 hover:bg-slate-50 dark:hover:bg-slate-800/40 cursor-pointer">
                                                <input type="checkbox" wire:model.live="bindWlan" value="{{ $i }}" class="w-4 h-4 text-purple-600 rounded border-slate-300 focus:ring-purple-500">
                                                <span class="text-xs font-semibold text-slate-800 dark:text-slate-200">SSID {{ $i }} (5G)</span>
                                            </label>
                                            @endfor
                                        </div>
                                    </div>
                                </div>
                                @endif

                                {{-- Manual Parameter View / Edit --}}
                                <div class="pt-2">
                                    <div class="flex items-center justify-between mb-1.5">
                                        <label class="text-[11px] font-bold uppercase tracking-wider text-slate-600 dark:text-slate-400 flex items-center gap-1.5">
                                            <span>Parameter TR-069 Interface String:</span>
                                        </label>
                                        <button type="button" wire:click="$toggle('useManualBinding')" class="text-[11px] text-purple-600 dark:text-purple-400 font-semibold hover:underline">
                                            {{ $useManualBinding ? 'Kembali ke Pilihan Checkbox' : 'Edit / Ketik Parameter Manual' }}
                                        </button>
                                    </div>
                                    <textarea wire:model="customBindString" rows="2" placeholder="InternetGatewayDevice.LANDevice.1.LANEthernetInterfaceConfig.1,..."
                                        class="w-full px-3 py-2 rounded-xl border border-slate-200 dark:border-slate-700 text-[11px] font-mono focus:ring-2 focus:ring-purple-500 bg-slate-50 dark:bg-slate-800/50 dark:text-slate-200 resize-none" {{ $useManualBinding ? '' : 'readonly' }}></textarea>
                                    <p class="text-[10px] text-slate-400 mt-1">
                                        Gunakan tanda koma (<code>,</code>) untuk memisahkan beberapa interface. Pada tipe <strong>ZTE F670L</strong>, sistem akan otomatis mendaftarkan ke tabel <code>X_ZTE-COM_PortBinding</code>.
                                    </p>
                                </div>
                            </div>

                            {{-- Technical Reference Card --}}
                            <div class="p-3.5 rounded-xl bg-slate-50 dark:bg-slate-900/40 border border-slate-200 dark:border-slate-800 text-[11px] space-y-1 text-slate-500 dark:text-slate-400">
                                <div class="font-bold text-slate-700 dark:text-slate-300 flex items-center gap-1.5">
                                    <span class="material-symbols-outlined text-[15px] text-blue-600">info</span>
                                    <span>Catatan Standar Parameter Vendor (TR-069):</span>
                                </div>
                                <ul class="list-disc pl-4 space-y-0.5 text-[10px]">
                                    <li><strong>FiberHome PPPoE:</strong> Mode wajib <code class="text-purple-600">PPPoE_Routed</code> dan Service List <code class="text-purple-600">TR069,INTERNET</code> agar port remote ACS tetap responsif.</li>
                                    <li><strong>WAN Bridge:</strong> Slot PPP = <code class="text-amber-600">PPPoE_Bridged</code>, Slot IP = <code class="text-amber-600">IP_Bridged</code>, Service List = <code class="text-amber-600">INTERNET</code> atau <code class="text-amber-600">OTHER</code>.</li>
                                    <li><strong>Wi-Fi Security Mode:</strong> Menggunakan sandi = <code class="text-emerald-600">11i</code> atau <code class="text-emerald-600">WPAand11i</code>. Tanpa sandi (Hotspot) = <code class="text-slate-500">None</code>.</li>
                                </ul>
                            </div>

                            {{-- Submit / Cancel Actions --}}
                            <div class="flex justify-end gap-3 pt-3 border-t border-slate-200 dark:border-slate-700">
                                <button type="button" wire:click="{{ $isCreating ? 'cancelCreate' : 'cancelEdit' }}"
                                    class="px-4 py-2 text-xs font-semibold text-slate-700 dark:text-slate-300 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl hover:bg-slate-50 dark:hover:bg-slate-700 transition-colors">
                                    Batal
                                </button>
                                <button type="submit" wire:loading.attr="disabled"
                                    class="px-5 py-2 text-xs font-bold text-white bg-purple-600 hover:bg-purple-700 rounded-xl shadow-sm transition-colors flex items-center gap-2 disabled:opacity-50">
                                    <span wire:loading wire:target="{{ $isCreating ? 'saveNewWan' : 'saveWan' }}" class="material-symbols-outlined text-[16px] animate-spin">autorenew</span>
                                    <span>{{ $isCreating ? 'Buat & Kirim Task ke ACS' : 'Simpan Perubahan & Kirim ke ACS' }}</span>
                                </button>
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
