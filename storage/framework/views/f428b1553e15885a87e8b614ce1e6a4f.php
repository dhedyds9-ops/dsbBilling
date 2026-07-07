<div class="max-w-7xl mx-auto mb-3">
    <!-- Breadcrumbs -->
    <?php if (isset($component)) { $__componentOriginal1a2164c88256e2df02baa87be70e8a2b = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal1a2164c88256e2df02baa87be70e8a2b = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.breadcrumbs','data' => ['crumbs' => [
        ['label' => 'Dashboard', 'url' => route('admin.dashboard')],
        ['label' => 'ISP', 'url' => '#'],
        ['label' => 'Paket Internet', 'url' => route('isp.service-profiles.index')],
        ['label' => $profile->name, 'url' => '#']
    ]]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.breadcrumbs'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['crumbs' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute([
        ['label' => 'Dashboard', 'url' => route('admin.dashboard')],
        ['label' => 'ISP', 'url' => '#'],
        ['label' => 'Paket Internet', 'url' => route('isp.service-profiles.index')],
        ['label' => $profile->name, 'url' => '#']
    ])]); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal1a2164c88256e2df02baa87be70e8a2b)): ?>
<?php $attributes = $__attributesOriginal1a2164c88256e2df02baa87be70e8a2b; ?>
<?php unset($__attributesOriginal1a2164c88256e2df02baa87be70e8a2b); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal1a2164c88256e2df02baa87be70e8a2b)): ?>
<?php $component = $__componentOriginal1a2164c88256e2df02baa87be70e8a2b; ?>
<?php unset($__componentOriginal1a2164c88256e2df02baa87be70e8a2b); ?>
<?php endif; ?>

    <!-- Feedback Alerts -->
    <?php if (isset($component)) { $__componentOriginal49d2c764cd006c1bf28fb7e122731888 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal49d2c764cd006c1bf28fb7e122731888 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.feedback.alert','data' => []] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('feedback.alert'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal49d2c764cd006c1bf28fb7e122731888)): ?>
<?php $attributes = $__attributesOriginal49d2c764cd006c1bf28fb7e122731888; ?>
<?php unset($__attributesOriginal49d2c764cd006c1bf28fb7e122731888); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal49d2c764cd006c1bf28fb7e122731888)): ?>
<?php $component = $__componentOriginal49d2c764cd006c1bf28fb7e122731888; ?>
<?php unset($__componentOriginal49d2c764cd006c1bf28fb7e122731888); ?>
<?php endif; ?>

    <!-- Header -->
    <div class="mb-3 flex items-center gap-3">
        <a href="<?php echo e(route('isp.service-profiles.index')); ?>" class="p-2 text-slate-500 hover:text-slate-700 hover:bg-slate-100 rounded-lg transition-colors">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
            </svg>
        </a>
        <div>
            <h1 class="text-2xl font-bold text-slate-900"><?php echo e($profile->name); ?></h1>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(auth()->user()->hasRole('super_admin')): ?>
                <p class="text-slate-500 mt-1">Kode: <?php echo e($profile->code); ?></p>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </div>
        <div class="ml-auto flex items-center gap-2">
            <button wire:click="toggleStatus" type="button" class="inline-flex items-center gap-2 px-4 py-2 <?php echo e($profile->status === 'active' ? 'bg-warning-600 hover:bg-warning-700' : 'bg-success-600 hover:bg-success-700'); ?> text-white rounded-lg transition-colors font-medium shadow-sm">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                </svg>
                <?php echo e($profile->status === 'active' ? 'Nonaktifkan' : 'Aktifkan'); ?>

            </button>
            <button wire:click="openCloneModal" type="button" class="inline-flex items-center gap-2 px-4 py-2 bg-slate-600 text-white rounded-lg hover:bg-slate-700 transition-colors font-medium shadow-sm">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2 2v8a2 2 0 012 2z"></path>
                </svg>
                Duplikat
            </button>
            <a href="<?php echo e(route('isp.service-profiles.edit', $profile->id)); ?>" class="inline-flex items-center gap-2 px-4 py-2 bg-primary-600 text-white rounded-lg hover:bg-primary-700 transition-colors font-medium shadow-sm">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                </svg>
                Edit
            </a>
        </div>
    </div>

    <!-- Usage Warning -->
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($profile->customerServices()->where('status', 'active')->count() > 0): ?>
        <div class="mb-3 p-3 bg-warning-50 border border-warning-200 rounded-xl">
            <div class="flex items-center gap-2">
                <svg class="w-5 h-5 text-warning-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77-1.333.192 3 1.732 3z"></path>
                </svg>
                <span class="text-warning-800 font-medium">Paket ini sedang digunakan oleh <?php echo e($profile->customerServices()->where('status', 'active')->count()); ?> pelanggan aktif. Perubahan dapat mempengaruhi layanan mereka.</span>
            </div>
        </div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-3">
        <!-- Left Column: Details -->
        <div class="lg:col-span-2 space-y-3">
            <!-- Detail Card -->
            <?php if (isset($component)) { $__componentOriginalf0ba6ef14ffa9e2e0936b821e3847e33 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalf0ba6ef14ffa9e2e0936b821e3847e33 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.base.card','data' => []] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('base.card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

                <div class="p-3 space-y-3">
                    <!-- Basic Info -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                        <div class="space-y-3">
                            <div>
                                <span class="text-sm font-medium text-slate-500">Nama Paket</span>
                                <p class="text-lg font-semibold text-slate-900"><?php echo e($profile->name); ?></p>
                            </div>
                            <div>
                                <span class="text-sm font-medium text-slate-500">Jenis</span>
                                <div class="mt-1">
                                    <span class="px-3 py-1 inline-flex text-sm leading-5 font-semibold rounded-full 
                                        <?php if($profile->service_type === 'pppoe'): ?> bg-primary-100 text-primary-800
                                        <?php elseif($profile->service_type === 'hotspot'): ?> bg-success-100 text-success-800
                                        <?php elseif($profile->service_type === 'voucher'): ?> bg-purple-100 text-purple-800
                                        <?php else: ?> bg-slate-100 text-slate-800
                                        <?php endif; ?>
                                    ">
                                        <?php echo e(strtoupper($profile->service_type)); ?>

                                    </span>
                                </div>
                            </div>
                            <div>
                                <span class="text-sm font-medium text-slate-500">Tipe Paket</span>
                                <div class="mt-1">
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($profile->package_type === 'unlimited'): ?>
                                        <span class="px-3 py-1 inline-flex text-sm leading-5 font-semibold rounded-full bg-success-100 text-success-800">Unlimited</span>
                                    <?php elseif($profile->package_type === 'time_based'): ?>
                                        <span class="px-3 py-1 inline-flex text-sm leading-5 font-semibold rounded-full bg-primary-100 text-primary-800"><?php echo e($profile->duration_value); ?> <?php echo e($profile->duration_unit === 'hours' ? 'Jam' : 'Hari'); ?></span>
                                    <?php elseif($profile->package_type === 'quota_based'): ?>
                                        <span class="px-3 py-1 inline-flex text-sm leading-5 font-semibold rounded-full bg-warning-100 text-warning-800"><?php echo e($profile->quota_value); ?> <?php echo e($profile->quota_unit); ?></span>
                                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                </div>
                            </div>
                        </div>
                        <div class="space-y-3">
                            <div>
                                <span class="text-sm font-medium text-slate-500">Status</span>
                                <div class="mt-1">
                                    <span class="px-3 py-1 inline-flex text-sm leading-5 font-semibold rounded-full <?php echo e($profile->status === 'active' ? 'bg-success-100 text-success-800' : 'bg-slate-100 text-slate-800'); ?>">
                                        <?php echo e($profile->status === 'active' ? 'Aktif' : 'Nonaktif'); ?>

                                    </span>
                                </div>
                            </div>
                            <div>
                                <span class="text-sm font-medium text-slate-500">Owner</span>
                                <p class="text-lg font-semibold text-slate-900"><?php echo e($profile->owner ? $profile->owner->name : '-'); ?></p>
                            </div>
                        </div>
                    </div>

                    <!-- Bandwidth & Harga -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-3 border-t border-slate-200 pt-3">
                        <div class="space-y-3">
                            <div>
                                <span class="text-sm font-medium text-slate-500">Bandwidth</span>
                                <p class="text-lg font-semibold text-slate-900"><?php echo e($profile->download_speed ?: '-'); ?> / <?php echo e($profile->upload_speed ?: '-'); ?> Mbps</p>
                            </div>
                            <div>
                                <span class="text-sm font-medium text-slate-500">Shared User</span>
                                <p class="text-lg font-semibold text-slate-900"><?php echo e($profile->max_devices ?: '-'); ?></p>
                            </div>
                        </div>
                        <div class="space-y-3">
                            <div>
                                <span class="text-sm font-medium text-slate-500">Harga</span>
                                <p class="text-lg font-semibold text-slate-900"><?php echo e($profile->is_free ? 'Gratis' : ($profile->base_price ? 'Rp ' . number_format($profile->base_price, 0, ',', '.') : '-')); ?></p>
                            </div>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(auth()->user()->hasRole('super_admin')): ?>
                                <div>
                                    <span class="text-sm font-medium text-slate-500">Harga Owner</span>
                                    <p class="text-lg font-semibold text-slate-900"><?php echo e($profile->owner_price ? 'Rp ' . number_format($profile->owner_price, 0, ',', '.') : '-'); ?></p>
                                </div>
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($profile->owner || auth()->user()->hasRole('super_admin')): ?>
                                <div>
                                    <span class="text-sm font-medium text-slate-500">Harga Reseller</span>
                                    <p class="text-lg font-semibold text-slate-900"><?php echo e($profile->reseller_price ? 'Rp ' . number_format($profile->reseller_price, 0, ',', '.') : '-'); ?></p>
                                </div>
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </div>
                    </div>
                </div>
             <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalf0ba6ef14ffa9e2e0936b821e3847e33)): ?>
