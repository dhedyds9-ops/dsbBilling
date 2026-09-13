<?php $__env->startSection('page_title'); ?>
<div class="flex items-center gap-3">
  <span class="material-symbols-outlined notranslate text-indigo-500" translate="no" style="font-size:24px">router</span>
  <span class="text-lg">Daftar Mikrotik (NAS)</span>
</div>
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

    
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        
        <div class="relative overflow-x-auto rounded-xl border border-indigo-200 dark:border-indigo-800/60 shadow-md bg-gradient-to-br from-indigo-50 to-white dark:from-indigo-950/50 dark:to-slate-800 group hover:shadow-lg transition-all">
            <div class="absolute top-0 left-0 right-0 h-1 bg-gradient-to-r from-indigo-500 to-blue-400 rounded-t-xl"></div>
            <div class="absolute top-3 right-3 opacity-10 text-indigo-400 group-hover:opacity-20 group-hover:scale-110 transition-all">
                <span class="material-symbols-outlined notranslate" translate="no" style="font-size:56px">dns</span>
            </div>
            <div class="p-4 pt-5">
                <h3 class="text-xs font-bold text-indigo-500 dark:text-indigo-400 uppercase tracking-widest mb-2">Total Router</h3>
                <div class="text-4xl font-black text-indigo-700 dark:text-indigo-300 mb-3"><?php echo e(number_format($summary['total'] ?? 0)); ?></div>
                <div class="text-xs text-slate-500 dark:text-slate-400">Semua router yang terdaftar</div>
            </div>
        </div>
        
        
        <div class="relative overflow-x-auto rounded-xl border border-emerald-200 dark:border-emerald-800/60 shadow-md bg-gradient-to-br from-emerald-50 to-white dark:from-emerald-950/50 dark:to-slate-800 group hover:shadow-lg transition-all">
            <div class="absolute top-0 left-0 right-0 h-1 bg-gradient-to-r from-emerald-500 to-teal-400 rounded-t-xl"></div>
            <div class="absolute top-3 right-3 opacity-10 text-emerald-400 group-hover:opacity-20 group-hover:scale-110 transition-all">
                <span class="material-symbols-outlined notranslate" translate="no" style="font-size:56px">check_circle</span>
            </div>
            <div class="p-4 pt-5">
                <h3 class="text-xs font-bold text-emerald-500 dark:text-emerald-400 uppercase tracking-widest mb-2">Router Aktif</h3>
                <div class="text-4xl font-black text-emerald-700 dark:text-emerald-300 mb-3"><?php echo e(number_format($summary['active'] ?? 0)); ?></div>
                <div class="text-xs text-slate-500 dark:text-slate-400">Router dalam status beroperasi</div>
            </div>
        </div>

        
        <div class="relative overflow-x-auto rounded-xl border border-slate-200 dark:border-slate-700 shadow-md bg-gradient-to-br from-slate-50 to-white dark:from-slate-800 dark:to-slate-900 group hover:shadow-lg transition-all">
            <div class="absolute top-0 left-0 right-0 h-1 bg-gradient-to-r from-slate-500 to-gray-400 rounded-t-xl"></div>
            <div class="absolute top-3 right-3 opacity-10 text-slate-400 group-hover:opacity-20 group-hover:scale-110 transition-all">
                <span class="material-symbols-outlined notranslate" translate="no" style="font-size:56px">offline_bolt</span>
            </div>
            <div class="p-4 pt-5">
                <h3 class="text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-widest mb-2">Nonaktif / Offline</h3>
                <div class="text-4xl font-black text-slate-700 dark:text-slate-300 mb-3"><?php echo e(number_format($summary['inactive'] ?? 0)); ?></div>
                <div class="text-xs text-slate-500 dark:text-slate-400">Router yang dinonaktifkan</div>
            </div>
        </div>
    </div>

    
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div class="flex items-center gap-2">
            <a href="<?php echo e(route('isp.routers.create')); ?>" class="inline-flex items-center justify-center px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium rounded-lg shadow-sm shadow-indigo-200 dark:shadow-none transition-all">
                <span class="material-symbols-outlined notranslate mr-1.5" translate="no" style="font-size:18px">add</span>
                Tambah Router
            </a>
        </div>
        
        <div class="flex items-center gap-2">
            <div class="flex-1 relative">
                <span class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400">
                    <span class="material-symbols-outlined notranslate" translate="no" style="font-size:18px">search</span>
                </span>
                <input type="text" wire:model.live.debounce.300ms="search"
                       placeholder="Cari nama atau IP..."
                       class="w-full pl-9 pr-4 py-2 bg-slate-50 dark:bg-slate-900/50 border border-slate-200 dark:border-slate-700 rounded-lg text-sm text-slate-700 dark:text-slate-300 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all dark:bg-slate-900 dark:text-slate-100">
            </div>
            <select wire:model.live="perPage"
                    class="pl-3 pr-10 py-2 bg-slate-50 dark:bg-slate-900/50 border border-slate-200 dark:border-slate-700 rounded-lg text-sm text-slate-700 dark:text-slate-300 focus:outline-none focus:ring-2 focus:ring-indigo-500 cursor-pointer dark:bg-slate-900 dark:text-slate-100">
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
                    <tr class="border-b border-slate-200 dark:border-slate-700 text-xs uppercase tracking-wider text-slate-500 dark:text-slate-400 bg-slate-50/80 dark:bg-slate-800/80">
                        <th class="px-4 py-3 text-left font-semibold">Nama & Kode</th>
                        <th class="px-4 py-3 text-left font-semibold">Lokasi POP</th>
                        <th class="px-4 py-3 text-left font-semibold">IP Address</th>
                        <th class="px-4 py-3 text-left font-semibold">Sesi Aktif</th>
                        <th class="px-4 py-3 text-left font-semibold">Status & Live</th>
                        <th class="px-4 py-3 text-right font-semibold">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200 dark:divide-slate-700/50">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__empty_0 = true; $__currentLoopData = $routers; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $router): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_0 = false; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                        <tr class="hover:bg-slate-50 dark:bg-slate-900/50 dark:hover:bg-slate-800/50 transition-colors">
                            <td class="px-4 py-3">
                                <div class="font-bold text-slate-900 dark:text-slate-100 flex items-center gap-2">
                                    <?php echo e($router->name); ?>

                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($router->is_default): ?>
                                        <span class="px-2 py-0.5 bg-indigo-100 text-indigo-700 dark:bg-indigo-900/50 dark:text-indigo-400 text-[10px] uppercase font-bold rounded-full">Default</span>
                                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                </div>
                                <div class="text-xs text-slate-500 dark:text-slate-400">Kode: <?php echo e($router->kode_router ?? '-'); ?></div>
                            </td>
                            <td class="px-4 py-3">
                                <div class="font-medium text-slate-700 dark:text-slate-300"><?php echo e($router->lokasi_pop ?? '-'); ?></div>
                            </td>
                            <td class="px-4 py-3 font-mono text-sm text-slate-700 dark:text-slate-300">
                                <?php echo e($router->ip_address); ?><span class="text-slate-400">:<?php echo e($router->port); ?></span>
                            </td>
                            <td class="px-4 py-3">
                                <div class="flex items-center gap-1.5">
                                    <span class="material-symbols-outlined notranslate text-emerald-500" translate="no" style="font-size:16px">people</span>
                                    <span class="font-medium text-slate-700 dark:text-slate-300"><?php echo e(number_format($router->active_sessions_count)); ?></span>
                                </div>
                            </td>
                            <td class="px-4 py-3">
                                <div class="flex items-center gap-2">
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($router->status === 'active'): ?>
                                        <span class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded-md text-[10px] font-medium uppercase tracking-wider bg-emerald-100 text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-400">
                                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span> Aktif
                                        </span>
                                    <?php else: ?>
                                        <span class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded-md text-[10px] font-medium uppercase tracking-wider bg-rose-100 text-rose-700 dark:bg-rose-900/40 dark:text-rose-400">
                                            <span class="w-1.5 h-1.5 rounded-full bg-rose-500"></span> Nonaktif
                                        </span>
                                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($router->status === 'active'): ?>
                                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($router->is_online === true): ?>
                                            <span class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded-md text-[10px] font-medium uppercase tracking-wider bg-emerald-100 text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-400" title="API Terhubung">
                                                <span class="material-symbols-outlined notranslate text-[12px]" translate="no">wifi</span> Terhubung
                                            </span>
                                        <?php elseif($router->is_online === false): ?>
                                            <span class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded-md text-[10px] font-medium uppercase tracking-wider bg-rose-100 text-rose-700 dark:bg-rose-900/40 dark:text-rose-400" title="API Terputus">
                                                <span class="material-symbols-outlined notranslate text-[12px]" translate="no">wifi_off</span> Terputus
                                            </span>
                                        <?php else: ?>
                                            <span class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded-md text-[10px] font-medium uppercase tracking-wider bg-slate-100 text-slate-700 dark:bg-slate-800 dark:text-slate-400" title="Checking...">
                                                <span class="material-symbols-outlined notranslate text-[12px] animate-spin" translate="no">sync</span> Cek...
                                            </span>
                                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                </div>
                            </td>
                            <td class="px-4 py-3 text-right">
                                <div class="flex items-center justify-end gap-1">
                                    <button wire:click="checkConnection(<?php echo e($router->id); ?>)" class="inline-flex items-center justify-center w-8 h-8 rounded-md bg-indigo-50 hover:bg-indigo-100 dark:bg-indigo-900/30 dark:hover:bg-indigo-900/50 text-indigo-600 dark:text-indigo-400 transition-colors" title="Check Connection">
                                        <span class="material-symbols-outlined notranslate" translate="no" style="font-size:18px">network_ping</span>
                                    </button>
                                    <a href="<?php echo e(route('isp.routers.edit', $router->id)); ?>" class="inline-flex items-center justify-center w-8 h-8 rounded-md bg-emerald-50 hover:bg-emerald-100 dark:bg-emerald-900/30 dark:hover:bg-emerald-900/50 text-emerald-600 dark:text-emerald-400 transition-colors" title="Edit">
                                        <span class="material-symbols-outlined notranslate" translate="no" style="font-size:18px">edit</span>
                                    </a>
                                    <button wire:click="deleteRouter(<?php echo e($router->id); ?>)" wire:confirm="Yakin ingin menghapus router ini?" class="inline-flex items-center justify-center w-8 h-8 rounded-md bg-rose-50 hover:bg-rose-100 dark:bg-rose-900/30 dark:hover:bg-rose-900/50 text-rose-600 dark:text-rose-400 transition-colors" title="Hapus">
                                        <span class="material-symbols-outlined notranslate" translate="no" style="font-size:18px">delete</span>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_0): ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                        <tr>
                            <td colspan="6" class="px-6 py-12 text-center text-slate-500 dark:text-slate-400">
                                <span class="material-symbols-outlined notranslate text-5xl mb-3 text-slate-300 dark:text-slate-600 dark:text-slate-400" translate="no">router</span>
                                <p class="text-lg font-medium text-slate-900 dark:text-slate-100">Belum ada router</p>
                                <p class="text-sm mt-1">Silakan tambahkan router Mikrotik pertama Anda.</p>
                            </td>
                        </tr>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </tbody>
            </table>
        </div>
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($routers->hasPages()): ?>
            <div class="px-4 py-3 border-t border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800/80">
                <?php echo e($routers->links()); ?>

            </div>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
    </div>
</div>
<?php /**PATH D:\dsBilling\resources\views\livewire\isp\router\index.blade.php ENDPATH**/ ?>