<?php
$html = file_get_contents('D:/dsBilling/resources/views/livewire/customer-portal/self-service/speed-test.blade.php');
libxml_use_internal_errors(true);
$dom = new DOMDocument;
$dom->loadHTML($html);
foreach (libxml_get_errors() as $error) {
    echo "Line {$error->line}: {$error->message}\n";
}
?>
