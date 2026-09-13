<?php $__env->startSection('page_title'); ?>
    <span class="material-symbols-outlined notranslate text-indigo-500" translate="no" style="font-size:24px">group</span>
    <span class="text-lg">Data Pelanggan</span>
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

    
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        
        <div class="relative overflow-x-auto rounded-xl border border-indigo-200 dark:border-indigo-800/60 shadow-md bg-gradient-to-br from-indigo-50 to-white dark:from-indigo-950/50 dark:to-slate-800 group hover:shadow-lg transition-all">
            <div class="absolute top-0 left-0 right-0 h-1 bg-gradient-to-r from-indigo-500 to-blue-400 rounded-t-xl"></div>
            <div class="absolute top-3 right-3 opacity-10 text-indigo-400 group-hover:opacity-20 group-hover:scale-110 transition-all">
                <span class="material-symbols-outlined notranslate" translate="no" style="font-size:56px">group</span>
            </div>
            <div class="p-4 pt-5">
                <h3 class="text-xs font-bold text-indigo-500 dark:text-indigo-400 uppercase tracking-widest mb-2">Total</h3>
                <div class="text-4xl font-black text-indigo-700 dark:text-indigo-300 mb-3"><?php echo e(number_format($totalCustomers)); ?></div>
                <div class="text-xs text-slate-500 dark:text-slate-400">semua pelanggan</div>
            </div>
        </div>
        
        
        <div wire:click="$set('statusFilter','active')" class="relative overflow-x-auto rounded-xl border border-emerald-200 dark:border-emerald-800/60 shadow-md bg-gradient-to-br from-emerald-50 to-white dark:from-emerald-950/50 dark:to-slate-800 group hover:shadow-lg transition-all cursor-pointer">
            <div class="absolute top-0 left-0 right-0 h-1 bg-gradient-to-r from-emerald-500 to-teal-400 rounded-t-xl"></div>
            <div class="absolute top-3 right-3 opacity-10 text-emerald-400 group-hover:opacity-20 group-hover:scale-110 transition-all">
                <span class="material-symbols-outlined notranslate" translate="no" style="font-size:56px">person_check</span>
            </div>
            <div class="p-4 pt-5">
                <h3 class="text-xs font-bold text-emerald-500 dark:text-emerald-400 uppercase tracking-widest mb-2">Aktif</h3>
                <div class="text-4xl font-black text-emerald-700 dark:text-emerald-300 mb-3"><?php echo e(number_format($activeCustomers)); ?></div>
                <div class="text-xs text-slate-500 dark:text-slate-400">klik untuk filter</div>
            </div>
        </div>

        
        <div wire:click="$set('statusFilter','suspend')" class="relative overflow-x-auto rounded-xl border border-red-200 dark:border-red-800/60 shadow-md bg-gradient-to-br from-red-50 to-white dark:from-red-950/50 dark:to-slate-800 group hover:shadow-lg transition-all cursor-pointer">
            <div class="absolute top-0 left-0 right-0 h-1 bg-gradient-to-r from-red-500 to-rose-400 rounded-t-xl"></div>
            <div class="absolute top-3 right-3 opacity-10 text-red-400 group-hover:opacity-20 group-hover:scale-110 transition-all">
                <span class="material-symbols-outlined notranslate" translate="no" style="font-size:56px">person_off</span>
            </div>
            <div class="p-4 pt-5">
                <h3 class="text-xs font-bold text-red-500 dark:text-red-400 uppercase tracking-widest mb-2">Suspend</h3>
                <div class="text-4xl font-black text-red-700 dark:text-red-300 mb-3"><?php echo e(number_format($suspendCustomers)); ?></div>
                <div class="text-xs text-slate-500 dark:text-slate-400">klik untuk filter</div>
            </div>
        </div>

        
        <div class="relative overflow-x-auto rounded-xl border border-sky-200 dark:border-sky-800/60 shadow-md bg-gradient-to-br from-sky-50 to-white dark:from-sky-950/50 dark:to-slate-800 group hover:shadow-lg transition-all">
            <div class="absolute top-0 left-0 right-0 h-1 bg-gradient-to-r from-sky-500 to-cyan-400 rounded-t-xl"></div>
            <div class="absolute top-3 right-3 opacity-10 text-sky-400 group-hover:opacity-20 group-hover:scale-110 transition-all">
                <span class="material-symbols-outlined notranslate" translate="no" style="font-size:56px">person_add</span>
            </div>
            <div class="p-4 pt-5">
                <h3 class="text-xs font-bold text-sky-500 dark:text-sky-400 uppercase tracking-widest mb-2">Baru Bulan Ini</h3>
                <div class="text-4xl font-black text-sky-700 dark:text-sky-300 mb-3"><?php echo e(number_format($newCustomers)); ?></div>
                <div class="text-xs text-slate-500 dark:text-slate-400">pelanggan baru</div>
            </div>
        </div>
    </div>

    
    <div class="bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl shadow-sm p-4">
        <div class="flex flex-col md:flex-row gap-3">
            
            <div class="flex-1 relative">
                <span class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400">
                    <span class="material-symbols-outlined notranslate" translate="no" style="font-size:18px">search</span>
                </span>
                <input type="text" wire:model.live.debounce.300ms="search"
                       placeholder="Cari nama, email, telepon..."
                       class="w-full pl-9 pr-4 py-2 bg-slate-50 dark:bg-slate-900/50 border border-slate-200 dark:border-slate-700 rounded-lg text-sm text-slate-700 dark:text-slate-300 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all dark:bg-slate-900 dark:text-slate-100">
            </div>
            
            <select wire:model.live="statusFilter"
                    class="pl-3 pr-10 py-2 bg-slate-50 dark:bg-slate-900/50 border border-slate-200 dark:border-slate-700 rounded-lg text-sm text-slate-700 dark:text-slate-300 focus:outline-none focus:ring-2 focus:ring-indigo-500 cursor-pointer dark:bg-slate-900 dark:text-slate-100">
                <option value="">Semua Status</option>
                <option value="active">Aktif</option>
                <option value="suspend">Suspend</option>
                <option value="inactive">Nonaktif</option>
            </select>
            
            <select wire:model.live="perPage"
                    class="pl-3 pr-10 py-2 bg-slate-50 dark:bg-slate-900/50 border border-slate-200 dark:border-slate-700 rounded-lg text-sm text-slate-700 dark:text-slate-300 focus:outline-none focus:ring-2 focus:ring-indigo-500 cursor-pointer dark:bg-slate-900 dark:text-slate-100">
                <option value="20">20 / halaman</option>
                <option value="50">50 / halaman</option>
                <option value="100">100 / halaman</option>
                <option value="500">500 / halaman</option>
                <option value="999999">Semua</option>
            </select>
            
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($search || $statusFilter): ?>
            <button wire:click="resetFilters"
                    class="inline-flex items-center gap-1.5 px-3 py-2 bg-red-50 dark:bg-red-900/30 hover:bg-red-100 dark:bg-red-900/50 dark:hover:bg-red-800/50 text-red-600 dark:text-red-400 rounded-lg text-sm font-medium transition-colors cursor-pointer">
                <span class="material-symbols-outlined notranslate" translate="no" style="font-size:16px">filter_alt_off</span>
                Reset
            </button>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            
            
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(count($selectedCustomers) > 0): ?>
                <button wire:click="bulkDelete" wire:confirm="Anda akan menghapus secara permanen <?php echo e(count($selectedCustomers)); ?> pelanggan terpilih beserta layanannya. Tindakan ini tidak dapat dibatalkan." type="button"
                        class="inline-flex items-center gap-1.5 px-3 py-2 bg-red-600 hover:bg-red-700 text-white rounded-lg text-sm font-medium transition-colors cursor-pointer shadow-sm">
                    <span class="material-symbols-outlined notranslate" translate="no" style="font-size:16px">delete_sweep</span>
                    Hapus Terpilih (<?php echo e(count($selectedCustomers)); ?>)
                </button>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </div>
    </div>

    
    <div class="bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-slate-200 dark:border-slate-700 text-xs uppercase tracking-wider text-slate-500 dark:text-slate-400 bg-slate-50/80 dark:bg-slate-800/80">
                        <th class="px-4 py-3 w-10 text-center">
                            <input type="checkbox" wire:model.live="selectAll" class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500 bg-slate-50 dark:bg-slate-900/50 dark:border-slate-600 cursor-pointer dark:bg-slate-900 dark:text-slate-100">
                        </th>
                        <th class="px-4 py-3 text-left font-semibold whitespace-nowrap">
                            <button wire:click="sortBy('id')" class="flex items-center gap-1 hover:text-indigo-600 dark:hover:text-indigo-400 transition-colors cursor-pointer">
                                Id
                                <span class="material-symbols-outlined notranslate" translate="no" style="font-size:14px">unfold_more</span>
                            </button>
                        </th>
                        <th class="px-4 py-3 text-left font-semibold whitespace-nowrap">Nama</th>
                        <th class="px-4 py-3 text-left font-semibold whitespace-nowrap">Username</th>
                        <th class="px-4 py-3 text-left font-semibold whitespace-nowrap">Password</th>
                        <th class="px-4 py-3 text-left font-semibold whitespace-nowrap">Layanan</th>
                        <th class="px-4 py-3 text-left font-semibold whitespace-nowrap">Paket</th>
                        <th class="px-4 py-3 text-left font-semibold whitespace-nowrap">Ip Address</th>
                        <th class="px-4 py-3 text-left font-semibold whitespace-nowrap">Owner/Reseller</th>
                        <th class="px-4 py-3 text-left font-semibold whitespace-nowrap">WhatsApp</th>
                        <th class="px-4 py-3 text-left font-semibold whitespace-nowrap">Status</th>
                        <th class="px-4 py-3 text-left font-semibold whitespace-nowrap">
                            <button wire:click="sortBy('created_at')" class="flex items-center gap-1 hover:text-indigo-600 dark:hover:text-indigo-400 transition-colors cursor-pointer">
                                Tgl Register
                                <span class="material-symbols-outlined notranslate" translate="no" style="font-size:14px">unfold_more</span>
                            </button>
                        </th>
                        <th class="px-4 py-3 text-left font-semibold whitespace-nowrap">Tgl Isolir</th>
                        <th class="px-4 py-3 text-center font-semibold whitespace-nowrap">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-700/50">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $customers; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $customer): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                        <?php
                            $svc = $customer->customerServices->first();
                            $isOnline = false;
                            $ipAddress = '-';
                            $username = '-';
                            $password = '-';
                            $layanan = '-';
                            if ($svc && $svc->pppoeUser) {
                                $isOnline = $svc->pppoeUser->is_online ?? false;
                                $ipAddress = $svc->pppoeUser->static_ip ?? '-';
                                $username = $svc->pppoeUser->username ?? '-';
                                $password = $svc->pppoeUser->password ?? '-';
                                $layanan = 'PPPoE';
                            } elseif ($svc && $svc->hotspotUser) {
                                $isOnline = $svc->hotspotUser->is_online ?? false;
                                $ipAddress = $svc->hotspotUser->static_ip ?? '-';
                                $username = $svc->hotspotUser->username ?? '-';
                                $password = $svc->hotspotUser->password ?? '-';
                                $layanan = 'Hotspot';
                            }
                            
                            $paket = $svc?->serviceProfile?->name ?? '-';
                            $tglRegister = $customer->created_at?->format('d-m-Y');
                            $tglIsolir = $svc?->suspended_at?->format('d-m-Y') ?? '-';
                            $reseller = $customer->reseller->name ?? 'Default';
                            $dynamicId = ($customer->created_at ? $customer->created_at->format('Ymd') : date('Ymd')) . str_pad($customer->id % 100, 2, '0', STR_PAD_LEFT);

                            $isInactive = $customer->status !== 'active';
                            
                            if ($isInactive) {
                                $rowBg = 'bg-slate-50/80 dark:bg-slate-800/40 opacity-75 grayscale-[30%] hover:bg-slate-100 dark:bg-slate-800 dark:hover:bg-slate-800/60';
                                $textPrimary = 'text-slate-500 dark:text-slate-400 line-through decoration-slate-300 dark:decoration-slate-600';
                                $textSecondary = 'text-slate-400 dark:text-slate-500 dark:text-slate-400';
                                $idColor = 'text-slate-500 dark:text-slate-500 dark:text-slate-400';
                            } elseif ($isOnline) {
                                $rowBg = 'bg-emerald-50/40 dark:bg-emerald-900/20 hover:bg-emerald-100 dark:bg-emerald-900/50/50 dark:hover:bg-emerald-900/30';
                                $textPrimary = 'text-emerald-900 dark:text-emerald-100 font-bold';
                                $textSecondary = 'text-emerald-700 dark:text-emerald-300';
                                $idColor = 'text-emerald-800 dark:text-emerald-400 font-semibold';
                            } else {
                                $rowBg = 'hover:bg-indigo-50 dark:bg-indigo-900/30/40 dark:hover:bg-indigo-900/10';
                                $textPrimary = 'text-slate-800 dark:text-slate-100 font-medium';
                                $textSecondary = 'text-slate-600 dark:text-slate-400';
                                $idColor = 'text-slate-700 dark:text-slate-300';
                            }
                        ?>
                        <tr <?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::$currentLoop['key'] = 'customer-'.e($customer->id).''; ?>wire:key="customer-<?php echo e($customer->id); ?>" class="<?php echo e($rowBg); ?> transition-colors group">
                            
                            <td class="px-4 py-3 whitespace-nowrap text-center">
                                <input type="checkbox" wire:model.live="selectedCustomers" value="<?php echo e($customer->id); ?>" 
                                       class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500 cursor-pointer bg-white dark:bg-slate-900 dark:border-slate-600">
                            </td>
                            
                            <td class="px-4 py-3 whitespace-nowrap <?php echo e($idColor); ?> font-mono text-xs flex items-center gap-2">
                                <input type="checkbox" wire:change="toggleStatus(<?php echo e($customer->id); ?>)" 
                                       class="rounded border-slate-300 dark:border-slate-600 text-indigo-600 focus:ring-indigo-500 cursor-pointer"
                                       <?php if(!$isInactive): ?> checked <?php endif; ?>
                                       title="Toggle Aktif/Nonaktif">
                                <?php echo e($dynamicId); ?>

                            </td>
                            
                            
                            <td class="px-4 py-3 whitespace-nowrap <?php echo e($textPrimary); ?>">
                                <a href="<?php echo e(route('crm.customers.show', $customer->id)); ?>" class="hover:text-indigo-600 dark:hover:text-indigo-400 hover:underline transition-colors">
                                    <?php echo e($customer->name ?? '-'); ?>

                                </a>
                            </td>
                            <td class="px-4 py-3">
                                <div class="flex items-center gap-2">
                                    <span class="text-sm font-semibold text-indigo-600 dark:text-indigo-400 font-mono"><?php echo e($username); ?></span>
                                    <button type="button" onclick="navigator.clipboard.writeText('<?php echo e($username); ?>'); alert('Username disalin!')" class="text-slate-400 hover:text-indigo-500 transition-colors" title="Salin Username">
                                        <span class="material-symbols-outlined notranslate" translate="no" style="font-size:14px">content_copy</span>
                                    </button>
                                </div>
                            </td>
                            <td class="px-4 py-3" x-data="{ showPw: false }">
                                <div class="flex items-center gap-2">
                                    <span class="text-xs font-mono text-slate-500 dark:text-slate-400" x-show="!showPw">&bull;&bull;&bull;&bull;&bull;&bull;&bull;&bull;</span>
                                    <span class="text-xs font-mono text-slate-700 dark:text-slate-300" x-show="showPw" x-cloak><?php echo e($password); ?></span>
                                    <button type="button" @click="showPw = !showPw" class="text-slate-400 hover:text-slate-600 dark:text-slate-400 dark:hover:text-slate-300 transition-colors" title="Lihat/Sembunyikan Password">
                                        <span class="material-symbols-outlined notranslate" translate="no" style="font-size:14px" x-text="showPw ? 'visibility_off' : 'visibility'"></span>
                                    </button>
                                    <button type="button" onclick="navigator.clipboard.writeText('<?php echo e($password); ?>'); alert('Password disalin!')" class="text-slate-400 hover:text-indigo-500 transition-colors" title="Salin Password">
                                        <span class="material-symbols-outlined notranslate" translate="no" style="font-size:14px">content_copy</span>
                                    </button>
                                </div>
                            </td>
                            
                            
                            <td class="px-4 py-3 whitespace-nowrap">
                                <span class="inline-flex items-center px-2.5 py-1 rounded-md text-xs font-bold <?php echo e($layanan === 'PPPoE' ? 'bg-indigo-100 text-indigo-700 dark:bg-indigo-900/40 dark:text-indigo-400' : 'bg-amber-100 text-amber-700 dark:bg-amber-900/40 dark:text-amber-400'); ?>">
                                    <?php echo e($layanan); ?>

                                </span>
                            </td>
                            
                            
                            <td class="px-4 py-3 whitespace-nowrap <?php echo e($textSecondary); ?>">
                                <?php echo e($paket); ?>

                            </td>
                            
                            
                            <td class="px-4 py-3 whitespace-nowrap <?php echo e($textSecondary); ?> font-mono text-xs">
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($ipAddress !== '-'): ?>
                                    <a href="http://<?php echo e($ipAddress); ?>" target="_blank" rel="noopener noreferrer" class="hover:text-indigo-600 dark:hover:text-indigo-400 hover:underline transition-colors inline-flex items-center gap-1">
                                        <?php echo e($ipAddress); ?>

                                        <span class="material-symbols-outlined notranslate" translate="no" style="font-size:12px">open_in_new</span>
                                    </a>
                                <?php else: ?>
                                    <?php echo e($ipAddress); ?>

                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            </td>
                            
                            
                            <td class="px-4 py-3 whitespace-nowrap">
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($reseller !== 'Default'): ?>
                                    <span class="inline-flex items-center px-2 py-1 rounded text-xs font-medium bg-purple-100 text-purple-700 dark:bg-purple-900/40 dark:text-purple-400">
                                        <?php echo e($reseller); ?>

                                    </span>
                                <?php else: ?>
                                    <span class="inline-flex items-center px-2 py-1 rounded text-xs font-medium bg-slate-100 text-slate-700 dark:bg-slate-800 dark:text-slate-400">
                                        Pusat (HQ)
                                    </span>
                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            </td>
                            
                            
                            <td class="px-4 py-3 whitespace-nowrap <?php echo e($textSecondary); ?>">
                                <?php echo e($customer->phone ?? '-'); ?>

                            </td>
                            
                            
                            <td class="px-4 py-3 whitespace-nowrap">
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($isInactive || $tglIsolir !== '-'): ?>
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md text-xs font-bold bg-rose-700 text-white shadow-sm">
                                        <span class="w-1.5 h-1.5 rounded-full bg-rose-300"></span>
                                        Suspend
                                    </span>
                                <?php elseif($isOnline): ?>
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md text-xs font-bold bg-green-500 text-white shadow-sm">
                                        <span class="w-1.5 h-1.5 rounded-full bg-white dark:bg-slate-800 animate-pulse"></span>
                                        Online
                                    </span>
                                <?php else: ?>
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md text-xs font-bold bg-red-500 text-white shadow-sm">
                                        <span class="w-1.5 h-1.5 rounded-full bg-red-200"></span>
                                        Offline
                                    </span>
                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            </td>
                            
                            
                            <td class="px-4 py-3 whitespace-nowrap <?php echo e($textSecondary); ?>">
                                <?php echo e($tglRegister); ?>

                            </td>
                            
                            
                            <td class="px-4 py-3 whitespace-nowrap <?php echo e($textSecondary); ?>">
                                <?php echo e($tglIsolir); ?>

                            </td>
                            
                            
                            <td class="px-4 py-3 whitespace-nowrap">
                                <div class="flex items-center justify-center gap-2">
                                    
                                    <a href="<?php echo e(route('crm.customers.show', $customer->id)); ?>" class="p-1.5 bg-amber-50 dark:bg-amber-900/30 text-amber-600 rounded hover:bg-amber-100 dark:bg-amber-900/50 dark:hover:bg-amber-900/50 transition-colors" title="Edit / Detail">
                                        <span class="material-symbols-outlined notranslate" translate="no" style="font-size:16px">edit</span>
                                    </a>
                                    
                                    <a href="#" class="p-1.5 bg-sky-50 dark:bg-sky-900/30 text-sky-600 rounded hover:bg-sky-100 dark:hover:bg-sky-900/50 transition-colors" title="Portal Customer">
                                        <span class="material-symbols-outlined notranslate" translate="no" style="font-size:16px">language</span>
                                    </a>
                                    
                                    <button wire:click="delete(<?php echo e($customer->id); ?>)" wire:confirm="Yakin ingin menghapus pelanggan ini?" class="p-1.5 bg-red-50 dark:bg-red-900/30 text-red-600 rounded hover:bg-red-100 dark:bg-red-900/50 dark:hover:bg-red-900/50 transition-colors" title="Hapus">
                                        <span class="material-symbols-outlined notranslate" translate="no" style="font-size:16px">delete</span>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                        <tr>
                            <td colspan="14" class="px-4 py-10 text-center">
                                <div class="flex flex-col items-center gap-2 text-slate-400 dark:text-slate-500 dark:text-slate-400">
                                    <span class="material-symbols-outlined notranslate" translate="no" style="font-size:48px">manage_search</span>
                                    <p class="font-semibold text-slate-500 dark:text-slate-400">Tidak ada data pelanggan</p>
                                </div>
                            </td>
                        </tr>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </tbody>
            </table>
        </div>
        
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($customers->hasPages()): ?>
        <div class="px-4 py-3 border-t border-slate-200 dark:border-slate-700 bg-slate-50/50 dark:bg-slate-800/50">
            <?php echo e($customers->links()); ?>

        </div>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
    </div>

</div>
<?php /**PATH D:\dsBilling\resources\views\livewire\crm\customer\index.blade.php ENDPATH**/ ?>