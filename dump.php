<?php
$driver = new \App\Services\Adapters\Monitoring\GenieACSDriver();
$params = $driver->getDeviceParameters("000AC2-HG6145F-FHTT9B069E50");

$flatParams = [];
$flatten = function($array, $prefix) use (&$flatten, &$flatParams) {
    foreach ($array as $key => $value) {
        $newKey = $prefix === '' ? $key : $prefix . '.' . $key;
        if (is_array($value) && !isset($value['_value'])) {
            $flatten($value, $newKey);
        } elseif (is_array($value) && isset($value['_value'])) {
            $flatParams[$newKey] = $value['_value'];
        }
    }
};
$flatten($params, '');

file_put_contents("hg6145f_dump.txt", print_r($flatParams, true));
echo "Dumped to hg6145f_dump.txt\n";
