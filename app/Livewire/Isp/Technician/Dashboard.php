<?php

namespace App\Livewire\Isp\Technician;

use Livewire\Component;
use Livewire\Attributes\Layout;
use App\Models\CRM\Installation;
use App\Models\Provisioning\ProvisionPipeline;
use App\Models\Support\Ticket;
use Illuminate\Support\Facades\Auth;

#[Layout('layouts.technician-app')]
class Dashboard extends Component
{
    public array $breadcrumbs = [];

    private function isAdminOversight(): bool
    {
        $user = Auth::user();
        if (!$user) {
            return false;
        }

        return $user->hasRole(['administrator', 'superadmin'])
            && strtoupper((string) $user->job_function) !== 'TECHNICIAN';
    }

    public function mount()
    {
        $this->breadcrumbs = [
            ['label' => 'Technician Dashboard', 'url' => '#'],
        ];
    }

    public function render()
    {
        $userId = Auth::id();
        $isAdmin = $this->isAdminOversight();

        $scopePipeline = function ($query) use ($userId, $isAdmin) {
            if ($isAdmin) {
                return;
            }

            $query->where(function ($sub) use ($userId) {
                $sub->where('created_by', $userId)
                    ->orWhereHas('serviceInstance', function ($siQ) use ($userId) {
                        $siQ->withTrashed()->where(function ($siSub) use ($userId) {
                            $siSub->where('created_by', $userId)
                                ->orWhereHas('customerService', function ($csQ) use ($userId) {
                                    $csQ->withoutGlobalScopes()->withTrashed()->where('created_by', $userId);
                                });
                        });
                    });
            });
        };

        $pipelineProses = ProvisionPipeline::where($scopePipeline)
            ->whereIn('status', ['pending', 'running', 'in_progress', 'provisioning'])
            ->count();

        $hasInstTechIds = \Illuminate\Support\Facades\Schema::hasColumn('installations', 'technician_ids');
        $hasTicketTechIds = \Illuminate\Support\Facades\Schema::hasColumn('tickets', 'technician_ids');

        // 1. SPK Instalasi (CRM) Aktif yang Ditugaskan ke Teknisi
        $activeInstallations = Installation::withoutGlobalScopes()
            ->with([
                'customer' => fn ($q) => $q->withoutGlobalScopes()->with('reseller'),
                'survey.prospect',
            ])
            ->when(!$isAdmin, function ($q) use ($userId, $hasInstTechIds) {
                $q->where(function ($sub) use ($userId, $hasInstTechIds) {
                    $sub->where('technician_id', $userId)
                        ->orWhere('assigned_to', $userId);
                    if ($hasInstTechIds) {
                        $sub->orWhereJsonContains('technician_ids', $userId);
                    }
                });
            })
            ->whereIn('status', ['pending', 'scheduled', 'in_progress', 'assigned'])
            ->orderBy('scheduled_date', 'asc')
            ->orderBy('scheduled_time', 'asc')
            ->limit(10)
            ->get();

        $assignedInstallationsCount = $activeInstallations->count();

        // 2. Tiket Aktif (Support & Gangguan) yang Ditugaskan ke Teknisi
        $activeTicketsCount = Ticket::withoutGlobalScopes()
            ->when(!$isAdmin, function ($q) use ($userId, $hasTicketTechIds) {
                $q->where(function ($sub) use ($userId, $hasTicketTechIds) {
                    $sub->where('assigned_to', $userId);
                    if ($hasTicketTechIds) {
                        $sub->orWhereJsonContains('technician_ids', $userId);
                    }
                });
            })
            ->whereIn('status', ['open', 'in_progress', 'pending_customer', 'assigned'])
            ->count();

        // Total Tugas Aktif dalam Proses
        $countProses = $assignedInstallationsCount + $activeTicketsCount + $pipelineProses;
        $totalActiveTasks = $activeTicketsCount + $assignedInstallationsCount;

        // 3. Tugas Selesai (Instalasi CRM Selesai + Tiket Resolved + Pipeline Selesai)
        $completedInstCount = Installation::withoutGlobalScopes()
            ->when(!$isAdmin, function ($q) use ($userId, $hasInstTechIds) {
                $q->where(function ($sub) use ($userId, $hasInstTechIds) {
                    $sub->where('technician_id', $userId)
                        ->orWhere('assigned_to', $userId);
                    if ($hasInstTechIds) {
                        $sub->orWhereJsonContains('technician_ids', $userId);
                    }
                });
            })
            ->whereIn('status', ['completed', 'verified', 'active'])
            ->count();

        $completedTicketsCount = Ticket::withoutGlobalScopes()
            ->when(!$isAdmin, function ($q) use ($userId, $hasTicketTechIds) {
                $q->where(function ($sub) use ($userId, $hasTicketTechIds) {
                    $sub->where('assigned_to', $userId);
                    if ($hasTicketTechIds) {
                        $sub->orWhereJsonContains('technician_ids', $userId);
                    }
                });
            })
            ->whereIn('status', ['resolved', 'closed'])
            ->count();

        $countSelesai = $completedInstCount + $completedTicketsCount + ProvisionPipeline::where($scopePipeline)
            ->whereIn('status', ['completed', 'provisioned', 'active'])
            ->count();

        // 4. Tugas Gagal / Batal
        $failedInstCount = Installation::withoutGlobalScopes()
            ->when(!$isAdmin, function ($q) use ($userId, $hasInstTechIds) {
                $q->where(function ($sub) use ($userId, $hasInstTechIds) {
                    $sub->where('technician_id', $userId)
                        ->orWhere('assigned_to', $userId);
                    if ($hasInstTechIds) {
                        $sub->orWhereJsonContains('technician_ids', $userId);
                    }
                });
            })
            ->whereIn('status', ['cancelled', 'failed'])
            ->count();

        $countGagal = $failedInstCount + ProvisionPipeline::where($scopePipeline)
            ->whereIn('status', ['failed', 'cancelled', 'rolled_back'])
            ->count();

        // 5. Riwayat Pemasangan Lapangan (Instalasi CRM Nyata)
        $crmHistory = Installation::withoutGlobalScopes()
            ->with([
                'customer' => fn ($q) => $q->withoutGlobalScopes(),
                'survey.prospect',
            ])
            ->when(!$isAdmin, function ($q) use ($userId, $hasInstTechIds) {
                $q->where(function ($sub) use ($userId, $hasInstTechIds) {
                    $sub->where('technician_id', $userId)
                        ->orWhere('assigned_to', $userId);
                    if ($hasInstTechIds) {
                        $sub->orWhereJsonContains('technician_ids', $userId);
                    }
                });
            })
            ->latest('completed_at')
            ->latest('updated_at')
            ->limit(10)
            ->get();

        $recentInstallations = $crmHistory->map(function ($inst) {
            $isDone = in_array($inst->status, ['completed', 'verified', 'active']);
            $isFail = in_array($inst->status, ['cancelled', 'failed']);
            $custName = $inst->customer?->name ?? ($inst->survey?->prospect?->name ?? ($inst->customer_name ?: 'Pelanggan Baru'));
            $date = $inst->completed_at ?? ($inst->scheduled_date ?? $inst->created_at);

            return (object) [
                'id' => $inst->id,
                'customer_name' => $custName,
                'status' => $isDone ? 'completed' : ($isFail ? 'failed' : 'in_progress'),
                'raw_status' => $inst->status,
                'created_at' => $date ? \Carbon\Carbon::parse($date) : now(),
                'type_label' => 'Pasang Baru (PSB)',
                'monitor_url' => route('technician.my-jobs.index'),
            ];
        });

        // Jika riwayat CRM kosong, fallback ke riwayat pipeline
        if ($recentInstallations->isEmpty()) {
            $pipelines = ProvisionPipeline::where($scopePipeline)
                ->with([
                    'serviceInstance' => function ($query) {
                        $query->withTrashed()->with([
                            'customerService' => function ($q) {
                                $q->withoutGlobalScopes()->withTrashed()->with([
                                    'customer' => fn ($cq) => $cq->withoutGlobalScopes(),
                                ]);
                            },
                        ]);
                    },
                ])
                ->orderBy('created_at', 'desc')
                ->limit(10)
                ->get();

            $recentInstallations = $pipelines->map(function ($p) {
                return (object) [
                    'id' => $p->id,
                    'customer_name' => $p->serviceInstance?->customerService?->customer?->name ?? 'Pelanggan',
                    'status' => $p->status,
                    'raw_status' => $p->status,
                    'created_at' => $p->created_at,
                    'type_label' => 'Provisioning',
                    'monitor_url' => route('technician.provisioning.show', $p->id),
                ];
            });
        }

        return view('livewire.isp.technician.dashboard', compact(
            'recentInstallations',
            'activeInstallations',
            'assignedInstallationsCount',
            'totalActiveTasks',
            'countProses',
            'countSelesai',
            'countGagal',
            'activeTicketsCount'
        ));
    }
}
