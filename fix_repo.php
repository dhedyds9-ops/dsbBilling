<?php
$f1 = "app/Repositories/ISP/VoucherRepository.php";
file_put_contents($f1, str_replace(["App\\Repositories\\AAA", "App\\Models\\AAA"], ["App\\Repositories\\ISP", "App\\Models\\ISP"], file_get_contents($f1)));
$f2 = "app/Services/ISP/VoucherService.php";
file_put_contents($f2, str_replace(["App\\Repositories\\AAA"], ["App\\Repositories\\ISP"], file_get_contents($f2)));

