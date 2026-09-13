<?php $__env->startSection('title', 'Detail Akun Kas'); ?>

<?php $__env->startSection('content'); ?>
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Detail Akun Kas</h2>
        <a href="<?php echo e(route('cash-accounts.index')); ?>" class="btn btn-secondary">
            <i class="bi bi-arrow-left me-1"></i> Kembali
        </a>
    </div>

    <div class="card">
        <div class="card-body">
            <dl class="row">
                <dt class="col-sm-3">Kode Akun</dt>
                <dd class="col-sm-9"><?php echo e($cashAccount->code); ?></dd>

                <dt class="col-sm-3">Nama Akun</dt>
                <dd class="col-sm-9"><?php echo e($cashAccount->name); ?></dd>

                <dt class="col-sm-3">Tipe Akun</dt>
                <dd class="col-sm-9">
                    <?php
                        $typeLabels = [
                            'kas_besar' => 'Kas Besar',
                            'kecil' => 'Kas Kecil',
                            'tabungan' => 'Tabungan'
                        ];
                    ?>
                    <?php echo e($typeLabels[$cashAccount->type] ?? $cashAccount->type); ?>

                </dd>

                <dt class="col-sm-3">Deskripsi</dt>
                <dd class="col-sm-9"><?php echo e($cashAccount->description ?? '-'); ?></dd>

                <dt class="col-sm-3">Saldo Saat Ini</dt>
                <dd class="col-sm-9">Rp <?php echo e(number_format($cashAccount->balance, 0, ',', '.')); ?></dd>

                <dt class="col-sm-3">Status</dt>
                <dd class="col-sm-9">
                    <span class="badge <?php echo e($cashAccount->status === 'active' ? 'bg-success' : 'bg-secondary'); ?>">
                        <?php echo e($cashAccount->status === 'active' ? 'Aktif' : 'Nonaktif'); ?>

                    </span>
                </dd>

                <dt class="col-sm-3">Dibuat Pada</dt>
                <dd class="col-sm-9"><?php echo e($cashAccount->created_at->format('d/m/Y H:i')); ?></dd>

                <dt class="col-sm-3">Diperbarui Pada</dt>
                <dd class="col-sm-9"><?php echo e($cashAccount->updated_at->format('d/m/Y H:i')); ?></dd>
            </dl>

            <div class="d-flex gap-2 mt-4">
                <a href="<?php echo e(route('cash-accounts.edit', $cashAccount)); ?>" class="btn btn-warning">
                    <i class="bi bi-pencil me-1"></i> Edit
                </a>
                <form action="<?php echo e(route('cash-accounts.destroy', $cashAccount)); ?>" method="POST" onsubmit="return confirm('Yakin ingin menghapus akun kas ini?')">
                    <?php echo csrf_field(); ?>
                    <?php echo method_field('DELETE'); ?>
                    <button type="submit" class="btn btn-danger">
                        <i class="bi bi-trash me-1"></i> Hapus
                    </button>
                </form>
            </div>
        </div>
    </div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\dsBilling\resources\views\cash-accounts\show.blade.php ENDPATH**/ ?>