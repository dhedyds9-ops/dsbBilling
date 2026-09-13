<div class="min-h-screen noc-bg noc-text noc-mono" wire:poll.15s>
    <div class="max-w-[1400px] mx-auto px-4 py-4">
        <div class="flex items-center justify-between mb-4">
            <div>
                <h1 class="text-lg font-semibold noc-text tracking-tight">NOC · Pipeline #<?php echo e($pipeline->id); ?></h1>
                <p class="text-xs noc-muted mt-0.5"><span class="inline-block w-2 h-2 rounded-full noc-pulse bg-emerald-500 mr-1.5"></span>LIVE · UUID <span class="font-mono"><?php echo e($pipeline->uuid); ?></span></p>
            </div>
            <div class="flex items-center gap-2">
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(Route::has('noc.provisioning.index')): ?>
                    <a href="<?php echo e(route('noc.provisioning.index')); ?>" class="px-3 py-1.5 text-xs border rounded noc-btn-outline transition">← Pipelines</a>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(Route::has('noc.overview')): ?>
                    <a href="<?php echo e(route('noc.overview')); ?>" class="px-3 py-1.5 text-xs border rounded noc-btn-outline transition">Overview</a>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </div>
        </div>

        <?php
            $statusClass = match($pipeline->status) {
                'pending'   => 'noc-badge-info',
                'running'   => 'noc-badge-warning',
                'completed' => 'noc-badge-ok',
                'failed'    => 'noc-badge-critical',
                default     => 'bg-gray-700 noc-text',
            };
            $customer = $pipeline->serviceInstance->customerService->customer;
            $onu      = $pipeline->serviceInstance->customerService->onu;
        ?>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
            <div class="lg:col-span-2 space-y-4">
                <div class="noc-panel-bg border noc-border rounded p-4">
                    <div class="flex items-center justify-between mb-4">
                        <div class="flex items-center gap-3">
                            <span class="inline-block px-2.5 py-1 rounded text-[11px] font-semibold uppercase tracking-wider <?php echo e($statusClass); ?>"><?php echo e($pipeline->status); ?></span>
                            <span class="text-xs noc-muted">Pipeline</span>
                        </div>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($pipeline->status === 'failed'): ?>
                            <button wire:click="retry" onclick="return confirm('Retry failed steps via PipelineOrchestrator ?? ')"
                                    class="px-3 py-1.5 text-xs font-semibold bg-yellow-900 hover:bg-yellow-800 border border-yellow-700 rounded text-yellow-200 transition">↻ Retry Pipeline</button>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </div>

                    <div class="grid grid-cols-2 gap-3 text-xs mb-4 pb-4 border-b noc-border">
                        <div>
                            <div class="noc-section-label">Created</div>
                            <div class="noc-text"><?php echo e($pipeline->created_at->format('M d- Y H:i:s')); ?></div>
                            <div class="text-[10px] noc-muted mt-0.5"><?php echo e($pipeline->created_at->diffForHumans()); ?></div>
                        </div>
                        <div>
                            <div class="noc-section-label">Updated</div>
                            <div class="noc-text"><?php echo e($pipeline->updated_at->format('M d- Y H:i:s')); ?></div>
                            <div class="text-[10px] noc-muted mt-0.5"><?php echo e($pipeline->updated_at->diffForHumans()); ?></div>
                        </div>
                        <div>
                            <div class="noc-section-label">Triggered By</div>
                            <div class="noc-text"><?php echo e($pipeline->createdBy->name ?: '—'); ?></div>
                        </div>
                        <div>
                            <div class="noc-section-label">Error</div>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($pipeline->error_message): ?>
                                <div class="text-red-500 text-[11px]"><?php echo e($pipeline->error_message); ?></div>
                            <?php else: ?>
                                <div class="noc-muted">—</div>
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </div>
                    </div>

                    <div>
                        <div class="noc-section-label mb-3">Step Timeline</div>
                        <div class="space-y-1">
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $pipeline->steps->sortBy('order'); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $step): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                                <?php
                                    $stepStatusClass = match($step->status) {
                                        'pending'   => 'border-gray-600 noc-muted',
                                        'running'   => 'border-yellow-500 text-yellow-500',
                                        'completed' => 'border-emerald-600 text-emerald-500',
                                        'failed'    => 'border-red-600 text-red-500',
                                        default     => 'border-gray-600 noc-muted',
                                    };
                                    $stepBadgeClass = match($step->status) {
                                        'pending'   => 'bg-gray-700 noc-text-secondary',
                                        'running'   => 'noc-badge-warning',
                                        'completed' => 'noc-badge-ok',
                                        'failed'    => 'noc-badge-critical',
                                        default     => 'bg-gray-700 noc-text',
                                    };
                                ?>
                                <div class="flex items-start gap-3 p-2.5 rounded noc-bg border noc-border">
                                    <div class="flex flex-col items-center">
                                        <div class="w-5 h-5 rounded-full border-2 flex items-center justify-center text-[10px] font-bold <?php echo e($stepStatusClass); ?>">
                                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($step->status === 'completed'): ?> ✓
                                            <?php elseif($step->status === 'failed'): ?> ✗
                                            <?php elseif($step->status === 'running'): ?> <span class="w-2 h-2 noc-pulse rounded-full bg-current"></span>
                                            <?php else: ?> <?php echo e($step->order); ?>

                                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                        </div>
                                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(!$loop->last): ?>
                                            <div class="w-px flex-1 noc-progress my-1 min-h-[20px]"></div>
                                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <div class="flex items-center justify-between mb-0.5">
                                            <div class="font-semibold noc-text text-xs"><?php echo e($step->step_name); ?></div>
                                            <span class="inline-block px-2 py-0.5 rounded text-[10px] font-semibold uppercase tracking-wider <?php echo e($stepBadgeClass); ?>"><?php echo e($step->status); ?></span>
                                        </div>
                                        <div class="text-[10px] noc-muted mb-1">
                                            <span class="uppercase tracking-wider"><?php echo e($step->step_type); ?></span>
                                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($step->started_at || $step->completed_at || $step->failed_at): ?>
                                                <span class="mx-1.5">·</span>
                                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($step->started_at && !$step->completed_at && !$step->failed_at): ?>
                                                    started <?php echo e($step->started_at->diffForHumans()); ?>

                                                <?php elseif($step->completed_at): ?>
                                                    <?php echo e($step->completed_at->diffForHumans()); ?>

                                                <?php elseif($step->failed_at): ?>
                                                    failed <?php echo e($step->failed_at->diffForHumans()); ?>

                                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                        </div>
                                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($step->error_message): ?>
                                            <div class="text-[11px] text-red-500 bg-red-950/30 border border-red-950 rounded px-2 py-1 mt-1"><?php echo e($step->error_message); ?></div>
                                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                    </div>
                                </div>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>

            <div class="space-y-4">
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($customer): ?>
                    <div class="noc-panel-bg border noc-border rounded p-4">
                        <div class="noc-section-label mb-2">Customer</div>
                        <div class="text-sm noc-text font-medium"><?php echo e($customer->name); ?></div>
                        <div class="text-[11px] noc-muted mt-0.5 font-mono"><?php echo e($customer->code); ?></div>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($customerService = $pipeline->serviceInstance->customerService): ?>
                            <div class="mt-3 pt-3 border-t noc-border space-y-1.5 text-xs">
                                <div class="flex justify-between">
                                    <span class="noc-muted">Service Type</span>
                                    <span class="noc-text-secondary uppercase"><?php echo e($customerService->service_type); ?></span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="noc-muted">Status</span>
                                    <span class="noc-text-secondary"><?php echo e($customerService->status); ?></span>
                                </div>
                            </div>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </div>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($onu): ?>
                    <div class="noc-panel-bg border noc-border rounded p-4">
                        <div class="noc-section-label mb-2">ONU Device</div>
                        <div class="text-xs space-y-1.5">
                            <div class="flex justify-between"><span class="noc-muted">SN</span><span class="noc-text-secondary font-mono text-[11px]"><?php echo e($onu->serial_number); ?></span></div>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($onu->name): ?>
                                <div class="flex justify-between"><span class="noc-muted">Name</span><span class="noc-text-secondary"><?php echo e($onu->name); ?></span></div>
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($onu->olt->name): ?>
                                <div class="flex justify-between"><span class="noc-muted">OLT</span><span class="noc-text-secondary"><?php echo e($onu->olt->name); ?></span></div>
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            <div class="flex justify-between"><span class="noc-muted">Status</span><span class="noc-text-secondary"><?php echo e($onu->status); ?></span></div>
                        </div>
                    </div>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($pipeline->metadata && !empty($pipeline->metadata)): ?>
                    <div class="noc-panel-bg border noc-border rounded p-4">
                        <div class="noc-section-label mb-2">Metadata (safe only)</div>
                        <pre class="text-[11px] noc-muted noc-bg border noc-border rounded px-3 py-2 overflow-x-auto noc-scroll max-h-40"><?php echo e(json_encode($pipeline->metadata- JSON_PRETTY_PRINT)); ?></pre>
                    </div>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </div>
        </div>
    </div>
</div>






<?php /**PATH D:\dsBilling\resources\views\livewire\noc\provisioning\show.blade.php ENDPATH**/ ?>