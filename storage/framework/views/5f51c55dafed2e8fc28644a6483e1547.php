

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
$userName = $user ? $user->name : 'Administrator';
$userEmail = $user ? $user->email : 'admin@example.com';
$userAvatar = $user ? $user->avatar : null;
$userRole = 'Karyawan';
if ($user) {
    if ($user->hasRole('administrator')) {
        $userRole = 'Administrator';
    } elseif ($user->hasRole('reseller')) {
        $userRole = 'Reseller';
    } elseif ($user->hasRole('customer')) {
        $userRole = 'Pelanggan';
    } elseif ($user->hasRole('manager')) {
        $userRole = $user->job_function ?: 'Manager';
    } else {
        $userRole = $user->job_function ?: 'Karyawan';
    }
}
$userRole = str_replace('_', ' ', $userRole);
$userRole = ucwords(strtolower($userRole));
?>

<div x-data="{ open: false }" class="relative">
    <button
        @click="open = !open"
        class="flex items-center gap-3 p-2 rounded-lg hover:bg-slate-100 dark:bg-slate-800 dark:hover:bg-slate-800/50 transition-colors"
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

        <svg class="hidden md:block w-4 h-4 text-slate-500 dark:text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
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
        class="absolute right-0 mt-2 w-56 bg-white dark:bg-[#111c36] rounded-xl border border-slate-200 dark:border-slate-700/50 shadow-soft-lg overflow-hidden"
        style="display: none;"
    >
        
        <div class="px-4 py-3 border-b border-slate-200 dark:border-slate-700/50">
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
                <div class="overflow-hidden">
                    <p class="font-medium text-slate-900 dark:text-slate-100 truncate"><?php echo e($userName); ?></p>
                    <p class="text-xs text-slate-500 dark:text-slate-400 truncate"><?php echo e($userEmail); ?></p>
                </div>
            </div>
        </div>

        
        <div class="py-1">
            <a href="/profile" class="flex items-center gap-3 px-4 py-2.5 text-sm text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:bg-slate-800 dark:hover:bg-slate-800/50 transition-colors">
                <span class="material-symbols-outlined notranslate" style="font-size:16px">manage_accounts</span>
                Profil Saya
            </a>

            <a href="<?php echo e(route('home')); ?>" target="_blank" class="flex items-center gap-3 px-4 py-2.5 text-sm text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:bg-slate-800 dark:hover:bg-slate-800/50 transition-colors">
                <span class="material-symbols-outlined notranslate" style="font-size:16px">home</span>
                <span class="flex-1">Landing Page</span>
                <span class="material-symbols-outlined notranslate text-slate-400" style="font-size:14px">open_in_new</span>
            </a>

        </div>

        
        <div class="py-1 border-t border-slate-200 dark:border-slate-700/50">
            <form method="POST" action="<?php echo e(route('logout')); ?>">
                <?php echo csrf_field(); ?>
                <button type="submit" class="flex items-center gap-3 w-full px-4 py-2.5 text-sm text-red-600 dark:text-red-400 hover:bg-red-50 dark:bg-red-900/30 dark:hover:bg-red-900/10 transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                    </svg>
                    Keluar (Logout)
                </button>
            </form>
        </div>
    </div>
</div>
<?php /**PATH D:\dsBilling\resources\views/components/admin/profile-menu.blade.php ENDPATH**/ ?>