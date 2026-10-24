@php
    $isTech   = request()->routeIs('technician.*');
    $p        = $payroll;
    $emp      = $p->employee;
    $isPaid   = $p->status === 'paid';

    // Hitung komponen keuangan
    $totalIncome  = (float)$p->base_salary + (float)$p->allowances;
    $totalDeduct  = (float)$p->deductions;
    $netSalary    = (float)$p->net_salary;

    // Rekap Hari & Kehadiran dari database
    $workDays    = (int)($p->work_days ?? 0);
    $lateDays    = (int)($p->late_days ?? 0);
    $absentDays  = (int)($p->absent_days ?? 0);
    $presentDays = (int)($p->present_days ?? 0);
    $sickDays    = (int)($p->sick_days ?? 0);
    $permDays    = (int)($p->permission_days ?? 0);

    // Fallback cerdas: Jika slip di-generate sebelum kolom rekap ada di database
    if ($workDays == 0 && $lateDays == 0 && $absentDays == 0) {
        if ($p->period_start && $p->period_end) {
            $periodRange = \Carbon\CarbonPeriod::create($p->period_start, $p->period_end);
            foreach ($periodRange as $day) {
                if ($day->dayOfWeek !== \Carbon\Carbon::SUNDAY) {
                    $workDays++;
                }
            }

            if ($emp?->user_id) {
                $attRows = \App\Models\Workforce\Attendance::where('technician_id', $emp->user_id)
                    ->whereBetween('date', [$p->period_start, $p->period_end])
                    ->get(['status']);

                foreach ($attRows as $row) {
                    $val = $row->status instanceof \Src\Domain\Workforce\Enums\AttendanceStatus
                        ? $row->status->value
                        : (string) $row->status;

                    match ($val) {
                        'checked_in', 'checked_out' => $presentDays++,
                        'late'                       => $lateDays++,
                        'sick'                       => $sickDays++,
                        'permission', 'leave'        => $permDays++,
                        'absent'                     => $absentDays++,
                        default                      => null,
                    };
                }
            }
        }

        // Jika di tabel attendance tidak tercatat namun ada di notes (misal: "Terlambat 3x (-Rp 75.000)")
        if ($lateDays == 0 && !empty($p->notes) && preg_match('/Terlambat\s+(\d+)x/i', $p->notes, $m)) {
            $lateDays = (int) $m[1];
        }
        if ($absentDays == 0 && !empty($p->notes) && preg_match('/Alpa\s+(\d+)x/i', $p->notes, $m)) {
            $absentDays = (int) $m[1];
        }
    }

    // Sesuai aturan perusahaan: POTONGAN HANYA JIKA ADA KASBON (Absensi tidak memotong gaji)
    $hasOldPenaltyNotes = !empty($p->notes) && (str_contains($p->notes, 'Terlambat') || str_contains($p->notes, 'Alpa')) && !str_contains(strtolower($p->notes), 'kasbon');
    $isOldLateDeduction = ($lateDays > 0 && ($lateDays * 25000 == (float)$p->deductions));

    // Jika potongan berasal dari denda absensi lama, abaikan (anggap 0)
    $kasbonDeductions = ($hasOldPenaltyNotes || $isOldLateDeduction) ? 0 : (float)$p->deductions;
    $netSalary        = max(0, $totalIncome - $kasbonDeductions);

    // Periode
    $periodLabel = ($p->period_start && $p->period_end)
        ? \Carbon\Carbon::parse($p->period_start)->translatedFormat('d M Y')
          . ' – '
          . \Carbon\Carbon::parse($p->period_end)->translatedFormat('d M Y')
        : \Carbon\Carbon::createFromDate($p->period_year, $p->period_month, 1)->translatedFormat('F Y');

    // Rekap absensi status
    $hasAttendance = $workDays > 0 || $presentDays > 0 || $lateDays > 0 || $absentDays > 0;
    $sickIzin      = $sickDays + $permDays;
    $attendancePct = $workDays > 0
        ? min(100, round(( ($presentDays + $lateDays) / $workDays ) * 100))
        : 0;
@endphp

