

<?php $__env->startSection('title', 'Data Pelanggan'); ?>

<?php $__env->startSection('content'); ?>
    <div class="row g-4">
        <!-- Header & KPI -->
        <div class="col-12">
            <div class="card card-stat bg-white dark:bg-slate-800">
                <div class="card-body d-flex align-items-center justify-content-between">
                    <div>
                        <h2 class="h3 mb-1 fw-bold">Data Pelanggan</h2>
                        <p class="text-muted mb-0">Total: <span class="text-primary fw-bold"><?php echo e($customers->total()); ?></span> Pelanggan</p>
                    </div>
                    <div class="d-flex gap-2">
                        <button class="btn btn-outline-secondary">
                            <i class="bi bi-arrow-clockwise me-1"></i> Refresh
                        </button>
                        <button class="btn btn-primary">
                            <i class="bi bi-plus me-1"></i> Tambah Pelanggan
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filters -->
        <div class="col-12">
            <div class="card card-stat bg-white dark:bg-slate-800">
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-3">
                            <input type="text" class="form-control dark:bg-slate-900 dark:text-slate-100" placeholder="Cari nama, alamat, atau email...">
                        </div>
                        <div class="col-md-2">
                            <select class="form-select dark:bg-slate-900 dark:text-slate-100 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-100 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-100">
                                <option>Semua Paket</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <select class="form-select dark:bg-slate-900 dark:text-slate-100 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-100 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-100">
                                <option>Semua Status</option>
                                <option>Active</option>
                                <option>Suspended</option>
                                <option>Expired</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <select class="form-select dark:bg-slate-900 dark:text-slate-100 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-100 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-100">
                                <option>Semua Wilayah</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <button class="btn btn-primary w-100">
                                <i class="bi bi-funnel me-1"></i> Filter
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Table -->
        <div class="col-12">
            <div class="card card-stat bg-white dark:bg-slate-800">
                <div class="card-body p-0">
                    <div class="w-full overflow-x-auto">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                            <tr>
                                <th>Nama</th>
                                <th>Status</th>
                                <th>Paket</th>
                                <th>IP Address</th>
                                <th>Aktivitas</th>
                                <th>Alamat</th>
                                <th>Telepon</th>
                                <th>Billing</th>
                                <th>Aksi</th>
                            </tr>
                            </thead>
                            <tbody>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $customers; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $customer): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="avatar-sm bg-gradient-primary rounded-circle d-flex align-items-center justify-content-center text-white fw-bold me-2">
                                                <?php echo e(strtoupper(substr($customer->name ?? 'U', 0, 1))); ?>

                                            </div>
                                            <div>
                                                <div class="fw-bold"><?php echo e($customer->name); ?></div>
                                                <div class="text-muted small"><?php echo e($customer->email); ?></div>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge bg-success badge-status">Active</span>
                                    </td>
                                    <td><span class="text-primary fw-bold">HOME 100 Mbps</span></td>
                                    <td><span class="text-muted small">192.168.1.10</span></td>
                                    <td>
                                        <div class="text-muted small">
                                            <i class="bi bi-download"></i> 4.5 GB
                                            <i class="bi bi-upload ms-2"></i> 205 MB
                                        </div>
                                    </td>
                                    <td><div class="text-muted small">Jalan Contoh No. 123</div></td>
                                    <td><div class="text-muted small">081234567890</div></td>
                                    <td>
                                        <span class="badge bg-primary badge-status">Prepaid</span>
                                    </td>
                                    <td>
                                        <div class="d-flex gap-1">
                                            <button class="btn btn-sm btn-outline-primary">
                                                <i class="bi bi-eye"></i>
                                            </button>
                                            <button class="btn btn-sm btn-outline-success">
                                                <i class="bi bi-pencil"></i>
                                            </button>
                                            <button class="btn btn-sm btn-outline-danger">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                    <div class="d-flex justify-content-between align-items-center p-3 border-top">
                        <div class="text-muted small">
                            Menampilkan <?php echo e($customers->firstItem()); ?> - <?php echo e($customers->lastItem()); ?> dari <?php echo e($customers->total()); ?>

                        </div>
                        <div>
                            <?php echo e($customers->links()); ?>

                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\dsBilling\resources\views\customers\index.blade.php ENDPATH**/ ?>