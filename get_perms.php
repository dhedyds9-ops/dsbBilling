<?php
$role = App\Models\Role::where('name', 'reseller')->first();
if ($role) {
    echo "Permissions for role 'reseller':\n";
    foreach ($role->permissions as $perm) {
        echo "- " . $perm->name . "\n";
    }
} else {
    echo "Role not found.\n";
}
?>
