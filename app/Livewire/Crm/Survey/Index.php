<?php

namespace App\Livewire\Crm\Survey;

use App\Livewire\Crm\BaseCrmComponent;
use App\Models\CRM\Survey;

class Index extends BaseCrmComponent
{
    

    public $showScheduleModal = false;
    public $schedule_prospect_id = null;
    public $schedule_prospect_name = '';
    public $scheduled_at = '';
    public $assigned_to = '';
    public $survey_address = '';
    public $survey_notes = '';
    public $reseller_id = ''; // Owner/Reseller

    // Properties for Input Hasil Survey
    public $showInputResultModal = false;
    public $input_survey_id = null;
    public $input_odp_id = '';
    public $input_port = '';
    public $input_distance = '';
    public $input_cable_estimation = '';
    public $input_latitude = '';
    public $input_longitude = '';
    public $input_recommendation = 'feasible';
    public $input_notes = '';

    public function mount()
    {
        parent::mount();
        $this->activeTab = 'prospects';
        $this->activeModule = 'crm';
        $this->activePage = 'surveys';
        $this->filters = ['status' => ''];
        $this->scheduled_at = now()->addDay()->format('Y-m-d\TH:i');
        $this->reseller_id = auth()->id();
    }

    public function openScheduleModal($prospectId)
    {
        abort_if(auth()->user()->job_function === \App\Enums\JobFunction::TECHNICIAN->value, 403, 'Akses ditolak: Teknisi tidak diizinkan membuat jadwal.');
        
        $prospect = \App\Models\CRM\Prospect::findOrFail($prospectId);
        $this->schedule_prospect_id = $prospect->id;
        $this->schedule_prospect_name = $prospect->name;
        $this->survey_address = $prospect->address;
        // Default to prospect creator as the reseller/owner
        $this->reseller_id = $prospect->created_by; 
        $this->showScheduleModal = true;
    }

    public function saveSchedule(\App\Services\Onboarding\CustomerOnboardingService $onboarding)
    {
        abort_if(auth()->user()->job_function === \App\Enums\JobFunction::TECHNICIAN->value, 403, 'Akses ditolak: Teknisi tidak diizinkan membuat jadwal.');

        $this->validate([
            'scheduled_at' => 'required|date',
            'assigned_to' => 'nullable|exists:users,id',
            'survey_address' => 'required|string',
            'survey_notes' => 'nullable|string',
            'reseller_id' => 'required|exists:users,id',
        ]);

        // Update the prospect's owner (created_by is used as owner in this context)
        $prospect = \App\Models\CRM\Prospect::find($this->schedule_prospect_id);
        if ($prospect && $this->reseller_id) {
            $prospect->update(['created_by' => $this->reseller_id]);
        }

        // Cek bentrok jadwal (Conflict Validation)
        if ($this->assigned_to) {
            $scheduledTime = \Carbon\Carbon::parse($this->scheduled_at);
            
            $conflict = Survey::where('assigned_to', $this->assigned_to)
                ->whereIn('status', ['scheduled', 'in_progress'])
                ->whereBetween('scheduled_at', [
                    $scheduledTime->copy()->subHours(2),
                    $scheduledTime->copy()->addHours(2)
                ])->first();

            if ($conflict) {
                $this->addError('assigned_to', 'Peringatan: Teknisi ini sudah memiliki jadwal survey pada jam ' . $conflict->scheduled_at->format('H:i') . '. Jarak waktu minimal antar tugas adalah 2 jam.');
                return;
            }
        }

        $survey = $onboarding->createSurvey([
            'prospect_id' => $this->schedule_prospect_id,
            'scheduled_at' => $this->scheduled_at,
            'assigned_to' => $this->assigned_to ?: null,
            'address' => $this->survey_address,
            'notes' => $this->survey_notes,
            'status' => 'scheduled',
        ], auth()->id());

        // Kirim Notifikasi WA ke Teknisi (Jika dipilih)
        if ($this->assigned_to) {
            $technician = \App\Models\User::find($this->assigned_to);
            if ($technician && $technician->whatsapp) {
                try {
                    $waService = app(\App\Services\Notifications\WhatsApp\WhatsAppNotificationService::class);
                    $waMsg = new \App\Services\Notifications\WhatsApp\ValueObjects\WaOutgoingMessage(
                        toPhone: $technician->whatsapp,
                        type: 'text',
                        text: "*[TUGAS BARU] JADWAL SURVEY*\n\nHalo {$technician->name},\nAda tugas survey baru yang ditugaskan kepada Anda:\n\n*Prospek:* {$this->schedule_prospect_name}\n*Waktu:* " . date('d M Y H:i', strtotime($this->scheduled_at)) . "\n*Alamat:* {$this->survey_address}\n*Catatan:* " . ($this->survey_notes ?: '-') . "\n\nSilakan cek sistem untuk detail lebih lanjut.",
                        category: 'support',
                        priorityHigh: true
                    );
                    $waService->sendImmediate($waMsg);
                } catch (\Exception $e) {
                    // Fail silently for notification
                }
            }
        }

        $this->showScheduleModal = false;
        $this->activeTab = 'scheduled';
        session()->flash('success', 'Jadwal survei berhasil dibuat!');
    }

