<div class="space-y-6">
    {{-- Page Header --}}
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

    {{-- Widgets Section --}}
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
        @foreach ($widgets as $key => $widget)
            <x-display.kpi-card
                :title="$widget['title']"
                :value="$widget['value']"
                :change="$widget['change']"
                :chartData="$widget['chartData']"
                :chartColor="$widget['chartColor']"
                :icon="$widget['icon']"
            />
        @endforeach
    </div>

    {{-- Charts Section --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        {{-- Revenue Chart --}}
        <div class="bg-white rounded-xl border border-slate-200 p-6 shadow-soft">
            <h3 class="text-lg font-semibold text-slate-900 mb-4">{{ $charts['revenue']['title'] }}</h3>
            <div x-data="{
                chart: null,
                init() {
                    import('https://cdn.jsdelivr.net/npm/apexcharts').then(module => {
                        const ApexCharts = module.default;
                        this.chart = new ApexCharts(this.$refs.chart, {
                            series: @json($charts['revenue']['series']),
                            chart: { type: 'area', height: 300, toolbar: { show: true } },
                            xaxis: { categories: @json($charts['revenue']['categories']) },
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

        {{-- Growth Chart --}}
        <div class="bg-white rounded-xl border border-slate-200 p-6 shadow-soft">
            <h3 class="text-lg font-semibold text-slate-900 mb-4">{{ $charts['growth']['title'] }}</h3>
            <div x-data="{
                chart: null,
                init() {
                    import('https://cdn.jsdelivr.net/npm/apexcharts').then(module => {
                        const ApexCharts = module.default;
                        this.chart = new ApexCharts(this.$refs.chart, {
                            series: @json($charts['growth']['series']),
                            chart: { type: 'line', height: 300, toolbar: { show: true } },
                            xaxis: { categories: @json($charts['growth']['categories']) },
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

        {{-- Traffic Chart --}}
        <div class="bg-white rounded-xl border border-slate-200 p-6 shadow-soft">
            <h3 class="text-lg font-semibold text-slate-900 mb-4">{{ $charts['traffic']['title'] }}</h3>
            <div x-data="{
                chart: null,
                init() {
                    import('https://cdn.jsdelivr.net/npm/apexcharts').then(module => {
                        const ApexCharts = module.default;
                        this.chart = new ApexCharts(this.$refs.chart, {
                            series: @json($charts['traffic']['series']),
                            chart: { type: 'bar', height: 300, toolbar: { show: true } },
                            xaxis: { categories: @json($charts['traffic']['categories']) },
                            colors: ['#0ea5e9', '#22c55e'],
                            dataLabels: { enabled: false },
                            tooltip: { theme: 'dark' }
                        });
                        this.chart.render();
                    });
                }
            }" x-ref="chart" class="h-80"></div>
        </div>

        {{-- Alarm Trend Chart --}}
        <div class="bg-white rounded-xl border border-slate-200 p-6 shadow-soft">
            <h3 class="text-lg font-semibold text-slate-900 mb-4">{{ $charts['alarm_trend']['title'] }}</h3>
            <div x-data="{
                chart: null,
                init() {
                    import('https://cdn.jsdelivr.net/npm/apexcharts').then(module => {
                        const ApexCharts = module.default;
                        this.chart = new ApexCharts(this.$refs.chart, {
                            series: @json($charts['alarm_trend']['series']),
                            chart: { type: 'line', height: 300, toolbar: { show: true } },
                            xaxis: { categories: @json($charts['alarm_trend']['categories']) },
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

        {{-- Customer Growth Chart --}}
        <div class="bg-white rounded-xl border border-slate-200 p-6 shadow-soft">
            <h3 class="text-lg font-semibold text-slate-900 mb-4">{{ $charts['customer_growth']['title'] }}</h3>
            <div x-data="{
                chart: null,
                init() {
                    import('https://cdn.jsdelivr.net/npm/apexcharts').then(module => {
                        const ApexCharts = module.default;
                        this.chart = new ApexCharts(this.$refs.chart, {
                            series: @json($charts['customer_growth']['series']),
                            chart: { type: 'area', height: 300, toolbar: { show: true } },
                            xaxis: { categories: @json($charts['customer_growth']['categories']) },
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

        {{-- Cash Flow Chart --}}
        <div class="bg-white rounded-xl border border-slate-200 p-6 shadow-soft">
            <h3 class="text-lg font-semibold text-slate-900 mb-4">{{ $charts['cash_flow']['title'] }}</h3>
            <div x-data="{
                chart: null,
                init() {
                    import('https://cdn.jsdelivr.net/npm/apexcharts').then(module => {
                        const ApexCharts = module.default;
                        this.chart = new ApexCharts(this.$refs.chart, {
                            series: @json($charts['cash_flow']['series']),
                            chart: { type: 'bar', height: 300, toolbar: { show: true } },
                            xaxis: { categories: @json($charts['cash_flow']['categories']) },
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

    {{-- Realtime Section --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        {{-- Notifications --}}
        <div class="bg-white rounded-xl border border-slate-200 shadow-soft">
            <div class="p-6 border-b border-slate-200">
                <h3 class="text-lg font-semibold text-slate-900">Notifications</h3>
            </div>
            <div class="divide-y divide-slate-200 max-h-96 overflow-y-auto">
                @foreach ($realtimeData['notifications'] as $notification)
                    <div class="p-4">
                        <div class="flex items-start gap-3">
                            <div class="mt-0.5 w-8 h-8 rounded-full flex items-center justify-center 
                                @if($notification['type'] === 'success') bg-success-100 text-success-600
                                @elseif($notification['type'] === 'warning') bg-warning-100 text-warning-600
                                @else bg-info-100 text-info-600
                                @endif">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    @if($notification['type'] === 'success')
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                    @elseif($notification['type'] === 'warning')
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                    @else
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    @endif
                                </svg>
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-medium text-slate-900">{{ $notification['title'] }}</p>
                                <p class="text-xs text-slate-500 mt-0.5">{{ $notification['message'] }}</p>
                                <p class="text-xs text-slate-400 mt-1">{{ $notification['time'] }}</p>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        {{-- Alarms --}}
        <div class="bg-white rounded-xl border border-slate-200 shadow-soft">
            <div class="p-6 border-b border-slate-200">
                <h3 class="text-lg font-semibold text-slate-900">Alarms</h3>
            </div>
            <div class="divide-y divide-slate-200 max-h-96 overflow-y-auto">
                @foreach ($realtimeData['alarms'] as $alarm)
                    <div class="p-4">
                        <div class="flex items-start gap-3">
                            <div class="mt-0.5 w-2 h-2 rounded-full 
                                @if($alarm['severity'] === 'critical') bg-danger-500
                                @else bg-warning-500
                                @endif"></div>
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-medium text-slate-900">{{ $alarm['title'] }}</p>
                                <p class="text-xs text-slate-500 mt-0.5">{{ $alarm['message'] }}</p>
                                <p class="text-xs text-slate-400 mt-1">{{ $alarm['time'] }}</p>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        {{-- Activity --}}
        <div class="bg-white rounded-xl border border-slate-200 shadow-soft">
            <div class="p-6 border-b border-slate-200">
                <h3 class="text-lg font-semibold text-slate-900">Activity</h3>
            </div>
            <div class="divide-y divide-slate-200 max-h-96 overflow-y-auto">
                @foreach ($realtimeData['activities'] as $activity)
                    <div class="p-4">
                        <div class="flex items-start gap-3">
                            <div class="w-8 h-8 rounded-full bg-slate-100 flex items-center justify-center flex-shrink-0">
                                <span class="text-sm font-medium text-slate-600">{{ substr($activity['user'], 0, 1) }}</span>
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="text-sm text-slate-900">
                                    <span class="font-medium">{{ $activity['user'] }}</span> {{ $activity['action'] }}
                                </p>
                                <p class="text-xs text-slate-500 mt-0.5">
                                    <span class="px-2 py-0.5 bg-slate-100 rounded-full font-medium text-xs">{{ $activity['module'] }}</span>
                                    {{ $activity['time'] }}
                                </p>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
</div>