<?php $attributes = $__attributesOriginalf0ba6ef14ffa9e2e0936b821e3847e33; ?>
<?php unset($__attributesOriginalf0ba6ef14ffa9e2e0936b821e3847e33); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalf0ba6ef14ffa9e2e0936b821e3847e33)): ?>
<?php $component = $__componentOriginalf0ba6ef14ffa9e2e0936b821e3847e33; ?>
<?php unset($__componentOriginalf0ba6ef14ffa9e2e0936b821e3847e33); ?>
<?php endif; ?>

            <!-- Configuration Preview -->
            <?php if (isset($component)) { $__componentOriginalf0ba6ef14ffa9e2e0936b821e3847e33 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalf0ba6ef14ffa9e2e0936b821e3847e33 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.base.card','data' => []] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('base.card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

                <div class="p-3">
                    <h3 class="text-lg font-semibold text-slate-900 mb-3">Preview Konfigurasi</h3>
                    
                    <div class="space-y-3">
                        <!-- Queue Config -->
                        <div class="border border-slate-200 rounded-xl overflow-hidden">
                            <div class="bg-slate-50 px-3 py-2 border-b border-slate-200 flex items-center gap-2">
                                <svg class="w-5 h-5 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                                </svg>
                                <span class="font-medium text-slate-800">Simple Queue</span>
                            </div>
                            <div class="p-3 bg-slate-900 text-success-400 font-mono text-sm">
/queue simple add name="<?php echo e($profile->name); ?>" target="" max-limit=<?php echo e($profile->download_speed); ?>M/<?php echo e($profile->upload_speed); ?>M burst-limit=<?php echo e(intval($profile->download_speed * 1.5)); ?>M/<?php echo e(intval($profile->upload_speed * 1.5)); ?>M burst-threshold=<?php echo e(intval($profile->download_speed * 0.8)); ?>M/<?php echo e(intval($profile->upload_speed * 0.8)); ?>M burst-time=60/60
                            </div>
                        </div>

                        <!-- Radius Config -->
                        <div class="border border-slate-200 rounded-xl overflow-hidden">
                            <div class="bg-slate-50 px-3 py-2 border-b border-slate-200 flex items-center gap-2">
                                <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path>
                                </svg>
                                <span class="font-medium text-slate-800">Radius Group</span>
                            </div>
                            <div class="p-3 bg-slate-900 text-success-400 font-mono text-sm">
/radgroupcheck add groupname="<?php echo e($profile->radius_group_name); ?>" attribute=Fall-Through value=1<br>
/radgroupreply add groupname="<?php echo e($profile->radius_group_name); ?>" attribute=MikroTik-Rate-Limit value="<?php echo e($profile->download_speed); ?>M/<?php echo e($profile->upload_speed); ?>M <?php echo e(intval($profile->download_speed * 1.5)); ?>M/<?php echo e(intval($profile->upload_speed * 1.5)); ?>M <?php echo e(intval($profile->download_speed * 0.8)); ?>M/<?php echo e(intval($profile->upload_speed * 0.8)); ?>M 60/60 1 <?php echo e($profile->radius_group_name); ?>"<br>
<?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($profile->radius_session_timeout): ?>
/radgroupreply add groupname="<?php echo e($profile->radius_group_name); ?>" attribute=Session-Timeout value=<?php echo e($profile->radius_session_timeout); ?><br>
<?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
<?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($profile->radius_idle_timeout): ?>
/radgroupreply add groupname="<?php echo e($profile->radius_group_name); ?>" attribute=Idle-Timeout value=<?php echo e($profile->radius_idle_timeout); ?><br>
<?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
<?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($profile->radius_simultaneous_use): ?>
/radgroupcheck add groupname="<?php echo e($profile->radius_group_name); ?>" attribute=Simultaneous-Use value=<?php echo e($profile->radius_simultaneous_use); ?><br>
<?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            </div>
                        </div>

                        <!-- PPP Profile -->
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($profile->service_type === 'pppoe'): ?>
                            <div class="border border-slate-200 rounded-xl overflow-hidden">
                                <div class="bg-slate-50 px-3 py-2 border-b border-slate-200 flex items-center gap-2">
                                    <svg class="w-5 h-5 text-success-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 9l3 3-3 3m5 0h3M5 20h14a2 2 0 002-2V6a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                    </svg>
                                    <span class="font-medium text-slate-800">PPP Profile</span>
                                </div>
                                <div class="p-3 bg-slate-900 text-success-400 font-mono text-sm">
/ppp profile add name="<?php echo e($profile->ppp_profile_name); ?>" local-address=<?php echo e($profile->ip_pool_parent); ?> remote-address=<?php echo e($profile->radius_framed_pool); ?> rate-limit="<?php echo e($profile->download_speed); ?>M/<?php echo e($profile->upload_speed); ?>M" only-one=<?php echo e($profile->radius_mac_binding ? 'yes' : 'no'); ?>

                                </div>
                            </div>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

                        <!-- Hotspot Profile -->
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($profile->service_type === 'hotspot' || $profile->service_type === 'voucher'): ?>
                            <div class="border border-slate-200 rounded-xl overflow-hidden">
                                <div class="bg-slate-50 px-3 py-2 border-b border-slate-200 flex items-center gap-2">
                                    <svg class="w-5 h-5 text-warning-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.111 16.404a5.5 5.5 0 017.778 0M12 20h.01m-7.08-7.071c3.904-3.905 10.236-3.905 14.141 0M1.394 9.393c5.857-5.857 15.355-5.857 21.213 0"></path>
                                    </svg>
                                    <span class="font-medium text-slate-800">Hotspot Profile</span>
                                </div>
                                <div class="p-3 bg-slate-900 text-success-400 font-mono text-sm">
/ip hotspot profile add name="<?php echo e($profile->target_hotspot_profile); ?>" rate-limit="<?php echo e($profile->download_speed); ?>M/<?php echo e($profile->upload_speed); ?>" shared-users=<?php echo e($profile->max_devices); ?>

                                </div>
                            </div>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </div>
                </div>
             <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalf0ba6ef14ffa9e2e0936b821e3847e33)): ?>