    public function openInputResultModal($surveyId)
    {
        $survey = Survey::with('prospect')->findOrFail($surveyId);
        $this->input_survey_id = $survey->id;
        $this->schedule_prospect_name = $survey->prospect->name ?? '-';
        $this->input_odp_id = $survey->odp_id ?? '';
        $this->input_port = $survey->port ?? '';
        $this->input_distance = $survey->distance ?? '';
        $this->input_cable_estimation = $survey->cable_estimation ?? '';
        $this->input_latitude = $survey->latitude ?? '';
        $this->input_longitude = $survey->longitude ?? '';
        $this->input_recommendation = $survey->recommendation ?? 'feasible';
        $this->input_notes = $survey->notes ?? '';
        
        $this->showInputResultModal = true;
    }

    public function saveSurveyResult()
    {
        $this->validate([
            'input_odp_id' => 'nullable|exists:odps,id',
            'input_port' => 'nullable|string',
            'input_distance' => 'nullable|numeric',
            'input_cable_estimation' => 'nullable|numeric',
            'input_latitude' => 'nullable|string',
            'input_longitude' => 'nullable|string',
            'input_recommendation' => 'required|string|in:feasible,not_feasible',
            'input_notes' => 'nullable|string',
        ]);

        $survey = Survey::findOrFail($this->input_survey_id);

        // Teknisi hanya boleh update tugasnya sendiri, Admin boleh update semua
        if (auth()->user()->job_function === \App\Enums\JobFunction::TECHNICIAN->value) {
            abort_if($survey->assigned_to !== auth()->id(), 403, 'Akses ditolak: Anda hanya dapat menyimpan hasil survey yang ditugaskan kepada Anda.');
        }

        $survey->update([
            'odp_id' => $this->input_odp_id ?: null,
            'port' => $this->input_port ?: null,
            'distance' => $this->input_distance !== '' && $this->input_distance !== null ? (float)$this->input_distance : null,
            'cable_estimation' => $this->input_cable_estimation !== '' && $this->input_cable_estimation !== null ? (float)$this->input_cable_estimation : null,
            'latitude' => $this->input_latitude !== '' && $this->input_latitude !== null ? (float)$this->input_latitude : null,
            'longitude' => $this->input_longitude !== '' && $this->input_longitude !== null ? (float)$this->input_longitude : null,
            'recommendation' => $this->input_recommendation,
            'notes' => $this->input_notes,
            'completed_at' => now(),
            'status' => 'completed',
        ]);

        $this->showInputResultModal = false;
        $this->activeTab = 'completed';
        session()->flash('success', 'Hasil survey berhasil disimpan!');
    }

