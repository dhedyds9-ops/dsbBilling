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
use App\Models\ISP\Voucher;
use App\Models\ISP\OnlineSession;
use App\Models\ISP\PppActiveSession;
use App\Models\ISP\HotspotActiveSession;
use App\Models\ISP\RadiusNas;
use App\Models\ISP\RadiusAccounting;
use App\Models\ISP\RouterMonitoringLog;
use App\Models\ACS\ACSDevice;
use App\Models\Alarm;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Carbon\Carbon;

class Index extends Component
{
    public string $dateRange = 'today';
    
    public array $kpi = [];
    public array $systemInfo = [];
    public array $chartData = [];
    public array $activities = [];
    public array $topTraffic = [];
    public array $alerts = [];

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
        // 0. Determine Date Range Filter Bounds
        [$startDate, $endDate] = match ($this->dateRange) {
            'today' => [Carbon::today()->startOfDay(), Carbon::today()->endOfDay()],
            'yesterday' => [Carbon::yesterday()->startOfDay(), Carbon::yesterday()->endOfDay()],
            'this_week' => [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()],
            'last_week' => [Carbon::now()->subWeek()->startOfWeek(), Carbon::now()->subWeek()->endOfWeek()],
            'last_month' => [Carbon::now()->subMonth()->startOfMonth(), Carbon::now()->subMonth()->endOfMonth()],
            default => [Carbon::now()->startOfMonth(), Carbon::now()->endOfMonth()], // 'this_month'
        };

        // 1-5. Caching KPI metrics
        $this->kpi = Cache::remember("dashboard_kpi_{$this->dateRange}", 60, function() use ($startDate, $endDate) {
            // 1. Customer Metrics (hanya pelanggan reguler)
            try {
                $baseCust         = Customer::withoutGuests();
                $totalCustomers   = (clone $baseCust)->count();
                $activeCustomers  = (clone $baseCust)->where('status', 'active')->count();
                $suspendCustomers = (clone $baseCust)->where('status', 'suspend')->count();
                $newCustomers     = (clone $baseCust)->whereBetween('created_at', [$startDate, $endDate])->count();
            } catch (\Exception $e) {
                $totalCustomers = 0; $activeCustomers = 0; $suspendCustomers = 0; $newCustomers = 0;
            }

            // 2. Services Metrics
            try {
                $pppoeTotal = PPPoEUser::count();
                $pppoeOnline = PppActiveSession::count();
                if ($pppoeOnline === 0) {
                    $pppoeOnline = OnlineSession::where('protocol', 'pppoe')->count();
                }
                $pppoeOffline = max(0, $pppoeTotal - $pppoeOnline);

                $hotspotTotal = HotspotUser::count();
                $hotspotOnline = HotspotActiveSession::count();
                if ($hotspotOnline === 0) {
                    $hotspotOnline = OnlineSession::where('protocol', 'hotspot')->count();
                }
            } catch (\Exception $e) {
                $pppoeTotal = 0; $pppoeOnline = 0; $pppoeOffline = 0; $hotspotTotal = 0; $hotspotOnline = 0;
            }

            // 3. Voucher Metrics
            try {
                $voucherSold = Voucher::whereNotIn('status', ['available', 'expired', 'void'])
                    ->whereBetween('updated_at', [$startDate, $endDate])
                    ->count();
                    
                $voucherRevenue = Voucher::whereNotIn('vouchers.status', ['available', 'expired', 'void'])
                    ->whereBetween('vouchers.updated_at', [$startDate, $endDate])
                    ->join('service_profiles', 'vouchers.service_profile_id', '=', 'service_profiles.id')
                    ->sum(DB::raw('COALESCE(service_profiles.promo_price, service_profiles.base_price, 0)'));
                    
                $voucherAvailable = Voucher::where('status', 'available')->count();
            } catch (\Exception $e) {
                $voucherSold = 0; $voucherRevenue = 0; $voucherAvailable = 0;
            }

            // 4. Billing Metrics
            try {
                $tagihan = Invoice::whereBetween('created_at', [$startDate, $endDate])->sum('total_amount');
                
                $paid = Invoice::whereBetween('created_at', [$startDate, $endDate])
                               ->where('status', 'paid')->sum('paid_amount');
                if ($paid == 0) {
                    $paid = Payment::whereBetween('payment_date', [$startDate, $endDate])->sum('amount');
                }
                               
                $unpaid = Invoice::whereIn('status', ['unpaid', 'partially_paid'])->sum('total_amount');
                $overdue = Invoice::where('status', 'overdue')->sum('total_amount');
                $overdueCount = Invoice::where('status', 'overdue')->count();
            } catch (\Exception $e) {
                $tagihan = 0; $paid = 0; $unpaid = 0; $overdue = 0; $overdueCount = 0;
            }

            // 5. Network Metrics
            try {
                $onuTotal = Onu::count();
                $onuOnline = Onu::where('status', 'active')->count();
                $onuLos = Onu::where('status', 'los')->count();

                $routerTotal = Router::count();
                $routerOnline = Router::where('status', 'active')->count();
                $routerOffline = max(0, $routerTotal - $routerOnline);

                $oltTotal = Olt::count();
                $oltOnline = Olt::where('status', 'active')->count();
            } catch (\Exception $e) {
                $onuTotal = 0; $onuOnline = 0; $onuLos = 0;
                $routerTotal = 0; $routerOnline = 0; $routerOffline = 0;
                $oltTotal = 0; $oltOnline = 0;
            }

            return [
                'customer' => [
                    'total' => $totalCustomers,
                    'active' => $activeCustomers,
                    'suspend' => $suspendCustomers,
                    'new' => $newCustomers,
                ],
                'service' => [
                    'pppoe_total' => $pppoeTotal,
                    'pppoe_online' => $pppoeOnline,
                    'pppoe_offline' => $pppoeOffline,
                    'hotspot_total' => $hotspotTotal,
                    'hotspot_online' => $hotspotOnline,
                    'total_online' => $pppoeOnline + $hotspotOnline,
                ],
                'billing' => [
                    'tagihan' => $tagihan,
                    'paid' => $paid,
                    'unpaid' => $unpaid,
                    'overdue' => $overdue,
                    'overdue_count' => $overdueCount,
                ],
                'voucher' => [
                    'sold' => $voucherSold,
                    'revenue' => $voucherRevenue,
                    'available' => $voucherAvailable,
                ],
                'network' => [
                    'onu_total' => $onuTotal,
                    'onu_online' => $onuOnline,
                    'onu_los' => $onuLos,
                    'router_total' => $routerTotal,
                    'router_online' => $routerOnline,
                    'router_offline' => $routerOffline,
                    'olt_total' => $oltTotal,
                    'olt_online' => $oltOnline,
                ]
            ];
        });

