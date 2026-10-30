<?php

namespace App\Livewire\Isp\Technician\MyJobs;

use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use App\Models\CRM\Survey;
use App\Models\CRM\Installation;
use App\Models\Customer\CustomerService;
use App\Models\Support\Ticket;
use App\Models\ISP\Odp;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Event;
use Illuminate\Pagination\LengthAwarePaginator;
use Src\Domain\Support\Events\TicketResolvedEvent;

#[Layout('layouts.technician-app')]
class Index extends Component
{
    use WithPagination;

    #[Url]
    public string $activeTab = 'active'; // active, history

    #[Url]
    public string $jobType = 'all'; // all, installation, ticket, survey

    #[Url]
    public string $search = '';

    // Modal Input Hasil Survey
    public $showInputResultModal = false;
    public $selectedSurveyId = null;

    public $input_odp_id = '';
    public $input_port = '';
    public $input_distance = '';
    public $input_cable_estimation = '';
    public $input_latitude = '';
    public $input_longitude = '';
    public $input_recommendation = 'feasible';
    public $input_notes = '';

    // Modal Selesaikan Tiket
    public bool $showResolveTicketModal = false;
    public ?int $selectedTicketId = null;
    public ?string $selectedTicketCode = null;
    public ?string $selectedTicketTitle = null;
    public ?string $selectedTicketCustomer = null;
    public string $ticket_resolution_notes = '';

    /**
     * Hanya Super Admin murni (bukan akun Teknisi ber-role Manager) yang memiliki mode pantau semua tugas.
     */
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
        if (!in_array($this->activeTab, ['active', 'history'], true)) {
            $this->activeTab = 'active';
        }
        if (!in_array($this->jobType, ['all', 'installation', 'ticket', 'survey'], true)) {
            $this->jobType = 'all';
        }
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function switchTab($tab)
    {
        $this->activeTab = in_array($tab, ['active', 'history'], true) ? $tab : 'active';
        $this->resetPage();
    }

    public function filterJobType($type)
    {
        $this->jobType = in_array($type, ['all', 'installation', 'ticket', 'survey'], true) ? $type : 'all';
        $this->resetPage();
    }

    public function openResolveTicketModal($ticketId)
    {
        $ticket = Ticket::withoutGlobalScopes()
            ->with(['customer' => fn ($q) => $q->withoutGlobalScopes()])
            ->findOrFail($ticketId);

        if (!$this->isAdminOversight() && (int) $ticket->assigned_to !== (int) Auth::id()) {
            abort(403, 'Tiket ini tidak ditugaskan kepada Anda.');
        }

        $this->selectedTicketId = $ticket->id;
        $this->selectedTicketCode = !empty($ticket->uuid) ? ('TKT-' . strtoupper(substr(str_replace('-', '', $ticket->uuid), 0, 6))) : ('#' . $ticket->id);
        $this->selectedTicketTitle = $ticket->title;
        $this->selectedTicketCustomer = $ticket->customer?->name ?? 'Pelanggan #' . $ticket->customer_id;
        $this->ticket_resolution_notes = '';
        $this->showResolveTicketModal = true;
    }

    public function confirmResolveTicket()
    {
        if (!$this->selectedTicketId) {
            return;
        }

        $ticket = Ticket::withoutGlobalScopes()->findOrFail($this->selectedTicketId);

        if (!$this->isAdminOversight() && (int) $ticket->assigned_to !== (int) Auth::id()) {
            abort(403, 'Tiket ini tidak ditugaskan kepada Anda.');
        }

        $ticket->update([
            'status' => 'resolved',
            'resolved_at' => now(),
        ]);

        $note = trim($this->ticket_resolution_notes) !== ''
            ? trim($this->ticket_resolution_notes)
            : 'Diselesaikan oleh teknisi melalui portal tugas';

        Event::dispatch(
            TicketResolvedEvent::create(
                (string) $ticket->id,
                (string) Auth::id(),
                $note
            )
        );

        $code = $this->selectedTicketCode ?? ('#' . $ticket->id);
        $this->showResolveTicketModal = false;
        $this->selectedTicketId = null;
        $this->ticket_resolution_notes = '';

        session()->flash('success', "Tiket {$code} berhasil diselesaikan.");
    }

    public function markTicketResolved($id)
    {
        $this->openResolveTicketModal($id);
    }

