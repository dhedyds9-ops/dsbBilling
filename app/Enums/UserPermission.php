<?php

namespace App\Enums;

/**
 * UserPermission — Daftar permission standar dsBilling ISP.
 *
 * PRINSIP:
 *  - Permission menentukan APA yang boleh dilakukan
 *  - Role menentukan SIAPA yang login
 *  - Jangan membuat role baru hanya karena butuh permission tertentu
 *
 * Format penamaan: {resource}.{action}
 */
enum UserPermission: string
{
    // =============================================
    // CUSTOMER MANAGEMENT
    // =============================================
    case CustomerView     = 'customer.view';
    case CustomerCreate   = 'customer.create';
    case CustomerUpdate   = 'customer.update';
    case CustomerDelete   = 'customer.delete';
    case CustomerExport   = 'customer.export';

    // =============================================
    // RESELLER MANAGEMENT
    // =============================================
    case ResellerView   = 'reseller.view';
    case ResellerCreate = 'reseller.create';
    case ResellerUpdate = 'reseller.update';
    case ResellerDelete = 'reseller.delete';
    case ResellerManage = 'reseller.manage';

    // =============================================
    // BRANCH MANAGEMENT
    // =============================================
    case BranchView   = 'branch.view';
    case BranchCreate = 'branch.create';
    case BranchUpdate = 'branch.update';
    case BranchDelete = 'branch.delete';
    case BranchManage = 'branch.manage';

    // =============================================
    // BILLING / INVOICE
    // =============================================
    case InvoiceView    = 'invoice.view';
    case InvoiceCreate  = 'invoice.create';
    case InvoiceUpdate  = 'invoice.update';
    case InvoiceDelete  = 'invoice.delete';
    case InvoicePayment = 'invoice.payment';
    case InvoiceExport  = 'invoice.export';

    // =============================================
    // PAYMENT
    // =============================================
    case PaymentView   = 'payment.view';
    case PaymentCreate = 'payment.create';
    case PaymentUpdate = 'payment.update';
    case PaymentDelete = 'payment.delete';

    // =============================================
    // FINANCE / KEUANGAN
    // =============================================
    case FinanceView       = 'finance.view';
    case FinanceReport     = 'finance.report';
    case FinanceSettlement = 'finance.settlement';
    case FinanceExport     = 'finance.export';

    // =============================================
    // PPPoE USER
    // =============================================
    case PppoeUserView   = 'pppoe-user.view';
    case PppoeUserCreate = 'pppoe-user.create';
    case PppoeUserUpdate = 'pppoe-user.update';
    case PppoeUserDelete = 'pppoe-user.delete';

    // =============================================
    // HOTSPOT USER
    // =============================================
    case HotspotUserView   = 'hotspot-user.view';
    case HotspotUserCreate = 'hotspot-user.create';
    case HotspotUserUpdate = 'hotspot-user.update';
    case HotspotUserDelete = 'hotspot-user.delete';

    // =============================================
    // VOUCHER
    // =============================================
    case VoucherView   = 'voucher.view';
    case VoucherCreate = 'voucher.create';
    case VoucherUpdate = 'voucher.update';
    case VoucherDelete = 'voucher.delete';
    case VoucherPrint  = 'voucher.print';

    // =============================================
    // SERVICE PROFILE / PACKAGE
    // =============================================
    case ServiceProfileView   = 'service-profile.view';
    case ServiceProfileCreate = 'service-profile.create';
    case ServiceProfileUpdate = 'service-profile.update';
    case ServiceProfileDelete = 'service-profile.delete';
    case ServiceProfileRestore = 'service-profile.restore';
    case ServiceProfileImport  = 'service-profile.import';
    case ServiceProfileExport  = 'service-profile.export';

    // =============================================
    // NETWORK PROFILE
    // =============================================
    case NetworkProfileView   = 'network-profile.view';
    case NetworkProfileCreate = 'network-profile.create';
    case NetworkProfileUpdate = 'network-profile.update';
    case NetworkProfileDelete = 'network-profile.delete';
    case NetworkProfileImport  = 'network-profile.import';
    case NetworkProfileExport  = 'network-profile.export';

    // =============================================
    // IP POOL
    // =============================================
    case IpPoolView   = 'ip-pool.view';
    case IpPoolCreate = 'ip-pool.create';
    case IpPoolUpdate = 'ip-pool.update';
    case IpPoolDelete = 'ip-pool.delete';
    case IpPoolImport  = 'ip-pool.import';
    case IpPoolExport  = 'ip-pool.export';

