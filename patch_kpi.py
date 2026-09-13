import os

file_php = 'D:/dsBilling/app/Livewire/Dashboard/Index.php'
with open(file_php, 'r', encoding='utf-8') as f:
    content = f.read()

php_code = """        // 3. Billing Metrics
        try {
            $voucherSold = \\App\\Models\\ISP\\Voucher::whereNotIn('status', ['available', 'expired', 'void'])
                ->whereMonth('updated_at', Carbon::now()->month)
                ->count();
                
            $voucherRevenue = \\App\\Models\\ISP\\Voucher::whereNotIn('vouchers.status', ['available', 'expired', 'void'])
                ->whereMonth('vouchers.updated_at', Carbon::now()->month)
                ->join('service_profiles', 'vouchers.service_profile_id', '=', 'service_profiles.id')
                ->sum(DB::raw('COALESCE(service_profiles.promo_price, service_profiles.base_price, 0)'));
                
            $voucherAvailable = \\App\\Models\\ISP\\Voucher::where('status', 'available')->count();
        } catch (\\Exception $e) {
            $voucherSold = 0; $voucherRevenue = 0; $voucherAvailable = 0;
        }"""
content = content.replace("// 3. Billing Metrics", php_code + "\n\n        // 3. Billing Metrics")

php_arr = """            'billing' => [
                'tagihan' => $tagihan,
                'paid' => $paid,
                'unpaid' => $unpaid,
                'overdue' => $overdue,
            ],
            'voucher' => [
                'sold' => $voucherSold,
                'revenue' => $voucherRevenue,
                'available' => $voucherAvailable,
            ],"""
content = content.replace("""            'billing' => [
                'tagihan' => $tagihan,
                'paid' => $paid,
                'unpaid' => $unpaid,
                'overdue' => $overdue,
            ],""", php_arr)

with open(file_php, 'w', encoding='utf-8') as f:
    f.write(content)
print("Updated PHP controller")
