<?php

namespace Src\Domain\Tenant\ValueObjects;

class TenantLimits
{
    public function __construct(
        public readonly int $maxUsers = 10,
        public readonly int $maxCustomers = 1000,
        public readonly int $maxDevices = 500,
        public readonly int $maxOltPorts = 100,
        public readonly int $maxOnuUnits = 5000,
        public readonly int $maxApiCallsPerMonth = 10000,
        public readonly int $maxStorageGb = 100,
        public readonly int $maxBandwidthGbps = 10,
        public readonly bool $include GisModule = false,
        public readonly bool $includeBIModule = false,
        public readonly bool $include WorkflowModule = false,
        public readonly bool $include AIModule = false,
        public readonly bool $allowCustomBranding = false,
        public readonly bool $allowPluginInstallation = false
    ) {}

    public function toArray(): array
    {
        return [
            'max_users' => $this->maxUsers,
            'max_customers' => $this->maxCustomers,
            'max_devices' => $this->maxDevices,
            'max_olt_ports' => $this->maxOltPorts,
            'max_onu_units' => $this->maxOnuUnits,
            'max_api_calls_per_month' => $this->maxApiCallsPerMonth,
            'max_storage_gb' => $this->maxStorageGb,
            'max_bandwidth_gbps' => $this->maxBandwidthGbps,
            'include_gis_module' => $this->include GisModule,
            'include_bi_module' => $this->include BI module,
            'include_workflow_module' => $this->include WorkflowModule,
            'include_ai_module' => $this->include AIModule,
            'allow_custom_branding' => $this->allowCustomBranding,
            'allow_plugin_installation' => $this->allowPluginInstallation,
        ];
    }

    public static function fromArray(array $data): self
    {
        return new self(
            maxUsers: $data['max_users'] ?? 10,
            maxCustomers: $data['max_customers'] ?? 1000,
            maxDevices: $data['max_devices'] ?? 500,
            maxOltPorts: $data['max_olt_ports'] ?? 100,
            maxOnuUnits: $data['max_onu_units'] ?? 5000,
            maxApiCallsPerMonth: $data['max_api_calls_per_month'] ?? 10000,
            maxStorageGb: $data['max_storage_gb'] ?? 100,
            maxBandwidthGbps: $data['max_bandwidth_gbps'] ?? 10,
            includeGisModule: $data['include_gis_module'] ?? false,
            includeBIModule: $data['include_bi_module'] ?? false,
            includeWorkflowModule: $data['include_workflow_module'] ?? false,
            includeAIModule: $data['include_ai_module'] ?? false,
            allowCustomBranding: $data['allow_custom_branding'] ?? false,
            allowPluginInstallation: $data['allow_plugin_installation'] ?? false
        );
    }
}
