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
        $ownerId = Auth::id();
        $customerFilter = function ($q) use ($ownerId) {
            $q->where('reseller_id', $ownerId)->orWhere('created_by', $ownerId);
        };

        // Load recent payments for reseller's customers
        $payments = \App\Models\Payment\Payment::with(['customer', 'invoices'])
            ->whereHas('customer', $customerFilter)
            ->whereIn('status', ['paid', 'success'])
            ->latest('paid_at')
            ->take(20)
            ->get();
            
        $logs = [];
        foreach ($payments as $payment) {
            $customer = $payment->customer;
            if ($customer) {
                $type = $customer->service_type ?? 'hotspot';
                $timeAgo = $payment->paid_at ? $payment->paid_at->diffForHumans() : Carbon::parse($payment->created_at)->diffForHumans();
                $id = $customer->customer_id;
                $name = $customer->name;
                
                $logs[] = [
                    'time' => $timeAgo,
                    'message' => "customer {$type} : {$id} # ( {$name} ) langganan diperpanjang",
                ];
            }
        }
        
        $this->activities = $logs;
    }

    public function loadDashboardData()
    {
        $ownerId = Auth::id();

        // Subquery to filter customers owned by this reseller
        $customerFilter = function ($q) use ($ownerId) {
            $q->where('reseller_id', $ownerId)->orWhere('created_by', $ownerId);
        };

        // Top Row
        $incomeHariIni = Payment::whereHas('customer', $customerFilter)
            ->whereIn('status', ['paid', 'success'])
            ->whereDate('paid_at', today())
            ->sum('amount');
            
        if ($incomeHariIni == 0) {
            $incomeHariIni = Payment::whereHas('customer', $customerFilter)
                ->where('status', 'paid')
                ->whereDate('created_at', today())
                ->sum('amount');
        }

        $invoiceDataTagihan = Invoice::whereHas('customer', $customerFilter)
            ->where('status', 'unpaid')
            ->count();

        // Count Hotspot and PPPoE users correctly tied to this reseller
        try {
            $hotspotUserCount = HotspotUser::where('reseller_id', $ownerId)->orWhere('created_by', $ownerId)->count();
            $pppoeUserCount = PPPoEUser::where('reseller_id', $ownerId)->orWhere('created_by', $ownerId)->count();
            
            // Fix Isolation Leak: Only count active voucher sessions created by this reseller
            $hotspotOnline = HotspotActiveSession::whereHas('hotspotUser', function ($q) use ($ownerId) {
                $q->where('reseller_id', $ownerId)->orWhere('created_by', $ownerId);
            })->count();
            
            $pppOnline = PppActiveSession::whereHas('pppoeUser', function ($q) use ($ownerId) {
                $q->where('reseller_id', $ownerId)->orWhere('created_by', $ownerId);
            })->count();
        } catch (\Exception $e) {
            $hotspotUserCount = 0;
            $pppoeUserCount = 0;
            $hotspotOnline = 0;
            $pppOnline = 0;
        }

        $totalVoucher = Voucher::where('created_by', $ownerId)->count();
        $vcCreatedToday = Voucher::where('created_by', $ownerId)->whereDate('created_at', today())->count();
        
        // Fix Data Leakage: Restrict active session counts to this reseller's vouchers
        $vcLoginToday = \App\Models\ISP\HotspotActiveSession::whereHas('voucher', function($q) use ($ownerId) {
            $q->where('created_by', $ownerId);
        })->count();
        
        $expVoucher = Voucher::where('created_by', $ownerId)->where('status', 'expired')->count();
        $expCustomer = Customer::where($customerFilter)->where('status', 'suspended')->count();
        
        $jatuhTempo = Invoice::whereHas('customer', $customerFilter)
            ->where('status', 'unpaid')
            ->whereDate('due_date', '<', today())
            ->count();

        $this->mixData = [
            'income_hari_ini' => $incomeHariIni,
            'invoice_data_tagihan' => $invoiceDataTagihan,
            'ppp_online' => $pppOnline,
            'hotspot_online' => $hotspotOnline,
            
            'hotspot_user' => $hotspotUserCount,
            'pppoe_user' => $pppoeUserCount,
            'vpn_user' => 0,
            'total_voucher' => $totalVoucher,
            'vc_created_today' => $vcCreatedToday,
            'vc_login_today' => $vcLoginToday,
            'exp_voucher' => $expVoucher,
            'exp_customer' => $expCustomer,
            
            'jatuh_tempo' => $jatuhTempo,
        ];
        
        // Tables
        $this->recentIncomes = Payment::with(['customer', 'invoices'])
            ->whereHas('customer', $customerFilter)
            ->whereIn('status', ['paid', 'success'])
            ->latest('paid_at')
            ->take(10)
            ->get();
            
        $this->recentInvoices = Invoice::with('customer')
            ->whereHas('customer', $customerFilter)
            ->where('status', 'unpaid')
            ->latest()
            ->take(10)
            ->get();
    }

    public function render()
    {
        return view('livewire.reseller-portal.dashboard');
    }
}