    public function delete($id)
    {
        abort_if(auth()->user()->job_function === \App\Enums\JobFunction::TECHNICIAN->value, 403, 'Akses ditolak: Teknisi tidak diizinkan menghapus data.');

        if ($this->activeTab === 'prospects') {
            $prospect = \App\Models\CRM\Prospect::findOrFail($id);
            $prospect->delete();
            session()->flash('success', 'Prospect berhasil dihapus!');
        } else {
            $survey = Survey::findOrFail($id);
            $survey->delete();
            session()->flash('success', 'Survey berhasil dihapus!');
        }
    }

    public function export()
    {
        session()->flash('info', 'Export feature will be implemented later!');
    }

    public function render()
    {
        if ($this->activeTab === 'prospects') {
            $query = \App\Models\CRM\Prospect::whereDoesntHave('surveys');
            
            if ($this->search) {
                $query->where(function($q) {
                    $q->where('name', 'like', '%' . $this->search . '%')
                      ->orWhere('phone', 'like', '%' . $this->search . '%');
                });
            }
            $items = $query->latest()->paginate($this->perPage === 'all' ? 999999 : $this->perPage);
        } else {
            $query = Survey::with('prospect', 'assignedTo');

            if ($this->search) {
                $query->whereHas('prospect', function($q) {
                    $q->where('name', 'like', '%' . $this->search . '%')
                      ->orWhere('phone', 'like', '%' . $this->search . '%');
                });
            }

            if ($this->activeTab === 'scheduled') {
                $query->whereNotIn('status', ['completed', 'cancelled']);
            } elseif ($this->activeTab === 'completed') {
                $query->whereIn('status', ['completed', 'cancelled']);
            }

            $items = $query->latest()->paginate($this->perPage === 'all' ? 999999 : $this->perPage);
        }

        $stats = [
            'prospects' => \App\Models\CRM\Prospect::whereDoesntHave('surveys')->count(),
            'scheduled' => Survey::whereNotIn('status', ['completed', 'cancelled'])->count(),
            'completed' => Survey::whereIn('status', ['completed', 'cancelled'])->count(),
        ];

        // Ambil staf dengan fungsi pekerjaan Teknisi atau NOC
        $technicians = \App\Models\User::whereIn('job_function', [
            \App\Enums\JobFunction::TECHNICIAN->value,
            \App\Enums\JobFunction::NOC->value,
        ])->get();

        // Hitung beban kerja (workload) jika ada tanggal yang dipilih
        if ($this->scheduled_at) {
            try {
                $selectedDate = \Carbon\Carbon::parse($this->scheduled_at)->toDateString();
                
                $workloads = Survey::whereIn('status', ['scheduled', 'in_progress'])
                    ->whereDate('scheduled_at', $selectedDate)
                    ->selectRaw('assigned_to, count(*) as total')
                    ->groupBy('assigned_to')
                    ->pluck('total', 'assigned_to');
                
                $technicians->map(function($tech) use ($workloads) {
                    $tech->workload = $workloads[$tech->id] ?? 0;
                    return $tech;
                });
            } catch (\Exception $e) {
                // Ignore parse errors if date is incomplete
                $technicians->map(function($tech) { $tech->workload = 0; return $tech; });
            }
        } else {
            $technicians->map(function($tech) { $tech->workload = 0; return $tech; });
        }
        
        // Ambil daftar Owner/Reseller (Kecuali Teknisi dan NOC)
        $resellers = \App\Models\User::whereHas('roles', function($q) {
            $q->whereIn('name', ['administrator', 'reseller', 'manager']);
        })
        ->where(function ($q) {
            $q->whereNull('job_function')
              ->orWhereNotIn('job_function', [
                  \App\Enums\JobFunction::TECHNICIAN->value,
                  \App\Enums\JobFunction::NOC->value,
              ]);
        })
        ->get();

        // Ambil daftar ODP untuk pilihan
        $odps = \App\Models\ISP\Odp::orderBy('name')->get();

        return view('livewire.crm.survey.index', compact('items', 'stats', 'technicians', 'resellers', 'odps'));
    }
}



