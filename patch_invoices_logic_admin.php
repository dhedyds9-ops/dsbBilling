<?php
$file = 'D:/dsBilling/app/Livewire/ResellerPortal/Billing/Invoices.php';
$content = file_get_contents($file);

$searchProps = "public \$showDetailModal = false;";
$replaceProps = "public \$showDetailModal = false;\n    public \$showPaymentModal = false;\n    public \$paymentInvoiceId = null;\n    public \$paymentInvoiceTotal = 0;\n    public \$paymentAmount = 0;\n    public \$paymentMethod = 'cash';";
$content = str_replace($searchProps, $replaceProps, $content);

$searchMethod = "    public function markAsPaid(\$id)
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
    }";

$replaceMethod = "    public function openPaymentModal(\$id)
    {
        \$resellerId = Auth::id();
        \$invoice = Invoice::whereHas('customer', function (\$q) use (\$resellerId) {
            \$q->where('reseller_id', \$resellerId);
        })->findOrFail(\$id);

        \$this->paymentInvoiceId = \$invoice->id;
        \$this->paymentInvoiceTotal = max(0, \$invoice->total_amount - \$invoice->paid_amount);
        \$this->paymentAmount = \$this->paymentInvoiceTotal;
        \$this->paymentMethod = 'cash';
        \$this->showPaymentModal = true;
        
        // Hide detail modal if it's open
        \$this->showDetailModal = false;
    }

    public function closePaymentModal()
    {
        \$this->showPaymentModal = false;
        \$this->paymentInvoiceId = null;
    }

    public function submitPayment()
    {
        try {
            if (\$this->paymentAmount <= 0) {
                throw new \Exception('Jumlah bayar harus lebih dari 0.');
            }

            \$invoice = Invoice::findOrFail(\$this->paymentInvoiceId);
            
            app(\App\Services\Billing\PaymentService::class)->createPayment(
                customerId: \$invoice->customer_id,
                amount: \$this->paymentAmount,
                userId: Auth::id(),
                invoiceIds: [\$invoice->id],
                currency: 'IDR',
                method: \$this->paymentMethod,
                status: 'success',
                gateway: 'manual'
            );

            \$this->dispatch('swal:success', ['title' => 'Berhasil', 'text' => 'Pembayaran berhasil disimpan.']);
            \$this->closePaymentModal();
        } catch (\Exception \$e) {
            \$this->dispatch('swal:error', ['title' => 'Error', 'text' => \$e->getMessage()]);
        }
    }";

$content = str_replace($searchMethod, $replaceMethod, $content);
file_put_contents($file, $content);
echo "Updated Invoices.php logic for Payment Modal.\n";
?>
