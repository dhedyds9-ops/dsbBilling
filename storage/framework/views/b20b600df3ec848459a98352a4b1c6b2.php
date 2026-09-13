<?php $__env->startSection('header_title', 'Tugas Saya'); ?>

<div class="space-y-4 p-4">

    
    <div class="bg-gradient-to-br from-indigo-600 to-blue-700 text-white p-5 rounded-2xl shadow-lg relative overflow-hidden">
        <div class="absolute top-0 right-0 opacity-10 pointer-events-none">
            <span class="material-symbols-outlined" style="font-size:130px">engineering</span>
        </div>
        <div class="relative z-10">
            <p class="text-indigo-200 text-xs font-semibold mb-1">Daftar instalasi & survey lapangan</p>
            <div class="grid grid-cols-2 gap-3 mt-3">
                <div class="bg-white dark:bg-slate-800/20 rounded-xl p-3 flex items-center justify-between">
                    <div>
                        <p class="text-xs text-indigo-100">Tugas Aktif</p>
                        <h3 class="text-2xl font-black"><?php echo e($stats['active'] ?? 0); ?></h3>
                    </div>
                    <span class="material-symbols-outlined opacity-80" style="font-size:28px">work</span>
                </div>
                <div class="bg-white dark:bg-slate-800/20 rounded-xl p-3 flex items-center justify-between">
                    <div>
                        <p class="text-xs text-indigo-100">Selesai</p>
                        <h3 class="text-2xl font-black"><?php echo e($stats['history'] ?? 0); ?></h3>
                    </div>
                    <span class="material-symbols-outlined opacity-80" style="font-size:28px">check_circle</span>
                </div>
            </div>
        </div>
    </div>

    
    <div class="bg-slate-200/70 dark:bg-slate-800 p-1 rounded-xl flex items-center shadow-inner">
        <button wire:click="switchTab('active')" class="flex-1 py-2.5 rounded-lg text-sm font-bold transition-all duration-200 <?php echo e($activeTab === 'active' ? 'bg-white dark:bg-slate-900 text-indigo-600 dark:text-indigo-400 shadow-sm' : 'text-slate-500 dark:text-slate-400'); ?>">
            Tugas Aktif
        </button>
        <button wire:click="switchTab('history')" class="flex-1 py-2.5 rounded-lg text-sm font-bold transition-all duration-200 <?php echo e($activeTab === 'history' ? 'bg-white dark:bg-slate-900 text-indigo-600 dark:text-indigo-400 shadow-sm' : 'text-slate-500 dark:text-slate-400'); ?>">
            Riwayat
        </button>
    </div>

    
    <div class="relative">
        <span class="material-symbols-outlined absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400" style="font-size:20px">search</span>
        <input type="text" wire:model.live.debounce.300ms="search" placeholder="Cari nama atau alamat..." class="w-full pl-11 pr-4 py-3 border border-slate-200 dark:border-slate-700 rounded-2xl text-sm focus:ring-2 focus:ring-indigo-500 bg-white dark:bg-slate-900 dark:text-slate-100 shadow-sm dark:bg-slate-900 dark:text-slate-100">
    </div>

    
    <div class="space-y-3">
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__empty_0 = true; $__currentLoopData = $jobs; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $job): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_0 = false; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-4 shadow-sm relative overflow-hidden">
            <div class="absolute left-0 top-0 bottom-0 w-1 <?php echo e($activeTab === 'active' ? 'bg-indigo-500' : 'bg-emerald-500'); ?>"></div>

            
            <div class="flex justify-between items-start mb-3 pb-3 border-b border-slate-100 dark:border-slate-800">
                <div class="flex items-center gap-1.5 text-slate-700 dark:text-slate-300 text-sm font-semibold">
                    <span class="material-symbols-outlined text-indigo-500" style="font-size:16px">event</span>
                    <?php echo e($job->scheduled_at ? \Carbon\Carbon::parse($job->scheduled_at)->format('d/m/Y H:i') : '-'); ?>

                </div>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($activeTab === 'active'): ?>
                    <span class="px-2.5 py-1 bg-indigo-50 text-indigo-700 dark:bg-indigo-900/30 dark:text-indigo-400 rounded-lg text-[10px] font-bold uppercase">Berjalan</span>
                <?php else: ?>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($job->status === 'completed'): ?>
                        <span class="px-2.5 py-1 bg-amber-50 dark:bg-amber-900/30 text-amber-700 rounded-lg text-[10px] font-bold uppercase">Menunggu Aktivasi</span>
                    <?php else: ?>
                        <span class="px-2.5 py-1 bg-emerald-50 dark:bg-emerald-900/30 text-emerald-700 rounded-lg text-[10px] font-bold uppercase">Aktif</span>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </div>

            
            <h4 class="font-black text-base text-slate-900 dark:text-white leading-tight"><?php echo e($job->prospect->name ?? 'Unknown'); ?></h4>
            <a href="tel:<?php echo e($job->prospect->phone ?? ''); ?>" class="flex items-center gap-1.5 mt-1 text-slate-500 dark:text-slate-400 text-sm hover:text-emerald-600">
                <span class="material-symbols-outlined text-emerald-500" style="font-size:14px">call</span>
                <?php echo e($job->prospect->phone ?? '-'); ?>

            </a>
            <div class="flex items-start gap-1.5 mt-2 text-slate-500 dark:text-slate-400 text-xs bg-slate-50 dark:bg-slate-800/50 p-2.5 rounded-xl">
                <span class="material-symbols-outlined text-slate-400 mt-0.5 shrink-0" style="font-size:14px">location_on</span>
                <span class="leading-snug"><?php echo e($job->prospect->address ?? 'Alamat tidak tersedia'); ?></span>
            </div>

            
            <div class="mt-3 flex gap-2">
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($job->prospect && $job->prospect->latitude && $job->prospect->longitude): ?>
                    <a href="https://maps.google.com/?q=<?php echo e($job->prospect->latitude); ?>,<?php echo e($job->prospect->longitude); ?>" target="_blank"
                       class="flex-1 py-2.5 bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 rounded-xl font-bold text-sm flex items-center justify-center gap-1.5">
                        <span class="material-symbols-outlined text-blue-500" style="font-size:18px">map</span>
                        Peta
                    </a>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($activeTab === 'active'): ?>
                    <button wire:click="openInputResultModal(<?php echo e($job->id); ?>)"
                            class="flex-[2] py-2.5 bg-indigo-600 text-white rounded-xl font-bold text-sm flex items-center justify-center gap-1.5 active:scale-95 transition-transform">
                        <span class="material-symbols-outlined" style="font-size:18px">edit_document</span>
                        Input Hasil
                    </button>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </div>
        </div>
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_0): ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-10 text-center shadow-sm">
            <div class="w-16 h-16 bg-slate-50 dark:bg-slate-800 rounded-full flex items-center justify-center mx-auto mb-3">
                <span class="material-symbols-outlined text-slate-300 dark:text-slate-600 dark:text-slate-400" style="font-size:36px">
                    <?php echo e($activeTab === 'active' ? 'inbox' : 'history'); ?>

                </span>
            </div>
            <h3 class="text-base font-bold text-slate-900 dark:text-white mb-1">Tidak ada tugas</h3>
            <p class="text-xs text-slate-500 dark:text-slate-400">
                <?php echo e($activeTab === 'active' ? 'Anda belum memiliki tugas survey atau pemasangan.' : 'Belum ada riwayat pekerjaan.'); ?>

            </p>
        </div>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
    </div>

    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($jobs->hasPages()): ?>
    <div class="pt-2"><?php echo e($jobs->links(data: ['scrollTo' => false])); ?></div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

