@props([
    'traffic' => [],
    'period' => '1h',
    'setPeriodMethod' => 'setTrafficPeriod',
    'heightClass' => 'h-[250px]',
])

@php
    $currentRx = 0;
    $currentTx = 0;
    $peakTotal = 0;
    
    if (!empty($traffic)) {
        // Last point is current
        $lastPoint = end($traffic);
        $currentRx = $lastPoint['rx'] ?? 0;
        $currentTx = $lastPoint['tx'] ?? 0;
        
        foreach($traffic as $pt) {
            $rx = $pt['rx'] ?? 0;
            $tx = $pt['tx'] ?? 0;
            // Peak throughput is max of rx or tx, or if they want max combined?
            // Usually network throughput is max of (rx, tx), or rx+tx depending on full duplex. Let's use max(rx, tx) for interface capacity, or rx+tx if total?
            // "PEAK = peak throughput pada periode yang sedang dipilih" -> let's use max of RX or TX. Actually, max(RX) + max(TX) is not accurate for a single point. 
            // We'll take the max of (rx + tx) for total throughput or just max(rx, tx) depending on standard. Let's use max of max(rx, tx) as the peak throughput point.
            // Wait, "Total = RX + TX", let's use max(rx + tx) or max(max(rx), max(tx)). Let's use max(rx, tx) as it's standard for full duplex links.
            $peakTotal = max($peakTotal, max($rx, $tx));
        }
    }
    
    // Formatting helper
    $formatBps = function($bps) {
        if ($bps === 0 || !$bps) return '0 bps';
        if ($bps < 1000) return $bps . ' bps';
        if ($bps < 1000000) return number_format($bps / 1000, 1) . ' Kbps';
        if ($bps < 1000000000) return number_format($bps / 1000000, 1) . ' Mbps';
        return number_format($bps / 1000000000, 2) . ' Gbps';
    };

    $chartId = 'traffic-graph-' . uniqid();
@endphp

<div class="flex flex-col bg-[#0d1117] overflow-hidden w-full h-full" x-data="nocTrafficGraphData('{{ $chartId }}')" x-init="initChart(@js($traffic), '{{ $period }}')">
    {{-- HEADER --}}
    <div class="flex items-center justify-between px-4 py-2 border-b border-gray-800">
        <div class="flex items-center gap-3">
            <span class="text-xs font-bold text-gray-300 uppercase tracking-widest">Traffic</span>
            <span class="flex items-center gap-1 text-[10px] text-green-400">
                <span class="w-1.5 h-1.5 rounded-full bg-green-500 animate-pulse"></span>
                Live
            </span>
        </div>
        <div class="flex gap-1 bg-[#161b22] p-0.5 rounded border border-gray-800">
            @foreach(['15m', '1h', '6h', '24h'] as $p)
                <button 
                    wire:click="{{ $setPeriodMethod }}('{{ $p }}')" 
                    class="px-2 py-1 text-[10px] uppercase font-bold rounded transition-colors {{ $period === $p ? 'bg-blue-600 text-white' : 'text-gray-400 hover:text-gray-200 hover:bg-gray-800' }}"
                >
                    {{ $p }}
                </button>
            @endforeach
        </div>
    </div>

    {{-- CURRENT METRICS --}}
    <div class="px-4 py-3 border-b border-gray-800 flex gap-8 items-center bg-[#0d1117]">
        <div class="flex flex-col">
            <span class="text-[10px] text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-0.5">RX (Download)</span>
            <span class="text-lg font-bold text-green-400 font-mono">{{ $formatBps($currentRx) }}</span>
        </div>
        <div class="flex flex-col">
            <span class="text-[10px] text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-0.5">TX (Upload)</span>
            <span class="text-lg font-bold text-blue-400 font-mono">{{ $formatBps($currentTx) }}</span>
        </div>
        <div class="flex flex-col pl-4 border-l border-gray-800">
            <span class="text-[10px] text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-0.5">PEAK</span>
            <span class="text-lg font-bold text-purple-400 font-mono">{{ $formatBps($peakTotal) }}</span>
        </div>
    </div>

    {{-- GRAPH AREA --}}
    <div class="flex-1 w-full relative min-h-[200px] {{ $heightClass }}"
         x-data="nocTrafficGraphData('{{ $chartId }}')"
         x-init="initChart(@js($traffic), '{{ $period }}')">
        
        @if(empty($traffic))
            <div class="absolute inset-0 flex flex-col items-center justify-center bg-[#0d1117] z-10">
                <span class="text-sm text-gray-400 mb-1">No traffic data available yet</span>
                <span class="text-xs text-gray-600 dark:text-gray-400">Waiting for router monitoring data...</span>
            </div>
        @endif
        
        <div id="{{ $chartId }}" class="absolute inset-0 w-full h-full" wire:ignore></div>
    </div>
</div>

