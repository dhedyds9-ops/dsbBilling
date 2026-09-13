<aside class="main-sidebar sidebar-dark-primary elevation-4">
    <!-- Brand Logo -->
    <a href="{{ route('noc.overview') }}" class="brand-link">
        <span class="brand-text font-weight-light">NOC Portal</span>
    </a>

    <!-- Sidebar -->
    <div class="sidebar">
        <!-- Sidebar Menu -->
        <nav class="mt-2">
            <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu" data-accordion="false">
                
                <li class="nav-item">
                    <a href="{{ route('noc.overview') }}" class="nav-link {{ request()->routeIs('noc.overview') ? 'active' : '' }}">
                        <i class="nav-icon fas fa-tachometer-alt"></i>
                        <p>NOC Overview</p>
                    </a>
                </li>

                <li class="nav-item">
                    <a href="{{ route('jaringan.monitoring') }}" class="nav-link {{ request()->routeIs('jaringan.monitoring') ? 'active' : '' }}">
                        <i class="nav-icon fas fa-chart-line"></i>
                        <p>Live Monitoring</p>
                    </a>
                </li>

                @can('noc.olt.view')
                <li class="nav-item">
                    <a href="{{ route('noc.olts.index') }}" class="nav-link {{ request()->routeIs('noc.olts.*') ? 'active' : '' }}">
                        <i class="nav-icon fas fa-server"></i>
                        <p>OLT</p>
                    </a>
                </li>
                @endcan

                <li class="nav-item">
                    <a href="{{ route('jaringan.fiber') }}" class="nav-link {{ request()->routeIs('jaringan.fiber') ? 'active' : '' }}">
                        <i class="nav-icon fas fa-network-wired"></i>
                        <p>PON</p>
                    </a>
                </li>

                @can('onu.view')
                <li class="nav-item">
                    <a href="{{ route('noc.onus.index') }}" class="nav-link {{ request()->routeIs('noc.onus.*') ? 'active' : '' }}">
                        <i class="nav-icon fas fa-hdd"></i>
                        <p>ONU</p>
                    </a>
                </li>
                @endcan

                <li class="nav-item">
                    <a href="{{ route('noc.topology.index') }}" class="nav-link {{ request()->routeIs('noc.topology.*') ? 'active' : '' }}">
                        <i class="nav-icon fas fa-project-diagram"></i>
                        <p>Topology</p>
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

                @can('noc.alarms.view')
                <li class="nav-item">
                    <a href="{{ route('noc.alarms.index') }}" class="nav-link {{ request()->routeIs('noc.alarms.*') ? 'active' : '' }}">
                        <i class="nav-icon fas fa-bell"></i>
                        <p>Alarms</p>
                    </a>
                </li>
                @endcan

                <li class="nav-item">
                    <a href="{{ route('support.ticket') }}" class="nav-link {{ request()->routeIs('support.ticket') ? 'active' : '' }}">
                        <i class="nav-icon fas fa-exclamation-triangle"></i>
                        <p>Incidents</p>
                    </a>
                </li>

                @can('onu.remediation.view')
                <li class="nav-item">
                    <a href="#" class="nav-link">
                        <i class="nav-icon fas fa-tools"></i>
                        <p>Remediation</p>
                    </a>
                </li>
                @endcan

                @can('onu.firmware.update')
                <li class="nav-item">
                    <a href="#" class="nav-link">
                        <i class="nav-icon fas fa-microchip"></i>
                        <p>Firmware Jobs</p>
                    </a>
                </li>
                @endcan

                @can('audit.view')
                <li class="nav-item">
                    <a href="{{ route('admin.audit-trail.index') }}" class="nav-link {{ request()->routeIs('admin.audit-trail.*') ? 'active' : '' }}">
                        <i class="nav-icon fas fa-history"></i>
                        <p>Audit</p>
                    </a>
                </li>
                @endcan

            </ul>
        </nav>
    </div>
</aside>






