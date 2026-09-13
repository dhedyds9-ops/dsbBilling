<div class="space-y-6">
    <div class="sm:flex sm:items-center sm:justify-between">
        <div>
            <h1 class="text-xl font-bold text-slate-900 dark:text-slate-100">Detail IP Pool</h1>
            <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Informasi lengkap IP Pool beserta Network Profile terkait dan Router yang menggunakan pool ini.</p>
        </div>
        <div class="mt-4 sm:mt-0 flex flex-wrap gap-2">
            <button wire:click="toggleStatus" class="inline-flex items-center justify-center px-4 py-2 border border-slate-300 dark:border-slate-600 rounded-xl shadow-sm text-sm font-medium text-slate-700 dark:text-slate-200 bg-white dark:bg-slate-800 hover:bg-slate-50 dark:bg-slate-900/50 dark:hover:bg-slate-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 transition-colors">
                <svg class="-ml-1 mr-2 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                <?php echo e($pool->status === 'active' ? 'Non Aktifkan' : 'Aktifkan'); ?>

            </button>
            <a href="<?php echo e(route('isp.ip-pools.edit', $pool->id)); ?>" class="inline-flex items-center justify-center px-4 py-2 border border-transparent rounded-xl shadow-sm text-sm font-medium text-white bg-primary-600 hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 transition-colors">
                <svg class="-ml-1 mr-2 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                Edit Pool
            </a>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2 space-y-6">
            <div class="bg-white dark:bg-slate-800 shadow-sm rounded-2xl border border-slate-200 dark:border-slate-700 overflow-hidden">
                <div class="px-6 py-4 border-b border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800/50">
                    <h2 class="text-lg font-semibold text-slate-900 dark:text-slate-100">Informasi Umum</h2>
                </div>
                <div class="p-6">
                    <dl class="grid grid-cols-1 sm:grid-cols-2 gap-x-6 gap-y-4">
                        <div>
                            <dt class="text-xs font-medium text-slate-500 dark:text-slate-400 uppercase tracking-wider">Nama Pool</dt>
                            <dd class="mt-1 text-base font-semibold text-slate-900 dark:text-slate-100"><?php echo e($pool->name); ?></dd>
                        </div>
                        <div>
                            <dt class="text-xs font-medium text-slate-500 dark:text-slate-400 uppercase tracking-wider">Kode Pool</dt>
                            <dd class="mt-1 text-base font-mono text-slate-900 dark:text-slate-100"><?php echo e($pool->code); ?></dd>
                        </div>
                        <div>
                            <dt class="text-xs font-medium text-slate-500 dark:text-slate-400 uppercase tracking-wider">Status</dt>
                            <dd class="mt-1">
                                <?php if (isset($component)) { $__componentOriginalab7baa01105b3dfe1e0cf1dfc58879b4 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalab7baa01105b3dfe1e0cf1dfc58879b4 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.ui.badge','data' => ['variant' => 'success']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('ui.badge'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['variant' => 'success']); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

                                    <?php echo e(strtoupper($pool->status)); ?>

                                 <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalab7baa01105b3dfe1e0cf1dfc58879b4)): ?>
