<div class="min-h-screen noc-bg noc-text noc-mono" wire:poll.60s>
    <div class="max-w-[1800px] mx-auto px-4 py-4">
        <div class="flex items-center justify-between mb-4">
            <div>
                <h1 class="text-lg font-semibold noc-text tracking-tight">NOC · Topology & Impact Analysis</h1>
                <p class="text-xs noc-muted mt-0.5"><span class="inline-block w-2 h-2 rounded-full noc-pulse bg-green-500 mr-1.5"></span>LIVE · Hitung dampak perangkat terhadap pelanggan</p>
            </div>
            <div class="flex items-center gap-2">
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(Route::has('noc.overview')): ?>
                    <a href="<?php echo e(route('noc.overview')); ?>" class="px-3 py-1.5 text-xs border rounded noc-btn-outline transition">← Overview</a>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </div>
        </div>

        <div class="noc-panel-bg border noc-border rounded mb-4 overflow-hidden">
            <div class="flex border-b noc-border">
                <button wire:click="setTab('olt')" class="px-4 py-2.5 text-xs font-medium border-b-2 transition <?php echo e($activeTab==='olt' ? 'noc-tab-active' : 'noc-tab-inactive'); ?>">
                    OLT Impact
                </button>
                <button wire:click="setTab('pon')" class="px-4 py-2.5 text-xs font-medium border-b-2 transition <?php echo e($activeTab==='pon' ? 'noc-tab-active' : 'noc-tab-inactive'); ?>">
                    PON Port Impact
                </button>
                <button wire:click="setTab('router')" class="px-4 py-2.5 text-xs font-medium border-b-2 transition <?php echo e($activeTab==='router' ? 'noc-tab-active' : 'noc-tab-inactive'); ?>">
                    Router Impact
                </button>
            </div>

            <div class="p-3 grid grid-cols-1 md:grid-cols-12 gap-2 items-end">
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($activeTab === 'olt'): ?>
                    <div class="md:col-span-8">
                        <label class="noc-section-label">Pilih OLT</label>
                        <select wire:model.live="selectedOltId" class="w-full noc-input border noc-border rounded px-2.5 py-1.5 text-sm noc-text focus:outline-none dark:bg-slate-900 dark:text-slate-100">
                            <option value="">— Select OLT —</option>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $olts; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $olt): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                                <option value="<?php echo e($olt->id); ?>"><?php echo e($olt->name); ?> <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($olt->code): ?><span class="noc-muted">(<?php echo e($olt->code); ?>)</span><?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?></option>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                        </select>
                    </div>
                    <div class="md:col-span-4">
                        <div class="noc-section-label">Total OLT</div>
                        <div class="text-sm noc-text-secondary"><?php echo e($olts->count()); ?> device aktif</div>
                    </div>
                <?php elseif($activeTab === 'pon'): ?>
                    <div class="md:col-span-5">
                        <label class="noc-section-label">Filter OLT (opsional)</label>
                        <select wire:model.live="selectedOltId" class="w-full noc-input border noc-border rounded px-2.5 py-1.5 text-sm noc-text focus:outline-none dark:bg-slate-900 dark:text-slate-100">
                            <option value="">All OLTs</option>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $olts; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $olt): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                                <option value="<?php echo e($olt->id); ?>"><?php echo e($olt->name); ?></option>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                        </select>
                    </div>
                    <div class="md:col-span-7">
                        <label class="noc-section-label">Pilih PON Port</label>
                        <select wire:model.live="selectedPonId" class="w-full noc-input border noc-border rounded px-2.5 py-1.5 text-sm noc-text focus:outline-none dark:bg-slate-900 dark:text-slate-100">
                            <option value="">— Select PON Port —</option>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $ponPorts; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $pon): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                                <option value="<?php echo e($pon->id); ?>">
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(isset($pon->olt->name)): ?> <?php echo e($pon->olt->name); ?> — <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                    PON <?php echo e($pon->port_number); ?> (<?php echo e($pon->name); ?>)
                                </option>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                        </select>
                    </div>
                <?php elseif($activeTab === 'router'): ?>
                    <div class="md:col-span-8">
                        <label class="noc-section-label">Pilih Router</label>
                        <select wire:model.live="selectedRouterId" class="w-full noc-input border noc-border rounded px-2.5 py-1.5 text-sm noc-text focus:outline-none dark:bg-slate-900 dark:text-slate-100">
                            <option value="">— Select Router —</option>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $routers; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $r): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                                <option value="<?php echo e($r->id); ?>"><?php echo e($r->name); ?> <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($r->code): ?><span class="noc-muted">(<?php echo e($r->code); ?>)</span><?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?></option>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                        </select>
                    </div>
                    <div class="md:col-span-4">
                        <div class="noc-section-label">Total Router</div>
                        <div class="text-sm noc-text-secondary"><?php echo e($routers->count()); ?> device aktif</div>
                    </div>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-4 gap-4 mb-4">
            <div class="noc-summary-card">
                <div class="noc-summary-label"><?php echo e($impactResult['device_type']); ?></div>
                <div class="text-sm font-semibold noc-text mt-1"><?php echo e($impactResult['device']); ?></div>
            </div>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(in_array($impactResult['device_type'], ['OLT','PON Port'])): ?>
                <div class="noc-summary-card">
                    <div class="noc-summary-label">PON Port Terdampak</div>
                    <div class="noc-summary-value text-blue-500"><?php echo e($impactResult['pon_count']); ?></div>
                </div>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            <div class="noc-summary-card">
                <div class="noc-summary-label">ONU Terdampak</div>
                <div class="noc-summary-value text-yellow-500"><?php echo e($impactResult['onu_count']); ?></div>
            </div>
            <div class="noc-summary-card">
                <div class="noc-summary-label">Pelanggan Terdampak</div>
                <div class="noc-summary-value text-red-500"><?php echo e($impactResult['customer_count']); ?></div>
            </div>
            <div class="noc-summary-card">
                <div class="noc-summary-label">Layanan Aktif</div>
                <div class="noc-summary-value text-yellow-500"><?php echo e($impactResult['service_count']); ?></div>
                <div class="mt-1 flex gap-1.5 text-[10px]">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($impactResult['breakdown']['pppoe']>0): ?><span class="noc-badge-info px-1.5 py-0.5 rounded">PPPoE <?php echo e($impactResult['breakdown']['pppoe']); ?></span><?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($impactResult['breakdown']['hotspot']>0): ?><span class="noc-badge-warning px-1.5 py-0.5 rounded">HS <?php echo e($impactResult['breakdown']['hotspot']); ?></span><?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($impactResult['breakdown']['other']>0): ?><span class="noc-badge-unknown px-1.5 py-0.5 rounded">Other <?php echo e($impactResult['breakdown']['other']); ?></span><?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>
            </div>
        </div>

        <div class="noc-panel-bg border noc-border rounded overflow-hidden">
            <div class="px-3 py-2 border-b noc-border flex items-center justify-between">
                <div class="text-[11px] uppercase tracking-wider noc-muted font-semibold">Affected Customers</div>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($affectedCustomers->total() > 0): ?>
                    <div class="text-[11px] noc-muted"><?php echo e($affectedCustomers->total()); ?> record · Page <?php echo e($affectedCustomers->currentPage()); ?>/<?php echo e($affectedCustomers->lastPage()); ?></div>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </div>
            <div class="overflow-x-auto noc-scroll">
                <table class="w-full text-left text-sm text-slate-600 dark:text-slate-300">
                    <thead class="bg-slate-50 dark:bg-slate-800/50 border-b border-slate-200 dark:bg-slate-800/50 dark:border-slate-700">
                        <tr class="text-slate-500 dark:text-slate-400">
                            <th class="p-3 font-semibold w-16">#</th>
                            <th class="p-3 font-semibold">Customer</th>
                            <th class="p-3 font-semibold">Code</th>
                            <th class="p-3 font-semibold">Service</th>
                            <th class="p-3 font-semibold">ONU SN</th>
                            <th class="p-3 font-semibold">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-700/60">
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__empty_0 = true; $__currentLoopData = $affectedCustomers; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $idx => $cs): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_0 = false; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                            <tr class="hover:bg-slate-50 dark:bg-slate-900/50 dark:hover:bg-slate-800/50 transition-colors border-b noc-divider noc-row-hover transition">
                                <td class="p-3  noc-muted"><?php echo e($affectedCustomers->firstItem() + $idx); ?></td>
                                <td class="p-3">
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(isset($cs->customer->name)): ?>
                                        <div class="noc-text"><?php echo e($cs->customer->name); ?></div>
                                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                </td>
                                <td class="p-3  noc-muted font-mono"><?php echo e($cs->customer->code); ?></td>
                                <td class="p-3">
                                    <span class="uppercase <?php echo e($cs->service_type==='pppoe'?'noc-badge-info':'noc-badge-warning'); ?> px-1.5 py-0.5 rounded text-[10px]"><?php echo e($cs->service_type); ?></span>
                                </td>
                                <td class="p-3">
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($cs->onu): ?>
                                        <div class="font-mono text-[11px] text-blue-500"><?php echo e($cs->onu->serial_number); ?></div>
                                        <div class="text-[10px] mt-0.5 <?php echo e($cs->onu->status==='online'?'text-emerald-500':'text-red-500'); ?>"><?php echo e(strtoupper($cs->onu->status)); ?></div>
                                    <?php else: ?>
                                        <span class="noc-muted opacity-70">—</span>
                                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                </td>
                                <td class="p-3">
                                    <span class="inline-block px-2 py-0.5 rounded text-[10px] font-semibold uppercase tracking-wider <?php echo e($cs->status==='active'?'noc-badge-ok':'noc-badge-unknown'); ?>"><?php echo e($cs->status); ?></span>
                                </td>
                            </tr>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_0): ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                            <tr class="hover:bg-slate-50 dark:bg-slate-900/50 dark:hover:bg-slate-800/50 transition-colors">
                                <td colspan="6" class="p-3  text-center noc-muted text-xs">
                                    <?php echo e(($activeTab==='olt' && !$selectedOltId) || ($activeTab==='pon' && !$selectedPonId) || ($activeTab==='router' && !$selectedRouterId)
                                        ? 'Pilih perangkat di atas untuk melihat daftar pelanggan terdampak.'
                                        : 'Tidak ada pelanggan aktif yang terdampak pada perangkat ini.'); ?>

                                </td>
                            </tr>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </tbody>
                </table>
            </div>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($affectedCustomers->hasPages()): ?>
                <div class="px-3 py-2 border-t noc-border text-xs noc-muted">
                    <?php echo e($affectedCustomers->onEachSide(1)->links()); ?>

                </div>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </div>
    </div>
</div>






<?php /**PATH D:\dsBilling\resources\views\livewire\noc\topology\index.blade.php ENDPATH**/ ?>