<?php
    $__companyName = \App\Models\Setting::getValue('company.name', config('app.name', 'dsBilling'));
    $__companyLogo = \App\Models\Setting::getValue('company.logo_url', null);
    $__isDashboard = request()->routeIs('customer-portal.dashboard');
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
            @media print {
                /* Force exact A4 size at 100% scale without headers/footers */
                @page { margin: 10mm; size: A4 portrait; }
                html, body {
                    background: #ffffff !important;
                    background-color: #ffffff !important;
                    margin: 0 !important;
                    padding: 0 !important;
                    width: 100% !important;
                    height: auto !important;
                    color: #111827 !important;
                }
                /* Hide all UI chrome */
                .print-hide, nav, header, aside, footer { display: none !important; }
                .print-reset-height { min-height: 0 !important; height: auto !important; }
                .print-reset-overflow { overflow: visible !important; }
                .print-reset-padding { padding: 0 !important; margin: 0 !important; }
                .print-reset-layout { 
                    max-width: none !important; 
                    width: 100% !important;
                    box-shadow: none !important; 
                    border: none !important; 
                    display: block !important; 
                    position: static !important;
                    background: #ffffff !important;
                    background-color: #ffffff !important;
                }
                /* Force ALL elements to transparent bg so dark mode doesnt bleed through */
                *, *::before, *::after {
                    background-color: transparent !important;
                    background-image: none !important;
                    box-shadow: none !important;
                    text-shadow: none !important;
                }
                /* Restore white for the invoice doc wrapper itself */
                .invoice-document-wrapper,
                .invoice-document {
                    background: #ffffff !important;
                    background-color: #ffffff !important;
                    color: #111827 !important;
                }
                /* Remove wrappers around invoice doc */
                .print-hide-bg {
                    background: transparent !important;
                    border: none !important;
                    padding: 0 !important;
                    margin: 0 !important;
                    border-radius: 0 !important;
                }
                /* Make overflow containers visible for print */
                .overflow-x-auto, .overflow-y-auto {
                    overflow: visible !important;
                }
                /* Force inner wrapper (min-w-[700px]) to 100% width */
                .min-w-\[700px\] {
                    min-width: 0 !important;
                    width: 100% !important;
                    border-radius: 0 !important;
                    padding: 0 !important;
                }
            }
        </style>
     <?php $__env->endSlot(); ?>

     <?php $__env->slot('title', null, []); ?> <?php echo $__env->yieldContent('title', $title ?? (config('app.name', 'BillingHub'))); ?> <?php $__env->endSlot(); ?>

    <!-- App Shell (Max width constraint to look like app on desktop) -->
    <div class="max-w-md mx-auto min-h-screen relative bg-slate-50 dark:bg-slate-900 shadow-[0_0_40px_rgba(0,0,0,0.05)] sm:border-x border-slate-200 dark:border-slate-800 flex flex-col print-reset-layout print-reset-height">
        
        <!-- App Header (Sticky) -->
        <header class="sticky top-0 z-40 bg-white/80 dark:bg-slate-900/80 backdrop-blur-md border-b border-slate-100 dark:border-slate-800 px-4 h-14 flex items-center justify-between print-hide">
            <div class="flex items-center gap-3 min-w-0 flex-1">
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(!$__isDashboard): ?>
                    <button onclick="history.back()" class="p-1.5 -ml-1.5 shrink-0 text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:bg-slate-800 dark:hover:bg-slate-800 rounded-full transition-colors">
                        <span class="material-symbols-outlined text-[24px]">arrow_back</span>
                    </button>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

                <?php
                    $__headerTitle = trim($__env->yieldContent('header_title'));
                    $__hasCustomTitle = !empty($__headerTitle) || !empty($title ?? null);
                ?>

                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($__hasCustomTitle): ?>
                    <h1 class="text-lg font-bold text-teal-800 dark:text-teal-400 truncate">
                        <?php echo e($__headerTitle ?: ($title ?? '')); ?>

                    </h1>
                <?php else: ?>
                    <a href="<?php echo e(route('customer-portal.dashboard')); ?>" class="flex items-center gap-3 min-w-0">
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(!empty($__companyLogo)): ?>
                            <img src="<?php echo e($__companyLogo); ?>" alt="<?php echo e($__companyName); ?>"
                                 class="h-11 w-auto shrink-0 object-contain max-w-[120px]"
                                 style="display: block;"
                                 onerror="this.style.display='none';var fb=this.nextElementSibling;if(fb){fb.style.display='inline-flex';fb.style.alignItems='center';fb.style.justifyContent='center';fb.style.textAlign='center';}">
                            <span class="w-11 h-11 shrink-0 bg-gradient-to-br from-teal-500 to-indigo-600 text-white font-black hidden rounded-xl"
                                  style="font-size: 16px; letter-spacing: -0.5px;">
                                <?php echo e(strtoupper(mb_substr($__companyName, 0, 2))); ?>

                            </span>
                        <?php else: ?>
                            <span class="w-11 h-11 shrink-0 bg-gradient-to-br from-teal-500 to-indigo-600 text-white inline-flex items-center justify-center font-black rounded-xl"
                                  style="font-size: 16px; letter-spacing: -0.5px;">
                                <?php echo e(strtoupper(mb_substr($__companyName, 0, 2))); ?>

                            </span>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        <div class="flex flex-col leading-tight min-w-0">
                            <span class="text-lg font-extrabold text-teal-800 dark:text-teal-400 truncate">
                                <?php echo e($__companyName); ?>

                            </span>
                            <span class="text-[11px] font-medium text-slate-400 dark:text-slate-500 dark:text-slate-400 truncate">
                                Customer Portal
                            </span>
                        </div>
                    </a>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </div>
            
            <div class="flex items-center gap-1 shrink-0 -mr-1">
                <button @click="darkMode = !darkMode; localStorage.setItem('dsb_dark_mode', darkMode ? '1' : '0')" class="p-1.5 text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:bg-slate-800 dark:hover:bg-slate-800 rounded-full transition-colors">
                    <span class="material-symbols-outlined text-[22px]" x-text="darkMode ? 'light_mode' : 'dark_mode'"></span>
                </button>
                <?php
