<?php
$techFiles = [
    'D:/dsBilling/app/Livewire/ISP/Technician/Dashboard.php',
    'D:/dsBilling/app/Livewire/ISP/Technician/MyJobs/Index.php',
    'D:/dsBilling/app/Livewire/ISP/Technician/MyJobs/Show.php',
    'D:/dsBilling/app/Livewire/ISP/Technician/Installation/Wizard.php',
    'D:/dsBilling/app/Livewire/ISP/Technician/Attendance/Index.php',
    'D:/dsBilling/app/Livewire/ISP/Technician/Material/Index.php',
    'D:/dsBilling/app/Livewire/ISP/Technician/Provisioning/Show.php',
    'D:/dsBilling/app/Livewire/ISP/Technician/QC/Show.php',
];

foreach ($techFiles as $path) {
    if (!file_exists($path)) { echo "SKIP: $path\n"; continue; }
    $content = file_get_contents($path);
    
    // Remove AdminComponent usage
    $content = str_replace('use App\Livewire\AdminComponent;', '', $content);
    $content = str_replace('use App\Livewire\Admin\AdminComponent;', '', $content);
    $content = str_replace('extends AdminComponent', 'extends Component', $content);
    
    // Add Livewire Component use if not present
    if (strpos($content, 'use Livewire\Component;') === false) {
        $content = str_replace('use Livewire\Attributes\Layout;', '', $content);
        $content = str_replace('use App\Models', "use Livewire\Component;\nuse Livewire\Attributes\Layout;\nuse App\Models", $content);
        // If still no use Livewire\Component, add after namespace
        if (strpos($content, 'use Livewire\Component;') === false) {
            $content = preg_replace('/(namespace .+;)/', "$1\n\nuse Livewire\Component;\nuse Livewire\Attributes\Layout;", $content);
        }
    } else {
        if (strpos($content, 'use Livewire\Attributes\Layout;') === false) {
            $content = str_replace('use Livewire\Component;', "use Livewire\Component;\nuse Livewire\Attributes\Layout;", $content);
        }
    }
    
    // Add #[Layout] attribute before class declaration if not present
    if (strpos($content, "#[Layout('layouts.technician-app')]") === false) {
        $content = preg_replace('/(class \w+ extends Component)/', "#[Layout('layouts.technician-app')]\n$1", $content);
    }
    
    // Remove parent::mount() calls that set admin breadcrumbs etc
    $content = preg_replace('/\s*parent::mount\(\);?\s*\n/', "\n", $content);
    $content = preg_replace('/\s*\$this->activeModule\s*=\s*[^\n]+\n/', "\n", $content);
    $content = preg_replace('/\s*\$this->activePage\s*=\s*[^\n]+\n/', "\n", $content);
    $content = preg_replace('/\s*\$this->breadcrumbs\s*=\s*\[[^\]]+\];\s*\n/', "\n", $content);
    
    file_put_contents($path, $content);
    echo "Updated: $path\n";
}
echo "All done!\n";
?>
