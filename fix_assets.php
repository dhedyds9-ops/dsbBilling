<?php
$files = [
    'resources/views/landing.blade.php',
    'resources/views/auth/login-admin.blade.php',
    'resources/views/auth/login-customer.blade.php'
];

foreach ($files as $file) {
    if (file_exists($file)) {
        $content = file_get_contents($file);
        $content = str_replace("asset('storage/' . \$companyLogo)", "asset(\$companyLogo)", $content);
        file_put_contents($file, $content);
    }
}
echo "Fixed storage asset paths.\n";
