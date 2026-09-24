<div class="space-y-6">
      <div class="px-4 py-3 bg-white dark:bg-slate-800 border-b border-slate-200 dark:border-slate-700 flex items-center justify-between flex-wrap gap-3 mb-4">
    <div class="flex items-center gap-3">
      <a href="{{ route('acs.devices.index') }}" class="p-1.5 rounded-md text-slate-500 dark:text-slate-400 hover:text-indigo-600 hover:bg-slate-100 dark:bg-slate-800 dark:hover:bg-slate-700 transition-colors" title="Kembali">
        <span class="material-symbols-outlined notranslate" translate="no" style="font-size:22px">arrow_back</span>
      </a>
      <div>
        <h1 class="text-lg font-bold text-slate-900 dark:text-slate-100">Edit Device</h1>
        <div class="text-xs text-slate-500 dark:text-slate-400">Edit data perangkat ACS</div>
      </div>
    </div>
  </div>

    <x-base.card>
        <form wire:submit="save" class="space-y-6">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                                <div>
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Pelanggan (Customer)</label>
                    <select wire:model="customer_service_id" class="w-full px-4 py-2 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 dark:bg-slate-900 dark:text-slate-100">
                        <option value="">-- Tanpa Pelanggan --</option>
                        @foreach($customerServices ?? [] as $cs)
                            <option value="{{ $cs->id }}">{{ $cs->service_number }} - {{ $cs->customer->name ?? 'Unknown' }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Serial Number</label>
                    <input type="text" wire:model="serial_number" disabled class="w-full bg-slate-100 cursor-not-allowed px-4 py-2 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 dark:bg-slate-900 dark:text-slate-100" placeholder="Masukkan serial number">
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">MAC Address</label>
                    <input type="text" wire:model="mac_address" disabled class="w-full bg-slate-100 cursor-not-allowed px-4 py-2 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 dark:bg-slate-900 dark:text-slate-100" placeholder="XX:XX:XX:XX:XX:XX">
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Vendor</label>
                    <select wire:model="vendor_id" class="w-full px-4 py-2 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 dark:bg-slate-900 dark:text-slate-100">
                        <option value="">Pilih Vendor</option>
                        @foreach($vendors ?? [] as $vendor)
                            <option value="{{ $vendor->id }}">{{ $vendor->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Model</label>
                    <input type="text" wire:model="model" class="w-full px-4 py-2 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 dark:bg-slate-900 dark:text-slate-100" placeholder="Model perangkat">
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Status</label>
                    <select wire:model="status" disabled class="w-full bg-slate-100 cursor-not-allowed px-4 py-2 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 dark:bg-slate-900 dark:text-slate-100">
                        <option value="offline">Offline</option>
                        <option value="online">Online</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">IP Address</label>
                    <input type="text" wire:model="ip_address" disabled class="w-full bg-slate-100 cursor-not-allowed px-4 py-2 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 dark:bg-slate-900 dark:text-slate-100" placeholder="192.168.1.1">
                </div>
            </div>

                        

            <div class="flex justify-end gap-4 pt-4 border-t border-slate-200 dark:border-slate-700">
                <a href="{{ route('acs.devices.show', $deviceId) }}" class="px-4 py-2 text-sm font-medium text-slate-700 dark:text-slate-300 bg-slate-100 dark:bg-slate-800 rounded-lg hover:bg-slate-200 transition-colors">Kembali</a>
                <button type="submit" class="px-4 py-2 text-sm font-medium text-white bg-indigo-600 rounded-lg hover:bg-indigo-700 transition-colors">Simpan</button>
            </div>
        </form>
    </x-base.card>
</div>

