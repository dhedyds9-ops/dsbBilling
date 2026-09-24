<div class="space-y-6">
    <x-admin.page-header title="Riwayat Transaksi Payment Gateway" subtitle="Log pembayaran dari pihak ketiga (Midtrans- Xendit- Tripay- dll)">
        <x-slot name="actions">
            <x-base.button href="{{ route('pengaturan.payment-gateway') }}" variant="secondary">
                <x-icon name="arrow-left" class="w-4 h-4 mr-2" />
                Kembali
            </x-base.button>
        </x-slot>
    </x-admin.page-header>

    @if (session()->has('success'))
        <div class="p-4 bg-emerald-50 dark:bg-emerald-900/30 text-emerald-700 rounded-lg border border-emerald-200">
            {{ session('success') }}
        </div>
    @endif

    <x-base.card>
        <div class="p-4 border-b border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800/50/50 flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div class="flex items-center gap-2 flex-wrap">
                <div class="relative">
                    <svg class="w-4 h-4 text-slate-400 absolute left-3 top-1/2 -translate-y-1/2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                    <input type="text" wire:model.live.debounce.300ms="search" placeholder="Cari ID Transaksi / Invoice..." class="pl-9 pr-4 py-2 text-sm border border-slate-300 dark:border-slate-600 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-blue-500 bg-white dark:bg-slate-800 w-64 dark:bg-slate-900 dark:text-slate-100">
                </div>
                <select wire:model.live="statusFilter" class="px-3 py-2 text-sm border border-slate-300 dark:border-slate-600 rounded-lg focus:ring-2 focus:ring-primary-500 bg-white dark:bg-slate-90 dark:bg-slate-900 dark:text-slate-1000">
                    <option value="">Semua Status</option>
                    <option value="pending">Pending</option>
                    <option value="settlement">Settlement / Paid</option>
                    <option value="expire">Expire / Failed</option>
                </select>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-slate-600 dark:text-slate-300">
                <thead class="bg-slate-50 dark:bg-slate-800/50 border-b border-slate-200 dark:bg-slate-800/50 dark:border-slate-700">
                    <tr class="text-slate-500 dark:text-slate-400">
                        <th class="p-3 font-semibold">Waktu</th>
                        <th class="p-3 font-semibold">ID Transaksi (Gateway)</th>
                        <th class="p-3 font-semibold">No. Invoice</th>
                        <th class="p-3 font-semibold">Provider</th>
                        <th class="p-3 font-semibold text-right">Nominal</th>
                        <th class="p-3 font-semibold text-center">Status</th>
                        <th class="p-3 font-semibold text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-700/60">
                    @forelse($filteredPayments as $payment)
                        <tr class="hover:bg-slate-50 dark:bg-slate-800/50 transition-colors">
                            <td class="p-3  text-slate-500 dark:text-slate-400">{{ $payment['date'] }}</td>
                            <td class="p-3  font-mono text-slate-900 dark:text-slate-100">{{ $payment['id'] }}</td>
                            <td class="p-3  font-medium text-slate-700 dark:text-slate-300">{{ $payment['invoice_no'] }}</td>
                            <td class="p-3">
                                <x-ui.badge variant="neutral">
                                    {{ $payment['gateway'] }}
                                </x-ui.badge>
                            </td>
                            <td class="p-3  text-right font-mono font-medium text-slate-900 dark:text-slate-100">
                                Rp {{ number_format($payment['amount']- 0 ?? '-'- '.') }}
                            </td>
                            <td class="p-3  text-center">
                                @if($payment['status'] === 'settlement')
                                    <x-ui.badge variant="success">Settlement</x-ui.badge>
                                @elseif($payment['status'] === 'expire')
                                    <x-ui.badge variant="danger">Expired</x-ui.badge>
                                @else
                                    <x-ui.badge variant="warning">Pending</x-ui.badge>
                                @endif
                            </td>
                            <td class="p-3  text-center">
                                <button wire:click="openDetail('{{ $payment['id'] }}')" class="w-7 h-7 inline-flex items-center justify-center rounded shadow-sm transition-colors bg-blue-500 text-white hover:bg-primary-600" title="Lihat Detail">
                                    <svg class="w-4 h-4 flex items-center justify-center rounded shadow-sm transition-colors bg-blue-500 text-white hover:bg-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr class="hover:bg-slate-50 dark:bg-slate-900/50 dark:hover:bg-slate-800/50 transition-colors">
                            <td colspan="7" class="p-3  text-center text-slate-500 dark:text-slate-400">
                                <div class="flex flex-col items-center justify-center">
                                    <svg class="w-12 h-12 text-slate-300 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/></svg>
                                    <p class="text-lg font-medium text-slate-900 dark:text-slate-100">Belum Ada Transaksi</p>
                                    <p class="mt-1">Riwayat transaksi pembayaran akan muncul di sini.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table></div>

        </x-base.card>

    {{-- Modal Detail & Verifikasi --}}
    @if($showDetailModal && $selectedPayment)
        <div class="fixed inset-0 bg-slate-900/50 backdrop-blur-sm z-50 flex items-center justify-center p-4">
            <div class="bg-white dark:bg-slate-800 rounded-xl shadow-xl w-full max-w-lg overflow-hidden">
                <div class="px-6 py-4 border-b border-slate-200 dark:border-slate-700 flex items-center justify-between">
                    <h3 class="text-lg font-bold text-slate-900 dark:text-slate-100">Detail Transaksi Payment Gateway</h3>
                    <button wire:click="$set('showDetailModal'- false)" class="text-slate-400 hover:text-slate-500 dark:text-slate-400">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>
                <div class="p-6 space-y-4">
                    <div class="grid grid-cols-2 gap-4 text-sm">
                        <div>
                            <span class="block text-slate-500 dark:text-slate-400 mb-1">ID Transaksi (Gateway)</span>
                            <div class="font-mono text-slate-900 dark:text-slate-100 font-medium">{{ $selectedPayment['id'] }}</div>
                        </div>
                        <div>
                            <span class="block text-slate-500 dark:text-slate-400 mb-1">Provider Gateway</span>
                            <div class="text-slate-900 dark:text-slate-100 font-medium">{{ $selectedPayment['gateway'] }}</div>
                        </div>
                        <div>
                            <span class="block text-slate-500 dark:text-slate-400 mb-1">No. Invoice</span>
                            <div class="text-slate-900 dark:text-slate-100 font-medium">{{ $selectedPayment['invoice_no'] }}</div>
                        </div>
                        <div>
                            <span class="block text-slate-500 dark:text-slate-400 mb-1">Nominal</span>
                            <div class="text-slate-900 dark:text-slate-100 font-medium">Rp {{ number_format($selectedPayment['amount']- 0 ?? '-'- '.') }}</div>
                        </div>
                        <div>
                            <span class="block text-slate-500 dark:text-slate-400 mb-1">Waktu Transaksi</span>
                            <div class="text-slate-900 dark:text-slate-100 font-medium">{{ $selectedPayment['date'] }}</div>
                        </div>
                        <div>
                            <span class="block text-slate-500 dark:text-slate-400 mb-1">Status</span>
                            @if($selectedPayment['status'] === 'settlement')
                                <x-ui.badge variant="success">Settlement</x-ui.badge>
                            @elseif($selectedPayment['status'] === 'expire')
                                <x-ui.badge variant="danger">Expired</x-ui.badge>
                            @else
                                <x-ui.badge variant="warning">Pending</x-ui.badge>
                            @endif
                        </div>
                    </div>

                    <div class="mt-6 pt-6 border-t border-slate-200 dark:border-slate-700">
                        <h4 class="font-medium text-slate-900 dark:text-slate-100 mb-3 text-sm">Aksi Transaksi</h4>
                        <div class="flex flex-col gap-2">
                            <button wire:click="checkStatus('{{ $selectedPayment['id'] }}')" class="w-full flex items-center justify-center gap-2 px-4 py-2 bg-slate-100 dark:bg-slate-700 hover:bg-slate-200 text-slate-700 dark:text-slate-300 text-sm font-medium rounded-lg transition-colors">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                                Sinkronisasi Status dari Gateway
                            </button>
                            
                            @if($selectedPayment['status'] === 'pending')
                                <button wire:click="verifyPayment('{{ $selectedPayment['id'] }}')" class="w-full flex items-center justify-center gap-2 px-4 py-2 bg-blue-50 dark:bg-blue-900/30 hover:bg-blue-100 dark:bg-blue-900/50 text-primary-700 border border-blue-200 text-sm font-medium rounded-lg transition-colors">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11, 18 0 9 9 0 0118 0z"/></svg>
                                    Tandai Sebagai Dibayar (Manual Override)
                                </button>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>






