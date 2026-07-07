<?php

namespace App\Livewire\Dashboard;

use App\Livewire\AdminComponent;

class Index extends AdminComponent
{
    public array $widgets = [];
    public array $charts = [];
    public array $realtimeData = [];
    public string $dateRange = 'this_month';

    public function mount(): void
    {
        parent::mount();
        $this->activeModule = 'dashboard';
        $this->activePage = 'index';
        $this->loadDashboardData();
    }

    public function loadDashboardData(): void
    {
        $this->loadWidgets();
        $this->loadCharts();
        $this->loadRealtimeData();
    }

    private function loadWidgets(): void
    {
        $this->widgets = [
            'revenue' => [
                'title' => 'Revenue',
                'value' => 'Rp 250.000.000',
                'change' => 15.3,
                'chartData' => [180, 195, 200, 210, 225, 240, 250],
                'chartColor' => 'success',
                'icon' => 'currency-dollar',
            ],
            'customer' => [
                'title' => 'Customer',
                'value' => '3.456',
                'change' => 8.2,
                'chartData' => [2800, 2900, 3000, 3100, 3200, 3350, 3456],
                'chartColor' => 'primary',
                'icon' => 'users',
            ],
            'active_pppoe' => [
                'title' => 'Active PPPoE',
                'value' => '2.890',
                'change' => 3.5,
                'chartData' => [2500, 2600, 2700, 2750, 2800, 2850, 2890],
                'chartColor' => 'info',
                'icon' => 'signal',
            ],
            'hotspot' => [
                'title' => 'Hotspot',
                'value' => '1.200',
                'change' => -2.1,
                'chartData' => [1300, 1280, 1250, 1230, 1210, 1205, 1200],
                'chartColor' => 'warning',
                'icon' => 'wifi',
            ],
            'voucher' => [
                'title' => 'Voucher',
                'value' => '5.000',
                'change' => 12.0,
                'chartData' => [4000, 4200, 4400, 4600, 4700, 4850, 5000],
                'chartColor' => 'success',
                'icon' => 'ticket',
            ],
            'online_onu' => [
                'title' => 'Online ONU',
                'value' => '3.100',
                'change' => 5.8,
                'chartData' => [2800, 2850, 2900, 2950, 3000, 3050, 3100],
                'chartColor' => 'success',
                'icon' => 'device-tablet',
            ],
            'offline_onu' => [
                'title' => 'Offline ONU',
                'value' => '356',
                'change' => -10.2,
                'chartData' => [450, 430, 410, 390, 380, 370, 356],
                'chartColor' => 'danger',
                'icon' => 'exclamation-circle',
            ],
            'olt' => [
                'title' => 'OLT',
                'value' => '12',
                'change' => 0,
                'chartData' => [12, 12, 12, 12, 12, 12, 12],
                'chartColor' => 'primary',
                'icon' => 'server',
            ],
            'router' => [
                'title' => 'Router',
                'value' => '25',
                'change' => 0,
                'chartData' => [25, 25, 25, 25, 25, 25, 25],
                'chartColor' => 'info',
                'icon' => 'server-stack',
            ],
            'alarm' => [
                'title' => 'Alarm',
                'value' => '45',
                'change' => 12.5,
                'chartData' => [35, 38, 40, 42, 43, 44, 45],
                'chartColor' => 'danger',
                'icon' => 'bell-alert',
            ],
            'ticket' => [
                'title' => 'Ticket',
                'value' => '89',
                'change' => -5.3,
                'chartData' => [100, 98, 95, 92, 90, 89, 89],
                'chartColor' => 'warning',
                'icon' => 'support',
            ],
            'invoice' => [
                'title' => 'Invoice',
                'value' => '245',
                'change' => 8.9,
                'chartData' => [200, 210, 220, 230, 235, 240, 245],
                'chartColor' => 'primary',
                'icon' => 'document-text',
            ],
            'outstanding' => [
                'title' => 'Outstanding',
                'value' => 'Rp 150.000.000',
                'change' => -3.2,
                'chartData' => [180, 175, 170, 165, 160, 155, 150],
                'chartColor' => 'warning',
                'icon' => 'clock',
            ],
            'collection' => [
                'title' => 'Collection',
                'value' => 'Rp 100.000.000',
                'change' => 18.5,
                'chartData' => [70, 75, 80, 85, 90, 95, 100],
                'chartColor' => 'success',
                'icon' => 'banknotes',
            ],
            'bandwidth' => [
                'title' => 'Bandwidth',
                'value' => '850 Mbps',
                'change' => 4.7,
                'chartData' => [700, 720, 750, 780, 800, 830, 850],
                'chartColor' => 'primary',
                'icon' => 'speedometer',
            ],
            'cpu' => [
                'title' => 'CPU',
                'value' => '45%',
                'change' => -2.0,
                'chartData' => [50, 48, 47, 46, 45, 45, 45],
                'chartColor' => 'info',
                'icon' => 'cpu-chip',
            ],
            'memory' => [
                'title' => 'Memory',
                'value' => '68%',
                'change' => 1.5,
                'chartData' => [60, 62, 64, 65, 66, 67, 68],
                'chartColor' => 'warning',
                'icon' => 'memory-stick',
            ],
            'storage' => [
                'title' => 'Storage',
                'value' => '72%',
                'change' => 0.5,
                'chartData' => [68, 69, 70, 71, 71, 72, 72],
                'chartColor' => 'warning',
                'icon' => 'hard-drive',
            ],
        ];
    }

