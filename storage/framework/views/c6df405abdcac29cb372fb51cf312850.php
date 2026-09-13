<?php $__env->startSection('header_title', 'Detail Tagihan'); ?>

<div class="min-h-[calc(100vh-4rem)] flex flex-col pb-24 print-reset-height print-reset-padding print-reset-layout bg-slate-100 dark:bg-slate-950/50">
    
    <!-- Action Buttons (Print Only) -->
    <div class="bg-white dark:bg-slate-900 border-b border-slate-100 dark:border-slate-800 p-4 sticky top-0 z-10 print-hide shadow-sm flex gap-2">
        <button type="button" onclick="window.print()" class="flex-1 flex items-center justify-center gap-1.5 px-3 py-3 border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-700 dark:text-slate-300 text-sm font-bold rounded-xl hover:bg-slate-50 dark:bg-slate-900/50 dark:hover:bg-slate-700 transition-all active:scale-95">
            <span class="material-symbols-outlined text-[18px]">print</span>
            Cetak (A4)
        </button>
        <a href="<?php echo e(route('customer-portal.billing.invoice-print-80mm', $invoice->id)); ?>" target="_blank" class="flex-1 flex items-center justify-center gap-1.5 px-3 py-3 border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-700 dark:text-slate-300 text-sm font-bold rounded-xl hover:bg-slate-50 dark:bg-slate-900/50 dark:hover:bg-slate-700 transition-all active:scale-95">
            <span class="material-symbols-outlined text-[18px]">receipt_long</span>
            Struk (80mm)
        </a>
    </div>

    <div class="flex-1 p-4 print-reset-padding print-reset-layout space-y-6">
        
        <!-- INLINE PAYMENT METHODS SECTION -->
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($invoice->status !== 'paid'): ?>
        <div class="bg-white dark:bg-slate-900 rounded-2xl shadow-sm border border-slate-200 dark:border-slate-800 overflow-hidden print-hide">
            <div class="bg-teal-600 p-4 flex items-center gap-3">
                <span class="material-symbols-outlined text-white text-2xl">payments</span>
                <h3 class="text-lg font-bold text-white">Pembayaran</h3>
            </div>
            <div class="p-4 sm:p-6 space-y-4">
                <p class="text-sm text-slate-500">Pilih metode pembayaran di bawah ini untuk melunasi tagihan sebesar <strong class="text-slate-800 dark:text-white">Rp <?php echo e(number_format(max(0, $invoice->total_amount - $invoice->paid_amount), 0, ',', '.')); ?></strong></p>

                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $activeGateways ?? []; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $gateway): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                    <div class="p-4 bg-red-50 dark:bg-red-900/30 text-red-600 text-sm rounded-xl border border-red-100 text-center">
                        Tidak ada metode pembayaran yang dikonfigurasi.
                    </div>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(!empty($activeGateways)): ?>
                    <div class="space-y-3">
                        <div>
                            <label class="block text-xs font-semibold text-slate-600 dark:text-slate-400 mb-1.5">Pilih Payment Gateway</label>
                            <div class="relative">
                                <select wire:model.live="selectedGateway"
                                        class="w-full appearance-none px-4 py-3 pr-10 rounded-xl border-2 border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-200 text-sm focus:border-teal-500 focus:ring-2 focus:ring-teal-500/20 outline-none transition-all dark:bg-slate-900 dark:text-slate-100">
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $gatewayOptions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $k => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                                        <option value="<?php echo e($k); ?>"><?php echo e($label); ?></option>
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                                </select>
                                <span class="material-symbols-outlined text-slate-400 absolute right-3 top-1/2 -translate-y-1/2 pointer-events-none">expand_more</span>
                            </div>
                        </div>

                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($selectedGateway === 'manual_transfer'): ?>
                            <div class="border-2 border-slate-200 dark:border-slate-700 rounded-xl overflow-hidden">
                                <div class="bg-slate-50 dark:bg-slate-800 p-3 border-b border-slate-200 dark:border-slate-700 flex items-center gap-2">
                                    <span class="material-symbols-outlined text-teal-600">account_balance</span>
                                    <h4 class="font-bold text-slate-800 dark:text-white text-sm">Transfer Bank Manual</h4>
                                </div>
                                <div class="p-3 space-y-3">
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $activeGateways['manual_transfer']['bank_accounts'] ?? []; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $bank): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(!empty($bank['active'])): ?>
                                        <div class="bg-white dark:bg-slate-900 border border-slate-100 dark:border-slate-700 rounded-lg p-3 flex justify-between items-center shadow-sm">
                                            <div class="flex-1">
                                                <p class="font-bold text-teal-600 text-sm"><?php echo e($bank['bank'] ?? 'Bank'); ?></p>
                                                <p class="text-base font-mono font-bold text-slate-800 dark:text-white my-0.5 select-all"><?php echo e($bank['account_number'] ?? '-'); ?></p>
                                                <p class="text-xs text-slate-500 dark:text-slate-400">a.n <?php echo e($bank['account_name'] ?? '-'); ?></p>
                                            </div>
                                            <button type="button"
                                                    onclick="navigator.clipboard && navigator.clipboard.writeText('<?php echo e($bank['account_number'] ?? ''); ?>')"
                                                    class="ml-2 inline-flex items-center gap-1 px-2.5 py-1.5 rounded-lg bg-slate-100 hover:bg-teal-100 dark:bg-slate-800 dark:hover:bg-teal-900/30 text-slate-600 hover:text-teal-700 dark:text-slate-300 dark:hover:text-teal-300 text-xs font-semibold transition-colors">
                                                <span class="material-symbols-outlined text-[16px]">content_copy</span>
                                                Salin
                                            </button>
                                        </div>
                                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                                    <div class="flex gap-2 p-2 bg-orange-50 dark:bg-orange-900/20 text-orange-700 dark:text-orange-400 rounded-lg text-xs items-start">
                                        <span class="material-symbols-outlined text-[16px] shrink-0">info</span>
                                        <p>Setelah melakukan transfer, harap kirimkan bukti pembayaran ke WhatsApp Admin/CS.</p>
                                    </div>
                                </div>
                            </div>
                        <?php elseif($selectedGateway === 'manual_ewallet'): ?>
                            <div class="border-2 border-slate-200 dark:border-slate-700 rounded-xl overflow-hidden">
                                <div class="bg-slate-50 dark:bg-slate-800 p-3 border-b border-slate-200 dark:border-slate-700 flex items-center gap-2">
                                    <span class="material-symbols-outlined text-amber-600">wallet</span>
                                    <h4 class="font-bold text-slate-800 dark:text-white text-sm">Transfer e-Wallet Manual (GoPay / OVO / DANA / ShopeePay)</h4>
                                </div>
                                <div class="p-3 space-y-3">
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $activeGateways['manual_ewallet']['providers'] ?? []; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $ew): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(!empty($ew['active'])): ?>
                                        <div class="bg-white dark:bg-slate-900 border border-slate-100 dark:border-slate-700 rounded-lg p-3 flex justify-between items-center shadow-sm">
                                            <div class="flex-1">
                                                <p class="font-bold text-amber-600 text-sm"><?php echo e($ew['name'] ?? 'e-Wallet'); ?></p>
                                                <p class="text-base font-mono font-bold text-slate-800 dark:text-white my-0.5 select-all"><?php echo e($ew['number'] ?? '-'); ?></p>
                                                <p class="text-xs text-slate-500 dark:text-slate-400">a.n <?php echo e($ew['holder'] ?? '-'); ?></p>
                                            </div>
                                            <button type="button"
                                                    onclick="navigator.clipboard && navigator.clipboard.writeText('<?php echo e($ew['number'] ?? ''); ?>')"
                                                    class="ml-2 inline-flex items-center gap-1 px-2.5 py-1.5 rounded-lg bg-slate-100 hover:bg-amber-100 dark:bg-slate-800 dark:hover:bg-amber-900/30 text-slate-600 hover:text-amber-700 dark:text-slate-300 dark:hover:text-amber-300 text-xs font-semibold transition-colors">
                                                <span class="material-symbols-outlined text-[16px]">content_copy</span>
                                                Salin
                                            </button>
                                        </div>
                                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                                    <div class="flex gap-2 p-2 bg-orange-50 dark:bg-orange-900/20 text-orange-700 dark:text-orange-400 rounded-lg text-xs items-start">
                                        <span class="material-symbols-outlined text-[16px] shrink-0">info</span>
                                        <p>Setelah melakukan transfer ke nomor e-wallet di atas, harap kirimkan bukti pembayaran ke WhatsApp Admin/CS.</p>
                                    </div>
                                </div>
                            </div>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(request()->query('paid') == '1'): ?>
                            <div class="flex gap-3 p-3 bg-emerald-50 dark:bg-emerald-900/30 border border-emerald-200 dark:border-emerald-800 rounded-xl items-start">
                                <span class="material-symbols-outlined text-emerald-600 dark:text-emerald-400 text-[22px] shrink-0 mt-0.5">check_circle</span>
                                <div class="flex-1 min-w-0">
                                    <p class="text-sm font-bold text-emerald-800 dark:text-emerald-300 mb-1">Pembayaran Diproses</p>
                                    <p class="text-xs text-emerald-700 dark:text-emerald-400 break-words whitespace-pre-line">Pembayaran Anda sedang dalam proses verifikasi sistem. Status akan otomatis berubah menjadi Lunas setelah terkonfirmasi.</p>
                                </div>
                            </div>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($paymentErrorMessage): ?>
                            <div class="flex gap-3 p-3 bg-red-50 dark:bg-red-900/30 border border-red-200 dark:border-red-800 rounded-xl items-start animate-pulse">
                                <span class="material-symbols-outlined text-red-600 dark:text-red-400 text-[22px] shrink-0 mt-0.5">error</span>
                                <div class="flex-1 min-w-0">
                                    <p class="text-sm font-bold text-red-800 dark:text-red-300 mb-1">Gagal Memproses Pembayaran</p>
                                    <p class="text-xs text-red-700 dark:text-red-400 break-words whitespace-pre-line"><?php echo e($paymentErrorMessage); ?></p>
                                </div>
                            </div>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

                        <button type="button"
                                wire:click="proceedPay"
                                wire:loading.attr="disabled"
                                wire:target="proceedPay"
                                class="w-full relative inline-flex items-center justify-center gap-2 px-4 py-3.5 rounded-xl bg-teal-600 hover:bg-teal-700 active:scale-[0.98] text-white text-sm font-bold shadow-lg shadow-teal-600/20 transition-all disabled:opacity-60 disabled:cursor-not-allowed">
                            <span wire:loading.remove wire:target="proceedPay" class="material-symbols-outlined text-[20px]">
                                <?php echo e(in_array($selectedGateway, ['manual_transfer','manual_ewallet'], true) ? 'check_circle' : 'arrow_forward'); ?>

                            </span>
                            <span wire:loading.remove wire:target="proceedPay">
                                <?php echo e(in_array($selectedGateway, ['manual_transfer','manual_ewallet'], true) ? 'Saya Sudah Transfer' : 'Bayar Sekarang'); ?>

                            </span>

                            <div wire:loading wire:target="proceedPay" class="absolute inset-0 bg-teal-900/30 backdrop-blur-[1px] flex items-center justify-center rounded-xl">
                                <div class="flex items-center gap-2 text-white font-bold">
                                    <span class="material-symbols-outlined animate-spin">autorenew</span>
                                    Memproses...
                                </div>
                            </div>
                        </button>
                    </div>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </div>
        </div>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

        <!-- Invoice Viewer Container -->
        <div class="w-full overflow-x-auto bg-slate-100 dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 p-2 sm:p-4 print-reset-padding print-hide-bg">
            <div class="min-w-[700px] bg-white dark:bg-slate-800 rounded-xl shadow-sm p-6 sm:p-8 text-slate-800 dark:text-slate-200">
                <?php if (isset($component)) { $__componentOriginal1395fdc3b9103afc23760ed630a12cc5 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal1395fdc3b9103afc23760ed630a12cc5 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.billing.invoice-document','data' => ['invoice' => $invoice,'company' => $company]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('billing.invoice-document'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['invoice' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($invoice),'company' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($company)]); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal1395fdc3b9103afc23760ed630a12cc5)): ?>
<?php $attributes = $__attributesOriginal1395fdc3b9103afc23760ed630a12cc5; ?>
<?php unset($__attributesOriginal1395fdc3b9103afc23760ed630a12cc5); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal1395fdc3b9103afc23760ed630a12cc5)): ?>
<?php $component = $__componentOriginal1395fdc3b9103afc23760ed630a12cc5; ?>
<?php unset($__componentOriginal1395fdc3b9103afc23760ed630a12cc5); ?>
<?php endif; ?>
            </div>
        </div>
        
    </div>
</div>






<?php /**PATH D:\dsBilling\resources\views\livewire\customer-portal\billing\invoice-show.blade.php ENDPATH**/ ?>