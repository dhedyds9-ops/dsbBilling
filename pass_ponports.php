<?php
$file = 'app/Livewire/NOC/Onu/Index.php';
$content = file_get_contents($file);
$content = str_replace("'olts'    => \$this->olts,", "'olts'    => \$this->olts,\n            'ponPorts' => \$this->ponPorts,", $content);
file_put_contents($file, $content);

$file = 'app/Livewire/ISP/Onu/Index.php';
$content = file_get_contents($file);
$content = str_replace("'olts'    => \$this->olts,", "'olts'    => \$this->olts,\n            'ponPorts' => \$this->ponPorts,", $content);
file_put_contents($file, $content);
echo "Passed ponPorts to view\n";
