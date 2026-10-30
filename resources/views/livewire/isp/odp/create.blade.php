<div class="space-y-6 pb-12 max-w-5xl">
    <!-- Success/Error Flash Message -->
    <div class="mb-3">
        @if(session()->has('success'))
            <x-feedback.alert variant="success">
                {{ session('success') }}
            </x-feedback.alert>
        @endif
        @if(session()->has('error'))
            <x-feedback.alert variant="danger">
                {{ session('error') }}
            </x-feedback.alert>
        @endif
        @if(session()->has('warning'))
            <x-feedback.alert variant="warning">
                {{ session('warning') }}
            </x-feedback.alert>
        @endif
        @if(session()->has('info'))
            <x-feedback.alert variant="info">
                {{ session('info') }}
            </x-feedback.alert>
        @endif
    </div>

    <div class="space-y-6">
        <x-admin.breadcrumbs :breadcrumbs="$this->breadcrumbs" />
        <div class="flex items-center gap-4">
            <a href="{{ route('isp.odps.index') }}" class="p-2 text-slate-500 hover:text-slate-700 dark:text-slate-300 rounded-lg hover:bg-slate-100 dark:bg-slate-800">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
            </a>
            <div>
                <h1 class="text-2xl font-bold text-slate-900 dark:text-slate-100">Buat ODP Baru</h1>
                <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Tambahkan ODP baru</p>
            </div>
        </div>

        <div class="bg-white dark:bg-slate-800 rounded-xl shadow-sm border border-slate-200 dark:border-slate-700 overflow-hidden"><div class="p-4">
            <form wire:submit.prevent="save" class="space-y-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Kode ODP</label>
                        <input type="text" wire:model="code" placeholder="Masukkan kode ODP" class="w-full px-4 py-2 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-primary-500 dark:bg-slate-900 dark:text-slate-100">
                        @error('code') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Nama ODP</label>
                        <input type="text" wire:model="name" placeholder="Masukkan nama ODP" class="w-full px-4 py-2 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-primary-500 dark:bg-slate-900 dark:text-slate-100">
                        @error('name') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">ODC Induk</label>
                        <select wire:model.live="odc_id" class="w-full px-4 py-2 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-primary-500 dark:bg-slate-900 dark:text-slate-100">
                            <option value="">Pilih ODC</option>
                            @foreach($odcs as $odc)
                                <option value="{{ $odc->id }}">{{ $odc->code }} - {{ $odc->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Cabang / Wilayah Kantor <span class="text-xs text-slate-400 font-normal">(Opsional)</span></label>
                        <select wire:model="branch_id" class="w-full px-4 py-2 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-primary-500 dark:bg-slate-900 dark:text-slate-100">
                            <option value="">-- Tanpa Cabang Khusus / Global --</option>
                            @foreach($branches as $b)
                                <option value="{{ $b->id }}">{{ $b->name }} ({{ $b->code }})</option>
                            @endforeach
                        </select>
                        @error('branch_id') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Kecamatan (District) <span class="text-xs text-slate-400 font-normal">(Pencocokan Reseller)</span></label>
                        <input type="text" wire:model="district" placeholder="Contoh: Girimarto / Purwantoro" class="w-full px-4 py-2 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-primary-500 dark:bg-slate-900 dark:text-slate-100">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Kelurahan / Desa (Village)</label>
                        <input type="text" wire:model="village" placeholder="Contoh: Tambakmerang" class="w-full px-4 py-2 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-primary-500 dark:bg-slate-900 dark:text-slate-100">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Kabupaten / Kota (Regency)</label>
                        <input type="text" wire:model="regency" placeholder="Contoh: Wonogiri" class="w-full px-4 py-2 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-primary-500 dark:bg-slate-900 dark:text-slate-100">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Provinsi</label>
                        <input type="text" wire:model="province" placeholder="Contoh: Jawa Tengah" class="w-full px-4 py-2 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-primary-500 dark:bg-slate-900 dark:text-slate-100">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Jumlah Port</label>
                        <input type="number" wire:model="port_count" placeholder="Masukkan jumlah port" class="w-full px-4 py-2 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-primary-500 dark:bg-slate-900 dark:text-slate-100">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Status</label>
                        <select wire:model="status" class="w-full px-4 py-2 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-primary-500 dark:bg-slate-900 dark:text-slate-100">
                            <option value="active">Aktif</option>
                            <option value="inactive">Nonaktif</option>
                        </select>
                        @error('status') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                    </div>
                    <div class="md:col-span-2">
                        <x-forms.coordinate-picker latModel="latitude" lngModel="longitude" />
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Alamat</label>
                    <textarea wire:model="address" rows="3" placeholder="Masukkan alamat ODP" class="w-full px-4 py-2 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-primary-500 dark:bg-slate-900 dark:text-slate-100"></textarea>
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Deskripsi</label>
                    <textarea wire:model="description" rows="3" placeholder="Masukkan deskripsi ODP" class="w-full px-4 py-2 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-primary-500 dark:bg-slate-900 dark:text-slate-100"></textarea>
                </div>
                <div class="flex items-center justify-end gap-4 pt-4 border-t border-slate-200 dark:border-slate-700">
                    <a href="{{ route('isp.odps.index') }}" class="px-6 py-2 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-700 dark:text-slate-300 rounded-lg text-sm font-medium hover:bg-slate-50 dark:bg-slate-900/50 transition-colors">
                        Batal
                    </a>
                    <button type="submit" wire:loading.attr="disabled" wire:target="save" class=" px-6 py-2.5 bg-indigo-600 hover:bg-indigo-700 disabled:opacity-50 disabled:cursor-not-allowed text-white rounded-xl text-sm font-semibold transition-all inline-flex items-center gap-2 cursor-pointer shadow-sm">
        <span wire:loading.remove wire:target="save" class="material-symbols-outlined notranslate" translate="no" style="font-size:18px">save</span>
        <span wire:loading wire:target="save" class="material-symbols-outlined notranslate animate-spin" translate="no" style="font-size:18px">autorenew</span>
        <span wire:loading.remove wire:target="save">Simpan</span>
        <span wire:loading wire:target="save">Menyimpan...</span>
    </button>
                </div>
            </form>
        </div></div>
    </div>
</div>
