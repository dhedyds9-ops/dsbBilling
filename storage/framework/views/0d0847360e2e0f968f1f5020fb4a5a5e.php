<div>
    <?php $__env->startSection('page_title'); ?>
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl bg-indigo-500/10 flex items-center justify-center text-indigo-600 dark:text-indigo-400">
                <span class="material-symbols-outlined notranslate" translate="no">confirmation_number</span>
            </div>
            <div>
                <h1 class="text-xl font-bold text-slate-900 dark:text-slate-100 dark:text-white leading-tight">Voucher</h1>
            </div>
        </div>
    <?php $__env->stopSection(); ?>

    

    <div class="space-y-4">
        
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            
            <div wire:click="$set('filters.status', '')" class="relative overflow-x-auto rounded-xl border border-indigo-200 dark:border-indigo-800/60 shadow-md bg-gradient-to-br from-indigo-50 to-white dark:from-indigo-950/50 dark:to-slate-800 group hover:shadow-lg transition-all cursor-pointer">
                <div class="absolute top-0 left-0 right-0 h-1 bg-gradient-to-r from-indigo-500 to-blue-400 rounded-t-xl"></div>
                <div class="absolute top-3 right-3 opacity-10 text-indigo-400 group-hover:opacity-20 group-hover:scale-110 transition-all">
                    <span class="material-symbols-outlined notranslate" translate="no" style="font-size:56px">confirmation_number</span>
                </div>
                <div class="p-4 pt-5">
                    <h3 class="text-xs font-bold text-indigo-500 uppercase tracking-widest mb-2">Total Voucher</h3>
                    <div class="text-4xl font-black text-indigo-700 dark:text-indigo-300 mb-3"><?php echo e(number_format($stats['total'] ?? 0)); ?></div>
                    <div class="text-xs text-slate-500 dark:text-slate-400">Klik untuk memfilter</div>
                </div>
            </div>

            
            <div wire:click="$set('filters.status', 'available')" class="relative overflow-x-auto rounded-xl border border-emerald-200 dark:border-emerald-800/60 shadow-md bg-gradient-to-br from-emerald-50 to-white dark:from-emerald-950/50 dark:to-slate-800 group hover:shadow-lg transition-all cursor-pointer">
                <div class="absolute top-0 left-0 right-0 h-1 bg-gradient-to-r from-emerald-500 to-teal-400 rounded-t-xl"></div>
                <div class="absolute top-3 right-3 opacity-10 text-emerald-400 group-hover:opacity-20 group-hover:scale-110 transition-all">
                    <span class="material-symbols-outlined notranslate" translate="no" style="font-size:56px">check_circle</span>
                </div>
                <div class="p-4 pt-5">
                    <h3 class="text-xs font-bold text-emerald-500 uppercase tracking-widest mb-2">Tersedia</h3>
                    <div class="text-4xl font-black text-emerald-700 dark:text-emerald-300 mb-3"><?php echo e(number_format($stats['available'] ?? 0)); ?></div>
                    <div class="text-xs text-slate-500 dark:text-slate-400">Klik untuk memfilter</div>
                </div>
            </div>

            
            <div wire:click="$set('filters.status', 'used')" class="relative overflow-x-auto rounded-xl border border-amber-200 dark:border-amber-800/60 shadow-md bg-gradient-to-br from-amber-50 to-white dark:from-amber-950/50 dark:to-slate-800 group hover:shadow-lg transition-all cursor-pointer">
                <div class="absolute top-0 left-0 right-0 h-1 bg-gradient-to-r from-amber-500 to-orange-400 rounded-t-xl"></div>
                <div class="absolute top-3 right-3 opacity-10 text-amber-400 group-hover:opacity-20 group-hover:scale-110 transition-all">
                    <span class="material-symbols-outlined notranslate" translate="no" style="font-size:56px">person_check</span>
                </div>
                <div class="p-4 pt-5">
                    <h3 class="text-xs font-bold text-amber-500 uppercase tracking-widest mb-2">Terpakai</h3>
                    <div class="text-4xl font-black text-amber-700 dark:text-amber-300 mb-3"><?php echo e(number_format($stats['used'] ?? 0)); ?></div>
                    <div class="text-xs text-slate-500 dark:text-slate-400">Klik untuk memfilter</div>
                </div>
            </div>

            
            <div wire:click="$set('filters.status', 'expired')" class="relative overflow-x-auto rounded-xl border border-red-200 dark:border-red-800/60 shadow-md bg-gradient-to-br from-red-50 to-white dark:from-red-950/50 dark:to-slate-800 group hover:shadow-lg transition-all cursor-pointer">
                <div class="absolute top-0 left-0 right-0 h-1 bg-gradient-to-r from-red-500 to-rose-400 rounded-t-xl"></div>
                <div class="absolute top-3 right-3 opacity-10 text-red-400 group-hover:opacity-20 group-hover:scale-110 transition-all">
                    <span class="material-symbols-outlined notranslate" translate="no" style="font-size:56px">hourglass_disabled</span>
                </div>
                <div class="p-4 pt-5">
                    <h3 class="text-xs font-bold text-red-500 uppercase tracking-widest mb-2">Kedaluwarsa</h3>
                    <div class="text-4xl font-black text-red-700 dark:text-red-300 mb-3"><?php echo e(number_format($stats['expired'] ?? 0)); ?></div>
                    <div class="text-xs text-slate-500 dark:text-slate-400">Klik untuk memfilter</div>
                </div>
            </div>
        </div>

        
        <div class="flex flex-col sm:flex-row gap-3 items-center justify-between bg-white dark:bg-slate-800 p-4 rounded-xl shadow-sm border border-slate-200 dark:border-slate-700">
            <div class="flex-1 w-full relative">
                <span class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400">
                    <span class="material-symbols-outlined notranslate" translate="no" style="font-size: 20px">search</span>
                </span>
                <input type="text" wire:model.live.debounce.300ms="search" placeholder="Cari kode voucher / pelanggan..." class="w-full pl-9 pr-4 py-2 bg-slate-50 dark:bg-slate-900/50 border border-slate-200 dark:border-slate-700 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 transition-all dark:bg-slate-900 dark:text-slate-100">
            </div>
            <div class="flex items-center gap-2 w-full sm:w-auto">
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(count($selectedIds) > 0): ?>
                    <button onclick="confirm('Yakin ingin menghapus <?php echo e(count($selectedIds)); ?> voucher?') || event.stopImmediatePropagation()" wire:click="confirmBulkDelete" class="inline-flex items-center px-4 py-2 bg-red-50 dark:bg-red-900/30 hover:bg-red-100 dark:bg-red-900/50 text-red-600 dark:text-red-400 text-sm font-medium rounded-lg transition-colors">
                        <span class="material-symbols-outlined notranslate mr-1.5" translate="no" style="font-size: 18px">delete</span>
                        Hapus (<?php echo e(count($selectedIds)); ?>)
                    </button>
                    <button wire:click="printSelected" class="inline-flex items-center px-4 py-2 bg-slate-50 dark:bg-slate-900/50 hover:bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400 text-sm font-medium rounded-lg border border-slate-200 dark:border-slate-700 transition-colors">
                        <span class="material-symbols-outlined notranslate mr-1.5" translate="no" style="font-size: 18px">print</span>
                        Cetak (<?php echo e(count($selectedIds)); ?>)
                    </button>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                <button wire:click="$set('showGenerateModal', true)" class="inline-flex items-center px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium rounded-lg shadow-sm transition-colors">
                    <span class="material-symbols-outlined notranslate mr-1.5" translate="no" style="font-size: 18px">add</span>
                    Generate Voucher
                </button>
            </div>
            <div class="flex items-center gap-3 w-full sm:w-auto">
                <select wire:model.live="filters.voucher_pool_id" class="pl-3 pr-10 py-2 bg-slate-50 dark:bg-slate-900/50 border border-slate-200 dark:border-slate-700 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 cursor-pointer dark:bg-slate-900 dark:text-slate-100">
                    <option value="">Semua Tanggal Buat</option>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $voucherPools; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $pool): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                        <option value="<?php echo e($pool->id); ?>">vc-<?php echo e($pool->created_at->format('d-m-Y-H:i:s')); ?></option>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                </select>
                <select wire:model.live="filters.status" class="pl-3 pr-10 py-2 bg-slate-50 dark:bg-slate-900/50 border border-slate-200 dark:border-slate-700 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 cursor-pointer dark:bg-slate-900 dark:text-slate-100">
                    <option value="">Semua Status</option>
                    <option value="available">Tersedia</option>
                    <option value="used">Sudah Dipakai</option>
                    <option value="expired">Kedaluwarsa</option>
                </select>
                <select wire:model.live="perPage" class="pl-3 pr-10 py-2 bg-slate-50 dark:bg-slate-900/50 border border-slate-200 dark:border-slate-700 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 cursor-pointer dark:bg-slate-900 dark:text-slate-100">
                    <option value="20">20 / halaman</option>
                    <option value="50">50 / halaman</option>
                    <option value="100">100 / halaman</option>
                </select>
            </div>
        </div>

        
        <div class="bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-slate-200 dark:border-slate-700 text-xs uppercase tracking-wider text-slate-500 dark:text-slate-400 bg-slate-50 dark:bg-slate-900/80">
                            <th class="px-4 py-3 text-left font-semibold">
                                <input type="checkbox" wire:model.live="selectAll" class="rounded border-slate-300 dark:border-slate-600 text-indigo-600 dark:text-indigo-400 shadow-sm focus:ring-indigo-500 dark:bg-slate-900 dark:text-slate-100">
                            </th>
                            <th class="px-4 py-3 text-left font-semibold whitespace-nowrap">Id</th>
                            <th class="px-4 py-3 text-left font-semibold whitespace-nowrap">Username</th>
                            <th class="px-4 py-3 text-left font-semibold whitespace-nowrap">Password</th>
                            <th class="px-4 py-3 text-left font-semibold whitespace-nowrap">Nama Profil</th>
                            <th class="px-4 py-3 text-left font-semibold whitespace-nowrap">Harga Jual</th>
                            <th class="px-4 py-3 text-left font-semibold whitespace-nowrap">Nama Server</th>
                            <th class="px-4 py-3 text-left font-semibold whitespace-nowrap">Service</th>
                            <th class="px-4 py-3 text-left font-semibold whitespace-nowrap">Tanggal Dibuat</th>
                            <th class="px-4 py-3 text-left font-semibold whitespace-nowrap">Jatuh Tempo</th>
                            <th class="px-4 py-3 text-left font-semibold whitespace-nowrap">Owner Data</th>
                            <th class="px-4 py-3 text-center font-semibold whitespace-nowrap">Status</th>
                            <th class="px-4 py-3 text-right font-semibold whitespace-nowrap">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-700">
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__empty_0 = true; $__currentLoopData = $vouchers; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $voucher): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_0 = false; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                            <?php
                                $isAvailable = $voucher->status === 'available';
                                $isUsed = $voucher->status === 'used';
                                $isExpired = $voucher->status === 'expired';
                                
                                $rowBg = 'hover:bg-slate-50 dark:bg-slate-800/50 transition-colors';
                                $textPrimary = 'text-slate-900 dark:text-slate-100 font-medium';
                                $textSecondary = 'text-slate-600 dark:text-slate-400';
                                $idColor = 'text-slate-700 dark:text-slate-300';
                                
                                if ($isUsed) {
                                    $rowBg = 'bg-amber-50/30 dark:bg-amber-900/10 hover:bg-amber-100 dark:bg-amber-900/50/50 dark:hover:bg-amber-900/20 transition-colors';
                                    $textPrimary = 'text-amber-900 dark:text-amber-100 font-semibold';
                                    $textSecondary = 'text-amber-700 dark:text-amber-300';
                                    $idColor = 'text-amber-800 dark:text-amber-200';
                                } elseif ($isExpired) {
                                    $rowBg = 'bg-red-50/40 dark:bg-red-900/10 hover:bg-red-100 dark:bg-red-900/50/60 dark:hover:bg-red-900/20 transition-colors grayscale-[20%]';
                                    $textPrimary = 'text-red-900 dark:text-red-100 font-semibold';
                                    $textSecondary = 'text-red-700 dark:text-red-300';
                                    $idColor = 'text-red-800 dark:text-red-200';
                                } elseif ($isAvailable) {
                                    $rowBg = 'bg-emerald-50/30 dark:bg-emerald-900/10 hover:bg-emerald-100 dark:bg-emerald-900/50/50 dark:hover:bg-emerald-900/20 transition-colors';
                                    $textPrimary = 'text-emerald-900 dark:text-emerald-100 font-semibold';
                                    $textSecondary = 'text-emerald-700 dark:text-emerald-300';
                                    $idColor = 'text-emerald-800 dark:text-emerald-200';
                                }
                            ?>
                            <tr class="<?php echo e($rowBg); ?>">
                                <td class="px-4 py-3">
                                    <input type="checkbox" wire:model.live="selectedIds" value="<?php echo e($voucher->id); ?>" class="rounded border-slate-300 dark:border-slate-600 text-indigo-600 dark:text-indigo-400 shadow-sm focus:ring-indigo-500">
                                </td>
                                <td class="px-4 py-3 <?php echo e($textSecondary); ?> font-mono text-xs">
                                    <?php echo e($voucher->id); ?>

                                </td>
                                <td class="px-4 py-3 <?php echo e($textPrimary); ?> font-mono text-base tracking-wider">
                                    <?php echo e($voucher->login_method === 'username_password' ? ($voucher->hotspotUser?->username ?? $voucher->code) : $voucher->code); ?>

                                </td>
                                <td class="px-4 py-3 <?php echo e($textSecondary); ?> font-mono tracking-wider">
                                    <?php echo e($voucher->login_method === 'username_password' ? ($voucher->hotspotUser?->cleartext_password ?? '***') : $voucher->code); ?>

                                </td>
                                <td class="px-4 py-3 <?php echo e($textSecondary); ?>">
                                    <?php echo e($voucher->serviceProfile?->name ?? '-'); ?>

                                </td>
                                <td class="px-4 py-3 <?php echo e($textSecondary); ?> font-mono text-xs">
                                    Rp <?php echo e(number_format($voucher->serviceProfile?->price ?? 0, 0, ',', '.')); ?>

                                </td>
                                <td class="px-4 py-3 <?php echo e($textSecondary); ?> text-xs">
                                    <?php echo e($voucher->nasDevice?->name ?? 'Semua Server'); ?>

                                </td>
                                <td class="px-4 py-3 <?php echo e($textSecondary); ?> text-xs uppercase">
                                    <?php echo e($voucher->type ?? 'HOTSPOT'); ?>

                                </td>
                                <td class="px-4 py-3 text-xs font-mono">
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($voucher->created_at): ?>
                                        <button type="button" wire:click="printSingle(<?php echo e($voucher->id); ?>)" class="text-indigo-600 dark:text-indigo-400 hover:text-indigo-800 dark:hover:text-indigo-300 hover:underline transition-colors text-left font-medium" title="Cetak Batch ini">
                                            vc-<?php echo e($voucher->created_at->format('d-m-Y-H:i:s')); ?>

                                        </button>
                                    <?php else: ?>
                                        -
                                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                </td>
                                <td class="px-4 py-3 <?php echo e($textSecondary); ?> text-xs">
                                    <?php echo e($voucher->expires_at ? $voucher->expires_at->format('d-m-Y') : '-'); ?>

                                </td>
                                <td class="px-4 py-3 <?php echo e($textSecondary); ?>">
                                    <?php echo e($voucher->reseller?->name ?? 'Pusat (HQ)'); ?>

                                </td>
                                <td class="px-4 py-3 text-center whitespace-nowrap">
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($isAvailable): ?>
                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md text-xs font-bold bg-green-500 text-white shadow-sm">
                                            <span class="w-1.5 h-1.5 rounded-full bg-white dark:bg-slate-800"></span>
                                            Tersedia
                                        </span>
                                    <?php elseif($isUsed): ?>
                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md text-xs font-bold bg-amber-500 text-white shadow-sm">
                                            <span class="w-1.5 h-1.5 rounded-full bg-amber-200"></span>
                                            Terpakai
                                        </span>
                                    <?php elseif($isExpired): ?>
                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md text-xs font-bold bg-red-500 text-white shadow-sm">
                                            <span class="w-1.5 h-1.5 rounded-full bg-red-200"></span>
                                            Expired
                                        </span>
                                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                </td>
                                <td class="px-4 py-3 text-right whitespace-nowrap">
                                    <div class="flex items-center justify-end gap-1">
                                        <button wire:click="printSingle(<?php echo e($voucher->id); ?>)" class="inline-flex items-center justify-center w-8 h-8 rounded-md bg-emerald-50 dark:bg-emerald-900/30 hover:bg-emerald-100 dark:bg-emerald-900/50 text-emerald-600 dark:text-emerald-400 transition-colors" title="Cetak Voucher & Batch">
                                            <span class="material-symbols-outlined notranslate" translate="no" style="font-size:18px">print</span>
                                        </button>

                                        <button wire:click="delete(<?php echo e($voucher->id); ?>)" onclick="confirm('Yakin ingin menghapus voucher ini?') || event.stopImmediatePropagation()" class="inline-flex items-center justify-center w-8 h-8 rounded-md bg-red-50 dark:bg-red-900/30 hover:bg-red-100 dark:bg-red-900/50 text-red-600 dark:text-red-400 transition-colors" title="Hapus">
                                            <span class="material-symbols-outlined notranslate" translate="no" style="font-size:18px">delete</span>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_0): ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                            <tr>
                                <td colspan="12" class="px-4 py-8 text-center text-slate-500 dark:text-slate-400">
                                    <div class="flex flex-col items-center justify-center">
                                        <span class="material-symbols-outlined notranslate mb-2 text-slate-300" translate="no" style="font-size:48px">confirmation_number</span>
                                        <p>Belum ada data Voucher</p>
                                    </div>
                                </td>
                            </tr>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </tbody>
                </table>
            </div>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($vouchers->hasPages()): ?>
                <div class="px-4 py-3 border-t border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800/50">
                    <?php echo e($vouchers->links()); ?>

                </div>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </div>
    </div>

