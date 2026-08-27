<?php $__env->startSection('title', 'Detail Pengeluaran Sharing'); ?>

<?php $__env->startSection('content'); ?>
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Detail Pengeluaran Sharing</h2>
        <a href="<?php echo e(route('expense-sharing.index')); ?>" class="btn btn-secondary">
            <i class="bi bi-arrow-left me-1"></i> Kembali
        </a>
    </div>

    <div class="card">
        <div class="card-body">
            <table class="table table-bordered">
                <tr>
                    <th style="width: 30%;">Kode</th>
                    <td><?php echo e($cashTransaction->code); ?></td>
                </tr>
                <tr>
                    <th>Tanggal</th>
                    <td><?php echo e($cashTransaction->date->format('d/m/Y')); ?></td>
                </tr>
                <tr>
                    <th>Akun Kas</th>
                    <td><?php echo e($cashTransaction->cashAccount->name ?? '-'); ?></td>
                </tr>
                <tr>
                    <th>Kategori Pengeluaran</th>
                    <td><?php echo e($cashTransaction->expenseCategory->name ?? '-'); ?></td>
                </tr>
                <tr>
                    <th>Jumlah</th>
                    <td>Rp <?php echo e(number_format($cashTransaction->amount, 0, ',', '.')); ?></td>
                </tr>
                <tr>
                    <th>Status</th>
                    <td>
                        <span class="badge <?php echo e($cashTransaction->status === 'posted' ? 'bg-success' : ($cashTransaction->status === 'canceled' ? 'bg-danger' : 'bg-secondary')); ?>">
                            <?php echo e($cashTransaction->status === 'posted' ? 'Diposting' : ($cashTransaction->status === 'canceled' ? 'Dibatalkan' : 'Draft')); ?>

                        </span>
                    </td>
                </tr>
                <tr>
                    <th>Deskripsi</th>
                    <td><?php echo e($cashTransaction->description ?? '-'); ?></td>
                </tr>
            </table>
        </div>
    </div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\Lenovo\OneDrive\dsBilling\resources\views\expense-sharing\show.blade.php ENDPATH**/ ?>