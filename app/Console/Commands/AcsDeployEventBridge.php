<?php

namespace App\Console\Commands;

use App\Services\Adapters\Monitoring\GenieACSDriver;
use Illuminate\Console\Command;

class AcsDeployEventBridge extends Command
{
    protected $signature = 'acs:deploy-event-bridge {--verify-only : Only verify without deploying}';
    protected $description = 'Deploy or verify GenieACS Event Bridge provision script and preset for dsBilling Inform Webhook';

    public function handle(GenieACSDriver $driver): int
    {
        $this->info('GenieACS Event Bridge Manager (v1.2.16)');
        $this->line('Provision: dsBilling_Event_Ingestion');
        $this->line('Preset:    dsBilling_Event_Ingestion');

        if ($this->option('verify-only')) {
            $presets = $driver->listPresets();
            $provisions = $driver->listProvisions();

            $hasPreset = collect($presets)->contains(fn ($p) => ($p['_id'] ?? '') === 'dsBilling_Event_Ingestion');
            $hasProv = collect($provisions)->contains(fn ($p) => ($p['_id'] ?? '') === 'dsBilling_Event_Ingestion');

            $this->info('Verification Result:');
            $this->line('Provision deployed: ' . ($hasProv ? '<info>YES</info>' : '<comment>NO</comment>'));
            $this->line('Preset deployed:    ' . ($hasPreset ? '<info>YES</info>' : '<comment>NO</comment>'));

            return ($hasProv && $hasPreset) ? Command::SUCCESS : Command::FAILURE;
        }

        $this->info('Deploying Provision and Preset to GenieACS...');
        $result = $driver->deployEventBridge();

        if ($result['success']) {
            $this->info('Event Bridge deployed successfully to GenieACS!');
            $this->line('- Provision "dsBilling_Event_Ingestion": OK');
            $this->line('- Preset "dsBilling_Event_Ingestion":    OK');
            $this->comment('Next Step: Ensure /opt/genieacs/config/ext/dsbilling-webhook.js exists on the GenieACS server.');
            return Command::SUCCESS;
        }

        $this->error('Failed to deploy Event Bridge to GenieACS.');
        $this->line('- Provision: ' . ($result['provision_deployed'] ? 'OK' : 'FAILED'));
        $this->line('- Preset:    ' . ($result['preset_deployed'] ? 'OK' : 'FAILED'));

        return Command::FAILURE;
    }
}
