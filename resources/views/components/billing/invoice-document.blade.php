@props(['invoice', 'company' => null, 'isPortal' => null])
@php
    $isPortal = $isPortal ?? request()->is('customer-portal*');

    $companyAddress = collect([
        $company['address'] ?? '',
        $company['city'] ?? '',
        $company['province'] ?? $company['lrovince'] ?? '',
    ])->filter()->join(', ');

    $showPartner = \App\Services\Pengaturan\CompanySettingsService::shouldShowPartner($company ?? []);

    $isPaid = $invoice->status === 'paid';
    $sisaTagihan = max(0, $invoice->total_amount - $invoice->paid_amount);

    $manualBanks = [];
    try {
        $setting = \App\Models\Setting::where('key', 'payment_gateway.manual_transfer')->first();
        if ($setting && isset($setting->value['bank_accounts'])) {
            $manualBanks = collect($setting->value['bank_accounts'])
                ->filter(fn($b) => $b['active'] ?? true)
                ->toArray();
        }
    } catch (\Exception $e) {}
@endphp

<!-- START: GLOBAL INVOICE DOCUMENT -->
<div class="invoice-document-wrapper w-full">
    <style>
        /* Print/PDF Base Rules */
        @media print {
            @page {
                size: A4 portrait;
                margin: 10mm;
            }
            body {
                background: #FFFFFF !important;
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
                margin: 0 !important;
                padding: 0 !important;
                font-family: Arial, Helvetica, sans-serif !important;
            }
            .no-print, nav, aside, button, .sidebar, .navbar {
                display: none !important;
            }
            .invoice-document {
                background: #FFFFFF !important;
                box-shadow: none !important;
                border: none !important;
                padding: 0 !important;
                margin: 0 !important;
                width: 100% !important;
                max-width: none !important;
                color: #111827 !important;
            }
            .invoice-document .border, 
            .invoice-document .border-b, 
            .invoice-document .border-t, 
            .invoice-document .border-l, 
            .invoice-document .border-r {
                border-color: #292b2bff !important;
            }
            .invoice-document .text-slate-800,
            .invoice-document .text-slate-900,
            .invoice-document .dark\:text-slate-100,
            .invoice-document .dark\:text-slate-200,
            .invoice-document .text-primary-600 {
                color: #111827 !important;
            }
            .invoice-document .text-slate-500,
            .invoice-document .text-slate-600,
            .invoice-document .dark\:text-slate-400 {
                color: #4B5563 !important;
            }
            .invoice-document .bg-slate-50,
            .invoice-document .bg-emerald-50,
            .invoice-document .bg-red-50 {
                background-color: transparent !important;
            }
            .print-grid-cols-2 { grid-template-columns: repeat(2, minmax(0, 1fr)) !important; }
            .print-w-1-2 { width: 50% !important; }
            .print-flex-row { flex-direction: row !important; }
            .print-overflow-visible { overflow: visible !important; }
            .print-min-w-full { min-width: 100% !important; }
            .print-text-right { text-align: right !important; }
            .print-mt-0 { margin-top: 0 !important; }
            .print-items-end { align-items: flex-end !important; }
            .print-items-center { align-items: center !important; }
            .print-text-3xl { font-size: 1.875rem !important; line-height: 2.25rem !important; }
            .print-hidden-line { display: none !important; }
            .print-show-line { display: block !important; }
            .portal-screen-only { display: none !important; }
            .portal-print-only { display: block !important; }
        }
        @media screen {
            .portal-print-only { display: none !important; }
            .invoice-document-wrapper {
                background: transparent;
                display: flex;
                justify-content: center;
                width: 100%;
            }
            .invoice-document {
                width: 100%;
                max-width: 210mm;
                min-height: auto;
                padding: 1.5rem;
                box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05), 0 2px 4px -1px rgba(0, 0, 0, 0.03);
                border-radius: 8px;
            }
            @media (min-width: 640px) {
                .invoice-document {
                    padding: 2.5rem;
                }
            }
        }
    </style>

    @if($isPortal)
        {{-- ========================================================
             CUSTOMER PORTAL: MOBILE SCREEN VIEW
             ======================================================== --}}
        <div class="portal-screen-only w-full space-y-4 text-slate-800 dark:text-slate-200">
            <!-- 1. Header Identitas Perusahaan -->
            <div class="flex items-center gap-3 pb-3 border-b border-slate-200 dark:border-slate-800">
                <div class="h-12 w-14 rounded-lg bg-slate-50 dark:bg-slate-800 flex items-center justify-center p-1 shrink-0 border border-slate-200/80 dark:border-slate-700">
                    @if(!empty($company['logo_url']))
                        <img src="{{ asset(ltrim($company['logo_url'], '/')) }}" alt="Logo" class="max-h-full max-w-full object-contain">
                    @else
                        <span class="font-bold text-slate-400 text-xs">LOGO</span>
                    @endif
                </div>
                <div class="min-w-0 flex-1">
                    <h3 class="font-bold text-slate-900 dark:text-slate-100 text-sm tracking-tight truncate">{{ ($company['name'] ?? '') ?: 'ISP INDONESIA' }}</h3>
                    @if(!empty($company['email']))
                        <p class="text-xs text-slate-500 dark:text-slate-400 truncate">{{ $company['email'] }}</p>
                    @elseif(!empty($company['mobile']))
                        <p class="text-xs text-slate-500 dark:text-slate-400 truncate">{{ $company['mobile'] }}</p>
                    @endif
                </div>
                @if($showPartner && !empty($company['partner_name']))
                    <div class="text-right shrink-0">
                        <span class="text-[10px] text-slate-400 block">Mitra</span>
                        <span class="text-xs font-semibold text-slate-700 dark:text-slate-300">{{ $company['partner_name'] }}</span>
                    </div>
                @endif
            </div>

            <!-- 2. Tagihan & Periode Card -->
            <div class="bg-slate-50 dark:bg-slate-800/60 rounded-xl p-3 border border-slate-200/80 dark:border-slate-700/60 space-y-2">
                <div class="flex items-center justify-between gap-2">
                    <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Nomor Invoice</span>
                    <span class="font-mono font-bold text-xs text-slate-900 dark:text-slate-100 truncate">#{{ $invoice->invoice_number ?? $invoice->id }}</span>
                </div>
                <div class="grid grid-cols-2 gap-2 pt-2 border-t border-slate-200/60 dark:border-slate-700/60 text-xs">
                    <div>
                        <span class="text-[10px] text-slate-400 block">Tgl Terbit</span>
                        <span class="font-semibold text-slate-700 dark:text-slate-300">{{ $invoice->issue_date ? $invoice->issue_date->format('d M Y') : '-' }}</span>
                    </div>
                    <div class="text-right">
                        <span class="text-[10px] text-slate-400 block">Jatuh Tempo</span>
                        <span class="font-semibold text-slate-700 dark:text-slate-300">{{ $invoice->due_date ? $invoice->due_date->format('d M Y') : '-' }}</span>
                    </div>
                </div>
            </div>

            <!-- 3. Status Tagihan Banner -->
            <div class="rounded-xl p-4 border {{ $isPaid ? 'bg-emerald-50/70 border-emerald-200 dark:bg-emerald-950/20 dark:border-emerald-800/60 text-emerald-900 dark:text-emerald-200' : 'bg-rose-50/70 border-rose-200 dark:bg-rose-950/20 dark:border-rose-800/60 text-rose-900 dark:text-rose-200' }}">
                <div class="flex items-center justify-between mb-1">
                    <span class="text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">Status Pembayaran</span>
                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-bold tracking-wide {{ $isPaid ? 'bg-emerald-600 text-white' : 'bg-rose-600 text-white' }}">
                        <span class="material-symbols-outlined text-xs">{{ $isPaid ? 'check_circle' : 'pending' }}</span>
                        {{ $isPaid ? 'LUNAS' : 'BELUM LUNAS' }}
                    </span>
                </div>
                <div class="mt-2">
                    <span class="text-[11px] text-slate-500 dark:text-slate-400 block">{{ $isPaid ? 'Total Telah Dibayar' : 'Sisa Tagihan yang Harus Dibayar' }}</span>
                    <p class="text-2xl font-black font-mono mt-0.5 {{ $isPaid ? 'text-emerald-600 dark:text-emerald-400' : 'text-rose-600 dark:text-rose-400' }}">
                        Rp {{ number_format($isPaid ? $invoice->paid_amount : $sisaTagihan, 0, ',', '.') }}
                    </p>
                </div>
            </div>

            <!-- 4. Ditagihkan Kepada -->
            <div class="bg-slate-50 dark:bg-slate-800/60 border border-slate-200/80 dark:border-slate-700/60 rounded-xl p-3.5 space-y-1">
                <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400 block mb-1">Ditagihkan Kepada</span>
                <p class="font-bold text-slate-900 dark:text-slate-100 text-sm">{{ $invoice->customer?->name ?? 'Pelanggan' }}</p>
                <div class="flex flex-wrap items-center gap-x-3 gap-y-0.5 text-xs text-slate-500 dark:text-slate-400">
                    @if(!empty($invoice->customer?->code))
                        <span class="font-mono">ID: {{ $invoice->customer->code }}</span>
                    @endif
                    @if(!empty($invoice->customer?->phone))
                        <span>Telp: {{ $invoice->customer->phone }}</span>
                    @endif
                </div>
                @if(!empty($invoice->customer?->address))
                    <p class="text-xs text-slate-500 dark:text-slate-400 pt-1 border-t border-slate-200/50 dark:border-slate-700/50 mt-1 leading-relaxed">{{ $invoice->customer->address }}</p>
                @endif
            </div>

            <!-- 5. Rincian Item Layanan & Total -->
            <div class="border border-slate-200 dark:border-slate-700 rounded-xl overflow-hidden shadow-xs">
                <div class="bg-slate-50 dark:bg-slate-800/80 px-3.5 py-2.5 border-b border-slate-200 dark:border-slate-700 flex items-center justify-between">
                    <span class="font-bold text-xs text-slate-700 dark:text-slate-300 uppercase tracking-wider">Rincian Layanan</span>
                    <span class="text-[11px] text-slate-400">{{ count($invoice->item_details ?? ($invoice->items ?? [])) }} Item</span>
                </div>
                <div class="divide-y divide-slate-100 dark:divide-slate-800">
                    @forelse($invoice->item_details ?? ($invoice->items ?? []) as $item)
                        <div class="p-3.5 flex justify-between items-start gap-3">
                            <div class="min-w-0 flex-1">
                                <p class="font-semibold text-xs text-slate-800 dark:text-slate-200 leading-snug">{{ is_array($item) ? ($item['description'] ?? 'Item Layanan') : ($item->description ?? 'Item Layanan') }}</p>
                                <p class="text-[11px] text-slate-400 mt-0.5">Periode: {{ $invoice->issue_date ? $invoice->issue_date->format('M Y') : '-' }}</p>
                            </div>
                            <p class="font-mono font-bold text-xs text-slate-900 dark:text-slate-100 shrink-0">
                                Rp {{ number_format(is_array($item) ? ($item['subtotal'] ?? 0) : ($item->subtotal ?? $item->total ?? 0), 0, ',', '.') }}
                            </p>
                        </div>
                    @empty
                        <div class="p-4 text-center text-xs text-slate-400 italic">Tidak ada detail layanan.</div>
                    @endforelse
                </div>
                <div class="bg-slate-50/70 dark:bg-slate-800/40 p-3.5 border-t border-slate-200 dark:border-slate-700 space-y-1.5 text-xs">
                    <div class="flex justify-between text-slate-600 dark:text-slate-400">
                        <span>Subtotal</span>
                        <span class="font-mono font-semibold">Rp {{ number_format($invoice->subtotal, 0, ',', '.') }}</span>
                    </div>
                    @if($invoice->tax_amount > 0)
                    <div class="flex justify-between text-slate-600 dark:text-slate-400">
                        <span>PPN {{ $invoice->tax_rate > 0 ? '('.$invoice->tax_rate.'%)' : '' }}</span>
                        <span class="font-mono font-semibold">Rp {{ number_format($invoice->tax_amount, 0, ',', '.') }}</span>
                    </div>
                    @endif
                    <div class="flex justify-between font-bold text-sm text-slate-900 dark:text-white pt-2 border-t border-slate-200 dark:border-slate-700">
                        <span>Total Tagihan</span>
                        <span class="font-mono text-base text-teal-600 dark:text-teal-400">Rp {{ number_format($invoice->total_amount, 0, ',', '.') }}</span>
                    </div>
                    @if($invoice->paid_amount > 0)
                    <div class="flex justify-between text-xs text-emerald-600 dark:text-emerald-400 pt-1">
                        <span>Telah Dibayar</span>
                        <span class="font-mono font-bold">Rp {{ number_format($invoice->paid_amount, 0, ',', '.') }}</span>
                    </div>
                    @endif
                </div>
            </div>

            <!-- 6. Instruksi Pembayaran (Transfer Bank Manual jika ada) -->
            @if(!$isPaid && count($manualBanks) > 0)
                <div class="bg-slate-50 dark:bg-slate-800/60 border border-slate-200/80 dark:border-slate-700/60 rounded-xl p-3.5 space-y-2">
                    <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400 block">Transfer Manual</span>
                    <div class="space-y-2">
                        @foreach($manualBanks as $bank)
                            <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-lg p-2.5 flex justify-between items-center gap-2">
                                <div class="min-w-0">
                                    <p class="font-bold text-slate-800 dark:text-slate-200 text-xs">{{ strtoupper($bank['bank_name'] ?? '') }}</p>
                                    <p class="text-[10px] text-slate-500 dark:text-slate-400 truncate">a.n. {{ $bank['account_holder'] ?? '-' }}</p>
                                </div>
                                <div class="flex items-center gap-1.5 shrink-0">
                                    <span class="font-mono font-bold text-teal-600 dark:text-teal-400 text-xs">{{ $bank['account_number'] ?? '' }}</span>
                                    <button type="button"
                                            onclick="navigator.clipboard && navigator.clipboard.writeText('{{ $bank['account_number'] ?? '' }}')"
                                            class="p-1 rounded hover:bg-slate-100 dark:hover:bg-slate-800 text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 transition-colors"
                                            title="Salin No Rekening">
                                        <span class="material-symbols-outlined text-[15px]">content_copy</span>
                                    </button>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            <!-- 7. Footer & QR Signature -->
            <div class="pt-3 border-t border-slate-200 dark:border-slate-700 text-center space-y-2">
                <div class="flex justify-center">
                    <img src="https://api.qrserver.com/v1/create-qr-code/?size=70x70&data={{ urlencode('Disahkan secara digital. Inv: ' . ($invoice->invoice_number ?? '')) }}&color=0f172a" alt="QR Digital" class="w-14 h-14 opacity-80 rounded">
                </div>
                <p class="text-[11px] font-bold text-slate-700 dark:text-slate-300">{{ $company['name'] ?? 'Manajemen' }}</p>
                <p class="text-[10px] text-slate-400 leading-normal">Terima kasih telah menggunakan layanan internet kami.</p>
            </div>
        </div>

        {{-- ========================================================
             CUSTOMER PORTAL: FORMAL A4 DOCUMENT (PRINT ONLY)
             ======================================================== --}}
        <div class="portal-print-only w-full">
            @include('components.billing.partials.invoice-a4-document', [
                'invoice' => $invoice,
                'company' => $company,
                'companyAddress' => $companyAddress,
                'showPartner' => $showPartner,
                'isPaid' => $isPaid,
                'sisaTagihan' => $sisaTagihan,
                'manualBanks' => $manualBanks,
            ])
        </div>
    @else
        {{-- ========================================================
             ADMIN & RESELLER PORTAL: A4 DOCUMENT (SCREEN + PRINT)
             ======================================================== --}}
        @include('components.billing.partials.invoice-a4-document', [
            'invoice' => $invoice,
            'company' => $company,
            'companyAddress' => $companyAddress,
            'showPartner' => $showPartner,
            'isPaid' => $isPaid,
            'sisaTagihan' => $sisaTagihan,
            'manualBanks' => $manualBanks,
        ])
    @endif
</div>
<!-- END: GLOBAL INVOICE DOCUMENT -->
