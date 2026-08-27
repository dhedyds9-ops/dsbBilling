<?php
    $navigation = \App\Navigation\MenuRegistry::getNavigation();

    $searchIndex = [];
    $resolveUrl = function (?string $routeName): ?string {
        if (!$routeName) return null;
        try {
            return route($routeName);
        } catch (\Throwable $e) {
            return null;
        }
    };

    // Build search index (with nested groups)
    foreach ($navigation as $catIdx => $category) {
        foreach (($category['groups'] ?? []) as $group) {
            foreach (($group['items'] ?? []) as $item) {
                $url = $resolveUrl($item['route'] ?? null);
                if (!$url) continue;

                $searchIndex[] = [
                    'route' => $item['route'] ?? null,
                    'url' => $url,
                    'label' => $item['label'] ?? '',
                    'icon' => $item['icon'] ?? ($category['icon'] ?? 'home'),
                    'category' => $category['label'] ?? '',
                    'catIdx' => $catIdx,
                ];
            }
        }
        // If category has direct route (e.g., Dashboard, no submenu)
        if (!($category['groups'] ?? null) && ($category['route'] ?? null)) {
            $url = $resolveUrl($category['route']);
            if ($url) {
                $searchIndex[] = [
                    'route' => $category['route'],
                    'url' => $url,
                    'label' => $category['label'] ?? '',
                    'icon' => $category['icon'] ?? 'home',
                    'category' => $category['label'] ?? '',
                    'catIdx' => $catIdx,
                ];
            }
        }
    }

    // Determine initially active/open category based on current route
    $activeCategoryIndex = null;
    foreach ($navigation as $catIdx => $category) {
        if (!($category['groups'] ?? null)) continue;
        foreach (($category['groups'] ?? []) as $group) {
            foreach (($group['items'] ?? []) as $item) {
                if (!empty($item['active']) && request()->routeIs($item['active'])) {
                    $activeCategoryIndex = $catIdx;
                    break 3;
                }
            }
        }
    }
?>

<aside
    x-bind:class="[
        sidebarMobileOpen ? 'fixed inset-y-0 left-0 z-50' : 'hidden lg:flex lg:flex-col fixed inset-y-0 left-0 z-40',
        sidebarCollapsed ? 'w-20' : 'w-72',
        'bg-white dark:bg-slate-900 border-r border-slate-100 dark:border-slate-800 transition-all duration-300 ease-in-out'
    ]"
