
<div>
    <div x-data="{ subtitle: 'Periode: <?php echo e(\Carbon\Carbon::parse($filters['start_date'] ?? now())->translatedFormat('d F Y')); ?> s/d <?php echo e(\Carbon\Carbon::parse($filters['end_date'] ?? now())->translatedFormat('d F Y')); ?>' }" x-effect="if(document.getElementById('dynamic-subtitle-periode')) document.getElementById('dynamic-subtitle-periode').innerHTML = subtitle"></div>
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-slate-800 dark:text-slate-100">Pemasukan Periode</h1>
        <p class="text-slate-500 dark:text-slate-400 mt-1">Periode : <span class="text-emerald-600 font-bold dark:text-emerald-500"><?php echo e(\Carbon\Carbon::parse($filters['start_date'] ?? now())->translatedFormat('d F Y')); ?> s/d <?php echo e(\Carbon\Carbon::parse($filters['end_date'] ?? now())->translatedFormat('d F Y')); ?></span></p>
    </div>
    <div class="space-y-4">
        
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div class="relative overflow-x-auto rounded-xl border border-emerald-200 dark:border-emerald-800/60 shadow-sm bg-gradient-to-br from-emerald-50 to-white dark:from-emerald-950/50 dark:to-slate-800">
                <div class="absolute top-0 left-0 right-0 h-1 bg-gradient-to-r from-emerald-500 to-green-400 rounded-t-xl"></div>
                <div class="p-4 flex items-center justify-between">
                    <div>
                        <h3 class="text-xs font-bold text-emerald-600 dark:text-emerald-500 uppercase tracking-widest mb-1">NETT SETELAH RESELLER</h3>
                        <span class="text-xl md:text-2xl font-black text-slate-800 dark:text-slate-100 tracking-tight">Rp <?php echo e(number_format($summary['profit'] ?? 0, 0, ',', '.')); ?></span>
                        <p class="text-[10px] text-slate-400 mt-0.5">Bruto dikurangi komisi reseller</p>
                    </div>
                    <div class="w-10 h-10 rounded-full bg-emerald-100 dark:bg-emerald-900/40 flex items-center justify-center text-emerald-600 dark:text-emerald-400">
                        <span class="material-symbols-outlined notranslate">check_circle</span>
                    </div>
                </div>
            </div>

            <div class="relative overflow-x-auto rounded-xl border border-orange-200 dark:border-orange-800/60 shadow-sm bg-gradient-to-br from-orange-50 to-white dark:from-orange-950/50 dark:to-slate-800">
                <div class="absolute top-0 left-0 right-0 h-1 bg-gradient-to-r from-orange-500 to-amber-400 rounded-t-xl"></div>
                <div class="p-4 flex items-center justify-between">
                    <div>
                        <h3 class="text-xs font-bold text-orange-600 dark:text-orange-500 uppercase tracking-widest mb-1">KOMISI RESELLER</h3>
                        <span class="text-xl md:text-2xl font-black text-slate-800 dark:text-slate-100 tracking-tight">Rp <?php echo e(number_format($summary['fee_seller'] ?? 0, 0, ',', '.')); ?></span>
                        <p class="text-[10px] text-slate-400 mt-0.5">Margin harga jual - harga reseller</p>
                    </div>
                    <div class="w-10 h-10 rounded-full bg-orange-100 dark:bg-orange-900/40 flex items-center justify-center text-orange-600 dark:text-orange-400">
                        <span class="material-symbols-outlined notranslate">groups</span>
                    </div>
                </div>
            </div>

            <div class="relative overflow-x-auto rounded-xl border border-slate-200 dark:border-slate-700 shadow-sm bg-gradient-to-br from-slate-50 to-white dark:from-slate-800 dark:to-slate-900">
                <div class="absolute top-0 left-0 right-0 h-1 bg-gradient-to-r from-slate-500 to-gray-400 rounded-t-xl"></div>
                <div class="p-4 flex items-center justify-between">
                    <div>
                        <h3 class="text-xs font-bold text-slate-600 dark:text-slate-400 uppercase tracking-widest mb-1">TOTAL BRUTO TERBAYAR</h3>
                        <span class="text-xl md:text-2xl font-black text-slate-800 dark:text-slate-100 tracking-tight">Rp <?php echo e(number_format($summary['total_ppn'] ?? 0, 0, ',', '.')); ?></span>
                        <p class="text-[10px] text-slate-400 mt-0.5">Total pembayaran yang diterima</p>
                    </div>
                    <div class="w-10 h-10 rounded-full bg-slate-100 dark:bg-slate-700/50 flex items-center justify-center text-slate-600 dark:text-slate-300">
                        <span class="material-symbols-outlined notranslate">payments</span>
                    </div>
                </div>
            </div>
        </div>

        
        <div class="bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl shadow-sm">
            
            <div class="p-4 border-b border-slate-200 dark:border-slate-700 bg-slate-50/50 dark:bg-slate-900/20 flex flex-col md:flex-row md:items-center justify-between gap-4 rounded-t-xl">
                <div class="flex items-center gap-2 flex-wrap flex-1">
                    <div x-data="{ open: false }" @click.away="open = false" class="relative z-50">
                        <button @click="open = !open" type="button" class="px-4 py-2 bg-indigo-500 hover:bg-indigo-600 text-white rounded-lg text-sm font-medium transition-colors flex items-center gap-2">
                            <span class="material-symbols-outlined notranslate" style="font-size:18px">calendar_month</span> Pilih Periode
                        </button>
                        
                        <div x-show="open" x-transition style="display: none;" class="absolute top-full left-0 mt-2 w-64 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl shadow-xl p-4">
                            <div class="mb-3">
                                <label class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1">Dari</label>
                                <input type="date" wire:model.defer="filters.start_date" class="w-full bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-slate-700 dark:text-slate-200 text-sm rounded-lg focus:ring-indigo-500 focus:border-indigo-500 block px-3 py-2 dark:bg-slate-900 dark:text-slate-100">
                            </div>
                            <div class="mb-4">
                                <label class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1">Sampai</label>
                                <input type="date" wire:model.defer="filters.end_date" class="w-full bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-slate-700 dark:text-slate-200 text-sm rounded-lg focus:ring-indigo-500 focus:border-indigo-500 block px-3 py-2 dark:bg-slate-900 dark:text-slate-100">
                            </div>
                            <div class="space-y-2">
                                <button @click="open = false; $wire.$refresh()" type="button" class="w-full px-4 py-2 bg-indigo-500 hover:bg-indigo-600 text-white rounded-lg text-sm font-medium transition-colors">
                                    Terapkan
                                </button>
                                <button @click="open = false; $wire.set('filters.start_date', ''); $wire.set('filters.end_date', '');" type="button" class="w-full px-4 py-2 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-600 text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:bg-slate-900/50 dark:hover:bg-slate-700 rounded-lg text-sm font-medium transition-colors">
                                    Lihat Semua Data
                                </button>
                            </div>
                        </div>
                    </div>
                    
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(isset($this->filterConfig) && is_array($this->filterConfig)): ?>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $this->filterConfig; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $f): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($f['type'] === 'select'): ?>
                                <select wire:model.live="filters.<?php echo e($f['key']); ?>" class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-slate-700 dark:text-slate-300 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block pl-3 pr-10 py-2 dark:bg-slate-900 dark:text-slate-100">
                                    <option value="all"><?php echo e($f['label']); ?></option>
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $f['options']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $val => $lbl): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                                        <option value="<?php echo e($val); ?>"><?php echo e($lbl); ?></option>
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                                </select>
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>
                
                <div class="flex items-center gap-2">
                    <button wire:click="exportExcel" type="button" class="px-4 py-2 bg-emerald-50 text-emerald-600 dark:bg-emerald-900/20 border border-emerald-200 dark:border-emerald-800 dark:text-emerald-400 rounded-lg text-sm font-medium hover:bg-emerald-100 dark:bg-emerald-900/50 dark:hover:bg-emerald-900/40 transition-colors flex items-center gap-2">
                        <span class="material-symbols-outlined notranslate" style="font-size:18px">table_view</span> Excel
                    </button>
                    <button wire:click="exportPdf" type="button" class="px-4 py-2 bg-rose-50 text-rose-600 dark:bg-rose-900/20 border border-rose-200 dark:border-rose-800 dark:text-rose-400 rounded-lg text-sm font-medium hover:bg-rose-100 dark:bg-rose-900/50 dark:hover:bg-rose-900/40 transition-colors flex items-center gap-2">
                        <span class="material-symbols-outlined notranslate" style="font-size:18px">picture_as_pdf</span> PDF
                    </button>
                </div>
            </div>

            
            <div class="overflow-x-auto">
                <table class="w-full text-sm text-left">
                    <thead class="text-xs text-slate-500 dark:text-slate-400 bg-slate-50 dark:bg-slate-900/50 uppercase border-b border-slate-200 dark:border-slate-700">
                        <tr>
                            <th scope="col" class="px-3 py-3 whitespace-nowrap">Id</th>
                            <th scope="col" class="px-3 py-3 whitespace-nowrap">Invoice</th>
                            <th scope="col" class="px-3 py-3 whitespace-nowrap">ID Pelanggan</th>
                            <th scope="col" class="px-3 py-3 whitespace-nowrap">Nama</th>
                            <th scope="col" class="px-3 py-3 whitespace-nowrap">Tipe Service</th>
                            <th scope="col" class="px-3 py-3 whitespace-nowrap">Paket Langganan</th>
                            <th scope="col" class="px-3 py-3 whitespace-nowrap text-right">Total Bruto</th>
                            <th scope="col" class="px-3 py-3 whitespace-nowrap text-right">Komisi Reseller</th>
                            <th scope="col" class="px-3 py-3 whitespace-nowrap text-right">Nett (Stlh Reseller)</th>
                            <th scope="col" class="px-3 py-3 whitespace-nowrap">Tanggal Aktif</th>
                            <th scope="col" class="px-3 py-3 whitespace-nowrap">Owner Data</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-700/60">
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__empty_0 = true; $__currentLoopData = $rows; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $r): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_0 = false; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                            <tr class="hover:bg-slate-50 dark:bg-slate-900/50 dark:hover:bg-slate-700/30">
                                <td class="px-3 py-2 whitespace-nowrap text-slate-700 dark:text-slate-300"><?php echo e($r['id'] ?? '-'); ?></td>
                                <td class="px-3 py-2 whitespace-nowrap font-medium text-slate-900 dark:text-slate-100"><?php echo e($r['invoice_number'] ?? '-'); ?></td>
                                <td class="px-3 py-2 whitespace-nowrap text-slate-700 dark:text-slate-300"><?php echo e($r['customer_id'] ?? '-'); ?></td>
                                <td class="px-3 py-2 whitespace-nowrap font-medium text-slate-900 dark:text-slate-100">
                                    <div class="flex items-center gap-1">
                                        <span class="material-symbols-outlined notranslate text-slate-400" style="font-size:16px">account_circle</span>
                                        <?php echo e($r['customer_name'] ?? '-'); ?>

                                    </div>
                                </td>
                                <td class="px-3 py-2 whitespace-nowrap">
                                    <span class="px-2 py-0.5 rounded text-[10px] font-bold tracking-wide <?php echo e(str_contains(strtolower($r['service_type'] ?? ''), 'hotspot') ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400' : 'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400'); ?>">
                                        <?php echo e($r['service_type'] ?? '-'); ?>

                                    </span>
                                </td>
                                <td class="px-3 py-2 whitespace-nowrap text-slate-700 dark:text-slate-300"><?php echo e($r['package_name'] ?? '-'); ?></td>
                                <td class="px-3 py-2 whitespace-nowrap text-right font-medium text-slate-700 dark:text-slate-300">Rp. <?php echo e(number_format($r['harga_ppn'] ?? 0, 0, ',', '.')); ?></td>
                                <td class="px-3 py-2 whitespace-nowrap text-right text-slate-700 dark:text-slate-300">Rp. <?php echo e(number_format($r['fee_seller'] ?? 0, 0, ',', '.')); ?></td>
                                <td class="px-3 py-2 whitespace-nowrap text-right font-semibold text-emerald-700 dark:text-emerald-400">Rp. <?php echo e(number_format($r['profit'] ?? 0, 0, ',', '.')); ?></td>
                                <td class="px-3 py-2 whitespace-nowrap text-slate-700 dark:text-slate-300"><?php echo e($r['paid_at'] ?? '-'); ?></td>
                                <td class="px-3 py-2 whitespace-nowrap text-slate-700 dark:text-slate-300"><?php echo e($r['reseller_name'] ?? '-'); ?></td>
                            </tr>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_0): ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                            <tr>
                                <td colspan="11" class="px-3 py-12 text-center text-slate-500 dark:text-slate-400">
                                    <svg class="mx-auto mb-2 w-10 h-10 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                    Tidak ada data untuk periode ini.
                                </td>
                            </tr>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </tbody>
                </table>
            </div>

            
            <div class="p-3 border-t border-slate-200 dark:border-slate-700 bg-slate-50/50 dark:bg-slate-900/20 rounded-b-xl">
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(method_exists($rows, 'links')): ?>
                    <?php echo e($rows->links('livewire::tailwind')); ?>

                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </div>
        </div>
    </div>
    
    <?php echo $__env->make('partials.enterprise.confirm-modal', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
</div><?php /**PATH D:\dsBilling\resources\views\livewire\keuangan\income-periode\index.blade.php ENDPATH**/ ?>