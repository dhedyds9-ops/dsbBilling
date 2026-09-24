<div class="space-y-6">
    <div class="sm:flex sm:items-center sm:justify-between">
        <div>
            <h1 class="text-xl font-bold text-slate-900 dark:text-slate-100">Edit IP Pool</h1>
            <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Update konfigurasi IP Pool yang sudah ada.</p>
        </div>
    </div>

    <form wire:submit="save" class="bg-white dark:bg-slate-800 shadow-sm rounded-2xl border border-slate-200 dark:border-slate-700">
        <div class="p-6 space-y-6 sm:p-8">
            <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                <div class="sm:col-span-2">
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300">Nama Pool <span class="text-red-500">*</span></label>
                    <input wire:model="form.name" type="text" class="mt-1 block w-full rounded-xl border-slate-300 dark:border-slate-600 dark:bg-slate-900 shadow-sm focus:border-blue-500 focus:ring-primary-500 sm:text-sm dark:bg-slate-900 dark:text-slate-100" placeholder="Contoh: PPPoE-HOME-100">
                    @error('form.name') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300">Kode Pool <span class="text-red-500">*</span></label>
                    <input wire:model="form.code" type="text" class="mt-1 block w-full rounded-xl border-slate-300 dark:border-slate-600 dark:bg-slate-900 shadow-sm focus:border-blue-500 focus:ring-primary-500 sm:text-sm font-mono uppercase dark:bg-slate-900 dark:text-slate-100" placeholder="Contoh: POOL_HOME100">
                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">Kode unik, harus berbeda setiap pool.</p>
                    @error('form.code') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300">POP / Lokasi</label>
                    <select wire:model="form.pop_id" class="mt-1 block w-full rounded-xl border-slate-300 dark:border-slate-600 dark:bg-slate-900 shadow-sm focus:border-blue-500 focus:ring-primary-500 sm:text-sm dark:bg-slate-900 dark:text-slate-100">
                        <option value="">-- Pilih POP (Opsional) --</option>
                        @foreach($pops as $pop)
                            <option value="{{ $pop->id }}">{{ $pop->name }} @if($pop->router) — ({{ $pop->router->name }}) @endif</option>
                        @endforeach
                    </select>
                    @error('form.pop_id') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300">Network Address</label>
                    <input wire:model="form.network" type="text" class="mt-1 block w-full rounded-xl border-slate-300 dark:border-slate-600 dark:bg-slate-900 shadow-sm focus:border-blue-500 focus:ring-primary-500 sm:text-sm font-mono dark:bg-slate-900 dark:text-slate-100" placeholder="Contoh: 192.168.100.0">
                    @error('form.network') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300">Gateway <span class="text-red-500">*</span></label>
                    <input wire:model="form.gateway" type="text" class="mt-1 block w-full rounded-xl border-slate-300 dark:border-slate-600 dark:bg-slate-900 shadow-sm focus:border-blue-500 focus:ring-primary-500 sm:text-sm font-mono dark:bg-slate-900 dark:text-slate-100" placeholder="Contoh: 192.168.100.1">
                    @error('form.gateway') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300">Start IP <span class="text-red-500">*</span></label>
                    <input wire:model="form.start_ip" type="text" class="mt-1 block w-full rounded-xl border-slate-300 dark:border-slate-600 dark:bg-slate-900 shadow-sm focus:border-blue-500 focus:ring-primary-500 sm:text-sm font-mono dark:bg-slate-900 dark:text-slate-100" placeholder="Contoh: 192.168.100.2">
                    @error('form.start_ip') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300">End IP <span class="text-red-500">*</span></label>
                    <input wire:model="form.end_ip" type="text" class="mt-1 block w-full rounded-xl border-slate-300 dark:border-slate-600 dark:bg-slate-900 shadow-sm focus:border-blue-500 focus:ring-primary-500 sm:text-sm font-mono dark:bg-slate-900 dark:text-slate-100" placeholder="Contoh: 192.168.100.254">
                    @error('form.end_ip') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                </div>

                <div class="sm:col-span-2">
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300">DNS Servers</label>
                    <input wire:model="form.dns" type="text" class="mt-1 block w-full rounded-xl border-slate-300 dark:border-slate-600 dark:bg-slate-900 shadow-sm focus:border-blue-500 focus:ring-primary-500 sm:text-sm font-mono dark:bg-slate-900 dark:text-slate-100" placeholder="Contoh: 8.8.8.8,8.8.4.4">
                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">Pisahkan dengan koma untuk DNS primer dan sekunder.</p>
                    @error('form.dns') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300">Status <span class="text-red-500">*</span></label>
                    <select wire:model="form.status" class="mt-1 block w-full rounded-xl border-slate-300 dark:border-slate-600 dark:bg-slate-900 shadow-sm focus:border-blue-500 focus:ring-primary-500 sm:text-sm dark:bg-slate-900 dark:text-slate-100">
                        <option value="active">Aktif</option>
                        <option value="inactive">Non Aktif</option>
                    </select>
                    <p class="text-xs text-amber-600 dark:text-amber-400 mt-1">Hati-hati! Perubahan range IP bisa berdampak pada pelanggan yang sudah aktif.</p>
                    @error('form.status') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                </div>
            </div>
        </div>
        
        <div class="px-6 py-4 bg-slate-50 dark:bg-slate-800/50 border-t border-slate-200 dark:border-slate-700 flex justify-end gap-3 rounded-b-2xl">
            <a href="{{ route('isp.ip-pools.index') }}" class="px-4 py-2 border border-slate-300 dark:border-slate-600 rounded-xl shadow-sm text-sm font-medium text-slate-700 dark:text-slate-200 bg-white dark:bg-slate-800 hover:bg-slate-50 dark:bg-slate-900/50 dark:hover:bg-slate-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500">Batal</a>
            <button type="submit" class="px-4 py-2 border border-transparent rounded-xl shadow-sm text-sm font-medium text-white bg-primary-600 hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 inline-flex items-center">
                <svg class="-ml-1 mr-2 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/></svg>
                <span wire:loading.remove wire:target="save">Simpan Perubahan</span>
                <span wire:loading wire:target="save">Menyimpan...</span>
            </button>
        </div>
    </form>
</div>







