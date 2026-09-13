<?php
    $navigation = \App\Navigation\MenuRegistry::getNavigation();
    $companyName = \App\Models\Setting::getValue('company.name', config('app.name', 'dsBilling'));
    $companyLogo = \App\Models\Setting::getValue('company.logo_url', null);

    $searchIndex = [];
    $initialOpenGroupId = null;

    $resolveUrl = function (?string $routeName): ?string {
        if (! $routeName) return null;
        try { return route($routeName); } catch (\Throwable $e) { return null; }
    };

    foreach ($navigation as $category) {
        foreach (($category['groups'] ?? []) as $group) {
            foreach (($group['items'] ?? []) as $item) {
                $url = $resolveUrl($item['route'] ?? null);
                if (! $url) continue;
                $searchIndex[] = [
                    'route' => $item['route'] ?? null,
                    'url' => $url,
                    'label' => $item['label'] ?? '',
                    'icon' => $item['icon'] ?? ($category['icon'] ?? 'home'),
                    'category' => $category['label'] ?? '',
                    'group' => $group['label'] ?? '',
                ];
            }
        }
    }

    foreach ($navigation as $category) {
        foreach (($category['groups'] ?? []) as $group) {
            $groupHasActive = false;
            foreach (($group['items'] ?? []) as $item) {
                $pattern = $item['active'] ?? ($item['route'] ?? null);
                if ($pattern && request()->routeIs($pattern)) {
                    $groupHasActive = true;
                    break;
                }
            }
            if ($groupHasActive) {
                $initialOpenGroupId = substr(md5(($category['label'] ?? '') . '|' . ($group['label'] ?? '')), 0, 12);
                break 2;
            }
        }
    }
?>

<aside
    x-bind:class="[
        sidebarMobileOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0',
        sidebarCollapsed ? 'w-20' : 'w-64',
        'fixed inset-y-0 left-0 z-50 flex flex-col bg-white dark:bg-[#111c36] border-r border-slate-200 dark:border-slate-700/50 transition-all duration-300 shadow-sm'
    ]"
