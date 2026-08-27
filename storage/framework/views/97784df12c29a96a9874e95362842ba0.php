<div class="space-y-6">
    
    <div class="flex flex-col gap-4">
        <div class="flex flex-col md:flex-row items-start md:items-center justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold text-slate-900">Dashboard ISP</h1>
                <p class="mt-1 text-sm text-slate-500">Selamat datang! Berikut ringkasan bisnis dan aksi cepat untuk hari ini.</p>
            </div>
            <div class="flex items-center gap-3">
                <select 
                    wire:model="dateRange"
                    wire:change="setDateRange($event.target.value)"
                    class="px-4 py-2 bg-white border border-slate-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-primary-500"
                >
                    <option value="today">Hari Ini</option>
                    <option value="yesterday">Kemarin</option>
                    <option value="this_week">Minggu Ini</option>
                    <option value="last_week">Minggu Lalu</option>
                    <option value="this_month">Bulan Ini</option>
                    <option value="last_month">Bulan Lalu</option>
                </select>
                <button class="px-4 py-2 bg-primary-600 hover:bg-primary-700 text-white rounded-lg text-sm font-medium transition-colors flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                    </svg>
                    Export
                </button>
            </div>
        </div>

        
        <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
            <a href="<?php echo e(route('isp.pppoe-users.index')); ?>" class="group flex items-center gap-3 p-4 bg-gradient-to-r from-blue-500 to-blue-600 rounded-xl text-white transition-all hover:shadow-lg hover:-translate-y-1">
                <div class="p-2 bg-white/20 rounded-lg">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                    </svg>
                </div>
                <div>
                    <h4 class="font-semibold">PPPoE</h4>
                    <p class="text-xs opacity-80">User PPPoE</p>
                </div>
            </a>
            <a href="<?php echo e(route('isp.hotspot-users.index')); ?>" class="group flex items-center gap-3 p-4 bg-gradient-to-r from-orange-500 to-orange-600 rounded-xl text-white transition-all hover:shadow-lg hover:-translate-y-1">
                <div class="p-2 bg-white/20 rounded-lg">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.111 16.404a5.5 5.5 0 017.778 0M12 20h.01m-7.08-7.071c3.904-3.905 10.236-3.905 14.141 0M1.394 9.393c5.857-5.857 15.355-5.857 21.213 0" />
                    </svg>
                </div>
                <div>
                    <h4 class="font-semibold">Hotspot</h4>
                    <p class="text-xs opacity-80">User Hotspot</p>
                </div>
            </a>
            <a href="<?php echo e(route('isp.vouchers.index')); ?>" class="group flex items-center gap-3 p-4 bg-gradient-to-r from-green-500 to-green-600 rounded-xl text-white transition-all hover:shadow-lg hover:-translate-y-1">
                <div class="p-2 bg-white/20 rounded-lg">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z" />
                    </svg>
                </div>
                <div>
                    <h4 class="font-semibold">Voucher</h4>
                    <p class="text-xs opacity-80">Generate Voucher</p>
                </div>
            </a>
            <a href="<?php echo e(route('billing.invoices.index')); ?>" class="group flex items-center gap-3 p-4 bg-gradient-to-r from-red-500 to-red-600 rounded-xl text-white transition-all hover:shadow-lg hover:-translate-y-1">
                <div class="p-2 bg-white/20 rounded-lg">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 14l6-6M5 21h14M12 3v18" />
                    </svg>
                </div>
                <div>
                    <h4 class="font-semibold">Invoice</h4>
                    <p class="text-xs opacity-80">Tagihan Jatuh Tempo</p>
                </div>
            </a>
        </div>
    </div>

    
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="bg-gradient-to-br from-green-500 to-emerald-600 rounded-xl p-6 text-white shadow-lg">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-sm font-medium opacity-90">Pendapatan Bulan Ini</h3>
                <div class="p-2 bg-white/20 rounded-lg">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
            </div>
            <p class="text-3xl font-bold"><?php echo e($widgets['revenue']['value']); ?></p>
            <div class="flex items-center gap-1 mt-2 text-sm">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 10l7-7m0 0l7 7m-7-7v18"/>
                </svg>
                <span class="font-medium"><?php echo e(abs($widgets['revenue']['change'])); ?>%</span>
                <span class="opacity-75">vs bulan lalu</span>
            </div>
        </div>

        <div class="bg-gradient-to-br from-blue-500 to-indigo-600 rounded-xl p-6 text-white shadow-lg">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-sm font-medium opacity-90">User PPPoE Aktif</h3>
                <div class="p-2 bg-white/20 rounded-lg">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                    </svg>
                </div>
            </div>
            <p class="text-3xl font-bold"><?php echo e($widgets['active_pppoe']['value']); ?></p>
            <div class="flex items-center gap-1 mt-2 text-sm">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 10l7-7m0 0l7 7m-7-7v18"/>
                </svg>
                <span class="font-medium"><?php echo e(abs($widgets['active_pppoe']['change'])); ?>%</span>
                <span class="opacity-75">vs hari lalu</span>
            </div>
        </div>

        <div class="bg-gradient-to-br from-orange-500 to-amber-600 rounded-xl p-6 text-white shadow-lg">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-sm font-medium opacity-90">User Hotspot Aktif</h3>
                <div class="p-2 bg-white/20 rounded-lg">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.111 16.404a5.5 5.5 0 017.778 0M12 20h.01m-7.08-7.071c3.904-3.905 10.236-3.905 14.141 0M1.394 9.393c5.857-5.857 15.355-5.857 21.213 0" />
                    </svg>
                </div>
            </div>
            <p class="text-3xl font-bold"><?php echo e($widgets['hotspot']['value']); ?></p>
            <div class="flex items-center gap-1 mt-2 text-sm">
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($widgets['hotspot']['change'] >= 0): ?>
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 10l7-7m0 0l7 7m-7-7v18"/>
                    </svg>
                <?php else: ?>
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3"/>
                    </svg>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                <span class="font-medium"><?php echo e(abs($widgets['hotspot']['change'])); ?>%</span>
                <span class="opacity-75">vs hari lalu</span>
            </div>
        </div>

        <div class="bg-gradient-to-br from-red-500 to-rose-600 rounded-xl p-6 text-white shadow-lg">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-sm font-medium opacity-90">Tagihan Jatuh Tempo</h3>
                <div class="p-2 bg-white/20 rounded-lg">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
            </div>
            <p class="text-3xl font-bold"><?php echo e($widgets['outstanding']['value']); ?></p>
            <div class="flex items-center gap-1 mt-2 text-sm">
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($widgets['outstanding']['change'] < 0): ?>
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3"/>
                    </svg>
                <?php else: ?>
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 10l7-7m0 0l7 7m-7-7v18"/>
                    </svg>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                <span class="font-medium"><?php echo e(abs($widgets['outstanding']['change'])); ?>%</span>
                <span class="opacity-75">vs bulan lalu</span>
            </div>
        </div>
    </div>

    
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        
        <div class="lg:col-span-2 bg-white rounded-xl border border-slate-200 p-6 shadow-soft">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold text-slate-900">Pendapatan Bulanan</h3>
            </div>
            <div x-data="{
                chart: null,
                init() {
                    import('https://cdn.jsdelivr.net/npm/apexcharts').then(module => {
                        const ApexCharts = module.default;
                        this.chart = new ApexCharts(this.$refs.chart, {
                            series: <?php echo json_encode($charts['revenue']['series'], 15, 512) ?>,
                            chart: { type: 'area', height: 300, toolbar: { show: false } },
                            xaxis: { categories: <?php echo json_encode($charts['revenue']['categories'], 15, 512) ?> },
                            colors: ['#22c55e'],
                            fill: { type: 'gradient', gradient: { shadeIntensity: 1, opacityFrom: 0.4, opacityTo: 0.1 } },
                            dataLabels: { enabled: false },
                            stroke: { curve: 'smooth' },
                            tooltip: { theme: 'dark' }
                        });
                        this.chart.render();
                    });
                }
            }" x-ref="chart" class="h-80"></div>
        </div>

        
        <div class="space-y-6">
            
            <div class="bg-white rounded-xl border border-slate-200 shadow-soft">
                <div class="p-4 border-b border-slate-200">
                    <h3 class="font-semibold text-slate-900 flex items-center gap-2">
                        <svg class="w-5 h-5 text-warning-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                        </svg>
                        Notifikasi Penting
                    </h3>
                </div>
                <div class="divide-y divide-slate-200 max-h-80 overflow-y-auto">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $realtimeData['notifications']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $notification): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                        <div class="p-4 hover:bg-slate-50 transition-colors">
                            <div class="flex items-start gap-3">
                                <div class="mt-0.5 w-8 h-8 rounded-full flex items-center justify-center 
                                    <?php if($notification['type'] === 'success'): ?> bg-success-100 text-success-600
                                    <?php elseif($notification['type'] === 'warning'): ?> bg-warning-100 text-warning-600
                                    <?php else: ?> bg-info-100 text-info-600
                                    <?php endif; ?>">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($notification['type'] === 'success'): ?>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                        <?php elseif($notification['type'] === 'warning'): ?>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                        <?php else: ?>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                    </svg>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <p class="text-sm font-medium text-slate-900"><?php echo e($notification['title']); ?></p>
                                    <p class="text-xs text-slate-500 mt-0.5"><?php echo e($notification['message']); ?></p>
                                    <p class="text-xs text-slate-400 mt-1"><?php echo e($notification['time']); ?></p>
                                </div>
                            </div>
                        </div>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                </div>
            </div>

            
            <div class="bg-white rounded-xl border border-slate-200 shadow-soft">
                <div class="p-4 border-b border-slate-200">
                    <h3 class="font-semibold text-slate-900 flex items-center gap-2">
                        <svg class="w-5 h-5 text-danger-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                        </svg>
                        Alarm Jaringan
                    </h3>
                </div>
                <div class="divide-y divide-slate-200 max-h-80 overflow-y-auto">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $realtimeData['alarms']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $alarm): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                        <div class="p-4 hover:bg-slate-50 transition-colors">
                            <div class="flex items-start gap-3">
                                <div class="mt-1 w-2 h-2 rounded-full flex-shrink-0
                                    <?php if($alarm['severity'] === 'critical'): ?> bg-danger-500 animate-pulse
                                    <?php else: ?> bg-warning-500
                                    <?php endif; ?>"></div>
                                <div class="flex-1 min-w-0">
                                    <p class="text-sm font-medium text-slate-900"><?php echo e($alarm['title']); ?></p>
                                    <p class="text-xs text-slate-500 mt-0.5"><?php echo e($alarm['message']); ?></p>
                                    <p class="text-xs text-slate-400 mt-1"><?php echo e($alarm['time']); ?></p>
                                </div>
                            </div>
                        </div>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <div class="bg-white rounded-xl border border-slate-200 p-6 shadow-soft">
            <h3 class="text-lg font-semibold text-slate-900 mb-4">Traffic Jaringan</h3>
            <div x-data="{
                chart: null,
                init() {
                    import('https://cdn.jsdelivr.net/npm/apexcharts').then(module => {
                        const ApexCharts = module.default;
                        this.chart = new ApexCharts(this.$refs.chart, {
                            series: <?php echo json_encode($charts['traffic']['series'], 15, 512) ?>,
                            chart: { type: 'bar', height: 280, toolbar: { show: false } },
                            xaxis: { categories: <?php echo json_encode($charts['traffic']['categories'], 15, 512) ?> },
                            colors: ['#0ea5e9', '#22c55e'],
                            dataLabels: { enabled: false },
                            tooltip: { theme: 'dark' }
                        });
                        this.chart.render();
                    });
                }
            }" x-ref="chart" class="h-72"></div>
        </div>
        <div class="bg-white rounded-xl border border-slate-200 p-6 shadow-soft">
            <h3 class="text-lg font-semibold text-slate-900 mb-4">Pertumbuhan Pelanggan</h3>
            <div x-data="{
                chart: null,
                init() {
                    import('https://cdn.jsdelivr.net/npm/apexcharts').then(module => {
                        const ApexCharts = module.default;
                        this.chart = new ApexCharts(this.$refs.chart, {
                            series: <?php echo json_encode($charts['customer_growth']['series'], 15, 512) ?>,
                            chart: { type: 'area', height: 280, toolbar: { show: false } },
                            xaxis: { categories: <?php echo json_encode($charts['customer_growth']['categories'], 15, 512) ?> },
                            colors: ['#0ea5e9', '#22c55e'],
                            fill: { type: 'gradient', gradient: { shadeIntensity: 1, opacityFrom: 0.4, opacityTo: 0.1 } },
                            dataLabels: { enabled: false },
                            stroke: { curve: 'smooth' },
                            tooltip: { theme: 'dark' }
                        });
                        this.chart.render();
                    });
                }
            }" x-ref="chart" class="h-72"></div>
        </div>
    </div>
</div>
<?php /**PATH C:\Users\Lenovo\OneDrive\dsBilling\resources\views\livewire\dashboard\index.blade.php ENDPATH**/ ?>