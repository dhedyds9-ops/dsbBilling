<?php

namespace App\Services\Provisioning;

class StateCanonicalizer
{
    /**
     * Normalize the array by sorting keys recursively, removing sensitive data, 
     * and casting types correctly to ensure identical hashes for identical configurations.
     */
    public function canonicalize(array $state, bool $removeSensitive = true): array
    {
        $normalized = $this->recursiveNormalize($state);
        
        if ($removeSensitive) {
            $normalized = $this->redactSensitive($normalized);
        }

        return $normalized;
    }

    public function hash(array $state, bool $removeSensitive = true): string
    {
        $canonical = $this->canonicalize($state, $removeSensitive);
        // Using JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE for stable output
        $json = json_encode($canonical, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        return hash('sha256', $json);
    }

    private function recursiveNormalize(array $array): array
    {
        ksort($array);
        foreach ($array as $key => &$value) {
            if (is_array($value)) {
                $value = $this->recursiveNormalize($value);
            } elseif (is_bool($value)) {
                // Ensure strict boolean
                $value = (bool)$value;
            } elseif (is_numeric($value)) {
                // Cast to numeric types appropriately
                $value = strpos((string)$value, '.') !== false ? (float)$value : (int)$value;
            } elseif ($value === null) {
                // Ignore nulls for canonicalization if they mean "not set"
                unset($array[$key]);
            }
        }
        return $array;
    }

    private function redactSensitive(array $array): array
    {
        // Simple redaction strategy: remove keys with 'password' or 'secret'
        foreach ($array as $key => &$value) {
            if (is_array($value)) {
                $value = $this->redactSensitive($value);
            } else {
                if (str_contains(strtolower($key), 'password') || str_contains(strtolower($key), 'secret')) {
                    $value = '***REDACTED***';
                }
            }
        }
        return $array;
    }
}
