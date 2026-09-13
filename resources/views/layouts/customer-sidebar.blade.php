<aside class="main-sidebar sidebar-dark-primary elevation-4">
    <!-- Brand Logo -->
    <a href="{{ route('customer-portal.dashboard') }}" class="brand-link">
        <span class="brand-text font-weight-light">Client Portal</span>
    </a>

    <!-- Sidebar -->
    <div class="sidebar">
        <!-- Sidebar Menu -->
        <nav class="mt-2">
            <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu" data-accordion="false">
                
                <li class="nav-item">
                    <a href="{{ route('customer-portal.dashboard') }}" class="nav-link {{ request()->routeIs('customer-portal.dashboard') ? 'active' : '' }}">
                        <i class="nav-icon fas fa-tachometer-alt"></i>
                        <p>Dashboard</p>
                    </a>
                </li>

                <!-- Tagihan & Pembayaran -->
                <li class="nav-item">
                    <a href="{{ route('customer-portal.billing.invoice-list') }}" class="nav-link {{ request()->routeIs('customer-portal.billing.invoice-list') ? 'active' : '' }}">
                        <i class="nav-icon fas fa-file-invoice-dollar"></i>
                        <p>Tagihan Saya</p>
                    </a>
                </li>

                <!-- Self Service -->
                <li class="nav-item">
                    <a href="{{ route('customer-portal.self-service.change-pppoe-password') }}" class="nav-link {{ request()->routeIs('customer-portal.self-service.change-pppoe-password') ? 'active' : '' }}">
                        <i class="nav-icon fas fa-wifi"></i>
                        <p>Ganti Password PPPoE</p>
                    </a>
                </li>
                
                <li class="nav-item">
                    <a href="{{ route('customer-portal.self-service.change-onu-wifi-password') }}" class="nav-link {{ request()->routeIs('customer-portal.self-service.change-onu-wifi-password') ? 'active' : '' }}">
                        <i class="nav-icon fas fa-router"></i>
                        <p>Ganti Password WiFi</p>
                    </a>
                </li>

                <li class="nav-item">
                    <a href="{{ route('customer-portal.self-service.change-hotspot-credentials') }}" class="nav-link {{ request()->routeIs('customer-portal.self-service.change-hotspot-credentials') ? 'active' : '' }}">
                        <i class="nav-icon fas fa-signal"></i>
                        <p>Credentials Hotspot</p>
                    </a>
                </li>

                <!-- Support -->
                <li class="nav-item">
                    <a href="#" class="nav-link">
                        <i class="nav-icon fas fa-ticket-alt"></i>
                        <p>Tiket Saya (Segera)</p>
                    </a>
                </li>

                <li class="nav-item">
                    <a href="#" class="nav-link">
                        <i class="nav-icon fas fa-question-circle"></i>
                        <p>Bantuan (Segera)</p>
                    </a>
                </li>

                <!-- Profil Saya -->
                <li class="nav-item">
                    <a href="{{ route('profile.edit') }}" class="nav-link {{ request()->routeIs('profile.edit') ? 'active' : '' }}">
                        <i class="nav-icon fas fa-user"></i>
                        <p>Profil Saya</p>
                    </a>
                </li>

            </ul>
        </nav>
    </div>
</aside>






