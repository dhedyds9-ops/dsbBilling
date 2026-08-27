<div class="max-w-7xl mx-auto p-4 sm:p-6 lg:p-8" wire:poll.5s>

    <!-- Session Alerts -->
    <div class="mb-4 space-y-2">
        @if(session()->has('success'))
            <x-feedback.alert variant="success">{{ session('success') }}</x-feedback.alert>
        @endif
        @if(session()->has('error'))
            <x-feedback.alert variant="danger">{{ session('error') }}</x-feedback.alert>
        @endif
    </div>

    <!-- Tabs Setup -->
    @php
        $tabColors = [
            'pppoe'    => 'border-blue-600 text-blue-600',
            'hotspot'  => 'border-green-600 text-green-600',
            'voucher'  => 'border-purple-600 text-purple-600',
        ];
        $inactiveTab = 'border-transparent text-slate-500 hover:text-slate-700 hover:border-slate-300';
    @endphp

    <!-- Tabs Navigation + Search -->
    <div class="border-b border-slate-200 mb-4">
        <div class="flex items-center justify-between">
            <nav class="flex gap-4" aria-label="Tabs">
                @foreach($tabColors as $tab => $color)
                    <button
                        wire:click="setActiveTab('{{ $tab }}')"
                        class="px-4 py-2 text-sm font-medium border-b-2 transition-colors {{ $activeTab === $tab ? $color : $inactiveTab }}">
                        {{ ucfirst($tab) }}
                    </button>
                @endforeach
            </nav>
            <div class="w-full md:w-96">
                <label for="user-online-search" class="sr-only">Cari User Online</label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                        </svg>
                    </div>
                    <input
                        id="user-online-search"
                        wire:model.live.debounce.300ms="search"
                        type="text"
                        class="block w-full pl-10 pr-3 py-2.5 border border-gray-300 rounded-lg text-sm bg-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-primary-500 transition"
                        placeholder="Cari user, router, IP, MAC, voucher, owner..."
                    >
                </div>
            </div>
        </div>
    </div>

    <!-- Tab Content -->
    <div>
        @if($activeTab === 'pppoe')
            <x-base.card class="overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="th-col">Nama User</th>
                                <th class="th-col">Router</th>
                                <th class="th-col">Alamat IP</th>
                                <th class="th-col">Uptime</th>
                                <th class="th-col">Traffic (In/Out)</th>
                                <th class="th-col">Mulai Session</th>
                                <th class="th-col text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @forelse($pppoeSessions as $session)
                                <tr wire:key="pppoe-{{ $session->id }}" class="hover:bg-gray-50 transition-colors">
                                    <td class="td-col font-medium">{{ $session->name }}</td>
                                    <td class="td-col">{{ $session->router?->name ?? '-' }}</td>
                                    <td class="td-col">{{ $session->address }}</td>
                                    <td class="td-col">{{ $session->uptime }}</td>
                                    <td class="td-col">
                                        <span class="text-blue-600">{{ number_format($session->bytes_in) }}</span> / 
                                        <span class="text-green-600">{{ number_format($session->bytes_out) }}</span>
                                    </td>
                                    <td class="td-col">{{ $session->session_started_at ? $session->session_started_at->format('d/m/Y H:i') : '-' }}</td>
                                    <td class="td-col text-center">
                                        <button 
                                            wire:click="kickPppoe({{ $session->id }})" 
                                            wire:confirm="Yakin ingin memutuskan koneksi PPPoE user {{ $session->name }}?"
                                            class="inline-flex items-center justify-center p-1.5 rounded-md text-red-600 hover:bg-red-100 hover:text-red-800 focus:outline-none focus:ring-2 focus:ring-red-500 transition-colors"
                                            title="Kick User">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path>
                                            </svg>
                                        </button>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="px-6 py-12 text-center">
                                        <div class="empty-state">
                                            <div class="empty-state-icon">
                                                <svg class="w-6 h-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path></svg>
                                            </div>
                                            <h3 class="empty-state-title">Tidak ada PPPoE User Online</h3>
                                            <p class="empty-state-desc">Semua PPPoE User sedang offline.</p>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                @if($pppoeSessions->hasPages())
                    <div class="bg-white px-6 py-3 border-t border-gray-200">
                        {{ $pppoeSessions->links() }}
                    </div>
                @endif
            </x-base.card>

        @elseif($activeTab === 'hotspot')
            <x-base.card class="overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="th-col">Nama User</th>
                                <th class="th-col">Router</th>
                                <th class="th-col">Alamat IP</th>
                                <th class="th-col">MAC Address</th>
                                <th class="th-col">Uptime</th>
                                <th class="th-col">Traffic (In/Out)</th>
                                <th class="th-col">Mulai Session</th>
                                <th class="th-col text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @forelse($hotspotSessions as $session)
                                <tr wire:key="hotspot-{{ $session->id }}" class="hover:bg-gray-50 transition-colors">
                                    <td class="td-col font-medium">{{ $session->user }}</td>
                                    <td class="td-col">{{ $session->router?->name ?? '-' }}</td>
                                    <td class="td-col">{{ $session->address }}</td>
                                    <td class="td-col">{{ $session->mac_address }}</td>
                                    <td class="td-col">{{ $session->uptime }}</td>
                                    <td class="td-col">
                                        <span class="text-blue-600">{{ number_format($session->bytes_in) }}</span> / 
                                        <span class="text-green-600">{{ number_format($session->bytes_out) }}</span>
                                    </td>
                                    <td class="td-col">{{ $session->session_started_at ? $session->session_started_at->format('d/m/Y H:i') : '-' }}</td>
                                    <td class="td-col text-center">
                                        <button 
                                            wire:click="kickHotspot({{ $session->id }})" 
                                            wire:confirm="Yakin ingin memutuskan koneksi Hotspot user {{ $session->user }}?"
                                            class="inline-flex items-center justify-center p-1.5 rounded-md text-red-600 hover:bg-red-100 hover:text-red-800 focus:outline-none focus:ring-2 focus:ring-red-500 transition-colors"
                                            title="Kick User">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path>
                                            </svg>
                                        </button>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="px-6 py-12 text-center">
                                        <div class="empty-state">
                                            <div class="empty-state-icon">
                                                <svg class="w-6 h-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path></svg>
                                            </div>
                                            <h3 class="empty-state-title">Tidak ada Hotspot User Online</h3>
                                            <p class="empty-state-desc">Semua Hotspot User sedang offline.</p>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                @if($hotspotSessions->hasPages())
                    <div class="bg-white px-6 py-3 border-t border-gray-200">
                        {{ $hotspotSessions->links() }}
                    </div>
                @endif
            </x-base.card>

        @elseif($activeTab === 'voucher')
            <x-base.card class="overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="th-col">Kode Voucher</th>
                                <th class="th-col">Paket</th>
                                <th class="th-col">Status</th>
                                <th class="th-col">Hotspot User</th>
                                <th class="th-col">Owner</th>
                                <th class="th-col">Tgl Aktifasi</th>
                                <th class="th-col">Kadaluarsa</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @forelse($vouchers as $voucher)
                                <tr wire:key="voucher-{{ $voucher->id }}" class="hover:bg-gray-50 transition-colors">
                                    <td class="td-col font-medium">{{ $voucher->code }}</td>
                                    <td class="td-col">{{ $voucher->serviceProfile?->name ?? '-' }}</td>
                                    <td class="td-col">
                                        <span class="status-badge {{ $voucher->status === 'active' ? 'bg-green-100 text-green-800' : ($voucher->status === 'expired' ? 'bg-red-100 text-red-800' : 'bg-gray-100 text-gray-800') }}">
                                            {{ ucfirst($voucher->status) }}
                                        </span>
                                    </td>
                                    <td class="td-col">{{ $voucher->hotspotUser?->username ?? '-' }}</td>
                                    <td class="td-col">{{ $voucher->owner?->name ?? '-' }}</td>
                                    <td class="td-col">{{ $voucher->activated_at ? $voucher->activated_at->format('d/m/Y H:i') : '-' }}</td>
                                    <td class="td-col">{{ $voucher->expires_at ? $voucher->expires_at->format('d/m/Y H:i') : '-' }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="px-6 py-12 text-center">
                                        <div class="empty-state">
                                            <div class="empty-state-icon">
                                                <svg class="w-6 h-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path></svg>
                                            </div>
                                            <h3 class="empty-state-title">Tidak ada Voucher Aktif</h3>
                                            <p class="empty-state-desc">Belum ada voucher yang diaktifkan.</p>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                @if($vouchers->hasPages())
                    <div class="bg-white px-6 py-3 border-t border-gray-200">
                        {{ $vouchers->links() }}
                    </div>
                @endif
            </x-base.card>
        @endif
    </div>
</div>

<!-- Tambahkan CSS Helper di bawah ini atau pindahkan ke file CSS global Anda -->
<style>
    .th-col { 
        padding: 0.75rem 1.5rem; 
        text-align: left; 
        font-size: 0.75rem; 
        font-weight: 600; 
        color: #6b7280; 
        uppercase; 
        letter-spacing: 0.05em; 
    }
    .td-col { 
        padding: 1rem 1.5rem; 
        white-space: nowrap; 
        font-size: 0.875rem; 
        color: #4b5563; 
    }
    .empty-state { 
        display: flex; flex-direction: column; align-items: center; justify-content: center; 
    }
    .empty-state-icon { 
        width: 3rem; height: 3rem; background-color: #f3f4f6; border-radius: 9999px; 
        display: flex; align-items: center; justify-content: center; margin-bottom: 0.75rem; 
    }
    .empty-state-title { font-size: 0.875rem; font-weight: 600; color: #111827; margin-bottom: 0.25rem; }
    .empty-state-desc { font-size: 0.75rem; color: #6b7280; }
    .status-badge { 
        padding: 0.25rem 0.625rem; display: inline-flex; font-size: 0.75rem; 
        line-height: 1rem; font-weight: 600; border-radius: 9999px; 
    }
</style>