    public function openInputResultModal($surveyId)
    {
        $survey = Survey::withoutGlobalScopes()->findOrFail($surveyId);

        if (!$this->isAdminOversight() && (int) $survey->assigned_to !== (int) Auth::id()) {
            abort(403, 'Tugas survey ini tidak ditugaskan kepada Anda.');
        }

        $this->selectedSurveyId = $survey->id;
        $this->input_odp_id = $survey->odp_id;
        $this->input_port = $survey->port;
        $this->input_distance = $survey->distance;
        $this->input_cable_estimation = $survey->cable_estimation;
        $this->input_latitude = $survey->latitude;
        $this->input_longitude = $survey->longitude;
        $this->input_recommendation = $survey->recommendation ?? 'feasible';
        $this->input_notes = $survey->notes;

        $this->showInputResultModal = true;
    }

    public function saveSurveyResult()
    {
        $this->validate([
            'input_recommendation' => 'required|in:feasible,not_feasible',
        ]);

        $survey = Survey::withoutGlobalScopes()->findOrFail($this->selectedSurveyId);

        if (!$this->isAdminOversight() && (int) $survey->assigned_to !== (int) Auth::id()) {
            abort(403, 'Tugas survey ini tidak ditugaskan kepada Anda.');
        }

        $survey->update([
            'odp_id' => $this->input_odp_id ?: null,
            'port' => $this->input_port,
            'distance' => $this->input_distance !== '' ? (float) $this->input_distance : null,
            'cable_estimation' => $this->input_cable_estimation !== '' ? (float) $this->input_cable_estimation : null,
            'latitude' => $this->input_latitude !== '' ? (float) $this->input_latitude : null,
            'longitude' => $this->input_longitude !== '' ? (float) $this->input_longitude : null,
            'recommendation' => $this->input_recommendation,
            'notes' => $this->input_notes,
            'status' => 'completed',
            'completed_at' => now(),
        ]);

        $this->showInputResultModal = false;
        $this->activeTab = 'history';
        session()->flash('success', 'Hasil survey berhasil disimpan dan ditandai selesai.');
    }

