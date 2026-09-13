<div>
    <?php if (isset($component)) { $__componentOriginalcb19cb35a534439097b02b8af91726ee = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalcb19cb35a534439097b02b8af91726ee = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.page-header','data' => ['title' => 'Rekap Absensi']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.page-header'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'Rekap Absensi']); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

         <?php $__env->slot('description', null, []); ?> 
            Pantau kehadiran, lokasi, dan jam kerja Teknisi serta Staf NOC secara real-time.
         <?php $__env->endSlot(); ?>
     <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalcb19cb35a534439097b02b8af91726ee)): ?>
<?php $attributes = $__attributesOriginalcb19cb35a534439097b02b8af91726ee; ?>
<?php unset($__attributesOriginalcb19cb35a534439097b02b8af91726ee); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalcb19cb35a534439097b02b8af91726ee)): ?>
<?php $component = $__componentOriginalcb19cb35a534439097b02b8af91726ee; ?>
<?php unset($__componentOriginalcb19cb35a534439097b02b8af91726ee); ?>
<?php endif; ?>

    <div class="max-w-6xl mx-auto space-y-6">
        
        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-5 shadow-sm flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div class="flex items-center gap-4 w-full sm:w-auto">
                <div class="flex-1 sm:flex-none">
                    <label class="block text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-1">Tanggal</label>
                    <input type="date" wire:model.live="dateFilter" class="w-full px-4 py-2 border border-slate-200 dark:border-slate-700 rounded-xl text-sm focus:ring-2 focus:ring-indigo-500 bg-slate-50 dark:bg-slate-800 dark:text-slate-100 dark:bg-slate-900 dark:text-slate-100">
                </div>
            </div>
            <div class="w-full sm:w-72 relative">
                <span class="material-symbols-outlined notranslate absolute left-3 top-1/2 -translate-y-1/2 text-slate-400" translate="no" style="font-size:20px">search</span>
                <input type="text" wire:model.live.debounce.300ms="search" class="w-full pl-10 pr-4 py-2 border border-slate-200 dark:border-slate-700 rounded-xl text-sm focus:ring-2 focus:ring-indigo-500 bg-slate-50 dark:bg-slate-800 dark:text-slate-100 dark:bg-slate-900 dark:text-slate-100" placeholder="Cari nama teknisi...">
            </div>
        </div>

        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm whitespace-nowrap">
                    <thead class="bg-slate-50 dark:bg-slate-900/50 text-slate-600 dark:text-slate-400 font-semibold border-b border-slate-200 dark:border-slate-800">
                        <tr>
                            <th class="px-6 py-4">Pegawai</th>
                            <th class="px-6 py-4">Status</th>
                            <th class="px-6 py-4 text-center">Check-In</th>
                            <th class="px-6 py-4 text-center">Check-Out</th>
                            <th class="px-6 py-4">Lokasi GPS</th>
                            <th class="px-6 py-4">Catatan</th>
                            <th class="px-6 py-4">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $attendances; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $att): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                            <tr class="hover:bg-slate-50 dark:bg-slate-900/50 dark:hover:bg-slate-800/50">
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-3">
                                        <?php if (isset($component)) { $__componentOriginalf3a01b417729d1136223a876b3a78880 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalf3a01b417729d1136223a876b3a78880 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.display.avatar','data' => ['name' => $att->technician->name ?? 'Unknown','size' => 'sm']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('display.avatar'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($att->technician->name ?? 'Unknown'),'size' => 'sm']); ?>
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
                                        <div>
                                            <p class="font-bold text-slate-900 dark:text-slate-100"><?php echo e($att->technician->name ?? 'Unknown'); ?></p>
                                            <p class="text-[10px] font-black uppercase text-indigo-500 tracking-wider"><?php echo e($att->technician->job_function ?? 'STAF'); ?></p>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($att->status->value === 'checked_in'): ?>
                                        <span class="px-3 py-1 bg-blue-50 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400 rounded-lg text-xs font-bold uppercase">Bekerja</span>
                                    <?php elseif($att->status->value === 'checked_out'): ?>
                                        <span class="px-3 py-1 bg-emerald-50 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400 rounded-lg text-xs font-bold uppercase">Selesai</span>
                                    <?php elseif($att->status->value === 'late'): ?>
                                        <span class="px-3 py-1 bg-amber-50 text-amber-700 dark:bg-amber-900/30 dark:text-amber-400 rounded-lg text-xs font-bold uppercase">Terlambat</span>
                                    <?php elseif($att->status->value === 'sick'): ?>
                                        <span class="px-3 py-1 bg-red-50 text-red-700 dark:bg-red-900/30 dark:text-red-400 rounded-lg text-xs font-bold uppercase">Sakit</span>
                                    <?php elseif($att->status->value === 'leave'): ?>
                                        <span class="px-3 py-1 bg-purple-50 text-purple-700 dark:bg-purple-900/30 dark:text-purple-400 rounded-lg text-xs font-bold uppercase">Cuti/Izin</span>
                                    <?php else: ?>
                                        <span class="px-3 py-1 bg-slate-100 text-slate-600 dark:bg-slate-800 dark:text-slate-400 rounded-lg text-xs font-bold uppercase"><?php echo e($att->status->value); ?></span>
                                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <div class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-slate-100 dark:bg-slate-800 rounded-lg text-slate-700 dark:text-slate-300 font-mono font-bold">
                                        <span class="material-symbols-outlined notranslate text-emerald-500" translate="no" style="font-size:16px">login</span>
                                        <?php echo e($att->checked_in_at ? $att->checked_in_at->format('H:i') : '-'); ?>

                                    </div>
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($att->checked_out_at): ?>
                                        <div class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-slate-100 dark:bg-slate-800 rounded-lg text-slate-700 dark:text-slate-300 font-mono font-bold">
                                            <span class="material-symbols-outlined notranslate text-red-500" translate="no" style="font-size:16px">logout</span>
                                            <?php echo e($att->checked_out_at->format('H:i')); ?>

                                        </div>
                                    <?php else: ?>
                                        <span class="text-slate-400 italic">-</span>
                                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                </td>
                                <td class="px-6 py-4">
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($att->check_in_latitude): ?>
                                        <a href="https://maps.google.com/?q=<?php echo e($att->check_in_latitude); ?>,<?php echo e($att->check_in_longitude); ?>" target="_blank" class="inline-flex items-center gap-1 text-xs text-indigo-600 hover:text-indigo-700 font-bold bg-indigo-50 dark:bg-indigo-900/30 px-2 py-1 rounded">
                                            <span class="material-symbols-outlined notranslate" translate="no" style="font-size:14px">map</span> Map
                                        </a>
                                    <?php else: ?>
                                        <span class="text-slate-400 italic text-xs">No GPS</span>
                                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                </td>
                                <td class="px-6 py-4">
                                    <span class="text-xs text-slate-600 dark:text-slate-400 truncate max-w-[150px] inline-block" title="<?php echo e($att->notes); ?>">
                                        <?php echo e($att->notes ?: '-'); ?>

                                    </span>
                                </td>
                                <td class="px-6 py-4">
                                    <div x-data="{ open: false }" class="relative">
                                        <button @click="open = !open" @click.away="open = false" class="p-1.5 text-slate-400 hover:text-indigo-600 rounded-lg hover:bg-slate-100 dark:bg-slate-800 transition-colors">
                                            <span class="material-symbols-outlined notranslate" style="font-size:18px">more_vert</span>
                                        </button>
                                        <div x-show="open" x-cloak class="absolute right-0 mt-1 w-36 bg-white dark:bg-slate-800 rounded-xl shadow-lg border border-slate-100 dark:border-slate-700 z-50 overflow-hidden text-xs">
                                            <button wire:click="updateStatus('<?php echo e($att->id); ?>', 'sick')" @click="open = false" class="w-full text-left px-4 py-2 hover:bg-slate-50 dark:bg-slate-900/50 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300">Set Sakit</button>
                                            <button wire:click="updateStatus('<?php echo e($att->id); ?>', 'leave')" @click="open = false" class="w-full text-left px-4 py-2 hover:bg-slate-50 dark:bg-slate-900/50 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300">Set Izin/Cuti</button>
                                            <button wire:click="updateStatus('<?php echo e($att->id); ?>', 'absent')" @click="open = false" class="w-full text-left px-4 py-2 hover:bg-slate-50 dark:bg-slate-900/50 dark:hover:bg-slate-700 text-red-600 dark:text-red-400">Set Alpa (Absent)</button>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                            <tr>
                                <td colspan="7" class="px-6 py-12 text-center text-slate-500 dark:text-slate-400">
                                    <span class="material-symbols-outlined notranslate block mx-auto text-slate-300 mb-2" translate="no" style="font-size:48px">group_off</span>
                                    <p class="font-bold text-slate-900 dark:text-slate-100">Tidak ada data absensi</p>
                                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">Belum ada teknisi yang absen pada tanggal ini.</p>
                                </td>
                            </tr>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php /**PATH D:\dsBilling\resources\views\livewire\admin\attendance\index.blade.php ENDPATH**/ ?>