@php
    $bulanIndo = [
        1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
        5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
        9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
    ];
@endphp

<div class="space-y-6 pb-12">
    {{-- Header & Tombol Generate --}}
    <div class="flex flex-col sm:flex-row justify-between sm:items-center gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-900 dark:text-slate-100 flex items-center gap-2">
                <span class="material-symbols-outlined text-indigo-600 dark:text-indigo-400" style="font-size: 28px;">payments</span>
                Rekap Gaji / Payroll
            </h1>
            <p class="text-sm text-slate-500 dark:text-slate-400 mt-0.5">Kelola slip gaji, perhitungan tunjangan, potongan, dan status pembayaran gaji staf.</p>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('admin.payroll.generate') }}"
               class="inline-flex items-center gap-2 px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-sm font-semibold shadow-xs transition-colors cursor-pointer">
                <span class="material-symbols-outlined notranslate" style="font-size: 18px;" translate="no">receipt_long</span>
                Generate Gaji Periode
            </a>
        </div>
    </div>

    {{-- Kartu Ringkasan Statistik --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="bg-white dark:bg-slate-900 p-4 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-xs flex items-center gap-3">
            <div class="w-11 h-11 rounded-xl bg-indigo-50 dark:bg-indigo-900/40 text-indigo-600 dark:text-indigo-400 flex items-center justify-center shrink-0">
                <span class="material-symbols-outlined notranslate" translate="no">account_balance_wallet</span>
            </div>
            <div>
                <p class="text-xs text-slate-500 dark:text-slate-400 font-medium">Total Beban Gaji</p>
                <p class="text-xl font-bold text-slate-900 dark:text-slate-100 font-mono">Rp {{ number_format($stats['total_net'] ?? 0, 0, ',', '.') }}</p>
            </div>
        </div>

        <div class="bg-white dark:bg-slate-900 p-4 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-xs flex items-center gap-3">
            <div class="w-11 h-11 rounded-xl bg-emerald-50 dark:bg-emerald-900/40 text-emerald-600 dark:text-emerald-400 flex items-center justify-center shrink-0">
                <span class="material-symbols-outlined notranslate" translate="no">paid</span>
            </div>
            <div>
                <p class="text-xs text-slate-500 dark:text-slate-400 font-medium">Sudah Dibayar</p>
                <p class="text-xl font-bold text-emerald-600 dark:text-emerald-400 font-mono">Rp {{ number_format($stats['paid_net'] ?? 0, 0, ',', '.') }}</p>
            </div>
        </div>

        <div class="bg-white dark:bg-slate-900 p-4 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-xs flex items-center gap-3">
            <div class="w-11 h-11 rounded-xl bg-amber-50 dark:bg-amber-900/40 text-amber-600 dark:text-amber-400 flex items-center justify-center shrink-0">
                <span class="material-symbols-outlined notranslate" translate="no">pending</span>
            </div>
            <div>
                <p class="text-xs text-slate-500 dark:text-slate-400 font-medium">Belum Dibayar</p>
                <p class="text-xl font-bold text-amber-600 dark:text-amber-400 font-mono">Rp {{ number_format($stats['unpaid_net'] ?? 0, 0, ',', '.') }}</p>
            </div>
        </div>

        <div class="bg-white dark:bg-slate-900 p-4 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-xs flex items-center gap-3">
            <div class="w-11 h-11 rounded-xl bg-blue-50 dark:bg-blue-900/40 text-blue-600 dark:text-blue-400 flex items-center justify-center shrink-0">
                <span class="material-symbols-outlined notranslate" translate="no">badge</span>
            </div>
            <div>
                <p class="text-xs text-slate-500 dark:text-slate-400 font-medium">Total Slip Gaji</p>
                <p class="text-xl font-bold text-blue-600 dark:text-blue-400">{{ number_format($stats['total_slip'] ?? 0) }} Staf</p>
            </div>
        </div>
    </div>

    {{-- Filter Bar --}}
    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-4 shadow-xs flex flex-col md:flex-row items-stretch md:items-center justify-between gap-3">
        <div class="flex items-center gap-2 overflow-x-auto pb-1 md:pb-0 flex-wrap">
            <div class="flex items-center gap-1.5">
                <label class="text-xs font-bold text-slate-500 dark:text-slate-400 uppercase">Bulan:</label>
                <select wire:model.live="month"
                        class="px-3 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-sm text-slate-700 dark:text-slate-200 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    @foreach($bulanIndo as $mNum => $mName)
                        <option value="{{ $mNum }}">{{ $mName }}</option>
                    @endforeach
                </select>
            </div>

            <div class="flex items-center gap-1.5">
                <label class="text-xs font-bold text-slate-500 dark:text-slate-400 uppercase">Tahun:</label>
                <select wire:model.live="year"
                        class="px-3 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-sm text-slate-700 dark:text-slate-200 focus:outline-none focus:ring-2 focus:ring-indigo-500 font-mono">
                    @for($y = (int)date('Y') - 3; $y <= (int)date('Y') + 1; $y++)
                        <option value="{{ $y }}">{{ $y }}</option>
                    @endfor
                </select>
            </div>

            <select wire:model.live="filterStatus"
                    class="px-3 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-sm text-slate-700 dark:text-slate-200 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                <option value="">Semua Status</option>
                <option value="draft">Draft</option>
                <option value="paid">Sudah Dibayar</option>
            </select>
        </div>

        <div class="w-full md:w-72 relative">
            <span class="material-symbols-outlined notranslate absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 pointer-events-none" translate="no" style="font-size:20px">search</span>
            <input type="text" wire:model.live.debounce.300ms="search" placeholder="Cari nama atau NIK..."
                   class="w-full pl-10 pr-4 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-sm text-slate-900 dark:text-slate-100 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-indigo-500">
        </div>
    </div>

    {{-- Tabel Gaji --}}
    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl shadow-xs overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm whitespace-nowrap">
                <thead class="bg-slate-50 dark:bg-slate-800/60 text-slate-600 dark:text-slate-400 font-semibold border-b border-slate-200 dark:border-slate-800">
                    <tr>
                        <th class="px-4 py-3.5 text-center w-28">Aksi</th>
                        <th class="px-4 py-3.5">Pegawai</th>
                        <th class="px-4 py-3.5">Periode</th>
                        {{-- Rekap Absensi --}}
                        <th class="px-4 py-3.5 text-center" title="Total hari kerja">Hari Kerja</th>
                        <th class="px-4 py-3.5 text-center" title="Hari hadir (masuk)">Hadir</th>
                        <th class="px-4 py-3.5 text-center" title="Hari terlambat">Terlambat</th>
                        <th class="px-4 py-3.5 text-center" title="Sakit + Izin">Sakit/Izin</th>
                        <th class="px-4 py-3.5 text-center" title="Alpa / Tidak Hadir">Alpa</th>
                        {{-- Keuangan --}}
                        <th class="px-4 py-3.5">Gaji Pokok</th>
                        <th class="px-4 py-3.5">Tunjangan</th>
                        <th class="px-4 py-3.5">Potongan</th>
                        <th class="px-4 py-3.5">Gaji Bersih (Net)</th>
                        <th class="px-4 py-3.5 text-center">Status</th>
                        <th class="px-4 py-3.5">Tgl Pembayaran</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                    @forelse($payrolls as $payroll)
                        @php
                            $emp = $payroll->employee;
                            $isPaid = $payroll->status === 'paid';
                            $namaBulan = $bulanIndo[$payroll->period_month] ?? $payroll->period_month;
                        @endphp
                        <tr class="hover:bg-slate-50/80 dark:hover:bg-slate-800/40 transition-colors">
                            {{-- Aksi di sisi KIRI --}}
                            <td class="px-4 py-3.5 text-center whitespace-nowrap">
                                <div class="flex items-center justify-center gap-1">
                                    {{-- Lihat Slip Gaji --}}
                                    <a href="{{ route('admin.payroll.show', $payroll->id) }}"
                                       class="inline-flex items-center justify-center w-8 h-8 rounded-lg bg-indigo-50 hover:bg-indigo-100 dark:bg-indigo-900/30 dark:hover:bg-indigo-900/50 text-indigo-600 dark:text-indigo-400 transition-colors"
                                       title="Lihat Slip Gaji">
                                        <span class="material-symbols-outlined notranslate" translate="no" style="font-size:18px">description</span>
                                    </a>

                                    {{-- Tombol Bayar / Mark Paid --}}
                                    @if(!$isPaid)
                                        <button wire:click="markAsPaid({{ $payroll->id }})" wire:confirm="Tandai slip gaji ini sudah dibayar?"
                                                class="inline-flex items-center justify-center w-8 h-8 rounded-lg bg-emerald-50 hover:bg-emerald-100 dark:bg-emerald-900/30 dark:hover:bg-emerald-900/50 text-emerald-600 dark:text-emerald-400 transition-colors"
                                                title="Tandai Sudah Dibayar">
                                            <span class="material-symbols-outlined notranslate" translate="no" style="font-size:18px">check_circle</span>
                                        </button>
                                        <a href="{{ route('admin.payroll.edit', $payroll->id) }}"
                                           class="inline-flex items-center justify-center w-8 h-8 rounded-lg bg-blue-50 hover:bg-blue-100 dark:bg-blue-900/30 dark:hover:bg-blue-900/50 text-blue-600 dark:text-blue-400 transition-colors"
                                           title="Edit Gaji">
                                            <span class="material-symbols-outlined notranslate" translate="no" style="font-size:18px">edit</span>
                                        </a>
                                    @endif

                                    {{-- Hapus --}}
                                    <button wire:click="confirmDelete({{ $payroll->id }})"
                                            class="inline-flex items-center justify-center w-8 h-8 rounded-lg bg-red-50 hover:bg-red-100 dark:bg-red-900/30 dark:hover:bg-red-900/50 text-red-600 dark:text-red-400 transition-colors"
                                            title="Hapus Slip">
                                        <span class="material-symbols-outlined notranslate" translate="no" style="font-size:18px">delete</span>
                                    </button>
                                </div>
                            </td>

                            {{-- Pegawai --}}
                            <td class="px-4 py-3.5">
                                <div class="flex items-center gap-2.5">
                                    <div class="w-8 h-8 rounded-full bg-indigo-100 dark:bg-indigo-900/50 text-indigo-600 dark:text-indigo-300 font-bold flex items-center justify-center text-xs shrink-0">
                                        {{ strtoupper(substr($emp?->name ?? 'U', 0, 1)) }}
                                    </div>
                                    <div>
                                        <p class="font-semibold text-slate-900 dark:text-slate-100">{{ $emp?->name ?? 'Pegawai Terhapus' }}</p>
                                        <p class="text-xs text-slate-500 dark:text-slate-400 font-mono">{{ $emp?->nik ?: '-' }} • {{ $emp?->position ?: 'Staf' }}</p>
                                    </div>
                                </div>
                            </td>

                            {{-- Periode --}}
                            <td class="px-4 py-3.5 font-medium text-slate-800 dark:text-slate-200 whitespace-nowrap">
                                {{ $namaBulan }} {{ $payroll->period_year }}
                                @if($payroll->period_start && $payroll->period_end)
                                    <br><span class="text-[10px] text-slate-400 font-mono">
                                        {{ $payroll->period_start->format('d/m') }} – {{ $payroll->period_end->format('d/m') }}
                                    </span>
                                @endif
                            </td>

                            {{-- Rekap Absensi: Hari Kerja --}}
                            <td class="px-4 py-3.5 text-center">
                                <span class="inline-flex items-center justify-center min-w-[28px] h-7 px-2 rounded-lg bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 text-xs font-bold font-mono">
                                    {{ $payroll->work_days ?? '-' }}
                                </span>
                            </td>

                            {{-- Hadir --}}
                            <td class="px-4 py-3.5 text-center">
                                <span class="inline-flex items-center justify-center min-w-[28px] h-7 px-2 rounded-lg bg-emerald-50 dark:bg-emerald-900/30 text-emerald-700 dark:text-emerald-400 text-xs font-bold font-mono">
                                    {{ $payroll->present_days ?? 0 }}
                                </span>
                            </td>

                            {{-- Terlambat --}}
                            <td class="px-4 py-3.5 text-center">
                                @if(($payroll->late_days ?? 0) > 0)
                                    <span class="inline-flex items-center justify-center min-w-[28px] h-7 px-2 rounded-lg bg-amber-50 dark:bg-amber-900/30 text-amber-700 dark:text-amber-400 text-xs font-bold font-mono">
                                        {{ $payroll->late_days }}
                                    </span>
                                @else
                                    <span class="text-slate-300 dark:text-slate-600 text-xs">0</span>
                                @endif
                            </td>

                            {{-- Sakit/Izin --}}
                            <td class="px-4 py-3.5 text-center">
                                @php $sickIzin = ($payroll->sick_days ?? 0) + ($payroll->permission_days ?? 0); @endphp
                                @if($sickIzin > 0)
                                    <span class="inline-flex items-center justify-center min-w-[28px] h-7 px-2 rounded-lg bg-purple-50 dark:bg-purple-900/30 text-purple-700 dark:text-purple-400 text-xs font-bold font-mono">
                                        {{ $sickIzin }}
                                    </span>
                                @else
                                    <span class="text-slate-300 dark:text-slate-600 text-xs">0</span>
                                @endif
                            </td>

                            {{-- Alpa --}}
                            <td class="px-4 py-3.5 text-center">
                                @if(($payroll->absent_days ?? 0) > 0)
                                    <span class="inline-flex items-center justify-center min-w-[28px] h-7 px-2 rounded-lg bg-red-50 dark:bg-red-900/30 text-red-700 dark:text-red-400 text-xs font-bold font-mono">
                                        {{ $payroll->absent_days }}
                                    </span>
                                @else
                                    <span class="text-slate-300 dark:text-slate-600 text-xs">0</span>
                                @endif
                            </td>

                            {{-- Gaji Pokok --}}
                            <td class="px-4 py-3.5 font-mono text-xs text-slate-700 dark:text-slate-300">
                                Rp {{ number_format($payroll->base_salary, 0, ',', '.') }}
                            </td>

                            {{-- Tunjangan --}}
                            <td class="px-4 py-3.5 font-mono text-xs text-emerald-600 dark:text-emerald-400">
                                + Rp {{ number_format($payroll->allowances ?? 0, 0, ',', '.') }}
                            </td>

                            {{-- Potongan --}}
                            <td class="px-4 py-3.5 font-mono text-xs text-rose-600 dark:text-rose-400">
                                - Rp {{ number_format($payroll->deductions ?? 0, 0, ',', '.') }}
                            </td>

                            {{-- Gaji Bersih (Net) --}}
                            <td class="px-4 py-3.5 font-mono text-xs font-bold text-indigo-600 dark:text-indigo-400">
                                Rp {{ number_format($payroll->net_salary, 0, ',', '.') }}
                            </td>

                            {{-- Status --}}
                            <td class="px-4 py-3.5 text-center">
                                @if($isPaid)
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-emerald-50 dark:bg-emerald-900/30 text-emerald-700 dark:text-emerald-400">
                                        Lunas
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-amber-50 dark:bg-amber-900/30 text-amber-700 dark:text-amber-400">
                                        Draft / Pending
                                    </span>
                                @endif
                            </td>

                            {{-- Tgl Bayar --}}
                            <td class="px-4 py-3.5 text-xs text-slate-600 dark:text-slate-400 font-mono">
                                {{ $payroll->payment_date ? $payroll->payment_date->format('d/m/Y') : '-' }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="14" class="px-6 py-12 text-center text-slate-500 dark:text-slate-400">
                                <div class="flex flex-col items-center justify-center">
                                    <span class="material-symbols-outlined notranslate text-slate-300 dark:text-slate-600 mb-2" style="font-size: 48px;" translate="no">receipt_long</span>
                                    <p class="text-base font-semibold text-slate-800 dark:text-slate-200">Belum Ada Slip Gaji</p>
                                    <p class="text-xs text-slate-400 mt-1">Belum ada slip gaji yang dibuat untuk periode {{ $bulanIndo[$month] ?? $month }} {{ $year }}.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($payrolls->hasPages())
            <div class="px-4 py-3 border-t border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-800/40">
                {{ $payrolls->links() }}
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
                    <h3 class="text-lg font-bold text-slate-900 dark:text-slate-100">Hapus Slip Gaji?</h3>
                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">Data slip gaji ini akan dihapus permanen. Anda dapat men-generate ulang jika dibutuhkan.</p>
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
