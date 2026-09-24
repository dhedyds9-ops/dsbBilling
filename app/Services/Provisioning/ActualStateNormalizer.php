<?php

namespace App\Services\Provisioning;

use App\Models\ISP\Onu;
use App\Models\ISP\OnuParameterMapping;

class ActualStateNormalizer
{
    public function normalize(Onu $onu, array $rawParams, array $desiredState): array
    {
        $actualState = [];
        
        // Flatten desired state to semantic keys
        $semanticKeys = $this->flatten($desiredState);
        
        // Load mappings for this ONU
        $mappings = OnuParameterMapping::where('onu_id', $onu->id)
            ->whereIn('semantic_key', array_keys($semanticKeys))
            ->get()
            ->keyBy('semantic_key');

        foreach ($semanticKeys as $key => $expectedValue) {
            $mapping = $mappings->get($key);
            if ($mapping && $this->pathExists($rawParams, $mapping->actual_path)) {
                $actualValue = $this->extractParamValue($rawParams, $mapping->actual_path);
                // Convert back types if necessary (GenieACS returns strings)
                if (is_bool($expectedValue)) {
                    $actualValue = filter_var($actualValue, FILTER_VALIDATE_BOOLEAN);
                } elseif (is_int($expectedValue)) {
                    $actualValue = (int)$actualValue;
                }
                
                // Normalizer reverse mapping
                if (preg_match('/^wan\.(\d+)\.mode$/', $key)) {
                    if ($actualValue === 'IP_Routed' || $actualValue === 'PPPoE_Routed') {
                        $actualValue = 'pppoe';
                    }
                }

                $this->setNestedValue($actualState, $key, $actualValue);
            }
        }

        return $actualState;
    }

    public function flatten(array $array, string $prefix = ''): array
    {
        $result = [];
        foreach ($array as $key => $value) {
            $newKey = $prefix === '' ? $key : $prefix . '.' . $key;
            if (is_array($value) && !array_is_list($value)) {
                $result = array_merge($result, $this->flatten($value, $newKey));
            } else {
                // If it's a list (like ["lan1", "lan2"]), we might want to treat it specially,
                // but for simple key-value, let's keep it as is.
                $result[$newKey] = $value;
            }
        }
        return $result;
    }

    private function setNestedValue(array &$array, string $path, $value): void
    {
        $keys = explode('.', $path);
        $current = &$array;
        foreach ($keys as $i => $key) {
            if ($i === count($keys) - 1) {
                $current[$key] = $value;
            } else {
                if (!isset($current[$key]) || !is_array($current[$key])) {
                    $current[$key] = [];
                }
                $current = &$current[$key];
            }
        }
    }

    private function pathExists(array $params, string $path): bool
    {
        $parts = explode('.', $path);
        $node = $params;
        foreach ($parts as $p) {
            if (!is_array($node) || !array_key_exists($p, $node)) return false;
            $node = $node[$p];
        }
        return true;
    }

    private function extractParamValue(array $params, string $path)
    {
        $parts = explode('.', $path);
        $node = $params;
        foreach ($parts as $p) {
            if (!is_array($node) || !array_key_exists($p, $node)) return null;
            $node = $node[$p];
        }
        if (is_array($node) && isset($node['_value'])) {
            return $node['_value'];
        }
        if (is_scalar($node)) return $node;
        return null;
    }
}
