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
use App\Repositories\AAA\PPPoEUserRepository;
use App\Repositories\AAA\HotspotUserRepository;
use App\Repositories\AAA\RadiusAccountingRepository;
use App\Repositories\AAA\RadiusNasRepository;
use App\Repositories\AAA\VoucherPoolRepository;
use App\Repositories\AAA\VoucherRepository;
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
use App\Services\AAA\AAAService;
use Illuminate\Support\ServiceProvider;
use Src\Domain\CRM\CoverageCheckRepositoryInterface;
use Src\Domain\CRM\CustomerActivationRepositoryInterface;
use Src\Domain\CRM\InstallationChecklistRepositoryInterface;
use Src\Domain\CRM\InstallationRepositoryInterface;
use Src\Domain\CRM\LeadRepositoryInterface;
use Src\Domain\CRM\MaterialUsageRepositoryInterface;
use Src\Domain\CRM\ProspectRepositoryInterface;
use Src\Domain\CRM\QualityControlRepositoryInterface;
use Src\Domain\CRM\QuotationRepositoryInterface;
use Src\Domain\CRM\SurveyRepositoryInterface;
use Src\Domain\Provisioning\CapacityManagementRepositoryInterface;
use Src\Domain\Provisioning\DeviceAssignmentRepositoryInterface;
use Src\Domain\Provisioning\IPAllocationRepositoryInterface;
use Src\Domain\Provisioning\PortReservationRepositoryInterface;
use Src\Domain\Provisioning\QueueAllocationRepositoryInterface;
use Src\Domain\Provisioning\ResourceAssignmentRepositoryInterface;
use Src\Domain\Provisioning\ResourceReservationRepositoryInterface;
use Src\Domain\Provisioning\ServiceInstanceRepositoryInterface;
use Src\Domain\Provisioning\VLANAllocationRepositoryInterface;
use Src\Domain\AAA\AAAServiceInterface;
use Src\Domain\AAA\PPPoEUserRepositoryInterface;
use Src\Domain\AAA\HotspotUserRepositoryInterface;
use Src\Domain\AAA\RadiusAccountingRepositoryInterface;
use Src\Domain\AAA\RadiusNasRepositoryInterface;
use Src\Domain\AAA\VoucherPoolRepositoryInterface;
use Src\Domain\AAA\VoucherRepositoryInterface;
use Src\Domain\Billing\InvoiceRepositoryInterface;
use Src\Domain\Billing\InvoiceItemRepositoryInterface;
use Src\Domain\Billing\SubscriptionRepositoryInterface;
use Src\Domain\Billing\BillingCycleRepositoryInterface;
use Src\Domain\Workforce\Repositories\AttendanceRepositoryInterface;
use Src\Domain\Workforce\Repositories\GPSHistoryRepositoryInterface;
use Src\Domain\Workforce\Repositories\GeofenceRepositoryInterface;
use Src\Domain\Workforce\Repositories\RouteHistoryRepositoryInterface;
use Src\Domain\Workforce\Repositories\RouteOptimizationRepositoryInterface;
use Src\Domain\Workforce\Repositories\QCInspectionRepositoryInterface;
use Src\Domain\Workforce\Repositories\QCChecklistRepositoryInterface;
use Src\Domain\Workforce\Repositories\QCResultRepositoryInterface;
use Src\Domain\Workforce\Repositories\QCApprovalRepositoryInterface;
use Src\Domain\Workforce\Repositories\SyncTaskRepositoryInterface;
use Src\Domain\Workforce\Repositories\SyncQueueRepositoryInterface;
use Src\Domain\Workforce\Repositories\SyncConflictRepositoryInterface;
use Src\Domain\Workforce\Repositories\SyncHistoryRepositoryInterface;
use Src\Domain\GIS\Repositories\GeoPointRepositoryInterface;
use Src\Domain\GIS\Repositories\GeoRouteRepositoryInterface;
use Src\Domain\GIS\Repositories\GeoPathRepositoryInterface;
use Src\Domain\GIS\Repositories\GeoPolygonRepositoryInterface;
use Src\Domain\GIS\Repositories\GeoAreaRepositoryInterface;
use Src\Domain\GIS\Repositories\CoverageAreaRepositoryInterface;
use Src\Domain\GIS\Repositories\ServiceAreaRepositoryInterface;
use Src\Domain\GIS\Repositories\MapLayerRepositoryInterface;
use Src\Domain\GIS\Repositories\CoordinateReferenceSystemRepositoryInterface;

