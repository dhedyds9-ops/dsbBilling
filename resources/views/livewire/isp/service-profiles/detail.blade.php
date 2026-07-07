<div class="max-w-7xl mx-auto p-3">
    <!-- Breadcrumbs -->
    <x-admin.breadcrumbs :crumbs="[
        ['label' => 'Dashboard', 'url' => route('admin.dashboard')],
        ['label' => 'ISP', 'url' => '#'],
        ['label' => 'Paket Internet', 'url' => route('isp.service-profiles.index')],
        ['label' => $profile->name, 'url' => '#']
    ]" />

    <!-- Success/Error Flash Message -->
    <div class="mb-3">
        @if(session()->has('success'))
            <x-feedback.alert variant="success">
                {{ session('success') }}
            </x-feedback.alert>
        @endif
        @if(session()->has('error'))
            <x-feedback.alert variant="danger">
                {{ session('error') }}
            </x-feedback.alert>
        @endif
        @if(session()->has('warning'))
            <x-feedback.alert variant="warning">
                {{ session('warning') }}
            </x-feedback.alert>
        @endif
        @if(session()->has('info'))
            <x-feedback.alert variant="info">
                {{ session('info') }}
            </x-feedback.alert>
        @endif
    </div>

    <!-- Header -->
    <div class="mb-3 flex items-center gap-3">
        <a href="{{ route('isp.service-profiles.index') }}" class="p-2 text-slate-500 hover:text-slate-700 hover:bg-slate-100 rounded-lg transition-colors">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
            </svg>
        </a>
        <div>
            <h1 class="text-2xl font-bold text-slate-900">{{ $profile->name }}</h1>
            @if(auth()->user()->hasRole('super_admin'))
                <p class="text-slate-500 mt-1">Kode: {{ $profile->code }}</p>
            @endif
        </div>
        <div class="ml-auto flex items-center gap-2">
            <button wire:click="toggleStatus" type="button" class="inline-flex items-center gap-2 px-4 py-2 {{ $profile->status === 'active' ? 'bg-warning-600 hover:bg-warning-700' : 'bg-success-600 hover:bg-success-700' }} text-white rounded-lg transition-colors font-medium shadow-sm">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                </svg>
                {{ $profile->status === 'active' ? 'Nonaktifkan' : 'Aktifkan' }}
            </button>
            <button wire:click="openCloneModal" type="button" class="inline-flex items-center gap-2 px-4 py-2 bg-slate-600 text-white rounded-lg hover:bg-slate-700 transition-colors font-medium shadow-sm">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2 2v8a2 2 0 012 2z"></path>
                </svg>
                Duplikat
            </button>
            <a href="{{ route('isp.service-profiles.edit', $profile->id) }}" class="inline-flex items-center gap-2 px-4 py-2 bg-primary-600 text-white rounded-lg hover:bg-primary-700 transition-colors font-medium shadow-sm">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                </svg>
                Edit
            </a>
        </div>
    </div>

    <!-- Usage Warning -->
    @if($profile->customerServices()->where('status', 'active')->count() > 0)
        <div class="mb-3 p-3 bg-warning-50 border border-warning-200 rounded-xl">
            <div class="flex items-center gap-2">
                <svg class="w-5 h-5 text-warning-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77-1.333.192 3 1.732 3z"></path>
                </svg>
                <span class="text-warning-800 font-medium">Paket ini sedang digunakan oleh {{ $profile->customerServices()->where('status', 'active')->count() }} pelanggan aktif. Perubahan dapat mempengaruhi layanan mereka.</span>
            </div>
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-3">
        <!-- Left Column: Details -->
        <div class="lg:col-span-2 space-y-3">
            <!-- Detail Card -->
            <x-base.card>
                <div class="p-3 space-y-3">
                    <!-- Basic Info -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                        <div class="space-y-3">
                            <div>
                                <span class="text-sm font-medium text-slate-500">Nama Paket</span>
                                <p class="text-lg font-semibold text-slate-900">{{ $profile->name }}</p>
                            </div>
                            <div>
                                <span class="text-sm font-medium text-slate-500">Jenis</span>
                                <div class="mt-1">
                                    <span class="px-3 py-1 inline-flex text-sm leading-5 font-semibold rounded-full 
                                        @if($profile->service_type === 'pppoe') bg-primary-100 text-primary-800
                                        @elseif($profile->service_type === 'hotspot') bg-success-100 text-success-800
                                        @elseif($profile->service_type === 'voucher') bg-purple-100 text-purple-800
                                        @else bg-slate-100 text-slate-800
                                        @endif
                                    ">
                                        {{ strtoupper($profile->service_type) }}
                                    </span>
                                </div>
                            </div>
                            <div>
                                <span class="text-sm font-medium text-slate-500">Tipe Paket</span>
                                <div class="mt-1">
                                    @if($profile->package_type === 'unlimited')
                                        <span class="px-3 py-1 inline-flex text-sm leading-5 font-semibold rounded-full bg-success-100 text-success-800">Unlimited</span>
                                    @elseif($profile->package_type === 'time_based')
                                        <span class="px-3 py-1 inline-flex text-sm leading-5 font-semibold rounded-full bg-primary-100 text-primary-800">{{ $profile->duration_value }} {{ $profile->duration_unit === 'hours' ? 'Jam' : 'Hari' }}</span>
                                    @elseif($profile->package_type === 'quota_based')
                                        <span class="px-3 py-1 inline-flex text-sm leading-5 font-semibold rounded-full bg-warning-100 text-warning-800">{{ $profile->quota_value }} {{ $profile->quota_unit }}</span>
                                    @endif
                                </div>
                            </div>
                        </div>
                        <div class="space-y-3">
                            <div>
                                <span class="text-sm font-medium text-slate-500">Status</span>
                                <div class="mt-1">
                                    <span class="px-3 py-1 inline-flex text-sm leading-5 font-semibold rounded-full {{ $profile->status === 'active' ? 'bg-success-100 text-success-800' : 'bg-slate-100 text-slate-800' }}">
                                        {{ $profile->status === 'active' ? 'Aktif' : 'Nonaktif' }}
                                    </span>
                                </div>
                            </div>
                            <div>
                                <span class="text-sm font-medium text-slate-500">Owner</span>
                                <p class="text-lg font-semibold text-slate-900">{{ $profile->owner ? $profile->owner->name : '-' }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- Bandwidth & Harga -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-3 border-t border-slate-200 pt-3">
                        <div class="space-y-3">
                            <div>
                                <span class="text-sm font-medium text-slate-500">Bandwidth</span>
                                <p class="text-lg font-semibold text-slate-900">{{ $profile->download_speed ?: '-' }} / {{ $profile->upload_speed ?: '-' }} Mbps</p>
                            </div>
                            <div>
                                <span class="text-sm font-medium text-slate-500">Shared User</span>
                                <p class="text-lg font-semibold text-slate-900">{{ $profile->max_devices ?: '-' }}</p>
                            </div>
                        </div>
                        <div class="space-y-3">
                            <div>
                                <span class="text-sm font-medium text-slate-500">Harga</span>
                                <p class="text-lg font-semibold text-slate-900">{{ $profile->is_free ? 'Gratis' : ($profile->base_price ? 'Rp ' . number_format($profile->base_price, 0, ',', '.') : '-') }}</p>
                            </div>
                            @if(auth()->user()->hasRole('super_admin'))
                                <div>
                                    <span class="text-sm font-medium text-slate-500">Harga Owner</span>
                                    <p class="text-lg font-semibold text-slate-900">{{ $profile->owner_price ? 'Rp ' . number_format($profile->owner_price, 0, ',', '.') : '-' }}</p>
                                </div>
                            @endif
                            @if($profile->owner || auth()->user()->hasRole('super_admin'))
                                <div>
                                    <span class="text-sm font-medium text-slate-500">Harga Reseller</span>
                                    <p class="text-lg font-semibold text-slate-900">{{ $profile->reseller_price ? 'Rp ' . number_format($profile->reseller_price, 0, ',', '.') : '-' }}</p>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </x-base.card>

            <!-- Configuration Preview -->
            <x-base.card>
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
/queue simple add name="{{ $profile->name }}" target="" max-limit={{ $profile->download_speed }}M/{{ $profile->upload_speed }}M burst-limit={{ intval($profile->download_speed * 1.5) }}M/{{ intval($profile->upload_speed * 1.5) }}M burst-threshold={{ intval($profile->download_speed * 0.8) }}M/{{ intval($profile->upload_speed * 0.8) }}M burst-time=60/60
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
/radgroupcheck add groupname="{{ $profile->radius_group_name }}" attribute=Fall-Through value=1<br>
/radgroupreply add groupname="{{ $profile->radius_group_name }}" attribute=MikroTik-Rate-Limit value="{{ $profile->download_speed }}M/{{ $profile->upload_speed }}M {{ intval($profile->download_speed * 1.5) }}M/{{ intval($profile->upload_speed * 1.5) }}M {{ intval($profile->download_speed * 0.8) }}M/{{ intval($profile->upload_speed * 0.8) }}M 60/60 1 {{ $profile->radius_group_name }}"<br>
@if($profile->radius_session_timeout)
/radgroupreply add groupname="{{ $profile->radius_group_name }}" attribute=Session-Timeout value={{ $profile->radius_session_timeout }}<br>
@endif
@if($profile->radius_idle_timeout)
/radgroupreply add groupname="{{ $profile->radius_group_name }}" attribute=Idle-Timeout value={{ $profile->radius_idle_timeout }}<br>
@endif
@if($profile->radius_simultaneous_use)
/radgroupcheck add groupname="{{ $profile->radius_group_name }}" attribute=Simultaneous-Use value={{ $profile->radius_simultaneous_use }}<br>
@endif
                            </div>
                        </div>

                        <!-- PPP Profile -->
                        @if($profile->service_type === 'pppoe')
                            <div class="border border-slate-200 rounded-xl overflow-hidden">
                                <div class="bg-slate-50 px-3 py-2 border-b border-slate-200 flex items-center gap-2">
                                    <svg class="w-5 h-5 text-success-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 9l3 3-3 3m5 0h3M5 20h14a2 2 0 002-2V6a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                    </svg>
                                    <span class="font-medium text-slate-800">PPP Profile</span>
                                </div>
                                <div class="p-3 bg-slate-900 text-success-400 font-mono text-sm">
/ppp profile add name="{{ $profile->ppp_profile_name }}" local-address={{ $profile->ip_pool_parent }} remote-address={{ $profile->radius_framed_pool }} rate-limit="{{ $profile->download_speed }}M/{{ $profile->upload_speed }}M" only-one={{ $profile->radius_mac_binding ? 'yes' : 'no' }}
                                </div>
                            </div>
                        @endif

                        <!-- Hotspot Profile -->
                        @if($profile->service_type === 'hotspot' || $profile->service_type === 'voucher')
                            <div class="border border-slate-200 rounded-xl overflow-hidden">
                                <div class="bg-slate-50 px-3 py-2 border-b border-slate-200 flex items-center gap-2">
                                    <svg class="w-5 h-5 text-warning-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.111 16.404a5.5 5.5 0 017.778 0M12 20h.01m-7.08-7.071c3.904-3.905 10.236-3.905 14.141 0M1.394 9.393c5.857-5.857 15.355-5.857 21.213 0"></path>
                                    </svg>
                                    <span class="font-medium text-slate-800">Hotspot Profile</span>
                                </div>
                                <div class="p-3 bg-slate-900 text-success-400 font-mono text-sm">
/ip hotspot profile add name="{{ $profile->target_hotspot_profile }}" rate-limit="{{ $profile->download_speed }}M/{{ $profile->upload_speed }}" shared-users={{ $profile->max_devices }}
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </x-base.card>

            <!-- Audit History -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                <div class="p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Riwayat Perubahan</h3>
                    
                    @if($auditLogs->count() > 0)
                        <div class="space-y-3">
                            @foreach($auditLogs as $log)
                                <div class="border border-gray-200 rounded-lg p-4">
                                    <div class="flex items-center justify-between mb-2">
                                        <div class="flex items-center gap-2">
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                                @if($log->event === 'created') bg-green-100 text-green-800
                                                @elseif($log->event === 'updated') bg-blue-100 text-blue-800
                                                @elseif($log->event === 'deleted') bg-red-100 text-red-800
                                                @elseif($log->event === 'restored') bg-purple-100 text-purple-800
                                                @else bg-gray-100 text-gray-800
                                                @endif
                                            ">
                                                {{ ucfirst($log->event) }}
                                            </span>
                                            <span class="text-sm text-gray-500">{{ $log->user ? $log->user->name : 'System' }}</span>
                                        </div>
                                        <span class="text-xs text-gray-400">{{ $log->created_at->diffForHumans() }}</span>
                                    </div>
                                    
                                    @if($log->old_values && $log->new_values)
                                        @php
                                            $old = is_array($log->old_values) ? $log->old_values : json_decode($log->old_values, true);
                                            $new = is_array($log->new_values) ? $log->new_values : json_decode($log->new_values, true);
                                        @endphp
                                        <div class="mt-2 space-y-1">
                                            @foreach(array_diff_assoc($new, $old) as $key => $value)
                                                @if(!in_array($key, ['updated_at', 'updated_by']))
                                                    <div class="flex items-start gap-2">
                                                        <span class="text-sm font-medium text-gray-600">{{ $key }}:</span>
                                                        @if(isset($old[$key]))
                                                            <span class="text-sm text-red-600 line-through">{{ is_array($old[$key]) ? json_encode($old[$key]) : $old[$key] }}</span>
                                                        @endif
                                                        <span class="text-sm text-green-600">{{ is_array($value) ? json_encode($value) : $value }}</span>
                                                    </div>
                                                @endif
                                            @endforeach
                                        </div>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center py-8 text-gray-500">
                            <svg class="w-12 h-12 mx-auto text-gray-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                            </svg>
                            <p>Belum ada riwayat perubahan</p>
                        </div>
                    @endif
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
                            <p class="text-base text-gray-900">{{ $profile->radius_group_name ?: '-' }}</p>
                        </div>
                        <div class="p-3 bg-gray-50 rounded-lg">
                            <span class="text-sm font-medium text-gray-500">Queue</span>
                            <p class="text-base text-gray-900">{{ $profile->download_speed ? $profile->download_speed . 'M/' . $profile->upload_speed . 'M' : '-' }}</p>
                        </div>
                        @if($profile->service_type === 'pppoe')
                            <div class="p-3 bg-gray-50 rounded-lg">
                                <span class="text-sm font-medium text-gray-500">PPP Profile</span>
                                <p class="text-base text-gray-900">{{ $profile->ppp_profile_name ?: '-' }}</p>
                            </div>
                        @endif
                        @if($profile->service_type === 'hotspot' || $profile->service_type === 'voucher')
                            <div class="p-3 bg-gray-50 rounded-lg">
                                <span class="text-sm font-medium text-gray-500">Hotspot Profile</span>
                                <p class="text-base text-gray-900">{{ $profile->target_hotspot_profile ?: '-' }}</p>
                            </div>
                        @endif
                        <div class="p-3 bg-gray-50 rounded-lg">
                            <span class="text-sm font-medium text-gray-500">Pengguna Aktif</span>
                            <p class="text-base text-gray-900">{{ $profile->customerServices()->where('status', 'active')->count() }}</p>
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
                            <span class="text-gray-900">{{ $profile->created_at ? $profile->created_at->format('d M Y H:i') : '-' }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-500">Diperbarui</span>
                            <span class="text-gray-900">{{ $profile->updated_at ? $profile->updated_at->format('d M Y H:i') : '-' }}</span>
                        </div>
                        @if($profile->createdBy)
                            <div class="flex justify-between">
                                <span class="text-gray-500">Dibuat Oleh</span>
                                <span class="text-gray-900">{{ $profile->createdBy->name }}</span>
                            </div>
                        @endif
                        @if($profile->updatedBy)
                            <div class="flex justify-between">
                                <span class="text-gray-500">Diperbarui Oleh</span>
                                <span class="text-gray-900">{{ $profile->updatedBy->name }}</span>
                            </div>
                        @endif
                        @if($profile->deleted_at)
                            <div class="flex justify-between text-red-600">
                                <span>Dihapus</span>
                                <span>{{ $profile->deleted_at->format('d M Y H:i') }}</span>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Clone Modal -->
    @if($showCloneModal)
        <div x-data="{ open: @entangle('showCloneModal') }" x-show="open" class="fixed inset-0 z-50 overflow-y-auto">
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
    @endif
</div>
