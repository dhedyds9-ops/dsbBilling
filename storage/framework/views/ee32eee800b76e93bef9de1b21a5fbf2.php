<div class="space-y-6">
    <div class="flex items-center gap-4">
        <div class="p-2 text-slate-500 dark:text-slate-400 rounded-lg bg-slate-100 dark:bg-slate-700">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
        </div>
        <div>
            <h1 class="text-xl font-bold text-slate-900 dark:text-slate-100">Email Gateway</h1>
            <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Konfigurasi SMTP dan Manajemen Agen Email</p>
        </div>
    </div>

    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(session()->has('success_smtp')): ?>
        <div class="p-4 bg-emerald-50 dark:bg-emerald-900/30 text-emerald-700 rounded-lg border border-emerald-200">
            <?php echo e(session('success_smtp')); ?>

        </div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(session()->has('success')): ?>
        <div class="p-4 bg-emerald-50 dark:bg-emerald-900/30 text-emerald-700 rounded-lg border border-emerald-200">
            <?php echo e(session('success')); ?>

        </div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

    <div class="grid grid-cols-1 xl:grid-cols-4 gap-6">
        <div class="xl:col-span-3 space-y-6">
            <?php if (isset($component)) { $__componentOriginalf0ba6ef14ffa9e2e0936b821e3847e33 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalf0ba6ef14ffa9e2e0936b821e3847e33 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.base.card','data' => []] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('base.card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

                <div class="px-4 py-3 border-b border-slate-200 dark:border-slate-700 font-semibold text-slate-900 dark:text-slate-100 flex justify-between items-center">
                    Konfigurasi SMTP Server
                    <?php if (isset($component)) { $__componentOriginala8bb031a483a05f647cb99ed3a469847 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginala8bb031a483a05f647cb99ed3a469847 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.ui.button','data' => ['variant' => 'primary','wire:click' => 'saveSmtp']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('ui.button'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['variant' => 'primary','wire:click' => 'saveSmtp']); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

                        Simpan SMTP
                     <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginala8bb031a483a05f647cb99ed3a469847)): ?>
