<?php $__env->startSection('title', 'Detail Kategori Pengeluaran'); ?>

<?php $__env->startSection('content'); ?>
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Detail Kategori Pengeluaran</h2>
        <a href="<?php echo e(route('expense-categories.index')); ?>" class="btn btn-secondary">
            <i class="bi bi-arrow-left me-1"></i> Kembali
        </a>
    </div>

    <div class="card">
        <div class="card-body">
            <dl class="row">
                <dt class="col-sm-3">Kode Kategori</dt>
                <dd class="col-sm-9"><?php echo e($expenseCategory->code); ?></dd>

                <dt class="col-sm-3">Nama Kategori</dt>
                <dd class="col-sm-9"><?php echo e($expenseCategory->name); ?></dd>

                <dt class="col-sm-3">Deskripsi</dt>
                <dd class="col-sm-9"><?php echo e($expenseCategory->description ?? '-'); ?></dd>

                <dt class="col-sm-3">Status</dt>
                <dd class="col-sm-9">
                    <span class="badge <?php echo e($expenseCategory->status === 'active' ? 'bg-success' : 'bg-secondary'); ?>">
                        <?php echo e($expenseCategory->status === 'active' ? 'Aktif' : 'Nonaktif'); ?>

                    </span>
                </dd>

                <dt class="col-sm-3">Dibuat Pada</dt>
                <dd class="col-sm-9"><?php echo e($expenseCategory->created_at->format('d/m/Y H:i')); ?></dd>

                <dt class="col-sm-3">Diperbarui Pada</dt>
                <dd class="col-sm-9"><?php echo e($expenseCategory->updated_at->format('d/m/Y H:i')); ?></dd>
            </dl>

            <div class="d-flex gap-2 mt-4">
                <a href="<?php echo e(route('expense-categories.edit', $expenseCategory)); ?>" class="btn btn-warning">
                    <i class="bi bi-pencil me-1"></i> Edit
                </a>
                <form action="<?php echo e(route('expense-categories.destroy', $expenseCategory)); ?>" method="POST" onsubmit="return confirm('Yakin ingin menghapus kategori ini?')">
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

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\Lenovo\OneDrive\dsBilling\resources\views\expense-categories\show.blade.php ENDPATH**/ ?>