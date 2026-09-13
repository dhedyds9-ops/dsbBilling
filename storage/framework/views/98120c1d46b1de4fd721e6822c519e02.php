<div>
  <?php echo $__env->make('livewire.acs._tabs', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

  <div class="space-y-5 pb-10">
    <div class="flex flex-col sm:flex-row sm:items-center justify-end gap-4">
        <div class="flex items-center gap-2">
            <button wire:click="$refresh" class="inline-flex items-center justify-center px-4 py-2 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 hover:bg-slate-50 dark:bg-slate-900/50 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 text-sm font-medium rounded-lg shadow-sm transition-all">
                <span class="material-symbols-outlined notranslate mr-1.5" translate="no" style="font-size:18px">refresh</span>
                Refresh Data
            </button>
        </div>
    </div>

    
    <div class="bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm text-left">
                <thead>
                    <tr class="border-b border-slate-200 dark:border-slate-700 text-xs uppercase tracking-wider text-slate-500 dark:text-slate-400 bg-slate-50/80 dark:bg-slate-800/80">
                        <th class="px-4 py-3 font-semibold whitespace-nowrap">Severity</th>
                        <th class="px-4 py-3 font-semibold whitespace-nowrap">Device</th>
                        <th class="px-4 py-3 font-semibold whitespace-nowrap">Alarm Deskripsi</th>
                        <th class="px-4 py-3 font-semibold whitespace-nowrap">Waktu</th>
                        <th class="px-4 py-3 font-semibold whitespace-nowrap text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-700/50 text-slate-700 dark:text-slate-300">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $alarms ?? []; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $alarm): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                        <tr class="hover:bg-slate-50 dark:bg-slate-900/50/50 dark:hover:bg-slate-800/50 transition-colors">
                            <td class="px-4 py-3">
                                <span class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded-md text-[10px] font-medium uppercase tracking-wider 
                                    <?php if($alarm->severity === 'critical'): ?> bg-rose-100 text-rose-700 dark:bg-rose-900/40 dark:text-rose-400
                                    <?php elseif($alarm->severity === 'major'): ?> bg-orange-100 text-orange-700 dark:bg-orange-900/40 dark:text-orange-400
                                    <?php elseif($alarm->severity === 'minor'): ?> bg-amber-100 text-amber-700 dark:bg-amber-900/40 dark:text-amber-400
                                    <?php else: ?> bg-blue-100 text-blue-700 dark:bg-blue-900/40 dark:text-blue-400
                                    <?php endif; ?>
                                ">
                                    <?php echo e($alarm->severity); ?>

                                </span>
                            </td>
                            <td class="px-4 py-3 font-medium text-slate-900 dark:text-slate-100">
                                <?php echo e($alarm->device?->serial_number ?? '-'); ?>

                            </td>
                            <td class="px-4 py-3">
                                <?php echo e($alarm->description ?? '-'); ?>

                            </td>
                            <td class="px-4 py-3 text-xs font-mono">
                                <?php echo e($alarm->created_at?->format('d/m/Y H:i') ?? '-'); ?>

                            </td>
                            <td class="px-4 py-3 text-right">
                                <button wire:click="delete(<?php echo e($alarm->id); ?>)" wire:confirm="Yakin ingin menghapus alarm ini?" class="inline-flex items-center justify-center w-8 h-8 rounded-md bg-rose-50 hover:bg-rose-100 dark:bg-rose-900/30 dark:hover:bg-rose-900/50 text-rose-600 dark:text-rose-400 transition-colors" title="Hapus">
                                    <span class="material-symbols-outlined notranslate" translate="no" style="font-size:18px">delete</span>
                                </button>
                            </td>
                        </tr>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                        <tr>
                            <td colspan="5" class="px-4 py-12 text-center">
                                <div class="flex flex-col items-center justify-center text-slate-500 dark:text-slate-400">
                                    <span class="material-symbols-outlined notranslate text-slate-300 dark:text-slate-600 dark:text-slate-400 mb-3" translate="no" style="font-size:48px">check_circle</span>
                                    <div class="text-sm font-medium text-slate-900 dark:text-slate-100">Semua Aman</div>
                                    <div class="text-xs mt-1">Belum ada Alarm tercatat.</div>
                                </div>
                            </td>
                        </tr>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </tbody>
            </table>
        </div>

        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(isset($alarms) && method_exists($alarms, 'hasPages') && $alarms->hasPages()): ?>
            <div class="px-4 py-3 border-t border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800/80">
                <?php echo e($alarms->links()); ?>

            </div>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
    </div>
  </div>
</div><?php /**PATH D:\dsBilling\resources\views\livewire\acs\alarm\index.blade.php ENDPATH**/ ?>