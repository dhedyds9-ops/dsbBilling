<?php $__env->startSection('title', 'Detail Revenue Sharing'); ?>

<?php $__env->startSection('content'); ?>
    <div class="container-fluid">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>Detail <?php echo e($batch->batch_number); ?></h2>
            <a href="<?php echo e(route('revenue-sharing.index', ['period' => $batch->period])); ?>" class="btn btn-secondary">
                <i class="bi bi-arrow-left"></i> Kembali
            </a>
        </div>

        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0">Informasi Batch</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-3 mb-3">
                        <strong>Batch Number:</strong>
                        <p class="mb-0"><?php echo e($batch->batch_number); ?></p>
                    </div>
                    <div class="col-md-3 mb-3">
                        <strong>Periode:</strong>
                        <p class="mb-0"><?php echo e($batch->period); ?></p>
                    </div>
                    <div class="col-md-3 mb-3">
                        <strong>Status:</strong>
                        <p class="mb-0">
                            <span class="badge bg-<?php echo e($batch->status === 'locked' ? 'secondary' : ($batch->status === 'approved' ? 'success' : 'warning')); ?>">
                                <?php echo e(ucfirst($batch->status)); ?>

                            </span>
                        </p>
                    </div>
                    <div class="col-md-3 mb-3">
                        <strong>Total Pendapatan:</strong>
                        <p class="mb-0">Rp <?php echo e(number_format($batch->total_revenue, 0, ',', '.')); ?></p>
                    </div>
                    <div class="col-md-3 mb-3">
                        <strong>Total Pengeluaran:</strong>
                        <p class="mb-0">Rp <?php echo e(number_format($batch->total_expense, 0, ',', '.')); ?></p>
                    </div>
                    <div class="col-md-3 mb-3">
                        <strong>Total Didistribusikan:</strong>
                        <p class="mb-0">Rp <?php echo e(number_format($batch->total_distributed, 0, ',', '.')); ?></p>
                    </div>
                    <div class="col-md-3 mb-3">
                        <strong>Dibuat oleh:</strong>
                        <p class="mb-0"><?php echo e($batch->generatedBy?->name); ?></p>
                    </div>
                    <div class="col-md-3 mb-3">
                        <strong>Dibuat pada:</strong>
                        <p class="mb-0"><?php echo e($batch->generated_at?->format('d/m/Y H:i')); ?></p>
                    </div>
                </div>
            </div>
        </div>

        <div class="card">
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
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $batch->items; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                                <tr>
                                    <td><?php echo e($item->member->name); ?></td>
                                    <td>Rp <?php echo e(number_format($item->member_revenue, 0, ',', '.')); ?></td>
                                    <td><?php echo e(number_format($item->percentage, 2)); ?>%</td>
                                    <td>Rp <?php echo e(number_format($item->expense_share, 0, ',', '.')); ?></td>
                                    <td class="fw-bold text-success">Rp <?php echo e(number_format($item->net_share, 0, ',', '.')); ?></td>
                                </tr>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                        </tbody>
                        <tfoot class="table-secondary">
                            <tr>
                                <th>Total</th>
                                <th>Rp <?php echo e(number_format($batch->total_revenue, 0, ',', '.')); ?></th>
                                <th>100%</th>
                                <th>Rp <?php echo e(number_format($batch->total_expense, 0, ',', '.')); ?></th>
                                <th>Rp <?php echo e(number_format($batch->total_distributed, 0, ',', '.')); ?></th>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    </div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\Lenovo\OneDrive\dsBilling\resources\views\revenue-sharing\show.blade.php ENDPATH**/ ?>