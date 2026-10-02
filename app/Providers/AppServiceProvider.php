<?php

namespace App\Providers;

use App\Repositories\CRM\CoverageCheckRepository;
use App\Repositories\CRM\CustomerActivationRepository;
use App\Repositories\CRM\InstallationChecklistRepository;
use App\Repositories\CRM\InstallationRepository;
use App\Repositories\CRM\LeadRepository;
use App\Repositories\CRM\MaterialUsageRepository;
use App\Repositories\CRM\ProspectRepository;
use App\Repositories\CRM\QualityControlRepository;
use App\Repositories\CRM\QuotationRepository;
use App\Repositories\CRM\SurveyRepository;
use App\Repositories\Provisioning\CapacityManagementRepository;
use App\Repositories\Provisioning\DeviceAssignmentRepository;
use App\Repositories\Provisioning\IpAllocationRepository;
use App\Repositories\Provisioning\PortReservationRepository;
use App\Repositories\Provisioning\ProvisionPipelineRepository;
use App\Repositories\Provisioning\QueueAllocationRepository;
use App\Repositories\Provisioning\ResourceAssignmentRepository;
use App\Repositories\Provisioning\ResourceReservationRepository;
use App\Repositories\Provisioning\ServiceInstanceRepository;
use App\Repositories\Provisioning\VlanAllocationRepository;
use App\Repositories\Billing\InvoiceItemRepository;
use App\Repositories\Billing\InvoiceRepository;
use App\Repositories\Billing\SubscriptionRepository;
use App\Repositories\Billing\BillingCycleRepository;
use App\Repositories\Workforce\AttendanceRepository;
use App\Repositories\Workforce\GPSHistoryRepository;
use App\Repositories\Workforce\GeofenceRepository;
use App\Repositories\Workforce\RouteHistoryRepository;
use App\Repositories\Workforce\RouteOptimizationRepository;
use App\Repositories\Workforce\QCInspectionRepository;
use App\Repositories\Workforce\QCChecklistRepository;
use App\Repositories\Workforce\QCResultRepository;
use App\Repositories\Workforce\QCApprovalRepository;
use App\Repositories\Workforce\SyncTaskRepository;
use App\Repositories\Workforce\SyncQueueRepository;
use App\Repositories\Workforce\SyncConflictRepository;
use App\Repositories\Workforce\SyncHistoryRepository;
use App\Repositories\GIS\GeoPointRepository;
use App\Repositories\GIS\GeoRouteRepository;
use App\Repositories\GIS\GeoPathRepository;
use App\Repositories\GIS\GeoPolygonRepository;
use App\Repositories\GIS\GeoAreaRepository;
use App\Repositories\GIS\CoverageAreaRepository;
use App\Repositories\GIS\ServiceAreaRepository;
use App\Repositories\GIS\MapLayerRepository;
use App\Repositories\GIS\CoordinateReferenceSystemRepository;
use App\Repositories\Voucher\EloquentVoucherRepository;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public array $bindings = [
        // CRM Repositories
                                                                                
        // Provisioning Repositories
                                                                                
        // Billing Repositories
                                
        // Workforce Repositories
                                                                                                                                                                                
        // Voucher Repositories
            ];

    public function register(): void
    {
        // Moved to InfrastructureServiceProvider
    }

    public function boot(): void
    {
        if ($this->app->environment('production')) {
            \Illuminate\Support\Facades\URL::forceScheme('https');
        }

        // Wire Laravel's native 'can' to our custom permission system
        \Illuminate\Support\Facades\Gate::before(function ($user, $ability) {
            if (method_exists($user, 'hasPermission') && $user->hasPermission($ability)) {
                return true;
            }
        });

        \Illuminate\Support\Facades\View::composer('layouts.admin', \App\View\Composers\AdminLayoutComposer::class);

        \Illuminate\Support\Facades\RateLimiter::for('radius.accounting', function (\Illuminate\Http\Request $request) {
            $nasIp = $request->input('nas_ip_address')
                ?? ($request->header('X-Forwarded-For') ? explode(',', (string)$request->header('X-Forwarded-For'))[0]
                : $request->ip());
            return [
                \Illuminate\Cache\RateLimiting\Limit::perMinute(1200)->by((string)$nasIp),
            ];
        });

        \Illuminate\Support\Facades\RateLimiter::for('radius.preauth', function (\Illuminate\Http\Request $request) {
            // Throttle per NAS IP (bukan per client IP) — FreeRADIUS mengirim dari satu NAS server
            // 300 req/menit sudah cukup untuk ISP dengan 100-200 user login bersamaan
            $nasIp = $request->input('nas_ip_address')
                ?? ($request->header('X-Forwarded-For') ? explode(',', (string)$request->header('X-Forwarded-For'))[0]
                : $request->ip());
            return [
                \Illuminate\Cache\RateLimiting\Limit::perMinute(300)->by((string)$nasIp),
            ];
        });
    }
}
