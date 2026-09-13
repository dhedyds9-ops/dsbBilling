<?php
$file = 'app/Livewire/Billing/Invoice/Index.php';
$content = file_get_contents($file);

// Replace render() start
$pattern1 = '/public function render\(\)\s*\{\s*\$query = \$this->buildScopedQuery\(\);/s';
$replacement1 = <<<PHP
public function render()
    {
        // sync activeTab from filters
        if (isset(\$this->filters['status'])) {
            \$this->activeTab = empty(\$this->filters['status']) ? 'all' : \$this->filters['status'];
        }

        \$query = \$this->buildScopedQuery();
PHP;
$content = preg_replace($pattern1, $replacement1, $content);

// Replace statsQuery clone
$pattern2 = '/\$today = Carbon::today\(\)->toDateString\(\);\s*\$statsQuery = clone \$query;\s*\$overdueQuery = clone \$query;/s';
$replacement2 = <<<PHP
\$today = Carbon::today()->toDateString();
        \$statsQuery = \$this->buildScopedQuery(true);
        \$overdueQuery = \$this->buildScopedQuery(true);
PHP;
$content = preg_replace($pattern2, $replacement2, $content);

file_put_contents($file, $content);
echo "Fixed statsQuery in render\n";
