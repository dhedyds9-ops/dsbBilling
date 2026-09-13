<div>
    <?php $__env->startSection('page_title'); ?>
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl bg-blue-500/10 flex items-center justify-center text-blue-600 dark:text-blue-400">
                <span class="material-symbols-outlined notranslate" translate="no">cell_tower</span>
            </div>
            <div>
                <h1 class="text-xl font-bold text-slate-900 dark:text-slate-100 dark:text-white leading-tight">Session Online</h1>
                <p class="text-sm text-slate-500 dark:text-slate-400">Monitoring koneksi aktif pelanggan PPPoE, Hotspot & Voucher.</p>
            </div>
        </div>
    <?php $__env->stopSection(); ?>

    <div class="space-y-4" wire:poll.10s>
        
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            
            <div wire:click="setActiveTab('pppoe')" class="relative overflow-x-auto rounded-xl border <?php echo e($activeTab === 'pppoe' ? 'border-blue-500 shadow-md ring-1 ring-blue-500' : 'border-blue-200 dark:border-blue-800/60 shadow-sm'); ?> bg-gradient-to-br from-blue-50 to-white dark:from-blue-950/50 dark:to-slate-800 group hover:shadow-md transition-all cursor-pointer">
                <div class="absolute top-0 left-0 right-0 h-1 bg-gradient-to-r from-blue-500 to-cyan-400 rounded-t-xl"></div>
                <div class="absolute top-3 right-3 opacity-10 text-blue-400 group-hover:opacity-20 group-hover:scale-110 transition-all">
                    <span class="material-symbols-outlined notranslate" translate="no" style="font-size:56px">router</span>
                </div>
                <div class="p-4 pt-5">
                    <h3 class="text-xs font-bold text-blue-500 uppercase tracking-widest mb-2">PPPoE Online</h3>
                    <div class="flex items-baseline gap-2">
                        <span class="text-3xl font-black text-slate-800 dark:text-slate-100 tracking-tight"><?php echo e(number_format($stats['pppoe'])); ?></span>
                        <span class="text-sm font-medium text-slate-500 dark:text-slate-400">Sesi</span>
                    </div>
                </div>
            </div>

            
            <div wire:click="setActiveTab('hotspot')" class="relative overflow-x-auto rounded-xl border <?php echo e($activeTab === 'hotspot' ? 'border-green-500 shadow-md ring-1 ring-green-500' : 'border-green-200 dark:border-green-800/60 shadow-sm'); ?> bg-gradient-to-br from-green-50 to-white dark:from-green-950/50 dark:to-slate-800 group hover:shadow-md transition-all cursor-pointer">
                <div class="absolute top-0 left-0 right-0 h-1 bg-gradient-to-r from-green-500 to-emerald-400 rounded-t-xl"></div>
                <div class="absolute top-3 right-3 opacity-10 text-green-400 group-hover:opacity-20 group-hover:scale-110 transition-all">
                    <span class="material-symbols-outlined notranslate" translate="no" style="font-size:56px">wifi</span>
                </div>
                <div class="p-4 pt-5">
                    <h3 class="text-xs font-bold text-green-500 uppercase tracking-widest mb-2">Hotspot Online</h3>
                    <div class="flex items-baseline gap-2">
                        <span class="text-3xl font-black text-slate-800 dark:text-slate-100 tracking-tight"><?php echo e(number_format($stats['hotspot'])); ?></span>
                        <span class="text-sm font-medium text-slate-500 dark:text-slate-400">Sesi</span>
                    </div>
                </div>
            </div>

            
            <div wire:click="setActiveTab('voucher')" class="relative overflow-x-auto rounded-xl border <?php echo e($activeTab === 'voucher' ? 'border-purple-500 shadow-md ring-1 ring-purple-500' : 'border-purple-200 dark:border-purple-800/60 shadow-sm'); ?> bg-gradient-to-br from-purple-50 to-white dark:from-purple-950/50 dark:to-slate-800 group hover:shadow-md transition-all cursor-pointer">
                <div class="absolute top-0 left-0 right-0 h-1 bg-gradient-to-r from-purple-500 to-fuchsia-400 rounded-t-xl"></div>
                <div class="absolute top-3 right-3 opacity-10 text-purple-400 group-hover:opacity-20 group-hover:scale-110 transition-all">
                    <span class="material-symbols-outlined notranslate" translate="no" style="font-size:56px">confirmation_number</span>
                </div>
                <div class="p-4 pt-5">
                    <h3 class="text-xs font-bold text-purple-500 uppercase tracking-widest mb-2">Voucher Online</h3>
                    <div class="flex items-baseline gap-2">
                        <span class="text-3xl font-black text-slate-800 dark:text-slate-100 tracking-tight"><?php echo e(number_format($stats['voucher'])); ?></span>
                        <span class="text-sm font-medium text-slate-500 dark:text-slate-400">Sesi</span>
                    </div>
                </div>
            </div>

            
            <div class="relative overflow-x-auto rounded-xl border border-slate-200 dark:border-slate-700 shadow-sm bg-gradient-to-br from-slate-50 to-white dark:from-slate-800 dark:to-slate-900">
                <div class="absolute top-0 left-0 right-0 h-1 bg-gradient-to-r from-slate-400 to-slate-300 dark:from-slate-600 dark:to-slate-500 rounded-t-xl"></div>
                <div class="absolute top-3 right-3 opacity-10 text-slate-400 group-hover:scale-110 transition-all">
                    <span class="material-symbols-outlined notranslate" translate="no" style="font-size:56px">group</span>
                </div>
                <div class="p-4 pt-5">
                    <h3 class="text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-widest mb-2">Total Semua</h3>
                    <div class="flex items-baseline gap-2">
                        <span class="text-3xl font-black text-slate-800 dark:text-slate-100 tracking-tight"><?php echo e(number_format($stats['total'])); ?></span>
                        <span class="text-sm font-medium text-slate-500 dark:text-slate-400">Sesi</span>
                    </div>
                </div>
            </div>
        </div>

        
        <div class="flex flex-col sm:flex-row gap-3 items-center justify-between bg-white dark:bg-slate-800 p-4 rounded-xl shadow-sm border border-slate-200 dark:border-slate-700">
            <div class="flex-1 w-full relative">
                <span class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400">
                    <span class="material-symbols-outlined notranslate" translate="no" style="font-size: 20px">search</span>
                </span>
                <input type="text" wire:model.live.debounce.300ms="search" placeholder="Cari User, IP, Router..." class="w-full pl-9 pr-4 py-2 bg-slate-50 dark:bg-slate-900/50 border border-slate-200 dark:border-slate-700 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 transition-all dark:bg-slate-900 dark:text-slate-100">
            </div>
            <div class="text-sm text-slate-500 dark:text-slate-400">
                Menampilkan <span class="font-bold text-slate-700 dark:text-slate-300"><?php echo e(ucfirst($activeTab)); ?></span> online
            </div>
        </div>

        
        <div class="bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl shadow-sm overflow-hidden overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-slate-200 dark:border-slate-700 text-xs uppercase tracking-wider text-slate-500 dark:text-slate-400 bg-slate-50 dark:bg-slate-900/80">
                        <th class="px-4 py-3 text-left font-semibold whitespace-nowrap">Nama Pelanggan</th>
                        <th class="px-4 py-3 text-left font-semibold whitespace-nowrap">Akun (Username)</th>
                        <th class="px-4 py-3 text-left font-semibold whitespace-nowrap">Owner / Reseller</th>
                        <th class="px-4 py-3 text-left font-semibold whitespace-nowrap">Router</th>
                        <th class="px-4 py-3 text-left font-semibold whitespace-nowrap">Alamat IP</th>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($activeTab !== 'pppoe'): ?>
                            <th class="px-4 py-3 text-left font-semibold whitespace-nowrap">MAC Address</th>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        <th class="px-4 py-3 text-left font-semibold whitespace-nowrap">Uptime</th>
                        <th class="px-4 py-3 text-left font-semibold whitespace-nowrap">Traffic (In/Out)</th>
                        <th class="px-4 py-3 text-left font-semibold whitespace-nowrap">Mulai Session</th>
                        <th class="px-4 py-3 text-center font-semibold whitespace-nowrap">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-700">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__empty_0 = true; $__currentLoopData = $results; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $session): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_0 = false; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                        <tr class="hover:bg-slate-50 dark:bg-slate-900/50 dark:hover:bg-slate-800/50 transition-colors">
                            <td class="px-4 py-3 font-medium text-slate-900 dark:text-slate-100">
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($activeTab === 'pppoe'): ?>
                                    <?php echo e($session->pppoeUser?->customerService?->customer?->name ?? 'Anonim / Tidak Ditemukan'); ?>

                                <?php elseif($activeTab === 'voucher'): ?>
                                    <span class="italic text-slate-500 dark:text-slate-400">Pengguna Voucher</span>
                                <?php else: ?>
                                    <?php echo e($session->hotspotUser?->customerService?->customer?->name ?? 'Anonim / Tidak Ditemukan'); ?>

                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            </td>
                            <td class="px-4 py-3 text-slate-600 dark:text-slate-400 font-mono text-xs">
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($activeTab === 'pppoe'): ?>
                                    <?php echo e($session->name); ?>

                                <?php else: ?>
                                    <?php echo e($session->user); ?>

                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            </td>
                            <td class="px-4 py-3 text-slate-600 dark:text-slate-400">
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($activeTab === 'pppoe'): ?>
                                    <span class="inline-flex items-center gap-1"><span class="material-symbols-outlined notranslate text-slate-400" style="font-size:14px" translate="no">storefront</span> <?php echo e($session->pppoeUser?->customerService?->customer?->createdBy?->name ?? 'Admin'); ?></span>
                                <?php elseif($activeTab === 'voucher'): ?>
                                    <span class="inline-flex items-center gap-1"><span class="material-symbols-outlined notranslate text-slate-400" style="font-size:14px" translate="no">storefront</span> <?php echo e($session->voucher?->reseller?->name ?? 'Admin'); ?></span>
                                <?php else: ?>
                                    <span class="inline-flex items-center gap-1"><span class="material-symbols-outlined notranslate text-slate-400" style="font-size:14px" translate="no">storefront</span> <?php echo e($session->hotspotUser?->customerService?->customer?->createdBy?->name ?? 'Admin'); ?></span>
                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            </td>
                            <td class="px-4 py-3 text-slate-600 dark:text-slate-400">
                                <?php echo e($session->router?->name ?? '-'); ?>

                            </td>
                            <td class="px-4 py-3 text-slate-600 dark:text-slate-400 font-mono">
                                <?php echo e($session->address); ?>

                            </td>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($activeTab !== 'pppoe'): ?>
                                <td class="px-4 py-3 text-slate-600 dark:text-slate-400 font-mono">
                                    <?php echo e($session->mac_address ?? '-'); ?>

                                </td>
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            <td class="px-4 py-3 text-slate-600 dark:text-slate-400">
                                <span class="inline-flex items-center gap-1.5 px-2 py-1 rounded-md text-xs font-medium bg-emerald-100 text-emerald-700 dark:bg-emerald-900/50 dark:text-emerald-400">
                                    <span class="material-symbols-outlined notranslate" translate="no" style="font-size: 14px">schedule</span>
                                    <?php echo e($session->uptime); ?>

                                </span>
                            </td>
                            <td class="px-4 py-3 text-xs font-mono">
                                <span class="text-blue-600 dark:text-blue-400"><?php echo e(number_format($session->bytes_in)); ?></span> / 
                                <span class="text-green-600 dark:text-green-400"><?php echo e(number_format($session->bytes_out)); ?></span>
                            </td>
                            <td class="px-4 py-3 text-slate-600 dark:text-slate-400 text-xs">
                                <?php echo e($session->session_started_at ? $session->session_started_at->format('d/m/Y H:i:s') : '-'); ?>

                            </td>
                            <td class="px-4 py-3 text-center whitespace-nowrap">
                                <button 
                                    <?php if($activeTab === 'pppoe'): ?>
                                        wire:click="kickPppoe(<?php echo e($session->id); ?>)" 
                                    <?php else: ?>
                                        wire:click="kickHotspot(<?php echo e($session->id); ?>)" 
                                    <?php endif; ?>
                                    onclick="confirm('Yakin ingin memutuskan koneksi sesi ini?') || event.stopImmediatePropagation()"
                                    class="inline-flex items-center justify-center w-8 h-8 rounded-md bg-red-50 hover:bg-red-100 text-red-600 dark:text-red-400 dark:bg-red-900/20 dark:hover:bg-red-900/40 transition-colors" title="Kick / Putuskan Koneksi">
                                    <span class="material-symbols-outlined notranslate" translate="no" style="font-size:18px">power_settings_new</span>
                                </button>
                            </td>
                        </tr>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_0): ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                        <tr>
                            <td colspan="<?php echo e($activeTab === 'pppoe' ? 10 : 11); ?>" class="px-4 py-12 text-center text-slate-500 dark:text-slate-400">
                                <div class="flex flex-col items-center justify-center">
                                    <span class="material-symbols-outlined notranslate mb-2 text-slate-300 dark:text-slate-600 dark:text-slate-400" translate="no" style="font-size:48px">cell_tower</span>
                                    <p class="text-lg font-medium text-slate-900 dark:text-slate-100 mt-2">Belum ada Sesi Aktif</p>
                                    <p class="text-sm mt-1">Tidak ada pelanggan <?php echo e(ucfirst($activeTab)); ?> yang sedang online saat ini.</p>
                                </div>
                            </td>
                        </tr>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </tbody>
            </table>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($results->hasPages()): ?>
                <div class="px-4 py-3 border-t border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800/50">
                    <?php echo e($results->links()); ?>

                </div>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </div>
    </div>
</div><?php /**PATH D:\dsBilling\resources\views\livewire\isp\user-online\index.blade.php ENDPATH**/ ?>