<aside class="main-sidebar sidebar-dark-primary elevation-4">
    <!-- Brand Logo -->
    <a href="<?php echo e(route('noc.overview')); ?>" class="brand-link">
        <span class="brand-text font-weight-light">NOC Portal</span>
    </a>

    <!-- Sidebar -->
    <div class="sidebar">
        <!-- Sidebar Menu -->
        <nav class="mt-2">
            <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu" data-accordion="false">
                
                <li class="nav-item">
                    <a href="<?php echo e(route('noc.overview')); ?>" class="nav-link <?php echo e(request()->routeIs('noc.overview') ? 'active' : ''); ?>">
                        <i class="nav-icon fas fa-tachometer-alt"></i>
                        <p>NOC Overview</p>
                    </a>
                </li>

                <li class="nav-item">
                    <a href="<?php echo e(route('jaringan.monitoring')); ?>" class="nav-link <?php echo e(request()->routeIs('jaringan.monitoring') ? 'active' : ''); ?>">
                        <i class="nav-icon fas fa-chart-line"></i>
                        <p>Live Monitoring</p>
                    </a>
                </li>

                <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('noc.olt.view')): ?>
                <li class="nav-item">
                    <a href="<?php echo e(route('noc.olts.index')); ?>" class="nav-link <?php echo e(request()->routeIs('noc.olts.*') ? 'active' : ''); ?>">
                        <i class="nav-icon fas fa-server"></i>
                        <p>OLT</p>
                    </a>
                </li>
                <?php endif; ?>

                <li class="nav-item">
                    <a href="<?php echo e(route('jaringan.fiber')); ?>" class="nav-link <?php echo e(request()->routeIs('jaringan.fiber') ? 'active' : ''); ?>">
                        <i class="nav-icon fas fa-network-wired"></i>
                        <p>PON</p>
                    </a>
                </li>

                <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('onu.view')): ?>
                <li class="nav-item">
                    <a href="<?php echo e(route('noc.onus.index')); ?>" class="nav-link <?php echo e(request()->routeIs('noc.onus.*') ? 'active' : ''); ?>">
                        <i class="nav-icon fas fa-hdd"></i>
                        <p>ONU</p>
                    </a>
                </li>
                <?php endif; ?>

                <li class="nav-item">
                    <a href="<?php echo e(route('noc.topology.index')); ?>" class="nav-link <?php echo e(request()->routeIs('noc.topology.*') ? 'active' : ''); ?>">
                        <i class="nav-icon fas fa-project-diagram"></i>
                        <p>Topology</p>
                    </a>
                </li>

                <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('onu.diagnose')): ?>
                <li class="nav-item">
                    <a href="#" class="nav-link">
                        <i class="nav-icon fas fa-bolt"></i>
                        <p>Optical Power</p>
                    </a>
                </li>
                <?php endif; ?>

                <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('noc.alarms.view')): ?>
                <li class="nav-item">
                    <a href="<?php echo e(route('noc.alarms.index')); ?>" class="nav-link <?php echo e(request()->routeIs('noc.alarms.*') ? 'active' : ''); ?>">
                        <i class="nav-icon fas fa-bell"></i>
                        <p>Alarms</p>
                    </a>
                </li>
                <?php endif; ?>

                <li class="nav-item">
                    <a href="<?php echo e(route('support.ticket')); ?>" class="nav-link <?php echo e(request()->routeIs('support.ticket') ? 'active' : ''); ?>">
                        <i class="nav-icon fas fa-exclamation-triangle"></i>
                        <p>Incidents</p>
                    </a>
                </li>

                <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('onu.remediation.view')): ?>
                <li class="nav-item">
                    <a href="#" class="nav-link">
                        <i class="nav-icon fas fa-tools"></i>
                        <p>Remediation</p>
                    </a>
                </li>
                <?php endif; ?>

                <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('onu.firmware.update')): ?>
                <li class="nav-item">
                    <a href="#" class="nav-link">
                        <i class="nav-icon fas fa-microchip"></i>
                        <p>Firmware Jobs</p>
                    </a>
                </li>
                <?php endif; ?>

                <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('audit.view')): ?>
                <li class="nav-item">
                    <a href="<?php echo e(route('admin.audit-trail.index')); ?>" class="nav-link <?php echo e(request()->routeIs('admin.audit-trail.*') ? 'active' : ''); ?>">
                        <i class="nav-icon fas fa-history"></i>
                        <p>Audit</p>
                    </a>
                </li>
                <?php endif; ?>

            </ul>
        </nav>
    </div>
</aside>






<?php /**PATH D:\dsBilling\resources\views\layouts\noc-sidebar.blade.php ENDPATH**/ ?>