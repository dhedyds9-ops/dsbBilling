<?php
    $customer = \App\Models\CRM\Customer::where('user_id', auth()->id())->first();
    $companyName = \App\Models\Setting::getValue('company.name', config('app.name', 'dsBilling'));
    $planName = $service_profile?->name ?? null;
    $dlSpeed = $service_profile?->download_speed ?? null;
    $ulSpeed = $service_profile?->upload_speed ?? null;
    $speedLabel = $dlSpeed ? (($dlSpeed >= 1000 ? round($dlSpeed/1000, 0).'Gbps' : $dlSpeed.'Mbps') . '↓') : null;
?>

<div class="max-w-md mx-auto sm:max-w-full p-4 sm:p-6 bg-slate-50 dark:bg-slate-900 min-h-[calc(100vh-4rem)] pb-24">
    <!-- MOBILE WRAPPER (Cards & spacing optimized for mobile, scales up for desktop) -->
    <div class="max-w-md mx-auto sm:max-w-4xl space-y-4">
        
        <!-- Promotional Banner -->
        <div class="rounded-3xl overflow-hidden relative shadow-lg 
            bg-gradient-to-br from-teal-500 via-teal-600 to-emerald-700
            dark:from-teal-700 dark:via-slate-800 dark:to-emerald-900
            dark:border dark:border-teal-700/50">
            <!-- Background Decorations -->
            <div class="absolute top-0 right-0 w-64 h-64 bg-white/10 dark:bg-white dark:bg-slate-800/5 rounded-full blur-3xl -translate-y-1/2 translate-x-1/3"></div>
            <div class="absolute bottom-0 left-0 w-40 h-40 bg-emerald-300/20 dark:bg-emerald-900/40 rounded-full blur-2xl translate-y-1/3 -translate-x-1/3"></div>
            
            <!-- Content -->
            <div class="relative z-10 p-5 sm:p-6 flex items-center justify-between">
                <div class="flex-1 pr-2">
                    <!-- Status Badge -->
                    <div class="inline-flex items-center gap-1.5 px-2.5 py-1 
                        bg-white/20 dark:bg-white dark:bg-slate-800/10 
                        text-white text-[10px] font-bold tracking-wider rounded-lg mb-3 
                        backdrop-blur-sm border border-white/20 dark:border-white/10 uppercase shadow-sm">
                        <span class="w-1.5 h-1.5 rounded-full <?php echo e($internet_status['status'] === 'online' ? 'bg-emerald-300 animate-pulse' : 'bg-red-300'); ?>"></span>
                        <?php echo e($internet_status['status'] === 'online' ? 'Aktif' : 'Tidak Aktif'); ?>

                    </div>

                    <!-- Plan Name -->
                    <h2 class="text-white dark:text-slate-100 font-extrabold text-xl sm:text-2xl leading-tight tracking-tight mb-1.5">
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($planName): ?>
                            <?php echo e($planName); ?>

                        <?php else: ?>
                            Layanan Internet <span class="text-emerald-200 dark:text-teal-300">Anda</span>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </h2>

                    <!-- Speed / Status -->
                    <p class="text-teal-100 dark:text-slate-300 text-[11px] sm:text-xs leading-relaxed opacity-90 mt-1">
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($speedLabel): ?>
                            Kecepatan: <strong class="text-white dark:text-emerald-300"><?php echo e($speedLabel); ?></strong>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($ulSpeed): ?> / <strong class="text-white dark:text-emerald-300"><?php echo e($ulSpeed); ?>Mbps↑</strong> <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        <?php else: ?>
                            <?php echo e($internet_status['message']); ?>

                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </p>

                    <!-- Username badge -->
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($pppoe_user || $hotspot_user): ?>
                    <div class="mt-2 inline-flex items-center gap-1 text-[10px] font-mono 
                        text-teal-100 dark:text-slate-400">
                        <span class="material-symbols-outlined text-[12px]">person</span>
                        <?php echo e($pppoe_user?->username ?? $hotspot_user?->username); ?>

                    </div>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>
                
                <!-- Icon -->
                <div class="flex-shrink-0 relative pl-2">
                    <div class="absolute inset-0 bg-emerald-400/20 dark:bg-teal-500/10 rounded-full blur-xl animate-pulse"></div>
                    <div class="relative w-16 h-16 
                        bg-gradient-to-tr from-white/10 to-white/20 dark:from-white/5 dark:to-white/10 
                        rounded-2xl flex items-center justify-center 
                        backdrop-blur-md border border-white/30 dark:border-white/10 
                        shadow-[0_8px_32px_rgba(0,0,0,0.15)] rotate-6 transform hover:rotate-12 transition-transform duration-300">
                        <span class="material-symbols-outlined text-white dark:text-emerald-300 drop-shadow-md" translate="no" style="font-size: 32px;">
                            <?php echo e($pppoe_user ? 'router' : ($hotspot_user ? 'wifi' : 'signal_wifi_off')); ?>

                        </span>
                    </div>
                </div>
            </div>
            
            <!-- Bottom Accent Line -->
            <div class="absolute bottom-0 left-0 right-0 h-1 
                bg-gradient-to-r from-emerald-400 via-teal-300 to-cyan-400
                dark:from-teal-600 dark:via-emerald-700 dark:to-cyan-800 opacity-80"></div>
        </div>

        <!-- Customer Info Card -->
        <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-sm border border-slate-100 dark:border-slate-700/60 p-5">
            <div class="flex justify-between items-start mb-4">
                <div>
                    <p class="text-xs text-slate-500 dark:text-slate-400">Nama Pelanggan</p>
                    <p class="font-bold text-lg text-slate-900 dark:text-slate-100"><?php echo e($customer->name ?? auth()->user()->name); ?></p>
                </div>
                <div class="text-right">
                    <p class="text-xs text-slate-500 dark:text-slate-400 font-mono">ID: <?php echo e($customer->code ?? 'N/A'); ?></p>
                    <div class="inline-flex items-center gap-1.5 mt-1 <?php echo e($internet_status['status'] === 'online' ? 'text-emerald-600' : 'text-red-600'); ?>">
                        <span class="w-1.5 h-1.5 rounded-full <?php echo e($internet_status['status'] === 'online' ? 'bg-emerald-500 animate-pulse' : 'bg-red-500'); ?>"></span>
                        <span class="text-xs font-semibold"><?php echo e($internet_status['status'] === 'online' ? 'Internet Online' : 'Internet Offline'); ?></span>
                    </div>
                </div>
            </div>

            <div class="border-t border-slate-100 dark:border-slate-700/60 pt-4 mb-4">
                <div class="flex justify-between items-end">
                    <div>
                        <p class="text-xs text-slate-500 dark:text-slate-400"><?php echo e($total_outstanding > 0 ? 'Total belum bayar' : 'Total tagihan bulan ini'); ?></p>
                        <p class="font-bold text-2xl <?php echo e($total_outstanding > 0 ? 'text-red-600 dark:text-red-500' : 'text-slate-900 dark:text-slate-100'); ?>">
                            Rp <?php echo e(number_format($total_outstanding, 0, ',', '.')); ?>

                        </p>
                    </div>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($total_outstanding > 0): ?>
                        <div class="px-3 py-1 bg-red-100 dark:bg-red-900/30 text-red-600 dark:text-red-400 text-xs font-bold rounded-lg border border-red-200 dark:border-red-800">
                            BELUM LUNAS
                        </div>
                    <?php else: ?>
                        <div class="px-3 py-1 bg-emerald-100 dark:bg-emerald-900/30 text-emerald-600 dark:text-emerald-400 text-xs font-bold rounded-lg border border-emerald-200 dark:border-emerald-800">
                            LUNAS
                        </div>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>
            </div>

            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(isset($next_billing_date) && $next_billing_date): ?>
            <div class="flex justify-between items-center text-[11px] text-slate-500 dark:text-slate-400 bg-slate-50 dark:bg-slate-900/50 p-2.5 rounded-lg">
                <div class="flex items-center gap-1">
                    <span class="material-symbols-outlined text-[14px]">calendar_today</span>
                    <span>Tagihan berikutnya: <?php echo e($next_billing_date->format('d M Y')); ?></span>
                </div>
                <div class="flex items-center gap-1">
                    <span class="material-symbols-outlined text-[14px]">schedule</span>
                    <span>Jatuh tempo: +<?php echo e(\App\Models\Setting::getValue('billing.invoice_due_days', 7)); ?> hari</span>
                </div>
            </div>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </div>

        <!-- Grid Menu -->
        <div class="grid grid-cols-3 gap-3 sm:gap-4 mt-2">
            <!-- 1. Tagihan -->
            <a href="<?php echo e(route('customer-portal.billing.invoice-list')); ?>" class="bg-white dark:bg-slate-800 p-4 rounded-2xl shadow-sm border border-slate-100 dark:border-slate-700/60 flex flex-col items-center justify-center text-center gap-2 hover:bg-slate-50 dark:bg-slate-900/50 transition-colors">
                <div class="w-12 h-12 rounded-full bg-blue-100 dark:bg-blue-900/30 text-blue-600 dark:text-blue-400 flex items-center justify-center">
                    <span class="material-symbols-outlined text-[24px]">receipt_long</span>
                </div>
                <span class="text-[11px] font-semibold text-slate-700 dark:text-slate-300">Tagihan</span>
            </a>

            <!-- 2. Tiket Aduan -->
            <a href="<?php echo e(route('customer-portal.support.ticket-list')); ?>" class="bg-white dark:bg-slate-800 p-4 rounded-2xl shadow-sm border border-slate-100 dark:border-slate-700/60 flex flex-col items-center justify-center text-center gap-2 hover:bg-slate-50 dark:bg-slate-900/50 transition-colors">
                <div class="w-12 h-12 rounded-full bg-emerald-100 dark:bg-emerald-900/30 text-emerald-600 dark:text-emerald-400 flex items-center justify-center">
                    <span class="material-symbols-outlined text-[24px]">support_agent</span>
                </div>
                <span class="text-[11px] font-semibold text-slate-700 dark:text-slate-300">Tiket Aduan</span>
            </a>

            <!-- 3. Paket Internet -->
            <a href="<?php echo e(route('customer-portal.self-service.change-plan')); ?>" class="bg-white dark:bg-slate-800 p-4 rounded-2xl shadow-sm border border-slate-100 dark:border-slate-700/60 flex flex-col items-center justify-center text-center gap-2 hover:bg-slate-50 dark:bg-slate-900/50 transition-colors">
                <div class="w-12 h-12 rounded-full bg-teal-100 dark:bg-teal-900/30 text-teal-600 dark:text-teal-400 flex items-center justify-center">
                    <span class="material-symbols-outlined text-[24px]">wifi</span>
                </div>
                <span class="text-[11px] font-semibold text-slate-700 dark:text-slate-300">Paket Internet</span>
            </a>

            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(isset($pppoe_user) && $pppoe_user): ?>
            <!-- 4. Pengaturan WiFi -->
            <a href="<?php echo e(route('customer-portal.self-service.change-onu-wifi-password')); ?>" class="bg-white dark:bg-slate-800 p-4 rounded-2xl shadow-sm border border-slate-100 dark:border-slate-700/60 flex flex-col items-center justify-center text-center gap-2 hover:bg-slate-50 dark:bg-slate-900/50 transition-colors">
                <div class="w-12 h-12 rounded-full bg-orange-100 dark:bg-orange-900/30 text-orange-600 dark:text-orange-400 flex items-center justify-center">
                    <span class="material-symbols-outlined text-[24px]">router</span>
                </div>
                <span class="text-[11px] font-semibold text-slate-700 dark:text-slate-300">Setting WiFi</span>
            </a>
            <?php elseif(isset($hotspot_user) && $hotspot_user): ?>
            <!-- 4. Ganti Password Hotspot -->
            <a href="<?php echo e(route('customer-portal.self-service.change-hotspot-credentials')); ?>" class="bg-white dark:bg-slate-800 p-4 rounded-2xl shadow-sm border border-slate-100 dark:border-slate-700/60 flex flex-col items-center justify-center text-center gap-2 hover:bg-slate-50 dark:bg-slate-900/50 transition-colors">
                <div class="w-12 h-12 rounded-full bg-orange-100 dark:bg-orange-900/30 text-orange-600 dark:text-orange-400 flex items-center justify-center">
                    <span class="material-symbols-outlined text-[24px]">password</span>
                </div>
                <span class="text-[11px] font-semibold text-slate-700 dark:text-slate-300">Pass Hotspot</span>
            </a>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

            <!-- 5. Perangkat Aktif -->
            <a href="<?php echo e(route('customer-portal.self-service.active-sessions')); ?>" class="bg-white dark:bg-slate-800 p-4 rounded-2xl shadow-sm border border-slate-100 dark:border-slate-700/60 flex flex-col items-center justify-center text-center gap-2 hover:bg-slate-50 dark:bg-slate-900/50 transition-colors">
                <div class="w-12 h-12 rounded-full bg-purple-100 dark:bg-purple-900/30 text-purple-600 dark:text-purple-400 flex items-center justify-center">
                    <span class="material-symbols-outlined text-[24px]">devices</span>
                </div>
                <span class="text-[11px] font-semibold text-slate-700 dark:text-slate-300">Perangkat</span>
            </a>

                        <!-- 7. Speedtest -->
            <a href="<?php echo e(route('customer-portal.self-service.speed-test')); ?>" class="bg-white dark:bg-slate-800 p-4 rounded-2xl shadow-sm border border-slate-100 dark:border-slate-700/60 flex flex-col items-center justify-center text-center gap-2 hover:bg-slate-50 dark:bg-slate-900/50 transition-colors">
                <div class="w-12 h-12 rounded-full bg-pink-100 dark:bg-pink-900/30 text-pink-600 dark:text-pink-400 flex items-center justify-center">
                    <span class="material-symbols-outlined text-[24px]">speed</span>
                </div>
                <span class="text-[11px] font-semibold text-slate-700 dark:text-slate-300">Speedtest</span>
            </a>
            <!-- 6. Histori Koneksi -->
            <a href="<?php echo e(route('customer-portal.self-service.connection-info')); ?>" class="bg-white dark:bg-slate-800 p-4 rounded-2xl shadow-sm border border-slate-100 dark:border-slate-700/60 flex flex-col items-center justify-center text-center gap-2 hover:bg-slate-50 dark:bg-slate-900/50 transition-colors">
                <div class="w-12 h-12 rounded-full bg-sky-100 dark:bg-sky-900/30 text-sky-600 dark:text-sky-400 flex items-center justify-center">
                    <span class="material-symbols-outlined text-[24px]">history</span>
                </div>
                <span class="text-[11px] font-semibold text-slate-700 dark:text-slate-300">Riwayat</span>
            </a>
        </div>
        <!-- Pusat Bantuan (Troubleshooting) -->
        <a href="<?php echo e(route('customer-portal.support.contact-admin')); ?>" class="mt-4 bg-gradient-to-r from-teal-500 to-emerald-500 hover:from-teal-600 hover:to-emerald-600 p-4 rounded-2xl shadow-sm text-white flex items-center justify-between transition-all group">
            <div class="flex items-center gap-4">
                <div class="w-10 h-10 rounded-full bg-white dark:bg-slate-800/20 flex items-center justify-center backdrop-blur-sm">
                    <span class="material-symbols-outlined text-[24px]">support_agent</span>
                </div>
                <div>
                    <h3 class="font-bold text-sm sm:text-base leading-tight">Pusat Bantuan & Panduan</h3>
                    <p class="text-[10px] sm:text-xs text-teal-50 opacity-90 mt-0.5">Penanganan gangguan mandiri & Chat Admin</p>
                </div>
            </div>
            <span class="material-symbols-outlined transform group-hover:translate-x-1 transition-transform">chevron_right</span>
        </a>

        <!-- Tagihan Terbaru Card -->
        <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-sm border border-slate-100 dark:border-slate-700/60 p-5 mt-4">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-sm font-bold text-slate-900 dark:text-slate-100">Tagihan Terbaru</h2>
                <a href="<?php echo e(route('customer-portal.billing.invoice-list')); ?>" class="text-[11px] font-semibold text-indigo-600 dark:text-indigo-400 hover:underline">Lihat Semua</a>
            </div>
            
            <div class="space-y-3">
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $recent_invoices->take(3); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $invoice): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                <div class="flex items-center justify-between p-3 rounded-xl border border-slate-100 dark:border-slate-700/60 hover:bg-slate-50 dark:bg-slate-900/50 dark:hover:bg-slate-900/50 transition-colors">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-full <?php echo e($invoice->status === 'paid' ? 'bg-emerald-100 text-emerald-600 dark:bg-emerald-900/30 dark:text-emerald-400' : ($invoice->status === 'overdue' ? 'bg-red-100 text-red-600 dark:bg-red-900/30 dark:text-red-400' : 'bg-amber-100 text-amber-600 dark:bg-amber-900/30 dark:text-amber-400')); ?> flex items-center justify-center shrink-0">
                            <span class="material-symbols-outlined text-[20px]"><?php echo e($invoice->status === 'paid' ? 'check_circle' : 'receipt_long'); ?></span>
                        </div>
                        <div>
                            <div class="font-bold text-xs text-slate-900 dark:text-slate-100">Rp <?php echo e(number_format($invoice->total_amount, 0, ',', '.')); ?></div>
                            <div class="text-[10px] text-slate-500 dark:text-slate-400 font-mono mt-0.5"><?php echo e($invoice->invoice_number); ?> &bull; <?php echo e($invoice->due_date?->format('d M Y')); ?></div>
                        </div>
                    </div>
                    <div>
                        <span class="px-2.5 py-1 rounded-full text-[10px] font-bold <?php echo e($invoice->status === 'paid' ? 'bg-emerald-50 text-emerald-600 border border-emerald-200 dark:bg-emerald-900/30 dark:border-emerald-800' : ($invoice->status === 'overdue' ? 'bg-red-50 text-red-600 border border-red-200 dark:bg-red-900/30 dark:border-red-800' : 'bg-amber-50 text-amber-600 border border-amber-200 dark:bg-amber-900/30 dark:border-amber-800')); ?>">
                            <?php echo e($invoice->status === 'paid' ? 'Lunas' : ($invoice->status === 'overdue' ? 'Jatuh Tempo' : 'Belum Lunas')); ?>

                        </span>
                    </div>
                </div>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                <div class="text-center py-4 bg-slate-50 dark:bg-slate-900/50 rounded-xl">
                    <span class="material-symbols-outlined text-slate-400 mb-1" style="font-size: 24px;">task</span>
                    <p class="text-[11px] text-slate-500 dark:text-slate-400">Belum ada tagihan.</p>
                </div>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </div>
        </div>

        <!-- Layanan Aktif Card -->
        <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-sm border border-slate-100 dark:border-slate-700/60 p-5 mt-4">
            <h2 class="text-sm font-bold text-slate-900 dark:text-slate-100 mb-3">Layanan Aktif Saya</h2>
            <div class="space-y-3">
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $customer_services; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $service): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                <?php
                    $svcProfile = $service->serviceProfile;
                    $svcPPPoE = $service->pppoeUser;
                    $svcHotspot = $service->hotspotUser;
                    $svcName = $svcProfile?->name ?? $service->service?->name ?? 'Layanan Internet';
                    $svcUsername = $svcPPPoE?->username ?? $svcHotspot?->username ?? $service->username ?? '-';
                    $svcDlSpeed = $svcProfile?->download_speed;
                    $svcUlSpeed = $svcProfile?->upload_speed;
                    $svcType = $svcProfile?->service_type ?? ($svcPPPoE ? 'PPPoE' : ($svcHotspot ? 'Hotspot' : '-'));
                ?>
                <div class="flex items-center justify-between p-3 rounded-xl bg-slate-50 dark:bg-slate-900/50">
                    <div class="flex items-center gap-3">
                        <div class="w-9 h-9 rounded-full <?php echo e($service->status === 'active' ? 'bg-teal-100 dark:bg-teal-900/30 text-teal-600 dark:text-teal-400' : 'bg-slate-200 dark:bg-slate-700 text-slate-500 dark:text-slate-400'); ?> flex items-center justify-center shrink-0">
                            <span class="material-symbols-outlined text-[18px]"><?php echo e($svcPPPoE ? 'router' : ($svcHotspot ? 'wifi' : 'cell_tower')); ?></span>
                        </div>
                        <div>
                            <div class="font-semibold text-xs text-slate-900 dark:text-slate-100"><?php echo e($svcName); ?></div>
                            <div class="text-[10px] text-slate-500 dark:text-slate-400 mt-0.5">
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($svcUsername !== '-'): ?>
                                    <span class="font-mono"><?php echo e($svcUsername); ?></span>
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($svcDlSpeed): ?> &bull; <?php echo e($svcDlSpeed); ?>Mbps ↓ <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($svcUlSpeed): ?> / <?php echo e($svcUlSpeed); ?>Mbps ↑ <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                <?php else: ?>
                                    <?php echo e($svcType); ?> &bull; ID: <?php echo e(Str::limit($service->uuid ?? '-', 12)); ?>

                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            </div>
                        </div>
                    </div>
                    <span class="px-2 py-0.5 rounded-full text-[10px] font-semibold <?php echo e($service->status === 'active' ? 'bg-emerald-100 dark:bg-emerald-900/30 text-emerald-700 dark:text-emerald-400' : 'bg-slate-200 dark:bg-slate-700 text-slate-600 dark:text-slate-400'); ?>">
                        <?php echo e($service->status === 'active' ? 'Aktif' : ucfirst($service->status)); ?>

                    </span>
                </div>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                <div class="text-center py-4">
                    <span class="material-symbols-outlined text-slate-300 dark:text-slate-600 dark:text-slate-400 block mb-1" style="font-size:28px">signal_disconnected</span>
                    <span class="text-xs text-slate-500 dark:text-slate-400">Belum ada layanan yang aktif.</span>
                </div>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </div>
        </div>
    </div>
</div><?php /**PATH D:\dsBilling\resources\views/livewire/customer-portal/dashboard.blade.php ENDPATH**/ ?>