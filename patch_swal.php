<?php
$files = [
    'D:/dsBilling/resources/views/livewire/reseller-portal/customer/create.blade.php',
    'D:/dsBilling/resources/views/livewire/reseller-portal/customer/create-hotspot.blade.php'
];

$script = <<<EOT
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    document.addEventListener('livewire:initialized', () => {
        Livewire.on('toast', (data) => {
            let detail = Array.isArray(data) ? data[0] : data;
            
            if (detail.type === 'success') {
                Swal.fire({
                    title: 'Berhasil!',
                    text: detail.message,
                    icon: 'success',
                    showCancelButton: true,
                    confirmButtonColor: '#4f46e5',
                    cancelButtonColor: '#64748b',
                    confirmButtonText: '<span class="material-symbols-outlined align-middle mr-1" style="font-size:18px">print</span>Cetak Invoice',
                    cancelButtonText: 'Tutup'
                }).then((result) => {
                    if (result.isConfirmed && detail.invoice_id) {
                        window.location.href = '/reseller-portal/customers'; // Redirect to customers for now
                    } else {
                        window.location.href = '/reseller-portal/customers';
                    }
                });
            } else {
                Swal.fire({
                    title: 'Perhatian!',
                    text: detail.message,
                    icon: 'warning',
                    confirmButtonColor: '#eab308'
                });
            }
        });
    });
</script>
EOT;

foreach ($files as $file) {
    if (!file_exists($file)) continue;
    $content = file_get_contents($file);
    
    // Remove the old alert script
    $content = preg_replace('/<script>\s*document\.addEventListener\(\'livewire:initialized\'.*?<\/script>/s', '', $content);
    
    // Add asterisks to required fields
    // Fields: Name, Username, Password, Service Profile
    $content = str_replace('<label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5">Nama Lengkap</label>', '<label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5">Nama Lengkap <span class="text-red-500">*</span></label>', $content);
    
    $content = str_replace('<label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5">Username PPPoE</label>', '<label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5">Username PPPoE <span class="text-red-500">*</span></label>', $content);
    
    $content = str_replace('<label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5">Password PPPoE</label>', '<label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5">Password PPPoE <span class="text-red-500">*</span></label>', $content);
    
    $content = str_replace('<label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5">Username Hotspot</label>', '<label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5">Username Hotspot <span class="text-red-500">*</span></label>', $content);
    
    $content = str_replace('<label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5">Password Hotspot</label>', '<label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5">Password Hotspot <span class="text-red-500">*</span></label>', $content);
    
    $content = str_replace('<label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5">Paket Layanan (Service Profile)</label>', '<label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5">Paket Layanan (Service Profile) <span class="text-red-500">*</span></label>', $content);

    if (strpos($content, 'sweetalert2') === false) {
        $content .= "\n" . $script;
    }
    
    file_put_contents($file, $content);
}
echo "Added Swal and asterisks.\n";
?>
