<?php
$file = 'D:/dsBilling/resources/views/livewire/reseller-portal/billing/invoices.blade.php';
$content = file_get_contents($file);

$searchBtn = "                                <button type=\"button\" class=\"text-indigo-600 hover:text-indigo-800 dark:text-indigo-400 dark:hover:text-indigo-300 text-sm font-medium\" wire:click=\"viewDetail({{ \$row->id }})\">
                                    Detail
                                </button>";
$replaceBtn = "                                <div class=\"flex items-center justify-center gap-3\">
                                    <button type=\"button\" class=\"text-indigo-600 hover:text-indigo-800 dark:text-indigo-400 dark:hover:text-indigo-300 text-sm font-medium\" wire:click=\"viewDetail({{ \$row->id }})\">
                                        Detail
                                    </button>
                                    @if(\$row->status !== 'paid')
                                        <button type=\"button\" class=\"text-emerald-600 hover:text-emerald-800 dark:text-emerald-400 dark:hover:text-emerald-300 text-sm font-medium flex items-center\" wire:click=\"markAsPaid({{ \$row->id }})\" wire:confirm=\"Apakah Anda yakin ingin menandai tagihan ini sebagai lunas? (Pembayaran Tunai)\">
                                            <span class=\"material-symbols-outlined notranslate text-sm mr-1\" translate=\"no\">payments</span> Bayar
                                        </button>
                                    @endif
                                </div>";

$content = str_replace($searchBtn, $replaceBtn, $content);

// Also add SweetAlert initialization
$swalScript = <<<HTML
    <script>
        document.addEventListener('livewire:initialized', () => {
            Livewire.on('swal:success', (data) => {
                const info = Array.isArray(data) ? data[0] : data;
                Swal.fire({
                    icon: 'success',
                    title: info.title,
                    text: info.text,
                    background: document.documentElement.classList.contains('dark') ? '#1e293b' : '#fff',
                    color: document.documentElement.classList.contains('dark') ? '#f8fafc' : '#0f172a',
                    confirmButtonColor: '#4f46e5'
                });
            });
            Livewire.on('swal:error', (data) => {
                const info = Array.isArray(data) ? data[0] : data;
                Swal.fire({
                    icon: 'error',
                    title: info.title,
                    text: info.text,
                    background: document.documentElement.classList.contains('dark') ? '#1e293b' : '#fff',
                    color: document.documentElement.classList.contains('dark') ? '#f8fafc' : '#0f172a',
                    confirmButtonColor: '#ef4444'
                });
            });
        });
    </script>
</div>
HTML;

$content = preg_replace('/<\/div>\s*$/', $swalScript, $content);
file_put_contents($file, $content);
echo "Added pay button and swal to invoices.blade.php\n";
?>
