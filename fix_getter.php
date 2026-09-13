<?php
$file = 'D:/dsBilling/app/Livewire/Crm/Customer/Customer360.php';
$content = file_get_contents($file);

$getCustomerMethod = <<<'PHP'
    protected function getCustomer()
    {
        if (!$this->customer) {
            $this->customer = \App\Models\CRM\Customer::with([
                'customerServices.service',
                'customerServices.serviceProfile',
                'customerServices.onu.odp.odc',
                'customerServices.onu.ponPort',
                'customerServices.onu.olt',
                'customerServices.pppoeUser',
                'customerServices.hotspotUser',
                'invoices',
                'payments',
                'contracts',
                'installations', 'tickets',
            ])->findOrFail($this->customerId);
        }
        return $this->customer;
    }
PHP;

// Insert getCustomer method after public $wifiPassword;
$content = preg_replace('/(public \$wifiPassword = \'\';)/', "$1\n\n$getCustomerMethod\n", $content);

// Replace all $this->customer with $this->getCustomer() ONLY where needed (like in openEditModal, updateCustomer)
// Actually, let's just do a global replace for $this->customer -> $this->getCustomer()
// EXCEPT in the getCustomer() method itself!
$content = str_replace('$this->customer->', '$this->getCustomer()->', $content);
$content = str_replace('if (!$this->getCustomer())', 'if (!$this->customer)', $content);
$content = str_replace('$this->getCustomer() =', '$this->customer =', $content);

// Fix getCustomer method which got messed up by the global replace
$content = preg_replace('/protected function getCustomer\(\)\s*\{\s*if \(!\$this->customer\) \{\s*\$this->customer = ([\s\S]*?)findOrFail\(\$this->customerId\);\s*\}\s*return \$this->getCustomer\(\);\s*\}/', 
<<<'PHP'
    protected function getCustomer()
    {
        if (!$this->customer) {
            $this->customer = \App\Models\CRM\Customer::with([
                'customerServices.service',
                'customerServices.serviceProfile',
                'customerServices.onu.odp.odc',
                'customerServices.onu.ponPort',
                'customerServices.onu.olt',
                'customerServices.pppoeUser',
                'customerServices.hotspotUser',
                'invoices',
                'payments',
                'contracts',
                'installations', 'tickets',
            ])->findOrFail($this->customerId);
        }
        return $this->customer;
    }
PHP
, $content);

// Fix render() method top part
$renderStart = <<<PHP
    public function render()
    {
        \$customer = \$this->getCustomer();
PHP;
$content = preg_replace('/public function render\(\)\s*\{[\s\S]*?\$customer = \$this->getCustomer\(\);/', $renderStart, $content);

file_put_contents($file, $content);
echo "Added getCustomer() getter to handle protected property hydration.";
?>
