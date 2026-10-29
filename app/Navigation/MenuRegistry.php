<?php

namespace App\Navigation;

class MenuRegistry
{
    public static function getNavigation(): array
    {
        $prefix = request()->route() ? ltrim(request()->route()->getPrefix() ?? "", "/") : "";

        if (str_starts_with((string)$prefix, 'technician')) {
            return self::buildTechnicianNavigation();
        }

        if (str_starts_with((string)$prefix, 'reseller-portal') || (auth()->check() && auth()->user()->hasRole('reseller') && !auth()->user()->hasAnyRole(['administrator', 'superadmin', 'manager']))) {
            return self::buildResellerNavigation();
        }

        if (str_starts_with((string)$prefix, 'customer-portal')) {
            return self::buildCustomerNavigation();
        }

        if (str_starts_with((string)$prefix, 'noc')) {
            return self::buildNocNavigation();
        }

        return self::buildAdminNavigation();
    }

    private static function buildAdminNavigation(): array
    {
        $navigation = [
            [
                'label' => 'Admin / Management',
                'icon' => 'admin_panel_settings',
                'icon_color' => 'text-blue-500',
                'groups' => [
                    [
                        'label' => '',
                        'items' => [
                            ['label' => 'Dashboard', 'route' => 'dashboard', 'icon' => 'dashboard', 'active' => 'dashboard'],
                            ['label' => 'Portal NOC', 'route' => 'noc.overview', 'icon' => 'monitor_heart'],
                        ]
                    ],
                      [
                        'label' => '',
                        'items' => [
                          ['label' => 'Router / NAS', 'route' => 'isp.routers.index', 'icon' => 'router', 'active' => 'isp.routers.*', 'permission' => 'router.view'],
                          ['label' => 'Data OLT', 'route' => 'isp.olts.index', 'icon' => 'developer_board', 'active' => 'isp.olts.*', 'permission' => 'olt.view'],
                        ]
                    ],
                     [
                        'label' => '',
                        'items' => [
                            ['label' => 'GenieACS', 'route' => 'acs.dashboard', 'icon' => 'router', 'active' => 'acs.*', 'permission' => 'onu.view'],
                        ]
                    ],
                      [
                        'label' => '',
                        'items' => [
                            ['label' => 'Profil Paket', 'route' => 'isp.service-profiles.index', 'icon' => 'inventory_2', 'active' => 'isp.service-profiles.*', 'permission' => 'service-profile.view'],
                           
                        ]
                    ],
                    [
                        'label' => 'List Pelanggan',
                        'icon' => 'people',
                        'items' => [
                            ['label' => 'Customer', 'route' => 'crm.customers.index', 'active' => 'crm.customers.*', 'permission' => 'customer.view'],
                            ['label' => 'PPPoE', 'route' => 'isp.pppoe-users.index', 'active' => 'isp.pppoe-users.*', 'permission' => 'pppoe-user.view'],
                            ['label' => 'Hotspot', 'route' => 'isp.hotspot-users.index', 'active' => 'isp.hotspot-users.*', 'permission' => 'hotspot-user.view'],
                            ['label' => 'Cookies', 'route' => 'isp.hotspot-cookies.index', 'active' => 'isp.hotspot-cookies.*', 'permission' => 'hotspot-user.view'],
                            ['label' => 'Voucher', 'route' => 'isp.vouchers.index', 'active' => 'isp.vouchers.*', 'permission' => 'voucher.view'],
                            ['label' => 'E-Voucher', 'route' => 'isp.evouchers.index', 'active' => 'isp.evouchers.*', 'permission' => 'voucher.view'],
                            ['label' => 'Online', 'route' => 'isp.user-online.index', 'active' => 'isp.user-online.*', 'permission' => 'customer.view'],
                            ['label' => 'Isolir', 'route' => 'pelanggan.isolir', 'active' => 'pelanggan.isolir', 'permission' => 'customer.view'],
                        ]
                    ],
                    [
                        'label' => 'Data Tagihan',
                        'icon' => 'receipt_long',
                        'items' => [
                            ['label' => 'Semua Tagihan', 'route' => 'billing.invoices.index', 'active' => 'billing.invoices.*', 'permission' => 'invoice.view'],
                            ['label' => 'Pembayaran', 'route' => 'billing.payments.index', 'active' => 'billing.payments.*', 'permission' => 'payment.view'],
                            ['label' => 'Periode Tagihan', 'route' => 'billing.periode.index', 'active' => 'billing.periode.*', 'permission' => 'invoice.view'],
                        ]
                    ],
                    [
                        'label' => 'Data Keuangan',
                        'icon' => 'account_balance_wallet',
                        'items' => [
                            ['label' => 'Laba Rugi', 'route' => 'keuangan.laba-rugi', 'active' => 'keuangan.laba-rugi', 'permission' => 'finance.view'],
                            ['label' => 'Pemasukan Harian', 'route' => 'keuangan.income-harian', 'active' => 'keuangan.income-harian', 'permission' => 'finance.view'],
                            ['label' => 'Pemasukan Bulanan', 'route' => 'keuangan.income-periode', 'active' => 'keuangan.income-periode', 'permission' => 'finance.report'],
                            ['label' => 'Pengeluaran', 'route' => 'keuangan.pengeluaran', 'active' => 'keuangan.pengeluaran', 'permission' => 'finance.view'],
                            ['label' => 'Deposit Reseller', 'route' => 'keuangan.topup-reseller', 'active' => 'keuangan.topup-reseller', 'permission' => 'finance.settlement'],
                            ['label' => 'Laporan BHP & USO', 'route' => 'keuangan.bhp-uso', 'active' => 'keuangan.bhp-uso', 'permission' => 'finance.report'],
                        ]
                    ],
                    [
                        'label' => 'Infrastruktur Fiber',
                        'icon' => 'hub',
                        'items' => [
                            ['label' => 'Data ODC', 'route' => 'isp.odcs.index', 'active' => 'isp.odcs.*', 'permission' => 'odc.view'],
                            ['label' => 'Data ODP', 'route' => 'isp.odps.index', 'active' => 'isp.odps.*', 'permission' => 'odp.view'],
                            ['label' => 'Data POP', 'route' => 'isp.pops.index', 'active' => 'isp.pops.*', 'permission' => 'pop.view'],
                            ['label' => 'Data Vendor', 'route' => 'isp.vendors.index', 'active' => 'isp.vendors.*', 'permission' => 'vendor.view'],
                            ['label' => 'Network Assets', 'route' => 'inventory.assets.index', 'active' => 'inventory.assets.*', 'permission' => 'network.view'],
                        ]
                    ],
                    [
                        'label' => 'Kepegawaian',
                        'icon' => 'badge',
                        'items' => [
                            ['label' => 'Pegawai', 'route' => 'admin.employee.index', 'active' => 'admin.employee.*', 'permission' => 'user.view'],
                            ['label' => 'Absensi', 'route' => 'admin.attendance.index', 'active' => 'admin.attendance.*', 'permission' => 'workforce.attendance.execute'],
                            ['label' => 'Gaji / Payroll', 'route' => 'admin.payroll.index', 'active' => 'admin.payroll.*', 'permission' => 'finance.view'],
                        ]
                    ],
                    [
                        'label' => 'CRM',
                        'icon' => 'handshake',
                        'items' => [
                            ['label' => 'Prospek', 'route' => 'crm.leads.index', 'active' => 'crm.leads.*', 'permission' => 'customer.view'],
                            ['label' => 'Survey', 'route' => 'crm.surveys.index', 'active' => 'crm.surveys.*', 'permission' => 'customer.view'],
                            ['label' => 'Penawaran', 'route' => 'crm.quotations.index', 'active' => 'crm.quotations.*', 'permission' => 'customer.view'],
                            ['label' => 'Kontrak', 'route' => 'crm.contracts.index', 'active' => 'crm.contracts.*', 'permission' => 'customer.view'],
                        ]
                    ],
                    [
                        'label' => 'Support',
                        'icon' => 'support_agent',
                        'items' => [
                            ['label' => 'Tiket', 'route' => 'support.ticket', 'active' => 'support.ticket', 'permission' => 'ticket.view'],
                            ['label' => 'Instalasi', 'route' => 'crm.installations.index', 'active' => 'crm.installations.*', 'permission' => 'workforce.jobs.view'],
                            ['label' => 'Aktivasi', 'route' => 'crm.activations.index', 'active' => 'crm.activations.*', 'permission' => 'customer.view'],
                            ['label' => 'Maintenance', 'route' => 'support.maintenance', 'active' => 'support.maintenance', 'permission' => 'workforce.jobs.view'],
                            ['label' => 'Riwayat WO', 'route' => 'support.work-orders.history', 'active' => 'support.work-orders.*', 'permission' => 'workforce.jobs.view'],
                        ]
                    ],
                    [
                        'label' => 'Pengaturan',
                        'icon' => 'settings',
                        'items' => [
                            ['label' => 'Identitas Perusahaan', 'route' => 'pengaturan.perusahaan', 'active' => 'pengaturan.perusahaan', 'permission' => 'pengaturan.perusahaan'],
                            ['label' => 'Payment Gateway', 'route' => 'pengaturan.payment-gateway', 'active' => 'pengaturan.payment-gateway', 'permission' => 'settings.view'],
                            ['label' => 'WhatsApp API', 'route' => 'pengaturan.whatsapp', 'active' => 'pengaturan.whatsapp', 'permission' => 'settings.view'],
                            ['label' => 'Telegram Bot', 'route' => 'pengaturan.telegram', 'active' => 'pengaturan.telegram', 'permission' => 'settings.view'],
                            ['label' => 'Pengaturan Sistem', 'route' => 'admin.settings.index', 'active' => 'admin.settings.*', 'permission' => 'settings.view'],
                            ['label' => 'User Management', 'route' => 'admin.users.index', 'active' => 'admin.users.*', 'permission' => 'user.view'],
                            ['label' => 'Role & Hak Akses', 'route' => 'admin.roles.index', 'active' => 'admin.roles.*', 'permission' => 'role.view'],
                            ['label' => 'Template Voucher', 'route' => 'isp.voucher-templates.index', 'active' => 'isp.voucher-templates.*', 'permission' => 'voucher-template.view'],
                            ['label' => 'Download LoginPage', 'route' => 'pengaturan.loginpage', 'active' => 'pengaturan.loginpage*', 'icon' => 'cloud_download', 'permission' => 'voucher.view'],
                            ['label' => 'Audit Log', 'route' => 'admin.audit-trail.index', 'active' => 'admin.audit-trail.*', 'permission' => 'role.view'],
                        ]
                    ]
                ]
            ]
        ];

        return self::filterNavigationByPermissions($navigation);
    }

