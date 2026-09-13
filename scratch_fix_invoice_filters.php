<?php
$file = 'app/Livewire/Billing/Invoice/Index.php';
$content = file_get_contents($file);

$mountPattern = '/\$this->filters\s*=\s*\[\s*\'status\'\s*=>\s*\'unpaid\',\s*\'service_type\'\s*=>\s*\'all\',\s*\'reseller_id\'\s*=>\s*\'all\'\s*\];/s';

$mountReplacement = <<<PHP
        \$reqFilters = request('filters', []);
        \$defaultStatus = (isset(\$reqFilters['tahun']) || isset(\$reqFilters['bulan'])) ? '' : 'unpaid';
        
        \$this->filters = [
            'status' => \$reqFilters['status'] ?? \$defaultStatus,
            'service_type' => \$reqFilters['service_type'] ?? 'all',
            'reseller_id' => \$reqFilters['reseller_id'] ?? 'all',
            'tahun' => \$reqFilters['tahun'] ?? '',
            'bulan' => \$reqFilters['bulan'] ?? ''
        ];
PHP;

$content = preg_replace($mountPattern, $mountReplacement, $content);

$queryPattern = '/if \(!empty\(\$this->filters\[\'reseller_id\'\]\) && \$this->filters\[\'reseller_id\'\] !== \'all\'\) \{/';

$queryReplacement = <<<PHP
        if (!empty(\$this->filters['tahun'])) {
            \$query->whereYear('issue_date', \$this->filters['tahun']);
        }
        if (!empty(\$this->filters['bulan'])) {
            \$query->whereMonth('issue_date', \$this->filters['bulan']);
        }

        if (!empty(\$this->filters['reseller_id']) && \$this->filters['reseller_id'] !== 'all') {
PHP;

$content = preg_replace($queryPattern, $queryReplacement, $content);

file_put_contents($file, $content);
echo "Updated Invoice Index filters\n";
