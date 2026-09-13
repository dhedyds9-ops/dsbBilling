<?php
$file = 'D:/dsBilling/app/Http/Controllers/Reseller/ReportPrintController.php';
$content = file_get_contents($file);

$search = "return \$pdf->stream('Laporan_Penjualan_' . \$reseller->name . '_' . \$month . '_' . \$year . '.pdf');
    
    public function printCommission(Request \$request)";

$replace = "return \$pdf->stream('Laporan_Penjualan_' . \$reseller->name . '_' . \$month . '_' . \$year . '.pdf');
    }
    
    public function printCommission(Request \$request)";

$content = str_replace($search, $replace, $content);
file_put_contents($file, $content);
echo "Fixed syntax error.\n";
?>