>
    <!-- Logo & Brand -->
    <div class="flex items-center h-16 px-4 border-b border-slate-200 dark:border-slate-700/50 shrink-0">
        <a href="<?php echo e(route('dashboard')); ?>" class="flex items-center justify-center w-full">
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($companyLogo): ?>
                <img src="<?php echo e(asset($companyLogo)); ?>" alt="Logo" class="h-8 max-w-[180px] object-contain">
            <?php else: ?>
                <div class="w-8 h-8 rounded-lg bg-blue-600 flex items-center justify-center text-white shrink-0">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                    </svg>
                </div>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </a>
    </div>

    <!-- Navigation -->
    <nav
        class="flex-1 overflow-y-auto py-4 px-3 space-y-6"
        x-data="{
            openGroup: <?php echo \Illuminate\Support\Js::from($initialOpenGroupId)->toHtml() ?>,
            toggleGroup(id) {
                this.openGroup = this.openGroup === id ? null : id;
            },
            isOpen(id) {
                return this.openGroup === id;
            }
        }"
    >
        <div class="space-y-6">
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $navigation; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $category): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                <?php
                    $categoryLabel = $category['label'] ?? '';
                    $categoryIcon = $category['icon'] ?? 'home';
                ?>

                <div>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($categoryLabel !== 'Dashboard' && $categoryLabel !== 'Dashboard Reseller'): ?>
                    <div class="px-3 mb-2 flex items-center gap-2">
                        <p x-show="!sidebarCollapsed" class="text-[11px] font-medium text-blue-500/80 dark:text-blue-400 uppercase tracking-wider">
                            <?php echo e($categoryLabel); ?>

                        </p>
                    </div>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

                    <div class="space-y-1">
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = ($category['groups'] ?? []); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $group): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                            <?php
                                $groupLabel = $group['label'] ?? '';
                                $groupId = substr(md5($categoryLabel . '|' . $groupLabel), 0, 12);
                                $groupIcon = $group['icon'] ?? (($group['items'][0]['icon'] ?? null) ?: $categoryIcon);

                                $groupHasActive = false;
                                foreach (($group['items'] ?? []) as $item) {
                                    $pattern = $item['active'] ?? ($item['route'] ?? null);
                                    if ($pattern && request()->routeIs($pattern)) {
                                        $groupHasActive = true;
                                        break;
                                    }
                                }
                                
                                // Jika tidak ada group label (seperti dashboard), langsung tampilkan itemnya
                                $isSingleItem = empty($groupLabel);
                            ?>

                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($isSingleItem): ?>
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = ($group['items'] ?? []); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                                    <?php
                                        $itemLabel = $item['label'] ?? '';
                                        $itemIcon = $item['icon'] ?? $groupIcon;
                                        $itemRoute = $item['route'] ?? null;
                                        $itemActivePattern = $item['active'] ?? $itemRoute;
                                        $itemIsActive = $itemActivePattern ? request()->routeIs($itemActivePattern) : false;
                                        $itemUrl = $resolveUrl($itemRoute) ?? '#';
                                    ?>
                                    <a
                                        href="<?php echo e($itemUrl); ?>"
                                        class="flex items-center gap-3 px-3 py-2.5 text-sm rounded-xl transition-colors <?php echo e($itemIsActive ? 'bg-blue-50/80 dark:bg-blue-900/30 text-blue-600 dark:text-blue-400 font-semibold' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-50 dark:bg-slate-900/50 dark:hover:bg-slate-800/50 hover:text-slate-900 dark:text-slate-100 dark:hover:text-slate-100'); ?>"
                                    >
                                        <span class="material-symbols-outlined w-5 h-5 flex items-center justify-center <?php echo e($itemIsActive ? 'text-blue-600 dark:text-blue-400' : 'text-slate-500 dark:text-slate-400'); ?>"><?php echo e($itemIcon); ?></span>
                                        <span x-show="!sidebarCollapsed" class="flex-1"><?php echo e($itemLabel); ?></span>
                                    </a>
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                            <?php else: ?>
                                <div>
                                    <button
                                        type="button"
                                        @click="toggleGroup('<?php echo e($groupId); ?>')"
                                        class="w-full flex items-center gap-3 px-3 py-2.5 text-sm rounded-xl transition-colors <?php echo e($groupHasActive ? 'bg-blue-50/80 dark:bg-blue-900/30 text-blue-600 dark:text-blue-400 font-semibold' : 'text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:bg-slate-900/50 dark:hover:bg-slate-800/50 hover:text-slate-900 dark:text-slate-100 dark:hover:text-slate-100'); ?>"
                                    >
                                        <span class="material-symbols-outlined w-5 h-5 flex items-center justify-center <?php echo e($groupHasActive ? 'text-blue-600 dark:text-blue-400' : ($category['icon_color'] ?? 'text-slate-700 dark:text-slate-300')); ?>"><?php echo e($groupIcon); ?></span>
                                        <span x-show="!sidebarCollapsed" class="flex-1 text-left"><?php echo e($groupLabel); ?></span>
                                        <svg
                                            x-show="!sidebarCollapsed"
                                            x-bind:class="isOpen('<?php echo e($groupId); ?>') ? 'rotate-180' : ''"
                                            class="w-4 h-4 text-slate-400 transition-transform"
                                            fill="none"
                                            stroke="currentColor"
                                            viewBox="0 0 24 24"
                                        >
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                        </svg>
                                    </button>

                                    <!-- Submenu Tree -->
                                    <div x-show="isOpen('<?php echo e($groupId); ?>')" x-collapse class="mt-1 relative">
                                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = ($group['items'] ?? []); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                                            <?php
                                                $itemLabel = $item['label'] ?? '';
                                                $itemRoute = $item['route'] ?? null;
                                                $itemActivePattern = $item['active'] ?? $itemRoute;
                                                $itemIsActive = $itemActivePattern ? request()->routeIs($itemActivePattern) : false;
                                                $itemUrl = $resolveUrl($itemRoute) ?? '#';
                                                $isLast = $loop->last;
                                            ?>

                                            <div class="relative pl-11 pr-3 py-1">
                                                <!-- Vertical Line -->
                                                <div class="absolute left-[21px] top-0 <?php echo e($isLast ? 'h-1/2' : 'h-full'); ?> w-px bg-slate-200 dark:bg-slate-700"></div>
                                                
                                                <!-- Horizontal Line -->
                                                <div class="absolute left-[21px] top-1/2 -translate-y-1/2 w-3 h-px bg-slate-200 dark:bg-slate-700"></div>

                                                <!-- Active Blue Dot -->
                                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($itemIsActive): ?>
                                                    <div class="absolute left-[19px] top-1/2 -translate-y-1/2 w-[5px] h-[5px] rounded-full bg-blue-600 ring-4 ring-white dark:ring-[#111c36] z-10"></div>
                                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

                                                <a
                                                    href="<?php echo e($itemUrl); ?>"
                                                    class="block px-3 py-2 text-sm rounded-lg transition-colors <?php echo e($itemIsActive ? 'bg-blue-50/50 dark:bg-blue-900/20 text-blue-600 dark:text-blue-400 font-medium' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:text-slate-100 dark:hover:text-slate-100 hover:bg-slate-50 dark:bg-slate-900/50 dark:hover:bg-slate-800/50'); ?>"
                                                >
                                                    <?php echo e($itemLabel); ?>

                                                </a>
                                            </div>
                                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                                    </div>
                                </div>
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                    </div>
                </div>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
        </div>
    </nav>
</aside>
<?php /**PATH D:\dsBilling\resources\views/components/admin/sidebar.blade.php ENDPATH**/ ?>