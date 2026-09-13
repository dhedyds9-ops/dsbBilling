<?php $__env->startSection('page_title'); ?>
    <span class="material-symbols-outlined notranslate text-indigo-500" translate="no" style="font-size:24px">inventory_2</span>
    <span class="text-lg"><?php echo e(isset($profileId) ? 'Edit Paket Internet' : 'Tambah Paket Internet'); ?></span>
<?php $__env->stopSection(); ?>

<div class="space-y-5 pb-10 max-w-4xl">
    
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($errors->any()): ?>
        <div class="px-4 py-3 bg-red-50 dark:bg-red-900/30 border border-red-200 dark:border-red-700 rounded-xl">
            <p class="text-sm font-semibold text-red-800 dark:text-red-300 mb-1">Terjadi kesalahan:</p>
            <ul class="list-disc list-inside text-sm text-red-700 dark:text-red-400 space-y-0.5">
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                    <li><?php echo e($error); ?></li>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
            </ul>
        </div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

    
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(session()->has('success')): ?>
        <div class="flex items-center gap-3 px-4 py-3 bg-emerald-50 dark:bg-emerald-900/30 border border-emerald-200 dark:border-emerald-700 rounded-xl text-emerald-700 dark:text-emerald-400 text-sm font-medium">
            <span class="material-symbols-outlined notranslate" translate="no" style="font-size:18px">check_circle</span>
            <?php echo e(session('success')); ?>

        </div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

    <div class="bg-white dark:bg-slate-800 rounded-xl shadow-sm border border-slate-200 dark:border-slate-700 overflow-hidden">
        <form wire:submit.prevent="save">
            <div class="p-6 space-y-8">
                
                <!-- Section 1: Informasi Paket -->
                <div>
                    <div class="flex items-center gap-2 mb-4 text-indigo-600 dark:text-indigo-400">
                        <span class="material-symbols-outlined notranslate" translate="no" style="font-size:20px">widgets</span>
                        <h3 class="text-base font-bold uppercase tracking-wider">Informasi Paket</h3>
                    </div>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5">Nama Paket <span class="text-red-500">*</span></label>
                            <input type="text" wire:model.live="name" placeholder="Contoh: Home 20 Mbps" 
                                   class="w-full px-4 py-2.5 bg-slate-50 dark:bg-slate-900/50 border border-slate-200 dark:border-slate-700 rounded-lg text-sm text-slate-700 dark:text-slate-300 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all dark:bg-slate-900 dark:text-slate-100">
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['name'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <span class="text-red-500 text-xs mt-1 block"><?php echo e($message); ?></span> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5">Jenis Service <span class="text-red-500">*</span></label>
                            <select wire:model.live="service_type" 
                                    class="w-full px-4 py-2.5 bg-slate-50 dark:bg-slate-900/50 border border-slate-200 dark:border-slate-700 rounded-lg text-sm text-slate-700 dark:text-slate-300 focus:outline-none focus:ring-2 focus:ring-indigo-500 cursor-pointer transition-all dark:bg-slate-900 dark:text-slate-100">
                                <option value="pppoe">PPPoE</option>
                                <option value="hotspot">Hotspot</option>
                                <option value="voucher">Voucher</option>
                            </select>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['service_type'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <span class="text-red-500 text-xs mt-1 block"><?php echo e($message); ?></span> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5">Tipe Paket <span class="text-red-500">*</span></label>
                            <div class="flex gap-4 mt-3">
                                <label class="flex items-center gap-2 cursor-pointer">
                                    <input type="radio" wire:model.live="package_type" value="unlimited" class="w-4 h-4 text-indigo-600 border-slate-300 dark:border-slate-600 dark:bg-slate-900 dark:text-slate-100">
                                    <span class="text-sm text-slate-700 dark:text-slate-300">Unlimited</span>
                                </label>
                                <label class="flex items-center gap-2 cursor-pointer">
                                    <input type="radio" wire:model.live="package_type" value="time_based" class="w-4 h-4 text-indigo-600 border-slate-300 dark:border-slate-600 dark:bg-slate-900 dark:text-slate-100">
                                    <span class="text-sm text-slate-700 dark:text-slate-300">Time Based</span>
                                </label>
                                <label class="flex items-center gap-2 cursor-pointer">
                                    <input type="radio" wire:model.live="package_type" value="quota_based" class="w-4 h-4 text-indigo-600 border-slate-300 dark:border-slate-600 dark:bg-slate-900 dark:text-slate-100">
                                    <span class="text-sm text-slate-700 dark:text-slate-300">Quota Based</span>
                                </label>
                            </div>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['package_type'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <span class="text-red-500 text-xs mt-1 block"><?php echo e($message); ?></span> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5">Download Speed (Mbps) <span class="text-red-500">*</span></label>
                            <input type="number" wire:model="download_speed" min="1" max="10000" placeholder="20" 
                                   class="w-full px-4 py-2.5 bg-slate-50 dark:bg-slate-900/50 border border-slate-200 dark:border-slate-700 rounded-lg text-sm text-slate-700 dark:text-slate-300 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all dark:bg-slate-900 dark:text-slate-100">
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['download_speed'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <span class="text-red-500 text-xs mt-1 block"><?php echo e($message); ?></span> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5">Upload Speed (Mbps) <span class="text-red-500">*</span></label>
                            <input type="number" wire:model="upload_speed" min="1" max="10000" placeholder="10" 
                                   class="w-full px-4 py-2.5 bg-slate-50 dark:bg-slate-900/50 border border-slate-200 dark:border-slate-700 rounded-lg text-sm text-slate-700 dark:text-slate-300 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all dark:bg-slate-900 dark:text-slate-100">
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['upload_speed'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <span class="text-red-500 text-xs mt-1 block"><?php echo e($message); ?></span> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </div>

                        
                        <div class="md:col-span-2">
                            <div class="flex items-center gap-3 p-3.5 bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-700/50 rounded-lg">
                                <label class="relative inline-flex items-center cursor-pointer">
                                    <input type="checkbox" wire:model.live="enable_burst" class="sr-only peer dark:bg-slate-900 dark:text-slate-100">
                                    <div class="w-10 h-5 bg-slate-200 dark:bg-slate-700 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-5 peer-checked:after:border-white after:content-[''] after:absolute after:top-0.5 after:left-0.5 after:bg-white dark:bg-slate-800 after:border after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-amber-500"></div>
                                </label>
                                <div>
                                    <span class="text-sm font-semibold text-amber-800 dark:text-amber-300">Aktifkan Burst Speed</span>
                                    <p class="text-xs text-amber-600 dark:text-amber-400 mt-0.5">Pelanggan mendapat kecepatan ekstra di awal koneksi. Auto hitung: Limit = 2× speed, Threshold = 80% speed.</p>
                                </div>
                            </div>

                            <div x-show="$wire.enable_burst" x-transition class="mt-4 p-4 bg-slate-50 dark:bg-slate-900/30 border border-slate-200 dark:border-slate-700 rounded-lg space-y-4">
                                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                    
                                    <div>
                                        <label class="block text-xs font-medium text-slate-500 dark:text-slate-400 mb-1 uppercase tracking-wider">Burst Limit Download (Mbps)</label>
                                        <input type="number" wire:model="burst_limit_download" min="1" placeholder="auto" 
                                               class="w-full px-3 py-2 bg-white dark:bg-slate-900 dark:bg-slate-900/50 border border-slate-200 dark:border-slate-700 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-amber-400 transition-all dark:bg-slate-900 dark:text-slate-100">
                                        <p class="text-xs text-slate-400 mt-1">Max speed saat burst</p>
                                    </div>
                                    <div>
                                        <label class="block text-xs font-medium text-slate-500 dark:text-slate-400 mb-1 uppercase tracking-wider">Threshold Download (Mbps)</label>
                                        <input type="number" wire:model="burst_threshold_download" min="1" placeholder="auto" 
                                               class="w-full px-3 py-2 bg-white dark:bg-slate-900 dark:bg-slate-900/50 border border-slate-200 dark:border-slate-700 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-amber-400 transition-all dark:bg-slate-900 dark:text-slate-100">
                                        <p class="text-xs text-slate-400 mt-1">Batas mulai burst</p>
                                    </div>
                                    <div>
                                        <label class="block text-xs font-medium text-slate-500 dark:text-slate-400 mb-1 uppercase tracking-wider">Burst Time Download (detik)</label>
                                        <input type="number" wire:model="burst_time_download" min="1" placeholder="60" 
                                               class="w-full px-3 py-2 bg-white dark:bg-slate-900 dark:bg-slate-900/50 border border-slate-200 dark:border-slate-700 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-amber-400 transition-all dark:bg-slate-900 dark:text-slate-100">
                                        <p class="text-xs text-slate-400 mt-1">Durasi burst</p>
                                    </div>
                                    
                                    <div>
                                        <label class="block text-xs font-medium text-slate-500 dark:text-slate-400 mb-1 uppercase tracking-wider">Burst Limit Upload (Mbps)</label>
                                        <input type="number" wire:model="burst_limit_upload" min="1" placeholder="auto" 
                                               class="w-full px-3 py-2 bg-white dark:bg-slate-900 dark:bg-slate-900/50 border border-slate-200 dark:border-slate-700 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-amber-400 transition-all dark:bg-slate-900 dark:text-slate-100">
                                    </div>
                                    <div>
                                        <label class="block text-xs font-medium text-slate-500 dark:text-slate-400 mb-1 uppercase tracking-wider">Threshold Upload (Mbps)</label>
                                        <input type="number" wire:model="burst_threshold_upload" min="1" placeholder="auto" 
                                               class="w-full px-3 py-2 bg-white dark:bg-slate-900 dark:bg-slate-900/50 border border-slate-200 dark:border-slate-700 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-amber-400 transition-all dark:bg-slate-900 dark:text-slate-100">
                                    </div>
                                    <div>
                                        <label class="block text-xs font-medium text-slate-500 dark:text-slate-400 mb-1 uppercase tracking-wider">Burst Time Upload (detik)</label>
                                        <input type="number" wire:model="burst_time_upload" min="1" placeholder="60" 
                                               class="w-full px-3 py-2 bg-white dark:bg-slate-900 dark:bg-slate-900/50 border border-slate-200 dark:border-slate-700 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-amber-400 transition-all dark:bg-slate-900 dark:text-slate-100">
                                    </div>
                                </div>
                                <button type="button" wire:click="calculateAutoBurst"
                                        class="flex items-center gap-1.5 px-3 py-1.5 bg-amber-500 hover:bg-amber-600 text-white text-xs font-medium rounded-lg transition-colors">
                                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                                    Hitung Ulang Otomatis
                                </button>
                            </div>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5">Masa Aktif <span class="text-red-500">*</span></label>
                            <div class="flex gap-2">
                                <input type="number" wire:model="validity_value" min="1" placeholder="30" 
                                       class="flex-1 px-4 py-2.5 bg-slate-50 dark:bg-slate-900/50 border border-slate-200 dark:border-slate-700 rounded-lg text-sm text-slate-700 dark:text-slate-300 focus:outline-none focus:ring-2 focus:ring-indigo-500 transition-all dark:bg-slate-900 dark:text-slate-100">
                                <select wire:model="validity_unit" 
                                        class="w-28 px-4 py-2.5 bg-slate-50 dark:bg-slate-900/50 border border-slate-200 dark:border-slate-700 rounded-lg text-sm text-slate-700 dark:text-slate-300 focus:outline-none focus:ring-2 focus:ring-indigo-500 cursor-pointer transition-all dark:bg-slate-900 dark:text-slate-100">
                                    <option value="hours">Jam</option>
                                    <option value="days">Hari</option>
                                    <option value="months">Bulan</option>
                                </select>
                            </div>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['validity_value'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <span class="text-red-500 text-xs mt-1 block"><?php echo e($message); ?></span> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5">Shared Users <span class="text-red-500">*</span></label>
                            <input type="number" wire:model="max_devices" min="1" placeholder="1" 
                                   class="w-full px-4 py-2.5 bg-slate-50 dark:bg-slate-900/50 border border-slate-200 dark:border-slate-700 rounded-lg text-sm text-slate-700 dark:text-slate-300 focus:outline-none focus:ring-2 focus:ring-indigo-500 transition-all dark:bg-slate-900 dark:text-slate-100">
                            <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">Jumlah perangkat yang login bersamaan.</p>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['max_devices'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <span class="text-red-500 text-xs mt-1 block"><?php echo e($message); ?></span> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5">Status <span class="text-red-500">*</span></label>
                            <select wire:model="status" 
                                    class="w-full px-4 py-2.5 bg-slate-50 dark:bg-slate-900/50 border border-slate-200 dark:border-slate-700 rounded-lg text-sm text-slate-700 dark:text-slate-300 focus:outline-none focus:ring-2 focus:ring-indigo-500 cursor-pointer transition-all dark:bg-slate-900 dark:text-slate-100">
                                <option value="active">Aktif</option>
                                <option value="inactive">Nonaktif</option>
                            </select>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['status'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <span class="text-red-500 text-xs mt-1 block"><?php echo e($message); ?></span> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </div>
                    </div>
                </div>

                <hr class="border-slate-100 dark:border-slate-700/50">

                <!-- Section 2: Limitasi (Time/Quota Based) -->
                <div>
                    <div class="flex items-center gap-2 mb-4 text-rose-500 dark:text-rose-400">
                        <span class="material-symbols-outlined notranslate" translate="no" style="font-size:20px">timer</span>
                        <h3 class="text-base font-bold uppercase tracking-wider">Limitasi Paket</h3>
                    </div>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div x-show="$wire.package_type === 'time_based'" x-transition class="md:col-span-2">
                            <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5">Durasi (Time Based) <span class="text-red-500">*</span></label>
                            <div class="flex gap-2 w-full md:w-1/2">
                                <input type="number" wire:model="duration_value" min="1" placeholder="30" 
                                       class="flex-1 px-4 py-2.5 bg-slate-50 dark:bg-slate-900/50 border border-slate-200 dark:border-slate-700 rounded-lg text-sm text-slate-700 dark:text-slate-300 focus:outline-none focus:ring-2 focus:ring-indigo-500 transition-all dark:bg-slate-900 dark:text-slate-100">
                                <select wire:model="duration_unit" 
                                        class="w-32 px-4 py-2.5 bg-slate-50 dark:bg-slate-900/50 border border-slate-200 dark:border-slate-700 rounded-lg text-sm text-slate-700 dark:text-slate-300 focus:outline-none focus:ring-2 focus:ring-indigo-500 cursor-pointer transition-all dark:bg-slate-900 dark:text-slate-100">
                                    <option value="hours">Jam</option>
                                    <option value="days">Hari</option>
                                </select>
                            </div>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['duration_value'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <span class="text-red-500 text-xs mt-1 block"><?php echo e($message); ?></span> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </div>
                        <div x-show="$wire.package_type === 'quota_based'" x-transition class="md:col-span-2">
                            <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5">Kuota (Quota Based) <span class="text-red-500">*</span></label>
                            <div class="flex gap-2 w-full md:w-1/2">
                                <input type="number" wire:model="quota_value" min="1" placeholder="100" 
                                       class="flex-1 px-4 py-2.5 bg-slate-50 dark:bg-slate-900/50 border border-slate-200 dark:border-slate-700 rounded-lg text-sm text-slate-700 dark:text-slate-300 focus:outline-none focus:ring-2 focus:ring-indigo-500 transition-all dark:bg-slate-900 dark:text-slate-100">
                                <select wire:model="quota_unit" 
                                        class="w-32 px-4 py-2.5 bg-slate-50 dark:bg-slate-900/50 border border-slate-200 dark:border-slate-700 rounded-lg text-sm text-slate-700 dark:text-slate-300 focus:outline-none focus:ring-2 focus:ring-indigo-500 cursor-pointer transition-all dark:bg-slate-900 dark:text-slate-100">
                                    <option value="MB">MB</option>
                                    <option value="GB">GB</option>
                                </select>
                            </div>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['quota_value'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <span class="text-red-500 text-xs mt-1 block"><?php echo e($message); ?></span> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </div>
                        <div x-show="$wire.package_type === 'unlimited'" class="md:col-span-2 text-sm text-slate-500 dark:text-slate-400 italic">
                            Paket unlimited tidak memiliki batasan waktu atau kuota.
                        </div>
                    </div>
                </div>

                <hr class="border-slate-100 dark:border-slate-700/50">

                <!-- Section 3: Harga -->
                <?php $isAdministrator = auth()->user()->hasRole('administrator') ?? false; ?>
                <div>
                    <div class="flex items-center gap-2 mb-4 text-amber-500 dark:text-amber-400">
                        <span class="material-symbols-outlined notranslate" translate="no" style="font-size:20px">payments</span>
                        <h3 class="text-base font-bold uppercase tracking-wider">Harga Paket</h3>
                    </div>
                    
                    <div class="mb-5">
                        <label class="flex items-center gap-3 cursor-pointer">
                            <input type="checkbox" wire:model.live="is_free" class="w-4 h-4 text-indigo-600 border-slate-300 dark:border-slate-600 rounded focus:ring-indigo-500 dark:bg-slate-900 dark:text-slate-100">
                            <span class="text-sm font-medium text-slate-700 dark:text-slate-300">Tandai sebagai Paket Gratis</span>
                        </label>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($isAdministrator): ?>
                            <!-- Harga Owner dihapus dari UI (dibuat 0 di background) -->
                            <div>
                                <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5">Harga Beli Reseller <span class="text-red-500">*</span></label>
                                <div class="relative">
                                    <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-slate-500 dark:text-slate-400 font-medium">Rp</span>
                                    <input type="number" wire:model="reseller_price" min="0" <?php if($is_free): echo 'disabled'; endif; ?> placeholder="120000" 
                                           class="w-full pl-10 pr-4 py-2.5 bg-slate-50 dark:bg-slate-900/50 border border-slate-200 dark:border-slate-700 rounded-lg text-sm text-slate-700 dark:text-slate-300 focus:outline-none focus:ring-2 focus:ring-indigo-500 disabled:bg-slate-100 dark:bg-slate-800 dark:disabled:bg-slate-800 disabled:cursor-not-allowed transition-all dark:bg-slate-900 dark:text-slate-100">
                                </div>
                                <p class="text-[10px] text-slate-500 mt-1.5 leading-relaxed">Harga modal ke Reseller.<br><span class="font-semibold text-emerald-600 dark:text-emerald-400">Komisi = Harga Jual - Harga Beli Reseller</span>.</p>
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['reseller_price'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <span class="text-red-500 text-xs mt-1 block"><?php echo e($message); ?></span> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            </div>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5">Harga Jual (User) <span class="text-red-500">*</span></label>
                            <div class="relative">
                                <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-slate-500 dark:text-slate-400 font-medium">Rp</span>
                                <input type="number" wire:model="base_price" min="0" <?php if($is_free): echo 'disabled'; endif; ?> placeholder="150000" 
                                       class="w-full pl-10 pr-4 py-2.5 bg-slate-50 dark:bg-slate-900/50 border border-slate-200 dark:border-slate-700 rounded-lg text-sm text-slate-700 dark:text-slate-300 focus:outline-none focus:ring-2 focus:ring-indigo-500 disabled:bg-slate-100 dark:bg-slate-800 dark:disabled:bg-slate-800 disabled:cursor-not-allowed transition-all dark:bg-slate-900 dark:text-slate-100">
                            </div>
                            <p class="text-[10px] text-slate-500 dark:text-slate-400 mt-1.5 leading-relaxed">Harga akhir tagihan yang dibayarkan pelanggan.</p>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['base_price'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <span class="text-red-500 text-xs mt-1 block"><?php echo e($message); ?></span> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </div>
                    </div>
                </div>


                <hr class="border-slate-100 dark:border-slate-700/50">

                <!-- Section 4: Advanced Options (Collapsible) -->
                <div x-data="{ open: false }">
                    <button type="button" @click="open = !open" class="flex items-center gap-2 mb-4 text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:text-slate-100 dark:hover:text-slate-200 transition-colors">
                        <span class="material-symbols-outlined notranslate transition-transform duration-200" :class="{ 'rotate-180': open }" translate="no" style="font-size:20px">settings</span>
                        <h3 class="text-base font-bold uppercase tracking-wider">Advanced (Opsional)</h3>
                    </button>
                    
                    <div x-show="open" x-collapse>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 pt-2">
                            <div>
                                <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5">PPN (%)</label>
                                <input type="number" wire:model="tax_percentage" min="0" max="100" placeholder="11" 
                                       class="w-full px-4 py-2.5 bg-slate-50 dark:bg-slate-900/50 border border-slate-200 dark:border-slate-700 rounded-lg text-sm text-slate-700 dark:text-slate-300 focus:outline-none focus:ring-2 focus:ring-indigo-500 transition-all dark:bg-slate-900 dark:text-slate-100">
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['tax_percentage'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <span class="text-red-500 text-xs mt-1 block"><?php echo e($message); ?></span> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            </div>
                            <div class="flex items-center">
                                <label class="flex items-center gap-3 cursor-pointer mt-6">
                                    <input type="checkbox" wire:model="tax_enabled" class="w-4 h-4 text-indigo-600 border-slate-300 dark:border-slate-600 rounded focus:ring-indigo-500 dark:bg-slate-900 dark:text-slate-100">
                                    <span class="text-sm font-medium text-slate-700 dark:text-slate-300">Aktifkan PPN</span>
                                </label>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5">Login Start Time</label>
                                <input type="time" wire:model="login_start_time" 
                                       class="w-full px-4 py-2.5 bg-slate-50 dark:bg-slate-900/50 border border-slate-200 dark:border-slate-700 rounded-lg text-sm text-slate-700 dark:text-slate-300 focus:outline-none focus:ring-2 focus:ring-indigo-500 transition-all cursor-pointer dark:bg-slate-900 dark:text-slate-100">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5">Login End Time</label>
                                <input type="time" wire:model="login_end_time" 
                                       class="w-full px-4 py-2.5 bg-slate-50 dark:bg-slate-900/50 border border-slate-200 dark:border-slate-700 rounded-lg text-sm text-slate-700 dark:text-slate-300 focus:outline-none focus:ring-2 focus:ring-indigo-500 transition-all cursor-pointer dark:bg-slate-900 dark:text-slate-100">
                            </div>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($isAdministrator): ?>
                                <div>
                                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5">Visibility</label>
                                    <select wire:model="visibility" 
                                            class="w-full px-4 py-2.5 bg-slate-50 dark:bg-slate-900/50 border border-slate-200 dark:border-slate-700 rounded-lg text-sm text-slate-700 dark:text-slate-300 focus:outline-none focus:ring-2 focus:ring-indigo-500 cursor-pointer transition-all dark:bg-slate-900 dark:text-slate-100">
                                        <option value="private">Private</option>
                                        <option value="shared">Shared</option>
                                        <option value="global">Global</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5">Owner / Reseller</label>
                                    <select wire:model="owner_id" 
                                            class="w-full px-4 py-2.5 bg-slate-50 dark:bg-slate-900/50 border border-slate-200 dark:border-slate-700 rounded-lg text-sm text-slate-700 dark:text-slate-300 focus:outline-none focus:ring-2 focus:ring-indigo-500 cursor-pointer transition-all dark:bg-slate-900 dark:text-slate-100">
                                        <option value="">-- Pilih Owner --</option>
                                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $users; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $user): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                                            <option value="<?php echo e($user->id); ?>"><?php echo e($user->name); ?></option>
                                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                                    </select>
                                </div>
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            <div class="md:col-span-2">
                                <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5">Technical Notes</label>
                                <textarea wire:model="technical_notes" rows="2" placeholder="Catatan teknis internal (opsional)" 
                                          class="w-full px-4 py-2.5 bg-slate-50 dark:bg-slate-900/50 border border-slate-200 dark:border-slate-700 rounded-lg text-sm text-slate-700 dark:text-slate-300 focus:outline-none focus:ring-2 focus:ring-indigo-500 transition-all dark:bg-slate-900 dark:text-slate-100"></textarea>
                            </div>
                        </div>
                    </div>
                </div>

            </div>

            <!-- Footer Actions -->
            <div class="bg-slate-50 dark:bg-slate-900/50 p-4 border-t border-slate-200 dark:border-slate-700 flex items-center justify-between gap-3">
                <a href="<?php echo e(route('isp.service-profiles.index')); ?>" class="inline-flex items-center px-4 py-2 bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-600 hover:bg-slate-50 dark:bg-slate-900/50 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 text-sm font-medium rounded-lg transition-colors cursor-pointer">
                    <span class="material-symbols-outlined notranslate mr-1.5" translate="no" style="font-size:18px">arrow_back</span>
                    Batal
                </a>
                <button type="submit" wire:loading.attr="disabled" class="inline-flex items-center px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium rounded-lg shadow-sm shadow-indigo-200 dark:shadow-none transition-colors cursor-pointer">
                    <svg wire:loading wire:target="save" class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                    <span wire:loading.remove wire:target="save" class="material-symbols-outlined notranslate mr-1.5" translate="no" style="font-size:18px">save</span>
                    <span wire:loading.remove wire:target="save"><?php echo e(isset($profileId) ? 'Simpan Perubahan' : 'Simpan Paket'); ?></span>
                    <span wire:loading wire:target="save">Menyimpan...</span>
                </button>
            </div>
        </form>
    </div>
</div>
<?php /**PATH D:\dsBilling\resources\views\livewire\isp\service-profile\form.blade.php ENDPATH**/ ?>