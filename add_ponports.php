<?php
$file = 'app/Livewire/NOC/Onu/Index.php';
$content = file_get_contents($file);

// add ponPorts computed property before render()
$search = "    #[\Livewire\Attributes\Layout('layouts.noc')]\n    public function render()";

$replace = <<<PHP
    #[Computed]
    public function ponPorts()
    {
        if (empty(\$this->oltFilter) || \$this->oltFilter === 'all') {
            return collect();
        }
        return \App\Models\ISP\PonPort::where('olt_id', \$this->oltFilter)->orderBy('port_index')->get();
    }

    #[\Livewire\Attributes\Layout('layouts.noc')]
    public function render()
PHP;

$content = str_replace($search, $replace, $content);
file_put_contents($file, $content);
echo "Added ponPorts computed property\n";
