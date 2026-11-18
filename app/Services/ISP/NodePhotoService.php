<?php

namespace App\Services\ISP;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class NodePhotoService
{
    /**
     * Convert an uploaded file, base64 payload, or file path to an optimized Base64 Data URI.
     * Uses GD for resizing (max 1000px, 78% quality) to ensure small storage payload (~40KB-60KB).
     */
    public static function createDataUri($file, int $maxDim = 1000, int $quality = 78): ?string
    {
        // If already a data URI, validate and return
        if (is_string($file) && str_starts_with($file, 'data:image')) {
            return $file;
        }

        $realPath = null;
        if (is_string($file) && @file_exists($file)) {
            $realPath = $file;
        } elseif (is_object($file) && method_exists($file, 'getRealPath')) {
            $realPath = $file->getRealPath();
        }

        if (!$realPath || !@file_exists($realPath)) {
            return null;
        }

        // Try GD compression if available
        if (extension_loaded('gd')) {
            try {
                $imgInfo = @getimagesize($realPath);
                if ($imgInfo && !empty($imgInfo[2])) {
                    $src = match ($imgInfo[2]) {
                        IMAGETYPE_JPEG => @imagecreatefromjpeg($realPath),
                        IMAGETYPE_PNG => @imagecreatefrompng($realPath),
                        IMAGETYPE_WEBP => @imagecreatefromwebp($realPath),
                        default => null,
                    };

                    if ($src) {
                        // Fix EXIF orientation for JPEG taken from mobile phones
                        if (function_exists('exif_read_data') && $imgInfo[2] === IMAGETYPE_JPEG) {
                            try {
                                $exif = @exif_read_data($realPath);
                                if (!empty($exif['Orientation'])) {
                                    switch ($exif['Orientation']) {
                                        case 3:
                                            $src = imagerotate($src, 180, 0);
                                            break;
                                        case 6:
                                            $src = imagerotate($src, -90, 0);
                                            break;
                                        case 8:
                                            $src = imagerotate($src, 90, 0);
                                            break;
                                    }
                                }
                            } catch (\Throwable $e) {}
                        }

                        $w = imagesx($src);
                        $h = imagesy($src);
                        if ($w > $maxDim || $h > $maxDim) {
                            $ratio = min($maxDim / $w, $maxDim / $h);
                            $nw = max(1, (int) round($w * $ratio));
                            $nh = max(1, (int) round($h * $ratio));
                            $dst = imagecreatetruecolor($nw, $nh);
                            $white = imagecolorallocate($dst, 255, 255, 255);
                            imagefilledrectangle($dst, 0, 0, $nw, $nh, $white);
                            imagecopyresampled($dst, $src, 0, 0, 0, 0, $nw, $nh, $w, $h);
                            imagedestroy($src);
                            $src = $dst;
                        }

                        ob_start();
                        imagejpeg($src, null, $quality);
                        $compressedBytes = ob_get_clean();
                        imagedestroy($src);

                        if ($compressedBytes && strlen($compressedBytes) > 50) {
                            return 'data:image/jpeg;base64,' . base64_encode($compressedBytes);
                        }
                    }
                }
            } catch (\Throwable $e) {
                Log::warning('[NodePhotoService::createDataUri] GD processing failed: ' . $e->getMessage());
            }
        }

        // Fallback: raw base64 encode directly from file bytes
        try {
            $rawBytes = @file_get_contents($realPath);
            if ($rawBytes) {
                $mime = @mime_content_type($realPath) ?: 'image/jpeg';
                return "data:{$mime};base64," . base64_encode($rawBytes);
            }
        } catch (\Throwable $e) {
            Log::error('[NodePhotoService::createDataUri] Fallback failed: ' . $e->getMessage());
        }

        return null;
    }

