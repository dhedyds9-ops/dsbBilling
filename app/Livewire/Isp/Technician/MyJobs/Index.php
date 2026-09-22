<?php

namespace App\Livewire\ISP\Technician\MyJobs;


use Livewire\Component;
use Livewire\Attributes\Layout;
use App\Models\CRM\Survey;
use App\Models\ISP\Odp;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Auth;

#[Layout('layouts.technician-app')]
class Index extends Component
{
    use WithPagination;

    public string $activeTab = 'active'; // active, history
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

    public function mount()
    {
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function switchTab($tab)
    {
        $this->activeTab = $tab;
        $this->resetPage();
    }

    public function openInputResultModal($surveyId)
    {
        $survey = Survey::findOrFail($surveyId);

        // Security check: Only assigned technician can input results
        abort_if($survey->assigned_to !== Auth::id(), 403, 'Unauthorized.');

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

        $survey = Survey::findOrFail($this->selectedSurveyId);
        
        abort_if($survey->assigned_to !== Auth::id(), 403, 'Unauthorized.');

        $survey->update([
            'odp_id' => $this->input_odp_id ?: null,
            'port' => $this->input_port,
            'distance' => $this->input_distance !== '' ? (float)$this->input_distance : null,
            'cable_estimation' => $this->input_cable_estimation !== '' ? (float)$this->input_cable_estimation : null,
            'latitude' => $this->input_latitude !== '' ? (float)$this->input_latitude : null,
            'longitude' => $this->input_longitude !== '' ? (float)$this->input_longitude : null,
            'recommendation' => $this->input_recommendation,
            'notes' => $this->input_notes,
            'status' => 'completed',
            'completed_at' => now(),
        ]);

        $this->showInputResultModal = false;
        $this->activeTab = 'history';
        session()->flash('success', 'Hasil pekerjaan berhasil disimpan dan ditandai selesai.');
    }

    public function render()
    {
        $query = Survey::with(['prospect', 'odp'])
            ->where('assigned_to', Auth::id());

        if ($this->activeTab === 'active') {
            $query->whereIn('status', ['scheduled', 'in_progress', 'pending']);
        } else {
            $query->whereIn('status', ['completed', 'activated']);
        }

        if ($this->search) {
            $query->whereHas('prospect', function ($q) {
                $q->where('name', 'like', '%' . $this->search . '%')
                  ->orWhere('phone', 'like', '%' . $this->search . '%')
                  ->orWhere('address', 'like', '%' . $this->search . '%');
            });
        }

        $jobs = $query->orderBy('scheduled_at', 'asc')->paginate(10);

        $stats = [
            'active' => Survey::where('assigned_to', Auth::id())->whereIn('status', ['scheduled', 'in_progress', 'pending'])->count(),
            'history' => Survey::where('assigned_to', Auth::id())->whereIn('status', ['completed', 'activated'])->count(),
        ];

        $odps = \App\Models\ISP\Odp::all();

        return view('livewire.isp.technician.my-jobs.index', compact('jobs', 'stats', 'odps'))
            ->layout('layouts.enterprise');
    }
}
