<?php
$iter = new RecursiveIteratorIterator(new RecursiveDirectoryIterator("app"));
foreach($iter as $file) {
    if($file->isFile() && $file->getExtension() === "php") {
        $c = file_get_contents($file->getPathname());
        $r = str_replace(["App\\Models\\AAA", "App\\Services\\AAA", "App\\Models\\Customer\\Contract"], ["App\\Models\\ISP", "App\\Services\\ISP", "App\\Models\\CRM\\Contract"], $c);
        if($c !== $r) {
            file_put_contents($file->getPathname(), $r);
            echo "Updated: " . $file->getPathname() . "\n";
        }
    }
}

