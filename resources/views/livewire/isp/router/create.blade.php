<div class="max-w-7xl mx-auto p-3">
    <!-- Breadcrumb -->

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
        @if ($errors->any())
            <x-feedback.alert variant="danger">
                <strong>Peringatan Validasi:</strong>
                <ul class="list-disc pl-5 mt-1">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </x-feedback.alert>
        @endif
    </div>

    @section('page_title')
    <div class="flex items-center gap-2">
        <a href="{{ route('isp.routers.index') }}" class="w-8 h-8 rounded-full bg-slate-100 dark:bg-slate-800 flex items-center justify-center text-slate-600 dark:text-slate-400 hover:bg-slate-200 dark:hover:bg-slate-700 transition-colors" title="Kembali">
            <span class="material-symbols-outlined notranslate" translate="no" style="font-size:18px">arrow_back</span>
        </a>
        <div class="w-8 h-8 rounded-full bg-blue-100 dark:bg-blue-900/50 flex items-center justify-center text-blue-600 dark:text-blue-400 font-bold text-sm">
            <span class="material-symbols-outlined notranslate" translate="no" style="font-size:18px">add</span>
        </div>
        <span class="text-lg">Tambah Mikrotik (Nas)</span>
    </div>
@endsection

<div class="space-y-6 pb-10">
    <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-sm border border-slate-200 dark:border-slate-700 overflow-hidden">
        <div class="p-6 border-b border-slate-200 dark:border-slate-700 bg-slate-50/50 dark:bg-slate-800/50">
            <h2 class="text-lg font-bold text-slate-900 dark:text-white">Informasi Perangkat</h2>
            <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">Lengkapi informasi dasar perangkat router Mikrotik Anda.</p>
        </div>

        <form wire:submit.prevent="save" class="p-6 space-y-8">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Kode Router <span class="text-red-500">*</span></label>
                    <input type="text" wire:model="code" placeholder="Misal: RT-JKT-01" class="w-full px-4 py-2.5 bg-white dark:bg-slate-900 dark:bg-slate-900/50 border border-slate-200 dark:border-slate-700 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 dark:text-slate-100 dark:bg-slate-900 dark:text-slate-100">
                    @error('code') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                </div>
                <div>
                    <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Nama Router <span class="text-red-500">*</span></label>
                    <input type="text" wire:model="name" placeholder="Misal: Router Utama Jakarta" class="w-full px-4 py-2.5 bg-white dark:bg-slate-900 dark:bg-slate-900/50 border border-slate-200 dark:border-slate-700 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 dark:text-slate-100 dark:bg-slate-900 dark:text-slate-100">
                    @error('name') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                </div>
                <div>
                    <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Lokasi POP <span class="text-slate-400 font-normal text-xs ml-1">(Opsional)</span></label>
                    <select wire:model="pop_id" class="w-full px-4 py-2.5 bg-white dark:bg-slate-900 dark:bg-slate-900/50 border border-slate-200 dark:border-slate-700 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 dark:text-slate-100 dark:bg-slate-900 dark:text-slate-100">
                        <option value="">Pilih Lokasi POP</option>
                        @foreach($pops as $pop)
                            <option value="{{ $pop->id }}">{{ $pop->code }} - {{ $pop->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Vendor <span class="text-slate-400 font-normal text-xs ml-1">(Opsional)</span></label>
                    <select wire:model="vendor_id" class="w-full px-4 py-2.5 bg-white dark:bg-slate-900 dark:bg-slate-900/50 border border-slate-200 dark:border-slate-700 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 dark:text-slate-100 dark:bg-slate-900 dark:text-slate-100">
                        <option value="">Pilih Vendor</option>
                        @foreach($vendors as $vendor)
                            <option value="{{ $vendor->id }}">{{ $vendor->code }} - {{ $vendor->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Model / Tipe <span class="text-slate-400 font-normal text-xs ml-1">(Opsional)</span></label>
                    <input type="text" wire:model="model" placeholder="Misal: CCR1036-8G-2S+" class="w-full px-4 py-2.5 bg-white dark:bg-slate-900 dark:bg-slate-900/50 border border-slate-200 dark:border-slate-700 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 dark:text-slate-100 dark:bg-slate-900 dark:text-slate-100">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Serial Number <span class="text-slate-400 font-normal text-xs ml-1">(Opsional)</span></label>
                    <input type="text" wire:model="serial_number" placeholder="S/N Perangkat" class="w-full px-4 py-2.5 bg-white dark:bg-slate-900 dark:bg-slate-900/50 border border-slate-200 dark:border-slate-700 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 dark:text-slate-100 dark:bg-slate-900 dark:text-slate-100">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Versi RouterOS <span class="text-slate-400 font-normal text-xs ml-1">(Opsional)</span></label>
                    <input type="text" wire:model="routeros_version" placeholder="Misal: v7.11.2" class="w-full px-4 py-2.5 bg-white dark:bg-slate-900 dark:bg-slate-900/50 border border-slate-200 dark:border-slate-700 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 dark:text-slate-100 dark:bg-slate-900 dark:text-slate-100">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Status <span class="text-red-500">*</span></label>
                    <select wire:model="status" class="w-full px-4 py-2.5 bg-white dark:bg-slate-900 dark:bg-slate-900/50 border border-slate-200 dark:border-slate-700 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 dark:text-slate-100 dark:bg-slate-900 dark:text-slate-100">
                        <option value="active">Aktif beroperasi</option>
                        <option value="inactive">Nonaktif</option>
                    </select>
                    @error('status') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                </div>
                <div class="md:col-span-2">
                    <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Deskripsi / Catatan <span class="text-slate-400 font-normal text-xs ml-1">(Opsional)</span></label>
                    <textarea wire:model="description" rows="3" placeholder="Informasi tambahan mengenai router ini..." class="w-full px-4 py-2.5 bg-white dark:bg-slate-900 dark:bg-slate-900/50 border border-slate-200 dark:border-slate-700 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 dark:text-slate-100 dark:bg-slate-900 dark:text-slate-100"></textarea>
                </div>
            </div>

            <hr class="border-slate-200 dark:border-slate-700">
            
            <div>
                <h3 class="text-sm font-bold text-slate-900 dark:text-white mb-4">Pengaturan Koneksi API</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">IP Address <span class="text-red-500">*</span></label>
                        <input type="text" wire:model="ip_address" placeholder="192.168.x.x atau IP Publik" class="w-full px-4 py-2.5 bg-white dark:bg-slate-900 dark:bg-slate-900/50 border border-slate-200 dark:border-slate-700 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 dark:text-slate-100 font-mono dark:bg-slate-900 dark:text-slate-100">
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">API Port <span class="text-red-500">*</span></label>
                            <input type="number" wire:model="api_port" placeholder="8728" class="w-full px-4 py-2.5 bg-white dark:bg-slate-900 dark:bg-slate-900/50 border border-slate-200 dark:border-slate-700 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 dark:text-slate-100 font-mono dark:bg-slate-900 dark:text-slate-100">
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Timeout (detik) <span class="text-slate-400 font-normal text-xs ml-1">(Opsional)</span></label>
                            <input type="number" wire:model="timeout" placeholder="30" class="w-full px-4 py-2.5 bg-white dark:bg-slate-900 dark:bg-slate-900/50 border border-slate-200 dark:border-slate-700 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 dark:text-slate-100 dark:bg-slate-900 dark:text-slate-100">
                        </div>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">API Username <span class="text-red-500">*</span></label>
                        <input type="text" wire:model="username" placeholder="Kosongkan untuk auto-generate" class="w-full px-4 py-2.5 bg-white dark:bg-slate-900 dark:bg-slate-900/50 border border-slate-200 dark:border-slate-700 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 dark:text-slate-100 dark:bg-slate-900 dark:text-slate-100">
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">API Password <span class="text-red-500">*</span></label>
                        <input type="password" wire:model="password" placeholder="Kosongkan untuk auto-generate" class="w-full px-4 py-2.5 bg-white dark:bg-slate-900 dark:bg-slate-900/50 border border-slate-200 dark:border-slate-700 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 dark:text-slate-100 dark:bg-slate-900 dark:text-slate-100">
                    </div>

                    <div class="md:col-span-2 pt-2">
                        <label class="flex items-center gap-3 p-4 border border-slate-200 dark:border-slate-700 rounded-xl bg-slate-50 dark:bg-slate-800/50 cursor-pointer hover:bg-slate-100 dark:bg-slate-800 dark:hover:bg-slate-800 transition-colors">
                            <input type="checkbox" wire:model="use_ssl" class="w-5 h-5 rounded border-slate-300 text-blue-600 focus:ring-blue-500 dark:border-slate-600 dark:bg-slate-90 dark:bg-slate-900 dark:text-slate-1000">
                            <div>
                                <div class="text-sm font-bold text-slate-900 dark:text-white">Gunakan SSL/TLS</div>
                                <div class="text-xs text-slate-500 dark:text-slate-400">Aktifkan enkripsi jika router Anda mendukung API-SSL (Port 8729)</div>
                            </div>
                        </label>
                    </div>
                </div>
            </div>

            <div class="flex items-center justify-end gap-3 pt-6 border-t border-slate-200 dark:border-slate-700">
                <a href="{{ route('isp.routers.index') }}" class="px-6 py-2.5 bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-600 text-slate-700 dark:text-slate-300 rounded-xl text-sm font-semibold hover:bg-slate-50 dark:bg-slate-900/50 dark:hover:bg-slate-700 transition-colors">
                    Batal
                </a>
                <button type="submit" class="px-6 py-2.5 bg-blue-600 hover:bg-blue-700 text-white rounded-xl text-sm font-semibold shadow-sm flex items-center gap-2 transition-colors">
                    <span class="material-symbols-outlined notranslate text-[18px]" translate="no">save</span>
                    Simpan Data
                </button>
            </div>
        </form>
    </div>
</div>
</div>



