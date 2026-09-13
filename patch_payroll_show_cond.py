import sys

file_blade = 'D:/dsBilling/resources/views/livewire/admin/payroll/show.blade.php'
with open(file_blade, 'r', encoding='utf-8') as f:
    content = f.read()

search = """    <div class="mb-4 flex flex-col sm:flex-row justify-between items-start sm:items-center print:hidden gap-3">
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
    </div>"""

replace = """    @if(!request()->routeIs('technician.*'))
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
    @endif"""

if search in content:
    content = content.replace(search, replace)
    with open(file_blade, 'w', encoding='utf-8') as f:
        f.write(content)
    print("Admin Payroll Show blade updated to hide admin actions for tech")
else:
    print("Search block not found.")