<?php $attributes = $__attributesOriginalab7baa01105b3dfe1e0cf1dfc58879b4; ?>
<?php unset($__attributesOriginalab7baa01105b3dfe1e0cf1dfc58879b4); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalab7baa01105b3dfe1e0cf1dfc58879b4)): ?>
<?php $component = $__componentOriginalab7baa01105b3dfe1e0cf1dfc58879b4; ?>
<?php unset($__componentOriginalab7baa01105b3dfe1e0cf1dfc58879b4); ?>
<?php endif; ?>
                            </dd>
                        </div>
                        <div>
                            <dt class="text-xs font-medium text-slate-500 dark:text-slate-400 uppercase tracking-wider">POP / Lokasi</dt>
                            <dd class="mt-1 text-base text-slate-900 dark:text-slate-100"><?php echo e(optional($pool->pop)->name ?? '-'); ?></dd>
                        </div>
                        <div>
                            <dt class="text-xs font-medium text-slate-500 dark:text-slate-400 uppercase tracking-wider">Network</dt>
                            <dd class="mt-1 text-base font-mono text-slate-900 dark:text-slate-100"><?php echo e($pool->network ?? '-'); ?></dd>
                        </div>
                        <div>
                            <dt class="text-xs font-medium text-slate-500 dark:text-slate-400 uppercase tracking-wider">Gateway</dt>
                            <dd class="mt-1 text-base font-mono text-slate-900 dark:text-slate-100"><?php echo e($pool->gateway ?? '-'); ?></dd>
                        </div>
                        <div>
                            <dt class="text-xs font-medium text-slate-500 dark:text-slate-400 uppercase tracking-wider">Start IP</dt>
                            <dd class="mt-1 text-base font-mono text-slate-900 dark:text-slate-100"><?php echo e($pool->start_ip ?? '-'); ?></dd>
                        </div>
                        <div>
                            <dt class="text-xs font-medium text-slate-500 dark:text-slate-400 uppercase tracking-wider">End IP</dt>
                            <dd class="mt-1 text-base font-mono text-slate-900 dark:text-slate-100"><?php echo e($pool->end_ip ?? '-'); ?></dd>
                        </div>
                        <div>
                            <dt class="text-xs font-medium text-slate-500 dark:text-slate-400 uppercase tracking-wider">DNS Servers</dt>
                            <dd class="mt-1 text-base font-mono text-slate-900 dark:text-slate-100"><?php echo e($pool->dns_servers ?? '-'); ?></dd>
                        </div>
                        <div>
                            <dt class="text-xs font-medium text-slate-500 dark:text-slate-400 uppercase tracking-wider">Netmask</dt>
                            <dd class="mt-1 text-base font-mono text-slate-900 dark:text-slate-100"><?php echo e($pool->netmask ?? '255.255.255.0'); ?></dd>
                        </div>
                    </dl>
                </div>
            </div>

            <div class="bg-white dark:bg-slate-800 shadow-sm rounded-2xl border border-slate-200 dark:border-slate-700 overflow-hidden">
                <div class="px-6 py-4 border-b border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800/50 flex items-center justify-between">
                    <h2 class="text-lg font-semibold text-slate-900 dark:text-slate-100">Network Profile (<?php echo e($networkProfiles->count()); ?>)</h2>
                    <a href="<?php echo e(route('isp.network-profiles.index')); ?>" class="text-sm text-primary-600 hover:text-primary-700 dark:text-blue-400 dark:hover:text-blue-300 font-medium">Lihat semua →</a>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm text-slate-600 dark:text-slate-300">
                        <thead class="bg-slate-50 dark:bg-slate-800/50 border-b border-slate-200 dark:border-slate-700 text-slate-500 dark:text-slate-400">
                            <tr>
                                <th class="px-6 py-3 text-left font-semibold">Nama Profile</th>
                                <th class="px-6 py-3 text-left font-semibold">Tipe</th>
                                <th class="px-6 py-3 text-left font-semibold">Router</th>
                                <th class="px-6 py-3 text-left font-semibold">VLAN</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-slate-700/60">
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $networkProfiles; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $np): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                            <tr class="hover:bg-slate-50 dark:bg-slate-900/50 dark:hover:bg-slate-700/50 transition-colors">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="font-medium text-slate-900 dark:text-slate-100"><?php echo e($np->name); ?></div>
                                    <div class="text-xs text-slate-500 dark:text-slate-400"><?php echo e($np->description ?? 'No description'); ?></div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <?php if (isset($component)) { $__componentOriginalab7baa01105b3dfe1e0cf1dfc58879b4 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalab7baa01105b3dfe1e0cf1dfc58879b4 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.ui.badge','data' => ['variant' => 'info']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('ui.badge'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['variant' => 'info']); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

                                        <?php echo e(strtoupper($np->type)); ?>

                                     <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalab7baa01105b3dfe1e0cf1dfc58879b4)): ?>
<?php $attributes = $__attributesOriginalab7baa01105b3dfe1e0cf1dfc58879b4; ?>
<?php unset($__attributesOriginalab7baa01105b3dfe1e0cf1dfc58879b4); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalab7baa01105b3dfe1e0cf1dfc58879b4)): ?>
<?php $component = $__componentOriginalab7baa01105b3dfe1e0cf1dfc58879b4; ?>
<?php unset($__componentOriginalab7baa01105b3dfe1e0cf1dfc58879b4); ?>
<?php endif; ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <?php echo e(optional($np->router)->name ?? '-'); ?>

                                </td>
                                <td class="px-6 py-4 whitespace-nowrap font-mono">
                                    <?php echo e($np->vlan_id ?? '-'); ?>

                                </td>
                            </tr>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                            <tr>
                                <td colspan="4" class="px-6 py-8 text-center text-slate-500 dark:text-slate-400">
                                    Belum ada Network Profile yang menggunakan IP Pool ini.
                                </td>
                            </tr>
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="bg-white dark:bg-slate-800 shadow-sm rounded-2xl border border-slate-200 dark:border-slate-700 overflow-hidden">
                <div class="px-6 py-4 border-b border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800/50">
                    <h2 class="text-lg font-semibold text-slate-900 dark:text-slate-100">IP Allocation Log</h2>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm text-slate-600 dark:text-slate-300">
                        <thead class="bg-slate-50 dark:bg-slate-800/50 border-b border-slate-200 dark:border-slate-700 text-slate-500 dark:text-slate-400">
                            <tr>
                                <th class="px-6 py-3 text-left font-semibold">IP Address</th>
                                <th class="px-6 py-3 text-left font-semibold">Pelanggan</th>
                                <th class="px-6 py-3 text-left font-semibold">Status</th>
                                <th class="px-6 py-3 text-left font-semibold">Tanggal</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-slate-700/60">
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $ipAllocations; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $alloc): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                            <tr class="hover:bg-slate-50 dark:bg-slate-900/50 dark:hover:bg-slate-700/50 transition-colors">
                                <td class="px-6 py-4 whitespace-nowrap font-mono text-slate-900 dark:text-slate-100">
                                    <?php echo e($alloc->ip_address ?? '-'); ?>

                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <?php echo e(optional(optional($alloc->customerService)->customer)->name ?? '-'); ?>

                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <?php if (isset($component)) { $__componentOriginalab7baa01105b3dfe1e0cf1dfc58879b4 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalab7baa01105b3dfe1e0cf1dfc58879b4 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.ui.badge','data' => ['variant' => 'neutral']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('ui.badge'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['variant' => 'neutral']); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

                                        <?php echo e($alloc->status ?? 'assigned'); ?>

                                     <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalab7baa01105b3dfe1e0cf1dfc58879b4)): ?>
