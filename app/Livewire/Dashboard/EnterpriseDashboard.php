<?php

namespace App\Livewire\Dashboard;

use Livewire\Component;
use Livewire\WithPolling;
use Illuminate\Support\Facades\Http;

class EnterpriseDashboard extends Component
{
    use WithPolling;

    public string $selectedPeriod = 'today';
    public string $selectedRegion = 'all';
    public bool $isLoading = true;

    // KPI Data
    public array $kpis = [];

    // Chart Data
    public array $revenueChartData = [];
    public array $trafficChartData = [];
    public array $alarmTrendChartData = [];
    public array $customerGrowthChartData = [];
    public array $cashFlowChartData = [];

    // Realtime Data
    public array $recentActivities = [];
    public array $activeAlarms = [];
    public array $notifications = [];

    // Realtime stats
    public int $onlineOnuCount = 0;
    public int $offlineOnuCount = 0;
    public int $activePppoeCount = 0;
    public int $activeHotspotCount = 0;

    public function mount()
    {
        $this->loadDashboardData();
    }

    public function loadDashboardData()
    {
        $this->isLoading = true;

        // Load KPI Data
        $this->loadKPIs();

        // Load Chart Data
        $this->loadRevenueChart();
        $this->loadTrafficChart();
        $this->loadAlarmTrendChart();
        $this->loadCustomerGrowthChart();
        $this->loadCashFlowChart();

        // Load Realtime Data
        $this->loadRecentActivities();
        $this->loadActiveAlarms();
        $this->loadNotifications();
        $this->loadRealtimeStats();

        $this->isLoading = false;
    }

    public function loadKPIs()
    {
        $this->kpis = [
            'revenue' => [
                'label' => 'Total Revenue',
                'value' => 'Rp 125,450,000',
                'rawValue' => 125450000,
                'trend' => 12.5,
                'trendDirection' => 'up',
                'icon' => 'currency-dollar',
                'iconBg' => 'success',
            ],
            'customers' => [
                'label' => 'Total Customers',
                'value' => '12,458',
                'rawValue' => 12458,
                'trend' => 5.2,
                'trendDirection' => 'up',
                'icon' => 'users',
                'iconBg' => 'primary',
            ],
            'activePppoe' => [
                'label' => 'Active PPPoE',
                'value' => '8,234',
                'rawValue' => 8234,
                'trend' => 3.1,
                'trendDirection' => 'up',
                'icon' => 'user-group',
                'iconBg' => 'info',
            ],
            'activeHotspot' => [
                'label' => 'Active Hotspot',
                'value' => '3,156',
                'rawValue' => 3156,
                'trend' => -1.2,
                'trendDirection' => 'down',
                'icon' => 'wifi',
                'iconBg' => 'secondary',
            ],
            'vouchers' => [
                'label' => 'Active Vouchers',
                'value' => '892',
                'rawValue' => 892,
                'trend' => 8.5,
                'trendDirection' => 'up',
                'icon' => 'ticket',
                'iconBg' => 'warning',
            ],
            'onlineOnu' => [
                'label' => 'Online ONU',
                'value' => '4,567',
                'rawValue' => 4567,
                'trend' => 0.5,
                'trendDirection' => 'up',
                'icon' => 'server',
                'iconBg' => 'success',
            ],
            'offlineOnu' => [
                'label' => 'Offline ONU',
                'value' => '23',
                'rawValue' => 23,
                'trend' => -15.0,
                'trendDirection' => 'down',
                'icon' => 'server',
                'iconBg' => 'danger',
            ],
            'olt' => [
                'label' => 'OLT Devices',
                'value' => '48',
                'rawValue' => 48,
                'trend' => 0,
                'trendDirection' => 'neutral',
                'icon' => 'server',
                'iconBg' => 'primary',
            ],
            'router' => [
                'label' => 'Active Routers',
                'value' => '156',
                'rawValue' => 156,
                'trend' => 2.3,
                'trendDirection' => 'up',
                'icon' => 'router',
                'iconBg' => 'info',
            ],
            'alarms' => [
                'label' => 'Active Alarms',
                'value' => '17',
                'rawValue' => 17,
                'trend' => -25.0,
                'trendDirection' => 'down',
                'icon' => 'exclamation-triangle',
                'iconBg' => 'danger',
            ],
            'tickets' => [
                'label' => 'Open Tickets',
                'value' => '34',
                'rawValue' => 34,
                'trend' => -8.0,
                'trendDirection' => 'down',
                'icon' => 'ticket',
                'iconBg' => 'warning',
            ],
            'invoices' => [
                'label' => 'Invoices This Month',
                'value' => '2,458',
                'rawValue' => 2458,
                'trend' => 15.0,
                'trendDirection' => 'up',
                'icon' => 'document-text',
                'iconBg' => 'primary',
            ],
            'outstanding' => [
                'label' => 'Outstanding',
                'value' => 'Rp 45,230,000',
                'rawValue' => 45230000,
                'trend' => 5.5,
                'trendDirection' => 'up',
                'icon' => 'clock',
                'iconBg' => 'warning',
            ],
            'collection' => [
                'label' => 'Collection Rate',
                'value' => '94.5%',
                'rawValue' => 94.5,
                'trend' => 2.1,
                'trendDirection' => 'up',
                'icon' => 'collection',
                'iconBg' => 'success',
            ],
            'bandwidth' => [
                'label' => 'Bandwidth Usage',
                'value' => '7.8 Gbps',
                'rawValue' => 7.8,
                'trend' => 12.0,
                'trendDirection' => 'up',
                'icon' => 'chart-bar',
                'iconBg' => 'info',
                'suffix' => 'Gbps',
            ],
            'cpu' => [
                'label' => 'Avg CPU Usage',
                'value' => '42%',
                'rawValue' => 42,
                'trend' => -5.0,
                'trendDirection' => 'down',
                'icon' => 'cpu',
                'iconBg' => 'primary',
            ],
            'memory' => [
                'label' => 'Avg Memory',
                'value' => '58%',
                'rawValue' => 58,
                'trend' => 3.2,
                'trendDirection' => 'up',
                'icon' => 'chip',
                'iconBg' => 'secondary',
            ],
            'storage' => [
                'label' => 'Storage Used',
                'value' => '2.4 TB',
                'rawValue' => 2.4,
                'trend' => 8.0,
                'trendDirection' => 'up',
                'icon' => 'database',
                'iconBg' => 'info',
                'suffix' => 'TB',
            ],
        ];
    }

