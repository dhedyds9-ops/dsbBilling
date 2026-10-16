<?php

namespace App\Livewire\ResellerPortal\Reports;

use App\Livewire\AdminComponent;
use App\Models\Payment\Payment;
use App\Models\ISP\Voucher;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class Revenue extends AdminComponent
{
    public string $filterType = 'all'; // all, pelanggan, voucher
    public $selectedMonth;
    public $selectedYear;

    public function mount()
    {
        parent::mount();
        $this->activeModule = 'reseller-portal';
        $this->activePage = 'reseller-portal.reports.revenue';
        
        $this->selectedMonth = (int) now()->month;
        $this->selectedYear = (int) now()->year;

        $this->breadcrumbs = [
            ['label' => 'Reseller Portal', 'url' => '#'],
            ['label' => 'Laporan', 'url' => '#'],
            ['label' => 'Pendapatan', 'url' => route('reseller-portal.reports.revenue')],
        ];
    }

    public function setFilterType(string $type)
    {
        $this->filterType = in_array($type, ['all', 'pelanggan', 'voucher']) ? $type : 'all';
    }

    public function render()
    {
        $user = Auth::user();
        $resellerId = $user->getEffectiveResellerId() ?? ($user->hasRole('reseller') ? $user->id : null);
        $month = (int) ($this->selectedMonth ?: now()->month);
        $year = (int) ($this->selectedYear ?: now()->year);

        $customerFilter = function ($cq) use ($resellerId) {
            $cq->withoutGlobalScopes()->where(function($sq) use ($resellerId) {
                $sq->where('customers.reseller_id', $resellerId)->orWhere('customers.created_by', $resellerId);
            });
        };

        // 1. Voucher orders online payment IDs
        $voucherPaymentIds = [];
        try {
            $voucherPaymentIds = DB::table('invoice_payment')
                ->join('voucher_orders', 'invoice_payment.invoice_id', '=', 'voucher_orders.invoice_id')
                ->pluck('invoice_payment.payment_id')
                ->toArray();
        } catch (\Exception $e) {
            $voucherPaymentIds = [];
        }

        // 2. Base query for payments
        $paymentQuery = Payment::withoutGlobalScopes()
            ->whereIn('status', ['success', 'approved', 'paid'])
            ->where('gateway', '!=', 'reseller_topup')
            ->where(function($q) use ($month, $year) {
                $q->where(function($sq) use ($month, $year) {
                    $sq->whereMonth('paid_at', $month)->whereYear('paid_at', $year);
                })->orWhere(function($sq) use ($month, $year) {
                    $sq->whereNull('paid_at')->whereMonth('created_at', $month)->whereYear('created_at', $year);
                });
            });

        if ($resellerId) {
            $paymentQuery->where(function ($q) use ($resellerId, $customerFilter) {
                $q->where('reseller_id', $resellerId)
                  ->orWhereHas('customer', $customerFilter);
            });
        }

        // Customer Subscription Payments
        $customerPaymentQuery = (clone $paymentQuery);
        if (!empty($voucherPaymentIds)) {
            $customerPaymentQuery->whereNotIn('payments.id', $voucherPaymentIds);
        }
        $pelangganRevenue = (clone $customerPaymentQuery)->sum('amount');
        $pelangganCount = (clone $customerPaymentQuery)->count();

        // Online Voucher Payments
        $voucherOnlineRevenue = 0;
        $voucherOnlineCount = 0;
        if (!empty($voucherPaymentIds)) {
            $onlineVoucherPaymentQuery = (clone $paymentQuery)->whereIn('payments.id', $voucherPaymentIds);
            $voucherOnlineRevenue = (clone $onlineVoucherPaymentQuery)->sum('amount');
            $voucherOnlineCount = (clone $onlineVoucherPaymentQuery)->count();
        }

        // Offline / Active Vouchers
        $voucherOfflineRevenue = 0;
        $voucherOfflineCount = 0;
        try {
            $activeVoucherQuery = Voucher::withoutGlobalScopes()
                ->leftJoin('service_profiles', 'vouchers.service_profile_id', '=', 'service_profiles.id')
                ->where(function($q) {
                    $q->whereIn('vouchers.status', ['used', 'active'])
                      ->orWhereNotNull('vouchers.activated_at');
                })
                ->where(function($q) use ($month, $year) {
                    $q->where(function($sq) use ($month, $year) {
                        $sq->whereMonth('vouchers.activated_at', $month)->whereYear('vouchers.activated_at', $year);
                    })->orWhere(function($sq) use ($month, $year) {
                        $sq->whereNull('vouchers.activated_at')->whereMonth('vouchers.created_at', $month)->whereYear('vouchers.created_at', $year);
                    });
                });

            if ($resellerId) {
                $activeVoucherQuery->where(function($q) use ($resellerId) {
                    $q->where('vouchers.reseller_id', $resellerId)
                      ->orWhere('vouchers.created_by', $resellerId);
                });
            }

            $voucherPriceExpr = "COALESCE(NULLIF(vouchers.fee_seller, 0), NULLIF(service_profiles.reseller_price, 0), service_profiles.base_price, 0)";
            $voucherOfflineRevenue = (clone $activeVoucherQuery)->selectRaw("SUM({$voucherPriceExpr}) as total")->value('total') ?? 0;
            $voucherOfflineCount = (clone $activeVoucherQuery)->count();
        } catch (\Exception $e) {
            $voucherOfflineRevenue = 0;
            $voucherOfflineCount = 0;
        }

        $voucherRevenue = $voucherOnlineRevenue + $voucherOfflineRevenue;
        $voucherCount = $voucherOnlineCount + $voucherOfflineCount;

        $totalRevenue = $pelangganRevenue + $voucherRevenue;
        $transactionCount = $pelangganCount + $voucherCount;
        $averagePayment = $transactionCount > 0 ? $totalRevenue / $transactionCount : 0;

        // Transaction list based on filter
        $listQuery = (clone $paymentQuery)->with(['customer' => fn($q) => $q->withoutGlobalScopes()]);

        if ($this->filterType === 'pelanggan' && !empty($voucherPaymentIds)) {
            $listQuery->whereNotIn('payments.id', $voucherPaymentIds);
        } elseif ($this->filterType === 'voucher' && !empty($voucherPaymentIds)) {
            $listQuery->whereIn('payments.id', $voucherPaymentIds);
        }

        $recentPayments = $listQuery->orderBy('created_at', 'desc')->take(20)->get();

        // Also fetch recent vouchers if filter is voucher or all
        $recentVouchers = collect();
        if ($this->filterType === 'voucher' || $this->filterType === 'all') {
            try {
                $voucherListQ = Voucher::withoutGlobalScopes()
                    ->with('serviceProfile')
                    ->where(function($q) {
                        $q->whereIn('status', ['used', 'active'])
                          ->orWhereNotNull('activated_at');
                    })
                    ->where(function($q) use ($month, $year) {
                        $q->where(function($sq) use ($month, $year) {
                            $sq->whereMonth('activated_at', $month)->whereYear('activated_at', $year);
                        })->orWhere(function($sq) use ($month, $year) {
                            $sq->whereNull('activated_at')->whereMonth('created_at', $month)->whereYear('created_at', $year);
                        });
                    });

                if ($resellerId) {
                    $voucherListQ->where(function($q) use ($resellerId) {
                        $q->where('reseller_id', $resellerId)->orWhere('created_by', $resellerId);
                    });
                }
                $recentVouchers = $voucherListQ->latest('activated_at')->latest('created_at')->take(10)->get();
            } catch (\Exception $e) {
                $recentVouchers = collect();
            }
        }

        $monthDate = \Carbon\Carbon::createFromDate($year, $month, 1);

        return view('livewire.reseller-portal.reports.revenue', [
            'totalRevenue' => $totalRevenue,
            'pelangganRevenue' => $pelangganRevenue,
            'pelangganCount' => $pelangganCount,
            'voucherRevenue' => $voucherRevenue,
            'voucherCount' => $voucherCount,
            'transactionCount' => $transactionCount,
            'averagePayment' => $averagePayment,
            'recentPayments' => $recentPayments,
            'recentVouchers' => $recentVouchers,
            'voucherPaymentIds' => $voucherPaymentIds,
            'currentMonthName' => $monthDate->translatedFormat('F Y'),
        ]);
    }
}
