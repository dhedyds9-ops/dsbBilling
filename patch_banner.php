<?php
$file = 'D:/dsBilling/resources/views/livewire/customer-portal/dashboard.blade.php';
$content = file_get_contents($file);

$oldBanner = <<<HTML
        <!-- Promotional Banner -->
        <div class="rounded-2xl overflow-hidden relative shadow-sm aspect-[21/9] sm:aspect-[21/6] bg-gradient-to-r from-blue-600 to-indigo-600">
            <div class="absolute inset-0 flex flex-col justify-center px-6">
                <h2 class="text-white font-bold text-xl sm:text-3xl leading-tight">Koneksi Stabil<br>Tanpa Batas</h2>
                <p class="text-blue-100 text-xs sm:text-sm mt-1">Layanan internet cepat & mudah dikelola ditangan Anda.</p>
            </div>
            <!-- Decorative circle -->
            <div class="absolute -right-10 -bottom-10 w-40 h-40 bg-white/10 rounded-full blur-2xl"></div>
        </div>
HTML;

$newBanner = <<<HTML
        <!-- Promotional Banner -->
        <div class="rounded-3xl overflow-hidden relative shadow-lg bg-gradient-to-br from-indigo-600 via-blue-600 to-indigo-800">
            <!-- Background Decorations -->
            <div class="absolute top-0 right-0 w-64 h-64 bg-white/10 rounded-full blur-3xl -translate-y-1/2 translate-x-1/3"></div>
            <div class="absolute bottom-0 left-0 w-40 h-40 bg-teal-400/20 rounded-full blur-2xl translate-y-1/3 -translate-x-1/3"></div>
            
            <!-- Content -->
            <div class="relative z-10 p-6 flex items-center justify-between">
                <div class="flex-1 pr-2">
                    <div class="inline-flex items-center gap-1.5 px-2.5 py-1 bg-white/20 text-white text-[10px] font-bold tracking-wider rounded-lg mb-3 backdrop-blur-sm border border-white/20 uppercase shadow-sm">
                        <span class="w-1.5 h-1.5 rounded-full bg-teal-300 animate-pulse"></span>
                        Info Layanan
                    </div>
                    <h2 class="text-white font-extrabold text-2xl sm:text-3xl leading-[1.15] tracking-tight mb-1.5">
                        Koneksi Cepat<br><span class="text-teal-300">Tanpa Batas</span>
                    </h2>
                    <p class="text-indigo-100 text-[11px] sm:text-sm leading-relaxed max-w-[200px] opacity-90">
                        Internet stabil di genggaman Anda.
                    </p>
                </div>
                
                <!-- Icon/Illustration -->
                <div class="flex-shrink-0 relative pl-2">
                    <div class="absolute inset-0 bg-teal-400/30 rounded-full blur-xl animate-pulse"></div>
                    <div class="relative w-16 h-16 bg-gradient-to-tr from-white/10 to-white/20 rounded-2xl flex items-center justify-center backdrop-blur-md border border-white/30 shadow-[0_8px_32px_rgba(0,0,0,0.15)] rotate-6 transform hover:rotate-12 transition-transform duration-300">
                        <span class="material-symbols-outlined text-white drop-shadow-md" translate="no" style="font-size: 32px;">rocket_launch</span>
                    </div>
                </div>
            </div>
            
            <!-- Bottom Accent Line -->
            <div class="absolute bottom-0 left-0 right-0 h-1.5 bg-gradient-to-r from-teal-400 via-blue-400 to-indigo-500"></div>
        </div>
HTML;

$content = str_replace($oldBanner, $newBanner, $content);
file_put_contents($file, $content);
echo "Banner updated.\n";
?>
