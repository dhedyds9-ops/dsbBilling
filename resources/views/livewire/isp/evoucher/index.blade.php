<div>
    @section('page_title')
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl bg-purple-500/10 flex items-center justify-center text-purple-600 dark:text-purple-400">
                <span class="material-symbols-outlined notranslate" translate="no">shopping_cart_checkout</span>
            </div>
            <div>
                <h1 class="text-xl font-bold text-slate-900 dark:text-slate-100 dark:text-white leading-tight">Order E-Voucher</h1>
                <p class="text-sm text-slate-500 dark:text-slate-400 dark:text-slate-400">Daftar transaksi pembelian voucher digital</p>
            </div>
        </div>
    @endsection

    <div class="space-y-4">
        {{-- TOOLBAR --}}
        <div class="flex flex-col sm:flex-row gap-3 items-center justify-between bg-white dark:bg-slate-800 p-4 rounded-xl shadow-sm border border-slate-200 dark:border-slate-700">
            <div class="flex-1 w-full relative">
                <span class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400">
                    <span class="material-symbols-outlined notranslate" translate="no" style="font-size: 20px">search</span>
                </span>
                <input type="text" wire:model.live.debounce.300ms="search" placeholder="Cari Order ID, No WA, atau Profil..." class="w-full pl-9 pr-4 py-2 bg-slate-50 dark:bg-slate-900/50 border border-slate-200 dark:border-slate-700 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-purple-500 transition-all dark:bg-slate-900 dark:text-slate-100">
            </div>

            <div class="flex items-center gap-3 w-full sm:w-auto">
                <select wire:model.live="statusFilter" class="pl-3 pr-10 py-2 bg-slate-50 dark:bg-slate-900/50 border border-slate-200 dark:border-slate-700 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-purple-500 cursor-pointer dark:bg-slate-900 dark:text-slate-100">
                    <option value="">Semua Status</option>
                    <option value="pending">Pending</option>
                    <option value="paid">Paid (Dibayar)</option>
                    <option value="completed">Completed (Selesai)</option>
                    <option value="failed">Failed (Gagal)</option>
                </select>
            </div>
        </div>

        {{-- DATA TABLE --}}
        <div class="bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl shadow-sm overflow-hidden overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-slate-200 dark:border-slate-700 text-xs uppercase tracking-wider text-slate-500 dark:text-slate-400 bg-slate-50 dark:bg-slate-900/80">
                        <th class="px-4 py-3 text-left font-semibold whitespace-nowrap">Order ID</th>
                        <th class="px-4 py-3 text-left font-semibold whitespace-nowrap">No. Whatsapp</th>
                        <th class="px-4 py-3 text-left font-semibold whitespace-nowrap">Profil</th>
                        <th class="px-4 py-3 text-center font-semibold whitespace-nowrap">Qty</th>
                        <th class="px-4 py-3 text-left font-semibold whitespace-nowrap">Harga [+PPN]</th>
                        <th class="px-4 py-3 text-left font-semibold whitespace-nowrap">Voucher Akun</th>
                        <th class="px-4 py-3 text-left font-semibold whitespace-nowrap">Tgl Order</th>
                        <th class="px-4 py-3 text-center font-semibold whitespace-nowrap">Status</th>
                        <th class="px-4 py-3 text-left font-semibold whitespace-nowrap">Tgl Bayar</th>
                        <th class="px-4 py-3 text-left font-semibold whitespace-nowrap">Via Bayar</th>
                        <th class="px-4 py-3 text-left font-semibold whitespace-nowrap">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-700">
                    @forelse($orders as $order)
                        @php
                            $isPending = $order->status === 'pending';
                            $isCompleted = $order->status === 'completed';
                            $isPaid = $order->status === 'paid';
                            $isFailed = $order->status === 'failed';
                            
                            $rowBg = 'hover:bg-slate-50 dark:bg-slate-800/50 transition-colors';
                            
                            if ($isFailed) {
                                $rowBg = 'bg-red-50 dark:bg-red-900/30/30 hover:bg-red-50 dark:bg-red-900/30/50 transition-colors';
                            } elseif ($isCompleted) {
                                $rowBg = 'bg-emerald-50 dark:bg-emerald-900/30/20 hover:bg-emerald-50 dark:bg-emerald-900/30/40 transition-colors';
                            }
                        @endphp
                        <tr class="{{ $rowBg }}">
                            <td class="px-4 py-3 font-mono text-slate-900 dark:text-slate-100 font-medium tracking-wider">
                                #{{ $order->id }}
                            </td>
                            <td class="px-4 py-3 text-slate-600 dark:text-slate-400 font-medium">
                                {{ $order->wa_number ?? '-' }}
                            </td>
                            <td class="px-4 py-3 text-slate-600 dark:text-slate-400">
                                {{ $order->service_profile_name ?? '-' }}
                            </td>
                            <td class="px-4 py-3 text-slate-600 dark:text-slate-400 text-center font-mono">
                                {{ $order->quantity }}
                            </td>
                            <td class="px-4 py-3 text-slate-600 dark:text-slate-400 font-mono text-xs">
                                Rp {{ number_format($order->total_amount ?? 0, 0, ',', '.') }}
                            </td>
                            <td class="px-4 py-3 text-slate-600 dark:text-slate-400 font-mono text-xs">
                                @if($order->voucher_username)
                                    <div>U: <span class="text-slate-900 dark:text-slate-100">{{ $order->voucher_username }}</span></div>
                                    <div>P: <span class="text-slate-900 dark:text-slate-100">{{ $order->voucher_password }}</span></div>
                                @else
                                    <span class="text-slate-400 italic">Belum digenerate</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-slate-600 dark:text-slate-400 text-xs">
                                {{ $order->created_at ? $order->created_at->format('d/m/Y H:i') : '-' }}
                            </td>
                            <td class="px-4 py-3 text-center whitespace-nowrap">
                                @if($isCompleted)
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md text-xs font-bold bg-green-500 text-white shadow-sm">
                                        <span class="w-1.5 h-1.5 rounded-full bg-white dark:bg-slate-800"></span>
                                        Selesai
                                    </span>
                                @elseif($isPaid)
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md text-xs font-bold bg-blue-500 text-white shadow-sm">
                                        <span class="w-1.5 h-1.5 rounded-full bg-white dark:bg-slate-800"></span>
                                        Dibayar
                                    </span>
                                @elseif($isPending)
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md text-xs font-bold bg-amber-500 text-white shadow-sm">
                                        <span class="w-1.5 h-1.5 rounded-full bg-white dark:bg-slate-800"></span>
                                        Pending
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md text-xs font-bold bg-red-500 text-white shadow-sm">
                                        <span class="w-1.5 h-1.5 rounded-full bg-white dark:bg-slate-800"></span>
                                        Gagal
                                    </span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-slate-600 dark:text-slate-400 text-xs">
                                {{ $order->paid_at ? \Carbon\Carbon::parse($order->paid_at)->format('d/m/Y H:i') : '-' }}
                            </td>
                            <td class="px-4 py-3 text-slate-600 dark:text-slate-400 text-xs font-mono uppercase">
                                {{ $order->invoice?->payments?->first()?->gateway ?? 'Manual' }}
                            </td>
                            <td class="px-4 py-3 text-right whitespace-nowrap">
                                <div class="flex items-center gap-1">
                                    <button wire:click="delete({{ $order->id }})" onclick="confirm('Yakin ingin menghapus transaksi ini?') || event.stopImmediatePropagation()" class="inline-flex items-center justify-center w-8 h-8 rounded-md bg-red-50 dark:bg-red-900/30 hover:bg-red-100 dark:bg-red-900/50 text-red-600 dark:text-red-400 transition-colors" title="Hapus">
                                        <span class="material-symbols-outlined notranslate" translate="no" style="font-size:18px">delete</span>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="10" class="px-4 py-12 text-center text-slate-500 dark:text-slate-400">
                                <div class="flex flex-col items-center justify-center">
                                    <span class="material-symbols-outlined notranslate mb-2 text-slate-300" translate="no" style="font-size:48px">receipt_long</span>
                                    <p class="text-lg font-medium text-slate-900 dark:text-slate-100 mt-2">Belum ada Order E-Voucher</p>
                                    <p class="text-sm mt-1">Transaksi dari landing page atau portal digital akan muncul di sini.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
            @if($orders->hasPages())
                <div class="px-4 py-3 border-t border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800/50">
                    {{ $orders->links() }}
                </div>
            @endif
        </div>
    </div>


@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    document.addEventListener('livewire:init', () => {
        Livewire.on('evoucher-deleted', () => {
            const isDark = document.documentElement.classList.contains('dark');
            Swal.fire({
                title: 'Berhasil',
                text: 'Order E-Voucher telah dihapus.',
                icon: 'success',
                timer: 2000,
                showConfirmButton: false,
                width: '25rem',
                background: isDark ? '#1e293b' : '#fff',
                color: isDark ? '#f8fafc' : '#0f172a'
            });
        });
    });
</script>
@endpush
</div>