<?php $attributes = $__attributesOriginalf0ba6ef14ffa9e2e0936b821e3847e33; ?>
<?php unset($__attributesOriginalf0ba6ef14ffa9e2e0936b821e3847e33); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalf0ba6ef14ffa9e2e0936b821e3847e33)): ?>
<?php $component = $__componentOriginalf0ba6ef14ffa9e2e0936b821e3847e33; ?>
<?php unset($__componentOriginalf0ba6ef14ffa9e2e0936b821e3847e33); ?>
<?php endif; ?>

            <!-- Audit History -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                <div class="p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Riwayat Perubahan</h3>
                    
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($auditLogs->count() > 0): ?>
                        <div class="space-y-3">
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $auditLogs; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $log): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                                <div class="border border-gray-200 rounded-lg p-4">
                                    <div class="flex items-center justify-between mb-2">
                                        <div class="flex items-center gap-2">
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                                <?php if($log->event === 'created'): ?> bg-green-100 text-green-800
                                                <?php elseif($log->event === 'updated'): ?> bg-blue-100 text-blue-800
                                                <?php elseif($log->event === 'deleted'): ?> bg-red-100 text-red-800
                                                <?php elseif($log->event === 'restored'): ?> bg-purple-100 text-purple-800
                                                <?php else: ?> bg-gray-100 text-gray-800
                                                <?php endif; ?>
                                            ">
                                                <?php echo e(ucfirst($log->event)); ?>

                                            </span>
                                            <span class="text-sm text-gray-500"><?php echo e($log->user ? $log->user->name : 'System'); ?></span>
                                        </div>
                                        <span class="text-xs text-gray-400"><?php echo e($log->created_at->diffForHumans()); ?></span>
                                    </div>
                                    
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($log->old_values && $log->new_values): ?>
                                        <?php
                                            $old = is_array($log->old_values) ? $log->old_values : json_decode($log->old_values, true);
                                            $new = is_array($log->new_values) ? $log->new_values : json_decode($log->new_values, true);
                                        ?>
                                        <div class="mt-2 space-y-1">
                                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = array_diff_assoc($new, $old); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $value): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(!in_array($key, ['updated_at', 'updated_by'])): ?>
                                                    <div class="flex items-start gap-2">
                                                        <span class="text-sm font-medium text-gray-600"><?php echo e($key); ?>:</span>
                                                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(isset($old[$key])): ?>
                                                            <span class="text-sm text-red-600 line-through"><?php echo e(is_array($old[$key]) ? json_encode($old[$key]) : $old[$key]); ?></span>
                                                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                                        <span class="text-sm text-green-600"><?php echo e(is_array($value) ? json_encode($value) : $value); ?></span>
                                                    </div>
                                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                                        </div>
                                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                </div>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                        </div>
                    <?php else: ?>
                        <div class="text-center py-8 text-gray-500">
                            <svg class="w-12 h-12 mx-auto text-gray-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                            </svg>
                            <p>Belum ada riwayat perubahan</p>
                        </div>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Right Column: Summary -->
        <div class="space-y-6">
            <!-- Provisioning Status -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                <div class="p-6">
                    <h3 class="text-sm font-semibold text-gray-700 mb-4">Status Provisioning</h3>
                    <div class="space-y-3">
                        <div class="p-3 bg-gray-50 rounded-lg">
                            <span class="text-sm font-medium text-gray-500">Radius Group</span>
                            <p class="text-base text-gray-900"><?php echo e($profile->radius_group_name ?: '-'); ?></p>
                        </div>
                        <div class="p-3 bg-gray-50 rounded-lg">
                            <span class="text-sm font-medium text-gray-500">Queue</span>
                            <p class="text-base text-gray-900"><?php echo e($profile->download_speed ? $profile->download_speed . 'M/' . $profile->upload_speed . 'M' : '-'); ?></p>
                        </div>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($profile->service_type === 'pppoe'): ?>
                            <div class="p-3 bg-gray-50 rounded-lg">
                                <span class="text-sm font-medium text-gray-500">PPP Profile</span>
                                <p class="text-base text-gray-900"><?php echo e($profile->ppp_profile_name ?: '-'); ?></p>
                            </div>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($profile->service_type === 'hotspot' || $profile->service_type === 'voucher'): ?>
                            <div class="p-3 bg-gray-50 rounded-lg">
                                <span class="text-sm font-medium text-gray-500">Hotspot Profile</span>
                                <p class="text-base text-gray-900"><?php echo e($profile->target_hotspot_profile ?: '-'); ?></p>
                            </div>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        <div class="p-3 bg-gray-50 rounded-lg">
                            <span class="text-sm font-medium text-gray-500">Pengguna Aktif</span>
                            <p class="text-base text-gray-900"><?php echo e($profile->customerServices()->where('status', 'active')->count()); ?></p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Technical Info -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                <div class="p-6">
                    <h3 class="text-sm font-semibold text-gray-700 mb-4">Informasi Teknis</h3>
                    <div class="space-y-3 text-sm">
                        <div class="flex justify-between">
                            <span class="text-gray-500">Dibuat</span>
                            <span class="text-gray-900"><?php echo e($profile->created_at ? $profile->created_at->format('d M Y H:i') : '-'); ?></span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-500">Diperbarui</span>
                            <span class="text-gray-900"><?php echo e($profile->updated_at ? $profile->updated_at->format('d M Y H:i') : '-'); ?></span>
                        </div>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($profile->createdBy): ?>
                            <div class="flex justify-between">
                                <span class="text-gray-500">Dibuat Oleh</span>
                                <span class="text-gray-900"><?php echo e($profile->createdBy->name); ?></span>
                            </div>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($profile->updatedBy): ?>
                            <div class="flex justify-between">
                                <span class="text-gray-500">Diperbarui Oleh</span>
                                <span class="text-gray-900"><?php echo e($profile->updatedBy->name); ?></span>
                            </div>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($profile->deleted_at): ?>
                            <div class="flex justify-between text-red-600">
                                <span>Dihapus</span>
                                <span><?php echo e($profile->deleted_at->format('d M Y H:i')); ?></span>
                            </div>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Clone Modal -->
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($showCloneModal): ?>
        <div x-data="{ open: <?php if ((object) ('showCloneModal') instanceof \Livewire\WireDirective) : ?>window.Livewire.find('<?php echo e($__livewire->getId()); ?>').entangle('<?php echo e('showCloneModal'->value()); ?>')<?php echo e('showCloneModal'->hasModifier('live') ? '.live' : ''); ?><?php else : ?>window.Livewire.find('<?php echo e($__livewire->getId()); ?>').entangle('<?php echo e('showCloneModal'); ?>')<?php endif; ?> }" x-show="open" class="fixed inset-0 z-50 overflow-y-auto">
            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <!-- Background overlay -->
                <div x-show="open" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" class="fixed inset-0 transition-opacity bg-gray-500 bg-opacity-75"></div>

                <!-- Modal panel -->
                <div class="inline-block w-full max-w-lg p-6 my-8 overflow-hidden text-left align-middle transition-all transform bg-white rounded-xl shadow-xl sm:align-middle">
                    <div class="mb-4">
                        <h3 class="text-lg font-semibold text-gray-900 mb-2">Duplikat Paket</h3>
                        <p class="text-sm text-gray-500 mb-4">Pilih bagian mana yang ingin disalin</p>
                        
                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Nama Paket Baru</label>
                                <input type="text" wire:model="cloneName" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            </div>
                            
                            <div class="space-y-2">
                                <label class="block text-sm font-medium text-gray-700">Salin Bagian:</label>
                                <div class="grid grid-cols-2 gap-2">
                                    <label class="flex items-center gap-2">
                                        <input type="checkbox" wire:model="cloneOptions.description" class="w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500">
                                        <span class="text-sm text-gray-700">Deskripsi</span>
                                    </label>
                                    <label class="flex items-center gap-2">
                                        <input type="checkbox" wire:model="cloneOptions.service_type" class="w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500">
                                        <span class="text-sm text-gray-700">Jenis Layanan</span>
                                    </label>
                                    <label class="flex items-center gap-2">
                                        <input type="checkbox" wire:model="cloneOptions.package_type" class="w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500">
                                        <span class="text-sm text-gray-700">Tipe Paket</span>
                                    </label>
                                    <label class="flex items-center gap-2">
                                        <input type="checkbox" wire:model="cloneOptions.bandwidth" class="w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500">
                                        <span class="text-sm text-gray-700">Bandwidth</span>
                                    </label>
                                    <label class="flex items-center gap-2">
                                        <input type="checkbox" wire:model="cloneOptions.prices" class="w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500">
                                        <span class="text-sm text-gray-700">Harga</span>
                                    </label>
                                    <label class="flex items-center gap-2">
                                        <input type="checkbox" wire:model="cloneOptions.validity" class="w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500">
                                        <span class="text-sm text-gray-700">Masa Aktif</span>
                                    </label>
                                    <label class="flex items-center gap-2">
                                        <input type="checkbox" wire:model="cloneOptions.max_devices" class="w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500">
                                        <span class="text-sm text-gray-700">Shared User</span>
                                    </label>
                                    <label class="flex items-center gap-2">
                                        <input type="checkbox" wire:model="cloneOptions.technical" class="w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500">
                                        <span class="text-sm text-gray-700">Konfigurasi Teknis</span>
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="flex justify-end gap-3">
                        <button wire:click="closeCloneModal" type="button" class="px-4 py-2 text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors font-medium">
                            Batal
                        </button>
                        <button wire:click="cloneProfile" type="button" class="px-4 py-2 text-white bg-blue-600 rounded-lg hover:bg-blue-700 transition-colors font-medium">
                            Duplikat
                        </button>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
</div>
<?php /**PATH C:\Users\Lenovo\OneDrive\dsBilling\resources\views/livewire/isp/service-profiles/detail.blade.php ENDPATH**/ ?>