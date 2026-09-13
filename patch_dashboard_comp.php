<?php
$file = 'D:/dsBilling/app/Livewire/CustomerPortal/Dashboard.php';
$content = file_get_contents($file);

$searchStr = "class Dashboard extends Component";
$replaceStr = "use Livewire\Attributes\Layout;\n\n#[Layout('layouts.customer-app')]\nclass Dashboard extends Component";

$content = str_replace($searchStr, $replaceStr, $content);
file_put_contents($file, $content);
echo "Dashboard component fixed.\n";
?>
