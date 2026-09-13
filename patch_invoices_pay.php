<?php
$file = 'D:/dsBilling/app/Livewire/ResellerPortal/Billing/Invoices.php';
$content = file_get_contents($file);

$searchMethod = "    public function render()";
$replaceMethod = "    public function markAsPaid(\$id)
    {
        try {
            \$resellerId = Auth::id();
            \$invoice = Invoice::whereHas('customer', function (\$q) use (\$resellerId) {
                \$q->where('reseller_id', \$resellerId);
            })->findOrFail(\$id);

            if (\$invoice->status === 'paid') {
                \$this->dispatch('swal:error', ['title' => 'Gagal', 'text' => 'Tagihan ini sudah lunas.']);
                return;
            }

            app(\App\Services\Billing\PaymentService::class)->createPayment(
                customerId: \$invoice->customer_id,
                amount: max(0, \$invoice->total_amount - \$invoice->paid_amount),
                userId: Auth::id(),
                invoiceIds: [\$invoice->id],
                currency: 'IDR',
                method: 'cash',
                status: 'success',
                gateway: 'manual'
            );

            \$this->dispatch('swal:success', ['title' => 'Berhasil', 'text' => 'Tagihan berhasil ditandai sebagai Lunas.']);
            
            // Reload the selected invoice if modal is open
            if (\$this->showDetailModal && \$this->selectedInvoice && \$this->selectedInvoice->id === \$id) {
                \$this->viewDetail(\$id);
            }
        } catch (\Exception \$e) {
            \$this->dispatch('swal:error', ['title' => 'Error', 'text' => \$e->getMessage()]);
        }
    }

    public function render()";

$content = str_replace($searchMethod, $replaceMethod, $content);
file_put_contents($file, $content);
echo "Added markAsPaid logic to Invoices.php\n";
?>
