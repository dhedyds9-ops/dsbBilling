<?php $__env->startSection('page_title'); ?>
    <span class="material-symbols-outlined notranslate text-indigo-500" translate="no" style="font-size:24px">hub</span>
    <span class="text-lg">Data ODC</span>
<?php $__env->stopSection(); ?>

<div class="space-y-5 pb-10">

    
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(session('success')): ?>
        <div class="flex items-center gap-3 px-4 py-3 bg-emerald-50 dark:bg-emerald-900/30 border border-emerald-200 dark:border-emerald-700 rounded-xl text-emerald-700 dark:text-emerald-400 text-sm font-medium">
            <span class="material-symbols-outlined notranslate" translate="no" style="font-size:18px">check_circle</span>
            <?php echo e(session('success')); ?>

        </div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(session('error')): ?>
        <div class="flex items-center gap-3 px-4 py-3 bg-red-50 dark:bg-red-900/30 border border-red-200 dark:border-red-700 rounded-xl text-red-700 dark:text-red-400 text-sm font-medium">
            <span class="material-symbols-outlined notranslate" translate="no" style="font-size:18px">error</span>
            <?php echo e(session('error')); ?>

        </div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

    
    
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div class="flex items-center gap-2">
            <a href="<?php echo e(route('isp.odcs.create')); ?>" class="inline-flex items-center justify-center px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium rounded-lg shadow-sm shadow-indigo-200 dark:shadow-none transition-all">
                <span class="material-symbols-outlined notranslate mr-1.5" translate="no" style="font-size:18px">add</span>
                Tambah ODC
            </a>
            <button wire:click="export" class="inline-flex items-center justify-center px-4 py-2 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 hover:bg-slate-50 dark:bg-slate-900/50 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 text-sm font-medium rounded-lg shadow-sm transition-all">
                <span class="material-symbols-outlined notranslate mr-1.5" translate="no" style="font-size:18px">download</span>
                Export
            </button>
        </div>
        
        <div class="flex items-center gap-2">
            <div class="flex-1 relative">
                <span class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400">
                    <span class="material-symbols-outlined notranslate" translate="no" style="font-size:18px">search</span>
                </span>
                <input type="text" wire:model.live.debounce.300ms="search"
                       placeholder="Cari kode, nama..."
                       class="w-full pl-9 pr-4 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg text-sm text-slate-700 dark:text-slate-300 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all dark:bg-slate-900 dark:text-slate-100">
            </div>
            <select wire:model.live="filters.status"
                    class="pl-3 pr-10 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg text-sm text-slate-700 dark:text-slate-300 focus:outline-none focus:ring-2 focus:ring-indigo-500 cursor-pointer dark:bg-slate-900 dark:text-slate-100">
                <option value="" class="bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-200">Semua Status</option>
                <option value="active" class="bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-200">Aktif</option>
                <option value="inactive" class="bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-200">Nonaktif</option>
            </select>
            <select wire:model.live="perPage"
                    class="pl-3 pr-10 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg text-sm text-slate-700 dark:text-slate-300 focus:outline-none focus:ring-2 focus:ring-indigo-500 cursor-pointer dark:bg-slate-900 dark:text-slate-100">
                <option value="10" class="bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-200">10 / halaman</option>
                <option value="25" class="bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-200">25 / halaman</option>
                <option value="50" class="bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-200">50 / halaman</option>
                <option value="100" class="bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-200">100 / halaman</option>
            </select>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($search || $filters['status']): ?>
            <button wire:click="resetFilters"
                    class="inline-flex items-center gap-1.5 px-3 py-2 bg-red-50 dark:bg-red-900/30 hover:bg-red-100 dark:bg-red-900/50 dark:hover:bg-red-800/50 text-red-600 dark:text-red-400 rounded-lg text-sm font-medium transition-colors cursor-pointer">
                <span class="material-symbols-outlined notranslate" translate="no" style="font-size:16px">filter_alt_off</span>
                Reset
            </button>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </div>
    </div>
    
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(!empty($selectedODCs ?? $selectedODC ?? $selectedOdcs ?? $selectedOdps ?? $selectedOlts)): ?>
    <div class="flex flex-wrap items-center gap-2 p-3 bg-indigo-50 dark:bg-indigo-900/30 border border-indigo-100 dark:border-indigo-800 rounded-lg">
        <span class="text-sm text-indigo-800 dark:text-indigo-300 font-medium mr-2"><?php echo e(count($selectedOdcs ?? $selectedOdcs ?? $selectedOdps ?? $selectedOlts)); ?> Terpilih</span>
        <button wire:click="bulkActivate" class="px-3 py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-medium rounded transition-colors shadow-sm">Aktifkan</button>
        <button wire:click="bulkDeactivate" class="px-3 py-1.5 bg-amber-600 hover:bg-amber-700 text-white text-xs font-medium rounded transition-colors shadow-sm">Nonaktifkan</button>
        <button wire:click="bulkDelete" wire:confirm="Yakin hapus data terpilih?" class="px-3 py-1.5 bg-red-600 hover:bg-red-700 text-white text-xs font-medium rounded transition-colors shadow-sm">Hapus Terpilih</button>
    </div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
    

    <!-- Table -->
    <div class="bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                <thead class="bg-slate-50/80 dark:bg-slate-800/80">
                    <tr class="border-b border-slate-200 dark:border-slate-700 text-xs uppercase tracking-wider text-slate-500 dark:text-slate-400">
                        <th scope="col" class="px-6 py-3 text-left">
                            <input type="checkbox" wire:model.live="selectAll" class="w-4 h-4 text-primary-600 border-gray-300 dark:border-slate-600 rounded focus:ring-primary-500 dark:bg-slate-900 dark:text-slate-100" />
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-gray-500 dark:text-slate-400 uppercase tracking-wider">Status</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-gray-500 dark:text-slate-400 uppercase tracking-wider">Nama ODC</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-gray-500 dark:text-slate-400 uppercase tracking-wider">Alamat</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-gray-500 dark:text-slate-400 uppercase tracking-wider">Kode</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-gray-500 dark:text-slate-400 uppercase tracking-wider">OLT</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-gray-500 dark:text-slate-400 uppercase tracking-wider">POP</th>
                        <th scope="col" class="px-6 py-3 text-right text-xs font-semibold text-gray-500 dark:text-slate-400 uppercase tracking-wider">Aksi</th>
                    </tr>
                </thead>
                <tbody class="bg-white dark:bg-slate-800 divide-y divide-gray-200 dark:divide-gray-700">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__empty_0 = true; $__currentLoopData = $odcs; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $odc): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_0 = false; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                        <tr <?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::$currentLoop['key'] = ''.e($odc->id).''; ?>wire:key="<?php echo e($odc->id); ?>" class="hover:bg-gray-50 dark:hover:bg-slate-700/50 dark:bg-slate-800 dark:hover:bg-slate-700/50 dark:bg-slate-800 transition-colors <?php if($odc->trashed()): ?> bg-red-50 dark:bg-red-900/30 <?php endif; ?>">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <input type="checkbox" wire:model.live="selectedOdcs" value="<?php echo e($odc->id); ?>" class="w-4 h-4 text-primary-600 border-gray-300 dark:border-slate-600 rounded focus:ring-primary-500" <?php if($odc->trashed()): ?> disabled <?php endif; ?> />
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($odc->trashed()): ?>
                                    <span class="px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 dark:bg-red-900/50 text-red-800">Dihapus</span>
                                <?php else: ?>
                                    <span class="px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full <?php echo e($odc->status === 'active' ? 'bg-green-100 text-green-800' : 'bg-gray-100 dark:bg-slate-700 text-gray-800 dark:text-gray-200'); ?>">
                                        <?php echo e($odc->status === 'active' ? 'Aktif' : 'Nonaktif'); ?>

                                    </span>
                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-semibold text-gray-900 dark:text-slate-100">
                                    <a href="<?php echo e(route('isp.odcs.show', $odc->id)); ?>" class="hover:text-primary-600 transition-colors">
                                        <?php echo e($odc->name); ?>

                                    </a>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-500 dark:text-slate-400 truncate max-w-[200px]" title="<?php echo e($odc->address); ?>"><?php echo e($odc->address ?: '-'); ?></div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="font-mono text-sm text-gray-900 dark:text-slate-100"><?php echo e($odc->code); ?></span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-medium text-gray-900 dark:text-slate-100"><?php echo e($odc->olt->name ?? '-'); ?></div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-600 dark:text-gray-400"><?php echo e($odc->pop->name ?? '-'); ?></div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                <div class="flex items-center justify-end gap-2">
                                    <a href="<?php echo e(route('isp.odcs.show', $odc->id)); ?>" class="p-1.5 bg-indigo-50 dark:bg-indigo-900/30 text-indigo-600 rounded hover:bg-indigo-100 dark:bg-indigo-900/50 transition-colors" title="Detail">
                                        <span class="material-symbols-outlined notranslate" translate="no" style="font-size:16px">visibility</span>
                                    </a>
                                    <a href="<?php echo e(route('isp.odcs.edit', $odc->id)); ?>" class="p-1.5 bg-amber-50 dark:bg-amber-900/30 text-amber-600 rounded hover:bg-amber-100 dark:bg-amber-900/50 transition-colors" title="Edit">
                                        <span class="material-symbols-outlined notranslate" translate="no" style="font-size:16px">edit</span>
                                    </a>
                                    <button type="button" wire:click="delete(<?php echo e($odc->id); ?>)" wire:confirm="Yakin ingin menghapus ODC ini?" class="p-1.5 bg-red-50 dark:bg-red-900/30 text-red-600 rounded hover:bg-red-100 dark:bg-red-900/50 transition-colors" title="Hapus">
                                        <span class="material-symbols-outlined notranslate" translate="no" style="font-size:16px">delete</span>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_0): ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                        <tr>
                            <td colspan="10" class="px-6 py-16 text-center">
                                <div class="flex flex-col items-center justify-center">
                                    <div class="w-16 h-16 bg-gray-100 dark:bg-slate-700 rounded-full flex items-center justify-center mb-4">
                                        <svg class="w-8 h-8 text-gray-400 dark:text-slate-500 dark:text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                                        </svg>
                                    </div>
                                    <h3 class="text-lg font-semibold text-gray-900 dark:text-slate-100 mb-1">Belum ada ODC</h3>
                                    <p class="text-gray-500 dark:text-slate-400 mb-6">Silakan tambahkan ODC pertama.</p>
                                    <a href="<?php echo e(route('isp.odcs.create')); ?>" class="inline-flex items-center gap-2 px-5 py-2.5 bg-primary-600 text-white rounded-lg hover:bg-primary-700 transition-colors font-medium">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                                        </svg>
                                        Tambah ODC
                                    </a>
                                </div>
                            </td>
                        </tr>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </tbody>
            </table>
        </div>

        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($odcs->hasPages()): ?>
            <div class="bg-white dark:bg-slate-800 px-6 py-4 border-t border-gray-200 dark:border-slate-700">
                <?php echo e($odcs->links()); ?>

            </div>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
    </div>

    <!-- Delete Confirmation Modal -->
    
</div><?php /**PATH D:\dsBilling\resources\views\livewire\isp\odc\index.blade.php ENDPATH**/ ?>