        $onuLos = $this->kpi['network']['onu_los'] ?? 0;
        $routerOffline = $this->kpi['network']['router_offline'] ?? 0;
        $routerTotal = $this->kpi['network']['router_total'] ?? 0;
        $routerOnline = $this->kpi['network']['router_online'] ?? 0;
        $overdueCount = $this->kpi['billing']['overdue_count'] ?? 0;
        $overdue = $this->kpi['billing']['overdue'] ?? 0;
        $pppoeOnline = $this->kpi['service']['pppoe_online'] ?? 0;
        $hotspotOnline = $this->kpi['service']['hotspot_online'] ?? 0;
        $pppoeTotal = $this->kpi['service']['pppoe_total'] ?? 0;
        $hotspotTotal = $this->kpi['service']['hotspot_total'] ?? 0;
        $voucherSold = $this->kpi['voucher']['sold'] ?? 0;

        // 6. Real System & RADIUS Health (Cached 30s)
        $this->systemInfo = Cache::remember('dashboard_system_info', 30, function() use ($routerTotal, $routerOnline, $pppoeOnline, $hotspotOnline) {
            try {
                $dbHealthy = false;
                try {
                    DB::connection()->getPdo();
                    $dbHealthy = true;
                } catch (\Exception $e) {
                    $dbHealthy = false;
                }

                $radiusNasCount = RadiusNas::where('status', 'active')->count();
                $radiusAcctToday = RadiusAccounting::whereDate('created_at', Carbon::today())->count();
                $radiusActiveSessions = RadiusAccounting::where(function($q) {
                    $q->where('acct_status_type', 'Start')->orWhereNull('acct_stop_time');
                })->count();

                $acsCount = ACSDevice::count();

                return [
                    'database_status' => $dbHealthy ? 'HEALTHY' : 'DEGRADED',
                    'radius_nas_count' => $radiusNasCount,
                    'radius_acct_today' => $radiusAcctToday,
                    'radius_active_sessions' => $radiusActiveSessions > 0 ? $radiusActiveSessions : ($pppoeOnline + $hotspotOnline),
                    'acs_device_count' => $acsCount,
                    'router_status_text' => $routerTotal > 0 ? "{$routerOnline}/{$routerTotal} Online" : "0 Terdaftar",
                ];
            } catch (\Exception $e) {
                return [
                    'database_status' => 'UNKNOWN',
                    'radius_nas_count' => 0,
                    'radius_acct_today' => 0,
                    'radius_active_sessions' => 0,
                    'acs_device_count' => 0,
                    'router_status_text' => '0 Terdaftar',
                ];
            }
        });

