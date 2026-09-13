<div>
    <style>
        @media print {
            .no-print {
                display: none !important;
            }
            body {
                background-color: white !important;
            }
        }
    </style>
    <div class="no-print mb-4 flex justify-between items-center px-4 md:px-8">
        <a href="<?php echo e(route('reseller-portal.billing.invoices')); ?>" class="px-4 py-2 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-700 dark:text-slate-300 rounded-lg hover:bg-slate-50 dark:bg-slate-900/50 flex items-center gap-2 transition-colors">
            <span class="material-symbols-outlined notranslate" translate="no" style="font-size:18px">arrow_back</span>
            Kembali
        </a>
        <button onclick="window.print()" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 flex items-center gap-2 transition-colors shadow-sm">
            <span class="material-symbols-outlined notranslate" translate="no" style="font-size:18px">print</span>
            Print Kertas
        </button>
    </div>
    
    <div class="bg-slate-100 dark:bg-slate-900 p-4 md:p-8 min-h-screen flex justify-center hidden:no-print">
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
</div><?php /**PATH D:\dsBilling\resources\views\livewire\reseller-portal\billing\invoice-show.blade.php ENDPATH**/ ?>