<?php

namespace App\Console\Commands\ISP;

use App\Models\ISP\Router;
use Illuminate\Console\Command;
use Symfony\Component\Process\Process;

class InstallRadiusCommand extends Command
{
    protected $signature = 'radius:install {--secret= : Custom shared secret for RADIUS clients}';
    protected $description = 'Instal dan konfigurasi FreeRADIUS (REST API & MikroTik) secara otomatis';

    public function handle(): int
    {
        $this->info("=== dsBilling: FreeRADIUS Auto Installer ===");

        $scriptPath = base_path('scripts/setup-freeradius.sh');
        if (!file_exists($scriptPath)) {
            $this->error("Skrip instalasi tidak ditemukan: {$scriptPath}");
            return 1;
        }

        @chmod($scriptPath, 0755);

        $process = new Process(['bash', $scriptPath]);
        $process->setTimeout(600);
        $process->run(function ($type, $buffer) {
            $this->output->write($buffer);
        });

        return $process->isSuccessful() ? 0 : 1;
    }
}
