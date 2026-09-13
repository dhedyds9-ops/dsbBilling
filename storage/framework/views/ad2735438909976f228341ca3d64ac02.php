<div class="min-h-screen bg-gray-50 dark:bg-gray-900/50 flex flex-col justify-center py-12 sm:px-6 lg:px-8">
    <div class="sm:mx-auto sm:w-full sm:max-w-md">
        <div class="flex justify-center">
            <?php if (isset($component)) { $__componentOriginal8892e718f3d0d7a916180885c6f012e7 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal8892e718f3d0d7a916180885c6f012e7 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.application-logo','data' => ['class' => 'w-auto h-12 text-primary-600']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('application-logo'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['class' => 'w-auto h-12 text-primary-600']); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal8892e718f3d0d7a916180885c6f012e7)): ?>
<?php $attributes = $__attributesOriginal8892e718f3d0d7a916180885c6f012e7; ?>
<?php unset($__attributesOriginal8892e718f3d0d7a916180885c6f012e7); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal8892e718f3d0d7a916180885c6f012e7)): ?>
<?php $component = $__componentOriginal8892e718f3d0d7a916180885c6f012e7; ?>
<?php unset($__componentOriginal8892e718f3d0d7a916180885c6f012e7); ?>
<?php endif; ?>
        </div>
        <h2 class="mt-6 text-center text-3xl font-extrabold text-gray-900 dark:text-gray-100">
            Status Pembelian
        </h2>
    </div>

    <div class="mt-8 sm:mx-auto sm:w-full sm:max-w-md" wire:poll.3s="checkStatus" x-data="{ isPolling: true }" <?php $__env->stopSection(); ?>-polling.window="isPolling = false">
        <div class="bg-white dark:bg-slate-800 py-8 px-4 shadow sm:rounded-lg sm:px-10">
            
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($order->status === \App\Models\VoucherOrder::STATUS_COMPLETED): ?>
                <div class="text-center mb-6">
                    <div class="mx-auto flex items-center justify-center h-16 w-16 rounded-full bg-green-100 dark:bg-green-900/50 mb-4">
                        <span class="material-symbols-outlined text-green-600 text-3xl">check_circle</span>
                    </div>
                    <h3 class="text-xl font-bold text-gray-900 dark:text-gray-100">Pembelian Berhasil! 🎉</h3>
                    <p class="text-sm text-gray-500 dark:text-gray-400 mt-2">Voucher internet Anda sudah siap digunakan.</p>
                </div>

                <div class="bg-gray-50 dark:bg-gray-900/50 rounded-lg p-6 border border-gray-200 dark:border-gray-700 mb-6 relative overflow-hidden">
                    <div class="absolute top-0 right-0 -mt-2 -mr-2 w-16 h-16 bg-primary-100 rounded-bl-full z-0"></div>
                    
                    <div class="relative z-10 space-y-4">
                        <div class="border-b border-gray-200 dark:border-gray-700 pb-4">
                            <p class="text-xs text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-1">Paket Internet</p>
                            <p class="font-bold text-gray-900 dark:text-gray-100"><?php echo e($order->service_profile_name); ?></p>
                        </div>
                        
                        <div>
                            <p class="text-xs text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-1">Username</p>
                            <div class="flex items-center">
                                <code class="bg-white dark:bg-slate-800 px-3 py-2 rounded border border-gray-300 dark:border-gray-600 font-mono text-lg text-primary-700 flex-1"><?php echo e($order->voucher_username); ?></code>
                            </div>
                        </div>
                        
                        <div>
                            <p class="text-xs text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-1">Password</p>
                            <div class="flex items-center">
                                <code class="bg-white dark:bg-slate-800 px-3 py-2 rounded border border-gray-300 dark:border-gray-600 font-mono text-lg text-primary-700 flex-1"><?php echo e($order->voucher_password); ?></code>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="space-y-3">
                    <a href="http://login.hotspot" target="_blank" class="w-full flex justify-center py-3 px-4 border border-transparent rounded-md shadow-sm text-base font-medium text-white bg-primary-600 hover:bg-primary-700">
                        Login Hotspot Sekarang
                    </a>
                    
                    <a href="/" class="w-full flex justify-center py-2 px-4 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-slate-800 hover:bg-gray-50 dark:bg-gray-900/50">
                        Kembali ke Beranda
                    </a>
                </div>
                
                <p class="text-xs text-center text-gray-500 dark:text-gray-400 mt-6">
                    Kredensial ini juga telah dikirimkan ke WhatsApp Anda: <strong><?php echo e($order->wa_number); ?></strong>
                </p>

            <?php elseif($order->status === \App\Models\VoucherOrder::STATUS_FAILED): ?>
                <div class="text-center mb-6">
                    <div class="mx-auto flex items-center justify-center h-16 w-16 rounded-full bg-red-100 dark:bg-red-900/50 mb-4">
                        <span class="material-symbols-outlined text-red-600 text-3xl">cancel</span>
                    </div>
                    <h3 class="text-xl font-bold text-red-600">Terjadi Kesalahan</h3>
                    <p class="text-sm text-gray-500 dark:text-gray-400 mt-2">Maaf, voucher Anda gagal dibuat.</p>
                </div>
                
                <div class="bg-red-50 dark:bg-red-900/30 p-4 rounded-md border border-red-100 mb-6 text-sm text-red-700 text-center">
                    <?php echo e($order->failure_reason ?? 'Kesalahan sistem saat memproses voucher ke Mikrotik.'); ?>

                </div>
                
                <a href="https://wa.me/62811111111" class="w-full flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-green-600 hover:bg-green-700">
                    Hubungi Bantuan (CS)
                </a>

            <?php elseif($order->status === \App\Models\VoucherOrder::STATUS_PENDING): ?>
                <div class="text-center mb-6">
                    <div class="mx-auto flex items-center justify-center h-16 w-16 rounded-full bg-yellow-100 dark:bg-yellow-900/50 mb-4">
                        <span class="material-symbols-outlined text-yellow-600 text-3xl">payments</span>
                    </div>
                    <h3 class="text-xl font-bold text-gray-900 dark:text-gray-100">Menunggu Pembayaran</h3>
                    <p class="text-sm text-gray-500 dark:text-gray-400 mt-2">Silakan selesaikan pembayaran di halaman Midtrans/Gateway.</p>
                </div>
                <div class="flex justify-center">
                    <div class="animate-pulse flex space-x-2">
                        <div class="h-2 w-2 bg-gray-400 rounded-full"></div>
                        <div class="h-2 w-2 bg-gray-400 rounded-full"></div>
                        <div class="h-2 w-2 bg-gray-400 rounded-full"></div>
                    </div>
                </div>

            <?php else: ?>
                <div class="text-center mb-6">
                    <div class="mx-auto flex items-center justify-center h-16 w-16 rounded-full bg-blue-100 dark:bg-blue-900/50 mb-4 animate-bounce">
                        <span class="material-symbols-outlined text-primary-600 text-3xl">settings_b_roll</span>
                    </div>
                    <h3 class="text-xl font-bold text-gray-900 dark:text-gray-100">Sedang Memproses...</h3>
                    <p class="text-sm text-gray-500 dark:text-gray-400 mt-2">Pembayaran diterima! Sistem sedang membuat kode Voucher di Mikrotik Anda.</p>
                </div>
                
                <div class="w-full bg-gray-200 rounded-full h-2.5 mb-4 dark:bg-gray-700 overflow-hidden">
                  <div class="bg-primary-600 h-2.5 rounded-full w-full animate-pulse"></div>
                </div>
                
                <p class="text-xs text-center text-gray-400">Harap tunggu sejenak, halaman akan memuat otomatis.</p>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            
        </div>
    </div>
</div>






<?php /**PATH D:\dsBilling\resources\views\livewire\guest\voucher-order-success.blade.php ENDPATH**/ ?>