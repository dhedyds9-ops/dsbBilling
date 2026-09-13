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

    
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(session()->has('success')): ?>
        <div class="mb-3 px-4 py-3 bg-emerald-50 dark:bg-emerald-900/30 border border-emerald-200 dark:border-emerald-700 text-emerald-800 dark:text-emerald-300 rounded-lg text-sm"><?php echo e(session('success')); ?></div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(session()->has('error')): ?>
        <div class="mb-3 px-4 py-3 bg-red-50 dark:bg-red-900/30 border border-red-200 dark:border-red-700 text-red-800 dark:text-red-300 rounded-lg text-sm"><?php echo e(session('error')); ?></div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

    
    <div class="flex justify-between items-center mb-4">
    <?php $__env->startSection('page_title'); ?>
        <div>
            <h1 class="text-xl font-bold text-slate-900 dark:text-slate-100">Paket Internet</h1>
            <p class="text-sm text-slate-500 dark:text-slate-400 mt-0.5">Kelola seluruh paket layanan internet</p>
        </div>
        <?php $__env->stopSection(); ?>
        <div class="flex items-center gap-2">
            <a href="<?php echo e(route('isp.service-profiles.create')); ?>" class="inline-flex items-center gap-2 px-4 py-2 bg-primary-600 text-white rounded-lg hover:bg-primary-700 transition-colors text-sm font-medium">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                Tambah Paket
            </a>
        </div>
    </div>

    
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(!empty($selectedPackages)): ?>
        <div class="mb-3 px-4 py-3 bg-primary-50 dark:bg-primary-900/30 border border-primary-200 dark:border-primary-700 rounded-xl flex flex-wrap items-center gap-3">
            <span class="px-2.5 py-1 bg-primary-100 dark:bg-primary-800 text-primary-800 dark:text-primary-200 rounded-full text-sm font-medium">
                <?php echo e(count($selectedPackages)); ?> Paket Dipilih
            </span>
            <button wire:click="$set('selectedPackages', [])" class="text-sm text-slate-500 dark:text-slate-400 hover:text-slate-700 dark:text-slate-300 dark:hover:text-slate-200">
                Batal
            </button>
            <div class="flex items-center gap-2 ml-auto">
                <button wire:click="confirmBulkActivate" wire:loading.attr="disabled" class="inline-flex items-center gap-1.5 px-3 py-1.5 text-sm bg-emerald-600 text-white rounded-lg hover:bg-emerald-700 transition-colors">
                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg> Aktifkan
                </button>
                <button wire:click="confirmBulkDeactivate" wire:loading.attr="disabled" class="inline-flex items-center gap-1.5 px-3 py-1.5 text-sm bg-amber-500 text-white rounded-lg hover:bg-amber-600 transition-colors">
                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M10 9v6m4-6v6m7-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg> Nonaktifkan
                </button>
                <button wire:click="confirmBulkDelete" wire:loading.attr="disabled" class="inline-flex items-center gap-1.5 px-3 py-1.5 text-sm bg-red-600 text-white rounded-lg hover:bg-red-700 transition-colors">
                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg> Hapus
                </button>
            </div>
        </div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

    
    <div class="flex flex-col sm:flex-row gap-3 items-center justify-between bg-white dark:bg-slate-800 p-4 rounded-xl shadow-sm border border-slate-200 dark:border-slate-700 mb-4">
        
        <div class="flex-1 w-full relative max-w-sm">
            <span class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
            </span>
            <input type="text" wire:model.live.debounce.300ms="search" placeholder="Cari nama paket atau bandwidth..." class="w-full pl-9 pr-4 py-2 bg-slate-50 dark:bg-slate-900/50 border border-slate-200 dark:border-slate-700 rounded-lg text-sm text-slate-700 dark:text-slate-300 focus:outline-none focus:ring-2 focus:ring-primary-500 transition-all dark:bg-slate-900 dark:text-slate-100">
        </div>

        
        <div class="flex items-center gap-2 flex-wrap">
            <select wire:model.live="filters.service_type" class="bg-slate-50 dark:bg-slate-900/50 border border-slate-200 dark:border-slate-700 text-slate-700 dark:text-slate-300 text-sm rounded-lg focus:ring-primary-500 focus:border-primary-500 block pl-3 pr-8 py-2 dark:bg-slate-900 dark:text-slate-100">
                <option value="">Semua Jenis</option>
                <option value="pppoe">PPPoE</option>
                <option value="hotspot">Hotspot</option>
                <option value="voucher">Voucher</option>
            </select>
            <select wire:model.live="filters.status" class="bg-slate-50 dark:bg-slate-900/50 border border-slate-200 dark:border-slate-700 text-slate-700 dark:text-slate-300 text-sm rounded-lg focus:ring-primary-500 focus:border-primary-500 block pl-3 pr-8 py-2 dark:bg-slate-900 dark:text-slate-100">
                <option value="">Semua Status</option>
                <option value="active">Aktif</option>
                <option value="inactive">Nonaktif</option>
            </select>
            <?php $isAdministrator = auth()->user()->hasRole('administrator') ?? false; ?>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($isAdministrator): ?>
                <select wire:model.live="filters.owner_id" class="bg-slate-50 dark:bg-slate-900/50 border border-slate-200 dark:border-slate-700 text-slate-700 dark:text-slate-300 text-sm rounded-lg focus:ring-primary-500 focus:border-primary-500 block pl-3 pr-8 py-2 dark:bg-slate-900 dark:text-slate-100">
                    <option value="">Semua Owner</option>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = \App\Models\User::select('id', 'name')->whereHas('roles', fn($q) => $q->whereIn('name', \App\Enums\UserRole::backofficeRoles()))->orderBy('name')->limit(100)->get(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $user): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                        <option value="<?php echo e($user->id); ?>"><?php echo e($user->name); ?></option>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                </select>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            <select wire:model.live="perPage" class="bg-slate-50 dark:bg-slate-900/50 border border-slate-200 dark:border-slate-700 text-slate-700 dark:text-slate-300 text-sm rounded-lg focus:ring-primary-500 focus:border-primary-500 block pl-3 pr-8 py-2 dark:bg-slate-900 dark:text-slate-100">
                <option value="10">10 Baris</option>
                <option value="25">25 Baris</option>
                <option value="50">50 Baris</option>
                <option value="100">100 Baris</option>
            </select>
            <button wire:click="resetFilters" title="Reset Filter" class="p-2 bg-slate-50 dark:bg-slate-900/50 border border-slate-200 dark:border-slate-700 text-slate-500 dark:text-slate-400 hover:text-slate-700 dark:text-slate-300 dark:hover:text-slate-200 hover:bg-slate-100 dark:bg-slate-800 dark:hover:bg-slate-700 rounded-lg transition-colors">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
            </button>
        </div>
    </div>

    
    <div class="bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl shadow-sm overflow-hidden overflow-x-auto">
        <table class="w-full text-sm text-left">
            <thead>
                <tr class="border-b border-slate-200 dark:border-slate-700 text-xs uppercase tracking-wider text-slate-500 dark:text-slate-400 bg-slate-50 dark:bg-slate-900/80">
                    <th class="px-4 py-3 font-semibold">
                        <input type="checkbox" wire:model.live="selectAll" class="w-4 h-4 text-primary-600 border-slate-300 dark:border-slate-600 rounded dark:bg-slate-900 dark:text-slate-100">
                    </th>
                    <th class="px-4 py-3 font-semibold whitespace-nowrap">Status</th>
                    <th class="px-4 py-3 font-semibold whitespace-nowrap">Nama Paket</th>
                    <th class="px-4 py-3 font-semibold whitespace-nowrap">Jenis</th>
                    <th class="px-4 py-3 font-semibold whitespace-nowrap">Bandwidth</th>
                    <th class="px-4 py-3 font-semibold whitespace-nowrap">Tipe</th>
                    <th class="px-4 py-3 font-semibold whitespace-nowrap">Shared</th>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($isAdministrator): ?>
                        <th class="px-4 py-3 font-semibold whitespace-nowrap text-right">Harga Reseller</th>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    <th class="px-4 py-3 font-semibold whitespace-nowrap text-right">Harga Jual</th>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($isAdministrator): ?>
                        <th class="px-4 py-3 font-semibold whitespace-nowrap text-right">Komisi Reseller</th>
                        <th class="px-4 py-3 font-semibold whitespace-nowrap">Owner</th>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    <th class="px-4 py-3 font-semibold whitespace-nowrap text-center">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 dark:divide-slate-700">
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__empty_0 = true; $__currentLoopData = $profiles; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $profile): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_0 = false; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                    <tr <?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::$currentLoop['key'] = ''.e($profile->id).''; ?>wire:key="<?php echo e($profile->id); ?>" class="hover:bg-slate-50 dark:hover:bg-slate-800/50 transition-colors <?php echo e($profile->trashed() ? 'bg-red-50 dark:bg-red-900/10 opacity-75' : ''); ?>">
                        <td class="px-4 py-3">
                            <input type="checkbox" wire:model.live="selectedPackages" value="<?php echo e($profile->id); ?>" class="w-4 h-4 text-primary-600 border-slate-300 dark:border-slate-600 rounded" <?php if($profile->trashed()): echo 'disabled'; endif; ?>>
                        </td>
                        <td class="px-4 py-3 whitespace-nowrap">
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($profile->trashed()): ?>
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold bg-red-100 dark:bg-red-900/40 text-red-700 dark:text-red-400">Dihapus</span>
                            <?php elseif($profile->status === 'active'): ?>
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold bg-emerald-100 dark:bg-emerald-900/40 text-emerald-700 dark:text-emerald-400">Aktif</span>
                            <?php else: ?>
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold bg-slate-100 dark:bg-slate-700 text-slate-600 dark:text-slate-400">Nonaktif</span>
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </td>
                        <td class="px-4 py-3">
                            <div class="font-medium text-slate-900 dark:text-slate-100"><?php echo e($profile->name); ?></div>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($profile->description): ?>
                                <div class="text-xs text-slate-400 truncate max-w-40"><?php echo e($profile->description); ?></div>
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </td>
                        <td class="px-4 py-3 whitespace-nowrap">
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(strtolower($profile->service_type) === 'hotspot'): ?>
                                <span class="inline-flex items-center px-2 py-1 rounded-md text-[10px] font-bold tracking-wide bg-orange-100 text-orange-700 dark:bg-orange-900/30 dark:text-orange-400 uppercase">HOTSPOT</span>
                            <?php elseif(strtolower($profile->service_type) === 'pppoe'): ?>
                                <span class="inline-flex items-center px-2 py-1 rounded-md text-[10px] font-bold tracking-wide bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400 uppercase">PPPOE</span>
                            <?php elseif(strtolower($profile->service_type) === 'voucher'): ?>
                                <span class="inline-flex items-center px-2 py-1 rounded-md text-[10px] font-bold tracking-wide bg-purple-100 text-purple-700 dark:bg-purple-900/30 dark:text-purple-400 uppercase">VOUCHER</span>
                            <?php else: ?>
                                <span class="inline-flex items-center px-2 py-1 rounded-md text-[10px] font-bold tracking-wide bg-slate-100 text-slate-700 dark:bg-slate-800 dark:text-slate-300 uppercase"><?php echo e($profile->service_type); ?></span>
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </td>
                        <td class="px-4 py-3 text-slate-600 dark:text-slate-400 whitespace-nowrap"><?php echo e($profile->download_speed); ?>/<?php echo e($profile->upload_speed); ?> Mbps</td>
                        <td class="px-4 py-3 text-slate-600 dark:text-slate-400"><?php echo e($profile->package_type ?? '-'); ?></td>
                        <td class="px-4 py-3 text-center whitespace-nowrap">
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($profile->max_devices): ?>
                                <span class="inline-flex items-center px-2 py-1 rounded-md text-[10px] font-bold tracking-wide bg-indigo-50 text-indigo-700 dark:bg-indigo-900/30 dark:text-indigo-400">
                                    <span class="material-symbols-outlined notranslate text-[12px] mr-1" style="font-size: 14px;">devices</span>
                                    <?php echo e($profile->max_devices); ?> Device
                                </span>
                            <?php else: ?>
                                <span class="inline-flex items-center px-2 py-1 rounded-md text-[10px] font-bold tracking-wide bg-slate-100 text-slate-500 dark:bg-slate-800 dark:text-slate-400">
                                    Unlimited
                                </span>
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </td>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($isAdministrator): ?>
                            <td class="px-4 py-3 text-slate-600 dark:text-slate-400 text-right whitespace-nowrap">Rp <?php echo e(number_format($profile->reseller_price ?? 0, 0, ',', '.')); ?></td>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        <td class="px-4 py-3 font-semibold text-slate-800 dark:text-slate-200 text-right whitespace-nowrap">Rp <?php echo e(number_format($profile->base_price, 0, ',', '.')); ?></td>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($isAdministrator): ?>
                            <td class="px-4 py-3 font-semibold text-emerald-600 dark:text-emerald-400 text-right whitespace-nowrap">Rp <?php echo e(number_format(max(0, ($profile->base_price ?? 0) - ($profile->reseller_price ?? 0)), 0, ',', '.')); ?></td>
                            <td class="px-4 py-3 text-slate-600 dark:text-slate-400 text-sm"><?php echo e($profile->owner->name ?? 'System'); ?></td>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        <td class="px-4 py-3">
                            <div class="flex items-center justify-center gap-1.5">
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(!$profile->trashed()): ?>
                                    <a href="<?php echo e(route('isp.service-profiles.edit', $profile->id)); ?>" class="p-1.5 bg-slate-100 dark:bg-slate-800 text-slate-500 dark:text-slate-400 hover:bg-amber-100 dark:bg-amber-900/50 hover:text-amber-600 dark:hover:bg-amber-900/30 dark:hover:text-amber-400 rounded-md transition-colors" title="Edit">
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                    </a>
                                    <button type="button" wire:click="toggleStatus(<?php echo e($profile->id); ?>)" wire:loading.attr="disabled" class="p-1.5 bg-slate-100 dark:bg-slate-800 rounded-md transition-colors <?php echo e($profile->status === 'active' ? 'text-slate-500 dark:text-slate-400 hover:bg-orange-100 hover:text-orange-600 dark:hover:bg-orange-900/30 dark:hover:text-orange-400' : 'text-slate-500 dark:text-slate-400 hover:bg-emerald-100 dark:bg-emerald-900/50 hover:text-emerald-600 dark:hover:bg-emerald-900/30 dark:hover:text-emerald-400'); ?>" title="<?php echo e($profile->status === 'active' ? 'Nonaktifkan' : 'Aktifkan'); ?>">
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                                    </button>
                                    <button type="button" wire:click="delete(<?php echo e($profile->id); ?>)" wire:loading.attr="disabled" wire:confirm="Hapus paket ini?" class="p-1.5 bg-slate-100 dark:bg-slate-800 text-slate-500 dark:text-slate-400 hover:bg-red-100 dark:bg-red-900/50 hover:text-red-600 dark:hover:bg-red-900/30 dark:hover:text-red-400 rounded-md transition-colors" title="Hapus">
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                    </button>
                                <?php else: ?>
                                    <button type="button" wire:click="restore(<?php echo e($profile->id); ?>)" wire:loading.attr="disabled" wire:confirm="Pulihkan paket ini?" class="p-1.5 text-emerald-500 hover:text-emerald-700 dark:hover:text-emerald-300 rounded-md hover:bg-slate-100 dark:bg-slate-800 dark:hover:bg-slate-700 transition-colors" title="Pulihkan">
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                                    </button>
                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            </div>
                        </td>
                    </tr>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_0): ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                    <tr>
                        <td colspan="12" class="px-6 py-16 text-center">
                            <div class="flex flex-col items-center justify-center">
                                <div class="w-16 h-16 bg-slate-100 dark:bg-slate-700 rounded-full flex items-center justify-center mb-4">
                                    <svg class="w-8 h-8 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                                </div>
                                <h3 class="text-base font-semibold text-slate-900 dark:text-slate-100 mb-1">Belum ada Paket Internet</h3>
                                <p class="text-sm text-slate-500 dark:text-slate-400 mb-5">Silakan tambahkan paket internet pertama.</p>
                                <a href="<?php echo e(route('isp.service-profiles.create')); ?>" class="inline-flex items-center gap-2 px-4 py-2 bg-primary-600 text-white rounded-lg hover:bg-primary-700 transition-colors text-sm font-medium">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                                    Tambah Paket
                                </a>
                            </div>
                        </td>
                    </tr>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </tbody>
        </table>

        
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($profiles->hasPages()): ?>
            <div class="px-4 py-3 border-t border-slate-200 dark:border-slate-700">
                <?php echo e($profiles->links()); ?>

            </div>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
    </div>

    
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($showImportModal): ?>
        <div class="fixed inset-0 z-50 overflow-y-auto bg-slate-500/75 dark:bg-slate-900/80">
            <div class="flex items-center justify-center min-h-screen p-4">
                <div class="w-full max-w-lg bg-white dark:bg-slate-800 rounded-xl shadow-xl p-6">
                    <h3 class="text-lg font-semibold text-slate-900 dark:text-slate-100 mb-1">Import Paket Internet</h3>
                    <p class="text-sm text-slate-500 dark:text-slate-400 mb-4">Pilih file Excel (.xlsx, .xls) atau CSV untuk diimpor.</p>
                    <div class="space-y-4">
                        <input type="file" wire:model="importFile" class="block w-full text-sm text-slate-500 dark:text-slate-400 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-medium file:bg-primary-50 file:text-primary-700 hover:file:bg-primary-100 dark:bg-slate-900 dark:text-slate-100">
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['importFile'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <span class="text-red-500 text-sm"><?php echo e($message); ?></span> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </div>
                    <div class="flex justify-end gap-3 mt-5">
                        <button wire:click="closeImportModal" type="button" class="px-4 py-2 text-slate-700 dark:text-slate-300 bg-white dark:bg-slate-700 border border-slate-300 dark:border-slate-600 rounded-lg hover:bg-slate-50 dark:bg-slate-900/50 dark:hover:bg-slate-600 transition-colors text-sm font-medium">Batal</button>
                        <button wire:click="import" wire:loading.attr="disabled" type="button" class="px-4 py-2 text-white bg-primary-600 rounded-lg hover:bg-primary-700 transition-colors text-sm font-medium">
                            <span wire:loading.remove>Import</span>
                            <span wire:loading>Memproses...</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

    
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($showBulkEditModal): ?>
        <div class="fixed inset-0 z-50 overflow-y-auto bg-slate-500/75 dark:bg-slate-900/80">
            <div class="flex items-center justify-center min-h-screen p-4">
                <div class="w-full max-w-2xl bg-white dark:bg-slate-800 rounded-xl shadow-xl p-6">
                    <h3 class="text-lg font-semibold text-slate-900 dark:text-slate-100 mb-1">Edit Massal <?php echo e(count($selectedPackages)); ?> Paket</h3>
                    <p class="text-sm text-slate-500 dark:text-slate-400 mb-4">Isi hanya kolom yang ingin diubah.</p>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Download Speed (Mbps)</label>
                            <input type="number" wire:model="bulkEditData.download_speed" class="w-full px-3 py-2 border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-900 dark:bg-slate-700 text-slate-900 dark:text-slate-100 rounded-lg focus:ring-2 focus:ring-primary-500 text-sm dark:bg-slate-900 dark:text-slate-100" placeholder="Kosongkan jika tidak diubah">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Upload Speed (Mbps)</label>
                            <input type="number" wire:model="bulkEditData.upload_speed" class="w-full px-3 py-2 border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-900 dark:bg-slate-700 text-slate-900 dark:text-slate-100 rounded-lg focus:ring-2 focus:ring-primary-500 text-sm dark:bg-slate-900 dark:text-slate-100" placeholder="Kosongkan jika tidak diubah">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Harga Jual</label>
                            <input type="number" wire:model="bulkEditData.base_price" class="w-full px-3 py-2 border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-900 dark:bg-slate-700 text-slate-900 dark:text-slate-100 rounded-lg focus:ring-2 focus:ring-primary-500 text-sm dark:bg-slate-900 dark:text-slate-100" placeholder="Kosongkan jika tidak diubah">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Harga Reseller</label>
                            <input type="number" wire:model="bulkEditData.reseller_price" class="w-full px-3 py-2 border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-900 dark:bg-slate-700 text-slate-900 dark:text-slate-100 rounded-lg focus:ring-2 focus:ring-primary-500 text-sm dark:bg-slate-900 dark:text-slate-100" placeholder="Kosongkan jika tidak diubah">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Status</label>
                            <select wire:model="bulkEditData.status" class="w-full px-3 py-2 border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-900 dark:bg-slate-700 text-slate-900 dark:text-slate-100 rounded-lg focus:ring-2 focus:ring-primary-500 text-sm dark:bg-slate-900 dark:text-slate-100">
                                <option value="">-- Pilih Status --</option>
                                <option value="active">Aktif</option>
                                <option value="inactive">Nonaktif</option>
                            </select>
                        </div>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($isAdministrator): ?>
                            <div class="md:col-span-2">
                                <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Owner</label>
                                <select wire:model="bulkEditData.owner_id" class="w-full px-3 py-2 border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-900 dark:bg-slate-700 text-slate-900 dark:text-slate-100 rounded-lg focus:ring-2 focus:ring-primary-500 text-sm dark:bg-slate-900 dark:text-slate-100">
                                    <option value="">-- Pilih Owner --</option>
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = \App\Models\User::select('id', 'name')->limit(100)->get(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $user): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                                        <option value="<?php echo e($user->id); ?>"><?php echo e($user->name); ?></option>
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                                </select>
                            </div>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </div>
                    <div class="flex justify-end gap-3 mt-6">
                        <button wire:click="closeBulkEditModal" type="button" class="px-4 py-2 text-slate-700 dark:text-slate-300 bg-white dark:bg-slate-700 border border-slate-300 dark:border-slate-600 rounded-lg hover:bg-slate-50 dark:bg-slate-900/50 dark:hover:bg-slate-600 transition-colors text-sm font-medium">Batal</button>
                        <button wire:click="bulkEdit" wire:loading.attr="disabled" type="button" class="px-4 py-2 text-white bg-primary-600 rounded-lg hover:bg-primary-700 transition-colors text-sm font-medium">
                            <span wire:loading.remove>Simpan Perubahan</span>
                            <span wire:loading>Menyimpan...</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
</div>
<?php /**PATH D:\dsBilling\resources\views\livewire\isp\service-profile\index.blade.php ENDPATH**/ ?>