<?php
$file = 'app/Livewire/NOC/Onu/Index.php';
$content = file_get_contents($file);

$search = <<<PHP
    public string \$search = '';
    public string \$statusFilter = 'all';
    public string \$oltFilter   = '';
    #[\Livewire\Attributes\Url]
    public string \$ponFilter   = '';
PHP;

$replace = <<<PHP
    #[\Livewire\Attributes\Url]
    public string \$search = '';
    
    #[\Livewire\Attributes\Url]
    public string \$statusFilter = 'all';
    
    #[\Livewire\Attributes\Url]
    public string \$oltFilter   = '';
    
    #[\Livewire\Attributes\Url]
    public string \$ponFilter   = '';
PHP;

$content = str_replace($search, $replace, $content);
file_put_contents($file, $content);
echo "Added Url attributes to filters.\n";
