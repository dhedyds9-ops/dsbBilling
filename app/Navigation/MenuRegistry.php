<?php

namespace App\Navigation;

use App\Models\ACS\ACSAlarm;
use App\Models\Billing\Invoice;
use Illuminate\Support\Facades\Cache;

class MenuRegistry
{
    public static function getNavigation(): array
    {
        $navigation = Cache::remember('wifi_nan_navigation.v2', 3600, function () {
            return self::buildNavigation();
        });

        return self::applyBadges($navigation);
    }

    private static function buildNavigation(): array
    {
        return [
            [
                'label' => 'Dashboard',
                'icon' => 'home',
                'groups' => [
                    [
                        'label' => 'Dashboard',
                        'items' => [
                            [
                                'label' => 'Dashboard',
                                'route' => 'dashboard',
                                'icon' => 'home',
                                'active' => 'dashboard'
                            ],
                        ]
                    ],
                ]
            ],

            [
                'label' => 'Internet',
                'icon' => 'wifi',
                'groups' => [
                    [
                        'label' => 'Profile Paket',
                        'items' => [
                            [
                                'label' => 'Paket Internet',
                                'route' => 'isp.service-profiles.index',
                                'icon' => 'activity',
                                'active' => 'isp.service-profiles.*'
                            ],
                        ]
                    ],
                    [
                        'label' => 'PPPoE',
                        'items' => [
                            [
                                'label' => 'User PPPoE',
                                'route' => 'isp.pppoe-users.index',
                                'icon' => 'users',
                                'active' => 'isp.pppoe-users.*'
                            ],
                            [
                                'label' => 'PPPoE Online',
                                'route' => null,
                                'icon' => 'activity',
                                'active' => null
                            ],
                        ]
                    ],
                    [
                        'label' => 'Hotspot',
                        'items' => [
                            [
                                'label' => 'User Hotspot',
                                'route' => 'isp.hotspot-users.index',
                                'icon' => 'users',
                                'active' => 'isp.hotspot-users.*'
                            ],
                            [
                                'label' => 'Hotspot Online',
                                'route' => null,
                                'icon' => 'activity',
                                'active' => null
                            ],
                        ]
                    ],
                    [
                        'label' => 'Voucher',
                        'items' => [
                            [
                                'label' => 'Voucher',
                                'route' => 'isp.vouchers.index',
                                'icon' => 'ticket',
                                'active' => 'isp.vouchers.*'
                            ],
                            [
                                'label' => 'Voucher Online',
                                'route' => null,
                                'icon' => 'activity',
                                'active' => null
                            ],
                            [
                                'label' => 'E-Voucher',
                                'route' => null,
                                'icon' => 'smartphone',
                                'active' => null
                            ],
                        ]
                    ],
                ]
            ],

            [
                'label' => 'GIS',
                'icon' => 'map',
                'groups' => [
                    [
                        'label' => 'GIS',
                        'items' => [
                            [
                                'label' => 'Peta Pelanggan',
                                'route' => 'gis.map',
                                'icon' => 'map-pin',
                                'active' => 'gis.map'
                            ],
                        ]
                    ],
                ]
            ],

            [
                'label' => 'Billing',
                'icon' => 'credit-card',
                'groups' => [
                    [
                        'label' => 'Billing',
                        'items' => [
                            [
                                'label' => 'Invoice',
                                'route' => 'billing.invoices.index',
                                'icon' => 'file-invoice',
                                'badge_key' => 'billing.invoices.pending',
                                'active' => 'billing.invoices.*'
                            ],
                            [
                                'label' => 'Pembayaran',
                                'route' => 'billing.payments.index',
                                'icon' => 'credit-card',
                                'active' => 'billing.payments.*'
                            ],
                        ]
                    ],
                ]
            ],

            [
                'label' => 'MikroTik',
                'icon' => 'server',
                'groups' => [
                    [
                        'label' => 'Router (NAS)',
                        'items' => [
                            [
                                'label' => 'Router',
                                'route' => 'isp.routers.index',
                                'icon' => 'server',
                                'active' => 'isp.routers.*'
                            ],
                            [
                                'label' => 'Monitoring',
                                'route' => null,
                                'icon' => 'activity',
                                'active' => null
                            ],
                            [
                                'label' => 'API Test',
                                'route' => null,
                                'icon' => 'terminal',
                                'active' => null
                            ],
                            [
                                'label' => 'Radius Test',
                                'route' => null,
                                'icon' => 'activity',
                                'active' => null
                            ],
                            [
                                'label' => 'Backup',
                                'route' => null,
                                'icon' => 'hard-drive',
                                'active' => null
                            ],
                            [
                                'label' => 'Restore',
                                'route' => null,
                                'icon' => 'refresh-cw',
                                'active' => null
                            ],
                            [
                                'label' => 'Log Router',
                                'route' => null,
                                'icon' => 'file-text',
                                'active' => null
                            ],
                        ]
                    ],
                ]
            ],

            [
                'label' => 'Network',
                'icon' => 'network',
                'groups' => [
                    [
                        'label' => 'Network',
                        'items' => [
                            [
                                'label' => 'OLT',
                                'route' => 'isp.olts.index',
                                'icon' => 'server',
                                'active' => 'isp.olts.*'
                            ],
                            [
                                'label' => 'ONU',
                                'route' => 'isp.onus.index',
                                'icon' => 'wifi',
                                'active' => 'isp.onus.*'
                            ],
                            [
                                'label' => 'ODP',
                                'route' => 'isp.odps.index',
                                'icon' => 'map-pin',
                                'active' => 'isp.odps.*'
                            ],
                            [
                                'label' => 'ODC',
                                'route' => 'isp.odcs.index',
                                'icon' => 'box',
                                'active' => 'isp.odcs.*'
                            ],
                            [
                                'label' => 'POP',
                                'route' => 'isp.pops.index',
                                'icon' => 'building-2',
                                'active' => 'isp.pops.*'
                            ],
                            [
                                'label' => 'Tower',
                                'route' => 'isp.towers.index',
                                'icon' => 'map-pin',
                                'active' => 'isp.towers.*'
                            ],
                        ]
                    ],
                ]
            ],

            [
                'label' => 'GenieACS',
                'icon' => 'wifi',
                'groups' => [
                    [
                        'label' => 'GenieACS',
                        'items' => [
                            [
                                'label' => 'Dashboard',
                                'route' => 'acs.dashboard',
                                'icon' => 'speedometer',
                                'active' => 'acs.dashboard'
                            ],
                            [
                                'label' => 'Devices',
                                'route' => 'acs.devices.index',
                                'icon' => 'server',
                                'active' => 'acs.devices.*'
                            ],
                            [
                                'label' => 'Tasks',
                                'route' => 'acs.tasks.index',
                                'icon' => 'check-square',
                                'active' => 'acs.tasks.*'
                            ],
                        ]
                    ],
                ]
            ],

            [
                'label' => 'Reports',
                'icon' => 'bar-chart-2',
                'groups' => [
                    [
                        'label' => 'Reports',
                        'items' => [
                            [
                                'label' => 'Pendapatan',
                                'route' => null,
                                'icon' => 'dollar-sign',
                                'active' => null
                            ],
                            [
                                'label' => 'Piutang',
                                'route' => null,
                                'icon' => 'alert-circle',
                                'active' => null
                            ],
                            [
                                'label' => 'Online User',
                                'route' => null,
                                'icon' => 'users',
                                'active' => null
                            ],
                            [
                                'label' => 'Radius Accounting',
                                'route' => null,
                                'icon' => 'activity',
                                'active' => null
                            ],
                        ]
                    ],
                ]
            ],

            [
                'label' => 'Settings',
                'icon' => 'settings',
                'groups' => [
                    [
                        'label' => 'Settings',
                        'items' => [
                            [
                                'label' => 'FreeRADIUS',
                                'route' => null,
                                'icon' => 'server-stack',
                                'active' => null
                            ],
                            [
                                'label' => 'MikroTik',
                                'route' => null,
                                'icon' => 'server',
                                'active' => null
                            ],
                            [
                                'label' => 'Payment Gateway',
                                'route' => null,
                                'icon' => 'credit-card',
                                'active' => null
                            ],
                            [
                                'label' => 'WhatsApp Gateway',
                                'route' => null,
                                'icon' => 'message-circle',
                                'active' => null
                            ],
                            [
                                'label' => 'System',
                                'route' => 'settings',
                                'icon' => 'sliders',
                                'active' => 'settings'
                            ],
                        ]
                    ],
                ]
            ],
        ];
    }

    private static function applyBadges(array $navigation): array
    {
        $resolvers = [
            'billing.invoices.pending' => function (): ?int {
                try {
                    return Invoice::query()
                        ->where('status', '!=', 'paid')
                        ->count();
                } catch (\Throwable $e) {
                    return null;
                }
            },
            'acs.alarms.active' => function (): ?int {
                try {
                    return ACSAlarm::query()
                        ->where('status', 'active')
                        ->count();
                } catch (\Throwable $e) {
                    return null;
                }
            },
        ];

        foreach ($navigation as $categoryIndex => $category) {
            foreach (($category['groups'] ?? []) as $groupIndex => $group) {
                foreach (($group['items'] ?? []) as $itemIndex => $item) {
                    $badgeKey = $item['badge_key'] ?? null;
                    if (! $badgeKey || ! isset($resolvers[$badgeKey])) {
                        continue;
                    }

                    $badgeValue = Cache::remember("wifi_nan_badge.{$badgeKey}", 60, $resolvers[$badgeKey]);

                    $navigation[$categoryIndex]['groups'][$groupIndex]['items'][$itemIndex]['badge'] = is_numeric($badgeValue)
                        ? (int) $badgeValue
                        : null;
                }
            }
        }

        return $navigation;
    }
}
