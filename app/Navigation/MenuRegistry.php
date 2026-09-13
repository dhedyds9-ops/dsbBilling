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

        if (str_starts_with((string)$prefix, 'reseller-portal')) {
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
                          ['label' => 'Router / NAS', 'route' => 'isp.routers.index', 'active' => 'isp.routers.*'],
                        ]
                    ],
                     [
                        'label' => '',
                        'items' => [
                            ['label' => 'GenieACS', 'route' => 'acs.dashboard', 'icon' => 'router', 'active' => 'acs.*'],
                        ]
                    ],
                      [
                        'label' => '',
                        'items' => [
                            ['label' => 'Profil Paket', 'route' => 'isp.service-profiles.index', 'icon' => 'inventory_2', 'active' => 'isp.service-profiles.*'],
                           
                        ]
                    ],
                    [
                        'label' => 'List Pelanggan',
                        'icon' => 'people',
                        'items' => [
                            ['label' => 'Customer', 'route' => 'crm.customers.index', 'active' => 'crm.customers.*'],
                            ['label' => 'PPPoE', 'route' => 'isp.pppoe-users.index', 'active' => 'isp.pppoe-users.*'],
                            ['label' => 'Hotspot', 'route' => 'isp.hotspot-users.index', 'active' => 'isp.hotspot-users.*'],
                            ['label' => 'Voucher', 'route' => 'isp.vouchers.index', 'active' => 'isp.vouchers.*'],
                            ['label' => 'E-Voucher', 'route' => 'isp.evouchers.index', 'active' => 'isp.evouchers.*'],
                            ['label' => 'Online', 'route' => 'isp.user-online.index', 'active' => 'isp.user-online.*'],
                            ['label' => 'Isolir', 'route' => 'pelanggan.isolir', 'active' => 'pelanggan.isolir'],
                            
                        ]
                    ],
                                        [
                        'label' => 'Data Tagihan',
                        'icon' => 'receipt_long',
                        'items' => [
                            ['label' => 'Semua Tagihan', 'route' => 'billing.invoices.index', 'active' => 'billing.invoices.*'],
                            ['label' => 'Pembayaran', 'route' => 'billing.payments.index', 'active' => 'billing.payments.*'],
                            ['label' => 'Periode Tagihan', 'route' => 'billing.periode.index', 'active' => 'billing.periode.*'],
                        ]
                    ],
                                        [
                        'label' => 'Data Keuangan',
                        'icon' => 'account_balance_wallet',
                        'items' => [
                            ['label' => 'Laba Rugi', 'route' => 'keuangan.laba-rugi', 'active' => 'keuangan.laba-rugi'],
                            ['label' => 'Pemasukan Harian', 'route' => 'keuangan.income-harian', 'active' => 'keuangan.income-harian'],
                            ['label' => 'Pemasukan Bulanan', 'route' => 'keuangan.income-periode', 'active' => 'keuangan.income-periode'],
                            ['label' => 'Pengeluaran', 'route' => 'keuangan.pengeluaran', 'active' => 'keuangan.pengeluaran'],
                            ['label' => 'Deposit Reseller', 'route' => 'keuangan.topup-reseller', 'active' => 'keuangan.topup-reseller'],
                            ['label' => 'Laporan BHP & USO', 'route' => 'keuangan.bhp-uso', 'active' => 'keuangan.bhp-uso'],
                        ]
                    ],
                    [
                        'label' => 'Infrastruktur Fiber',
                        'icon' => 'hub',
                        'items' => [
                            ['label' => 'Data OLT', 'route' => 'isp.olts.index', 'active' => 'isp.olts.*'],
                            ['label' => 'Data ODC', 'route' => 'isp.odcs.index', 'active' => 'isp.odcs.*'],
                            ['label' => 'Data ODP', 'route' => 'isp.odps.index', 'active' => 'isp.odps.*'],
                            ['label' => 'Network Assets', 'route' => 'inventory.assets.index', 'active' => 'inventory.assets.*'],
                        ]
                    ],
                      [
                          'label' => 'Kepegawaian',
                          'icon' => 'badge',
                          'items' => [
                              ['label' => 'Data Pegawai', 'route' => 'admin.employee.index', 'active' => 'admin.employee.*'],
                              ['label' => 'Rekap Absensi', 'route' => 'admin.attendance.index', 'active' => 'admin.attendance.*'],
                              ['label' => 'Rekap Gaji', 'route' => 'admin.payroll.index', 'active' => 'admin.payroll.*'],
                          ]
                      ],

                    [
                        'label' => 'CRM & Support',
                        'icon' => 'support_agent',
                        'items' => [
                            ['label' => 'Tiket Bantuan', 'route' => 'support.ticket', 'active' => 'support.ticket'],
                            ['label' => 'Calon Pelanggan', 'route' => 'crm.leads.index', 'active' => 'crm.leads.*'],
                            ['label' => 'Survey Pelanggan', 'route' => 'crm.surveys.index', 'active' => 'crm.surveys.*'],
                            ['label' => 'Quotation (Penawaran)', 'route' => 'crm.quotations.index', 'active' => 'crm.quotations.*'],
                            ['label' => 'Kontrak Pelanggan', 'route' => 'crm.contracts.index', 'active' => 'crm.contracts.*'],
                            ['label' => 'Jadwal Instalasi', 'route' => 'crm.installations.index', 'active' => 'crm.installations.*'],
                            ['label' => 'Aktivasi Layanan', 'route' => 'crm.activations.index', 'active' => 'crm.activations.*'],
                            ['label' => 'Maintenance', 'route' => 'support.maintenance', 'active' => 'support.maintenance'],
                            ['label' => 'Workflow & Tugas', 'route' => 'workflow.list.index', 'active' => 'workflow.list.*'],
                        ]
                    ],
                    [
                        'label' => 'Pengaturan',
                        'icon' => 'settings',
                        'items' => [
                            ['label' => 'Identitas Perusahaan', 'route' => 'pengaturan.perusahaan', 'active' => 'pengaturan.perusahaan'],
                            ['label' => 'Koneksi Perangkat', 'route' => 'pengaturan.koneksi', 'active' => 'pengaturan.koneksi'],
                            ['label' => 'Payment Gateway', 'route' => 'pengaturan.payment-gateway', 'active' => 'pengaturan.payment-gateway'],
                            ['label' => 'WhatsApp API', 'route' => 'pengaturan.whatsapp', 'active' => 'pengaturan.whatsapp'],
                            ['label' => 'Telegram Bot', 'route' => 'pengaturan.telegram', 'active' => 'pengaturan.telegram'],
                            ['label' => 'Pengaturan Sistem', 'route' => 'admin.settings.index', 'active' => 'admin.settings.*'],
                            ['label' => 'User Management', 'route' => 'admin.users.index', 'active' => 'admin.users.*'],
                            ['label' => 'Template Voucher', 'route' => 'isp.voucher-templates.index', 'active' => 'isp.voucher-templates.*'],
                            ['label' => 'Audit Log', 'route' => 'admin.audit-trail.index', 'active' => 'admin.audit-trail.*'],
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
                            ['label' => 'ONU', 'route' => 'noc.onus.index', 'active' => 'noc.onus.*'],
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
                        'icon' => 'globe',
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
                            ['label' => 'Riwayat Pekerjaan', 'route' => 'technician.history', 'icon' => 'history', 'active' => 'technician.history'],
                        ]
                    ],
                    [
                        'label' => 'Tugas Saya',
                        'icon' => 'work',
                        'items' => [
                            ['label' => 'PSB', 'route' => 'technician.my-jobs.psb', 'active' => 'technician.my-jobs.psb'],
                            ['label' => 'Maintenance', 'route' => 'technician.my-jobs.maintenance', 'active' => 'technician.my-jobs.maintenance'],
                            ['label' => 'Troubleshooting', 'route' => 'technician.my-jobs.troubleshooting', 'active' => 'technician.my-jobs.troubleshooting'],
                        ]
                    ],
                    [
                        'label' => 'Instalasi',
                        'icon' => 'build',
                        'items' => [
                            ['label' => 'Instalasi', 'route' => 'technician.installation.index', 'active' => 'technician.installation.index'],
                            ['label' => 'Scan ONU', 'route' => 'technician.installation.scan', 'active' => 'technician.installation.scan'],
                            ['label' => 'Register ONU', 'route' => 'technician.installation.register', 'active' => 'technician.installation.register'],
                            ['label' => 'Provisioning', 'route' => 'technician.installation.provision', 'active' => 'technician.installation.provision'],
                            ['label' => 'Test Connection', 'route' => 'technician.installation.test', 'active' => 'technician.installation.test'],
                            ['label' => 'Dokumentasi Instalasi', 'route' => 'technician.installation.docs', 'active' => 'technician.installation.docs'],
                        ]
                    ],
                    [
                        'label' => 'ODP',
                        'icon' => 'share',
                        'items' => [
                            ['label' => 'Cari ODP', 'route' => 'technician.odp.search', 'active' => 'technician.odp.search'],
                            ['label' => 'ODP Terdekat', 'route' => 'technician.odp.nearest', 'active' => 'technician.odp.nearest'],
                            ['label' => 'Port ODP', 'route' => 'technician.odp.ports', 'active' => 'technician.odp.ports'],
                        ]
                    ],
                    [
                        'label' => 'Pelanggan',
                        'icon' => 'person',
                        'items' => [
                            ['label' => 'Detail Pelanggan', 'route' => 'technician.customers.show', 'active' => 'technician.customers.show'],
                            ['label' => 'Status Layanan', 'route' => 'technician.customers.status', 'active' => 'technician.customers.status'],
                            ['label' => 'Riwayat Gangguan', 'route' => 'technician.customers.history', 'active' => 'technician.customers.history'],
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
                              ['label' => 'Customer', 'route' => 'reseller-portal.customers.index', 'active' => 'reseller-portal.customers.index'],
                            ['label' => 'PPPoE', 'route' => 'reseller-portal.customers.pppoe', 'active' => 'reseller-portal.customers.pppoe'],
                            ['label' => 'Hotspot', 'route' => 'reseller-portal.customers.hotspot', 'active' => 'reseller-portal.customers.hotspot'],
                            ['label' => 'Voucher', 'route' => 'reseller-portal.sales.voucher', 'active' => 'reseller-portal.sales.voucher'],
                            ['label' => 'Online', 'route' => 'reseller-portal.customers.user-online', 'active' => 'reseller-portal.customers.user-online'],
                            ['label' => 'Isolir', 'route' => 'pelanggan.isolir', 'active' => 'pelanggan.isolir'],
                        ]
                    ],
                    [
                        'label' => 'Data Tagihan',
                        'icon' => 'receipt',
                        'items' => [
                            ['label' => 'SemuaTagihan', 'route' => 'reseller-portal.billing.invoices', 'active' => 'reseller-portal.billing.invoices'],
                            ['label' => 'Pembayaran', 'route' => 'reseller-portal.billing.payments', 'active' => 'reseller-portal.billing.payments'],
                        ]
                    ],
                    [
                        'label' => 'Data Keuangan',
                        'icon' => 'account_balance_wallet',
                        'items' => [
                            ['label' => 'Saldo Deposit', 'route' => 'reseller-portal.finance.balance', 'active' => 'reseller-portal.finance.balance'],
                            ['label' => 'Top Up', 'route' => 'reseller-portal.finance.topup', 'active' => 'reseller-portal.finance.topup'],
                            ['label' => 'Mutasi', 'route' => 'reseller-portal.finance.mutations', 'active' => 'reseller-portal.finance.mutations'],
                        ]
                    ],
                    [
                        'label' => 'Laporan',
                        'icon' => 'bar_chart',
                        'items' => [
                            ['label' => 'Penjualan', 'route' => 'reseller-portal.reports.sales', 'active' => 'reseller-portal.reports.sales'],
                            ['label' => 'Pendapatan', 'route' => 'reseller-portal.reports.revenue', 'active' => 'reseller-portal.reports.revenue'],
                            ['label' => 'Komisi', 'route' => 'reseller-portal.reports.commission', 'active' => 'reseller-portal.reports.commission'],
                        ]
                    ],
                    [
                        'label' => 'Support & Ticket',
                        'icon' => 'support_agent',
                        'items' => [
                          ['label' => 'Tiket Bantuan', 'route' => 'support.ticket', 'active' => 'support.ticket'],
                            ['label' => 'Calon Pelanggan', 'route' => 'crm.leads.index', 'active' => 'crm.leads.*'],
                            ['label' => 'Survey', 'route' => 'crm.surveys.index', 'active' => 'crm.surveys.*'],
                            ['label' => 'Aktivasi Layanan', 'route' => 'crm.activations.index', 'active' => 'crm.activations.*'],
                            ['label' => 'Maintenance', 'route' => 'support.maintenance', 'active' => 'support.maintenance'], 
                        ]
                    ],

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
        // Simple graceful filter: if the route name is not registered, or user has no permission, handle it smoothly.
        // We assume all items are visible unless locked down later.
        $filtered = [];

        foreach ($navigation as $menu) {
            if (isset($menu['groups']) && is_array($menu['groups'])) {
                $filteredGroups = [];
                foreach ($menu['groups'] as $group) {
                    $filteredItems = [];
                    if (isset($group['items']) && is_array($group['items'])) {
                        foreach ($group['items'] as $item) {
                            $route = $item['route'] ?? '';
                            // To prevent breaking UI while routes are being developed, 
                            // we just allow all items through. 
                            // Real permission check would go here later.
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





