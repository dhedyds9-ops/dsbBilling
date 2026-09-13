<?php $__env->startSection('page_title'); ?>
    <a href="<?php echo e(route('isp.odcs.index')); ?>" class="flex items-center text-slate-500 hover:text-slate-800 dark:text-slate-200 dark:hover:text-slate-100 transition-colors mr-2">
        <span class="material-symbols-outlined notranslate" translate="no">arrow_back</span>
    </a>
    <div class="w-8 h-8 rounded-full bg-indigo-100 dark:bg-indigo-900/50 flex items-center justify-center text-indigo-600 dark:text-indigo-400 font-bold text-sm mr-2">
        <?php echo e(substr($odc->name ?? 'U', 0, 1)); ?>

    </div>
    <span class="text-lg">Detail ODC</span>
<?php $__env->stopSection(); ?>

<div class="space-y-6 pb-10">
    
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-black text-slate-800 dark:text-slate-100"><?php echo e($odc->name); ?></h1>
            <div class="flex items-center gap-3 mt-1 text-sm text-slate-500 dark:text-slate-400">
                <span class="font-mono text-xs font-semibold text-indigo-600 dark:text-indigo-400"><?php echo e($odc->code); ?></span>
                <span class="px-2.5 py-0.5 font-semibold rounded-md text-xs bg-slate-100 text-slate-600 dark:bg-slate-800 dark:text-slate-400">
                    <?php echo e(ucfirst($odc->status)); ?>

                </span>
            </div>
        </div>
        <div class="flex items-center gap-2">
            <a href="<?php echo e(route('isp.odcs.edit', $odc->id)); ?>" class="inline-flex items-center gap-2 px-4 py-2 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-700 dark:text-slate-300 rounded-xl text-sm font-semibold shadow-sm hover:bg-slate-50 dark:bg-slate-900/50 dark:hover:bg-slate-700 transition-all cursor-pointer">
                <span class="material-symbols-outlined notranslate" translate="no" style="font-size:18px">edit</span>
                Edit
            </a>
            <button type="button" wire:click="delete" wire:confirm="Yakin hapus data ini?" class="inline-flex items-center gap-2 px-4 py-2 bg-red-50 dark:bg-red-900/30 text-red-600 dark:text-red-400 rounded-xl text-sm font-semibold hover:bg-red-100 dark:bg-red-900/50 dark:hover:bg-red-800/50 transition-all cursor-pointer">
                <span class="material-symbols-outlined notranslate" translate="no" style="font-size:18px">delete</span>
                Hapus
            </button>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <div class="bg-white dark:bg-slate-800 rounded-xl shadow-sm border border-slate-200 dark:border-slate-700 overflow-hidden"><div class="p-6">
             <?php $__env->slot('header', null, []); ?> 
                <h3 class="text-lg font-semibold text-slate-900 dark:text-slate-100">Informasi ODC</h3>
             <?php $__env->endSlot(); ?>
            <div class="space-y-4">
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-slate-500 dark:text-slate-400 mb-1">Kode</label>
                        <p class="text-slate-900 dark:text-slate-100 font-mono"><?php echo e($odc->code); ?></p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-500 dark:text-slate-400 mb-1">Nama</label>
                        <p class="text-slate-900 dark:text-slate-100"><?php echo e($odc->name); ?></p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-500 dark:text-slate-400 mb-1">OLT</label>
                        <p class="text-slate-900 dark:text-slate-100"><?php echo e($odc->olt->name ?? '-'); ?></p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-500 dark:text-slate-400 mb-1">POP</label>
                        <p class="text-slate-900 dark:text-slate-100"><?php echo e($odc->pop->name ?? '-'); ?></p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-500 dark:text-slate-400 mb-1">Jumlah Port</label>
                        <p class="text-slate-900 dark:text-slate-100"><?php echo e($odc->port_count ?? '-'); ?></p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-500 dark:text-slate-400 mb-1">Latitude</label>
                        <p class="text-slate-900 dark:text-slate-100 font-mono"><?php echo e($odc->latitude ?? '-'); ?></p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-500 dark:text-slate-400 mb-1">Longitude</label>
                        <p class="text-slate-900 dark:text-slate-100 font-mono"><?php echo e($odc->longitude ?? '-'); ?></p>
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-500 dark:text-slate-400 mb-1">Alamat</label>
                    <p class="text-slate-900 dark:text-slate-100"><?php echo e($odc->address ?? '-'); ?></p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-500 dark:text-slate-400 mb-1">Deskripsi</label>
                    <p class="text-slate-900 dark:text-slate-100"><?php echo e($odc->description ?? '-'); ?></p>
                </div>
            </div>
        </div></div>

        <div class="bg-white dark:bg-slate-800 rounded-xl shadow-sm border border-slate-200 dark:border-slate-700 overflow-hidden"><div class="p-6">
             <?php $__env->slot('header', null, []); ?> 
                <h3 class="text-lg font-semibold text-slate-900 dark:text-slate-100">Statistik</h3>
             <?php $__env->endSlot(); ?>
            <div class="space-y-4">
                <div class="grid grid-cols-2 gap-4 pt-4 border-t border-slate-200 dark:border-slate-700">
                    <div class="p-4 bg-slate-50 dark:bg-slate-900/50 rounded-lg">
                        <p class="text-sm text-slate-500 dark:text-slate-400">ODP</p>
                        <p class="text-2xl font-bold text-slate-900 dark:text-slate-100"><?php echo e($odc->odps->count()); ?></p>
                    </div>
                </div>
            </div>
        </div></div>
    </div>
</div>
<?php /**PATH D:\dsBilling\resources\views\livewire\isp\odc\show.blade.php ENDPATH**/ ?>