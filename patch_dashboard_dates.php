<?php
$file = 'D:/dsBilling/resources/views/livewire/customer-portal/dashboard.blade.php';
$content = file_get_contents($file);

$oldHtml = <<<HTML
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
HTML;

$newHtml = <<<HTML
            @if(isset(\$next_billing_date) && \$next_billing_date)
            <div class="flex justify-between items-center text-[11px] text-slate-500 dark:text-slate-400 bg-slate-50 dark:bg-slate-900/50 p-2.5 rounded-lg">
                <div class="flex items-center gap-1">
                    <span class="material-symbols-outlined text-[14px]">calendar_today</span>
                    <span>Tagihan berikutnya: {{ \$next_billing_date->format('d M Y') }}</span>
                </div>
                <div class="flex items-center gap-1">
                    <span class="material-symbols-outlined text-[14px]">schedule</span>
                    <span>Jatuh tempo: +{{ \App\Models\Setting::getValue('billing.invoice_due_days', 7) }} hari</span>
                </div>
            </div>
            @endif
HTML;

$content = str_replace($oldHtml, $newHtml, $content);
file_put_contents($file, $content);
echo "Patched dashboard hardcoded dates.\n";
?>
