<div class="row g-4">
    <div class="col-12">
        <div class="card card-stat bg-white dark:bg-slate-800">
            <div class="card-body d-flex align-items-center justify-content-between">
                <div>
                    <h2 class="h3 mb-1 fw-bold">Lead Management</h2>
                    <p class="text-muted mb-0">Total: <span class="text-primary fw-bold"><?php echo e($leads->total()); ?></span> Lead</p>
                </div>
                <button class="btn btn-primary" onclick="window.location.href='<?php echo e(route('onboarding.leads.create')); ?>'">
                    <i class="bi bi-plus me-1"></i> Tambah Lead
                </button>
            </div>
        </div>
    </div>

    <div class="col-12">
        <div class="card card-stat bg-white dark:bg-slate-800">
            <div class="card-body p-0">
                <div class="w-full overflow-x-auto">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                        <tr>
                            <th>Nama</th>
                            <th>Telepon</th>
                            <th>Email</th>
                            <th>Alamat</th>
                            <th>Status</th>
                            <th>Tanggal</th>
                            <th>Aksi</th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $leads; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $lead): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="avatar-sm bg-gradient-primary rounded-circle d-flex align-items-center justify-content-center text-white fw-bold me-2">
                                            <?php echo e(substr($lead->name[0] ?? 'U', 0, 1)); ?>

                                        </div>
                                        <div>
                                            <div class="fw-bold"><?php echo e($lead->name); ?></div>
                                        </div>
                                    </div>
                                </td>
                                <td><div class="text-muted small"><?php echo e($lead->phone); ?></div></td>
                                <td><div class="text-muted small"><?php echo e($lead->email); ?></div></td>
                                <td><div class="text-muted small"><?php echo e(Str::limit($lead->address, 30)); ?></div></td>
                                <td>
                                    <span class="badge <?php echo e($lead->status == 'new' ? 'bg-primary' : ($lead->status == 'converted' ? 'bg-success' : 'bg-warning')); ?> badge-status">
                                        <?php echo e(ucfirst($lead->status)); ?>

                                    </span>
                                </td>
                                <td><div class="text-muted small"><?php echo e($lead->created_at->format('d/m/Y')); ?></div></td>
                                <td>
                                    <div class="d-flex gap-1">
                                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($lead->status == 'new'): ?>
                                            <button class="btn btn-sm btn-success" wire:click="convertToProspect(<?php echo e($lead->id); ?>)">
                                                <i class="bi bi-arrow-right-circle"></i> Convert
                                            </button>
                                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                        <button class="btn btn-sm btn-outline-primary">
                                            <i class="bi bi-eye"></i>
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
                        Menampilkan <?php echo e($leads->firstItem()); ?> - <?php echo e($leads->lastItem()); ?> dari <?php echo e($leads->total()); ?>

                    </div>
                    <div>
                        <?php echo e($leads->links()); ?>

                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php /**PATH D:\dsBilling\resources\views\livewire\onboarding\lead-index.blade.php ENDPATH**/ ?>