<div class="max-w-7xl mx-auto p-3">
    <?php if (isset($component)) { $__componentOriginal1a2164c88256e2df02baa87be70e8a2b = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal1a2164c88256e2df02baa87be70e8a2b = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.breadcrumbs','data' => []] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.breadcrumbs'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
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

    <div class="mb-3">
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(session()->has('success')): ?>
            <?php if (isset($component)) { $__componentOriginal49d2c764cd006c1bf28fb7e122731888 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal49d2c764cd006c1bf28fb7e122731888 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.feedback.alert','data' => ['variant' => 'success']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('feedback.alert'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['variant' => 'success']); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>
<?php echo e(session('success')); ?> <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal49d2c764cd006c1bf28fb7e122731888)): ?>
<?php $attributes = $__attributesOriginal49d2c764cd006c1bf28fb7e122731888; ?>
<?php unset($__attributesOriginal49d2c764cd006c1bf28fb7e122731888); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal49d2c764cd006c1bf28fb7e122731888)): ?>
<?php $component = $__componentOriginal49d2c764cd006c1bf28fb7e122731888; ?>
<?php unset($__componentOriginal49d2c764cd006c1bf28fb7e122731888); ?>
<?php endif; ?>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(session()->has('error')): ?>
            <?php if (isset($component)) { $__componentOriginal49d2c764cd006c1bf28fb7e122731888 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal49d2c764cd006c1bf28fb7e122731888 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.feedback.alert','data' => ['variant' => 'danger']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('feedback.alert'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['variant' => 'danger']); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>
<?php echo e(session('error')); ?> <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal49d2c764cd006c1bf28fb7e122731888)): ?>
<?php $attributes = $__attributesOriginal49d2c764cd006c1bf28fb7e122731888; ?>
<?php unset($__attributesOriginal49d2c764cd006c1bf28fb7e122731888); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal49d2c764cd006c1bf28fb7e122731888)): ?>
<?php $component = $__componentOriginal49d2c764cd006c1bf28fb7e122731888; ?>
<?php unset($__componentOriginal49d2c764cd006c1bf28fb7e122731888); ?>
<?php endif; ?>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(session()->has('warning')): ?>
            <?php if (isset($component)) { $__componentOriginal49d2c764cd006c1bf28fb7e122731888 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal49d2c764cd006c1bf28fb7e122731888 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.feedback.alert','data' => ['variant' => 'warning']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('feedback.alert'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['variant' => 'warning']); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>
<?php echo e(session('warning')); ?> <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal49d2c764cd006c1bf28fb7e122731888)): ?>
<?php $attributes = $__attributesOriginal49d2c764cd006c1bf28fb7e122731888; ?>
<?php unset($__attributesOriginal49d2c764cd006c1bf28fb7e122731888); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal49d2c764cd006c1bf28fb7e122731888)): ?>
<?php $component = $__componentOriginal49d2c764cd006c1bf28fb7e122731888; ?>
<?php unset($__componentOriginal49d2c764cd006c1bf28fb7e122731888); ?>
<?php endif; ?>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(session()->has('info')): ?>
            <?php if (isset($component)) { $__componentOriginal49d2c764cd006c1bf28fb7e122731888 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal49d2c764cd006c1bf28fb7e122731888 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.feedback.alert','data' => ['variant' => 'info']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('feedback.alert'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['variant' => 'info']); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>
<?php echo e(session('info')); ?> <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal49d2c764cd006c1bf28fb7e122731888)): ?>
<?php $attributes = $__attributesOriginal49d2c764cd006c1bf28fb7e122731888; ?>
<?php unset($__attributesOriginal49d2c764cd006c1bf28fb7e122731888); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal49d2c764cd006c1bf28fb7e122731888)): ?>
<?php $component = $__componentOriginal49d2c764cd006c1bf28fb7e122731888; ?>
<?php unset($__componentOriginal49d2c764cd006c1bf28fb7e122731888); ?>
<?php endif; ?>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
    </div>

    <!-- HEADER (Pola PPPoE: Judul Kiri + Dropdown Manajemen Kanan) -->
    <div class="flex justify-between items-center mb-3 gap-3 flex-wrap">
        <div x-data="{ open: false }" class="relative inline-block text-left">
            <div>
                <button @click="open = !open" type="button" class="inline-flex items-center gap-2 px-5 py-2.5 bg-primary-600 text-white rounded-lg hover:bg-primary-700 transition-colors font-medium shadow-sm shadow-primary-500/20">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"></path>
                    </svg>
                    Manajemen Bandwidth
                    <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                    </svg>
                </button>
            </div>
            <div x-show="open" @click.away="open = false" x-transition class="origin-top-right absolute right-0 mt-2 w-64 rounded-lg shadow-lg bg-white dark:bg-slate-800 ring-1 ring-black ring-opacity-5 dark:ring-white/10 divide-y divide-gray-100 dark:divide-slate-700 z-50">
                <div class="py-1">
                    <button type="button" wire:click="openCreateModal" @click="open = false" class="flex items-center gap-2 px-4 py-2 text-sm w-full text-left text-gray-700 dark:text-slate-200 hover:bg-gray-50 dark:hover:bg-slate-700">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                        Tambah Bandwidth
                    </button>
                </div>
                <div class="py-1">
                    <button type="button" onclick="window.print()" @click="open = false" class="flex items-center gap-2 px-4 py-2 text-sm w-full text-left text-gray-700 dark:text-slate-200 hover:bg-gray-50 dark:hover:bg-slate-700">
                        <svg class="w-4 h-4 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                        Export CSV / Print
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- TOOLBAR (Pola PPPoE: x-base.card) -->
    <?php if (isset($component)) { $__componentOriginalf0ba6ef14ffa9e2e0936b821e3847e33 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalf0ba6ef14ffa9e2e0936b821e3847e33 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.base.card','data' => ['class' => 'mb-3']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('base.card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['class' => 'mb-3']); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

        <div class="flex flex-col lg:flex-row gap-4 items-start lg:items-center justify-between">
            <div class="w-full lg:w-1/3">
                <label for="searchBw" class="sr-only">Search</label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                    </div>
                    <input id="searchBw" wire:model.live.debounce.300ms="search" type="text" class="block w-full pl-10 pr-3 py-2.5 border border-gray-300 dark:border-slate-700 dark:bg-slate-800 dark:text-white rounded-lg leading-5 bg-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-primary-500 transition-all" placeholder="Cari nama bandwidth...">
                </div>
            </div>
        </div>
     <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalf0ba6ef14ffa9e2e0936b821e3847e33)): ?>
