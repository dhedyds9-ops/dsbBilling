<div class="max-w-3xl mx-auto space-y-6 pb-12">
    <!-- Header -->
    <div class="flex items-center justify-between">
        <div class="flex items-center gap-3">
            <a href="{{ route('admin.payroll.index') }}" class="p-2 text-slate-500 hover:text-slate-800 dark:text-slate-400 dark:hover:text-slate-200 rounded-xl hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors" title="Kembali ke Rekap Gaji">
                <span class="material-symbols-outlined notranslate" translate="no">arrow_back</span>
            </a>
            <div>
                <h1 class="text-2xl font-bold text-slate-900 dark:text-slate-100">Generate Slip Gaji Periode</h1>
                <p class="text-sm text-slate-500 dark:text-slate-400">Buat draf slip gaji untuk seluruh pegawai aktif berdasarkan periode cutoff & absensi.</p>
            </div>
        </div>
    </div>

    @if (session()->has('error'))
        <div class="p-4 bg-red-50 dark:bg-red-900/30 border border-red-200 dark:border-red-800 rounded-2xl text-red-700 dark:text-red-300 text-sm">
            <p class="font-bold">{{ session('error') }}</p>
        </div>
    @endif

    @if ($errors->any())
        <div class="p-4 bg-red-50 dark:bg-red-900/30 border border-red-200 dark:border-red-800 rounded-2xl text-red-700 dark:text-red-300 text-sm">
            <p class="font-bold mb-1">Periksa isian formulir berikut:</p>
            <ul class="list-disc list-inside space-y-0.5 text-xs">
                @foreach ($errors->all() as $err)
                    <li>{{ $err }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <!-- Info Box: Integrasi Absensi Otomatis -->
    <div class="p-4 bg-indigo-50/70 dark:bg-indigo-950/30 border border-indigo-200/80 dark:border-indigo-800/60 rounded-2xl flex items-start gap-3">
        <span class="material-symbols-outlined notranslate text-indigo-600 dark:text-indigo-400 mt-0.5" translate="no">info</span>
        <div class="text-xs text-indigo-900 dark:text-indigo-200 space-y-1">
            <p class="font-bold text-sm">Perhitungan Absensi Terintegrasi Otomatis</p>
            <p>Sistem akan memeriksa catatan absensi teknisi/pegawai pada rentang tanggal cutoff (<strong>Tanggal Mulai</strong> s/d <strong>Tanggal Akhir</strong>). Pegawai yang memiliki akun login tertaut dan tercatat <strong>Terlambat</strong> atau <strong>Alpa</strong> akan otomatis dikenakan penalti potongan gaji pada draf slip gaji ini.</p>
        </div>
    </div>

    <form wire:submit.prevent="generate" class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-6 shadow-xs space-y-6">
        @php
            $bulanIndo = [
                1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
                5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
                9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
            ];
        @endphp

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- Bulan Gaji -->
            <div>
                <label class="block mb-1.5 text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider">
                    Bulan Periode Gaji <span class="text-red-500">*</span>
                </label>
                <select wire:model="month"
                        class="w-full px-4 py-2.5 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-sm text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    @foreach ($bulanIndo as $num => $namaBulan)
                        <option value="{{ $num }}">{{ $namaBulan }}</option>
                    @endforeach
                </select>
                @error('month') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
            </div>

            <!-- Tahun Gaji -->
            <div>
                <label class="block mb-1.5 text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider">
                    Tahun <span class="text-red-500">*</span>
                </label>
                <select wire:model="year"
                        class="w-full px-4 py-2.5 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-sm text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    @for ($y = date('Y') - 1; $y <= date('Y') + 1; $y++)
                        <option value="{{ $y }}">{{ $y }}</option>
                    @endfor
                </select>
                @error('year') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
            </div>

            <!-- Cutoff Start -->
            <div>
                <label class="block mb-1.5 text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider">
                    Tanggal Mulai Cutoff Absensi
                </label>
                <input type="date" wire:model="period_start"
                       class="w-full px-4 py-2.5 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-sm text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-indigo-500 font-mono">
                <p class="text-[11px] text-slate-400 mt-1">Awal periode perhitungan kehadiran (default: tanggal 26 bulan lalu).</p>
                @error('period_start') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
            </div>

            <!-- Cutoff End -->
            <div>
                <label class="block mb-1.5 text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider">
                    Tanggal Selesai Cutoff Absensi
                </label>
                <input type="date" wire:model="period_end"
                       class="w-full px-4 py-2.5 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-sm text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-indigo-500 font-mono">
                <p class="text-[11px] text-slate-400 mt-1">Akhir periode perhitungan kehadiran (default: tanggal 25 bulan ini).</p>
                @error('period_end') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
            </div>

            <!-- Notes -->
            <div class="md:col-span-2">
                <label class="block mb-1.5 text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider">
                    Catatan Slip Gaji (Opsional)
                </label>
                <textarea wire:model="notes" rows="3" placeholder="Contoh: Gaji periode reguler bulan ini..."
                          class="w-full px-4 py-2.5 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-sm text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-indigo-500"></textarea>
                <p class="text-[11px] text-slate-400 mt-1">Catatan ini akan disematkan di seluruh slip gaji yang digenerate.</p>
                @error('notes') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
            </div>
        </div>

        <!-- Action Buttons -->
        <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-100 dark:border-slate-800">
            <a href="{{ route('admin.payroll.index') }}"
               class="px-5 py-2.5 bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 rounded-xl text-sm font-semibold transition-colors">
                Batal
            </a>
            <button type="submit"
                    wire:loading.attr="disabled"
                    class="inline-flex items-center gap-2 px-5 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-sm font-semibold shadow-xs transition-colors cursor-pointer disabled:opacity-50">
                <span wire:loading.remove class="material-symbols-outlined notranslate text-lg" translate="no">payments</span>
                <span wire:loading class="material-symbols-outlined notranslate text-lg animate-spin" translate="no">progress_activity</span>
                <span>Proses Generate Slip Gaji</span>
            </button>
        </div>
    </form>
</div>
