<?php
$models = [
    "app/Models/Billing/Invoice.php",
    "app/Models/Customer/CustomerService.php",
    "app/Models/ISP/ServiceProfile.php",
    "app/Models/ISP/Voucher.php",
    "app/Models/Keuangan/Expense.php",
    "app/Models/Payment/Payment.php",
    "app/Models/Support/Ticket.php"
];

foreach ($models as $file) {
    if (!file_exists($file)) continue;
    $content = file_get_contents($file);
    if (strpos($content, "HasResellerScope") === false) {
        // Add use App\Traits\HasResellerScope; at the top
        $content = preg_replace("/namespace ([^;]+);/", "namespace $1;\n\nuse App\Traits\HasResellerScope;", $content);
        // Add use HasResellerScope; inside the class
        $content = preg_replace("/class ([^{]+)\s*\{/", "class $1 {\n    use HasResellerScope;\n", $content);
        file_put_contents($file, $content);
        echo "Applied to $file\n";
    }
}

