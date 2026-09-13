<?php $__env->startSection('page_title'); ?>
    <div class="flex items-center gap-2">
        <div class="w-8 h-8 rounded-full bg-emerald-100 dark:bg-emerald-900/50 flex items-center justify-center text-emerald-600 dark:text-emerald-400 font-bold text-sm">
            <span class="material-symbols-outlined notranslate" translate="no" style="font-size:18px">offline_bolt</span>
        </div>
        <span class="text-lg">Aktivasi Layanan</span>
    </div>
<?php $__env->stopSection(); ?>

<div class="space-y-6 pb-10">
    
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div wire:click="$set('activeTab', 'pending')" class="cursor-pointer relative overflow-x-auto rounded-xl border <?php echo e($activeTab === 'pending' ? 'border-amber-500 ring-1 ring-amber-500' : 'border-amber-200 dark:border-amber-800/60'); ?> shadow-md bg-gradient-to-br from-amber-50 to-white dark:from-amber-950/50 dark:to-slate-800 transition-all">
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($activeTab === 'pending'): ?> <div class="absolute top-0 left-0 right-0 h-1 bg-amber-500 rounded-t-xl"></div> <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            <div class="p-4 pt-5">
                <h3 class="text-xs font-bold text-amber-500 dark:text-amber-400 uppercase tracking-widest mb-2">Menunggu Aktivasi</h3>
                <div class="text-2xl font-black text-amber-700 dark:text-amber-300 mb-1"><?php echo e(number_format($stats['pending'] ?? 0)); ?></div>
                <div class="text-xs text-slate-500 dark:text-slate-400">Siap diaktifkan & didaftarkan ke radius</div>
            </div>
        </div>

        <div wire:click="$set('activeTab', 'completed')" class="cursor-pointer relative overflow-x-auto rounded-xl border <?php echo e($activeTab === 'completed' ? 'border-emerald-500 ring-1 ring-emerald-500' : 'border-emerald-200 dark:border-emerald-800/60'); ?> shadow-md bg-gradient-to-br from-emerald-50 to-white dark:from-emerald-950/50 dark:to-slate-800 transition-all">
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($activeTab === 'completed'): ?> <div class="absolute top-0 left-0 right-0 h-1 bg-emerald-500 rounded-t-xl"></div> <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            <div class="p-4 pt-5">
                <h3 class="text-xs font-bold text-emerald-500 dark:text-emerald-400 uppercase tracking-widest mb-2">Pelanggan Aktif</h3>
                <div class="text-2xl font-black text-emerald-700 dark:text-emerald-300 mb-1"><?php echo e(number_format($stats['completed'] ?? 0)); ?></div>
                <div class="text-xs text-slate-500 dark:text-slate-400">Layanan telah berhasil diaktifkan</div>
            </div>
        </div>
    </div>

    
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-white dark:bg-slate-800 p-4 rounded-xl shadow-sm border border-slate-200 dark:border-slate-700">
        <div class="flex flex-wrap items-center gap-3">
            <div class="relative w-full sm:w-64">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <span class="material-symbols-outlined notranslate text-slate-400" translate="no" style="font-size:18px">search</span>
                </div>
                <input wire:model.live.debounce.300ms="search" type="text" 
                    class="block w-full pl-10 pr-3 py-2 border border-slate-200 dark:border-slate-700 rounded-lg text-sm bg-slate-50 dark:bg-slate-900/50 focus:ring-2 focus:ring-indigo-500 dark:text-slate-100 placeholder-slate-400 dark:bg-slate-900 dark:text-slate-100" 
                    placeholder="Cari nama pelanggan...">
            </div>
        </div>

        <div class="flex items-center gap-3">
            <select wire:model.live="perPage" class="py-2 pl-3 pr-8 border border-slate-200 dark:border-slate-700 rounded-lg text-sm bg-slate-50 dark:bg-slate-900/50 focus:ring-2 focus:ring-indigo-500 dark:text-slate-100 dark:bg-slate-900 dark:text-slate-100">
                <option value="20">20 / halaman</option>
                <option value="50">50 / halaman</option>
                <option value="all">Semua</option>
            </select>
        </div>
    </div>

    
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(session('success')): ?>
        <div class="bg-emerald-50 dark:bg-emerald-900/30 p-4 rounded-xl border border-emerald-200 dark:border-emerald-800/50 flex gap-3 text-emerald-800 dark:text-emerald-400">
            <span class="material-symbols-outlined notranslate shrink-0 mt-0.5" translate="no">check_circle</span>
            <p class="text-sm font-bold"><?php echo e(session('success')); ?></p>
        </div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['activation'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
        <div class="bg-red-50 dark:bg-red-900/30 p-4 rounded-xl border border-red-200 dark:border-red-800/50 flex gap-3 text-red-800 dark:text-red-400">
            <span class="material-symbols-outlined notranslate shrink-0 mt-0.5" translate="no">error</span>
            <p class="text-sm font-bold"><?php echo e($message); ?></p>
        </div>
    <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

    
    <div class="bg-white dark:bg-slate-800 rounded-xl shadow-sm border border-slate-200 dark:border-slate-700 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm text-left">
                <thead class="bg-slate-50 dark:bg-slate-900/50 text-slate-600 dark:text-slate-400 font-semibold border-b border-slate-200 dark:border-slate-700">
                    <tr>
                        <th class="px-6 py-4">Prospek / Pelanggan</th>
                        <th class="px-6 py-4">Kontak</th>
                        <th class="px-6 py-4">Teknisi / Instalasi</th>
                        <th class="px-6 py-4">ODP & Jarak</th>
                        <th class="px-6 py-4 text-center">Status</th>
                        <th class="px-6 py-4 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-700">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $items; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                        <tr class="hover:bg-slate-50 dark:bg-slate-900/50 dark:hover:bg-slate-800/50 transition-colors">
                            <td class="px-6 py-4">
                                <div class="flex flex-col gap-1">
                                    <span class="font-bold text-slate-900 dark:text-slate-100"><?php echo e($item->prospect->name ?? '-'); ?></span>
                                    <span class="text-xs text-slate-500 dark:text-slate-400 font-mono">ID: <?php echo e($item->prospect->id ?? '-'); ?></span>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex flex-col gap-1">
                                    <span class="text-slate-600 dark:text-slate-300 flex items-center gap-1"><span class="material-symbols-outlined notranslate text-[14px]" translate="no">call</span> <?php echo e($item->prospect->phone ?? '-'); ?></span>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-2">
                                    <?php if (isset($component)) { $__componentOriginalf3a01b417729d1136223a876b3a78880 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalf3a01b417729d1136223a876b3a78880 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.display.avatar','data' => ['name' => $item->assignedTo->name ?? 'Unassigned','size' => 'sm']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('display.avatar'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($item->assignedTo->name ?? 'Unassigned'),'size' => 'sm']); ?>
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
                                    <div class="flex flex-col">
                                        <span class="font-medium text-slate-900 dark:text-slate-100"><?php echo e($item->assignedTo->name ?? 'Belum Ditugaskan'); ?></span>
                                        <span class="text-xs text-slate-500 dark:text-slate-400"><?php echo e($item->updated_at->format('d M Y H:i')); ?></span>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex flex-col gap-1">
                                    <span class="font-medium text-indigo-600 dark:text-indigo-400"><?php echo e($item->odp->name ?? 'Tidak Ada'); ?></span>
                                    <span class="text-xs text-slate-500 dark:text-slate-400">Jarak: <?php echo e($item->distance ?? 0); ?>m | Kabel: <?php echo e($item->cable_estimation ?? 0); ?>m</span>
                                </div>
                            </td>
                            <td class="px-6 py-4 text-center">
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($item->status === 'activated'): ?>
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-xs font-bold bg-emerald-50 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400 border border-emerald-200 dark:border-emerald-800/50">
                                        <span class="material-symbols-outlined notranslate text-[14px]" translate="no">check_circle</span> Aktif
                                    </span>
                                <?php else: ?>
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-xs font-bold bg-amber-50 text-amber-700 dark:bg-amber-900/30 dark:text-amber-400 border border-amber-200 dark:border-amber-800/50">
                                        <span class="material-symbols-outlined notranslate text-[14px]" translate="no">pending</span> Menunggu
                                    </span>
                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            </td>
                            <td class="px-6 py-4 text-right">
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($item->status !== 'activated'): ?>
                                    <button wire:click="openActivationModal(<?php echo e($item->id); ?>)" class="inline-flex items-center gap-1 px-3 py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg text-xs font-semibold transition-colors shadow-sm">
                                        <span class="material-symbols-outlined notranslate text-[16px]" translate="no">power</span>
                                        Aktifkan
                                    </button>
                                <?php else: ?>
                                    <button disabled class="inline-flex items-center gap-1 px-3 py-1.5 bg-slate-100 dark:bg-slate-800 text-slate-400 rounded-lg text-xs font-semibold cursor-not-allowed">
                                        <span class="material-symbols-outlined notranslate text-[16px]" translate="no">verified</span>
                                        Aktif
                                    </button>
                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            </td>
                        </tr>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                        <tr>
                            <td colspan="6" class="px-6 py-12 text-center">
                                <div class="flex flex-col items-center justify-center">
                                    <span class="material-symbols-outlined notranslate text-slate-300 dark:text-slate-600 dark:text-slate-400 mb-3" translate="no" style="font-size:48px">
                                        <?php echo e($activeTab === 'pending' ? 'inbox' : 'verified'); ?>

                                    </span>
                                    <h3 class="text-base font-bold text-slate-900 dark:text-slate-100 mb-1">Tidak ada data</h3>
                                    <p class="text-sm text-slate-500 dark:text-slate-400">
                                        <?php echo e($activeTab === 'pending' ? 'Belum ada prospek yang siap diaktifkan layanannya.' : 'Belum ada layanan pelanggan yang aktif.'); ?>

                                    </p>
                                </div>
                            </td>
                        </tr>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </tbody>
            </table>
        </div>
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($items->hasPages()): ?>
            <div class="px-6 py-4 border-t border-slate-200 dark:border-slate-700">
                <?php echo e($items->links()); ?>

            </div>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
    </div>

    
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($showActivationModal): ?>
    <div class="fixed inset-0 z-50 flex items-center justify-center overflow-y-auto overflow-x-hidden bg-slate-900/50 backdrop-blur-sm p-4">
        <div class="relative w-full max-w-2xl bg-white dark:bg-slate-800 rounded-2xl shadow-xl border border-slate-200 dark:border-slate-700 overflow-hidden">
            
            <div class="px-6 py-4 border-b border-slate-200 dark:border-slate-700 flex items-center justify-between bg-slate-50/50 dark:bg-slate-900/50">
                <h3 class="text-lg font-bold text-slate-900 dark:text-white flex items-center gap-2">
                    <span class="material-symbols-outlined notranslate text-emerald-500" translate="no">power</span>
                    Aktivasi Layanan: <?php echo e($activation_prospect_name); ?>

                </h3>
                <button wire:click="$set('showActivationModal', false)" class="text-slate-400 hover:text-slate-600 dark:text-slate-400 dark:hover:text-slate-300 transition-colors">
                    <span class="material-symbols-outlined notranslate" translate="no">close</span>
                </button>
            </div>

            
            <form wire:submit="activateService">
                <div class="p-6 space-y-6">
                    
                    <div class="bg-blue-50 dark:bg-blue-900/30 border border-blue-200 dark:border-blue-800/50 rounded-xl p-4 flex gap-3 text-blue-800 dark:text-blue-300">
                        <span class="material-symbols-outlined notranslate shrink-0" translate="no">info</span>
                        <div class="text-sm">
                            <p class="font-bold mb-1">Informasi Aktivasi PPPoE</p>
                            <p>Proses ini akan mendaftarkan akun PPPoE ke Radius dan menyambungkan ke jaringan Mikrotik (NAS) secara otomatis.</p>
                        </div>
                    </div>

                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                        <div class="col-span-1 md:col-span-2">
                            <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Network Profile (Router/NAS)</label>
                            <select wire:model="network_profile_id" required class="w-full px-4 py-2 border border-slate-200 dark:border-slate-600 rounded-xl text-sm focus:ring-2 focus:ring-indigo-500 bg-white dark:bg-slate-900 dark:text-slate-100 dark:bg-slate-900 dark:text-slate-100">
                                <option value="">-- Pilih Jaringan Mikrotik --</option>
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $networkProfiles; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $np): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                                    <option value="<?php echo e($np->id); ?>"><?php echo e($np->name); ?> (NAS: <?php echo e($np->nas_ip); ?>)</option>
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                            </select>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['network_profile_id'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <p class="text-xs text-red-500 mt-1"><?php echo e($message); ?></p> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </div>

                        <div class="col-span-1 md:col-span-2">
                            <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Service Profile (Paket Internet)</label>
                            <select wire:model="service_profile_id" required class="w-full px-4 py-2 border border-slate-200 dark:border-slate-600 rounded-xl text-sm focus:ring-2 focus:ring-indigo-500 bg-white dark:bg-slate-900 dark:text-slate-100 dark:bg-slate-900 dark:text-slate-100">
                                <option value="">-- Pilih Paket PPPoE --</option>
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $serviceProfiles; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $sp): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                                    <option value="<?php echo e($sp->id); ?>"><?php echo e($sp->name); ?> (Rp <?php echo e(number_format($sp->price, 0, ',', '.')); ?>)</option>
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                            </select>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['service_profile_id'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <p class="text-xs text-red-500 mt-1"><?php echo e($message); ?></p> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </div>

                        <div>
                            <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Username PPPoE</label>
                            <input type="text" wire:model="pppoe_username" required class="w-full px-4 py-2 border border-slate-200 dark:border-slate-600 rounded-xl text-sm focus:ring-2 focus:ring-indigo-500 bg-white dark:bg-slate-900 dark:text-slate-100 font-mono dark:bg-slate-900 dark:text-slate-100">
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['pppoe_username'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <p class="text-xs text-red-500 mt-1"><?php echo e($message); ?></p> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </div>

                        <div>
                            <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Password PPPoE</label>
                            <div class="relative">
                                <input type="text" wire:model="pppoe_password" required class="w-full px-4 py-2 border border-slate-200 dark:border-slate-600 rounded-xl text-sm focus:ring-2 focus:ring-indigo-500 bg-white dark:bg-slate-900 dark:text-slate-100 font-mono pr-10 dark:bg-slate-900 dark:text-slate-100">
                            </div>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['pppoe_password'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <p class="text-xs text-red-500 mt-1"><?php echo e($message); ?></p> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </div>
                    </div>
                </div>

                
                <div class="px-6 py-4 border-t border-slate-200 dark:border-slate-700 bg-slate-50/50 dark:bg-slate-900/50 flex items-center justify-end gap-3">
                    <button type="button" wire:click="$set('showActivationModal', false)" class="px-5 py-2.5 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-700 dark:text-slate-300 rounded-xl text-sm font-semibold hover:bg-slate-50 dark:bg-slate-900/50 transition-colors">
                        Batal
                    </button>
                    <button type="submit" class="px-5 py-2.5 bg-emerald-600 text-white rounded-xl text-sm font-semibold hover:bg-emerald-700 transition-colors flex items-center gap-2">
                        <span class="material-symbols-outlined notranslate" translate="no" style="font-size:18px">check_circle</span>
                        Proses Aktivasi
                    </button>
                </div>
            </form>
        </div>
    </div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
</div>
<?php /**PATH D:\dsBilling\resources\views\livewire\crm\activation\index.blade.php ENDPATH**/ ?>