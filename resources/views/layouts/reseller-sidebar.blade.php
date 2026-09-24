<aside class="main-sidebar sidebar-dark-primary elevation-4">
    <!-- Brand Logo -->
    <a href="{{ route('reseller-portal.dashboard') }}" class="brand-link">
        <span class="brand-text font-weight-light">Reseller Portal</span>
    </a>

    <!-- Sidebar -->
    <div class="sidebar">
        <!-- Sidebar Menu -->
        <nav class="mt-2">
            <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu" data-accordion="false">
                
                <li class="nav-item">
                    <a href="{{ route('reseller-portal.dashboard') }}" class="nav-link {{ request()->routeIs('reseller-portal.dashboard') ? 'active' : '' }}">
                        <i class="nav-icon fas fa-tachometer-alt"></i>
                        <p>Dashboard</p>
                    </a>
                </li>

                @can('customer.view')
                <li class="nav-item">
                    <a href="{{ route('reseller-portal.customers.index') }}" class="nav-link {{ request()->routeIs('reseller-portal.customers.*') ? 'active' : '' }}">
                        <i class="nav-icon fas fa-users"></i>
                        <p>Pelanggan</p>
                    </a>
                </li>
                @endcan

                @can('service.view')
                <li class="nav-item">
                    <a href="{{ route('reseller-portal.service-profiles.index') }}" class="nav-link {{ request()->routeIs('reseller-portal.service-profiles.*') ? 'active' : '' }}">
                        <i class="nav-icon fas fa-cubes"></i>
                        <p>Layanan</p>
                    </a>
                </li>
                @endcan

                @can('pppoe.view')
                <li class="nav-item">
                    <a href="{{ route('reseller-portal.pppoe-users.index') }}" class="nav-link {{ request()->routeIs('reseller-portal.pppoe-users.*') ? 'active' : '' }}">
                        <i class="nav-icon fas fa-network-wired"></i>
                        <p>PPPoE</p>
                    </a>
                </li>
                @endcan

                @can('hotspot.view')
                <li class="nav-item">
                    <a href="{{ route('reseller-portal.hotspot-users.index') }}" class="nav-link {{ request()->routeIs('reseller-portal.hotspot-users.*') ? 'active' : '' }}">
                        <i class="nav-icon fas fa-wifi"></i>
                        <p>Hotspot</p>
                    </a>
                </li>
                @endcan

                @can('voucher.view')
                <li class="nav-item">
                    <a href="{{ route('reseller-portal.vouchers.index') }}" class="nav-link {{ request()->routeIs('reseller-portal.vouchers.*') ? 'active' : '' }}">
                        <i class="nav-icon fas fa-ticket-alt"></i>
                        <p>Voucher</p>
                    </a>
                </li>
                @endcan

                @can('billing.view')
                <li class="nav-item">
                    <a href="{{ route('reseller-portal.invoices.index') }}" class="nav-link {{ request()->routeIs('reseller-portal.invoices.*') ? 'active' : '' }}">
                        <i class="nav-icon fas fa-file-invoice-dollar"></i>
                        <p>Tagihan</p>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('reseller-portal.payments.index') }}" class="nav-link {{ request()->routeIs('reseller-portal.payments.*') ? 'active' : '' }}">
                        <i class="nav-icon fas fa-money-bill-wave"></i>
                        <p>Pembayaran</p>
                    </a>
                </li>
                @endcan

                @can('commission.view')
                <li class="nav-item">
                    <a href="#" class="nav-link">
                        <i class="nav-icon fas fa-hand-holding-usd"></i>
                        <p>Komisi</p>
                    </a>
                </li>
                @endcan

                @can('sales.manage')
                <li class="nav-item">
                    <a href="#" class="nav-link">
                        <i class="nav-icon fas fa-briefcase"></i>
                        <p>Sales</p>
                    </a>
                </li>
                @endcan

                @can('report.view')
                <li class="nav-item">
                    <a href="{{ route('reseller-portal.reports.index') }}" class="nav-link {{ request()->routeIs('reseller-portal.reports.*') ? 'active' : '' }}">
                        <i class="nav-icon fas fa-chart-bar"></i>
                        <p>Laporan</p>
                    </a>
                </li>
                @endcan

                <li class="nav-item">
                    <a href="#" class="nav-link">
                        <i class="nav-icon fas fa-headset"></i>
                        <p>Support</p>
                    </a>
                </li>

                <li class="nav-item">
                    <a href="#" class="nav-link">
                        <i class="nav-icon fas fa-user-circle"></i>
                        <p>Profil</p>
                    </a>
                </li>

            </ul>
        </nav>
    </div>
</aside>






