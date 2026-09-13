<aside class="main-sidebar sidebar-dark-primary elevation-4">
    <!-- Brand Logo -->
    <a href="{{ route('technician.dashboard') }}" class="brand-link">
        <span class="brand-text font-weight-light">Technician Portal</span>
    </a>

    <!-- Sidebar -->
    <div class="sidebar">
        <!-- Sidebar Menu -->
        <nav class="mt-2">
            <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu" data-accordion="false">
                
                <li class="nav-item">
                    <a href="{{ route('technician.dashboard') }}" class="nav-link {{ request()->routeIs('technician.dashboard') ? 'active' : '' }}">
                        <i class="nav-icon fas fa-tools"></i>
                        <p>Technician Overview</p>
                    </a>
                </li>

                <!-- My Jobs / Assigned Customers -->
                <li class="nav-item">
                    <a href="#" class="nav-link">
                        <i class="nav-icon fas fa-tasks"></i>
                        <p>My Jobs</p>
                    </a>
                </li>

                <li class="nav-item">
                    <a href="#" class="nav-link">
                        <i class="nav-icon fas fa-users"></i>
                        <p>Assigned Customers</p>
                    </a>
                </li>

                @can('onu.view')
                <li class="nav-item">
                    <a href="#" class="nav-link">
                        <i class="nav-icon fas fa-hdd"></i>
                        <p>Assigned ONU</p>
                    </a>
                </li>
                @endcan

                <li class="nav-item">
                    <a href="{{ route('technician.installation.wizard') }}" class="nav-link {{ request()->routeIs('technician.installation.*') ? 'active' : '' }}">
                        <i class="nav-icon fas fa-magic"></i>
                        <p>Installation</p>
                    </a>
                </li>

                @can('onu.configure.wifi')
                <li class="nav-item">
                    <a href="#" class="nav-link">
                        <i class="nav-icon fas fa-wifi"></i>
                        <p>ONU Configuration</p>
                    </a>
                </li>
                @endcan

                <li class="nav-item">
                    <a href="#" class="nav-link">
                        <i class="nav-icon fas fa-stethoscope"></i>
                        <p>Troubleshooting</p>
                    </a>
                </li>

                @can('onu.diagnose')
                <li class="nav-item">
                    <a href="#" class="nav-link">
                        <i class="nav-icon fas fa-bolt"></i>
                        <p>Optical Power</p>
                    </a>
                </li>
                @endcan

                @can('onu.remediation.view')
                <li class="nav-item">
                    <a href="#" class="nav-link">
                        <i class="nav-icon fas fa-wrench"></i>
                        <p>Remediation</p>
                    </a>
                </li>
                @endcan

                <li class="nav-item">
                    <a href="#" class="nav-link">
                        <i class="nav-icon fas fa-history"></i>
                        <p>Job History</p>
                    </a>
                </li>

            </ul>
        </nav>
    </div>
</aside>






