<?php

namespace App\Livewire\Crm\Activation;

use App\Livewire\Crm\BaseCrmComponent;
use App\Models\CRM\Customer;
use App\Models\CRM\Installation;
use App\Models\CRM\Survey;
use App\Models\ISP\ServiceProfile;
use App\Models\Provisioning\NetworkProfile;
use App\Models\Support\Ticket;
use App\Services\Provisioning\ProvisioningService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Livewire\WithPagination;

class Index extends BaseCrmComponent
{
    use WithPagination;

    public string $activeTab = 'pending'; // 'pending' = Menunggu Aktivasi, 'completed' = Pelanggan Aktif

    // Activation Form
    public bool $showActivationModal = false;
    public ?int $activation_installation_id = null;
    public ?int $activation_survey_id = null;
    public string $activation_target_type = 'installation'; // 'installation' or 'survey'
    public string $activation_prospect_name = '';
    
    public string $pppoe_username = '';
    public string $pppoe_password = '';
    public string $service_profile_id = '';
    public string $network_profile_id = '';

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

    public function openActivationModal(int $id, string $type = 'installation')
    {
        $this->resetErrorBag();
        $this->activation_target_type = $type;

        if ($type === 'installation') {
            $inst = Installation::withoutGlobalScopes()->with(['customer', 'technician', 'onu', 'survey.prospect'])->findOrFail($id);

            // Validasi: Apakah sudah aktif
            if (in_array($inst->status, ['completed', 'verified'], true) && !empty($inst->completed_at)) {
                session()->flash('info', "SPK #{$inst->id} ({$inst->customer_name}) sudah berstatus AKTIF.");
                return;
            }

            $this->activation_installation_id = $inst->id;
            $this->activation_survey_id = $inst->survey_id;
            $customerName = $inst->customer?->name ?? $inst->customer_name ?? $inst->survey?->prospect?->name ?? 'Pelanggan';
            $this->activation_prospect_name = $customerName;

            // Generate Username PPPoE
            $companyName = \App\Models\Setting::getValue('company.name', config('app.name', 'dsbilling'));
            $companySuffix = strtolower(preg_replace('/[^a-zA-Z0-9]/', '', (string) $companyName));
            $cleanName = strtolower(preg_replace('/[^a-zA-Z0-9]/', '', (string) $customerName));
            $this->pppoe_username = ($cleanName ?: 'user' . $inst->id) . '@' . ($companySuffix ?: 'dsbilling');
            $this->pppoe_password = Str::random(8);

            // Pre-select Paket & Network
            $this->service_profile_id = (string) ($inst->package_id ?: '');
            if (empty($this->service_profile_id)) {
                $firstProfile = ServiceProfile::where('status', 'active')->first();
                $this->service_profile_id = $firstProfile ? (string) $firstProfile->id : '';
            }

            $firstNet = NetworkProfile::first();
            $this->network_profile_id = $firstNet ? (string) $firstNet->id : '';

            $this->showActivationModal = true;
            return;
        }

        // Fallback survey legacy
        $survey = Survey::with(['prospect', 'installation'])->findOrFail($id);
        $inst = $survey->installation;
        if ($inst && !in_array($inst->status, ['completed', 'verified'], true)) {
            session()->flash('error', "Layanan belum dapat diaktifkan: Pekerjaan instalasi fisik (SPK #{$inst->id}) masih berstatus '" . strtoupper($inst->status) . "'. Pastikan teknisi telah menyelesaikan penarikan kabel & scan modem ONT di lapangan.");
            return;
        }

        $this->activation_installation_id = $inst?->id;
        $this->activation_survey_id = $survey->id;
        $this->activation_prospect_name = $survey->prospect->name ?? 'Unknown';

        $companyName = \App\Models\Setting::getValue('company.name', 'dsbilling');
        $companySuffix = strtolower(preg_replace('/[^a-zA-Z0-9]/', '', (string) $companyName));
        $this->pppoe_username = strtolower(preg_replace('/[^a-zA-Z0-9]/', '', (string) ($survey->prospect->name ?? 'user'))) . '@' . ($companySuffix ?: 'dsbilling');
        $this->pppoe_password = Str::random(8);

        $firstProfile = ServiceProfile::where('status', 'active')->first();
        $this->service_profile_id = $firstProfile ? (string) $firstProfile->id : '';
        $firstNet = NetworkProfile::first();
        $this->network_profile_id = $firstNet ? (string) $firstNet->id : '';

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

        try {
            $user = auth()->user();
            $userId = $user ? $user->id : 0;

            if ($this->activation_target_type === 'installation' && $this->activation_installation_id) {
                $inst = Installation::withoutGlobalScopes()->with(['customer', 'technician', 'onu', 'survey.prospect'])->findOrFail($this->activation_installation_id);

                $customerName = $inst->customer?->name ?? $inst->customer_name ?? $inst->survey?->prospect?->name ?? 'Pelanggan';
                $customerPhone = $inst->customer?->phone ?? $inst->customer_phone ?? $inst->survey?->prospect?->phone ?? '08123456789';
                $customerAddress = $inst->customer?->address ?? $inst->installation_address ?? $inst->survey?->prospect?->address ?? '-';

                $data = [
                    'customer_id' => $inst->customer_id ?: null,
                    'name' => $customerName,
                    'phone' => $customerPhone,
                    'email' => $inst->customer?->email ?? (strtolower(preg_replace('/[^a-zA-Z0-9]/', '', (string) $customerName)) . '@dsbilling.local'),
                    'address' => $customerAddress,
                    'service_profile_id' => $this->service_profile_id,
                    'network_profile_id' => $this->network_profile_id,
                    'odp_id' => $inst->survey?->odp_id ?: ($inst->onu?->odp_id ?: null),
                    'onu_id' => $inst->onu_id ?: null,
                    'mac_address' => $inst->onu_mac_address ?: ($inst->onu?->mac_address ?: null),
                    'onu_serial_number' => $inst->onu_serial_number ?: ($inst->onu?->serial_number ?: null),
                    'username' => $this->pppoe_username,
                    'password' => $this->pppoe_password,
                    'status' => 'active',
                    'reseller_id' => $inst->reseller_id ?: ($inst->customer?->reseller_id ?: null),
                    'branch_id' => $inst->customer?->branch_id ?: ($inst->onu?->branch_id ?: null),
                ];

                $provisionResult = $provisioningService->activatePPPoEService($data, $userId);
                $createdCustomer = $provisionResult['customer'] ?? null;

                $inst->update([
                    'customer_id' => $inst->customer_id ?: ($createdCustomer?->id ?: null),
                    'package_id' => (int) $this->service_profile_id,
                    'status' => 'completed',
                    'completed_at' => now(),
                    'completed_by' => $userId,
                    'notes' => trim(($inst->notes ? $inst->notes . "\n" : '') . "Diaktivasi via Admin Portal pada " . now()->format('d/m/Y H:i') . " oleh " . ($user->name ?? 'Admin')),
                ]);

                if ($inst->survey) {
                    $inst->survey->update(['status' => 'activated']);
                    if ($inst->survey->prospect) {
                        $inst->survey->prospect->update(['status' => 'activated']);
                    }
                }

                if (Schema::hasTable('tickets')) {
                    Ticket::withoutGlobalScopes()
                        ->where('installation_id', $inst->id)
                        ->whereNotIn('status', ['resolved', 'closed'])
                        ->update([
                            'status' => 'resolved',
                            'resolved_at' => now(),
                            'resolved_by' => $userId,
                            'resolution_notes' => 'Diaktivasi via Admin Portal pada ' . now()->format('d/m/Y H:i'),
                        ]);
                }

                $this->showActivationModal = false;
                $this->activeTab = 'completed';
                session()->flash('success', "Layanan untuk {$customerName} (SPK #{$inst->id}) berhasil diaktifkan!");
                return;
            }

            // Legacy Survey flow
            $survey = Survey::with(['prospect', 'installation'])->findOrFail($this->activation_survey_id);
            $inst = $survey->installation;

            $data = [
                'name' => $survey->prospect->name ?? 'Unknown',
                'phone' => $survey->prospect->phone ?? '0000',
                'email' => $survey->prospect->email ?? 'none@dsbilling.local',
                'address' => $survey->prospect->address ?? 'No Address',
                'service_profile_id' => $this->service_profile_id,
                'network_profile_id' => $this->network_profile_id,
                'odp_id' => $survey->odp_id,
                'onu_id' => $inst?->onu_id,
                'mac_address' => $inst?->onu_mac_address,
                'onu_serial_number' => $inst?->onu_serial_number,
                'username' => $this->pppoe_username,
                'password' => $this->pppoe_password,
                'status' => 'active',
            ];

            $provisioningService->activatePPPoEService($data, $userId);

            if ($survey->prospect) {
                $survey->prospect->update(['status' => 'activated']);
            }
            $survey->update(['status' => 'activated']);
            if ($inst) {
                $inst->update([
                    'status' => 'completed',
                    'completed_at' => now(),
                    'completed_by' => $userId,
                ]);
            }

            $this->showActivationModal = false;
            $this->activeTab = 'completed';
            session()->flash('success', 'Layanan berhasil diaktifkan dan pelanggan didaftarkan!');

        } catch (\Throwable $e) {
            $this->addError('activation', 'Gagal mengaktifkan layanan: ' . $e->getMessage());
        }
    }

