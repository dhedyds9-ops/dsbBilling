<div class="space-y-6">
    <x-admin.breadcrumbs :breadcrumbs="$this->breadcrumbs" />

    {{-- Actions & Back --}}
    <div class="no-print flex items-center justify-between">
        <a href="{{ auth()->user()->hasRole('reseller') ? route('reseller-portal.billing.payments') : route('billing.payments.index') }}" class="inline-flex items-center gap-2 px-4 py-2 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-700 dark:text-slate-300 rounded-lg text-sm font-medium hover:bg-slate-50 dark:hover:bg-slate-700 transition-colors">
            <span class="material-symbols-outlined notranslate" translate="no" style="font-size:18px">arrow_back</span>
            Kembali
        </a>
        <div class="flex items-center gap-2">
            <button onclick="window.print()" class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg text-sm font-medium transition-colors shadow-sm">
                <span class="material-symbols-outlined notranslate" translate="no" style="font-size:18px">print</span>
                Cetak Bukti Bayar
            </button>
        </div>
    </div>

    {{-- Payment Summary Card --}}
    <div class="bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-2xl p-6 shadow-sm">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between pb-6 border-b border-slate-100 dark:border-slate-700/60 gap-4">
            <div>
                <span class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Nomor Referensi</span>
                <h1 class="text-2xl font-black text-slate-900 dark:text-slate-100 tracking-tight">{{ $payment->reference_number ?? ('PAY-' . str_pad($payment->id, 6, '0', STR_PAD_LEFT)) }}</h1>
                <p class="text-xs text-slate-500 mt-1">Dicatat pada: {{ $payment->created_at?->format('d M Y, H:i') ?? '-' }}</p>
            </div>
            <div class="flex sm:flex-col items-center sm:items-end gap-2">
                @if($payment->status === 'success')
                    <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-bold bg-emerald-100 text-emerald-700 dark:bg-emerald-900/50 dark:text-emerald-400">
                        <span class="material-symbols-outlined notranslate" style="font-size:16px" translate="no">check_circle</span> Sukses / Lunas
                    </span>
                @elseif($payment->status === 'pending')
                    <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-bold bg-amber-100 text-amber-700 dark:bg-amber-900/50 dark:text-amber-400">
                        <span class="material-symbols-outlined notranslate" style="font-size:16px" translate="no">schedule</span> Menunggu Verifikasi
                    </span>
                @elseif($payment->status === 'failed')
                    <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-bold bg-red-100 text-red-700 dark:bg-red-900/50 dark:text-red-400">
                        <span class="material-symbols-outlined notranslate" style="font-size:16px" translate="no">cancel</span> Gagal / Batal
                    </span>
                @else
                    <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-bold bg-slate-100 text-slate-700 dark:bg-slate-800 dark:text-slate-400">
                        {{ ucfirst($payment->status) }}
                    </span>
                @endif
                <div class="text-2xl font-black text-emerald-600 dark:text-emerald-400">
                    Rp {{ number_format($payment->amount, 0, ',', '.') }}
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 pt-6">
            {{-- Data Pelanggan --}}
            <div class="space-y-2">
                <h4 class="text-xs font-bold text-slate-400 uppercase tracking-wider">Data Pelanggan</h4>
                <div class="text-sm font-semibold text-slate-900 dark:text-slate-100">{{ $payment->customer?->name ?? '-' }}</div>
                <div class="text-xs text-slate-500">ID Pelanggan: <span class="font-medium text-slate-700 dark:text-slate-300">{{ $payment->customer?->code ?? '-' }}</span></div>
                <div class="text-xs text-slate-500">Telepon / WhatsApp: <span class="font-medium text-slate-700 dark:text-slate-300">{{ $payment->customer?->phone ?? '-' }}</span></div>
                <div class="text-xs text-slate-500">Alamat: <span class="font-medium text-slate-700 dark:text-slate-300">{{ $payment->customer?->address ?? '-' }}</span></div>
            </div>

            {{-- Detail Transaksi --}}
            <div class="space-y-2">
                <h4 class="text-xs font-bold text-slate-400 uppercase tracking-wider">Metode & Waktu</h4>
                <div class="text-xs text-slate-500">Metode Pembayaran: <span class="font-semibold text-slate-800 dark:text-slate-200 uppercase">{{ str_replace('_', ' ', $payment->method) }}</span></div>
                <div class="text-xs text-slate-500">Gateway: <span class="font-semibold text-slate-800 dark:text-slate-200 uppercase">{{ $payment->gateway ?: 'MANUAL' }}</span></div>
                <div class="text-xs text-slate-500">Waktu Bayar: <span class="font-medium text-slate-700 dark:text-slate-300">{{ $payment->paid_at?->format('d M Y, H:i') ?? '-' }}</span></div>
                <div class="text-xs text-slate-500">Mata Uang: <span class="font-medium text-slate-700 dark:text-slate-300">{{ $payment->currency ?? 'IDR' }}</span></div>
            </div>

            {{-- Reseller & Petugas --}}
            <div class="space-y-2">
                <h4 class="text-xs font-bold text-slate-400 uppercase tracking-wider">Informasi Tambahan</h4>
                <div class="text-xs text-slate-500">Petugas Input: <span class="font-medium text-slate-700 dark:text-slate-300">{{ $payment->user?->name ?? 'Sistem' }}</span></div>
                @if($payment->notes)
                    <div class="text-xs text-slate-500">Catatan: <span class="font-medium text-slate-700 dark:text-slate-300">{{ $payment->notes }}</span></div>
                @endif
            </div>
        </div>
    </div>

    {{-- Associated Invoices --}}
    <div class="bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-2xl p-6 shadow-sm">
        <h3 class="text-sm font-bold text-slate-900 dark:text-slate-100 uppercase tracking-wider mb-4 flex items-center gap-2">
            <span class="material-symbols-outlined notranslate text-blue-600 dark:text-blue-400" translate="no" style="font-size:20px">receipt</span>
            Tagihan Terkait
        </h3>

        @if($payment->invoices->count() > 0)
            <div class="overflow-x-auto">
                <table class="w-full text-sm text-left">
                    <thead>
                        <tr class="border-b border-slate-200 dark:border-slate-700 text-xs uppercase tracking-wider text-slate-500 dark:text-slate-400">
                            <th class="py-3 px-4 font-semibold">No. Invoice</th>
                            <th class="py-3 px-4 font-semibold">Tgl Terbit</th>
                            <th class="py-3 px-4 font-semibold">Jatuh Tempo</th>
                            <th class="py-3 px-4 font-semibold">Status Tagihan</th>
                            <th class="py-3 px-4 font-semibold text-right">Total Tagihan</th>
                            <th class="py-3 px-4 font-semibold text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-700">
                        @foreach($payment->invoices as $inv)
                            <tr class="hover:bg-slate-50 dark:hover:bg-slate-900/50 transition-colors">
                                <td class="py-3 px-4 font-bold text-blue-600 dark:text-blue-400">
                                    {{ $inv->invoice_number }}
                                </td>
                                <td class="py-3 px-4 text-slate-600 dark:text-slate-400">
                                    {{ $inv->issue_date?->format('d M Y') ?? '-' }}
                                </td>
                                <td class="py-3 px-4 text-slate-600 dark:text-slate-400">
                                    {{ $inv->due_date?->format('d M Y') ?? '-' }}
                                </td>
                                <td class="py-3 px-4">
                                    @if($inv->status === 'paid')
                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-xs font-semibold bg-emerald-100 text-emerald-700 dark:bg-emerald-900/50 dark:text-emerald-400">Lunas</span>
                                    @else
                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-xs font-semibold bg-amber-100 text-amber-700 dark:bg-amber-900/50 dark:text-amber-400">{{ ucfirst($inv->status) }}</span>
                                    @endif
                                </td>
                                <td class="py-3 px-4 text-right font-mono font-medium text-slate-900 dark:text-slate-100">
                                    Rp {{ number_format($inv->total_amount, 0, ',', '.') }}
                                </td>
                                <td class="py-3 px-4 text-center">
                                    <a href="{{ route('billing.invoices.show', $inv->id) }}" class="inline-flex items-center justify-center px-3 py-1 bg-blue-50 hover:bg-blue-100 text-blue-600 dark:bg-blue-900/30 dark:hover:bg-blue-900/50 dark:text-blue-400 rounded-lg text-xs font-medium transition-colors">
                                        Lihat Invoice
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div class="py-8 text-center text-slate-400">
                <p class="text-sm">Pembayaran ini tidak langsung ditautkan ke invoice tertentu.</p>
            </div>
        @endif
    </div>
</div>
