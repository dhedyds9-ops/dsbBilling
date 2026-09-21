<?php

namespace App\Console\Commands;

use App\Services\Adapters\Monitoring\GenieACSDriver;
use Illuminate\Console\Command;

class SyncGenieAcsVirtualParameters extends Command
{
    protected $signature = 'genieacs:sync-vp {file? : Path to the CSV file containing Virtual Parameters}';
    protected $description = 'Sync Virtual Parameters from a CSV file to GenieACS';

    public function handle(GenieACSDriver $driver)
    {
        $file = $this->argument('file');
        if (!$file) {
            $file = database_path('genieacs/virtual_parameters.csv');
        }

        if (!file_exists($file)) {
            $this->error("File not found: {$file}");
            $this->info("Please place your exported Virtual Parameters CSV file at database/genieacs/virtual_parameters.csv");
            return 1;
        }

        $this->info("Reading Virtual Parameters from {$file}...");
        
        // Read CSV
        $handle = fopen($file, "r");
        if ($handle !== FALSE) {
            $header = fgetcsv($handle, 100000, ",");
            
            $successCount = 0;
            $failCount = 0;

            while (($data = fgetcsv($handle, 100000, ",")) !== FALSE) {
                if (count($data) < 2) continue;
                
                $name = $data[0];
                $script = $data[1];
                
                $this->info("Pushing VP: {$name}...");
                if ($driver->upsertVirtualParameter($name, $script)) {
                    $successCount++;
                } else {
                    $this->error("Failed to push VP: {$name}");
                    $failCount++;
                }
            }
            fclose($handle);
            
            $this->info("Done! Successfully pushed {$successCount} parameters. Failed: {$failCount}.");
        } else {
            $this->error("Failed to open file.");
            return 1;
        }

        return 0;
    }
}
