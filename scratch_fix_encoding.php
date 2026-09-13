<?php
$file = 'resources/views/livewire/pengaturan/perusahaan/index.blade.php';
$content = file_get_contents($file);

// Clean up mojibake
$content = str_replace('ðŸ“ž', '📞', $content);
$content = str_replace('ðŸ“±', '📱', $content);
$content = str_replace('âœ‰ï¸', '✉️', $content);
$content = str_replace('ðŸŒ', '🌐', $content);
$content = str_replace('ðŸ“', '📍', $content);
$content = str_replace('Â·', '·', $content);
$content = str_replace('â‰¥', '≥', $content);

// Ensure proper UTF-8 encoding
$content = mb_convert_encoding($content, 'UTF-8', 'auto');

file_put_contents($file, $content);
echo "Fixed encoding\n";
