<?php
$activeClass = <<<EOT
<?php

namespace App\Livewire\ResellerPortal\Customer;

class Active extends Index
{
    public function mount()
    {
        parent::mount();
        \$this->activePage = 'customers.active';
        \$this->statusFilter = 'active';
    }
}
EOT;

$isolatedClass = <<<EOT
<?php

namespace App\Livewire\ResellerPortal\Customer;

class Isolated extends Index
{
    public function mount()
    {
        parent::mount();
        \$this->activePage = 'customers.isolated';
        \$this->statusFilter = 'suspend';
    }
}
EOT;

file_put_contents('D:/dsBilling/app/Livewire/ResellerPortal/Customer/Active.php', $activeClass);
file_put_contents('D:/dsBilling/app/Livewire/ResellerPortal/Customer/Isolated.php', $isolatedClass);
echo "Created Active and Isolated components.\n";
?>
