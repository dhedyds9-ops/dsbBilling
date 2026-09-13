<?php
$file = 'D:/dsBilling/app/Http/Requests/Auth/LoginRequest.php';
$content = file_get_contents($file);

$hotspotLogic = <<<PHP
        if (str_contains(\$identity, '@')) {
PHP;

$insertLogic = <<<PHP
        // Cek apakah username ini adalah username Hotspot
        \$hotspotUser = \App\Models\ISP\HotspotUser::with('customerService.customer')->whereRaw('LOWER(username) = ?', [\$lower])->first();
        if (\$hotspotUser && \$hotspotUser->customerService && \$hotspotUser->customerService->customer && \$hotspotUser->customerService->customer->user_id) {
            \$u = \App\Models\User::find(\$hotspotUser->customerService->customer->user_id);
            if (\$u) return \$u;
        }

        if (str_contains(\$identity, '@')) {
PHP;

$content = str_replace($hotspotLogic, $insertLogic, $content);
file_put_contents($file, $content);
echo "Added Hotspot login support.\n";
?>
