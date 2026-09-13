<?php
$file = 'D:/dsBilling/app/Livewire/Crm/Customer/Customer360.php';
$content = file_get_contents($file);

// 1. Change public $customer to protected $customer
$content = preg_replace('/public \$customer;/', 'protected $customer;', $content);

// 2. We need to pass $customer to the view in render()
// Wait, mount() sets $this->customer. If it's protected, it won't persist across requests.
// So in render(), we MUST fetch it if it's not set.
// But we can just use a getter or fetch it inside render().

// Let's replace the top of render():
$renderStart = <<<PHP
    public function render()
    {
        // Hydrate customer if not set
        if (!\$this->customer) {
            \$this->customer = \App\Models\CRM\Customer::with([
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
            ])->findOrFail(\$this->customerId);
        }
        
        \$customer = \$this->customer;
PHP;

$content = preg_replace('/public function render\(\)\s*\{/', $renderStart, $content);

// 3. Add $customer to compact(...)
$content = preg_replace('/compact\(\s*/', "compact(\n            'customer', ", $content);

// 4. In mount(), we don't need to load the massive eager-loaded relations anymore, or we can just leave it as is.
// Actually we can just remove the massive loading from mount() since render() does it.
$mountSearch = <<<'PHP'
        $this->customer = Customer::with([
            'customerServices.service',
            'customerServices.serviceProfile',
            'customerServices.onu',
            'customerServices.pppoeUser',
            'customerServices.hotspotUser',
            'customerServices.pppoeUser',
            'customerServices.hotspotUser',
            'invoices',
            'payments',
            'contracts',
            'installations', 'tickets',
        ])->findOrFail($this->customerId);
PHP;
$mountReplace = <<<'PHP'
        $this->customer = Customer::findOrFail($this->customerId);
PHP;
$content = str_replace($mountSearch, $mountReplace, $content);

file_put_contents($file, $content);
echo "Refactored Customer360 to not dehydrate the massive Customer model.";
?>
