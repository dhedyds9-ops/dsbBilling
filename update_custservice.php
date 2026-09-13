<?php
$file = 'D:/dsBilling/app/Models/Customer/CustomerService.php';
$content = file_get_contents($file);

if (strpos($content, 'acsDevice()') === false) {
    $relation = <<<PHP
    public function acsDevice()
    {
        return \$this->hasOne(\App\Models\ACS\ACSDevice::class, 'customer_service_id');
    }
PHP;
    $content = preg_replace('/(}\s*)$/s', "\n$relation\n$1", $content);
    file_put_contents($file, $content);
}
echo "Added acsDevice relation.";
?>