    public function getJobsProperty()
    {
        $userId = Auth::id();
        $isAdmin = $this->isAdminOversight();
        $search = trim($this->search);

        $items = collect();

        // 1. Fetch Installations from CustomerService (created/handled by this technician)
        if ($this->jobType === 'all' || $this->jobType === 'installation') {
            $csQuery = CustomerService::withoutGlobalScopes()
                ->with([
                    'customer' => fn ($q) => $q->withoutGlobalScopes(),
                    'onu' => fn ($q) => $q->withoutGlobalScopes()->with('olt'),
                    'serviceProfile' => fn ($q) => $q->withoutGlobalScopes(),
                    'service',
                ]);

            if (!$isAdmin) {
                $csQuery->where('created_by', $userId);
            }

            if ($this->activeTab === 'active') {
                $csQuery->whereIn('status', ['pending', 'provisioning', 'in_progress']);
            } else {
                $csQuery->whereIn('status', ['active', 'completed', 'suspended', 'approved']);
            }

            if ($search) {
                $csQuery->where(function ($q) use ($search) {
                    $q->where('username', 'like', "%{$search}%")
                      ->orWhereHas('customer', function ($cq) use ($search) {
                          $cq->withoutGlobalScopes()->where(function ($sub) use ($search) {
                              $sub->where('name', 'like', "%{$search}%")
                                  ->orWhere('phone', 'like', "%{$search}%")
                                  ->orWhere('address', 'like', "%{$search}%")
                                  ->orWhere('code', 'like', "%{$search}%");
                          });
                      })
                      ->orWhereHas('onu', function ($oq) use ($search) {
                          $oq->withoutGlobalScopes()->where(function ($sub) use ($search) {
                              $sub->where('serial_number', 'like', "%{$search}%")
                                  ->orWhere('name', 'like', "%{$search}%");
                          });
                      });
                });
            }

            $customerServices = $csQuery->latest('activated_at')->latest('created_at')->get();

            foreach ($customerServices as $cs) {
                $serviceName = $cs->serviceProfile?->name ?? ($cs->service?->name ?? 'Layanan Internet');
                $serviceType = strtoupper($cs->serviceProfile?->service_type ?? ($cs->service?->type ?? 'PPPOE'));
                $isActive = in_array($cs->status, ['active', 'completed']);

                $items->push((object) [
                    'id' => 'cs_' . $cs->id,
                    'raw_id' => $cs->id,
                    'source' => 'customer_service',
                    'job_type' => 'installation',
                    'type_badge' => 'Instalasi ' . $serviceType,
                    'service_type' => $serviceType,
                    'title' => $cs->customer?->name ?? 'Pelanggan #' . $cs->customer_id,
                    'subject' => $serviceName,
                    'description' => null,
                    'priority' => 'normal',
                    'category' => 'installation',
                    'code' => $cs->customer?->code ?? '-',
                    'phone' => $cs->customer?->phone ?? '-',
                    'address' => $cs->customer?->address ?? 'Alamat tidak tersedia',
                    'username' => $cs->username,
                    'package_name' => $serviceName,
                    'onu_sn' => $cs->onu?->serial_number ?? null,
                    'onu_name' => $cs->onu?->name ?? null,
                    'date' => $cs->activated_at ?? $cs->created_at,
                    'status' => $cs->status,
                    'status_label' => $isActive ? 'Selesai / Aktif' : 'Menunggu / Proses',
                    'is_completed' => $isActive,
                    'latitude' => $cs->customer?->latitude,
                    'longitude' => $cs->customer?->longitude,
                    'survey_id' => null,
                    'ticket_id' => null,
                ]);
            }

            // 1b. Fetch CRM Work Order Installations (strictly assigned_to or technician_id)
            $instQuery = Installation::withoutGlobalScopes()
                ->with([
                    'customer' => fn ($q) => $q->withoutGlobalScopes(),
                    'survey.prospect',
                    'onu' => fn ($q) => $q->withoutGlobalScopes(),
                ]);

            if (!$isAdmin) {
                $instQuery->where(function ($q) use ($userId) {
                    $q->where('technician_id', $userId)
                      ->orWhere('assigned_to', $userId);
                });
            }

            if ($this->activeTab === 'active') {
                $instQuery->whereIn('status', ['pending', 'scheduled', 'in_progress']);
            } else {
                $instQuery->whereIn('status', ['completed', 'cancelled']);
            }

            if ($search) {
                $instQuery->where(function ($q) use ($search) {
                    $q->where('customer_name', 'like', "%{$search}%")
                      ->orWhere('onu_serial_number', 'like', "%{$search}%")
                      ->orWhereHas('customer', function ($cq) use ($search) {
                          $cq->withoutGlobalScopes()->where(function ($sub) use ($search) {
                              $sub->where('name', 'like', "%{$search}%")
                                  ->orWhere('phone', 'like', "%{$search}%")
                                  ->orWhere('address', 'like', "%{$search}%");
                          });
                      });
                });
            }

            $installations = $instQuery->latest('scheduled_at')->latest('created_at')->get();

            foreach ($installations as $inst) {
                $isCompleted = in_array($inst->status, ['completed']);
                $custName = !empty($inst->customer_name) ? $inst->customer_name : ($inst->customer?->name ?? ($inst->survey?->prospect?->name ?? 'Pelanggan #' . $inst->customer_id));
                $custPhone = $inst->customer?->phone ?? ($inst->survey?->prospect?->phone ?? '-');
                $custAddr = $inst->customer?->address ?? ($inst->survey?->prospect?->address ?? 'Alamat tidak tersedia');

                $items->push((object) [
                    'id' => 'inst_' . $inst->id,
                    'raw_id' => $inst->id,
                    'source' => 'installation',
                    'job_type' => 'installation',
                    'type_badge' => 'Instalasi WO',
                    'service_type' => 'INSTALASI',
                    'title' => $custName,
                    'subject' => 'Instalasi Pasang Baru',
                    'description' => $inst->notes,
                    'priority' => $inst->priority ?? 'normal',
                    'category' => 'installation',
                    'code' => $inst->customer?->code ?? ('WO-' . $inst->id),
                    'phone' => $custPhone,
                    'address' => $custAddr,
                    'username' => null,
                    'package_name' => 'Instalasi Pasang Baru',
                    'onu_sn' => $inst->onu_serial_number ?? ($inst->onu?->serial_number ?? null),
                    'onu_name' => $inst->onu?->name ?? null,
                    'date' => $inst->scheduled_at ?? $inst->created_at,
                    'status' => $inst->status,
                    'status_label' => $isCompleted ? 'Selesai' : (in_array($inst->status, ['in_progress']) ? 'Dalam Pengerjaan' : 'Dijadwalkan'),
                    'is_completed' => $isCompleted,
                    'latitude' => $inst->customer?->latitude ?? $inst->survey?->latitude,
                    'longitude' => $inst->customer?->longitude ?? $inst->survey?->longitude,
                    'survey_id' => null,
                    'ticket_id' => null,
                ]);
            }
        }

        // 2. Fetch Support Tickets (strictly assigned to this technician)
        if ($this->jobType === 'all' || $this->jobType === 'ticket') {
            $ticketQuery = Ticket::withoutGlobalScopes()
                ->with([
                    'customer' => fn ($q) => $q->withoutGlobalScopes(),
                    'assignedTo',
                ]);

            if (!$isAdmin) {
                $ticketQuery->where('assigned_to', $userId);
            }

            if ($this->activeTab === 'active') {
                $ticketQuery->whereIn('status', ['open', 'in_progress', 'pending_customer']);
            } else {
                $ticketQuery->whereIn('status', ['resolved', 'closed']);
            }

            if ($search) {
                $ticketQuery->where(function ($q) use ($search) {
                    $q->where('title', 'like', "%{$search}%")
                      ->orWhere('description', 'like', "%{$search}%")
                      ->orWhere('category', 'like', "%{$search}%")
                      ->orWhere('id', $search)
                      ->orWhereHas('customer', function ($cq) use ($search) {
                          $cq->withoutGlobalScopes()->where(function ($sub) use ($search) {
                              $sub->where('name', 'like', "%{$search}%")
                                  ->orWhere('phone', 'like', "%{$search}%")
                                  ->orWhere('address', 'like', "%{$search}%")
                                  ->orWhere('code', 'like', "%{$search}%");
                          });
                      });
                });
            }

            $tickets = $ticketQuery->latest('created_at')->get();

            foreach ($tickets as $ticket) {
                $isResolved = in_array($ticket->status, ['resolved', 'closed']);
                $code = !empty($ticket->uuid) ? ('TKT-' . strtoupper(substr(str_replace('-', '', $ticket->uuid), 0, 6))) : ('TKT-' . $ticket->id);

                $items->push((object) [
                    'id' => 'ticket_' . $ticket->id,
                    'raw_id' => $ticket->id,
                    'source' => 'ticket',
                    'job_type' => 'ticket',
                    'type_badge' => 'Tiket ' . strtoupper($ticket->category ?? 'Gangguan'),
                    'service_type' => 'TIKET',
                    'title' => $ticket->customer?->name ?? 'Pelanggan #' . ($ticket->customer_id ?? '-'),
                    'subject' => $ticket->title,
                    'description' => $ticket->description,
                    'priority' => $ticket->priority ?? 'medium',
                    'category' => $ticket->category ?? 'gangguan',
                    'code' => $code,
                    'phone' => $ticket->customer?->phone ?? '-',
                    'address' => $ticket->customer?->address ?? 'Alamat tidak tersedia',
                    'username' => null,
                    'package_name' => $ticket->title,
                    'onu_sn' => null,
                    'onu_name' => null,
                    'date' => $ticket->created_at,
                    'status' => $ticket->status,
                    'status_label' => $isResolved ? 'Selesai' : (in_array($ticket->status, ['in_progress']) ? 'Dalam Pengerjaan' : 'Tiket Aktif'),
                    'is_completed' => $isResolved,
                    'latitude' => $ticket->customer?->latitude,
                    'longitude' => $ticket->customer?->longitude,
                    'survey_id' => null,
                    'ticket_id' => $ticket->id,
                ]);
            }
        }

        // 3. Fetch Surveys (strictly assigned to this technician)
        if ($this->jobType === 'all' || $this->jobType === 'survey') {
            $surveyQuery = Survey::withoutGlobalScopes()
                ->with(['prospect', 'odp']);

            if (!$isAdmin) {
                $surveyQuery->where('assigned_to', $userId);
            }

            if ($this->activeTab === 'active') {
                $surveyQuery->whereIn('status', ['scheduled', 'in_progress', 'pending']);
            } else {
                $surveyQuery->whereIn('status', ['completed', 'activated']);
            }

            if ($search) {
                $surveyQuery->whereHas('prospect', function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('phone', 'like', "%{$search}%")
                      ->orWhere('address', 'like', "%{$search}%");
                });
            }

            $surveys = $surveyQuery->latest('scheduled_at')->latest('created_at')->get();

            foreach ($surveys as $survey) {
                $isCompleted = in_array($survey->status, ['completed', 'activated']);

                $items->push((object) [
                    'id' => 'survey_' . $survey->id,
                    'raw_id' => $survey->id,
                    'source' => 'survey',
                    'job_type' => 'survey',
                    'type_badge' => 'Survey Lapangan',
                    'service_type' => 'SURVEY',
                    'title' => $survey->prospect?->name ?? 'Calon Pelanggan',
                    'subject' => 'Survey Jalur & ODP',
                    'description' => $survey->notes,
                    'priority' => 'normal',
                    'category' => 'survey',
                    'code' => $survey->odp ? 'ODP: ' . $survey->odp->name : '-',
                    'phone' => $survey->prospect?->phone ?? '-',
                    'address' => $survey->prospect?->address ?? 'Alamat tidak tersedia',
                    'username' => null,
                    'package_name' => $survey->odp ? 'ODP: ' . $survey->odp->name : 'Survey Jalur',
                    'onu_sn' => null,
                    'onu_name' => null,
                    'date' => $survey->scheduled_at ?? $survey->created_at,
                    'status' => $survey->status,
                    'status_label' => $isCompleted ? 'Survey Selesai' : 'Dijadwalkan',
                    'is_completed' => $isCompleted,
                    'latitude' => $survey->prospect?->latitude ?? $survey->latitude,
                    'longitude' => $survey->prospect?->longitude ?? $survey->longitude,
                    'survey_id' => $survey->id,
                    'ticket_id' => null,
                ]);
            }
        }

