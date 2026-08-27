<?php $__env->startSection('title', 'Laporan Laba Rugi'); ?>

<?php $__env->startSection('content'); ?>
<div class="row mb-4">
    <div class="col-md-12">
        <div class="card">
            <div class="card-body">
                <form method="GET" class="row g-3">
                    <div class="col-md-5">
                        <label class="form-label">Tanggal Mulai</label>
                        <input type="date" name="start_date" value="<?php echo e($startDate); ?>" class="form-control">
                    </div>
                    <div class="col-md-5">
                        <label class="form-label">Tanggal Selesai</label>
                        <input type="date" name="end_date" value="<?php echo e($endDate); ?>" class="form-control">
                    </div>
                    <div class="col-md-2 d-flex align-items-end">
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="bi bi-funnel"></i> Filter
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">Laporan Laba Rugi Periode <?php echo e(\Carbon\Carbon::parse($startDate)->format('d/m/Y')); ?> - <?php echo e(\Carbon\Carbon::parse($endDate)->format('d/m/Y')); ?></h5>
        <div class="btn-group">
            <button class="btn btn-outline-secondary" onclick="window.print()">
                <i class="bi bi-printer"></i> Cetak
            </button>
        </div>
    </div>
    <div class="card-body">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <table class="table table-bordered">
                    <tr class="table-primary">
                        <th colspan="2" class="text-center">PENDAPATAN</th>
                    </tr>
                    <tr>
                        <td>Total Pendapatan</td>
                        <td class="text-end fw-bold text-success">Rp <?php echo e($profitLoss['total_revenue']); ?></td>
                    </tr>
                    <tr class="table-danger">
                        <th colspan="2" class="text-center">PENGELUARAN</th>
                    </tr>
                    <tr>
                        <td>Total Pengeluaran</td>
                        <td class="text-end fw-bold text-danger">Rp <?php echo e($profitLoss['total_expenses']); ?></td>
                    </tr>
                    <tr class="table-secondary">
                        <th class="text-center">LABA BERSIH</th>
                        <td class="text-end fw-bold fs-4">
                            <span class="<?php echo e((str_replace('.', '', $profitLoss['net_profit']) >= 0) ? 'text-success' : 'text-danger'); ?>">
                                Rp <?php echo e($profitLoss['net_profit']); ?>

                            </span>
                        </td>
                    </tr>
                </table>
            </div>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\Lenovo\OneDrive\dsBilling\resources\views\reports\profit-loss.blade.php ENDPATH**/ ?>