    private function loadCharts(): void
    {
        $this->charts = [
            'revenue' => [
                'title' => 'Revenue',
                'type' => 'area',
                'series' => [
                    ['name' => 'Revenue', 'data' => [120, 135, 142, 150, 165, 180, 195, 200, 210, 225, 240, 250]],
                ],
                'categories' => ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'],
            ],
            'growth' => [
                'title' => 'Growth',
                'type' => 'line',
                'series' => [
                    ['name' => 'Customer Growth', 'data' => [5, 8, 10, 12, 15, 18, 20, 22, 25, 28, 30, 32]],
                    ['name' => 'Revenue Growth', 'data' => [3, 6, 8, 10, 12, 14, 16, 18, 20, 22, 24, 26]],
                ],
                'categories' => ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'],
            ],
            'traffic' => [
                'title' => 'Traffic',
                'type' => 'bar',
                'series' => [
                    ['name' => 'Download', 'data' => [450, 480, 500, 520, 550, 580, 600, 620, 650, 680, 700, 750]],
                    ['name' => 'Upload', 'data' => [150, 160, 170, 180, 190, 200, 210, 220, 230, 240, 250, 260]],
                ],
                'categories' => ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'],
            ],
            'alarm_trend' => [
                'title' => 'Alarm Trend',
                'type' => 'line',
                'series' => [
                    ['name' => 'Critical', 'data' => [5, 6, 7, 5, 6, 7, 8, 7, 6, 7, 8, 9]],
                    ['name' => 'Warning', 'data' => [15, 18, 20, 18, 19, 20, 22, 21, 20, 22, 23, 24]],
                    ['name' => 'Info', 'data' => [20, 22, 25, 23, 24, 25, 28, 27, 26, 28, 29, 30]],
                ],
                'categories' => ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'],
            ],
            'customer_growth' => [
                'title' => 'Customer Growth',
                'type' => 'area',
                'series' => [
                    ['name' => 'New Customers', 'data' => [45, 52, 58, 65, 72, 80, 88, 95, 102, 110, 118, 125]],
                    ['name' => 'Active Customers', 'data' => [2800, 2850, 2900, 2950, 3000, 3050, 3100, 3150, 3200, 3250, 3350, 3456]],
                ],
                'categories' => ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'],
            ],
            'cash_flow' => [
                'title' => 'Cash Flow',
                'type' => 'bar',
                'series' => [
                    ['name' => 'Income', 'data' => [120, 135, 142, 150, 165, 180, 195, 200, 210, 225, 240, 250]],
                    ['name' => 'Expense', 'data' => [80, 90, 95, 100, 110, 120, 130, 135, 140, 150, 160, 170]],
                ],
                'categories' => ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'],
            ],
        ];
    }

    private function loadRealtimeData(): void
    {
        $this->realtimeData = [
            'notifications' => [
                [
                    'id' => 1,
                    'title' => 'Payment Received',
                    'message' => 'Payment of Rp 500.000 from Customer #123',
                    'time' => '2 minutes ago',
                    'type' => 'success',
                ],
                [
                    'id' => 2,
                    'title' => 'New Ticket',
                    'message' => 'New support ticket created by Customer #456',
                    'time' => '5 minutes ago',
                    'type' => 'warning',
                ],
                [
                    'id' => 3,
                    'title' => 'System Update',
                    'message' => 'System will be updated tonight at 2 AM',
                    'time' => '10 minutes ago',
                    'type' => 'info',
                ],
            ],
            'alarms' => [
                [
                    'id' => 1,
                    'title' => 'OLT-01 High CPU',
                    'message' => 'CPU usage at 92% - Threshold: 90%',
                    'time' => '5 minutes ago',
                    'severity' => 'critical',
                ],
                [
                    'id' => 2,
                    'title' => 'ONU Offline',
                    'message' => '5 ONUs are currently offline in OLT-02',
                    'time' => '15 minutes ago',
                    'severity' => 'warning',
                ],
                [
                    'id' => 3,
                    'title' => 'Bandwidth Usage',
                    'message' => 'Bandwidth usage approaching limit (85%)',
                    'time' => '30 minutes ago',
                    'severity' => 'warning',
                ],
            ],
            'activities' => [
                [
                    'id' => 1,
                    'user' => 'John Doe',
                    'action' => 'created a new customer',
                    'module' => 'CRM',
                    'time' => '1 minute ago',
                ],
                [
                    'id' => 2,
                    'user' => 'Jane Smith',
                    'action' => 'approved invoice #INV-2024-001',
                    'module' => 'Billing',
                    'time' => '5 minutes ago',
                ],
                [
                    'id' => 3,
                    'user' => 'System',
                    'action' => 'detected high CPU on OLT-01',
                    'module' => 'NOC',
                    'time' => '10 minutes ago',
                ],
                [
                    'id' => 4,
                    'user' => 'Mike Johnson',
                    'action' => 'resolved ticket #TKT-2024-045',
                    'module' => 'Support',
                    'time' => '15 minutes ago',
                ],
            ],
        ];
    }

    public function setDateRange(string $range): void
    {
        $this->dateRange = $range;
        $this->loadDashboardData();
    }

    public function render()
    {
        return view('livewire.dashboard.index');
    }
}
