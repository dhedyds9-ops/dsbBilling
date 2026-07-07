<?php

namespace App\View\Composers;

use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AdminLayoutComposer
{
    public function compose(View $view): void
    {
        $navigation = [
            [
                'label' => 'Dashboard',
                'items' => [
                    [
                        'label' => 'Overview',
                        'icon' => 'home',
                        'url' => route('dashboard'),
                        'active' => 'dashboard',
                    ],
                ],
            ],
            [
                'label' => 'CRM',
                'items' => [
                    [
                        'label' => 'Leads',
                        'icon' => 'user-plus',
                        'url' => route('crm.leads.index'),
                        'active' => 'crm.leads*',
                    ],
                    [
                        'label' => 'Prospects',
                        'icon' => 'user-check',
                        'url' => route('onboarding.prospects.index'),
                        'active' => 'onboarding.prospects*',
                    ],
                    [
                        'label' => 'Coverage Checks',
                        'icon' => 'map-marker-alt',
                        'url' => route('onboarding.coverage-checks.index'),
                        'active' => 'onboarding.coverage-checks*',
                    ],
                    [
                        'label' => 'Surveys',
                        'icon' => 'clipboard-list',
                        'url' => route('crm.surveys.index'),
                        'active' => 'crm.surveys*',
                    ],
                    [
                        'label' => 'Quotations',
                        'icon' => 'file-text',
                        'url' => route('crm.quotations.index'),
                        'active' => 'crm.quotations*',
                    ],
                    [
                        'label' => 'Contracts',
                        'icon' => 'file-contract',
                        'url' => route('crm.contracts.index'),
                        'active' => 'crm.contracts*',
                    ],
                    [
                        'label' => 'Installations',
                        'icon' => 'tool',
                        'url' => route('crm.installations.index'),
                        'active' => 'crm.installations*',
                    ],
                    [
                        'label' => 'Quality Control',
                        'icon' => 'clipboard-check',
                        'url' => route('onboarding.qc.index'),
                        'active' => 'onboarding.qc*',
                    ],
                    [
                        'label' => 'Activations',
                        'icon' => 'check-circle',
                        'url' => route('crm.activations.index'),
                        'active' => 'crm.activations*',
                    ],
                    [
                        'label' => 'Customers',
                        'icon' => 'users',
                        'url' => route('crm.customers.index'),
                        'active' => 'crm.customers*',
                    ],
                ],
            ],
            [
                'label' => 'Billing',
                'items' => [
                    [
                        'label' => 'Invoices',
                        'icon' => 'file-invoice',
                        'url' => route('billing.invoices.index'),
                        'active' => 'billing.invoices*',
                        'badge' => 12,
                    ],
                    [
                        'label' => 'Payments',
                        'icon' => 'credit-card',
                        'url' => route('billing.payments.index'),
                        'active' => 'billing.payments*',
                    ],
                ],
            ],
            [
                'label' => 'Network',
                'items' => [
                    [
                        'label' => 'Vendors',
                        'icon' => 'building',
                        'url' => route('isp.vendors.index'),
                        'active' => 'isp.vendors*',
                    ],
                    [
                        'label' => 'Towers',
                        'icon' => 'map-pin',
                        'url' => route('isp.towers.index'),
                        'active' => 'isp.towers*',
                    ],
                    [
                        'label' => 'POPs',
                        'icon' => 'server',
                        'url' => route('isp.pops.index'),
                        'active' => 'isp.pops*',
                    ],
                    [
                        'label' => 'OLTs',
                        'icon' => 'network-wired',
                        'url' => route('isp.olts.index'),
                        'active' => 'isp.olts*',
                    ],
                    [
                        'label' => 'ODCs',
                        'icon' => 'box',
                        'url' => route('isp.odcs.index'),
                        'active' => 'isp.odcs*',
                    ],
                    [
                        'label' => 'ODPs',
                        'icon' => 'location-dot',
                        'url' => route('isp.odps.index'),
                        'active' => 'isp.odps*',
                    ],
                    [
                        'label' => 'ONUs',
                        'icon' => 'device-router',
                        'url' => route('isp.onus.index'),
                        'active' => 'isp.onus*',
                    ],
                ],
            ],
            [
                'label' => 'GIS Platform',
                'items' => [
                    [
                        'label' => 'GIS Dashboard',
                        'icon' => 'map',
                        'url' => route('gis.index'),
                        'active' => 'gis*',
                    ],
                ],
            ],
        ];

        $permissions = [
            'view-dashboard',
            'view-crm',
            'view-billing',
            'view-network',
            'view-gis',
        ];

        $user = Auth::user();
        $userData = $user ? [
            'name' => $user->name ?? 'User',
            'email' => $user->email ?? 'user@example.com',
            'avatar' => null,
            'role' => 'Admin',
        ] : null;

        $view->with([
            'navigation' => $navigation,
            'permissions' => $permissions,
            'favorites' => [],
            'breadcrumbs' => [],
            'user' => $userData,
        ]);
    }
}
