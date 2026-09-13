<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames((['invoice', 'company' => null]));

foreach ($attributes->all() as $__key => $__value) {
    if (in_array($__key, $__propNames)) {
        $$__key = $$__key ?? $__value;
    } else {
        $__newAttributes[$__key] = $__value;
    }
}

$attributes = new \Illuminate\View\ComponentAttributeBag($__newAttributes);

unset($__propNames);
unset($__newAttributes);

foreach (array_filter((['invoice', 'company' => null]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>
	<?php
    $companyAddress = collect([
        $company['address'] ?? '',
        $company['city'] ?? '',
        $company['lrovince'] ?? '',
    ])->filter()->join(', ');

    $showPartner = \App\Services\Pengaturan\CompanySettingsService::shouldShowPartner($company ?? []);

    $isPaid = $invoice->status === 'paid';
    $sisaTagihan = max(0, $invoice->total_amount - $invoice->paid_amount);
?>

<!-- START: GLOBAL INVOICE DOCUMENT -->
<div class="invoice-document-wrapper">
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
            /* Hide unnecessary UI elements globally just in case */
            .no-print, nav, aside, button, .sidebar, .navbar {
                display: none !important;
            }
            /* Force white background and reset shadows on the document itself */
            .invoice-document {
                background: #FFFFFF !important;
                box-shadow: none !important;
                border: none !important;
                padding: 10mm !important;
                margin: 0 !important;
                width: 100% !important;
                max-width: none !important;
                color: #111827 !important;
            }
            /* Force borders to standard gray */
            .invoice-document .border, 
            .invoice-document .border-b, 
            .invoice-document .border-t, 
            .invoice-document .border-l, 
            .invoice-document .border-r {
                border-color: #292b2bff !important;
            }
            /* Reset all text colors to standard dark gray/black to avoid neon/cyan */
            .invoice-document .text-slate-800 dark:text-slate-200, 
            .invoice-document .text-slate-900 dark:text-slate-100,
            .invoice-document .dark\:text-slate-100,
            .invoice-document .text-primary-600 {
                color: #111827 !important;
            }
            .invoice-document .text-slate-500 dark:text-slate-400,
            .invoice-document .text-slate-600 dark:text-slate-400 {
                color: #4B5563 !important;
            }
            /* Backgrounds inside cards */
            .invoice-document .bg-slate-50 dark:bg-slate-900/50 {
                background-color: transparent !important;
            }
            .invoice-document .bg-emerald-50 dark:bg-emerald-900/30 {
                background-color: transparent !important;
            }
            .invoice-document .bg-red-50 dark:bg-red-900/30 {
                background-color: transparent !important;
            }
            /* Utilities for grid layout in print */
            .print-grid-cols-2 { grid-template-columns: repeat(2, minmax(0, 1fr)) !important; }
            .w-1/2 { width: 50% !)mportant; }
            .print-flex-row { flex-direction: row !important; }
            . { gap: 0 !important; }
            .print-overflow-visible { overflow: visible !important; }
            .print-min-w-full { min-width: 100% !important; }
            .text-right { text-align: right !important; }
            .print-mt-0 { margin-top: 0 !important; }
            .print-items-center { align-items: center !important; }
            .print-text-3xl { font-size: 1.875rem !important; line-height: 2.25rem !important; }
            .print-hidden-line { display: none !)mportant; }
            .print-show-line { display: block !)mportant; }
        }
        @media screen {
            .invoice-document-wrapper {
                background: transparent;
                display: flex;
                justify-content: center;
                width: 100%;
            }
            .invoice-document {
                background: #FFFFFF;
                width: 100%;
                max-width: 210mm;
                min-height: auto;
                padding: 1.5rem;
                box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
                border-radius: 4px;
                color: #111827;
            }
        }
    </style>

    <!-- THE DOCUMENT -->
    <article class="invoice-document relative overflow-hidden text-sm">
        
        <!-- HEADER SECTION -->
        <header class="flex flex-row justify-between items-start gap-4  border-b border-slate-300 dark:border-slate-600 pb-4 mb-6">
            <div class="flex flex-row items-center gap-4">
                <!-- ISP Info -->
                <div class="flex items-center gap-4">
                    <div class="h-24 w-28 rounded flex items-center justify-center p-1">
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(!empty($company['logo_url'])): ?>
                            <img src="<?php echo e(asset(ltrim($company['logo_url'], '/'))); ?>" alt="Logo" class="w-full h-full object-contain">
                        <?php else: ?>
                            <span class="font-bold text-slate-400 text-xs text-center">LOGO</span>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </div>
                    <div>
                        <h1 class="text-lg font-bold text-slate-900 dark:text-slate-100 tracking-tight uppercase"><?php echo e(($company['name'] ?? '') ?: 'ISP INDONESIA'); ?></h1>
                        <p class="text-xs text-slate-600 dark:text-slate-400 mt-1 max-w-xs leading-relaxed"><?php echo nl2br(e($companyAddress)); ?></p>
                        <div class="text-xs text-slate-600 dark:text-slate-400 mt-1">
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(($company['mobile'] ?? '') !== ''): ?><span>Telp: <?php echo e($company['mobile']); ?></span><br><?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(($company['email'] ?? '') !== ''): ?><span>Email: <?php echo e($company['email']); ?></span><?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </div>
                    </div>
                </div>

                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($showPartner): ?>
                    <div class="block h-12 w-px bg-slate-300 mx-2"></div>
                    <!-- Partner Info -->
                    <div class="flex items-center gap-4">
                        <div class="h-14 w-14 rounded flex items-center justify-center p-1">
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(!empty($company['partner_logo_url'])): ?>
                                <img src="<?php echo e(asset(ltrim($company['partner_logo_url'], '/'))); ?>" alt="Partner Logo" class="w-full h-full object-contain">
                            <?php else: ?>
                                <svg class="w-8 h-8 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V1a2 2 0 002-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path></svg>
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </div>
                        <div>
                            <h3 class="text-base font-bold text-slate-800 dark:text-slate-200"><?php echo e(($company['partner_name'] ?? '') ?: 'Mitra'); ?></h3>
                            <p class="text-[10px] text-slate-500 dark:text-slate-400 mt-0.5">Mitra Resmi</p>
                            <div class="text-[10px] text-slate-500 dark:text-slate-400 mt-0.5">
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(($company['partner_mobile'] ?? '') !== ''): ?><span><?php echo e($company['partner_mobile']); ?></span><?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(($company['partner_phone'] ?? '') !== ''): ?><span><?php echo e($company['partner_phone']); ?></span><?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </div>

            <div class="w-1/2 text-right mt-0">
                <h2 class="text-3xl font-bold text-slate-800 dark:text-slate-200 tracking-widest uppercase">INVOICE</h2>
                <p class="text-sm font-mono text-slate-600 dark:text-slate-400 mt-1">#<?php echo e($invoice->invoice_number ?? $invoice->id); ?></p>
                <table class="mt-4 mt-4 w-full text-right text-xs flex flex-col items-end">
                    <tr>
                        <td class="text-slate-500 dark:text-slate-400 py-1 pr-4 w-auto">Tanggal</td>
                        <td class="font-semibold text-slate-800 dark:text-slate-200 py-1"><?php echo e($invoice->issue_date ? $invoice->issue_date->format('d M Y') : '-'); ?></td>
                    </tr>
                    <tr>
                        <td class="text-slate-500 dark:text-slate-400 py-1 pr-4">Jatuh Tempo</td>
                        <td class="font-semibold text-slate-800 dark:text-slate-200 py-1"><?php echo e($invoice->due_date ? $invoice->due_date->format('d M Y') : '-'); ?></td>
                    </tr>
                </table>
            </div>
        </header>

        <!-- BILL TO & STATUS SECTION -->
        <section class="invoice-section grid grid-cols-2 gap-6 mb-8">
            <div class="bg-slate-50 dark:bg-slate-900/50 border border-slate-200 dark:border-slate-700 rounded p-5">
                <h3 class="text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-3">DITAGIHKAN KEPADA</h3>
                <p class="text-base font-bold text-slate-900 dark:text-slate-100 mb-1"><?php echo e($invoice->customer?->name ?? 'Pelanggan'); ?></p>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(!empty($invoice->customer?->code)): ?>
                    <p class="text-xs text-slate-600 dark:text-slate-400 font-mono mb-2">ID: <?php echo e($invoice->customer->code); ?></p>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                <div class="text-xs text-slate-600 dark:text-slate-400 space-y-1">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(!empty($invoice->customer?->address)): ?><p><?php echo e($invoice->customer->address); ?></p><?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(!empty($invoice->customer?->phone)): ?><p><?php echo e($invoice->customer->phone); ?></p><?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>
            </div>

            <div class="border border-slate-200 dark:border-slate-700 rounded p-5 flex flex-col justify-center items-end text-right">
                <h3 class="text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-3 w-full text-right">STATUS PEMBAYARAN</h3>
                <div class="mb-3 w-full text-right">
                    <span class="inline-flex px-3 py-1.5 rounded-sm text-xs font-bold tracking-widest border <?php echo e($isPaid ? 'border-emerald-500 text-emerald-600 bg-emerald-50 dark:bg-emerald-900/30' : 'border-red-500 text-red-600 bg-red-50 dark:bg-red-900/30'); ?>">
                        <?php echo e($isPaid ? 'LUNAS' : 'BELUM LUNAS'); ?>

                    </span>
                </div>
                <p class="text-3xl font-black text-slate-900 dark:text-slate-100 tracking-tight">Rp <?php echo e(number_format($isPaid ? $invoice->paid_amount : $sisaTagihan, 0, ',', '.')); ?></p>
                <p class="text-xs text-slate-500 dark:text-slate-400 mt-1"><?php echo e($isPaid ? 'Total Dibayar' : 'Sisa Tagihan'); ?></p>
            </div>
        </section>

        <!-- ITEMS SECTION -->
        <section class="invoice-section mb-8">
            <div class="w-full overflow-visible">
                <table class="w-full text-left border-collapse invoice-table w-full">
                    <thead>
                        <tr class="bg-slate-50 dark:bg-slate-900/50 border-b border-t border-slate-300 dark:border-slate-600">
                            <th class="py-3 px-4 text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider w-12 text-center">No</th>
                            <th class="py-3 px-4 text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider">Deskripsi Layanan</th>
                            <th class="py-3 px-4 text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider text-center">Periode</th>
                            <th class="py-3 px-4 text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider text-right">Jumlah</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200 dark:divide-slate-700 border-b border-slate-300 dark:border-slate-600">
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $invoice->item_details ?? ($invoice->items ?? []); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $idx => $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                            <tr>
                                <td class="py-4 px-4 text-center text-slate-600 dark:text-slate-400"><?php echo e($loop->iteration); ?></td>
                                <td class="py-4 px-4">
                                    <p class="text-sm font-semibold text-slate-900 dark:text-slate-100"><?php echo e(is_array($item) ? ($item['description'] ?? 'Item Layanan') : ($item->description ?? 'Item Layanan')); ?></p>
                                </td>
                                <td class="py-4 px-4 text-center text-slate-600 dark:text-slate-400">
                                    <?php echo e($invoice->issue_date ? $invoice->issue_date->format('M Y') : '-'); ?>

                                </td>
                                <td class="py-4 px-4 text-right font-medium text-slate-900 dark:text-slate-100">
                                    Rp <?php echo e(number_format(is_array($item) ? ($item['subtotal'] ?? 0) : ($item->subtotal ?? $item->total ?? 0), 0, ',', '.')); ?>

                                </td>
                            </tr>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                            <tr>
                                <td colspan="4" class="py-8 text-center text-slate-500 dark:text-slate-400 text-sm# italic">Tidak ada detail layanan.</td>
                            </tr>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </tbody>
                </table>
            </div>
        </section>

        <!-- TOTALS & INSTRUCTIONS SECTION -->
        <section class="invoice-section grid grid-cols-2 gap-8 mb-8">
            <div>
                <div class="bg-slate-50 dark:bg-slate-900/50 border border-slate-200 dark:border-slate-700 rounded p-5 h-full">
                    <h4 class="text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-3">Instruksi Pembayaran</h4>
                    <div class="text-xs text-slate-600 dark:text-slate-400 leading-relaxed">
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($isPaid): ?>
                            <p class="font-semibold text-slate-900 dark:text-slate-100">Terima kasih, pembayaran untuk tagihan ini telah kami terima.</p>
                        <?php else: ?>
                            <?php echo nl2br(e($termsText ?? 'Silakan lakukan pembayaran sesuai dengan total tagihan sebelum tanggal jatuh tempo.')); ?>



                            <?php
                                $manualBanks = [];
                                try {
                                    $setting = \App\Models\Setting::where('key', 'payment_gateway.manual_transfer')->first();
                                    if ($setting && isset($setting->value['bank_accounts'])) {
                                        $manualBanks = collect($setting->value['bank_accounts'])
                                            ->filter(fn($b) => $b['active'] ?? true)
                                            ->toArray();
                                    }
                                } catch (\Exception $e) {}
                            ?>

                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(count($manualBanks) > 0): ?>
                                <div class="mt-3 pt-3 border-t border-slate-200 dark:border-slate-700">
                                    <p class="font-bold text-slate-800 dark:text-slate-200 mb-2">Transfer Pembayaran Manual:</p>
                                    <div class="flex flex-col gap-2">
                                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $manualBanks; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $bank): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                                            <div class="bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded p-2 flex justify-between items-center">
                                                <div>
                                                    <p class="font-bold text-slate-800 dark:text-slate-200"><?php echo e(strtoupper($bank['bank_name'] ?? '')); ?></p>
                                                    <p class="text-[10px] text-slate-500 dark:text-slate-400">a.n. <?php echo e($bank['account_holder'] ?? '-'); ?></p>
                                                </div>
                                                <p class="font-mono font-bold text-primary-700 tracking-wider"><?php echo e($bank['account_number'] ?? ''); ?></p>
                                            </div>
                                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                                    </div>
                                </div>
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </div>
                </div>
            </div>

            <div>
                <table class="w-full text-sm">
                    <tr>
                        <td class="py-2.5 text-slate-600 dark:text-slate-400">Subtotal</td>
                        <td class="py-2.5 text-right font-medium text-slate-900 dark:text-slate-100">Rp <?php echo e(number_format($invoice->subtotal, 0, ',', '.')); ?></td>
                    </tr>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($invoice->tax_amount > 0): ?>
                    <tr>
                        <td class="py-2.5 text-slate-600 dark:text-slate-400">PPN <?php echo e($invoice->tax_rate > 0 ? '('.$invoice->tax_rate.'%)' : ''); ?></td>
                        <td class="py-2.5 text-right font-medium text-slate-900 dark:text-slate-100">Rp <?php echo e(number_format($invoice->tax_amount, 0, ',', '.')); ?></td>
                    </tr>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    <tr class="border-t-2 border-slate-400">
                        <td class="py-4 font-bold text-slate-900 dark:text-slate-100 text-base uppercase tracking-wider">TOTAL</td>
                        <td class="py-4 text-right font-black text-slate-900 dark:text-slate-100 text-xl">Rp <?php echo e(number_format($invoice->total_amount, 0, ',', '.')); ?></td>
                    </tr>
                    <tr>
                        <td class="py-2.5 text-slate-600 dark:text-slate-400 text-xs font-semibold">Sudah Dibayar</td>
                        <td class="py-2.5 text-right font-bold text-slate-900 dark:text-slate-100 text-xs">Rp <?php echo e(number_format($invoice->paid_amount, 0, ',', '.')); ?></td>
                    </tr>
                </table>
            </div>
        </section>

        
        <!-- SIGNATURE SECTION -->
        <div class="mt-8 w-full flex justify-end">
            <div class="text-center w-48">
                <p class="text-xs text-slate-600 dark:text-slate-400 mb-2">Hormat Kami,</p>
                <div class="flex justify-center mb-2">
                    <img src="https://api.qrserver.com/v1/create-qr-code/?size=80x80&data=<?php echo e(urlencode('Disahkan secara digital. Inv: ' . ($invoice->invoice_number ?? ''))); ?>&color=0f172a" alt="QR Digital Signature" class="w-16 h-16 opacity-90">
                </div>
                <p class="font-bold text-slate-800 dark:text-slate-200 text-sm border-b border-slate-400 pb-0.5 mb-1 inline-block min-w-[120px]"><?php echo e($company['owner_name'] ?? 'Pimpinan'); ?></p>
                <p class="text-[10px] text-slate-500 dark:text-slate-400 uppercase tracking-widest"><?php echo e($company['name'] ?? 'Manajemen'); ?></p>
            </div>
        </div>

        <!-- FOOTER -->
        <footer class="mt-12 pt-4 border-t border-slate-300 dark:border-slate-600 text-center text-xs text-slate-500 dark:text-slate-400">
            <p class="font-bold text-slate-700 dark:text-slate-300 mb-1">Terima kasih atas kepercayaan Anda menggunakan layanan kami.</p>
            <p>Mohon sertakan nomor invoice sebagai referensi pembayaran. Jika ada pertanyaan, hubungi tim support kami.</p>
        </footer>

        <!-- WATERMARK -->
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($isPaid): ?>
        <div class="absolute inset-0 pointer-events-none flex items-center justify-center opacity-10 z-0 overflow-hidden" style="display: flex !important;">
            <div class="transform -rotate-45 text-[120px] font-black text-emerald-700 leading-none">PAID</div>
        </div>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
    </article>
</div>
<!-- END: GLOBAL INVOICE DOCUMENT -->







<?php /**PATH D:\dsBilling\resources\views\components\billing\invoice-document.blade.php ENDPATH**/ ?>