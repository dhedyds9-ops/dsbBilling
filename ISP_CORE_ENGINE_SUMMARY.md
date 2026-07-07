# ISP Core Engine - Summary

## Overview
The ISP Core Engine is built using **Domain-Driven Design (DDD)** and **Event-Driven Architecture (EDA)**. It provides a complete foundation for an ISP Billing system with modular, extensible components.

## Folder Structure
```
src/
├── Domain/                      # Core business logic
│   ├── SharedKernel/            # Shared building blocks
│   │   ├── Events/
│   │   ├── ValueObjects/
│   │   ├── Aggregates/
│   │   └── Repositories/
│   ├── ServiceCatalog/          # Service Catalog domain
│   ├── ProductCatalog/          # Product Catalog domain
│   ├── Customer/                # Customer domain
│   │   ├── Contract/
│   │   └── Service/
│   ├── Billing/                 # Billing domain
│   ├── Payment/                 # Payment domain
│   ├── AAA/                     # AAA (Authentication, Authorization, Accounting) domain
│   ├── Provisioning/            # Provisioning domain
│   │   ├── PPPoE/
│   │   ├── Hotspot/
│   │   ├── Voucher/
│   │   ├── DHCP/
│   │   ├── StaticIP/
│   │   └── Radius/
│   ├── Notification/            # Notification domain
│   ├── Workflow/                # Workflow domain
│   ├── Scheduler/               # Scheduler domain
│   ├── Automation/              # Automation domain
│   ├── Integration/             # Integration domain
│   │   └── Adapter/
│   └── Plugin/                  # Plugin domain
├── Application/                 # Use cases and application services
│   ├── Services/
│   ├── Commands/
│   ├── Queries/
│   └── Events/
├── Infrastructure/              # Technical implementations
│   ├── Persistence/
│   ├── Adapters/
│   ├── Jobs/
│   ├── Listeners/
│   ├── Queues/
│   └── Schedulers/
└── Presentation/                # UI/API layer
```

## Domain Models

### SharedKernel
- **AggregateRoot**: Base class for all aggregates, provides domain event recording
- **DomainEvent**: Base class for all domain events
- **ValueObjects**:
  - `Uuid`: UUID value object
  - `Money`: Money value object with currency support
- **RepositoryInterface**: Base interface for all repositories
- **EventDispatcherInterface**: Interface for event dispatching

### ServiceCatalog
- **ServiceCatalog**: Represents a catalog of services (e.g., "Internet", "VoIP")
- **Service**: Represents an individual service (PPPoE, Hotspot, StaticIP, Voucher)
  - `ServiceType`: Enum for service types (PPPOE, HOTSPOT, STATIC_IP, VOUCHER)
- **Repositories**:
  - `ServiceCatalogRepositoryInterface`
  - `ServiceRepositoryInterface`

### ProductCatalog
- **Product**: Represents a product (Hardware, Software, Service Addon)
  - `ProductType`: Enum for product types (HARDWARE, SOFTWARE, SERVICE_ADDON)
- **Repositories**:
  - `ProductRepositoryInterface`

### Customer
- **Contract**: Customer contract with services
  - `ContractStatus`: Enum (DRAFT, ACTIVE, SUSPENDED, TERMINATED, EXPIRED)
- **CustomerService**: Customer's active service
  - `CustomerServiceStatus`: Enum (PENDING, ACTIVE, SUSPENDED, DISCONNECTED)
- **Repositories**:
  - `ContractRepositoryInterface`
  - `CustomerServiceRepositoryInterface`

### Billing
- **Invoice**: Invoice for customer
  - `InvoiceStatus`: Enum (DRAFT, UNPAID, PARTIAL, PAID, OVERDUE, VOID)
- **InvoiceItem**: Line item in an invoice

### Payment
- **Payment**: Payment record
  - `PaymentStatus`: Enum (PENDING, COMPLETED, FAILED, REFUNDED, CANCELLED)
  - `PaymentMethod`: Enum (CASH, BANK_TRANSFER, CREDIT_CARD, E_WALLET)
- **PaymentGatewayAdapterInterface**: Interface for payment gateway integration

### AAA
- **AAAServiceInterface**: Interface for Authentication, Authorization, Accounting

### Provisioning
- **ProvisioningAdapterInterface**: Base interface for all provisioning adapters
- Specific adapters:
  - `PPPoEProvisioningAdapterInterface`
  - `HotspotProvisioningAdapterInterface`
  - `VoucherProvisioningAdapterInterface`
  - `DHCPProvisioningAdapterInterface`
  - `StaticIPProvisioningAdapterInterface`
- **RadiusProvisioningAdapterInterface**: Interface for RADIUS integration

### Notification
- **Notification**: Notification record
  - `NotificationType`: Enum (EMAIL, SMS, PUSH, IN_APP)
  - `NotificationStatus`: Enum (PENDING, SENT, FAILED, DELIVERED, READ)
- **NotificationChannelInterface**: Interface for notification channels

### Workflow
- **Workflow**: Workflow definition with steps and triggers
  - `WorkflowStatus`: Enum (DRAFT, ACTIVE, INACTIVE)
- **WorkflowStep**: Individual step in a workflow
- **WorkflowTrigger**: Trigger for a workflow

### Scheduler
- **ScheduledTask**: Scheduled task with cron expression
  - `ScheduledTaskStatus`: Enum (ACTIVE, PAUSED, COMPLETED, FAILED)

### Integration
- **AdapterRegistry**: Registry for all adapters

### Plugin
- **PluginManager**: Manager for plugins
- **PluginInterface**: Interface for plugins

## Architecture

### Adapter Pattern
Used for:
- Provisioning (PPPoE, Hotspot, Voucher, DHCP, StaticIP, Radius)
- Payment Gateways
- Notification Channels
- Device Monitoring

### Event-Driven
Uses domain events for loose coupling between components.

### Queues
All long-running processes should be queued:
- Notifications
- Provisioning
- Billing
- Reports

### Scheduler
Automated tasks via cron expressions for:
- Invoice generation
- Payment reminders
- Service renewals
- Reports

## Next Steps
1. **Implement Application Services**: Create application services that use domain models
2. **Implement Infrastructure**: Create concrete implementations for repositories, adapters, queues
3. **Create Models & Migrations**: Create Eloquent models and database migrations
4. **Build Livewire Components**: Use the engine to build UI
5. **Implement Controllers & Routes**: Expose API endpoints
