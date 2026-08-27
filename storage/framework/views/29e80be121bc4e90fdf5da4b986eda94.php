

<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames(([
    'data' => [],
    'color' => 'primary',
    'height' => 40,
    'width' => 100,
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
    'data' => [],
    'color' => 'primary',
    'height' => 40,
    'width' => 100,
]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<?php
$colorMap = [
    'primary' => '#0ea5e9',
    'success' => '#22c55e',
    'warning' => '#f59e0b',
    'danger' => '#ef4444',
    'info' => '#3b82f6',
];

$strokeColor = $colorMap[$color] ?? $colorMap['primary'];
$fillColor = $strokeColor . '20';
?>

<div class="inline-block" style="height: <?php echo e($height); ?>px; width: <?php echo e($width); ?>px;" <?php echo e($attributes); ?>>
    <svg viewBox="0 0 <?php echo e($width); ?> <?php echo e($height); ?>" class="w-full h-full" preserveAspectRatio="none">
        <defs>
            <linearGradient id="sparkline-gradient-<?php echo e($color); ?>" x1="0" y1="0" x2="0" y2="1">
                <stop offset="0%" stop-color="<?php echo e($strokeColor); ?>" stop-opacity="0.3"/>
                <stop offset="100%" stop-color="<?php echo e($strokeColor); ?>" stop-opacity="0"/>
            </linearGradient>
        </defs>

        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(count($data) > 1): ?>
            <?php
                $max = max($data);
                $min = min($data);
                $range = $max - $range > 0 ? $max - $min : 1;
                $points = collect($data)->map(fn($v, $i) => [
                    'x' => ($i / (count($data) - 1)) * $width,
                    'y' => $height - (($v - $min) / $range) * $height * 0.9 - $height * 0.05
                ]);
                $linePath = $points->map(fn($p, $i) => ($i === 0 ? 'M' : 'L') . $p['x'] . ',' . $p['y'])->implode(' ');
                $areaPath = $linePath . ' L' . $width . ',' . $height . ' L0,' . $height . ' Z';
            ?>

            <path d="<?php echo e($areaPath); ?>" fill="url(#sparkline-gradient-<?php echo e($color); ?>)" />
            <path d="<?php echo e($linePath); ?>" fill="none" stroke="<?php echo e($strokeColor); ?>" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
    </svg>
</div>
<?php /**PATH C:\Users\Lenovo\OneDrive\dsBilling\resources\views\components\charts\sparkline.blade.php ENDPATH**/ ?>