<?php
$file = 'app/Livewire/NOC/Onu/Index.php';
$content = file_get_contents($file);

// Update perPage attribute and paginate logic
$search = <<<PHP
    public int \$perPage = 25;
PHP;
$replace = <<<PHP
    #[\Livewire\Attributes\Url]
    public int \$perPage = 25;
PHP;
$content = str_replace($search, $replace, $content);

$search2 = <<<PHP
            ->paginate(\$this->perPage);
PHP;
$replace2 = <<<PHP
            ->paginate(\$this->perPage === 0 ? 1000000 : \$this->perPage);
PHP;
$content = str_replace($search2, $replace2, $content);

// Add updatedPerPage to reset pagination
$search3 = <<<PHP
    public function updatedPonFilter(): void
    {
        \$this->resetPage();
    }
PHP;
$replace3 = <<<PHP
    public function updatedPonFilter(): void
    {
        \$this->resetPage();
    }

    public function updatedPerPage(): void
    {
        \$this->resetPage();
    }
PHP;
$content = str_replace($search3, $replace3, $content);

file_put_contents($file, $content);
echo "Updated backend logic for perPage\n";