    public function loadRevenueChart()
    {
        $this->revenueChartData = [
            'labels' => ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'],
            'datasets' => [
                [
                    'label' => 'Revenue',
                    'data' => [85000000, 92000000, 105000000, 98000000, 115000000, 125450000],
                    'borderColor' => '#0ea5e9',
                    'backgroundColor' => 'rgba(14, 165, 233, 0.1)',
                    'fill' => true,
                    'tension' => 0.4,
                ],
                [
                    'label' => 'Target',
                    'data' => [80000000, 90000000, 100000000, 100000000, 110000000, 120000000],
                    'borderColor' => '#22c55e',
                    'backgroundColor' => 'transparent',
                    'borderDash' => [5, 5],
                    'fill' => false,
                    'tension' => 0.4,
                ],
            ],
        ];
    }

    public function loadTrafficChart()
    {
        $this->trafficChartData = [
            'labels' => ['00:00', '04:00', '08:00', '12:00', '16:00', '20:00', '24:00'],
            'datasets' => [
                [
                    'label' => 'Download',
                    'data' => [2.1, 1.5, 3.2, 5.8, 7.2, 6.5, 4.2],
                    'borderColor' => '#0ea5e9',
                    'backgroundColor' => 'rgba(14, 165, 233, 0.1)',
                    'fill' => true,
                    'tension' => 0.4,
                ],
                [
                    'label' => 'Upload',
                    'data' => [1.2, 0.8, 2.1, 3.5, 4.2, 3.8, 2.5],
                    'borderColor' => '#a855f7',
                    'backgroundColor' => 'rgba(168, 85, 247, 0.1)',
                    'fill' => true,
                    'tension' => 0.4,
                ],
            ],
        ];
    }

    public function loadAlarmTrendChart()
    {
        $this->alarmTrendChartData = [
            'labels' => ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'],
            'datasets' => [
                [
                    'label' => 'Critical',
                    'data' => [2, 3, 1, 4, 2, 1, 0],
                    'backgroundColor' => '#ef4444',
                ],
                [
                    'label' => 'Warning',
                    'data' => [5, 8, 6, 9, 7, 4, 3],
                    'backgroundColor' => '#f59e0b',
                ],
                [
                    'label' => 'Info',
                    'data' => [12, 15, 10, 18, 14, 8, 6],
                    'backgroundColor' => '#3b82f6',
                ],
            ],
        ];
    }

