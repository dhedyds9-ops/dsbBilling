<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames(([
    'paginator' => null,
    'currentPage' => null,
    'totalPages' => null,
    'totalItems' => null,
    'perPage' => 15,
    'from' => null,
    'to' => null,
    'showInfo' => true,
    'simple' => false,
    'livewireTarget' => null,
]));

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

foreach (array_filter(([
    'paginator' => null,
    'currentPage' => null,
    'totalPages' => null,
    'totalItems' => null,
    'perPage' => 15,
    'from' => null,
    'to' => null,
    'showInfo' => true,
    'simple' => false,
    'livewireTarget' => null,
]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<?php
if ($paginator && $paginator instanceof \Illuminate\Contracts\Pagination\LengthAwarePaginator) {
    $currentPage = $currentPage ?? $paginator->currentPage();
    $totalPages = $totalPages ?? $paginator->lastPage();
    $totalItems = $totalItems ?? $paginator->total();
    $perPage = $perPage ?? $paginator->perPage();
    $from = $from ?? $paginator->firstItem();
    $to = $to ?? $paginator->lastItem();
}

$currentPage = $currentPage ?? 1;
$totalPages = $totalPages ?? 1;
$totalItems = $totalItems ?? 0;
$from = $from ?? (($totalItems > 0) ? (($currentPage - 1) * $perPage + 1) : 0);
$to = $to ?? min($currentPage * $perPage, $totalItems);

$hasPages = $totalPages > 1;
$showEllipsisStart = $currentPage > 4;
$showEllipsisEnd = $currentPage < $totalPages - 3;

$wireClick = function (string $method, int $page = 1) use ($livewireTarget): string {
    if ($livewireTarget) {
        return "wire:click=\"\$set('{$livewireTarget}', {$page})\"";
    }
    if ($paginator && method_exists($paginator, 'hasMorePages')) {
        return "wire:click=\"{$method}({$page})\"";
    }
    return "wire:click=\"{$method}({$page})\"";
};
?>

<?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($hasPages || $showInfo): ?>
    <nav class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 w-full">
        
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($showInfo): ?>
            <div class="text-sm text-[var(--ds-on-surface-variant)] whitespace-nowrap">
                Menampilkan
                <span class="font-semibold text-[var(--ds-on-surface)]"><?php echo e($from ?: 0); ?></span>
                <span>—</span>
                <span class="font-semibold text-[var(--ds-on-surface)]"><?php echo e($to ?: 0); ?></span>
                <span>dari</span>
                <span class="font-semibold text-[var(--ds-on-surface)]"><?php echo e($totalItems); ?></span>
                <span>entri</span>
            </div>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

        
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($hasPages): ?>
            <div class="flex items-center gap-1">
                
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(!$simple && $currentPage > 2): ?>
                    <button
                        type="button"
                        <?php echo $wireClick('gotoPage', 1); ?>

                        class="h-9 w-9 inline-flex items-center justify-center rounded-xl text-[var(--ds-on-surface-variant)] hover:text-[var(--ds-on-surface)] hover:bg-[var(--ds-surface-container-high)] transition-colors"
                        aria-label="Halaman pertama"
                    >
                        <span class="material-symbols-outlined ms-20">first_page</span>
                    </button>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

                
                <button
                    type="button"
                    <?php if($currentPage > 1): ?> <?php echo $wireClick('gotoPage', $currentPage - 1); ?> <?php endif; ?>
                    <?php if($currentPage === 1): ?> disabled <?php endif; ?>
                    class="h-9 w-9 inline-flex items-center justify-center rounded-xl text-[var(--ds-on-surface-variant)] hover:text-[var(--ds-on-surface)] hover:bg-[var(--ds-surface-container-high)] disabled:opacity-40 disabled:cursor-not-allowed disabled:hover:bg-transparent transition-colors"
                    aria-label="Halaman sebelumnya"
                >
                    <span class="material-symbols-outlined ms-20">chevron_left</span>
                </button>

                
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(!$simple): ?>
                    <?php
                        $start = max(1, $currentPage - 2);
                        $end = min($totalPages, $currentPage + 2);
                        if ($showEllipsisStart) $start = max(2, $currentPage - 1);
                        if ($showEllipsisEnd) $end = min($totalPages - 1, $currentPage + 1);
                    ?>

                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($showEllipsisStart): ?>
                        <button type="button" <?php echo $wireClick('gotoPage', 1); ?> class="h-9 min-w-[2.25rem] px-2.5 inline-flex items-center justify-center rounded-xl text-sm font-medium text-[var(--ds-on-surface-variant)] hover:bg-[var(--ds-surface-container-high)] transition-colors">
                            1
                        </button>
                        <span class="px-1 text-[var(--ds-outline)] text-sm select-none">…</span>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php for($i = $start; $i <= $end; $i++): ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                        <?php
                            $isActive = $currentPage === $i;
                        ?>
                        <button
                            type="button"
                            <?php echo $wireClick('gotoPage', $i); ?>

                            class="<?php echo \Illuminate\Support\Arr::toCssClasses([
                                'h-9 min-w-[2.25rem] px-2.5 inline-flex items-center justify-center rounded-xl text-sm font-medium transition-all',
                                'bg-[var(--ds-primary)] text-[var(--ds-on-primary)] shadow-soft-md' => $isActive,
                                'text-[var(--ds-on-surface-variant)] hover:text-[var(--ds-on-surface)] hover:bg-[var(--ds-surface-container-high)]' => !$isActive,
                            ]); ?>"
                            <?php if($isActive): ?> aria-current="page" <?php endif; ?>
                        >
                            <?php echo e($i); ?>

                        </button>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endfor; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>

                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($showEllipsisEnd): ?>
                        <span class="px-1 text-[var(--ds-outline)] text-sm select-none">…</span>
                        <button type="button" <?php echo $wireClick('gotoPage', $totalPages); ?> class="h-9 min-w-[2.25rem] px-2.5 inline-flex items-center justify-center rounded-xl text-sm font-medium text-[var(--ds-on-surface-variant)] hover:bg-[var(--ds-surface-container-high)] transition-colors">
                            <?php echo e($totalPages); ?>

                        </button>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                <?php else: ?>
                    <span class="px-3 text-sm font-medium text-[var(--ds-on-surface)]">
                        Hal. <?php echo e($currentPage); ?> / <?php echo e($totalPages); ?>

                    </span>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

                
                <button
                    type="button"
                    <?php if($currentPage < $totalPages): ?> <?php echo $wireClick('gotoPage', $currentPage + 1); ?> <?php endif; ?>
                    <?php if($currentPage === $totalPages): ?> disabled <?php endif; ?>
                    class="h-9 w-9 inline-flex items-center justify-center rounded-xl text-[var(--ds-on-surface-variant)] hover:text-[var(--ds-on-surface)] hover:bg-[var(--ds-surface-container-high)] disabled:opacity-40 disabled:cursor-not-allowed disabled:hover:bg-transparent transition-colors"
                    aria-label="Halaman berikutnya"
                >
                    <span class="material-symbols-outlined ms-20">chevron_right</span>
                </button>

                
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(!$simple && $currentPage < $totalPages - 1): ?>
                    <button
                        type="button"
                        <?php echo $wireClick('gotoPage', $totalPages); ?>

                        class="h-9 w-9 inline-flex items-center justify-center rounded-xl text-[var(--ds-on-surface-variant)] hover:text-[var(--ds-on-surface)] hover:bg-[var(--ds-surface-container-high)] transition-colors"
                        aria-label="Halaman terakhir"
                    >
                        <span class="material-symbols-outlined ms-20">last_page</span>
                    </button>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </div>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
    </nav>
<?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>






<?php /**PATH D:\dsBilling\resources\views\components\ui\pagination.blade.php ENDPATH**/ ?>