<?php
$file = 'D:/dsBilling/resources/views/livewire/customer-portal/dashboard.blade.php';
$content = file_get_contents($file);

$gridMenuHtml = <<<HTML
        <!-- Grid Menu -->
        <div class="grid grid-cols-3 gap-3 sm:gap-4 mt-2">
HTML;

$helpButtonHtml = <<<HTML
        <!-- Pusat Bantuan (Troubleshooting) -->
        <a href="{{ route('customer-portal.support.contact-admin') }}" class="mt-4 bg-gradient-to-r from-teal-500 to-emerald-500 hover:from-teal-600 hover:to-emerald-600 p-4 rounded-2xl shadow-sm text-white flex items-center justify-between transition-all group">
            <div class="flex items-center gap-4">
                <div class="w-10 h-10 rounded-full bg-white/20 flex items-center justify-center backdrop-blur-sm">
                    <span class="material-symbols-outlined text-[24px]">support_agent</span>
                </div>
                <div>
                    <h3 class="font-bold text-sm sm:text-base leading-tight">Pusat Bantuan & Panduan</h3>
                    <p class="text-[10px] sm:text-xs text-teal-50 opacity-90 mt-0.5">Penanganan gangguan mandiri & Chat Admin</p>
                </div>
            </div>
            <span class="material-symbols-outlined transform group-hover:translate-x-1 transition-transform">chevron_right</span>
        </a>
HTML;

$searchPattern = '/\s*<!-- Layanan Aktif Card -->/s';
$replacement = "\n" . $helpButtonHtml . "\n\n        <!-- Layanan Aktif Card -->";

$content = preg_replace($searchPattern, $replacement, $content);
file_put_contents($file, $content);
echo "Added Help Center button.\n";
?>
