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
        $customerServices = CustomerService::with(['service', 'serviceProfile', 'contract', 'pppoeUser', 'hotspotUser'])
            ->where('customer_id', $customerId)->get();

        // 1. Auto-heal jika customer_services belum terhubung tapi ada di pppoe_users / radius
        if ($customerServices->isEmpty()) {
            $customer = \App\Models\CRM\Customer::find($customerId);
            if ($customer) {
                // Coba cari di pppoe_users
                $orphanPppoe = PPPoEUser::where('username', $customer->code)
                    ->orWhere('username', 'like', $customer->code . '@%')
                    ->first();
                if ($orphanPppoe) {
                    $cs = CustomerService::create([
                        'uuid' => (string)\Illuminate\Support\Str::uuid(),
                        'customer_id' => $customerId,
                        'service_profile_id' => $orphanPppoe->service_profile_id,
                        'username' => $orphanPppoe->username,
                        'password' => $orphanPppoe->password,
                        'status' => $orphanPppoe->status ?: 'active',
                    ]);
                    $orphanPppoe->update(['customer_service_id' => $cs->id]);
                    $customerServices = collect([$cs]);
                }
            }
        }

        $primaryService = $customerServices->where('status', 'active')->first()
            ?? $customerServices->first();

        $pppoeUser = $primaryService?->pppoeUser
            ?? PPPoEUser::with(['customerService', 'serviceProfile'])
                ->where('customer_service_id', $primaryService?->id)->first();

        // Jika pppoeUser belum ketemu via customer_service_id, cari via username
        if (!$pppoeUser && $primaryService?->username) {
            $pppoeUser = PPPoEUser::with(['customerService', 'serviceProfile'])
                ->where('username', $primaryService->username)->first();
            if ($pppoeUser && empty($pppoeUser->customer_service_id)) {
                $pppoeUser->update(['customer_service_id' => $primaryService->id]);
            }
        }

        $hotspotUser = $primaryService?->hotspotUser
            ?? \App\Models\ISP\HotspotUser::with(['customerService'])
                ->where('customer_service_id', $primaryService?->id)->first();

        if (!$hotspotUser && $primaryService?->username) {
            $hotspotUser = \App\Models\ISP\HotspotUser::with(['customerService'])
                ->where('username', $primaryService->username)->first();
            if ($hotspotUser && empty($hotspotUser->customer_service_id)) {
                $hotspotUser->update(['customer_service_id' => $primaryService->id]);
            }
        }

        // Load serviceProfile for pppoeUser if not already loaded
        if ($pppoeUser && !$pppoeUser->relationLoaded('serviceProfile')) {
            $pppoeUser->load('serviceProfile');
        }

        // Resolusi service profile: dari pppoeUser, hotspotUser, atau customerService
        $serviceProfile = $pppoeUser?->serviceProfile
            ?? $primaryService?->serviceProfile
            ?? null;

        if (!$serviceProfile && $primaryService?->service_profile_id) {
            $serviceProfile = \App\Models\ISP\ServiceProfile::find($primaryService->service_profile_id);
        }

        $activeInvoices = Invoice::where('customer_id', $customerId)
            ->where('status', '!=', 'paid')
            ->whereRaw('total_amount > paid_amount')
            ->get();
        $recentInvoices = Invoice::where('customer_id', $customerId)
            ->latest()
            ->limit(5)
            ->get();

        // Hitung penggunaan bandwidth bulan ini dari RadiusAccounting
        $usageStats = $this->calculateUsageStats($pppoeUser, $hotspotUser);

        $subscription = \App\Models\Billing\Subscription::where('customer_id', $customerId)->where('status', 'active')->first();

        return [
            'next_billing_date' => $subscription ? $subscription->next_billing_date : null,
            'customer_services' => $customerServices,
            'primary_service' => $primaryService,
            'service_profile' => $serviceProfile,
            'pppoe_user' => $pppoeUser,
            'hotspot_user' => $hotspotUser,
            'active_invoices' => $activeInvoices,
            'recent_invoices' => $recentInvoices,
            'internet_status' => $this->determineInternetStatus($pppoeUser, $hotspotUser, $primaryService),
            'total_outstanding' => $activeInvoices->sum('total_amount') - $activeInvoices->sum('paid_amount'),
            'usage_stats' => $usageStats,
        ];
    }

    private function calculateUsageStats(?PPPoEUser $pppoeUser, $hotspotUser = null): array
    {
        if (!$pppoeUser && !$hotspotUser) {
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
        $startOfMonth = \Carbon\Carbon::now()->startOfMonth();
        $accountings = collect();
        if ($pppoeUser) {
            $accountings = RadiusAccounting::where('pppoe_user_id', $pppoeUser->id)
                ->where('acct_start_time', '>=', $startOfMonth)
                ->get();
        } elseif ($hotspotUser) {
            // Usually hotspot uses username
            $accountings = RadiusAccounting::where('username', $hotspotUser->username)
                ->where('acct_start_time', '>=', $startOfMonth)
                ->get();
        }

        $uploadBytes = $accountings->sum('acct_input_octets');
        $downloadBytes = $accountings->sum('acct_output_octets');
        $uploadMb = round($uploadBytes / 1024 / 1024, 2);
        $downloadMb = round($downloadBytes / 1024 / 1024, 2);
        $totalMb = $uploadMb + $downloadMb;

        // Hitung hari aktif
        $activeDays = 0;
        if ($pppoeUser && $pppoeUser->activated_at) {
            $activeDays = $pppoeUser->activated_at->diffInDays(\Carbon\Carbon::now());
        } elseif ($hotspotUser && $hotspotUser->activated_at) {
            $activeDays = $hotspotUser->activated_at->diffInDays(\Carbon\Carbon::now());
        }

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

    private function determineInternetStatus(?PPPoEUser $pppoeUser, $hotspotUser = null, ?CustomerService $primaryService = null): array
    {
        $serviceStatus = $primaryService?->status ?? $pppoeUser?->status ?? $hotspotUser?->status ?? null;
        $username = $pppoeUser?->username ?? $hotspotUser?->username ?? $primaryService?->username ?? null;

        // Cek koneksi online ke MikroTik / Radius (PPP Active Sessions & Accounting)
        $isOnline = false;
        if ($username) {
            $rawUname = (string)$username;
            $stripped = str_contains($rawUname, '@') ? explode('@', $rawUname)[0] : $rawUname;

            $isOnline = \App\Models\ISP\PppActiveSession::where('name', $rawUname)
                ->orWhere('name', $stripped)
                ->exists()
                || \App\Models\ISP\RadiusAccounting::where(function($q) use ($rawUname, $stripped) {
                    $q->where('username', $rawUname)->orWhere('username', $stripped);
                })->whereNull('acct_stop_time')->exists();
        }

        if (!$isOnline && $pppoeUser) {
            $isOnline = (bool)($pppoeUser->is_online ?? false);
        }

        // 1. Jika status layanan ditangguhkan / suspended
        if ($serviceStatus === 'suspended') {
            return [
                'status' => 'offline',
                'is_online' => false,
                'message' => 'Layanan ditangguhkan (Isolir)',
                'severity' => AlarmSeverity::WARNING->value,
            ];
        }

        // 2. Jika status layanan aktif (baik di customer_services maupun pppoeUser)
        if ($serviceStatus === 'active' || $isOnline) {
            return [
                'status' => 'online', // badge 'Aktif'
                'is_online' => $isOnline,
                'message' => $isOnline ? 'Internet Online & Aktif' : 'Layanan Aktif (Router Belum Terhubung)',
                'severity' => AlarmSeverity::OK->value,
            ];
        }

        if ($serviceStatus) {
            return [
                'status' => $serviceStatus,
                'is_online' => $isOnline,
                'message' => 'Status Layanan: ' . ucfirst($serviceStatus),
                'severity' => AlarmSeverity::INFO->value,
            ];
        }

        return [
            'status' => 'unknown',
            'is_online' => false,
            'message' => 'Layanan tidak ditemukan',
            'severity' => AlarmSeverity::INFO->value,
        ];
    }
}


