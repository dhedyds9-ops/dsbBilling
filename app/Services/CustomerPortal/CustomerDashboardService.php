<?php

namespace App\Services\CustomerPortal;

use App\Models\Customer\CustomerService;
use App\Models\AAA\PPPoEUser;
use App\Models\Billing\Invoice;
use Illuminate\Support\Facades\Auth;
use Src\Domain\Monitoring\AlarmSeverity;

class CustomerDashboardService
{
    public function getDashboardData(int $customerId): array
    {
        $customerServices = CustomerService::with(['serviceInstance', 'contract'])->where('customer_id', $customerId)->get();
        $pppoeUser = PPPoEUser::with(['customerService'])->where('customer_service_id', $customerServices->first()?->id)->first();
        $activeInvoices = Invoice::where('customer_id', $customerId)
            ->where('status', '!=', 'paid')
            ->get();
        $recentInvoices = Invoice::where('customer_id', $customerId)
            ->latest()
            ->limit(5)
            ->get();

        return [
            'customer_services' => $customerServices,
            'pppoe_user' => $pppoeUser,
            'active_invoices' => $activeInvoices,
            'recent_invoices' => $recentInvoices,
            'internet_status' => $this->determineInternetStatus($pppoeUser),
            'total_outstanding' => $activeInvoices->sum('total_amount'),
        ];
    }

    private function determineInternetStatus(?PPPoEUser $pppoeUser): array
    {
        if (!$pppoeUser) {
            return [
                'status' => 'unknown',
                'message' => 'Layanan tidak ditemukan',
            ];
        }

        return match ($pppoeUser->status) {
            'active' => [
                'status' => 'online',
                'message' => 'Internet aktif',
                'severity' => AlarmSeverity::OK->value,
            ],
            'suspended' => [
                'status' => 'offline',
                'message' => 'Internet ditangguhkan',
                'severity' => AlarmSeverity::WARNING->value,
            ],
            default => [
                'status' => 'unknown',
                'message' => 'Status tidak diketahui',
                'severity' => AlarmSeverity::INFO->value,
            ],
        };
    }
}