    public function loadCustomerGrowthChart()
    {
        $this->customerGrowthChartData = [
            'labels' => ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'],
            'datasets' => [
                [
                    'label' => 'New Customers',
                    'data' => [245, 312, 278, 356, 412, 389],
                    'borderColor' => '#22c55e',
                    'backgroundColor' => 'rgba(34, 197, 94, 0.1)',
                    'fill' => true,
                    'tension' => 0.4,
                ],
                [
                    'label' => 'Churned',
                    'data' => [12, 18, 15, 22, 19, 14],
                    'borderColor' => '#ef4444',
                    'backgroundColor' => 'transparent',
                    'tension' => 0.4,
                ],
            ],
        ];
    }

    public function loadCashFlowChart()
    {
        $this->cashFlowChartData = [
            'labels' => ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'],
            'datasets' => [
                [
                    'label' => 'Inflow',
                    'data' => [95000000, 102000000, 115000000, 108000000, 125000000, 135000000],
                    'backgroundColor' => '#22c55e',
                ],
                [
                    'label' => 'Outflow',
                    'data' => [45000000, 52000000, 48000000, 55000000, 58000000, 62000000],
                    'backgroundColor' => '#ef4444',
                ],
            ],
        ];
    }

    public function loadRecentActivities()
    {
        $this->recentActivities = [
            [
                'id' => 1,
                'type' => 'payment',
                'message' => 'Payment received from Customer #1234 - Rp 500,000',
                'time' => '2 minutes ago',
                'icon' => 'currency-dollar',
                'color' => 'success',
            ],
            [
                'id' => 2,
                'type' => 'ticket',
                'message' => 'New ticket created by Customer #5678',
                'time' => '5 minutes ago',
                'icon' => 'ticket',
                'color' => 'info',
            ],
            [
                'id' => 3,
                'type' => 'alarm',
                'message' => 'OLT-01 Link Down alarm cleared',
                'time' => '12 minutes ago',
                'icon' => 'check-circle',
                'color' => 'success',
            ],
            [
                'id' => 4,
                'type' => 'customer',
                'message' => 'New customer registered: PT Maju Bersama',
                'time' => '18 minutes ago',
                'icon' => 'user-plus',
                'color' => 'primary',
            ],
            [
                'id' => 5,
                'type' => 'system',
                'message' => 'Daily backup completed successfully',
                'time' => '30 minutes ago',
                'icon' => 'server',
                'color' => 'secondary',
            ],
        ];
    }

    public function loadActiveAlarms()
    {
        $this->activeAlarms = [
            [
                'id' => 1,
                'severity' => 'critical',
                'device' => 'OLT-01',
                'message' => 'CPU usage exceeded 95%',
                'time' => '5 minutes ago',
            ],
            [
                'id' => 2,
                'severity' => 'warning',
                'device' => 'Router-RTR-05',
                'message' => 'Memory usage at 87%',
                'time' => '12 minutes ago',
            ],
            [
                'id' => 3,
                'severity' => 'info',
                'device' => 'ONT-ABC123',
                'message' => 'ONT went offline temporarily',
                'time' => '25 minutes ago',
            ],
            [
                'id' => 4,
                'severity' => 'warning',
                'device' => 'Switch-SW-03',
                'message' => 'Port 24 error rate above threshold',
                'time' => '1 hour ago',
            ],
        ];
    }

    public function loadNotifications()
    {
        $this->notifications = [
            [
                'id' => 1,
                'title' => 'Invoice Generated',
                'message' => 'Invoice #INV-2024-1245 generated',
                'time' => '10 minutes ago',
                'read' => false,
            ],
            [
                'id' => 2,
                'title' => 'System Update',
                'message' => 'Scheduled maintenance at 02:00 AM',
                'time' => '1 hour ago',
                'read' => false,
            ],
            [
                'id' => 3,
                'title' => 'License Expiring',
                'message' => 'License will expire in 7 days',
                'time' => '2 hours ago',
                'read' => true,
            ],
        ];
    }

    public function loadRealtimeStats()
    {
        $this->onlineOnuCount = 4567;
        $this->offlineOnuCount = 23;
        $this->activePppoeCount = 8234;
        $this->activeHotspotCount = 3156;
    }

    public function updatedSelectedPeriod()
    {
        $this->loadDashboardData();
    }

    public function updatedSelectedRegion()
    {
        $this->loadDashboardData();
    }

    public function refreshData()
    {
        $this->loadDashboardData();
    }

    public function render()
    {
        return view('livewire.dashboard.enterprise-dashboard');
    }
}