>
    <!-- Logo -->
    <div class="flex items-center h-16 px-4 border-b border-slate-100 dark:border-slate-800">
        <a href="<?php echo e(route('dashboard')); ?>" class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-blue-600 to-indigo-600 flex items-center justify-center shadow-lg shadow-blue-500/30">
                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                </svg>
            </div>
            <span x-show="!sidebarCollapsed" class="text-xl font-bold text-slate-900 dark:text-white">
                dsBilling
            </span>
        </a>

        <button
            @click="sidebarCollapsed = !sidebarCollapsed; if (sidebarCollapsed) openCategory = null;"
            class="ml-auto hidden lg:flex p-2 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-800"
        >
            <svg class="w-5 h-5 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 19l-7-7 7-7m8 14l-7-7 7-7"/>
            </svg>
        </button>
    </div>

    <!-- Mobile Close -->
    <button
        @click="sidebarMobileOpen = false"
        class="lg:hidden absolute top-4 right-4 p-2 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-800"
    >
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
        </svg>
    </button>

    <!-- Navigation -->
    <nav class="flex-1 overflow-y-auto py-6 px-3 space-y-2"
         x-data="{
            searchQuery: '',
            searchIndex: <?php echo \Illuminate\Support\Js::from($searchIndex)->toHtml() ?>,
            openCategory: <?php echo json_encode($activeCategoryIndex, 15, 512) ?>,
            toggleCategory(idx) {
                if (sidebarCollapsed) return;
                this.openCategory = (this.openCategory === idx) ? null : idx;
            },
            isOpen(idx) { return this.openCategory === idx; }
         }">

        <!-- Search -->
        <div x-show="!sidebarCollapsed" class="px-3 mb-4">
            <div class="relative">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <?php if (isset($component)) { $__componentOriginalce262628e3a8d44dc38fd1f3965181bc = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalce262628e3a8d44dc38fd1f3965181bc = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.icon','data' => ['name' => 'search','class' => 'w-4 h-4 text-slate-400']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'search','class' => 'w-4 h-4 text-slate-400']); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalce262628e3a8d44dc38fd1f3965181bc)): ?>
<?php $attributes = $__attributesOriginalce262628e3a8d44dc38fd1f3965181bc; ?>
<?php unset($__attributesOriginalce262628e3a8d44dc38fd1f3965181bc); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalce262628e3a8d44dc38fd1f3965181bc)): ?>
<?php $component = $__componentOriginalce262628e3a8d44dc38fd1f3965181bc; ?>
<?php unset($__componentOriginalce262628e3a8d44dc38fd1f3965181bc); ?>
<?php endif; ?>
                </div>
                <input
                    type="text"
                    x-model="searchQuery"
                    placeholder="Cari menu..."
                    class="w-full pl-10 pr-4 py-3 text-sm rounded-2xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 focus:outline-none focus:ring-2 focus:ring-blue-500"
                />
            </div>
        </div>

        <div x-show="!searchQuery.trim().length" class="space-y-2">
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $navigation; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $catIdx => $category): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                <?php
                    $catLabel = $category['label'] ?? '';
                    $catIcon  = $category['icon'] ?? 'home';
                    $hasSubmenu = !empty($category['groups']);
                    $catUrl = $hasSubmenu ? null : $resolveUrl($category['route'] ?? null);
                    $isCatActive = (!$hasSubmenu && !empty($category['active']) && request()->routeIs($category['active']));
                ?>

                <div>
                    <!-- Category Header (Accordion Trigger or Direct Link) -->
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($hasSubmenu): ?>
                        
                        <button
                            type="button"
                            @click="toggleCategory(<?php echo e($catIdx); ?>)"
                            :class="[
                                isOpen(<?php echo e($catIdx); ?>)
                                    ? 'bg-slate-50 dark:bg-slate-800/50 text-blue-600 dark:text-blue-400'
                                    : 'text-slate-600 dark:text-slate-400 hover:bg-slate-50 dark:hover:bg-slate-800/30',
                                'w-full flex items-center gap-3 px-4 py-3 text-sm rounded-2xl transition-all duration-200 group'
                            ]"
                            :title="sidebarCollapsed ? '<?php echo e($catLabel); ?>' : null"
                        >
                            <?php if (isset($component)) { $__componentOriginalce262628e3a8d44dc38fd1f3965181bc = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalce262628e3a8d44dc38fd1f3965181bc = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.icon','data' => ['name' => $catIcon,'class' => 'w-5 h-5 shrink-0']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($catIcon),'class' => 'w-5 h-5 shrink-0']); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalce262628e3a8d44dc38fd1f3965181bc)): ?>
<?php $attributes = $__attributesOriginalce262628e3a8d44dc38fd1f3965181bc; ?>
<?php unset($__attributesOriginalce262628e3a8d44dc38fd1f3965181bc); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalce262628e3a8d44dc38fd1f3965181bc)): ?>
<?php $component = $__componentOriginalce262628e3a8d44dc38fd1f3965181bc; ?>
<?php unset($__componentOriginalce262628e3a8d44dc38fd1f3965181bc); ?>
<?php endif; ?>
                            <span x-show="!sidebarCollapsed" class="font-semibold flex-1 text-left"><?php echo e($catLabel); ?></span>

                            
                            <svg x-show="!sidebarCollapsed"
                                 class="w-4 h-4 shrink-0 transition-transform duration-200"
                                 :class="isOpen(<?php echo e($catIdx); ?>) ? 'rotate-90' : ''"
                                 fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                            </svg>
                        </button>
                    <?php else: ?>
                        
                        <a href="<?php echo e($catUrl); ?>"
                           class="w-full flex items-center gap-3 px-4 py-3 text-sm rounded-2xl transition-all duration-200
                                  <?php echo e($isCatActive
                                      ? 'bg-blue-600 text-white shadow-lg shadow-blue-500/30'
                                      : 'text-slate-600 dark:text-slate-400 hover:bg-slate-50 dark:hover:bg-slate-800/30'); ?>"
                           :title="sidebarCollapsed ? '<?php echo e($catLabel); ?>' : null"
                        >
                            <?php if (isset($component)) { $__componentOriginalce262628e3a8d44dc38fd1f3965181bc = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalce262628e3a8d44dc38fd1f3965181bc = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.icon','data' => ['name' => $catIcon,'class' => 'w-5 h-5 shrink-0']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($catIcon),'class' => 'w-5 h-5 shrink-0']); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalce262628e3a8d44dc38fd1f3965181bc)): ?>
<?php $attributes = $__attributesOriginalce262628e3a8d44dc38fd1f3965181bc; ?>
<?php unset($__attributesOriginalce262628e3a8d44dc38fd1f3965181bc); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalce262628e3a8d44dc38fd1f3965181bc)): ?>
<?php $component = $__componentOriginalce262628e3a8d44dc38fd1f3965181bc; ?>
<?php unset($__componentOriginalce262628e3a8d44dc38fd1f3965181bc); ?>
<?php endif; ?>
                            <span x-show="!sidebarCollapsed" class="font-semibold flex-1 text-left"><?php echo e($catLabel); ?></span>
                        </a>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

                    <!-- Submenu (Level 2 — Accordion Content, HIDDEN DEFAULT) -->
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($hasSubmenu): ?>
                        <div
                            x-show="isOpen(<?php echo e($catIdx); ?>)"
                            x-cloak
                            x-collapse.duration.200ms
                            class="mt-1 space-y-1 overflow-hidden"
                            :class="sidebarCollapsed ? 'hidden' : ''"
                        >
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = ($category['groups'] ?? []); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $group): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                                <div class="pl-3 pr-1 space-y-1">
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = ($group['items'] ?? []); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                                        <?php
                                            $url = $resolveUrl($item['route'] ?? null);
                                            $isActive = !empty($item['active']) && request()->routeIs($item['active']);
                                            $badge = $item['badge'] ?? null;
                                            $tooltip = $item['tooltip'] ?? null;
                                        ?>

                                        <a href="<?php echo e($url); ?>"
                                           <?php if($tooltip): ?> title="<?php echo e($tooltip); ?>" <?php endif; ?>
                                           class="flex items-center gap-3 px-4 py-2.5 text-sm rounded-xl transition-all duration-150
                                                  <?php echo e($isActive
                                                      ? 'bg-blue-600/10 dark:bg-blue-500/15 text-blue-600 dark:text-blue-400 font-semibold'
                                                      : 'text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800/50'); ?>">
                                            <?php if (isset($component)) { $__componentOriginalce262628e3a8d44dc38fd1f3965181bc = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalce262628e3a8d44dc38fd1f3965181bc = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.icon','data' => ['name' => $item['icon'] ?? 'circle','class' => 'w-4 h-4 shrink-0']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($item['icon'] ?? 'circle'),'class' => 'w-4 h-4 shrink-0']); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalce262628e3a8d44dc38fd1f3965181bc)): ?>
<?php $attributes = $__attributesOriginalce262628e3a8d44dc38fd1f3965181bc; ?>
<?php unset($__attributesOriginalce262628e3a8d44dc38fd1f3965181bc); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalce262628e3a8d44dc38fd1f3965181bc)): ?>
<?php $component = $__componentOriginalce262628e3a8d44dc38fd1f3965181bc; ?>
<?php unset($__componentOriginalce262628e3a8d44dc38fd1f3965181bc); ?>
<?php endif; ?>
                                            <span class="flex-1"><?php echo e($item['label']); ?></span>

                                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($badge && $badge > 0): ?>
                                                <span class="ml-auto px-2 py-0.5 text-[10px] font-bold rounded-full min-w-[1.25rem] text-center
                                                             <?php echo e($isActive ? 'bg-blue-600 text-white' : 'bg-red-100 text-red-600 dark:bg-red-900/50 dark:text-red-300'); ?>">
                                                    <?php echo e($badge); ?>

                                                </span>
                                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                        </a>
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                                </div>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                        </div>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
        </div>

        <!-- Search Results -->
        <div x-show="searchQuery.trim().length > 0" class="px-3" x-cloak>
            <div class="text-xs uppercase tracking-widest text-slate-400 mb-3 px-1">Hasil Pencarian</div>
            
            <div class="max-h-[60vh] overflow-y-auto space-y-1">
                <template x-for="result in searchIndex.filter(item => 
                    item.label.toLowerCase().includes(searchQuery.toLowerCase()) || 
                    item.category.toLowerCase().includes(searchQuery.toLowerCase())
                )" :key="result.route">
                    <a :href="result.url" 
                       @click="openCategory = result.catIdx; searchQuery = '';"
                       class="flex items-center gap-3 px-4 py-3 text-sm rounded-2xl hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors group">
                        <!-- Fallback SVG icon check via data attr -->
                        <svg class="w-5 h-5 text-slate-400 group-hover:text-blue-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <div class="min-w-0">
                            <div x-text="result.label" class="font-medium truncate"></div>
                            <div x-text="result.category" class="text-xs text-slate-400 truncate"></div>
                        </div>
                    </a>
                </template>

                <div x-show="searchIndex.filter(item => 
                    item.label.toLowerCase().includes(searchQuery.toLowerCase()) || 
                    item.category.toLowerCase().includes(searchQuery.toLowerCase())
                ).length === 0" class="px-4 py-8 text-center text-slate-400">
                    Tidak ada hasil ditemukan
                </div>
            </div>
        </div>
    </nav>
</aside>
<?php /**PATH C:\Users\Lenovo\OneDrive\dsBilling\resources\views\components\admin\sidebar.blade.php ENDPATH**/ ?>