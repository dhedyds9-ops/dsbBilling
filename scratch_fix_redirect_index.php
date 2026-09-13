<?php
$file = 'app/Livewire/Billing/Invoice/Index.php';
$content = file_get_contents($file);

$oldCode = <<<PHP
            session()->flash('success', 'Pembayaran berhasil dicatat!');
        } catch (\Exception \$e) {
            session()->flash('error', 'Gagal mencatat pembayaran: ' . \$e->getMessage());
        }
        
        \$this->closePaymentModal();
PHP;

$newCode = <<<PHP
            session()->flash('success', 'Pembayaran berhasil dicatat!');
            return redirect()->route('billing.invoices.show', \$this->paymentInvoiceId);
        } catch (\Exception \$e) {
            session()->flash('error', 'Gagal mencatat pembayaran: ' . \$e->getMessage());
            \$this->closePaymentModal();
        }
PHP;

$content = str_replace($oldCode, $newCode, $content);
file_put_contents($file, $content);
echo "Updated Invoice/Index.php redirect\n";
