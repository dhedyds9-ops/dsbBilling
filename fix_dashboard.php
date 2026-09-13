<?php
$file = 'D:/dsBilling/resources/views/livewire/customer-portal/dashboard.blade.php';

$content = <<<HTML
@php
    \$customer = \App\Models\CRM\Customer::where('user_id', auth()->id())->first();
    \$companyName = \App\Models\Setting::getValue('company.name', config('app.name', 'dsBilling'));
@endphp

<div class="max-w-md mx-auto sm:max-w-full sm:p-6 bg-slate-50 dark:bg-slate-900 min-h-[calc(100vh-4rem)] pb-24">
    <!-- MOBILE WRAPPER (Cards & spacing optimized for mobile, scales up for desktop) -->
    <div class="max-w-md mx-auto sm:max-w-4xl space-y-4">
        
        <!-- Promotional Banner -->
        <div class="rounded-2xl overflow-hidden relative shadow-sm aspect-[21/9] sm:aspect-[21/6] bg-gradient-to-r from-blue-600 to-indigo-600">
            <div class="absolute inset-0 flex flex-col justify-center px-6">
                <h2 class="text-white font-bold text-xl sm:text-3xl leading-tight">Koneksi Stabil<br>Tanpa Batas</h2>
                <p class="text-blue-100 text-xs sm:text-sm mt-1">Layanan internet cepat & mudah dikelola ditangan Anda.</p>
            </div>
            <!-- Decorative circle -->
            <div class="absolute -right-10 -bottom-10 w-40 h-40 bg-white/10 rounded-full blur-2xl"></div>
        </div>

        <!-- Customer Info Card -->
        <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-sm border border-slate-100 dark:border-slate-700/60 p-5">
            <div class="flex justify-between items-start mb-4">
                <div>
                    <p class="text-xs text-slate-500 dark:text-slate-400">Nama Pelanggan</p>
                    <p class="font-bold text-lg text-slate-900 dark:text-slate-100">{{ \$customer->name ?? auth()->user()->name }}</p>
                </div>
                <div class="text-right">
                    <p class="text-xs text-slate-500 dark:text-slate-400 font-mono">ID: {{ \$customer->code ?? 'N/A' }}</p>
                    <div class="inline-flex items-center gap-1.5 mt-1 {{ \$internet_status['status'] === 'online' ? 'text-emerald-600' : 'text-red-600' }}">
                        <span class="w-1.5 h-1.5 rounded-full {{ \$internet_status['status'] === 'online' ? 'bg-emerald-500 animate-pulse' : 'bg-red-500' }}"></span>
                        <span class="text-xs font-semibold">{{ \$internet_status['status'] === 'online' ? 'Internet Online' : 'Internet Offline' }}</span>
                    </div>
                </div>
            </div>

            <div class="border-t border-slate-100 dark:border-slate-700/60 pt-4 mb-4">
                <p class="text-xs text-slate-500 dark:text-slate-400">Total belum bayar</p>
                <p class="font-bold text-2xl {{ \$total_outstanding > 0 ? 'text-red-600' : 'text-slate-900 dark:text-slate-100' }}">
                    Rp {{ number_format(\$total_outstanding, 0, ',', '.') }}
                </p>
            </div>

            <div class="flex justify-between items-center text-[11px] text-slate-500 dark:text-slate-400 bg-slate-50 dark:bg-slate-900/50 p-2.5 rounded-lg">
                <div class="flex items-center gap-1">
                    <span class="material-symbols-outlined text-[14px]">calendar_today</span>
                    <span>Tagihan muncul Tgl 1</span>
                </div>
                <div class="flex items-center gap-1">
                    <span class="material-symbols-outlined text-[14px]">schedule</span>
                    <span>Jatuh tempo Tgl 5</span>
                </div>
            </div>
        </div>

        <!-- Grid Menu -->
        <div class="grid grid-cols-3 gap-3 sm:gap-4 mt-2">
            <!-- 1. Tagihan -->
            <a href="{{ route('customer-portal.billing.invoice-list') }}" class="bg-white dark:bg-slate-800 p-4 rounded-2xl shadow-sm border border-slate-100 dark:border-slate-700/60 flex flex-col items-center justify-center text-center gap-2 hover:bg-slate-50 transition-colors">
                <div class="w-12 h-12 rounded-full bg-blue-100 dark:bg-blue-900/30 text-blue-600 dark:text-blue-400 flex items-center justify-center">
                    <span class="material-symbols-outlined text-[24px]">receipt_long</span>
                </div>
                <span class="text-[11px] font-semibold text-slate-700 dark:text-slate-300">Tagihan</span>
            </a>

            <!-- 2. Tiket Aduan -->
            <a href="{{ route('customer-portal.support.ticket-list') }}" class="bg-white dark:bg-slate-800 p-4 rounded-2xl shadow-sm border border-slate-100 dark:border-slate-700/60 flex flex-col items-center justify-center text-center gap-2 hover:bg-slate-50 transition-colors">
                <div class="w-12 h-12 rounded-full bg-emerald-100 dark:bg-emerald-900/30 text-emerald-600 dark:text-emerald-400 flex items-center justify-center">
                    <span class="material-symbols-outlined text-[24px]">support_agent</span>
                </div>
                <span class="text-[11px] font-semibold text-slate-700 dark:text-slate-300">Tiket Aduan</span>
            </a>

            <!-- 3. Paket Internet -->
            <a href="{{ route('customer-portal.self-service.change-plan') }}" class="bg-white dark:bg-slate-800 p-4 rounded-2xl shadow-sm border border-slate-100 dark:border-slate-700/60 flex flex-col items-center justify-center text-center gap-2 hover:bg-slate-50 transition-colors">
                <div class="w-12 h-12 rounded-full bg-teal-100 dark:bg-teal-900/30 text-teal-600 dark:text-teal-400 flex items-center justify-center">
                    <span class="material-symbols-outlined text-[24px]">wifi</span>
                </div>
                <span class="text-[11px] font-semibold text-slate-700 dark:text-slate-300">Paket Internet</span>
            </a>

            @if(isset(\$pppoe_user) && \$pppoe_user)
            <!-- 4. Pengaturan WiFi -->
            <a href="{{ route('customer-portal.self-service.change-onu-wifi-password') }}" class="bg-white dark:bg-slate-800 p-4 rounded-2xl shadow-sm border border-slate-100 dark:border-slate-700/60 flex flex-col items-center justify-center text-center gap-2 hover:bg-slate-50 transition-colors">
                <div class="w-12 h-12 rounded-full bg-orange-100 dark:bg-orange-900/30 text-orange-600 dark:text-orange-400 flex items-center justify-center">
                    <span class="material-symbols-outlined text-[24px]">router</span>
                </div>
                <span class="text-[11px] font-semibold text-slate-700 dark:text-slate-300">Setting WiFi</span>
            </a>
            @endif

            <!-- 5. Perangkat Aktif -->
            <a href="{{ route('customer-portal.self-service.active-sessions') }}" class="bg-white dark:bg-slate-800 p-4 rounded-2xl shadow-sm border border-slate-100 dark:border-slate-700/60 flex flex-col items-center justify-center text-center gap-2 hover:bg-slate-50 transition-colors">
                <div class="w-12 h-12 rounded-full bg-purple-100 dark:bg-purple-900/30 text-purple-600 dark:text-purple-400 flex items-center justify-center">
                    <span class="material-symbols-outlined text-[24px]">devices</span>
                </div>
                <span class="text-[11px] font-semibold text-slate-700 dark:text-slate-300">Perangkat</span>
            </a>

            <!-- 6. Histori Koneksi -->
            <a href="{{ route('customer-portal.self-service.connection-info') }}" class="bg-white dark:bg-slate-800 p-4 rounded-2xl shadow-sm border border-slate-100 dark:border-slate-700/60 flex flex-col items-center justify-center text-center gap-2 hover:bg-slate-50 transition-colors">
                <div class="w-12 h-12 rounded-full bg-sky-100 dark:bg-sky-900/30 text-sky-600 dark:text-sky-400 flex items-center justify-center">
                    <span class="material-symbols-outlined text-[24px]">history</span>
                </div>
                <span class="text-[11px] font-semibold text-slate-700 dark:text-slate-300">Riwayat</span>
            </a>
        </div>

        <!-- Layanan Aktif Card -->
        <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-sm border border-slate-100 dark:border-slate-700/60 p-5 mt-4">
            <h2 class="text-sm font-bold text-slate-900 dark:text-slate-100 mb-3">Layanan Aktif Saya</h2>
            <div class="space-y-3">
                @forelse(\$customer_services as \$service)
                <div class="flex items-center justify-between p-3 rounded-xl bg-slate-50 dark:bg-slate-900/50">
                    <div class="flex items-center gap-3">
                        <div class="w-8 h-8 rounded-full bg-indigo-100 text-indigo-600 flex items-center justify-center">
                            <span class="material-symbols-outlined text-[18px]">cell_tower</span>
                        </div>
                        <div>
                            <div class="font-semibold text-xs text-slate-900 dark:text-slate-100">{{ \$service->serviceInstance?->serviceProfile?->name ?? 'Layanan Default' }}</div>
                            <div class="text-[10px] text-slate-500">ID: {{ \$service->uuid ?? '-' }}</div>
                        </div>
                    </div>
                    <span class="px-2 py-0.5 rounded-full text-[10px] font-semibold {{ \$service->status === 'active' ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-200 text-slate-700' }}">
                        {{ ucfirst(\$service->status) }}
                    </span>
                </div>
                @empty
                <div class="text-center py-4">
                    <span class="text-xs text-slate-500">Belum ada layanan yang aktif.</span>
                </div>
                @endforelse
            </div>
        </div>
    </div>
</div>
HTML;

file_put_contents($file, $content);
echo "Dashboard fixed.\n";
?>
