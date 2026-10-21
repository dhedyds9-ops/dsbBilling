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
        return \App\Models\Workflow\Workflow::query()
            ->when(isset($this->filters['status']) && $this->filters['status'] !== 'all', fn($q) => $q->where('status', $this->filters['status']))
            ->orderBy($this->sortField, $this->sortDirection)
            ->get();
    }

    public function getWorkflowStats(): array
    {
        $all = \App\Models\Workflow\Workflow::all();
        return [
            'total' => $all->count(),
            'active' => $all->where('status', 'active')->count(),
            'draft' => $all->where('status', 'draft')->count(),
            'total_instances' => 0,
            'total_tasks' => 0,
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
