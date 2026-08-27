<?php

namespace App\Services\CustomerPortal;

use App\Models\Customer\CustomerService;
use App\Models\ISP\PPPoEUser;
use App\Models\ISP\RadiusAccounting;
use App\Models\Billing\Invoice;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Carbon;
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

        // Hitung penggunaan bandwidth bulan ini dari RadiusAccounting
        $usageStats = $this->calculateUsageStats($pppoeUser);

        return [
            'customer_services' => $customerServices,
            'pppoe_user' => $pppoeUser,
            'active_invoices' => $activeInvoices,
            'recent_invoices' => $recentInvoices,
            'internet_status' => $this->determineInternetStatus($pppoeUser),
            'total_outstanding' => $activeInvoices->sum('total_amount'),
            'usage_stats' => $usageStats,
        ];
    }

    private function calculateUsageStats(?PPPoEUser $pppoeUser): array
    {
        if (!$pppoeUser) {
            return [
                'upload_mb' => 0,
                'download_mb' => 0,
                'total_mb' => 0,
                'quota_mb' => null,
                'remaining_mb' => null,
                'active_days' => 0,
                'validity_days' => null,
            ];
        }

        // Ambil data akuntansi bulan ini
        $startOfMonth = Carbon::now()->startOfMonth();
        $accountings = RadiusAccounting::where('pppoe_user_id', $pppoeUser->id)
            ->where('acct_start_time', '>=', $startOfMonth)
            ->get();

        $uploadBytes = $accountings->sum('acct_input_octets');
        $downloadBytes = $accountings->sum('acct_output_octets');
        $uploadMb = round($uploadBytes / 1024 / 1024, 2);
        $downloadMb = round($downloadBytes / 1024 / 1024, 2);
        $totalMb = $uploadMb + $downloadMb;

        // Hitung hari aktif
        $activeDays = $pppoeUser->activated_at ? $pppoeUser->activated_at->diffInDays(Carbon::now()) : 0;

        return [
            'upload_mb' => $uploadMb,
            'download_mb' => $downloadMb,
            'total_mb' => $totalMb,
            'quota_mb' => null, // Bisa diisi nanti dari InternetPackage
            'remaining_mb' => null,
            'active_days' => $activeDays,
            'validity_days' => null,
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

