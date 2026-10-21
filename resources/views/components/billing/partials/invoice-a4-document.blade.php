<article class="invoice-document relative overflow-hidden text-sm bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100">
    <!-- HEADER SECTION -->
    <header class="flex flex-row justify-between items-start gap-4 border-b border-slate-300 dark:border-slate-600 pb-4 mb-6 print-flex-row">
        <div class="flex flex-row items-center gap-4">
            <!-- ISP Info -->
            <div class="flex items-center gap-4">
                <div class="h-20 w-24 sm:h-24 sm:w-28 rounded flex items-center justify-center p-1 shrink-0">
                    @if(!empty($company['logo_url']))
                        <img src="{{ asset(ltrim($company['logo_url'], '/')) }}" alt="Logo" class="w-full h-full object-contain">
                    @else
                        <span class="font-bold text-slate-400 text-xs text-center">LOGO</span>
                    @endif
                </div>
                <div>
                    <h1 class="text-base sm:text-lg font-bold text-slate-900 dark:text-slate-100 tracking-tight uppercase">{{ ($company['name'] ?? '') ?: 'ISP INDONESIA' }}</h1>
                    <p class="text-xs text-slate-600 dark:text-slate-400 mt-1 max-w-xs leading-relaxed">{!! nl2br(e($companyAddress)) !!}</p>
                    <div class="text-xs text-slate-600 dark:text-slate-400 mt-1">
                        @if (($company['mobile'] ?? '') !== '')<span>Telp: {{ $company['mobile'] }}</span><br>@endif
                        @if (($company['email'] ?? '') !== '')<span>Email: {{ $company['email'] }}</span>@endif
                    </div>
                </div>
            </div>

            @if($showPartner)
                <div class="block h-12 w-px bg-slate-300 mx-2"></div>
                <!-- Partner Info -->
                <div class="flex items-center gap-4">
                    <div class="h-14 w-14 rounded flex items-center justify-center p-1 shrink-0">
                        @if(!empty($company['partner_logo_url']))
                            <img src="{{ asset(ltrim($company['partner_logo_url'], '/')) }}" alt="Partner Logo" class="w-full h-full object-contain">
                        @else
                            <svg class="w-8 h-8 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V1a2 2 0 002-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path></svg>
                        @endif
                    </div>
                    <div>
                        <h3 class="text-sm sm:text-base font-bold text-slate-800 dark:text-slate-200">{{ ($company['partner_name'] ?? '') ?: 'Mitra' }}</h3>
                        <p class="text-[10px] text-slate-500 dark:text-slate-400 mt-0.5">Mitra Resmi</p>
                        <div class="text-[10px] text-slate-500 dark:text-slate-400 mt-0.5">
                            @if (($company['partner_mobile'] ?? '') !== '')<span>{{ $company['partner_mobile'] }}</span>@endif
                            @if (($company['partner_phone'] ?? '') !== '')<span>{{ $company['partner_phone'] }}</span>@endif
                        </div>
                    </div>
                </div>
            @endif
        </div>

        <div class="w-1/2 text-right mt-0 print-w-1-2 print-text-right">
            <h2 class="text-3xl font-bold text-slate-800 dark:text-slate-200 tracking-widest uppercase print-text-3xl">INVOICE</h2>
            <p class="text-sm font-mono text-slate-600 dark:text-slate-400 mt-1">#{{ $invoice->invoice_number ?? $invoice->id }}</p>
            <table class="mt-4 w-full text-right text-xs flex flex-col items-end">
                <tr>
                    <td class="text-slate-500 dark:text-slate-400 py-1 pr-4 w-auto">Tanggal</td>
                    <td class="font-semibold text-slate-800 dark:text-slate-200 py-1">{{ $invoice->issue_date ? $invoice->issue_date->format('d M Y') : '-' }}</td>
                </tr>
                <tr>
                    <td class="text-slate-500 dark:text-slate-400 py-1 pr-4">Jatuh Tempo</td>
                    <td class="font-semibold text-slate-800 dark:text-slate-200 py-1">{{ $invoice->due_date ? $invoice->due_date->format('d M Y') : '-' }}</td>
                </tr>
            </table>
        </div>
    </header>

    <!-- BILL TO & STATUS SECTION -->
    <section class="invoice-section grid grid-cols-2 gap-6 mb-8 print-grid-cols-2">
        <div class="bg-slate-50 dark:bg-slate-900/50 border border-slate-200 dark:border-slate-700 rounded p-5">
            <h3 class="text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-3">DITAGIHKAN KEPADA</h3>
            <p class="text-base font-bold text-slate-900 dark:text-slate-100 mb-1">{{ $invoice->customer?->name ?? 'Pelanggan' }}</p>
            @if(!empty($invoice->customer?->code))
                <p class="text-xs text-slate-600 dark:text-slate-400 font-mono mb-2">ID: {{ $invoice->customer->code }}</p>
            @endif
            <div class="text-xs text-slate-600 dark:text-slate-400 space-y-1">
                @if(!empty($invoice->customer?->address))<p>{{ $invoice->customer->address }}</p>@endif
                @if(!empty($invoice->customer?->phone))<p>{{ $invoice->customer->phone }}</p>@endif
            </div>
        </div>

        <div class="border border-slate-200 dark:border-slate-700 rounded p-5 flex flex-col justify-center items-end text-right print-items-end print-text-right">
            <h3 class="text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-3 w-full text-right print-text-right">STATUS PEMBAYARAN</h3>
            <div class="mb-3 w-full text-right print-text-right">
                <span class="inline-flex px-3 py-1.5 rounded-sm text-xs font-bold tracking-widest border {{ $isPaid ? 'border-emerald-500 text-emerald-600 bg-emerald-50 dark:bg-emerald-900/30' : 'border-red-500 text-red-600 bg-red-50 dark:bg-red-900/30' }}">
                    {{ $isPaid ? 'LUNAS' : 'BELUM LUNAS' }}
                </span>
            </div>
            <p class="text-3xl font-black text-slate-900 dark:text-slate-100 tracking-tight">Rp {{ number_format($isPaid ? $invoice->paid_amount : $sisaTagihan, 0, ',', '.') }}</p>
            <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">{{ $isPaid ? 'Total Dibayar' : 'Sisa Tagihan' }}</p>
        </div>
    </section>

    <!-- ITEMS SECTION -->
    <section class="invoice-section mb-8">
        <div class="w-full overflow-x-auto print-overflow-visible">
            <table class="w-full text-left border-collapse invoice-table min-w-full">
                <thead>
                    <tr class="bg-slate-50 dark:bg-slate-900/50 border-b border-t border-slate-300 dark:border-slate-600">
                        <th class="py-3 px-4 text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider w-12 text-center">No</th>
                        <th class="py-3 px-4 text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider">Deskripsi Layanan</th>
                        <th class="py-3 px-4 text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider text-center">Periode</th>
                        <th class="py-3 px-4 text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider text-right">Jumlah</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200 dark:divide-slate-700 border-b border-slate-300 dark:border-slate-600">
                    @forelse($invoice->item_details ?? ($invoice->items ?? []) as $idx => $item)
                        <tr>
                            <td class="py-4 px-4 text-center text-slate-600 dark:text-slate-400">{{ $loop->iteration }}</td>
                            <td class="py-4 px-4">
                                <p class="text-sm font-semibold text-slate-900 dark:text-slate-100">{{ is_array($item) ? ($item['description'] ?? 'Item Layanan') : ($item->description ?? 'Item Layanan') }}</p>
                            </td>
                            <td class="py-4 px-4 text-center text-slate-600 dark:text-slate-400 whitespace-nowrap">
                                {{ $invoice->issue_date ? $invoice->issue_date->format('M Y') : '-' }}
                            </td>
                            <td class="py-4 px-4 text-right font-medium text-slate-900 dark:text-slate-100 whitespace-nowrap">
                                Rp {{ number_format(is_array($item) ? ($item['subtotal'] ?? 0) : ($item->subtotal ?? $item->total ?? 0), 0, ',', '.') }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="py-8 text-center text-slate-500 dark:text-slate-400 text-sm italic">Tidak ada detail layanan.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>

    <!-- TOTALS & INSTRUCTIONS SECTION -->
    <section class="invoice-section grid grid-cols-2 gap-8 mb-8 print-grid-cols-2">
        <div>
            <div class="bg-slate-50 dark:bg-slate-900/50 border border-slate-200 dark:border-slate-700 rounded p-5 h-full">
                <h4 class="text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-3">Instruksi Pembayaran</h4>
                <div class="text-xs text-slate-600 dark:text-slate-400 leading-relaxed">
                    @if($isPaid)
                        <p class="font-semibold text-slate-900 dark:text-slate-100">Terima kasih, pembayaran untuk tagihan ini telah kami terima.</p>
                    @else
                        {!! nl2br(e($termsText ?? 'Silakan lakukan pembayaran sesuai dengan total tagihan sebelum tanggal jatuh tempo.')) !!}

                        @if(count($manualBanks ?? []) > 0)
                            <div class="mt-3 pt-3 border-t border-slate-200 dark:border-slate-700">
                                <p class="font-bold text-slate-800 dark:text-slate-200 mb-2">Transfer Pembayaran Manual:</p>
                                <div class="flex flex-col gap-2">
                                    @foreach($manualBanks as $bank)
                                        <div class="bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded p-2 flex justify-between items-center gap-2">
                                            <div class="min-w-0">
                                                <p class="font-bold text-slate-800 dark:text-slate-200 truncate">{{ strtoupper($bank['bank_name'] ?? '') }}</p>
                                                <p class="text-[10px] text-slate-500 dark:text-slate-400 truncate">a.n. {{ $bank['account_holder'] ?? '-' }}</p>
                                            </div>
                                            <p class="font-mono font-bold text-primary-700 tracking-wider text-xs shrink-0">{{ $bank['account_number'] ?? '' }}</p>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endif
                    @endif
                </div>
            </div>
        </div>

        <div>
            <table class="w-full text-sm">
                <tr>
                    <td class="py-2.5 text-slate-600 dark:text-slate-400">Subtotal</td>
                    <td class="py-2.5 text-right font-medium text-slate-900 dark:text-slate-100">Rp {{ number_format($invoice->subtotal, 0, ',', '.') }}</td>
                </tr>
                @if($invoice->tax_amount > 0)
                <tr>
                    <td class="py-2.5 text-slate-600 dark:text-slate-400">PPN {{ $invoice->tax_rate > 0 ? '('.$invoice->tax_rate.'%)' : '' }}</td>
                    <td class="py-2.5 text-right font-medium text-slate-900 dark:text-slate-100">Rp {{ number_format($invoice->tax_amount, 0, ',', '.') }}</td>
                </tr>
                @endif
                <tr class="border-t-2 border-slate-400">
                    <td class="py-4 font-bold text-slate-900 dark:text-slate-100 text-base uppercase tracking-wider">TOTAL</td>
                    <td class="py-4 text-right font-black text-slate-900 dark:text-slate-100 text-xl">Rp {{ number_format($invoice->total_amount, 0, ',', '.') }}</td>
                </tr>
                <tr>
                    <td class="py-2.5 text-slate-600 dark:text-slate-400 text-xs font-semibold">Sudah Dibayar</td>
                    <td class="py-2.5 text-right font-bold text-slate-900 dark:text-slate-100 text-xs">Rp {{ number_format($invoice->paid_amount, 0, ',', '.') }}</td>
                </tr>
            </table>
        </div>
    </section>

    <!-- SIGNATURE SECTION -->
    <div class="mt-8 w-full flex justify-end">
        <div class="text-center w-48">
            <p class="text-xs text-slate-600 dark:text-slate-400 mb-2">Hormat Kami,</p>
            <div class="flex justify-center mb-2">
                <img src="https://api.qrserver.com/v1/create-qr-code/?size=80x80&data={{ urlencode('Disahkan secara digital. Inv: ' . ($invoice->invoice_number ?? '')) }}&color=0f172a" alt="QR Digital Signature" class="w-16 h-16 opacity-90">
            </div>
            <p class="font-bold text-slate-800 dark:text-slate-200 text-sm border-b border-slate-400 pb-0.5 mb-1 inline-block min-w-[120px]">{{ $company['owner_name'] ?? 'Pimpinan' }}</p>
            <p class="text-[10px] text-slate-500 dark:text-slate-400 uppercase tracking-widest">{{ $company['name'] ?? 'Manajemen' }}</p>
        </div>
    </div>

    <!-- FOOTER -->
    <footer class="mt-12 pt-4 border-t border-slate-300 dark:border-slate-600 text-center text-xs text-slate-500 dark:text-slate-400">
        <p class="font-bold text-slate-700 dark:text-slate-300 mb-1">Terima kasih atas kepercayaan Anda menggunakan layanan kami.</p>
        <p>Mohon sertakan nomor invoice sebagai referensi pembayaran. Jika ada pertanyaan, hubungi tim support kami.</p>
    </footer>

    <!-- WATERMARK -->
    @if($isPaid)
    <div class="absolute inset-0 pointer-events-none flex items-center justify-center opacity-10 z-0 overflow-hidden" style="display: flex !important;">
        <div class="transform -rotate-45 text-[120px] font-black text-emerald-700 leading-none">PAID</div>
    </div>
    @endif
</article>
