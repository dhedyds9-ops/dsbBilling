@section('page_title')
    <a href="{{ route('crm.customers.index') }}" class="flex items-center text-slate-500 hover:text-slate-800 dark:text-slate-200 dark:hover:text-slate-100 transition-colors mr-2">
        <span class="material-symbols-outlined notranslate" translate="no">arrow_back</span>
    </a>
    <div class="w-8 h-8 rounded-full bg-indigo-100 dark:bg-indigo-900/50 flex items-center justify-center text-indigo-600 dark:text-indigo-400 font-bold text-sm mr-2">
        {{ substr($customer->name ?? 'U', 0, 1) }}
    </div>
    <span class="text-lg">Detail Pelanggan</span>
@endsection

<div class="space-y-6 pb-10">
    @php
        $customerId = ($customer->created_at ? $customer->created_at->format('Ymd') : date('Ymd')) . str_pad($customer->id % 100, 2, '0', STR_PAD_LEFT);
    @endphp

    {{-- Header Content --}}
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-black text-slate-800 dark:text-slate-100">{{ $customer->name }}</h1>
            <div class="flex items-center gap-3 mt-1 text-sm text-slate-500 dark:text-slate-400">
                <span class="font-mono text-xs font-semibold text-indigo-600 dark:text-indigo-400">{{ $customerId }}</span>
                <span class="px-2.5 py-0.5 font-semibold rounded-md text-xs
                    @if($customer->status === 'active') bg-emerald-100 text-emerald-700 dark:bg-emerald-900/50 dark:text-emerald-400
                    @elseif($customer->status === 'inactive') bg-slate-100 text-slate-600 dark:bg-slate-800 dark:text-slate-400
                    @else bg-red-100 text-red-700 dark:bg-red-900/50 dark:text-red-400
                    @endif
                ">
                    {{ ucfirst($customer->status) }}
                </span>
                <span>{{ $customer->email ?? '-' }}</span>
                <span class="font-mono text-xs">{{ $customer->phone ?? '-' }}</span>
            </div>
        </div>
        <div class="flex items-center gap-2">
            <button wire:click="openEditModal" class="inline-flex items-center gap-2 px-4 py-2 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-700 dark:text-slate-300 rounded-xl text-sm font-semibold shadow-sm hover:bg-slate-50 dark:bg-slate-900/50 dark:hover:bg-slate-700 transition-all cursor-pointer">
                <span class="material-symbols-outlined notranslate" translate="no" style="font-size:18px">edit</span>
                Edit Profil
            </button>
        </div>
    </div>

    {{-- KPI Cards --}}
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
        {{-- Paket --}}
        <div class="relative overflow-x-auto rounded-xl border border-indigo-200 dark:border-indigo-800/60 shadow-md bg-gradient-to-br from-indigo-50 to-white dark:from-indigo-950/50 dark:to-slate-800 group hover:shadow-lg transition-all">
            <div class="absolute top-0 left-0 right-0 h-1 bg-gradient-to-r from-indigo-500 to-blue-400 rounded-t-xl"></div>
            <div class="absolute top-3 right-3 opacity-10 text-indigo-400 group-hover:opacity-20 group-hover:scale-110 transition-all">
                <span class="material-symbols-outlined notranslate" translate="no" style="font-size:56px">wifi</span>
            </div>
            <div class="p-4 pt-5">
                <h3 class="text-xs font-bold text-indigo-500 dark:text-indigo-400 uppercase tracking-widest mb-2">Paket</h3>
                <div class="text-xl font-black text-indigo-700 dark:text-indigo-300 mb-1 leading-tight">
                    {{ $customer->customerServices->first()?->serviceProfile?->name ?? $customer->customerServices->first()?->service?->name ?? 'Belum ada paket' }}
                </div>
                @if($customer->customerServices->first()?->serviceProfile)
                    <div class="text-xs text-slate-500 dark:text-slate-400">
                        ↓ {{ $customer->customerServices->first()->serviceProfile->download_speed }} Mbps &nbsp;↑ {{ $customer->customerServices->first()->serviceProfile->upload_speed }} Mbps
                    </div>
                @else
                    <div class="text-xs text-slate-500 dark:text-slate-400">-</div>
                @endif
            </div>
        </div>

        {{-- Tagihan Belum Dibayar --}}
        <div class="relative overflow-x-auto rounded-xl border border-rose-200 dark:border-rose-800/60 shadow-md bg-gradient-to-br from-rose-50 to-white dark:from-rose-950/50 dark:to-slate-800 group hover:shadow-lg transition-all">
            <div class="absolute top-0 left-0 right-0 h-1 bg-gradient-to-r from-rose-500 to-red-400 rounded-t-xl"></div>
            <div class="absolute top-3 right-3 opacity-10 text-rose-400 group-hover:opacity-20 group-hover:scale-110 transition-all">
                <span class="material-symbols-outlined notranslate" translate="no" style="font-size:56px">receipt_long</span>
            </div>
            <div class="p-4 pt-5">
                <h3 class="text-xs font-bold text-rose-500 dark:text-rose-400 uppercase tracking-widest mb-2">Tagihan Belum Lunas</h3>
                <div class="text-2xl font-black text-rose-700 dark:text-rose-300 mb-1">
                    Rp {{ number_format($unpaidBill, 0, ',', '.') }}
                </div>
                <div class="text-xs text-slate-500 dark:text-slate-400">
                    Jatuh tempo: {{ $earliestDueDate ? $earliestDueDate->format('d/m/Y') : '-' }}
                </div>
            </div>
        </div>

        {{-- Status Layanan --}}
        <div class="relative overflow-x-auto rounded-xl border border-emerald-200 dark:border-emerald-800/60 shadow-md bg-gradient-to-br from-emerald-50 to-white dark:from-emerald-950/50 dark:to-slate-800 group hover:shadow-lg transition-all">
            <div class="absolute top-0 left-0 right-0 h-1 bg-gradient-to-r from-emerald-500 to-teal-400 rounded-t-xl"></div>
            <div class="absolute top-3 right-3 opacity-10 text-emerald-400 group-hover:opacity-20 group-hover:scale-110 transition-all">
                <span class="material-symbols-outlined notranslate" translate="no" style="font-size:56px">check_circle</span>
            </div>
            <div class="p-4 pt-5">
                <h3 class="text-xs font-bold text-emerald-500 dark:text-emerald-400 uppercase tracking-widest mb-2">Status Layanan</h3>
                <div class="text-2xl font-black text-emerald-700 dark:text-emerald-300 mb-1">
                    {{ ucfirst($customer->customerServices->first()?->status ?? 'inactive') }}
                </div>
                <div class="text-xs text-slate-500 dark:text-slate-400">layanan utama aktif</div>
            </div>
        </div>

        {{-- Jumlah Layanan --}}
        <div class="relative overflow-x-auto rounded-xl border border-sky-200 dark:border-sky-800/60 shadow-md bg-gradient-to-br from-sky-50 to-white dark:from-sky-950/50 dark:to-slate-800 group hover:shadow-lg transition-all">
            <div class="absolute top-0 left-0 right-0 h-1 bg-gradient-to-r from-sky-500 to-cyan-400 rounded-t-xl"></div>
            <div class="absolute top-3 right-3 opacity-10 text-sky-400 group-hover:opacity-20 group-hover:scale-110 transition-all">
                <span class="material-symbols-outlined notranslate" translate="no" style="font-size:56px">dns</span>
            </div>
            <div class="p-4 pt-5">
                <h3 class="text-xs font-bold text-sky-500 dark:text-sky-400 uppercase tracking-widest mb-2">Jumlah Layanan</h3>
                <div class="text-2xl font-black text-sky-700 dark:text-sky-300 mb-1">{{ $customer->customerServices->count() }}</div>
                <div class="text-xs text-slate-500 dark:text-slate-400">total koneksi terdaftar</div>
            </div>
        </div>
    </div>

    {{-- Tabs Navigation --}}
    <div class="border-b border-slate-200 dark:border-slate-700 overflow-x-auto">
        <nav class="flex gap-1 whitespace-nowrap" aria-label="Tabs">
            @foreach([
                ['tab' => 'profile',       'label' => 'Profil',        'icon' => 'person'],
                ['tab' => 'contract',      'label' => 'Kontrak',       'icon' => 'description'],
                ['tab' => 'billing',       'label' => 'Billing',       'icon' => 'payments'],
                ['tab' => 'invoice',       'label' => 'Invoice',       'icon' => 'receipt'],
                ['tab' => 'payment',       'label' => 'Pembayaran',    'icon' => 'credit_card'],
                ['tab' => 'ticket',        'label' => 'Tiket',         'icon' => 'confirmation_number'],
                ['tab' => 'device',        'label' => 'Perangkat',     'icon' => 'router'],
                ['tab' => 'installation',  'label' => 'Instalasi',     'icon' => 'engineering'],
                ['tab' => 'gis',           'label' => 'GIS',           'icon' => 'map'],
                ['tab' => 'monitoring',    'label' => 'Monitoring',    'icon' => 'monitor_heart'],
                ['tab' => 'history',       'label' => 'Riwayat',       'icon' => 'history'],
                ['tab' => 'activity',      'label' => 'Aktivitas',     'icon' => 'timeline'],
                ['tab' => 'notification',  'label' => 'Notifikasi',    'icon' => 'notifications'],
            ] as $t)
            <button wire:click="setActiveTab('{{ $t['tab'] }}')"
                    class="inline-flex items-center gap-1.5 px-4 py-3 text-sm font-medium border-b-2 transition-colors whitespace-nowrap
                        @if($activeTab === $t['tab']) border-indigo-500 text-indigo-600 dark:text-indigo-400
                        @else border-transparent text-slate-500 dark:text-slate-400 hover:text-slate-700 dark:text-slate-300 dark:hover:text-slate-300 hover:border-slate-300 dark:border-slate-600
                        @endif">
                <span class="material-symbols-outlined notranslate" translate="no" style="font-size:16px">{{ $t['icon'] }}</span>
                {{ $t['label'] }}
            </button>
            @endforeach
        </nav>
    </div>

    {{-- Tab Content --}}
    <div class="mt-2">

        {{-- ============ PROFILE TAB ============ --}}
        @if($activeTab === 'profile')
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <x-base.card>
                <x-slot name="header">
                    <h3 class="text-base font-bold text-slate-900 dark:text-slate-100 flex items-center gap-2">
                        <span class="material-symbols-outlined notranslate text-indigo-500" translate="no" style="font-size:20px">person</span>
                        Informasi Pribadi
                    </h3>
                </x-slot>
                <div class="space-y-4">
                    <div>
                        <label class="block text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-1">Customer ID</label>
                        <p class="text-slate-900 dark:text-slate-100 font-mono font-bold text-lg text-indigo-600 dark:text-indigo-400">{{ $customerId }}</p>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-1">Nama Lengkap</label>
                        <p class="text-slate-900 dark:text-slate-100 font-semibold">{{ $customer->name }}</p>
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-1">Email</label>
                            <p class="text-slate-900 dark:text-slate-100">{{ $customer->email ?? '-' }}</p>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-1">WhatsApp / Telepon</label>
                            <p class="text-slate-900 dark:text-slate-100">{{ $customer->phone ?? '-' }}</p>
                        </div>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-1">Alamat</label>
                        <p class="text-slate-900 dark:text-slate-100">{{ $customer->address ?? '-' }}</p>
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-1">Terdaftar Sejak</label>
                            <p class="text-slate-900 dark:text-slate-100">{{ $customer->created_at?->format('d/m/Y') ?? '-' }}</p>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-1">Reseller / Owner</label>
                            <p class="text-slate-900 dark:text-slate-100">{{ $customer->reseller?->name ?? 'Default' }}</p>
                        </div>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-1">Status Akun</label>
                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-sm font-semibold
                            @if($customer->status === 'active') bg-emerald-100 text-emerald-700 dark:bg-emerald-900/50 dark:text-emerald-400
                            @elseif($customer->status === 'inactive') bg-slate-100 text-slate-600 dark:bg-slate-800 dark:text-slate-400
                            @else bg-red-100 text-red-700 dark:bg-red-900/50 dark:text-red-400
                            @endif">
                            <span class="w-1.5 h-1.5 rounded-full {{ $customer->status === 'active' ? 'bg-emerald-500 animate-pulse' : 'bg-slate-400' }}"></span>
                            {{ ucfirst($customer->status) }}
                        </span>
                    </div>
                </div>
            </x-base.card>

            <x-base.card>
                <x-slot name="header">
                    <h3 class="text-base font-bold text-slate-900 dark:text-slate-100 flex items-center gap-2">
                        <span class="material-symbols-outlined notranslate text-indigo-500" translate="no" style="font-size:20px">wifi</span>
                        Informasi Layanan
                    </h3>
                </x-slot>
                <div class="space-y-4">
                    @forelse($customer->customerServices as $service)
                    <div class="border border-slate-200 dark:border-slate-700 rounded-lg p-4 space-y-3">
                        <div class="flex items-center justify-between">
                            <div>
                                <label class="block text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-1">Paket Internet</label>
                                <p class="text-slate-900 dark:text-slate-100 font-bold">{{ $service->serviceProfile?->name ?? $service->service?->name ?? 'N/A' }}</p>
                            </div>
                            <div class="flex items-center gap-2">
                                <span class="px-2.5 py-1 rounded-lg text-xs font-semibold
                                    @if($service->status === 'active') bg-emerald-100 dark:bg-emerald-900/50 text-emerald-700
                                    @elseif($service->status === 'suspended') bg-red-100 dark:bg-red-900/50 text-red-600
                                    @else bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400
                                    @endif">
                                    {{ ucfirst($service->status) }}
                                </span>
                                <button wire:click="openEditServiceModal({{ $service->id }})" class="p-1 text-slate-400 hover:text-indigo-600 transition-colors" title="Ganti Layanan / Paket">
                                    <span class="material-symbols-outlined notranslate" translate="no" style="font-size:20px">settings</span>
                                </button>
                            </div>
                        </div>

                        <div class="bg-slate-50 dark:bg-slate-900/50 p-3 rounded-lg flex items-center gap-8">
                            <div>
                                <span class="block text-xs text-slate-500 dark:text-slate-400 mb-0.5">Username</span>
                                <div class="flex items-center gap-2">
                                    <span class="font-mono font-semibold text-slate-900 dark:text-slate-100">{{ $service->username ?? '-' }}</span>
                                    <button onclick="navigator.clipboard.writeText('{{ $service->username }}'); alert('Username tersalin!')" class="text-slate-400 hover:text-indigo-600 transition-colors" title="Copy Username">
                                        <span class="material-symbols-outlined notranslate" translate="no" style="font-size:16px">content_copy</span>
                                    </button>
                                </div>
                            </div>
                            <div>
                                <span class="block text-xs text-slate-500 dark:text-slate-400 mb-0.5">Password</span>
                                <div class="flex items-center gap-2">
                                    <span class="font-mono font-semibold text-slate-900 dark:text-slate-100">{{ $service->password ?? '-' }}</span>
                                    <button onclick="navigator.clipboard.writeText('{{ $service->password }}'); alert('Password tersalin!')" class="text-slate-400 hover:text-indigo-600 transition-colors" title="Copy Password">
                                        <span class="material-symbols-outlined notranslate" translate="no" style="font-size:16px">content_copy</span>
                                    </button>
                                </div>
                            </div>
                        </div>

                        @if($service->serviceProfile)
                        <div class="grid grid-cols-2 gap-3 text-sm">
                            <div>
                                <label class="block text-xs text-slate-500 dark:text-slate-400 mb-0.5">Download</label>
                                <span class="font-semibold text-slate-800 dark:text-slate-200">{{ $service->serviceProfile->download_speed }} Mbps</span>
                            </div>
                            <div>
                                <label class="block text-xs text-slate-500 dark:text-slate-400 mb-0.5">Upload</label>
                                <span class="font-semibold text-slate-800 dark:text-slate-200">{{ $service->serviceProfile->upload_speed }} Mbps</span>
                            </div>
                            <div>
                                <label class="block text-xs text-slate-500 dark:text-slate-400 mb-0.5">Harga Paket</label>
                                <span class="font-semibold text-slate-800 dark:text-slate-200">Rp {{ number_format($service->serviceProfile->price ?? 0, 0, ',', '.') }}</span>
                            </div>
                            <div>
                                <label class="block text-xs text-slate-500 dark:text-slate-400 mb-0.5">Tgl Aktivasi</label>
                                <span class="font-semibold text-slate-800 dark:text-slate-200">{{ $service->activated_at?->format('d/m/Y') ?? '-' }}</span>
                            </div>
                        </div>
                        @endif
                    </div>
                    @empty
                    <div class="text-center py-8 text-slate-400">
                        <span class="material-symbols-outlined notranslate" translate="no" style="font-size:40px">wifi_off</span>
                        <p class="mt-2 text-sm">Belum ada layanan terdaftar</p>
                    </div>
                    @endforelse
                </div>
            </x-base.card>
        </div>
        @endif

        {{-- ============ CONTRACT TAB ============ --}}
        @if($activeTab === 'contract')
        <x-base.card :padding="false">
            <x-slot name="header">
                <h3 class="text-base font-bold text-slate-900 dark:text-slate-100 flex items-center gap-2">
                    <span class="material-symbols-outlined notranslate text-indigo-500" translate="no" style="font-size:20px">description</span>
                    Kontrak Layanan
                </h3>
            </x-slot>
            @if($customer->contracts->isNotEmpty())
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="bg-slate-50 dark:bg-slate-900/50 border-b border-slate-200 dark:border-slate-700 text-xs uppercase tracking-wider text-slate-500 dark:text-slate-400">
                            <th class="px-6 py-3 text-left font-semibold">Nomor Kontrak</th>
                            <th class="px-6 py-3 text-left font-semibold">Tanggal Mulai</th>
                            <th class="px-6 py-3 text-left font-semibold">Tanggal Berakhir</th>
                            <th class="px-6 py-3 text-left font-semibold">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-700">
                        @foreach($customer->contracts as $contract)
                        <tr class="hover:bg-slate-50 dark:bg-slate-900/50 dark:hover:bg-slate-800/50">
                            <td class="px-6 py-4 font-mono font-semibold text-slate-900 dark:text-slate-100">{{ $contract->contract_number }}</td>
                            <td class="px-6 py-4 text-slate-600 dark:text-slate-400">{{ $contract->start_date?->format('d/m/Y') ?? '-' }}</td>
                            <td class="px-6 py-4 text-slate-600 dark:text-slate-400">{{ $contract->end_date?->format('d/m/Y') ?? '-' }}</td>
                            <td class="px-6 py-4">
                                <span class="px-2 py-1 text-xs font-semibold rounded-lg
                                    @if($contract->status === 'active') bg-emerald-100 dark:bg-emerald-900/50 text-emerald-700
                                    @elseif($contract->status === 'expired') bg-red-100 dark:bg-red-900/50 text-red-600
                                    @else bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400
                                    @endif">
                                    {{ ucfirst($contract->status) }}
                                </span>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @else
            <div class="p-10 text-center text-slate-400">
                <span class="material-symbols-outlined notranslate" translate="no" style="font-size:40px">description</span>
                <p class="mt-2 text-sm">Belum ada kontrak</p>
            </div>
            @endif
        </x-base.card>
        @endif

        {{-- ============ BILLING TAB ============ --}}
        @if($activeTab === 'billing')
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <x-base.card class="lg:col-span-2">
                <x-slot name="header">
                    <h3 class="text-base font-bold text-slate-900 dark:text-slate-100 flex items-center gap-2">
                        <span class="material-symbols-outlined notranslate text-indigo-500" translate="no" style="font-size:20px">payments</span>
                        Ringkasan Tagihan
                    </h3>
                </x-slot>
                <div class="space-y-0 divide-y divide-slate-200 dark:divide-slate-700">
                    <div class="flex justify-between items-center py-4">
                        <span class="text-slate-600 dark:text-slate-400">Tagihan Bulanan (estimasi)</span>
                        <span class="text-lg font-bold text-slate-900 dark:text-slate-100">Rp {{ number_format($monthlyBill, 0, ',', '.') }}</span>
                    </div>
                    <div class="flex justify-between items-center py-4">
                        <span class="text-slate-600 dark:text-slate-400">Total Tagihan Belum Lunas</span>
                        <span class="text-lg font-bold {{ $unpaidBill > 0 ? 'text-red-600' : 'text-emerald-600' }}">Rp {{ number_format($unpaidBill, 0, ',', '.') }}</span>
                    </div>
                    <div class="flex justify-between items-center py-4">
                        <span class="text-slate-600 dark:text-slate-400">Jatuh Tempo Terdekat</span>
                        <span class="text-lg font-bold text-slate-900 dark:text-slate-100">{{ $earliestDueDate ? $earliestDueDate->format('d/m/Y') : '-' }}</span>
                    </div>
                    <div class="flex justify-between items-center py-4">
                        <span class="text-slate-600 dark:text-slate-400">Metode Pembayaran Terakhir</span>
                        <span class="text-slate-900 dark:text-slate-100">{{ $payments->first()?->method ?? '-' }}</span>
                    </div>
                </div>
            </x-base.card>
            <x-base.card>
                <x-slot name="header">
                    <h3 class="text-base font-bold text-slate-900 dark:text-slate-100 flex items-center gap-2">
                        <span class="material-symbols-outlined notranslate text-rose-500" translate="no" style="font-size:20px">calendar_today</span>
                        Jatuh Tempo
                    </h3>
                </x-slot>
                <div class="text-center py-4">
                    @if($earliestDueDate)
                        @php 
                            // Hitung selisih hari dengan startOfDay agar dapat angka bulat (integer)
                            $daysLeft = (int) now()->startOfDay()->diffInDays($earliestDueDate->copy()->startOfDay(), false); 
                        @endphp
                        <p class="text-4xl font-black {{ $daysLeft < 3 ? 'text-red-600' : ($daysLeft < 7 ? 'text-orange-600' : 'text-slate-800 dark:text-slate-100') }}">
                            {{ max(0, $daysLeft) }}
                        </p>
                        <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">Hari Lagi</p>
                        <p class="text-xs text-slate-400 mt-2">{{ $earliestDueDate->format('d/m/Y') }}</p>
                    @else
                        <span class="material-symbols-outlined notranslate text-slate-300" translate="no" style="font-size:40px">check_circle</span>
                        <p class="text-sm text-slate-500 dark:text-slate-400 mt-2">Tidak ada tagihan jatuh tempo</p>
                    @endif
                </div>
            </x-base.card>
        </div>
        @endif

        {{-- ============ INVOICE TAB ============ --}}
        @if($activeTab === 'invoice')
        <x-base.card :padding="false">
            <x-slot name="header">
                <h3 class="text-base font-bold text-slate-900 dark:text-slate-100 flex items-center gap-2">
                    <span class="material-symbols-outlined notranslate text-indigo-500" translate="no" style="font-size:20px">receipt</span>
                    Daftar Invoice
                </h3>
            </x-slot>
            @if($invoices->isNotEmpty())
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="bg-slate-50 dark:bg-slate-900/50 border-b border-slate-200 dark:border-slate-700 text-xs uppercase tracking-wider text-slate-500 dark:text-slate-400">
                            <th class="px-6 py-3 text-left font-semibold">Nomor Invoice</th>
                            <th class="px-6 py-3 text-left font-semibold">Tanggal</th>
                            <th class="px-6 py-3 text-left font-semibold">Jatuh Tempo</th>
                            <th class="px-6 py-3 text-left font-semibold">Jumlah</th>
                            <th class="px-6 py-3 text-left font-semibold">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-700">
                        @foreach($invoices as $invoice)
                        <tr class="hover:bg-slate-50 dark:bg-slate-900/50 dark:hover:bg-slate-800/50">
                            <td class="px-6 py-4 font-mono font-semibold text-slate-900 dark:text-slate-100">{{ $invoice->invoice_number }}</td>
                            <td class="px-6 py-4 text-slate-600 dark:text-slate-400">{{ $invoice->issue_date?->format('d/m/Y') ?? '-' }}</td>
                            <td class="px-6 py-4 text-slate-600 dark:text-slate-400">{{ $invoice->due_date?->format('d/m/Y') ?? '-' }}</td>
                            <td class="px-6 py-4 font-semibold text-slate-900 dark:text-slate-100">Rp {{ number_format($invoice->total_amount, 0, ',', '.') }}</td>
                            <td class="px-6 py-4">
                                <span class="px-2 py-1 text-xs font-semibold rounded-lg
                                    @if($invoice->status === 'paid') bg-emerald-100 dark:bg-emerald-900/50 text-emerald-700
                                    @elseif($invoice->status === 'overdue') bg-red-100 dark:bg-red-900/50 text-red-600
                                    @else bg-orange-100 text-orange-600
                                    @endif">
                                    {{ $invoice->status === 'paid' ? 'Lunas' : ucfirst($invoice->status) }}
                                </span>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @else
            <div class="p-10 text-center text-slate-400">
                <span class="material-symbols-outlined notranslate" translate="no" style="font-size:40px">receipt</span>
                <p class="mt-2 text-sm">Belum ada invoice</p>
            </div>
            @endif
        </x-base.card>
        @endif

        {{-- ============ PAYMENT TAB ============ --}}
        @if($activeTab === 'payment')
        <x-base.card :padding="false">
            <x-slot name="header">
                <h3 class="text-base font-bold text-slate-900 dark:text-slate-100 flex items-center gap-2">
                    <span class="material-symbols-outlined notranslate text-indigo-500" translate="no" style="font-size:20px">credit_card</span>
                    Riwayat Pembayaran
                </h3>
            </x-slot>
            @if($payments->isNotEmpty())
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="bg-slate-50 dark:bg-slate-900/50 border-b border-slate-200 dark:border-slate-700 text-xs uppercase tracking-wider text-slate-500 dark:text-slate-400">
                            <th class="px-6 py-3 text-left font-semibold">ID Pembayaran</th>
                            <th class="px-6 py-3 text-left font-semibold">Tanggal</th>
                            <th class="px-6 py-3 text-left font-semibold">Jumlah</th>
                            <th class="px-6 py-3 text-left font-semibold">Metode</th>
                            <th class="px-6 py-3 text-left font-semibold">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-700">
                        @foreach($payments as $payment)
                        <tr class="hover:bg-slate-50 dark:bg-slate-900/50 dark:hover:bg-slate-800/50">
                            <td class="px-6 py-4 font-mono text-xs text-slate-900 dark:text-slate-100">{{ $payment->uuid ?? $payment->id }}</td>
                            <td class="px-6 py-4 text-slate-600 dark:text-slate-400">{{ $payment->paid_at?->format('d/m/Y') ?? $payment->created_at->format('d/m/Y') }}</td>
                            <td class="px-6 py-4 font-semibold text-slate-900 dark:text-slate-100">Rp {{ number_format($payment->amount, 0, ',', '.') }}</td>
                            <td class="px-6 py-4 text-slate-600 dark:text-slate-400">{{ $payment->method ?? '-' }}</td>
                            <td class="px-6 py-4">
                                <span class="px-2 py-1 text-xs font-semibold rounded-lg
                                    @if($payment->status === 'success') bg-emerald-100 dark:bg-emerald-900/50 text-emerald-700
                                    @elseif($payment->status === 'failed') bg-red-100 dark:bg-red-900/50 text-red-600
                                    @else bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400
                                    @endif">
                                    {{ ucfirst($payment->status) }}
                                </span>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @else
            <div class="p-10 text-center text-slate-400">
                <span class="material-symbols-outlined notranslate" translate="no" style="font-size:40px">credit_card_off</span>
                <p class="mt-2 text-sm">Belum ada riwayat pembayaran</p>
            </div>
            @endif
        </x-base.card>
        @endif

        {{-- ============ TICKET TAB ============ --}}
        @if($activeTab === 'ticket')
        <x-base.card :padding="false">
            <x-slot name="header">
                <h3 class="text-base font-bold text-slate-900 dark:text-slate-100 flex items-center gap-2">
                    <span class="material-symbols-outlined notranslate text-indigo-500" translate="no" style="font-size:20px">confirmation_number</span>
                    Tiket Support
                </h3>
            </x-slot>
            @if(!empty($tickets))
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="bg-slate-50 dark:bg-slate-900/50 border-b border-slate-200 dark:border-slate-700 text-xs uppercase tracking-wider text-slate-500 dark:text-slate-400">
                            <th class="px-6 py-3 text-left font-semibold">ID Tiket</th>
                            <th class="px-6 py-3 text-left font-semibold">Judul</th>
                            <th class="px-6 py-3 text-left font-semibold">Prioritas</th>
                            <th class="px-6 py-3 text-left font-semibold">Status</th>
                            <th class="px-6 py-3 text-left font-semibold">Dibuat</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-700">
                        @foreach($tickets as $ticket)
                        <tr class="hover:bg-slate-50 dark:bg-slate-900/50 dark:hover:bg-slate-800/50">
                            <td class="px-6 py-4 font-mono font-semibold text-slate-900 dark:text-slate-100">{{ $ticket['id'] }}</td>
                            <td class="px-6 py-4 text-slate-600 dark:text-slate-400">{{ $ticket['title'] }}</td>
                            <td class="px-6 py-4">
                                <span class="px-2 py-1 text-xs font-semibold rounded-lg
                                    @if($ticket['priority'] === 'high') bg-red-100 dark:bg-red-900/50 text-red-600
                                    @elseif($ticket['priority'] === 'medium') bg-orange-100 text-orange-600
                                    @else bg-blue-100 dark:bg-blue-900/50 text-blue-600
                                    @endif">
                                    {{ $ticket['priority'] === 'high' ? 'Tinggi' : ($ticket['priority'] === 'medium' ? 'Sedang' : 'Rendah') }}
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                <span class="px-2 py-1 text-xs font-semibold rounded-lg
                                    @if($ticket['status'] === 'open') bg-emerald-100 dark:bg-emerald-900/50 text-emerald-700
                                    @elseif($ticket['status'] === 'in_progress') bg-blue-100 dark:bg-blue-900/50 text-blue-600
                                    @else bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400
                                    @endif">
                                    {{ $ticket['status'] === 'open' ? 'Buka' : ($ticket['status'] === 'in_progress' ? 'Dalam Proses' : 'Selesai') }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-slate-600 dark:text-slate-400">{{ $ticket['created_at']->format('d/m/Y') }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @else
            <div class="p-10 text-center text-slate-400">
                <span class="material-symbols-outlined notranslate" translate="no" style="font-size:40px">confirmation_number</span>
                <p class="mt-2 text-sm font-semibold text-slate-500 dark:text-slate-400">Belum ada tiket support</p>
                <p class="text-xs mt-1">Modul tiket akan aktif setelah integrasi selesai</p>
            </div>
            @endif
        </x-base.card>
        @endif

        {{-- ============ DEVICE TAB ============ --}}
        @if($activeTab === 'device')
        <x-base.card :padding="false">
            <x-slot name="header">
                <h3 class="text-base font-bold text-slate-900 dark:text-slate-100 flex items-center gap-2">
                    <span class="material-symbols-outlined notranslate text-indigo-500" translate="no" style="font-size:20px">router</span>
                    Perangkat Terdaftar
                </h3>
            </x-slot>
            @if(!empty($devices))
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="bg-slate-50 dark:bg-slate-900/50 border-b border-slate-200 dark:border-slate-700 text-xs uppercase tracking-wider text-slate-500 dark:text-slate-400">
                            <th class="px-6 py-3 text-left font-semibold">ID</th>
                            <th class="px-6 py-3 text-left font-semibold">Tipe</th>
                            <th class="px-6 py-3 text-left font-semibold">Model</th>
                            <th class="px-6 py-3 text-left font-semibold">Serial Number</th>
                            <th class="px-6 py-3 text-left font-semibold">WiFi SSID</th>
                            <th class="px-6 py-3 text-left font-semibold">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-700">
                        @foreach($devices as $device)
                        <tr class="hover:bg-slate-50 dark:bg-slate-900/50 dark:hover:bg-slate-800/50">
                            <td class="px-6 py-4 font-mono text-xs text-slate-900 dark:text-slate-100">{{ $device['id'] }}</td>
                            <td class="px-6 py-4 text-slate-600 dark:text-slate-400">{{ $device['type'] }}</td>
                            <td class="px-6 py-4 text-slate-600 dark:text-slate-400">{{ $device['brand'] }} {{ $device['model'] }}</td>
                            <td class="px-6 py-4 font-mono text-xs text-slate-900 dark:text-slate-100">{{ $device['serial'] }}</td>
                            <td class="px-6 py-4 text-slate-600 dark:text-slate-400">{{ $device['wifi_ssid'] }}</td>
                            <td class="px-6 py-4">
                                <span class="px-2 py-1 text-xs font-semibold rounded-lg
                                    @if($device['status'] === 'active') bg-emerald-100 dark:bg-emerald-900/50 text-emerald-700
                                    @else bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400
                                    @endif">
                                    {{ ucfirst($device['status']) }}
                                </span>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @else
            <div class="p-10 text-center text-slate-400">
                <span class="material-symbols-outlined notranslate" translate="no" style="font-size:40px">router</span>
                <p class="mt-2 text-sm">Belum ada perangkat terdaftar</p>
            </div>
            @endif
        </x-base.card>
        @endif

        {{-- ============ INSTALLATION TAB ============ --}}
        @if($activeTab === 'installation')
        <x-base.card :padding="false">
            <x-slot name="header">
                <h3 class="text-base font-bold text-slate-900 dark:text-slate-100 flex items-center gap-2">
                    <span class="material-symbols-outlined notranslate text-indigo-500" translate="no" style="font-size:20px">engineering</span>
                    Riwayat Instalasi
                </h3>
            </x-slot>
            @if($installations->isNotEmpty())
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="bg-slate-50 dark:bg-slate-900/50 border-b border-slate-200 dark:border-slate-700 text-xs uppercase tracking-wider text-slate-500 dark:text-slate-400">
                            <th class="px-6 py-3 text-left font-semibold">ID</th>
                            <th class="px-6 py-3 text-left font-semibold">Tanggal</th>
                            <th class="px-6 py-3 text-left font-semibold">Teknisi</th>
                            <th class="px-6 py-3 text-left font-semibold">Status</th>
                            <th class="px-6 py-3 text-left font-semibold">Catatan</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-700">
                        @foreach($installations as $installation)
                        <tr class="hover:bg-slate-50 dark:bg-slate-900/50 dark:hover:bg-slate-800/50">
                            <td class="px-6 py-4 font-mono text-xs text-slate-900 dark:text-slate-100">{{ $installation->uuid ?? $installation->id }}</td>
                            <td class="px-6 py-4 text-slate-600 dark:text-slate-400">{{ $installation->completed_at?->format('d/m/Y') ?? $installation->scheduled_at?->format('d/m/Y') ?? '-' }}</td>
                            <td class="px-6 py-4 text-slate-600 dark:text-slate-400">{{ $installation->assignedTo?->name ?? '-' }}</td>
                            <td class="px-6 py-4">
                                <span class="px-2 py-1 text-xs font-semibold rounded-lg
                                    @if($installation->status === 'completed') bg-emerald-100 dark:bg-emerald-900/50 text-emerald-700
                                    @elseif($installation->status === 'in_progress') bg-blue-100 dark:bg-blue-900/50 text-blue-600
                                    @else bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400
                                    @endif">
                                    {{ ucfirst($installation->status) }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-slate-600 dark:text-slate-400">{{ $installation->notes ?? '-' }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @else
            <div class="p-10 text-center text-slate-400">
                <span class="material-symbols-outlined notranslate" translate="no" style="font-size:40px">engineering</span>
                <p class="mt-2 text-sm">Belum ada riwayat instalasi</p>
            </div>
            @endif
        </x-base.card>
        @endif

        {{-- ============ GIS TAB ============ --}}
        @if($activeTab === 'gis')
        <x-base.card>
            <x-slot name="header">
                <h3 class="text-base font-bold text-slate-900 dark:text-slate-100 flex items-center gap-2">
                    <span class="material-symbols-outlined notranslate text-indigo-500" translate="no" style="font-size:20px">map</span>
                    Lokasi Pelanggan
                </h3>
            </x-slot>
            @if($customer->latitude && $customer->longitude)
            <div class="space-y-3">
                <div class="flex gap-4 text-sm">
                    <div class="flex items-center gap-2 text-slate-600 dark:text-slate-400">
                        <span class="material-symbols-outlined notranslate text-indigo-500" translate="no" style="font-size:18px">location_on</span>
                        Lat: <span class="font-mono font-semibold text-slate-900 dark:text-slate-100">{{ $customer->latitude }}</span>
                        &nbsp;|&nbsp; Lng: <span class="font-mono font-semibold text-slate-900 dark:text-slate-100">{{ $customer->longitude }}</span>
                    </div>
                    <a href="https://maps.google.com/?q={{ $customer->latitude }},{{ $customer->longitude }}" target="_blank" rel="noopener noreferrer"
                       class="inline-flex items-center gap-1 text-indigo-600 hover:text-indigo-800 text-sm font-medium">
                        <span class="material-symbols-outlined notranslate" translate="no" style="font-size:16px">open_in_new</span>
                        Buka di Google Maps
                    </a>
                </div>
                <div class="h-72 bg-slate-100 dark:bg-slate-900 rounded-xl flex items-center justify-center border border-slate-200 dark:border-slate-700">
                    <iframe class="w-full h-full rounded-xl"
                        src="https://maps.google.com/maps?q={{ $customer->latitude }},{{ $customer->longitude }}&z=16&output=embed"
                        frameborder="0" allowfullscreen loading="lazy"></iframe>
                </div>
            </div>
            @else
            <div class="h-48 bg-slate-100 dark:bg-slate-900 rounded-xl flex items-center justify-center">
                <div class="text-center text-slate-400">
                    <span class="material-symbols-outlined notranslate" translate="no" style="font-size:48px">location_off</span>
                    <p class="mt-2 text-sm font-semibold text-slate-500 dark:text-slate-400">Koordinat lokasi belum diisi</p>
                    <p class="text-xs mt-1">Silahkan edit profil dan isi data latitude/longitude</p>
                </div>
            </div>
            @endif
        </x-base.card>
        @endif

        {{-- ============ MONITORING TAB ============ --}}
        @if($activeTab === 'monitoring')
        @if(!empty($monitoring))
        <div class="space-y-4">
            @foreach($monitoring as $mon)
            <x-base.card>
                <x-slot name="header">
                    <h3 class="text-base font-bold text-slate-900 dark:text-slate-100 flex items-center gap-2">
                        <span class="material-symbols-outlined notranslate text-indigo-500" translate="no" style="font-size:20px">monitor_heart</span>
                        Status Koneksi: {{ $mon['service'] }}
                    </h3>
                </x-slot>
                <div class="grid grid-cols-2 md:grid-cols-3 gap-4">
                    <div class="p-3 bg-slate-50 dark:bg-slate-900/50 rounded-lg">
                        <label class="block text-xs text-slate-500 dark:text-slate-400 mb-1">Status Layanan</label>
                        <span class="inline-flex items-center gap-1.5 text-sm font-bold {{ $mon['status'] === 'active' ? 'text-emerald-600' : 'text-red-600' }}">
                            <span class="w-2 h-2 rounded-full {{ $mon['status'] === 'active' ? 'bg-emerald-500 animate-pulse' : 'bg-red-500' }}"></span>
                            {{ ucfirst($mon['status']) }}
                        </span>
                    </div>
                    <div class="p-3 bg-slate-50 dark:bg-slate-900/50 rounded-lg">
                        <label class="block text-xs text-slate-500 dark:text-slate-400 mb-1">IP Address</label>
                        <span class="font-mono font-semibold text-slate-900 dark:text-slate-100">{{ $mon['ip'] ?? 'Dynamic' }}</span>
                    </div>
                    <div class="p-3 bg-slate-50 dark:bg-slate-900/50 rounded-lg">
                        <label class="block text-xs text-slate-500 dark:text-slate-400 mb-1">MAC Address</label>
                        <span class="font-mono text-sm font-semibold text-slate-900 dark:text-slate-100">{{ $mon['mac'] ?? 'N/A' }}</span>
                    </div>
                    <div class="p-3 bg-slate-50 dark:bg-slate-900/50 rounded-lg">
                        <label class="block text-xs text-slate-500 dark:text-slate-400 mb-1">ONU Status</label>
                        <span class="font-semibold text-slate-900 dark:text-slate-100">{{ $mon['onu_status'] ?? 'N/A' }}</span>
                    </div>
                    <div class="p-3 bg-slate-50 dark:bg-slate-900/50 rounded-lg">
                        <label class="block text-xs text-slate-500 dark:text-slate-400 mb-1">RX Power (ONU)</label>
                        <span class="font-mono font-semibold text-slate-900 dark:text-slate-100">{{ $mon['onu_rx'] !== 'N/A' ? $mon['onu_rx'] . ' dBm' : 'N/A' }}</span>
                    </div>
                    <div class="p-3 bg-slate-50 dark:bg-slate-900/50 rounded-lg">
                        <label class="block text-xs text-slate-500 dark:text-slate-400 mb-1">TX Power (ONU)</label>
                        <span class="font-mono font-semibold text-slate-900 dark:text-slate-100">{{ $mon['onu_tx'] !== 'N/A' ? $mon['onu_tx'] . ' dBm' : 'N/A' }}</span>
                    </div>
                    <div class="p-3 bg-slate-50 dark:bg-slate-900/50 rounded-lg col-span-2 md:col-span-1">
                        <label class="block text-xs text-slate-500 dark:text-slate-400 mb-1">Terakhir Terlihat</label>
                        <span class="text-sm font-semibold text-slate-900 dark:text-slate-100">
                            {{ $mon['last_seen'] !== 'Never' ? (is_string($mon['last_seen']) ? $mon['last_seen'] : $mon['last_seen']->diffForHumans()) : 'Belum pernah' }}
                        </span>
                    </div>
                </div>
            </x-base.card>
            @endforeach
        </div>
        @else
        <x-base.card>
            <div class="p-10 text-center text-slate-400">
                <span class="material-symbols-outlined notranslate" translate="no" style="font-size:48px">signal_disconnected</span>
                <p class="mt-2 text-sm font-semibold text-slate-500 dark:text-slate-400">Tidak ada data monitoring</p>
                <p class="text-xs mt-1">Pelanggan ini belum memiliki perangkat yang terhubung</p>
            </div>
        </x-base.card>
        @endif
        @endif

        {{-- ============ HISTORY TAB ============ --}}
        @if($activeTab === 'history')
        <x-base.card>
            <x-slot name="header">
                <h3 class="text-base font-bold text-slate-900 dark:text-slate-100 flex items-center gap-2">
                    <span class="material-symbols-outlined notranslate text-indigo-500" translate="no" style="font-size:20px">history</span>
                    Timeline Pelanggan
                </h3>
            </x-slot>
            @if(!empty($timeline))
            <div class="space-y-6">
                @foreach($timeline as $item)
                <div class="flex gap-4">
                    <div class="flex flex-col items-center">
                        <div class="w-3 h-3 rounded-full mt-1 flex-shrink-0
                            @if($item['type'] === 'create') bg-indigo-500
                            @elseif($item['type'] === 'survey') bg-sky-500
                            @elseif($item['type'] === 'contract') bg-emerald-500
                            @elseif($item['type'] === 'installation') bg-orange-500
                            @elseif($item['type'] === 'activation') bg-purple-500
                            @else bg-slate-400
                            @endif">
                        </div>
                        @if(!$loop->last)
                        <div class="w-0.5 flex-1 bg-slate-200 dark:bg-slate-700 mt-1"></div>
                        @endif
                    </div>
                    <div class="flex-1 pb-6">
                        <p class="text-xs text-slate-500 dark:text-slate-400 mb-1">{{ $item['date']->format('d/m/Y H:i') }}</p>
                        <p class="font-semibold text-slate-900 dark:text-slate-100">{{ $item['title'] }}</p>
                        <p class="text-sm text-slate-600 dark:text-slate-400 mt-1">{{ $item['description'] }}</p>
                    </div>
                </div>
                @endforeach
            </div>
            @else
            <div class="text-center py-8 text-slate-400">
                <span class="material-symbols-outlined notranslate" translate="no" style="font-size:40px">history</span>
                <p class="mt-2 text-sm">Belum ada riwayat</p>
            </div>
            @endif
        </x-base.card>
        @endif

        {{-- ============ ACTIVITY TAB ============ --}}
        @if($activeTab === 'activity')
        <x-base.card>
            <x-slot name="header">
                <h3 class="text-base font-bold text-slate-900 dark:text-slate-100 flex items-center gap-2">
                    <span class="material-symbols-outlined notranslate text-indigo-500" translate="no" style="font-size:20px">timeline</span>
                    Aktivitas Terbaru
                </h3>
            </x-slot>
            @if(!empty($activities))
            <div class="space-y-3">
                @foreach($activities as $activity)
                <div class="flex items-start gap-3 p-3 hover:bg-slate-50 dark:bg-slate-900/50 dark:hover:bg-slate-800/50 rounded-xl transition-colors">
                    <div class="w-9 h-9 rounded-full bg-indigo-100 dark:bg-indigo-900/50 flex items-center justify-center flex-shrink-0">
                        <span class="text-sm font-bold text-indigo-600 dark:text-indigo-400">{{ substr($activity['user'], 0, 1) }}</span>
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm text-slate-900 dark:text-slate-100">
                            <span class="font-semibold">{{ $activity['user'] }}</span> {{ $activity['action'] }}
                        </p>
                        <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5 flex items-center gap-2">
                            <span class="px-2 py-0.5 bg-indigo-100 dark:bg-indigo-900/50 text-indigo-600 dark:text-indigo-400 rounded-full text-xs">{{ $activity['module'] }}</span>
                            {{ $activity['time'] }}
                        </p>
                    </div>
                </div>
                @endforeach
            </div>
            @else
            <div class="text-center py-8 text-slate-400">
                <span class="material-symbols-outlined notranslate" translate="no" style="font-size:40px">timeline</span>
                <p class="mt-2 text-sm">Belum ada aktivitas tercatat</p>
            </div>
            @endif
        </x-base.card>
        @endif

        {{-- ============ NOTIFICATION TAB ============ --}}
        @if($activeTab === 'notification')
        <x-base.card>
            <x-slot name="header">
                <h3 class="text-base font-bold text-slate-900 dark:text-slate-100 flex items-center gap-2">
                    <span class="material-symbols-outlined notranslate text-indigo-500" translate="no" style="font-size:20px">notifications</span>
                    Notifikasi
                </h3>
            </x-slot>
            @if(!empty($notifications))
            <div class="space-y-3">
                @foreach($notifications as $notification)
                <div class="flex items-start gap-3 p-4 {{ !$notification['read'] ? 'bg-indigo-50 dark:bg-indigo-900/20 border border-indigo-100 dark:border-indigo-800/50' : 'bg-slate-50 dark:bg-slate-800/50' }} rounded-xl">
                    <div class="w-9 h-9 rounded-full flex items-center justify-center flex-shrink-0
                        {{ !$notification['read'] ? 'bg-indigo-100 dark:bg-indigo-900/50' : 'bg-slate-200 dark:bg-slate-700' }}">
                        <span class="material-symbols-outlined notranslate {{ !$notification['read'] ? 'text-indigo-600' : 'text-slate-500 dark:text-slate-400' }}" translate="no" style="font-size:18px">notifications</span>
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-semibold text-slate-900 dark:text-slate-100">{{ $notification['title'] }}</p>
                        <p class="text-sm text-slate-600 dark:text-slate-400 mt-1">{{ $notification['message'] }}</p>
                        <p class="text-xs text-slate-400 mt-1">{{ $notification['created_at']->diffForHumans() }}</p>
                    </div>
                    @if(!$notification['read'])
                    <span class="w-2.5 h-2.5 rounded-full bg-indigo-500 flex-shrink-0 mt-1"></span>
                    @endif
                </div>
                @endforeach
            </div>
            @else
            <div class="text-center py-10 text-slate-400">
                <span class="material-symbols-outlined notranslate" translate="no" style="font-size:48px">notifications_off</span>
                <p class="mt-2 text-sm font-semibold text-slate-500 dark:text-slate-400">Tidak ada notifikasi</p>
                <p class="text-xs mt-1">Notifikasi akan muncul di sini saat ada pembaruan</p>
            </div>
            @endif
        </x-base.card>
        @endif

    </div>

    {{-- EDIT MODAL --}}
    @if($showEditModal)
    <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm" wire:click.self="$set('showEditModal', false)">
        <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-2xl w-full max-w-lg" @click.stop>
            <div class="px-6 py-4 border-b border-slate-200 dark:border-slate-700 flex items-center justify-between">
                <h3 class="text-lg font-bold text-slate-900 dark:text-slate-100 flex items-center gap-2">
                    <span class="material-symbols-outlined notranslate text-indigo-500" translate="no" style="font-size:20px">edit</span>
                    Edit Profil Pelanggan
                </h3>
                <button wire:click="$set('showEditModal', false)" class="text-slate-400 hover:text-slate-600 dark:text-slate-400 dark:hover:text-slate-200 transition-colors">
                    <span class="material-symbols-outlined notranslate" translate="no" style="font-size:22px">close</span>
                </button>
            </div>
            <div class="px-6 py-4 space-y-4 max-h-[70vh] overflow-y-auto">
                <div>
                    <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Nama <span class="text-red-500">*</span></label>
                    <input wire:model="edit_name" type="text" class="w-full px-4 py-2.5 border border-slate-200 dark:border-slate-600 rounded-xl text-sm focus:ring-2 focus:ring-indigo-500 dark:bg-slate-900 dark:text-slate-100 dark:bg-slate-900 dark:text-slate-100" placeholder="Nama lengkap">
                    @error('edit_name') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Telepon / WhatsApp</label>
                        <input wire:model="edit_phone" type="text" class="w-full px-4 py-2.5 border border-slate-200 dark:border-slate-600 rounded-xl text-sm focus:ring-2 focus:ring-indigo-500 dark:bg-slate-900 dark:text-slate-100 dark:bg-slate-900 dark:text-slate-100" placeholder="08xx...">
                        @error('edit_phone') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Email</label>
                        <input wire:model="edit_email" type="email" class="w-full px-4 py-2.5 border border-slate-200 dark:border-slate-600 rounded-xl text-sm focus:ring-2 focus:ring-indigo-500 dark:bg-slate-900 dark:text-slate-100 dark:bg-slate-900 dark:text-slate-100" placeholder="email@domain.com">
                        @error('edit_email') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Status Akun</label>
                    <select wire:model="edit_status" class="w-full px-4 py-2.5 border border-slate-200 dark:border-slate-600 rounded-xl text-sm focus:ring-2 focus:ring-indigo-500 dark:bg-slate-900 dark:text-slate-100 dark:bg-slate-900 dark:text-slate-100">
                        <option value="active">Aktif</option>
                        <option value="inactive">Nonaktif (Suspend)</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Alamat</label>
                    <textarea wire:model="edit_address" rows="3" class="w-full px-4 py-2.5 border border-slate-200 dark:border-slate-600 rounded-xl text-sm focus:ring-2 focus:ring-indigo-500 dark:bg-slate-900 dark:text-slate-100 resize-none dark:bg-slate-900 dark:text-slate-100" placeholder="Alamat lengkap..."></textarea>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Latitude</label>
                        <input wire:model="edit_latitude" type="text" class="w-full px-4 py-2.5 border border-slate-200 dark:border-slate-600 rounded-xl text-sm focus:ring-2 focus:ring-indigo-500 dark:bg-slate-900 dark:text-slate-100 font-mono dark:bg-slate-900 dark:text-slate-100" placeholder="-7.xxx">
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Longitude</label>
                        <input wire:model="edit_longitude" type="text" class="w-full px-4 py-2.5 border border-slate-200 dark:border-slate-600 rounded-xl text-sm focus:ring-2 focus:ring-indigo-500 dark:bg-slate-900 dark:text-slate-100 font-mono dark:bg-slate-900 dark:text-slate-100" placeholder="110.xxx">
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Reseller / Owner</label>
                        <select wire:model="edit_reseller_id" class="w-full px-4 py-2.5 border border-slate-200 dark:border-slate-600 rounded-xl text-sm focus:ring-2 focus:ring-indigo-500 dark:bg-slate-900 dark:text-slate-100 dark:bg-slate-900 dark:text-slate-100">
                            <option value="">-- Pilih Reseller --</option>
                            @foreach(\App\Models\User::all() as $user)
                                <option value="{{ $user->id }}">{{ $user->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Cabang (Branch)</label>
                        <select wire:model="edit_branch_id" class="w-full px-4 py-2.5 border border-slate-200 dark:border-slate-600 rounded-xl text-sm focus:ring-2 focus:ring-indigo-500 dark:bg-slate-900 dark:text-slate-100 dark:bg-slate-900 dark:text-slate-100">
                            <option value="">-- Pilih Cabang --</option>
                            @foreach(\App\Models\Master\Branch::all() as $branch)
                                <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Catatan</label>
                    <textarea wire:model="edit_notes" rows="2" class="w-full px-4 py-2.5 border border-slate-200 dark:border-slate-600 rounded-xl text-sm focus:ring-2 focus:ring-indigo-500 dark:bg-slate-900 dark:text-slate-100 resize-none dark:bg-slate-900 dark:text-slate-100" placeholder="Catatan tambahan..."></textarea>
                </div>
            </div>
            <div class="px-6 py-4 border-t border-slate-200 dark:border-slate-700 flex items-center justify-end gap-3">
                <button wire:click="$set('showEditModal', false)" class="px-4 py-2 rounded-xl border border-slate-200 dark:border-slate-700 text-slate-700 dark:text-slate-300 text-sm font-semibold hover:bg-slate-50 dark:bg-slate-900/50 dark:hover:bg-slate-700 transition-colors cursor-pointer">
                    Batal
                </button>
                <button wire:click="updateCustomer" class="px-4 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-semibold shadow transition-colors cursor-pointer">
                    Simpan Perubahan
                </button>
            </div>
        </div>
    </div>
    @endif

    {{-- EDIT SERVICE MODAL --}}
    @if($showEditServiceModal)
    <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm" wire:click.self="$set('showEditServiceModal', false)">
        <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-2xl w-full max-w-lg" @click.stop>
            <div class="px-6 py-4 border-b border-slate-200 dark:border-slate-700 flex items-center justify-between">
                <h3 class="text-lg font-bold text-slate-900 dark:text-slate-100 flex items-center gap-2">
                    <span class="material-symbols-outlined notranslate text-indigo-500" translate="no" style="font-size:20px">wifi_protected_setup</span>
                    Ganti Layanan / Paket
                </h3>
                <button wire:click="$set('showEditServiceModal', false)" class="text-slate-400 hover:text-slate-600 dark:text-slate-400 dark:hover:text-slate-200 transition-colors">
                    <span class="material-symbols-outlined notranslate" translate="no" style="font-size:22px">close</span>
                </button>
            </div>
            <div class="px-6 py-4 space-y-4 max-h-[70vh] overflow-y-auto">
                <div>
                    <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Jenis Layanan</label>
                    <div class="grid grid-cols-2 gap-3">
                        <label class="cursor-pointer">
                            <input type="radio" wire:model.live="edit_service_type" value="pppoe" class="peer sr-only dark:bg-slate-900 dark:text-slate-100">
                            <div class="px-4 py-3 rounded-xl border border-slate-200 dark:border-slate-700 text-center peer-checked:bg-indigo-50 dark:bg-indigo-900/30 peer-checked:border-indigo-500 peer-checked:text-indigo-700 dark:peer-checked:bg-indigo-900/30 dark:peer-checked:text-indigo-400 transition-all">
                                <span class="block font-bold">PPPoE</span>
                                <span class="text-xs text-slate-500 dark:text-slate-400">Rumahan / Broadband</span>
                            </div>
                        </label>
                        <label class="cursor-pointer">
                            <input type="radio" wire:model.live="edit_service_type" value="hotspot" class="peer sr-only dark:bg-slate-900 dark:text-slate-100">
                            <div class="px-4 py-3 rounded-xl border border-slate-200 dark:border-slate-700 text-center peer-checked:bg-indigo-50 dark:bg-indigo-900/30 peer-checked:border-indigo-500 peer-checked:text-indigo-700 dark:peer-checked:bg-indigo-900/30 dark:peer-checked:text-indigo-400 transition-all">
                                <span class="block font-bold">Hotspot</span>
                                <span class="text-xs text-slate-500 dark:text-slate-400">Voucher / Warkop</span>
                            </div>
                        </label>
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Profil Paket</label>
                    <select wire:model="edit_service_profile_id" class="w-full px-4 py-2.5 border border-slate-200 dark:border-slate-600 rounded-xl text-sm focus:ring-2 focus:ring-indigo-500 dark:bg-slate-900 dark:text-slate-100 dark:bg-slate-900 dark:text-slate-100">
                        <option value="">-- Pilih Paket Baru --</option>
                        @foreach(\App\Models\ISP\ServiceProfile::where('service_type', $edit_service_type)->get() as $profile)
                            <option value="{{ $profile->id }}">{{ $profile->name }} (Rp {{ number_format($profile->price, 0, ',', '.') }})</option>
                        @endforeach
                    </select>
                    @error('edit_service_profile_id') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Metode Autentikasi</label>
                    <select wire:model.live="edit_service_password_mode" class="w-full px-4 py-2.5 border border-slate-200 dark:border-slate-600 rounded-xl text-sm focus:ring-2 focus:ring-indigo-500 dark:bg-slate-900 dark:text-slate-100 mb-3 dark:bg-slate-900 dark:text-slate-100">
                        <option value="custom">Username & Password Berbeda</option>
                        <option value="same">Username = Password</option>
                    </select>
                </div>
                <div class="grid {{ $edit_service_password_mode === 'same' ? 'grid-cols-1' : 'grid-cols-2' }} gap-4" x-data="{ copyToClipboard(text) { navigator.clipboard.writeText(text); alert('Tersalin ke clipboard!'); } }">
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Username</label>
                        <div class="relative">
                            <input wire:model.live="edit_service_username" x-ref="uname" type="text" class="w-full pl-4 pr-10 py-2.5 border border-slate-200 dark:border-slate-600 rounded-xl text-sm focus:ring-2 focus:ring-indigo-500 dark:bg-slate-900 dark:text-slate-100 dark:bg-slate-900 dark:text-slate-100" placeholder="Username">
                            <button @click="copyToClipboard($refs.uname.value)" type="button" class="absolute right-2 top-1.5 p-1.5 text-slate-400 hover:text-indigo-600 transition-colors" title="Copy Username">
                                <span class="material-symbols-outlined notranslate" translate="no" style="font-size:18px">content_copy</span>
                            </button>
                        </div>
                        @error('edit_service_username') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                    </div>
                    
                    @if($edit_service_password_mode !== 'same')
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Password</label>
                        <div class="relative">
                            <input wire:model="edit_service_password" x-ref="pwd" type="text" class="w-full pl-4 pr-10 py-2.5 border border-slate-200 dark:border-slate-600 rounded-xl text-sm focus:ring-2 focus:ring-indigo-500 dark:bg-slate-900 dark:text-slate-100 dark:bg-slate-900 dark:text-slate-100" placeholder="Password">
                            <button @click="copyToClipboard($refs.pwd.value)" type="button" class="absolute right-2 top-1.5 p-1.5 text-slate-400 hover:text-indigo-600 transition-colors" title="Copy Password">
                                <span class="material-symbols-outlined notranslate" translate="no" style="font-size:18px">content_copy</span>
                            </button>
                        </div>
                        @error('edit_service_password') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                    </div>
                    @endif
                </div>
                <div class="p-3 bg-amber-50 dark:bg-amber-900/30 rounded-xl border border-amber-200 dark:border-amber-800 text-sm text-amber-700 dark:text-amber-400">
                    <strong>Penting:</strong> Jika Anda mengubah jenis layanan (misal PPPoE ke Hotspot), akun lama di RADIUS akan otomatis dihapus dan diganti dengan yang baru.
                </div>
            </div>
            <div class="px-6 py-4 border-t border-slate-200 dark:border-slate-700 flex items-center justify-end gap-3">
                <button wire:click="$set('showEditServiceModal', false)" class="px-4 py-2 rounded-xl border border-slate-200 dark:border-slate-700 text-slate-700 dark:text-slate-300 text-sm font-semibold hover:bg-slate-50 dark:bg-slate-900/50 dark:hover:bg-slate-700 transition-colors cursor-pointer">
                    Batal
                </button>
                <button wire:click="updateService" class="px-4 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-semibold shadow transition-colors cursor-pointer">
                    Simpan Layanan
                </button>
            </div>
        </div>
    </div>
    @endif

</div>
