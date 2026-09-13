<?php
$file = 'D:/dsBilling/app/Services/CustomerPortal/CustomerDashboardService.php';
$content = file_get_contents($file);

$searchStr = "return [
            'customer_services' => \$customerServices,";

$replaceStr = "\$subscription = \App\Models\Billing\Subscription::where('customer_id', \$customerId)->where('status', 'active')->first();
        return [
            'next_billing_date' => \$subscription ? \$subscription->next_billing_date : null,
            'customer_services' => \$customerServices,";

$content = str_replace($searchStr, $replaceStr, $content);
file_put_contents($file, $content);
echo "Added next_billing_date to Dashboard Service.\n";
?>
