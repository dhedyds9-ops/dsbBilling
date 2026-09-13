<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames(([
    'title' => null,
    'htmlClass' => '',
    'htmlAttributes' => '',
    'bodyClass' => '',
    'bodyAttributes' => '',
    'head' => null,
    'scripts' => null,
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
    'title' => null,
    'htmlClass' => '',
    'htmlAttributes' => '',
    'bodyClass' => '',
    'bodyAttributes' => '',
    'head' => null,
    'scripts' => null,
]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>
<!DOCTYPE html>
<html lang="<?php echo e(str_replace('_', '-', app()->getLocale())); ?>" class="<?php echo e($htmlClass); ?>" <?php echo $htmlAttributes; ?>>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover, maximum-scale=1, user-scalable=0">
    <meta name="csrf-token" content="<?php echo e(csrf_token()); ?>">

    <title><?php echo $__env->yieldContent('title', $title ?? config('app.name', 'dsBilling')); ?></title>

    
    <link rel="icon" href="/favicon.png?v=1" type="image/png">

    
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="preconnect" href="https://fonts.bunny.net">

    
    <?php echo e($head ?? ''); ?>

    <?php echo $__env->yieldPushContent('head'); ?>

    
    <?php echo \Livewire\Mechanisms\FrontendAssets\FrontendAssets::styles(); ?>

    <?php echo app('Illuminate\Foundation\Vite')(['resources/css/app.css', 'resources/js/app.js']); ?>
    
    <?php echo $__env->yieldPushContent('styles'); ?>
</head>
<body class="<?php echo e($bodyClass); ?>" <?php echo $bodyAttributes; ?>>
    
    <?php echo e($slot); ?>


    
    <?php echo \Livewire\Mechanisms\FrontendAssets\FrontendAssets::scripts(); ?>

    
    
    <?php echo e($scripts ?? ''); ?>

    
    <?php echo $__env->yieldPushContent('scripts'); ?>
</body>
</html>






<?php /**PATH D:\dsBilling\resources\views\components\layouts\base.blade.php ENDPATH**/ ?>