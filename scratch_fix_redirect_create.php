<?php
$file = 'app/Livewire/Billing/Payment/Create.php';
$content = file_get_contents($file);

$oldCode = <<<PHP
        session()->flash('success', 'Payment berhasil dibuat!');
        return redirect()->route('billing.payments.show', \$payment->id);
PHP;

$newCode = <<<PHP
        session()->flash('success', 'Payment berhasil dibuat!');
        
        if (!empty(\$this->invoice_ids)) {
            return redirect()->route('billing.invoices.show', \$this->invoice_ids[0]);
        }
        return redirect()->route('billing.payments.index');
PHP;

$content = str_replace($oldCode, $newCode, $content);
file_put_contents($file, $content);
echo "Updated Payment/Create.php redirect\n";
