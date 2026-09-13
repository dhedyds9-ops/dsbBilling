<?php
$file = 'D:/dsBilling/app/Livewire/ResellerPortal/Billing/Invoices.php';
$content = file_get_contents($file);

$searchMethod = "\$this->dispatch('swal:success', ['title' => 'Berhasil', 'text' => 'Pembayaran berhasil disimpan.']);
            \$this->closePaymentModal();";

$replaceMethod = "\$this->dispatch('swal:success', ['title' => 'Berhasil', 'text' => 'Pembayaran berhasil disimpan.']);
            return redirect()->route('reseller-portal.billing.invoices.show', \$invoice->id);";

$content = str_replace($searchMethod, $replaceMethod, $content);
file_put_contents($file, $content);
echo "Updated redirect logic.\n";
?>
