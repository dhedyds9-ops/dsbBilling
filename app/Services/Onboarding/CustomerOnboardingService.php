<?php

namespace App\Services\Onboarding;

use App\Models\CRM\Lead;
use App\Models\CRM\Prospect;
use App\Models\CRM\CoverageCheck;
use App\Models\CRM\Survey;
use App\Models\CRM\Quotation;
use App\Models\CRM\Installation;
use App\Models\CRM\QualityControl;
use App\Models\CRM\CustomerActivation;
use App\Repositories\CRM\LeadRepository;
use App\Repositories\CRM\ProspectRepository;
use App\Repositories\CRM\CoverageCheckRepository;
use App\Repositories\CRM\SurveyRepository;
use App\Repositories\CRM\QuotationRepository;
use App\Repositories\CRM\InstallationRepository;
use App\Repositories\CRM\QualityControlRepository;
use App\Repositories\CRM\CustomerActivationRepository;
use Src\Domain\CRM\Events\LeadCreatedEvent;
use Src\Domain\CRM\Events\ProspectCreatedEvent;
use Src\Domain\CRM\Events\CoverageCheckedEvent;
use Src\Domain\CRM\Events\SurveyCompletedEvent;
use Src\Domain\CRM\Events\QuotationCreatedEvent;
use Src\Domain\CRM\Events\QuotationApprovedEvent;
use Src\Domain\CRM\Events\InstallationScheduledEvent;
use Src\Domain\CRM\Events\InstallationCompletedEvent;
use Src\Domain\CRM\Events\QualityControlPassedEvent;
use Src\Domain\CRM\Events\CustomerActivatedEvent;
use App\Jobs\Onboarding\ProvisionServiceJob;
use App\Jobs\Onboarding\ActivatePppoeJob;
use App\Jobs\Onboarding\ActivateOnuJob;
use App\Jobs\Onboarding\CreateCustomerServiceJob;
use App\Jobs\Onboarding\GenerateFirstInvoiceJob;
use App\Jobs\Onboarding\SendCustomerNotificationJob;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\DB;

class CustomerOnboardingService
{
    public function __construct(
        protected LeadRepository $leadRepo,
        protected ProspectRepository $prospectRepo,
        protected CoverageCheckRepository $coverageCheckRepo,
        protected SurveyRepository $surveyRepo,
        protected QuotationRepository $quotationRepo,
        protected InstallationRepository $installationRepo,
        protected QualityControlRepository $qcRepo,
        protected CustomerActivationRepository $activationRepo,
    ) {}

    // Lead Management
    public function createLead(array $data, $userId): Lead
    {
        $data['uuid'] = (string) Str::uuid();
        $data['created_by'] = $userId;
        $data['updated_by'] = $userId;

        $lead = DB::transaction(function () use ($data) {
            return $this->leadRepo->create($data);
        });

        Event::dispatch(new LeadCreatedEvent($lead->uuid, $lead->name));

        return $lead;
    }

    public function convertLeadToProspect(int $leadId, $userId): Prospect
    {
        $lead = $this->leadRepo->find($leadId);
        
        $prospectData = [
            'uuid' => (string) Str::uuid(),
            'name' => $lead->name,
            'phone' => $lead->phone,
            'email' => $lead->email,
            'address' => $lead->address,
            'province' => $lead->province,
            'city' => $lead->city,
            'district' => $lead->district,
            'village' => $lead->village,
            'status' => 'new',
            'created_by' => $userId,
            'updated_by' => $userId,
        ];

        $prospect = DB::transaction(function () use ($prospectData, $lead) {
            $prospect = $this->prospectRepo->create($prospectData);
            $lead->update(['status' => 'converted']);
            return $prospect;
        });

        Event::dispatch(new ProspectCreatedEvent($prospect->uuid, $prospect->name));

        return $prospect;
    }

    // Coverage Check
    public function checkCoverage(array $data, $userId): CoverageCheck
    {
        $data['uuid'] = (string) Str::uuid();
        $data['created_by'] = $userId;
        $data['updated_by'] = $userId;

        $coverageCheck = DB::transaction(function () use ($data) {
            return $this->coverageCheckRepo->create($data);
        });

        Event::dispatch(new CoverageCheckedEvent(
            $coverageCheck->prospect_id,
            $coverageCheck->is_available
        ));

        return $coverageCheck;
    }

    // Survey Management
    public function createSurvey(array $data, $userId): Survey
    {
        $data['uuid'] = (string) Str::uuid();
        $data['created_by'] = $userId;
        $data['updated_by'] = $userId;

        $survey = DB::transaction(function () use ($data) {
            $survey = $this->surveyRepo->create($data);
            
            $prospect = $this->prospectRepo->find($data['prospect_id']);
            if ($prospect) {
                $prospect->update(['status' => 'survey_scheduled']);
            }
            
            return $survey;
        });

        return $survey;
    }

    public function completeSurvey(int $surveyId, array $data, $userId): Survey
    {
        $data['completed_at'] = now();
        $data['status'] = 'completed';
        $data['updated_by'] = $userId;

        $survey = DB::transaction(function () use ($surveyId, $data) {
            $survey = $this->surveyRepo->find($surveyId);
            $survey->update($data);

            $prospect = $survey->prospect;
            $prospect->update(['status' => 'survey_completed']);

            return $survey;
        });

        Event::dispatch(new SurveyCompletedEvent($survey->uuid, $survey->prospect_id));

        return $survey;
    }