        private static function buildNocNavigation(): array
    {
        $nocDashboardItems = [];
        if (auth()->check() && auth()->user()->hasRole('administrator')) {
            $nocDashboardItems[] = ['label' => 'Kembali ke Admin', 'route' => 'dashboard', 'icon' => 'arrow_back'];
        }
        $nocDashboardItems[] = ['label' => 'NOC Dashboard', 'route' => 'noc.overview', 'icon' => 'dashboard', 'active' => 'noc.overview'];

        $navigation = [
            [
                'label' => 'NOC Portal',
                'icon' => 'monitor_heart',
                'icon_color' => 'text-purple-500',
                'groups' => [
                    [
                        'label' => '',
                        'items' => $nocDashboardItems
                    ],
                    [
                        'label' => 'Monitoring',
                        'icon' => 'monitoring',
                        'items' => [
                            ['label' => 'Router / MikroTik', 'route' => 'noc.routers.index', 'active' => 'noc.routers.*'],
                            ['label' => 'OLT', 'route' => 'noc.olts.index', 'active' => 'noc.olts.*'],

                            ['label' => 'Active Sessions', 'route' => 'noc.pppoe.index', 'active' => 'noc.pppoe.*'],
                        ]
                    ],
                    [
                        'label' => 'Alarms',
                        'icon' => 'warning',
                        'items' => [
                            ['label' => 'Alert List', 'route' => 'noc.alerts.index', 'active' => 'noc.alerts.*'],
                            ['label' => 'Alarm Events', 'route' => 'noc.alarms.index', 'active' => 'noc.alarms.*'],
                        ]
                    ],
                    [
                        'label' => 'Support & Ticket',
                        'icon' => 'support_agent',
                        'items' => [
                            ['label' => 'Tiket Bantuan', 'route' => 'support.ticket', 'active' => 'support.ticket'],
                            ['label' => 'Instalasi Baru', 'route' => 'support.installation', 'active' => 'support.installation'],
                            ['label' => 'Riwayat Instalasi', 'route' => 'support.work-orders.history', 'active' => 'support.work-orders.*'],
                            ['label' => 'Maintenance', 'route' => 'support.maintenance', 'active' => 'support.maintenance'],
                        ]
                    ],
                    [
                        'label' => 'Topology',

                        'icon' => 'hub',
                        'items' => [
                            ['label' => 'Network Map', 'route' => 'noc.topology.index', 'active' => 'noc.topology.*'],
                        ]
                    ],
                    [
                        'label' => 'Provisioning',
                        'icon' => 'bolt',
                        'items' => [
                            ['label' => 'Provisioning & Jobs', 'route' => 'noc.provisioning.index', 'active' => 'noc.provisioning.*'],
                        ]
                    ],
                                        [
                        'label' => 'Spatial & Analytics',
                        'icon' => 'public',
                        'items' => [
                            ['label' => 'GIS Dashboard', 'route' => 'gis.index', 'active' => 'gis.index'],
                            ['label' => 'Live Mapping', 'route' => 'gis.map', 'active' => 'gis.map'],
                            ['label' => 'Analytics', 'route' => 'gis.analytics', 'active' => 'gis.analytics'],
                        ]
                    ],
                    [
                        'label' => 'Inventory',
                        'icon' => 'inventory',
                        'items' => [
                            ['label' => 'Data Vendor', 'route' => 'isp.vendors.index', 'active' => 'isp.vendors.*'],
                            ['label' => 'Network Assets', 'route' => 'inventory.assets.index', 'active' => 'inventory.assets.*'],
                        ]
                    ]
                ]
            ]
        ];

        return self::filterNavigationByPermissions($navigation);
    }