<?php $attributes = $__attributesOriginalf0ba6ef14ffa9e2e0936b821e3847e33; ?>
<?php unset($__attributesOriginalf0ba6ef14ffa9e2e0936b821e3847e33); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalf0ba6ef14ffa9e2e0936b821e3847e33)): ?>
<?php $component = $__componentOriginalf0ba6ef14ffa9e2e0936b821e3847e33; ?>
<?php unset($__componentOriginalf0ba6ef14ffa9e2e0936b821e3847e33); ?>
<?php endif; ?>

    <!-- TABLE (Pola Router Index: x-base.card overflow-hidden) -->
    <?php if (isset($component)) { $__componentOriginalf0ba6ef14ffa9e2e0936b821e3847e33 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalf0ba6ef14ffa9e2e0936b821e3847e33 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.base.card','data' => ['class' => 'overflow-hidden']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('base.card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['class' => 'overflow-hidden']); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-slate-700">
                <thead class="bg-gray-50 dark:bg-slate-800/50">
                    <tr>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-gray-500 dark:text-slate-400 uppercase tracking-wider">Nama Bandwidth</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-gray-500 dark:text-slate-400 uppercase tracking-wider">Max Speed</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-gray-500 dark:text-slate-400 uppercase tracking-wider">Burst Limit (Auto)</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-gray-500 dark:text-slate-400 uppercase tracking-wider">Burst Threshold</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-gray-500 dark:text-slate-400 uppercase tracking-wider">Burst Time</th>
                        <th scope="col" class="px-6 py-3 text-right text-xs font-semibold text-gray-500 dark:text-slate-400 uppercase tracking-wider">Aksi</th>
                    </tr>
                </thead>
                <tbody class="bg-white dark:bg-slate-900 divide-y divide-gray-200 dark:divide-slate-700">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $bandwidths; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $bw): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                        <?php
                            preg_match('/([0-9.]+)\s*([a-zA-Z]+)/i', $bw->download_speed ?? '0 Mbps', $dl);
                            preg_match('/([0-9.]+)\s*([a-zA-Z]+)/i', $bw->upload_speed ?? '0 Mbps', $ul);
                            $unit = $dl[2] ?? 'Mbps';
                            $dlVal = (float)($dl[1] ?? 0);
                            $ulVal = (float)($ul[1] ?? 0);
                            $dlBurst = $bw->burst_limit_download ?? round($dlVal * 1.5, 2);
                            $ulBurst = $bw->burst_limit_upload ?? round($ulVal * 1.5, 2);
                            $dlThr = $bw->burst_threshold_download ?? round($dlVal * 0.7, 2);
                            $ulThr = $bw->burst_threshold_upload ?? round($ulVal * 0.7, 2);
                            $bt = $bw->burst_time_download ?? 8;
                        ?>
                        <tr <?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::$currentLoop['key'] = ''.e($bw->id).''; ?>wire:key="<?php echo e($bw->id); ?>" class="hover:bg-gray-50 dark:hover:bg-slate-800/30 transition-colors">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-emerald-500 to-cyan-500 text-white flex items-center justify-center font-bold shadow">
                                        <?php echo e($bw->name ? strtoupper(substr($bw->name, 0, 1)) : 'B'); ?>

                                    </div>
                                    <div>
                                        <div class="text-sm font-semibold text-gray-900 dark:text-white"><?php echo e($bw->name); ?></div>
                                        <div class="text-xs text-gray-400 dark:text-slate-500 font-mono"><?php echo e($bw->code ?? '-'); ?></div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center gap-4">
                                    <div>
                                        <span class="text-[10px] font-semibold uppercase text-primary-600">DL</span>
                                        <div class="text-sm font-bold text-gray-900 dark:text-white"><?php echo e($dlVal); ?> <?php echo e($unit); ?></div>
                                    </div>
                                    <div class="w-px h-8 bg-gray-200 dark:bg-slate-700"></div>
                                    <div>
                                        <span class="text-[10px] font-semibold uppercase text-success-600">UL</span>
                                        <div class="text-sm font-bold text-gray-900 dark:text-white"><?php echo e($ulVal); ?> <?php echo e($unit); ?></div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center gap-4">
                                    <div>
                                        <span class="text-[10px] font-semibold uppercase text-purple-600">DL Burst</span>
                                        <div class="text-sm font-bold text-purple-700 dark:text-purple-300"><?php echo e($dlBurst); ?> <?php echo e($unit); ?></div>
                                    </div>
                                    <div class="w-px h-8 bg-purple-200 dark:bg-purple-900/30"></div>
                                    <div>
                                        <span class="text-[10px] font-semibold uppercase text-purple-600">UL Burst</span>
                                        <div class="text-sm font-bold text-purple-700 dark:text-purple-300"><?php echo e($ulBurst); ?> <?php echo e($unit); ?></div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center gap-4">
                                    <div>
                                        <span class="text-[10px] font-semibold uppercase text-orange-600">DL Thr</span>
                                        <div class="text-sm font-semibold text-gray-700 dark:text-slate-300"><?php echo e($dlThr); ?> <?php echo e($unit); ?></div>
                                    </div>
                                    <div class="w-px h-8 bg-gray-200 dark:bg-slate-700"></div>
                                    <div>
                                        <span class="text-[10px] font-semibold uppercase text-orange-600">UL Thr</span>
                                        <div class="text-sm font-semibold text-gray-700 dark:text-slate-300"><?php echo e($ulThr); ?> <?php echo e($unit); ?></div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-2.5 py-1 bg-primary-100 dark:bg-primary-900/30 text-primary-700 dark:text-primary-300 text-xs font-bold rounded-full">
                                    <?php echo e($bt); ?>s
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                <div class="inline-flex items-center gap-1">
                                    <button wire:click="openEditModal(<?php echo e($bw->id); ?>)" title="Edit" type="button" class="p-2 rounded-lg text-primary-600 hover:bg-primary-50 dark:hover:bg-primary-900/20">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                    </button>
                                    <button wire:click="delete(<?php echo e($bw->id); ?>)" wire:confirm="Yakin hapus bandwidth <?php echo e($bw->name); ?>?" title="Hapus" type="button" class="p-2 rounded-lg text-red-600 hover:bg-red-50 dark:hover:bg-red-900/20">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                        <tr>
                            <td colspan="6" class="px-6 py-12 text-center text-gray-400 dark:text-slate-500">
                                <svg class="w-10 h-10 mx-auto mb-3 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                                Belum ada bandwidth profile.
                                <div class="mt-2 text-sm text-gray-500 dark:text-slate-400">Gunakan dropdown <b>Manajemen Bandwidth → Tambah Bandwidth</b>.</div>
                            </td>
                        </tr>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </tbody>
            </table>
        </div>
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(method_exists($bandwidths, 'hasPages') && $bandwidths->hasPages()): ?>
            <div class="p-4 border-t border-gray-200 dark:border-slate-700"><?php echo e($bandwidths->links()); ?></div>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
     <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalf0ba6ef14ffa9e2e0936b821e3847e33)): ?>
