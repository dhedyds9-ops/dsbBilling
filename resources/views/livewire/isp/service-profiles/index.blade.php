<div class="max-w-7xl mx-auto p-3">
    <!-- Breadcrumb -->
    <x-admin.breadcrumbs />

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
    </div>

    <!-- Header -->
    <div class="flex justify-between items-center mb-3">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Paket Internet</h1>
            <p class="text-gray-500 mt-1">Kelola seluruh paket layanan internet</p>
        </div>
        <div x-data="{ open: false }" class="relative inline-block text-left">
            <div>
                <button @click="open = !open" type="button" class="inline-flex items-center gap-2 px-5 py-2.5 bg-primary-600 text-white rounded-lg hover:bg-primary-700 transition-colors font-medium shadow-sm shadow-primary-500/20">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"></path>
                    </svg>
                    Manajemen Paket
                    <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                    </svg>
                </button>
            </div>
            <div x-show="open" @click.away="open = false" x-transition class="origin-top-right absolute right-0 mt-2 w-64 rounded-lg shadow-lg bg-white ring-1 ring-black ring-opacity-5 divide-y divide-gray-100 z-50">
                <div class="py-1">
                    <a href="{{ route('isp.service-profiles.create') }}" class="flex items-center gap-2 px-4 py-2 text-sm text-gray-700 hover:bg-gray-50">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                        </svg>
                        Tambah Paket
                    </a>
                </div>
                <div class="py-1">
                    <button 
                        type="button"
                        wire:click="confirmBulkDelete" 
                        wire:loading.attr="disabled"
                        @if(empty($selectedPackages)) disabled @endif 
                        class="flex items-center gap-2 px-4 py-2 text-sm w-full text-left @if(empty($selectedPackages)) text-gray-300 cursor-not-allowed @else text-red-600 hover:bg-red-50 @endif"
                    >
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                        </svg>
                        Hapus Yang Dipilih
                        <span wire:loading class="ml-2 text-gray-400 text-xs">...</span>
                    </button>
                </div>
                <div class="py-1">
                    <div class="border-t border-gray-100 my-1"></div>
                    <button type="button" wire:click="openImportModal" class="flex items-center gap-2 px-4 py-2 text-sm w-full text-left text-gray-700 hover:bg-gray-50">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 0 0 3 3h10a3 3 0 0 0 3-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"></path>
                        </svg>
                        Import Paket
                    </button>
                    <button type="button" wire:click="export" class="flex items-center gap-2 px-4 py-2 text-sm w-full text-left text-gray-700 hover:bg-gray-50">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 0 0 3 3h10a3 3 0 0 0 3-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path>
                        </svg>
                        Export Paket
                    </button>
                    <button type="button" wire:click="print" class="flex items-center gap-2 px-4 py-2 text-sm w-full text-left text-gray-700 hover:bg-gray-50">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5.586a1 1 0 0 1 .707.293l5.414 5.414a1 1 0 0 1 .293.707V19a2 2 0 0 1-2 2z"></path>
                        </svg>
                        Cetak Daftar Paket
                    </button>
                </div>
                <div class="py-1">
                    <div class="border-t border-gray-100 my-1"></div>
                    <button 
                        type="button"
                        wire:click="confirmBulkActivate" 
                        wire:loading.attr="disabled"
                        @if(empty($selectedPackages)) disabled @endif 
                        class="flex items-center gap-2 px-4 py-2 text-sm w-full text-left @if(empty($selectedPackages)) text-gray-300 cursor-not-allowed @else text-green-600 hover:bg-green-50 @endif"
                    >
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                        </svg>
                        Aktifkan Yang Dipilih
                        <span wire:loading class="ml-2 text-gray-400 text-xs">...</span>
                    </button>
                    <button 
                        type="button"
                        wire:click="confirmBulkDeactivate" 
                        wire:loading.attr="disabled"
                        @if(empty($selectedPackages)) disabled @endif 
                        class="flex items-center gap-2 px-4 py-2 text-sm w-full text-left @if(empty($selectedPackages)) text-gray-300 cursor-not-allowed @else text-orange-600 hover:bg-orange-50 @endif"
                    >
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"></path>
                        </svg>
                        Nonaktifkan Yang Dipilih
                        <span wire:loading class="ml-2 text-gray-400 text-xs">...</span>
                    </button>
                </div>
                <div class="py-1">
                    <div class="border-t border-gray-100 my-1"></div>
                    <button 
                        type="button"
                        wire:click="openBulkEditModal" 
                        wire:loading.attr="disabled"
                        @if(empty($selectedPackages)) disabled @endif 
                        class="flex items-center gap-2 px-4 py-2 text-sm w-full text-left @if(empty($selectedPackages)) text-gray-300 cursor-not-allowed @else text-purple-600 hover:bg-purple-50 @endif"
                    >
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                        </svg>
                        Edit Massal
                        <span wire:loading class="ml-2 text-gray-400 text-xs">...</span>
                    </button>
                    <button disabled class="flex items-center gap-2 px-4 py-2 text-sm w-full text-left text-gray-300 cursor-not-allowed">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                        </svg>
                        Ubah Owner
                        <span class="ml-auto text-xs text-gray-400">(Segera Hadir)</span>
                    </button>
                    <button disabled class="flex items-center gap-2 px-4 py-2 text-sm w-full text-left text-gray-300 cursor-not-allowed">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"></path>
                        </svg>
                        Ubah Harga
                        <span class="ml-auto text-xs text-gray-400">(Segera Hadir)</span>
                    </button>
                    <button disabled class="flex items-center gap-2 px-4 py-2 text-sm w-full text-left text-gray-300 cursor-not-allowed">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                        </svg>
                        Bulk Provisioning
                        <span class="ml-auto text-xs text-gray-400">(Segera Hadir)</span>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Selected Counter -->
    @if(!empty($selectedPackages))
        <div class="mb-3 flex items-center gap-2">
            <span class="px-3 py-1 bg-primary-100 text-primary-800 rounded-full text-sm font-medium">
                {{ count($selectedPackages) }} Paket Dipilih
            </span>
            <button wire:click="selectedPackages = []; selectAll = false" class="text-sm text-gray-500 hover:text-gray-700">
                Batal Pilih
            </button>
        </div>
    @endif

    <!-- Toolbar -->
    <x-base.card class="mb-3">
        <div class="flex flex-col lg:flex-row gap-4 items-start lg:items-center justify-between">
            <!-- Search -->
            <div class="w-full lg:w-1/3">
                <label for="search" class="sr-only">Search</label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                        </svg>
                    </div>
                    <input wire:model.live.debounce.300ms="search" type="text" class="block w-full pl-10 pr-3 py-2.5 border border-gray-300 rounded-lg leading-5 bg-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-primary-500 transition-all" placeholder="Cari nama paket atau bandwidth...">
                </div>
            </div>

            <!-- Filters -->
            <div class="flex items-center gap-3 flex-wrap">
                <div>
                    <select wire:model.live="filters.service_type" class="block w-full pl-3 pr-10 py-2.5 text-base border border-gray-300 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-primary-500 rounded-lg transition-all">
                        <option value="">Semua Jenis</option>
                        <option value="pppoe">PPPoE</option>
                        <option value="hotspot">Hotspot</option>
                        <option value="voucher">Voucher</option>
                    </select>
                </div>
                <div>
                    <select wire:model.live="filters.status" class="block w-full pl-3 pr-10 py-2.5 text-base border border-gray-300 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-primary-500 rounded-lg transition-all">
                        <option value="">Semua Status</option>
                        <option value="active">Aktif</option>
                        <option value="inactive">Nonaktif</option>
                    </select>
                </div>
                @php $isSuperAdmin = auth()->user()->hasRole('super_admin') ?? false; @endphp
                @if($isSuperAdmin)
                    <div>
                        <select wire:model.live="filters.owner_id" class="block w-full pl-3 pr-10 py-2.5 text-base border border-gray-300 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-primary-500 rounded-lg transition-all">
                            <option value="">Semua Owner</option>
                            @foreach(\App\Models\User::select('id', 'name')->limit(100)->get() as $user)
                                <option value="{{ $user->id }}">{{ $user->name }}</option>
                            @endforeach
                        </select>
                    </div>
                @endif
                <!-- Show Trashed Toggle -->
                <label class="flex items-center gap-2 text-sm text-gray-700 cursor-pointer">
                    <input type="checkbox" wire:model.live="showTrashed" class="w-4 h-4 text-primary-600 border-gray-300 rounded focus:ring-primary-500">
                    Tampilkan Paket Dihapus
                </label>
                <button wire:click="resetFilters" class="p-2.5 text-gray-500 hover:text-gray-700 hover:bg-gray-100 rounded-lg transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                    </svg>
                </button>
            </div>
        </div>
    </x-base.card>

    <!-- Table -->
    <x-base.card class="overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th scope="col" class="px-6 py-3 text-left">
                            <input type="checkbox" wire:model.live="selectAll" class="w-4 h-4 text-primary-600 border-gray-300 rounded focus:ring-primary-500">
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Status</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Nama Paket</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Jenis</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Bandwidth</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Tipe</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Shared User</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Harga</th>
                        @if($isSuperAdmin)
                            <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Owner</th>
                        @endif
                        <th scope="col" class="px-6 py-3 text-right text-xs font-semibold text-gray-500 uppercase tracking-wider">Aksi</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($profiles as $profile)
                        <tr wire:key="{{ $profile->id }}" class="hover:bg-gray-50 transition-colors @if($profile->trashed()) bg-red-50 @endif">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <input type="checkbox" wire:model.live="selectedPackages" value="{{ $profile->id }}" class="w-4 h-4 text-primary-600 border-gray-300 rounded focus:ring-primary-500" @if($profile->trashed()) disabled @endif>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @if($profile->trashed())
                                    <span class="px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">Dihapus</span>
                                @else
                                    <span class="px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full {{ $profile->status === 'active' ? 'bg-success-100 text-success-800' : 'bg-gray-100 text-gray-800' }}">
                                        {{ $profile->status === 'active' ? 'Aktif' : 'Nonaktif' }}
                                    </span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-semibold text-gray-900">
                                    <a href="{{ route('isp.service-profiles.show', $profile->id) }}" class="hover:text-primary-600 transition-colors">
                                        {{ $profile->name }}
                                    </a>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full 
                                    @if($profile->service_type === 'pppoe') bg-primary-100 text-primary-800
                                    @elseif($profile->service_type === 'hotspot') bg-success-100 text-success-800
                                    @elseif($profile->service_type === 'voucher') bg-warning-100 text-warning-800
                                    @else bg-gray-100 text-gray-800
                                    @endif
                                ">
                                    {{ strtoupper($profile->service_type) }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-medium text-gray-900">{{ $profile->download_speed ?: '-' }} / {{ $profile->upload_speed ?: '-' }} Mbps</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @if($profile->package_type === 'unlimited')
                                    <span class="px-2 py-0.5 bg-success-100 text-success-800 rounded-full text-xs font-medium">Unlimited</span>
                                @elseif($profile->package_type === 'time_based')
                                    <span class="px-2 py-0.5 bg-primary-100 text-primary-800 rounded-full text-xs font-medium">{{ $profile->duration_value }} {{ $profile->duration_unit === 'hours' ? 'Jam' : 'Hari' }}</span>
                                @elseif($profile->package_type === 'quota_based')
                                    <span class="px-2 py-0.5 bg-warning-100 text-warning-800 rounded-full text-xs font-medium">{{ $profile->quota_value }} {{ $profile->quota_unit }}</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-medium text-gray-900">{{ $profile->max_devices ?: '-' }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-semibold text-gray-900">
                                    {{ $profile->is_free ? 'Gratis' : ($profile->base_price ? 'Rp ' . number_format($profile->base_price, 0, ',', '.') : '-') }}
                                </div>
                            </td>
                            @if($isSuperAdmin)
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-600">
                                        {{ $profile->owner ? $profile->owner->name : '-' }}
                                    </div>
                                </td>
                            @endif
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                <div x-data="{ open: false }" class="relative inline-block text-left">
                                    <div>
                                        <button @click="open = !open" type="button" class="inline-flex items-center gap-1 p-2.5 text-gray-500 hover:text-gray-700 hover:bg-gray-100 rounded-lg transition-colors">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 5v.01M12 12v.01M12 19v.01M12 6a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2z"></path>
                                            </svg>
                                        </button>
                                    </div>
                                    <div x-show="open" @click.away="open = false" x-transition class="origin-top-right absolute right-0 mt-2 w-48 rounded-lg shadow-lg bg-white ring-1 ring-black ring-opacity-5 divide-y divide-gray-100 z-50">
                                        @if(!$profile->trashed())
                                            <div class="py-1">
                                                <a href="{{ route('isp.service-profiles.show', $profile->id) }}" class="flex items-center gap-2 px-4 py-2 text-sm text-gray-700 hover:bg-gray-50">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                                    </svg>
                                                    Lihat Detail
                                                </a>
                                                <a href="{{ route('isp.service-profiles.edit', $profile->id) }}" class="flex items-center gap-2 px-4 py-2 text-sm text-gray-700 hover:bg-gray-50">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                                    </svg>
                                                    Edit
                                                </a>
                                                <button 
                                                    type="button"
                                                    wire:click="duplicate({{ $profile->id }})" 
                                                    wire:loading.attr="disabled"
                                                    class="flex items-center gap-2 px-4 py-2 text-sm text-gray-700 hover:bg-gray-50 w-full text-left"
                                                >
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2 2v8a2 2 0 012 2z"></path>
                                                    </svg>
                                                    Duplicate
                                                    <span wire:loading class="ml-2 text-gray-400 text-xs">...</span>
                                                </button>
                                            </div>
                                            <div class="py-1">
                                                <button 
                                                    type="button"
                                                    wire:click="toggleStatus({{ $profile->id }})" 
                                                    wire:loading.attr="disabled"
                                                    class="flex items-center gap-2 px-4 py-2 text-sm text-gray-700 hover:bg-gray-50 w-full text-left"
                                                >
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                                    </svg>
                                                    {{ $profile->status === 'active' ? 'Nonaktifkan' : 'Aktifkan' }}
                                                    <span wire:loading class="ml-2 text-gray-400 text-xs">...</span>
                                                </button>
                                            </div>
                                            <div class="py-1">
                                                <button 
                                                    type="button"
                                                    wire:click="delete({{ $profile->id }})" 
                                                    wire:loading.attr="disabled"
                                                    wire:confirm="Apakah Anda yakin ingin menghapus paket ini?" 
                                                    class="flex items-center gap-2 px-4 py-2 text-sm text-danger-600 hover:bg-danger-50 w-full text-left"
                                                >
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                                    </svg>
                                                    Hapus
                                                    <span wire:loading class="ml-2 text-gray-400 text-xs">...</span>
                                                </button>
                                            </div>
                                        @else
                                            <div class="py-1">
                                                <button 
                                                    type="button"
                                                    wire:click="restore({{ $profile->id }})" 
                                                    wire:loading.attr="disabled"
                                                    wire:confirm="Apakah Anda yakin ingin memulihkan paket ini?" 
                                                    class="flex items-center gap-2 px-4 py-2 text-sm text-success-600 hover:bg-success-50 w-full text-left"
                                                >
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                                                    </svg>
                                                    Pulihkan
                                                    <span wire:loading class="ml-2 text-gray-400 text-xs">...</span>
                                                </button>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="10" class="px-6 py-16 text-center">
                                <div class="flex flex-col items-center justify-center">
                                    <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mb-4">
                                        <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                                        </svg>
                                    </div>
                                    <h3 class="text-lg font-semibold text-gray-900 mb-1">Belum ada Paket Internet</h3>
                                    <p class="text-gray-500 mb-6">Silakan tambahkan Paket Internet pertama.</p>
                                    <a href="{{ route('isp.service-profiles.create') }}" class="inline-flex items-center gap-2 px-5 py-2.5 bg-primary-600 text-white rounded-lg hover:bg-primary-700 transition-colors font-medium">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                                        </svg>
                                        Tambah Paket
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($profiles->hasPages())
            <div class="bg-white px-6 py-4 border-t border-gray-200">
                {{ $profiles->links() }}
            </div>
        @endif
    </x-base.card>

    <!-- Delete Confirmation Modal -->
    @if($showDeleteModal)
        <div x-data="{ open: @entangle('showDeleteModal') }" x-show="open" class="fixed inset-0 z-50 overflow-y-auto">
            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <!-- Background overlay -->
                <div x-show="open" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" class="fixed inset-0 transition-opacity bg-gray-500 bg-opacity-75"></div>

                <!-- Modal panel -->
                <div class="inline-block w-full max-w-md p-6 my-8 overflow-hidden text-left align-middle transition-all transform bg-white rounded-xl shadow-xl sm:align-middle">
                    <div class="mb-4">
                        <div class="flex items-center justify-center w-12 h-12 mx-auto mb-4 bg-danger-100 rounded-full">
                            <svg class="w-6 h-6 text-danger-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                            </svg>
                        </div>
                        <h3 class="text-lg font-semibold text-gray-900 mb-2 text-center">Hapus {{ count($selectedPackages) }} Paket?</h3>
                        <p class="text-sm text-gray-500 text-center">Data yang masih digunakan pelanggan tidak akan dapat dihapus.</p>
                    </div>
                    <div class="flex justify-end gap-3">
                        <button 
                            wire:click="closeDeleteModal" 
                            type="button" 
                            class="px-4 py-2 text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors font-medium"
                        >
                            Batal
                        </button>
                        <button 
                            wire:click="bulkDelete" 
                            wire:loading.attr="disabled"
                            type="button" 
                            class="px-4 py-2 text-white bg-red-600 rounded-lg hover:bg-red-700 transition-colors font-medium"
                        >
                            <span wire:loading.remove>Ya, Hapus</span>
                            <span wire:loading>Menghapus...</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <!-- Import Modal -->
    @if($showImportModal)
        <div x-data="{ open: @entangle('showImportModal') }" x-show="open" class="fixed inset-0 z-50 overflow-y-auto">
            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <!-- Background overlay -->
                <div x-show="open" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" class="fixed inset-0 transition-opacity bg-gray-500 bg-opacity-75"></div>

                <!-- Modal panel -->
                <div class="inline-block w-full max-w-lg p-6 my-8 overflow-hidden text-left align-middle transition-all transform bg-white rounded-xl shadow-xl sm:align-middle">
                    <div class="mb-4">
                        <div class="flex items-center justify-center w-12 h-12 mx-auto mb-4 bg-primary-100 rounded-full">
                            <svg class="w-6 h-6 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 0 0 3 3h10a3 3 0 0 0 3-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"></path>
                            </svg>
                        </div>
                        <h3 class="text-lg font-semibold text-gray-900 mb-2 text-center">Import Paket Internet</h3>
                        <p class="text-sm text-gray-500 text-center mb-4">Pilih file Excel (.xlsx, .xls) atau CSV untuk diimpor.</p>
                        
                        <div class="space-y-4">
                            <div>
                                <input type="file" wire:model="importFile" class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-medium file:bg-primary-50 file:text-primary-700 hover:file:bg-primary-100">
                                @error('importFile') <span class="text-danger-500 text-sm">{{ $message }}</span> @enderror
                            </div>
                            
                            <div class="bg-gray-50 p-4 rounded-lg text-sm">
                                <p class="font-medium text-gray-700 mb-2">Format Kolom:</p>
                                <ul class="text-gray-600 list-disc pl-5 space-y-1">
                                    <li>Nama Paket (wajib)</li>
                                    <li>Deskripsi</li>
                                    <li>Jenis Layanan (pppoe/hotspot/voucher)</li>
                                    <li>Download Speed (Mbps)</li>
                                    <li>Upload Speed (Mbps)</li>
                                    <li>Harga Dasar</li>
                                    <li>Harga Owner</li>
                                    <li>Harga Reseller</li>
                                    <li>Status (active/inactive)</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                    <div class="flex justify-end gap-3">
                        <button 
                            wire:click="closeImportModal" 
                            type="button" 
                            class="px-4 py-2 text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors font-medium"
                        >
                            Batal
                        </button>
                        <button 
                            wire:click="import" 
                            wire:loading.attr="disabled"
                            type="button" 
                            class="px-4 py-2 text-white bg-primary-600 rounded-lg hover:bg-primary-700 transition-colors font-medium">
                            <span wire:loading.remove>Import</span>
                            <span wire:loading>Memproses...</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <!-- Bulk Edit Modal -->
    @if($showBulkEditModal)
        <div x-data="{ open: @entangle('showBulkEditModal') }" x-show="open" class="fixed inset-0 z-50 overflow-y-auto">
            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <!-- Background overlay -->
                <div x-show="open" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" class="fixed inset-0 transition-opacity bg-gray-500 bg-opacity-75"></div>

                <!-- Modal panel -->
                <div class="inline-block w-full max-w-2xl p-6 my-8 overflow-hidden text-left align-middle transition-all transform bg-white rounded-xl shadow-xl sm:align-middle">
                    <div class="mb-4">
                        <div class="flex items-center justify-center w-12 h-12 mx-auto mb-4 bg-primary-100 rounded-full">
                            <svg class="w-6 h-6 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                            </svg>
                        </div>
                        <h3 class="text-lg font-semibold text-gray-900 mb-2 text-center">Edit Massal {{ count($selectedPackages) }} Paket</h3>
                        <p class="text-sm text-gray-500 text-center mb-4">Isi hanya kolom yang ingin diubah. Biarkan kosong jika tidak ingin diubah.</p>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <!-- Bandwidth -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Download Speed (Mbps)</label>
                                <input type="number" wire:model="bulkEditData.download_speed" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500" placeholder="Kosongkan jika tidak diubah">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Upload Speed (Mbps)</label>
                                <input type="number" wire:model="bulkEditData.upload_speed" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500" placeholder="Kosongkan jika tidak diubah">
                            </div>
                            
                            <!-- Harga -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Harga Dasar</label>
                                <input type="number" wire:model="bulkEditData.base_price" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500" placeholder="Kosongkan jika tidak diubah">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Harga Owner</label>
                                <input type="number" wire:model="bulkEditData.owner_price" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500" placeholder="Kosongkan jika tidak diubah">
                            </div>
                            <div class="md:col-span-2">
                                <label class="block text-sm font-medium text-gray-700 mb-1">Harga Reseller</label>
                                <input type="number" wire:model="bulkEditData.reseller_price" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500" placeholder="Kosongkan jika tidak diubah">
                            </div>
                            
                            <!-- Status -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                                <select wire:model="bulkEditData.status" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                                    <option value="">-- Pilih Status --</option>
                                    <option value="active">Aktif</option>
                                    <option value="inactive">Nonaktif</option>
                                </select>
                            </div>
                            
                            @php $isSuperAdmin = auth()->user()->hasRole('super_admin') ?? false; @endphp
                            @if($isSuperAdmin)
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Owner</label>
                                <select wire:model="bulkEditData.owner_id" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                                    <option value="">-- Pilih Owner --</option>
                                    @foreach(\App\Models\User::select('id', 'name')->limit(100)->get() as $user)
                                        <option value="{{ $user->id }}">{{ $user->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            @endif
                        </div>
                    </div>
                    <div class="flex justify-end gap-3">
                        <button 
                            wire:click="closeBulkEditModal" 
                            type="button" 
                            class="px-4 py-2 text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors font-medium"
                        >
                            Batal
                        </button>
                        <button 
                            wire:click="bulkEdit" 
                            wire:loading.attr="disabled"
                            type="button" 
                            class="px-4 py-2 text-white bg-primary-600 rounded-lg hover:bg-primary-700 transition-colors font-medium"
                        >
                            <span wire:loading.remove>Simpan Perubahan</span>
                            <span wire:loading>Menyimpan...</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
