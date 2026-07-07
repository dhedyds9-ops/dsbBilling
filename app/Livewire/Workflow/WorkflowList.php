<?php

namespace App\Livewire\Workflow;

use App\Livewire\AdminComponent;

class WorkflowList extends AdminComponent
{
    public array $filters = [
        'status' => 'all',
        'module' => 'all',
    ];

    public string $sortField = 'created_at';
    public string $sortDirection = 'desc';
    public int $perPage = 15;

    public function mount(): void
    {
        parent::mount();
        $this->activeModule = 'workflow';
        $this->activePage = 'index';
    }

    public function getWorkflows(): \Illuminate\Support\Collection
    {
        // Placeholder - dalam implementasi nyata, fetch dari repository
        return collect([
            ['id' => 1, 'name' => 'Customer Installation', 'module' => 'CRM', 'status' => 'active', 'instances' => 5, 'tasks' => 12, 'created_at' => now()->subDays(10)],
            ['id' => 2, 'name' => 'Invoice Approval', 'module' => 'Billing', 'status' => 'active', 'instances' => 3, 'tasks' => 8, 'created_at' => now()->subDays(5)],
            ['id' => 3, 'name' => 'Asset Transfer', 'module' => 'Inventory', 'status' => 'draft', 'instances' => 0, 'tasks' => 0, 'created_at' => now()->subDays(2)],
            ['id' => 4, 'name' => 'Complaint Handling', 'module' => 'CRM', 'status' => 'active', 'instances' => 7, 'tasks' => 15, 'created_at' => now()->subDays(7)],
        ]);
    }

    public function getWorkflowStats(): array
    {
        $workflows = $this->getWorkflows();
        return [
            'total' => $workflows->count(),
            'active' => $workflows->where('status', 'active')->count(),
            'draft' => $workflows->where('status', 'draft')->count(),
            'total_instances' => $workflows->sum('instances'),
            'total_tasks' => $workflows->sum('tasks'),
        ];
    }

    public function startWorkflow(int $workflowId): void
    {
        // Logic to start workflow
    }

    public function render()
    {
        return view('livewire.workflow.workflow-list', [
            'workflows' => $this->getWorkflows(),
            'stats' => $this->getWorkflowStats(),
        ]);
    }
}