<?php $attributes = $__attributesOriginalf0ba6ef14ffa9e2e0936b821e3847e33; ?>
<?php unset($__attributesOriginalf0ba6ef14ffa9e2e0936b821e3847e33); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalf0ba6ef14ffa9e2e0936b821e3847e33)): ?>
<?php $component = $__componentOriginalf0ba6ef14ffa9e2e0936b821e3847e33; ?>
<?php unset($__componentOriginalf0ba6ef14ffa9e2e0936b821e3847e33); ?>
<?php endif; ?>

    <!-- MODAL FORM -->
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($showFormModal): ?>
    <div x-data="{ open: true }" x-show="open" x-cloak class="fixed inset-0 z-50 overflow-y-auto">
        <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 sm:p-0">
            <div x-show="open" x-transition class="fixed inset-0 bg-slate-900/50 backdrop-blur-sm"></div>
            <div class="inline-block w-full max-w-2xl p-6 my-8 text-left align-middle transition-all transform bg-white dark:bg-slate-800 shadow-2xl rounded-2xl">
                <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-4 flex items-center gap-2">
                    <svg class="w-5 h-5 text-success-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                    <?php echo e($editingId ? 'Edit' : 'Tambah'); ?> Bandwidth Profile
                </h3>
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-slate-300 mb-1">Nama Bandwidth</label>
                        <input type="text" wire:model="form.name" class="w-full rounded-lg border-gray-300 dark:border-slate-700 dark:bg-slate-900 dark:text-white focus:border-primary-500 focus:ring-primary-500 text-sm" placeholder="Contoh: 10Mbps Basic">
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['form.name'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <span class="text-xs text-red-600 mt-1 block"><?php echo e($message); ?></span> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </div>

                    <!-- MAX SPEED -->
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 bg-slate-50 dark:bg-slate-900 p-4 rounded-xl border border-slate-200 dark:border-slate-700">
                        <div>
                            <label class="block text-sm font-semibold text-primary-600 mb-1">Download Max</label>
                            <input type="number" step="0.01" wire:model.live="form.download_max" class="w-full rounded-lg border-gray-300 dark:border-slate-700 bg-white dark:bg-slate-800 dark:text-white focus:border-primary-500 focus:ring-primary-500 text-sm">
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['form.download_max'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <span class="text-xs text-red-600 mt-1 block"><?php echo e($message); ?></span> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-success-600 mb-1">Upload Max</label>
                            <input type="number" step="0.01" wire:model.live="form.upload_max" class="w-full rounded-lg border-gray-300 dark:border-slate-700 bg-white dark:bg-slate-800 dark:text-white focus:border-primary-500 focus:ring-primary-500 text-sm">
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['form.upload_max'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <span class="text-xs text-red-600 mt-1 block"><?php echo e($message); ?></span> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 dark:text-slate-300 mb-1">Unit</label>
                            <select wire:model="form.unit" class="w-full rounded-lg border-gray-300 dark:border-slate-700 bg-white dark:bg-slate-800 dark:text-white focus:border-primary-500 focus:ring-primary-500 text-sm">
                                <option value="Kbps">Kbps</option>
                                <option value="Mbps">Mbps</option>
                                <option value="Gbps">Gbps</option>
                            </select>
                        </div>
                    </div>

                    <!-- AUTO BURST CONFIG -->
                    <div class="bg-purple-50 dark:bg-purple-900/10 border border-purple-200 dark:border-purple-800 p-4 rounded-xl">
                        <div class="flex items-center justify-between mb-3">
                            <div class="flex items-center gap-2">
                                <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                                <span class="text-sm font-bold text-purple-700 dark:text-purple-300">Auto Generate Burst Limit</span>
                            </div>
                            <label class="inline-flex items-center cursor-pointer">
                                <input type="checkbox" wire:model.live="autoBurst" class="sr-only peer">
                                <div class="relative w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-purple-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-purple-600"></div>
                            </label>
                        </div>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($autoBurst): ?>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div>
                                <label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1">Burst Ratio (× Max)</label>
                                <input type="number" step="0.1" min="1" wire:model.live="burstRatio" class="w-full rounded-lg border-gray-300 dark:border-slate-700 bg-white dark:bg-slate-800 dark:text-white text-sm">
                                <span class="text-[10px] text-gray-400 dark:text-slate-500">Standar ISP: 1.5 s/d 2.0</span>
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1">Threshold (%)</label>
                                <input type="number" min="1" max="99" wire:model.live="thresholdPercent" class="w-full rounded-lg border-gray-300 dark:border-slate-700 bg-white dark:bg-slate-800 dark:text-white text-sm">
                                <span class="text-[10px] text-gray-400 dark:text-slate-500">Standar ISP: 70%</span>
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1">Burst Time (Detik)</label>
                                <input type="number" min="1" wire:model.live="burstTime" class="w-full rounded-lg border-gray-300 dark:border-slate-700 bg-white dark:bg-slate-800 dark:text-white text-sm">
                                <span class="text-[10px] text-gray-400 dark:text-slate-500">Standar ISP: 8s</span>
                            </div>
                        </div>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </div>

                    <!-- HASIL AUTO-GENERATE BURST -->
                    <div class="bg-emerald-50 dark:bg-emerald-900/10 border border-emerald-200 dark:border-emerald-800 p-4 rounded-xl">
                        <div class="text-xs font-bold text-emerald-700 dark:text-emerald-300 mb-3 flex items-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            PREVIEW BURST LIMIT AKHIR
                        </div>
                        <div class="grid grid-cols-2 md:grid-cols-3 gap-3 text-xs">
                            <div>
                                <span class="text-gray-500 dark:text-slate-400">Download Burst</span>
                                <div class="font-bold text-gray-900 dark:text-white text-sm"><?php echo e(number_format($form['burst_limit_download'] ?? 0, 2)); ?> <?php echo e($form['unit'] ?? 'Mbps'); ?></div>
                            </div>
                            <div>
                                <span class="text-gray-500 dark:text-slate-400">Upload Burst</span>
                                <div class="font-bold text-gray-900 dark:text-white text-sm"><?php echo e(number_format($form['burst_limit_upload'] ?? 0, 2)); ?> <?php echo e($form['unit'] ?? 'Mbps'); ?></div>
                            </div>
                            <div>
                                <span class="text-gray-500 dark:text-slate-400">Burst Time</span>
                                <div class="font-bold text-gray-900 dark:text-white text-sm"><?php echo e($form['burst_time_download'] ?? 8); ?>s</div>
                            </div>
                            <div>
                                <span class="text-gray-500 dark:text-slate-400">DL Threshold</span>
                                <div class="font-bold text-gray-900 dark:text-white text-sm"><?php echo e(number_format($form['burst_threshold_download'] ?? 0, 2)); ?> <?php echo e($form['unit'] ?? 'Mbps'); ?></div>
                            </div>
                            <div>
                                <span class="text-gray-500 dark:text-slate-400">UL Threshold</span>
                                <div class="font-bold text-gray-900 dark:text-white text-sm"><?php echo e(number_format($form['burst_threshold_upload'] ?? 0, 2)); ?> <?php echo e($form['unit'] ?? 'Mbps'); ?></div>
                            </div>
                            <div>
                                <span class="text-gray-500 dark:text-slate-400">Formula</span>
                                <div class="font-bold text-emerald-700 dark:text-emerald-300 text-sm">AUTO ✓</div>
                            </div>
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-slate-300 mb-1">Catatan / Deskripsi</label>
                        <textarea wire:model="form.description" rows="2" class="w-full rounded-lg border-gray-300 dark:border-slate-700 dark:bg-slate-900 dark:text-white focus:border-primary-500 focus:ring-primary-500 text-sm"></textarea>
                    </div>
                </div>
                <div class="mt-6 flex justify-end gap-2">
                    <button wire:click="closeFormModal" type="button" class="px-4 py-2.5 text-sm font-medium text-gray-700 dark:text-slate-300 bg-gray-100 dark:bg-slate-700 hover:bg-gray-200 dark:hover:bg-slate-600 rounded-lg">Batal</button>
                    <button wire:click="save" type="button" class="px-5 py-2.5 text-sm font-medium text-white bg-primary-600 hover:bg-primary-700 rounded-lg shadow-sm shadow-primary-500/20">
                        <?php echo e($editingId ? 'Simpan Perubahan' : 'Simpan Bandwidth'); ?>

                    </button>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
</div>
<?php /**PATH C:\Users\Lenovo\OneDrive\dsBilling\resources\views\livewire\profile-paket\bandwidth.blade.php ENDPATH**/ ?>