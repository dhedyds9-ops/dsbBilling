<?php

declare(strict_types=1);

namespace App\Livewire\Support\Sla;

use App\Livewire\AdminComponent;
use App\Models\Master\Branch;
use App\Models\User;
use App\Services\Support\SlaTrackingService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class Report extends AdminComponent
{
    public string $startDate;
    public string $endDate;
    public $branchFilter = '';
    public $resellerFilter = '';
    public $priorityFilter = '';

    public function mount()
    {
        $this->startDate = now()->subDays(30)->toDateString();
        $this->endDate = now()->toDateString();
    }

    public function getBreadcrumbs(): array
    {
        return [
            ['label' => 'Dashboard', 'url' => route('dashboard')],
            ['label' => 'Support', 'url' => route('support.ticket')],
            ['label' => 'Laporan SLA Operasional', 'url' => route('support.sla.report')],
        ];
    }

    public function resetFilters(): void
    {
        $this->startDate = now()->subDays(30)->toDateString();
        $this->endDate = now()->toDateString();
        $this->branchFilter = '';
        $this->resellerFilter = '';
        $this->priorityFilter = '';
    }

    public function render(SlaTrackingService $slaService)
    {
        $user = Auth::user();

        $filters = [
            'start_date'  => $this->startDate,
            'end_date'    => $this->endDate,
            'branch_id'   => $this->branchFilter ?: null,
            'reseller_id' => $this->resellerFilter ?: null,
            'priority'    => $this->priorityFilter ?: null,
        ];

        $report = $slaService->getSlaReport($filters, $user);

        $branches = Branch::query()->orderBy('name')->get();
        $resellers = User::query()
            ->withoutGlobalScopes()
            ->whereHas('roles', fn($r) => $r->where('name', 'reseller'))
            ->orWhere('job_function', 'RESELLER')
            ->orderBy('name')
            ->get();

        return view('livewire.support.sla.report', [
            'report'    => $report,
            'branches'  => $branches,
            'resellers' => $resellers,
        ])->layout('layouts.admin', [
            'title' => 'Laporan Kinerja SLA Operasional',
            'breadcrumbs' => $this->getBreadcrumbs(),
        ]);
    }
}