<?php $attributes = $__attributesOriginalab7baa01105b3dfe1e0cf1dfc58879b4; ?>
<?php unset($__attributesOriginalab7baa01105b3dfe1e0cf1dfc58879b4); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalab7baa01105b3dfe1e0cf1dfc58879b4)): ?>
<?php $component = $__componentOriginalab7baa01105b3dfe1e0cf1dfc58879b4; ?>
<?php unset($__componentOriginalab7baa01105b3dfe1e0cf1dfc58879b4); ?>
<?php endif; ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-slate-500 dark:text-slate-400">
                                    <?php echo e($alloc->created_at?->format('d M Y H:i') ?? '-'); ?>

                                </td>
                            </tr>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                            <tr>
                                <td colspan="4" class="px-6 py-8 text-center text-slate-500 dark:text-slate-400">
                                    Belum ada alokasi IP untuk pool ini.
                                </td>
                            </tr>
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </tbody>
                    </table>
                </div>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($ipAllocations->hasPages()): ?>
                <div class="px-6 py-4 border-t border-slate-200 dark:border-slate-700">
                    <?php echo e($ipAllocations->links()); ?>

                </div>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </div>
        </div>

        <div class="space-y-6">
            <div class="bg-white dark:bg-slate-800 shadow-sm rounded-2xl border border-slate-200 dark:border-slate-700 overflow-hidden">
                <div class="px-6 py-4 border-b border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800/50">
                    <h2 class="text-lg font-semibold text-slate-900 dark:text-slate-100">IP Usage</h2>
                </div>
                <div class="p-6 space-y-4">
                    <div>
                        <div class="flex items-center justify-between text-sm">
                            <span class="text-slate-600 dark:text-slate-400">Total IP</span>
                            <span class="font-semibold text-slate-900 dark:text-slate-100"><?php echo e($pool->total_ips ?? 0); ?></span>
                        </div>
                        <div class="mt-1 h-2 bg-slate-200 dark:bg-slate-700 rounded-full overflow-hidden">
                            <div class="h-full bg-blue-500 rounded-full" style="width: 100%"></div>
                        </div>
                    </div>
                    <div>
                        <div class="flex items-center justify-between text-sm">
                            <span class="text-slate-600 dark:text-slate-400">Terpakai</span>
                            <span class="font-semibold text-amber-600 dark:text-amber-400"><?php echo e($pool->used_ips ?? 0); ?></span>
                        </div>
                        <div class="mt-1 h-2 bg-slate-200 dark:bg-slate-700 rounded-full overflow-hidden">
                            <div class="h-full bg-amber-500 rounded-full" style="width: <?php echo e($pool->total_ips ? min(100, round(($pool->used_ips / $pool->total_ips) * 100)) : 0); ?>%"></div>
                        </div>
                    </div>
                    <div>
                        <div class="flex items-center justify-between text-sm">
                            <span class="text-slate-600 dark:text-slate-400">Tersedia</span>
                            <span class="font-semibold text-emerald-600 dark:text-emerald-400"><?php echo e(max(0, ($pool->total_ips ?? 0) - ($pool->used_ips ?? 0))); ?></span>
                        </div>
                        <div class="mt-1 h-2 bg-slate-200 dark:bg-slate-700 rounded-full overflow-hidden">
                            <div class="h-full bg-emerald-500 rounded-full" style="width: <?php echo e($pool->total_ips ? max(0, 100 - round(($pool->used_ips / $pool->total_ips) * 100)) : 100); ?>%"></div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="bg-white dark:bg-slate-800 shadow-sm rounded-2xl border border-slate-200 dark:border-slate-700 overflow-hidden">
                <div class="px-6 py-4 border-b border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800/50">
                    <h2 class="text-lg font-semibold text-slate-900 dark:text-slate-100">Router Terkait (<?php echo e($routerList->count()); ?>)</h2>
                </div>
                <ul class="divide-y divide-slate-100 dark:divide-slate-700/60">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $routerList; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $router): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                    <li class="px-6 py-4">
                        <div class="flex items-center justify-between">
                            <div>
                                <div class="font-medium text-slate-900 dark:text-slate-100"><?php echo e($router->name); ?></div>
                                <div class="text-xs text-slate-500 dark:text-slate-400 font-mono"><?php echo e($router->ip_address); ?></div>
                            </div>
                            <?php if (isset($component)) { $__componentOriginalab7baa01105b3dfe1e0cf1dfc58879b4 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalab7baa01105b3dfe1e0cf1dfc58879b4 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.ui.badge','data' => ['variant' => 'success']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('ui.badge'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['variant' => 'success']); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

                                <?php echo e(optional($router)->status ? strtoupper(optional($router)->status) : 'AKTIF'); ?>

                             <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalab7baa01105b3dfe1e0cf1dfc58879b4)): ?>