<!-- Modal Generate Voucher -->
    <div x-data="{ show: <?php if ((object) ('showGenerateModal') instanceof \Livewire\WireDirective) : ?>window.Livewire.find('<?php echo e($__livewire->getId()); ?>').entangle('<?php echo e('showGenerateModal'->value()); ?>')<?php echo e('showGenerateModal'->hasModifier('live') ? '.live' : ''); ?><?php else : ?>window.Livewire.find('<?php echo e($__livewire->getId()); ?>').entangle('<?php echo e('showGenerateModal'); ?>')<?php endif; ?> }" x-show="show" class="relative z-50" aria-labelledby="modal-title" role="dialog" aria-modal="true" style="display: none;">
        <div x-show="show" x-transition.opacity class="fixed inset-0 bg-slate-900/75 backdrop-blur-sm transition-opacity"></div>
        <div class="fixed inset-0 z-10 w-screen overflow-y-auto">
            <div class="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0">
                <div x-show="show" x-transition.scale.origin.bottom sm.origin.center class="relative transform overflow-x-auto rounded-xl bg-white dark:bg-slate-800 text-left shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-2xl border border-slate-200 dark:border-slate-700">
                    <form wire:submit.prevent="generate">
                        <div class="px-6 py-4 border-b border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-900/50 flex justify-between items-center">
                            <h3 class="text-lg font-bold text-slate-900 dark:text-slate-100" id="modal-title">Generate Voucher</h3>
                            <button type="button" x-on:click="show = false" class="text-slate-400 hover:text-slate-500 dark:text-slate-400 dark:hover:text-slate-300">
                                <span class="material-symbols-outlined notranslate" translate="no" style="font-size: 24px">close</span>
                            </button>
                        </div>
                        <div class="px-6 py-6 space-y-4">
                            <!-- Paket -->
                            <div>
                                <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Paket Internet</label>
                                <select wire:model="service_profile_id" class="w-full border-slate-300 dark:border-slate-600 dark:bg-slate-700 dark:text-slate-200 rounded-lg focus:ring-indigo-500 focus:border-indigo-500 p-2 border outline-none dark:bg-slate-900 dark:text-slate-100">
                                    <option value="">Pilih Paket</option>
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $serviceProfiles; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $profile): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                                        <option value="<?php echo e($profile->id); ?>"><?php echo e($profile->name); ?></option>
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                                </select>
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['service_profile_id'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <span class="text-red-500 text-xs"><?php echo e($message); ?></span> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                
                                <!-- Server -->
                                <div>
                                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Server / Router</label>
                                    <select wire:model="nas_device_id" class="w-full border-slate-300 dark:border-slate-600 dark:bg-slate-700 dark:text-slate-200 rounded-lg focus:ring-indigo-500 focus:border-indigo-500 p-2 border outline-none dark:bg-slate-900 dark:text-slate-100">
                                        <option value="">Semua Server</option>
                                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $nasDevices; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $nas): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                                            <option value="<?php echo e($nas->id); ?>"><?php echo e($nas->name); ?></option>
                                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                                    </select>
                                </div>
                                <!-- Kombinasi Kode -->
                                <div>
                                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Kombinasi Kode</label>
                                    <select wire:model="code_combination" class="w-full border-slate-300 dark:border-slate-600 dark:bg-slate-700 dark:text-slate-200 rounded-lg focus:ring-indigo-500 focus:border-indigo-500 p-2 border outline-none dark:bg-slate-900 dark:text-slate-100">
                                        <option value="uppercase_alphanumeric">Huruf Besar & Angka</option>
                                        <option value="uppercase">Hanya Huruf Besar</option>
                                        <option value="lowercase">Hanya Huruf Kecil</option>
                                        <option value="numbers">Hanya Angka</option>
                                        <option value="alphanumeric">Huruf Besar, Kecil & Angka</option>
                                    </select>
                                </div>
                                <!-- Metode Login -->
                                <div>
                                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Metode Login</label>
                                    <select wire:model="login_method" class="w-full border-slate-300 dark:border-slate-600 dark:bg-slate-700 dark:text-slate-200 rounded-lg focus:ring-indigo-500 focus:border-indigo-500 p-2 border outline-none dark:bg-slate-900 dark:text-slate-100">
                                        <option value="voucher_code">Kode Voucher (Otomatis)</option>
                                        <option value="username_password">Username & Password</option>
                                    </select>
                                </div>
                                <!-- Jumlah -->
                                <div>
                                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Jumlah Voucher</label>
                                    <input type="number" wire:model="quantity" min="1" max="5000" class="w-full border-slate-300 dark:border-slate-600 dark:bg-slate-700 dark:text-slate-200 rounded-lg focus:ring-indigo-500 focus:border-indigo-500 p-2 border outline-none dark:bg-slate-900 dark:text-slate-100">
                                </div>
                                <!-- Panjang -->
                                <div>
                                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Panjang Karakter</label>
                                    <input type="number" wire:model="length" min="4" max="32" class="w-full border-slate-300 dark:border-slate-600 dark:bg-slate-700 dark:text-slate-200 rounded-lg focus:ring-indigo-500 focus:border-indigo-500 p-2 border outline-none dark:bg-slate-900 dark:text-slate-100">
                                </div>
                                <!-- Prefix -->
                                <div>
                                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Awalan (Prefix)</label>
                                    <input type="text" wire:model="prefix" placeholder="VC-" class="w-full border-slate-300 dark:border-slate-600 dark:bg-slate-700 dark:text-slate-200 rounded-lg focus:ring-indigo-500 focus:border-indigo-500 p-2 border outline-none dark:bg-slate-900 dark:text-slate-100">
                                </div>
                            </div>
                        </div>
                        <div class="bg-slate-50 dark:bg-slate-900/50 px-6 py-4 border-t border-slate-200 dark:border-slate-700 flex justify-end gap-2 rounded-b-xl">
                            <button type="button" x-on:click="show = false" class="px-4 py-2 bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-600 text-slate-700 dark:text-slate-300 rounded-lg hover:bg-slate-50 dark:bg-slate-900/50 dark:hover:bg-slate-700 font-medium transition-colors">Batal</button>
                            <button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 font-medium transition-colors flex items-center gap-1.5">
                                <span class="material-symbols-outlined notranslate text-sm" translate="no">magic_button</span> Generate
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

