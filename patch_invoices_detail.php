<?php
$file = 'D:/dsBilling/app/Livewire/ResellerPortal/Billing/Invoices.php';
$content = file_get_contents($file);

$search = "class Invoices extends AdminComponent\n{";
$replace = "class Invoices extends AdminComponent\n{\n    public \$showDetailModal = false;\n    public \$selectedInvoice = null;";

$content = str_replace($search, $replace, $content);

$searchMethod = "    public function render()";
$replaceMethod = "    public function viewDetail(\$id)\n    {\n        \$resellerId = Auth::id();\n        \$this->selectedInvoice = Invoice::whereHas('customer', function (\$q) use (\$resellerId) {\n            \$q->where('reseller_id', \$resellerId);\n        })->with(['customer', 'items'])->find(\$id);\n\n        if (\$this->selectedInvoice) {\n            \$this->showDetailModal = true;\n        }\n    }\n\n    public function render()";

$content = str_replace($searchMethod, $replaceMethod, $content);
file_put_contents($file, $content);
echo "Added detail logic to Invoices.php\n";
?>
