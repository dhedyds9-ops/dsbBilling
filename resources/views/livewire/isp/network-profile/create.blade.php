<div class="space-y-6">
    <div class="sm:flex sm:items-center sm:justify-between">
        <div>
            <h1 class="text-xl font-bold text-slate-900 dark:text-slate-100">Tambah Network Profile</h1>
            <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Buat profile jaringan baru untuk mapping VLAN dan MikroTik Router.</p>
        </div>
    </div>

    <form wire:submit="save" class="bg-white dark:bg-slate-800 shadow-sm rounded-2xl border border-slate-200 dark:border-slate-700">
        <div class="p-6 space-y-6 sm:p-8">
            <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                <div class="sm:col-span-2">
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300">Nama Profile <span class="text-red-500">*</span></label>
                    <input wire:model="name" type="text" class="mt-1 block w-full rounded-xl border-slate-300 dark:border-slate-600 dark:bg-slate-900 shadow-sm focus:border-blue-500 focus:ring-primary-500 sm:text-sm dark:bg-slate-900 dark:text-slate-100" placeholder="Contoh: PPPoE-POP-A">
                    @error('name') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                </div>

                <div class="sm:col-span-2">
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300">Deskripsi</label>
                    <textarea wire:model="description" rows="2" class="mt-1 block w-full rounded-xl border-slate-300 dark:border-slate-600 dark:bg-slate-900 shadow-sm focus:border-blue-500 focus:ring-primary-500 sm:text-sm dark:bg-slate-900 dark:text-slate-100" placeholder="Opsional..."></textarea>
                    @error('description') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300">Tipe Layanan <span class="text-red-500">*</span></label>
                    <select wire:model="type" class="mt-1 block w-full rounded-xl border-slate-300 dark:border-slate-600 dark:bg-slate-900 shadow-sm focus:border-blue-500 focus:ring-primary-500 sm:text-sm dark:bg-slate-900 dark:text-slate-100">
                        <option value="pppoe">PPPoE</option>
                        <option value="hotspot">Hotspot</option>
                        <option value="static">Static IP</option>
                    </select>
                    @error('type') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300">VLAN ID <span class="text-red-500">*</span></label>
                    <input wire:model="vlan_id" type="number" min="1" max="4094" class="mt-1 block w-full rounded-xl border-slate-300 dark:border-slate-600 dark:bg-slate-900 shadow-sm focus:border-blue-500 focus:ring-primary-500 sm:text-sm dark:bg-slate-900 dark:text-slate-100" placeholder="Contoh: 100">
                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">VLAN ID yang akan di-push ke ONU dan dibuat otomatis di MikroTik.</p>
                    @error('vlan_id') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                </div>

                <div class="sm:col-span-2">
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300">Target Router <span class="text-red-500">*</span></label>
                    <select wire:model="router_id" class="mt-1 block w-full rounded-xl border-slate-300 dark:border-slate-600 dark:bg-slate-900 shadow-sm focus:border-blue-500 focus:ring-primary-500 sm:text-sm dark:bg-slate-900 dark:text-slate-100">
                        <option value="">-- Pilih Router --</option>
                        @foreach($routers as $r)
                            <option value="{{ $r->id }}">{{ $r->name }} ({{ $r->ip_address }})</option>
                        @endforeach
                    </select>
                    @error('router_id') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                </div>
            </div>
        </div>
        
        <div class="px-6 py-4 bg-slate-50 dark:bg-slate-800/50 border-t border-slate-200 dark:border-slate-700 flex justify-end gap-3 rounded-b-2xl">
            <a href="{{ route('isp.network-profiles.index') }}" class="px-4 py-2 border border-slate-300 dark:border-slate-600 rounded-xl shadow-sm text-sm font-medium text-slate-700 dark:text-slate-300 bg-white dark:bg-slate-800 hover:bg-slate-50 dark:bg-slate-900/50 dark:hover:bg-slate-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500">Batal</a>
            <button type="submit" class="px-4 py-2 border border-transparent rounded-xl shadow-sm text-sm font-medium text-white bg-primary-600 hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 inline-flex items-center">
                <span wire:loading.remove wire:target="save">Simpan Profile</span>
                <span wire:loading wire:target="save">Menyimpan...</span>
            </button>
        </div>
    </form>
</div>







