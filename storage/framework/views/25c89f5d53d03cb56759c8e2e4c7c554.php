<div <?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::$currentLoop['key'] = 'keuangan-topup-reseller-'.e(now()->timestamp).''; ?>wire:key="keuangan-topup-reseller-<?php echo e(now()->timestamp); ?>">
    <?php $__env->startSection('page_title'); ?>
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl bg-blue-500/10 flex items-center justify-center text-blue-600 dark:text-blue-400">
                <span class="material-symbols-outlined notranslate" translate="no">account_balance</span>
            </div>
            <div>
                <h1 class="text-xl font-bold text-slate-900 dark:text-slate-100 leading-tight">Deposit & Top-up Reseller</h1>
                <p class="text-sm text-slate-500 dark:text-slate-400">Manajemen pengisian saldo wallet reseller dan bukti transfer</p>
            </div>
        </div>
    <?php $__env->stopSection(); ?>

    <div class="space-y-4">
        
        <div class="grid grid-cols-2 md:grid-cols-5 gap-4">
            
            <div class="relative overflow-x-auto rounded-xl border border-blue-200 dark:border-blue-800/60 shadow-sm bg-gradient-to-br from-blue-50 to-white dark:from-blue-950/50 dark:to-slate-800">
                <div class="absolute top-0 left-0 right-0 h-1 bg-gradient-to-r from-blue-500 to-cyan-400 rounded-t-xl"></div>
                <div class="p-3 pt-4">
                    <h3 class="text-[10px] font-bold text-blue-600 dark:text-blue-500 uppercase tracking-widest mb-1">Topup Bulan Ini</h3>
                    <div class="flex items-baseline gap-1">
                        <span class="text-lg md:text-xl font-black text-slate-800 dark:text-slate-100 tracking-tight">Rp <?php echo e(number_format($summary['monthly_total'] ?? 0, 0, ',', '.')); ?></span>
                    </div>
                </div>
            </div>

            
            <div class="relative overflow-x-auto rounded-xl border border-amber-200 dark:border-amber-800/60 shadow-sm bg-gradient-to-br from-amber-50 to-white dark:from-amber-950/50 dark:to-slate-800">
                <div class="absolute top-0 left-0 right-0 h-1 bg-gradient-to-r from-amber-500 to-orange-400 rounded-t-xl"></div>
                <div class="p-3 pt-4">
                    <h3 class="text-[10px] font-bold text-amber-600 dark:text-amber-500 uppercase tracking-widest mb-1">Pending Approval</h3>
                    <div class="flex items-baseline gap-1">
                        <span class="text-lg md:text-xl font-black text-slate-800 dark:text-slate-100 tracking-tight"><?php echo e(number_format($summary['pending_count'] ?? 0, 0, ',', '.')); ?></span>
                        <span class="text-xs text-slate-500 dark:text-slate-400">trx</span>
                    </div>
                </div>
            </div>

            
            <div class="relative overflow-x-auto rounded-xl border border-emerald-200 dark:border-emerald-800/60 shadow-sm bg-gradient-to-br from-emerald-50 to-white dark:from-emerald-950/50 dark:to-slate-800">
                <div class="absolute top-0 left-0 right-0 h-1 bg-gradient-to-r from-emerald-500 to-green-400 rounded-t-xl"></div>
                <div class="p-3 pt-4">
                    <h3 class="text-[10px] font-bold text-emerald-600 dark:text-emerald-500 uppercase tracking-widest mb-1">Disetujui</h3>
                    <div class="flex items-baseline gap-1">
                        <span class="text-lg md:text-xl font-black text-slate-800 dark:text-slate-100 tracking-tight"><?php echo e(number_format($summary['approved_count'] ?? 0, 0, ',', '.')); ?></span>
                        <span class="text-xs text-slate-500 dark:text-slate-400">trx</span>
                    </div>
                </div>
            </div>

            
            <div class="relative overflow-x-auto rounded-xl border border-red-200 dark:border-red-800/60 shadow-sm bg-gradient-to-br from-red-50 to-white dark:from-red-950/50 dark:to-slate-800">
                <div class="absolute top-0 left-0 right-0 h-1 bg-gradient-to-r from-red-500 to-rose-400 rounded-t-xl"></div>
                <div class="p-3 pt-4">
                    <h3 class="text-[10px] font-bold text-red-600 dark:text-red-500 uppercase tracking-widest mb-1">Ditolak</h3>
                    <div class="flex items-baseline gap-1">
                        <span class="text-lg md:text-xl font-black text-slate-800 dark:text-slate-100 tracking-tight"><?php echo e(number_format($summary['rejected_count'] ?? 0, 0, ',', '.')); ?></span>
                        <span class="text-xs text-slate-500 dark:text-slate-400">trx</span>
                    </div>
                </div>
            </div>
            
            
            <div class="relative overflow-x-auto rounded-xl border border-purple-200 dark:border-purple-800/60 shadow-sm bg-gradient-to-br from-purple-50 to-white dark:from-purple-950/50 dark:to-slate-800">
                <div class="absolute top-0 left-0 right-0 h-1 bg-gradient-to-r from-purple-500 to-fuchsia-400 rounded-t-xl"></div>
                <div class="p-3 pt-4">
                    <h3 class="text-[10px] font-bold text-purple-600 dark:text-purple-500 uppercase tracking-widest mb-1">Total Saldo Aktif</h3>
                    <div class="flex items-baseline gap-1">
                        <span class="text-lg md:text-xl font-black text-slate-800 dark:text-slate-100 tracking-tight">Rp <?php echo e(number_format($summary['total_reseller_balance'] ?? 0, 0, ',', '.')); ?></span>
                    </div>
                </div>
            </div>
        </div>

        
        <div class="flex flex-col sm:flex-row gap-3 items-center justify-between bg-white dark:bg-slate-800 p-4 rounded-xl shadow-sm border border-slate-200 dark:border-slate-700">
            <div class="flex-1 w-full relative flex gap-2">
                <div class="relative w-full sm:w-64">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <span class="material-symbols-outlined notranslate text-slate-400" translate="no" style="font-size: 18px">search</span>
                    </div>
                    <input type="text" wire:model.live.debounce.300ms="search" placeholder="Cari reseller / ref..." class="bg-slate-50 dark:bg-slate-900/50 border border-slate-200 dark:border-slate-700 text-slate-900 dark:text-slate-100 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full pl-10 px-3 py-2 dark:bg-slate-900 dark:text-slate-100">
                </div>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $this->filterConfig; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $f): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($f['type'] === 'select'): ?>
                        <select wire:model.live="filters.<?php echo e($f['key']); ?>" class="bg-slate-50 dark:bg-slate-900/50 border border-slate-200 dark:border-slate-700 text-slate-700 dark:text-slate-300 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block px-3 py-2 min-w-[120px] dark:bg-slate-900 dark:text-slate-100">
                            <option value=""><?php echo e($f['label']); ?></option>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $f['options']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $val => $lbl): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                                <option value="<?php echo e($val); ?>"><?php echo e($lbl); ?></option>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                        </select>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
            </div>
            <div class="flex items-center gap-2">
                <button wire:click="exportCsv" class="px-4 py-2 bg-emerald-50 text-emerald-600 dark:bg-emerald-900/20 border border-emerald-200 dark:border-emerald-800 dark:text-emerald-400 rounded-lg text-sm font-medium hover:bg-emerald-100 dark:bg-emerald-900/50 dark:hover:bg-emerald-900/40 transition-colors flex items-center gap-2">
                    <span class="material-symbols-outlined notranslate" translate="no" style="font-size:18px">download</span> Export CSV
                </button>
            </div>
        </div>

    <?php echo $__env->make('partials.enterprise.bulk-bar', ['bulkActions' => $bulkActions], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($errorMessage): ?>
        <div class="px-3 py-2 bg-red-50 dark:bg-red-900/30 border-b border-red-100 dark:border-red-800 text-sm text-red-700 dark:text-red-200">
            <?php echo e($errorMessage); ?>

        </div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($loading): ?>
        <div class="p-8 flex items-center justify-center text-slate-500 dark:text-slate-400">
            <svg class="w-6 h-6 animate-spin mr-2" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
            Memuat data...
        </div>
    <?php else: ?>
        <div class="bg-white dark:bg-slate-800 overflow-x-auto border-b border-slate-200 dark:border-slate-700">
            <table class="min-w-full text-sm">
                <thead class="bg-slate-50 dark:bg-slate-700/40 border-y border-slate-200 dark:border-slate-700">
                    <tr>
                        <th class="px-3 py-2 w-10">
                            <input type="checkbox" wire:model.live="selectAll" class="rounded border-slate-300 dark:border-slate-600 dark:bg-slate-900 dark:text-slate-100">
                        </th>
                        <th class="px-3 py-2 text-left font-semibold text-slate-600 dark:text-slate-300 cursor-pointer select-none" wire:click="sortBy('created_at')">
                            Tgl
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($sortField === 'created_at'): ?><span class="ml-1"><?php echo e($sortDirection === 'asc' ? '↑' : '↓'); ?></span><?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </th>
                        <th class="px-3 py-2 text-left font-semibold text-slate-600 dark:text-slate-300">Reseller</th>
                        <th class="px-3 py-2 text-left font-semibold text-slate-600 dark:text-slate-300 cursor-pointer select-none" wire:click="sortBy('reference_code')">
                            Kode Referensi
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($sortField === 'reference_code'): ?><span class="ml-1"><?php echo e($sortDirection === 'asc' ? '↑' : '↓'); ?></span><?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </th>
                        <th class="px-3 py-2 text-right font-semibold text-slate-600 dark:text-slate-300 cursor-pointer select-none" wire:click="sortBy('amount')">
                            Jumlah
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($sortField === 'amount'): ?><span class="ml-1"><?php echo e($sortDirection === 'asc' ? '↑' : '↓'); ?></span><?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </th>
                        <th class="px-3 py-2 text-left font-semibold text-slate-600 dark:text-slate-300">Metode</th>
                        <th class="px-3 py-2 text-left font-semibold text-slate-600 dark:text-slate-300">Bukti TF</th>
                        <th class="px-3 py-2 text-left font-semibold text-slate-600 dark:text-slate-300">Status</th>
                        <th class="px-3 py-2 text-left font-semibold text-slate-600 dark:text-slate-300">Diajukan Oleh</th>
                        <th class="px-3 py-2 text-left font-semibold text-slate-600 dark:text-slate-300">Diverifikasi Oleh</th>
                        <th class="px-3 py-2 text-right font-semibold text-slate-600 dark:text-slate-300">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-700/60">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__empty_0 = true; $__currentLoopData = $rows; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $row): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_0 = false; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                        <?php
                            $statusClass = match(strtolower($row->status ?? '')) {
                                'pending' => 'bg-amber-100 text-amber-700 dark:bg-amber-900/40 dark:text-amber-300',
                                'approved', 'success' => 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-300',
                                'rejected', 'failed' => 'bg-red-100 text-red-700 dark:bg-red-900/40 dark:text-red-300',
                                default => 'bg-slate-100 text-slate-700 dark:bg-slate-700 dark:text-slate-200',
                            };
                            $statusLabel = match(strtolower($row->status ?? '')) {
                                'pending' => 'Pending Approval',
                                'approved', 'success' => 'Disetujui',
                                'rejected', 'failed' => 'Ditolak',
                                default => ucfirst($row->status ?? '-'),
                            };
                            $resellerName = $row->reseller->name ?? $row->reseller_name ?? '-';
                            $submitterName = $row->submittedBy?->name ?? $row->submitted_by_name ?? '-';
                            $verifierName = $row->verifiedBy?->name ?? '-';
                        ?>
                        <tr class="hover:bg-slate-50 dark:bg-slate-900/50 dark:hover:bg-slate-700/30">
                            <td class="px-3 py-2">
                                <input type="checkbox" wire:model.live="selected" value="<?php echo e((string) $row->id); ?>" class="rounded border-slate-300 dark:border-slate-600">
                            </td>
                            <td class="px-3 py-2 whitespace-nowrap text-slate-700 dark:text-slate-300"><?php echo e($row->created_at?->format('d/m/Y H:i') ?? '-'); ?></td>
                            <td class="px-3 py-2 font-medium text-slate-900 dark:text-slate-100"><?php echo e($resellerName); ?></td>
                            <td class="px-3 py-2 font-mono text-xs text-slate-700 dark:text-slate-300"><?php echo e($row->reference_code ?? $row->reference_number ?? '-'); ?></td>
                            <td class="px-3 py-2 whitespace-nowrap text-right font-semibold text-slate-900 dark:text-slate-100">Rp <?php echo e(number_format((float) ($row->amount ?? 0), 0, ',', '.')); ?></td>
                            <td class="px-3 py-2 text-slate-700 dark:text-slate-300"><?php echo e(ucfirst(str_replace('_', ' ', $row->method ?? '-'))); ?></td>
                            <td class="px-3 py-2">
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(!empty($row->proof_file)): ?>
                                    <a href="<?php echo e($row->proof_file); ?>" target="_blank" class="text-blue-600 hover:text-blue-700 dark:text-blue-400 underline text-xs">Preview</a>
                                <?php else: ?>
                                    <span class="text-slate-400 dark:text-slate-500 dark:text-slate-400 text-xs">-</span>
                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            </td>
                            <td class="px-3 py-2">
                                <span class="inline-flex px-2 py-0.5 rounded-full text-[11px] font-medium <?php echo e($statusClass); ?>"><?php echo e($statusLabel); ?></span>
                            </td>
                            <td class="px-3 py-2 text-slate-700 dark:text-slate-300 text-xs"><?php echo e($submitterName); ?></td>
                            <td class="px-3 py-2 text-slate-700 dark:text-slate-300 text-xs"><?php echo e($verifierName); ?></td>
                            <td class="px-3 py-2 whitespace-nowrap text-right">
                                <div class="inline-flex gap-1">
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(strtolower($row->status ?? '') === 'pending'): ?>
                                        <button wire:click="approve(<?php echo e($row->id); ?>)" class="px-2 py-1 text-[11px] rounded bg-emerald-600 hover:bg-emerald-700 text-white font-medium">Setujui</button>
                                        <button wire:click="confirmReject(<?php echo e($row->id); ?>)" class="px-2 py-1 text-[11px] rounded bg-red-600 hover:bg-red-700 text-white font-medium">Tolak</button>
                                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                    <button wire:click="previewProof(<?php echo e($row->id); ?>)" class="px-2 py-1 text-[11px] rounded border border-slate-200 hover:bg-slate-50 dark:bg-slate-900/50 text-slate-700 dark:border-slate-700 dark:hover:bg-slate-700 dark:text-slate-200">Bukti</button>
                                    <button wire:click="viewDetail(<?php echo e($row->id); ?>)" class="px-2 py-1 text-[11px] rounded border border-slate-200 hover:bg-slate-50 dark:bg-slate-900/50 text-slate-700 dark:border-slate-700 dark:hover:bg-slate-700 dark:text-slate-200">Detail</button>
                                </div>
                            </td>
                        </tr>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_0): ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                        <tr>
                            <td colspan="11" class="px-3 py-12 text-center text-slate-500 dark:text-slate-400">
                                <svg class="mx-auto mb-2 w-10 h-10 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/></svg>
                                Tidak ada data topup reseller.
                            </td>
                        </tr>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </tbody>
            </table>
        </div>

        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($rows->hasPages()): ?>
            <div class="px-3 py-3 border-t border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 flex items-center justify-between gap-2 flex-wrap">
                <div class="text-xs text-slate-600 dark:text-slate-400">
                    Menampilkan <?php echo e($rows->firstItem()); ?> - <?php echo e($rows->lastItem()); ?> dari <?php echo e($rows->total()); ?> data
                </div>
                <div><?php echo e($rows->links()); ?></div>
                <div class="flex items-center gap-1">
                    <select wire:model.live="perPage" class="text-xs rounded-md border border-slate-200 dark:border-slate-700 py-1 px-2 bg-white dark:bg-slate-900 dark:bg-slate-700 dark:text-slate-200 dark:bg-slate-900 dark:text-slate-100">
                        <option value="10">10 / hal</option>
                        <option value="25">25 / hal</option>
                        <option value="50">50 / hal</option>
                        <option value="100">100 / hal</option>
                    </select>
                </div>
            </div>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

    <?php echo $__env->make('partials.enterprise.confirm-modal', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
</div>
<?php /**PATH D:\dsBilling\resources\views\livewire\keuangan\topup-reseller\index.blade.php ENDPATH**/ ?>