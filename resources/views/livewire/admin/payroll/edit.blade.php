<div class="max-w-4xl space-y-6 pb-12 sm:px-6 lg:px-8">
    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-xl sm:rounded-lg p-4">
        <div class="flex justify-between items-center mb-6">
            <h2 class="text-2xl font-semibold text-gray-800 dark:text-gray-200">Edit Slip Gaji - {{ $payroll->employee?->name ?? 'Karyawan' }}</h2>
            <a href="{{ route('admin.payroll.index') }}" class="text-indigo-600 dark:text-indigo-400 hover:text-indigo-900">&larr; Kembali</a>
        </div>

        <div class="mb-4 p-4 bg-gray-50 dark:bg-gray-700 rounded-lg">
            <p class="text-sm text-gray-600 dark:text-gray-300">
                <strong>Periode:</strong> 
                @if($payroll->period_start && $payroll->period_end)
                    {{ \Carbon\Carbon::parse($payroll->period_start)->translatedFormat('d M Y') }} - {{ \Carbon\Carbon::parse($payroll->period_end)->translatedFormat('d M Y') }}
                @else
                    {{ \Carbon\Carbon::createFromDate($payroll->period_year, $payroll->period_month, 1)->translatedFormat('F Y') }}
                @endif
            </p>
        </div>

        <form wire:submit.prevent="save">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Gaji Pokok</label>
                    <input type="number" step="0.01" wire:model.live.debounce.300ms="base_salary" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    @error('base_salary') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Tunjangan (Bonus, dll)</label>
                    <input type="number" step="0.01" wire:model.live.debounce.300ms="allowances" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    @error('allowances') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Potongan Kasbon / Pinjaman</label>
                    <input type="number" step="0.01" wire:model.live.debounce.300ms="deductions" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500" placeholder="0">
                    @error('deductions') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Penerimaan Bersih (Otomatis)</label>
                    <input type="number" readonly wire:model="net_salary" class="mt-1 block w-full rounded-md border-gray-300 bg-gray-100 dark:border-gray-600 dark:bg-gray-600 dark:text-white shadow-sm font-bold text-green-600">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Status Pembayaran</label>
                    <select wire:model="status" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        <option value="draft">Draft (Belum Dibayar)</option>
                        <option value="paid">Paid (Sudah Dibayar)</option>
                    </select>
                    @error('status') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                </div>
            </div>

            <div class="mb-6">
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Keterangan Kasbon / Catatan Tambahan</label>
                <textarea wire:model="notes" rows="3" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500" placeholder="Contoh: Kasbon tgl 12/10/2026 Rp 100.000"></textarea>
                @error('notes') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
            </div>

            <div class="flex items-center justify-end">
                <button type="submit" wire:loading.attr="disabled" wire:target="save" class=" px-6 py-2.5 bg-indigo-600 hover:bg-indigo-700 disabled:opacity-50 disabled:cursor-not-allowed text-white rounded-xl text-sm font-semibold transition-all inline-flex items-center gap-2 cursor-pointer shadow-sm">
        <span wire:loading.remove wire:target="save" class="material-symbols-outlined notranslate" translate="no" style="font-size:18px">save</span>
        <span wire:loading wire:target="save" class="material-symbols-outlined notranslate animate-spin" translate="no" style="font-size:18px">autorenew</span>
        <span wire:loading.remove wire:target="save">Simpan Perubahan</span>
        <span wire:loading wire:target="save">Menyimpan...</span>
    </button>
            </div>
        </form>
    </div>
</div>
