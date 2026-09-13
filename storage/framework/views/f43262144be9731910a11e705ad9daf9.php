<?php $__env->startSection('page_title'); ?>
    <a href="<?php echo e(route('reseller-portal.customers.index')); ?>" class="flex items-center text-slate-500 hover:text-slate-800 dark:text-slate-200 dark:hover:text-slate-100 transition-colors mr-2">
        <span class="material-symbols-outlined notranslate" translate="no">arrow_back</span>
    </a>
    <div class="w-8 h-8 rounded-full bg-indigo-100 dark:bg-indigo-900/50 flex items-center justify-center text-indigo-600 dark:text-indigo-400 font-bold text-sm mr-2">
        <?php echo e(substr($customer->name ?? 'U', 0, 1)); ?>

    </div>
    <span class="text-lg">Detail Pelanggan</span>
<?php $__env->stopSection(); ?>

<div class="space-y-6 pb-10">
    
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <?php
        $customerId = ($customer->created_at ? $customer->created_at->format('Ymd') : date('Ymd')) . str_pad($customer->id % 100, 2, '0', STR_PAD_LEFT);
    ?>

    
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-black text-slate-800 dark:text-slate-100"><?php echo e($customer->name); ?></h1>
            <div class="flex items-center gap-3 mt-1 text-sm text-slate-500 dark:text-slate-400">
                <span class="font-mono text-xs font-semibold text-indigo-600 dark:text-indigo-400"><?php echo e($customerId); ?></span>
                <span class="px-2.5 py-0.5 font-semibold rounded-md text-xs
                    <?php if($customer->status === 'active'): ?> bg-emerald-100 text-emerald-700 dark:bg-emerald-900/50 dark:text-emerald-400
                    <?php elseif($customer->status === 'inactive'): ?> bg-slate-100 text-slate-600 dark:bg-slate-800 dark:text-slate-400
                    <?php else: ?> bg-red-100 text-red-700 dark:bg-red-900/50 dark:text-red-400
                    <?php endif; ?>
                ">
                    <?php echo e(ucfirst($customer->status)); ?>

                </span>
                <span><?php echo e($customer->email ?? '-'); ?></span>
                <span class="font-mono text-xs"><?php echo e($customer->phone ?? '-'); ?></span>
            </div>
        </div>
        <div class="flex items-center gap-2">
            <button wire:click="openEditModal" class="inline-flex items-center gap-2 px-4 py-2 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-700 dark:text-slate-300 rounded-xl text-sm font-semibold shadow-sm hover:bg-slate-50 dark:bg-slate-900/50 dark:hover:bg-slate-700 transition-all cursor-pointer">
                <span class="material-symbols-outlined notranslate" translate="no" style="font-size:18px">edit</span>
                Edit Profil
            </button>
        </div>
    </div>

    
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
        
        <div class="relative overflow-x-auto rounded-xl border border-indigo-200 dark:border-indigo-800/60 shadow-md bg-gradient-to-br from-indigo-50 to-white dark:from-indigo-950/50 dark:to-slate-800 group hover:shadow-lg transition-all">
            <div class="absolute top-0 left-0 right-0 h-1 bg-gradient-to-r from-indigo-500 to-blue-400 rounded-t-xl"></div>
            <div class="absolute top-3 right-3 opacity-10 text-indigo-400 group-hover:opacity-20 group-hover:scale-110 transition-all">
                <span class="material-symbols-outlined notranslate" translate="no" style="font-size:56px">wifi</span>
            </div>
            <div class="p-4 pt-5">
                <h3 class="text-xs font-bold text-indigo-500 dark:text-indigo-400 uppercase tracking-widest mb-2">Paket</h3>
                <div class="text-xl font-black text-indigo-700 dark:text-indigo-300 mb-1 leading-tight">
                    <?php echo e($customer->customerServices->first()?->serviceProfile?->name ?? $customer->customerServices->first()?->service?->name ?? 'Belum ada paket'); ?>

                </div>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($customer->customerServices->first()?->serviceProfile): ?>
                    <div class="text-xs text-slate-500 dark:text-slate-400">
                         <?php echo e($customer->customerServices->first()->serviceProfile->download_speed); ?> Mbps &nbsp; <?php echo e($customer->customerServices->first()->serviceProfile->upload_speed); ?> Mbps
                    </div>
                <?php else: ?>
                    <div class="text-xs text-slate-500 dark:text-slate-400">-</div>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </div>
        </div>

        
        <div class="relative overflow-x-auto rounded-xl border border-rose-200 dark:border-rose-800/60 shadow-md bg-gradient-to-br from-rose-50 to-white dark:from-rose-950/50 dark:to-slate-800 group hover:shadow-lg transition-all">
            <div class="absolute top-0 left-0 right-0 h-1 bg-gradient-to-r from-rose-500 to-red-400 rounded-t-xl"></div>
            <div class="absolute top-3 right-3 opacity-10 text-rose-400 group-hover:opacity-20 group-hover:scale-110 transition-all">
                <span class="material-symbols-outlined notranslate" translate="no" style="font-size:56px">receipt_long</span>
            </div>
            <div class="p-4 pt-5">
                <h3 class="text-xs font-bold text-rose-500 dark:text-rose-400 uppercase tracking-widest mb-2">Tagihan Belum Lunas</h3>
                <div class="text-2xl font-black text-rose-700 dark:text-rose-300 mb-1">
                    Rp <?php echo e(number_format($unpaidBill, 0, ',', '.')); ?>

                </div>
                <div class="text-xs text-slate-500 dark:text-slate-400">
                    Jatuh tempo: <?php echo e($earliestDueDate ? $earliestDueDate->format('d/m/Y') : '-'); ?>

                </div>
            </div>
        </div>

        
        <div class="relative overflow-x-auto rounded-xl border border-emerald-200 dark:border-emerald-800/60 shadow-md bg-gradient-to-br from-emerald-50 to-white dark:from-emerald-950/50 dark:to-slate-800 group hover:shadow-lg transition-all">
            <div class="absolute top-0 left-0 right-0 h-1 bg-gradient-to-r from-emerald-500 to-teal-400 rounded-t-xl"></div>
            <div class="absolute top-3 right-3 opacity-10 text-emerald-400 group-hover:opacity-20 group-hover:scale-110 transition-all">
                <span class="material-symbols-outlined notranslate" translate="no" style="font-size:56px">check_circle</span>
            </div>
            <div class="p-4 pt-5">
                <h3 class="text-xs font-bold text-emerald-500 dark:text-emerald-400 uppercase tracking-widest mb-2">Status Layanan</h3>
                <div class="text-2xl font-black text-emerald-700 dark:text-emerald-300 mb-1">
                    <?php echo e(ucfirst($customer->customerServices->first()?->status ?? 'inactive')); ?>

                </div>
                <div class="text-xs text-slate-500 dark:text-slate-400">layanan utama aktif</div>
            </div>
        </div>

        
        <div class="relative overflow-x-auto rounded-xl border border-sky-200 dark:border-sky-800/60 shadow-md bg-gradient-to-br from-sky-50 to-white dark:from-sky-950/50 dark:to-slate-800 group hover:shadow-lg transition-all">
            <div class="absolute top-0 left-0 right-0 h-1 bg-gradient-to-r from-sky-500 to-cyan-400 rounded-t-xl"></div>
            <div class="absolute top-3 right-3 opacity-10 text-sky-400 group-hover:opacity-20 group-hover:scale-110 transition-all">
                <span class="material-symbols-outlined notranslate" translate="no" style="font-size:56px">dns</span>
            </div>
            <div class="p-4 pt-5">
                <h3 class="text-xs font-bold text-sky-500 dark:text-sky-400 uppercase tracking-widest mb-2">Jumlah Layanan</h3>
                <div class="text-2xl font-black text-sky-700 dark:text-sky-300 mb-1"><?php echo e($customer->customerServices->count()); ?></div>
                <div class="text-xs text-slate-500 dark:text-slate-400">total koneksi terdaftar</div>
            </div>
        </div>
    </div>

    
    <div class="border-b border-slate-200 dark:border-slate-700 overflow-x-auto">
        <nav class="flex gap-1 whitespace-nowrap" aria-label="Tabs">
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = [
                ['tab' => 'profile',       'label' => 'Profil & Lokasi',       'icon' => 'person'],
                ['tab' => 'finance',       'label' => 'Keuangan & Tagihan',    'icon' => 'payments'],
                ['tab' => 'device',        'label' => 'Perangkat & Jaringan',  'icon' => 'router'],
                ['tab' => 'support',       'label' => 'Support & Teknis',      'icon' => 'engineering'],
                ['tab' => 'history',       'label' => 'Riwayat & Log',         'icon' => 'history'],
            ]; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $t): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
            <button wire:click="setActiveTab('<?php echo e($t['tab']); ?>')"
                    class="inline-flex items-center gap-1.5 px-4 py-3 text-sm font-medium border-b-2 transition-colors whitespace-nowrap
                        <?php if($activeTab === $t['tab']): ?> border-indigo-500 text-indigo-600 dark:text-indigo-400
                        <?php else: ?> border-transparent text-slate-500 dark:text-slate-400 hover:text-slate-700 dark:text-slate-300 dark:hover:text-slate-300 hover:border-slate-300 dark:border-slate-600
                        <?php endif; ?>">
                <span class="material-symbols-outlined notranslate" translate="no" style="font-size:16px"><?php echo e($t['icon']); ?></span>
                <?php echo e($t['label']); ?>

            </button>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
        </nav>
    </div>

    
    <div class="mt-2">

                
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($activeTab === 'profile'): ?>
        <div class="space-y-6">
            
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
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

                 <?php $__env->slot('header', null, []); ?> 
                    <h3 class="text-base font-bold text-slate-900 dark:text-slate-100 flex items-center gap-2">
                        <span class="material-symbols-outlined notranslate text-indigo-500" translate="no" style="font-size:20px">person</span>
                        Informasi Pribadi
                    </h3>
                 <?php $__env->endSlot(); ?>
                <div class="space-y-4">
                    <div>
                        <label class="block text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-1">Customer ID</label>
                        <p class="text-slate-900 dark:text-slate-100 font-mono font-bold text-lg text-indigo-600 dark:text-indigo-400"><?php echo e($customerId); ?></p>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-1">Nama Lengkap</label>
                        <p class="text-slate-900 dark:text-slate-100 font-semibold"><?php echo e($customer->name); ?></p>
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-1">Email</label>
                            <p class="text-slate-900 dark:text-slate-100"><?php echo e($customer->email ?? '-'); ?></p>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-1">WhatsApp / Telepon</label>
                            <p class="text-slate-900 dark:text-slate-100"><?php echo e($customer->phone ?? '-'); ?></p>
                        </div>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-1">Alamat</label>
                        <p class="text-slate-900 dark:text-slate-100"><?php echo e($customer->address ?? '-'); ?></p>
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-1">Terdaftar Sejak</label>
                            <p class="text-slate-900 dark:text-slate-100"><?php echo e($customer->created_at?->format('d/m/Y') ?? '-'); ?></p>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-1">Reseller / Owner</label>
                            <p class="text-slate-900 dark:text-slate-100"><?php echo e($customer->reseller?->name ?? 'Default'); ?></p>
                        </div>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-1">Status Akun</label>
                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-sm font-semibold
                            <?php if($customer->status === 'active'): ?> bg-emerald-100 text-emerald-700 dark:bg-emerald-900/50 dark:text-emerald-400
                            <?php elseif($customer->status === 'inactive'): ?> bg-slate-100 text-slate-600 dark:bg-slate-800 dark:text-slate-400
                            <?php else: ?> bg-red-100 text-red-700 dark:bg-red-900/50 dark:text-red-400
                            <?php endif; ?>">
                            <span class="w-1.5 h-1.5 rounded-full <?php echo e($customer->status === 'active' ? 'bg-emerald-500 animate-pulse' : 'bg-slate-400'); ?>"></span>
                            <?php echo e(ucfirst($customer->status)); ?>

                        </span>
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

                 <?php $__env->slot('header', null, []); ?> 
                    <h3 class="text-base font-bold text-slate-900 dark:text-slate-100 flex items-center gap-2">
                        <span class="material-symbols-outlined notranslate text-indigo-500" translate="no" style="font-size:20px">wifi</span>
                        Informasi Layanan
                    </h3>
                 <?php $__env->endSlot(); ?>
                <div class="space-y-4">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__empty_0 = true; $__currentLoopData = $customer->customerServices; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $service): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_0 = false; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                    <div class="border border-slate-200 dark:border-slate-700 rounded-lg p-4 space-y-3">
                        <div class="flex items-center justify-between">
                            <div>
                                <label class="block text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-1">Paket Internet</label>
                                <p class="text-slate-900 dark:text-slate-100 font-bold"><?php echo e($service->serviceProfile?->name ?? $service->service?->name ?? 'N/A'); ?></p>
                            </div>
                            <div class="flex items-center gap-2">
                                <span class="px-2.5 py-1 rounded-lg text-xs font-semibold
                                    <?php if($service->status === 'active'): ?> bg-emerald-100 dark:bg-emerald-900/50 text-emerald-700
                                    <?php elseif($service->status === 'suspended'): ?> bg-red-100 dark:bg-red-900/50 text-red-600
                                    <?php else: ?> bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400
                                    <?php endif; ?>">
                                    <?php echo e(ucfirst($service->status)); ?>

                                </span>
                                <button wire:click="openEditServiceModal(<?php echo e($service->id); ?>)" class="p-1 text-slate-400 hover:text-indigo-600 transition-colors" title="Ganti Layanan / Paket">
                                    <span class="material-symbols-outlined notranslate" translate="no" style="font-size:20px">settings</span>
                                </button>
                            </div>
                        </div>

                        <div class="bg-slate-50 dark:bg-slate-900/50 p-3 rounded-lg flex items-center gap-8">
                            <div>
                                <span class="block text-xs text-slate-500 dark:text-slate-400 mb-0.5">Username</span>
                                <div class="flex items-center gap-2">
                                    <span class="font-mono font-semibold text-slate-900 dark:text-slate-100"><?php echo e($service->username ?? '-'); ?></span>
                                    <button onclick="navigator.clipboard.writeText('<?php echo e($service->username); ?>'); alert('Username tersalin!')" class="text-slate-400 hover:text-indigo-600 transition-colors" title="Copy Username">
                                        <span class="material-symbols-outlined notranslate" translate="no" style="font-size:16px">content_copy</span>
                                    </button>
                                </div>
                            </div>
                            <div>
                                <span class="block text-xs text-slate-500 dark:text-slate-400 mb-0.5">Password</span>
                                <div class="flex items-center gap-2">
                                    <span class="font-mono font-semibold text-slate-900 dark:text-slate-100"><?php echo e($service->password ?? '-'); ?></span>
                                    <button onclick="navigator.clipboard.writeText('<?php echo e($service->password); ?>'); alert('Password tersalin!')" class="text-slate-400 hover:text-indigo-600 transition-colors" title="Copy Password">
                                        <span class="material-symbols-outlined notranslate" translate="no" style="font-size:16px">content_copy</span>
                                    </button>
                                </div>
                            </div>
                        </div>

                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($service->serviceProfile): ?>
                        <div class="grid grid-cols-2 gap-3 text-sm">
                            <div>
                                <label class="block text-xs text-slate-500 dark:text-slate-400 mb-0.5">Download</label>
                                <span class="font-semibold text-slate-800 dark:text-slate-200"><?php echo e($service->serviceProfile->download_speed); ?> Mbps</span>
                            </div>
                            <div>
                                <label class="block text-xs text-slate-500 dark:text-slate-400 mb-0.5">Upload</label>
                                <span class="font-semibold text-slate-800 dark:text-slate-200"><?php echo e($service->serviceProfile->upload_speed); ?> Mbps</span>
                            </div>
                            <div>
                                <label class="block text-xs text-slate-500 dark:text-slate-400 mb-0.5">Harga Paket</label>
                                <span class="font-semibold text-slate-800 dark:text-slate-200">Rp <?php echo e(number_format($service->serviceProfile->price ?? 0, 0, ',', '.')); ?></span>
                            </div>
                            <div>
                                <label class="block text-xs text-slate-500 dark:text-slate-400 mb-0.5">Tgl Aktivasi</label>
                                <span class="font-semibold text-slate-800 dark:text-slate-200"><?php echo e($service->activated_at?->format('d/m/Y') ?? '-'); ?></span>
                            </div>
                        </div>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </div>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_0): ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                    <div class="text-center py-8 text-slate-400">
                        <span class="material-symbols-outlined notranslate" translate="no" style="font-size:40px">wifi_off</span>
                        <p class="mt-2 text-sm">Belum ada layanan terdaftar</p>
                    </div>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
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

            
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <div class="space-y-6">
                    <?php if (isset($component)) { $__componentOriginalf0ba6ef14ffa9e2e0936b821e3847e33 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalf0ba6ef14ffa9e2e0936b821e3847e33 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.base.card','data' => ['padding' => false]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('base.card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['padding' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(false)]); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

             <?php $__env->slot('header', null, []); ?> 
                <h3 class="text-base font-bold text-slate-900 dark:text-slate-100 flex items-center gap-2">
                    <span class="material-symbols-outlined notranslate text-indigo-500" translate="no" style="font-size:20px">description</span>
                    Kontrak Layanan
                </h3>
             <?php $__env->endSlot(); ?>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($customer->contracts->isNotEmpty()): ?>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="bg-slate-50 dark:bg-slate-900/50 border-b border-slate-200 dark:border-slate-700 text-xs uppercase tracking-wider text-slate-500 dark:text-slate-400">
                            <th class="px-6 py-3 text-left font-semibold">Nomor Kontrak</th>
                            <th class="px-6 py-3 text-left font-semibold">Tanggal Mulai</th>
                            <th class="px-6 py-3 text-left font-semibold">Tanggal Berakhir</th>
                            <th class="px-6 py-3 text-left font-semibold">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-700">
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $customer->contracts; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $contract): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                        <tr class="hover:bg-slate-50 dark:bg-slate-900/50 dark:hover:bg-slate-800/50">
                            <td class="px-6 py-4 font-mono font-semibold text-slate-900 dark:text-slate-100"><?php echo e($contract->contract_number); ?></td>
                            <td class="px-6 py-4 text-slate-600 dark:text-slate-400"><?php echo e($contract->start_date?->format('d/m/Y') ?? '-'); ?></td>
                            <td class="px-6 py-4 text-slate-600 dark:text-slate-400"><?php echo e($contract->end_date?->format('d/m/Y') ?? '-'); ?></td>
                            <td class="px-6 py-4">
                                <span class="px-2 py-1 text-xs font-semibold rounded-lg
                                    <?php if($contract->status === 'active'): ?> bg-emerald-100 dark:bg-emerald-900/50 text-emerald-700
                                    <?php elseif($contract->status === 'expired'): ?> bg-red-100 dark:bg-red-900/50 text-red-600
                                    <?php else: ?> bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400
                                    <?php endif; ?>">
                                    <?php echo e(ucfirst($contract->status)); ?>

                                </span>
                            </td>
                        </tr>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                    </tbody>
                </table>
            </div>
            <?php else: ?>
            <div class="p-10 text-center text-slate-400">
                <span class="material-symbols-outlined notranslate" translate="no" style="font-size:40px">description</span>
                <p class="mt-2 text-sm">Belum ada kontrak</p>
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
                </div>
                <div class="space-y-6">
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

             <?php $__env->slot('header', null, []); ?> 
                <h3 class="text-base font-bold text-slate-900 dark:text-slate-100 flex items-center gap-2">
                    <span class="material-symbols-outlined notranslate text-indigo-500" translate="no" style="font-size:20px">map</span>
                    Lokasi Pelanggan
                </h3>
             <?php $__env->endSlot(); ?>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($customer->latitude && $customer->longitude): ?>
            <div class="space-y-3">
                <div class="flex gap-4 text-sm">
                    <div class="flex items-center gap-2 text-slate-600 dark:text-slate-400">
                        <span class="material-symbols-outlined notranslate text-indigo-500" translate="no" style="font-size:18px">location_on</span>
                        Lat: <span class="font-mono font-semibold text-slate-900 dark:text-slate-100"><?php echo e($customer->latitude); ?></span>
                        &nbsp;|&nbsp; Lng: <span class="font-mono font-semibold text-slate-900 dark:text-slate-100"><?php echo e($customer->longitude); ?></span>
                    </div>
                    <a href="https://maps.google.com/?q=<?php echo e($customer->latitude); ?>,<?php echo e($customer->longitude); ?>" target="_blank" rel="noopener noreferrer"
                       class="inline-flex items-center gap-1 text-indigo-600 hover:text-indigo-800 text-sm font-medium">
                        <span class="material-symbols-outlined notranslate" translate="no" style="font-size:16px">open_in_new</span>
                        Buka di Google Maps
                    </a>
                </div>
                <div class="h-72 bg-slate-100 dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-slate-700 relative z-0" wire:ignore>
                      <div id="customer-map" class="w-full h-full rounded-xl"></div>
                  </div>
            </div>
            <?php else: ?>
            <div class="h-48 bg-slate-100 dark:bg-slate-900 rounded-xl flex items-center justify-center">
                <div class="text-center text-slate-400">
                    <span class="material-symbols-outlined notranslate" translate="no" style="font-size:48px">location_off</span>
                    <p class="mt-2 text-sm font-semibold text-slate-500 dark:text-slate-400">Koordinat lokasi belum diisi</p>
                    <p class="text-xs mt-1">Silahkan edit profil dan isi data latitude/longitude</p>
                </div>
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
                </div>
            </div>
        </div>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

        
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($activeTab === 'finance'): ?>
        <div class="space-y-6">
            
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <?php if (isset($component)) { $__componentOriginalf0ba6ef14ffa9e2e0936b821e3847e33 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalf0ba6ef14ffa9e2e0936b821e3847e33 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.base.card','data' => ['class' => 'lg:col-span-2']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('base.card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['class' => 'lg:col-span-2']); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

                 <?php $__env->slot('header', null, []); ?> 
                    <h3 class="text-base font-bold text-slate-900 dark:text-slate-100 flex items-center gap-2">
                        <span class="material-symbols-outlined notranslate text-indigo-500" translate="no" style="font-size:20px">payments</span>
                        Ringkasan Tagihan
                    </h3>
                 <?php $__env->endSlot(); ?>
                <div class="space-y-0 divide-y divide-slate-200 dark:divide-slate-700">
                    <div class="flex justify-between items-center py-4">
                        <span class="text-slate-600 dark:text-slate-400">Tagihan Bulanan (estimasi)</span>
                        <span class="text-lg font-bold text-slate-900 dark:text-slate-100">Rp <?php echo e(number_format($monthlyBill, 0, ',', '.')); ?></span>
                    </div>
                    <div class="flex justify-between items-center py-4">
                        <span class="text-slate-600 dark:text-slate-400">Total Tagihan Belum Lunas</span>
                        <span class="text-lg font-bold <?php echo e($unpaidBill > 0 ? 'text-red-600' : 'text-emerald-600'); ?>">Rp <?php echo e(number_format($unpaidBill, 0, ',', '.')); ?></span>
                    </div>
                    <div class="flex justify-between items-center py-4">
                        <span class="text-slate-600 dark:text-slate-400">Jatuh Tempo Terdekat</span>
                        <span class="text-lg font-bold text-slate-900 dark:text-slate-100"><?php echo e($earliestDueDate ? $earliestDueDate->format('d/m/Y') : '-'); ?></span>
                    </div>
                    <div class="flex justify-between items-center py-4">
                        <span class="text-slate-600 dark:text-slate-400">Metode Pembayaran Terakhir</span>
                        <span class="text-slate-900 dark:text-slate-100"><?php echo e($payments->first()?->method ?? '-'); ?></span>
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

                 <?php $__env->slot('header', null, []); ?> 
                    <h3 class="text-base font-bold text-slate-900 dark:text-slate-100 flex items-center gap-2">
                        <span class="material-symbols-outlined notranslate text-rose-500" translate="no" style="font-size:20px">calendar_today</span>
                        Jatuh Tempo
                    </h3>
                 <?php $__env->endSlot(); ?>
                <div class="text-center py-4">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($earliestDueDate): ?>
                        <?php 
                            // Hitung selisih hari dengan startOfDay agar dapat angka bulat (integer)
                            $daysLeft = (int) now()->startOfDay()->diffInDays($earliestDueDate->copy()->startOfDay(), false); 
                        ?>
                        <p class="text-4xl font-black <?php echo e($daysLeft < 3 ? 'text-red-600' : ($daysLeft < 7 ? 'text-orange-600' : 'text-slate-800 dark:text-slate-100')); ?>">
                            <?php echo e(max(0, $daysLeft)); ?>

                        </p>
                        <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">Hari Lagi</p>
                        <p class="text-xs text-slate-400 mt-2"><?php echo e($earliestDueDate->format('d/m/Y')); ?></p>
                    <?php else: ?>
                        <span class="material-symbols-outlined notranslate text-slate-300" translate="no" style="font-size:40px">check_circle</span>
                        <p class="text-sm text-slate-500 dark:text-slate-400 mt-2">Tidak ada tagihan jatuh tempo</p>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
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

            
            <div class="grid grid-cols-1 xl:grid-cols-2 gap-6">
                <div>
                    <?php if (isset($component)) { $__componentOriginalf0ba6ef14ffa9e2e0936b821e3847e33 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalf0ba6ef14ffa9e2e0936b821e3847e33 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.base.card','data' => ['padding' => false]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('base.card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['padding' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(false)]); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

             <?php $__env->slot('header', null, []); ?> 
                <h3 class="text-base font-bold text-slate-900 dark:text-slate-100 flex items-center gap-2">
                    <span class="material-symbols-outlined notranslate text-indigo-500" translate="no" style="font-size:20px">receipt</span>
                    Daftar Invoice
                </h3>
             <?php $__env->endSlot(); ?>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($invoices->isNotEmpty()): ?>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="bg-slate-50 dark:bg-slate-900/50 border-b border-slate-200 dark:border-slate-700 text-xs uppercase tracking-wider text-slate-500 dark:text-slate-400">
                            <th class="px-6 py-3 text-left font-semibold">Nomor Invoice</th>
                            <th class="px-6 py-3 text-left font-semibold">Tanggal</th>
                            <th class="px-6 py-3 text-left font-semibold">Jatuh Tempo</th>
                            <th class="px-6 py-3 text-left font-semibold">Jumlah</th>
                            <th class="px-6 py-3 text-left font-semibold">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-700">
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $invoices; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $invoice): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                        <tr class="hover:bg-slate-50 dark:bg-slate-900/50 dark:hover:bg-slate-800/50">
                            <td class="px-6 py-4 font-mono font-semibold text-slate-900 dark:text-slate-100"><?php echo e($invoice->invoice_number); ?></td>
                            <td class="px-6 py-4 text-slate-600 dark:text-slate-400"><?php echo e($invoice->issue_date?->format('d/m/Y') ?? '-'); ?></td>
                            <td class="px-6 py-4 text-slate-600 dark:text-slate-400"><?php echo e($invoice->due_date?->format('d/m/Y') ?? '-'); ?></td>
                            <td class="px-6 py-4 font-semibold text-slate-900 dark:text-slate-100">Rp <?php echo e(number_format($invoice->total_amount, 0, ',', '.')); ?></td>
                            <td class="px-6 py-4">
                                <span class="px-2 py-1 text-xs font-semibold rounded-lg
                                    <?php if($invoice->status === 'paid'): ?> bg-emerald-100 dark:bg-emerald-900/50 text-emerald-700
                                    <?php elseif($invoice->status === 'overdue'): ?> bg-red-100 dark:bg-red-900/50 text-red-600
                                    <?php else: ?> bg-orange-100 text-orange-600
                                    <?php endif; ?>">
                                    <?php echo e($invoice->status === 'paid' ? 'Lunas' : ucfirst($invoice->status)); ?>

                                </span>
                            </td>
                        </tr>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                    </tbody>
                </table>
            </div>
            <?php else: ?>
            <div class="p-10 text-center text-slate-400">
                <span class="material-symbols-outlined notranslate" translate="no" style="font-size:40px">receipt</span>
                <p class="mt-2 text-sm">Belum ada invoice</p>
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
                </div>
                <div>
                    <?php if (isset($component)) { $__componentOriginalf0ba6ef14ffa9e2e0936b821e3847e33 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalf0ba6ef14ffa9e2e0936b821e3847e33 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.base.card','data' => ['padding' => false]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('base.card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['padding' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(false)]); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

             <?php $__env->slot('header', null, []); ?> 
                <h3 class="text-base font-bold text-slate-900 dark:text-slate-100 flex items-center gap-2">
                    <span class="material-symbols-outlined notranslate text-indigo-500" translate="no" style="font-size:20px">credit_card</span>
                    Riwayat Pembayaran
                </h3>
             <?php $__env->endSlot(); ?>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($payments->isNotEmpty()): ?>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="bg-slate-50 dark:bg-slate-900/50 border-b border-slate-200 dark:border-slate-700 text-xs uppercase tracking-wider text-slate-500 dark:text-slate-400">
                            <th class="px-6 py-3 text-left font-semibold">ID Pembayaran</th>
                            <th class="px-6 py-3 text-left font-semibold">Tanggal</th>
                            <th class="px-6 py-3 text-left font-semibold">Jumlah</th>
                            <th class="px-6 py-3 text-left font-semibold">Metode</th>
                            <th class="px-6 py-3 text-left font-semibold">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-700">
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $payments; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $payment): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                        <tr class="hover:bg-slate-50 dark:bg-slate-900/50 dark:hover:bg-slate-800/50">
                            <td class="px-6 py-4 font-mono text-xs text-slate-900 dark:text-slate-100"><?php echo e($payment->uuid ?? $payment->id); ?></td>
                            <td class="px-6 py-4 text-slate-600 dark:text-slate-400"><?php echo e($payment->paid_at?->format('d/m/Y') ?? $payment->created_at->format('d/m/Y')); ?></td>
                            <td class="px-6 py-4 font-semibold text-slate-900 dark:text-slate-100">Rp <?php echo e(number_format($payment->amount, 0, ',', '.')); ?></td>
                            <td class="px-6 py-4 text-slate-600 dark:text-slate-400"><?php echo e($payment->method ?? '-'); ?></td>
                            <td class="px-6 py-4">
                                <span class="px-2 py-1 text-xs font-semibold rounded-lg
                                    <?php if($payment->status === 'success'): ?> bg-emerald-100 dark:bg-emerald-900/50 text-emerald-700
                                    <?php elseif($payment->status === 'failed'): ?> bg-red-100 dark:bg-red-900/50 text-red-600
                                    <?php else: ?> bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400
                                    <?php endif; ?>">
                                    <?php echo e(ucfirst($payment->status)); ?>

                                </span>
                            </td>
                        </tr>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                    </tbody>
                </table>
            </div>
            <?php else: ?>
            <div class="p-10 text-center text-slate-400">
                <span class="material-symbols-outlined notranslate" translate="no" style="font-size:40px">credit_card_off</span>
                <p class="mt-2 text-sm">Belum ada riwayat pembayaran</p>
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
                </div>
            </div>
        </div>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

        
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($activeTab === 'device'): ?>
            <div class="space-y-6">
            
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

                 <?php $__env->slot('header', null, []); ?> 
                    <div class="flex justify-between items-center w-full">
                        <h3 class="text-base font-bold text-slate-900 dark:text-slate-100 flex items-center gap-2">
                            <span class="material-symbols-outlined notranslate text-indigo-500" translate="no">router</span>
                            Router Pelanggan (ACS / TR-069)
                        </h3>
                    </div>
                 <?php $__env->endSlot(); ?>
                
                <div class="space-y-4">
                    <?php $hasRouter = false; ?>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $customer->customerServices; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $service): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($service->acsDevice): ?>
                            <?php $hasRouter = true; ?>
                            <div class="border border-slate-200 dark:border-slate-700 rounded-xl p-5 bg-slate-50 dark:bg-slate-900/50 relative overflow-hidden">
                                
                                <div class="absolute right-0 top-0 w-48 h-48 bg-indigo-50 dark:bg-indigo-900/30 rounded-full blur-3xl -mr-10 -mt-10 pointer-events-none"></div>
                                
                                <div class="relative z-10 flex flex-col md:flex-row md:items-center justify-between gap-6">
                                    <div>
                                        <div class="flex items-center gap-3 mb-2">
                                            <span class="font-bold text-lg text-slate-900 dark:text-slate-100"><?php echo e($service->acsDevice->serial_number); ?></span>
                                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md text-[10px] font-bold uppercase tracking-wider <?php echo e($service->acsDevice->status === 'online' ? 'bg-emerald-100 dark:bg-emerald-900/50 text-emerald-700' : 'bg-rose-100 dark:bg-rose-900/50 text-rose-700'); ?>">
                                                <span class="w-2 h-2 rounded-full <?php echo e($service->acsDevice->status === 'online' ? 'bg-emerald-500' : 'bg-rose-500'); ?>"></span>
                                                <?php echo e($service->acsDevice->status); ?>

                                            </span>
                                        </div>
                                        <div class="text-sm font-medium text-slate-600 dark:text-slate-400 mb-1">
                                            <?php echo e($service->acsDevice->manufacturer ?? 'Unknown Vendor'); ?> - <?php echo e($service->acsDevice->model ?? 'Unknown Model'); ?>

                                        </div>
                                        <div class="text-xs text-slate-500 dark:text-slate-400 font-mono">
                                            IP: <?php echo e($service->acsDevice->ip_address ?? '-'); ?> | MAC: <?php echo e($service->acsDevice->mac_address ?? '-'); ?>

                                        </div>
                                        <div class="text-xs text-slate-400 mt-2 flex items-center gap-1">
                                            <span class="material-symbols-outlined notranslate" style="font-size:14px" translate="no">update</span>
                                            Last Inform: <?php echo e($service->acsDevice->last_inform ? $service->acsDevice->last_inform->diffForHumans() : '-'); ?>

                                        </div>
                                        
                                        <div class="mt-4 pt-4 border-t border-slate-200 dark:border-slate-700">
                                            <div class="text-[10px] font-bold uppercase tracking-widest text-slate-400 mb-2">Topologi Jaringan</div>
                                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($service->onu): ?>
                                            <div class="flex flex-wrap items-center gap-2 text-xs font-medium text-slate-700 dark:text-slate-300">
                                                <div class="flex items-center gap-1.5 bg-slate-100 dark:bg-slate-800 px-2.5 py-1.5 rounded-lg border border-slate-200 dark:border-slate-700">
                                                    <span class="material-symbols-outlined text-slate-400" style="font-size: 16px;">dns</span>
                                                    <span><?php echo e($service->onu->olt->name ?? 'N/A'); ?></span>
                                                </div>
                                                <span class="material-symbols-outlined text-slate-300" style="font-size: 14px;">arrow_forward</span>
                                                <div class="flex items-center gap-1.5 bg-slate-100 dark:bg-slate-800 px-2.5 py-1.5 rounded-lg border border-slate-200 dark:border-slate-700">
                                                    <span class="material-symbols-outlined text-slate-400" style="font-size: 16px;">lan</span>
                                                    <span>PON: <?php echo e($service->onu->formatted_pon_port ?? $service->onu->ponPort->name ?? '-'); ?></span>
                                                </div>
                                                <span class="material-symbols-outlined text-slate-300" style="font-size: 14px;">arrow_forward</span>
                                                <div class="flex items-center gap-1.5 bg-slate-100 dark:bg-slate-800 px-2.5 py-1.5 rounded-lg border border-slate-200 dark:border-slate-700">
                                                    <span class="material-symbols-outlined text-slate-400" style="font-size: 16px;">device_hub</span>
                                                    <span>ODP: <?php echo e($service->onu->odp->name ?? '-'); ?></span>
                                                </div>
                                            </div>
                                        <?php else: ?>
                                            <div class="flex items-center gap-2 text-xs text-slate-400 italic">
                                                <span class="material-symbols-outlined" style="font-size:16px;">link_off</span>
                                                Topologi belum dipetakan ke ODP / OLT.
                                            </div>
                                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                        </div>
                                    </div>
                                    
                                    <div class="flex flex-wrap items-center gap-3 bg-white dark:bg-slate-800 p-3 rounded-lg border border-slate-100 dark:border-slate-700 shadow-sm">
                                        <button wire:click="openWifiModal(<?php echo e($service->acsDevice->id); ?>)" class="px-4 py-2 bg-indigo-50 dark:bg-indigo-900/30 text-indigo-700 border border-indigo-200 hover:bg-indigo-100 dark:bg-indigo-900/50 rounded-lg text-sm font-semibold flex items-center gap-2 transition-colors">
                                            <span class="material-symbols-outlined notranslate" translate="no" style="font-size:18px">wifi</span> Ubah WiFi
                                        </button>
                                        <button wire:click="rebootModem(<?php echo e($service->acsDevice->id); ?>)" wire:confirm="Yakin ingin merestart modem ini dari jarak jauh?" class="px-4 py-2 bg-amber-50 dark:bg-amber-900/30 text-amber-700 border border-amber-200 hover:bg-amber-100 dark:bg-amber-900/50 rounded-lg text-sm font-semibold flex items-center gap-2 transition-colors">
                                            <span class="material-symbols-outlined notranslate" translate="no" style="font-size:18px">restart_alt</span> Reboot
                                        </button>
                                        <a href="<?php echo e(route('acs.devices.show', $service->acsDevice->id)); ?>" class="px-4 py-2 bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-600 text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:bg-slate-900/50 rounded-lg text-sm font-semibold flex items-center gap-2 transition-colors" target="_blank">
                                            <span class="material-symbols-outlined notranslate" translate="no" style="font-size:18px">open_in_new</span> Detail Penuh
                                        </a>
                                    </div>
                                </div>
                            </div>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                    
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(!$hasRouter): ?>
                        <div class="text-center py-12 text-slate-500 dark:text-slate-400 bg-slate-50 dark:bg-slate-900/50 rounded-xl border border-dashed border-slate-300 dark:border-slate-600">
                            <span class="material-symbols-outlined notranslate text-5xl text-slate-300 mb-3" translate="no">router</span>
                            <p class="text-base font-medium text-slate-600 dark:text-slate-400">Belum ada perangkat modem (ACS)</p>
                            <p class="text-sm mt-1">Perangkat akan otomatis terdaftar saat terhubung ke internet dan melakukan Inform.</p>
                        </div>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
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

                 <?php $__env->slot('header', null, []); ?> 
                    <h3 class="text-sm font-bold text-slate-900 dark:text-slate-100 flex items-center gap-2">
                        <span class="material-symbols-outlined notranslate text-emerald-500" translate="no" style="font-size:20px">share</span>
                        Topologi Jaringan FTTH
                    </h3>
                 <?php $__env->endSlot(); ?>
                
                <div class="space-y-4">
                    <?php $hasAnyTopology = false; ?>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $customer->customerServices; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $service): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($service->onu): ?>
                            <?php $hasAnyTopology = true; ?>
                            <div class="p-4 bg-slate-50 dark:bg-slate-900/50 border border-slate-200 dark:border-slate-700 rounded-xl">
                                <div class="text-xs font-bold text-slate-500 dark:text-slate-400 mb-3 uppercase tracking-wider">Koneksi Layanan: <?php echo e($service->serviceProfile->name ?? 'Layanan Utama'); ?></div>
                                <div class="flex flex-wrap items-center gap-3 text-sm font-medium text-slate-700 dark:text-slate-300">
                                    <div class="flex items-center gap-2 bg-white dark:bg-slate-800 px-3 py-2 rounded-lg border border-slate-200 dark:border-slate-700 shadow-sm">
                                        <span class="material-symbols-outlined text-slate-400" style="font-size: 20px;">dns</span>
                                        <div class="flex flex-col">
                                            <span class="text-[10px] text-slate-400 leading-none">OLT SERVER</span>
                                            <span><?php echo e($service->onu->olt->name ?? 'N/A'); ?></span>
                                        </div>
                                    </div>
                                    <span class="material-symbols-outlined text-slate-300" style="font-size: 20px;">arrow_forward</span>
                                    <div class="flex items-center gap-2 bg-white dark:bg-slate-800 px-3 py-2 rounded-lg border border-slate-200 dark:border-slate-700 shadow-sm">
                                        <span class="material-symbols-outlined text-slate-400" style="font-size: 20px;">lan</span>
                                        <div class="flex flex-col">
                                            <span class="text-[10px] text-slate-400 leading-none">PON PORT</span>
                                            <span><?php echo e($service->onu->formatted_pon_port ?? $service->onu->ponPort->name ?? '-'); ?></span>
                                        </div>
                                    </div>
                                    <span class="material-symbols-outlined text-slate-300" style="font-size: 20px;">arrow_forward</span>
                                    <div class="flex items-center gap-2 bg-white dark:bg-slate-800 px-3 py-2 rounded-lg border border-slate-200 dark:border-slate-700 shadow-sm">
                                        <span class="material-symbols-outlined text-slate-400" style="font-size: 20px;">device_hub</span>
                                        <div class="flex flex-col">
                                            <span class="text-[10px] text-slate-400 leading-none">ODP BOX</span>
                                            <span><?php echo e($service->onu->odp->name ?? '-'); ?></span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                    
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(!$hasAnyTopology): ?>
                        <div class="text-center py-8 text-slate-500 dark:text-slate-400 bg-slate-50 dark:bg-slate-900/50 rounded-xl border border-dashed border-slate-300 dark:border-slate-600">
                            <span class="material-symbols-outlined notranslate text-4xl text-slate-300 mb-2" translate="no">link_off</span>
                            <p class="text-sm font-medium text-slate-600 dark:text-slate-400">Pelanggan belum dipetakan ke ODP / OLT</p>
                            <p class="text-xs mt-1 text-slate-400">Teknisi dapat melakukan pemetaan melalui modul Jaringan.</p>
                        </div>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
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

            
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(!empty($devices)): ?>
            <?php if (isset($component)) { $__componentOriginalf0ba6ef14ffa9e2e0936b821e3847e33 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalf0ba6ef14ffa9e2e0936b821e3847e33 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.base.card','data' => ['padding' => false]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('base.card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['padding' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(false)]); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

                 <?php $__env->slot('header', null, []); ?> 
                    <h3 class="text-sm font-bold text-slate-900 dark:text-slate-100 flex items-center gap-2">
                        <span class="material-symbols-outlined notranslate text-slate-400" translate="no" style="font-size:20px">inventory_2</span>
                        Perangkat Inventaris (Legacy ONU)
                    </h3>
                 <?php $__env->endSlot(); ?>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="bg-slate-50 dark:bg-slate-900/50 border-b border-slate-200 dark:border-slate-700 text-xs uppercase tracking-wider text-slate-500 dark:text-slate-400">
                                <th class="px-6 py-3 text-left font-semibold">Perangkat</th>
                                <th class="px-6 py-3 text-left font-semibold">Topologi FTTH</th>
                                <th class="px-6 py-3 text-left font-semibold">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-slate-700">
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $devices; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $device): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                            <tr class="hover:bg-slate-50 dark:bg-slate-900/50">
                                <td class="px-6 py-4">
                                    <div class="font-bold text-slate-900 dark:text-slate-100"><?php echo e($device['brand']); ?> <?php echo e($device['model']); ?></div>
                                    <div class="font-mono text-xs text-slate-500 dark:text-slate-400 mt-1">SN: <?php echo e($device['serial']); ?></div>
                                </td>
                                <td class="px-6 py-4">
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(isset($device['topology'])): ?>
                                        <div class="flex items-center gap-2 text-xs font-medium text-slate-600 dark:text-slate-400">
                                            <div class="flex flex-col items-center">
                                                <span class="material-symbols-outlined text-slate-400" style="font-size: 16px;">dns</span>
                                                <span><?php echo e($device['topology']['olt']); ?></span>
                                            </div>
                                            <span class="material-symbols-outlined text-slate-300" style="font-size: 14px;">arrow_forward</span>
                                            <div class="flex flex-col items-center">
                                                <span class="material-symbols-outlined text-slate-400" style="font-size: 16px;">lan</span>
                                                <span>PON: <?php echo e($device['topology']['pon_port']); ?></span>
                                            </div>
                                            <span class="material-symbols-outlined text-slate-300" style="font-size: 14px;">arrow_forward</span>
                                            <div class="flex flex-col items-center">
                                                <span class="material-symbols-outlined text-slate-400" style="font-size: 16px;">device_hub</span>
                                                <span>ODP: <?php echo e($device['topology']['odp']); ?></span>
                                            </div>
                                        </div>
                                    <?php else: ?>
                                        <span class="text-slate-400 italic">Topologi tidak tersedia</span>
                                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                </td>
                                <td class="px-6 py-4">
                                    <span class="px-2 py-1 text-xs font-semibold rounded-lg <?php echo e($device['status'] === 'active' ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400'); ?>">
                                        <?php echo e(ucfirst($device['status'])); ?>

                                    </span>
                                </td>
                            </tr>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                        </tbody>
                    </table>
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
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </div>
            <div class="mt-8">
                <h3 class="text-sm font-bold text-slate-900 dark:text-slate-100 flex items-center gap-2 mb-4">
                    <span class="material-symbols-outlined notranslate text-indigo-500" translate="no" style="font-size:20px">monitor_heart</span>
                    Status Koneksi
                </h3>

        
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(!empty($monitoring)): ?>
        <div class="space-y-4">
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $monitoring; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $mon): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
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

                 <?php $__env->slot('header', null, []); ?> 
                    <h3 class="text-base font-bold text-slate-900 dark:text-slate-100 flex items-center gap-2">
                        <span class="material-symbols-outlined notranslate text-indigo-500" translate="no" style="font-size:20px">monitor_heart</span>
                        Status Koneksi: <?php echo e($mon['service']); ?>

                    </h3>
                 <?php $__env->endSlot(); ?>
                <div class="grid grid-cols-2 md:grid-cols-3 gap-4">
                    <div class="p-3 bg-slate-50 dark:bg-slate-900/50 rounded-lg">
                        <label class="block text-xs text-slate-500 dark:text-slate-400 mb-1">Status Layanan</label>
                        <span class="inline-flex items-center gap-1.5 text-sm font-bold <?php echo e($mon['status'] === 'active' ? 'text-emerald-600' : 'text-red-600'); ?>">
                            <span class="w-2 h-2 rounded-full <?php echo e($mon['status'] === 'active' ? 'bg-emerald-500 animate-pulse' : 'bg-red-500'); ?>"></span>
                            <?php echo e(ucfirst($mon['status'])); ?>

                        </span>
                    </div>
                    <div class="p-3 bg-slate-50 dark:bg-slate-900/50 rounded-lg">
                        <label class="block text-xs text-slate-500 dark:text-slate-400 mb-1">IP Address</label>
                        <span class="font-mono font-semibold text-slate-900 dark:text-slate-100"><?php echo e($mon['ip'] ?? 'Dynamic'); ?></span>
                    </div>
                    <div class="p-3 bg-slate-50 dark:bg-slate-900/50 rounded-lg">
                        <label class="block text-xs text-slate-500 dark:text-slate-400 mb-1">MAC Address</label>
                        <span class="font-mono text-sm font-semibold text-slate-900 dark:text-slate-100"><?php echo e($mon['mac'] ?? 'N/A'); ?></span>
                    </div>
                    <div class="p-3 bg-slate-50 dark:bg-slate-900/50 rounded-lg">
                        <label class="block text-xs text-slate-500 dark:text-slate-400 mb-1">ONU Status</label>
                        <span class="font-semibold text-slate-900 dark:text-slate-100"><?php echo e($mon['onu_status'] ?? 'N/A'); ?></span>
                    </div>
                    <div class="p-3 bg-slate-50 dark:bg-slate-900/50 rounded-lg">
                        <label class="block text-xs text-slate-500 dark:text-slate-400 mb-1">RX Power (ONU)</label>
                        <span class="font-mono font-semibold text-slate-900 dark:text-slate-100"><?php echo e($mon['onu_rx'] !== 'N/A' ? $mon['onu_rx'] . ' dBm' : 'N/A'); ?></span>
                    </div>
                    <div class="p-3 bg-slate-50 dark:bg-slate-900/50 rounded-lg">
                        <label class="block text-xs text-slate-500 dark:text-slate-400 mb-1">TX Power (ONU)</label>
                        <span class="font-mono font-semibold text-slate-900 dark:text-slate-100"><?php echo e($mon['onu_tx'] !== 'N/A' ? $mon['onu_tx'] . ' dBm' : 'N/A'); ?></span>
                    </div>
                    <div class="p-3 bg-slate-50 dark:bg-slate-900/50 rounded-lg col-span-2 md:col-span-1">
                        <label class="block text-xs text-slate-500 dark:text-slate-400 mb-1">Terakhir Terlihat</label>
                        <span class="text-sm font-semibold text-slate-900 dark:text-slate-100">
                            <?php echo e($mon['last_seen'] !== 'Never' ? (is_string($mon['last_seen']) ? $mon['last_seen'] : $mon['last_seen']->diffForHumans()) : 'Belum pernah'); ?>

                        </span>
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
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
        </div>
        <?php else: ?>
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

            <div class="p-10 text-center text-slate-400">
                <span class="material-symbols-outlined notranslate" translate="no" style="font-size:48px">signal_disconnected</span>
                <p class="mt-2 text-sm font-semibold text-slate-500 dark:text-slate-400">Tidak ada data monitoring</p>
                <p class="text-xs mt-1">Pelanggan ini belum memiliki perangkat yang terhubung</p>
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
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        
            </div>
        </div>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

        
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($activeTab === 'support'): ?>
        <div class="grid grid-cols-1 xl:grid-cols-2 gap-6">
            <div class="space-y-6">
                <?php if (isset($component)) { $__componentOriginalf0ba6ef14ffa9e2e0936b821e3847e33 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalf0ba6ef14ffa9e2e0936b821e3847e33 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.base.card','data' => ['padding' => false]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('base.card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['padding' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(false)]); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

             <?php $__env->slot('header', null, []); ?> 
                <h3 class="text-base font-bold text-slate-900 dark:text-slate-100 flex items-center gap-2">
                    <span class="material-symbols-outlined notranslate text-indigo-500" translate="no" style="font-size:20px">confirmation_number</span>
                    Tiket Support
                </h3>
             <?php $__env->endSlot(); ?>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(!empty($tickets)): ?>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="bg-slate-50 dark:bg-slate-900/50 border-b border-slate-200 dark:border-slate-700 text-xs uppercase tracking-wider text-slate-500 dark:text-slate-400">
                            <th class="px-6 py-3 text-left font-semibold">ID Tiket</th>
                            <th class="px-6 py-3 text-left font-semibold">Judul</th>
                            <th class="px-6 py-3 text-left font-semibold">Prioritas</th>
                            <th class="px-6 py-3 text-left font-semibold">Status</th>
                            <th class="px-6 py-3 text-left font-semibold">Dibuat</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-700">
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $tickets; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $ticket): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                        <tr class="hover:bg-slate-50 dark:bg-slate-900/50 dark:hover:bg-slate-800/50">
                            <td class="px-6 py-4 font-mono font-semibold text-slate-900 dark:text-slate-100"><?php echo e($ticket['id']); ?></td>
                            <td class="px-6 py-4 text-slate-600 dark:text-slate-400"><?php echo e($ticket['title']); ?></td>
                            <td class="px-6 py-4">
                                <span class="px-2 py-1 text-xs font-semibold rounded-lg
                                    <?php if($ticket['priority'] === 'high'): ?> bg-red-100 dark:bg-red-900/50 text-red-600
                                    <?php elseif($ticket['priority'] === 'medium'): ?> bg-orange-100 text-orange-600
                                    <?php else: ?> bg-blue-100 dark:bg-blue-900/50 text-blue-600
                                    <?php endif; ?>">
                                    <?php echo e($ticket['priority'] === 'high' ? 'Tinggi' : ($ticket['priority'] === 'medium' ? 'Sedang' : 'Rendah')); ?>

                                </span>
                            </td>
                            <td class="px-6 py-4">
                                <span class="px-2 py-1 text-xs font-semibold rounded-lg
                                    <?php if($ticket['status'] === 'open'): ?> bg-emerald-100 dark:bg-emerald-900/50 text-emerald-700
                                    <?php elseif($ticket['status'] === 'in_progress'): ?> bg-blue-100 dark:bg-blue-900/50 text-blue-600
                                    <?php else: ?> bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400
                                    <?php endif; ?>">
                                    <?php echo e($ticket['status'] === 'open' ? 'Buka' : ($ticket['status'] === 'in_progress' ? 'Dalam Proses' : 'Selesai')); ?>

                                </span>
                            </td>
                            <td class="px-6 py-4 text-slate-600 dark:text-slate-400"><?php echo e($ticket['created_at']->format('d/m/Y')); ?></td>
                        </tr>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                    </tbody>
                </table>
            </div>
            <?php else: ?>
            <div class="p-10 text-center text-slate-400">
                <span class="material-symbols-outlined notranslate" translate="no" style="font-size:40px">confirmation_number</span>
                <p class="mt-2 text-sm font-semibold text-slate-500 dark:text-slate-400">Belum ada tiket support</p>
                <p class="text-xs mt-1">Modul tiket akan aktif setelah integrasi selesai</p>
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
            </div>
            <div class="space-y-6">
                <?php if (isset($component)) { $__componentOriginalf0ba6ef14ffa9e2e0936b821e3847e33 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalf0ba6ef14ffa9e2e0936b821e3847e33 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.base.card','data' => ['padding' => false]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('base.card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['padding' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(false)]); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

             <?php $__env->slot('header', null, []); ?> 
                <h3 class="text-base font-bold text-slate-900 dark:text-slate-100 flex items-center gap-2">
                    <span class="material-symbols-outlined notranslate text-indigo-500" translate="no" style="font-size:20px">engineering</span>
                    Riwayat Instalasi
                </h3>
             <?php $__env->endSlot(); ?>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($installations->isNotEmpty()): ?>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="bg-slate-50 dark:bg-slate-900/50 border-b border-slate-200 dark:border-slate-700 text-xs uppercase tracking-wider text-slate-500 dark:text-slate-400">
                            <th class="px-6 py-3 text-left font-semibold">ID</th>
                            <th class="px-6 py-3 text-left font-semibold">Tanggal</th>
                            <th class="px-6 py-3 text-left font-semibold">Teknisi</th>
                            <th class="px-6 py-3 text-left font-semibold">Status</th>
                            <th class="px-6 py-3 text-left font-semibold">Catatan</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-700">
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $installations; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $installation): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                        <tr class="hover:bg-slate-50 dark:bg-slate-900/50 dark:hover:bg-slate-800/50">
                            <td class="px-6 py-4 font-mono text-xs text-slate-900 dark:text-slate-100"><?php echo e($installation->uuid ?? $installation->id); ?></td>
                            <td class="px-6 py-4 text-slate-600 dark:text-slate-400"><?php echo e($installation->completed_at?->format('d/m/Y') ?? $installation->scheduled_at?->format('d/m/Y') ?? '-'); ?></td>
                            <td class="px-6 py-4 text-slate-600 dark:text-slate-400"><?php echo e($installation->assignedTo?->name ?? '-'); ?></td>
                            <td class="px-6 py-4">
                                <span class="px-2 py-1 text-xs font-semibold rounded-lg
                                    <?php if($installation->status === 'completed'): ?> bg-emerald-100 dark:bg-emerald-900/50 text-emerald-700
                                    <?php elseif($installation->status === 'in_progress'): ?> bg-blue-100 dark:bg-blue-900/50 text-blue-600
                                    <?php else: ?> bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400
                                    <?php endif; ?>">
                                    <?php echo e(ucfirst($installation->status)); ?>

                                </span>
                            </td>
                            <td class="px-6 py-4 text-slate-600 dark:text-slate-400"><?php echo e($installation->notes ?? '-'); ?></td>
                        </tr>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                    </tbody>
                </table>
            </div>
            <?php else: ?>
            <div class="p-10 text-center text-slate-400">
                <span class="material-symbols-outlined notranslate" translate="no" style="font-size:40px">engineering</span>
                <p class="mt-2 text-sm">Belum ada riwayat instalasi</p>
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
            </div>
        </div>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

        
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($activeTab === 'history'): ?>
        <div class="grid grid-cols-1 xl:grid-cols-3 gap-6">
            <div class="xl:col-span-2 space-y-6">
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

             <?php $__env->slot('header', null, []); ?> 
                <h3 class="text-base font-bold text-slate-900 dark:text-slate-100 flex items-center gap-2">
                    <span class="material-symbols-outlined notranslate text-indigo-500" translate="no" style="font-size:20px">history</span>
                    Timeline Pelanggan
                </h3>
             <?php $__env->endSlot(); ?>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(!empty($timeline)): ?>
            <div class="space-y-6">
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $timeline; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                <div class="flex gap-4">
                    <div class="flex flex-col items-center">
                        <div class="w-3 h-3 rounded-full mt-1 flex-shrink-0
                            <?php if($item['type'] === 'create'): ?> bg-indigo-500
                            <?php elseif($item['type'] === 'survey'): ?> bg-sky-500
                            <?php elseif($item['type'] === 'contract'): ?> bg-emerald-500
                            <?php elseif($item['type'] === 'installation'): ?> bg-orange-500
                            <?php elseif($item['type'] === 'activation'): ?> bg-purple-500
                            <?php else: ?> bg-slate-400
                            <?php endif; ?>">
                        </div>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(!$loop->last): ?>
                        <div class="w-0.5 flex-1 bg-slate-200 dark:bg-slate-700 mt-1"></div>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </div>
                    <div class="flex-1 pb-6">
                        <p class="text-xs text-slate-500 dark:text-slate-400 mb-1"><?php echo e($item['date']->format('d/m/Y H:i')); ?></p>
                        <p class="font-semibold text-slate-900 dark:text-slate-100"><?php echo e($item['title']); ?></p>
                        <p class="text-sm text-slate-600 dark:text-slate-400 mt-1"><?php echo e($item['description']); ?></p>
                    </div>
                </div>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
            </div>
            <?php else: ?>
            <div class="text-center py-8 text-slate-400">
                <span class="material-symbols-outlined notranslate" translate="no" style="font-size:40px">history</span>
                <p class="mt-2 text-sm">Belum ada riwayat</p>
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
            </div>
            <div class="space-y-6">
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

             <?php $__env->slot('header', null, []); ?> 
                <h3 class="text-base font-bold text-slate-900 dark:text-slate-100 flex items-center gap-2">
                    <span class="material-symbols-outlined notranslate text-indigo-500" translate="no" style="font-size:20px">timeline</span>
                    Aktivitas Terbaru
                </h3>
             <?php $__env->endSlot(); ?>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(!empty($activities)): ?>
            <div class="space-y-3">
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $activities; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $activity): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                <div class="flex items-start gap-3 p-3 hover:bg-slate-50 dark:bg-slate-900/50 dark:hover:bg-slate-800/50 rounded-xl transition-colors">
                    <div class="w-9 h-9 rounded-full bg-indigo-100 dark:bg-indigo-900/50 flex items-center justify-center flex-shrink-0">
                        <span class="text-sm font-bold text-indigo-600 dark:text-indigo-400"><?php echo e(substr($activity['user'], 0, 1)); ?></span>
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm text-slate-900 dark:text-slate-100">
                            <span class="font-semibold"><?php echo e($activity['user']); ?></span> <?php echo e($activity['action']); ?>

                        </p>
                        <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5 flex items-center gap-2">
                            <span class="px-2 py-0.5 bg-indigo-100 dark:bg-indigo-900/50 text-indigo-600 dark:text-indigo-400 rounded-full text-xs"><?php echo e($activity['module']); ?></span>
                            <?php echo e($activity['time']); ?>

                        </p>
                    </div>
                </div>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
            </div>
            <?php else: ?>
            <div class="text-center py-8 text-slate-400">
                <span class="material-symbols-outlined notranslate" translate="no" style="font-size:40px">timeline</span>
                <p class="mt-2 text-sm">Belum ada aktivitas tercatat</p>
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
                
                
            </div>
        </div>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

    </div>

    
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($showEditModal): ?>
    <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm" wire:click.self="$set('showEditModal', false)">
        <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-2xl w-full max-w-lg" @click.stop>
            <div class="px-6 py-4 border-b border-slate-200 dark:border-slate-700 flex items-center justify-between">
                <h3 class="text-lg font-bold text-slate-900 dark:text-slate-100 flex items-center gap-2">
                    <span class="material-symbols-outlined notranslate text-indigo-500" translate="no" style="font-size:20px">edit</span>
                    Edit Profil Pelanggan
                </h3>
                <button wire:click="$set('showEditModal', false)" class="text-slate-400 hover:text-slate-600 dark:text-slate-400 dark:hover:text-slate-200 transition-colors">
                    <span class="material-symbols-outlined notranslate" translate="no" style="font-size:22px">close</span>
                </button>
            </div>
            <div class="px-6 py-4 space-y-4 max-h-[70vh] overflow-y-auto">
                <div>
                    <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Nama <span class="text-red-500">*</span></label>
                    <input wire:model="edit_name" type="text" class="w-full px-4 py-2.5 border border-slate-200 dark:border-slate-600 rounded-xl text-sm focus:ring-2 focus:ring-indigo-500 dark:bg-slate-900 dark:text-slate-100 dark:bg-slate-900 dark:text-slate-100" placeholder="Nama lengkap">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['edit_name'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <p class="text-xs text-red-500 mt-1"><?php echo e($message); ?></p> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Telepon / WhatsApp</label>
                        <input wire:model="edit_phone" type="text" class="w-full px-4 py-2.5 border border-slate-200 dark:border-slate-600 rounded-xl text-sm focus:ring-2 focus:ring-indigo-500 dark:bg-slate-900 dark:text-slate-100 dark:bg-slate-900 dark:text-slate-100" placeholder="08xx...">
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['edit_phone'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <p class="text-xs text-red-500 mt-1"><?php echo e($message); ?></p> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Email</label>
                        <input wire:model="edit_email" type="email" class="w-full px-4 py-2.5 border border-slate-200 dark:border-slate-600 rounded-xl text-sm focus:ring-2 focus:ring-indigo-500 dark:bg-slate-900 dark:text-slate-100 dark:bg-slate-900 dark:text-slate-100" placeholder="email@domain.com">
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['edit_email'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <p class="text-xs text-red-500 mt-1"><?php echo e($message); ?></p> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Status Akun</label>
                    <select wire:model="edit_status" class="w-full px-4 py-2.5 border border-slate-200 dark:border-slate-600 rounded-xl text-sm focus:ring-2 focus:ring-indigo-500 dark:bg-slate-900 dark:text-slate-100 dark:bg-slate-900 dark:text-slate-100">
                        <option value="active">Aktif</option>
                        <option value="inactive">Nonaktif (Suspend)</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Alamat</label>
                    <textarea wire:model="edit_address" rows="3" class="w-full px-4 py-2.5 border border-slate-200 dark:border-slate-600 rounded-xl text-sm focus:ring-2 focus:ring-indigo-500 dark:bg-slate-900 dark:text-slate-100 resize-none dark:bg-slate-900 dark:text-slate-100" placeholder="Alamat lengkap..."></textarea>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Latitude</label>
                        <input wire:model="edit_latitude" type="text" class="w-full px-4 py-2.5 border border-slate-200 dark:border-slate-600 rounded-xl text-sm focus:ring-2 focus:ring-indigo-500 dark:bg-slate-900 dark:text-slate-100 font-mono dark:bg-slate-900 dark:text-slate-100" placeholder="-7.xxx">
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Longitude</label>
                        <input wire:model="edit_longitude" type="text" class="w-full px-4 py-2.5 border border-slate-200 dark:border-slate-600 rounded-xl text-sm focus:ring-2 focus:ring-indigo-500 dark:bg-slate-900 dark:text-slate-100 font-mono dark:bg-slate-900 dark:text-slate-100" placeholder="110.xxx">
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Reseller / Owner</label>
                        <select wire:model="edit_reseller_id" class="w-full px-4 py-2.5 border border-slate-200 dark:border-slate-600 rounded-xl text-sm focus:ring-2 focus:ring-indigo-500 dark:bg-slate-900 dark:text-slate-100 dark:bg-slate-900 dark:text-slate-100">
                            <option value="">-- Pilih Reseller --</option>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = \App\Models\User::all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $user): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                                <option value="<?php echo e($user->id); ?>"><?php echo e($user->name); ?></option>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Cabang (Branch)</label>
                        <select wire:model="edit_branch_id" class="w-full px-4 py-2.5 border border-slate-200 dark:border-slate-600 rounded-xl text-sm focus:ring-2 focus:ring-indigo-500 dark:bg-slate-900 dark:text-slate-100 dark:bg-slate-900 dark:text-slate-100">
                            <option value="">-- Pilih Cabang --</option>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = \App\Models\Master\Branch::all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $branch): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                                <option value="<?php echo e($branch->id); ?>"><?php echo e($branch->name); ?></option>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                        </select>
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Catatan</label>
                    <textarea wire:model="edit_notes" rows="2" class="w-full px-4 py-2.5 border border-slate-200 dark:border-slate-600 rounded-xl text-sm focus:ring-2 focus:ring-indigo-500 dark:bg-slate-900 dark:text-slate-100 resize-none dark:bg-slate-900 dark:text-slate-100" placeholder="Catatan tambahan..."></textarea>
                </div>
            </div>
            <div class="px-6 py-4 border-t border-slate-200 dark:border-slate-700 flex items-center justify-end gap-3">
                <button wire:click="$set('showEditModal', false)" class="px-4 py-2 rounded-xl border border-slate-200 dark:border-slate-700 text-slate-700 dark:text-slate-300 text-sm font-semibold hover:bg-slate-50 dark:bg-slate-900/50 dark:hover:bg-slate-700 transition-colors cursor-pointer">
                    Batal
                </button>
                <button wire:click="updateCustomer" class="px-4 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-semibold shadow transition-colors cursor-pointer">
                    Simpan Perubahan
                </button>
            </div>
        </div>
    </div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

    
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($showEditServiceModal): ?>
    <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm" wire:click.self="$set('showEditServiceModal', false)">
        <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-2xl w-full max-w-lg" @click.stop>
            <div class="px-6 py-4 border-b border-slate-200 dark:border-slate-700 flex items-center justify-between">
                <h3 class="text-lg font-bold text-slate-900 dark:text-slate-100 flex items-center gap-2">
                    <span class="material-symbols-outlined notranslate text-indigo-500" translate="no" style="font-size:20px">wifi_protected_setup</span>
                    Ganti Layanan / Paket
                </h3>
                <button wire:click="$set('showEditServiceModal', false)" class="text-slate-400 hover:text-slate-600 dark:text-slate-400 dark:hover:text-slate-200 transition-colors">
                    <span class="material-symbols-outlined notranslate" translate="no" style="font-size:22px">close</span>
                </button>
            </div>
            <div class="px-6 py-4 space-y-4 max-h-[70vh] overflow-y-auto">
                <div>
                    <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Jenis Layanan</label>
                    <div class="grid grid-cols-2 gap-3">
                        <label class="cursor-pointer">
                            <input type="radio" wire:model.live="edit_service_type" value="pppoe" class="peer sr-only dark:bg-slate-900 dark:text-slate-100">
                            <div class="px-4 py-3 rounded-xl border border-slate-200 dark:border-slate-700 text-center peer-checked:bg-indigo-50 dark:bg-indigo-900/30 peer-checked:border-indigo-500 peer-checked:text-indigo-700 dark:peer-checked:bg-indigo-900/30 dark:peer-checked:text-indigo-400 transition-all">
                                <span class="block font-bold">PPPoE</span>
                                <span class="text-xs text-slate-500 dark:text-slate-400">Rumahan / Broadband</span>
                            </div>
                        </label>
                        <label class="cursor-pointer">
                            <input type="radio" wire:model.live="edit_service_type" value="hotspot" class="peer sr-only dark:bg-slate-900 dark:text-slate-100">
                            <div class="px-4 py-3 rounded-xl border border-slate-200 dark:border-slate-700 text-center peer-checked:bg-indigo-50 dark:bg-indigo-900/30 peer-checked:border-indigo-500 peer-checked:text-indigo-700 dark:peer-checked:bg-indigo-900/30 dark:peer-checked:text-indigo-400 transition-all">
                                <span class="block font-bold">Hotspot</span>
                                <span class="text-xs text-slate-500 dark:text-slate-400">Voucher / Warkop</span>
                            </div>
                        </label>
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Profil Paket</label>
                    <select wire:model="edit_service_profile_id" class="w-full px-4 py-2.5 border border-slate-200 dark:border-slate-600 rounded-xl text-sm focus:ring-2 focus:ring-indigo-500 dark:bg-slate-900 dark:text-slate-100 dark:bg-slate-900 dark:text-slate-100">
                        <option value="">-- Pilih Paket Baru --</option>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = \App\Models\ISP\ServiceProfile::where('service_type', $edit_service_type)->get(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $profile): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                            <option value="<?php echo e($profile->id); ?>"><?php echo e($profile->name); ?> (Rp <?php echo e(number_format($profile->price, 0, ',', '.')); ?>)</option>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                    </select>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['edit_service_profile_id'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <p class="text-xs text-red-500 mt-1"><?php echo e($message); ?></p> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>
                <div>
                    <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Metode Autentikasi</label>
                    <select wire:model.live="edit_service_password_mode" class="w-full px-4 py-2.5 border border-slate-200 dark:border-slate-600 rounded-xl text-sm focus:ring-2 focus:ring-indigo-500 dark:bg-slate-900 dark:text-slate-100 mb-3 dark:bg-slate-900 dark:text-slate-100">
                        <option value="custom">Username & Password Berbeda</option>
                        <option value="same">Username = Password</option>
                    </select>
                </div>
                <div class="grid <?php echo e($edit_service_password_mode === 'same' ? 'grid-cols-1' : 'grid-cols-2'); ?> gap-4" x-data="{ copyToClipboard(text) { navigator.clipboard.writeText(text); alert('Tersalin ke clipboard!'); } }">
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Username</label>
                        <div class="relative">
                            <input wire:model.live="edit_service_username" x-ref="uname" type="text" class="w-full pl-4 pr-10 py-2.5 border border-slate-200 dark:border-slate-600 rounded-xl text-sm focus:ring-2 focus:ring-indigo-500 dark:bg-slate-900 dark:text-slate-100 dark:bg-slate-900 dark:text-slate-100" placeholder="Username">
                            <button @click="copyToClipboard($refs.uname.value)" type="button" class="absolute right-2 top-1.5 p-1.5 text-slate-400 hover:text-indigo-600 transition-colors" title="Copy Username">
                                <span class="material-symbols-outlined notranslate" translate="no" style="font-size:18px">content_copy</span>
                            </button>
                        </div>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['edit_service_username'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <p class="text-xs text-red-500 mt-1"><?php echo e($message); ?></p> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </div>
                    
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($edit_service_password_mode !== 'same'): ?>
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Password</label>
                        <div class="relative">
                            <input wire:model="edit_service_password" x-ref="pwd" type="text" class="w-full pl-4 pr-10 py-2.5 border border-slate-200 dark:border-slate-600 rounded-xl text-sm focus:ring-2 focus:ring-indigo-500 dark:bg-slate-900 dark:text-slate-100 dark:bg-slate-900 dark:text-slate-100" placeholder="Password">
                            <button @click="copyToClipboard($refs.pwd.value)" type="button" class="absolute right-2 top-1.5 p-1.5 text-slate-400 hover:text-indigo-600 transition-colors" title="Copy Password">
                                <span class="material-symbols-outlined notranslate" translate="no" style="font-size:18px">content_copy</span>
                            </button>
                        </div>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['edit_service_password'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <p class="text-xs text-red-500 mt-1"><?php echo e($message); ?></p> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </div>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>
                <div class="p-3 bg-amber-50 dark:bg-amber-900/30 rounded-xl border border-amber-200 dark:border-amber-800 text-sm text-amber-700 dark:text-amber-400">
                    <strong>Penting:</strong> Jika Anda mengubah jenis layanan (misal PPPoE ke Hotspot), akun lama di RADIUS akan otomatis dihapus dan diganti dengan yang baru.
                </div>
            </div>
            <div class="px-6 py-4 border-t border-slate-200 dark:border-slate-700 flex items-center justify-end gap-3">
                <button wire:click="$set('showEditServiceModal', false)" class="px-4 py-2 rounded-xl border border-slate-200 dark:border-slate-700 text-slate-700 dark:text-slate-300 text-sm font-semibold hover:bg-slate-50 dark:bg-slate-900/50 dark:hover:bg-slate-700 transition-colors cursor-pointer">
                    Batal
                </button>
                <button wire:click="updateService" class="px-4 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-semibold shadow transition-colors cursor-pointer">
                    Simpan Layanan
                </button>
            </div>
        </div>
    </div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>


    
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($showWifiModal): ?>
    <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/50 backdrop-blur-sm">
        <div class="bg-white dark:bg-slate-800 rounded-xl shadow-xl w-full max-w-md overflow-hidden transform transition-all">
            <div class="px-5 py-4 border-b border-slate-100 dark:border-slate-700 flex items-center justify-between">
                <h3 class="text-lg font-bold text-slate-900 dark:text-slate-100">Ganti Nama/Pass WiFi</h3>
                <button wire:click="$set('showWifiModal', false)" class="text-slate-400 hover:text-slate-600 dark:text-slate-400">
                    <span class="material-symbols-outlined notranslate" translate="no">close</span>
                </button>
            </div>
            
            <form wire:submit.prevent="saveWifi">
                <div class="p-5 space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Target WLAN</label>
                        <select wire:model.live="wlanTarget" class="w-full px-3 py-2 border border-slate-200 dark:border-slate-700 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500 dark:bg-slate-900 dark:text-slate-100">
                            <option value="1">WLAN 1 (Utama - 2.4GHz)</option>
                            <option value="5">WLAN 5 (Utama - 5GHz)</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Nama WiFi (SSID)</label>
                        <input type="text" wire:model="wifiSsid" required class="w-full px-3 py-2 border border-slate-200 dark:border-slate-700 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500 dark:bg-slate-900 dark:text-slate-100">
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['wifiSsid'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <span class="text-xs text-red-500"><?php echo e($message); ?></span> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Password Baru</label>
                        <input type="text" wire:model="wifiPassword" class="w-full px-3 py-2 border border-slate-200 dark:border-slate-700 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500 dark:bg-slate-900 dark:text-slate-100">
                        <p class="text-[11px] text-slate-500 dark:text-slate-400 mt-1">Minimal 8 karakter. Biarkan sama jika tidak ingin mengganti sandi.</p>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['wifiPassword'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <span class="text-xs text-red-500"><?php echo e($message); ?></span> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </div>
                </div>
                <div class="px-5 py-3 bg-slate-50 dark:bg-slate-900/50 border-t border-slate-100 dark:border-slate-700 flex items-center justify-end gap-3">
                    <button type="button" wire:click="$set('showWifiModal', false)" class="px-4 py-2 text-sm font-medium text-slate-700 dark:text-slate-300 bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-600 rounded-lg hover:bg-slate-50 dark:bg-slate-900/50">Batal</button>
                    <button type="submit" class="px-4 py-2 text-sm font-medium text-white bg-indigo-600 rounded-lg hover:bg-indigo-700 flex items-center gap-2">
                        <span class="material-symbols-outlined notranslate" translate="no" style="font-size:18px">send</span> Kirim ke Modem
                    </button>
                </div>
            </form>
        </div>
    </div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
</div>



<script>
document.addEventListener('livewire:initialized', () => {
    let map = null;
    
    function initMap() {
        if (typeof L === 'undefined') {
            setTimeout(initMap, 100);
            return;
        }
        const mapEl = document.getElementById('customer-map');
        if (!mapEl) return;
        
        if (map !== null) {
            map.invalidateSize();
            return;
        }
        
        let lat = <?php echo e($customer->latitude ?? 0); ?>;
        let lng = <?php echo e($customer->longitude ?? 0); ?>;
        
        map = L.map('customer-map').setView([lat, lng], 16);
        
        let osm = L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '&copy; OpenStreetMap contributors'
        });
        
        let googleSat = L.tileLayer('https://mt{s}.google.com/vt/lyrs=y&x={x}&y={y}&z={z}', {
            maxZoom: 20,
            subdomains: ['0', '1', '2', '3']
        });
        
        osm.addTo(map);
        
        L.control.layers({
            "Street (OSM)": osm,
            "Satellite (Google)": googleSat
        }).addTo(map);
        
        L.marker([lat, lng]).addTo(map).bindPopup("<?php echo e($customer->name); ?>");
    }
    
    initMap();
    
    Livewire.hook('commit', ({ succeed }) => {
        succeed(() => {
            setTimeout(() => {
                initMap();
            }, 50);
        });
    });
});
</script>
</div>


<?php /**PATH D:\dsBilling\resources\views\livewire\reseller-portal\customer\customer360.blade.php ENDPATH**/ ?>