<?php
$file = 'app/Services/Adapters/Monitoring/GenieACSDriver.php';
$content = file_get_contents($file);

$search = <<<'PHP'
          try {
              $payload = ['name' => 'setParameterValues', 'parameterValues' => $parameterValues];
              $response = Http::withBasicAuth($this->username, $this->password)
                  ->timeout($this->timeout)
                  ->asJson()
                  ->post("{$this->baseUrl}/devices/" . urlencode($deviceId) . "/tasks?connection_request", $payload);
              if ($response->successful()) {
                  return true;
              }
              $body = (string)$response->body();
              throw new Exception("setParameterValues HTTP {$response->status()}: {$body}");
          } catch (ConnectionException|RequestException $e) {
              if ($e instanceof RequestException && $e->response && $e->response->status() === 404) {
                  throw new Exception("Perangkat tidak ditemukan di GenieACS", 0, $e);
              }
              throw new Exception("GenieACS tidak dapat dihubungi: " . $e->getMessage(), 0, $e);
          }
PHP;

$replace = <<<'PHP'
          try {
              // 1. Queue the task WITHOUT connection_request so it returns instantly and we know it's saved.
              $payload = ['name' => 'setParameterValues', 'parameterValues' => $parameterValues];
              $response = Http::withBasicAuth($this->username, $this->password)
                  ->timeout($this->timeout)
                  ->asJson()
                  ->post("{$this->baseUrl}/devices/" . urlencode($deviceId) . "/tasks", $payload);
              
              if (!$response->successful()) {
                  $body = (string)$response->body();
                  throw new Exception("setParameterValues HTTP {$response->status()}: {$body}");
              }

              // 2. Trigger connection request asynchronously (fire and forget with 1 second timeout)
              // We just send a dummy refresh task to wake up the CPE.
              try {
                  $dummyPayload = ['name' => 'refreshObject', 'objectName' => 'InternetGatewayDevice.DeviceInfo.UpTime'];
                  Http::withBasicAuth($this->username, $this->password)
                      ->timeout(1)
                      ->asJson()
                      ->post("{$this->baseUrl}/devices/" . urlencode($deviceId) . "/tasks?connection_request", $dummyPayload);
              } catch (\Exception $e) {
                  // Ignore timeout or any error on the wake-up call
              }

              return true;
          } catch (ConnectionException|RequestException $e) {
              if ($e instanceof RequestException && $e->response && $e->response->status() === 404) {
                  throw new Exception("Perangkat tidak ditemukan di GenieACS", 0, $e);
              }
              throw new Exception("GenieACS tidak dapat dihubungi: " . $e->getMessage(), 0, $e);
          }
PHP;

$content = str_replace($search, $replace, $content);
file_put_contents($file, $content);
echo "GenieACSDriver updated to two-step task queuing.\n";
