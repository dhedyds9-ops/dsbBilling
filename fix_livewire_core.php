<?php
$file = 'D:/dsBilling/vendor/livewire/livewire/src/Drawer/Utils.php';
$content = file_get_contents($file);

// Find escapeStringForHtml and completely sanitize it
$search = <<<PHP
        try {
            return htmlspecialchars(json_encode(\$subject, JSON_THROW_ON_ERROR), ENT_QUOTES|ENT_SUBSTITUTE);
        } catch (\JsonException \$e) {
            \Log::error("Livewire json_encode failed in escapeStringForHtml", [
                'error' => \$e->getMessage(),
                'subject_keys' => is_array(\$subject) ? array_keys(\$subject) : gettype(\$subject)
            ]);
            
            // Try to find the exact bad string
            \$findBad = function(\$arr, \$path = "") use (&\$findBad) {
                if (is_array(\$arr)) {
                    foreach (\$arr as \$k => \$v) {
                        \$findBad(\$v, \$path ? "\$path.\$k" : \$k);
                    }
                } elseif (is_string(\$arr)) {
                    if (!mb_check_encoding(\$arr, 'UTF-8')) {
                        \Log::error("Found bad UTF-8 string at path: " . \$path);
                    }
                }
            };
            if (is_array(\$subject)) \$findBad(\$subject);
            
            throw \$e;
        }
PHP;

$replace = <<<PHP
        \$encoded = json_encode(\$subject, JSON_INVALID_UTF8_SUBSTITUTE);
        if (\$encoded === false) {
            \$encoded = json_encode(['error' => 'Malformed UTF-8']);
        }
        return htmlspecialchars(\$encoded, ENT_QUOTES|ENT_SUBSTITUTE);
PHP;

$content = str_replace($search, $replace, $content);
file_put_contents($file, $content);
echo "Replaced JsonException throw with JSON_INVALID_UTF8_SUBSTITUTE in Livewire Utils!";
?>