        private static function buildTechnicianNavigation(): array
    {
        $navigation = [
            [
                'label' => 'Portal Teknisi',
                'icon' => 'handyman',
                'icon_color' => 'text-amber-500',
                'groups' => [
                    [
                        'label' => '',
                        'items' => [
                            ['label' => 'Dashboard', 'route' => 'technician.dashboard', 'icon' => 'dashboard', 'active' => 'technician.dashboard'],
                            ['label' => 'Absensi', 'route' => 'technician.attendance', 'icon' => 'fingerprint', 'active' => 'technician.attendance'],
                        ]
                    ],
                    [
                        'label' => 'Pekerjaan Lapangan',
                        'icon' => 'work',
                        'items' => [
                            ['label' => 'Daftar Semua Pekerjaan', 'route' => 'technician.my-jobs.index', 'active' => 'technician.my-jobs.index'],
                            ['label' => 'Pemasangan Baru (PSB)', 'route' => 'technician.my-jobs.psb', 'active' => 'technician.my-jobs.psb'],
                            ['label' => 'Jadwal Maintenance', 'route' => 'technician.my-jobs.maintenance', 'active' => 'technician.my-jobs.maintenance'],
                            ['label' => 'Tiket Gangguan', 'route' => 'technician.my-jobs.troubleshooting', 'active' => 'technician.my-jobs.troubleshooting'],
                        ]
                    ],
                    [
                        'label' => 'Instalasi Perangkat',
                        'icon' => 'router',
                        'items' => [
                            ['label' => 'Wizard Instalasi', 'route' => 'technician.installation.wizard', 'active' => 'technician.installation.wizard'],
                            ['label' => 'Scan Barcode ONU', 'route' => 'technician.installation.scan', 'active' => 'technician.installation.scan'],
                            ['label' => 'Register ke OLT', 'route' => 'technician.installation.register', 'active' => 'technician.installation.register'],
                        ]
                    ],
                    [
                        'label' => 'Jaringan Fiber (ODP)',
                        'icon' => 'share',
                        'items' => [
                            ['label' => 'Pencarian ODP', 'route' => 'technician.odp.search', 'active' => 'technician.odp.search'],
                            ['label' => 'ODP Terdekat (Maps)', 'route' => 'technician.odp.nearest', 'active' => 'technician.odp.nearest'],
                        ]
                    ],
                    [
                        'label' => 'Laporan & Audit',
                        'icon' => 'assignment',
                        'items' => [
                            ['label' => 'Audit Pekerjaan', 'route' => 'technician.audit.form', 'active' => 'technician.audit.*'],
                            ['label' => 'Slip Gaji', 'route' => 'technician.payroll.index', 'active' => 'technician.payroll.*'],
                        ]
                    ]
                ]
            ]
        ];

        return self::filterNavigationByPermissions($navigation);
    }

