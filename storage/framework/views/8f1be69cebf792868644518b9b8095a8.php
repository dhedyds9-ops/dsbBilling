<div class="row g-4">
    <div class="col-12">
        <div class="card card-stat bg-white dark:bg-slate-800">
            <div class="card-body d-flex align-items-center justify-content-between">
                <div>
                    <h2 class="h3 mb-1 fw-bold">Quality Control</h2>
                    <p class="text-muted mb-0">Total: <span class="text-primary fw-bold"><?php echo e($qualityControls->total()); ?></span> QC</p>
                </div>
                <button class="btn btn-primary">
                    <i class="bi bi-plus me-1"></i> Tambah QC
                </button>
            </div>
        </div>
    </div>

    <div class="col-12">
        <div class="card card-stat bg-white dark:bg-slate-800">
            <div class="card-body p-0">
                <div class="w-full overflow-x-auto">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                        <tr>
                            <th>Instalasi</th>
                            <th>Status QC</th>
                            <th>Catatan</th>
                            <th>Tanggal</th>
                            <th>Aksi</th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $qualityControls; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $qc): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                            <tr>
                                <td>
                                    <div class="fw-bold"><?php echo e($qc->installation->customer->name ?? '-'); ?></div>
                                </td>
                                <td>
                                    <span class="badge <?php echo e($qc->status == 'passed' ? 'bg-success' : 'bg-danger'); ?> badge-status">
                                        <?php echo e(ucfirst($qc->status)); ?>

                                    </span>
                                </td>
                                <td><div class="text-muted small"><?php echo e(Str::limit($qc->notes, 50)); ?></div></td>
                                <td><div class="text-muted small"><?php echo e($qc->created_at->format('d/m/Y')); ?></div></td>
                                <td>
                                    <div class="d-flex gap-1">
                                        <button class="btn btn-sm btn-outline-primary">
                                            <i class="bi bi-eye"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                        </tbody>
                    </table>
                </div>
                <div class="d-flex justify-content-between align-items-center p-3 border-top">
                    <div class="text-muted small">
                        Menampilkan <?php echo e($qualityControls->firstItem()); ?> - <?php echo e($qualityControls->lastItem()); ?> dari <?php echo e($qualityControls->total()); ?>

                    </div>
                    <div>
                        <?php echo e($qualityControls->links()); ?>

                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php /**PATH D:\dsBilling\resources\views\livewire\onboarding\quality-control-index.blade.php ENDPATH**/ ?>