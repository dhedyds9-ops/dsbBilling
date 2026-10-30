@section('page_title')
    <a href="{{ route('isp.olts.index') }}" class="inline-flex items-center justify-center p-1.5 mr-1 text-slate-500 hover:text-slate-800 dark:text-slate-400 dark:hover:text-slate-100 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors" title="Kembali">
        <span class="material-symbols-outlined notranslate" translate="no" style="font-size:20px">arrow_back</span>
    </a>
    <span class="material-symbols-outlined notranslate text-indigo-500" translate="no" style="font-size:24px">add_circle</span>
    <span class="text-lg">Tambah OLT Baru</span>
@endsection

<div class="space-y-5 pb-10 max-w-4xl">
    {{-- SESSION FLASH --}}
    @if(session('success'))
        <div class="flex items-center gap-3 px-4 py-3 bg-emerald-50 dark:bg-emerald-900/30 border border-emerald-200 dark:border-emerald-700 rounded-xl text-emerald-700 dark:text-emerald-400 text-sm font-medium">
            <span class="material-symbols-outlined notranslate" translate="no" style="font-size:18px">check_circle</span>
            {{ session('success') }}
        </div>
    @endif
    @if(session('error'))
        <div class="flex items-center gap-3 px-4 py-3 bg-red-50 dark:bg-red-900/30 border border-red-200 dark:border-red-700 rounded-xl text-red-700 dark:text-red-400 text-sm font-medium">
            <span class="material-symbols-outlined notranslate" translate="no" style="font-size:18px">error</span>
            {{ session('error') }}
        </div>
    @endif
    @if(session('info'))
        <div class="flex items-center gap-3 px-4 py-3 bg-blue-50 dark:bg-blue-900/30 border border-blue-200 dark:border-blue-700 rounded-xl text-blue-700 dark:text-blue-400 text-sm font-medium">
            <span class="material-symbols-outlined notranslate" translate="no" style="font-size:18px">info</span>
            {{ session('info') }}
        </div>
    @endif
    @if ($errors->any())
        <div class="flex items-start gap-3 px-4 py-3 bg-red-50 dark:bg-red-900/30 border border-red-200 dark:border-red-700 rounded-xl text-red-700 dark:text-red-400 text-sm">
            <span class="material-symbols-outlined notranslate mt-0.5" translate="no" style="font-size:18px">error</span>
            <div>
                <p class="font-bold mb-1">Peringatan Validasi:</p>
                <ul class="list-disc pl-4 space-y-0.5 text-red-600 dark:text-red-400">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        </div>
    @endif

    <div class="bg-white dark:bg-slate-800 rounded-xl shadow-sm border border-slate-200 dark:border-slate-700 overflow-hidden">
        <form wire:submit.prevent="save" class="p-4 space-y-6">
            {{-- BAGIAN 1: INFORMASI UTAMA OLT --}}
            <div>
                <div class="flex items-center justify-between pb-3 mb-4 border-b border-slate-200 dark:border-slate-700">
                    <div>
                        <h3 class="text-base font-bold text-slate-800 dark:text-slate-100 flex items-center gap-2">
                            <span class="material-symbols-outlined notranslate text-indigo-600 dark:text-indigo-400" style="font-size:20px">router</span>
                            Informasi Perangkat & Koneksi OLT
                        </h3>
                        <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">Isi data pokok berikut untuk menghubungkan OLT ke billing via SNMP.</p>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                    {{-- Nama OLT --}}
                    <div>
                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Nama OLT <span class="text-red-500">*</span></label>
                        <input type="text" wire:model.live.debounce.400ms="name" placeholder="Contoh: OLT Gandasoli Utama" class="w-full px-4 py-2 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 dark:text-slate-100">
                        @error('name') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    {{-- Kode OLT --}}
                    <div>
                        <div class="flex items-center justify-between mb-1">
                            <label class="block text-sm font-medium text-slate-700 dark:text-slate-300">Kode OLT <span class="text-red-500">*</span></label>
                            <button type="button" wire:click="generateCode" class="text-xs text-indigo-600 dark:text-indigo-400 hover:underline inline-flex items-center gap-1 cursor-pointer">
                                <span class="material-symbols-outlined notranslate" style="font-size:14px">refresh</span> Auto-generate
                            </button>
                        </div>
                        <input type="text" wire:model="code" placeholder="Contoh: OLT-GND-01" class="w-full px-4 py-2 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-lg text-sm font-mono uppercase focus:outline-none focus:ring-2 focus:ring-indigo-500 dark:text-slate-100">
                        @error('code') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    {{-- Vendor OLT --}}
                    <div>
                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Vendor OLT <span class="text-red-500">*</span></label>
                        <select wire:model="vendor_id" class="w-full px-4 py-2 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 dark:text-slate-100 cursor-pointer">
                            <option value="">-- Pilih Vendor OLT --</option>
                            @foreach($vendors as $vendor)
                                <option value="{{ $vendor->id }}">{{ $vendor->name }} ({{ $vendor->code }})</option>
                            @endforeach
                        </select>
                        @error('vendor_id') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    {{-- IP Address --}}
                    <div>
                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">IP Address OLT <span class="text-red-500">*</span></label>
                        <input type="text" wire:model="ip_address" placeholder="Contoh: 192.168.10.2 atau 10.10.0.1" class="w-full px-4 py-2 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-lg text-sm font-mono focus:outline-none focus:ring-2 focus:ring-indigo-500 dark:text-slate-100">
                        @error('ip_address') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    {{-- SNMP Community Read --}}
                    <div>
                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">SNMP Community (Read)</label>
                        <input type="text" wire:model="snmp_community_read" placeholder="public" class="w-full px-4 py-2 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-lg text-sm font-mono focus:outline-none focus:ring-2 focus:ring-indigo-500 dark:text-slate-100">
                        <span class="text-xs text-slate-400 mt-1 block">Default umum: <code>public</code></span>
                    </div>

                    {{-- POP / Shelter --}}
                    <div>
                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">POP / Shelter <span class="text-slate-400 font-normal text-xs ml-1">(Opsional)</span></label>
                        <select wire:model="pop_id" class="w-full px-4 py-2 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 dark:text-slate-100 cursor-pointer">
                            <option value="">-- Tanpa POP / Belum Ditentukan --</option>
                            @foreach($pops as $pop)
                                <option value="{{ $pop->id }}">{{ $pop->name }} ({{ $pop->code }})</option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Cabang / Wilayah Kantor --}}
                    <div>
                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Cabang / Wilayah Kantor <span class="text-slate-400 font-normal text-xs ml-1">(Opsional)</span></label>
                        <select wire:model="branch_id" class="w-full px-4 py-2 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 dark:text-slate-100 cursor-pointer">
                            <option value="">-- Tanpa Cabang Khusus / Global --</option>
                            @foreach($branches as $b)
                                <option value="{{ $b->id }}">{{ $b->name }} ({{ $b->code }})</option>
                            @endforeach
                        </select>
                        @error('branch_id') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    {{-- Koordinat Lokasi OLT --}}
                    <div class="md:col-span-2">
                        <x-forms.coordinate-picker latModel="latitude" lngModel="longitude" />
                    </div>

                    {{-- Status --}}
                    <div class="md:col-span-2 flex items-center gap-4 pt-1">
                        <label class="text-sm font-medium text-slate-700 dark:text-slate-300">Status OLT:</label>
                        <label class="inline-flex items-center gap-2 cursor-pointer text-sm">
                            <input type="radio" wire:model="status" value="active" class="text-indigo-600 focus:ring-indigo-500">
                            <span class="text-emerald-700 dark:text-emerald-400 font-medium">Aktif</span>
                        </label>
                        <label class="inline-flex items-center gap-2 cursor-pointer text-sm">
                            <input type="radio" wire:model="status" value="inactive" class="text-indigo-600 focus:ring-indigo-500">
                            <span class="text-slate-500 font-medium">Nonaktif</span>
                        </label>
                    </div>
                </div>
            </div>

            {{-- LIVE FEEDBACK HASIL TEST KONEKSI --}}
            @if($detectedInfo)
            <div class="p-4 bg-emerald-50 dark:bg-emerald-900/30 border border-emerald-200 dark:border-emerald-800 rounded-xl flex items-start gap-3">
                <span class="material-symbols-outlined notranslate text-emerald-600 dark:text-emerald-400 mt-0.5" style="font-size:22px">check_circle</span>
                <div class="flex-1">
                    <h4 class="font-bold text-emerald-800 dark:text-emerald-300 text-sm">SNMP Terhubung Berhasil!</h4>
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 mt-2 text-xs text-emerald-700 dark:text-emerald-300">
                        <div><span class="text-emerald-600 dark:text-emerald-400 font-medium">Model:</span> {{ $detectedInfo['model'] }}</div>
                        <div><span class="text-emerald-600 dark:text-emerald-400 font-medium">Firmware:</span> {{ $detectedInfo['firmware'] }}</div>
                        <div><span class="text-emerald-600 dark:text-emerald-400 font-medium">Uptime:</span> {{ $detectedInfo['uptime'] }}</div>
                    </div>
                </div>
            </div>
            @endif

            {{-- ACCORDION TOGGLE: PENGATURAN LANJUTAN (OPSIONAL) --}}
            <div class="border border-slate-200 dark:border-slate-700 rounded-xl overflow-hidden transition-all">
                <button type="button" wire:click="toggleAdvanced" class="w-full flex items-center justify-between p-4 bg-slate-50 dark:bg-slate-800/80 hover:bg-slate-100 dark:hover:bg-slate-700/50 text-left transition-colors cursor-pointer">
                    <div class="flex items-center gap-3">
                        <span class="material-symbols-outlined notranslate text-slate-500" style="font-size:20px">tune</span>
                        <div>
                            <span class="text-sm font-semibold text-slate-700 dark:text-slate-200">Pengaturan Lanjutan & Kredensial (Opsional)</span>
                            <p class="text-xs text-slate-400 dark:text-slate-500">Kredensial CLI (Telnet/SSH), Port SNMP kustom, Zabbix, dan Detail Perangkat.</p>
                        </div>
                    </div>
                    <span class="material-symbols-outlined notranslate text-slate-400 transition-transform duration-200 {{ $showAdvanced ? 'rotate-180' : '' }}" style="font-size:20px">
                        expand_more
                    </span>
                </button>

                @if($showAdvanced)
                <div class="p-5 bg-white dark:bg-slate-800 border-t border-slate-200 dark:border-slate-700 space-y-5">
                    {{-- Section A: SNMP Lanjutan --}}
                    <div>
                        <h4 class="text-xs uppercase tracking-wider font-bold text-slate-500 dark:text-slate-400 mb-3 flex items-center gap-1.5">
                            <span class="material-symbols-outlined notranslate" style="font-size:16px">settings_ethernet</span>
                            Port & Community SNMP Lanjutan
                        </h4>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div>
                                <label class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1">SNMP Version</label>
                                <select wire:model="snmp_version" class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 dark:text-slate-100">
                                    <option value="1">v1</option>
                                    <option value="2c">v2c (Rekomendasi)</option>
                                    <option value="3">v3</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1">SNMP Port</label>
                                <input type="number" wire:model="snmp_port" placeholder="161" class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-lg text-sm font-mono focus:outline-none focus:ring-2 focus:ring-indigo-500 dark:text-slate-100">
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1">SNMP Community (Write)</label>
                                <input type="text" wire:model="snmp_community_write" placeholder="private" class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-lg text-sm font-mono focus:outline-none focus:ring-2 focus:ring-indigo-500 dark:text-slate-100">
                            </div>
                        </div>
                    </div>

                    {{-- Section B: Kredensial CLI --}}
                    <div class="pt-4 border-t border-slate-100 dark:border-slate-700/60">
                        <h4 class="text-xs uppercase tracking-wider font-bold text-slate-500 dark:text-slate-400 mb-3 flex items-center gap-1.5">
                            <span class="material-symbols-outlined notranslate" style="font-size:16px">terminal</span>
                            Kredensial CLI / Terminal (Telnet / SSH)
                        </h4>
                        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                            <div>
                                <label class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1">CLI Mode</label>
                                <select wire:model.live="cli_mode" class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 dark:text-slate-100">
                                    <option value="telnet">Telnet</option>
                                    <option value="ssh">SSH</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1">CLI Port</label>
                                <input type="number" wire:model="cli_port" placeholder="23" class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-lg text-sm font-mono focus:outline-none focus:ring-2 focus:ring-indigo-500 dark:text-slate-100">
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1">Username CLI</label>
                                <input type="text" wire:model="username" placeholder="admin" class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 dark:text-slate-100">
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1">Password CLI</label>
                                <input type="password" wire:model="password" placeholder="••••••••" class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 dark:text-slate-100">
                            </div>
                        </div>
                    </div>

                    {{-- Section C: Detail Hardware & Lokasi --}}
                    <div class="pt-4 border-t border-slate-100 dark:border-slate-700/60">
                        <h4 class="text-xs uppercase tracking-wider font-bold text-slate-500 dark:text-slate-400 mb-3 flex items-center gap-1.5">
                            <span class="material-symbols-outlined notranslate" style="font-size:16px">info</span>
                            Detail Hardware, Zabbix & Catatan
                        </h4>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div>
                                <label class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1">Model (Manual)</label>
                                <input type="text" wire:model="model" placeholder="Terisi otomatis via SNMP" class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 dark:text-slate-100">
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1">Serial Number (Manual)</label>
                                <input type="text" wire:model="serial_number" placeholder="Contoh: SN12345678" class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 dark:text-slate-100">
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1">Zabbix Host ID</label>
                                <input type="text" wire:model="zabbix_host_id" placeholder="Contoh: 10085" class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 dark:text-slate-100">
                            </div>
                            <div class="md:col-span-3">
                                <label class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1">Deskripsi / Lokasi Fisik</label>
                                <textarea wire:model="description" rows="2" placeholder="Catatan rak, lokasi gedung, atau nomor inventaris..." class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 dark:text-slate-100"></textarea>
                            </div>
                        </div>
                    </div>
                </div>
                @endif
            </div>

            {{-- TOMBOL AKSI BAWAH --}}
            <div class="flex items-center justify-end gap-3 pt-6 border-t border-slate-200 dark:border-slate-700">
                <a href="{{ route('isp.olts.index') }}" class="px-5 py-2.5 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-700 dark:text-slate-300 rounded-xl text-sm font-semibold hover:bg-slate-50 dark:bg-slate-900/50 transition-colors">
                    Batal
                </a>
                <button type="button" wire:click="testConnection" wire:loading.attr="disabled" wire:target="testConnection" class="px-5 py-2.5 bg-indigo-50 hover:bg-indigo-100 text-indigo-700 dark:bg-indigo-900/40 dark:hover:bg-indigo-900/60 dark:text-indigo-300 border border-indigo-200 dark:border-indigo-700/60 rounded-xl text-sm font-semibold transition-colors inline-flex items-center justify-center gap-2 cursor-pointer disabled:opacity-50 disabled:cursor-not-allowed">
                    <span wire:loading.remove wire:target="testConnection" class="material-symbols-outlined notranslate" translate="no" style="font-size:18px">network_check</span>
                    <span wire:loading wire:target="testConnection" class="material-symbols-outlined notranslate animate-spin" translate="no" style="font-size:18px">autorenew</span>
                    <span wire:loading.remove wire:target="testConnection">Test Koneksi</span>
                    <span wire:loading wire:target="testConnection">Memeriksa...</span>
                </button>
                <button type="submit" wire:loading.attr="disabled" wire:target="save" class="px-6 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-sm font-semibold transition-colors shadow-sm cursor-pointer disabled:opacity-50 disabled:cursor-not-allowed inline-flex items-center justify-center gap-2 min-w-[130px]">
                    <span wire:loading.remove wire:target="save" class="material-symbols-outlined notranslate" translate="no" style="font-size:18px">save</span>
                    <span wire:loading wire:target="save" class="material-symbols-outlined notranslate animate-spin" translate="no" style="font-size:18px">autorenew</span>
                    <span wire:loading.remove wire:target="save">Simpan</span>
                    <span wire:loading wire:target="save">Menyimpan...</span>
                </button>
            </div>
        </form>
    </div>
</div>
