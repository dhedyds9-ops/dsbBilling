<div class="p-6">
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-6">
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
            <div class="text-gray-900 font-bold text-xl">
                <?php echo e($internet_status['status'] === 'online' ? '🟢' : '🔴'); ?>

                <?php echo e($internet_status['message']); ?>

            </div>
            <div class="text-gray-600 text-sm">
                Status Koneksi Internet
            </div>
        </div>
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
            <div class="text-gray-900 font-bold text-xl">
                Rp <?php echo e(number_format($total_outstanding, 2, ',', '.')); ?>

            </div>
            <div class="text-gray-600 text-sm">
                Tagihan Outstanding
            </div>
        </div>
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
            <div class="text-gray-900 font-bold text-xl">
                <?php echo e(count($active_invoices)); ?>

            </div>
            <div class="text-gray-600 text-sm">
                Tagihan Aktif
            </div>
        </div>
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
            <div class="text-gray-900 font-bold text-xl">
                <?php echo e(count($customer_services)); ?>

            </div>
            <div class="text-gray-600 text-sm">
                Layanan Aktif
            </div>
        </div>
    </div>
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
            <h2 class="text-lg font-semibold text-gray-900 mb-4">Tagihan Terbaru</h2>
            <ul>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $recent_invoices; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $invoice): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                <li class="border-b border-gray-200 py-3 flex justify-between">
                    <div>
                        <div class="font-medium"><?php echo e($invoice->invoice_number); ?></div>
                        <div class="text-sm text-gray-600"><?php echo e($invoice->due_date?->format('d/m/Y')); ?></div>
                    </div>
                    <div class="text-right">
                        <div class="font-medium">Rp <?php echo e(number_format($invoice->total_amount, 2, ',', '.')); ?></div>
                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full <?php echo e($invoice->status === 'paid' ? 'bg-green-100 text-green-800' : ($invoice->status === 'overdue' ? 'bg-red-100 text-red-800' : 'bg-yellow-100 text-yellow-800')); ?>">
                            <?php echo e(ucfirst($invoice->status)); ?>

                        </span>
                    </div>
                </li>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
            </ul>
        </div>
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
            <h2 class="text-lg font-semibold text-gray-900 mb-4">Layanan Aktif</h2>
            <ul>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $customer_services; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $service): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                <li class="border-b border-gray-200 py-3">
                    <div class="font-medium"><?php echo e($service->serviceInstance?->serviceProfile?->name ?? 'Layanan'); ?></div>
                    <div class="text-sm text-gray-600">Status: <?php echo e(ucfirst($service->status)); ?></div>
                </li>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
            </ul>
        </div>
    </div>
</div>

<?php /**PATH C:\Users\Lenovo\OneDrive\dsBilling\resources\views\livewire\customer-portal\dashboard.blade.php ENDPATH**/ ?>