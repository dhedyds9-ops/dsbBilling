<?php
    $__isDashboard = request()->routeIs('technician.dashboard');
?>

<?php if (isset($component)) { $__componentOriginald4c772c02301431d3253f64117700596 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginald4c772c02301431d3253f64117700596 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.layouts.base','data' => ['htmlClass' => 'h-full','bodyClass' => 'antialiased text-slate-800 dark:text-slate-200 bg-slate-50 dark:bg-slate-900 min-h-screen']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('layouts.base'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['html-class' => 'h-full','body-class' => 'antialiased text-slate-800 dark:text-slate-200 bg-slate-50 dark:bg-slate-900 min-h-screen']); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>


     <?php $__env->slot('bodyAttributes', null, []); ?> 
        x-data="{ darkMode: localStorage.getItem('dsb_dark_mode') === '1' }" :class="darkMode ? 'dark' : ''"
     <?php $__env->endSlot(); ?>

     <?php $__env->slot('head', null, []); ?> 
        <meta name="theme-color" content="#ffffff">
        <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Figtree:wght@300;400;500;600;700;800;900&family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200&display=swap" />
        <style>
            body { overscroll-behavior: none; }
            .material-symbols-outlined { font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24; }
            .material-symbols-outlined.fill { font-variation-settings: 'FILL' 1, 'wght' 400, 'GRAD' 0, 'opsz' 24; }
            @media print { .print-hide { display: none !important; } }
        </style>
     <?php $__env->endSlot(); ?>

     <?php $__env->slot('title', null, []); ?> <?php echo $__env->yieldContent('title', $title ?? 'Technician Portal'); ?> <?php $__env->endSlot(); ?>

    <!-- App Shell (max-w-md like Customer Portal) -->
    <div class="max-w-md print:max-w-none mx-auto min-h-screen relative bg-slate-50 dark:bg-slate-900 print:bg-white shadow-[0_0_40px_rgba(0,0,0,0.05)] sm:border-x border-slate-200 dark:border-slate-800 print:border-none print:shadow-none flex flex-col">

        <!-- Sticky Top Header -->
        <header class="sticky top-0 z-40 bg-white/80 dark:bg-slate-900/80 backdrop-blur-md border-b border-slate-100 dark:border-slate-800 px-4 h-14 flex items-center justify-between print-hide">
            <div class="flex items-center gap-3 min-w-0 flex-1">
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(!$__isDashboard): ?>
                    <button onclick="history.back()" class="p-1.5 -ml-1.5 shrink-0 text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:bg-slate-800 dark:hover:bg-slate-800 rounded-full transition-colors">
                        <span class="material-symbols-outlined" style="font-size:24px">arrow_back</span>
                    </button>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

                <?php $__headerTitle = trim($__env->yieldContent('header_title')); ?>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(!empty($__headerTitle)): ?>
                    <h1 class="text-lg font-bold text-indigo-800 dark:text-indigo-400 truncate"><?php echo e($__headerTitle); ?></h1>
                <?php else: ?>
                    <div class="flex items-center gap-2.5 min-w-0">
                        <div class="w-9 h-9 shrink-0 bg-gradient-to-br from-indigo-500 to-blue-600 text-white inline-flex items-center justify-center rounded-xl shadow-sm">
                            <span class="material-symbols-outlined" style="font-size:20px">engineering</span>
                        </div>
                        <div class="flex flex-col leading-tight min-w-0">
                            <span class="text-base font-extrabold text-indigo-800 dark:text-indigo-400 truncate">Technician</span>
                            <span class="text-[10px] font-medium text-slate-400 dark:text-slate-500 dark:text-slate-400 truncate">Portal Teknisi</span>
                        </div>
                    </div>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </div>

            <div class="flex items-center gap-1 shrink-0 -mr-1">
                <?php
$__split = function ($name, $params = []) {
    return [$name, $params];
};
[$__name, $__params] = $__split('notification-center', []);

$__keyOuter = $__key ?? null;

$__key = null;
$__componentSlots = [];

$__key ??= \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::generateKey('lw-2812278666-0', $__key);

$__html = app('livewire')->mount($__name, $__params, $__key, $__componentSlots);

echo $__html;

unset($__html);
unset($__key);
$__key = $__keyOuter;
unset($__keyOuter);
unset($__name);
unset($__params);
unset($__componentSlots);
unset($__split);
?>
                <button @click="darkMode = !darkMode; localStorage.setItem('dsb_dark_mode', darkMode ? '1' : '0')" class="p-1.5 text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:bg-slate-800 dark:hover:bg-slate-800 rounded-full transition-colors">
                    <span class="material-symbols-outlined" style="font-size:22px" x-text="darkMode ? 'light_mode' : 'dark_mode'"></span>
                </button>
                <div class="w-8 h-8 bg-indigo-100 dark:bg-indigo-900 text-indigo-700 dark:text-indigo-300 rounded-full flex items-center justify-center font-bold text-sm">
                    <?php echo e(strtoupper(mb_substr(auth()->user()->name ?? 'T', 0, 1))); ?>

                </div>
            </div>
        </header>

        <!-- Main Content -->
        <main class="flex-1 overflow-y-auto pb-20">
            <?php echo e($slot ?? ''); ?>

            <?php echo $__env->yieldContent('content'); ?>
        </main>

        <!-- Bottom Navigation Bar -->
        <nav class="fixed bottom-0 left-1/2 -translate-x-1/2 w-full max-w-md bg-white dark:bg-slate-900 border-t border-slate-100 dark:border-slate-800 px-2 py-1.5 flex items-center justify-around z-40 shadow-[0_-4px_24px_rgba(0,0,0,0.04)] print-hide">
            <a href="<?php echo e(route('technician.dashboard')); ?>" class="flex flex-col items-center gap-0.5 px-3 py-1.5 rounded-xl transition-colors <?php echo e(request()->routeIs('technician.dashboard') ? 'text-indigo-600 dark:text-indigo-400 bg-indigo-50 dark:bg-indigo-900/30' : 'text-slate-400 dark:text-slate-500 dark:text-slate-400'); ?>">
                <span class="material-symbols-outlined <?php echo e(request()->routeIs('technician.dashboard') ? 'fill' : ''); ?>" style="font-size:24px">home</span>
                <span class="text-[10px] font-semibold">Home</span>
            </a>
            <a href="<?php echo e(route('technician.my-jobs.index')); ?>" class="flex flex-col items-center gap-0.5 px-3 py-1.5 rounded-xl transition-colors <?php echo e(request()->routeIs('technician.my-jobs.index') ? 'text-indigo-600 dark:text-indigo-400 bg-indigo-50 dark:bg-indigo-900/30' : 'text-slate-400 dark:text-slate-500 dark:text-slate-400'); ?>">
                <span class="material-symbols-outlined <?php echo e(request()->routeIs('technician.my-jobs.index') ? 'fill' : ''); ?>" style="font-size:24px">assignment</span>
                <span class="text-[10px] font-semibold">PSB</span>
            </a>
            <a href="<?php echo e(route('technician.my-jobs.troubleshooting')); ?>" class="flex flex-col items-center gap-0.5 px-3 py-1.5 rounded-xl transition-colors <?php echo e(request()->routeIs('technician.my-jobs.troubleshooting') ? 'text-indigo-600 dark:text-indigo-400 bg-indigo-50 dark:bg-indigo-900/30' : 'text-slate-400 dark:text-slate-500 dark:text-slate-400'); ?>">
                <span class="material-symbols-outlined <?php echo e(request()->routeIs('technician.my-jobs.troubleshooting') ? 'fill' : ''); ?>" style="font-size:24px">build</span>
                <span class="text-[10px] font-semibold">Gangguan</span>
            </a>
            <a href="<?php echo e(route('technician.installation.wizard')); ?>" class="flex flex-col items-center gap-0.5 px-3 py-1.5 rounded-xl transition-colors <?php echo e(request()->routeIs('technician.installation.*') ? 'text-indigo-600 dark:text-indigo-400 bg-indigo-50 dark:bg-indigo-900/30' : 'text-slate-400 dark:text-slate-500 dark:text-slate-400'); ?>">
                <span class="material-symbols-outlined <?php echo e(request()->routeIs('technician.installation.*') ? 'fill' : ''); ?>" style="font-size:24px">build_circle</span>
                <span class="text-[10px] font-semibold">Instalasi</span>
            </a>
            <a href="<?php echo e(route('technician.attendance')); ?>" class="flex flex-col items-center gap-0.5 px-3 py-1.5 rounded-xl transition-colors <?php echo e(request()->routeIs('technician.attendance') ? 'text-indigo-600 dark:text-indigo-400 bg-indigo-50 dark:bg-indigo-900/30' : 'text-slate-400 dark:text-slate-500 dark:text-slate-400'); ?>">
                <span class="material-symbols-outlined <?php echo e(request()->routeIs('technician.attendance') ? 'fill' : ''); ?>" style="font-size:24px">fingerprint</span>
                <span class="text-[10px] font-semibold">Absensi</span>
            </a>
            <form method="POST" action="<?php echo e(route('logout')); ?>" class="m-0">
                <?php echo csrf_field(); ?>
                <button type="submit" class="flex flex-col items-center gap-0.5 px-3 py-1.5 rounded-xl text-slate-400 dark:text-slate-500 dark:text-slate-400 hover:text-red-500 transition-colors">
                    <span class="material-symbols-outlined" style="font-size:24px">logout</span>
                    <span class="text-[10px] font-semibold">Keluar</span>
                </button>
            </form>
        </nav>
    </div>

    <?php if (isset($component)) { $__componentOriginal4840fa01a8d366d0491fbf5f590c70be = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal4840fa01a8d366d0491fbf5f590c70be = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.ui.toast-manager','data' => []] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('ui.toast-manager'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal4840fa01a8d366d0491fbf5f590c70be)): ?>
<?php $attributes = $__attributesOriginal4840fa01a8d366d0491fbf5f590c70be; ?>
<?php unset($__attributesOriginal4840fa01a8d366d0491fbf5f590c70be); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal4840fa01a8d366d0491fbf5f590c70be)): ?>
<?php $component = $__componentOriginal4840fa01a8d366d0491fbf5f590c70be; ?>
<?php unset($__componentOriginal4840fa01a8d366d0491fbf5f590c70be); ?>
<?php endif; ?>

    

 <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginald4c772c02301431d3253f64117700596)): ?>
<?php $attributes = $__attributesOriginald4c772c02301431d3253f64117700596; ?>
<?php unset($__attributesOriginald4c772c02301431d3253f64117700596); ?>
<?php endif; ?>
<?php if (isset($__componentOriginald4c772c02301431d3253f64117700596)): ?>
<?php $component = $__componentOriginald4c772c02301431d3253f64117700596; ?>
<?php unset($__componentOriginald4c772c02301431d3253f64117700596); ?>
<?php endif; ?>

<?php /**PATH D:\dsBilling\resources\views\layouts\technician-app.blade.php ENDPATH**/ ?>