        // 7. System Alerts (Real Data)
        $realAlerts = [];
        if ($onuLos > 0) {
            $realAlerts[] = [
                'level' => 'red',
                'title' => "{$onuLos} ONU Mengalami LOS",
                'desc' => 'Prioritas Tinggi · Cek kabel fiber atau redaman ODP segera.',
            ];
        }
        if ($routerOffline > 0) {
            $realAlerts[] = [
                'level' => 'red',
                'title' => "{$routerOffline} Router Offline",
                'desc' => 'Perangkat router tidak merespons API / ping.',
            ];
        }
        if ($overdueCount > 0) {
            $realAlerts[] = [
                'level' => 'amber',
                'title' => "{$overdueCount} Tagihan Melewati Jatuh Tempo",
                'desc' => 'Total Rp ' . number_format($overdue, 0, ',', '.') . ' belum dilunasi pelanggan.',
            ];
        }

        // Fetch open system alarms from Alarm model
        try {
            $systemAlarms = Alarm::where('status', 'open')->latest()->take(3)->get();
            foreach ($systemAlarms as $alarm) {
                $realAlerts[] = [
                    'level' => $alarm->level === 'critical' ? 'red' : 'amber',
                    'title' => $alarm->title,
                    'desc' => $alarm->description ?? 'Peringatan otomatis sistem monitoring.',
                ];
            }
        } catch (\Exception $e) {}

        $this->alerts = $realAlerts;

        // 8. Recent Activity (Cached 30s)
        $this->activities = Cache::remember('dashboard_recent_activities', 30, function() {
            $recentActivities = [];
            try {
                $payments = Payment::with('invoices.customer')->latest()->take(5)->get();
                foreach ($payments as $payment) {
                    $customerName = 'Pelanggan';
                    if ($payment->invoices && $payment->invoices->count() > 0 && $payment->invoices->first()->customer) {
                        $customerName = $payment->invoices->first()->customer->name;
                    }
                    
                    $recentActivities[] = [
                        'color' => 'emerald',
                        'text' => 'Pembayaran Rp ' . number_format($payment->amount, 0, ',', '.') . ' dari ' . $customerName,
                        'time' => $payment->created_at ? $payment->created_at->diffForHumans() : 'Baru saja',
                    ];
                }

                if (count($recentActivities) < 5) {
                    $newestCustomers = Customer::latest()->take(5 - count($recentActivities))->get();
                    foreach ($newestCustomers as $nc) {
                        $recentActivities[] = [
                            'color' => 'indigo',
                            'text' => 'Pendaftaran pelanggan baru: ' . $nc->name,
                            'time' => $nc->created_at ? $nc->created_at->diffForHumans() : 'Baru saja',
                        ];
                    }
                }
            } catch (\Exception $e) {
                \Illuminate\Support\Facades\Log::error("Dashboard recent activities: " . $e->getMessage());
            }
            return $recentActivities;
        });