    private static function buildResellerNavigation(): array
    {
        $navigation = [
            [
                'label' => 'Portal Reseller',
                'icon' => 'storefront',
                'icon_color' => 'text-emerald-500',
                'groups' => [
                    [
                        'label' => '',
                        'items' => [
                            ['label' => 'Dashboard', 'route' => 'reseller-portal.dashboard', 'icon' => 'dashboard', 'active' => 'reseller-portal.dashboard'],
                        ]
                    ],
                    [
                        'label' => 'List Pelanggan',
                        'icon' => 'people',
                        'items' => [
                            ['label' => 'Customer', 'route' => 'reseller-portal.customers.index', 'active' => 'reseller-portal.customers.index', 'permission' => 'customer.view'],
                            ['label' => 'PPPoE', 'route' => 'reseller-portal.customers.pppoe', 'active' => 'reseller-portal.customers.pppoe', 'permission' => 'pppoe-user.view'],
                            ['label' => 'Hotspot', 'route' => 'reseller-portal.customers.hotspot', 'active' => 'reseller-portal.customers.hotspot', 'permission' => 'hotspot-user.view'],
                            ['label' => 'Voucher', 'route' => 'reseller-portal.sales.voucher', 'active' => 'reseller-portal.sales.voucher', 'permission' => 'voucher.view'],
                            ['label' => 'Online', 'route' => 'reseller-portal.customers.user-online', 'active' => 'reseller-portal.customers.user-online', 'permission' => 'customer.view'],
                            ['label' => 'Riwayat Instalasi', 'route' => 'reseller-portal.customers.installation-history', 'active' => 'reseller-portal.customers.installation-history', 'permission' => 'customer.view'],
                            ['label' => 'Terisolir', 'route' => 'reseller-portal.customers.isolated', 'active' => 'reseller-portal.customers.isolated', 'permission' => 'customer.view'],
                        ]
                    ],
                    [
                        'label' => 'Data Tagihan',
                        'icon' => 'receipt_long',
                        'items' => [
                            ['label' => 'Semua Tagihan', 'route' => 'reseller-portal.billing.invoices', 'active' => 'reseller-portal.billing.invoices*', 'permission' => 'invoice.view'],
                            ['label' => 'Pembayaran', 'route' => 'reseller-portal.billing.payments', 'active' => 'reseller-portal.billing.payments*', 'permission' => 'payment.view'],
                        ]
                    ],
                    [
                        'label' => 'Network & GIS',
                        'icon' => 'public',
                        'items' => [
                            ['label' => 'Peta Satelit', 'route' => 'reseller-portal.network.map', 'active' => 'reseller-portal.network.map', 'permission' => 'gis.view'],
                            ['label' => 'Data OLT', 'route' => 'reseller-portal.network.olts.index', 'active' => 'reseller-portal.network.olts.*', 'permission' => 'olt.view'],
                            ['label' => 'Data ODC', 'route' => 'reseller-portal.network.odcs.index', 'active' => 'reseller-portal.network.odcs.*', 'permission' => 'odc.view'],
                            ['label' => 'Data ODP', 'route' => 'reseller-portal.network.odps.index', 'active' => 'reseller-portal.network.odps.*', 'permission' => 'odp.view'],
                        ]
                    ],
                    [
                        'label' => 'Data Keuangan',
                        'icon' => 'account_balance_wallet',
                        'items' => [
                            ['label' => 'Pemasukan Harian', 'route' => 'reseller-portal.reports.revenue', 'active' => ['reseller-portal.reports.revenue*', 'keuangan.income-harian*'], 'permission' => 'report.view'],
                            ['label' => 'Pemasukan Bulanan', 'route' => 'reseller-portal.reports.sales', 'active' => ['reseller-portal.reports.sales*', 'keuangan.income-periode*'], 'permission' => 'finance.report'],
                            ['label' => 'Pengeluaran', 'route' => 'reseller-portal.finance.expenses', 'active' => 'reseller-portal.finance.expenses*', 'permission' => 'finance.view'],
                            ['label' => 'Saldo Deposit', 'route' => 'reseller-portal.finance.balance', 'active' => 'reseller-portal.finance.balance', 'permission' => 'finance.view'],
                            ['label' => 'Top Up', 'route' => 'reseller-portal.finance.topup', 'active' => 'reseller-portal.finance.topup', 'permission' => 'finance.settlement'],
                            ['label' => 'Mutasi Saldo', 'route' => 'reseller-portal.finance.mutations', 'active' => 'reseller-portal.finance.mutations', 'permission' => 'finance.view'],
                        ]
                    ]
                ]
            ]
        ];

        return self::filterNavigationByPermissions($navigation);
    }

