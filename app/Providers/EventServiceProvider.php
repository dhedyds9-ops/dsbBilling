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
use Src\Domain\Billing\Events\InvoiceCreatedEvent;
use Src\Domain\Billing\Events\InvoicePaidEvent;
use Src\Domain\Billing\Events\InvoiceOverdueEvent;
use Src\Domain\Billing\Events\PaymentReceivedEvent;
use Src\Domain\Billing\Events\PaymentVerifiedEvent;
use Src\Domain\Billing\Events\SubscriptionCreatedEvent;
use App\Events\ISP\InternetPackageSaved;
use App\Events\ISP\PPPoEUserStatusChangedEvent;
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
use App\Listeners\Billing\InvoiceCreatedListener;
use App\Listeners\Billing\InvoicePaidListener;
use App\Listeners\Billing\InvoiceOverdueListener;
use App\Listeners\Billing\PaymentReceivedListener;
use App\Listeners\Billing\PaymentVerifiedListener;
use App\Listeners\Billing\SubscriptionCreatedListener;
use App\Listeners\ISP\SyncInternetPackageToRadius;
use App\Listeners\ISP\SendPPPoEUserStatusChangedNotification;
use App\Listeners\ISP\FiberServiceProvisioningListener;
use App\Listeners\ISP\FiberServiceSuspensionListener;

class EventServiceProvider extends ServiceProvider
{
    protected $listen = [
        // ==================== PROVISIONING EVENTS (Src\Domain SSOT) ====================
        ServiceActivatedEvent::class => [
            FiberServiceProvisioningListener::class,
        ],
        ServiceReactivatedEvent::class => [
            FiberServiceProvisioningListener::class,
        ],
        ServiceSuspendedEvent::class => [
            FiberServiceSuspensionListener::class,
        ],
        ServiceTerminatedEvent::class => [
            FiberServiceSuspensionListener::class,
        ],

        InvoiceCreatedEvent::class => [
            InvoiceCreatedListener::class,
        ],
        InvoicePaidEvent::class => [
            InvoicePaidListener::class,
        ],
        InvoiceOverdueEvent::class => [
            InvoiceOverdueListener::class,
        ],
        PaymentReceivedEvent::class => [
            PaymentReceivedListener::class,
        ],
        PaymentVerifiedEvent::class => [
            PaymentVerifiedListener::class,
        ],
        SubscriptionCreatedEvent::class => [
            SubscriptionCreatedListener::class,
        ],

        // ==================== ISP LEGACY EVENTS (App\Events — TODO: migrate ke Src\Domain\ISP\Events) ====================
        InternetPackageSaved::class => [
            SyncInternetPackageToRadius::class,
        ],
        PPPoEUserStatusChangedEvent::class => [
            SendPPPoEUserStatusChangedNotification::class,
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
