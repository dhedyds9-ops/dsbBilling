<?php
$driver = new \App\Services\Adapters\Monitoring\GenieACSDriver();
$params = $driver->getDeviceParameters("00259E-HG8245H5-48575443B421579D");

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

foreach ($flatParams as $k => $v) {
    if (stripos($k, 'rx') !== false || stripos($k, 'tx') !== false || stripos($k, 'power') !== false || stripos($k, 'optic') !== false || stripos($k, 'pon') !== false) {
        echo "$k = $v\n";
    }
}