    // =============================================
    // VOUCHER TEMPLATE
    // =============================================
    case VoucherTemplateView   = 'voucher-template.view';
    case VoucherTemplateCreate = 'voucher-template.create';
    case VoucherTemplateUpdate = 'voucher-template.update';
    case VoucherTemplateDelete = 'voucher-template.delete';
    case VoucherTemplateImport  = 'voucher-template.import';
    case VoucherTemplateExport  = 'voucher-template.export';
    case VoucherTemplatePrint   = 'voucher-template.print';

    // =============================================
    // NETWORK MANAGEMENT (umbrella, backward compat)
    // =============================================
    case NetworkView   = 'network.view';
    case NetworkManage = 'network.manage';

    case RouterView   = 'router.view';
    case RouterCreate = 'router.create';
    case RouterUpdate = 'router.update';
    case RouterDelete = 'router.delete';
    case RouterManage = 'router.manage';

    case NasDeviceView   = 'nas-device.view';
    case NasDeviceCreate = 'nas-device.create';
    case NasDeviceUpdate = 'nas-device.update';
    case NasDeviceDelete = 'nas-device.delete';

    // =============================================
    // RADIUS
    // =============================================
    case RadiusView   = 'radius.view';
    case RadiusManage = 'radius.manage';

    // =============================================
    // REPORT / LAPORAN
    // =============================================
    case ReportView   = 'report.view';
    case ReportExport = 'report.export';

    // =============================================
    // SUPPORT / TICKET
    // =============================================
    case TicketView   = 'ticket.view';
    case TicketCreate = 'ticket.create';
    case TicketUpdate = 'ticket.update';
    case TicketDelete = 'ticket.delete';
    case TicketAssign = 'ticket.assign';

    // =============================================
    // PENGATURAN / SETTINGS
    // =============================================
    case SettingsView   = 'settings.view';
    case SettingsUpdate = 'settings.update';
    case CompanyUpdate  = 'pengaturan.perusahaan';

    // =============================================
    // MONITORING
    // =============================================
    case MonitoringView   = 'monitoring.view';
    case MonitoringManage = 'monitoring.manage';

    // =============================================
    // NOC MONITORING (granular permissions)
    // =============================================
    /** Main gate: akses menu NOC Monitoring dan Overview Dashboard */
    case NocView = 'noc.view';

    /** Akses tab OLT Monitor (status, PON, metric) */
    case NocOltView = 'noc.olt.view';

    /** Akses tab ONU Monitor (list, status, LOS, RX/TX Power) */
    case NocOnuView = 'noc.onu.view';
    /** Aksi ONU: reboot, re-provision, set WiFi (via GenieACS API) — TIDAK akses GenieACS UI */
    case NocOnuManage = 'noc.onu.manage';

    /** Akses tab Router Monitor (health, CPU, memory, uptime) */
    case NocRouterView = 'noc.router.view';

    /** Akses tab PPPoE Sessions (active, kick user) */
    case NocPppoeView = 'noc.pppoe.view';

    /** Akses tab Alarms/Event (list open/closed) */
    case NocAlarmsView = 'noc.alarms.view';
    /** Aksi alarm: acknowledge, resolve, assign */
    case NocAlarmsManage = 'noc.alarms.manage';

    /** Akses Provisioning Pipeline Monitor (sub-gate sensitif) */
    case NocProvisioningView = 'noc.provisioning.view';

    /** Akses Topology & Impact Analysis (sub-gate sensitif) */
    case NocTopologyView = 'noc.topology.view';

    /** Portal access flag (bukan view permission): ditandai jika user punya akses NOC operator console */
    case NocPortal = 'noc.portal';

    // =============================================
    // GIS
    // =============================================
    case GisView   = 'gis.view';
    case GisManage = 'gis.manage';

    // =============================================
    // USER MANAGEMENT
    // =============================================
    case UserView   = 'user.view';
    case UserCreate = 'user.create';
    case UserUpdate = 'user.update';
    case UserDelete = 'user.delete';

    // =============================================
    // ROLE & PERMISSION MANAGEMENT (untuk audit trail)
    // =============================================
    /** Lihat daftar role dan permission assignment */
    case RoleView = 'role.view';
    /** Ubah role user dan sync permission role/user */
    case RoleManage = 'role.manage';

    // =============================================
    // PORTAL ACCESS (NEW)
    // =============================================
    case TechnicianPortal = 'technician.portal';
    // =============================================
    // WORKFORCE & TECHNICIAN (Phase P1)
    // =============================================
    case WorkforceJobsView = 'workforce.jobs.view';
    case WorkforceJobsExecute = 'workforce.jobs.execute';
    case WorkforceQcView = 'workforce.qc.view';
    case WorkforceQcExecute = 'workforce.qc.execute';
    case WorkforceMaterialView = 'workforce.material.view';
    case WorkforceMaterialExecute = 'workforce.material.execute';
    case WorkforceAttendanceExecute = 'workforce.attendance.execute';

