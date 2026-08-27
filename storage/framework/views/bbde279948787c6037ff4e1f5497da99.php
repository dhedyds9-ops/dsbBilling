<?php $__env->startSection('title', 'Transaksi Kas'); ?>

<?php $__env->startSection('content'); ?>
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2><?php echo e($pageTitle); ?></h2>
        <div class="d-flex gap-2">
            <form method="GET" action="<?php echo e(route('cash-transactions.index')); ?>" class="d-flex gap-2">
                <input type="hidden" name="type" value="<?php echo e(request('type')); ?>">
                <input type="month" name="period" class="form-control" value="<?php echo e(request('period')); ?>" placeholder="Periode">
                <button type="submit" class="btn btn-secondary">Filter</button>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(request('period') || request('type')): ?>
                    <a href="<?php echo e(route('cash-transactions.index')); ?>" class="btn btn-light">Reset</a>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </form>
            <a href="<?php echo e(route('cash-transactions.create', ['type' => request('type')])); ?>" class="btn btn-primary">
                <i class="bi bi-plus-circle me-1"></i> Tambah <?php echo e(request('type') === 'income' ? 'Pendapatan' : (request('type') === 'expense' ? 'Pengeluaran' : (request('type') === 'transfer' ? 'Transfer' : 'Transaksi'))); ?>

            </a>
        </div>
    </div>

    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(session('success')): ?>
        <div class="alert alert-success"><?php echo e(session('success')); ?></div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-hover">
                    <thead class="table-light">
                        <tr>
                            <th>Kode</th>
                            <th>Tanggal</th>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(!request('type')): ?>
                                <th>Tipe</th>
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            <th>Akun Kas</th>
                            <th>Akun Tujuan</th>
                            <th>Kategori Pengeluaran</th>
                            <th>Jumlah</th>
                            <th>Status</th>
                            <th style="width: 220px;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $transactions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $transaction): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                            <tr>
                                <td><?php echo e($transaction->code); ?></td>
                                <td><?php echo e($transaction->date->format('d/m/Y')); ?></td>
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(!request('type')): ?>
                                    <td>
                                        <span class="badge <?php echo e($transaction->type === 'income' ? 'bg-success' : ($transaction->type === 'expense' ? 'bg-danger' : 'bg-info')); ?>">
                                            <?php echo e($transaction->type === 'income' ? 'Pendapatan' : ($transaction->type === 'expense' ? 'Pengeluaran' : 'Transfer')); ?>

                                        </span>
                                    </td>
                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                <td><?php echo e($transaction->cashAccount->name ?? '-'); ?></td>
                                <td><?php echo e($transaction->relatedCashAccount->name ?? '-'); ?></td>
                                <td><?php echo e($transaction->expenseCategory->name ?? '-'); ?></td>
                                <td>Rp <?php echo e(number_format($transaction->amount, 0, ',', '.')); ?></td>
                                <td>
                                    <span class="badge <?php echo e($transaction->status === 'posted' ? 'bg-success' : ($transaction->status === 'canceled' ? 'bg-danger' : 'bg-secondary')); ?>">
                                        <?php echo e($transaction->status === 'posted' ? 'Diposting' : ($transaction->status === 'canceled' ? 'Dibatalkan' : 'Draft')); ?>

                                    </span>
                                </td>
                                <td>
                                    <div class="d-flex gap-2">
                                        <a href="<?php echo e(route('cash-transactions.show', $transaction->id)); ?>" class="btn btn-sm btn-info">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                        <a href="<?php echo e(route('cash-transactions.edit', $transaction->id)); ?>" class="btn btn-sm btn-warning">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                        <form action="<?php echo e(route('cash-transactions.destroy', $transaction->id)); ?>" method="POST" onsubmit="return confirm('Yakin ingin menghapus transaksi kas ini?')">
                                            <?php echo csrf_field(); ?>
                                            <?php echo method_field('DELETE'); ?>
                                            <button type="submit" class="btn btn-sm btn-danger">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                    </tbody>
                </table>
            </div>
            <?php echo e($transactions->links()); ?>

        </div>
    </div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\Lenovo\OneDrive\dsBilling\resources\views\cash-transactions\index.blade.php ENDPATH**/ ?>