    private static function buildCustomerNavigation(): array
    {
        $navigation = [
            [
                'label' => 'Customer Portal',
                'icon' => 'person',
                'icon_color' => 'text-blue-500',
                'groups' => [
                    [
                        'label' => '',
                        'items' => [
                            ['label' => 'Home', 'route' => 'customer-portal.dashboard', 'icon' => 'home', 'active' => 'customer-portal.dashboard'],
                            ['label' => 'Layanan Saya', 'route' => 'customer-portal.services', 'icon' => 'router', 'active' => 'customer-portal.services'],
                            ['label' => 'Tagihan', 'route' => 'customer-portal.billing.invoice-list', 'icon' => 'receipt_long', 'active' => 'customer-portal.billing.*'],
                            ['label' => 'Pembayaran', 'route' => 'customer-portal.payments', 'icon' => 'payment', 'active' => 'customer-portal.payments'],
                        ]
                    ],
                    [
                        'label' => 'WiFi',
                        'icon' => 'wifi',
                        'items' => [
                            ['label' => 'Nama WiFi', 'route' => 'customer-portal.wifi.ssid', 'active' => 'customer-portal.wifi.ssid'],
                            ['label' => 'Password WiFi', 'route' => 'customer-portal.self-service.change-onu-wifi-password', 'active' => 'customer-portal.self-service.change-onu-wifi-password'],
                            ['label' => 'Connected Devices', 'route' => 'customer-portal.self-service.connected-devices', 'active' => 'customer-portal.self-service.connected-devices'],
                        ]
                    ],
                    [
                        'label' => 'Paket',
                        'icon' => 'inventory_2',
                        'items' => [
                            ['label' => 'Paket Saat Ini', 'route' => 'customer-portal.package.current', 'active' => 'customer-portal.package.current'],
                            ['label' => 'Upgrade Paket', 'route' => 'customer-portal.self-service.change-plan', 'active' => 'customer-portal.self-service.change-plan'],
                        ]
                    ],
                    [
                        'label' => 'Support',
                        'icon' => 'support_agent',
                        'items' => [
                            ['label' => 'Tiket Bantuan', 'route' => 'customer-portal.support.ticket-list', 'active' => 'customer-portal.support.ticket-*'],
                            ['label' => 'Hubungi Admin', 'route' => 'customer-portal.support.contact-admin', 'active' => 'customer-portal.support.contact-admin'],
                        ]
                    ],
                    [
                        'label' => '',
                        'items' => [
                            ['label' => 'Profil', 'route' => 'customer-portal.profile', 'icon' => 'account_circle', 'active' => 'customer-portal.profile'],
                        ]
                    ]
                ]
            ]
        ];

        return self::filterNavigationByPermissions($navigation);
    }