    // =============================================
    // ONU REMEDIATION & CONFIGURATION (Phase 7.5.4)
    // =============================================
    case OnuView = 'onu.view';
    case OnuDiagnose = 'onu.diagnose';
    case OnuReboot = 'onu.reboot';
    case OnuConfigureWifi = 'onu.configure.wifi';
    case OnuConfigureWan = 'onu.configure.wan';
    case OnuFirmwareUpdate = 'onu.firmware.update';
    case OnuFirmwareDowngrade = 'onu.firmware.downgrade';
    case OnuFactoryReset = 'onu.factory_reset';
    case OnuRemediationView = 'onu.remediation.view';
    case OnuRemediationExecute = 'onu.remediation.execute';
    case OnuRemediationApprove = 'onu.remediation.approve';
    case OnuRemediationReconcile = 'onu.remediation.reconcile';
    case OnuUnlockExecute = 'onu.unlock.execute';
    case OnuUnlockApprove = 'onu.unlock.approve';

    /**
     * Semua nilai permission sebagai array string.
     */
    public static function allValues(): array
    {
        return array_column(self::cases(), 'value');
    }

    /**
     * Permission set default untuk role Manager.
     * Manager mendapatkan akses ini secara default (dapat dikurangi/ditambah).
     */
    public static function defaultManagerPermissions(): array
    {
        return [
            self::CustomerView->value,
            self::CustomerCreate->value,
            self::CustomerUpdate->value,
            self::InvoiceView->value,
            self::InvoiceCreate->value,
            self::InvoicePayment->value,
            self::PaymentView->value,
            self::PaymentCreate->value,
            self::PppoeUserView->value,
            self::PppoeUserCreate->value,
            self::PppoeUserUpdate->value,
            self::HotspotUserView->value,
            self::HotspotUserCreate->value,
            self::HotspotUserUpdate->value,
            self::VoucherView->value,

            // ===== Paket & Layanan — Granular Permissions (Manager) =====
            // ServiceProfile: Manager = View/Create/Update/Export. Delete/Restore/Import = Admin only
            self::ServiceProfileView->value,
            self::ServiceProfileCreate->value,
            self::ServiceProfileUpdate->value,
            self::ServiceProfileExport->value,
            // NetworkProfile: Manager = View/Create/Update/Export. Delete/Import = Admin only
            self::NetworkProfileView->value,
            self::NetworkProfileCreate->value,
            self::NetworkProfileUpdate->value,
            self::NetworkProfileExport->value,
            // IpPool: Manager = View/Create/Update/Export. Delete/Import = Admin only
            self::IpPoolView->value,
            self::IpPoolCreate->value,
            self::IpPoolUpdate->value,
            self::IpPoolExport->value,
            // VoucherTemplate: Manager = View/Create/Update/Export/Print. Delete/Import = Admin only
            self::VoucherTemplateView->value,
            self::VoucherTemplateCreate->value,
            self::VoucherTemplateUpdate->value,
            self::VoucherTemplateExport->value,
            self::VoucherTemplatePrint->value,

            // Network Umbrella (backward compat)
            self::NetworkView->value,
            self::NetworkManage->value,
            self::RouterView->value,
            self::ReportView->value,
            self::TicketView->value,
            self::TicketCreate->value,
            self::TicketUpdate->value,
            self::MonitoringView->value,
            self::GisView->value,

            // NOC Monitoring (view level — Manager = NOC read-only operator)
            self::NocView->value,
            self::NocOltView->value,
            self::NocOnuView->value,
            self::NocRouterView->value,
            self::NocPppoeView->value,
            self::NocAlarmsView->value,
            self::NocProvisioningView->value,
            self::NocTopologyView->value,
            self::NocPortal->value,

            // Role & Permission (view only, Manage only Administrator)
            self::RoleView->value,
        ];
    }

    /**
     * Permission set default untuk role Reseller.
     *
     * RESIKOLASI DATA:
     *  - Reseller TIDAK boleh melihat NOC monitoring global
     *  - Reseller hanya data pelanggan, layanan, tagihan, dan jaringan MILIKNYA
     */
    public static function defaultResellerPermissions(): array
    {
        return [
            self::CustomerView->value,
            self::CustomerCreate->value,
            self::InvoiceView->value,
            self::InvoicePayment->value,
            self::PaymentView->value,
            self::PppoeUserView->value,
            self::PppoeUserCreate->value,
            self::HotspotUserView->value,
            self::HotspotUserCreate->value,
            self::VoucherView->value,
            self::VoucherCreate->value,
            self::VoucherPrint->value,
            self::ServiceProfileView->value,
            self::ReportView->value,
            // ROLE.manage dan NOC.* DILARANG untuk reseller
        ];
    }
}

