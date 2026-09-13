<?php
$files = [
    'D:/dsBilling/app/Livewire/ResellerPortal/Customer/Create.php',
    'D:/dsBilling/app/Livewire/ResellerPortal/Customer/CreateHotspot.php'
];

foreach ($files as $file) {
    if (!file_exists($file)) continue;
    $content = file_get_contents($file);
    
    // Replace the generic toast message with a specific one
    $search = "catch (\\Illuminate\\Validation\\ValidationException \$e) {\n            \$this->dispatch('toast', type: 'error', message: 'Ada kolom wajib yang belum diisi atau salah!');\n            throw \$e;\n        }";
    $replace = "catch (\\Illuminate\\Validation\\ValidationException \$e) {\n            \$errors = \$e->validator->errors()->all();\n            \$errorMsg = implode('<br>', \$errors);\n            \$this->dispatch('toast', [\n                'type' => 'error',\n                'message' => \$errorMsg ?: 'Ada kolom wajib yang belum diisi atau salah!'\n            ]);\n            throw \$e;\n        }";
    
    $content = str_replace($search, $replace, $content);
    
    file_put_contents($file, $content);
}
echo "Updated validation error messages.\n";
?>
