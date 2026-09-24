<?php

namespace App\Livewire\Dashboard;

use Livewire\Component;
use App\Models\CRM\Customer;
use App\Models\ISP\PPPoEUser;
use App\Models\ISP\HotspotUser;
use App\Models\ISP\Router;
use App\Models\ISP\Olt;
use App\Models\ISP\Onu;
use App\Models\Billing\Invoice;
use App\Models\Payment\Payment;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class Index extends Component
{
    public string $dateRange = 'today';
    
    public array $kpi = [];
    public array $systemInfo = [];
    public array $chartData = [];
    public array $activities = [];
    public array $topTraffic = [];

    public function mount()
    {
        $this->loadData();
    }

    public function setDateRange(string $range): void
    {
        $this->dateRange = $range;
        $this->loadData();
    }

        public function loadData(): void
    {
        // 1. Customer Metrics
        try {
            $totalCustomers = Customer::count();
            $activeCustomers = Customer::where('status', 'active')->count();
            $suspendCustomers = Customer::where('status', 'suspend')->count();
            $newCustomers = Customer::whereMonth('created_at', Carbon::now()->month)->count();
        } catch (\Exception $e) {
            $totalCustomers = 0; $activeCustomers = 0; $suspendCustomers = 0; $newCustomers = 0;
        }

        // 2. Services Metrics
        try {
            $pppoeTotal = PPPoEUser::count();
            $pppoeOnline = \App\Models\ISP\PppActiveSession::count();
            $pppoeOffline = max(0, $pppoeTotal - $pppoeOnline);
            $hotspotTotal = HotspotUser::count();
            $hotspotOnline = \App\Models\ISP\HotspotActiveSession::count();
        } catch (\Exception $e) {
            $pppoeTotal = 0; $pppoeOnline = 0; $pppoeOffline = 0; $hotspotTotal = 0; $hotspotOnline = 0;
        }

                // 3. Billing Metrics
        try {
            $voucherSold = \App\Models\ISP\Voucher::whereNotIn('status', ['available', 'expired', 'void'])
                ->whereMonth('updated_at', Carbon::now()->month)
                ->count();
                
            $voucherRevenue = \App\Models\ISP\Voucher::whereNotIn('vouchers.status', ['available', 'expired', 'void'])
                ->whereMonth('vouchers.updated_at', Carbon::now()->month)
                ->join('service_profiles', 'vouchers.service_profile_id', '=', 'service_profiles.id')
                ->sum(DB::raw('COALESCE(service_profiles.promo_price, service_profiles.base_price, 0)'));
                
            $voucherAvailable = \App\Models\ISP\Voucher::where('status', 'available')->count();
        } catch (\Exception $e) {
            $voucherSold = 0; $voucherRevenue = 0; $voucherAvailable = 0;
        }

        // 3. Billing Metrics
        try {
            $currentMonth = Carbon::now()->month;
            $currentYear = Carbon::now()->year;
            
            $tagihan = Invoice::whereMonth('created_at', $currentMonth)
                              ->whereYear('created_at', $currentYear)->sum('total_amount');
            
            $paid = Invoice::whereMonth('created_at', $currentMonth)
                           ->whereYear('created_at', $currentYear)
                           ->where('status', 'paid')->sum('paid_amount');
                           
            $unpaid = Invoice::whereMonth('created_at', $currentMonth)
                             ->whereYear('created_at', $currentYear)
                             ->where('status', 'unpaid')->sum('total_amount');
                             
            $overdue = Invoice::whereMonth('created_at', $currentMonth)
                              ->whereYear('created_at', $currentYear)
                              ->where('status', 'overdue')->sum('total_amount');
        } catch (\Exception $e) {
            $tagihan = 0; $paid = 0; $unpaid = 0; $overdue = 0;
        }

        // 4. Network Metrics
        try {
            $onuTotal = Onu::count();
            $routerTotal = Router::count();
            $routerOnline = Router::where('status', 'active')->count();
            $routerOffline = $routerTotal - $routerOnline;
            $oltTotal = Olt::count();
            $onuLos = Onu::where('status', 'los')->count();
        } catch (\Exception $e) {
            $onuTotal = 0; $routerTotal = 0; $routerOnline = 0; $routerOffline = 0; $oltTotal = 0; $onuLos = 0;
        }

        $this->kpi = [
            'customer' => [
                'total' => $totalCustomers,
                'active' => $activeCustomers,
                'suspend' => $suspendCustomers,
                'new' => $newCustomers,
            ],
            'service' => [
                'pppoe_online' => $pppoeOnline,
                'pppoe_offline' => $pppoeOffline,
                'hotspot_session' => $hotspotTotal,
                'hotspot_online' => $hotspotOnline,
            ],
            'billing' => [
                'tagihan' => $tagihan,
                'paid' => $paid,
                'unpaid' => $unpaid,
                'overdue' => $overdue,
            ],
            'voucher' => [
                'sold' => $voucherSold,
                'revenue' => $voucherRevenue,
                'available' => $voucherAvailable,
            ],
            'network' => [
                'onu_total' => $onuTotal,
                'onu_los' => $onuLos,
                'router_online' => $routerOnline,
                'router_offline' => $routerOffline,
                'olt_total' => $oltTotal,
            ]
        ];

        $this->systemInfo = [
            'radius_uptime' => 'Online',
            'database_status' => 'HEALTHY',
        ];

        // 5. Recent Activity
        $recentActivities = [];
        try {
            $payments = Payment::with('invoices.customer')->latest()->take(5)->get();
            foreach($payments as $payment) {
                $customerName = 'Unknown Customer';
                if ($payment->invoices && $payment->invoices->count() > 0 && $payment->invoices->first()->customer) {
                    $customerName = $payment->invoices->first()->customer->name;
                }
                
                $recentActivities[] = [
                    'color' => 'emerald',
                    'text' => 'Pembayaran Rp ' . number_format($payment->amount, 0, ',', '.') . ' dari ' . $customerName,
                    'time' => $payment->created_at->diffForHumans(),
                ];
            }
        } catch (\Exception $e) {}
        $this->activities = $recentActivities;

        // 6. Chart Data
        $weeklyPaid = [];
        $weeklyUnpaid = [];
        for ($i = 1; $i <= 4; $i++) {
            // Rough weekly breakdown for current month
            $start = Carbon::now()->startOfMonth()->addDays(($i-1)*7);
            $end = $i == 4 ? Carbon::now()->endOfMonth() : $start->copy()->addDays(6)->endOfDay();
            
            $wPaid = Invoice::whereBetween('created_at', [$start, $end])->where('status', 'paid')->sum('paid_amount');
            $wUnpaid = Invoice::whereBetween('created_at', [$start, $end])->whereIn('status', ['unpaid', 'overdue'])->sum('total_amount');
            
            $weeklyPaid[] = $wPaid;
            $weeklyUnpaid[] = $wUnpaid;
        }

        $this->chartData = [
            'revenue' => [
                'categories' => ['Minggu 1', 'Minggu 2', 'Minggu 3', 'Minggu 4'],
                'paid' => $weeklyPaid,
                'unpaid' => $weeklyUnpaid,
            ],
            'distribution' => [$pppoeTotal, $hotspotTotal, $onuTotal]
        ];
        // 7. Top Traffic Users
        $topTrafficRaw = \App\Models\ISP\OnlineSession::orderByRaw('(bytes_in + bytes_out) DESC')->take(5)->get();
        $top = [];
        foreach ($topTrafficRaw as $sess) {
            $downGb = number_format($sess->bytes_out / 1073741824, 2);
            $upGb = number_format($sess->bytes_in / 1073741824, 2);
            $top[] = [
                'name' => $sess->username,
                'service' => strtoupper($sess->protocol),
                'ip' => $sess->address,
                'download' => $downGb . ' GB',
                'upload' => $upGb . ' GB',
            ];
        }
        $this->topTraffic = $top;
    }

    public function render()
    {
        return view('livewire.dashboard.index', [
            'kpi' => $this->kpi,
            'systemInfo' => $this->systemInfo,
            'activities' => $this->activities,
            'chartData' => $this->chartData,
            'topTraffic' => $this->topTraffic,
        ]);
    }
}


