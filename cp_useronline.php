<?php
$src = 'D:/dsBilling/app/Livewire/ISP/UserOnline/Index.php';
$dst = 'D:/dsBilling/app/Livewire/ResellerPortal/Customer/UserOnline.php';

$content = file_get_contents($src);

// Update namespace and class
$content = str_replace('namespace App\Livewire\ISP\UserOnline;', 'namespace App\Livewire\ResellerPortal\Customer;', $content);
$content = str_replace('class Index extends BaseNetworkComponent', 'class UserOnline extends \App\Livewire\ISP\BaseNetworkComponent', $content);

// Update mount logic
$content = str_replace("\$this->activeModule = 'isp';", "\$this->activeModule = 'reseller-portal';", $content);
$content = str_replace("\$this->activePage = 'user-online';", "\$this->activePage = 'customers.user-online';", $content);

// View name
$content = str_replace("view('livewire.isp.user-online.index'", "view('livewire.reseller-portal.customer.user-online'", $content);

file_put_contents($dst, $content);
echo "Created Reseller UserOnline component.\n";
?>
