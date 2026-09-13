<?php
$file = 'D:/dsBilling/app/Livewire/ResellerPortal/Dashboard.php';
$content = file_get_contents($file);

$content = preg_replace(
    '/\$hotspotOnline\s*=\s*0;\s*\$pppOnline\s*=\s*0;\s*try\s*\{.*?\$vcLoginToday\s*=\s*\\\\App\\\\Models\\\\ISP\\\\HotspotActiveSession::count\(\);/s',
    <<<'PHP'
        try {
            $hotspotUserCount = HotspotUser::where('reseller_id', $ownerId)->orWhere('created_by', $ownerId)->count();
            $pppoeUserCount = PPPoEUser::where('reseller_id', $ownerId)->orWhere('created_by', $ownerId)->count();
            
            $hotspotOnline = HotspotActiveSession::whereHas('hotspotUser', function ($q) use ($ownerId) {
                $q->where('reseller_id', $ownerId)->orWhere('created_by', $ownerId);
            })->count();
            
            $pppOnline = PppActiveSession::whereHas('pppoeUser', function ($q) use ($ownerId) {
                $q->where('reseller_id', $ownerId)->orWhere('created_by', $ownerId);
            })->count();
        } catch (\Exception $e) {
            $hotspotUserCount = 0;
            $pppoeUserCount = 0;
            $hotspotOnline = 0;
            $pppOnline = 0;
        }

        $totalVoucher = Voucher::where('created_by', $ownerId)->count();
        $vcCreatedToday = Voucher::where('created_by', $ownerId)->whereDate('created_at', today())->count();
        
        // Fix Isolation Leak: Only count active voucher sessions created by this reseller
        $vcLoginToday = \App\Models\ISP\HotspotActiveSession::whereHas('voucher', function($q) use ($ownerId) {
            $q->where('created_by', $ownerId);
        })->count();
PHP,
    $content
);

file_put_contents($file, $content);
echo "Patched using regex.\n";
?>
