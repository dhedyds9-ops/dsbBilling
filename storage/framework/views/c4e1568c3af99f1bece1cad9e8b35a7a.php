

<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames(([
    'breadcrumbs' => [],
    'user' => null,
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
    'breadcrumbs' => [],
    'user' => null,
]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<header
    class="sticky top-0 z-30 h-16 shrink-0
           bg-white/90 dark:bg-[#111c36]/90 backdrop-blur-xl supports-[backdrop-filter]:bg-white/75 dark:bg-[#111c36]/75
           border-b border-slate-200 dark:border-slate-700/50"
>
    <div class="h-full px-4 sm:px-6 flex items-center gap-3 sm:gap-4">
        
        <!-- Mobile Toggle -->
        <button
            type="button"
            @click="sidebarMobileOpen = true"
            class="lg:hidden p-2 rounded-xl
                   text-slate-500 dark:text-slate-400 hover:text-slate-900 dark:text-slate-100 dark:hover:text-slate-100
                   hover:bg-slate-100 dark:bg-slate-800 dark:hover:bg-slate-800/50 transition-colors"
            aria-label="Buka menu"
        >
            <span class="material-symbols-outlined">menu</span>
        </button>

        <!-- Desktop Toggle -->
        <button
            type="button"
            @click="sidebarCollapsed = !sidebarCollapsed"
            class="hidden lg:flex p-2 rounded-xl
                   text-slate-500 dark:text-slate-400 hover:text-slate-900 dark:text-slate-100 dark:hover:text-slate-100
                   hover:bg-slate-100 dark:bg-slate-800 dark:hover:bg-slate-800/50 transition-colors"
            aria-label="Toggle menu"
        >
            <span class="material-symbols-outlined transition-transform duration-300" :class="sidebarCollapsed ? '' : ''">menu</span>
        </button>

        
        <?php if (! empty(trim($__env->yieldContent('page_title')))): ?>
            <div class="hidden sm:flex items-center gap-2 font-bold text-slate-800 dark:text-slate-100">
                <?php echo $__env->yieldContent('page_title'); ?>
            </div>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

        
        <?php if (isset($component)) { $__componentOriginal1a2164c88256e2df02baa87be70e8a2b = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal1a2164c88256e2df02baa87be70e8a2b = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.breadcrumbs','data' => ['breadcrumbs' => $breadcrumbs]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.breadcrumbs'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['breadcrumbs' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($breadcrumbs)]); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal1a2164c88256e2df02baa87be70e8a2b)): ?>
<?php $attributes = $__attributesOriginal1a2164c88256e2df02baa87be70e8a2b; ?>
<?php unset($__attributesOriginal1a2164c88256e2df02baa87be70e8a2b); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal1a2164c88256e2df02baa87be70e8a2b)): ?>
<?php $component = $__componentOriginal1a2164c88256e2df02baa87be70e8a2b; ?>
<?php unset($__componentOriginal1a2164c88256e2df02baa87be70e8a2b); ?>
<?php endif; ?>

        
        <div class="flex-1 min-w-0"></div>

        
        <div class="flex items-center gap-1 sm:gap-2">
            
            <div class="hidden md:block">
                <?php
$__split = function ($name, $params = []) {
    return [$name, $params];
};
[$__name, $__params] = $__split('global-search');

$__keyOuter = $__key ?? null;

$__key = null;
$__componentSlots = [];

$__key ??= \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::generateKey('lw-1450145036-0', $__key);

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

            
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(class_exists(\App\Livewire\NotificationCenter::class)): ?>
            <?php
$__split = function ($name, $params = []) {
    return [$name, $params];
};
[$__name, $__params] = $__split('notification-center', []);

$__keyOuter = $__key ?? null;

$__key = null;
$__componentSlots = [];

$__key ??= \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::generateKey('lw-1450145036-1', $__key);

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
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

            
            <button
                type="button"
                @click="darkMode = !darkMode"
                class="relative p-2 rounded-xl
                       text-slate-500 dark:text-slate-400 hover:text-slate-900 dark:text-slate-100 dark:hover:text-slate-100
                       hover:bg-slate-100 dark:bg-slate-800 dark:hover:bg-slate-800/50 transition-colors"
                :title="darkMode ? 'Mode Terang' : 'Mode Gelap'"
                aria-label="Toggle theme"
            >
                <span
                    x-show="!darkMode"
                    x-transition:enter="transition ease-out duration-200"
                    x-transition:enter-start="opacity-0 -rotate-45 scale-75"
                    x-transition:enter-end="opacity-100 rotate-0 scale-100"
                    x-transition:leave="transition ease-in duration-150"
                    x-transition:leave-start="opacity-100 rotate-0 scale-100"
                    x-transition:leave-end="opacity-0 rotate-45 scale-75"
                    class="material-symbols-outlined fill ms-22"
                    style="display: none;"
                >dark_mode</span>
                <span
                    x-show="darkMode"
                    x-transition:enter="transition ease-out duration-200"
                    x-transition:enter-start="opacity-0 rotate-45 scale-75"
                    x-transition:enter-end="opacity-100 rotate-0 scale-100"
                    x-transition:leave="transition ease-in duration-150"
                    x-transition:leave-start="opacity-100 rotate-0 scale-100"
                    x-transition:leave-end="opacity-0 -rotate-45 scale-75"
                    class="material-symbols-outlined fill ms-22"
                    style="display: none;"
                >light_mode</span>
            </button>

            
            <?php if (isset($component)) { $__componentOriginal35e2812982a8fadf1893105866b0df73 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal35e2812982a8fadf1893105866b0df73 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.profile-menu','data' => ['user' => $user]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.profile-menu'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['user' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($user)]); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal35e2812982a8fadf1893105866b0df73)): ?>
<?php $attributes = $__attributesOriginal35e2812982a8fadf1893105866b0df73; ?>
<?php unset($__attributesOriginal35e2812982a8fadf1893105866b0df73); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal35e2812982a8fadf1893105866b0df73)): ?>
<?php $component = $__componentOriginal35e2812982a8fadf1893105866b0df73; ?>
<?php unset($__componentOriginal35e2812982a8fadf1893105866b0df73); ?>
<?php endif; ?>
        </div>
    </div>
</header>
<?php /**PATH D:\dsBilling\resources\views/components/admin/topbar.blade.php ENDPATH**/ ?>