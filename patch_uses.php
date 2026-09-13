<?php
$file = 'D:/dsBilling/app/Http/Controllers/Reseller/ReportPrintController.php';
$content = file_get_contents($file);

$useSearch = 'use App\Models\Billing\Invoice;';
$useReplace = "use App\Models\Billing\Invoice;\nuse App\Models\Billing\InvoiceItem;\nuse Illuminate\Support\Facades\DB;";

$content = str_replace($useSearch, $useReplace, $content);
file_put_contents($file, $content);
echo "Added uses.\n";
?>
