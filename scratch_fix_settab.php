<?php
$file = 'app/Livewire/Billing/Invoice/Index.php';
$content = file_get_contents($file);

$oldCode = <<<PHP
    public function setTab(\$tab)
    {
        if (!in_array(\$tab, ['all', 'unpaid', 'paid', 'overdue'], true)) {
            \$tab = 'unpaid';
        }
        \$this->activeTab = \$tab;
PHP;

$newCode = <<<PHP
    public function setTab(\$tab)
    {
        if (!in_array(\$tab, ['all', 'unpaid', 'paid', 'overdue'], true)) {
            \$tab = 'unpaid';
        }
        \$this->activeTab = \$tab;
        \$this->filters['status'] = (\$tab === 'all') ? '' : \$tab;
PHP;

$content = str_replace($oldCode, $newCode, $content);
file_put_contents($file, $content);
echo "Fixed setTab\n";
