<div class="row g-4">
    <div class="col-12">
        <div class="card card-stat bg-white dark:bg-slate-800">
            <div class="card-body d-flex align-items-center justify-content-between">
                <div>
                    <h2 class="h3 mb-1 fw-bold">Instalasi</h2>
                    <p class="text-muted mb-0">Total: <span class="text-primary fw-bold"><?php echo e($installations->total()); ?></span> Instalasi</p>
                </div>
                <button class="btn btn-primary">
                    <i class="bi bi-plus me-1"></i> Jadwalkan Instalasi
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
                            <th>Pelanggan</th>
                            <th>Teknisi</th>
                            <th>Jadwal</th>
                            <th>ONU</th>
                            <th>Status</th>
                            <th>Tanggal</th>
                            <th>Aksi</th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $installations; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $installation): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                            <tr>
                                <td>
                                    <div class="fw-bold"><?php echo e($installation->customer->name ?? '-'); ?></div>
                                </td>
                                <td><div class="text-muted small"><?php echo e($installation->technician?->name ?? '-'); ?></div></td>
                                <td><div class="text-muted small"><?php echo e($installation->schedule_date->format('d/m/Y H:i')); ?></div></td>
                                <td><div class="text-muted small"><?php echo e($installation->onu?->serial_number ?? '-'); ?></div></td>
                                <td>
                                    <span class="badge <?php echo e($installation->status == 'completed' ? 'bg-success' : ($installation->status == 'in_progress' ? 'bg-primary' : 'bg-warning')); ?> badge-status">
                                        <?php echo e(ucfirst(str_replace('_', ' ', $installation->status))); ?>

                                    </span>
                                </td>
                                <td><div class="text-muted small"><?php echo e($installation->created_at->format('d/m/Y')); ?></div></td>
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
                        Menampilkan <?php echo e($installations->firstItem()); ?> - <?php echo e($installations->lastItem()); ?> dari <?php echo e($installations->total()); ?>

                    </div>
                    <div>
                        <?php echo e($installations->links()); ?>

                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php /**PATH D:\dsBilling\resources\views\livewire\onboarding\installation-index.blade.php ENDPATH**/ ?>