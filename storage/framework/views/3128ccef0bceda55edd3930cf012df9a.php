<?php $__env->startSection('title', 'Detail Paket Internet'); ?>

<?php $__env->startSection('content'); ?>
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Detail Paket Internet</h2>
        <a href="<?php echo e(route('internet-packages.index')); ?>" class="btn btn-secondary">
            <i class="bi bi-arrow-left me-1"></i> Kembali
        </a>
    </div>

    <div class="card">
        <div class="card-body">
            <div class="row mb-3">
                <div class="col-md-4">
                    <label class="form-label fw-bold">Kode Paket</label>
                    <p class="form-control-plaintext"><?php echo e($internetPackage->code); ?></p>
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-bold">Nama Paket</label>
                    <p class="form-control-plaintext"><?php echo e($internetPackage->name); ?></p>
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-bold">Status</label>
                    <p class="form-control-plaintext">
                        <span class="badge <?php echo e($internetPackage->is_active ? 'bg-success' : 'bg-secondary'); ?>">
                            <?php echo e($internetPackage->is_active ? 'Aktif' : 'Nonaktif'); ?>

                        </span>
                    </p>
                </div>
            </div>

            <div class="row mb-3">
                <div class="col-md-4">
                    <label class="form-label fw-bold">Download (Mbps)</label>
                    <p class="form-control-plaintext"><?php echo e($internetPackage->bandwidth_download); ?> Mbps</p>
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-bold">Upload (Mbps)</label>
                    <p class="form-control-plaintext"><?php echo e($internetPackage->bandwidth_upload); ?> Mbps</p>
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-bold">Harga</label>
                    <p class="form-control-plaintext">Rp <?php echo e(number_format($internetPackage->price, 0, ',', '.')); ?></p>
                </div>
            </div>

            <div class="mb-3">
                <label class="form-label fw-bold">Deskripsi</label>
                <p class="form-control-plaintext"><?php echo e($internetPackage->description ?? '-'); ?></p>
            </div>

            <div class="d-flex gap-2">
                <a href="<?php echo e(route('internet-packages.edit', $internetPackage->id)); ?>" class="btn btn-warning">
                    <i class="bi bi-pencil me-1"></i> Edit
                </a>
                <a href="<?php echo e(route('internet-packages.index')); ?>" class="btn btn-secondary">
                    Kembali
                </a>
            </div>
        </div>
    </div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\dsBilling\resources\views\internet-packages\show.blade.php ENDPATH**/ ?>