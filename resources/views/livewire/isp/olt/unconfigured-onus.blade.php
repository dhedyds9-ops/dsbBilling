<div>
    <div class="flex justify-between items-center mb-4">
        <h3 class="text-lg font-medium text-slate-900 dark:text-slate-100">ONU Belum Terkonfigurasi (Unconfigured)</h3>
        <button wire:click="discover" wire:loading.attr="disabled" class="bg-indigo-600 text-white px-4 py-2 rounded-md hover:bg-indigo-700 transition">
            <span wire:loading.remove wire:target="discover">
                <i class="fas fa-search mr-2"></i> Scan OLT
            </span>
            <span wire:loading wire:target="discover">
                <i class="fas fa-spinner fa-spin mr-2"></i> Scanning...
            </span>
        </button>
    </div>

    @if($errorMessage)
        <div class="bg-red-100 dark:bg-red-900/50 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4">
            {{ $errorMessage }}
        </div>
    @endif

    @if($successMessage)
        <div class="bg-green-100 dark:bg-green-900/50 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4">
            {{ $successMessage }}
        </div>
    @endif

        <x-admin.table-card>
        <table class="w-full text-left text-sm text-slate-600 dark:text-slate-300">
            <thead class="bg-slate-50 dark:bg-slate-800/50 border-b border-slate-200 dark:bg-slate-800/50 dark:border-slate-700">
                <tr class="text-slate-500 dark:text-slate-400">
                    <th class="p-3 font-semibold">PON Port</th>
                    <th class="p-3 font-semibold">Serial Number</th>
                    <th class="p-3 font-semibold">Vendor OUI</th>
                    <th class="p-3 font-semibold text-right">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 dark:divide-slate-700/60">
                @forelse($unconfiguredOnus as $onu)
                    <tr class="hover:bg-slate-50 dark:bg-slate-900/50 dark:hover:bg-slate-800/50 transition-colors">
                        <td class="p-3  whitespace-nowrap text-sm text-slate-900 dark:text-slate-100">{{ $onu['pon_port'] }}</td>
                        <td class="p-3  whitespace-nowrap text-sm text-slate-900 dark:text-slate-100 font-mono">{{ $onu['serial_number'] }}</td>
                        <td class="p-3  whitespace-nowrap text-sm text-slate-500 dark:text-slate-400">{{ $onu['vendor_oui'] }}</td>
                        <td class="p-3  whitespace-nowrap text-right text-sm font-medium">
                            <button wire:click="openProvisionModal('{{ $onu['serial_number'] }}', {{ $onu['pon_port'] }}, '{{ $onu['vendor_oui'] }}', '{{ $onu['onu_id'] ?? '' }}')" class="text-indigo-600 hover:text-indigo-900">
                                Provision
                            </button>
                        </td>
                    </tr>
                @empty
                    <tr class="hover:bg-slate-50 dark:bg-slate-900/50 dark:hover:bg-slate-800/50 transition-colors">
                        <td colspan="4" class="p-3  whitespace-nowrap text-sm text-slate-500 dark:text-slate-400 text-center">
                            @if($isLoading)
                                Sedang mengambil data dari OLT...
                            @else
                                Klik "Scan OLT" untuk mencari ONU yang belum terdaftar.
                            @endif
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </x-admin.table-card>

    <!-- Provisioning Modal -->
    @if($showProvisionModal)
    <div class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 bg-slate-500 bg-opacity-75 transition-opacity" wire:click="closeProvisionModal"></div>
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
            <div class="inline-block align-bottom bg-white dark:bg-slate-800 rounded-lg text-left overflow-x-auto shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                <form wire:submit.prevent="submitProvision">
                    <div class="bg-white dark:bg-slate-800 px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                        <h3 class="text-lg leading-6 font-medium text-slate-900 dark:text-slate-100" id="modal-title">Provision ONU: {{ $provisionData['serial_number'] }}</h3>
                        <div class="mt-4 space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-slate-700 dark:text-slate-300">Pilih Layanan Pelanggan</label>
                                <select wire:model="provisionData.customer_service_id" class="mt-1 block w-full border-slate-300 dark:border-slate-600 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm dark:bg-slate-900 dark:text-slate-100" required>
                                    <option value="">-- Pilih Layanan --</option>
                                    @foreach($this->customerServices as $cs)
                                        <option value="{{ $cs->id }}">{{ $cs->customer->name ?? 'Unknown' }} - {{ $cs->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-slate-700 dark:text-slate-300">Nama ONU</label>
                                <input type="text" wire:model="provisionData.name" class="mt-1 block w-full border-slate-300 dark:border-slate-600 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm dark:bg-slate-900 dark:text-slate-100" required>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-slate-700 dark:text-slate-300">Service Profile (OLT Line Profile)</label>
                                <input type="text" wire:model="provisionData.profile" class="mt-1 block w-full border-slate-300 dark:border-slate-600 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm dark:bg-slate-900 dark:text-slate-100" required>
                                <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">Contoh: default- 100M- 50M (Sesuai settingan di OLT)</p>
                            </div>
                        </div>
                    </div>
                    <div class="bg-slate-50 dark:bg-slate-900/50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                        <button type="submit" wire:loading.attr="disabled" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-indigo-600 text-base font-medium text-white hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:ml-3 sm:w-auto sm:text-sm">
                            <span wire:loading.remove wire:target="submitProvision">Provision Sekarang</span>
                            <span wire:loading wire:target="submitProvision">Memproses...</span>
                        </button>
                        <button type="button" wire:click="closeProvisionModal" class="mt-3 w-full inline-flex justify-center rounded-md border border-slate-300 dark:border-slate-600 shadow-sm px-4 py-2 bg-white dark:bg-slate-800 text-base font-medium text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:bg-slate-900/50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                            Batal
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @endif
</div>







