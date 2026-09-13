<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use RouterOS\Client;
use RouterOS\Query;

$router = App\Models\ISP\Router::first();
if (!$router) die("No router\n");

$client = new Client([
    'host' => $router->ip_address,
    'user' => $router->username,
    'pass' => $router->password,
    'port' => (int) $router->api_port
]);

$interfaces = $client->query((new Query('/interface/print')))->read();
$names = [];
foreach($interfaces as $i) {
    if ($i['type'] != 'pppoe-in' && $i['type'] != 'hotspot') {
        $names[] = $i['name'];
    }
}
if(empty($names)) die("no interfaces");

$namesStr = implode(',', $names);
$q = new Query('/interface/monitor-traffic');
$q->equal('interface', $namesStr);
$q->equal('once', '');
$res = $client->query($q)->read();

print_r($res);