@once
<script src="https://cdn.jsdelivr.net/npm/echarts@5.5.0/dist/echarts.min.js"></script>
@script
<script>
window.nocTrafficGraphData = function(chartId) { return {
        chart: null,
        
        formatBps(bps) {
            if (bps === 0 || !bps) return '0 bps';
            if (bps < 1000) return bps + ' bps';
            if (bps < 1000000) return (bps / 1000).toFixed(1) + ' Kbps';
            if (bps < 1000000000) return (bps / 1000000).toFixed(1) + ' Mbps';
            return (bps / 1000000000).toFixed(2) + ' Gbps';
        },

                        initChart(data, period) {
            if (typeof window.echarts === 'undefined') {
                if (!window.loadingEcharts) {
                    window.loadingEcharts = true;
                    const script = document.createElement('script');
                    script.src = 'https://cdn.jsdelivr.net/npm/echarts@5.5.0/dist/echarts.min.js';
                    document.head.appendChild(script);
                }
                setTimeout(() => this.initChart(data, period), 200);
                return;
            }
            const dom = document.getElementById(chartId);
            if (!dom) return;
            
            // Dispose existing chart if any to avoid memory leaks during livewire updates
            const existing = echarts.getInstanceByDom(dom);
            if (existing) {
                existing.dispose();
            }
            
            if (!data || data.length === 0) return;
            
            this.chart = echarts.init(dom, 'dark');
            
            const times = data.map(i => {
                const date = new Date(i.time);
                return date.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
            });
            
            const rawRx = data.map(i => i.rx || 0);
            const rawTx = data.map(i => i.tx || 0);
            
            const formatFn = this.formatBps;
            
            const option = {
                backgroundColor: 'transparent',
                tooltip: {
                    trigger: 'axis',
                    backgroundColor: 'rgba(13, 17, 23, 0.9)',
                    borderColor: '#374151',
                    textStyle: { color: '#e5e7eb', fontSize: 12 },
                    formatter: function(params) {
                        let time = params[0].axisValue;
                        let rx = 0, tx = 0;
                        params.forEach(p => {
                            if (p.seriesName === 'RX') rx = p.value;
                            if (p.seriesName === 'TX') tx = p.value;
                        });
                        let total = rx + tx;
                        
                        return `
                            <div style="font-weight:bold;margin-bottom:4px;color:#9ca3af;">${time}</div>
                            <div style="display:flex;justify-content:space-between;gap:16px;">
                                <span style="color:#34d399">● RX</span> 
                                <span style="font-family:monospace">${formatFn(rx)}</span>
                            </div>
                            <div style="display:flex;justify-content:space-between;gap:16px;">
                                <span style="color:#60a5fa">● TX</span> 
                                <span style="font-family:monospace">${formatFn(tx)}</span>
                            </div>
                            <div style="display:flex;justify-content:space-between;gap:16px;margin-top:4px;padding-top:4px;border-top:1px solid #374151;">
                                <span style="color:#e5e7eb">Total</span> 
                                <span style="font-family:monospace;font-weight:bold;">${formatFn(total)}</span>
                            </div>
                        `;
                    }
                },
                legend: {
                    data: ['RX', 'TX'],
                    icon: 'circle',
                    itemWidth: 8,
                    textStyle: { color: '#9ca3af' },
                    bottom: 0
                },
                grid: {
                    top: 20,
                    left: 10,
                    right: 15,
                    bottom: 25,
                    containLabel: true
                },
                xAxis: {
                    type: 'category',
                    boundaryGap: false,
                    data: times,
                    axisLine: { lineStyle: { color: '#374151' } },
                    axisLabel: { color: '#6b7280', fontSize: 10 },
                    splitLine: { show: false }
                },
                yAxis: {
                    type: 'value',
                    axisLine: { show: false },
                    axisTick: { show: false },
                    axisLabel: { 
                        color: '#6b7280', 
                        fontSize: 10,
                        formatter: function(value) {
                            return formatFn(value);
                        }
                    },
                    splitLine: { 
                        show: true, 
                        lineStyle: { color: '#1f2937', type: 'dashed' } 
                    }
                },
                series: [
                    {
                        name: 'RX',
                        type: 'line',
                        smooth: 0.3,
                        symbol: 'none',
                        lineStyle: { color: '#34d399', width: 2 },
                        areaStyle: {
                            color: new echarts.graphic.LinearGradient(0, 0, 0, 1, [
                                { offset: 0, color: 'rgba(52, 211, 153, 0.2)' },
                                { offset: 1, color: 'rgba(52, 211, 153, 0)' }
                            ])
                        },
                        data: rawRx
                    },
                    {
                        name: 'TX',
                        type: 'line',
                        smooth: 0.3,
                        symbol: 'none',
                        lineStyle: { color: '#60a5fa', width: 2 },
                        areaStyle: {
                            color: new echarts.graphic.LinearGradient(0, 0, 0, 1, [
                                { offset: 0, color: 'rgba(96, 165, 250, 0.2)' },
                                { offset: 1, color: 'rgba(96, 165, 250, 0)' }
                            ])
                        },
                        data: rawTx
                    }
                ]
            };
            
            this.chart.setOption(option);
            window.addEventListener('resize', () => this.chart.resize());
            
            // Cleanup on destroy
            this.$cleanup(() => {
                if (this.chart) {
                    this.chart.dispose();
                }
            });
        }
    }; };
</script>
@endscript
@endonce





