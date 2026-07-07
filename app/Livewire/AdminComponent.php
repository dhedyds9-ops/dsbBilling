<?php

namespace App\Livewire;

use Livewire\Component;
use Illuminate\Support\Facades\Auth;

abstract class AdminComponent extends Component
{
    public function configure(): void
    {
        $this->layout('layouts.enterprise');
    }

    public string $sidebarCollapsed = 'false';
    public string $darkMode = 'false';
    public string $activeModule = '';
    public string $activePage = '';
    
    public array $breadcrumbs = [];
    public array $favorites = [];
    public array $notifications = [];

    protected $listeners = [
        'toggleSidebar' => 'toggleSidebar',
        'toggleDarkMode' => 'toggleDarkMode',
        'refreshPage' => '$refresh',
    ];

    public function mount()
    {
        $this->darkMode = session('dark_mode', 'false');
        $this->sidebarCollapsed = session('sidebar_collapsed', 'false');

        if ($this->darkMode === 'true') {
            $this->dispatch('setDarkMode', true);
        }
    }

    public function getNavigationProperty()
    {
        return [
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
                        'label' => 'Coverage Check',
                        'icon' => 'map-pin-check',
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
                        'icon' => 'check-double',
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
                'label' => 'AAA',
                'items' => [
                    [
                        'label' => 'PPPoE Users',
                        'icon' => 'wifi',
                        'url' => '#',
                        'active' => 'aaa.pppoe*',
                    ],
                    [
                        'label' => 'Hotspot Users',
                        'icon' => 'signal',
                        'url' => '#',
                        'active' => 'aaa.hotspot*',
                    ],
                    [
                        'label' => 'Vouchers',
                        'icon' => 'ticket',
                        'url' => '#',
                        'active' => 'aaa.voucher*',
                    ],
                    [
                        'label' => 'Radius NAS',
                        'icon' => 'server',
                        'url' => '#',
                        'active' => 'aaa.nas*',
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
                    [
                        'label' => 'Billing Cycles',
                        'icon' => 'calendar',
                        'url' => '#',
                        'active' => 'billing.cycle*',
                    ],
                ],
            ],
            [
                'label' => 'Network Infrastructure',
                'items' => [
                    [
                        'label' => 'Vendors',
                        'icon' => 'building',
                        'url' => route('isp.vendors.index'),
                        'active' => 'isp.vendors*',
                    ],
                    [
                        'label' => 'Towers',
                        'icon' => 'tower-cell',
                        'url' => route('isp.towers.index'),
                        'active' => 'isp.towers*',
                    ],
                    [
                        'label' => 'POPs',
                        'icon' => 'building-2',
                        'url' => route('isp.pops.index'),
                        'active' => 'isp.pops*',
                    ],
                    [
                        'label' => 'OLTs',
                        'icon' => 'hard-drive',
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
                        'icon' => 'circle-dashed',
                        'url' => route('isp.odps.index'),
                        'active' => 'isp.odps*',
                    ],
                    [
                        'label' => 'ONUs',
                        'icon' => 'device-mobile',
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
                    [
                        'label' => 'Fiber Map',
                        'icon' => 'route',
                        'url' => '#',
                        'active' => 'gis.fiber*',
                    ],
                    [
                        'label' => 'Tower Map',
                        'icon' => 'map-pin',
                        'url' => '#',
                        'active' => 'gis.tower*',
                    ],
                ],
            ],
            [
                'label' => 'Monitoring & NOC',
                'items' => [
                    [
                        'label' => 'Alarms',
                        'icon' => 'bell',
                        'url' => '#',
                        'active' => 'noc.alarm*',
                        'badge' => 5,
                    ],
                    [
                        'label' => 'Tickets',
                        'icon' => 'ticket',
                        'url' => '#',
                        'active' => 'support.ticket*',
                    ],
                ],
            ],
            [
                'label' => 'Administration',
                'items' => [
                    [
                        'label' => 'Users',
                        'icon' => 'users',
                        'url' => '#',
                        'active' => 'admin.user*',
                    ],
                    [
                        'label' => 'Roles & Permissions',
                        'icon' => 'shield',
                        'url' => '#',
                        'active' => 'admin.role*',
                    ],
                    [
                        'label' => 'Audit Trail',
                        'icon' => 'history',
                        'url' => '#',
                        'active' => 'admin.audit*',
                    ],
                ],
            ],
        ];
    }

    public function getPermissionsProperty()
    {
        return [
            'view-dashboard',
            'view-crm',
            'view-billing',
            'view-network',
            'view-gis',
        ];
    }

    public function toggleSidebar(): void
    {
        $this->sidebarCollapsed = $this->sidebarCollapsed === 'true' ? 'false' : 'true';
        session(['sidebar_collapsed' => $this->sidebarCollapsed]);
    }

    public function toggleDarkMode(): void
    {
        $this->darkMode = $this->darkMode === 'true' ? 'false' : 'true';
        session(['dark_mode' => $this->darkMode]);
        $this->dispatch('setDarkMode', $this->darkMode === 'true');
    }

    public function logout(): void
    {
        Auth::logout();
        session()->invalidate();
        session()->regenerateToken();
        redirect('/login');
    }

    public function setActiveModule(string $module): void
    {
        $this->activeModule = $module;
    }

    public function setActivePage(string $page): void
    {
        $this->activePage = $page;
    }
    
    public function setBreadcrumbs(array $breadcrumbs): void
    {
        $this->breadcrumbs = $breadcrumbs;
    }
}
