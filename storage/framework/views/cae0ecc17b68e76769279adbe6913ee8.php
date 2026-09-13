<div>
  <?php
    ob_start();
  ?>
    <a href="<?php echo e(route('acs.firmware.create')); ?>" class="inline-flex items-center justify-center px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium rounded-lg shadow-sm shadow-indigo-200 dark:shadow-none transition-all">
      <span class="material-symbols-outlined notranslate mr-1.5" translate="no" style="font-size:18px">upload</span>
      Upload Firmware
    </a>
  <?php
    $actions = ob_get_clean();
  ?>
  
  <?php echo $__env->make('livewire.acs._tabs', ['actions' => $actions], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

  <div class="space-y-5 pb-10">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div class="flex items-center gap-2">
            <div class="flex-1 relative">
                <span class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400">
                    <span class="material-symbols-outlined notranslate" translate="no" style="font-size:18px">search</span>
                </span>
                <input type="text" wire:model.live.debounce.300ms="search"
                       placeholder="Cari firmware..."
                       class="w-full pl-9 pr-4 py-2 bg-slate-50 dark:bg-slate-900/50 border border-slate-200 dark:border-slate-700 rounded-lg text-sm text-slate-700 dark:text-slate-300 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all dark:bg-slate-900 dark:text-slate-100">
            </div>
            
            <select wire:model.live="perPage"
                    class="pl-3 pr-8 py-2 bg-slate-50 dark:bg-slate-900/50 border border-slate-200 dark:border-slate-700 rounded-lg text-sm text-slate-700 dark:text-slate-300 focus:outline-none focus:ring-2 focus:ring-indigo-500 cursor-pointer dark:bg-slate-900 dark:text-slate-100">
                <option value="10">10 / halaman</option>
                <option value="25">25 / halaman</option>
                <option value="50">50 / halaman</option>
            </select>
        </div>
    </div>

    
    <div class="bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm text-left">
                <thead>
                    <tr class="border-b border-slate-200 dark:border-slate-700 text-xs uppercase tracking-wider text-slate-500 dark:text-slate-400 bg-slate-50/80 dark:bg-slate-800/80">
                        <th class="px-4 py-3 font-semibold whitespace-nowrap">File Name</th>
                        <th class="px-4 py-3 font-semibold whitespace-nowrap">Version</th>
                        <th class="px-4 py-3 font-semibold whitespace-nowrap">Product Class</th>
                        <th class="px-4 py-3 font-semibold whitespace-nowrap">Size</th>
                        <th class="px-4 py-3 font-semibold whitespace-nowrap">Uploaded</th>
                        <th class="px-4 py-3 font-semibold whitespace-nowrap text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-700/50 text-slate-700 dark:text-slate-300">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $firmwares ?? []; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $fw): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                        <tr class="hover:bg-slate-50 dark:bg-slate-900/50/50 dark:hover:bg-slate-800/50 transition-colors">
                            <td class="px-4 py-3 font-medium text-slate-900 dark:text-slate-100">
                                <?php echo e($fw->filename ?? '-'); ?>

                            </td>
                            <td class="px-4 py-3 font-mono text-xs">
                                <?php echo e($fw->version ?? '-'); ?>

                            </td>
                            <td class="px-4 py-3">
                                <?php echo e($fw->product_class ?? '-'); ?>

                            </td>
                            <td class="px-4 py-3 text-xs">
                                <?php echo e($fw->size ? round($fw->size / 1024 / 1024, 2) . ' MB' : '-'); ?>

                            </td>
                            <td class="px-4 py-3 text-xs">
                                <?php echo e($fw->created_at?->format('d/m/Y') ?? '-'); ?>

                            </td>
                            <td class="px-4 py-3 text-right">
                                <div class="flex justify-end gap-1">
                                    <a href="<?php echo e(route('acs.firmware.edit', $fw->id)); ?>" class="inline-flex items-center justify-center w-8 h-8 rounded-md bg-emerald-50 hover:bg-emerald-100 dark:bg-emerald-900/30 dark:hover:bg-emerald-900/50 text-emerald-600 dark:text-emerald-400 transition-colors" title="Edit">
                                        <span class="material-symbols-outlined notranslate" translate="no" style="font-size:18px">edit</span>
                                    </a>
                                    <button wire:click="delete(<?php echo e($fw->id); ?>)" wire:confirm="Yakin ingin menghapus firmware ini?" class="inline-flex items-center justify-center w-8 h-8 rounded-md bg-rose-50 hover:bg-rose-100 dark:bg-rose-900/30 dark:hover:bg-rose-900/50 text-rose-600 dark:text-rose-400 transition-colors" title="Hapus">
                                        <span class="material-symbols-outlined notranslate" translate="no" style="font-size:18px">delete</span>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                        <tr>
                            <td colspan="6" class="px-4 py-12 text-center">
                                <div class="flex flex-col items-center justify-center text-slate-500 dark:text-slate-400">
                                    <span class="material-symbols-outlined notranslate text-slate-300 dark:text-slate-600 dark:text-slate-400 mb-3" translate="no" style="font-size:48px">system_update_alt</span>
                                    <div class="text-sm font-medium text-slate-900 dark:text-slate-100">Belum ada Firmware</div>
                                </div>
                            </td>
                        </tr>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </tbody>
            </table>
        </div>

        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(isset($firmwares) && method_exists($firmwares, 'hasPages') && $firmwares->hasPages()): ?>
            <div class="px-4 py-3 border-t border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800/80">
                <?php echo e($firmwares->links()); ?>

            </div>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
    </div>
  </div>
</div><?php /**PATH D:\dsBilling\resources\views\livewire\acs\firmware\index.blade.php ENDPATH**/ ?>