<div class="space-y-6">
    {{-- Header --}}
    <div class="flex items-center gap-4">
        <a href="{{ route('crm.customers.index') }}" class="p-2 text-slate-500 hover:text-slate-700 rounded-lg hover:bg-slate-100">
            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
            </svg>
        </a>
        <div class="flex-1">
            <div class="flex items-center gap-3">
                <div class="w-14 h-14 rounded-full bg-primary-100 flex items-center justify-center text-primary-600 text-2xl font-bold">
                    {{ substr($customer->name ?? 'U', 0, 1) }}
                </div>
                <div>
                    <h1 class="text-2xl font-bold text-slate-900">{{ $customer->name }}</h1>
                    <div class="flex items-center gap-2 mt-1">
                        <span class="px-3 py-1 text-xs font-medium rounded-full 
                            @if($customer->status === 'active') bg-green-100 text-green-600
                            @elseif($customer->status === 'inactive') bg-slate-100 text-slate-600
                            @else bg-red-100 text-red-600
                            @endif
                        ">
                            {{ ucfirst($customer->status) }}
                        </span>
                        <span class="text-sm text-slate-500">{{ $customer->email ?? '-' }}</span>
                    </div>
                </div>
            </div>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('crm.customers.edit', $customer->id) }}" class="px-4 py-2 bg-white border border-slate-200 text-slate-700 rounded-lg text-sm font-medium hover:bg-slate-50 transition-colors">
                Edit
            </a>
        </div>
    </div>

    {{-- Customer Dashboard Cards --}}
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
        <x-base.card>
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-slate-500">Paket</p>
                    <p class="text-2xl font-bold text-slate-900 mt-1">
                        {{ $customer->customerServices->first()?->serviceProfile?->name ?? $customer->customerServices->first()?->service?->name ?? 'Belum ada paket' }}
                    </p>
                    @if($customer->customerServices->first()?->serviceProfile)
                        <p class="text-sm text-slate-500 mt-1">
                            {{ $customer->customerServices->first()->serviceProfile->download_speed }} Mbps / {{ $customer->customerServices->first()->serviceProfile->upload_speed }} Mbps
                        </p>
                    @endif
                </div>
                <div class="w-10 h-10 rounded-lg bg-blue-100 flex items-center justify-center">
                    <svg class="w-5 h-5 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                    </svg>
                </div>
            </div>
        </x-base.card>
        <x-base.card>
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-slate-500">Tagihan Belum Dibayar</p>
                    <p class="text-2xl font-bold text-slate-900 mt-1">
                        Rp {{ number_format($customer->invoices->where('status', '!=', 'paid')->sum('total_amount'), 0, ',', '.') }}
                    </p>
                </div>
                <div class="w-10 h-10 rounded-lg bg-orange-100 flex items-center justify-center">
                    <svg class="w-5 h-5 text-orange-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
            </div>
        </x-base.card>
        <x-base.card>
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-slate-500">Status Layanan</p>
                    <p class="text-2xl font-bold text-slate-900 mt-1">
                        {{ ucfirst($customer->customerServices->first()?->status ?? 'inactive') }}
                    </p>
                </div>
                <div class="w-10 h-10 rounded-lg bg-green-100 flex items-center justify-center">
                    <svg class="w-5 h-5 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 12h.01M12 12h.01M19 12h.01M6 12a1 1 0 11-2 0 1 1 0 012 0zm7 0a1 1 0 11-2 0 1 1 0 012 0zm7 0a1 1 0 11-2 0 1 1 0 012 0z"/>
                    </svg>
                </div>
            </div>
        </x-base.card>
        <x-base.card>
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-slate-500">Jumlah Layanan</p>
                    <p class="text-2xl font-bold text-slate-900 mt-1">{{ $customer->customerServices->count() }}</p>
                </div>
                <div class="w-10 h-10 rounded-lg bg-blue-100 flex items-center justify-center">
                    <svg class="w-5 h-5 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 100 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 100-4V7a2 2 0 00-2-2H5z"/>
                    </svg>
                </div>
            </div>
        </x-base.card>
    </div>

    {{-- Tabs Navigation --}}
    <div class="border-b border-slate-200">
        <nav class="flex gap-1 overflow-x-auto" aria-label="Tabs">
            <button wire:click="setActiveTab('profile')" class="px-4 py-3 text-sm font-medium border-b-2 transition-colors whitespace-nowrap
                @if($activeTab === 'profile') border-primary-500 text-primary-600
                @else border-transparent text-slate-500 hover:text-slate-700 hover:border-slate-300
                @endif">
                Profile
            </button>
            <button wire:click="setActiveTab('contract')" class="px-4 py-3 text-sm font-medium border-b-2 transition-colors whitespace-nowrap
                @if($activeTab === 'contract') border-primary-500 text-primary-600
                @else border-transparent text-slate-500 hover:text-slate-700 hover:border-slate-300
                @endif">
                Contract
            </button>
            <button wire:click="setActiveTab('billing')" class="px-4 py-3 text-sm font-medium border-b-2 transition-colors whitespace-nowrap
                @if($activeTab === 'billing') border-primary-500 text-primary-600
                @else border-transparent text-slate-500 hover:text-slate-700 hover:border-slate-300
                @endif">
                Billing
            </button>
            <button wire:click="setActiveTab('invoice')" class="px-4 py-3 text-sm font-medium border-b-2 transition-colors whitespace-nowrap
                @if($activeTab === 'invoice') border-primary-500 text-primary-600
                @else border-transparent text-slate-500 hover:text-slate-700 hover:border-slate-300
                @endif">
                Invoice
            </button>
            <button wire:click="setActiveTab('payment')" class="px-4 py-3 text-sm font-medium border-b-2 transition-colors whitespace-nowrap
                @if($activeTab === 'payment') border-primary-500 text-primary-600
                @else border-transparent text-slate-500 hover:text-slate-700 hover:border-slate-300
                @endif">
                Payment
            </button>
            <button wire:click="setActiveTab('ticket')" class="px-4 py-3 text-sm font-medium border-b-2 transition-colors whitespace-nowrap
                @if($activeTab === 'ticket') border-primary-500 text-primary-600
                @else border-transparent text-slate-500 hover:text-slate-700 hover:border-slate-300
                @endif">
                Ticket
            </button>
            <button wire:click="setActiveTab('device')" class="px-4 py-3 text-sm font-medium border-b-2 transition-colors whitespace-nowrap
                @if($activeTab === 'device') border-primary-500 text-primary-600
                @else border-transparent text-slate-500 hover:text-slate-700 hover:border-slate-300
                @endif">
                Device
            </button>
            <button wire:click="setActiveTab('installation')" class="px-4 py-3 text-sm font-medium border-b-2 transition-colors whitespace-nowrap
                @if($activeTab === 'installation') border-primary-500 text-primary-600
                @else border-transparent text-slate-500 hover:text-slate-700 hover:border-slate-300
                @endif">
                Installation
            </button>
            <button wire:click="setActiveTab('gis')" class="px-4 py-3 text-sm font-medium border-b-2 transition-colors whitespace-nowrap
                @if($activeTab === 'gis') border-primary-500 text-primary-600
                @else border-transparent text-slate-500 hover:text-slate-700 hover:border-slate-300
                @endif">
                GIS
            </button>
            <button wire:click="setActiveTab('monitoring')" class="px-4 py-3 text-sm font-medium border-b-2 transition-colors whitespace-nowrap
                @if($activeTab === 'monitoring') border-primary-500 text-primary-600
                @else border-transparent text-slate-500 hover:text-slate-700 hover:border-slate-300
                @endif">
                Monitoring
            </button>
            <button wire:click="setActiveTab('history')" class="px-4 py-3 text-sm font-medium border-b-2 transition-colors whitespace-nowrap
                @if($activeTab === 'history') border-primary-500 text-primary-600
                @else border-transparent text-slate-500 hover:text-slate-700 hover:border-slate-300
                @endif">
                History
            </button>
            <button wire:click="setActiveTab('activity')" class="px-4 py-3 text-sm font-medium border-b-2 transition-colors whitespace-nowrap
                @if($activeTab === 'activity') border-primary-500 text-primary-600
                @else border-transparent text-slate-500 hover:text-slate-700 hover:border-slate-300
                @endif">
                Activity
            </button>
            <button wire:click="setActiveTab('notification')" class="px-4 py-3 text-sm font-medium border-b-2 transition-colors whitespace-nowrap
                @if($activeTab === 'notification') border-primary-500 text-primary-600
                @else border-transparent text-slate-500 hover:text-slate-700 hover:border-slate-300
                @endif">
                Notification
            </button>
        </nav>
    </div>

    {{-- Tab Content --}}
    <div class="mt-6">
        {{-- Profile Tab --}}
        @if($activeTab === 'profile')
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <x-base.card>
                    <x-slot name="header">
                        <h3 class="text-lg font-semibold text-slate-900">Informasi Pribadi</h3>
                    </x-slot>
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-slate-500 mb-1">Nama Lengkap</label>
                            <p class="text-slate-900">{{ $customer->name }}</p>
                        </div>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-slate-500 mb-1">Email</label>
                                <p class="text-slate-900">{{ $customer->email ?? '-' }}</p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-slate-500 mb-1">Telepon</label>
                                <p class="text-slate-900">{{ $customer->phone ?? '-' }}</p>
                            </div>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-500 mb-1">Alamat</label>
                            <p class="text-slate-900">{{ $customer->address ?? '-' }}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-500 mb-1">Customer ID</label>
                            <p class="text-slate-900 font-mono">{{ $customer->uuid ?? $customer->id }}</p>
                        </div>
                    </div>
                </x-base.card>

                <x-base.card>
                    <x-slot name="header">
                        <h3 class="text-lg font-semibold text-slate-900">Informasi Layanan</h3>
                    </x-slot>
                    <div class="space-y-4">
                        @foreach($customer->customerServices as $service)
                            <div class="border-b border-slate-100 pb-4 mb-4">
                                <div>
                                    <label class="block text-sm font-medium text-slate-500 mb-1">Paket Internet</label>
                                    <p class="text-slate-900 font-semibold">{{ $service->serviceProfile?->name ?? $service->service?->name ?? 'N/A' }}</p>
                                </div>
                                @if($service->serviceProfile)
                                    <div class="grid grid-cols-2 gap-4 mt-3">
                                        <div>
                                            <label class="block text-sm font-medium text-slate-500 mb-1">Download Speed</label>
                                            <p class="text-slate-900">{{ $service->serviceProfile->download_speed }} Mbps</p>
                                        </div>
                                        <div>
                                            <label class="block text-sm font-medium text-slate-500 mb-1">Upload Speed</label>
                                            <p class="text-slate-900">{{ $service->serviceProfile->upload_speed }} Mbps</p>
                                        </div>
                                        <div>
                                            <label class="block text-sm font-medium text-slate-500 mb-1">PPPoE Profile</label>
                                            <p class="text-slate-900">{{ $service->serviceProfile->pppoe_profile ?? 'N/A' }}</p>
                                        </div>
                                        <div>
                                            <label class="block text-sm font-medium text-slate-500 mb-1">Hotspot Profile</label>
                                            <p class="text-slate-900">{{ $service->serviceProfile->hotspot_profile ?? 'N/A' }}</p>
                                        </div>
                                        <div>
                                            <label class="block text-sm font-medium text-slate-500 mb-1">Harga Paket</label>
                                            <p class="text-slate-900">Rp {{ number_format($service->serviceProfile->price, 0, ',', '.') }}</p>
                                        </div>
                                        <div>
                                            <label class="block text-sm font-medium text-slate-500 mb-1">PPN ({{ $service->serviceProfile->tax_percentage }}%)</label>
                                            <p class="text-slate-900">Rp {{ number_format(($service->serviceProfile->price * $service->serviceProfile->tax_percentage) / 100, 0, ',', '.') }}</p>
                                        </div>
                                    </div>
                                @endif
                                <div class="mt-3">
                                    <label class="block text-sm font-medium text-slate-500 mb-1">Status Layanan</label>
                                    <span class="px-3 py-1 text-xs font-medium rounded-full
                                        @if($service->status === 'active') bg-green-100 text-green-600
                                        @elseif($service->status === 'suspended') bg-red-100 text-red-600
                                        @else bg-slate-100 text-slate-600
                                        @endif
                                    ">
                                        {{ ucfirst($service->status) }}
                                    </span>
                                </div>
                                @if($service->activated_at)
                                    <div class="mt-2">
                                        <label class="block text-sm font-medium text-slate-500 mb-1">Tanggal Aktivasi</label>
                                        <p class="text-slate-900">{{ $service->activated_at->format('d/m/Y') }}</p>
                                    </div>
                                @endif
                            </div>
                        @endforeach
                        @if($customer->customerServices->isEmpty())
                            <p class="text-slate-500">Belum ada layanan</p>
                        @endif
                    </div>
                </x-base.card>
            </div>
        @endif

        {{-- Contract Tab --}}
        @if($activeTab === 'contract')
            <x-base.card>
                <x-slot name="header">
                    <h3 class="text-lg font-semibold text-slate-900">Kontrak Layanan</h3>
                </x-slot>
                @if($customer->contracts->isNotEmpty())
                    <div class="overflow-x-auto">
                        <table class="w-full">
                            <thead>
                                <tr class="bg-slate-50">
                                    <th class="px-4 py-3 text-left text-xs font-semibold text-slate-600 uppercase">Nomor Kontrak</th>
                                    <th class="px-4 py-3 text-left text-xs font-semibold text-slate-600 uppercase">Tanggal Mulai</th>
                                    <th class="px-4 py-3 text-left text-xs font-semibold text-slate-600 uppercase">Tanggal Berakhir</th>
                                    <th class="px-4 py-3 text-left text-xs font-semibold text-slate-600 uppercase">Status</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-200">
                                @foreach($customer->contracts as $contract)
                                    <tr>
                                        <td class="px-4 py-4 text-sm font-medium text-slate-900">{{ $contract->contract_number }}</td>
                                        <td class="px-4 py-4 text-sm text-slate-600">{{ $contract->start_date?->format('d/m/Y') ?? '-' }}</td>
                                        <td class="px-4 py-4 text-sm text-slate-600">{{ $contract->end_date?->format('d/m/Y') ?? '-' }}</td>
                                        <td class="px-4 py-4">
                                            <span class="px-2 py-1 text-xs font-medium rounded-full
                                                @if($contract->status === 'active') bg-green-100 text-green-600
                                                @elseif($contract->status === 'expired') bg-red-100 text-red-600
                                                @else bg-slate-100 text-slate-600
                                                @endif
                                            ">
                                                {{ ucfirst($contract->status) }}
                                            </span>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <p class="text-slate-500">Belum ada kontrak</p>
                @endif
            </x-base.card>
        @endif

        {{-- Billing Tab --}}
        @if($activeTab === 'billing')
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <x-base.card class="lg:col-span-2">
                    <x-slot name="header">
                        <h3 class="text-lg font-semibold text-slate-900">Ringkasan Tagihan</h3>
                    </x-slot>
                    <div class="space-y-4">
                        <div class="flex justify-between items-center py-3 border-b border-slate-200">
                            <span class="text-slate-600">Tagihan Bulanan</span>
                            <span class="text-lg font-semibold text-slate-900">Rp 350.000</span>
                        </div>
                        <div class="flex justify-between items-center py-3 border-b border-slate-200">
                            <span class="text-slate-600">Total Tagihan Belum Dibayar</span>
                            <span class="text-lg font-semibold text-orange-600">Rp 350.000</span>
                        </div>
                        <div class="flex justify-between items-center py-3">
                            <span class="text-slate-600">Metode Pembayaran</span>
                            <span class="text-lg font-semibold text-slate-900">Transfer Bank</span>
                        </div>
                    </div>
                </x-base.card>
                <x-base.card>
                    <x-slot name="header">
                        <h3 class="text-lg font-semibold text-slate-900">Jatuh Tempo</h3>
                    </x-slot>
                    <div class="text-center py-4">
                        <p class="text-3xl font-bold text-orange-600">10</p>
                        <p class="text-sm text-slate-500 mt-1">Hari Lagi</p>
                        <p class="text-xs text-slate-400 mt-2">Tanggal {{ now()->addDays(10)->format('d/m/Y') }}</p>
                    </div>
                </x-base.card>
            </div>
        @endif

        {{-- Invoice Tab --}}
        @if($activeTab === 'invoice')
            <x-base.card :padding="false">
                <x-slot name="header">
                    <h3 class="text-lg font-semibold text-slate-900">Daftar Invoice</h3>
                </x-slot>
                @if($invoices->isNotEmpty())
                    <div class="overflow-x-auto">
                        <table class="w-full">
                            <thead>
                                <tr class="bg-slate-50">
                                    <th class="px-6 py-3 text-left text-xs font-semibold text-slate-600 uppercase">Nomor Invoice</th>
                                    <th class="px-6 py-3 text-left text-xs font-semibold text-slate-600 uppercase">Tanggal</th>
                                    <th class="px-6 py-3 text-left text-xs font-semibold text-slate-600 uppercase">Jatuh Tempo</th>
                                    <th class="px-6 py-3 text-left text-xs font-semibold text-slate-600 uppercase">Jumlah</th>
                                    <th class="px-6 py-3 text-left text-xs font-semibold text-slate-600 uppercase">Status</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-200">
                                @foreach($invoices as $invoice)
                                    <tr class="hover:bg-slate-50">
                                        <td class="px-6 py-4 text-sm font-medium text-slate-900">{{ $invoice->invoice_number }}</td>
                                        <td class="px-6 py-4 text-sm text-slate-600">{{ $invoice->issue_date?->format('d/m/Y') ?? '-' }}</td>
                                        <td class="px-6 py-4 text-sm text-slate-600">{{ $invoice->due_date?->format('d/m/Y') ?? '-' }}</td>
                                        <td class="px-6 py-4 text-sm text-slate-900">Rp {{ number_format($invoice->total_amount, 0, ',', '.') }}</td>
                                        <td class="px-6 py-4">
                                            <span class="px-2 py-1 text-xs font-medium rounded-full 
                                                @if($invoice->status === 'paid') bg-green-100 text-green-600
                                                @else bg-orange-100 text-orange-600
                                                @endif
                                            ">
                                                {{ $invoice->status === 'paid' ? 'Lunas' : ucfirst($invoice->status) }}
                                            </span>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="p-6 text-slate-500">Belum ada invoice</div>
                @endif
            </x-base.card>
        @endif

        {{-- Payment Tab --}}
        @if($activeTab === 'payment')
            <x-base.card :padding="false">
                <x-slot name="header">
                    <h3 class="text-lg font-semibold text-slate-900">Riwayat Pembayaran</h3>
                </x-slot>
                @if($payments->isNotEmpty())
                    <div class="overflow-x-auto">
                        <table class="w-full">
                            <thead>
                                <tr class="bg-slate-50">
                                    <th class="px-6 py-3 text-left text-xs font-semibold text-slate-600 uppercase">ID Pembayaran</th>
                                    <th class="px-6 py-3 text-left text-xs font-semibold text-slate-600 uppercase">Tanggal</th>
                                    <th class="px-6 py-3 text-left text-xs font-semibold text-slate-600 uppercase">Jumlah</th>
                                    <th class="px-6 py-3 text-left text-xs font-semibold text-slate-600 uppercase">Metode</th>
                                    <th class="px-6 py-3 text-left text-xs font-semibold text-slate-600 uppercase">Status</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-200">
                                @foreach($payments as $payment)
                                    <tr class="hover:bg-slate-50">
                                        <td class="px-6 py-4 text-sm font-medium text-slate-900">{{ $payment->uuid ?? $payment->id }}</td>
                                        <td class="px-6 py-4 text-sm text-slate-600">{{ $payment->paid_at?->format('d/m/Y') ?? $payment->created_at->format('d/m/Y') }}</td>
                                        <td class="px-6 py-4 text-sm text-slate-900">Rp {{ number_format($payment->amount, 0, ',', '.') }}</td>
                                        <td class="px-6 py-4 text-sm text-slate-600">{{ $payment->method ?? '-' }}</td>
                                        <td class="px-6 py-4">
                                            <span class="px-2 py-1 text-xs font-medium rounded-full
                                                @if($payment->status === 'success') bg-green-100 text-green-600
                                                @elseif($payment->status === 'failed') bg-red-100 text-red-600
                                                @else bg-slate-100 text-slate-600
                                                @endif
                                            ">
                                                {{ ucfirst($payment->status) }}
                                            </span>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="p-6 text-slate-500">Belum ada pembayaran</div>
                @endif
            </x-base.card>
        @endif

        {{-- Ticket Tab --}}
        @if($activeTab === 'ticket')
            <x-base.card :padding="false">
                <x-slot name="header">
                    <h3 class="text-lg font-semibold text-slate-900">Tiket Support</h3>
                </x-slot>
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead>
                            <tr class="bg-slate-50">
                                <th class="px-6 py-3 text-left text-xs font-semibold text-slate-600 uppercase">ID Tiket</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-slate-600 uppercase">Judul</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-slate-600 uppercase">Prioritas</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-slate-600 uppercase">Status</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-slate-600 uppercase">Dibuat</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-200">
                            @foreach($tickets as $ticket)
                                <tr class="hover:bg-slate-50">
                                    <td class="px-6 py-4 text-sm font-medium text-slate-900">{{ $ticket['id'] }}</td>
                                    <td class="px-6 py-4 text-sm text-slate-600">{{ $ticket['title'] }}</td>
                                    <td class="px-6 py-4">
                                        <span class="px-2 py-1 text-xs font-medium rounded-full 
                                            @if($ticket['priority'] === 'high') bg-red-100 text-red-600
                                            @elseif($ticket['priority'] === 'medium') bg-orange-100 text-orange-600
                                            @else bg-blue-100 text-blue-600
                                            @endif
                                        ">
                                            {{ $ticket['priority'] === 'high' ? 'Tinggi' : ($ticket['priority'] === 'medium' ? 'Sedang' : 'Rendah') }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4">
                                        <span class="px-2 py-1 text-xs font-medium rounded-full 
                                            @if($ticket['status'] === 'open') bg-green-100 text-green-600
                                            @elseif($ticket['status'] === 'in_progress') bg-blue-100 text-blue-600
                                            @else bg-slate-100 text-slate-600
                                            @endif
                                        ">
                                            {{ $ticket['status'] === 'open' ? 'Buka' : ($ticket['status'] === 'in_progress' ? 'Dalam Proses' : 'Selesai') }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-sm text-slate-600">{{ $ticket['created_at']->format('d/m/Y') }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </x-base.card>
        @endif

        {{-- Device Tab --}}
        @if($activeTab === 'device')
            <x-base.card :padding="false">
                <x-slot name="header">
                    <h3 class="text-lg font-semibold text-slate-900">Perangkat</h3>
                </x-slot>
                @if(!empty($devices))
                    <div class="overflow-x-auto">
                        <table class="w-full">
                            <thead>
                                <tr class="bg-slate-50">
                                    <th class="px-6 py-3 text-left text-xs font-semibold text-slate-600 uppercase">ID</th>
                                    <th class="px-6 py-3 text-left text-xs font-semibold text-slate-600 uppercase">Tipe</th>
                                    <th class="px-6 py-3 text-left text-xs font-semibold text-slate-600 uppercase">Merek</th>
                                    <th class="px-6 py-3 text-left text-xs font-semibold text-slate-600 uppercase">Model</th>
                                    <th class="px-6 py-3 text-left text-xs font-semibold text-slate-600 uppercase">Serial Number</th>
                                    <th class="px-6 py-3 text-left text-xs font-semibold text-slate-600 uppercase">Status</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-200">
                                @foreach($devices as $device)
                                    <tr class="hover:bg-slate-50">
                                        <td class="px-6 py-4 text-sm font-medium text-slate-900">{{ $device['id'] }}</td>
                                        <td class="px-6 py-4 text-sm text-slate-600">{{ $device['type'] }}</td>
                                        <td class="px-6 py-4 text-sm text-slate-600">{{ $device['brand'] }}</td>
                                        <td class="px-6 py-4 text-sm text-slate-600">{{ $device['model'] }}</td>
                                        <td class="px-6 py-4 text-sm font-mono text-slate-900">{{ $device['serial'] }}</td>
                                        <td class="px-6 py-4">
                                            <span class="px-2 py-1 text-xs font-medium rounded-full
                                                @if($device['status'] === 'active') bg-green-100 text-green-600
                                                @else bg-slate-100 text-slate-600
                                                @endif
                                            ">
                                                {{ ucfirst($device['status']) }}
                                            </span>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="p-6 text-slate-500">Belum ada perangkat</div>
                @endif
            </x-base.card>
        @endif

        {{-- Installation Tab --}}
        @if($activeTab === 'installation')
            <x-base.card :padding="false">
                <x-slot name="header">
                    <h3 class="text-lg font-semibold text-slate-900">Riwayat Instalasi</h3>
                </x-slot>
                @if($installations->isNotEmpty())
                    <div class="overflow-x-auto">
                        <table class="w-full">
                            <thead>
                                <tr class="bg-slate-50">
                                    <th class="px-6 py-3 text-left text-xs font-semibold text-slate-600 uppercase">ID</th>
                                    <th class="px-6 py-3 text-left text-xs font-semibold text-slate-600 uppercase">Tanggal</th>
                                    <th class="px-6 py-3 text-left text-xs font-semibold text-slate-600 uppercase">Teknisi</th>
                                    <th class="px-6 py-3 text-left text-xs font-semibold text-slate-600 uppercase">Status</th>
                                    <th class="px-6 py-3 text-left text-xs font-semibold text-slate-600 uppercase">Catatan</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-200">
                                @foreach($installations as $installation)
                                    <tr class="hover:bg-slate-50">
                                        <td class="px-6 py-4 text-sm font-medium text-slate-900">{{ $installation->uuid ?? $installation->id }}</td>
                                        <td class="px-6 py-4 text-sm text-slate-600">
                                            {{ $installation->completed_at?->format('d/m/Y') ?? $installation->scheduled_at?->format('d/m/Y') ?? '-' }}
                                        </td>
                                        <td class="px-6 py-4 text-sm text-slate-600">
                                            {{ $installation->assignedTo?->name ?? '-' }}
                                        </td>
                                        <td class="px-6 py-4">
                                            <span class="px-2 py-1 text-xs font-medium rounded-full
                                                @if($installation->status === 'completed') bg-green-100 text-green-600
                                                @elseif($installation->status === 'in_progress') bg-blue-100 text-blue-600
                                                @else bg-slate-100 text-slate-600
                                                @endif
                                            ">
                                                {{ ucfirst($installation->status) }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 text-sm text-slate-600">{{ $installation->notes ?? '-' }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="p-6 text-slate-500">Belum ada instalasi</div>
                @endif
            </x-base.card>
        @endif

        {{-- GIS Tab --}}
        @if($activeTab === 'gis')
            <x-base.card>
                <x-slot name="header">
                    <h3 class="text-lg font-semibold text-slate-900">Lokasi Pelanggan</h3>
                </x-slot>
                <div class="h-96 bg-slate-100 rounded-lg flex items-center justify-center">
                    <div class="text-center text-slate-400">
                        <svg class="w-16 h-16 mx-auto mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                        </svg>
                        <p>Peta Lokasi</p>
                        <p class="text-sm mt-1">Integrasi peta akan ditampilkan di sini</p>
                    </div>
                </div>
            </x-base.card>
        @endif

        {{-- Monitoring Tab --}}
        @if($activeTab === 'monitoring')
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <x-base.card>
                    <x-slot name="header">
                        <h3 class="text-lg font-semibold text-slate-900">Status Koneksi</h3>
                    </x-slot>
                    <div class="space-y-4">
                        <div class="flex items-center justify-between py-3 border-b border-slate-200">
                            <span class="text-slate-600">Status</span>
                            <span class="flex items-center gap-2 text-green-600">
                                <span class="w-2 h-2 rounded-full bg-green-500 animate-pulse"></span>
                                Online
                            </span>
                        </div>
                        <div class="flex items-center justify-between py-3 border-b border-slate-200">
                            <span class="text-slate-600">Signal Strength</span>
                            <span class="text-slate-900">-20 dBm</span>
                        </div>
                        <div class="flex items-center justify-between py-3 border-b border-slate-200">
                            <span class="text-slate-600">Download Speed</span>
                            <span class="text-slate-900">95 Mbps</span>
                        </div>
                        <div class="flex items-center justify-between py-3">
                            <span class="text-slate-600">Upload Speed</span>
                            <span class="text-slate-900">45 Mbps</span>
                        </div>
                    </div>
                </x-base.card>

                <x-base.card>
                    <x-slot name="header">
                        <h3 class="text-lg font-semibold text-slate-900">Grafik Penggunaan</h3>
                    </x-slot>
                    <div class="h-48 bg-slate-100 rounded-lg flex items-center justify-center">
                        <div class="text-center text-slate-400">
                            <svg class="w-12 h-12 mx-auto mb-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                            </svg>
                            <p class="text-sm">Grafik Penggunaan Bandwidth</p>
                        </div>
                    </div>
                </x-base.card>
            </div>
        @endif

        {{-- History Tab --}}
        @if($activeTab === 'history')
            <x-base.card>
                <x-slot name="header">
                    <h3 class="text-lg font-semibold text-slate-900">Customer Timeline</h3>
                </x-slot>
                <div class="space-y-6">
                    @foreach($timeline as $item)
                        <div class="flex gap-4">
                            <div class="flex flex-col items-center">
                                <div class="w-3 h-3 rounded-full 
                                    @if($item['type'] === 'create') bg-primary-500
                                    @elseif($item['type'] === 'survey') bg-blue-500
                                    @elseif($item['type'] === 'contract') bg-green-500
                                    @elseif($item['type'] === 'installation') bg-orange-500
                                    @elseif($item['type'] === 'activation') bg-purple-500
                                    @else bg-slate-500
                                    @endif
                                "></div>
                                @if(!$loop->last)
                                    <div class="w-0.5 flex-1 bg-slate-200"></div>
                                @endif
                            </div>
                            <div class="flex-1 pb-6">
                                <p class="text-xs text-slate-500 mb-1">{{ $item['date']->format('d/m/Y H:i') }}</p>
                                <p class="font-medium text-slate-900">{{ $item['title'] }}</p>
                                <p class="text-sm text-slate-600 mt-1">{{ $item['description'] }}</p>
                            </div>
                        </div>
                    @endforeach
                </div>
            </x-base.card>
        @endif

        {{-- Activity Tab --}}
        @if($activeTab === 'activity')
            <x-base.card>
                <x-slot name="header">
                    <h3 class="text-lg font-semibold text-slate-900">Aktivitas Terbaru</h3>
                </x-slot>
                <div class="space-y-4">
                    @foreach($activities as $activity)
                        <div class="flex items-start gap-3 p-3 hover:bg-slate-50 rounded-lg">
                            <div class="w-10 h-10 rounded-full bg-slate-100 flex items-center justify-center flex-shrink-0">
                                <span class="text-sm font-medium text-slate-600">{{ substr($activity['user'], 0, 1) }}</span>
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="text-sm text-slate-900">
                                    <span class="font-medium">{{ $activity['user'] }}</span> {{ $activity['action'] }}
                                </p>
                                <p class="text-xs text-slate-500 mt-0.5">
                                    <span class="px-2 py-0.5 bg-slate-100 rounded-full text-xs">{{ $activity['module'] }}</span>
                                    {{ $activity['time'] }}
                                </p>
                            </div>
                        </div>
                    @endforeach
                </div>
            </x-base.card>
        @endif

        {{-- Notification Tab --}}
        @if($activeTab === 'notification')
            <x-base.card>
                <x-slot name="header">
                    <h3 class="text-lg font-semibold text-slate-900">Notifikasi</h3>
                </x-slot>
                <div class="space-y-3">
                    @foreach($notifications as $notification)
                        <div class="flex items-start gap-3 p-4 bg-slate-50 rounded-lg">
                            <div class="w-10 h-10 rounded-full 
                                @if(!$notification['read']) bg-primary-100
                                @else bg-slate-200
                                @endif
                            flex items-center justify-center flex-shrink-0">
                                <svg class="w-5 h-5 
                                    @if(!$notification['read']) text-primary-600
                                    @else text-slate-500
                                    @endif
                                " fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                                </svg>
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-medium text-slate-900">{{ $notification['title'] }}</p>
                                <p class="text-sm text-slate-600 mt-1">{{ $notification['message'] }}</p>
                                <p class="text-xs text-slate-400 mt-1">{{ $notification['created_at']->diffForHumans() }}</p>
                            </div>
                            @if(!$notification['read'])
                                <span class="w-2 h-2 rounded-full bg-primary-500 flex-shrink-0"></span>
                            @endif
                        </div>
                    @endforeach
                </div>
            </x-base.card>
        @endif
    </div>
</div>
