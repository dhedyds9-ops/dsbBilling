@section('header_title', 'Pusat Bantuan')

<div class="p-4 sm:p-6 min-h-[calc(100vh-4rem)] space-y-4 pb-24">
    <!-- Header Illustration / Intro -->
    <div class="bg-white dark:bg-slate-800 rounded-2xl p-5 shadow-sm border border-slate-100 dark:border-slate-700/60 text-center">
        <div class="w-16 h-16 bg-blue-100 dark:bg-blue-900/50 text-primary-600 rounded-full flex items-center justify-center mx-auto mb-3">
            <span class="material-symbols-outlined text-3xl">build_circle</span>
        </div>
        <h2 class="text-lg font-bold text-slate-800 dark:text-slate-100">Kendala Jaringan?</h2>
        <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">Coba lakukan langkah penanganan mandiri berikut sebelum menghubungi teknisi kami untuk solusi yang lebih cepat.</p>
    </div>

    @if($isPppoe)
    <!-- PPPoE Troubleshooting -->
    <div class="bg-white dark:bg-slate-800 rounded-2xl p-5 shadow-sm border border-slate-100 dark:border-slate-700/60">
        <h3 class="font-bold text-slate-800 dark:text-slate-200 mb-3 border-b border-slate-100 dark:border-slate-700/60 pb-2">Penanganan Mandiri (Router/Modem)</h3>
        <ol class="space-y-4 text-sm relative border-l-2 border-slate-100 dark:border-slate-700 ml-3 pl-4">
            <li class="relative">
                <span class="absolute -left-[23px] top-0 w-3 h-3 bg-teal-500 rounded-full border-[3px] border-white dark:border-slate-800"></span>
                <strong class="text-slate-700 dark:text-slate-300 block mb-1">Cek Lampu LOS (Merah)</strong>
                <span class="text-xs text-slate-500 dark:text-slate-400">Jika lampu berlabel "LOS" berkedip merah, kabel optik mungkin terputus. Segera hubungi Admin.</span>
            </li>
            <li class="relative">
                <span class="absolute -left-[23px] top-0 w-3 h-3 bg-teal-500 rounded-full border-[3px] border-white dark:border-slate-800"></span>
                <strong class="text-slate-700 dark:text-slate-300 block mb-1">Restart Modem/Router</strong>
                <span class="text-xs text-slate-500 dark:text-slate-400">Cabut kabel listrik dari colokan selama 3 menit, lalu colokkan kembali dan tunggu hingga lampu PON hijau stabil.</span>
            </li>
            <li class="relative">
                <span class="absolute -left-[23px] top-0 w-3 h-3 bg-teal-500 rounded-full border-[3px] border-white dark:border-slate-800"></span>
                <strong class="text-slate-700 dark:text-slate-300 block mb-1">Periksa Kabel LAN</strong>
                <span class="text-xs text-slate-500 dark:text-slate-400">Pastikan kabel LAN (jika memakai perangkat tambahan) terhubung dengan kuat berbunyi "klik".</span>
            </li>
        </ol>
    </div>
    @endif

    @if($isHotspot)
    <!-- Hotspot Troubleshooting -->
    <div class="bg-white dark:bg-slate-800 rounded-2xl p-5 shadow-sm border border-slate-100 dark:border-slate-700/60">
        <h3 class="font-bold text-slate-800 dark:text-slate-200 mb-3 border-b border-slate-100 dark:border-slate-700/60 pb-2">Penanganan Mandiri (Hotspot)</h3>
        <ol class="space-y-4 text-sm relative border-l-2 border-slate-100 dark:border-slate-700 ml-3 pl-4">
            <li class="relative">
                <span class="absolute -left-[23px] top-0 w-3 h-3 bg-teal-500 rounded-full border-[3px] border-white dark:border-slate-800"></span>
                <strong class="text-slate-700 dark:text-slate-300 block mb-1">Cek Halaman Login (Seamless)</strong>
                <span class="text-xs text-slate-500 dark:text-slate-400">Matikan lalu hidupkan WiFi HP Anda. Pastikan dialihkan ke halaman login. Jika tidak, lupakan (Forget Network) jaringan WiFi tersebut lalu hubungkan kembali.</span>
            </li>
            <li class="relative">
                <span class="absolute -left-[23px] top-0 w-3 h-3 bg-teal-500 rounded-full border-[3px] border-white dark:border-slate-800"></span>
                <strong class="text-slate-700 dark:text-slate-300 block mb-1">Limit Session (Perangkat Maksimal)</strong>
                <span class="text-xs text-slate-500 dark:text-slate-400">Jika muncul "Limit reached", cek menu Perangkat Aktif di portal ini untuk mendiskonek (kick) perangkat lain yang masih menggunakan akun Anda.</span>
            </li>
            <li class="relative">
                <span class="absolute -left-[23px] top-0 w-3 h-3 bg-teal-500 rounded-full border-[3px] border-white dark:border-slate-800"></span>
                <strong class="text-slate-700 dark:text-slate-300 block mb-1">Masa Aktif Habis</strong>
                <span class="text-xs text-slate-500 dark:text-slate-400">Pastikan tagihan atau paket Anda masih dalam masa aktif di menu Beranda.</span>
            </li>
        </ol>
    </div>
    @endif

    <!-- Still Having Issues (Contact Admin) -->
    <div class="mt-8 text-center space-y-4">
        <p class="text-sm font-medium text-slate-600 dark:text-slate-400">Masih mengalami kendala?</p>
        <a href="https://wa.me/{{ $whatsappNumber }}" target="_blank" class="w-full flex items-center justify-center gap-2 bg-emerald-500 hover:bg-emerald-600 text-white font-bold py-4 rounded-xl shadow-lg shadow-emerald-500/30 transition-all active:scale-95 text-[15px]">
            <span class="material-symbols-outlined text-[20px]">chat</span>
            Chat Admin via WhatsApp
        </a>
    </div>
</div>






