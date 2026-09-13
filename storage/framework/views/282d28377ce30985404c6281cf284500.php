<div <?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::$currentLoop['key'] = 'keuangan-pengeluaran-'.e(now()->timestamp).''; ?>wire:key="keuangan-pengeluaran-<?php echo e(now()->timestamp); ?>">
        <?php $__env->startSection('page_title'); ?>
        <div class="flex items-center gap-2">
            <span class="material-symbols-outlined notranslate text-rose-500" translate="no" style="font-size:24px">account_balance_wallet</span>
            <span class="text-lg">Pengeluaran (Opex)</span>
        </div>
    <?php $__env->stopSection(); ?>

    <div class="space-y-4">
        
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            
            <div class="relative overflow-x-auto rounded-xl border border-rose-200 dark:border-rose-800/60 shadow-sm bg-gradient-to-br from-rose-50 to-white dark:from-rose-950/50 dark:to-slate-800">
                <div class="absolute top-0 left-0 right-0 h-1 bg-gradient-to-r from-rose-500 to-red-400 rounded-t-xl"></div>
                <div class="p-4 pt-5">
                    <h3 class="text-xs font-bold text-rose-600 dark:text-rose-500 uppercase tracking-widest mb-2">Total Bulan Ini</h3>
                    <div class="flex items-baseline gap-2">
                        <span class="text-xl md:text-2xl font-black text-slate-800 dark:text-slate-100 tracking-tight">Rp <?php echo e(number_format($summary['monthly_total'] ?? 0, 0, ',', '.')); ?></span>
                    </div>
                </div>
            </div>

            
            <div class="relative overflow-x-auto rounded-xl border border-amber-200 dark:border-amber-800/60 shadow-sm bg-gradient-to-br from-amber-50 to-white dark:from-amber-950/50 dark:to-slate-800">
                <div class="absolute top-0 left-0 right-0 h-1 bg-gradient-to-r from-amber-500 to-orange-400 rounded-t-xl"></div>
                <div class="p-4 pt-5">
                    <h3 class="text-xs font-bold text-amber-600 dark:text-amber-500 uppercase tracking-widest mb-2">Menunggu Approval</h3>
                    <div class="flex items-baseline gap-2">
                        <span class="text-xl md:text-2xl font-black text-slate-800 dark:text-slate-100 tracking-tight"><?php echo e(number_format($summary['needs_approval'] ?? 0, 0, ',', '.')); ?></span>
                        <span class="text-xs font-medium text-slate-500 dark:text-slate-400">item</span>
                    </div>
                </div>
            </div>

            
            <div class="relative overflow-x-auto rounded-xl border border-emerald-200 dark:border-emerald-800/60 shadow-sm bg-gradient-to-br from-emerald-50 to-white dark:from-emerald-950/50 dark:to-slate-800">
                <div class="absolute top-0 left-0 right-0 h-1 bg-gradient-to-r from-emerald-500 to-green-400 rounded-t-xl"></div>
                <div class="p-4 pt-5">
                    <h3 class="text-xs font-bold text-emerald-600 dark:text-emerald-500 uppercase tracking-widest mb-2">Disetujui</h3>
                    <div class="flex items-baseline gap-2">
                        <span class="text-xl md:text-2xl font-black text-slate-800 dark:text-slate-100 tracking-tight"><?php echo e(number_format($summary['approved'] ?? 0, 0, ',', '.')); ?></span>
                        <span class="text-xs font-medium text-slate-500 dark:text-slate-400">item</span>
                    </div>
                </div>
            </div>

            
            <div class="relative overflow-x-auto rounded-xl border border-blue-200 dark:border-blue-800/60 shadow-sm bg-gradient-to-br from-blue-50 to-white dark:from-blue-950/50 dark:to-slate-800">
                <div class="absolute top-0 left-0 right-0 h-1 bg-gradient-to-r from-blue-500 to-cyan-400 rounded-t-xl"></div>
                <div class="p-4 pt-5">
                    <h3 class="text-xs font-bold text-blue-600 dark:text-blue-500 uppercase tracking-widest mb-2">Anggaran Sisa</h3>
                    <div class="flex items-baseline gap-2">
                        <span class="text-xl md:text-2xl font-black text-slate-800 dark:text-slate-100 tracking-tight">Rp <?php echo e(number_format($summary['budget_remaining'] ?? 0, 0, ',', '.')); ?></span>
                    </div>
                </div>
            </div>
        </div>

        
                <div class="flex flex-col sm:flex-row gap-3 items-center justify-between bg-white dark:bg-slate-800 p-4 rounded-xl shadow-sm border border-slate-200 dark:border-slate-700">
            <div class="flex items-center gap-2 w-full sm:w-auto">
                <button wire:click="exportCsv" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg text-sm font-semibold shadow-sm flex items-center gap-2 transition-colors">
                    <span class="material-symbols-outlined notranslate text-[18px]" translate="no">download</span>
                    Export CSV
                </button>
            </div>
            <div class="flex-1 w-full relative">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <span class="material-symbols-outlined notranslate text-slate-400" translate="no" style="font-size: 18px">search</span>
                </div>
                <input type="text" wire:model.live.debounce.300ms="search" placeholder="Cari kode / deskripsi..." class="bg-slate-50 dark:bg-slate-900/50 border border-slate-200 dark:border-slate-700 text-slate-900 dark:text-slate-100 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full pl-10 px-3 py-2 dark:bg-slate-900 dark:text-slate-100">
            </div>
            <div class="flex flex-wrap gap-2">
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
              <div>
                <button wire:click="exportCsv" class="px-4 py-2 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-700 dark:text-slate-300 rounded-lg text-sm font-medium hover:bg-slate-50 dark:bg-slate-900/50 dark:hover:bg-slate-700 transition-colors flex items-center gap-2">
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
                        <th class="px-3 py-2 text-left font-semibold text-slate-600 dark:text-slate-300 cursor-pointer select-none" wire:click="sortBy('expense_date')">
                            Tgl
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($sortField === 'expense_date'): ?><span class="ml-1"><?php echo e($sortDirection === 'asc' ? '↑' : '↓'); ?></span><?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </th>
                        <th class="px-3 py-2 text-left font-semibold text-slate-600 dark:text-slate-300 cursor-pointer select-none" wire:click="sortBy('code')">
                            Kode
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($sortField === 'code'): ?><span class="ml-1"><?php echo e($sortDirection === 'asc' ? '↑' : '↓'); ?></span><?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </th>
                        <th class="px-3 py-2 text-left font-semibold text-slate-600 dark:text-slate-300">Deskripsi</th>
                        <th class="px-3 py-2 text-left font-semibold text-slate-600 dark:text-slate-300">Kategori</th>
                        <th class="px-3 py-2 text-right font-semibold text-slate-600 dark:text-slate-300 cursor-pointer select-none" wire:click="sortBy('amount')">
                            Jumlah
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($sortField === 'amount'): ?><span class="ml-1"><?php echo e($sortDirection === 'asc' ? '↑' : '↓'); ?></span><?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </th>
                        <th class="px-3 py-2 text-left font-semibold text-slate-600 dark:text-slate-300">Status</th>
                        <th class="px-3 py-2 text-left font-semibold text-slate-600 dark:text-slate-300">Attachment</th>
                        <th class="px-3 py-2 text-left font-semibold text-slate-600 dark:text-slate-300">Pemohon</th>
                        <th class="px-3 py-2 text-left font-semibold text-slate-600 dark:text-slate-300">Penyetuju</th>
                        <th class="px-3 py-2 text-right font-semibold text-slate-600 dark:text-slate-300">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-700/60">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__empty_0 = true; $__currentLoopData = $rows; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $row): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_0 = false; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                        <?php
                            $statusLabel = match(strtolower($row->status ?? '')) {
                                'draft' => 'Draft',
                                'pending_approval' => 'Butuh Approval',
                                'approved' => 'Disetujui',
                                'rejected' => 'Ditolak',
                                default => ucfirst($row->status ?? '-'),
                            };
                            $statusClass = match(strtolower($row->status ?? '')) {
                                'draft' => 'bg-slate-100 text-slate-700 dark:bg-slate-700 dark:text-slate-200',
                                'pending_approval' => 'bg-amber-100 text-amber-700 dark:bg-amber-900/40 dark:text-amber-300',
                                'approved' => 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-300',
                                'rejected' => 'bg-red-100 text-red-700 dark:bg-red-900/40 dark:text-red-300',
                                default => 'bg-slate-100 text-slate-700 dark:bg-slate-700 dark:text-slate-200',
                            };
                            $catLabels = [
                                'operasional' => 'Operasional',
                                'pegawai' => 'Pegawai',
                                'isp_tools' => 'ISP/Tools',
                                'marketing' => 'Marketing',
                                'lain' => 'Lainnya',
                            ];
                        ?>
                        <tr class="hover:bg-slate-50 dark:bg-slate-900/50 dark:hover:bg-slate-700/30">
                            <td class="px-3 py-2">
                                <input type="checkbox" wire:model.live="selected" value="<?php echo e((string) $row->id); ?>" class="rounded border-slate-300 dark:border-slate-600">
                            </td>
                            <td class="px-3 py-2 whitespace-nowrap text-slate-700 dark:text-slate-300"><?php echo e($row->expense_date?->format('d/m/Y') ?? $row->created_at?->format('d/m/Y') ?? '-'); ?></td>
                            <td class="px-3 py-2 font-mono text-xs text-slate-800 dark:text-slate-200"><?php echo e($row->code ?? '-'); ?></td>
                            <td class="px-3 py-2 max-w-xs truncate text-slate-700 dark:text-slate-300" title="<?php echo e($row->description ?? ''); ?>"><?php echo e($row->description ?? '-'); ?></td>
                            <td class="px-3 py-2 whitespace-nowrap text-slate-700 dark:text-slate-300"><?php echo e($catLabels[$row->category] ?? ucfirst($row->category ?? '-')); ?></td>
                            <td class="px-3 py-2 whitespace-nowrap text-right font-semibold text-red-700 dark:text-red-400">Rp <?php echo e(number_format((float) ($row->amount ?? 0), 0, ',', '.')); ?></td>
                            <td class="px-3 py-2">
                                <span class="inline-flex px-2 py-0.5 rounded-full text-[11px] font-medium <?php echo e($statusClass); ?>"><?php echo e($statusLabel); ?></span>
                            </td>
                            <td class="px-3 py-2">
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(!empty($row->attachment_file)): ?>
                                    <a href="<?php echo e($row->attachment_file); ?>" target="_blank" class="text-blue-600 hover:text-blue-700 dark:text-blue-400 underline text-xs">Lihat</a>
                                <?php else: ?>
                                    <span class="text-slate-400 dark:text-slate-500 dark:text-slate-400 text-xs">-</span>
                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            </td>
                            <td class="px-3 py-2 whitespace-nowrap text-slate-700 dark:text-slate-300 text-xs"><?php echo e($row->requestedBy?->name ?? '-'); ?></td>
                            <td class="px-3 py-2 whitespace-nowrap text-slate-700 dark:text-slate-300 text-xs"><?php echo e($row->approvedBy?->name ?? '-'); ?></td>
                            <td class="px-3 py-2 whitespace-nowrap text-right">
                                <div class="inline-flex gap-1 flex-wrap justify-end">
                                    <button wire:click="edit(<?php echo e($row->id); ?>)" class="px-2 py-1 text-[11px] rounded border border-slate-200 hover:bg-slate-50 dark:bg-slate-900/50 text-slate-700 dark:border-slate-700 dark:hover:bg-slate-700 dark:text-slate-200">Edit</button>
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(strtolower($row->status ?? '') === 'pending_approval'): ?>
                                        <button wire:click="approve(<?php echo e($row->id); ?>)" class="px-2 py-1 text-[11px] rounded bg-emerald-600 hover:bg-emerald-700 text-white font-medium">Setujui</button>
                                        <button wire:click="confirmReject(<?php echo e($row->id); ?>)" class="px-2 py-1 text-[11px] rounded bg-red-600 hover:bg-red-700 text-white font-medium">Tolak</button>
                                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                    <button wire:click="viewAttachment(<?php echo e($row->id); ?>)" class="px-2 py-1 text-[11px] rounded border border-slate-200 hover:bg-slate-50 dark:bg-slate-900/50 text-slate-700 dark:border-slate-700 dark:hover:bg-slate-700 dark:text-slate-200">File</button>
                                    <button wire:click="confirmDelete(<?php echo e($row->id); ?>)" class="px-2 py-1 text-[11px] rounded border border-red-200 hover:bg-red-50 dark:bg-red-900/30 text-red-700 dark:border-red-900 dark:hover:bg-red-900/30 dark:text-red-300">Hapus</button>
                                    <button wire:click="printReceipt(<?php echo e($row->id); ?>)" class="px-2 py-1 text-[11px] rounded border border-slate-200 hover:bg-slate-50 dark:bg-slate-900/50 text-slate-700 dark:border-slate-700 dark:hover:bg-slate-700 dark:text-slate-200">Kwitansi</button>
                                </div>
                            </td>
                        </tr>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_0): ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                        <tr>
                            <td colspan="11" class="px-3 py-12 text-center text-slate-500 dark:text-slate-400">
                                <svg class="mx-auto mb-2 w-10 h-10 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                                Tidak ada data pengeluaran.
                            </td>
                        </tr>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </tbody>
            </table>
        </div>

        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(is_object($rows) && method_exists($rows, 'hasPages') && $rows->hasPages()): ?>
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
<?php /**PATH D:\dsBilling\resources\views\livewire\keuangan\pengeluaran\index.blade.php ENDPATH**/ ?>