class AppServiceProvider extends ServiceProvider
{
    public array $bindings = [
        // CRM Repositories
        ProspectRepositoryInterface::class => ProspectRepository::class,
        SurveyRepositoryInterface::class => SurveyRepository::class,
        InstallationRepositoryInterface::class => InstallationRepository::class,
        LeadRepositoryInterface::class => LeadRepository::class,
        CoverageCheckRepositoryInterface::class => CoverageCheckRepository::class,
        QuotationRepositoryInterface::class => QuotationRepository::class,
        InstallationChecklistRepositoryInterface::class => InstallationChecklistRepository::class,
        MaterialUsageRepositoryInterface::class => MaterialUsageRepository::class,
        QualityControlRepositoryInterface::class => QualityControlRepository::class,
        CustomerActivationRepositoryInterface::class => CustomerActivationRepository::class,

        // Provisioning Repositories
        ServiceInstanceRepositoryInterface::class => ServiceInstanceRepository::class,
        ResourceReservationRepositoryInterface::class => ResourceReservationRepository::class,
        ResourceAssignmentRepositoryInterface::class => ResourceAssignmentRepository::class,
        CapacityManagementRepositoryInterface::class => CapacityManagementRepository::class,
        PortReservationRepositoryInterface::class => PortReservationRepository::class,
        VLANAllocationRepositoryInterface::class => VlanAllocationRepository::class,
        IPAllocationRepositoryInterface::class => IpAllocationRepository::class,
        QueueAllocationRepositoryInterface::class => QueueAllocationRepository::class,
        DeviceAssignmentRepositoryInterface::class => DeviceAssignmentRepository::class,
        ProvisionPipelineRepositoryInterface::class => ProvisionPipelineRepository::class,

        // AAA Services & Repositories
        AAAServiceInterface::class => AAAService::class,
        PPPoEUserRepositoryInterface::class => PPPoEUserRepository::class,
        HotspotUserRepositoryInterface::class => HotspotUserRepository::class,
        RadiusAccountingRepositoryInterface::class => RadiusAccountingRepository::class,
        RadiusNasRepositoryInterface::class => RadiusNasRepository::class,
        VoucherPoolRepositoryInterface::class => VoucherPoolRepository::class,
        VoucherRepositoryInterface::class => VoucherRepository::class,

        // Billing Repositories
        InvoiceRepositoryInterface::class => InvoiceRepository::class,
        InvoiceItemRepositoryInterface::class => InvoiceItemRepository::class,
        SubscriptionRepositoryInterface::class => SubscriptionRepository::class,
        BillingCycleRepositoryInterface::class => BillingCycleRepository::class,

        // Workforce Repositories
        GPSHistoryRepositoryInterface::class => GPSHistoryRepository::class,
        GeofenceRepositoryInterface::class => GeofenceRepository::class,
        AttendanceRepositoryInterface::class => AttendanceRepository::class,
        RouteHistoryRepositoryInterface::class => RouteHistoryRepository::class,
        RouteOptimizationRepositoryInterface::class => RouteOptimizationRepository::class,
        QCInspectionRepositoryInterface::class => QCInspectionRepository::class,
        QCChecklistRepositoryInterface::class => QCChecklistRepository::class,
        QCResultRepositoryInterface::class => QCResultRepository::class,
        QCApprovalRepositoryInterface::class => QCApprovalRepository::class,
        SyncTaskRepositoryInterface::class => SyncTaskRepository::class,
        SyncQueueRepositoryInterface::class => SyncQueueRepository::class,
        SyncConflictRepositoryInterface::class => SyncConflictRepository::class,
        SyncHistoryRepositoryInterface::class => SyncHistoryRepository::class,
        GeoPointRepositoryInterface::class => GeoPointRepository::class,
        GeoRouteRepositoryInterface::class => GeoRouteRepository::class,
        GeoPathRepositoryInterface::class => GeoPathRepository::class,
        GeoPolygonRepositoryInterface::class => GeoPolygonRepository::class,
        GeoAreaRepositoryInterface::class => GeoAreaRepository::class,
        CoverageAreaRepositoryInterface::class => CoverageAreaRepository::class,
        ServiceAreaRepositoryInterface::class => ServiceAreaRepository::class,
        MapLayerRepositoryInterface::class => MapLayerRepository::class,
        CoordinateReferenceSystemRepositoryInterface::class => CoordinateReferenceSystemRepository::class,
    ];

    public function register(): void
    {
        // Moved to InfrastructureServiceProvider
    }

    public function boot(): void
    {
        \Illuminate\Support\Facades\View::composer('layouts.admin', \App\View\Composers\AdminLayoutComposer::class);
    }
}
