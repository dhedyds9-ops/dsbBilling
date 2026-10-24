<div class="space-y-6">
    {{-- Header & Breadcrumbs --}}
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div class="flex items-center gap-3">
            <a href="{{ route('crm.installations.index') }}" 
               class="p-2.5 text-slate-500 hover:text-slate-800 dark:text-slate-400 dark:hover:text-slate-200 rounded-xl bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 hover:bg-slate-50 transition-all shadow-xs">
                <span class="material-symbols-outlined notranslate text-lg" translate="no">arrow_back</span>
            </a>
            <div>
                <h1 class="text-2xl font-bold tracking-tight text-slate-900 dark:text-slate-100">Edit Instalasi #{{ $installationId }}</h1>
                <p class="mt-0.5 text-xs text-slate-500 dark:text-slate-400">Perbarui data instalasi, penugasan teknisi, jadwal atau nomor seri perangkat</p>
            </div>
        </div>
    </div>

    {{-- Form --}}
    <form wire:submit.prevent="save" class="space-y-6">
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            
            {{-- Kolom Kiri: Informasi Pelanggan & Jadwal --}}
            <div class="lg:col-span-2 space-y-6">
                {{-- Card: Sumber & Data Pelanggan --}}
                <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-6 shadow-xs space-y-5">
                    <div class="flex items-center gap-2 border-b border-slate-100 dark:border-slate-800 pb-3">
                        <span class="material-symbols-outlined notranslate text-indigo-600 dark:text-indigo-400" translate="no">person</span>
                        <h2 class="text-sm font-bold text-slate-900 dark:text-slate-100 uppercase tracking-wider">Informasi Pelanggan</h2>
                    </div>

                    {{-- Pilih Sumber Pelanggan --}}
                    <div>
                        <label class="block text-xs font-semibold text-slate-600 dark:text-slate-300 uppercase tracking-wider mb-2">Pilih Sumber Data *</label>
                        <div class="grid grid-cols-3 gap-2 p-1 bg-slate-100 dark:bg-slate-800 rounded-xl">
                            <button type="button" wire:click="$set('customer_source', 'customer')"
                                class="py-2 px-3 text-xs font-semibold rounded-lg transition-all {{ $customer_source === 'customer' ? 'bg-white dark:bg-slate-700 text-indigo-600 dark:text-indigo-400 shadow-xs' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900' }}">
                                Dari Pelanggan
                            </button>
                            <button type="button" wire:click="$set('customer_source', 'survey')"
                                class="py-2 px-3 text-xs font-semibold rounded-lg transition-all {{ $customer_source === 'survey' ? 'bg-white dark:bg-slate-700 text-indigo-600 dark:text-indigo-400 shadow-xs' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900' }}">
                                Dari Hasil Survey
                            </button>
                            <button type="button" wire:click="$set('customer_source', 'manual')"
                                class="py-2 px-3 text-xs font-semibold rounded-lg transition-all {{ $customer_source === 'manual' ? 'bg-white dark:bg-slate-700 text-indigo-600 dark:text-indigo-400 shadow-xs' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900' }}">
                                Input Manual
                            </button>
                        </div>
                    </div>

                    {{-- Dynamic Selector based on Source --}}
                    @if($customer_source === 'customer')
                        <div>
                            <label class="block text-xs font-semibold text-slate-600 dark:text-slate-300 mb-1.5">Pilih Pelanggan Terdaftar *</label>
                            <select wire:model.live="customer_id" 
                                    class="w-full px-3.5 py-2.5 bg-slate-50 dark:bg-slate-800/80 border border-slate-200 dark:border-slate-700 rounded-xl text-sm text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                                <option value="">-- Pilih Pelanggan --</option>
                                @foreach($customers as $c)
                                    <option value="{{ $c->id }}">[{{ $c->code ?? 'CUST-' . $c->id }}] {{ $c->name }} {{ $c->phone ? '('.$c->phone.')' : '' }}</option>
                                @endforeach
                            </select>
                            @error('customer_id') <p class="mt-1.5 text-xs text-rose-500 font-medium">{{ $message }}</p> @enderror
                        </div>
                    @elseif($customer_source === 'survey')
                        <div>
                            <label class="block text-xs font-semibold text-slate-600 dark:text-slate-300 mb-1.5">Pilih Data Hasil Survey *</label>
                            <select wire:model.live="survey_id" 
                                    class="w-full px-3.5 py-2.5 bg-slate-50 dark:bg-slate-800/80 border border-slate-200 dark:border-slate-700 rounded-xl text-sm text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                                <option value="">-- Pilih Hasil Survey --</option>
                                @foreach($surveys as $s)
                                    <option value="{{ $s->id }}">Survey #{{ $s->id }} - {{ $s->prospect?->name ?? 'Prospek #'.$s->prospect_id }} ({{ ucfirst($s->status) }})</option>
                                @endforeach
                            </select>
                            @error('survey_id') <p class="mt-1.5 text-xs text-rose-500 font-medium">{{ $message }}</p> @enderror
                        </div>
                    @endif

                    {{-- Customer Name Input / Confirmation --}}
                    <div>
                        <label class="block text-xs font-semibold text-slate-600 dark:text-slate-300 mb-1.5">
                            Nama Pelanggan {{ $customer_source === 'manual' ? '*' : '(Otomatis/Dapat Diedit)' }}
                        </label>
                        <input type="text" wire:model="customer_name" placeholder="Masukkan nama lengkap pelanggan..."
                               class="w-full px-3.5 py-2.5 bg-slate-50 dark:bg-slate-800/80 border border-slate-200 dark:border-slate-700 rounded-xl text-sm text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        @error('customer_name') <p class="mt-1.5 text-xs text-rose-500 font-medium">{{ $message }}</p> @enderror
                    </div>
                </div>

                {{-- Card: Data Perangkat (ONU & Router) --}}
                <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-6 shadow-xs space-y-5">
                    <div class="flex items-center gap-2 border-b border-slate-100 dark:border-slate-800 pb-3">
                        <span class="material-symbols-outlined notranslate text-indigo-600 dark:text-indigo-400" translate="no">router</span>
                        <h2 class="text-sm font-bold text-slate-900 dark:text-slate-100 uppercase tracking-wider">Data Perangkat (ONU & Router)</h2>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-semibold text-slate-600 dark:text-slate-300 mb-1.5">Serial Number ONU / ONT</label>
                            <input type="text" wire:model="onu_serial_number" placeholder="Contoh: ZTEG12345678"
                                   class="w-full px-3.5 py-2.5 bg-slate-50 dark:bg-slate-800/80 border border-slate-200 dark:border-slate-700 rounded-xl text-sm text-slate-900 dark:text-slate-100 font-mono focus:outline-none focus:ring-2 focus:ring-indigo-500">
                            @error('onu_serial_number') <p class="mt-1.5 text-xs text-rose-500 font-medium">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-slate-600 dark:text-slate-300 mb-1.5">MAC Address ONU</label>
                            <input type="text" wire:model="onu_mac_address" placeholder="Contoh: 00:1A:2B:3C:4D:5E"
                                   class="w-full px-3.5 py-2.5 bg-slate-50 dark:bg-slate-800/80 border border-slate-200 dark:border-slate-700 rounded-xl text-sm text-slate-900 dark:text-slate-100 font-mono focus:outline-none focus:ring-2 focus:ring-indigo-500">
                            @error('onu_mac_address') <p class="mt-1.5 text-xs text-rose-500 font-medium">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-slate-600 dark:text-slate-300 mb-1.5">Model Router / Akses Poin</label>
                            <input type="text" wire:model="router_model" placeholder="Contoh: Mikrotik hAP ac2 / TP-Link WR840N"
                                   class="w-full px-3.5 py-2.5 bg-slate-50 dark:bg-slate-800/80 border border-slate-200 dark:border-slate-700 rounded-xl text-sm text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                            @error('router_model') <p class="mt-1.5 text-xs text-rose-500 font-medium">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-slate-600 dark:text-slate-300 mb-1.5">Serial Number Router</label>
                            <input type="text" wire:model="router_serial_number" placeholder="Contoh: SN-RT-998822"
                                   class="w-full px-3.5 py-2.5 bg-slate-50 dark:bg-slate-800/80 border border-slate-200 dark:border-slate-700 rounded-xl text-sm text-slate-900 dark:text-slate-100 font-mono focus:outline-none focus:ring-2 focus:ring-indigo-500">
                            @error('router_serial_number') <p class="mt-1.5 text-xs text-rose-500 font-medium">{{ $message }}</p> @enderror
                        </div>
                    </div>
                </div>

                {{-- Card: Catatan Instalasi --}}
                <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-6 shadow-xs space-y-4">
                    <div class="flex items-center gap-2 border-b border-slate-100 dark:border-slate-800 pb-3">
                        <span class="material-symbols-outlined notranslate text-indigo-600 dark:text-indigo-400" translate="no">notes</span>
                        <h2 class="text-sm font-bold text-slate-900 dark:text-slate-100 uppercase tracking-wider">Catatan Khusus Instalasi</h2>
                    </div>
                    <div>
                        <textarea wire:model="notes" rows="3" placeholder="Tuliskan catatan teknis, instruksi rute kabel, nomor tiang/ODP, atau permintaan khusus pelanggan..."
                                  class="w-full px-3.5 py-2.5 bg-slate-50 dark:bg-slate-800/80 border border-slate-200 dark:border-slate-700 rounded-xl text-sm text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-indigo-500"></textarea>
                        @error('notes') <p class="mt-1.5 text-xs text-rose-500 font-medium">{{ $message }}</p> @enderror
                    </div>
                </div>
            </div>

            {{-- Kolom Kanan: Jadwal, Teknisi & Status --}}
            <div class="space-y-6">
                <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-6 shadow-xs space-y-5">
                    <div class="flex items-center gap-2 border-b border-slate-100 dark:border-slate-800 pb-3">
                        <span class="material-symbols-outlined notranslate text-indigo-600 dark:text-indigo-400" translate="no">event_available</span>
                        <h2 class="text-sm font-bold text-slate-900 dark:text-slate-100 uppercase tracking-wider">Penjadwalan & Petugas</h2>
                    </div>

                    {{-- Tanggal & Jam --}}
                    <div class="space-y-4">
                        <div>
                            <label class="block text-xs font-semibold text-slate-600 dark:text-slate-300 mb-1.5">Tanggal Jadwal Instalasi *</label>
                            <input type="date" wire:model="scheduled_date"
                                   class="w-full px-3.5 py-2.5 bg-slate-50 dark:bg-slate-800/80 border border-slate-200 dark:border-slate-700 rounded-xl text-sm text-slate-900 dark:text-slate-100 font-mono focus:outline-none focus:ring-2 focus:ring-indigo-500">
                            @error('scheduled_date') <p class="mt-1.5 text-xs text-rose-500 font-medium">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-slate-600 dark:text-slate-300 mb-1.5">Waktu / Jam *</label>
                            <input type="time" wire:model="scheduled_time"
                                   class="w-full px-3.5 py-2.5 bg-slate-50 dark:bg-slate-800/80 border border-slate-200 dark:border-slate-700 rounded-xl text-sm text-slate-900 dark:text-slate-100 font-mono focus:outline-none focus:ring-2 focus:ring-indigo-500">
                            @error('scheduled_time') <p class="mt-1.5 text-xs text-rose-500 font-medium">{{ $message }}</p> @enderror
                        </div>

                        {{-- Teknisi Lapangan --}}
                        <div>
                            <label class="block text-xs font-semibold text-slate-600 dark:text-slate-300 mb-1.5">Teknisi Bertugas</label>
                            <select wire:model="assigned_to"
                                    class="w-full px-3.5 py-2.5 bg-slate-50 dark:bg-slate-800/80 border border-slate-200 dark:border-slate-700 rounded-xl text-sm text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                                <option value="">-- Belum Ditugaskan --</option>
                                @foreach($technicians as $t)
                                    <option value="{{ $t->id }}">{{ $t->name }} ({{ $t->email }})</option>
                                @endforeach
                            </select>
                            @error('assigned_to') <p class="mt-1.5 text-xs text-rose-500 font-medium">{{ $message }}</p> @enderror
                        </div>

                        {{-- Prioritas --}}
                        <div>
                            <label class="block text-xs font-semibold text-slate-600 dark:text-slate-300 mb-1.5">Tingkat Prioritas</label>
                            <select wire:model="priority"
                                    class="w-full px-3.5 py-2.5 bg-slate-50 dark:bg-slate-800/80 border border-slate-200 dark:border-slate-700 rounded-xl text-sm text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                                <option value="low">Rendah (Low)</option>
                                <option value="medium">Normal / Standar (Medium)</option>
                                <option value="high">Tinggi (High)</option>
                                <option value="critical">Kritis / Mendesak (Critical)</option>
                            </select>
                            @error('priority') <p class="mt-1.5 text-xs text-rose-500 font-medium">{{ $message }}</p> @enderror
                        </div>

                        {{-- Status --}}
                        <div>
                            <label class="block text-xs font-semibold text-slate-600 dark:text-slate-300 mb-1.5">Status Pengerjaan</label>
                            <select wire:model="status"
                                    class="w-full px-3.5 py-2.5 bg-slate-50 dark:bg-slate-800/80 border border-slate-200 dark:border-slate-700 rounded-xl text-sm text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                                <option value="scheduled">Dijadwalkan (Scheduled)</option>
                                <option value="in_progress">Sedang Berlangsung (In Progress)</option>
                                <option value="completed">Selesai (Completed)</option>
                                <option value="canceled">Dibatalkan (Canceled)</option>
                            </select>
                            @error('status') <p class="mt-1.5 text-xs text-rose-500 font-medium">{{ $message }}</p> @enderror
                        </div>
                    </div>

                    {{-- Tombol Aksi --}}
                    <div class="pt-4 border-t border-slate-100 dark:border-slate-800 flex flex-col gap-2.5">
                        <button type="submit" 
                                class="w-full py-2.5 px-4 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-sm font-semibold transition-all shadow-sm hover:shadow flex items-center justify-center gap-2">
                            <span class="material-symbols-outlined notranslate text-lg" translate="no">save</span>
                            Simpan Perubahan
                        </button>
                        <a href="{{ route('crm.installations.index') }}" 
                           class="w-full py-2.5 px-4 bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 rounded-xl text-sm font-semibold text-center transition-all">
                            Batal
                        </a>
                    </div>
                </div>
            </div>

        </div>
    </form>
</div>