    private static function filterNavigationByPermissions(array $navigation): array
    {
        $filtered = [];
        $user = auth()->user();

        // Administrator memiliki full bypass (semua menu tampil)
        $isAdmin = $user && method_exists($user, 'isSuperAdmin') && $user->isSuperAdmin();

        foreach ($navigation as $menu) {
            if (isset($menu['groups']) && is_array($menu['groups'])) {
                $filteredGroups = [];
                foreach ($menu['groups'] as $group) {
                    $filteredItems = [];
                    if (isset($group['items']) && is_array($group['items'])) {
                        foreach ($group['items'] as $item) {
                            $perm = $item['permission'] ?? null;
                            // Jika menu memiliki kebutuhan permission dan user bukan Administrator
                            if ($perm && $user && !$isAdmin) {
                                if (!$user->hasPermission($perm)) {
                                    continue; // Sembunyikan menu karena izin tidak ada / tidak dicentang
                                }
                            }
                            $filteredItems[] = $item;
                        }
                    }
                    if (!empty($filteredItems)) {
                        $group['items'] = $filteredItems;
                        $filteredGroups[] = $group;
                    }
                }
                if (!empty($filteredGroups)) {
                    $menu['groups'] = $filteredGroups;
                    $filtered[] = $menu;
                }
            } else {
                $filtered[] = $menu;
            }
        }

        return $filtered;
    }
}








