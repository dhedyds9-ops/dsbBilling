import sys

file_blade = 'D:/dsBilling/resources/views/livewire/isp/technician/dashboard.blade.php'
with open(file_blade, 'r', encoding='utf-8') as f:
    content = f.read()

# Add a section below Stats Grid
search = """    {{-- Recent Installations --}}
    <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-sm border border-slate-200 dark:border-slate-700 overflow-hidden">"""

replace = """    {{-- Layanan Karyawan --}}
    <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-sm border border-slate-200 dark:border-slate-700 overflow-hidden">
        <div class="px-4 py-3.5 border-b border-slate-100 dark:border-slate-700/50 bg-slate-50 dark:bg-slate-800/50 flex items-center gap-2">
            <span class="material-symbols-outlined text-indigo-500" style="font-size:20px">badge</span>
            <h2 class="font-bold text-slate-800 dark:text-slate-200 text-sm uppercase tracking-wider">Layanan Karyawan</h2>
        </div>
        <div class="p-4 flex gap-3">
            <a href="{{ route('technician.payroll.index') }}" class="flex-1 bg-indigo-50 dark:bg-indigo-900/30 hover:bg-indigo-100 dark:hover:bg-indigo-900/50 text-indigo-700 dark:text-indigo-300 rounded-xl p-3 flex flex-col items-center justify-center gap-1.5 transition-colors border border-indigo-100 dark:border-indigo-800 text-center">
                <span class="material-symbols-outlined" style="font-size:28px">request_quote</span>
                <span class="text-xs font-bold">Slip Gaji</span>
            </a>
            <!-- Tambahkan menu lain di sini nanti -->
        </div>
    </div>

    {{-- Recent Installations --}}
    <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-sm border border-slate-200 dark:border-slate-700 overflow-hidden">"""

if search in content:
    content = content.replace(search, replace)
    with open(file_blade, 'w', encoding='utf-8') as f:
        f.write(content)
    print("Dashboard updated with Payroll link")
else:
    print("Search block not found.")
