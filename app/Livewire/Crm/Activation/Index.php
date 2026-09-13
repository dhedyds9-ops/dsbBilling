<?php

namespace App\Livewire\Crm\Activation;

use App\Livewire\Crm\BaseCrmComponent;
use App\Models\CRM\Survey;
use App\Models\ISP\ServiceProfile;
use App\Models\Provisioning\NetworkProfile;
use App\Services\Provisioning\ProvisioningService;
use Livewire\WithPagination;

class Index extends BaseCrmComponent
{
    use WithPagination;

    public string $activeTab = 'pending';

    // Activation Form
    public $showActivationModal = false;
    public $activation_survey_id = null;
    public $activation_prospect_name = '';
    
    public $pppoe_username = '';
    public $pppoe_password = 'password123';
    public $service_profile_id = '';
    public $network_profile_id = '';

    public function mount()
    {
        $this->activeModule = 'crm';
        $this->activePage = 'activations';
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

    public function openActivationModal($surveyId)
    {
        $survey = Survey::with('prospect')->findOrFail($surveyId);
        $this->activation_survey_id = $survey->id;
        $this->activation_prospect_name = $survey->prospect->name ?? 'Unknown';
        
        // Generate Username PPPoE
        $companyName = \App\Models\Setting::getValue('company.name', 'dsbilling');
        $companySuffix = strtolower(preg_replace('/[^a-zA-Z0-9]/', '', $companyName));
        $this->pppoe_username = strtolower(preg_replace('/[^a-zA-Z0-9]/', '', $survey->prospect->name)) . '@' . $companySuffix;
        
        $this->pppoe_password = 'password123';
        $this->service_profile_id = '';
        $this->network_profile_id = '';

        $this->showActivationModal = true;
    }

    public function activateService(ProvisioningService $provisioningService)
    {
        $this->validate([
            'pppoe_username' => 'required|string',
            'pppoe_password' => 'required|string',
            'service_profile_id' => 'required|exists:service_profiles,id',
            'network_profile_id' => 'required|exists:network_profiles,id',
        ]);

        $survey = Survey::with('prospect')->findOrFail($this->activation_survey_id);
        
        try {
            $data = [
                'name' => $survey->prospect->name ?? 'Unknown',
                'phone' => $survey->prospect->phone ?? '0000',
                'email' => $survey->prospect->email ?? 'none@dsbilling.local',
                'address' => $survey->prospect->address ?? 'No Address',
                'service_profile_id' => $this->service_profile_id,
                'network_profile_id' => $this->network_profile_id,
                'odp_id' => $survey->odp_id,
                'username' => $this->pppoe_username,
                'password' => $this->pppoe_password,
                'status' => 'active',
            ];

            $provisioningService->activatePPPoEService($data, auth()->id());

            // Mark Prospect and Survey as activated
            if ($survey->prospect) {
                $survey->prospect->update(['status' => 'activated']);
            }
            $survey->update(['status' => 'activated']);
            
            $this->showActivationModal = false;
            $this->activeTab = 'completed';
            session()->flash('success', 'Layanan berhasil diaktifkan dan pelanggan didaftarkan!');
            
        } catch (\Exception $e) {
            $this->addError('activation', 'Gagal mengaktifkan layanan: ' . $e->getMessage());
        }
    }

    public function render()
    {
        if ($this->activeTab === 'pending') {
            $query = Survey::with(['prospect', 'assignedTo', 'odp'])
                ->where('status', 'completed')
                ->where('recommendation', 'feasible')
                ->whereHas('prospect', function($q) {
                    $q->where(function($sq) {
                        $sq->whereNull('status')->orWhere('status', '!=', 'activated');
                    });
                });
        } else {
            $query = Survey::with(['prospect', 'assignedTo', 'odp'])
                ->where('status', 'activated');
        }

        if ($this->search) {
            $query->whereHas('prospect', function($q) {
                $q->where('name', 'like', '%' . $this->search . '%')
                  ->orWhere('phone', 'like', '%' . $this->search . '%');
            });
        }

        $items = $query->latest('updated_at')->paginate($this->perPage === 'all' ? 999999 : $this->perPage);

        $stats = [
            'pending' => Survey::where('status', 'completed')->where('recommendation', 'feasible')->whereHas('prospect', function($q) { 
                $q->where(function($sq) {
                    $sq->whereNull('status')->orWhere('status', '!=', 'activated');
                });
            })->count(),
            'completed' => Survey::where('status', 'activated')->count(),
        ];

        $serviceProfiles = ServiceProfile::where('status', 'active')->get();
        $networkProfiles = NetworkProfile::get();

        return view('livewire.crm.activation.index', compact('items', 'stats', 'serviceProfiles', 'networkProfiles'));
    }
}