    /**
     * Mirror physical file to standard storage paths across environments.
     */
    public static function mirrorPhysicalFile($file, string $folder, ?string $preferredFilename = null): ?string
    {
        $realPath = null;
        if (is_string($file) && @file_exists($file)) {
            $realPath = $file;
        } elseif (is_object($file) && method_exists($file, 'getRealPath')) {
            $realPath = $file->getRealPath();
        }

        if (!$realPath || !@file_exists($realPath)) {
            return null;
        }

        $ext = 'jpg';
        if (is_object($file) && method_exists($file, 'getClientOriginalExtension')) {
            $ext = $file->getClientOriginalExtension() ?: 'jpg';
        }
        $filename = $preferredFilename ?: (Str::random(40) . '.' . $ext);
        $relPath = "network_photos/{$folder}/{$filename}";

        $targets = [
            storage_path("app/public/{$relPath}"),
            public_path("storage/{$relPath}"),
            "/var/www/shared_storage/{$relPath}",
            "/var/www/mstore/storage/app/public/{$relPath}",
            "/var/www/mstore/public/storage/{$relPath}",
            "/var/www/winets/storage/app/public/{$relPath}",
            "/var/www/winets/public/storage/{$relPath}",
        ];

        foreach ($targets as $tp) {
            $dir = dirname($tp);
            if (!@is_dir($dir)) {
                @mkdir($dir, 0775, true);
            }
            @copy($realPath, $tp);
            @chmod($tp, 0664);
        }

        return "/storage/{$relPath}";
    }

    /**
     * Attach photo to a model (stores Base64 Data URI at index 0 AND mirrors relative file path).
     * By default ($replace = true), it replaces existing photos to prevent accumulation of broken/stale images.
     */
    public static function attachPhoto($model, $file, string $nodeType, bool $replace = true): array
    {
        $folder = match ($nodeType) {
            'olt' => 'olts',
            'odc' => 'odcs',
            'closure' => 'closures',
            default => 'odp',
        };

        $dataUri = self::createDataUri($file);
        $relUrl = self::mirrorPhysicalFile($file, $folder);

        $current = is_array($model->photos) ? $model->photos : [];

        $cleaned = [];
        // Data URI goes first for guaranteed immediate rendering without filesystem dependency
        if ($dataUri) {
            $cleaned[] = $dataUri;
        }
        if ($relUrl) {
            $cleaned[] = $relUrl;
        }

        if (!$replace) {
            // Keep previous valid entries up to limit
            foreach ($current as $item) {
                if (empty($item) || $item === $dataUri || $item === $relUrl) {
                    continue;
                }
                if (str_starts_with($item, 'data:image')) {
                    if (count($cleaned) < 3) {
                        $cleaned[] = $item;
                    }
                } else {
                    if (count($cleaned) < 5) {
                        $cleaned[] = $item;
                    }
                }
            }
        }

        $model->photos = array_values(array_unique($cleaned));
        $model->save();

        if (class_exists(\App\Livewire\Gis\GisMap::class)) {
            \App\Livewire\Gis\GisMap::flushCache();
        }

        return [
            'dataUri' => $dataUri,
            'relUrl' => $relUrl,
            'photos' => $model->photos,
        ];
    }

    /**
     * Delete all photos attached to a model and purge physical files from disk.
     */
    public static function deletePhotos($model): bool
    {
        $current = is_array($model->photos) ? $model->photos : [];
        foreach ($current as $p) {
            if (!empty($p) && !str_starts_with($p, 'data:image')) {
                $cleanPath = ltrim(preg_replace('/^https?:\/\/[^\/]+(\/storage\/.*)$/', '$1', (string) $p), '/');
                $cleanPath = preg_replace('/^storage\//', '', $cleanPath);
                $targets = [
                    storage_path("app/public/{$cleanPath}"),
                    public_path("storage/{$cleanPath}"),
                    "/var/www/shared_storage/{$cleanPath}",
                    "/var/www/mstore/storage/app/public/{$cleanPath}",
                    "/var/www/winets/storage/app/public/{$cleanPath}",
                ];
                foreach ($targets as $tp) {
                    if (@file_exists($tp)) @unlink($tp);
                }
            }
        }

        $model->photos = [];
        $model->save();

        if (class_exists(\App\Livewire\Gis\GisMap::class)) {
            \App\Livewire\Gis\GisMap::flushCache();
        }

        return true;
    }
}