$__split = function ($name, $params = []) {
    return [$name, $params];
};
[$__name, $__params] = $__split('notification-center', []);

$__keyOuter = $__key ?? null;

$__key = null;
$__componentSlots = [];

$__key ??= \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::generateKey('lw-3101908457-0', $__key);

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
            </div>
        </header>

        <!-- Main Content (Scrollable) -->
        <main class="flex-1 overflow-y-auto pb-20 print-reset-height print-reset-overflow print-reset-padding">
            
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(auth()->guard()->check()): ?>
                <?php
                    $authUser = auth()->user();
                    $needsPasswordChange = $authUser && !$authUser->password_changed_at;
                ?>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($needsPasswordChange ?? false): ?>
                <div x-data="{ show: true }" x-show="show" x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100 translate-y-0" x-transition:leave-end="opacity-0 -translate-y-2"
                    class="mx-4 mt-3 p-3.5 rounded-2xl bg-amber-50 dark:bg-amber-900/30 border border-amber-200 dark:border-amber-700 flex items-start gap-3 shadow-sm print-hide">
                    <span class="material-symbols-outlined text-amber-600 dark:text-amber-400 shrink-0 mt-0.5" style="font-size:20px">warning</span>
                    <div class="flex-1 min-w-0">
                        <p class="text-xs font-bold text-amber-800 dark:text-amber-300">Ganti Password Default Anda</p>
                        <p class="text-[11px] text-amber-700 dark:text-amber-400 mt-0.5 leading-relaxed">Demi keamanan, segera ganti password portal Anda dari password default yang diberikan admin.</p>
                        <a href="<?php echo e(route('customer-portal.profile')); ?>" class="inline-flex items-center gap-1 mt-2 text-[11px] font-bold text-amber-800 dark:text-amber-300 underline">
                            <span class="material-symbols-outlined" style="font-size:14px">lock_reset</span>
                            Ganti Sekarang
                        </a>
                    </div>
                    <button @click="show = false" class="text-amber-500 hover:text-amber-700 dark:hover:text-amber-300 shrink-0">
                        <span class="material-symbols-outlined" style="font-size:18px">close</span>
                    </button>
                </div>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

            <?php echo e($slot ?? ''); ?>

            <?php echo $__env->yieldContent('content'); ?>
        </main>

        <!-- Bottom Navigation Bar (Fixed) -->
        <nav class="fixed bottom-0 left-1/2 -translate-x-1/2 w-full max-w-md bg-white dark:bg-slate-900 border-t border-slate-100 dark:border-slate-800 px-2 py-1.5 flex items-center justify-around z-40 shadow-[0_-4px_24px_rgba(0,0,0,0.04)] print-hide">
            <a href="<?php echo e(route('customer-portal.dashboard')); ?>" class="flex flex-col items-center gap-0.5 px-3 py-1.5 rounded-xl transition-colors <?php echo e(request()->routeIs('customer-portal.dashboard') ? 'text-teal-600 dark:text-teal-400 bg-teal-50 dark:bg-teal-900/30' : 'text-slate-400 dark:text-slate-500 dark:text-slate-400'); ?>">
                <span class="material-symbols-outlined <?php echo e(request()->routeIs('customer-portal.dashboard') ? 'fill' : ''); ?>" style="font-size:24px">home</span>
                <span class="text-[10px] font-semibold">Home</span>
            </a>
            <a href="<?php echo e(route('customer-portal.info')); ?>" class="flex flex-col items-center gap-0.5 px-2 py-1.5 rounded-xl transition-colors <?php echo e(request()->routeIs('customer-portal.info') ? 'text-teal-600 dark:text-teal-400 bg-teal-50 dark:bg-teal-900/30' : 'text-slate-400 dark:text-slate-500 dark:text-slate-400'); ?>">
                <span class="material-symbols-outlined <?php echo e(request()->routeIs('customer-portal.info') ? 'fill' : ''); ?>" style="font-size:24px">info</span>
                <span class="text-[10px] font-semibold">Info</span>
            </a>
            <a href="<?php echo e(route('customer-portal.support.ticket-list')); ?>" class="flex flex-col items-center gap-0.5 px-2 py-1.5 rounded-xl transition-colors <?php echo e(request()->routeIs('customer-portal.support.*') ? 'text-teal-600 dark:text-teal-400 bg-teal-50 dark:bg-teal-900/30' : 'text-slate-400 dark:text-slate-500 dark:text-slate-400'); ?>">
                <span class="material-symbols-outlined <?php echo e(request()->routeIs('customer-portal.support.*') ? 'fill' : ''); ?>" style="font-size:24px">support_agent</span>
                <span class="text-[10px] font-semibold">Tiket</span>
            </a>
            <a href="<?php echo e(route('customer-portal.profile')); ?>" class="flex flex-col items-center gap-0.5 px-2 py-1.5 rounded-xl transition-colors <?php echo e(request()->routeIs('customer-portal.profile') ? 'text-teal-600 dark:text-teal-400 bg-teal-50 dark:bg-teal-900/30' : 'text-slate-400 dark:text-slate-500 dark:text-slate-400'); ?>">
                <span class="material-symbols-outlined <?php echo e(request()->routeIs('customer-portal.profile') ? 'fill' : ''); ?>" style="font-size:24px">person</span>
                <span class="text-[10px] font-semibold">Profile</span>
            </a>
            <form method="POST" action="<?php echo e(route('logout')); ?>" class="m-0">
                <?php echo csrf_field(); ?>
                <button type="submit" class="flex flex-col items-center gap-0.5 px-3 py-1.5 rounded-xl text-slate-400 dark:text-slate-500 dark:text-slate-400 hover:text-red-500 transition-colors">
                    <span class="material-symbols-outlined" style="font-size:24px">logout</span>
                    <span class="text-[10px] font-semibold">Logout</span>
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







<?php /**PATH D:\dsBilling\resources\views\layouts\customer-app.blade.php ENDPATH**/ ?>