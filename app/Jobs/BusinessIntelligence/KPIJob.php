<?php

namespace App\Jobs\BusinessIntelligence;

use App\Services\BusinessIntelligence\KPIService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Src\Domain\BusinessIntelligence\Enums\KPIType;

class KPIJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public int $backoff = 30;
    public int $timeout = 120;

    public function __construct(
        public readonly ?string $kpiId = null,
        public readonly ?string $module = null,
        public readonly ?string $category = null
    ) {}

    public function handle(KPIService $kpiService): void
    {
        Log::info("KPIJob: Processing KPIs", [
            'kpi_id' => $this->kpiId,
            'module' => $this->module,
            'category' => $this->category
        ]);

        try {
            if ($this->kpiId) {
                // Calculate specific KPI
                $this->calculateSpecificKPI($kpiService);
            } elseif ($this->module) {
                // Calculate all KPIs for module
                $this->calculateModuleKPIs($kpiService);
            } elseif ($this->category) {
                // Calculate KPIs by category
                $this->calculateCategoryKPIs($kpiService);
            } else {
                // Calculate all active KPIs
                $this->calculateAllKPIs($kpiService);
            }

            Log::info("KPIJob: KPI calculation completed");

        } catch (\Exception $e) {
            Log::error("KPIJob: Failed to calculate KPIs", [
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    private function calculateSpecificKPI(KPIService $kpiService): void
    {
        $kpiUuid = \Src\Domain\SharedKernel\ValueObjects\Uuid::fromString($this->kpiId);

        // Get KPI value from data source
        $value = $this->fetchKPIValue($kpiId);

        if ($value !== null) {
            $kpiService->recordKPIValue($kpiUuid, $value);
        }
    }

    private function calculateModuleKPIs(KPIService $kpiService): void
    {
        $kpis = $kpiService->getKPIsByModule($this->module);

        foreach ($kpis as $kpi) {
            $value = $this->fetchKPIValue($kpi->id->toString());
            if ($value !== null) {
                $kpiService->recordKPIValue($kpi->id, $value);
            }
        }
    }

    private function calculateCategoryKPIs(KPIService $kpiService): void
    {
        $kpis = $kpiService->getKPIsByCategory($this->category);

        foreach ($kpis as $kpi) {
            $value = $this->fetchKPIValue($kpi->id->toString());
            if ($value !== null) {
                $kpiService->recordKPIValue($kpi->id, $value);
            }
        }
    }

    private function calculateAllKPIs(KPIService $kpiService): void
    {
        $modules = ['CRM', 'Finance', 'Billing', 'NOC', 'Inventory', 'Network', 'Service'];

        foreach ($modules as $module) {
            $kpis = $kpiService->getKPIsByModule($module);
            foreach ($kpis as $kpi) {
                $value = $this->fetchKPIValue($kpi->id->toString());
                if ($value !== null) {
                    $kpiService->recordKPIValue($kpi->id, $value);
                }
            }
        }
    }

    private function fetchKPIValue(string $kpiId): ?float
    {
        // Placeholder - in real implementation would query actual data
        // based on KPI type
        return match($kpiId) {
            default => rand(1000, 100000) / 100,
        };
    }

    public function failed(\Throwable $exception): void
    {
        Log::error("KPIJob: KPI calculation failed permanently", [
            'error' => $exception->getMessage()
        ]);
    }
}
