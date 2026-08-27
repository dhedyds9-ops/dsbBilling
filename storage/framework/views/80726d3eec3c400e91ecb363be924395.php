<div class="max-w-7xl mx-auto p-4 sm:p-6 lg:p-8" wire:poll.5s>

    <!-- Session Alerts -->
    <div class="mb-4 space-y-2">
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
    </div>

    <!-- Tabs Setup -->
    <?php
        $tabColors = [
            'pppoe'    => 'border-blue-600 text-blue-600',
            'hotspot'  => 'border-green-600 text-green-600',
            'voucher'  => 'border-purple-600 text-purple-600',
        ];
        $inactiveTab = 'border-transparent text-slate-500 hover:text-slate-700 hover:border-slate-300';
    ?>

    <!-- Tabs Navigation + Search -->
    <div class="border-b border-slate-200 mb-4">
        <div class="flex items-center justify-between">
            <nav class="flex gap-4" aria-label="Tabs">
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $tabColors; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $tab => $color): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                    <button
                        wire:click="setActiveTab('<?php echo e($tab); ?>')"
                        class="px-4 py-2 text-sm font-medium border-b-2 transition-colors <?php echo e($activeTab === $tab ? $color : $inactiveTab); ?>">
                        <?php echo e(ucfirst($tab)); ?>

                    </button>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
            </nav>
            <div class="w-full md:w-96">
                <label for="user-online-search" class="sr-only">Cari User Online</label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                        </svg>
                    </div>
                    <input
                        id="user-online-search"
                        wire:model.live.debounce.300ms="search"
                        type="text"
                        class="block w-full pl-10 pr-3 py-2.5 border border-gray-300 rounded-lg text-sm bg-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-primary-500 transition"
                        placeholder="Cari user, router, IP, MAC, voucher, owner..."
                    >
                </div>
            </div>
        </div>
    </div>

    <!-- Tab Content -->
    <div>
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($activeTab === 'pppoe'): ?>
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
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="th-col">Nama User</th>
                                <th class="th-col">Router</th>
                                <th class="th-col">Alamat IP</th>
                                <th class="th-col">Uptime</th>
                                <th class="th-col">Traffic (In/Out)</th>
                                <th class="th-col">Mulai Session</th>
                                <th class="th-col text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $pppoeSessions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $session): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                                <tr <?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::$currentLoop['key'] = 'pppoe-'.e($session->id).''; ?>wire:key="pppoe-<?php echo e($session->id); ?>" class="hover:bg-gray-50 transition-colors">
                                    <td class="td-col font-medium"><?php echo e($session->name); ?></td>
                                    <td class="td-col"><?php echo e($session->router?->name ?? '-'); ?></td>
                                    <td class="td-col"><?php echo e($session->address); ?></td>
                                    <td class="td-col"><?php echo e($session->uptime); ?></td>
                                    <td class="td-col">
                                        <span class="text-blue-600"><?php echo e(number_format($session->bytes_in)); ?></span> / 
                                        <span class="text-green-600"><?php echo e(number_format($session->bytes_out)); ?></span>
                                    </td>
                                    <td class="td-col"><?php echo e($session->session_started_at ? $session->session_started_at->format('d/m/Y H:i') : '-'); ?></td>
                                    <td class="td-col text-center">
                                        <button 
                                            wire:click="kickPppoe(<?php echo e($session->id); ?>)" 
                                            wire:confirm="Yakin ingin memutuskan koneksi PPPoE user <?php echo e($session->name); ?>?"
                                            class="inline-flex items-center justify-center p-1.5 rounded-md text-red-600 hover:bg-red-100 hover:text-red-800 focus:outline-none focus:ring-2 focus:ring-red-500 transition-colors"
                                            title="Kick User">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path>
                                            </svg>
                                        </button>
                                    </td>
                                </tr>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                                <tr>
                                    <td colspan="7" class="px-6 py-12 text-center">
                                        <div class="empty-state">
                                            <div class="empty-state-icon">
                                                <svg class="w-6 h-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path></svg>
                                            </div>
                                            <h3 class="empty-state-title">Tidak ada PPPoE User Online</h3>
                                            <p class="empty-state-desc">Semua PPPoE User sedang offline.</p>
                                        </div>
                                    </td>
                                </tr>
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </tbody>
                    </table>
                </div>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($pppoeSessions->hasPages()): ?>
                    <div class="bg-white px-6 py-3 border-t border-gray-200">
                        <?php echo e($pppoeSessions->links()); ?>

                    </div>
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

        <?php elseif($activeTab === 'hotspot'): ?>
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
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="th-col">Nama User</th>
                                <th class="th-col">Router</th>
                                <th class="th-col">Alamat IP</th>
                                <th class="th-col">MAC Address</th>
                                <th class="th-col">Uptime</th>
                                <th class="th-col">Traffic (In/Out)</th>
                                <th class="th-col">Mulai Session</th>
                                <th class="th-col text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $hotspotSessions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $session): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                                <tr <?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::$currentLoop['key'] = 'hotspot-'.e($session->id).''; ?>wire:key="hotspot-<?php echo e($session->id); ?>" class="hover:bg-gray-50 transition-colors">
                                    <td class="td-col font-medium"><?php echo e($session->user); ?></td>
                                    <td class="td-col"><?php echo e($session->router?->name ?? '-'); ?></td>
                                    <td class="td-col"><?php echo e($session->address); ?></td>
                                    <td class="td-col"><?php echo e($session->mac_address); ?></td>
                                    <td class="td-col"><?php echo e($session->uptime); ?></td>
                                    <td class="td-col">
                                        <span class="text-blue-600"><?php echo e(number_format($session->bytes_in)); ?></span> / 
                                        <span class="text-green-600"><?php echo e(number_format($session->bytes_out)); ?></span>
                                    </td>
                                    <td class="td-col"><?php echo e($session->session_started_at ? $session->session_started_at->format('d/m/Y H:i') : '-'); ?></td>
                                    <td class="td-col text-center">
                                        <button 
                                            wire:click="kickHotspot(<?php echo e($session->id); ?>)" 
                                            wire:confirm="Yakin ingin memutuskan koneksi Hotspot user <?php echo e($session->user); ?>?"
                                            class="inline-flex items-center justify-center p-1.5 rounded-md text-red-600 hover:bg-red-100 hover:text-red-800 focus:outline-none focus:ring-2 focus:ring-red-500 transition-colors"
                                            title="Kick User">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path>
                                            </svg>
                                        </button>
                                    </td>
                                </tr>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                                <tr>
                                    <td colspan="8" class="px-6 py-12 text-center">
                                        <div class="empty-state">
                                            <div class="empty-state-icon">
                                                <svg class="w-6 h-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path></svg>
                                            </div>
                                            <h3 class="empty-state-title">Tidak ada Hotspot User Online</h3>
                                            <p class="empty-state-desc">Semua Hotspot User sedang offline.</p>
                                        </div>
                                    </td>
                                </tr>
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </tbody>
                    </table>
                </div>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($hotspotSessions->hasPages()): ?>
                    <div class="bg-white px-6 py-3 border-t border-gray-200">
                        <?php echo e($hotspotSessions->links()); ?>

                    </div>
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

        <?php elseif($activeTab === 'voucher'): ?>
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
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="th-col">Kode Voucher</th>
                                <th class="th-col">Paket</th>
                                <th class="th-col">Status</th>
                                <th class="th-col">Hotspot User</th>
                                <th class="th-col">Owner</th>
                                <th class="th-col">Tgl Aktifasi</th>
                                <th class="th-col">Kadaluarsa</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $vouchers; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $voucher): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                                <tr <?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::$currentLoop['key'] = 'voucher-'.e($voucher->id).''; ?>wire:key="voucher-<?php echo e($voucher->id); ?>" class="hover:bg-gray-50 transition-colors">
                                    <td class="td-col font-medium"><?php echo e($voucher->code); ?></td>
                                    <td class="td-col"><?php echo e($voucher->serviceProfile?->name ?? '-'); ?></td>
                                    <td class="td-col">
                                        <span class="status-badge <?php echo e($voucher->status === 'active' ? 'bg-green-100 text-green-800' : ($voucher->status === 'expired' ? 'bg-red-100 text-red-800' : 'bg-gray-100 text-gray-800')); ?>">
                                            <?php echo e(ucfirst($voucher->status)); ?>

                                        </span>
                                    </td>
                                    <td class="td-col"><?php echo e($voucher->hotspotUser?->username ?? '-'); ?></td>
                                    <td class="td-col"><?php echo e($voucher->owner?->name ?? '-'); ?></td>
                                    <td class="td-col"><?php echo e($voucher->activated_at ? $voucher->activated_at->format('d/m/Y H:i') : '-'); ?></td>
                                    <td class="td-col"><?php echo e($voucher->expires_at ? $voucher->expires_at->format('d/m/Y H:i') : '-'); ?></td>
                                </tr>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                                <tr>
                                    <td colspan="7" class="px-6 py-12 text-center">
                                        <div class="empty-state">
                                            <div class="empty-state-icon">
                                                <svg class="w-6 h-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path></svg>
                                            </div>
                                            <h3 class="empty-state-title">Tidak ada Voucher Aktif</h3>
                                            <p class="empty-state-desc">Belum ada voucher yang diaktifkan.</p>
                                        </div>
                                    </td>
                                </tr>
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </tbody>
                    </table>
                </div>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($vouchers->hasPages()): ?>
                    <div class="bg-white px-6 py-3 border-t border-gray-200">
                        <?php echo e($vouchers->links()); ?>

                    </div>
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
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
    </div>