<?php $attributes = $__attributesOriginalab7baa01105b3dfe1e0cf1dfc58879b4; ?>
<?php unset($__attributesOriginalab7baa01105b3dfe1e0cf1dfc58879b4); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalab7baa01105b3dfe1e0cf1dfc58879b4)): ?>
<?php $component = $__componentOriginalab7baa01105b3dfe1e0cf1dfc58879b4; ?>
<?php unset($__componentOriginalab7baa01105b3dfe1e0cf1dfc58879b4); ?>
<?php endif; ?>
                        </div>
                    </li>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                    <li class="px-6 py-8 text-center text-sm text-slate-500 dark:text-slate-400">
                        Belum ada Router yang terhubung dengan IP Pool ini.
                    </li>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </ul>
            </div>

            <div class="bg-white dark:bg-slate-800 shadow-sm rounded-2xl border border-slate-200 dark:border-slate-700 overflow-hidden">
                <div class="px-6 py-4 border-b border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800/50">
                    <h2 class="text-lg font-semibold text-slate-900 dark:text-slate-100">Sync Log</h2>
                </div>
                <ul class="divide-y divide-slate-100 dark:divide-slate-700/60">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $syncLogs; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $log): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                    <li class="px-6 py-3">
                        <div class="flex items-start justify-between">
                            <div class="text-sm text-slate-700 dark:text-slate-200"><?php echo e($log->message ?? 'Sync activity'); ?></div>
                            <div class="text-xs text-slate-500 dark:text-slate-400"><?php echo e($log->created_at?->format('d M H:i') ?? '-'); ?></div>
                        </div>
                    </li>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                    <li class="px-6 py-8 text-center text-sm text-slate-500 dark:text-slate-400">
                        Belum ada riwayat sinkronisasi untuk IP Pool ini.
                    </li>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </ul>
            </div>

            <div class="bg-white dark:bg-slate-800 shadow-sm rounded-2xl border border-slate-200 dark:border-slate-700 overflow-hidden">
                <div class="px-6 py-4 border-b border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800/50">
                    <h2 class="text-lg font-semibold text-slate-900 dark:text-slate-100">Audit</h2>
                </div>
                <div class="p-6 space-y-3 text-sm">
                    <div class="flex justify-between">
                        <span class="text-slate-500 dark:text-slate-400">Dibuat Oleh</span>
                        <span class="font-medium text-slate-900 dark:text-slate-100"><?php echo e(optional($pool->createdBy)->name ?? '-'); ?></span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-slate-500 dark:text-slate-400">Tanggal Dibuat</span>
                        <span class="font-medium text-slate-900 dark:text-slate-100"><?php echo e($pool->created_at?->format('d M Y') ?? '-'); ?></span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-slate-500 dark:text-slate-400">Terakhir Update</span>
                        <span class="font-medium text-slate-900 dark:text-slate-100"><?php echo e(optional($pool->updatedBy)->name ?? '-'); ?></span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-slate-500 dark:text-slate-400">Waktu Update</span>
                        <span class="font-medium text-slate-900 dark:text-slate-100"><?php echo e($pool->updated_at?->format('d M Y H:i') ?? '-'); ?></span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>







<?php /**PATH D:\dsBilling\resources\views\livewire\isp\ip-pool\detail.blade.php ENDPATH**/ ?>