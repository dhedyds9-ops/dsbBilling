<?php

namespace App\Console\Commands\ISP;

use App\Models\ISP\JointClosure;
use App\Models\ISP\Odc;
use App\Models\ISP\Odp;
use App\Models\ISP\Olt;
use App\Services\ISP\NodePhotoService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class RepairNetworkPhotosCommand extends Command
{
    protected $signature = 'isp:repair-photos {--force : Remove dead photo references without confirmation}';
    protected $description = 'Audit, repair, and convert physical network node photos to permanent Base64 Data URIs, and purge dead 404 links';

    public function handle()
    {
        $this->info("=== dsBilling Network Photos Repair & Optimization Tool ===");

        // 1. Ensure storage directories exist with proper write permissions
        $subdirs = ['odcs', 'odp', 'olts', 'closures'];
        foreach ($subdirs as $dir) {
            $localPath = storage_path("app/public/network_photos/{$dir}");
            if (!File::isDirectory($localPath)) {
                File::makeDirectory($localPath, 0775, true, true);
                $this->line("Created directory: {$localPath}");
            }
            @chmod($localPath, 0775);
        }

        // 2. Scan candidate search roots on production servers
        $candidateRoots = [
            storage_path('app/public'),
            storage_path('app'),
            public_path('storage'),
            '/var/www/shared_storage',
            '/var/www/mstore/storage/app/public',
            '/var/www/winets/storage/app/public',
            '/var/www/mstore/public/storage',
            '/var/www/winets/public/storage',
        ];

        $modelsConfig = [
            'ODC' => ['class' => Odc::class, 'folder' => 'odcs'],
            'ODP' => ['class' => Odp::class, 'folder' => 'odp'],
            'OLT' => ['class' => Olt::class, 'folder' => 'olts'],
            'Closure' => ['class' => JointClosure::class, 'folder' => 'closures'],
        ];

        $totalChecked = 0;
        $totalRepaired = 0;
        $totalPurged = 0;

        foreach ($modelsConfig as $label => $cfg) {
            if (!class_exists($cfg['class'])) continue;
            $items = $cfg['class']::withoutGlobalScopes()->whereNotNull('photos')->get();

            foreach ($items as $item) {
                $photos = is_array($item->photos) ? $item->photos : [];
                if (empty($photos)) continue;

                $totalChecked++;
                $newPhotos = [];
                $hasBase64 = false;
                $changed = false;

                foreach ($photos as $p) {
                    if (empty($p)) continue;

                    if (str_starts_with($p, 'data:image')) {
                        $newPhotos[] = $p;
                        $hasBase64 = true;
                        continue;
                    }

                    // It's a file path or URL
                    $cleanPath = ltrim(preg_replace('/^https?:\/\/[^\/]+(\/storage\/.*)$/', '$1', (string) $p), '/');
                    $cleanPath = preg_replace('/^storage\//', '', $cleanPath);
                    $basename = basename($cleanPath);

                    // Find physical file on disk
                    $foundFile = null;
                    foreach ($candidateRoots as $root) {
                        $testPath = rtrim($root, '/') . '/' . $cleanPath;
                        if (@file_exists($testPath) && @is_file($testPath)) {
                            $foundFile = $testPath;
                            break;
                        }
                        // Also try direct network_photos/$folder/$basename
                        $testPath2 = rtrim($root, '/') . "/network_photos/{$cfg['folder']}/{$basename}";
                        if (@file_exists($testPath2) && @is_file($testPath2)) {
                            $foundFile = $testPath2;
                            break;
                        }
                    }

                    if ($foundFile) {
                        // File exists! Convert to Base64 if not already present
                        $dataUri = NodePhotoService::createDataUri($foundFile);
                        if ($dataUri && !$hasBase64) {
                            $newPhotos[] = $dataUri;
                            $hasBase64 = true;
                            $changed = true;
                            $totalRepaired++;
                            $this->info("[{$label} #{$item->id}] Converted physical file ({$basename}) to Base64 Data URI.");
                        }
                        $relUrl = NodePhotoService::mirrorPhysicalFile($foundFile, $cfg['folder'], $basename);
                        if ($relUrl) {
                            $newPhotos[] = $relUrl;
                        }
                    } else {
                        // File physically missing on disk!
                        if ($hasBase64) {
                            // Safe to purge dead link since Base64 exists
                            $changed = true;
                            $totalPurged++;
                            $this->warn("[{$label} #{$item->id}] Purged missing file link ({$basename}) since Data URI exists.");
                        } else {
                            $changed = true;
                            $totalPurged++;
                            $this->warn("[{$label} #{$item->id}] Removed non-existent 404 photo ({$basename}). UI will now show clean upload prompt.");
                        }
                    }
                }

                if ($changed) {
                    $item->photos = array_values(array_unique($newPhotos));
                    $item->save();
                }
            }
        }

        if (class_exists(\App\Livewire\Gis\GisMap::class)) {
            \App\Livewire\Gis\GisMap::flushCache();
        }

        $this->newLine();
        $this->info("Summary:");
        $this->table(
            ['Metric', 'Count'],
            [
                ['Total Nodes with Photos Checked', $totalChecked],
                ['Photos Converted / Repaired to Base64', $totalRepaired],
                ['Dead 404 Links Purged', $totalPurged],
            ]
        );

        $this->info("Photos repair completed successfully!");
        return 0;
    }
}
