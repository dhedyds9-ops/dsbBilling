<!DOCTYPE html>
<html lang="id" class="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Akses Dinonaktifkan - dsBilling License Authority</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=JetBrains+Mono:wght@400;600&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
    </style>
</head>
<body class="bg-slate-950 text-slate-100 min-h-screen flex items-center justify-center p-4 antialiased selection:bg-rose-500 selection:text-white relative overflow-hidden">

    <!-- Ambient Glow Background -->
    <div class="absolute top-1/4 left-1/2 -translate-x-1/2 -translate-y-1/2 w-[500px] h-[350px] bg-rose-600/10 blur-[120px] rounded-full pointer-events-none"></div>

    <div class="max-w-md w-full relative z-10 text-center">
        <!-- Lock Icon Badge -->
        <div class="w-16 h-16 rounded-3xl bg-rose-500/10 border border-rose-500/25 flex items-center justify-center text-rose-400 mx-auto shadow-2xl shadow-rose-600/20 mb-5">
            <i class="fa-solid fa-lock text-2xl"></i>
        </div>

        <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-rose-500/10 border border-rose-500/30 text-rose-300 text-xs font-semibold mb-4 uppercase tracking-wider">
            <span class="w-2 h-2 rounded-full bg-rose-400 animate-ping"></span>
            <span>Lisensi {{ strtoupper($status ?? 'Suspended') }}</span>
        </div>

        <h1 class="text-xl sm:text-2xl font-bold tracking-tight text-white mb-2">
            Akses Software dsBilling Dinonaktifkan
        </h1>

        <p class="text-xs sm:text-sm text-slate-400 max-w-sm mx-auto leading-relaxed mb-6">
            {{ $message ?? 'Masa berlaku sewa software dsBilling untuk instansi ini telah berakhir atau dinonaktifkan oleh administrator pusat dsBilling.' }}
        </p>

        <!-- Details Box -->
        <div class="bg-slate-900/80 border border-slate-800 rounded-2xl p-4 text-xs text-left space-y-2 mb-6 backdrop-blur-md">
            <div class="flex justify-between text-slate-400 pb-2 border-b border-slate-800/80">
                <span>Nama Instansi / Klien:</span>
                <span class="text-white font-semibold">{{ $client ?? config('app.name') }}</span>
            </div>
            <div class="flex justify-between text-slate-400 pb-2 border-b border-slate-800/80">
                <span>Domain Server:</span>
                <span class="text-indigo-400 font-mono font-semibold">{{ request()->getHost() }}</span>
            </div>
            <div class="flex justify-between text-slate-400">
                <span>Pusat Otoritas:</span>
                <span class="text-slate-300">dsBilling License Authority</span>
            </div>
        </div>

        <!-- Actions -->
        @php
            $salesPhone = env('SALES_WHATSAPP', '6283139935920');
            $waMsg = urlencode("Halo Admin dsBilling, saya pengelola " . ($client ?? request()->getHost()) . ". Aplikasi saya ter-suspend di domain " . request()->getHost() . ". Mohon info perpanjangan lisensi / aktivasi kembali. Terima kasih.");
        @endphp

        <div class="flex flex-col sm:flex-row items-center justify-center gap-3">
            <a href="https://wa.me/{{ $salesPhone }}?text={{ $waMsg }}" target="_blank" 
               class="w-full sm:w-auto px-5 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white font-semibold text-xs transition flex items-center justify-center gap-2 shadow-lg shadow-emerald-600/20">
                <i class="fa-brands fa-whatsapp text-sm"></i>
                <span>Hubungi Admin dsBilling</span>
            </a>

            <button onclick="window.location.reload()" 
                    class="w-full sm:w-auto px-5 py-2.5 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-300 hover:text-white font-semibold text-xs border border-slate-700 transition flex items-center justify-center gap-2">
                <i class="fa-solid fa-arrows-rotate text-xs"></i>
                <span>Muat Ulang Halaman</span>
            </button>
        </div>

        <p class="text-[10px] text-slate-600 mt-6">
            &copy; {{ date('Y') }} dsBilling Core Platform • All Rights Reserved
        </p>
    </div>

</body>
</html>
