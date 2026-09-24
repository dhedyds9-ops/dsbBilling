<?php
$file = 'app/Services/Adapters/Monitoring/GenieACSDriver.php';
$content = file_get_contents($file);

// Fix provisionPppoe
$content = preg_replace(
    '/(public function provisionPppoe.*?\$response = Http::withBasicAuth\(\$this->username, \$this->password\)\s*->timeout\(\$this->timeout\)\s*->asJson\()\s*->post\("\{.*?\}\/tasks\?connection_request", \$payload\);/s',
    "$1\n                ->post(\"{\$this->baseUrl}/devices/\" . urlencode(\$deviceId) . \"/tasks\", \$payload);\n" .
    "            \n" .
    "            try {\n" .
    "                Http::withBasicAuth(\$this->username, \$this->password)->timeout(1)->asJson()->post(\"{\$this->baseUrl}/devices/\" . urlencode(\$deviceId) . \"/tasks?connection_request\", ['name' => 'refreshObject', 'objectName' => '']);\n" .
    "            } catch (\\Exception \$e) {}\n",
    $content
);

// Fix rebootDevice
$content = preg_replace(
    '/(public function rebootDevice.*?\$response = Http::withBasicAuth\(\$this->username, \$this->password\)\s*->timeout\(\$this->timeout\)\s*->asJson\()\s*->post\("\{.*?\}\/tasks\?connection_request", \$payload\);/s',
    "$1\n                ->post(\"{\$this->baseUrl}/devices/\" . urlencode(\$deviceId) . \"/tasks\", \$payload);\n" .
    "            \n" .
    "            try {\n" .
    "                Http::withBasicAuth(\$this->username, \$this->password)->timeout(1)->asJson()->post(\"{\$this->baseUrl}/devices/\" . urlencode(\$deviceId) . \"/tasks?connection_request\", ['name' => 'refreshObject', 'objectName' => '']);\n" .
    "            } catch (\\Exception \$e) {}\n",
    $content
);

// Fix factoryResetDevice
$content = preg_replace(
    '/(public function factoryResetDevice.*?\$response = Http::withBasicAuth\(\$this->username, \$this->password\)\s*->timeout\(\$this->timeout\)\s*->asJson\()\s*->post\("\{.*?\}\/tasks\?connection_request", \$payload\);/s',
    "$1\n                ->post(\"{\$this->baseUrl}/devices/\" . urlencode(\$deviceId) . \"/tasks\", \$payload);\n" .
    "            \n" .
    "            try {\n" .
    "                Http::withBasicAuth(\$this->username, \$this->password)->timeout(1)->asJson()->post(\"{\$this->baseUrl}/devices/\" . urlencode(\$deviceId) . \"/tasks?connection_request\", ['name' => 'refreshObject', 'objectName' => '']);\n" .
    "            } catch (\\Exception \$e) {}\n",
    $content
);

file_put_contents($file, $content);
echo "GenieACSDriver other methods updated to two-step task queuing.\n";
