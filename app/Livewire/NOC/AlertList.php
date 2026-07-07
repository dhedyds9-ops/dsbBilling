<?php

namespace App\Livewire\NOC;

use App\Livewire\AdminComponent;

class AlertList extends AdminComponent
{
    public array $filters = [
        'severity' => 'all',
        'status' => 'all',
        'source' => 'all',
    ];

    public string $sortField = 'created_at';
    public string $sortDirection = 'desc';
    public int $perPage = 20;

    public function mount(): void
    {
        parent::mount();
        $this->activeModule = 'noc';
        $this->activePage = 'alerts';
    }

    public function getAlerts(): \Illuminate\Support\Collection
    {
        // Placeholder - dalam implementasi nyata, fetch dari repository
        return collect([
            ['id' => 1, 'title' => 'OLT-01 High CPU', 'severity' => 'critical', 'source' => 'OLT-01', 'status' => 'active', 'acknowledged_by' => null, 'created_at' => now()->subMinutes(15)],
            ['id' => 2, 'title' => 'Network Latency Spike', 'severity' => 'warning', 'source' => 'Core-Router-01', 'status' => 'acknowledged', 'acknowledged_by' => 'John Doe', 'created_at' => now()->subHours(1)],
            ['id' => 3, 'title' => 'Bandwidth Threshold Exceeded', 'severity' => 'warning', 'source' => 'OLT-02', 'status' => 'resolved', 'acknowledged_by' => 'Jane Smith', 'created_at' => now()->subHours(3)],
            ['id' => 4, 'title' => 'Fiber Cut Detected', 'severity' => 'critical', 'source' => 'Segment-A3', 'status' => 'active', 'acknowledged_by' => null, 'created_at' => now()->subMinutes(5)],
        ]);
    }

    public function getAlertStats(): array
    {
        $alerts = $this->getAlerts();
        return [
            'total' => $alerts->count(),
            'critical' => $alerts->where('severity', 'critical')->count(),
            'warning' => $alerts->where('severity', 'warning')->count(),
            'active' => $alerts->where('status', 'active')->count(),
            'acknowledged' => $alerts->where('status', 'acknowledged')->count(),
            'resolved' => $alerts->where('status', 'resolved')->count(),
        ];
    }

    public function acknowledgeAlert(int $alertId): void
    {
        // Logic to acknowledge alert
    }

    public function resolveAlert(int $alertId): void
    {
        // Logic to resolve alert
    }

    public function getSeverityColor(string $severity): string
    {
        return match($severity) {
            'critical' => 'danger',
            'warning' => 'warning',
            'info' => 'primary',
            default => 'neutral',
        };
    }

    public function render()
    {
        return view('livewire.noc.alert-list', [
            'alerts' => $this->getAlerts(),
            'stats' => $this->getAlertStats(),
        ]);
    }
}
