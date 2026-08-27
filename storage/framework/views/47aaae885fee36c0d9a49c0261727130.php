<?php $__env->startSection('title', 'Revenue Sharing'); ?>

<?php $__env->startSection('content'); ?>
    <div class="container-fluid">
        <h2 class="mb-4">Revenue Sharing</h2>

        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(session('success')): ?>
            <div class="alert alert-success"><?php echo e(session('success')); ?></div>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(session('error')): ?>
            <div class="alert alert-danger"><?php echo e(session('error')); ?></div>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($existingBatch && $existingBatch->transactions_changed): ?>
            <div class="alert alert-warning">
                <i class="bi bi-exclamation-triangle-fill"></i>
                Data transaksi periode ini telah berubah. Revenue Sharing perlu diregenerate.
            </div>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

        <div class="row mb-4">
            <div class="col-md-6">
                <form method="GET" class="d-flex gap-2">
                    <div class="form-group flex-grow-1">
                        <label for="period">Periode</label>
                        <input type="month" name="period" id="period" class="form-control" value="<?php echo e($period); ?>">
                    </div>
                    <div class="form-group align-self-end">
                        <button type="submit" class="btn btn-primary">Lihat</button>
                    </div>
                </form>
            </div>
            <div class="col-md-6 text-end align-self-end">
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(!$existingBatch || $existingBatch->transactions_changed || $existingBatch->status === 'draft'): ?>
                    <form method="POST" action="<?php echo e(route('revenue-sharing.generate')); ?>" class="d-inline-block">
                        <?php echo csrf_field(); ?>
                        <input type="hidden" name="period" value="<?php echo e($period); ?>">
                        <button type="submit" class="btn btn-success">
                            <i class="bi bi-magic"></i> Generate Batch
                        </button>
                    </form>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </div>
        </div>

        <!-- Summary Cards -->
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="card card-stat h-100">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="card-icon bg-primary text-white rounded-circle d-flex align-items-center justify-content-center" style="width: 60px; height: 60px;">
                                <i class="bi bi-cash-coin" style="font-size: 24px;"></i>
                            </div>
                            <div class="ms-3">
                                <h5 class="mb-1">Total Pendapatan</h5>
                                <h3 class="text-primary">Rp <?php echo e(number_format($preview['total_revenue'], 0, ',', '.')); ?></h3>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card card-stat h-100">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="card-icon bg-danger text-white rounded-circle d-flex align-items-center justify-content-center" style="width: 60px; height: 60px;">
                                <i class="bi bi-wallet2" style="font-size: 24px;"></i>
                            </div>
                            <div class="ms-3">
                                <h5 class="mb-1">Total Pengeluaran</h5>
                                <h3 class="text-danger">Rp <?php echo e(number_format($preview['total_expense'], 0, ',', '.')); ?></h3>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card card-stat h-100">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="card-icon bg-success text-white rounded-circle d-flex align-items-center justify-content-center" style="width: 60px; height: 60px;">
                                <i class="bi bi-graph-up-arrow" style="font-size: 24px;"></i>
                            </div>
                            <div class="ms-3">
                                <h5 class="mb-1">Total Bersih</h5>
                                <h3 class="text-success">Rp <?php echo e(number_format($preview['total_distributed'], 0, ',', '.')); ?></h3>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card card-stat h-100">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="card-icon bg-info text-white rounded-circle d-flex align-items-center justify-content-center" style="width: 60px; height: 60px;">
                                <i class="bi bi-people" style="font-size: 24px;"></i>
                            </div>
                            <div class="ms-3">
                                <h5 class="mb-1">Jumlah Anggota</h5>
                                <h3 class="text-info"><?php echo e(count($preview['items'])); ?></h3>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Detail Anggota -->
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0">Detail Anggota</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered table-hover">
                        <thead class="table-light">
                            <tr>
                                <th>Nama Anggota</th>
                                <th>Pendapatan</th>
                                <th>Persentase</th>
                                <th>Beban</th>
                                <th>Hak Bersih</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $preview['items']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                                <tr>
                                    <td><?php echo e($item['member']->name); ?></td>
                                    <td>Rp <?php echo e(number_format($item['member_revenue'], 0, ',', '.')); ?></td>
                                    <td><?php echo e(number_format($item['percentage'], 2)); ?>%</td>
                                    <td>Rp <?php echo e(number_format($item['expense_share'], 0, ',', '.')); ?></td>
                                    <td class="fw-bold text-success">Rp <?php echo e(number_format($item['net_share'], 0, ',', '.')); ?></td>
                                </tr>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                        </tbody>
                        <tfoot class="table-secondary">
                            <tr>
                                <th>Total</th>
                                <th>Rp <?php echo e(number_format($preview['total_revenue'], 0, ',', '.')); ?></th>
                                <th>100%</th>
                                <th>Rp <?php echo e(number_format($preview['total_expense'], 0, ',', '.')); ?></th>
                                <th>Rp <?php echo e(number_format($preview['total_distributed'], 0, ',', '.')); ?></th>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>

        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($existingBatch): ?>
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Batch <?php echo e($existingBatch->batch_number); ?></h5>
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-md-3"><strong>Periode:</strong> <?php echo e($existingBatch->period); ?></div>
                        <div class="col-md-3"><strong>Status:</strong> 
                            <span class="badge bg-<?php echo e($existingBatch->status === 'locked' ? 'secondary' : ($existingBatch->status === 'approved' ? 'success' : 'warning')); ?>">
                                <?php echo e(ucfirst($existingBatch->status)); ?>

                            </span>
                        </div>
                        <div class="col-md-3"><strong>Dibuat pada:</strong> <?php echo e($existingBatch->generated_at?->format('d/m/Y H:i')); ?></div>
                        <div class="col-md-3"><strong>Dibuat oleh:</strong> <?php echo e($existingBatch->generatedBy?->name); ?></div>
                    </div>
                    <div class="d-flex gap-2">
                        <a href="<?php echo e(route('revenue-sharing.show', $existingBatch)); ?>" class="btn btn-info">
                            <i class="bi bi-eye"></i> Lihat Detail
                        </a>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($existingBatch->status === 'generated'): ?>
                            <form method="POST" action="<?php echo e(route('revenue-sharing.approve', $existingBatch)); ?>" class="d-inline">
                                <?php echo csrf_field(); ?>
                                <button type="submit" class="btn btn-success">
                                    <i class="bi bi-check-circle"></i> Approve
                                </button>
                            </form>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($existingBatch->status === 'approved'): ?>
                            <form method="POST" action="<?php echo e(route('revenue-sharing.lock', $existingBatch)); ?>" class="d-inline">
                                <?php echo csrf_field(); ?>
                                <button type="submit" class="btn btn-warning">
                                    <i class="bi bi-lock"></i> Lock
                                </button>
                            </form>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </div>
                </div>
            </div>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

        <!-- History Batches -->
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($batches->count() > 0): ?>
            <div class="card mt-4">
                <div class="card-header">
                    <h5 class="mb-0">Riwayat Batch</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover">
                            <thead class="table-light">
                                <tr>
                                    <th>Batch Number</th>
                                    <th>Periode</th>
                                    <th>Status</th>
                                    <th>Dibuat pada</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $batches; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $batch): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                                    <tr>
                                        <td><?php echo e($batch->batch_number); ?></td>
                                        <td><?php echo e($batch->period); ?></td>
                                        <td>
                                            <span class="badge bg-<?php echo e($batch->status === 'locked' ? 'secondary' : ($batch->status === 'approved' ? 'success' : 'warning')); ?>">
                                                <?php echo e(ucfirst($batch->status)); ?>

                                            </span>
                                        </td>
                                        <td><?php echo e($batch->generated_at?->format('d/m/Y H:i')); ?></td>
                                        <td>
                                            <a href="<?php echo e(route('revenue-sharing.show', $batch)); ?>" class="btn btn-sm btn-info">
                                                <i class="bi bi-eye"></i>
                                            </a>
                                        </td>
                                    </tr>
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                    <?php echo e($batches->links()); ?>

                </div>
            </div>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
    </div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\Lenovo\OneDrive\dsBilling\resources\views\revenue-sharing\index.blade.php ENDPATH**/ ?>