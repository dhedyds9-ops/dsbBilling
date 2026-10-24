<div class="space-y-6 pb-12">
    {{-- Header & Tombol Tambah --}}
    <div class="flex flex-col sm:flex-row justify-between sm:items-center gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-900 dark:text-slate-100 flex items-center gap-2">
                <span class="material-symbols-outlined text-indigo-600 dark:text-indigo-400" style="font-size: 28px;">badge</span>
                Data Pegawai
            </h1>
            <p class="text-sm text-slate-500 dark:text-slate-400 mt-0.5">Kelola data seluruh staf, teknisi, dan karyawan perusahaan.</p>
        </div>
        <div class="flex items-center gap-2">
            @if(!empty($selectedIds))
                <button wire:click="bulkDelete" wire:confirm="Yakin ingin menghapus {{ count($selectedIds) }} data pegawai terpilih?"
                        class="inline-flex items-center gap-1.5 px-3.5 py-2 bg-red-600 hover:bg-red-700 text-white rounded-xl text-sm font-semibold shadow-xs transition-colors cursor-pointer">
                    <span class="material-symbols-outlined notranslate" style="font-size: 18px;" translate="no">delete</span>
                    Hapus Terpilih ({{ count($selectedIds) }})
                </button>
            @endif
            <a href="{{ route('admin.employee.create') }}"
               class="inline-flex items-center gap-2 px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-sm font-semibold shadow-xs transition-colors cursor-pointer">
                <span class="material-symbols-outlined notranslate" style="font-size: 18px;" translate="no">person_add</span>
                Tambah Pegawai
            </a>
        </div>
    </div>

    {{-- Kartu Ringkasan Statistik --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="bg-white dark:bg-slate-900 p-4 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-xs flex items-center gap-3">
            <div class="w-11 h-11 rounded-xl bg-indigo-50 dark:bg-indigo-900/40 text-indigo-600 dark:text-indigo-400 flex items-center justify-center shrink-0">
                <span class="material-symbols-outlined notranslate" translate="no">groups</span>
            </div>
            <div>
                <p class="text-xs text-slate-500 dark:text-slate-400 font-medium">Total Pegawai</p>
                <p class="text-xl font-bold text-slate-900 dark:text-slate-100">{{ number_format($stats['total'] ?? 0) }}</p>
            </div>
        </div>

        <div class="bg-white dark:bg-slate-900 p-4 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-xs flex items-center gap-3">
            <div class="w-11 h-11 rounded-xl bg-emerald-50 dark:bg-emerald-900/40 text-emerald-600 dark:text-emerald-400 flex items-center justify-center shrink-0">
                <span class="material-symbols-outlined notranslate" translate="no">check_circle</span>
            </div>
            <div>
                <p class="text-xs text-slate-500 dark:text-slate-400 font-medium">Pegawai Aktif</p>
                <p class="text-xl font-bold text-emerald-600 dark:text-emerald-400">{{ number_format($stats['active'] ?? 0) }}</p>
            </div>
        </div>

        <div class="bg-white dark:bg-slate-900 p-4 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-xs flex items-center gap-3">
            <div class="w-11 h-11 rounded-xl bg-rose-50 dark:bg-rose-900/40 text-rose-600 dark:text-rose-400 flex items-center justify-center shrink-0">
                <span class="material-symbols-outlined notranslate" translate="no">cancel</span>
            </div>
            <div>
                <p class="text-xs text-slate-500 dark:text-slate-400 font-medium">Nonaktif / Resign</p>
                <p class="text-xl font-bold text-rose-600 dark:text-rose-400">{{ number_format($stats['inactive'] ?? 0) }}</p>
            </div>
        </div>

        <div class="bg-white dark:bg-slate-900 p-4 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-xs flex items-center gap-3">
            <div class="w-11 h-11 rounded-xl bg-blue-50 dark:bg-blue-900/40 text-blue-600 dark:text-blue-400 flex items-center justify-center shrink-0">
                <span class="material-symbols-outlined notranslate" translate="no">manage_accounts</span>
            </div>
            <div>
                <p class="text-xs text-slate-500 dark:text-slate-400 font-medium">Tertaut Akun Login</p>
                <p class="text-xl font-bold text-blue-600 dark:text-blue-400">{{ number_format($stats['with_user'] ?? 0) }}</p>
            </div>
        </div>
    </div>

    {{-- Filter & Pencarian --}}
    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-4 shadow-xs flex flex-col md:flex-row items-stretch md:items-center justify-between gap-3">
        <div class="flex-1 relative">
            <span class="material-symbols-outlined notranslate absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 pointer-events-none" translate="no" style="font-size:20px">search</span>
            <input type="text" wire:model.live.debounce.300ms="search" placeholder="Cari nama, NIK, posisi, atau nomor telepon..."
                   class="w-full pl-10 pr-4 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-sm text-slate-900 dark:text-slate-100 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-indigo-500">
        </div>

        <div class="flex items-center gap-2 overflow-x-auto pb-1 md:pb-0">
            <select wire:model.live="filterDepartment"
                    class="px-3 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-sm text-slate-700 dark:text-slate-200 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                <option value="">Semua Departemen</option>
                @foreach(\App\Enums\JobFunction::cases() as $jobFunc)
                    <option value="{{ $jobFunc->value }}">{{ $jobFunc->label() }}</option>
                @endforeach
            </select>

            <select wire:model.live="filterStatus"
                    class="px-3 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-sm text-slate-700 dark:text-slate-200 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                <option value="">Semua Status</option>
                <option value="active">Aktif</option>
                <option value="inactive">Nonaktif</option>
            </select>

            @if($search || $filterStatus !== '' || $filterDepartment !== '')
                <button wire:click="resetFilters" class="p-2 text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 rounded-xl hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors" title="Reset Filter">
                    <span class="material-symbols-outlined notranslate" translate="no" style="font-size:20px">restart_alt</span>
                </button>
            @endif
        </div>
    </div>

    {{-- Tabel Pegawai --}}
    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl shadow-xs overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm whitespace-nowrap">
                <thead class="bg-slate-50 dark:bg-slate-800/60 text-slate-600 dark:text-slate-400 font-semibold border-b border-slate-200 dark:border-slate-800">
                    <tr>
                        <th class="px-4 py-3.5 w-10 text-center">
                            <input type="checkbox" wire:model.live="selectAll" class="rounded border-slate-300 text-indigo-600 shadow-xs focus:ring-indigo-500 dark:bg-slate-700 dark:border-slate-600">
                        </th>
                        <th class="px-4 py-3.5 text-center w-24">Aksi</th>
                        <th class="px-4 py-3.5">
                            <button wire:click="sortBy('nik')" class="flex items-center gap-1 hover:text-indigo-600 dark:hover:text-indigo-400 transition-colors cursor-pointer">
                                NIK
                                <span class="material-symbols-outlined notranslate" translate="no" style="font-size:14px">unfold_more</span>
                            </button>
                        </th>
                        <th class="px-4 py-3.5">
                            <button wire:click="sortBy('name')" class="flex items-center gap-1 hover:text-indigo-600 dark:hover:text-indigo-400 transition-colors cursor-pointer">
                                Nama Pegawai
                                <span class="material-symbols-outlined notranslate" translate="no" style="font-size:14px">unfold_more</span>
                            </button>
                        </th>
                        <th class="px-4 py-3.5">Jabatan & Dept</th>
                        <th class="px-4 py-3.5">Kontak</th>
                        <th class="px-4 py-3.5">Gaji Pokok</th>
                        <th class="px-4 py-3.5">Tgl Gabung</th>
                        <th class="px-4 py-3.5 text-center">Status</th>
                        <th class="px-4 py-3.5">Akun Login</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                    @forelse($employees as $employee)
                        @php
                            $isActive = $employee->status === 'active';
                        @endphp
                        <tr class="hover:bg-slate-50/80 dark:hover:bg-slate-800/40 transition-colors">
                            {{-- Checkbox --}}
                            <td class="px-4 py-3.5 text-center">
                                <input type="checkbox" wire:model.live="selectedIds" value="{{ $employee->id }}"
                                       class="rounded border-slate-300 text-indigo-600 shadow-xs focus:ring-indigo-500 dark:bg-slate-700 dark:border-slate-600">
                            </td>

                            {{-- Aksi di sisi KIRI --}}
                            <td class="px-4 py-3.5 text-center whitespace-nowrap">
                                <div class="flex items-center justify-center gap-1">
                                    <a href="{{ route('admin.employee.edit', $employee->id) }}"
                                       class="inline-flex items-center justify-center w-8 h-8 rounded-lg bg-indigo-50 hover:bg-indigo-100 dark:bg-indigo-900/30 dark:hover:bg-indigo-900/50 text-indigo-600 dark:text-indigo-400 transition-colors"
                                       title="Edit Pegawai">
                                        <span class="material-symbols-outlined notranslate" translate="no" style="font-size:18px">edit</span>
                                    </a>
                                    <button wire:click="confirmDelete({{ $employee->id }})"
                                            class="inline-flex items-center justify-center w-8 h-8 rounded-lg bg-red-50 hover:bg-red-100 dark:bg-red-900/30 dark:hover:bg-red-900/50 text-red-600 dark:text-red-400 transition-colors"
                                            title="Hapus Pegawai">
                                        <span class="material-symbols-outlined notranslate" translate="no" style="font-size:18px">delete</span>
                                    </button>
                                </div>
                            </td>

                            {{-- NIK --}}
                            <td class="px-4 py-3.5 font-mono text-xs font-semibold text-slate-700 dark:text-slate-300">
                                {{ $employee->nik }}
                            </td>

                            {{-- Nama --}}
                            <td class="px-4 py-3.5 font-medium text-slate-900 dark:text-slate-100">
                                <div class="flex items-center gap-2.5">
                                    <div class="w-8 h-8 rounded-full bg-indigo-100 dark:bg-indigo-900/50 text-indigo-600 dark:text-indigo-300 font-bold flex items-center justify-center text-xs shrink-0">
                                        {{ strtoupper(substr($employee->name, 0, 1)) }}
                                    </div>
                                    <div>
                                        <p class="font-semibold text-slate-900 dark:text-slate-100">{{ $employee->name }}</p>
                                        <p class="text-xs text-slate-500 dark:text-slate-400 font-mono">{{ $employee->email ?: '-' }}</p>
                                    </div>
                                </div>
                            </td>

                            {{-- Jabatan & Dept --}}
                            <td class="px-4 py-3.5">
                                <p class="text-slate-900 dark:text-slate-100 font-medium">{{ $employee->position ?: '-' }}</p>
                                @if($employee->department)
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-semibold uppercase bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300">
                                        {{ $employee->department }}
                                    </span>
                                @endif
                            </td>

                            {{-- Kontak --}}
                            <td class="px-4 py-3.5 text-xs font-mono text-slate-600 dark:text-slate-400">
                                {{ $employee->phone ?: '-' }}
                            </td>

                            {{-- Gaji Pokok --}}
                            <td class="px-4 py-3.5 font-mono text-xs font-semibold text-slate-800 dark:text-slate-200">
                                Rp {{ number_format($employee->base_salary, 0, ',', '.') }}
                            </td>

                            {{-- Tgl Gabung --}}
                            <td class="px-4 py-3.5 text-xs text-slate-600 dark:text-slate-400">
                                {{ $employee->join_date ? $employee->join_date->format('d/m/Y') : '-' }}
                            </td>

                            {{-- Status --}}
                            <td class="px-4 py-3.5 text-center">
                                @if($isActive)
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-emerald-50 dark:bg-emerald-900/30 text-emerald-700 dark:text-emerald-400">
                                        Aktif
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-rose-50 dark:bg-rose-900/30 text-rose-700 dark:text-rose-400">
                                        Nonaktif
                                    </span>
                                @endif
                            </td>

                            {{-- Akun Login (Keterkaitan dengan User Management) --}}
                            <td class="px-4 py-3.5">
                                @if($employee->user)
                                    <a href="{{ route('admin.users.edit', $employee->user->id) }}"
                                       class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-xs font-medium bg-blue-50 dark:bg-blue-900/30 text-blue-700 dark:text-blue-300 hover:bg-blue-100 dark:hover:bg-blue-900/50 transition-colors"
                                       title="Edit Akun Login User">
                                        <span class="material-symbols-outlined notranslate" style="font-size: 15px;" translate="no">verified_user</span>
                                        {{ $employee->user->name }}
                                    </a>
                                @else
                                    <span class="inline-flex items-center gap-1 text-xs text-slate-400 italic">
                                        <span class="material-symbols-outlined notranslate" style="font-size: 14px;" translate="no">link_off</span>
                                        Belum ditautkan
                                    </span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="10" class="px-6 py-12 text-center text-slate-500 dark:text-slate-400">
                                <div class="flex flex-col items-center justify-center">
                                    <span class="material-symbols-outlined notranslate text-slate-300 dark:text-slate-600 mb-2" style="font-size: 48px;" translate="no">person_off</span>
                                    <p class="text-base font-semibold text-slate-800 dark:text-slate-200">Belum Ada Data Pegawai</p>
                                    <p class="text-xs text-slate-400 mt-1">Silakan klik tombol "Tambah Pegawai" untuk menambahkan data baru.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($employees->hasPages())
            <div class="px-4 py-3 border-t border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-800/40">
                {{ $employees->links() }}
            </div>
        @endif
    </div>

    {{-- Modal Konfirmasi Hapus --}}
    @if($showDeleteModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/50 backdrop-blur-xs">
            <div class="bg-white dark:bg-slate-900 rounded-2xl p-6 max-w-sm w-full border border-slate-200 dark:border-slate-800 shadow-xl space-y-4">
                <div class="w-12 h-12 rounded-full bg-red-100 dark:bg-red-900/40 text-red-600 dark:text-red-400 flex items-center justify-center mx-auto">
                    <span class="material-symbols-outlined notranslate" style="font-size: 28px;" translate="no">warning</span>
                </div>
                <div class="text-center">
                    <h3 class="text-lg font-bold text-slate-900 dark:text-slate-100">Hapus Pegawai Ini?</h3>
                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">Data pegawai yang dihapus akan dipindahkan ke tempat sampah dan dapat dipulihkan jika diperlukan.</p>
                </div>
                <div class="flex items-center gap-2">
                    <button wire:click="$set('showDeleteModal', false)"
                            class="flex-1 px-4 py-2 bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 rounded-xl text-sm font-semibold transition-colors">
                        Batal
                    </button>
                    <button wire:click="deleteSingle"
                            class="flex-1 px-4 py-2 bg-red-600 hover:bg-red-700 text-white rounded-xl text-sm font-semibold shadow-xs transition-colors">
                        Ya, Hapus
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>