<?php $__env->startPush('scripts'); ?>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    document.addEventListener('livewire:init', () => {
                Livewire.on('voucher-generated-sweetalert', (data) => {
            const count = data && data.count ? data.count : (data[0] ? data[0].count : 0);
            const ids = data && data.ids ? data.ids : (data[0] ? data[0].ids : '');
            const isDark = document.documentElement.classList.contains('dark');
            Swal.fire({
                title: 'Berhasil!',
                width: '25rem',
                background: isDark ? '#1e293b' : '#fff',
                color: isDark ? '#f8fafc' : '#0f172a',
                text: count + ' voucher berhasil dibuat. Apakah Anda ingin langsung mencetaknya?',
                icon: 'success',
                showCancelButton: true,
                confirmButtonText: '<span class="material-symbols-outlined notranslate mr-1" translate="no" style="font-size: 18px; vertical-align: text-bottom;">print</span> Cetak Sekarang',
                cancelButtonText: 'Tutup',
                confirmButtonColor: '#4f46e5',
            }).then((result) => {
                if (result.isConfirmed) {
                    Livewire.dispatch('open-print-window', [{ ids: ids }]);
                }
            });
        });

        Livewire.on('open-print-window', (data) => {
            console.log("Print Event Triggered", data);
            
            let ids = "";
            if (data && data.ids) ids = data.ids;
            else if (data && data[0] && data[0].ids) ids = data[0].ids;
            else if (data && data[0] && typeof data[0] === "string") ids = data[0];
            else if (typeof data === "string") ids = data;
            
            if (!ids) {
                console.error("Could not find ids in data:", data);
                ids = Array.isArray(data) ? data.join(",") : data;
            }
            
            const isDark = document.documentElement.classList.contains('dark');
            Swal.fire({
                title: 'Cetak Voucher',
                width: '25rem',
                background: isDark ? '#1e293b' : '#fff',
                color: isDark ? '#f8fafc' : '#0f172a',
                html: `
                    <form id="printVoucherForm" method="POST" action="<?php echo e(route('reseller-portal.sales.voucher.print')); ?>" target="_blank">
                        <?php echo csrf_field(); ?>
                        <input type="hidden" name="ids_string" value="${ids}">
                        <div class="mb-4 text-left">
                            <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Pilih Template</label>
                            <select name="template_id" class="w-full border-slate-300 dark:border-slate-600 dark:bg-slate-700 dark:text-slate-200 rounded-lg focus:ring-indigo-500 focus:border-indigo-500 p-2 border outline-none dark:bg-slate-900 dark:text-slate-100">
                                <option value="">Gunakan Default Template</option>
                                <?php $__currentLoopData = \App\Models\ISP\VoucherTemplate::where('is_active', true)->get(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $tpl): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <option value="<?php echo e($tpl->id); ?>"><?php echo e($tpl->name); ?> <?php echo e($tpl->is_default ? '(Default)' : ''); ?></option>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </select>
                        </div>
                        <div id="preview-container"></div>
                    </form>
                `,
                showCancelButton: true,
                confirmButtonText: '<span class="material-symbols-outlined notranslate mr-1" translate="no" style="font-size: 18px; vertical-align: text-bottom;">print</span> Cetak Sekarang',
                cancelButtonText: 'Batal',
                confirmButtonColor: '#4f46e5',
                didOpen: () => {
                    const select = Swal.getPopup().querySelector('select[name="template_id"]');
                    select.addEventListener('change', (e) => {
                        const templateId = e.target.value;
                        if(templateId) {
                            fetch(`/reseller-portal/sales/voucher/preview-template/${templateId}`)
                                .then(res => res.json())
                                .then(htmlData => {
                                    document.getElementById('preview-container').innerHTML = htmlData.html;
                                });
                        } else {
                            document.getElementById('preview-container').innerHTML = '';
                        }
                    });
                },
                preConfirm: () => {
                    document.getElementById('printVoucherForm').submit();
                    return true;
                }
            });
        });
    });
</script>
<?php $__env->stopPush(); ?>
</div>
<?php /**PATH D:\dsBilling\resources\views\livewire\reseller-portal\sales\voucher.blade.php ENDPATH**/ ?>