@section('page_title')
    <div class="flex items-center gap-2">
        <div class="w-8 h-8 rounded-full bg-indigo-100 dark:bg-indigo-900/50 flex items-center justify-center text-indigo-600 dark:text-indigo-400 font-bold text-sm">
            <span class="material-symbols-outlined notranslate" translate="no" style="font-size:18px">manage_accounts</span>
        </div>
        <span class="text-lg">Manajemen User</span>
    </div>
@endsection

<div class="space-y-6 pb-10">
    {{-- Toolbar --}}
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-white dark:bg-slate-800 p-4 rounded-xl shadow-sm border border-slate-200 dark:border-slate-700">
        <div class="flex flex-wrap items-center gap-3">
            {{-- Search --}}
            <div class="relative w-full sm:w-64">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <span class="material-symbols-outlined notranslate text-slate-400" translate="no" style="font-size:18px">search</span>
                </div>
                <input wire:model.live.debounce.300ms="search" type="text" placeholder="Cari nama atau email..." 
                    class="block w-full pl-10 pr-3 py-2 border border-slate-200 dark:border-slate-700 rounded-lg text-sm bg-slate-50 dark:bg-slate-900/50 focus:ring-2 focus:ring-indigo-500 dark:text-slate-100 placeholder-slate-400 dark:bg-slate-900 dark:text-slate-100">
            </div>
            
            <button wire:click="resetFilters" class="px-3 py-2 text-sm text-slate-500 hover:text-slate-700 dark:text-slate-300 dark:hover:text-slate-300 font-medium hidden sm:block">
                Reset
            </button>
        </div>

        <div class="flex items-center gap-3">
            {{-- Per Page Selector --}}
            <select wire:model.live="perPage" class="py-2 pl-3 pr-8 border border-slate-200 dark:border-slate-700 rounded-lg text-sm bg-slate-50 dark:bg-slate-900/50 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 dark:text-slate-100 dark:bg-slate-900 dark:text-slate-100">
                <option value="10">10 / halaman</option>
                <option value="25">25 / halaman</option>
                <option value="50">50 / halaman</option>
                <option value="100">100 / halaman</option>
                <option value="all">Semua</option>
            </select>
            
            <button wire:click="export" class="inline-flex items-center gap-2 px-4 py-2 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-700 dark:text-slate-300 rounded-lg text-sm font-medium hover:bg-slate-50 dark:bg-slate-900/50 dark:hover:bg-slate-700 transition-colors">
                <span class="material-symbols-outlined notranslate" translate="no" style="font-size:18px">download</span>
                Export
            </button>

            <a href="{{ route('admin.users.create') }}" class="inline-flex items-center gap-2 px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg text-sm font-medium shadow-sm transition-colors whitespace-nowrap">
                <span class="material-symbols-outlined notranslate" translate="no" style="font-size:18px">person_add</span>
                Tambah User
            </a>
        </div>
    </div>

    {{-- Data Table --}}
    <div class="bg-white dark:bg-slate-800 rounded-xl shadow-sm border border-slate-200 dark:border-slate-700 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm text-left">
                <thead class="bg-slate-50 dark:bg-slate-900/50 text-slate-600 dark:text-slate-400 font-semibold border-b border-slate-200 dark:border-slate-700">
                    <tr>
                        <th class="px-6 py-4 whitespace-nowrap">Nama</th>
                        <th class="px-6 py-4 whitespace-nowrap">Username</th>
                        <th class="px-6 py-4 whitespace-nowrap">Status Akun</th>
                        <th class="px-6 py-4 whitespace-nowrap">Fungsi / Job</th>
                        <th class="px-6 py-4 whitespace-nowrap">Role & Hak Akses</th>
                        <th class="px-6 py-4 whitespace-nowrap">Terdaftar</th>
                        <th class="px-6 py-4 whitespace-nowrap text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-700/50">
                    @forelse($users as $user)
                        <tr class="hover:bg-slate-50 dark:bg-slate-900/50 dark:hover:bg-slate-800/50 group transition-colors">
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 rounded-full bg-indigo-100 dark:bg-indigo-900/50 flex items-center justify-center text-indigo-600 dark:text-indigo-400 font-bold shrink-0">
                                        {{ substr($user->name ?? 'U', 0, 1) }}
                                    </div>
                                    <div>
                                        <div class="font-bold text-slate-900 dark:text-slate-100">{{ $user->name ?? '-' }}</div>
                                        <div class="text-xs text-slate-500 dark:text-slate-400">{{ $user->email ?? '-' }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 font-mono text-sm text-slate-700 dark:text-slate-300">
                                {{ $user->username ?? '-' }}
                            </td>
                            <td class="px-6 py-4">
                                @if($user->is_active)
                                    <span class="px-2.5 py-1 text-xs font-semibold rounded-lg bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400">Aktif</span>
                                @else
                                    <span class="px-2.5 py-1 text-xs font-semibold rounded-lg bg-slate-100 text-slate-700 dark:bg-slate-800 dark:text-slate-400">Nonaktif</span>
                                @endif
                            </td>
                            <td class="px-6 py-4">
                                <div class="font-medium text-slate-700 dark:text-slate-300">
                                    {{ $user->job_function ?? '-' }}
                                </div>
                                <div class="text-xs text-slate-500 dark:text-slate-400">{{ $user->job_title ?? '-' }}</div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex flex-wrap gap-1">
                                    @foreach($user->roles as $role)
                                        <span class="px-2.5 py-1 text-xs font-semibold rounded-lg bg-indigo-100 text-indigo-700 dark:bg-indigo-900/30 dark:text-indigo-400">
                                            {{ ucfirst($role->name) }}
                                        </span>
                                    @endforeach
                                </div>
                            </td>
                            <td class="px-6 py-4 text-slate-600 dark:text-slate-400 whitespace-nowrap">
                                {{ $user->created_at?->translatedFormat('d M Y') ?? '-' }}
                            </td>
                            <td class="px-6 py-4 text-right">
                                <div class="flex items-center justify-end gap-2 opacity-0 group-hover:opacity-100 transition-opacity">
                                    <button wire:click="showSummary({{ $user->id }})" class="w-8 h-8 rounded-lg bg-slate-50 dark:bg-slate-800 text-slate-500 dark:text-slate-400 hover:text-indigo-600 dark:hover:text-indigo-400 hover:bg-indigo-50 dark:bg-indigo-900/30 dark:hover:bg-indigo-900/30 flex items-center justify-center transition-colors" title="Lihat Detail">
                                        <span class="material-symbols-outlined notranslate" translate="no" style="font-size:18px">visibility</span>
                                    </button>
                                    <a href="{{ route('admin.users.edit', $user->id) }}" class="w-8 h-8 rounded-lg bg-slate-50 dark:bg-slate-800 text-slate-500 dark:text-slate-400 hover:text-amber-600 dark:hover:text-amber-400 hover:bg-amber-50 dark:bg-amber-900/30 dark:hover:bg-amber-900/30 flex items-center justify-center transition-colors" title="Edit">
                                        <span class="material-symbols-outlined notranslate" translate="no" style="font-size:18px">edit</span>
                                    </a>
                                    @if($user->id !== auth()->id())
                                        <button wire:click="delete({{ $user->id }})" wire:confirm="Yakin ingin menghapus user ini?" class="w-8 h-8 rounded-lg bg-slate-50 dark:bg-slate-800 text-slate-500 dark:text-slate-400 hover:text-red-600 dark:hover:text-red-400 hover:bg-red-50 dark:bg-red-900/30 dark:hover:bg-red-900/30 flex items-center justify-center transition-colors">
                                            <span class="material-symbols-outlined notranslate" translate="no" style="font-size:18px">delete</span>
                                        </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-6 py-12 text-center text-slate-500 dark:text-slate-400">
                                <span class="material-symbols-outlined notranslate block mx-auto text-slate-300 dark:text-slate-600 dark:text-slate-400 mb-3" translate="no" style="font-size:48px">group_off</span>
                                <p class="text-lg font-medium text-slate-900 dark:text-slate-100">Tidak ada User</p>
                                <p class="text-slate-500 dark:text-slate-400 mt-1">Belum ada data staf atau user yang terdaftar.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($users->hasPages())
            <div class="px-6 py-4 border-t border-slate-200 dark:border-slate-700 bg-slate-50/50 dark:bg-slate-800/50">
                {{ $users->links() }}
            </div>
        @endif
    </div>
    {{-- Summary Modal --}}
    @if($showSummaryModal && $summaryUser)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/50 backdrop-blur-sm">
            <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-xl w-full max-w-2xl overflow-hidden" @click.away="$wire.closeSummaryModal()">
                <div class="flex items-center justify-between px-6 py-4 border-b border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-900/50">
                    <h3 class="text-lg font-bold text-slate-900 dark:text-slate-100 flex items-center gap-2">
                        <span class="material-symbols-outlined notranslate text-indigo-500" translate="no">analytics</span>
                        Ringkasan Laporan - [ {{ strtoupper($summaryUser->name) }} ]
                    </h3>
                    <button wire:click="closeSummaryModal" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-300">
                        <span class="material-symbols-outlined notranslate" translate="no">close</span>
                    </button>
                </div>
                
                <div class="p-6">
                    @if($summaryUser->hasRole('reseller'))
                        <div class="space-y-6">
                            {{-- Keuangan --}}
                            <div class="grid grid-cols-2 gap-4">
                                <div class="bg-slate-50 dark:bg-slate-900/50 p-4 rounded-xl border border-slate-200 dark:border-slate-700">
                                    <div class="text-sm text-slate-500 dark:text-slate-400 mb-1">Income Harian</div>
                                    <div class="text-lg font-bold text-emerald-600 dark:text-emerald-400">Rp. {{ number_format($summaryData['income_hari_ini'] ?? 0, 0, ',', '.') }}</div>
                                </div>
                                <div class="bg-slate-50 dark:bg-slate-900/50 p-4 rounded-xl border border-slate-200 dark:border-slate-700">
                                    <div class="text-sm text-slate-500 dark:text-slate-400 mb-1">Income Bulan Ini</div>
                                    <div class="text-lg font-bold text-emerald-600 dark:text-emerald-400">Rp. {{ number_format($summaryData['income_bulan_ini'] ?? 0, 0, ',', '.') }}</div>
                                </div>
                                <div class="bg-slate-50 dark:bg-slate-900/50 p-4 rounded-xl border border-slate-200 dark:border-slate-700">
                                    <div class="text-sm text-slate-500 dark:text-slate-400 mb-1">Fee Hari ini</div>
                                    <div class="text-lg font-bold text-indigo-600 dark:text-indigo-400">Rp. {{ number_format($summaryData['fee_hari_ini'] ?? 0, 0, ',', '.') }}</div>
                                </div>
                                <div class="bg-slate-50 dark:bg-slate-900/50 p-4 rounded-xl border border-slate-200 dark:border-slate-700">
                                    <div class="text-sm text-slate-500 dark:text-slate-400 mb-1">Fee Bulan Ini</div>
                                    <div class="text-lg font-bold text-indigo-600 dark:text-indigo-400">Rp. {{ number_format($summaryData['fee_bulan_ini'] ?? 0, 0, ',', '.') }}</div>
                                </div>
                            </div>
                            
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                                {{-- Voucher --}}
                                <div>
                                    <h4 class="text-sm font-bold text-slate-900 dark:text-slate-100 mb-3 border-b border-slate-200 dark:border-slate-700 pb-2">Data Voucher</h4>
                                    <ul class="space-y-2 text-sm">
                                        <li class="flex justify-between">
                                            <span class="text-slate-600 dark:text-slate-400">Stok Voucher</span>
                                            <span class="font-semibold text-slate-900 dark:text-slate-100">{{ $summaryData['stok_voucher'] ?? 0 }} pcs</span>
                                        </li>
                                        <li class="flex justify-between">
                                            <span class="text-slate-600 dark:text-slate-400">Voucher Expired</span>
                                            <span class="font-semibold text-red-600 dark:text-red-400">{{ $summaryData['voucher_expired'] ?? 0 }} pcs</span>
                                        </li>
                                        <li class="flex justify-between">
                                            <span class="text-slate-600 dark:text-slate-400">+ Voucher Bulan ini</span>
                                            <span class="font-semibold text-emerald-600 dark:text-emerald-400">{{ $summaryData['voucher_bulan_ini'] ?? 0 }} pcs</span>
                                        </li>
                                    </ul>
                                </div>
                                
                                {{-- Pelanggan --}}
                                <div>
                                    <h4 class="text-sm font-bold text-slate-900 dark:text-slate-100 mb-3 border-b border-slate-200 dark:border-slate-700 pb-2">Data Pelanggan</h4>
                                    <ul class="space-y-2 text-sm">
                                        <li class="flex justify-between">
                                            <span class="text-slate-600 dark:text-slate-400">Jumlah Pelanggan - HOTSPOT</span>
                                            <span class="font-semibold text-slate-900 dark:text-slate-100">{{ $summaryData['jml_hotspot'] ?? 0 }} user</span>
                                        </li>
                                        <li class="flex justify-between">
                                            <span class="text-slate-600 dark:text-slate-400">Jumlah Pelanggan - PPPoE</span>
                                            <span class="font-semibold text-slate-900 dark:text-slate-100">{{ $summaryData['jml_ppp'] ?? 0 }} user</span>
                                        </li>
                                        <li class="flex justify-between">
                                            <span class="text-slate-600 dark:text-slate-400">Pelanggan Isolir - HOTSPOT</span>
                                            <span class="font-semibold text-red-600 dark:text-red-400">{{ $summaryData['isolir_hotspot'] ?? 0 }} user</span>
                                        </li>
                                        <li class="flex justify-between">
                                            <span class="text-slate-600 dark:text-slate-400">Pelanggan Isolir - PPPoE</span>
                                            <span class="font-semibold text-red-600 dark:text-red-400">{{ $summaryData['isolir_ppp'] ?? 0 }} user</span>
                                        </li>
                                        <li class="flex justify-between">
                                            <span class="text-slate-600 dark:text-slate-400">+ Pelanggan Bulan ini - HOTSPOT</span>
                                            <span class="font-semibold text-emerald-600 dark:text-emerald-400">{{ $summaryData['hotspot_bulan_ini'] ?? 0 }} user</span>
                                        </li>
                                        <li class="flex justify-between">
                                            <span class="text-slate-600 dark:text-slate-400">+ Pelanggan Bulan ini - PPPoE</span>
                                            <span class="font-semibold text-emerald-600 dark:text-emerald-400">{{ $summaryData['ppp_bulan_ini'] ?? 0 }} user</span>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    @else
                        <div class="text-center py-8">
                            <span class="material-symbols-outlined notranslate text-slate-300 dark:text-slate-600 mb-3" translate="no" style="font-size: 48px">info</span>
                            <p class="text-slate-500 dark:text-slate-400">Laporan statistik hanya tersedia untuk akun dengan role <span class="font-semibold text-slate-700 dark:text-slate-300">Reseller</span>.</p>
                        </div>
                    @endif
                </div>
                
                <div class="px-6 py-4 border-t border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-900/50 flex justify-end">
                    <button wire:click="closeSummaryModal" class="px-5 py-2 text-sm font-semibold text-slate-700 dark:text-slate-300 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg hover:bg-slate-50 dark:hover:bg-slate-700 transition-colors">
                        Tutup
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>