</div>

<!-- Tambahkan CSS Helper di bawah ini atau pindahkan ke file CSS global Anda -->
<style>
    .th-col { 
        padding: 0.75rem 1.5rem; 
        text-align: left; 
        font-size: 0.75rem; 
        font-weight: 600; 
        color: #6b7280; 
        uppercase; 
        letter-spacing: 0.05em; 
    }
    .td-col { 
        padding: 1rem 1.5rem; 
        white-space: nowrap; 
        font-size: 0.875rem; 
        color: #4b5563; 
    }
    .empty-state { 
        display: flex; flex-direction: column; align-items: center; justify-content: center; 
    }
    .empty-state-icon { 
        width: 3rem; height: 3rem; background-color: #f3f4f6; border-radius: 9999px; 
        display: flex; align-items: center; justify-content: center; margin-bottom: 0.75rem; 
    }
    .empty-state-title { font-size: 0.875rem; font-weight: 600; color: #111827; margin-bottom: 0.25rem; }
    .empty-state-desc { font-size: 0.75rem; color: #6b7280; }
    .status-badge { 
        padding: 0.25rem 0.625rem; display: inline-flex; font-size: 0.75rem; 
        line-height: 1rem; font-weight: 600; border-radius: 9999px; 
    }
</style><?php /**PATH C:\Users\Lenovo\OneDrive\dsBilling\resources\views\livewire\isp\user-online\index.blade.php ENDPATH**/ ?>