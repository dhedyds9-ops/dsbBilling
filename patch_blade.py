file_blade = 'D:/dsBilling/resources/views/livewire/dashboard/index.blade.php'
with open(file_blade, 'r', encoding='utf-8') as f:
    content = f.read()

# Fix grid cols
content = content.replace("lg:grid-cols-4", "lg:grid-cols-5")

# Add Voucher card next to Billing card
billing_card_search = """        {{-- Card: PON & Akses --}}"""
voucher_card = """        {{-- Card: Voucher --}}
        <div class="relative overflow-hidden rounded-xl border border-orange-200 dark:border-orange-800/60 shadow-md bg-gradient-to-br from-orange-50 to-white dark:from-orange-950/50 dark:to-slate-800 group hover:shadow-lg transition-all">
            <div class="absolute top-0 left-0 right-0 h-1 bg-gradient-to-r from-orange-500 to-amber-400"></div>
            <div class="absolute top-3 right-3 opacity-10 text-orange-400 group-hover:opacity-20 group-hover:scale-110 transition-all">
                <span class="material-symbols-outlined" style="font-size:56px">confirmation_number</span>
            </div>
            <div class="p-4 pt-5">
                <h3 class="text-xs font-bold text-orange-500 dark:text-orange-400 uppercase tracking-widest mb-2">Penjualan Voucher</h3>
                <div class="text-2xl font-black text-orange-700 dark:text-orange-300 mb-3">Rp {{ number_format($kpi['voucher']['revenue'] ?? 0, 0, ',', '.') }}</div>
                <div class="grid grid-cols-2 gap-1.5">
                    <a href="{{ route('isp.vouchers.index') }}" class="rounded-lg px-2 py-1.5 bg-orange-100 dark:bg-orange-900/50 hover:bg-orange-200 dark:hover:bg-orange-800/70 transition-colors text-center">
                        <span class="block text-orange-700 dark:text-orange-400 font-semibold text-xs">Terjual</span>
                        <span class="text-orange-800 dark:text-orange-300 font-black text-sm">{{ number_format($kpi['voucher']['sold'] ?? 0, 0, ',', '.') }}</span>
                    </a>
                    <a href="{{ route('isp.vouchers.index') }}" class="rounded-lg px-2 py-1.5 bg-slate-100 dark:bg-slate-700/50 hover:bg-slate-200 dark:hover:bg-slate-600/70 transition-colors text-center">
                        <span class="block text-slate-700 dark:text-slate-400 font-semibold text-xs">Tersedia</span>
                        <span class="text-slate-800 dark:text-slate-300 font-black text-sm">{{ number_format($kpi['voucher']['available'] ?? 0, 0, ',', '.') }}</span>
                    </a>
                </div>
            </div>
        </div>

"""
content = content.replace(billing_card_search, voucher_card + billing_card_search)

# Fix View All link
content = content.replace('<a href="#" class="text-sm text-primary-600 hover:text-primary-700">View All &rarr;</a>', '<a href="{{ route(\'isp.user-online.index\') }}" class="text-sm text-primary-600 hover:text-primary-700">View All &rarr;</a>')

with open(file_blade, 'w', encoding='utf-8') as f:
    f.write(content)
print("Updated view")
