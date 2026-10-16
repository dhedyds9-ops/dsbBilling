<?php

namespace App\Livewire\ResellerPortal;

use App\Livewire\AdminComponent;
use App\Models\Payment\Payment;
use App\Models\Billing\Invoice;
use App\Models\ISP\PppActiveSession;
use App\Models\ISP\HotspotActiveSession;
use App\Models\ISP\PPPoEUser;
use App\Models\ISP\HotspotUser;
use App\Models\ISP\Voucher;
use App\Models\ISP\RadiusAccounting;
use App\Models\CRM\Customer;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class Dashboard extends AdminComponent
{
    public array $mixData = [];
    public $recentIncomes;
    public $recentInvoices;
    public string $activeTab = 'ringkasan';
    
    // Activity Log
    public $activities = [];

    public function mount()
    {
        parent::mount();
        $this->activeModule = 'reseller-portal';
        $this->activePage = 'dashboard';
        
        $this->breadcrumbs = [
            ['label' => 'Reseller Portal', 'url' => '#'],
            ['label' => 'Dashboard', 'url' => route('reseller-portal.dashboard')],
        ];
        
        $this->loadDashboardData();
        $this->loadActivities();
    }
    
    public function setTab($tab)
    {
        $this->activeTab = $tab;
    }
    
    public function loadActivities()
    {
        $user = Auth::user();
        $ownerId = $user->getEffectiveResellerId() ?? ($user->hasRole('reseller') ? $user->id : null);

        $customerFilter = function ($q) use ($ownerId) {
            if ($ownerId) {
                $q->where(function ($sub) use ($ownerId) {
                    $sub->where('reseller_id', $ownerId)->orWhere('created_by', $ownerId);
                });
            }
        };

        // Load recent payments for reseller's customers
        $paymentQuery = Payment::with(['customer', 'invoices'])
            ->whereIn('status', ['paid', 'success']);

        if ($ownerId) {
            $paymentQuery->where(function ($q) use ($ownerId, $customerFilter) {
                $q->where('reseller_id', $ownerId)->orWhereHas('customer', $customerFilter);
            });
        }

        $payments = $paymentQuery->latest('paid_at')
            ->latest('created_at')
            ->take(20)
            ->get();
            
        $logs = [];
        foreach ($payments as $payment) {
            $customer = $payment->customer;
            if ($customer) {
                $type = strtoupper($customer->service_type ?? 'INTERNET');
                $timeAgo = $payment->paid_at ? $payment->paid_at->diffForHumans() : Carbon::parse($payment->created_at)->diffForHumans();
                $id = $customer->customer_id;
                $name = $customer->name;
                $amountFormatted = number_format($payment->amount ?? 0, 0, ',', '.');
                
                $logs[] = [
                    'time' => $timeAgo,
                    'message' => "Pelanggan {$type}: <strong>{$id}</strong> ({$name}) pembayaran sebesar Rp {$amountFormatted} tercatat berhasil",
                ];
            }
        }
        
        $this->activities = $logs;
    }

    public function loadDashboardData()
    {
        $user = Auth::user();
        $ownerId = $user->getEffectiveResellerId() ?? ($user->hasRole('reseller') ? $user->id : null);

        // Subquery to filter customers owned by this reseller
        $customerFilter = function ($q) use ($ownerId) {
            if ($ownerId) {
                $q->where(function ($sub) use ($ownerId) {
                    $sub->where('reseller_id', $ownerId)->orWhere('created_by', $ownerId);
                });
            }
        };

        // Top Row - Pendapatan Hari Ini
        $paymentQuery = Payment::query()->whereIn('status', ['paid', 'success']);
        if ($ownerId) {
            $paymentQuery->where(function ($q) use ($ownerId, $customerFilter) {
                $q->where('reseller_id', $ownerId)->orWhereHas('customer', $customerFilter);
            });
        }

        $incomeHariIni = (clone $paymentQuery)->whereDate('paid_at', today())->sum('amount');
        if ($incomeHariIni == 0) {
            $incomeHariIni = (clone $paymentQuery)->whereDate('created_at', today())->sum('amount');
        }

        // Tagihan Tertunggak
        $invoiceQuery = Invoice::query()->where('status', 'unpaid');
        if ($ownerId) {
            $invoiceQuery->where(function ($q) use ($ownerId, $customerFilter) {
                $q->where('reseller_id', $ownerId)->orWhereHas('customer', $customerFilter);
            });
        }
        $invoiceDataTagihan = (clone $invoiceQuery)->count();
        $jatuhTempo = (clone $invoiceQuery)->whereDate('due_date', '<', today())->count();

        // Count Hotspot and PPPoE users correctly tied to this reseller
        try {
            if ($ownerId) {
                $hotspotUserCount = HotspotUser::withoutGlobalScopes()->where(function ($q) use ($ownerId) {
                    $q->where('reseller_id', $ownerId)
                      ->orWhere('created_by', $ownerId)
                      ->orWhereHas('customer', fn($cq) => $cq->withoutGlobalScopes()->where('reseller_id', $ownerId)->orWhere('created_by', $ownerId));
                })->count();

                $pppoeUserCount = PPPoEUser::withoutGlobalScopes()->where(function ($q) use ($ownerId) {
                    $q->where('reseller_id', $ownerId)
                      ->orWhere('created_by', $ownerId)
                      ->orWhereHas('customer', fn($cq) => $cq->withoutGlobalScopes()->where('reseller_id', $ownerId)->orWhere('created_by', $ownerId));
                })->count();
                
                $pppOnline = PppActiveSession::whereExists(function ($sub) use ($ownerId) {
                    $sub->selectRaw(1)->from('pppoe_users')
                        ->leftJoin('customer_services', 'pppoe_users.customer_service_id', '=', 'customer_services.id')
                        ->leftJoin('customers', 'customer_services.customer_id', '=', 'customers.id')
                        ->where(function($uq) {
                            $uq->whereRaw('LOWER(pppoe_users.username) = LOWER(ppp_active_sessions.name)')
                               ->orWhereRaw('LOWER(ppp_active_sessions.name) LIKE LOWER(pppoe_users.username) || \'@%\'')
                               ->orWhereRaw('LOWER(pppoe_users.username) LIKE LOWER(ppp_active_sessions.name) || \'@%\'');
                        })
                        ->whereNull('pppoe_users.deleted_at')
                        ->where(function($fq) use ($ownerId) {
                            $fq->where('pppoe_users.reseller_id', $ownerId)
                               ->orWhere('customers.reseller_id', $ownerId)
                               ->orWhere('customers.created_by', $ownerId)
                               ->orWhere('customer_services.reseller_id', $ownerId);
                        });
                })->count();

                if ($pppOnline === 0) {
                    $pppOnline = RadiusAccounting::whereNull('acct_stop_time')
                        ->whereExists(function($sub) use ($ownerId) {
                            $sub->selectRaw(1)->from('pppoe_users')
                                ->leftJoin('customer_services', 'pppoe_users.customer_service_id', '=', 'customer_services.id')
                                ->leftJoin('customers', 'customer_services.customer_id', '=', 'customers.id')
                                ->where(function($uq) {
                                    $uq->whereRaw('LOWER(pppoe_users.username) = LOWER(radius_accounting.username)')
                                       ->orWhereRaw('LOWER(radius_accounting.username) LIKE LOWER(pppoe_users.username) || \'@%\'')
                                       ->orWhereRaw('LOWER(pppoe_users.username) LIKE LOWER(radius_accounting.username) || \'@%\'');
                                })
                                ->whereNull('pppoe_users.deleted_at')
                                ->where(function($fq) use ($ownerId) {
                                    $fq->where('pppoe_users.reseller_id', $ownerId)
                                       ->orWhere('customers.reseller_id', $ownerId)
                                       ->orWhere('customers.created_by', $ownerId)
                                       ->orWhere('customer_services.reseller_id', $ownerId);
                                });
                        })->count();
                }

                $hotspotOnline = HotspotActiveSession::whereExists(function ($sub) use ($ownerId) {
                    $sub->selectRaw(1)->from('hotspot_users')
                        ->leftJoin('customer_services', 'hotspot_users.customer_service_id', '=', 'customer_services.id')
                        ->leftJoin('customers', 'customer_services.customer_id', '=', 'customers.id')
                        ->where(function($uq) {
                            $uq->whereRaw('LOWER(hotspot_users.username) = LOWER(hotspot_active_sessions.user)')
                               ->orWhereRaw('LOWER(hotspot_users.username) LIKE LOWER(hotspot_active_sessions.user) || \'@%\'');
                        })
                        ->whereNull('hotspot_users.deleted_at')
                        ->where(function($fq) use ($ownerId) {
                            $fq->where('hotspot_users.reseller_id', $ownerId)
                               ->orWhere('customers.reseller_id', $ownerId)
                               ->orWhere('customers.created_by', $ownerId)
                               ->orWhere('customer_services.reseller_id', $ownerId);
                        });
                })->count();
            } else {
                $hotspotUserCount = HotspotUser::count();
                $pppoeUserCount = PPPoEUser::count();
                $hotspotOnline = HotspotActiveSession::count();
                $pppOnline = PppActiveSession::count() ?: RadiusAccounting::whereNull('acct_stop_time')->count();
            }
        } catch (\Exception $e) {
            $hotspotUserCount = 0;
            $pppoeUserCount = 0;
            $hotspotOnline = 0;
            $pppOnline = 0;
        }

        // Vouchers
        $voucherQuery = Voucher::query();
        if ($ownerId) {
            $voucherQuery->where(function ($q) use ($ownerId) {
                $q->where('reseller_id', $ownerId)->orWhere('created_by', $ownerId);
            });
        }

        $totalVoucher = (clone $voucherQuery)->count();
        $vcCreatedToday = (clone $voucherQuery)->whereDate('created_at', today())->count();
        $expVoucher = (clone $voucherQuery)->where('status', 'expired')->count();

        // Active voucher sessions
        $activeSessionQuery = HotspotActiveSession::whereHas('voucher', function ($q) use ($ownerId) {
            if ($ownerId) {
                $q->where(function ($sub) use ($ownerId) {
                    $sub->where('reseller_id', $ownerId)->orWhere('created_by', $ownerId);
                });
            }
        });
        $vcLoginToday = $activeSessionQuery->count();
        
        // Suspended Customers
        $customerSuspendedQuery = Customer::where('status', 'suspended');
        if ($ownerId) {
            $customerSuspendedQuery->where($customerFilter);
        }
        $expCustomer = $customerSuspendedQuery->count();

        $this->mixData = [
            'income_hari_ini' => $incomeHariIni,
            'invoice_data_tagihan' => $invoiceDataTagihan,
            'ppp_online' => $pppOnline,
            'hotspot_online' => $hotspotOnline,
            'hotspot_user' => $hotspotUserCount,
            'pppoe_user' => $pppoeUserCount,
            'total_voucher' => $totalVoucher,
            'vc_created_today' => $vcCreatedToday,
            'vc_login_today' => $vcLoginToday,
            'exp_voucher' => $expVoucher,
            'exp_customer' => $expCustomer,
            'jatuh_tempo' => $jatuhTempo,
        ];
        
        // Tables
        $recentIncomesQuery = Payment::with(['customer', 'invoices'])
            ->whereIn('status', ['paid', 'success']);
        if ($ownerId) {
            $recentIncomesQuery->where(function ($q) use ($ownerId, $customerFilter) {
                $q->where('reseller_id', $ownerId)->orWhereHas('customer', $customerFilter);
            });
        }
        $this->recentIncomes = $recentIncomesQuery->latest('paid_at')->latest('created_at')->take(10)->get();
            
        $recentInvoicesQuery = Invoice::with('customer')->where('status', 'unpaid');
        if ($ownerId) {
            $recentInvoicesQuery->where(function ($q) use ($ownerId, $customerFilter) {
                $q->where('reseller_id', $ownerId)->orWhereHas('customer', $customerFilter);
            });
        }
        $this->recentInvoices = $recentInvoicesQuery->latest()->take(10)->get();
    }

    public function render()
    {
        return view('livewire.reseller-portal.dashboard');
    }
}