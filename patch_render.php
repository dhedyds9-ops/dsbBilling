<?php
$file = 'D:/dsBilling/app/Livewire/ResellerPortal/Customer/Create.php';
$content = file_get_contents($file);

$search = <<<'PHP'
    public function render()
    {
        $serviceProfiles = ServiceProfile::active()->get();
        $routers = Router::active()->get();
        
        return view('livewire.reseller-portal.customer.create', compact('serviceProfiles', 'routers'));
    }
PHP;

$replace = <<<'PHP'
    public function render()
    {
        $serviceProfiles = ServiceProfile::active()->get();
        $routers = Router::active()->get();
        $networkProfiles = \App\Models\Provisioning\NetworkProfile::all();
        $odps = \App\Models\ISP\Odp::all();
        $onus = \App\Models\ISP\Onu::all();
        
        return view('livewire.reseller-portal.customer.create', compact('serviceProfiles', 'routers', 'networkProfiles', 'odps', 'onus'));
    }
PHP;

$content = str_replace($search, $replace, $content);
file_put_contents($file, $content);
echo "Updated render method.\n";
?>