        return $items->sortByDesc(fn ($item) => $item->date ? \Carbon\Carbon::parse($item->date)->timestamp : 0);
    }

    public function getStatsProperty()
    {
        $userId = Auth::id();
        $isAdmin = $this->isAdminOversight();

        // Active & History CS
        $activeCs = CustomerService::withoutGlobalScopes()
            ->when(!$isAdmin, fn ($q) => $q->where('created_by', $userId))
            ->whereIn('status', ['pending', 'provisioning', 'in_progress'])
            ->count();

        $historyCs = CustomerService::withoutGlobalScopes()
            ->when(!$isAdmin, fn ($q) => $q->where('created_by', $userId))
            ->whereIn('status', ['active', 'completed', 'suspended', 'approved'])
            ->count();

        // Active & History Installations (CRM)
        $activeInst = Installation::withoutGlobalScopes()
            ->when(!$isAdmin, function ($q) use ($userId) {
                $q->where(fn ($sub) => $sub->where('technician_id', $userId)->orWhere('assigned_to', $userId));
            })
            ->whereIn('status', ['pending', 'scheduled', 'in_progress'])
            ->count();

        $historyInst = Installation::withoutGlobalScopes()
            ->when(!$isAdmin, function ($q) use ($userId) {
                $q->where(fn ($sub) => $sub->where('technician_id', $userId)->orWhere('assigned_to', $userId));
            })
            ->whereIn('status', ['completed', 'cancelled'])
            ->count();

        // Active & History Surveys
        $activeSurveys = Survey::withoutGlobalScopes()
            ->when(!$isAdmin, fn ($q) => $q->where('assigned_to', $userId))
            ->whereIn('status', ['scheduled', 'in_progress', 'pending'])
            ->count();

        $historySurveys = Survey::withoutGlobalScopes()
            ->when(!$isAdmin, fn ($q) => $q->where('assigned_to', $userId))
            ->whereIn('status', ['completed', 'activated'])
            ->count();

        // Active & History Tickets
        $activeTickets = Ticket::withoutGlobalScopes()
            ->when(!$isAdmin, fn ($q) => $q->where('assigned_to', $userId))
            ->whereIn('status', ['open', 'in_progress', 'pending_customer'])
            ->count();

        $historyTickets = Ticket::withoutGlobalScopes()
            ->when(!$isAdmin, fn ($q) => $q->where('assigned_to', $userId))
            ->whereIn('status', ['resolved', 'closed'])
            ->count();

        return [
            'active' => $activeCs + $activeInst + $activeSurveys + $activeTickets,
            'history' => $historyCs + $historyInst + $historySurveys + $historyTickets,
            'total_installations' => $activeCs + $historyCs + $activeInst + $historyInst,
            'total_surveys' => $activeSurveys + $historySurveys,
            'total_tickets' => $activeTickets + $historyTickets,
        ];
    }

    public function render()
    {
        $allJobs = $this->jobs;
        $page = $this->getPage();
        $perPage = 10;

        $paginatedJobs = new LengthAwarePaginator(
            $allJobs->forPage($page, $perPage),
            $allJobs->count(),
            $perPage,
            $page,
            ['path' => request()->url(), 'query' => request()->query()]
        );

        $stats = $this->stats;
        $odps = Odp::all();

        return view('livewire.isp.technician.my-jobs.index', [
            'jobs' => $paginatedJobs,
            'stats' => $stats,
            'odps' => $odps,
        ]);
    }
}
