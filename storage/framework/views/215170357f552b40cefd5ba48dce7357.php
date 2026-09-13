<!DOCTYPE html>
<html lang="<?php echo e(str_replace('_', '-', app()->getLocale())); ?>" class="bg-slate-50 dark:bg-slate-900" x-data="{ darkMode: localStorage.getItem('darkMode') === 'true' }" x-init="$watch('darkMode', val => localStorage.setItem('darkMode', val))" :class="{ 'dark': darkMode }">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="<?php echo e(csrf_token()); ?>">

    <title><?php echo $__env->yieldContent('title', $title ?? config('app.name', 'dsBilling Admin')); ?></title>

    <!-- Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@24,400,0,0" rel="stylesheet" />
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700&display=swap" rel="stylesheet" />

    <!-- Tailwind CDN Config for Light Theme -->
    <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
    <script>
        tailwind.config = { darkMode: 'class',
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Figtree', 'sans-serif'],
                    },
                    colors: {
                        primary: {
                            50: '#eff6ff',
                            100: '#dbeafe',
                            500: '#3b82f6',
                            600: '#2563eb',
                            700: '#1d4ed8',
                        }
                    }
                }
            }
        }
    </script>
    <style>
        [x-cloak] { display: none !important; }
        .apexcharts-canvas, .apexcharts-svg { background: transparent !important; }
        .apexcharts-tooltip { background: transparent !important; }

        /* Fix Material Symbols icons always rendering as icons (not text) */
        .material-symbols-outlined {
            font-family: 'Material Symbols Outlined' !important;
            font-weight: normal;
            font-style: normal;
            font-size: 24px;
            line-height: 1;
            letter-spacing: normal;
            text-transform: none !important;
            display: inline-block;
            white-space: nowrap;
            word-wrap: normal;
            direction: ltr !important;
            -webkit-font-feature-settings: 'liga';
            font-feature-settings: 'liga';
            -webkit-font-smoothing: antialiased;
            text-rendering: optimizeLegibility;
        }
    </style>

    <!-- Styles -->
    <?php echo \Livewire\Mechanisms\FrontendAssets\FrontendAssets::styles(); ?>

</head>

<body
    x-data="{
        sidebarCollapsed: false,
        sidebarMobileOpen: false
    }"
    class="min-h-screen font-sans text-slate-900 dark:text-slate-100 antialiased bg-slate-50 dark:bg-slate-900 print:bg-white print:text-black"
>
    <!-- Mobile Sidebar Overlay -->
    <div
        x-show="sidebarMobileOpen"
        @click="sidebarMobileOpen = false"
        x-transition:enter="transition-opacity ease-linear duration-300"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition-opacity ease-linear duration-300"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        class="fixed inset-0 z-40 bg-slate-900/80 backdrop-blur-sm lg:hidden"
        style="display: none;"
    ></div>

    <!-- Sidebar -->
    <div class="print:hidden">
        <?php if (isset($component)) { $__componentOriginalbebe114f3ccde4b38d7462a3136be045 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalbebe114f3ccde4b38d7462a3136be045 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.sidebar','data' => []] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.sidebar'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalbebe114f3ccde4b38d7462a3136be045)): ?>
<?php $attributes = $__attributesOriginalbebe114f3ccde4b38d7462a3136be045; ?>
<?php unset($__attributesOriginalbebe114f3ccde4b38d7462a3136be045); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalbebe114f3ccde4b38d7462a3136be045)): ?>
<?php $component = $__componentOriginalbebe114f3ccde4b38d7462a3136be045; ?>
<?php unset($__componentOriginalbebe114f3ccde4b38d7462a3136be045); ?>
<?php endif; ?>
    </div>

    <!-- Main Content Area -->
    <div
        class="lg:pl-64 print:pl-0 transition-all duration-300 relative flex flex-col min-h-screen"
        :class="sidebarCollapsed ? 'lg:pl-20 print:pl-0' : 'lg:pl-64 print:pl-0'"
    >
        <!-- Topbar -->
        <div class="print:hidden">
            <?php if (isset($component)) { $__componentOriginal232f012cda936a4eb249de3234ccfddd = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal232f012cda936a4eb249de3234ccfddd = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.topbar','data' => ['breadcrumbs' => $breadcrumbs ?? [],'user' => auth()->user()]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.topbar'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['breadcrumbs' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($breadcrumbs ?? []),'user' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(auth()->user())]); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal232f012cda936a4eb249de3234ccfddd)): ?>
<?php $attributes = $__attributesOriginal232f012cda936a4eb249de3234ccfddd; ?>
<?php unset($__attributesOriginal232f012cda936a4eb249de3234ccfddd); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal232f012cda936a4eb249de3234ccfddd)): ?>
<?php $component = $__componentOriginal232f012cda936a4eb249de3234ccfddd; ?>
<?php unset($__componentOriginal232f012cda936a4eb249de3234ccfddd); ?>
<?php endif; ?>
        </div>

        <!-- Page Content -->
        <main class="flex-1 p-6 print:p-0 print:m-0">
            <?php echo e($slot ?? ''); ?>

            <?php echo $__env->yieldContent('content'); ?>
        </main>
    </div>

    <!-- Scripts -->
    <?php echo \Livewire\Mechanisms\FrontendAssets\FrontendAssets::scripts(); ?>

    <?php echo $__env->yieldPushContent('scripts'); ?>

    <!-- Protect Material Symbols from Google Translate -->
    <script>
        function protectIcons() {
            document.querySelectorAll('.material-symbols-outlined').forEach(function(el) {
                el.setAttribute('translate', 'no');
                el.classList.add('notranslate');
            });
        }
        // Run on DOM ready
        document.addEventListener('DOMContentLoaded', protectIcons);
        // Run after Livewire updates
        document.addEventListener('livewire:update', protectIcons);
        // Watch for dynamic DOM changes (Google Translate itself)
        new MutationObserver(protectIcons).observe(document.body, { childList: true, subtree: true });
    </script>
<?php echo $__env->make('components.global-sweetalert', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
</body>
</html><?php /**PATH D:\dsBilling\resources\views/layouts/enterprise.blade.php ENDPATH**/ ?>