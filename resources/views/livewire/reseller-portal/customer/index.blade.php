@section('page_title')
    <span class="material-symbols-outlined notranslate text-indigo-500" translate="no" style="font-size:24px">group</span>
    <span class="text-lg">Data Pelanggan</span>
@endsection

<div class="space-y-5 pb-10">

    {{-- SESSION FLASH --}}
    @if(session('success'))
        <div class="flex items-center gap-3 px-4 py-3 bg-emerald-50 dark:bg-emerald-900/30 border border-emerald-200 dark:border-emerald-700 rounded-xl text-emerald-700 dark:text-emerald-400 text-sm font-medium">
            <span class="material-symbols-outlined notranslate" translate="no" style="font-size:18px">check_circle</span>
            {{ session('success') }}
        </div>
    @endif
    @if(session('error'))
        <div class="flex items-center gap-3 px-4 py-3 bg-red-50 dark:bg-red-900/30 border border-red-200 dark:border-red-700 rounded-xl text-red-700 dark:text-red-400 text-sm font-medium">
            <span class="material-symbols-outlined notranslate" translate="no" style="font-size:18px">error</span>
            {{ session('error') }}
        </div>
    @endif

    {{-- KPI CARDS --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        {{-- Total --}}
        <div class="relative overflow-x-auto rounded-xl border border-indigo-200 dark:border-indigo-800/60 shadow-md bg-gradient-to-br from-indigo-50 to-white dark:from-indigo-950/50 dark:to-slate-800 group hover:shadow-lg transition-all">
            <div class="absolute top-0 left-0 right-0 h-1 bg-gradient-to-r from-indigo-500 to-blue-400 rounded-t-xl"></div>
            <div class="absolute top-3 right-3 opacity-10 text-indigo-400 group-hover:opacity-20 group-hover:scale-110 transition-all">
                <span class="material-symbols-outlined notranslate" translate="no" style="font-size:56px">group</span>
            </div>
            <div class="p-4 pt-5">
                <h3 class="text-xs font-bold text-indigo-500 dark:text-indigo-400 uppercase tracking-widest mb-2">Total</h3>
                <div class="text-4xl font-black text-indigo-700 dark:text-indigo-300 mb-3">{{ number_format($totalCustomers) }}</div>
                <div class="text-xs text-slate-500 dark:text-slate-400">semua pelanggan</div>
            </div>
        </div>
        
        {{-- Aktif --}}
        <div wire:click="$set('statusFilter','active')" class="relative overflow-x-auto rounded-xl border border-emerald-200 dark:border-emerald-800/60 shadow-md bg-gradient-to-br from-emerald-50 to-white dark:from-emerald-950/50 dark:to-slate-800 group hover:shadow-lg transition-all cursor-pointer">
            <div class="absolute top-0 left-0 right-0 h-1 bg-gradient-to-r from-emerald-500 to-teal-400 rounded-t-xl"></div>
            <div class="absolute top-3 right-3 opacity-10 text-emerald-400 group-hover:opacity-20 group-hover:scale-110 transition-all">
                <span class="material-symbols-outlined notranslate" translate="no" style="font-size:56px">person_check</span>
            </div>
            <div class="p-4 pt-5">
                <h3 class="text-xs font-bold text-emerald-500 dark:text-emerald-400 uppercase tracking-widest mb-2">Aktif</h3>
                <div class="text-4xl font-black text-emerald-700 dark:text-emerald-300 mb-3">{{ number_format($activeCustomers) }}</div>
                <div class="text-xs text-slate-500 dark:text-slate-400">klik untuk filter</div>
            </div>
        </div>

        {{-- Suspend --}}
        <div wire:click="$set('statusFilter','suspend')" class="relative overflow-x-auto rounded-xl border border-red-200 dark:border-red-800/60 shadow-md bg-gradient-to-br from-red-50 to-white dark:from-red-950/50 dark:to-slate-800 group hover:shadow-lg transition-all cursor-pointer">
            <div class="absolute top-0 left-0 right-0 h-1 bg-gradient-to-r from-red-500 to-rose-400 rounded-t-xl"></div>
            <div class="absolute top-3 right-3 opacity-10 text-red-400 group-hover:opacity-20 group-hover:scale-110 transition-all">
                <span class="material-symbols-outlined notranslate" translate="no" style="font-size:56px">person_off</span>
            </div>
            <div class="p-4 pt-5">
                <h3 class="text-xs font-bold text-red-500 dark:text-red-400 uppercase tracking-widest mb-2">Suspend</h3>
                <div class="text-4xl font-black text-red-700 dark:text-red-300 mb-3">{{ number_format($suspendCustomers) }}</div>
                <div class="text-xs text-slate-500 dark:text-slate-400">klik untuk filter</div>
            </div>
        </div>

        {{-- Baru --}}
        <div class="relative overflow-x-auto rounded-xl border border-sky-200 dark:border-sky-800/60 shadow-md bg-gradient-to-br from-sky-50 to-white dark:from-sky-950/50 dark:to-slate-800 group hover:shadow-lg transition-all">
            <div class="absolute top-0 left-0 right-0 h-1 bg-gradient-to-r from-sky-500 to-cyan-400 rounded-t-xl"></div>
            <div class="absolute top-3 right-3 opacity-10 text-sky-400 group-hover:opacity-20 group-hover:scale-110 transition-all">
                <span class="material-symbols-outlined notranslate" translate="no" style="font-size:56px">person_add</span>
            </div>
            <div class="p-4 pt-5">
                <h3 class="text-xs font-bold text-sky-500 dark:text-sky-400 uppercase tracking-widest mb-2">Baru Bulan Ini</h3>
                <div class="text-4xl font-black text-sky-700 dark:text-sky-300 mb-3">{{ number_format($newCustomers) }}</div>
                <div class="text-xs text-slate-500 dark:text-slate-400">pelanggan baru</div>
            </div>
        </div>
    </div>

    {{-- SEARCH & FILTER BAR --}}
    <div class="bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl shadow-sm p-4">
        <div class="flex flex-col md:flex-row gap-3">
            {{-- Search --}}
            <div class="flex-1 relative">
                <span class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400">
                    <span class="material-symbols-outlined notranslate" translate="no" style="font-size:18px">search</span>
                </span>
                <input type="text" wire:model.live.debounce.300ms="search"
                       placeholder="Cari nama, email, telepon..."
                       class="w-full pl-9 pr-4 py-2 bg-slate-50 dark:bg-slate-900/50 border border-slate-200 dark:border-slate-700 rounded-lg text-sm text-slate-700 dark:text-slate-300 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all dark:bg-slate-900 dark:text-slate-100">
            </div>
            {{-- Status Filter --}}
            <select wire:model.live="statusFilter"
                    class="pl-3 pr-10 py-2 bg-slate-50 dark:bg-slate-900/50 border border-slate-200 dark:border-slate-700 rounded-lg text-sm text-slate-700 dark:text-slate-300 focus:outline-none focus:ring-2 focus:ring-indigo-500 cursor-pointer dark:bg-slate-900 dark:text-slate-100">
                <option value="">Semua Status</option>
                <option value="active">Aktif</option>
                <option value="suspend">Suspend</option>
                <option value="inactive">Nonaktif</option>
            </select>
            {{-- Per Page --}}
            <select wire:model.live="perPage"
                    class="pl-3 pr-10 py-2 bg-slate-50 dark:bg-slate-900/50 border border-slate-200 dark:border-slate-700 rounded-lg text-sm text-slate-700 dark:text-slate-300 focus:outline-none focus:ring-2 focus:ring-indigo-500 cursor-pointer dark:bg-slate-900 dark:text-slate-100">
                <option value="20">20 / halaman</option>
                <option value="50">50 / halaman</option>
                <option value="100">100 / halaman</option>
                <option value="500">500 / halaman</option>
                <option value="999999">Semua</option>
            </select>
            {{-- Reset --}}
            @if($search || $statusFilter)
            <button wire:click="resetFilters"
                    class="inline-flex items-center gap-1.5 px-3 py-2 bg-red-50 dark:bg-red-900/30 hover:bg-red-100 dark:bg-red-900/50 dark:hover:bg-red-800/50 text-red-600 dark:text-red-400 rounded-lg text-sm font-medium transition-colors cursor-pointer">
                <span class="material-symbols-outlined notranslate" translate="no" style="font-size:16px">filter_alt_off</span>
                Reset
            </button>
            @endif
        </div>
    </div>

    {{-- DATA TABLE --}}
    <div class="bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-slate-200 dark:border-slate-700 text-xs uppercase tracking-wider text-slate-500 dark:text-slate-400 bg-slate-50/80 dark:bg-slate-800/80">
                        <th class="px-4 py-3 text-left font-semibold whitespace-nowrap">
                            <button wire:click="sortBy('id')" class="flex items-center gap-1 hover:text-indigo-600 dark:hover:text-indigo-400 transition-colors cursor-pointer">
                                Id
                                <span class="material-symbols-outlined notranslate" translate="no" style="font-size:14px">unfold_more</span>
                            </button>
                        </th>
                        <th class="px-4 py-3 text-left font-semibold whitespace-nowrap">Nama</th>
                        <th class="px-4 py-3 text-left font-semibold whitespace-nowrap">Username</th>
                        <th class="px-4 py-3 text-left font-semibold whitespace-nowrap">Password</th>
                        <th class="px-4 py-3 text-left font-semibold whitespace-nowrap">Layanan</th>
                        <th class="px-4 py-3 text-left font-semibold whitespace-nowrap">Paket</th>
                        <th class="px-4 py-3 text-left font-semibold whitespace-nowrap">Ip Address</th>
                        <th class="px-4 py-3 text-left font-semibold whitespace-nowrap">Owner/Reseller</th>
                        <th class="px-4 py-3 text-left font-semibold whitespace-nowrap">WhatsApp</th>
                        <th class="px-4 py-3 text-left font-semibold whitespace-nowrap">Status</th>
                        <th class="px-4 py-3 text-left font-semibold whitespace-nowrap">
                            <button wire:click="sortBy('created_at')" class="flex items-center gap-1 hover:text-indigo-600 dark:hover:text-indigo-400 transition-colors cursor-pointer">
                                Tgl Register
                                <span class="material-symbols-outlined notranslate" translate="no" style="font-size:14px">unfold_more</span>
                            </button>
                        </th>
                        <th class="px-4 py-3 text-left font-semibold whitespace-nowrap">Tgl Isolir</th>
                        <th class="px-4 py-3 text-center font-semibold whitespace-nowrap">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-700/50">
                    @forelse($customers as $customer)
                        @php
                            $svc = $customer->customerServices->first();
                            $isOnline = false;
                            $ipAddress = '-';
                            $username = '-';
                            $password = '-';
                            $layanan = '-';
                            if ($svc && $svc->pppoeUser) {
                                $isOnline = $svc->pppoeUser->is_online ?? false;
                                $ipAddress = $svc->pppoeUser->static_ip ?? 'Dynamic';
                                $username = $svc->pppoeUser->username ?? '-';
                                $password = $svc->pppoeUser->password ?? '-';
                                $layanan = 'PPPoE';
                            } elseif ($svc && $svc->hotspotUser) {
                                $isOnline = $svc->hotspotUser->is_online ?? false;
                                $ipAddress = $svc->hotspotUser->static_ip ?? 'Dynamic';
                                $username = $svc->hotspotUser->username ?? '-';
                                $password = $svc->hotspotUser->password ?? '-';
                                $layanan = 'Hotspot';
                            } elseif ($svc) {
                                $username = $svc->username ?? '-';
                                $password = $svc->password ?? '-';
                                $layanan = strtoupper($svc->service_type ?? '-');
                            }
                            
                            $paket = $svc?->serviceProfile?->name ?? '-';
                            $tglRegister = $customer->created_at?->format('d-m-Y');
                            $tglIsolir = $svc?->suspended_at?->format('d-m-Y') ?? '-';
                            $reseller = $customer->reseller->name ?? 'Default';
                            $dynamicId = ($customer->created_at ? $customer->created_at->format('Ymd') : date('Ymd')) . str_pad($customer->id % 100, 2, '0', STR_PAD_LEFT);

                            $isInactive = $customer->status !== 'active';
                            
                            if ($isInactive) {
                                $rowBg = 'bg-slate-50/80 dark:bg-slate-800/40 opacity-75 grayscale-[30%] hover:bg-slate-100 dark:bg-slate-800 dark:hover:bg-slate-800/60';
                                $textPrimary = 'text-slate-500 dark:text-slate-400 line-through decoration-slate-300 dark:decoration-slate-600';
                                $textSecondary = 'text-slate-400 dark:text-slate-500 dark:text-slate-400';
                                $idColor = 'text-slate-500 dark:text-slate-500 dark:text-slate-400';
                            } elseif ($isOnline) {
                                $rowBg = 'bg-emerald-50/40 dark:bg-emerald-900/20 hover:bg-emerald-100 dark:bg-emerald-900/50/50 dark:hover:bg-emerald-900/30';
                                $textPrimary = 'text-emerald-900 dark:text-emerald-100 font-bold';
                                $textSecondary = 'text-emerald-700 dark:text-emerald-300';
                                $idColor = 'text-emerald-800 dark:text-emerald-400 font-semibold';
                            } else {
                                $rowBg = 'hover:bg-indigo-50 dark:bg-indigo-900/30/40 dark:hover:bg-indigo-900/10';
                                $textPrimary = 'text-slate-800 dark:text-slate-100 font-medium';
                                $textSecondary = 'text-slate-600 dark:text-slate-400';
                                $idColor = 'text-slate-700 dark:text-slate-300';
                            }
                        @endphp
                        <tr wire:key="customer-{{ $customer->id }}" class="{{ $rowBg }} transition-colors group">
                            {{-- Id + Checkbox --}}
                            <td class="px-4 py-3 whitespace-nowrap {{ $idColor }} font-mono text-xs flex items-center gap-2">
                                <input type="checkbox" wire:change="toggleStatus({{ $customer->id }})" 
                                       class="rounded border-slate-300 dark:border-slate-600 text-indigo-600 focus:ring-indigo-500 cursor-pointer"
                                       @if(!$isInactive) checked @endif
                                       title="Toggle Aktif/Nonaktif">
                                {{ $dynamicId }}
                            </td>
                            
                            {{-- Nama --}}
                            <td class="px-4 py-3 whitespace-nowrap {{ $textPrimary }}">
                                <a href="{{ route('reseller-portal.customers.show', $customer->id) }}" class="hover:text-indigo-600 dark:hover:text-indigo-400 hover:underline transition-colors">
                                    {{ $customer->name ?? '-' }}
                                </a>
                            </td>
                            <td class="px-4 py-3">
                                <div class="flex items-center gap-2">
                                    <span class="text-sm font-semibold text-indigo-600 dark:text-indigo-400 font-mono">{{ $username }}</span>
                                    <button type="button" onclick="navigator.clipboard.writeText('{{ $username }}'); alert('Username disalin!')" class="text-slate-400 hover:text-indigo-500 transition-colors" title="Salin Username">
                                        <span class="material-symbols-outlined notranslate" translate="no" style="font-size:14px">content_copy</span>
                                    </button>
                                </div>
                            </td>
                            <td class="px-4 py-3" x-data="{ showPw: false }">
                                <div class="flex items-center gap-2">
                                    <span class="text-xs font-mono text-slate-500 dark:text-slate-400" x-show="!showPw">&bull;&bull;&bull;&bull;&bull;&bull;&bull;&bull;</span>
                                    <span class="text-xs font-mono text-slate-700 dark:text-slate-300" x-show="showPw" x-cloak>{{ $password }}</span>
                                    <button type="button" @click="showPw = !showPw" class="text-slate-400 hover:text-slate-600 dark:text-slate-400 dark:hover:text-slate-300 transition-colors" title="Lihat/Sembunyikan Password">
                                        <span class="material-symbols-outlined notranslate" translate="no" style="font-size:14px" x-text="showPw ? 'visibility_off' : 'visibility'"></span>
                                    </button>
                                    <button type="button" onclick="navigator.clipboard.writeText('{{ $password }}'); alert('Password disalin!')" class="text-slate-400 hover:text-indigo-500 transition-colors" title="Salin Password">
                                        <span class="material-symbols-outlined notranslate" translate="no" style="font-size:14px">content_copy</span>
                                    </button>
                                </div>
                            </td>
                            
                            {{-- Layanan --}}
                            <td class="px-4 py-3 whitespace-nowrap">
                                <span class="inline-flex items-center px-2.5 py-1 rounded-md text-xs font-bold {{ $layanan === 'PPPoE' ? 'bg-indigo-100 text-indigo-700 dark:bg-indigo-900/40 dark:text-indigo-400' : 'bg-amber-100 text-amber-700 dark:bg-amber-900/40 dark:text-amber-400' }}">
                                    {{ $layanan }}
                                </span>
                            </td>
                            
                            {{-- Paket --}}
                            <td class="px-4 py-3 whitespace-nowrap {{ $textSecondary }}">
                                {{ $paket }}
                            </td>
                            
                            {{-- Ip Address --}}
                            <td class="px-4 py-3 whitespace-nowrap {{ $textSecondary }} font-mono text-xs">
                                @if($ipAddress !== '-')
                                    <a href="http://{{ $ipAddress }}" target="_blank" rel="noopener noreferrer" class="hover:text-indigo-600 dark:hover:text-indigo-400 hover:underline transition-colors inline-flex items-center gap-1">
                                        {{ $ipAddress }}
                                        <span class="material-symbols-outlined notranslate" translate="no" style="font-size:12px">open_in_new</span>
                                    </a>
                                @else
                                    {{ $ipAddress }}
                                @endif
                            </td>
                            
                            {{-- Owner/Reseller --}}
                            <td class="px-4 py-3 whitespace-nowrap">
                                @if($reseller !== 'Default')
                                    <span class="inline-flex items-center px-2 py-1 rounded text-xs font-medium bg-purple-100 text-purple-700 dark:bg-purple-900/40 dark:text-purple-400">
                                        {{ $reseller }}
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2 py-1 rounded text-xs font-medium bg-slate-100 text-slate-700 dark:bg-slate-800 dark:text-slate-400">
                                        Pusat (HQ)
                                    </span>
                                @endif
                            </td>
                            
                            {{-- WhatsApp --}}
                            <td class="px-4 py-3 whitespace-nowrap {{ $textSecondary }}">
                                {{ $customer->phone ?? '-' }}
                            </td>
                            
                            {{-- Status Online/Offline --}}
                            <td class="px-4 py-3 whitespace-nowrap">
                                @if($isInactive || $tglIsolir !== '-')
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md text-xs font-bold bg-rose-700 text-white shadow-sm">
                                        <span class="w-1.5 h-1.5 rounded-full bg-rose-300"></span>
                                        Suspend
                                    </span>
                                @elseif($isOnline)
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md text-xs font-bold bg-green-500 text-white shadow-sm">
                                        <span class="w-1.5 h-1.5 rounded-full bg-white dark:bg-slate-800 animate-pulse"></span>
                                        Online
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md text-xs font-bold bg-red-500 text-white shadow-sm">
                                        <span class="w-1.5 h-1.5 rounded-full bg-red-200"></span>
                                        Offline
                                    </span>
                                @endif
                            </td>
                            
                            {{-- Tgl Register --}}
                            <td class="px-4 py-3 whitespace-nowrap {{ $textSecondary }}">
                                {{ $tglRegister }}
                            </td>
                            
                            {{-- Tgl Isolir --}}
                            <td class="px-4 py-3 whitespace-nowrap {{ $textSecondary }}">
                                {{ $tglIsolir }}
                            </td>
                            
                            {{-- Aksi --}}
                            <td class="px-4 py-3 whitespace-nowrap">
                                <div class="flex items-center justify-center gap-2">
                                    {{-- Edit / Detail --}}
                                    <a href="{{ route('reseller-portal.customers.show', $customer->id) }}" class="p-1.5 bg-amber-50 dark:bg-amber-900/30 text-amber-600 rounded hover:bg-amber-100 dark:bg-amber-900/50 dark:hover:bg-amber-900/50 transition-colors" title="Edit / Detail">
                                        <span class="material-symbols-outlined notranslate" translate="no" style="font-size:16px">edit</span>
                                    </a>
                                    {{-- Customer Portal --}}
                                    <a href="#" class="p-1.5 bg-sky-50 dark:bg-sky-900/30 text-sky-600 rounded hover:bg-sky-100 dark:hover:bg-sky-900/50 transition-colors" title="Portal Customer">
                                        <span class="material-symbols-outlined notranslate" translate="no" style="font-size:16px">language</span>
                                    </a>
                                    {{-- Hapus --}}
                                    <button wire:click="delete({{ $customer->id }})" wire:confirm="Yakin ingin menghapus pelanggan ini?" class="p-1.5 bg-red-50 dark:bg-red-900/30 text-red-600 rounded hover:bg-red-100 dark:bg-red-900/50 dark:hover:bg-red-900/50 transition-colors" title="Hapus">
                                        <span class="material-symbols-outlined notranslate" translate="no" style="font-size:16px">delete</span>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="11" class="px-4 py-10 text-center">
                                <div class="flex flex-col items-center gap-2 text-slate-400 dark:text-slate-500 dark:text-slate-400">
                                    <span class="material-symbols-outlined notranslate" translate="no" style="font-size:48px">manage_search</span>
                                    <p class="font-semibold text-slate-500 dark:text-slate-400">Tidak ada data pelanggan</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        {{-- Pagination --}}
        @if($customers->hasPages())
        <div class="px-4 py-3 border-t border-slate-200 dark:border-slate-700 bg-slate-50/50 dark:bg-slate-800/50">
            {{ $customers->links() }}
        </div>
        @endif
    </div>

</div>

