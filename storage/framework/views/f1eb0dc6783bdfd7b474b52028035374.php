

<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames(([
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
    'user' => null,
]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<?php
$userName = data_get($user, 'name', 'User');
$userEmail = data_get($user, 'email', 'user@example.com');
$userAvatar = data_get($user, 'avatar');
$userRole = data_get($user, 'role', 'Admin');
?>

<div x-data="{ open: false }" class="relative">
    <button
        @click="open = !open"
        class="flex items-center gap-3 p-2 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-700 transition-colors"
    >
        <?php if (isset($component)) { $__componentOriginalf3a01b417729d1136223a876b3a78880 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalf3a01b417729d1136223a876b3a78880 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.display.avatar','data' => ['src' => $userAvatar,'name' => $userName,'size' => 'sm','status' => 'online']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('display.avatar'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['src' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($userAvatar),'name' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($userName),'size' => 'sm','status' => 'online']); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalf3a01b417729d1136223a876b3a78880)): ?>
<?php $attributes = $__attributesOriginalf3a01b417729d1136223a876b3a78880; ?>
<?php unset($__attributesOriginalf3a01b417729d1136223a876b3a78880); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalf3a01b417729d1136223a876b3a78880)): ?>
<?php $component = $__componentOriginalf3a01b417729d1136223a876b3a78880; ?>
<?php unset($__componentOriginalf3a01b417729d1136223a876b3a78880); ?>
<?php endif; ?>

        <div class="hidden md:block text-left">
            <p class="text-sm font-medium text-slate-900 dark:text-slate-100"><?php echo e($userName); ?></p>
            <p class="text-xs text-slate-500 dark:text-slate-400"><?php echo e($userRole); ?></p>
        </div>

        <svg class="hidden md:block w-4 h-4 text-slate-400 dark:text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
        </svg>
    </button>

    
    <div
        x-show="open"
        x-transition:enter="transition ease-out duration-100"
        x-transition:enter-start="opacity-0 scale-95"
        x-transition:enter-end="opacity-100 scale-100"
        x-transition:leave="transition ease-in duration-75"
        x-transition:leave-start="opacity-100 scale-100"
        x-transition:leave-end="opacity-0 scale-95"
        @click.away="open = false"
        @click.stop
        class="absolute right-0 mt-2 w-64 bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 shadow-soft-lg overflow-hidden"
        style="display: none;"
    >
        
        <div class="px-4 py-3 border-b border-slate-200 dark:border-slate-700">
            <div class="flex items-center gap-3">
                <?php if (isset($component)) { $__componentOriginalf3a01b417729d1136223a876b3a78880 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalf3a01b417729d1136223a876b3a78880 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.display.avatar','data' => ['src' => $userAvatar,'name' => $userName,'size' => 'md']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('display.avatar'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['src' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($userAvatar),'name' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($userName),'size' => 'md']); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalf3a01b417729d1136223a876b3a78880)): ?>
<?php $attributes = $__attributesOriginalf3a01b417729d1136223a876b3a78880; ?>
<?php unset($__attributesOriginalf3a01b417729d1136223a876b3a78880); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalf3a01b417729d1136223a876b3a78880)): ?>
<?php $component = $__componentOriginalf3a01b417729d1136223a876b3a78880; ?>
<?php unset($__componentOriginalf3a01b417729d1136223a876b3a78880); ?>
<?php endif; ?>
                <div>
                    <p class="font-medium text-slate-900 dark:text-slate-100"><?php echo e($userName); ?></p>
                    <p class="text-sm text-slate-500 dark:text-slate-400"><?php echo e($userEmail); ?></p>
                </div>
            </div>
        </div>

        
        <div class="py-2">
            <a href="/profile" class="flex items-center gap-3 px-4 py-2 text-sm text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-700 transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                </svg>
                Profile
            </a>

            <a href="/settings" class="flex items-center gap-3 px-4 py-2 text-sm text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-700 transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 002.572 1.065c.426 1.756 2.924 1.756 3.35 0a1.724 1.724 0 002.573-1.066c1.543.94 3.31-.826 2.37-2.37a1.724 1.724 0 002.572-1.065c.426 1.756 2.924 1.756-3.35 0a1.724 1.724 0 002.573-1.066c-1.543.94-3.31-.826 2.37-2.37a1.724 1.724 0 002.572-1.065c.426 1.756 2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                </svg>
                Settings
            </a>

            <a href="/activity-log" class="flex items-center gap-3 px-4 py-2 text-sm text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-700 transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                Activity Log
            </a>
        </div>

        
        <div class="py-2 border-t border-slate-200 dark:border-slate-700">
            <p class="px-4 py-1 text-xs font-medium text-slate-400 dark:text-slate-500 uppercase">Switch Tenant</p>
            <a href="#" class="flex items-center gap-3 px-4 py-2 text-sm text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-700 transition-colors">
                <div class="w-4 h-4 rounded border border-primary-500 bg-primary-500/20"></div>
                ISP Utama
                <span class="ml-auto text-xs text-primary-600 dark:text-primary-400">Active</span>
            </a>
            <a href="#" class="flex items-center gap-3 px-4 py-2 text-sm text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-700 transition-colors">
                <div class="w-4 h-4 rounded border border-slate-300 dark:border-slate-600"></div>
                ISP Backup
            </a>
        </div>

        
        <div class="py-2 border-t border-slate-200 dark:border-slate-700">
            <form method="POST" action="<?php echo e(route('logout')); ?>">
                <?php echo csrf_field(); ?>
                <button type="submit" class="flex items-center gap-3 w-full px-4 py-2 text-sm text-red-600 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-900/20 transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                    </svg>
                    Logout
                </button>
            </form>
        </div>
    </div>
</div>
<?php /**PATH C:\Users\Lenovo\OneDrive\dsBilling\resources\views/components/admin/profile-menu.blade.php ENDPATH**/ ?>