    // Quotation Management
    public function createQuotation(array $data, $userId): Quotation
    {
        $data['uuid'] = (string) Str::uuid();
        $data['number'] = 'QUO-' . date('Ymd') . '-' . str_pad(Quotation::count() + 1, 4, '0', STR_PAD_LEFT);
        $data['status'] = 'draft';
        $data['created_by'] = $userId;
        $data['updated_by'] = $userId;

        $quotation = DB::transaction(function () use ($data) {
            return $this->quotationRepo->create($data);
        });

        Event::dispatch(new QuotationCreatedEvent($quotation->uuid, $quotation->prospect_id));

        return $quotation;
    }

    public function approveQuotation(int $quotationId, $userId): Quotation
    {
        $data = [
            'status' => 'approved',
            'approved_by' => $userId,
            'approved_at' => now(),
            'updated_by' => $userId,
        ];

        $quotation = DB::transaction(function () use ($quotationId, $data) {
            $quotation = $this->quotationRepo->find($quotationId);
            $quotation->update($data);
            return $quotation;
        });

        Event::dispatch(new QuotationApprovedEvent($quotation->uuid, $quotation->prospect_id));

        return $quotation;
    }

    // Installation Management
    public function scheduleInstallation(array $data, $userId): Installation
    {
        $data['uuid'] = (string) Str::uuid();
        $data['status'] = 'scheduled';
        $data['created_by'] = $userId;
        $data['updated_by'] = $userId;

        $installation = DB::transaction(function () use ($data) {
            $install = $this->installationRepo->create($data);
            
            // Otomatisasi Tiket Pemasangan Baru (Level: HIGH)
            if (isset($install->survey->prospect)) {
                $prospect = $install->survey->prospect;
                \App\Models\Support\Ticket::create([
                    'uuid' => (string) Str::uuid(),
                    'customer_id' => null, // Pelanggan belum aktif, bisa dikaitkan ke prospect/lead di logic lain jika ada, atau dibiarkan null
                    'title' => 'Pemasangan Baru - ' . $prospect->name,
                    'description' => 'Jadwal Pemasangan Baru untuk ' . $prospect->name . ".\nAlamat: " . $prospect->address . "\nKontak: " . $prospect->phone,
                    'category' => 'installation',
                    'priority' => 'high',
                    'status' => 'open',
                    'assigned_to' => null, // PIC teknisi pemasangan
                    'due_date' => $data['scheduled_at'] ?? now()->addDays(3),
                    'created_by' => $data['created_by'],
                    'updated_by' => $data['updated_by'],
                ]);
            }

            return $install;
        });

        Event::dispatch(new InstallationScheduledEvent($installation->uuid, $installation->survey->prospect_id ?? 0));

        return $installation;
    }

    public function completeInstallation(int $installationId, array $data, $userId): Installation
    {
        $data['completed_at'] = now();
        $data['status'] = 'completed';
        $data['updated_by'] = $userId;

        $installation = DB::transaction(function () use ($installationId, $data) {
            $installation = $this->installationRepo->find($installationId);
            $installation->update($data);
            return $installation;
        });

        Event::dispatch(new InstallationCompletedEvent($installation->uuid, $installation->survey->prospect_id));

        return $installation;
    }

    // Quality Control
    public function performQC(int $installationId, array $data, $userId): QualityControl
    {
        $data['uuid'] = (string) Str::uuid();
        $data['installation_id'] = $installationId;
        $data['checked_at'] = now();
        $data['created_by'] = $userId;
        $data['updated_by'] = $userId;

        $qc = DB::transaction(function () use ($data) {
            return $this->qcRepo->create($data);
        });

        if ($qc->status === 'passed') {
            Event::dispatch(new QualityControlPassedEvent($qc->uuid, $qc->installation_id));
            $this->handleQcPassed($qc);
        }

        return $qc;
    }

    // Auto-workflow after QC passed
    protected function handleQcPassed(QualityControl $qc): void
    {
        // Dispatch jobs in chain
        $installationId = $qc->installation_id;

        ProvisionServiceJob::dispatch($installationId)
            ->chain([
                new ActivatePppoeJob($installationId),
                new ActivateOnuJob($installationId),
                new CreateCustomerServiceJob($installationId),
                new GenerateFirstInvoiceJob($installationId),
            ]);
    }

    // Customer Activation
    public function activateCustomer(int $qcId, array $data, $userId): CustomerActivation
    {
        $data['uuid'] = (string) Str::uuid();
        $data['quality_control_id'] = $qcId;
        $data['activated_at'] = now();
        $data['created_by'] = $userId;
        $data['updated_by'] = $userId;

        $activation = DB::transaction(function () use ($data) {
            $activation = $this->activationRepo->create($data);

            // TODO: Update customer service status to active

            return $activation;
        });

        Event::dispatch(new CustomerActivatedEvent($activation->uuid, $activation->customer_service_id));

        SendCustomerNotificationJob::dispatch($activation->customer_service_id);

        return $activation;
    }
}
