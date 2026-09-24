<?php
$file = 'app/Services/Adapters/Monitoring/GenieACSDriver.php';
$content = file_get_contents($file);

// Replace ALL occurrences of: "/tasks?connection_request" with "/tasks"
$content = str_replace('"/tasks?connection_request"', '"/tasks"', $content);

// Now, we need to append the async connection request call right after each successful ->post(.../tasks) block.
// Actually, GenieACS NBI has a dedicated endpoint for Connection Requests! 
// It is POST /devices/{id}/tasks?connection_request but with an empty body! 
// Let's just modify the POST requests directly.

// A simpler way: just change the Http::...->post() to do the two steps.
// Since the file might be messy, let's just write a generic regex that matches:
// $response = Http::withBasicAuth(...)->timeout(...)->asJson()->post(".../tasks", $payload);
// and replaces it with the two steps.

$pattern = '/\$response = Http::withBasicAuth\(\$this->username, \$this->password\)\s*->timeout\(\$this->timeout\)\s*->asJson\(\)\s*->post\("\{\$this->baseUrl\}\/devices\/" \. urlencode\(\$deviceId\) \. "\/tasks", \$payload\);/s';

$replacement = <<<'PHP'
$response = Http::withBasicAuth($this->username, $this->password)
                ->timeout($this->timeout)
                ->asJson()
                ->post("{$this->baseUrl}/devices/" . urlencode($deviceId) . "/tasks", $payload);
                
            // Trigger connection request asynchronously without blocking
            try {
                Http::withBasicAuth($this->username, $this->password)
                    ->timeout(1)
                    ->asJson()
                    ->post("{$this->baseUrl}/devices/" . urlencode($deviceId) . "/tasks?connection_request", ['name' => 'refreshObject', 'objectName' => '']);
            } catch (\Exception $e) {}
PHP;

$content = preg_replace($pattern, $replacement, $content);
file_put_contents($file, $content);
echo "GenieACSDriver updated.\n";