        // 9-10. Caching Chart Data (Revenue & Traffic)
        $this->chartData = Cache::remember("dashboard_chart_{$this->dateRange}", 60, function() use ($startDate, $endDate, $pppoeTotal, $hotspotTotal, $voucherSold) {
            $weeklyPaid = [];
            $weeklyUnpaid = [];
            $revenueCategories = [];

            if (in_array($this->dateRange, ['today', 'yesterday'])) {
                // Group by 4-hour intervals
                for ($h = 0; $h < 24; $h += 4) {
                    $bStart = $startDate->copy()->addHours($h);
                    $bEnd = $startDate->copy()->addHours($h + 4)->subSecond();
                    $revenueCategories[] = sprintf('%02d:00', $h);
                    
                    $wPaid = Invoice::whereBetween('created_at', [$bStart, $bEnd])->where('status', 'paid')->sum('paid_amount');
                    $wUnpaid = Invoice::whereBetween('created_at', [$bStart, $bEnd])->whereIn('status', ['unpaid', 'overdue'])->sum('total_amount');
                    $weeklyPaid[] = (int) $wPaid;
                    $weeklyUnpaid[] = (int) $wUnpaid;
                }
            } else {
                // Group by 4 weeks
                for ($i = 1; $i <= 4; $i++) {
                    $start = $startDate->copy()->addDays(($i - 1) * 7);
                    $end = ($i == 4) ? $endDate->copy() : $start->copy()->addDays(6)->endOfDay();
                    $revenueCategories[] = 'Minggu ' . $i;

                    $wPaid = Invoice::whereBetween('created_at', [$start, $end])->where('status', 'paid')->sum('paid_amount');
                    $wUnpaid = Invoice::whereBetween('created_at', [$start, $end])->whereIn('status', ['unpaid', 'overdue'])->sum('total_amount');
                    $weeklyPaid[] = (int) $wPaid;
                    $weeklyUnpaid[] = (int) $wUnpaid;
                }
            }

            // Real Traffic Chart Data (Optimized with DB aggregate)
            $trafficCategories = [];
            $downloadTraffic = [];
            $uploadTraffic = [];

            $now = Carbon::now();
            for ($i = 9; $i >= 0; $i--) {
                $bucketStart = $now->copy()->subHours(($i + 1) * 2);
                $bucketEnd = $now->copy()->subHours($i * 2);
                $trafficCategories[] = $bucketEnd->format('H:i');

                try {
                    $stat = RouterMonitoringLog::whereBetween('created_at', [$bucketStart, $bucketEnd])
                        ->selectRaw('AVG(rx_bps) as avg_rx, AVG(tx_bps) as avg_tx')
                        ->first();
                    $avgRx = $stat?->avg_rx ?? 0;
                    $avgTx = $stat?->avg_tx ?? 0;
                    $downloadTraffic[] = round($avgRx / 1000000, 2); // In Mbps
                    $uploadTraffic[] = round($avgTx / 1000000, 2);   // In Mbps
                } catch (\Exception $e) {
                    $downloadTraffic[] = 0;
                    $uploadTraffic[] = 0;
                }
            }

            return [
                'revenue' => [
                    'categories' => $revenueCategories,
                    'paid' => $weeklyPaid,
                    'unpaid' => $weeklyUnpaid,
                ],
                'distribution' => [$pppoeTotal, $hotspotTotal, $voucherSold],
                'traffic' => [
                    'categories' => $trafficCategories,
                    'download' => $downloadTraffic,
                    'upload' => $uploadTraffic,
                ]
            ];
        });

        // 11. Top Traffic Users (Cached 30s)
        $formatBytes = function ($bytes) {
            $bytes = (float) ($bytes ?? 0);
            if ($bytes >= 1073741824) {
                return number_format($bytes / 1073741824, 2) . ' GB';
            } elseif ($bytes >= 1048576) {
                return number_format($bytes / 1048576, 1) . ' MB';
            } elseif ($bytes >= 1024) {
                return number_format($bytes / 1024, 0) . ' KB';
            }
            return number_format($bytes, 0) . ' B';
        };

        $this->topTraffic = Cache::remember('dashboard_top_traffic', 30, function() use ($formatBytes) {
            $top = [];
            try {
                $topTrafficRaw = OnlineSession::orderByRaw('(bytes_in + bytes_out) DESC')->take(5)->get();
                if ($topTrafficRaw->isNotEmpty()) {
                    foreach ($topTrafficRaw as $sess) {
                        $top[] = [
                            'name' => $sess->username ?? 'Unknown',
                            'service' => strtoupper($sess->protocol ?? 'PPPoE'),
                            'ip' => $sess->address ?? '-',
                            'download' => $formatBytes($sess->bytes_out ?? 0),
                            'upload' => $formatBytes($sess->bytes_in ?? 0),
                        ];
                    }
                } else {
                    // Fallback to active PPPoE sessions
                    $pppSessions = PppActiveSession::orderByRaw('(bytes_in + bytes_out) DESC')->take(5)->get();
                    foreach ($pppSessions as $sess) {
                        $top[] = [
                            'name' => $sess->name ?? 'Unknown',
                            'service' => 'PPPOE',
                            'ip' => $sess->address ?? '-',
                            'download' => $formatBytes($sess->bytes_out ?? 0),
                            'upload' => $formatBytes($sess->bytes_in ?? 0),
                        ];
                    }
                }
            } catch (\Exception $e) {}
            return $top;
        });
    }

    public function render()
    {
        return view('livewire.dashboard.index', [
            'kpi' => $this->kpi,
            'systemInfo' => $this->systemInfo,
            'activities' => $this->activities,
            'chartData' => $this->chartData,
            'topTraffic' => $this->topTraffic,
            'alerts' => $this->alerts,
        ]);
    }
}