<?php $attributes = $__attributesOriginala8bb031a483a05f647cb99ed3a469847; ?>
<?php unset($__attributesOriginala8bb031a483a05f647cb99ed3a469847); ?>
<?php endif; ?>
<?php if (isset($__componentOriginala8bb031a483a05f647cb99ed3a469847)): ?>
<?php $component = $__componentOriginala8bb031a483a05f647cb99ed3a469847; ?>
<?php unset($__componentOriginala8bb031a483a05f647cb99ed3a469847); ?>
<?php endif; ?>
                </div>
                <div class="p-4 grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1">SMTP Host</label>
                        <input type="text" wire:model="smtp.host" class="w-full px-3 py-2 border border-slate-300 dark:border-slate-600 rounded focus:ring-2 focus:ring-primary-500 text-sm font-mono dark:bg-slate-900 dark:text-slate-100">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1">SMTP Port</label>
                        <input type="number" wire:model="smtp.port" class="w-full px-3 py-2 border border-slate-300 dark:border-slate-600 rounded focus:ring-2 focus:ring-primary-500 text-sm font-mono dark:bg-slate-900 dark:text-slate-100">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1">Username</label>
                        <input type="text" wire:model="smtp.username" class="w-full px-3 py-2 border border-slate-300 dark:border-slate-600 rounded focus:ring-2 focus:ring-primary-500 text-sm font-mono dark:bg-slate-900 dark:text-slate-100">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1">Password</label>
                        <input type="password" wire:model="smtp.password" class="w-full px-3 py-2 border border-slate-300 dark:border-slate-600 rounded focus:ring-2 focus:ring-primary-500 text-sm font-mono dark:bg-slate-900 dark:text-slate-100">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1">Encryption</label>
                        <select wire:model="smtp.encryption" class="w-full px-3 py-2 border border-slate-300 dark:border-slate-600 rounded focus:ring-2 focus:ring-primary-500 text-sm dark:bg-slate-900 dark:text-slate-100">
                            <option value="tls">TLS</option>
                            <option value="ssl">SSL</option>
                            <option value="">None</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1">From Address</label>
                        <input type="email" wire:model="smtp.from_address" class="w-full px-3 py-2 border border-slate-300 dark:border-slate-600 rounded focus:ring-2 focus:ring-primary-500 text-sm font-mono dark:bg-slate-900 dark:text-slate-100">
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

            <?php if (isset($component)) { $__componentOriginalf0ba6ef14ffa9e2e0936b821e3847e33 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalf0ba6ef14ffa9e2e0936b821e3847e33 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.base.card','data' => []] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('base.card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

                <div class="p-4 border-b border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800/50/50 flex flex-col md:flex-row md:items-center justify-between gap-4">
                    <div class="relative">
                        <svg class="w-4 h-4 text-slate-400 absolute left-3 top-1/2 -translate-y-1/2" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" /></svg>
                        <input type="text" wire:model.live.debounce.300ms="search" placeholder="Cari agen email..." class="pl-9 pr-4 py-2 text-sm border border-slate-300 dark:border-slate-600 rounded focus:ring-2 focus:ring-primary-500 w-64 dark:bg-slate-900 dark:text-slate-100">
                    </div>
                    <div>
                        <button wire:click="create" class="px-4 py-2 bg-primary-600 text-white text-sm font-medium rounded hover:bg-primary-700 transition-colors flex items-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                            Tambah Agen Email
                        </button>
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm text-slate-600 dark:text-slate-300">
                        <thead class="bg-slate-50 dark:bg-slate-800/50 border-b border-slate-200 dark:bg-slate-800/50 dark:border-slate-700">
                            <tr class="text-slate-500 dark:text-slate-400">
                                <th class="p-3 font-semibold">Nama Agen</th>
                                <th class="p-3 font-semibold">Alamat Email</th>
                                <th class="p-3 font-semibold">Notifikasi (Events)</th>
                                <th class="p-3 font-semibold text-center">Status</th>
                                <th class="p-3 font-semibold text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-slate-700/60">
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__empty_0 = true; $__currentLoopData = $filteredAgents; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $agent): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_0 = false; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                                <tr class="hover:bg-slate-50 dark:bg-slate-800/50">
                                    <td class="p-3  font-medium text-slate-900 dark:text-slate-100"><?php echo e($agent['name']); ?></td>
                                    <td class="p-3  text-slate-600 dark:text-slate-400 font-mono"><?php echo e($agent['email']); ?></td>
                                    <td class="p-3  text-slate-600 dark:text-slate-400"><?php echo e($agent['events']); ?></td>
                                    <td class="p-3  text-center">
                                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($agent['status'] === 'active'): ?>
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
Aktif <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalab7baa01105b3dfe1e0cf1dfc58879b4)): ?>
<?php $attributes = $__attributesOriginalab7baa01105b3dfe1e0cf1dfc58879b4; ?>
<?php unset($__attributesOriginalab7baa01105b3dfe1e0cf1dfc58879b4); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalab7baa01105b3dfe1e0cf1dfc58879b4)): ?>
<?php $component = $__componentOriginalab7baa01105b3dfe1e0cf1dfc58879b4; ?>
<?php unset($__componentOriginalab7baa01105b3dfe1e0cf1dfc58879b4); ?>
<?php endif; ?>
                                        <?php else: ?>
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
Nonaktif <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalab7baa01105b3dfe1e0cf1dfc58879b4)): ?>
<?php $attributes = $__attributesOriginalab7baa01105b3dfe1e0cf1dfc58879b4; ?>
<?php unset($__attributesOriginalab7baa01105b3dfe1e0cf1dfc58879b4); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalab7baa01105b3dfe1e0cf1dfc58879b4)): ?>
<?php $component = $__componentOriginalab7baa01105b3dfe1e0cf1dfc58879b4; ?>
<?php unset($__componentOriginalab7baa01105b3dfe1e0cf1dfc58879b4); ?>
<?php endif; ?>
                                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                    </td>
                                    <td class="p-3  text-center">
                                        <button wire:click="edit(<?php echo e($index); ?>)" class="w-7 h-7 inline-flex items-center justify-center rounded shadow-sm transition-colors bg-emerald-500 text-white hover:bg-emerald-600" title="Edit Agen">
                                            <svg class="w-4 h-4 flex items-center justify-center rounded shadow-sm transition-colors bg-emerald-500 text-white hover:bg-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                        </button>
                                    </td>
                                </tr>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_0): ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                                <tr class="hover:bg-slate-50 dark:bg-slate-900/50 dark:hover:bg-slate-800/50 transition-colors">
                                    <td colspan="5" class="p-3  text-center text-slate-500 dark:text-slate-400">Belum ada data Agen Email.</td>
                                </tr>
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </tbody>
                    </table></div>

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
        </div>
        
        <div class="xl:col-span-1 space-y-4">
            <?php if (isset($component)) { $__componentOriginalf0ba6ef14ffa9e2e0936b821e3847e33 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalf0ba6ef14ffa9e2e0936b821e3847e33 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.base.card','data' => []] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('base.card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

                <div class="p-4 border-b border-slate-200 dark:border-slate-700">
                    <h3 class="font-semibold text-slate-900 dark:text-slate-100">Email Penagihan</h3>
                </div>
                <div class="p-4 text-sm text-slate-600 dark:text-slate-400 space-y-3">
                    <p>Sistem ini sudah terintegrasi dengan <strong>InvoiceService</strong>.</p>
                    <p>Ketika invoice/tagihan baru dicetak- sistem akan mencoba mengirim tagihan tersebut melalui WhatsApp- dan <strong>juga ke alamat email pelanggan</strong> jika alamat email valid pada database.</p>
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
        </div>
    </div>

    
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($showModal): ?>
        <div class="fixed inset-0 bg-slate-900/50 backdrop-blur-sm z-50 flex items-center justify-center p-4">
            <div class="bg-white dark:bg-slate-800 rounded shadow-xl w-full max-w-md overflow-hidden">
                <div class="px-6 py-4 border-b border-slate-200 dark:border-slate-700 flex items-center justify-between">
                    <h3 class="text-lg font-bold text-slate-900 dark:text-slate-100"><?php echo e($form['id'] ?'Edit Agen' : 'Tambah Agen Email'); ?></h3>
                    <button wire:click="$set('showModal'- false)" class="text-slate-400 hover:text-slate-500 dark:text-slate-400">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>
                <div class="p-6 space-y-4 text-sm">
                    <div>
                        <label class="block font-medium text-slate-700 dark:text-slate-300 mb-1">Nama Agen / Departemen *</label>
                        <input type="text" wire:model="form.name" class="w-full px-3 py-2 border border-slate-300 dark:border-slate-600 rounded focus:ring-2 focus:ring-primary-500 dark:bg-slate-900 dark:text-slate-100">
                    </div>
                    <div>
                        <label class="block font-medium text-slate-700 dark:text-slate-300 mb-1">Alamat Email *</label>
                        <input type="email" wire:model="form.email" class="w-full px-3 py-2 border border-slate-300 dark:border-slate-600 rounded focus:ring-2 focus:ring-primary-500 font-mono dark:bg-slate-900 dark:text-slate-100">
                    </div>
                    <div>
                        <label class="block font-medium text-slate-700 dark:text-slate-300 mb-1">Tipe Notifikasi (Events)</label>
                        <input type="text" wire:model="form.events" placeholder="Contoh: billing- ticket- alarm" class="w-full px-3 py-2 border border-slate-300 dark:border-slate-600 rounded focus:ring-2 focus:ring-primary-500 dark:bg-slate-900 dark:text-slate-100">
                    </div>
                    <div>
                        <label class="block font-medium text-slate-700 dark:text-slate-300 mb-1">Status</label>
                        <select wire:model="form.status" class="w-full px-3 py-2 border border-slate-300 dark:border-slate-600 rounded focus:ring-2 focus:ring-primary-500 dark:bg-slate-900 dark:text-slate-100">
                            <option value="active">Aktif</option>
                            <option value="inactive">Nonaktif</option>
                        </select>
                    </div>
                </div>
                <div class="px-6 py-4 bg-slate-50 dark:bg-slate-800/50 border-t border-slate-200 dark:border-slate-700 flex justify-end gap-3">
                    <button wire:click="$set('showModal'- false)" class="px-4 py-2 text-sm font-medium text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:bg-slate-700 rounded transition-colors">Batal</button>
                    <button wire:click="save" class="px-4 py-2 text-sm font-medium bg-primary-600 text-white rounded hover:bg-primary-700 transition-colors">Simpan</button>
                </div>
            </div>
        </div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
</div>






<?php /**PATH D:\dsBilling\resources\views\livewire\pengaturan\email\agent.blade.php ENDPATH**/ ?>