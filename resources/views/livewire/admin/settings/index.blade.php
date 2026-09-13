@section('page_title')
    <div class="flex items-center gap-2">
        <div class="w-8 h-8 rounded-full bg-slate-100 dark:bg-slate-700 flex items-center justify-center text-slate-600 dark:text-slate-300">
            <span class="material-symbols-outlined notranslate" translate="no" style="font-size:18px">settings</span>
        </div>
        <span class="text-lg">Pengaturan Sistem</span>
    </div>
@endsection

<div class="space-y-6 pb-10">

    {{-- Flash --}}
    @if (session('success'))
        <div class="flex items-center gap-3 p-4 rounded-xl bg-emerald-50 dark:bg-emerald-900/20 border border-emerald-200 dark:border-emerald-800 text-emerald-700 dark:text-emerald-400 text-sm">
            <span class="material-symbols-outlined notranslate" style="font-size:20px">check_circle</span>
            {{ session('success') }}
        </div>
    @endif

    {{-- Tab Navigation --}}
    <div class="bg-white dark:bg-slate-800 rounded-xl shadow-sm border border-slate-200 dark:border-slate-700 p-4">
        <div class="flex flex-wrap gap-1">
            <button wire:click="$set('settingsTab','overview')"
                class="inline-flex items-center gap-2 px-4 py-2 text-sm font-medium rounded-lg transition-all
                    {{ $settingsTab === 'overview' ? 'bg-indigo-600 text-white shadow-sm' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-700' }}">
                <span class="material-symbols-outlined notranslate" style="font-size:16px">dashboard</span>
                Ringkasan
            </button>
            <button wire:click="$set('settingsTab','billing')"
                class="inline-flex items-center gap-2 px-4 py-2 text-sm font-medium rounded-lg transition-all
                    {{ $settingsTab === 'billing' ? 'bg-indigo-600 text-white shadow-sm' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-700' }}">
                <span class="material-symbols-outlined notranslate" style="font-size:16px">receipt_long</span>
                Billing
            </button>
            <button wire:click="$set('settingsTab','notification')"
                class="inline-flex items-center gap-2 px-4 py-2 text-sm font-medium rounded-lg transition-all
                    {{ $settingsTab === 'notification' ? 'bg-indigo-600 text-white shadow-sm' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-700' }}">
                <span class="material-symbols-outlined notranslate" style="font-size:16px">notifications</span>
                Notifikasi
            </button>
        </div>
    </div>

    {{-- ============ TAB: RINGKASAN / OVERVIEW ============ --}}
    @if ($settingsTab === 'overview')
    <div class="bg-white dark:bg-slate-800 rounded-xl shadow-sm border border-slate-200 dark:border-slate-700 overflow-hidden">
        <div class="px-6 py-4 border-b border-slate-200 dark:border-slate-700">
            <h2 class="text-sm font-semibold text-slate-900 dark:text-slate-100">Navigasi Pengaturan</h2>
            <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">Klik kartu di bawah untuk langsung ke halaman pengaturan yang sesuai.</p>
        </div>
        <div class="p-6 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">

            {{-- Perusahaan --}}
            <a href="{{ route('pengaturan.perusahaan') }}"
                class="flex items-start gap-4 p-4 rounded-xl border border-slate-200 dark:border-slate-700 hover:border-indigo-300 dark:hover:border-indigo-600 hover:bg-indigo-50/50 dark:hover:bg-indigo-900/10 transition-all group">
                <div class="w-10 h-10 rounded-xl bg-indigo-100 dark:bg-indigo-900/50 flex items-center justify-center shrink-0 group-hover:bg-indigo-200 dark:group-hover:bg-indigo-800/50 transition">
                    <span class="material-symbols-outlined notranslate text-indigo-600 dark:text-indigo-400" style="font-size:20px">business</span>
                </div>
                <div>
                    <p class="text-sm font-semibold text-slate-800 dark:text-slate-200">Profil Perusahaan</p>
                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5 leading-relaxed">Nama, alamat, NPWP, NIB, logo, dan identitas legal perusahaan</p>
                </div>
            </a>

            {{-- Payment Gateway --}}
            <a href="{{ route('pengaturan.payment-gateway') }}"
                class="flex items-start gap-4 p-4 rounded-xl border border-slate-200 dark:border-slate-700 hover:border-emerald-300 dark:hover:border-emerald-600 hover:bg-emerald-50/50 dark:hover:bg-emerald-900/10 transition-all group">
                <div class="w-10 h-10 rounded-xl bg-emerald-100 dark:bg-emerald-900/50 flex items-center justify-center shrink-0 group-hover:bg-emerald-200 dark:group-hover:bg-emerald-800/50 transition">
                    <span class="material-symbols-outlined notranslate text-emerald-600 dark:text-emerald-400" style="font-size:20px">payments</span>
                </div>
                <div>
                    <p class="text-sm font-semibold text-slate-800 dark:text-slate-200">Payment Gateway</p>
                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5 leading-relaxed">Midtrans, Xendit, Duitku, transfer manual, dan e-wallet</p>
                </div>
            </a>

            {{-- Telegram --}}
            <a href="{{ route('pengaturan.telegram') }}"
                class="flex items-start gap-4 p-4 rounded-xl border border-slate-200 dark:border-slate-700 hover:border-sky-300 dark:hover:border-sky-600 hover:bg-sky-50/50 dark:hover:bg-sky-900/10 transition-all group">
                <div class="w-10 h-10 rounded-xl bg-sky-100 dark:bg-sky-900/50 flex items-center justify-center shrink-0 group-hover:bg-sky-200 dark:group-hover:bg-sky-800/50 transition">
                    <span class="material-symbols-outlined notranslate text-sky-600 dark:text-sky-400" style="font-size:20px">send</span>
                </div>
                <div>
                    <p class="text-sm font-semibold text-slate-800 dark:text-slate-200">Bot Telegram</p>
                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5 leading-relaxed">Bot token, chat ID, dan template notifikasi Telegram</p>
                </div>
            </a>

            {{-- WhatsApp --}}
            <a href="{{ route('pengaturan.whatsapp') }}"
                class="flex items-start gap-4 p-4 rounded-xl border border-slate-200 dark:border-slate-700 hover:border-green-300 dark:hover:border-green-600 hover:bg-green-50/50 dark:hover:bg-green-900/10 transition-all group">
                <div class="w-10 h-10 rounded-xl bg-green-100 dark:bg-green-900/50 flex items-center justify-center shrink-0 group-hover:bg-green-200 dark:group-hover:bg-green-800/50 transition">
                    <span class="material-symbols-outlined notranslate text-green-600 dark:text-green-400" style="font-size:20px">chat</span>
                </div>
                <div>
                    <p class="text-sm font-semibold text-slate-800 dark:text-slate-200">WhatsApp Gateway</p>
                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5 leading-relaxed">Konfigurasi gateway WA untuk notifikasi dan reminder</p>
                </div>
            </a>

            {{-- Koneksi --}}
            <a href="{{ route('pengaturan.koneksi') }}"
                class="flex items-start gap-4 p-4 rounded-xl border border-slate-200 dark:border-slate-700 hover:border-orange-300 dark:hover:border-orange-600 hover:bg-orange-50/50 dark:hover:bg-orange-900/10 transition-all group">
                <div class="w-10 h-10 rounded-xl bg-orange-100 dark:bg-orange-900/50 flex items-center justify-center shrink-0 group-hover:bg-orange-200 dark:group-hover:bg-orange-800/50 transition">
                    <span class="material-symbols-outlined notranslate text-orange-600 dark:text-orange-400" style="font-size:20px">cable</span>
                </div>
                <div>
                    <p class="text-sm font-semibold text-slate-800 dark:text-slate-200">Koneksi & API</p>
                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5 leading-relaxed">Konfigurasi API eksternal, webhook, dan integrasi sistem</p>
                </div>
            </a>

            {{-- Billing (tab ini) --}}
            <button wire:click="$set('settingsTab','billing')"
                class="flex items-start gap-4 p-4 rounded-xl border border-slate-200 dark:border-slate-700 hover:border-purple-300 dark:hover:border-purple-600 hover:bg-purple-50/50 dark:hover:bg-purple-900/10 transition-all group text-left">
                <div class="w-10 h-10 rounded-xl bg-purple-100 dark:bg-purple-900/50 flex items-center justify-center shrink-0 group-hover:bg-purple-200 dark:group-hover:bg-purple-800/50 transition">
                    <span class="material-symbols-outlined notranslate text-purple-600 dark:text-purple-400" style="font-size:20px">receipt_long</span>
                </div>
                <div>
                    <p class="text-sm font-semibold text-slate-800 dark:text-slate-200">Billing & Invoice</p>
                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5 leading-relaxed">Prefix invoice, jatuh tempo, pajak, denda, dan mata uang</p>
                </div>
            </button>

        </div>
    </div>
    @endif

    {{-- ============ TAB: BILLING ============ --}}
    @if ($settingsTab === 'billing')
    <div class="bg-white dark:bg-slate-800 rounded-xl shadow-sm border border-slate-200 dark:border-slate-700 overflow-hidden">
        <div class="px-6 py-4 border-b border-slate-200 dark:border-slate-700 flex items-center gap-3">
            <div class="w-8 h-8 rounded-full bg-purple-100 dark:bg-purple-900/50 flex items-center justify-center">
                <span class="material-symbols-outlined notranslate text-purple-600 dark:text-purple-400" style="font-size:18px">receipt_long</span>
            </div>
            <div>
                <h2 class="text-sm font-semibold text-slate-900 dark:text-slate-100">Konfigurasi Billing & Invoice</h2>
                <p class="text-xs text-slate-500 dark:text-slate-400">Pengaturan invoice, pajak, denda, dan mata uang.</p>
            </div>
        </div>
        <form wire:submit.prevent="saveBilling" class="p-6">
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5">
                <div>
                    <label class="block text-xs font-semibold text-slate-600 dark:text-slate-400 uppercase tracking-wide mb-1.5">Prefix Invoice</label>
                    <input type="text" wire:model="invoice_prefix" placeholder="INV-"
                        class="block w-full px-3 py-2 border border-slate-200 dark:border-slate-700 rounded-lg text-sm bg-slate-50 dark:bg-slate-900/50 focus:ring-2 focus:ring-indigo-500 dark:text-slate-100 dark:bg-slate-900 transition">
                    <p class="text-xs text-slate-400 mt-1">Contoh: <code class="font-mono">INV-</code> → INV-0001</p>
                    @error('invoice_prefix') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-600 dark:text-slate-400 uppercase tracking-wide mb-1.5">Jatuh Tempo (hari)</label>
                    <input type="number" wire:model="invoice_due_days" min="1" max="365"
                        class="block w-full px-3 py-2 border border-slate-200 dark:border-slate-700 rounded-lg text-sm bg-slate-50 dark:bg-slate-900/50 focus:ring-2 focus:ring-indigo-500 dark:text-slate-100 dark:bg-slate-900 transition">
                    <p class="text-xs text-slate-400 mt-1">Hari sebelum invoice overdue</p>
                    @error('invoice_due_days') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-600 dark:text-slate-400 uppercase tracking-wide mb-1.5">Mata Uang</label>
                    <select wire:model="currency"
                        class="block w-full px-3 py-2 border border-slate-200 dark:border-slate-700 rounded-lg text-sm bg-slate-50 dark:bg-slate-900/50 focus:ring-2 focus:ring-indigo-500 dark:text-slate-100 dark:bg-slate-900 transition">
                        <option value="IDR">IDR – Rupiah Indonesia</option>
                        <option value="USD">USD – US Dollar</option>
                        <option value="MYR">MYR – Malaysian Ringgit</option>
                        <option value="SGD">SGD – Singapore Dollar</option>
                    </select>
                    @error('currency') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-600 dark:text-slate-400 uppercase tracking-wide mb-1.5">Pajak (%)</label>
                    <div class="relative">
                        <input type="number" wire:model="tax_percent" min="0" max="100" step="0.5"
                            class="block w-full pl-3 pr-10 py-2 border border-slate-200 dark:border-slate-700 rounded-lg text-sm bg-slate-50 dark:bg-slate-900/50 focus:ring-2 focus:ring-indigo-500 dark:text-slate-100 dark:bg-slate-900 transition">
                        <span class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 text-sm font-medium">%</span>
                    </div>
                    <p class="text-xs text-slate-400 mt-1">Isi 0 jika tidak ada PPN</p>
                    @error('tax_percent') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-600 dark:text-slate-400 uppercase tracking-wide mb-1.5">Denda Keterlambatan (%)</label>
                    <div class="relative">
                        <input type="number" wire:model="late_fee_percent" min="0" max="100" step="0.5"
                            class="block w-full pl-3 pr-10 py-2 border border-slate-200 dark:border-slate-700 rounded-lg text-sm bg-slate-50 dark:bg-slate-900/50 focus:ring-2 focus:ring-indigo-500 dark:text-slate-100 dark:bg-slate-900 transition">
                        <span class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 text-sm font-medium">%</span>
                    </div>
                    <p class="text-xs text-slate-400 mt-1">Isi 0 jika tidak ada denda</p>
                    @error('late_fee_percent') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
            </div>
            <div class="flex justify-end mt-6 pt-5 border-t border-slate-100 dark:border-slate-700">
                <button type="submit"
                    class="inline-flex items-center gap-2 px-5 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg text-sm font-medium shadow-sm transition-colors">
                    <span class="material-symbols-outlined notranslate" style="font-size:18px">save</span>
                    Simpan Pengaturan Billing
                </button>
            </div>
        </form>
    </div>
    @endif

    {{-- ============ TAB: NOTIFIKASI ============ --}}
    @if ($settingsTab === 'notification')
    <div class="bg-white dark:bg-slate-800 rounded-xl shadow-sm border border-slate-200 dark:border-slate-700 overflow-hidden">
        <div class="px-6 py-4 border-b border-slate-200 dark:border-slate-700 flex items-center gap-3">
            <div class="w-8 h-8 rounded-full bg-amber-100 dark:bg-amber-900/50 flex items-center justify-center">
                <span class="material-symbols-outlined notranslate text-amber-600 dark:text-amber-400" style="font-size:18px">notifications</span>
            </div>
            <div>
                <h2 class="text-sm font-semibold text-slate-900 dark:text-slate-100">Konfigurasi Notifikasi</h2>
                <p class="text-xs text-slate-500 dark:text-slate-400">Atur kapan reminder otomatis dikirim ke pelanggan.</p>
            </div>
        </div>
        <form wire:submit.prevent="saveNotification" class="p-6 space-y-5">
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                <div class="flex items-start gap-4 p-4 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-900/30">
                    <div class="w-9 h-9 rounded-full bg-blue-100 dark:bg-blue-900/50 flex items-center justify-center shrink-0">
                        <span class="material-symbols-outlined notranslate text-blue-600 dark:text-blue-400" style="font-size:18px">mark_email_unread</span>
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-semibold text-slate-800 dark:text-slate-200 mb-1">Reminder Tagihan</p>
                        <p class="text-xs text-slate-500 dark:text-slate-400 mb-3">Kirim notifikasi sebelum jatuh tempo invoice</p>
                        <div class="flex items-center gap-2">
                            <input type="number" wire:model="notif_invoice_before_days" min="0" max="30"
                                class="w-20 px-3 py-1.5 border border-slate-200 dark:border-slate-700 rounded-lg text-sm bg-white dark:bg-slate-900 dark:text-slate-100 focus:ring-2 focus:ring-indigo-500 transition text-center">
                            <span class="text-sm text-slate-500 dark:text-slate-400">hari sebelum jatuh tempo</span>
                        </div>
                        @error('notif_invoice_before_days') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                </div>
                <div class="flex items-start gap-4 p-4 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-900/30">
                    <div class="w-9 h-9 rounded-full bg-red-100 dark:bg-red-900/50 flex items-center justify-center shrink-0">
                        <span class="material-symbols-outlined notranslate text-red-600 dark:text-red-400" style="font-size:18px">block</span>
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-semibold text-slate-800 dark:text-slate-200 mb-1">Peringatan Isolir</p>
                        <p class="text-xs text-slate-500 dark:text-slate-400 mb-3">Kirim notifikasi sebelum jaringan diputus/isolir</p>
                        <div class="flex items-center gap-2">
                            <input type="number" wire:model="notif_isolir_before_days" min="0" max="30"
                                class="w-20 px-3 py-1.5 border border-slate-200 dark:border-slate-700 rounded-lg text-sm bg-white dark:bg-slate-900 dark:text-slate-100 focus:ring-2 focus:ring-indigo-500 transition text-center">
                            <span class="text-sm text-slate-500 dark:text-slate-400">hari sebelum isolir</span>
                        </div>
                        @error('notif_isolir_before_days') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                </div>
            </div>
            <div class="flex items-start gap-3 p-4 rounded-xl bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-800">
                <span class="material-symbols-outlined notranslate text-amber-500 shrink-0 mt-0.5" style="font-size:18px">info</span>
                <p class="text-xs text-amber-800 dark:text-amber-300 leading-relaxed">
                    Notifikasi dikirim via <strong>Telegram Bot</strong> atau <strong>WhatsApp Gateway</strong>.
                    Pastikan sudah dikonfigurasi di
                    <a href="{{ route('pengaturan.telegram') }}" class="underline font-medium">Pengaturan Telegram</a> atau
                    <a href="{{ route('pengaturan.whatsapp') }}" class="underline font-medium">Pengaturan WhatsApp</a>.
                </p>
            </div>
            <div class="flex justify-end pt-3 border-t border-slate-100 dark:border-slate-700">
                <button type="submit"
                    class="inline-flex items-center gap-2 px-5 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg text-sm font-medium shadow-sm transition-colors">
                    <span class="material-symbols-outlined notranslate" style="font-size:18px">save</span>
                    Simpan Pengaturan Notifikasi
                </button>
            </div>
        </form>
    </div>
    @endif

</div>
