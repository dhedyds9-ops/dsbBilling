@php
    $isTech = request()->routeIs('technician.*');
    $rowClass = $isTech ? 'flex-col' : 'flex-col md:flex-row';
    $gridClass = $isTech ? 'grid-cols-1' : 'grid-cols-1 md:grid-cols-2';
    $textRightClass = $isTech ? 'text-left mt-4' : 'text-left md:text-right w-full md:w-auto';
    $paddingClass = $isTech ? 'p-4' : 'p-4 sm:p-8';
@endphp
<div class="max-w-4xl mx-auto py-6 sm:px-6 lg:px-8">
    @if(!request()->routeIs('technician.*'))
    <div class="mb-4 flex flex-col sm:flex-row justify-between items-start sm:items-center print:hidden gap-3">
        <a href="{{ route('admin.payroll.index') }}" class="text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-slate-200 inline-flex items-center gap-1.5 transition-colors">
            <span class="material-symbols-outlined notranslate" style="font-size:20px">arrow_back</span> Kembali ke Penggajian
        </a>
        <div class="flex flex-wrap gap-2">
            <button wire:click="sendWhatsApp" class="bg-emerald-600 hover:bg-emerald-700 text-white font-medium py-1.5 px-3 rounded-lg flex items-center gap-1.5 text-sm transition-colors shadow-sm">
                <span class="material-symbols-outlined notranslate" style="font-size:18px">chat</span>
                Kirim via WA
            </button>
            <button onclick="window.print()" class="bg-indigo-600 hover:bg-indigo-700 text-white font-medium py-1.5 px-3 rounded-lg flex items-center gap-1.5 text-sm transition-colors shadow-sm">
                <span class="material-symbols-outlined notranslate" style="font-size:18px">print</span>
                Cetak Dokumen
            </button>
        </div>
    </div>
    @endif

    @if(session('success'))
    <div class="mb-4 p-3 bg-emerald-50 dark:bg-emerald-900/30 border border-emerald-200 dark:border-emerald-800 rounded-lg flex items-center gap-2 text-emerald-800 dark:text-emerald-300 text-sm print:hidden shadow-sm">
        <span class="material-symbols-outlined notranslate" style="font-size:18px">check_circle</span>
        {{ session('success') }}
    </div>
    @endif
    @if(session('error'))
    <div class="mb-4 p-3 bg-red-50 dark:bg-red-900/30 border border-red-200 dark:border-red-800 rounded-lg flex items-center gap-2 text-red-800 dark:text-red-300 text-sm print:hidden shadow-sm">
        <span class="material-symbols-outlined notranslate" style="font-size:18px">error</span>
        {{ session('error') }}
    </div>
    @endif

    <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg print:shadow-none print:rounded-none">
        <div class="{{ $paddingClass }} print:p-0 text-slate-800">
            <!-- Header Perusahaan -->
            <div class="flex {{ $rowClass }} justify-between items-start border-b-2 border-slate-800 pb-4 mb-6 print:flex-row print:border-black gap-4">
                <div class="flex items-center gap-4">
                    @if(\App\Models\Setting::getValue('company.logo_url'))
                    <img src="{{ \App\Models\Setting::getValue('company.logo_url') }}" class="h-16 w-auto object-contain" alt="Logo">
                    @else
                    <div class="h-14 w-14 bg-slate-200 print:bg-slate-300 rounded-lg flex items-center justify-center font-bold text-slate-500 text-xl print:border print:border-black">{{ substr(\App\Models\Setting::getValue('company.name', 'dsBilling'), 0, 1) }}</div>
                    @endif
                    <div>
                        <h2 class="text-xl sm:text-2xl font-black text-slate-900 print:text-black uppercase tracking-wide">{{ \App\Models\Setting::getValue('company.name', 'PT. dsBilling Enterprise') }}</h2>
                        <p class="text-[13px] sm:text-sm text-slate-600 print:text-black">{{ \App\Models\Setting::getValue('company.address', 'Alamat belum diatur') }}</p>
                        <p class="text-[13px] sm:text-sm text-slate-600 print:text-black">Telp: {{ \App\Models\Setting::getValue('company.phone', '-') }} | Email: {{ \App\Models\Setting::getValue('company.email', '-') }}</p>
                    </div>
                </div>
                <div class="{{ $textRightClass }} print:text-right print:w-auto">
                    <h1 class="text-2xl sm:text-3xl font-black text-slate-900 print:text-black tracking-wider uppercase">Slip Gaji</h1>
                    <p class="text-[13px] sm:text-sm font-semibold text-slate-700 print:text-black mt-1">
                        Periode: 
                        @if($payroll->period_start && $payroll->period_end)
                            {{ \Carbon\Carbon::parse($payroll->period_start)->translatedFormat('d M Y') }} - {{ \Carbon\Carbon::parse($payroll->period_end)->translatedFormat('d M Y') }}
                        @else
                            {{ \Carbon\Carbon::createFromDate($payroll->period_year, $payroll->period_month, 1)->translatedFormat('F Y') }}
                        @endif
                    </p>
                    <p class="text-xs text-slate-500 print:text-slate-700 mt-0.5">Dicetak: {{ now()->translatedFormat('d M Y H:i') }}</p>
                </div>
            </div>

            <!-- Detail Karyawan -->
            <div class="grid {{ $gridClass }} gap-4 mb-8 text-[13px] md:text-sm print:grid-cols-2 print:text-black">
                <div>
                    <table class="w-full">
                        <tr><td class="py-1 text-slate-500 print:text-slate-800 w-28 md:w-32 font-medium">Nama Karyawan</td><td class="py-1 font-bold text-slate-900 print:text-black">: {{ $payroll->employee->name ?? '-' }}</td></tr>
                        <tr><td class="py-1 text-slate-500 print:text-slate-800 font-medium">ID / NIK</td><td class="py-1 font-semibold text-slate-800 print:text-black">: {{ $payroll->employee->nik ?? '-' }}</td></tr>
                        <tr><td class="py-1 text-slate-500 print:text-slate-800 font-medium">Jabatan</td><td class="py-1 font-semibold text-slate-800 print:text-black">: {{ $payroll->employee->position ?? '-' }}</td></tr>
                    </table>
                </div>
                <div>
                    <table class="w-full">
                        <tr><td class="py-1 text-slate-500 print:text-slate-800 w-28 md:w-32 font-medium">Departemen</td><td class="py-1 font-semibold text-slate-800 print:text-black">: {{ $payroll->employee->department ?? '-' }}</td></tr>
                        <tr><td class="py-1 text-slate-500 print:text-slate-800 font-medium">Status</td><td class="py-1 font-bold text-slate-800 print:text-black uppercase">: {{ $payroll->status }}</td></tr>
                        <tr><td class="py-1 text-slate-500 print:text-slate-800 font-medium">Pembayaran</td><td class="py-1 font-semibold text-slate-800 print:text-black">: {{ $payroll->payment_date ? \Carbon\Carbon::parse($payroll->payment_date)->translatedFormat('d F Y') : '-' }}</td></tr>
                    </table>
                </div>
            </div>

            <!-- Rincian Gaji -->
            <div class="grid {{ $gridClass }} gap-8 mb-8 print:grid-cols-2">
                <!-- Pendapatan -->
                <div>
                    <h3 class="font-bold text-slate-800 print:text-black border-b border-slate-300 print:border-black pb-2 mb-3 tracking-wide">A. PENDAPATAN</h3>
                    <table class="w-full text-[13px] sm:text-sm print:text-black">
                        <tr>
                            <td class="py-1.5 text-slate-700 print:text-black">Gaji Pokok</td>
                            <td class="py-1.5 text-right font-semibold text-slate-800 print:text-black">Rp {{ number_format($payroll->base_salary, 0, ',', '.') }}</td>
                        </tr>
                        <tr>
                            <td class="py-1.5 text-slate-700 print:text-black">Tunjangan / Lainnya</td>
                            <td class="py-1.5 text-right font-semibold text-slate-800 print:text-black">Rp {{ number_format($payroll->allowances, 0, ',', '.') }}</td>
                        </tr>
                    </table>
                    <div class="border-t border-slate-300 print:border-black mt-3 pt-2">
                        <table class="w-full text-sm font-bold print:text-black">
                            <tr>
                                <td class="py-1 text-slate-800 print:text-black">Total Pendapatan</td>
                                <td class="py-1 text-right text-emerald-700 print:text-black">Rp {{ number_format($payroll->base_salary + $payroll->allowances, 0, ',', '.') }}</td>
                            </tr>
                        </table>
                    </div>
                </div>

                <!-- Potongan -->
                <div>
                    <h3 class="font-bold text-slate-800 print:text-black border-b border-slate-300 print:border-black pb-2 mb-3 tracking-wide">B. POTONGAN</h3>
                    <table class="w-full text-[13px] sm:text-sm print:text-black">
                        <tr>
                            <td class="py-1.5 text-slate-700 print:text-black">Potongan / Pinjaman</td>
                            <td class="py-1.5 text-right font-semibold text-slate-800 print:text-black">Rp {{ number_format($payroll->deductions, 0, ',', '.') }}</td>
                        </tr>
                    </table>
                    <div class="border-t border-slate-300 print:border-black mt-3 pt-2">
                        <table class="w-full text-sm font-bold print:text-black">
                            <tr>
                                <td class="py-1 text-slate-800 print:text-black">Total Potongan</td>
                                <td class="py-1 text-right text-red-600 print:text-black">Rp {{ number_format($payroll->deductions, 0, ',', '.') }}</td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Total -->
            <div class="bg-slate-50 border border-slate-200 rounded-lg p-5 mb-8 flex {{ $rowClass }} justify-between sm:items-center print:bg-transparent print:border-t-2 print:border-b-2 print:border-l-0 print:border-r-0 print:border-black print:rounded-none">
                <div class="mb-3 sm:mb-0">
                    <p class="text-sm font-bold text-slate-700 print:text-black tracking-widest">PENERIMAAN BERSIH (A - B)</p>
                    <p class="text-xs text-slate-500 print:text-slate-800 mt-1 italic">
                        Ditransfer ke: <strong>{{ $payroll->employee->bank_name ?? 'CASH' }}</strong> - {{ $payroll->employee->bank_account ?? '' }}
                    </p>
                </div>
                <div class="text-2xl sm:text-3xl font-black text-slate-900 print:text-black">
                    Rp {{ number_format($payroll->net_salary, 0, ',', '.') }}
                </div>
            </div>

            <!-- Notes & Signatures -->
            <div class="flex {{ $rowClass }} justify-between mt-12 text-[13px] sm:text-sm print:flex-row print:text-black">
                <div class="w-full {{ $isTech ? '' : 'sm:w-1/3' }} mb-8 sm:mb-0 print:mb-0 print:w-1/3">
                    <p class="mb-1 font-bold text-slate-800 print:text-black">Catatan Tambahan:</p>
                    <p class="text-slate-600 print:text-slate-800 italic">{{ $payroll->notes ?: '-' }}</p>
                </div>
                <div class="flex w-full {{ $isTech ? '' : 'sm:w-2/3' }} justify-around print:w-2/3">
                    <div class="text-center">
                        <p class="mb-20 text-slate-700 print:text-black">Penerima,</p>
                        <p class="font-bold border-b border-slate-400 print:border-black inline-block px-4 pb-1 text-slate-900 print:text-black">{{ $payroll->employee->name ?? '.......................' }}</p>
                        <p class="text-xs text-slate-500 print:text-slate-700 mt-1">Karyawan</p>
                    </div>
                    <div class="text-center">
                        <p class="mb-20 text-slate-700 print:text-black">Mengetahui,</p>
                        <p class="font-bold border-b border-slate-400 print:border-black inline-block px-4 pb-1 text-slate-900 print:text-black">{{ \App\Models\Setting::getValue('company.signature_name', 'HR / Finance') }}</p>
                        <p class="text-xs text-slate-500 print:text-slate-700 mt-1">{{ \App\Models\Setting::getValue('company.signature_title', 'Manajemen') }}</p>
                    </div>
                </div>
            </div>
            
            <div class="mt-8 text-center text-[10px] text-slate-400 print:text-slate-500 border-t border-slate-100 print:border-slate-300 pt-4">
                Dokumen ini dicetak oleh sistem secara otomatis dan sah tanpa cap perusahaan.
            </div>
        </div>
    </div>
</div>
