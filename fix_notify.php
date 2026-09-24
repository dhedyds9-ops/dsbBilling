<?php
$file = 'app/Livewire/ACS/Device/Show.php';
$content = file_get_contents($file);

$search = "            session()->flash('error', 'Gagal mengganti WiFi: ' . \$e->getMessage());";
$replace = $search . "\n            \$this->dispatch('notify', ['type' => 'error', 'message' => 'Gagal mengganti WiFi: ' . \$e->getMessage()]);";

$content = str_replace($search, $replace, $content);
file_put_contents($file, $content);
echo "Show.php updated with notify dispatch.\n";
