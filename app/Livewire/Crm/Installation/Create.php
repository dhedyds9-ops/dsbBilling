<?php

namespace App\Livewire\Crm\Installation;

use App\Livewire\AdminComponent;
use App\Models\CRM\Customer;
use App\Models\CRM\Installation;
use App\Models\CRM\Survey;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class Create extends AdminComponent
{
    public $customer_source = 'manual'; // customer | survey | manual
    public $customer_id = '';
    public $survey_id = '';
    public $customer_name = '';
    public $assigned_to = '';
    public $scheduled_date = '';
    public $scheduled_time = '09:00';
    public $status = 'scheduled';
    public $priority = 'medium';
    public $notes = '';

    // Device Information
    public $onu_serial_number = '';
    public $onu_mac_address = '';
    public $router_model = '';
    public $router_serial_number = '';

    public function mount()
    {
        parent::mount();
        $this->activeModule = 'crm';
        $this->activePage = 'installations';

        $this->scheduled_date = Carbon::today()->format('Y-m-d');
        $this->scheduled_time = '09:00';

        // Set default source to customer if customers exist
        if (Customer::count() > 0) {
            $this->customer_source = 'customer';
        } elseif (Survey::count() > 0) {
            $this->customer_source = 'survey';
        } else {
            $this->customer_source = 'manual';
        }

        $this->breadcrumbs = [
            ['label' => 'Dashboard', 'url' => route('dashboard')],
            ['label' => 'CRM', 'url' => '#'],
            ['label' => 'Instalasi', 'url' => route('crm.installations.index')],
            ['label' => 'Tambah Instalasi', 'url' => '#'],
        ];
    }

    public function updatedCustomerSource($source)
    {
        if ($source === 'manual') {
            $this->customer_id = '';
            $this->survey_id = '';
        } elseif ($source === 'customer') {
            $this->survey_id = '';
            if ($this->customer_id) {
                $this->updatedCustomerId($this->customer_id);
            }
        } elseif ($source === 'survey') {
            $this->customer_id = '';
            if ($this->survey_id) {
                $this->updatedSurveyId($this->survey_id);
            }
        }
    }

    public function updatedCustomerId($val)
    {
        if ($val) {
            $cust = Customer::find($val);
            if ($cust) {
                $this->customer_name = $cust->name;
            }
        }
    }

    public function updatedSurveyId($val)
    {
        if ($val) {
            $survey = Survey::with('prospect')->find($val);
            if ($survey && $survey->prospect) {
                $this->customer_name = $survey->prospect->name;
            }
        }
    }

    public function save()
    {
        $rules = [
            'customer_source' => 'required|in:customer,survey,manual',
            'scheduled_date' => 'required|date',
            'scheduled_time' => 'required|string',
            'assigned_to' => 'nullable|exists:users,id',
            'status' => 'required|in:scheduled,in_progress,completed,canceled',
            'priority' => 'required|in:low,medium,high,critical',
            'notes' => 'nullable|string',
            'onu_serial_number' => 'nullable|string|max:100',
            'onu_mac_address' => 'nullable|string|max:100',
            'router_model' => 'nullable|string|max:100',
            'router_serial_number' => 'nullable|string|max:100',
        ];

        if ($this->customer_source === 'customer') {
            $rules['customer_id'] = 'required|exists:customers,id';
        } elseif ($this->customer_source === 'survey') {
            $rules['survey_id'] = 'required|exists:surveys,id';
        } else {
            $rules['customer_name'] = 'required|string|max:255';
        }

        $this->validate($rules, [
            'customer_id.required' => 'Silakan pilih pelanggan terlebih dahulu.',
            'survey_id.required' => 'Silakan pilih data hasil survey terlebih dahulu.',
            'customer_name.required' => 'Nama pelanggan wajib diisi.',
            'scheduled_date.required' => 'Tanggal jadwal instalasi wajib diisi.',
        ]);

        // Auto-resolve customer name if not provided
        $finalName = $this->customer_name;
        if ($this->customer_source === 'customer' && $this->customer_id) {
            $cust = Customer::find($this->customer_id);
            if ($cust) $finalName = $cust->name;
        } elseif ($this->customer_source === 'survey' && $this->survey_id) {
            $survey = Survey::with('prospect')->find($this->survey_id);
            if ($survey && $survey->prospect) $finalName = $survey->prospect->name;
        }

        $scheduledAt = Carbon::parse($this->scheduled_date . ' ' . ($this->scheduled_time ?: '09:00:00'));

        Installation::create([
            'uuid' => (string) Str::uuid(),
            'customer_id' => $this->customer_source === 'customer' ? $this->customer_id : null,
            'survey_id' => $this->customer_source === 'survey' ? $this->survey_id : null,
            'customer_name' => $finalName,
            'assigned_to' => $this->assigned_to ?: null,
            'technician_id' => $this->assigned_to ?: null,
            'scheduled_date' => $this->scheduled_date,
            'scheduled_time' => $this->scheduled_time ?: '09:00',
            'scheduled_at' => $scheduledAt,
            'status' => $this->status,
            'priority' => $this->priority,
            'notes' => $this->notes,
            'onu_serial_number' => $this->onu_serial_number,
            'onu_mac_address' => $this->onu_mac_address,
            'router_model' => $this->router_model,
            'router_serial_number' => $this->router_serial_number,
            'created_by' => Auth::id() ?? 1,
        ]);

        session()->flash('success', 'Instalasi baru berhasil dijadwalkan!');
        return redirect()->route('crm.installations.index');
    }

    public function render()
    {
        $customers = Customer::orderBy('name')->get(['id', 'name', 'code', 'phone']);
        $surveys = Survey::with('prospect')->orderBy('id', 'desc')->get();
        $technicians = User::orderBy('name')->get(['id', 'name', 'email']);

        return view('livewire.crm.installation.create', [
            'customers' => $customers,
            'surveys' => $surveys,
            'technicians' => $technicians,
        ]);
    }
}
