

<?php $__env->startSection('title', 'Pendapatan Anggota'); ?>

<?php $__env->startSection('content'); ?>
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Daftar Pendapatan Anggota</h2>
        <div class="d-flex gap-2">
            <form method="GET" action="<?php echo e(route('member-incomes.index')); ?>" class="d-flex gap-2">
                <input type="month" name="period" class="form-control dark:bg-slate-900 dark:text-slate-100" value="<?php echo e(request('period')); ?>" placeholder="Periode">
                <button type="submit" class="btn btn-secondary">Filter</button>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(request('period')): ?>
                    <a href="<?php echo e(route('member-incomes.index')); ?>" class="btn btn-light">Reset</a>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </form>
            <a href="<?php echo e(route('member-incomes.create')); ?>" class="btn btn-primary">
                <i class="bi bi-plus-circle me-1"></i> Tambah Pendapatan
            </a>
        </div>
    </div>

    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(session('success')): ?>
        <div class="alert alert-success"><?php echo e(session('success')); ?></div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

    <div class="card">
        <div class="card-body">
            <div class="w-full overflow-x-auto">
                <table class="table table-bordered table-hover">
                    <thead class="table-light">
                        <tr>
                            <th>Kode</th>
                            <th>Tanggal</th>
                            <th>Periode</th>
                            <th>Anggota</th>
                            <th>Kategori</th>
                            <th>Jumlah</th>
                            <th>Status</th>
                            <th style="width: 220px;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $incomes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $income): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                            <tr>
                                <td><?php echo e($income->code); ?></td>
                                <td><?php echo e($income->date->format('d/m/Y')); ?></td>
                                <td><?php echo e($income->period); ?></td>
                                <td><?php echo e($income->member->name ?? '-'); ?></td>
                                <td><?php echo e($income->incomeCategory->name ?? '-'); ?></td>
                                <td>Rp <?php echo e(number_format($income->amount, 0, ',', '.')); ?></td>
                                <td>
                                    <span class="badge <?php echo e($income->status === 'posted' ? 'bg-success' : ($income->status === 'canceled' ? 'bg-danger' : 'bg-secondary')); ?>">
                                        <?php echo e($income->status === 'posted' ? 'Diposting' : ($income->status === 'canceled' ? 'Dibatalkan' : 'Draft')); ?>

                                    </span>
                                </td>
                                <td>
                                    <div class="d-flex gap-2">
                                        <a href="<?php echo e(route('member-incomes.show', $income->id)); ?>" class="btn btn-sm btn-info">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                        <a href="<?php echo e(route('member-incomes.edit', $income->id)); ?>" class="btn btn-sm btn-warning">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                        <form action="<?php echo e(route('member-incomes.destroy', $income->id)); ?>" method="POST" onsubmit="return confirm('Yakin ingin menghapus pendapatan anggota ini?')">
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
            <?php echo e($incomes->links()); ?>

        </div>
    </div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\dsBilling\resources\views\member-incomes\index.blade.php ENDPATH**/ ?>