<div class="max-w-4xl mx-auto py-6 px-4 sm:px-6 lg:px-8 print:p-0 print:max-w-full">

    {{-- Toolbar (non-print) --}}
    @if(!$isTech)
    <div class="mb-5 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3 print:hidden">
        <a href="{{ route('admin.payroll.index') }}"
           class="inline-flex items-center gap-1.5 text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-slate-200 transition-colors text-sm font-medium">
            <span class="material-symbols-outlined notranslate" style="font-size:20px">arrow_back</span>
            Kembali ke Rekap Gaji
        </a>
        <div class="flex flex-wrap gap-2">
            <button wire:click="sendWhatsApp"
                    class="inline-flex items-center gap-1.5 px-3.5 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-sm font-semibold shadow-xs transition-colors">
                <span class="material-symbols-outlined notranslate" style="font-size:18px">chat</span>
                Kirim WA
            </button>
            @if(!$isPaid)
            <a href="{{ route('admin.payroll.edit', $p->id) }}"
               class="inline-flex items-center gap-1.5 px-3.5 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-xl text-sm font-semibold shadow-xs transition-colors">
                <span class="material-symbols-outlined notranslate" style="font-size:18px">edit</span>
                Edit Slip
            </a>
            @endif
            <button onclick="window.print()"
                    class="inline-flex items-center gap-1.5 px-3.5 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-sm font-semibold shadow-xs transition-colors">
                <span class="material-symbols-outlined notranslate" style="font-size:18px">print</span>
                Cetak
            </button>
        </div>
    </div>
    @endif

    {{-- Flash messages --}}
    @if(session('success'))
    <div class="mb-4 p-3 bg-emerald-50 dark:bg-emerald-900/30 border border-emerald-200 dark:border-emerald-700 rounded-xl flex items-center gap-2 text-emerald-800 dark:text-emerald-300 text-sm print:hidden">
        <span class="material-symbols-outlined notranslate" style="font-size:18px">check_circle</span>
        {{ session('success') }}
    </div>
    @endif
    @if(session('error'))
    <div class="mb-4 p-3 bg-red-50 dark:bg-red-900/30 border border-red-200 dark:border-red-700 rounded-xl flex items-center gap-2 text-red-800 dark:text-red-300 text-sm print:hidden">
        <span class="material-symbols-outlined notranslate" style="font-size:18px">error</span>
        {{ session('error') }}
    </div>
    @endif

    {{-- ============================================================ --}}
    {{-- SLIP GAJI DOKUMEN                                            --}}
    {{-- ============================================================ --}}
    <div class="bg-white dark:bg-slate-900 shadow-xl rounded-2xl overflow-hidden print:shadow-none print:rounded-none print:bg-white">
        <div class="p-6 sm:p-10 print:p-8 text-slate-800 print:text-black">

            {{-- ── HEADER PERUSAHAAN ── --}}
            <div class="flex flex-col sm:flex-row justify-between items-start border-b-2 border-slate-800 dark:border-slate-200 print:border-black pb-5 mb-6 gap-4 print:flex-row">
                <div class="flex items-center gap-4">
                    @if(\App\Models\Setting::getValue('company.logo_url'))
                        <img src="{{ \App\Models\Setting::getValue('company.logo_url') }}" class="h-16 w-auto object-contain" alt="Logo">
                    @else
                        <div class="h-14 w-14 bg-indigo-600 print:bg-slate-800 rounded-xl flex items-center justify-center font-black text-white text-2xl shrink-0">
                            {{ strtoupper(substr(\App\Models\Setting::getValue('company.name', 'D'), 0, 1)) }}
                        </div>
                    @endif
                    <div>
                        <h2 class="text-xl sm:text-2xl font-black text-slate-900 dark:text-slate-100 print:text-black uppercase tracking-wide">
                            {{ \App\Models\Setting::getValue('company.name', 'PT. dsBilling Enterprise') }}
                        </h2>
                        <p class="text-xs text-slate-500 dark:text-slate-400 print:text-slate-700 mt-0.5">
                            {{ \App\Models\Setting::getValue('company.address', 'Alamat belum diatur') }}
                        </p>
                        <p class="text-xs text-slate-500 dark:text-slate-400 print:text-slate-700">
                            Telp: {{ \App\Models\Setting::getValue('company.phone', '-') }}
                            &nbsp;|&nbsp; Email: {{ \App\Models\Setting::getValue('company.email', '-') }}
                        </p>
                    </div>
                </div>
                <div class="text-left sm:text-right print:text-right">
                    <div class="inline-flex items-center gap-2 mb-1">
                        <h1 class="text-2xl sm:text-3xl font-black text-slate-900 dark:text-slate-100 print:text-black uppercase tracking-widest">Slip Gaji</h1>
                        @if($isPaid)
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-700 print:hidden uppercase tracking-wider">LUNAS</span>
                        @else
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-amber-100 text-amber-700 print:hidden uppercase tracking-wider">DRAFT</span>
                        @endif
                    </div>
                    <p class="text-sm font-semibold text-slate-700 dark:text-slate-300 print:text-black">Periode: {{ $periodLabel }}</p>
                    <p class="text-xs text-slate-400 dark:text-slate-500 print:text-slate-700 mt-0.5">
                        No. Slip: #{{ str_pad($p->id, 6, '0', STR_PAD_LEFT) }}
                        &nbsp;|&nbsp; Dicetak: {{ now()->translatedFormat('d M Y H:i') }}
                    </p>
                </div>
            </div>

            {{-- ── IDENTITAS KARYAWAN ── --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-x-8 gap-y-1 mb-8 text-sm print:grid-cols-2 print:text-black bg-slate-50 dark:bg-slate-800/40 print:bg-transparent rounded-xl p-4 print:p-0 print:border-b print:border-slate-300">
                <table class="w-full">
                    <tr><td class="py-1 text-slate-500 dark:text-slate-400 print:text-slate-700 w-36 font-medium">Nama Karyawan</td><td class="py-1 font-bold text-slate-900 dark:text-slate-100 print:text-black">: {{ $emp->name ?? '-' }}</td></tr>
                    <tr><td class="py-1 text-slate-500 dark:text-slate-400 print:text-slate-700 font-medium">NIK</td><td class="py-1 font-semibold text-slate-800 dark:text-slate-200 print:text-black font-mono">: {{ $emp->nik ?? '-' }}</td></tr>
                    <tr><td class="py-1 text-slate-500 dark:text-slate-400 print:text-slate-700 font-medium">Jabatan</td><td class="py-1 font-semibold text-slate-800 dark:text-slate-200 print:text-black">: {{ $emp->position ?? '-' }}</td></tr>
                </table>
                <table class="w-full">
                    <tr><td class="py-1 text-slate-500 dark:text-slate-400 print:text-slate-700 w-36 font-medium">Departemen</td><td class="py-1 font-semibold text-slate-800 dark:text-slate-200 print:text-black">: {{ $emp->department ?? '-' }}</td></tr>
                    <tr><td class="py-1 text-slate-500 dark:text-slate-400 print:text-slate-700 font-medium">Bank</td><td class="py-1 font-semibold text-slate-800 dark:text-slate-200 print:text-black">: {{ $emp->bank_name ?? 'CASH' }} {{ $emp->bank_account ? '– '.$emp->bank_account : '' }}</td></tr>
                    <tr><td class="py-1 text-slate-500 dark:text-slate-400 print:text-slate-700 font-medium">Status Pembayaran</td>
                        <td class="py-1 font-bold print:text-black">:
                            <span class="{{ $isPaid ? 'text-emerald-600' : 'text-amber-600' }} dark:{{ $isPaid ? 'text-emerald-400' : 'text-amber-400' }} uppercase">
                                {{ $isPaid ? 'SUDAH DIBAYAR' : 'BELUM DIBAYAR' }}
                            </span>
                            @if($isPaid && $p->payment_date)
                                <span class="text-slate-500 dark:text-slate-400 print:text-slate-700 font-normal text-xs">({{ \Carbon\Carbon::parse($p->payment_date)->translatedFormat('d M Y') }})</span>
                            @endif
                        </td>
                    </tr>
                </table>
            </div>

            {{-- ── REKAP KEHADIRAN ── --}}
            @if($hasAttendance)
            <div class="mb-8">
                <h3 class="text-xs font-black uppercase tracking-widest text-slate-500 dark:text-slate-400 print:text-slate-700 mb-3 flex items-center gap-2">
                    <span class="material-symbols-outlined notranslate text-indigo-500 print:hidden" style="font-size:18px">calendar_month</span>
                    Rekap Kehadiran Periode
                </h3>
                <div class="grid grid-cols-3 sm:grid-cols-6 gap-3 print:grid-cols-6">
                    {{-- Hari Kerja --}}
                    <div class="bg-slate-100 dark:bg-slate-800 print:border print:border-slate-300 rounded-xl p-3 text-center">
                        <p class="text-[10px] font-bold uppercase text-slate-500 dark:text-slate-400 print:text-slate-600 tracking-wide mb-1">Hari Kerja</p>
                        <p class="text-2xl font-black text-slate-900 dark:text-slate-100 print:text-black">{{ $workDays }}</p>
                        <p class="text-[10px] text-slate-400 print:text-slate-600">hari</p>
                    </div>
                    {{-- Hadir --}}
                    <div class="bg-emerald-50 dark:bg-emerald-900/30 print:border print:border-emerald-200 rounded-xl p-3 text-center">
                        <p class="text-[10px] font-bold uppercase text-emerald-600 dark:text-emerald-400 print:text-slate-600 tracking-wide mb-1">Hadir</p>
                        <p class="text-2xl font-black text-emerald-700 dark:text-emerald-300 print:text-black">{{ $presentDays }}</p>
                        <p class="text-[10px] text-emerald-400 print:text-slate-600">hari</p>
                    </div>
                    {{-- Terlambat --}}
                    <div class="bg-amber-50 dark:bg-amber-900/30 print:border print:border-amber-200 rounded-xl p-3 text-center">
                        <p class="text-[10px] font-bold uppercase text-amber-600 dark:text-amber-400 print:text-slate-600 tracking-wide mb-1">Terlambat</p>
                        <p class="text-2xl font-black text-amber-700 dark:text-amber-300 print:text-black">{{ $lateDays }}</p>
                        <p class="text-[10px] text-amber-400 print:text-slate-600">hari</p>
                    </div>
                    {{-- Sakit --}}
                    <div class="bg-blue-50 dark:bg-blue-900/30 print:border print:border-blue-200 rounded-xl p-3 text-center">
                        <p class="text-[10px] font-bold uppercase text-blue-600 dark:text-blue-400 print:text-slate-600 tracking-wide mb-1">Sakit</p>
                        <p class="text-2xl font-black text-blue-700 dark:text-blue-300 print:text-black">{{ $sickDays }}</p>
                        <p class="text-[10px] text-blue-400 print:text-slate-600">hari</p>
                    </div>
                    {{-- Izin --}}
                    <div class="bg-purple-50 dark:bg-purple-900/30 print:border print:border-purple-200 rounded-xl p-3 text-center">
                        <p class="text-[10px] font-bold uppercase text-purple-600 dark:text-purple-400 print:text-slate-600 tracking-wide mb-1">Izin</p>
                        <p class="text-2xl font-black text-purple-700 dark:text-purple-300 print:text-black">{{ $permDays }}</p>
                        <p class="text-[10px] text-purple-400 print:text-slate-600">hari</p>
                    </div>
                    {{-- Alpa --}}
                    <div class="bg-red-50 dark:bg-red-900/30 print:border print:border-red-200 rounded-xl p-3 text-center">
                        <p class="text-[10px] font-bold uppercase text-red-600 dark:text-red-400 print:text-slate-600 tracking-wide mb-1">Alpa</p>
                        <p class="text-2xl font-black text-red-700 dark:text-red-300 print:text-black">{{ $absentDays }}</p>
                        <p class="text-[10px] text-red-400 print:text-slate-600">hari</p>
                    </div>
                </div>
                {{-- Progress bar kehadiran --}}
                @if($attendancePct > 0)
                <div class="mt-3 flex items-center gap-3 print:hidden">
                    <div class="flex-1 h-2 bg-slate-200 dark:bg-slate-700 rounded-full overflow-hidden">
                        <div class="h-2 rounded-full {{ $attendancePct >= 90 ? 'bg-emerald-500' : ($attendancePct >= 75 ? 'bg-amber-500' : 'bg-red-500') }}"
                             style="width: {{ $attendancePct }}%"></div>
                    </div>
                    <span class="text-xs font-semibold {{ $attendancePct >= 90 ? 'text-emerald-600' : ($attendancePct >= 75 ? 'text-amber-600' : 'text-red-600') }}">
                        {{ $attendancePct }}% kehadiran
                    </span>
                </div>
                @endif
            </div>
            @endif

            {{-- ── RINCIAN GAJI ── --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-6 sm:gap-10 mb-8 print:grid-cols-2">

                {{-- A. PENDAPATAN --}}
                <div>
                    <h3 class="font-black text-slate-800 dark:text-slate-200 print:text-black border-b-2 border-slate-300 dark:border-slate-600 print:border-black pb-2 mb-3 tracking-wide text-sm uppercase">
                        A. Pendapatan
                    </h3>
                    <table class="w-full text-sm print:text-black">
                        <tr>
                            <td class="py-1.5 text-slate-600 dark:text-slate-400 print:text-slate-800">Gaji Pokok</td>
                            <td class="py-1.5 text-right font-semibold text-slate-800 dark:text-slate-200 print:text-black font-mono">
                                Rp {{ number_format($p->base_salary, 0, ',', '.') }}
                            </td>
                        </tr>
                        <tr>
                            <td class="py-1.5 text-slate-600 dark:text-slate-400 print:text-slate-800">Tunjangan / Bonus</td>
                            <td class="py-1.5 text-right font-semibold text-slate-800 dark:text-slate-200 print:text-black font-mono">
                                Rp {{ number_format($p->allowances, 0, ',', '.') }}
                            </td>
                        </tr>
                    </table>
                    <div class="border-t border-slate-300 dark:border-slate-600 print:border-black mt-3 pt-2">
                        <table class="w-full text-sm print:text-black">
                            <tr>
                                <td class="py-1 font-bold text-slate-800 dark:text-slate-200 print:text-black">Total Pendapatan</td>
                                <td class="py-1 text-right font-black text-emerald-600 dark:text-emerald-400 print:text-black font-mono">
                                    Rp {{ number_format($totalIncome, 0, ',', '.') }}
                                </td>
                            </tr>
                        </table>
                    </div>
                </div>

                {{-- B. POTONGAN --}}
                <div>
                    <h3 class="font-black text-slate-800 dark:text-slate-200 print:text-black border-b-2 border-slate-300 dark:border-slate-600 print:border-black pb-2 mb-3 tracking-wide text-sm uppercase">
                        B. Potongan
                    </h3>
                    <table class="w-full text-sm print:text-black">
                        {{-- Potongan HANYA untuk Kasbon / Pinjaman Karyawan --}}
                        @if($kasbonDeductions > 0)
                        <tr>
                            <td class="py-1.5 text-slate-600 dark:text-slate-400 print:text-slate-800">
                                Kasbon / Pinjaman
                                @if(!empty($p->notes))
                                    <span class="block text-xs text-slate-400 print:text-slate-600 italic">({{ $p->notes }})</span>
                                @endif
                            </td>
                            <td class="py-1.5 text-right font-semibold text-red-600 dark:text-red-400 print:text-black font-mono">
                                – Rp {{ number_format($kasbonDeductions, 0, ',', '.') }}
                            </td>
                        </tr>
                        @else
                        <tr>
                            <td colspan="2" class="py-1.5 text-slate-400 dark:text-slate-500 print:text-slate-600 italic text-sm">Tidak ada potongan kasbon</td>
                        </tr>
                        @endif
                    </table>
                    <div class="border-t border-slate-300 dark:border-slate-600 print:border-black mt-3 pt-2">
                        <table class="w-full text-sm print:text-black">
                            <tr>
                                <td class="py-1 font-bold text-slate-800 dark:text-slate-200 print:text-black">Total Potongan</td>
                                <td class="py-1 text-right font-black text-red-600 dark:text-red-400 print:text-black font-mono">
                                    – Rp {{ number_format($kasbonDeductions, 0, ',', '.') }}
                                </td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>

            {{-- ── KALKULASI FINAL ── --}}
            <div class="mb-3 print:hidden">
                <h3 class="text-[10px] font-black uppercase tracking-widest text-slate-400 dark:text-slate-500 mb-2">Formula Perhitungan</h3>
                <div class="flex flex-wrap items-center gap-2 text-sm font-mono">
                    <span class="px-3 py-1.5 bg-emerald-50 dark:bg-emerald-900/30 text-emerald-700 dark:text-emerald-400 rounded-lg font-bold">
                        Rp {{ number_format($totalIncome, 0, ',', '.') }}
                    </span>
                    <span class="text-slate-400 font-bold">−</span>
                    <span class="px-3 py-1.5 bg-red-50 dark:bg-red-900/30 text-red-700 dark:text-red-400 rounded-lg font-bold">
                        Rp {{ number_format($kasbonDeductions, 0, ',', '.') }}
                    </span>
                    <span class="text-slate-400 font-bold">=</span>
                    <span class="px-3 py-1.5 bg-indigo-50 dark:bg-indigo-900/30 text-indigo-700 dark:text-indigo-400 rounded-lg font-black text-base">
                        Rp {{ number_format($netSalary, 0, ',', '.') }}
                    </span>
                </div>
            </div>

            {{-- ── TOTAL BERSIH ── --}}
            <div class="bg-gradient-to-r from-indigo-600 to-indigo-700 print:bg-transparent print:border-2 print:border-black print:rounded-none rounded-2xl p-5 sm:p-6 mb-8 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3">
                <div>
                    <p class="text-indigo-200 print:text-slate-700 text-xs font-bold uppercase tracking-widest">Penerimaan Bersih (A − B)</p>
                    <p class="text-white print:text-black text-sm mt-1 font-medium">
                        Ditransfer ke: <strong>{{ $emp->bank_name ?? 'CASH' }}</strong>
                        @if($emp->bank_account) – {{ $emp->bank_account }} @endif
                    </p>
                </div>
                <div class="text-3xl sm:text-4xl font-black text-white print:text-black tracking-tight">
                    Rp {{ number_format($netSalary, 0, ',', '.') }}
                </div>
            </div>

            {{-- ── CATATAN ── --}}
            @php
                $cleanNotes = $p->notes;
                if (!empty($cleanNotes)) {
                    $cleanNotes = preg_replace('/Terlambat\s+\d+x(\s*\(-Rp\s*[\d\.,]+\))?/i', '', $cleanNotes);
                    $cleanNotes = preg_replace('/Alpa\s+\d+x(\s*\(-Rp\s*[\d\.,]+\))?/i', '', $cleanNotes);
                    $cleanNotes = trim(trim($cleanNotes, " \t\n\r\0\x0B|,-"));
                }
            @endphp
            @if(!empty($cleanNotes))
            <div class="mb-8 p-4 bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-800 print:border print:border-slate-300 rounded-xl print:bg-transparent">
                <p class="text-xs font-bold uppercase tracking-wider text-amber-700 dark:text-amber-400 print:text-slate-700 mb-1">Catatan</p>
                <p class="text-sm text-amber-800 dark:text-amber-300 print:text-slate-800">{{ $cleanNotes }}</p>
            </div>
            @endif

            {{-- ── TANDA TANGAN ── --}}
            <div class="flex flex-col sm:flex-row justify-between gap-6 mt-12 text-sm print:flex-row print:text-black">
                <div class="text-center flex-1">
                    <p class="text-slate-600 dark:text-slate-400 print:text-slate-800 mb-16">Penerima Gaji,</p>
                    <div class="border-b-2 border-slate-400 dark:border-slate-600 print:border-black mx-4 mb-1"></div>
                    <p class="font-bold text-slate-900 dark:text-slate-100 print:text-black">{{ $emp->name ?? '.............................' }}</p>
                    <p class="text-xs text-slate-500 dark:text-slate-400 print:text-slate-700">{{ $emp->position ?? 'Karyawan' }}</p>
                </div>
                <div class="text-center flex-1">
                    <p class="text-slate-600 dark:text-slate-400 print:text-slate-800 mb-16">Mengetahui / Menyetujui,</p>
                    <div class="border-b-2 border-slate-400 dark:border-slate-600 print:border-black mx-4 mb-1"></div>
                    <p class="font-bold text-slate-900 dark:text-slate-100 print:text-black">
                        {{ \App\Models\Setting::getValue('company.signature_name', 'HR / Finance') }}
                    </p>
                    <p class="text-xs text-slate-500 dark:text-slate-400 print:text-slate-700">
                        {{ \App\Models\Setting::getValue('company.signature_title', 'Manajemen') }}
                    </p>
                </div>
            </div>

            {{-- Footer --}}
            <div class="mt-8 pt-4 border-t border-slate-200 dark:border-slate-700 print:border-slate-300 text-center text-[10px] text-slate-400 dark:text-slate-500 print:text-slate-600">
                Dokumen ini digenerate otomatis oleh sistem {{ \App\Models\Setting::getValue('company.name', 'dsBilling') }} &bull; Sah tanpa tanda cap &bull; No. #{{ str_pad($p->id, 6, '0', STR_PAD_LEFT) }}
            </div>

        </div>{{-- /padding --}}
    </div>{{-- /card --}}
</div>