</div>


<?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($showInputResultModal): ?>
<div class="fixed inset-0 z-[100] bg-white dark:bg-slate-900 flex flex-col overflow-hidden">
    
    <div class="px-4 py-3.5 border-b border-slate-100 dark:border-slate-800 flex items-center justify-between bg-white dark:bg-slate-900 sticky top-0 z-10">
        <div>
            <h3 class="text-base font-black text-slate-900 dark:text-white flex items-center gap-2">
                <span class="material-symbols-outlined text-indigo-500" style="font-size:20px">engineering</span>
                Hasil Pekerjaan
            </h3>
            <p class="text-[10px] text-slate-500 dark:text-slate-400 font-medium uppercase tracking-wider mt-0.5">Input data teknis lapangan</p>
        </div>
        <button wire:click="$set('showInputResultModal', false)" class="w-9 h-9 flex items-center justify-center rounded-full bg-slate-100 dark:bg-slate-700 text-slate-600 dark:text-slate-300">
            <span class="material-symbols-outlined" style="font-size:22px">close</span>
        </button>
    </div>

    
    <form wire:submit.prevent="saveSurveyResult" class="flex-1 overflow-y-auto p-4 space-y-6 pb-32">

        <div class="space-y-3">
            <h4 class="text-xs font-bold text-indigo-600 dark:text-indigo-400 uppercase tracking-wider flex items-center gap-1.5 border-b border-indigo-100 dark:border-indigo-900/50 pb-2">
                <span class="material-symbols-outlined" style="font-size:16px">router</span> Detail Koneksi
            </h4>
            <div>
                <label class="block text-xs font-bold text-slate-600 dark:text-slate-400 mb-1.5 uppercase">ODP Terdekat</label>
                <select wire:model="input_odp_id" class="w-full px-4 py-3 border border-slate-200 dark:border-slate-700 rounded-xl text-sm focus:ring-2 focus:ring-indigo-500 bg-slate-50 dark:bg-slate-800 text-slate-900 dark:text-slate-100 dark:bg-slate-900 dark:text-slate-100">
                    <option value="">-- Pilih ODP --</option>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $odps; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $odp): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                        <option value="<?php echo e($odp->id); ?>"><?php echo e($odp->name); ?></option>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                </select>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['input_odp_id'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <p class="text-xs text-red-500 mt-1"><?php echo e($message); ?></p> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </div>
            <div>
                <label class="block text-xs font-bold text-slate-600 dark:text-slate-400 mb-1.5 uppercase">Port</label>
                <input type="text" wire:model="input_port" class="w-full px-4 py-3 border border-slate-200 dark:border-slate-700 rounded-xl text-sm focus:ring-2 focus:ring-indigo-500 bg-slate-50 dark:bg-slate-800 text-slate-900 dark:text-slate-100 dark:bg-slate-900 dark:text-slate-100" placeholder="Contoh: Port 3">
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['input_port'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <p class="text-xs text-red-500 mt-1"><?php echo e($message); ?></p> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </div>
        </div>

        <div class="space-y-3">
            <h4 class="text-xs font-bold text-indigo-600 dark:text-indigo-400 uppercase tracking-wider flex items-center gap-1.5 border-b border-indigo-100 dark:border-indigo-900/50 pb-2">
                <span class="material-symbols-outlined" style="font-size:16px">straighten</span> Estimasi Jarak
            </h4>
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-bold text-slate-600 dark:text-slate-400 mb-1.5 uppercase">Tarikan (m)</label>
                    <input type="number" step="0.1" wire:model="input_distance" class="w-full px-4 py-3 border border-slate-200 dark:border-slate-700 rounded-xl text-sm focus:ring-2 focus:ring-indigo-500 bg-slate-50 dark:bg-slate-800 text-slate-900 dark:text-slate-100 text-center font-bold dark:bg-slate-900 dark:text-slate-100" placeholder="0">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['input_distance'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <p class="text-xs text-red-500 mt-1"><?php echo e($message); ?></p> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-600 dark:text-slate-400 mb-1.5 uppercase">Kabel (m)</label>
                    <input type="number" step="0.1" wire:model="input_cable_estimation" class="w-full px-4 py-3 border border-slate-200 dark:border-slate-700 rounded-xl text-sm focus:ring-2 focus:ring-indigo-500 bg-slate-50 dark:bg-slate-800 text-slate-900 dark:text-slate-100 text-center font-bold dark:bg-slate-900 dark:text-slate-100" placeholder="0">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['input_cable_estimation'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <p class="text-xs text-red-500 mt-1"><?php echo e($message); ?></p> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>
            </div>
        </div>

        <div class="space-y-3">
            <h4 class="text-xs font-bold text-indigo-600 dark:text-indigo-400 uppercase tracking-wider flex items-center gap-1.5 border-b border-indigo-100 dark:border-indigo-900/50 pb-2">
                <span class="material-symbols-outlined" style="font-size:16px">share_location</span> Titik Koordinat (GPS)
            </h4>
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-bold text-slate-600 dark:text-slate-400 mb-1.5 uppercase">Lat</label>
                    <input type="text" wire:model="input_latitude" class="w-full px-4 py-3 border border-slate-200 dark:border-slate-700 rounded-xl text-xs focus:ring-2 focus:ring-indigo-500 bg-slate-50 dark:bg-slate-800 text-slate-900 dark:text-slate-100 text-center dark:bg-slate-900 dark:text-slate-100" placeholder="-6.123">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-600 dark:text-slate-400 mb-1.5 uppercase">Lng</label>
                    <input type="text" wire:model="input_longitude" class="w-full px-4 py-3 border border-slate-200 dark:border-slate-700 rounded-xl text-xs focus:ring-2 focus:ring-indigo-500 bg-slate-50 dark:bg-slate-800 text-slate-900 dark:text-slate-100 text-center dark:bg-slate-900 dark:text-slate-100" placeholder="106.123">
                </div>
            </div>
        </div>

        <div class="space-y-3">
            <h4 class="text-xs font-bold text-indigo-600 dark:text-indigo-400 uppercase tracking-wider flex items-center gap-1.5 border-b border-indigo-100 dark:border-indigo-900/50 pb-2">
                <span class="material-symbols-outlined" style="font-size:16px">verified</span> Kelayakan Instalasi
            </h4>
            <div class="grid grid-cols-2 gap-3">
                <label class="flex flex-col items-center cursor-pointer p-4 border-2 rounded-2xl transition-colors <?php echo e($input_recommendation == 'feasible' ? 'bg-emerald-50 dark:bg-emerald-900/30 border-emerald-500' : 'bg-white dark:bg-slate-800 border-slate-200 dark:border-slate-700'); ?>">
                    <input type="radio" wire:model="input_recommendation" value="feasible" class="sr-only dark:bg-slate-900 dark:text-slate-100">
                    <span class="material-symbols-outlined mb-1 <?php echo e($input_recommendation == 'feasible' ? 'text-emerald-500' : 'text-slate-400'); ?>" style="font-size:32px">check_circle</span>
                    <span class="text-sm font-bold <?php echo e($input_recommendation == 'feasible' ? 'text-emerald-700 dark:text-emerald-400' : 'text-slate-600 dark:text-slate-400'); ?>">LAYAK</span>
                </label>
                <label class="flex flex-col items-center cursor-pointer p-4 border-2 rounded-2xl transition-colors <?php echo e($input_recommendation == 'not_feasible' ? 'bg-red-50 dark:bg-red-900/30 border-red-500' : 'bg-white dark:bg-slate-800 border-slate-200 dark:border-slate-700'); ?>">
                    <input type="radio" wire:model="input_recommendation" value="not_feasible" class="sr-only dark:bg-slate-900 dark:text-slate-100">
                    <span class="material-symbols-outlined mb-1 <?php echo e($input_recommendation == 'not_feasible' ? 'text-red-500' : 'text-slate-400'); ?>" style="font-size:32px">cancel</span>
                    <span class="text-sm font-bold <?php echo e($input_recommendation == 'not_feasible' ? 'text-red-700 dark:text-red-400' : 'text-slate-600 dark:text-slate-400'); ?>">TDK LAYAK</span>
                </label>
            </div>
        </div>

        <div class="space-y-3">
            <h4 class="text-xs font-bold text-indigo-600 dark:text-indigo-400 uppercase tracking-wider flex items-center gap-1.5 border-b border-indigo-100 dark:border-indigo-900/50 pb-2">
                <span class="material-symbols-outlined" style="font-size:16px">speaker_notes</span> Catatan Lapangan
            </h4>
            <textarea wire:model="input_notes" rows="3" class="w-full px-4 py-3 border border-slate-200 dark:border-slate-700 rounded-xl text-sm focus:ring-2 focus:ring-indigo-500 bg-slate-50 dark:bg-slate-800 text-slate-900 dark:text-slate-100 dark:bg-slate-900 dark:text-slate-100" placeholder="Kondisi lapangan, hambatan, permintaan user..."></textarea>
        </div>
    </form>

    
    <div class="p-4 bg-white dark:bg-slate-900 border-t border-slate-100 dark:border-slate-800">
        <button wire:click="saveSurveyResult" class="w-full py-4 bg-indigo-600 text-white rounded-2xl font-bold text-sm flex items-center justify-center gap-2 active:scale-[0.98] transition-transform shadow-md shadow-indigo-600/20">
            <span class="material-symbols-outlined shrink-0" style="font-size:20px">save</span>
            SIMPAN HASIL PEKERJAAN
        </button>
    </div>
</div>
<?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
<?php /**PATH D:\dsBilling\resources\views\livewire\isp\technician\my-jobs\index.blade.php ENDPATH**/ ?>