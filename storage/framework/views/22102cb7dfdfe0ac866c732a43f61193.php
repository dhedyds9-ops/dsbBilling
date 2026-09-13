
<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames((['filters' => []]));

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

foreach (array_filter((['filters' => []]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>
<?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(count($filters) > 0): ?>
<div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-2 p-2 lg:p-3 bg-white dark:bg-slate-800 border-b border-slate-200 dark:border-slate-700">
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $filters; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $f): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
        <div class="min-w-0">
            <label class="block text-[11px] font-medium text-slate-600 dark:text-slate-300 mb-1"><?php echo e($f['label']); ?></label>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(($f['type'] ?? 'select') === 'select'): ?>
                <select wire:model.live="filters.<?php echo e($f['key']); ?>" class="w-full text-sm rounded-md border border-slate-200 focus:ring-1 focus:ring-blue-500 bg-white dark:bg-slate-900 dark:bg-slate-700 dark:border-slate-600 dark:text-slate-100 py-1.5 px-2 dark:bg-slate-900 dark:text-slate-100">
                    <option value="">Semua</option>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $f['options'] ?? []; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $v => $l): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                        <option value="<?php echo e($v); ?>"><?php echo e($l); ?></option>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                </select>
            <?php elseif(($f['type'] ?? '') === 'date'): ?>
                <input wire:model.live="filters.<?php echo e($f['key']); ?>" type="date" class="w-full text-sm rounded-md border border-slate-200 focus:ring-1 focus:ring-blue-500 bg-white dark:bg-slate-900 dark:bg-slate-700 dark:border-slate-600 dark:text-slate-100 py-1.5 px-2 dark:bg-slate-900 dark:text-slate-100">
            <?php elseif(($f['type'] ?? '') === 'number'): ?>
                <input wire:model.live="filters.<?php echo e($f['key']); ?>" type="number" class="w-full text-sm rounded-md border border-slate-200 focus:ring-1 focus:ring-blue-500 bg-white dark:bg-slate-900 dark:bg-slate-700 dark:border-slate-600 dark:text-slate-100 py-1.5 px-2 dark:bg-slate-900 dark:text-slate-100">
            <?php elseif(($f['type'] ?? '') === 'text'): ?>
                <input wire:model.live="filters.<?php echo e($f['key']); ?>" type="text" class="w-full text-sm rounded-md border border-slate-200 focus:ring-1 focus:ring-blue-500 bg-white dark:bg-slate-900 dark:bg-slate-700 dark:border-slate-600 dark:text-slate-100 py-1.5 px-2 dark:bg-slate-900 dark:text-slate-100">
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </div>
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
    <div class="flex items-end gap-2">
        <button wire:click="resetFilters" class="px-3 py-1.5 text-sm rounded-md border border-slate-200 hover:bg-slate-50 dark:bg-slate-900/50 dark:border-slate-700 dark:hover:bg-slate-700 dark:text-slate-200 text-slate-700 dark:text-slate-300">Reset</button>
    </div>
</div>
<?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
<?php /**PATH D:\dsBilling\resources\views\partials\enterprise\filters.blade.php ENDPATH**/ ?>