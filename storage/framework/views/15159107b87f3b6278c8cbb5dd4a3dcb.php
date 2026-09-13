<div class="row g-4">
    <div class="col-12">
        <div class="card card-stat bg-white dark:bg-slate-800">
            <div class="card-body d-flex align-items-center justify-content-between">
                <div>
                    <h2 class="h3 mb-1 fw-bold">Coverage Check</h2>
                    <p class="text-muted mb-0">Total: <span class="text-primary fw-bold"><?php echo e($coverageChecks->total()); ?></span> Coverage Check</p>
                </div>
                <button class="btn btn-primary">
                    <i class="bi bi-plus me-1"></i> Tambah Coverage Check
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
                            <th>Prospek</th>
                            <th>Alamat</th>
                            <th>GPS (Lat/Lng)</th>
                            <th>ODP</th>
                            <th>Status</th>
                            <th>Tanggal</th>
                            <th>Aksi</th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $coverageChecks; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $check): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                            <tr>
                                <td>
                                    <div class="fw-bold"><?php echo e($check->prospect->name ?? '-'); ?></div>
                                </td>
                                <td><div class="text-muted small"><?php echo e(Str::limit($check->address, 30)); ?></div></td>
                                <td><div class="text-muted small"><?php echo e($check->latitude); ?>, <?php echo e($check->longitude); ?></div></td>
                                <td><div class="text-muted small"><?php echo e($check->odp?->name ?? '-'); ?></div></td>
                                <td>
                                    <span class="badge <?php echo e($check->status == 'covered' ? 'bg-success' : ($check->status == 'not_covered' ? 'bg-danger' : 'bg-warning')); ?> badge-status">
                                        <?php echo e(ucfirst(str_replace('_', ' ', $check->status))); ?>

                                    </span>
                                </td>
                                <td><div class="text-muted small"><?php echo e($check->created_at->format('d/m/Y')); ?></div></td>
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
                        Menampilkan <?php echo e($coverageChecks->firstItem()); ?> - <?php echo e($coverageChecks->lastItem()); ?> dari <?php echo e($coverageChecks->total()); ?>

                    </div>
                    <div>
                        <?php echo e($coverageChecks->links()); ?>

                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php /**PATH D:\dsBilling\resources\views\livewire\onboarding\coverage-check-index.blade.php ENDPATH**/ ?>