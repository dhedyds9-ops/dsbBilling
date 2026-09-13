<div class="p-6 bg-slate-50 dark:bg-slate-900/30 min-h-screen">

    <!-- Page Header -->
          <?php $__env->startSection('page_title'); ?>
        <div>
            <h1 class="text-1xl font-bold text-slate-900 dark:text-white">Dashboard</h1>
            <span class="text-xs text-slate-500 dark:text-slate-400">Last Update:</span>
            <span class="text-xs font-semibold text-slate-700 dark:text-slate-300"><?php echo e(now()->format('d/m/Y H:i')); ?></span>

        </div>
        <?php $__env->stopSection(); ?>
    

    <!-- Tab Navigation -->
    <div class="mb-6 flex items-center gap-1 bg-white dark:bg-slate-800 p-1 rounded-lg border border-slate-200 dark:border-slate-700 inline-flex overflow-x-auto">
        <button wire:click="setTab('ringkasan')" type="button"
                class="px-4 py-2 rounded-md text-sm font-semibold transition-colors whitespace-nowrap
                       <?php echo e($activeTab === 'ringkasan' ? 'bg-primary-600 text-white shadow-sm' : 'text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:bg-slate-800 dark:hover:bg-slate-700'); ?>">
            Ringkasan
        </button>
        <button wire:click="setTab('tagihan')" type="button"
                class="px-4 py-2 rounded-md text-sm font-semibold transition-colors whitespace-nowrap
                       <?php echo e($activeTab === 'tagihan' ? 'bg-primary-600 text-white shadow-sm' : 'text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:bg-slate-800 dark:hover:bg-slate-700'); ?>">
            Tagihan
        </button>
        <button wire:click="setTab('aktivitas')" type="button"
                class="px-4 py-2 rounded-md text-sm font-semibold transition-colors whitespace-nowrap
                       <?php echo e($activeTab === 'aktivitas' ? 'bg-primary-600 text-white shadow-sm' : 'text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:bg-slate-800 dark:hover:bg-slate-700'); ?>">
            Aktivitas
        </button>
    </div>

    <!-- Top Stat Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        <!-- Income Hari Ini -->
        <div class="bg-white dark:bg-slate-800 rounded-xl shadow-sm border border-slate-100 dark:border-slate-700 p-5">
            <div class="flex items-start justify-between">
                <div>
                    <p class="text-[11px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Pendapatan Hari Ini</p>
                    <p class="text-2xl font-extrabold text-emerald-600 dark:text-emerald-400 mt-2">
                        Rp <?php echo e(number_format($mixData['income_hari_ini'] ?? 0, 0, ',', '.')); ?>

                    </p>
                    <p class="text-xs text-slate-400 dark:text-slate-500 dark:text-slate-400 mt-1">Tanggal: <?php echo e(today()->format('d M Y')); ?></p>
                </div>
                <div class="p-3 rounded-xl bg-emerald-100 dark:bg-emerald-900/30 text-emerald-600 dark:text-emerald-400">
                    <span class="material-symbols-outlined text-2xl">payments</span>
                </div>
            </div>
        </div>

        <!-- Tagihan Belum Dibayar -->
        <div class="bg-white dark:bg-slate-800 rounded-xl shadow-sm border border-slate-100 dark:border-slate-700 p-5">
            <div class="flex items-start justify-between">
                <div>
                    <p class="text-[11px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Tagihan Tertunggak</p>
                    <p class="text-2xl font-extrabold text-amber-600 dark:text-amber-400 mt-2">
                        <?php echo e($mixData['invoice_data_tagihan'] ?? 0); ?>

                        <span class="text-sm font-medium text-slate-400 ml-1">unit</span>
                    </p>
                    <p class="text-xs text-amber-500 mt-1 font-semibold">
                        Lewat tempo: <?php echo e($mixData['jatuh_tempo'] ?? 0); ?>

                    </p>
                </div>
                <div class="p-3 rounded-xl bg-amber-100 dark:bg-amber-900/30 text-amber-600 dark:text-amber-400">
                    <span class="material-symbols-outlined text-2xl">receipt_long</span>
                </div>
            </div>
        </div>

        <!-- Pelanggan Aktif -->
        <div class="bg-white dark:bg-slate-800 rounded-xl shadow-sm border border-slate-100 dark:border-slate-700 p-5">
            <div class="flex items-start justify-between">
                <div>
                    <p class="text-[11px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Total Pelanggan</p>
                    <p class="text-2xl font-extrabold text-sky-600 dark:text-sky-400 mt-2">
                        <?php echo e(($mixData['hotspot_user'] ?? 0) + ($mixData['pppoe_user'] ?? 0)); ?>

                    </p>
                    <div class="flex flex-col gap-1 mt-1 text-xs font-medium">
                        <div class="flex items-center gap-2">
                            <span class="text-slate-500">PPPoE: <span class="text-slate-700 dark:text-slate-200"><?php echo e($mixData['pppoe_user'] ?? 0); ?></span></span>
                            <span class="px-1.5 py-0.5 rounded-full bg-emerald-100 text-emerald-700 dark:bg-emerald-900/50 dark:text-emerald-400 text-[9px] font-bold"><?php echo e($mixData['ppp_online'] ?? 0); ?> Online</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="text-slate-500">Hotspot: <span class="text-slate-700 dark:text-slate-200"><?php echo e($mixData['hotspot_user'] ?? 0); ?></span></span>
                            <span class="px-1.5 py-0.5 rounded-full bg-emerald-100 text-emerald-700 dark:bg-emerald-900/50 dark:text-emerald-400 text-[9px] font-bold"><?php echo e($mixData['hotspot_online'] ?? 0); ?> Online</span>
                        </div>
                    </div>
                </div>
                <div class="p-3 rounded-xl bg-sky-100 dark:bg-sky-900/30 text-sky-600 dark:text-sky-400">
                    <span class="material-symbols-outlined text-2xl">group</span>
                </div>
            </div>
        </div>

        <!-- Expired / Suspended -->
        <div class="bg-white dark:bg-slate-800 rounded-xl shadow-sm border border-slate-100 dark:border-slate-700 p-5">
            <div class="flex items-start justify-between">
                <div>
                    <p class="text-[11px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Expired / Suspend</p>
                    <p class="text-2xl font-extrabold text-rose-600 dark:text-rose-400 mt-2">
                        <?php echo e(($mixData['exp_voucher'] ?? 0) + ($mixData['exp_customer'] ?? 0)); ?>

                    </p>
                    <div class="flex gap-3 mt-1 text-xs font-medium">
                        <span class="text-slate-500">Voucher: <span class="text-slate-700 dark:text-slate-200"><?php echo e($mixData['exp_voucher'] ?? 0); ?></span></span>
                        <span class="text-slate-500">Cust: <span class="text-slate-700 dark:text-slate-200"><?php echo e($mixData['exp_customer'] ?? 0); ?></span></span>
                    </div>
                </div>
                <div class="p-3 rounded-xl bg-rose-100 dark:bg-rose-900/30 text-rose-600 dark:text-rose-400">
                    <span class="material-symbols-outlined text-2xl">warning</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Voucher Grid (sesuai blade lama yang dipertahankan) -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 mb-6">
        <div class="bg-slate-50 dark:bg-slate-800/50 rounded-xl p-4 flex items-center gap-3 border border-slate-100 dark:border-slate-700">
            <div class="p-2.5 bg-slate-200 dark:bg-slate-700 rounded-lg">
                <?php if (isset($component)) { $__componentOriginalce262628e3a8d44dc38fd1f3965181bc = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalce262628e3a8d44dc38fd1f3965181bc = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.icon','data' => ['name' => 'ticket','class' => 'w-6 h-6 text-slate-600 dark:text-slate-300']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'ticket','class' => 'w-6 h-6 text-slate-600 dark:text-slate-300']); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalce262628e3a8d44dc38fd1f3965181bc)): ?>
<?php $attributes = $__attributesOriginalce262628e3a8d44dc38fd1f3965181bc; ?>
<?php unset($__attributesOriginalce262628e3a8d44dc38fd1f3965181bc); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalce262628e3a8d44dc38fd1f3965181bc)): ?>
<?php $component = $__componentOriginalce262628e3a8d44dc38fd1f3965181bc; ?>
<?php unset($__componentOriginalce262628e3a8d44dc38fd1f3965181bc); ?>
<?php endif; ?>
            </div>
            <div class="flex-1 min-w-0">
                <p class="text-[11px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wide">TOTAL VOUCHER</p>
                <p class="text-lg font-bold flex items-center gap-1 mt-1">
                    <?php if (isset($component)) { $__componentOriginalce262628e3a8d44dc38fd1f3965181bc = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalce262628e3a8d44dc38fd1f3965181bc = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.icon','data' => ['name' => 'ticket','class' => 'w-4 h-4 text-slate-400']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'ticket','class' => 'w-4 h-4 text-slate-400']); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalce262628e3a8d44dc38fd1f3965181bc)): ?>
<?php $attributes = $__attributesOriginalce262628e3a8d44dc38fd1f3965181bc; ?>
<?php unset($__attributesOriginalce262628e3a8d44dc38fd1f3965181bc); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalce262628e3a8d44dc38fd1f3965181bc)): ?>
<?php $component = $__componentOriginalce262628e3a8d44dc38fd1f3965181bc; ?>
<?php unset($__componentOriginalce262628e3a8d44dc38fd1f3965181bc); ?>
<?php endif; ?> <?php echo e($mixData['total_voucher'] ?? 0); ?>

                </p>
            </div>
        </div>
        <div class="bg-slate-50 dark:bg-slate-800/50 rounded-xl p-4 flex items-center gap-3 border border-slate-100 dark:border-slate-700">
            <div class="p-2.5 bg-slate-200 dark:bg-slate-700 rounded-lg">
                <?php if (isset($component)) { $__componentOriginalce262628e3a8d44dc38fd1f3965181bc = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalce262628e3a8d44dc38fd1f3965181bc = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.icon','data' => ['name' => 'printer','class' => 'w-6 h-6 text-slate-600 dark:text-slate-300']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'printer','class' => 'w-6 h-6 text-slate-600 dark:text-slate-300']); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalce262628e3a8d44dc38fd1f3965181bc)): ?>
<?php $attributes = $__attributesOriginalce262628e3a8d44dc38fd1f3965181bc; ?>
<?php unset($__attributesOriginalce262628e3a8d44dc38fd1f3965181bc); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalce262628e3a8d44dc38fd1f3965181bc)): ?>
<?php $component = $__componentOriginalce262628e3a8d44dc38fd1f3965181bc; ?>
<?php unset($__componentOriginalce262628e3a8d44dc38fd1f3965181bc); ?>
<?php endif; ?>
            </div>
            <div class="flex-1 min-w-0">
                <p class="text-[11px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wide">Voucher Dibuat Hari Ini</p>
                <p class="text-lg font-bold flex items-center gap-1 mt-1">
                    <?php if (isset($component)) { $__componentOriginalce262628e3a8d44dc38fd1f3965181bc = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalce262628e3a8d44dc38fd1f3965181bc = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.icon','data' => ['name' => 'document-plus','class' => 'w-4 h-4 text-slate-400']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'document-plus','class' => 'w-4 h-4 text-slate-400']); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalce262628e3a8d44dc38fd1f3965181bc)): ?>
<?php $attributes = $__attributesOriginalce262628e3a8d44dc38fd1f3965181bc; ?>
<?php unset($__attributesOriginalce262628e3a8d44dc38fd1f3965181bc); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalce262628e3a8d44dc38fd1f3965181bc)): ?>
<?php $component = $__componentOriginalce262628e3a8d44dc38fd1f3965181bc; ?>
<?php unset($__componentOriginalce262628e3a8d44dc38fd1f3965181bc); ?>
<?php endif; ?> <?php echo e($mixData['vc_created_today'] ?? 0); ?>

                </p>
            </div>
        </div>
        <div class="bg-slate-50 dark:bg-slate-800/50 rounded-xl p-4 flex items-center gap-3 border border-slate-100 dark:border-slate-700">
            <div class="p-2.5 bg-slate-200 dark:bg-slate-700 rounded-lg">
                <?php if (isset($component)) { $__componentOriginalce262628e3a8d44dc38fd1f3965181bc = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalce262628e3a8d44dc38fd1f3965181bc = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.icon','data' => ['name' => 'arrow-right-on-rectangle','class' => 'w-6 h-6 text-slate-600 dark:text-slate-300']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'arrow-right-on-rectangle','class' => 'w-6 h-6 text-slate-600 dark:text-slate-300']); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalce262628e3a8d44dc38fd1f3965181bc)): ?>
<?php $attributes = $__attributesOriginalce262628e3a8d44dc38fd1f3965181bc; ?>
<?php unset($__attributesOriginalce262628e3a8d44dc38fd1f3965181bc); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalce262628e3a8d44dc38fd1f3965181bc)): ?>
<?php $component = $__componentOriginalce262628e3a8d44dc38fd1f3965181bc; ?>
<?php unset($__componentOriginalce262628e3a8d44dc38fd1f3965181bc); ?>
<?php endif; ?>
            </div>
            <div class="flex-1 min-w-0">
                <p class="text-[11px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wide">Voucher Aktif (Online)</p>
                <p class="text-lg font-bold flex items-center gap-1 mt-1">
                    <?php if (isset($component)) { $__componentOriginalce262628e3a8d44dc38fd1f3965181bc = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalce262628e3a8d44dc38fd1f3965181bc = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.icon','data' => ['name' => 'arrow-right-on-rectangle','class' => 'w-4 h-4 text-slate-400']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'arrow-right-on-rectangle','class' => 'w-4 h-4 text-slate-400']); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalce262628e3a8d44dc38fd1f3965181bc)): ?>
<?php $attributes = $__attributesOriginalce262628e3a8d44dc38fd1f3965181bc; ?>
<?php unset($__attributesOriginalce262628e3a8d44dc38fd1f3965181bc); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalce262628e3a8d44dc38fd1f3965181bc)): ?>
<?php $component = $__componentOriginalce262628e3a8d44dc38fd1f3965181bc; ?>
<?php unset($__componentOriginalce262628e3a8d44dc38fd1f3965181bc); ?>
<?php endif; ?> <?php echo e($mixData['vc_login_today'] ?? 0); ?>

                </p>
            </div>
        </div>
    </div>

    <!-- Tab Content -->
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($activeTab === 'ringkasan'): ?>
    <!-- Ringkasan: Recent Incomes -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <div class="bg-white dark:bg-slate-800 rounded-xl shadow-sm border border-slate-100 dark:border-slate-700">
            <div class="p-4 border-b border-slate-100 dark:border-slate-700 flex items-center justify-between">
                <h2 class="text-sm font-bold text-slate-800 dark:text-white">Pendapatan Terbaru</h2>
                <span class="text-[11px] px-2 py-1 rounded-full bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400">
                    Paid/Success
                </span>
            </div>
            <div class="max-h-[420px] overflow-y-auto">
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($recentIncomes && $recentIncomes->count() > 0): ?>
                    <table class="w-full text-sm">
                        <thead class="bg-slate-50 dark:bg-slate-700/40 sticky top-0">
                            <tr>
                                <th class="text-left px-4 py-2.5 text-[11px] font-bold text-slate-500 dark:text-slate-400 uppercase">Waktu</th>
                                <th class="text-left px-4 py-2.5 text-[11px] font-bold text-slate-500 dark:text-slate-400 uppercase">Pelanggan</th>
                                <th class="text-right px-4 py-2.5 text-[11px] font-bold text-slate-500 dark:text-slate-400 uppercase">Jumlah</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-slate-700/60">
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $recentIncomes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $p): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                                <tr class="hover:bg-slate-50 dark:bg-slate-900/50/70 dark:hover:bg-slate-700/30">
                                    <td class="px-4 py-3 text-xs text-slate-500 dark:text-slate-400 whitespace-nowrap">
                                        <?php echo e($p->paid_at?->format('d M, H:i') ?? $p->created_at?->format('d M, H:i')); ?>

                                    </td>
                                    <td class="px-4 py-3 min-w-0">
                                        <p class="text-sm font-semibold text-slate-800 dark:text-white truncate">
                                            <?php echo e($p->customer?->name ?? '-'); ?>

                                        </p>
                                        <p class="text-[11px] text-slate-500 dark:text-slate-400 truncate">
                                            #<?php echo e($p->customer?->customer_id ?? '-'); ?> · <?php echo e($p->customer?->service_type ?? 'hotspot'); ?>

                                        </p>
                                    </td>
                                    <td class="px-4 py-3 text-sm font-bold text-emerald-600 dark:text-emerald-400 whitespace-nowrap text-right">
                                        Rp <?php echo e(number_format($p->amount ?? 0, 0, ',', '.')); ?>

                                    </td>
                                </tr>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <div class="p-10 text-center">
                        <span class="material-symbols-outlined text-5xl text-slate-300 dark:text-slate-600 dark:text-slate-400">inbox</span>
                        <p class="mt-3 text-sm font-semibold text-slate-600 dark:text-slate-400">Belum ada pendapatan hari ini</p>
                    </div>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </div>
        </div>

        <div class="bg-white dark:bg-slate-800 rounded-xl shadow-sm border border-slate-100 dark:border-slate-700">
            <div class="p-4 border-b border-slate-100 dark:border-slate-700 flex items-center justify-between">
                <h2 class="text-sm font-bold text-slate-800 dark:text-white">Aktivitas Terbaru</h2>
                <span class="text-[11px] px-2 py-1 rounded-full bg-primary-100 text-primary-700 dark:bg-primary-900/30 dark:text-primary-400">
                    Live Feed
                </span>
            </div>
            <div class="max-h-[420px] overflow-y-auto p-4 space-y-3">
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($activities && count($activities) > 0): ?>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $activities; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $act): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                        <div class="flex gap-3">
                            <div class="mt-0.5 w-8 h-8 rounded-full bg-emerald-100 dark:bg-emerald-900/30 text-emerald-600 dark:text-emerald-400 flex items-center justify-center shrink-0">
                                <span class="material-symbols-outlined text-base">check_circle</span>
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="text-sm text-slate-700 dark:text-slate-300 leading-relaxed"><?php echo $act['message']; ?></p>
                                <p class="text-[11px] text-slate-400 dark:text-slate-500 dark:text-slate-400 mt-0.5"><?php echo e($act['time']); ?></p>
                            </div>
                        </div>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                <?php else: ?>
                    <div class="py-10 text-center">
                        <span class="material-symbols-outlined text-5xl text-slate-300 dark:text-slate-600 dark:text-slate-400">hourglass_empty</span>
                        <p class="mt-3 text-sm font-semibold text-slate-600 dark:text-slate-400">Belum ada aktivitas</p>
                    </div>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </div>
        </div>
    </div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($activeTab === 'tagihan'): ?>
    <!-- Tagihan: Recent Unpaid Invoices -->
    <div class="bg-white dark:bg-slate-800 rounded-xl shadow-sm border border-slate-100 dark:border-slate-700">
        <div class="p-4 border-b border-slate-100 dark:border-slate-700 flex items-center justify-between">
            <h2 class="text-sm font-bold text-slate-800 dark:text-white">Tagihan Belum Dibayar</h2>
            <span class="text-[11px] px-2 py-1 rounded-full bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-400 font-semibold">
                <?php echo e($recentInvoices?->count() ?? 0); ?> Unit
            </span>
        </div>
        <div class="overflow-x-auto">
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($recentInvoices && $recentInvoices->count() > 0): ?>
                <table class="w-full text-sm">
                    <thead class="bg-slate-50 dark:bg-slate-700/40">
                        <tr>
                            <th class="text-left px-4 py-2.5 text-[11px] font-bold text-slate-500 dark:text-slate-400 uppercase">Invoice</th>
                            <th class="text-left px-4 py-2.5 text-[11px] font-bold text-slate-500 dark:text-slate-400 uppercase">Pelanggan</th>
                            <th class="text-left px-4 py-2.5 text-[11px] font-bold text-slate-500 dark:text-slate-400 uppercase">Periode</th>
                            <th class="text-left px-4 py-2.5 text-[11px] font-bold text-slate-500 dark:text-slate-400 uppercase">Jatuh Tempo</th>
                            <th class="text-right px-4 py-2.5 text-[11px] font-bold text-slate-500 dark:text-slate-400 uppercase">Total</th>
                            <th class="text-center px-4 py-2.5 text-[11px] font-bold text-slate-500 dark:text-slate-400 uppercase">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-700/60">
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $recentInvoices; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $inv): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                            <?php
                                $isOverdue = $inv->due_date && $inv->due_date->isPast();
                            ?>
                            <tr class="hover:bg-slate-50 dark:bg-slate-900/50/70 dark:hover:bg-slate-700/30">
                                <td class="px-4 py-3 font-mono text-xs font-bold text-slate-700 dark:text-slate-200 whitespace-nowrap">
                                    <?php echo e($inv->invoice_number ?? '-'); ?>

                                </td>
                                <td class="px-4 py-3 min-w-0">
                                    <p class="text-sm font-semibold text-slate-800 dark:text-white truncate">
                                        <?php echo e($inv->customer?->name ?? '-'); ?>

                                    </p>
                                    <p class="text-[11px] text-slate-500 dark:text-slate-400 truncate">
                                        #<?php echo e($inv->customer?->customer_id ?? '-'); ?>

                                    </p>
                                </td>
                                <td class="px-4 py-3 text-xs text-slate-600 dark:text-slate-300 whitespace-nowrap">
                                    <?php echo e($inv->period_start?->format('M Y') ?? '-'); ?>

                                </td>
                                <td class="px-4 py-3 text-xs whitespace-nowrap">
                                    <span class="<?php echo e($isOverdue ? 'text-rose-600 dark:text-rose-400 font-semibold' : 'text-slate-600 dark:text-slate-300'); ?>">
                                        <?php echo e($inv->due_date?->format('d M Y') ?? '-'); ?>

                                    </span>
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($isOverdue): ?>
                                        <p class="text-[10px] font-bold text-rose-500 mt-0.5">TERLAMBAT</p>
                                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                </td>
                                <td class="px-4 py-3 text-sm font-bold text-slate-800 dark:text-white whitespace-nowrap text-right">
                                    Rp <?php echo e(number_format($inv->total_amount ?? 0, 0, ',', '.')); ?>

                                </td>
                                <td class="px-4 py-3 whitespace-nowrap text-center">
                                    <span class="inline-flex text-[11px] px-2 py-1 rounded-full font-bold
                                        <?php echo e($isOverdue ? 'bg-rose-100 text-rose-700 dark:bg-rose-900/30 dark:text-rose-400' : 'bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-400'); ?>">
                                        <?php echo e(ucfirst($inv->status ?? 'unpaid')); ?>

                                    </span>
                                </td>
                            </tr>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <div class="p-12 text-center">
                    <span class="material-symbols-outlined text-5xl text-slate-300 dark:text-slate-600 dark:text-slate-400">verified</span>
                    <p class="mt-3 text-sm font-semibold text-slate-600 dark:text-slate-400">Tidak ada tagihan tertunggak. Bagus!</p>
                </div>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </div>
    </div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($activeTab === 'aktivitas'): ?>
    <!-- Aktivitas: Full activity feed -->
    <div class="bg-white dark:bg-slate-800 rounded-xl shadow-sm border border-slate-100 dark:border-slate-700">
        <div class="p-4 border-b border-slate-100 dark:border-slate-700">
            <h2 class="text-sm font-bold text-slate-800 dark:text-white">Feed Aktivitas</h2>
        </div>
        <div class="p-6 max-h-[600px] overflow-y-auto space-y-4">
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($activities && count($activities) > 0): ?>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $activities; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $act): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                    <div class="flex gap-4 p-3 rounded-lg hover:bg-slate-50 dark:bg-slate-900/50 dark:hover:bg-slate-700/30 transition-colors">
                        <div class="w-10 h-10 rounded-full bg-emerald-100 dark:bg-emerald-900/30 text-emerald-600 dark:text-emerald-400 flex items-center justify-center shrink-0">
                            <span class="material-symbols-outlined text-xl">wifi</span>
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-sm text-slate-800 dark:text-slate-200 leading-relaxed"><?php echo $act['message']; ?></p>
                            <p class="text-xs text-slate-400 dark:text-slate-500 dark:text-slate-400 mt-1"><?php echo e($act['time']); ?></p>
                        </div>
                    </div>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
            <?php else: ?>
                <div class="py-12 text-center">
                    <span class="material-symbols-outlined text-5xl text-slate-300 dark:text-slate-600 dark:text-slate-400">activity</span>
                    <p class="mt-3 text-sm font-semibold text-slate-600 dark:text-slate-400">Belum ada aktivitas yang tercatat</p>
                </div>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </div>
    </div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

</div>
<?php /**PATH D:\dsBilling\resources\views/livewire/reseller-portal/dashboard.blade.php ENDPATH**/ ?>