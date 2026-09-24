<?php
$file = 'app/Services/Adapters/Monitoring/GenieACSDriver.php';
$content = file_get_contents($file);

$search = 'public function upsertProvision(string $name, string $script, int $weight = 0): bool';
$replace = <<<'PHP'
    public function upsertVirtualParameter(string $name, string $script): bool
    {
        try {
            $response = Http::withBasicAuth($this->username, $this->password)
                ->withBody($script, 'text/plain')
                ->put("{$this->baseUrl}/virtual_parameters/" . rawurlencode($name));
            return $response->successful();
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('GenieACS upsertVirtualParameter failed: ' . $e->getMessage());
            return false;
        }
    }

    public function upsertProvision(string $name, string $script, int $weight = 0): bool
PHP;
$content = str_replace($search, $replace, $content);
file_put_contents($file, $content);
echo "GenieACSDriver updated.\n";
