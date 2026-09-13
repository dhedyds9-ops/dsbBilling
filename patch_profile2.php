<?php
$file = 'D:/dsBilling/resources/views/components/admin/profile-menu.blade.php';
$content = file_get_contents($file);

$search = <<<'PHP'
$userRole = 'Karyawan';
if ($user) {
    if ($user->hasRole('reseller')) {
        $userRole = 'Reseller';
    } elseif ($user->hasRole('customer')) {
        $userRole = 'Pelanggan';
    } else {
        $userRole = $user->job_function ?: 'Karyawan';
    }
}
PHP;

$replace = <<<'PHP'
$userRole = 'Karyawan';
if ($user) {
    if ($user->hasRole('administrator')) {
        $userRole = 'Administrator';
    } elseif ($user->hasRole('reseller')) {
        $userRole = 'Reseller';
    } elseif ($user->hasRole('customer')) {
        $userRole = 'Pelanggan';
    } elseif ($user->hasRole('manager')) {
        $userRole = $user->job_function ?: 'Manager';
    } else {
        $userRole = $user->job_function ?: 'Karyawan';
    }
}
PHP;

$content = str_replace($search, $replace, $content);
file_put_contents($file, $content);
echo "Patched profile menu for administrator.\n";
?>
