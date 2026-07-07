<?php

$dir1 = __DIR__ . '/bootstrap/cache';
$dir2 = __DIR__ . '/storage/framework/cache';

echo "Checking $dir1...\n";
echo "is_dir(): " . (is_dir($dir1) ? 'Yes' : 'No') . "\n";
echo "is_writable(): " . (is_writable($dir1) ? 'Yes' : 'No') . "\n";

echo "\nChecking $dir2...\n";
echo "is_dir(): " . (is_dir($dir2) ? 'Yes' : 'No') . "\n";
echo "is_writable(): " . (is_writable($dir2) ? 'Yes' : 'No') . "\n";

echo "\nTrying to write a test file in $dir1...\n";
$testFile = $dir1 . '/test.txt';
if (file_put_contents($testFile, 'test')) {
    echo "SUCCESS: Wrote test file to $testFile\n";
    unlink($testFile);
} else {
    echo "FAILED: Could not write test file to $dir1\n";
    echo "Error: " . error_get_last()['message'] . "\n";
}
