<div>
  <?php echo $__env->make('livewire.acs._tabs', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

  <div class="space-y-5 pb-10">
    
    <div class="grid grid-cols-2 md:grid-cols-2 lg:grid-cols-4 gap-4">
        
        <div class="relative overflow-hidden rounded-xl border border-emerald-200 dark:border-emerald-800/60 shadow-md bg-gradient-to-br from-emerald-50 to-white dark:from-emerald-950/50 dark:to-slate-800 group hover:shadow-lg transition-all">
            <div class="absolute top-0 left-0 right-0 h-1 bg-gradient-to-r from-emerald-500 to-teal-400 rounded-t-xl"></div>
            <div class="absolute top-3 right-3 opacity-10 text-emerald-400 group-hover:opacity-20 group-hover:scale-110 transition-all">
                <span class="material-symbols-outlined notranslate" translate="no" style="font-size:56px">wifi</span>
            </div>
            <div class="p-4 pt-5">
                <h3 class="text-xs font-bold text-emerald-500 dark:text-emerald-400 uppercase tracking-widest mb-2">Online Devices</h3>
                <div class="text-4xl font-black text-emerald-700 dark:text-emerald-300 mb-3"><?php echo e(number_format($stats['online_devices'] ?? 0)); ?></div>
                <div class="text-xs text-slate-500 dark:text-slate-400">Perangkat tersambung</div>
            </div>
        </div>

        
        <div class="relative overflow-hidden rounded-xl border border-rose-200 dark:border-rose-800/60 shadow-md bg-gradient-to-br from-rose-50 to-white dark:from-rose-950/50 dark:to-slate-800 group hover:shadow-lg transition-all">
            <div class="absolute top-0 left-0 right-0 h-1 bg-gradient-to-r from-rose-500 to-red-400 rounded-t-xl"></div>
            <div class="absolute top-3 right-3 opacity-10 text-rose-400 group-hover:opacity-20 group-hover:scale-110 transition-all">
                <span class="material-symbols-outlined notranslate" translate="no" style="font-size:56px">wifi_off</span>
            </div>
            <div class="p-4 pt-5">
                <h3 class="text-xs font-bold text-rose-500 dark:text-rose-400 uppercase tracking-widest mb-2">Offline Devices</h3>
                <div class="text-4xl font-black text-rose-700 dark:text-rose-300 mb-3"><?php echo e(number_format($stats['offline_devices'] ?? 0)); ?></div>
                <div class="text-xs text-slate-500 dark:text-slate-400">Perangkat terputus</div>
            </div>
        </div>

        
        <div class="relative overflow-hidden rounded-xl border border-indigo-200 dark:border-indigo-800/60 shadow-md bg-gradient-to-br from-indigo-50 to-white dark:from-indigo-950/50 dark:to-slate-800 group hover:shadow-lg transition-all">
            <div class="absolute top-0 left-0 right-0 h-1 bg-gradient-to-r from-indigo-500 to-blue-400 rounded-t-xl"></div>
            <div class="absolute top-3 right-3 opacity-10 text-indigo-400 group-hover:opacity-20 group-hover:scale-110 transition-all">
                <span class="material-symbols-outlined notranslate" translate="no" style="font-size:56px">pending_actions</span>
            </div>
            <div class="p-4 pt-5">
                <h3 class="text-xs font-bold text-indigo-500 dark:text-indigo-400 uppercase tracking-widest mb-2">Pending Tasks</h3>
                <div class="text-4xl font-black text-indigo-700 dark:text-indigo-300 mb-3"><?php echo e(number_format($stats['pending_tasks'] ?? 0)); ?></div>
                <div class="text-xs text-slate-500 dark:text-slate-400">Tugas antrean CPE</div>
            </div>
        </div>

        
        <div class="relative overflow-hidden rounded-xl border border-amber-200 dark:border-amber-800/60 shadow-md bg-gradient-to-br from-amber-50 to-white dark:from-amber-950/50 dark:to-slate-800 group hover:shadow-lg transition-all">
            <div class="absolute top-0 left-0 right-0 h-1 bg-gradient-to-r from-amber-500 to-yellow-400 rounded-t-xl"></div>
            <div class="absolute top-3 right-3 opacity-10 text-amber-400 group-hover:opacity-20 group-hover:scale-110 transition-all">
                <span class="material-symbols-outlined notranslate" translate="no" style="font-size:56px">warning</span>
            </div>
            <div class="p-4 pt-5">
                <h3 class="text-xs font-bold text-amber-500 dark:text-amber-400 uppercase tracking-widest mb-2">Active Alarms</h3>
                <div class="text-4xl font-black text-amber-700 dark:text-amber-300 mb-3"><?php echo e(number_format($stats['active_alarms'] ?? 0)); ?></div>
                <div class="text-xs text-slate-500 dark:text-slate-400">Alarm atau error aktif</div>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 xl:grid-cols-2 gap-4">
      
      <div class="bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl shadow-sm overflow-hidden flex flex-col">
        <div class="px-4 py-3 border-b border-slate-200 dark:border-slate-700 bg-slate-50/80 dark:bg-slate-800/80 flex items-center justify-between">
          <div class="font-semibold text-slate-900 dark:text-slate-100 text-sm">Perangkat Terbaru</div>
          <a href="<?php echo e(route('acs.devices.index')); ?>" class="text-xs text-indigo-600 hover:text-indigo-700 dark:text-indigo-400 dark:hover:text-indigo-300 font-medium">Lihat Semua &rarr;</a>
        </div>
        <div class="divide-y divide-slate-100 dark:divide-slate-700/50 flex-1">
          <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $recentDevices ?? []; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $device): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
            <div class="p-4 hover:bg-slate-50 dark:bg-slate-900/50 dark:hover:bg-slate-800/50 transition-colors flex items-center justify-between">
              <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-full bg-slate-100 dark:bg-slate-700 border border-slate-200 dark:border-slate-600 flex items-center justify-center text-slate-600 dark:text-slate-300 font-bold text-sm uppercase shadow-sm">
                  <?php echo e(substr($device->serial_number ?? 'D', 0, 1)); ?>

                </div>
                <div>
                  <div class="font-medium text-sm text-slate-900 dark:text-slate-100"><?php echo e($device->serial_number ?? '-'); ?></div>
                  <div class="text-xs text-slate-500 dark:text-slate-400 font-mono"><?php echo e($device->model ?? '-'); ?></div>
                </div>
              </div>
              <span class="inline-flex items-center px-2.5 py-1 rounded-md text-[10px] font-medium uppercase tracking-wider <?php echo e(($device->status === 'online') ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-400' : 'bg-rose-100 text-rose-700 dark:bg-rose-900/40 dark:text-rose-400'); ?>">
                <?php echo e($device->status); ?>

              </span>
            </div>
          <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
            <div class="p-8 text-center text-sm text-slate-500 dark:text-slate-400 flex flex-col items-center justify-center">
              <span class="material-symbols-outlined notranslate text-slate-300 dark:text-slate-600 dark:text-slate-400 mb-2" translate="no" style="font-size:32px">router</span>
              Belum ada perangkat terbaru
            </div>
          <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </div>
      </div>

      
      <div class="bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl shadow-sm overflow-hidden flex flex-col">
        <div class="px-4 py-3 border-b border-slate-200 dark:border-slate-700 bg-slate-50/80 dark:bg-slate-800/80 flex items-center justify-between">
          <div class="font-semibold text-slate-900 dark:text-slate-100 text-sm">Tugas (Tasks) Terbaru</div>
          <a href="<?php echo e(route('acs.tasks.index')); ?>" class="text-xs text-indigo-600 hover:text-indigo-700 dark:text-indigo-400 dark:hover:text-indigo-300 font-medium">Lihat Semua &rarr;</a>
        </div>
        <div class="divide-y divide-slate-100 dark:divide-slate-700/50 flex-1">
          <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $recentTasks ?? []; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $task): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
            <div class="p-4 hover:bg-slate-50 dark:bg-slate-900/50 dark:hover:bg-slate-800/50 transition-colors flex items-center justify-between">
              <div>
                <div class="font-medium text-sm text-slate-900 dark:text-slate-100"><?php echo e(ucfirst($task->type)); ?></div>
                <div class="text-xs text-slate-500 dark:text-slate-400"><?php echo e($task->created_at?->format('d/m/Y H:i') ?? '-'); ?></div>
              </div>
              <span class="inline-flex items-center px-2.5 py-1 rounded-md text-[10px] font-medium uppercase tracking-wider 
                  <?php if($task->status === 'completed'): ?> bg-emerald-100 text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-400
                  <?php elseif($task->status === 'running'): ?> bg-indigo-100 text-indigo-700 dark:bg-indigo-900/40 dark:text-indigo-400
                  <?php elseif($task->status === 'failed'): ?> bg-rose-100 text-rose-700 dark:bg-rose-900/40 dark:text-rose-400
                  <?php else: ?> bg-amber-100 text-amber-700 dark:bg-amber-900/40 dark:text-amber-400
                  <?php endif; ?>
              ">
                  <?php echo e($task->status); ?>

              </span>
            </div>
          <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
            <div class="p-8 text-center text-sm text-slate-500 dark:text-slate-400 flex flex-col items-center justify-center">
              <span class="material-symbols-outlined notranslate text-slate-300 dark:text-slate-600 dark:text-slate-400 mb-2" translate="no" style="font-size:32px">pending_actions</span>
              Belum ada tugas yang dijalankan
            </div>
          <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </div>
      </div>
    </div>
  </div>
</div>
<?php /**PATH D:\dsBilling\resources\views\livewire\acs\dashboard.blade.php ENDPATH**/ ?>