<?php
$file = 'D:/dsBilling/app/Services/Billing/InvoiceService.php';
$content = file_get_contents($file);

$searchItems = <<<PHP
            foreach (\$items as \$itemData) {
                \$subtotal = (\$itemData['quantity'] ?? 1) * (\$itemData['unit_price'] ?? 0);

                \$this->invoiceItemRepository->create([
                    'uuid' => (string) Str::uuid(),
                    'invoice_id' => \$invoice->id,
                    'description' => \$itemData['description'],
                    'quantity' => \$itemData['quantity'] ?? 1,
                    'unit_price' => \$itemData['unit_price'] ?? 0,
                    'subtotal' => \$subtotal,
                    'created_by' => \$userId,
                    'updated_by' => \$userId,
                ]);
            }
PHP;

$replaceItems = <<<PHP
            // Fetch customer profile to get settlement prices if missing
            \$customer = \App\Models\User::with('serviceProfile')->find(\$customerId);
            \$sp = \$customer ? \$customer->serviceProfile : null;
            
            \$defOwner = \$sp ? (\$sp->owner_settlement_price ?: \$sp->owner_price) : 0;
            \$defBranch = \$sp ? \$sp->branch_settlement_price : 0;
            \$defReseller = \$sp ? (\$sp->reseller_settlement_price ?: \$sp->reseller_price) : 0;

            foreach (\$items as \$itemData) {
                \$subtotal = (\$itemData['quantity'] ?? 1) * (\$itemData['unit_price'] ?? 0);

                \$this->invoiceItemRepository->create([
                    'uuid' => (string) Str::uuid(),
                    'invoice_id' => \$invoice->id,
                    'description' => \$itemData['description'],
                    'quantity' => \$itemData['quantity'] ?? 1,
                    'unit_price' => \$itemData['unit_price'] ?? 0,
                    'owner_settlement_price' => \$itemData['owner_settlement_price'] ?? \$defOwner,
                    'branch_settlement_price' => \$itemData['branch_settlement_price'] ?? \$defBranch,
                    'reseller_settlement_price' => \$itemData['reseller_settlement_price'] ?? \$defReseller,
                    'subtotal' => \$subtotal,
                    'created_by' => \$userId,
                    'updated_by' => \$userId,
                ]);
            }
PHP;

$content = str_replace($searchItems, $replaceItems, $content);
file_put_contents($file, $content);
echo "Updated InvoiceService createInvoice logic.\n";
?>
