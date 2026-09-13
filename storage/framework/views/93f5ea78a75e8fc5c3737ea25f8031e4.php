<?php $__env->startSection('title', 'Detail Pendapatan Anggota'); ?>

<?php $__env->startSection('content'); ?>
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Detail Pendapatan Anggota</h2>
        <a href="<?php echo e(route('member-incomes.index')); ?>" class="btn btn-secondary">
            <i class="bi bi-arrow-left me-1"></i> Kembali
        </a>
    </div>

    <div class="card">
        <div class="card-body">
            <table class="table table-bordered">
                <tr>
                    <th style="width: 30%;">Kode</th>
                    <td><?php echo e($memberIncome->code); ?></td>
                </tr>
                <tr>
                    <th>Tanggal</th>
                    <td><?php echo e($memberIncome->date->format('d/m/Y')); ?></td>
                </tr>
                <tr>
                    <th>Periode</th>
                    <td><?php echo e($memberIncome->period); ?></td>
                </tr>
                <tr>
                    <th>Anggota</th>
                    <td><?php echo e($memberIncome->member->name ?? '-'); ?></td>
                </tr>
                <tr>
                    <th>Kategori Pendapatan</th>
                    <td><?php echo e($memberIncome->incomeCategory->name ?? '-'); ?></td>
                </tr>
                <tr>
                    <th>Jumlah</th>
                    <td>Rp <?php echo e(number_format($memberIncome->amount, 0, ',', '.')); ?></td>
                </tr>
                <tr>
                    <th>Status</th>
                    <td>
                        <span class="badge <?php echo e($memberIncome->status === 'posted' ? 'bg-success' : ($memberIncome->status === 'canceled' ? 'bg-danger' : 'bg-secondary')); ?>">
                            <?php echo e($memberIncome->status === 'posted' ? 'Diposting' : ($memberIncome->status === 'canceled' ? 'Dibatalkan' : 'Draft')); ?>

                        </span>
                    </td>
                </tr>
                <tr>
                    <th>Deskripsi</th>
                    <td><?php echo e($memberIncome->description ?? '-'); ?></td>
                </tr>
            </table>
        </div>
    </div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\dsBilling\resources\views\member-incomes\show.blade.php ENDPATH**/ ?>