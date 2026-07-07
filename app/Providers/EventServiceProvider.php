<?php

namespace App\Providers;

use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;
use Src\Domain\Provisioning\Events\ServiceInstanceCreatedEvent;
use Src\Domain\Provisioning\Events\ResourcesReservedEvent;
use Src\Domain\Provisioning\Events\ResourceAllocatedEvent;
use Src\Domain\Provisioning\Events\DeviceAssignedEvent;
use Src\Domain\Provisioning\Events\VlanAllocatedEvent;
use Src\Domain\Provisioning\Events\IpAllocatedEvent;
use Src\Domain\Provisioning\Events\QueueAllocatedEvent;
use Src\Domain\Provisioning\Events\RouterProvisionedEvent;
use Src\Domain\Provisioning\Events\RadiusProvisionedEvent;
use Src\Domain\Provisioning\Events\OnuProvisionedEvent;
use Src\Domain\Provisioning\Events\ProvisioningVerifiedEvent;
use Src\Domain\Provisioning\Events\ProvisioningFailedEvent;
use Src\Domain\Provisioning\Events\ProvisioningCompletedEvent;
use Src\Domain\Provisioning\Events\ProvisionStartedEvent;
use Src\Domain\Provisioning\Events\CapacityExceededEvent;
use Src\Domain\Provisioning\Events\ResourcesReleasedEvent;
use Src\Domain\Provisioning\Events\RollbackStartedEvent;
use Src\Domain\Provisioning\Events\RollbackCompletedEvent;
use Src\Domain\Customer\Events\CustomerServiceCreatedEvent;
use Src\Domain\Customer\Events\ServiceActivatedEvent;
use Src\Domain\Customer\Events\ServiceSuspendedEvent;
use Src\Domain\Customer\Events\ServiceReactivatedEvent;
use Src\Domain\Customer\Events\ServiceTerminatedEvent;
use Src\Domain\AAA\Events\PPPoEUserCreatedEvent;
use Src\Domain\AAA\Events\PPPoEUserSuspendedEvent;
use Src\Domain\Billing\Events\InvoiceCreatedEvent;
use Src\Domain\Billing\Events\SubscriptionCreatedEvent;
use App\Events\ISP\InternetPackageSaved;
use App\Listeners\Provisioning\ServiceInstanceCreatedListener;
use App\Listeners\Provisioning\ResourcesReservedListener;
use App\Listeners\Provisioning\DeviceAssignedListener;
use App\Listeners\Provisioning\VlanAllocatedListener;
use App\Listeners\Provisioning\IpAllocatedListener;
use App\Listeners\Provisioning\QueueAllocatedListener;
use App\Listeners\Provisioning\RouterProvisionedListener;
use App\Listeners\Provisioning\RadiusProvisionedListener;
use App\Listeners\Provisioning\OnuProvisionedListener;
use App\Listeners\Provisioning\ProvisioningVerifiedListener;
use App\Listeners\Provisioning\ProvisioningFailedListener;
use App\Listeners\AAA\PPPoEUserCreatedListener;
use App\Listeners\AAA\PPPoEUserSuspendedListener;
use App\Listeners\Billing\InvoiceCreatedListener;
use App\Listeners\Billing\SubscriptionCreatedListener;
use App\Listeners\ISP\SyncInternetPackageToRadius;

class EventServiceProvider extends ServiceProvider
{
    protected $listen = [
        ServiceInstanceCreatedEvent::class => [
            ServiceInstanceCreatedListener::class,
        ],
        ResourcesReservedEvent::class => [
            ResourcesReservedListener::class,
        ],
        DeviceAssignedEvent::class => [
            DeviceAssignedListener::class,
        ],
        VlanAllocatedEvent::class => [
            VlanAllocatedListener::class,
        ],
        IpAllocatedEvent::class => [
            IpAllocatedListener::class,
        ],
        QueueAllocatedEvent::class => [
            QueueAllocatedListener::class,
        ],
        RouterProvisionedEvent::class => [
            RouterProvisionedListener::class,
        ],
        RadiusProvisionedEvent::class => [
            RadiusProvisionedListener::class,
        ],
        OnuProvisionedEvent::class => [
            OnuProvisionedListener::class,
        ],
        ProvisioningVerifiedEvent::class => [
            ProvisioningVerifiedListener::class,
        ],
        ProvisioningFailedEvent::class => [
            ProvisioningFailedListener::class,
        ],
        PPPoEUserCreatedEvent::class => [
            PPPoEUserCreatedListener::class,
        ],
        PPPoEUserSuspendedEvent::class => [
            PPPoEUserSuspendedListener::class,
        ],
        InvoiceCreatedEvent::class => [
            InvoiceCreatedListener::class,
        ],
        SubscriptionCreatedEvent::class => [
            SubscriptionCreatedListener::class,
        ],
        InternetPackageSaved::class => [
            SyncInternetPackageToRadius::class,
        ],
    ];

    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        //
    }
}
