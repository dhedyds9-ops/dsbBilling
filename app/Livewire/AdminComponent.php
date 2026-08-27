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
    /**
     * Default activeTab (boleh dioverride child dengan value spesifik misal 'midtrans', 'pppoe').
     * Fallback disediakan lewat __call() untuk child yang TIDAK mendefinisikan method setActiveTab() sendiri.
     * Child yang BUTUH custom behavior (misal panggil resetTabPages()) boleh override method setActiveTab() sendiri seperti biasa.
     */
    public string $activeTab = 'default';
    
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

    /**
     * PHP Magic Method __call: HANYA dijalankan JIKA child class TIDAK memiliki method dengan nama tersebut.
     *
     * ✅ Ini adalah cara 100% BACKWARD COMPATIBLE tanpa LSP conflict PHP 8.2:
     *    - 22 child class yang SUDAH PUNYA override setActiveTab() sendiri (beda signature/behavior)
     *      → TIDAK TERGANGGU sama sekali (pakai punya child sendiri)
     *    - Child class yang TIDAK PUNYA setActiveTab() method (misal PaymentGateway, Koneksi Index)
     *      → Fallback ke sini untuk set property activeTab
     *
     * @param  string  $method
     * @param  array   $args
     * @return mixed
     */
    public function __call($method, $params)
    {
        $args = (array) $params;
        // Fallback global setActiveTab(string $tab): HANYA untuk child yang TIDAK override method ini
        if ($method === 'setActiveTab' && count($args) >= 1) {
            $tab = trim((string)($args[0] ?? ''));
            if ($tab === '') return null;
            if (strlen($tab) > 80) $tab = substr($tab, 0, 80);
            $this->activeTab = $tab;
            return null;
        }

        // Fallback setActiveTabWithWhitelist(string $tab, array $allowed)
        if ($method === 'setActiveTabWithWhitelist' && count($args) >= 2) {
            $tab = trim((string)($args[0] ?? ''));
            $allowed = (array)($args[1] ?? []);
            if (count($allowed) > 0 && !in_array($tab, $allowed, true)) {
                $tab = (string)($allowed[array_key_first($allowed)] ?? $this->activeTab);
            }
            if ($tab !== '') $this->activeTab = $tab;
            return null;
        }

        // Default behavior untuk method tidak dikenal (mirip stdClass)
        throw new \BadMethodCallException(sprintf(
            'Method %s::%s(%s) not found.',
            static::class,
            $method,
            implode(', ', array_map(fn($v) => get_debug_type($v), $args))
        ));
    }

    public function setBreadcrumbs(array $breadcrumbs): void
    {
        $this->breadcrumbs = $breadcrumbs;
    }
}
