

<?php $__env->startSection('title', 'Neraca Saldo (Trial Balance)'); ?>

<?php $__env->startSection('content'); ?>
<div class="row mb-4">
    <div class="col-md-12">
        <div class="card">
            <div class="card-body">
                <form method="GET" class="row g-3">
                    <div class="col-md-5">
                        <label class="form-label">Tanggal Mulai</label>
                        <input type="date" name="start_date" value="<?php echo e($startDate); ?>" class="form-control dark:bg-slate-900 dark:text-slate-100">
                    </div>
                    <div class="col-md-5">
                        <label class="form-label">Tanggal Selesai</label>
                        <input type="date" name="end_date" value="<?php echo e($endDate); ?>" class="form-control dark:bg-slate-900 dark:text-slate-100">
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
        <h5 class="mb-0">Neraca Saldo Periode <?php echo e(\Carbon\Carbon::parse($startDate)->format('d/m/Y')); ?> - <?php echo e(\Carbon\Carbon::parse($endDate)->format('d/m/Y')); ?></h5>
        <div class="btn-group">
            <button class="btn btn-outline-secondary" onclick="window.print()">
                <i class="bi bi-printer"></i> Cetak
            </button>
        </div>
    </div>
    <div class="card-body">
        <div class="w-full overflow-x-auto">
            <table class="table table-striped table-hover">
                <thead class="table-dark">
                    <tr>
                        <th>Kode Akun</th>
                        <th>Nama Akun</th>
                        <th class="text-end">Debit</th>
                        <th class="text-end">Kredit</th>
                        <th class="text-end">Saldo</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $trialBalance['accounts']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $account): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                    <tr>
                        <td><strong><?php echo e($account['code']); ?></strong></td>
                        <td><?php echo e($account['name']); ?></td>
                        <td class="text-end">Rp <?php echo e($account['debit']); ?></td>
                        <td class="text-end">Rp <?php echo e($account['credit']); ?></td>
                        <td class="text-end">
                            <span class="<?php echo e((str_replace('.', '', $account['balance']) >= 0) ? 'text-success' : 'text-danger'); ?>">
                                Rp <?php echo e($account['balance']); ?>

                            </span>
                        </td>
                    </tr>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                </tbody>
                <tfoot class="table-secondary fw-bold">
                    <tr>
                        <td colspan="2" class="text-end">Total</td>
                        <td class="text-end">Rp <?php echo e($trialBalance['total_debit']); ?></td>
                        <td class="text-end">Rp <?php echo e($trialBalance['total_credit']); ?></td>
                        <td></td>
                    </tr>
                </tfoot>
            </table>
        </div>
        
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($trialBalance['is_balanced']): ?>
            <div class="alert alert-success mt-3">
                <i class="bi bi-check-circle"></i> Neraca saldo <strong>seimbang</strong>!
            </div>
        <?php else: ?>
            <div class="alert alert-danger mt-3">
                <i class="bi bi-exclamation-triangle"></i> Neraca saldo <strong>tidak seimbang</strong>!
            </div>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\dsBilling\resources\views\reports\trial-balance.blade.php ENDPATH**/ ?>