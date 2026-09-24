<div>
    <div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-900 dark:text-slate-100 tracking-tight">Tagihan Pelanggan</h1>
            <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">Kelola dan pantau seluruh tagihan (invoice) dari pelanggan Anda.</p>
        </div>
    </div>

    <!-- Filter & Toolbar -->
    <div class="flex flex-col sm:flex-row gap-3 items-center justify-between bg-white dark:bg-slate-800 p-4 rounded-xl shadow-sm border border-slate-200 dark:border-slate-700 mb-6">
        <div class="flex-1 w-full relative">
            <span class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400">
                <span class="material-symbols-outlined notranslate" translate="no" style="font-size: 20px">search</span>
            </span>
            <input type="text" wire:model.live.debounce.300ms="search" placeholder="Cari no tagihan atau nama pelanggan..." class="w-full pl-9 pr-4 py-2 bg-slate-50 dark:bg-slate-900/50 border border-slate-200 dark:border-slate-700 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 transition-all dark:bg-slate-900 dark:text-slate-100">
        </div>
        <div class="flex items-center gap-3 w-full sm:w-auto">
            <select wire:model.live="status" class="pl-3 pr-8 py-2 bg-slate-50 dark:bg-slate-900/50 border border-slate-200 dark:border-slate-700 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 cursor-pointer dark:bg-slate-900 dark:text-slate-100">
                <option value="">Semua Status</option>
                <option value="unpaid">Belum Lunas (Unpaid)</option>
                <option value="paid">Lunas (Paid)</option>
                <option value="overdue">Jatuh Tempo (Overdue)</option>
                <option value="canceled">Dibatalkan</option>
            </select>
            <select wire:model.live="perPage" class="pl-3 pr-8 py-2 bg-slate-50 dark:bg-slate-900/50 border border-slate-200 dark:border-slate-700 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 cursor-pointer dark:bg-slate-900 dark:text-slate-100">
                <option value="10">10 / halaman</option>
                <option value="25">25 / halaman</option>
                <option value="50">50 / halaman</option>
            </select>
        </div>
    </div>

    <!-- Data Table -->
    <div class="bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-slate-200 dark:border-slate-700 text-xs uppercase tracking-wider text-slate-500 dark:text-slate-400 bg-slate-50 dark:bg-slate-900/80">
                        <th class="px-4 py-3 text-left font-semibold">No Tagihan</th>
                        <th class="px-4 py-3 text-left font-semibold">Pelanggan</th>
                        <th class="px-4 py-3 text-left font-semibold">Total Tagihan</th>
                        <th class="px-4 py-3 text-left font-semibold">Tgl Jatuh Tempo</th>
                        <th class="px-4 py-3 text-left font-semibold">Status</th>
                        <th class="px-4 py-3 text-center font-semibold">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200 dark:divide-slate-700">
                    @forelse($invoices as $row)
                        <tr class="hover:bg-slate-50 dark:bg-slate-900/50 dark:hover:bg-slate-800/50 transition-colors">
                            <td class="px-4 py-3 text-slate-700 dark:text-slate-300 font-medium">
                                {{ $row->invoice_number }}
                                <div class="text-xs text-slate-400 font-normal mt-0.5">{{ $row->created_at->format('d M Y') }}</div>
                            </td>
                            <td class="px-4 py-3">
                                <div class="font-medium text-slate-800 dark:text-slate-200">{{ $row->customer->name ?? 'Unknown' }}</div>
                                <div class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">{{ $row->customer->username ?? '-' }}</div>
                            </td>
                            <td class="px-4 py-3 font-bold text-slate-700 dark:text-slate-300">
                                Rp {{ number_format($row->total_amount ?? 0, 0, ',', '.') }}
                            </td>
                            <td class="px-4 py-3 text-slate-600 dark:text-slate-400">
                                {{ $row->due_date ? $row->due_date->format('d M Y') : '-' }}
                            </td>
                            <td class="px-4 py-3">
                                @if($row->status === 'paid')
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-emerald-100 text-emerald-800 dark:bg-emerald-900/30 dark:text-emerald-400 border border-emerald-200 dark:border-emerald-800/50">
                                        Lunas
                                    </span>
                                @elseif($row->status === 'unpaid')
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-amber-100 text-amber-800 dark:bg-amber-900/30 dark:text-amber-400 border border-amber-200 dark:border-amber-800/50">
                                        Belum Lunas
                                    </span>
                                @elseif($row->status === 'overdue')
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-400 border border-red-200 dark:border-red-800/50">
                                        Jatuh Tempo
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-slate-100 text-slate-800 dark:bg-slate-700 dark:text-slate-300 border border-slate-200 dark:border-slate-600">
                                        {{ ucfirst($row->status) }}
                                    </span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-center">
                                <div class="flex items-center justify-center gap-2">
                                    <a href="{{ route('reseller-portal.billing.invoices.show', $row->id) }}" class="inline-flex items-center justify-center w-8 h-8 rounded-md bg-blue-50 hover:bg-blue-100 text-blue-600 dark:bg-blue-900/20 dark:hover:bg-blue-900/40 dark:text-blue-400 transition-colors" title="Detail">
                                        <span class="material-symbols-outlined notranslate" translate="no" style="font-size:18px">visibility</span>
                                    </a>
                                    @if(in_array($row->status, ['unpaid', 'partial', 'overdue', 'pending']))
                                        <button wire:click="openPaymentModal({{ $row->id }})" type="button" class="inline-flex items-center justify-center w-8 h-8 rounded-md bg-emerald-50 hover:bg-emerald-100 text-emerald-600 dark:bg-emerald-900/20 dark:hover:bg-emerald-900/40 dark:text-emerald-400 transition-colors" title="Proses Bayar"> <span class="material-symbols-outlined notranslate" translate="no" style="font-size:18px">payments</span> </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-4 py-8 text-center text-slate-500 dark:text-slate-400">
                                <div class="flex flex-col items-center justify-center">
                                    <span class="material-symbols-outlined notranslate text-4xl mb-2 opacity-50" translate="no">receipt_long</span>
                                    <p>Belum ada data tagihan pelanggan.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($invoices->hasPages())
            <div class="px-4 py-3 border-t border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-900/50">
                {{ $invoices->links(data: ['scrollTo' => false]) }}
            </div>
        @endif
    </div>
    <!-- Modal Detail Invoice -->
    <div x-data="{ show: @entangle('showDetailModal') }" x-show="show" class="relative z-50" aria-labelledby="modal-title" role="dialog" aria-modal="true" style="display: none;">
        <div x-show="show" x-transition.opacity class="fixed inset-0 bg-slate-900/75 backdrop-blur-sm transition-opacity"></div>
        <div class="fixed inset-0 z-10 w-screen overflow-y-auto">
            <div class="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0">
                <div x-show="show" x-transition.scale.origin.bottom sm.origin.center class="relative transform overflow-x-auto rounded-xl bg-white dark:bg-slate-800 text-left shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-2xl border border-slate-200 dark:border-slate-700">
                    
                    @if($selectedInvoice)
                    <div class="px-6 py-4 border-b border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-900/50 flex justify-between items-center">
                        <h3 class="text-lg font-bold text-slate-900 dark:text-slate-100" id="modal-title">Detail Tagihan: {{ $selectedInvoice->invoice_number }}</h3>
                        <button type="button" x-on:click="show = false" class="text-slate-400 hover:text-slate-500 dark:text-slate-400 dark:hover:text-slate-300">
                            <span class="material-symbols-outlined notranslate" translate="no" style="font-size: 24px">close</span>
                        </button>
                    </div>
                    <div class="px-6 py-6">
                        <div class="flex justify-between mb-6">
                            <div>
                                <p class="text-xs text-slate-500 dark:text-slate-400 uppercase tracking-wider font-semibold mb-1">Kepada</p>
                                <p class="font-bold text-slate-800 dark:text-slate-200">{{ $selectedInvoice->customer->name ?? '-' }}</p>
                                <p class="text-sm text-slate-600 dark:text-slate-400">{{ $selectedInvoice->customer->username ?? '-' }}</p>
                            </div>
                            <div class="text-right">
                                <p class="text-xs text-slate-500 dark:text-slate-400 uppercase tracking-wider font-semibold mb-1">Status</p>
                                @if($selectedInvoice->status === 'paid')
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-emerald-100 text-emerald-800 dark:bg-emerald-900/30 dark:text-emerald-400">Lunas</span>
                                @elseif($selectedInvoice->status === 'unpaid')
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-amber-100 text-amber-800 dark:bg-amber-900/30 dark:text-amber-400">Belum Lunas</span>
                                @else
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-slate-100 text-slate-800 dark:bg-slate-700 dark:text-slate-300">{{ ucfirst($selectedInvoice->status) }}</span>
                                @endif
                                <p class="text-xs text-slate-500 dark:text-slate-400 mt-2">Jatuh Tempo: {{ $selectedInvoice->due_date ? $selectedInvoice->due_date->format('d M Y') : '-' }}</p>
                            </div>
                        </div>

                        <table class="w-full text-sm mb-4">
                            <thead>
                                <tr class="border-b border-slate-200 dark:border-slate-700 text-slate-500 dark:text-slate-400">
                                    <th class="py-2 text-left font-semibold">Deskripsi Layanan</th>
                                    <th class="py-2 text-center font-semibold">Qty</th>
                                    <th class="py-2 text-right font-semibold">Subtotal</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 dark:divide-slate-700/50">
                                @forelse($selectedInvoice->items as $item)
                                    <tr>
                                        <td class="py-3 text-slate-700 dark:text-slate-300">{{ $item->description }}</td>
                                        <td class="py-3 text-center text-slate-700 dark:text-slate-300">{{ $item->quantity }}</td>
                                        <td class="py-3 text-right font-mono text-slate-700 dark:text-slate-300">Rp {{ number_format($item->subtotal, 0, ',', '.') }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="3" class="py-3 text-center text-slate-500 dark:text-slate-400">Tidak ada rincian item.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                            <tfoot>
                                <tr class="border-t-2 border-slate-200 dark:border-slate-700">
                                    <th colspan="2" class="py-3 text-right font-bold text-slate-800 dark:text-slate-200">Total Tagihan:</th>
                                    <th class="py-3 text-right font-bold font-mono text-indigo-600 dark:text-indigo-400 text-lg">Rp {{ number_format($selectedInvoice->total_amount, 0, ',', '.') }}</th>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                    <div class="px-6 py-4 bg-slate-50 dark:bg-slate-900/50 border-t border-slate-200 dark:border-slate-700 flex justify-end gap-3">
                        <button type="button" x-on:click="show = false" class="px-4 py-2 border border-slate-300 dark:border-slate-600 rounded-lg text-sm font-medium text-slate-700 dark:text-slate-300 bg-white dark:bg-slate-700 hover:bg-slate-50 dark:bg-slate-900/50 dark:hover:bg-slate-600 transition-colors">Tutup</button>
                        @if(in_array($selectedInvoice->status, ['unpaid', 'partial', 'overdue', 'pending']))
                            <button type="button" wire:click="openPaymentModal({{ $selectedInvoice->id }})" class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg text-sm font-medium transition-colors flex items-center gap-1.5">
                                <span class="material-symbols-outlined notranslate" translate="no" style="font-size:18px">payments</span> Proses Bayar
                            </button>
                        @endif
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
    {{-- PAYMENT MODAL --}}
    @if($showPaymentModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center overflow-y-auto overflow-x-hidden bg-slate-900/50 backdrop-blur-sm">
            <div class="relative w-full max-w-md p-4">
                <div class="relative bg-white dark:bg-slate-800 rounded-xl shadow-2xl border border-slate-200 dark:border-slate-700 overflow-hidden">
                    <div class="flex items-center justify-between px-5 py-4 border-b border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-900/50">
                        <h3 class="text-lg font-bold text-slate-900 dark:text-slate-100">Proses Pembayaran Cepat</h3>
                        <button wire:click="closePaymentModal" class="text-slate-400 hover:text-slate-500 dark:text-slate-400 dark:hover:text-slate-300">
                            <span class="material-symbols-outlined notranslate" translate="no">close</span>
                        </button>
                    </div>
                    <div class="p-5 space-y-4">
                        <div class="bg-blue-50 dark:bg-blue-900/20 text-blue-700 dark:text-blue-400 p-3 rounded-lg text-sm border border-blue-100 dark:border-blue-800">
                            Sisa tagihan yang harus dibayar: <strong>Rp {{ number_format($paymentInvoiceTotal, 0, ',', '.') }}</strong>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Jumlah Bayar (Rp)</label>
                            <input type="number" wire:model="paymentAmount" class="w-full rounded-lg border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-900 dark:bg-slate-700 text-slate-900 dark:text-slate-100 focus:border-blue-500 focus:ring-blue-500 dark:bg-slate-900 dark:text-slate-100">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Metode Pembayaran</label>
                            <select wire:model="paymentMethod" class="w-full rounded-lg border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-900 dark:bg-slate-700 text-slate-900 dark:text-slate-100 focus:border-blue-500 focus:ring-blue-500 dark:bg-slate-900 dark:text-slate-100">
                                <option value="cash">Tunai / Cash</option>
                                <option value="bank_transfer">Transfer Bank</option>
                            </select>
                        </div>
                    </div>
                    <div class="px-5 py-4 border-t border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-900/50 flex justify-end gap-2">
                        <button wire:click="closePaymentModal" class="px-4 py-2 border border-slate-300 dark:border-slate-600 rounded-lg text-sm font-medium text-slate-700 dark:text-slate-300 bg-white dark:bg-slate-700 hover:bg-slate-50 dark:bg-slate-900/50 dark:hover:bg-slate-600 transition-colors">Batal</button>
                        <button wire:click="submitPayment" class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg text-sm font-medium transition-colors">Perpanjang</button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <script>
        document.addEventListener('livewire:initialized', () => {
            Livewire.on('swal:success', (data) => {
                const info = Array.isArray(data) ? data[0] : data;
                Swal.fire({
                    icon: 'success',
                    title: info.title,
                    text: info.text,
                    background: document.documentElement.classList.contains('dark') ? '#1e293b' : '#fff',
                    color: document.documentElement.classList.contains('dark') ? '#f8fafc' : '#0f172a',
                    confirmButtonColor: '#4f46e5'
                });
            });
            Livewire.on('swal:error', (data) => {
                const info = Array.isArray(data) ? data[0] : data;
                Swal.fire({
                    icon: 'error',
                    title: info.title,
                    text: info.text,
                    background: document.documentElement.classList.contains('dark') ? '#1e293b' : '#fff',
                    color: document.documentElement.classList.contains('dark') ? '#f8fafc' : '#0f172a',
                    confirmButtonColor: '#ef4444'
                });
            });
        });
    </script>
</div>