    public function render()
    {
        $user = auth()->user();
        $isReseller = $user && method_exists($user, 'hasRole') && $user->hasRole('reseller');
        $resellerId = $isReseller ? ($user->getEffectiveResellerId() ?? $user->id) : null;

        $instQuery = Installation::query()
            ->with(['customer', 'technician', 'assignedTo', 'onu', 'package', 'survey.prospect', 'survey.odp']);

        if ($resellerId) {
            $instQuery->where(function ($q) use ($resellerId) {
                $q->where('reseller_id', $resellerId)
                  ->orWhereHas('customer', fn($cq) => $cq->where('reseller_id', $resellerId));
            });
        }

        if ($this->activeTab === 'pending') {
            $instQuery->where(function ($q) {
                $q->whereIn('status', ['pending', 'scheduled', 'in_progress'])
                  ->orWhereNull('completed_at');
            });
        } else {
            $instQuery->where(function ($q) {
                $q->whereIn('status', ['completed', 'verified'])
                  ->whereNotNull('completed_at');
            });
        }

        if (!empty($this->search)) {
            $s = trim($this->search);
            $instQuery->where(function ($q) use ($s) {
                $q->where('customer_name', 'like', "%{$s}%")
                  ->orWhere('customer_phone', 'like', "%{$s}%")
                  ->orWhere('installation_address', 'like', "%{$s}%")
                  ->orWhere('package_name', 'like', "%{$s}%")
                  ->orWhere('onu_serial_number', 'like', "%{$s}%")
                  ->orWhereHas('customer', function ($cq) use ($s) {
                      $cq->where('name', 'like', "%{$s}%")
                         ->orWhere('phone', 'like', "%{$s}%")
                         ->orWhere('code', 'like', "%{$s}%");
                  })
                  ->orWhereHas('technician', fn($tq) => $tq->where('name', 'like', "%{$s}%"))
                  ->orWhereHas('survey.prospect', fn($pq) => $pq->where('name', 'like', "%{$s}%"));
            });
        }

        $items = $instQuery->latest('updated_at')->paginate($this->perPage === 'all' ? 999999 : (int) $this->perPage);

        // Calculate KPI Stats
        $pendingStatsQuery = Installation::query();
        if ($resellerId) {
            $pendingStatsQuery->where(function ($q) use ($resellerId) {
                $q->where('reseller_id', $resellerId)
                  ->orWhereHas('customer', fn($cq) => $cq->where('reseller_id', $resellerId));
            });
        }
        $pendingCount = (clone $pendingStatsQuery)->where(function ($q) {
            $q->whereIn('status', ['pending', 'scheduled', 'in_progress'])
              ->orWhereNull('completed_at');
        })->count();

        $completedStatsQuery = Installation::query();
        if ($resellerId) {
            $completedStatsQuery->where(function ($q) use ($resellerId) {
                $q->where('reseller_id', $resellerId)
                  ->orWhereHas('customer', fn($cq) => $cq->where('reseller_id', $resellerId));
            });
        }
        $completedCount = (clone $completedStatsQuery)->where(function ($q) {
            $q->whereIn('status', ['completed', 'verified'])
              ->whereNotNull('completed_at');
        })->count();

        $stats = [
            'pending' => $pendingCount,
            'completed' => $completedCount,
        ];

        $serviceProfiles = ServiceProfile::where('status', 'active')->orderBy('name')->get();
        $networkProfiles = NetworkProfile::orderBy('name')->get();

        return view('livewire.crm.activation.index', compact('items', 'stats', 'serviceProfiles', 'networkProfiles'));
    }
}
