<div>
    @section('page_title')
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl bg-emerald-500/10 flex items-center justify-center text-emerald-600 dark:text-emerald-400">
                <span class="material-symbols-outlined notranslate" translate="no">payments</span>
            </div>
            <div>
                <h1 class="text-xl font-bold text-slate-900 dark:text-slate-100 leading-tight">Data Pembayaran</h1>
                <p class="text-sm text-slate-500 dark:text-slate-400">Riwayat dan pencatatan pembayaran dari pelanggan</p>
            </div>
        </div>
    @endsection

    <div class="space-y-4">
        {{-- KPI CARDS --}}
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            {{-- Total Pemasukan --}}
            <div wire:click="$set('filters.status', '')" class="relative overflow-x-auto rounded-xl border border-emerald-200 dark:border-emerald-800/60 shadow-sm bg-gradient-to-br from-emerald-50 to-white dark:from-emerald-950/50 dark:to-slate-800 group hover:shadow-md transition-all cursor-pointer">
                <div class="absolute top-0 left-0 right-0 h-1 bg-gradient-to-r from-emerald-500 to-green-400 rounded-t-xl"></div>
                <div class="absolute top-3 right-3 opacity-10 text-emerald-500 group-hover:opacity-20 group-hover:scale-110 transition-all">
                    <span class="material-symbols-outlined notranslate" translate="no" style="font-size:56px">account_balance_wallet</span>
                </div>
                <div class="p-4 pt-5">
                    <h3 class="text-xs font-bold text-emerald-600 dark:text-emerald-500 uppercase tracking-widest mb-2">Total Penerimaan</h3>
                    <div class="flex items-baseline gap-2">
                        <span class="text-2xl font-black text-slate-800 dark:text-slate-100 tracking-tight">Rp {{ number_format($stats['total_amount'] ?? 0, 0, ',', '.') }}</span>
                    </div>
                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">{{ number_format($stats['total'] ?? 0) }} Transaksi</p>
                </div>
            </div>

            {{-- Sukses --}}
            <div wire:click="$set('filters.status', 'success')" class="relative overflow-x-auto rounded-xl border border-blue-200 dark:border-blue-800/60 shadow-sm bg-gradient-to-br from-blue-50 to-white dark:from-blue-950/50 dark:to-slate-800 group hover:shadow-md transition-all cursor-pointer">
                <div class="absolute top-0 left-0 right-0 h-1 bg-gradient-to-r from-blue-500 to-cyan-400 rounded-t-xl"></div>
                <div class="absolute top-3 right-3 opacity-10 text-blue-500 group-hover:opacity-20 group-hover:scale-110 transition-all">
                    <span class="material-symbols-outlined notranslate" translate="no" style="font-size:56px">check_circle</span>
                </div>
                <div class="p-4 pt-5">
                    <h3 class="text-xs font-bold text-blue-600 dark:text-blue-500 uppercase tracking-widest mb-2">Berhasil (Success)</h3>
                    <div class="flex items-baseline gap-2">
                        <span class="text-2xl font-black text-slate-800 dark:text-slate-100 tracking-tight">{{ number_format($stats['success'] ?? 0) }}</span>
                        <span class="text-sm font-medium text-slate-500 dark:text-slate-400">Transaksi</span>
                    </div>
                </div>
            </div>

            {{-- Menunggu --}}
            <div wire:click="$set('filters.status', 'pending')" class="relative overflow-x-auto rounded-xl border border-amber-200 dark:border-amber-800/60 shadow-sm bg-gradient-to-br from-amber-50 to-white dark:from-amber-950/50 dark:to-slate-800 group hover:shadow-md transition-all cursor-pointer">
                <div class="absolute top-0 left-0 right-0 h-1 bg-gradient-to-r from-amber-500 to-orange-400 rounded-t-xl"></div>
                <div class="absolute top-3 right-3 opacity-10 text-amber-500 group-hover:opacity-20 group-hover:scale-110 transition-all">
                    <span class="material-symbols-outlined notranslate" translate="no" style="font-size:56px">hourglass_empty</span>
                </div>
                <div class="p-4 pt-5">
                    <h3 class="text-xs font-bold text-amber-600 dark:text-amber-500 uppercase tracking-widest mb-2">Menunggu (Pending)</h3>
                    <div class="flex items-baseline gap-2">
                        <span class="text-2xl font-black text-slate-800 dark:text-slate-100 tracking-tight">{{ number_format($stats['pending'] ?? 0) }}</span>
                        <span class="text-sm font-medium text-slate-500 dark:text-slate-400">Transaksi</span>
                    </div>
                </div>
            </div>

            {{-- Gagal --}}
            <div wire:click="$set('filters.status', 'failed')" class="relative overflow-x-auto rounded-xl border border-red-200 dark:border-red-800/60 shadow-sm bg-gradient-to-br from-red-50 to-white dark:from-red-950/50 dark:to-slate-800 group hover:shadow-md transition-all cursor-pointer">
                <div class="absolute top-0 left-0 right-0 h-1 bg-gradient-to-r from-red-500 to-rose-400 rounded-t-xl"></div>
                <div class="absolute top-3 right-3 opacity-10 text-red-500 group-hover:opacity-20 group-hover:scale-110 transition-all">
                    <span class="material-symbols-outlined notranslate" translate="no" style="font-size:56px">cancel</span>
                </div>
                <div class="p-4 pt-5">
                    <h3 class="text-xs font-bold text-red-600 dark:text-red-500 uppercase tracking-widest mb-2">Gagal / Dibatalkan</h3>
                    <div class="flex items-baseline gap-2">
                        <span class="text-2xl font-black text-slate-800 dark:text-slate-100 tracking-tight">{{ number_format($stats['failed'] ?? 0) }}</span>
                        <span class="text-sm font-medium text-slate-500 dark:text-slate-400">Transaksi</span>
                    </div>
                </div>
            </div>
        </div>

        {{-- TOOLBAR --}}
        <div class="flex flex-col lg:flex-row gap-3 items-stretch lg:items-center justify-between bg-white dark:bg-slate-800 p-3 sm:p-4 rounded-xl shadow-sm border border-slate-200 dark:border-slate-700">
            <div class="flex-1 w-full relative">
                <span class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400">
                    <span class="material-symbols-outlined notranslate" translate="no" style="font-size: 20px">search</span>
                </span>
                <input type="text" wire:model.live.debounce.300ms="search" placeholder="Cari No. Referensi atau Pelanggan..." class="w-full pl-9 pr-4 py-2 bg-slate-50 dark:bg-slate-900/50 border border-slate-200 dark:border-slate-700 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 transition-all dark:bg-slate-900 dark:text-slate-100">
            </div>
            <div class="flex flex-col sm:flex-row flex-wrap items-stretch sm:items-center gap-2">
                <div class="grid grid-cols-2 sm:flex sm:items-center gap-2 w-full sm:w-auto">
                    <select wire:model.live="filters.status" class="w-full sm:w-auto bg-slate-50 dark:bg-slate-900/50 border border-slate-200 dark:border-slate-700 text-slate-700 dark:text-slate-300 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block pl-3 pr-8 py-2 dark:bg-slate-900 dark:text-slate-100">
                        <option value="">Semua Status</option>
                        <option value="success">Berhasil</option>
                        <option value="pending">Menunggu</option>
                        <option value="failed">Gagal</option>
                    </select>
                    <select wire:model.live="filters.method" class="w-full sm:w-auto bg-slate-50 dark:bg-slate-900/50 border border-slate-200 dark:border-slate-700 text-slate-700 dark:text-slate-300 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block pl-3 pr-8 py-2 dark:bg-slate-900 dark:text-slate-100">
                        <option value="">Semua Metode</option>
                        <option value="cash">Tunai</option>
                        <option value="transfer">Transfer Bank</option>
                        <option value="qris">QRIS</option>
                        <option value="payment_gateway">Gateway</option>
                    </select>
                    @if(!$this->isReseller && (auth()->user()->hasAnyRole(['administrator', 'superadmin', 'manager']) || !auth()->user()->hasRole('reseller')))
                    <select wire:model.live="filters.reseller_id" class="w-full sm:w-auto bg-slate-50 dark:bg-slate-900/50 border border-slate-200 dark:border-slate-700 text-slate-700 dark:text-slate-300 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block pl-3 pr-8 py-2 dark:bg-slate-900 dark:text-slate-100">
                        <option value="">Semua Reseller</option>
                        @foreach($resellers as $res)
                            <option value="{{ $res->id }}">{{ $res->name }}</option>
                        @endforeach
                    </select>
                    @endif
                    <select wire:model.live="perPage" class="col-span-2 sm:col-auto w-full sm:w-auto bg-slate-50 dark:bg-slate-900/50 border border-slate-200 dark:border-slate-700 text-slate-700 dark:text-slate-300 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block pl-3 pr-8 py-2 dark:bg-slate-900 dark:text-slate-100">
                        <option value="10">10 Baris</option>
                        <option value="25">25 Baris</option>
                        <option value="50">50 Baris</option>
                        <option value="100">100 Baris</option>
                    </select>
                </div>
                <div class="flex flex-wrap items-center gap-2 w-full sm:w-auto">
                    <button wire:click="syncAllPending" wire:loading.attr="disabled" class="flex-1 sm:flex-none justify-center px-3 py-2 bg-amber-500 hover:bg-amber-600 text-white rounded-lg text-sm font-medium transition-colors flex items-center gap-1.5 shadow-sm whitespace-nowrap" title="Periksa status transaksi pending">
                        <span class="material-symbols-outlined notranslate" translate="no" style="font-size:18px">sync</span>
                        <span>Sinkron</span>
                    </button>
                    <button wire:click="export" wire:loading.attr="disabled" class="flex-1 sm:flex-none justify-center px-4 py-2 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-700 dark:text-slate-300 rounded-lg text-sm font-medium hover:bg-slate-50 dark:bg-slate-900/50 dark:hover:bg-slate-700 transition-colors flex items-center gap-2 whitespace-nowrap disabled:opacity-50" title="Export Excel">
                        <span wire:loading.remove wire:target="export" class="material-symbols-outlined notranslate" translate="no" style="font-size:18px">download</span>
                        <svg wire:loading wire:target="export" class="animate-spin -ml-1 mr-1 h-4 w-4 text-slate-700 dark:text-slate-300" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        <span wire:loading.remove wire:target="export">Export</span>
                        <span wire:loading wire:target="export">Mengunduh...</span>
                    </button>
                    @if(!$this->isReseller)
                    <a href="{{ route('billing.payments.create') }}" class="w-full sm:w-auto justify-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg text-sm font-medium transition-colors flex items-center gap-2 whitespace-nowrap">
                        <span class="material-symbols-outlined notranslate" translate="no" style="font-size:18px">add</span> Catat Pembayaran
                    </a>
                    @endif
                </div>
            </div>
        </div>

        {{-- DATA TABLE --}}
        <div class="bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl shadow-sm overflow-hidden overflow-x-auto relative">
            
            {{-- Bulk Actions Bar --}}
            @if(count($selected) > 0)
            <div class="absolute top-0 left-0 w-full h-12 bg-indigo-50 dark:bg-indigo-900/30 border-b border-indigo-100 dark:border-indigo-800 flex items-center justify-between px-4 z-10">
                <div class="flex items-center gap-3">
                    <span class="text-sm font-semibold text-indigo-700 dark:text-indigo-300">
                        <span class="w-5 h-5 inline-flex items-center justify-center bg-indigo-600 text-white rounded-full text-xs mr-1">{{ count($selected) }}</span> Terpilih
                    </span>
                </div>
                <div class="flex items-center gap-2">
                    <button wire:click="bulkDelete" wire:confirm="Yakin ingin menghapus {{ count($selected) }} pembayaran terpilih secara permanen?" class="px-3 py-1.5 bg-red-600 hover:bg-red-700 text-white rounded-lg text-xs font-medium transition-colors shadow-sm flex items-center gap-1.5">
                        <span class="material-symbols-outlined" style="font-size:16px">delete</span> Hapus
                    </button>
                </div>
            </div>
            @endif

            <table class="w-full text-sm text-left">
                <thead>
                    <tr class="border-b border-slate-200 dark:border-slate-700 text-xs uppercase tracking-wider text-slate-500 dark:text-slate-400 bg-slate-50 dark:bg-slate-900/80">
                        <th class="px-4 py-3 w-10 text-center">
                            <input type="checkbox" wire:model.live="selectAll" class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500 bg-slate-50 dark:bg-slate-900/50 dark:border-slate-600 cursor-pointer dark:bg-slate-900 dark:text-slate-100">
                        </th>
                        <th class="px-4 py-3 font-semibold whitespace-nowrap text-center">Aksi</th>
                        <th class="px-4 py-3 font-semibold whitespace-nowrap">No. Referensi</th>
                        <th class="px-4 py-3 font-semibold whitespace-nowrap">ID Pelanggan</th>
                        <th class="px-4 py-3 font-semibold whitespace-nowrap">Nama Pelanggan</th>
                        <th class="px-4 py-3 font-semibold whitespace-nowrap">Invoice Terkait</th>
                        <th class="px-4 py-3 font-semibold whitespace-nowrap">Tanggal Bayar</th>
                        <th class="px-4 py-3 font-semibold whitespace-nowrap">Metode & Saluran</th>
                        <th class="px-4 py-3 font-semibold whitespace-nowrap">Status</th>
                        <th class="px-4 py-3 font-semibold whitespace-nowrap text-right">Nominal</th>
                        @if(!$this->isReseller)
                        <th class="px-4 py-3 font-semibold whitespace-nowrap">Reseller / Mitra</th>
                        @endif
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-700">
                    @forelse($payments as $payment)
                        @php
                            $cleanPhone = $payment->customer?->phone ? preg_replace('/[^0-9]/', '', $payment->customer->phone) : null;
                            if ($cleanPhone && str_starts_with($cleanPhone, '0')) {
                                $cleanPhone = '62' . substr($cleanPhone, 1);
                            }
                        @endphp
                        <tr class="hover:bg-slate-50 dark:bg-slate-900/50 dark:hover:bg-slate-800/50 transition-colors">
                            <td class="px-4 py-3 text-center whitespace-nowrap">
                                <input type="checkbox" wire:model.live="selected" value="{{ $payment->id }}" class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500 bg-slate-50 dark:bg-slate-900/50 dark:border-slate-600 cursor-pointer">
                            </td>
                            <td class="px-4 py-3 whitespace-nowrap text-center">
                                <div class="flex items-center justify-center gap-1">
                                    @if($payment->status !== 'success')
                                        <button wire:click="syncPayment({{ $payment->id }})" wire:loading.attr="disabled" class="inline-flex items-center justify-center w-8 h-8 rounded-md bg-amber-50 hover:bg-amber-100 text-amber-600 dark:bg-amber-900/20 dark:hover:bg-amber-900/40 dark:text-amber-400 transition-colors" title="Cek Status Midtrans / Gateway">
                                            <span class="material-symbols-outlined notranslate" translate="no" style="font-size:18px">sync</span>
                                        </button>
                                    @endif
                                    <a href="{{ $this->isReseller ? route('reseller-portal.billing.payments.show', $payment->id) : route('billing.payments.show', $payment->id) }}" class="inline-flex items-center justify-center w-8 h-8 rounded-md bg-blue-50 hover:bg-blue-100 text-blue-600 dark:bg-blue-900/20 dark:hover:bg-blue-900/40 dark:text-blue-400 transition-colors" title="Lihat Detail">
                                        <span class="material-symbols-outlined notranslate" translate="no" style="font-size:18px">visibility</span>
                                    </a>
                                    @if($payment->invoices->count() > 0)
                                        <a href="{{ $this->isReseller ? route('reseller-portal.billing.invoices.show', $payment->invoices->first()->id) : route('billing.invoices.show', $payment->invoices->first()->id) }}" class="inline-flex items-center justify-center w-8 h-8 rounded-md bg-emerald-50 hover:bg-emerald-100 text-emerald-600 dark:bg-emerald-900/20 dark:hover:bg-emerald-900/40 dark:text-emerald-400 transition-colors" title="Print Invoice">
                                            <span class="material-symbols-outlined notranslate" translate="no" style="font-size:18px">print</span>
                                        </a>
                                    @endif
                                    @if($cleanPhone)
                                        @php
                                            $waPayText = "Halo *" . ($payment->customer?->name ?? 'Pelanggan') . "*,\n\n"
                                                . "Pembayaran tagihan Anda telah kami terima:\n"
                                                . "• No. Referensi: *" . ($payment->reference_number ?? ('PAY-' . str_pad($payment->id, 6, '0', STR_PAD_LEFT))) . "*\n"
                                                . "• Nominal: *Rp " . number_format($payment->amount, 0, ',', '.') . "*\n"
                                                . "• Metode: " . strtoupper(str_replace('_', ' ', $payment->method ?? '-')) . " (" . strtoupper($payment->gateway ?? 'MANUAL') . ")\n"
                                                . "• Tanggal: " . ($payment->paid_at ? $payment->paid_at->format('d/m/Y H:i') : ($payment->created_at ? $payment->created_at->format('d/m/Y H:i') : '-')) . " WIB\n"
                                                . "• Status: *" . ($payment->status === 'success' ? 'SUKSES' : strtoupper($payment->status)) . "*\n\n"
                                                . "Terima kasih atas pembayaran Anda.";
                                            $waPayUrl = "https://wa.me/" . $cleanPhone . "?text=" . rawurlencode($waPayText);
                                        @endphp
                                        <a href="{{ $waPayUrl }}" target="_blank" rel="noopener noreferrer" class="inline-flex items-center justify-center w-8 h-8 rounded-md bg-emerald-50 hover:bg-emerald-100 text-emerald-600 dark:bg-emerald-900/20 dark:hover:bg-emerald-900/40 dark:text-emerald-400 transition-colors" title="Kirim Konfirmasi via WhatsApp ({{ $payment->customer->phone }})">
                                            <svg class="w-4 h-4 fill-current" viewBox="0 0 24 24">
                                                <path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946.003-6.556 5.338-11.891 11.893-11.891 3.181.001 6.167 1.24 8.413 3.488 2.245 2.248 3.481 5.236 3.48 8.414-.003 6.557-5.338 11.892-11.893 11.892-1.99-.001-3.951-.5-5.688-1.448l-6.305 1.654zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.886-4.434 9.889-9.885.002-5.462-4.415-9.89-9.881-9.892-5.452 0-9.887 4.434-9.889 9.884-.001 2.225.651 3.891 1.746 5.634l-.999 3.648 3.742-.981zm11.387-5.464c-.074-.124-.272-.198-.57-.347-.297-.149-1.758-.868-2.031-.967-.272-.099-.47-.149-.669.149-.198.297-.768.967-.941 1.165-.173.198-.347.223-.644.074-.297-.149-1.255-.462-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.297-.347.446-.521.151-.172.2-.296.3-.495.099-.198.05-.372-.025-.521-.075-.148-.669-1.611-.916-2.206-.242-.579-.487-.501-.669-.51l-.57-.01c-.198 0-.52.074-.792.372s-1.04 1.016-1.04 2.479 1.065 2.876 1.213 3.074c.149.198 2.095 3.2 5.076 4.487.709.306 1.263.489 1.694.626.712.226 1.36.194 1.872.118.571-.085 1.758-.719 2.006-1.413.248-.695.248-1.29.173-1.414z"/>
                                            </svg>
                                        </a>
                                    @endif
                                    @can('delete', $payment)
                                        <button wire:click="delete({{ $payment->id }})" onclick="confirm('Yakin ingin menghapus data pembayaran ini?') || event.stopImmediatePropagation()" class="inline-flex items-center justify-center w-8 h-8 rounded-md bg-red-50 hover:bg-red-100 text-red-600 dark:bg-red-900/20 dark:hover:bg-red-900/40 dark:text-red-400 transition-colors" title="Hapus">
                                            <span class="material-symbols-outlined notranslate" translate="no" style="font-size:18px">delete</span>
                                        </button>
                                    @endcan
                                </div>
                            </td>
                            <td class="px-4 py-3 font-medium text-slate-900 dark:text-slate-100 whitespace-nowrap">
                                <div>{{ $payment->reference_number ?? ('PAY-' . str_pad($payment->id, 6, '0', STR_PAD_LEFT)) }}</div>
                                @if($payment->gateway_transaction_id)
                                    <div class="text-[10px] text-slate-400 font-mono">{{ $payment->gateway_transaction_id }}</div>
                                @endif
                            </td>
                            <td class="px-4 py-3 whitespace-nowrap">
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-mono font-medium bg-slate-100 text-slate-700 dark:bg-slate-700 dark:text-slate-200">
                                    {{ $payment->customer?->code ?? '-' }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-slate-700 dark:text-slate-300">
                                <div class="font-medium text-slate-900 dark:text-slate-100">{{ $payment->customer?->name ?? '-' }}</div>
                                @if($payment->customer?->phone)
                                    <div class="text-xs text-slate-500 dark:text-slate-400 font-mono">{{ $payment->customer->phone }}</div>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-slate-600 dark:text-slate-400 whitespace-nowrap">
                                @if($payment->invoices->count() > 0)
                                    @foreach($payment->invoices as $inv)
                                        <a href="{{ $this->isReseller ? route('reseller-portal.billing.invoices.show', $inv->id) : route('billing.invoices.show', $inv->id) }}" class="inline-block text-blue-600 dark:text-blue-400 hover:underline font-mono text-xs mr-1">
                                            {{ $inv->invoice_number }}
                                        </a>
                                    @endforeach
                                @else
                                    <span class="text-slate-400 text-xs">-</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-slate-600 dark:text-slate-400 whitespace-nowrap text-xs">
                                <div>{{ $payment->paid_at?->format('d M Y') ?? ($payment->created_at?->format('d M Y') ?? '-') }}</div>
                                <div class="text-[10px] text-slate-400">{{ $payment->paid_at?->format('H:i') ?? ($payment->created_at?->format('H:i') ?? '') }} WIB</div>
                            </td>
                            <td class="px-4 py-3 text-slate-600 dark:text-slate-400 whitespace-nowrap">
                                <div class="text-xs font-semibold uppercase text-slate-800 dark:text-slate-200">
                                    {{ str_replace('_', ' ', $payment->method ?? '-') }}
                                </div>
                                <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-medium {{ ($payment->gateway && strtolower($payment->gateway) !== 'manual') ? 'bg-indigo-50 text-indigo-700 dark:bg-indigo-900/30 dark:text-indigo-400 border border-indigo-200 dark:border-indigo-800' : 'bg-slate-100 text-slate-600 dark:bg-slate-700 dark:text-slate-400' }}">
                                    {{ strtoupper($payment->gateway ?? 'MANUAL') }}
                                </span>
                            </td>
                            <td class="px-4 py-3 whitespace-nowrap">
                                @if($payment->status === 'success')
                                    <span class="inline-flex items-center gap-1.5 px-2 py-1 rounded-md text-xs font-medium bg-emerald-100 text-emerald-700 dark:bg-emerald-900/50 dark:text-emerald-400"><span class="material-symbols-outlined notranslate" style="font-size:14px" translate="no">check_circle</span> Sukses</span>
                                @elseif($payment->status === 'pending')
                                    <span class="inline-flex items-center gap-1.5 px-2 py-1 rounded-md text-xs font-medium bg-amber-100 text-amber-700 dark:bg-amber-900/50 dark:text-amber-400"><span class="material-symbols-outlined notranslate" style="font-size:14px" translate="no">schedule</span> Menunggu</span>
                                @elseif($payment->status === 'failed')
                                    <span class="inline-flex items-center gap-1.5 px-2 py-1 rounded-md text-xs font-medium bg-red-100 text-red-700 dark:bg-red-900/50 dark:text-red-400"><span class="material-symbols-outlined notranslate" style="font-size:14px" translate="no">cancel</span> Gagal</span>
                                @else
                                    <span class="inline-flex items-center gap-1.5 px-2 py-1 rounded-md text-xs font-medium bg-slate-100 text-slate-700 dark:bg-slate-800 dark:text-slate-400">{{ ucfirst($payment->status) }}</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-right font-mono font-medium text-emerald-600 dark:text-emerald-400 whitespace-nowrap">
                                Rp {{ number_format($payment->amount, 0, ',', '.') }}
                            </td>
                            @if(!$this->isReseller)
                            <td class="px-4 py-3 text-slate-600 dark:text-slate-400 whitespace-nowrap">
                                @php
                                    $resellerName = $payment->customer?->reseller?->name ?? ($payment->reseller?->name ?? null);
                                @endphp
                                @if($resellerName)
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-medium bg-purple-50 text-purple-700 dark:bg-purple-900/30 dark:text-purple-300 border border-purple-200 dark:border-purple-800">
                                        <span class="material-symbols-outlined notranslate text-[12px]">store</span> {{ $resellerName }}
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-medium bg-slate-100 text-slate-600 dark:bg-slate-700 dark:text-slate-300">
                                        Direct / ISP
                                    </span>
                                @endif
                            </td>
                            @endif
                        </tr>
                    @empty
                        <tr>
                            <td colspan="{{ $this->isReseller ? 10 : 11 }}" class="px-4 py-12 text-center text-slate-500 dark:text-slate-400">
                                <div class="flex flex-col items-center justify-center">
                                    <span class="material-symbols-outlined notranslate mb-2 text-slate-300 dark:text-slate-600 dark:text-slate-400" translate="no" style="font-size:48px">payments</span>
                                    <p class="text-lg font-medium text-slate-900 dark:text-slate-100 mt-2">Tidak Ada Data</p>
                                    <p class="text-sm mt-1">Belum ada riwayat pembayaran yang ditemukan.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
            @if($payments->hasPages())
                <div class="px-4 py-3 border-t border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800/50">
                    {{ $payments->links() }}
                </div>
            @endif
        </div>
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    document.addEventListener('livewire:init', () => {
        Livewire.on('swal:alert', (data) => {
            const isDark = document.documentElement.classList.contains('dark');
            const payload = Array.isArray(data) ? data[0] : data;
            Swal.fire({
                icon: payload.icon || 'info',
                title: payload.title || 'Notifikasi',
                text: payload.text || '',
                background: isDark ? '#1e293b' : '#fff',
                color: isDark ? '#f8fafc' : '#0f172a',
                confirmButtonColor: '#3b82f6'
            });
        });
    });
</script>
@endpush