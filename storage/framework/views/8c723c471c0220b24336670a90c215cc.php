<?php $__env->startSection('header_title', 'Absensi'); ?>

<div class="space-y-4 p-4">

    
    <div class="bg-gradient-to-br from-indigo-600 to-blue-700 text-white p-5 rounded-2xl shadow-lg relative overflow-hidden text-center"
         x-data="{
             currentTime: '--:--:--',
             init() {
                 this.tick();
                 setInterval(() => this.tick(), 1000);
             },
             tick() {
                 try {
                     const now = new Date();
                     const h = String(now.getHours()).padStart(2, '0');
                     const m = String(now.getMinutes()).padStart(2, '0');
                     const s = String(now.getSeconds()).padStart(2, '0');
                     this.currentTime = h + ':' + m + ':' + s;
                 } catch (e) {
                     this.currentTime = 'Error';
                 }
             }
         }" x-init="init()">
        <div class="absolute top-0 right-0 opacity-10 pointer-events-none">
            <span class="material-symbols-outlined" style="font-size:130px">fingerprint</span>
        </div>
        <div class="relative z-10">
            <p class="text-indigo-200 text-xs font-semibold uppercase tracking-wider mb-2">
                <?php echo e(\Carbon\Carbon::now()->translatedFormat('l, d F Y')); ?>

            </p>
            <div class="text-5xl font-black tracking-wider font-mono mb-1" x-text="currentTime">--:--:--</div>
            <p class="text-indigo-200 text-xs">Pastikan Anda berada di area kerja.</p>
        </div>
    </div>

    
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($errorMessage): ?>
        <div class="bg-red-50 dark:bg-red-900/30 p-4 rounded-2xl border border-red-200 dark:border-red-800/50 flex gap-3 text-red-800 dark:text-red-400">
            <span class="material-symbols-outlined shrink-0" style="font-size:20px">error</span>
            <p class="text-xs font-bold self-center"><?php echo e($errorMessage); ?></p>
        </div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(session('success')): ?>
        <div class="bg-emerald-50 dark:bg-emerald-900/30 p-4 rounded-2xl border border-emerald-200 dark:border-emerald-800/50 flex gap-3 text-emerald-800 dark:text-emerald-400">
            <span class="material-symbols-outlined shrink-0" style="font-size:20px">check_circle</span>
            <p class="text-xs font-bold self-center"><?php echo e(session('success')); ?></p>
        </div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

    
    <div class="bg-white dark:bg-slate-900 rounded-2xl shadow-sm border border-slate-200 dark:border-slate-800 overflow-hidden"
         x-data="{
             gpsLoading: true,
             gpsError: false,
             init() {
                 this.getLocation();
             },
             getLocation() {
                 try {
                     this.gpsLoading = true;
                     this.gpsError = false;
                     if (!navigator.geolocation) {
                         this.gpsLoading = false;
                         this.gpsError = true;
                         return;
                     }
                     navigator.geolocation.getCurrentPosition(
                         (pos) => {
                             this.$dispatch('gps-located', { lat: pos.coords.latitude, lng: pos.coords.longitude });
                             this.gpsLoading = false;
                             this.gpsError = false;
                         },
                         (err) => {
                             this.gpsLoading = false;
                             this.gpsError = true;
                         },
                         { enableHighAccuracy: true, timeout: 15000, maximumAge: 0 }
                     );
                 } catch (e) {
                     this.gpsLoading = false;
                     this.gpsError = true;
                 }
             }
         }" x-init="init()">

        
        <div class="flex items-center justify-between px-4 py-3 border-b border-slate-100 dark:border-slate-800 bg-slate-50 dark:bg-slate-800/50">
            <div class="flex items-center gap-3">
                <div class="w-9 h-9 rounded-full flex items-center justify-center shrink-0 transition-colors"
                     :class="gpsLoading ? 'bg-blue-100 dark:bg-blue-900/50 text-blue-500' : (gpsError ? 'bg-red-100 dark:bg-red-900/50 text-red-500' : 'bg-emerald-100 dark:bg-emerald-900/50 text-emerald-500')">
                    <span class="material-symbols-outlined" style="font-size:18px"
                          :class="gpsLoading ? 'animate-spin' : ''"
                          x-text="gpsLoading ? 'sync' : (gpsError ? 'location_disabled' : 'my_location')">
                    </span>
                </div>
                <div>
                    <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">GPS</p>
                    <p class="text-xs font-bold"
                       :class="gpsLoading ? 'text-blue-600 dark:text-blue-400' : (gpsError ? 'text-red-600 dark:text-red-400' : 'text-emerald-600 dark:text-emerald-400')"
                       x-text="gpsLoading ? 'Mencari lokasi...' : (gpsError ? 'Tidak Ditemukan' : 'Lokasi Aktif')">
                    </p>
                </div>
            </div>
            <button @click="getLocation()" class="p-1.5 text-slate-400 hover:text-indigo-600 rounded-full hover:bg-slate-100 dark:bg-slate-800 dark:hover:bg-slate-700 transition-colors">
                <span class="material-symbols-outlined" style="font-size:20px">refresh</span>
            </button>
        </div>

        
        <div class="p-5">
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(!$attendance): ?>
                
                <div class="space-y-4">
                    <div class="text-center py-2">
                        <span class="inline-block px-4 py-1.5 bg-slate-100 dark:bg-slate-800 text-slate-500 dark:text-slate-400 rounded-full text-xs font-black uppercase tracking-wider">
                            Belum Hadir
                        </span>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-500 dark:text-slate-400 mb-1.5 uppercase">Catatan (Opsional)</label>
                        <input type="text" wire:model="notes"
                               class="w-full px-4 py-3 border border-slate-200 dark:border-slate-700 rounded-xl text-sm focus:ring-2 focus:ring-indigo-500 bg-slate-50 dark:bg-slate-800 text-slate-900 dark:text-slate-100 dark:bg-slate-900 dark:text-slate-100"
                               placeholder="Kondisi kesehatan, alasan, dll...">
                    </div>
                    <button wire:click="checkIn"
                            x-bind:disabled="gpsLoading || gpsError"
                            class="w-full py-4 bg-emerald-600 disabled:opacity-50 disabled:cursor-not-allowed text-white rounded-2xl font-black text-base flex items-center justify-center gap-2 hover:bg-emerald-700 active:scale-[0.98] transition-all shadow-lg shadow-emerald-600/30">
                        <span class="material-symbols-outlined shrink-0" style="font-size:24px">how_to_reg</span>
                        CHECK IN SEKARANG
                    </button>
                </div>

            <?php elseif($attendance && !$attendance->checked_out_at): ?>
                
                <div class="space-y-4">
                    <div class="text-center py-2">
                        <p class="text-xs font-bold text-slate-400 uppercase mb-1">Check-In Pukul</p>
                        <div class="flex items-center justify-center gap-2 text-emerald-600 dark:text-emerald-400">
                            <span class="material-symbols-outlined" style="font-size:28px">schedule</span>
                            <span class="text-4xl font-black font-mono"><?php echo e($attendance->checked_in_at->format('H:i')); ?></span>
                        </div>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-500 dark:text-slate-400 mb-1.5 uppercase">Catatan Akhir Hari (Opsional)</label>
                        <input type="text" wire:model="notes"
                               class="w-full px-4 py-3 border border-slate-200 dark:border-slate-700 rounded-xl text-sm focus:ring-2 focus:ring-indigo-500 bg-slate-50 dark:bg-slate-800 text-slate-900 dark:text-slate-100 dark:bg-slate-900 dark:text-slate-100"
                               placeholder="Laporan kegiatan hari ini...">
                    </div>
                    <button wire:click="checkOut"
                            x-bind:disabled="gpsLoading || gpsError"
                            class="w-full py-4 bg-amber-500 disabled:opacity-50 disabled:cursor-not-allowed text-white rounded-2xl font-black text-base flex items-center justify-center gap-2 hover:bg-amber-600 active:scale-[0.98] transition-all shadow-lg shadow-amber-500/30">
                        <span class="material-symbols-outlined shrink-0" style="font-size:24px">logout</span>
                        CHECK OUT SEKARANG
                    </button>
                </div>

            <?php else: ?>
                
                <div class="text-center py-4">
                    <div class="w-20 h-20 bg-emerald-100 dark:bg-emerald-900/30 text-emerald-600 dark:text-emerald-400 rounded-full flex items-center justify-center mx-auto mb-4">
                        <span class="material-symbols-outlined" style="font-size:44px;font-variation-settings:'FILL' 1">verified</span>
                    </div>
                    <h3 class="text-xl font-black text-slate-900 dark:text-white mb-1">Tugas Selesai!</h3>
                    <p class="text-slate-400 text-sm">Anda telah menyelesaikan jam kerja hari ini.</p>
                    <div class="mt-5 flex items-center justify-center gap-8 p-4 bg-slate-50 dark:bg-slate-800 rounded-2xl border border-slate-100 dark:border-slate-700">
                        <div class="text-center">
                            <p class="text-[10px] font-bold text-slate-400 uppercase">Masuk</p>
                            <p class="font-mono font-black text-slate-700 dark:text-slate-300 text-2xl mt-0.5"><?php echo e($attendance->checked_in_at->format('H:i')); ?></p>
                        </div>
                        <div class="w-px h-10 bg-slate-200 dark:bg-slate-700"></div>
                        <div class="text-center">
                            <p class="text-[10px] font-bold text-slate-400 uppercase">Pulang</p>
                            <p class="font-mono font-black text-slate-700 dark:text-slate-300 text-2xl mt-0.5"><?php echo e($attendance->checked_out_at->format('H:i')); ?></p>
                        </div>
                    </div>
                </div>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </div>
    </div>

    
    <div class="bg-white dark:bg-slate-900 rounded-2xl shadow-sm border border-slate-200 dark:border-slate-800 overflow-hidden">
        <div class="px-4 py-3.5 border-b border-slate-100 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-800/50 flex items-center gap-2">
            <span class="material-symbols-outlined text-indigo-500" style="font-size:20px">calendar_month</span>
            <h2 class="font-bold text-slate-800 dark:text-slate-200 text-sm uppercase tracking-wider">5 Hari Terakhir</h2>
        </div>
        <div class="divide-y divide-slate-100 dark:divide-slate-800">
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__empty_0 = true; $__currentLoopData = $history; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $hist): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_0 = false; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                <div class="px-4 py-3 flex items-center justify-between">
                    <div>
                        <p class="font-bold text-slate-900 dark:text-slate-100 text-sm"><?php echo e($hist->date->format('d M Y')); ?></p>
                        <div class="flex gap-4 mt-0.5">
                            <span class="text-xs text-slate-400 font-mono">
                                <span class="text-emerald-500 font-bold">IN</span> <?php echo e($hist->checked_in_at ? $hist->checked_in_at->format('H:i') : '-'); ?>

                            </span>
                            <span class="text-xs text-slate-400 font-mono">
                                <span class="text-amber-500 font-bold">OUT</span> <?php echo e($hist->checked_out_at ? $hist->checked_out_at->format('H:i') : '-'); ?>

                            </span>
                        </div>
                    </div>
                    <div>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($hist->status->value === 'checked_in'): ?>
                            <span class="px-2.5 py-1 bg-blue-50 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400 rounded-full text-[10px] font-black uppercase">Aktif</span>
                        <?php elseif($hist->status->value === 'checked_out'): ?>
                            <span class="px-2.5 py-1 bg-emerald-50 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400 rounded-full text-[10px] font-black uppercase">Selesai</span>
                        <?php elseif($hist->status->value === 'late'): ?>
                            <span class="px-2.5 py-1 bg-amber-50 text-amber-700 dark:bg-amber-900/30 dark:text-amber-400 rounded-full text-[10px] font-black uppercase">Telat</span>
                        <?php else: ?>
                            <span class="px-2.5 py-1 bg-red-50 text-red-700 dark:bg-red-900/30 dark:text-red-400 rounded-full text-[10px] font-black uppercase">Absen</span>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </div>
                </div>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_0): ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                <div class="p-8 text-center">
                    <span class="material-symbols-outlined text-slate-300 dark:text-slate-600 dark:text-slate-400" style="font-size:36px">event_busy</span>
                    <p class="text-sm font-bold text-slate-400 mt-2">Belum ada riwayat absensi.</p>
                </div>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </div>
    </div>

</div>

<?php /**PATH D:\dsBilling\resources\views\livewire\isp\technician\attendance\index.blade.php ENDPATH**/ ?>