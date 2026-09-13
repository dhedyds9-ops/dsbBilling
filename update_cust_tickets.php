<?php
$file = 'D:/dsBilling/app/Models/CRM/Customer.php';
$content = file_get_contents($file);

if (strpos($content, 'tickets()') === false) {
    $relation = <<<PHP
    public function tickets(): HasMany
    {
        return \$this->hasMany(\App\Models\Support\Ticket::class, 'customer_id', 'user_id');
    }
PHP;
    $content = preg_replace('/(}\s*)$/s', "\n$relation\n$1", $content);
    file_put_contents($file, $content);
}
echo "Added tickets relation to Customer.";
?>
