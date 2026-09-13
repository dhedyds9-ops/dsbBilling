<div class="space-y-6">
    <x-admin.breadcrumbs :breadcrumbs="$this->breadcrumbs" />
    {{-- Header --}}
    <div class="flex items-center gap-4">
        <a href="{{ route('billing.payments.index') }}" class="p-2 text-slate-500 hover:text-slate-700 dark:text-slate-300 rounded-lg hover:bg-slate-100 dark:bg-slate-800">
            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
            </svg>
        </a>
        <div>
            <h1 class="text-2xl font-bold text-slate-900 dark:text-slate-100">Buat Payment Baru</h1>
            <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Tambahkan payment baru untuk pelanggan</p>
        </div>
    </div>

    <x-base.card>
        <form wire:submit.prevent="save" class="space-y-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Customer</label>
                    <select wire:model="customer_id" class="w-full px-4 py-2 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-primary-500 dark:bg-slate-900 dark:text-slate-100">
                        <option value="">Pilih Customer</option>
                        @foreach($customers as $customer)
                            <option value="{{ $customer->id }}">{{ $customer->name }}</option>
                        @endforeach
                    </select>
                    @error('customer_id') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Referensi</label>
                    <input type="text" wire:model="reference_number" class="w-full px-4 py-2 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-primary-500 dark:bg-slate-900 dark:text-slate-100">
                    @error('reference_number') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Amount</label>
                    <input type="number" wire:model="amount" min="0" step="0.01" class="w-full px-4 py-2 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-primary-500 dark:bg-slate-900 dark:text-slate-100">
                    @error('amount') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Currency</label>
                    <select wire:model="currency" class="w-full px-4 py-2 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-primary-500 dark:bg-slate-900 dark:text-slate-100">
                        <option value="IDR">IDR</option>
                        <option value="USD">USD</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Metode</label>
                    <select wire:model="method" class="w-full px-4 py-2 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-primary-500 dark:bg-slate-900 dark:text-slate-100">
                        <option value="bank_transfer">Bank Transfer</option>
                        <option value="cash">Cash</option>
                        <option value="credit_card">Credit Card</option>
                        <option value="e_wallet">E-Wallet</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Status</label>
                    <select wire:model="status" class="w-full px-4 py-2 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-primary-500 dark:bg-slate-900 dark:text-slate-100">
                        <option value="pending">Pending</option>
                        <option value="success">Success</option>
                        <option value="failed">Failed</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Tanggal Payment</label>
                    <input type="date" wire:model="paid_at" class="w-full px-4 py-2 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-primary-500 dark:bg-slate-900 dark:text-slate-100">
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Gateway</label>
                    <select wire:model="gateway" class="w-full px-4 py-2 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-primary-500 dark:bg-slate-900 dark:text-slate-100">
                        <option value="manual">Manual</option>
                        <option value="midtrans">Midtrans</option>
                        <option value="xendit">Xendit</option>
                    </select>
                </div>
            </div>

            @if($customer_id)
                <div>
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Invoice (Opsional)</label>
                    <div class="space-y-2">
                        @foreach($invoices as $invoice)
                            <label class="flex items-center gap-2 p-3 bg-slate-50 dark:bg-slate-900/50 rounded-lg cursor-pointer">
                                <input type="checkbox" wire:model="invoice_ids" value="{{ $invoice->id }}" class="w-4 h-4 text-primary-600 border-slate-300 dark:border-slate-600 rounded focus:ring-primary-500">
                                <span class="flex-1 text-slate-900 dark:text-slate-100">{{ $invoice->invoice_number }}</span>
                                <span class="text-sm text-slate-600 dark:text-slate-400">Rp {{ number_format($invoice->total_amount, 0, ',', '.') }}</span>
                                <span class="px-2 py-1 text-xs font-medium rounded-full 
                                    @if($invoice->status === 'paid') bg-green-100 dark:bg-green-900/50 text-green-600
                                    @else bg-yellow-100 dark:bg-yellow-900/50 text-yellow-600
                                    @endif
                                ">
                                    {{ ucfirst($invoice->status) }}
                                </span>
                            </label>
                        @endforeach
                    </div>
                </div>
            @endif

            <div class="flex items-center justify-end gap-4 pt-4 border-t border-slate-200 dark:border-slate-700">
                <a href="{{ route('billing.payments.index') }}" class="px-6 py-2 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-700 dark:text-slate-300 rounded-lg text-sm font-medium hover:bg-slate-50 dark:bg-slate-900/50 transition-colors">
                    Batal
                </a>
                <button type="submit" class="px-6 py-2 bg-primary-600 hover:bg-primary-700 text-white rounded-lg text-sm font-medium transition-colors">
                    Simpan
                </button>
            </div>
        </form>
    </x-base.card>
</div>
