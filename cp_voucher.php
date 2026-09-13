<?php
$src = 'D:/dsBilling/app/Livewire/ISP/Voucher/Index.php';
$dst = 'D:/dsBilling/app/Livewire/ResellerPortal/Sales/Voucher.php';

$content = file_get_contents($src);
$content = str_replace('namespace App\Livewire\ISP\Voucher;', 'namespace App\Livewire\ResellerPortal\Sales;', $content);
$content = str_replace('class Index extends BaseNetworkComponent', 'class Voucher extends \App\Livewire\AdminComponent', $content);
$content = str_replace('use App\Livewire\ISP\BaseNetworkComponent;', '', $content);
$content = str_replace('$this->activeModule = \'isp\';', '$this->activeModule = \'reseller-portal\';', $content);
$content = str_replace('$this->activePage = \'vouchers\';', '$this->activePage = \'sales.voucher\';', $content);
$content = str_replace("['label' => 'ISP', 'url' => route('isp.service-profiles.index')]", "['label' => 'Reseller Portal', 'url' => route('reseller-portal.dashboard')]", $content);

// For isolation
$content = str_replace(")->where('vouchers.type', '!=', 'evoucher');", ")->where('vouchers.type', '!=', 'evoucher')->where('vouchers.reseller_id', auth()->id());", $content);

// When generating vouchers, force reseller_id
$content = preg_replace('/public function generate\(\)\s*\{/', "public function generate()\n    {\n        \$this->reseller_id = auth()->id();", $content);

// View name
$content = str_replace("view('livewire.isp.voucher.index'", "view('livewire.reseller-portal.sales.voucher'", $content);

file_put_contents($dst, $content);
echo "Created Sales/Voucher.php\n";
?>
