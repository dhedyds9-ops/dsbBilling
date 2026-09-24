<?php
$item = \App\Models\ISP\RadiusAccounting::first();
print_r($item ? array_keys($item->toArray()) : "empty");
