<div class="space-y-6">
    
    <div class="flex flex-col md:flex-row items-start md:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-900">Enterprise Dashboard</h1>
            <p class="mt-1 text-sm text-slate-500">Selamat datang! Berikut ringkasan bisnis Anda hari ini.</p>
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

    
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $widgets; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $widget): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
            <?php if (isset($component)) { $__componentOriginal9a4f840f2226578be8cfe14fe6732efc = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal9a4f840f2226578be8cfe14fe6732efc = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.display.kpi-card','data' => ['title' => $widget['title'],'value' => $widget['value'],'change' => $widget['change'],'chartData' => $widget['chartData'],'chartColor' => $widget['chartColor'],'icon' => $widget['icon']]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('display.kpi-card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($widget['title']),'value' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($widget['value']),'change' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($widget['change']),'chartData' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($widget['chartData']),'chartColor' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($widget['chartColor']),'icon' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($widget['icon'])]); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal9a4f840f2226578be8cfe14fe6732efc)): ?>
<?php $attributes = $__attributesOriginal9a4f840f2226578be8cfe14fe6732efc; ?>
<?php unset($__attributesOriginal9a4f840f2226578be8cfe14fe6732efc); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal9a4f840f2226578be8cfe14fe6732efc)): ?>
<?php $component = $__componentOriginal9a4f840f2226578be8cfe14fe6732efc; ?>
<?php unset($__componentOriginal9a4f840f2226578be8cfe14fe6732efc); ?>
<?php endif; ?>
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
    </div>

    
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        
        <div class="bg-white rounded-xl border border-slate-200 p-6 shadow-soft">
            <h3 class="text-lg font-semibold text-slate-900 mb-4"><?php echo e($charts['revenue']['title']); ?></h3>
            <div x-data="{
                chart: null,
                init() {
                    import('https://cdn.jsdelivr.net/npm/apexcharts').then(module => {
                        const ApexCharts = module.default;
                        this.chart = new ApexCharts(this.$refs.chart, {
                            series: <?php echo json_encode($charts['revenue']['series'], 15, 512) ?>,
                            chart: { type: 'area', height: 300, toolbar: { show: true } },
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

        
        <div class="bg-white rounded-xl border border-slate-200 p-6 shadow-soft">
            <h3 class="text-lg font-semibold text-slate-900 mb-4"><?php echo e($charts['growth']['title']); ?></h3>
            <div x-data="{
                chart: null,
                init() {
                    import('https://cdn.jsdelivr.net/npm/apexcharts').then(module => {
                        const ApexCharts = module.default;
                        this.chart = new ApexCharts(this.$refs.chart, {
                            series: <?php echo json_encode($charts['growth']['series'], 15, 512) ?>,
                            chart: { type: 'line', height: 300, toolbar: { show: true } },
                            xaxis: { categories: <?php echo json_encode($charts['growth']['categories'], 15, 512) ?> },
                            colors: ['#0ea5e9', '#22c55e'],
                            dataLabels: { enabled: false },
                            stroke: { curve: 'smooth' },
                            tooltip: { theme: 'dark' }
                        });
                        this.chart.render();
                    });
                }
            }" x-ref="chart" class="h-80"></div>
        </div>

        
        <div class="bg-white rounded-xl border border-slate-200 p-6 shadow-soft">
            <h3 class="text-lg font-semibold text-slate-900 mb-4"><?php echo e($charts['traffic']['title']); ?></h3>
            <div x-data="{
                chart: null,
                init() {
                    import('https://cdn.jsdelivr.net/npm/apexcharts').then(module => {
                        const ApexCharts = module.default;
                        this.chart = new ApexCharts(this.$refs.chart, {
                            series: <?php echo json_encode($charts['traffic']['series'], 15, 512) ?>,
                            chart: { type: 'bar', height: 300, toolbar: { show: true } },
                            xaxis: { categories: <?php echo json_encode($charts['traffic']['categories'], 15, 512) ?> },
                            colors: ['#0ea5e9', '#22c55e'],
                            dataLabels: { enabled: false },
                            tooltip: { theme: 'dark' }
                        });
                        this.chart.render();
                    });
                }
            }" x-ref="chart" class="h-80"></div>
        </div>

        
        <div class="bg-white rounded-xl border border-slate-200 p-6 shadow-soft">
            <h3 class="text-lg font-semibold text-slate-900 mb-4"><?php echo e($charts['alarm_trend']['title']); ?></h3>
            <div x-data="{
                chart: null,
                init() {
                    import('https://cdn.jsdelivr.net/npm/apexcharts').then(module => {
                        const ApexCharts = module.default;
                        this.chart = new ApexCharts(this.$refs.chart, {
                            series: <?php echo json_encode($charts['alarm_trend']['series'], 15, 512) ?>,
                            chart: { type: 'line', height: 300, toolbar: { show: true } },
                            xaxis: { categories: <?php echo json_encode($charts['alarm_trend']['categories'], 15, 512) ?> },
                            colors: ['#ef4444', '#f59e0b', '#3b82f6'],
                            dataLabels: { enabled: false },
                            stroke: { curve: 'smooth' },
                            tooltip: { theme: 'dark' }
                        });
                        this.chart.render();
                    });
                }
            }" x-ref="chart" class="h-80"></div>
        </div>

        
        <div class="bg-white rounded-xl border border-slate-200 p-6 shadow-soft">
            <h3 class="text-lg font-semibold text-slate-900 mb-4"><?php echo e($charts['customer_growth']['title']); ?></h3>
            <div x-data="{
                chart: null,
                init() {
                    import('https://cdn.jsdelivr.net/npm/apexcharts').then(module => {
                        const ApexCharts = module.default;
                        this.chart = new ApexCharts(this.$refs.chart, {
                            series: <?php echo json_encode($charts['customer_growth']['series'], 15, 512) ?>,
                            chart: { type: 'area', height: 300, toolbar: { show: true } },
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
            }" x-ref="chart" class="h-80"></div>
        </div>

        
        <div class="bg-white rounded-xl border border-slate-200 p-6 shadow-soft">
            <h3 class="text-lg font-semibold text-slate-900 mb-4"><?php echo e($charts['cash_flow']['title']); ?></h3>
            <div x-data="{
                chart: null,
                init() {
                    import('https://cdn.jsdelivr.net/npm/apexcharts').then(module => {
                        const ApexCharts = module.default;
                        this.chart = new ApexCharts(this.$refs.chart, {
                            series: <?php echo json_encode($charts['cash_flow']['series'], 15, 512) ?>,
                            chart: { type: 'bar', height: 300, toolbar: { show: true } },
                            xaxis: { categories: <?php echo json_encode($charts['cash_flow']['categories'], 15, 512) ?> },
                            colors: ['#22c55e', '#ef4444'],
                            dataLabels: { enabled: false },
                            tooltip: { theme: 'dark' }
                        });
                        this.chart.render();
                    });
                }
            }" x-ref="chart" class="h-80"></div>
        </div>
    </div>

    
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        
        <div class="bg-white rounded-xl border border-slate-200 shadow-soft">
            <div class="p-6 border-b border-slate-200">
                <h3 class="text-lg font-semibold text-slate-900">Notifications</h3>
            </div>
            <div class="divide-y divide-slate-200 max-h-96 overflow-y-auto">
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $realtimeData['notifications']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $notification): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                    <div class="p-4">
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
            <div class="p-6 border-b border-slate-200">
                <h3 class="text-lg font-semibold text-slate-900">Alarms</h3>
            </div>
            <div class="divide-y divide-slate-200 max-h-96 overflow-y-auto">
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $realtimeData['alarms']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $alarm): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                    <div class="p-4">
                        <div class="flex items-start gap-3">
                            <div class="mt-0.5 w-2 h-2 rounded-full 
                                <?php if($alarm['severity'] === 'critical'): ?> bg-danger-500
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

        
        <div class="bg-white rounded-xl border border-slate-200 shadow-soft">
            <div class="p-6 border-b border-slate-200">
                <h3 class="text-lg font-semibold text-slate-900">Activity</h3>
            </div>
            <div class="divide-y divide-slate-200 max-h-96 overflow-y-auto">
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $realtimeData['activities']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $activity): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                    <div class="p-4">
                        <div class="flex items-start gap-3">
                            <div class="w-8 h-8 rounded-full bg-slate-100 flex items-center justify-center flex-shrink-0">
                                <span class="text-sm font-medium text-slate-600"><?php echo e(substr($activity['user'], 0, 1)); ?></span>
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="text-sm text-slate-900">
                                    <span class="font-medium"><?php echo e($activity['user']); ?></span> <?php echo e($activity['action']); ?>

                                </p>
                                <p class="text-xs text-slate-500 mt-0.5">
                                    <span class="px-2 py-0.5 bg-slate-100 rounded-full font-medium text-xs"><?php echo e($activity['module']); ?></span>
                                    <?php echo e($activity['time']); ?>

                                </p>
                            </div>
                        </div>
                    </div>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
            </div>
        </div>
    </div>
</div>
<?php /**PATH C:\Users\Lenovo\OneDrive\dsBilling\resources\views/livewire/dashboard/index.blade.php ENDPATH**/ ?>