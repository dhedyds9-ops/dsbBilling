<?php
require "vendor/autoload.php";
$app = require_once "bootstrap/app.php";
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();
try {
    $view = view("livewire.isp.service-profile.index", [
        "profiles" => new \Illuminate\Pagination\LengthAwarePaginator(collect([]), 0, 10),
        "profileTypes" => collect([]),
        "isSuperAdmin" => false,
        "selectedPackages" => [],
        "selectAll" => false,
        "search" => "",
        "filters" => [],
        "showTrashed" => false,
        "showImportModal" => false,
        "showBulkEditModal" => false,
        "bulkEditData" => [],
    ]);
    echo $view->render();
    echo "\nOK\n";
} catch (\Throwable $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    echo "Line: " . $e->getLine() . "\n";
    echo "File: " . $e->getFile() . "\n";
}
