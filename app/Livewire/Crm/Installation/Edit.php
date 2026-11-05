<?php

namespace App\Livewire\Crm\Installation;

use App\Livewire\AdminComponent;
use App\Models\CRM\Customer;
use App\Models\CRM\Installation;
use App\Models\CRM\Survey;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class Edit extends AdminComponent
{
    public $installationId;
    public $customer_source = 'manual';
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

    public function mount($id = null)
    {
        parent::mount();
        $this->activeModule = 'crm';
        $this->activePage = 'installations';
        $this->installationId = $id;

        $installation = Installation::with(['customer', 'survey.prospect', 'assignedTo'])->findOrFail($id);
        
        $this->customer_id = $installation->customer_id ?? '';
        $this->survey_id = $installation->survey_id ?? '';
        $this->customer_name = $installation->customer_display_name;

        if ($this->customer_id) {
            $this->customer_source = 'customer';
        } elseif ($this->survey_id) {
            $this->customer_source = 'survey';
        } else {
            $this->customer_source = 'manual';
        }

        $this->assigned_to = $installation->assigned_to ?? $installation->technician_id ?? '';
        
        if ($installation->scheduled_date) {
            $this->scheduled_date = $installation->scheduled_date->format('Y-m-d');
        } elseif ($installation->scheduled_at) {
            $this->scheduled_date = $installation->scheduled_at->format('Y-m-d');
        } else {
            $this->scheduled_date = Carbon::today()->format('Y-m-d');
        }

        $this->scheduled_time = $installation->scheduled_time ?? ($installation->scheduled_at ? $installation->scheduled_at->format('H:i') : '09:00');
        $this->status = $installation->status ?? 'scheduled';
        $this->priority = $installation->priority ?? 'medium';
        $this->notes = $installation->notes ?? '';

        $this->onu_serial_number = $installation->onu_serial_number ?? '';
        $this->onu_mac_address = $installation->onu_mac_address ?? '';
        $this->router_model = $installation->router_model ?? '';
        $this->router_serial_number = $installation->router_serial_number ?? '';

        $this->breadcrumbs = [
            ['label' => 'Dashboard', 'url' => route('dashboard')],
            ['label' => 'CRM', 'url' => '#'],
            ['label' => 'Instalasi', 'url' => route('crm.installations.index')],
            ['label' => 'Edit Instalasi #' . $installation->id, 'url' => '#'],
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
            'assigned_to' => ['nullable', function ($attr, $value, $fail) {
                if (!$value) return;
                // Data lama: izinkan mempertahankan penugasan yang sudah ada
                $current = Installation::whereKey($this->installationId)->value('assigned_to');
                if ((int) $current === (int) $value) return;
                if (!\App\Services\CRM\InstallationTechnicianNotifier::technicianQuery()->whereKey($value)->exists()) {
                    $fail('Teknisi yang dipilih tidak valid (hanya user dengan fungsi Teknisi).');
                }
            }],
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

        $finalName = $this->customer_name;
        if ($this->customer_source === 'customer' && $this->customer_id) {
            $cust = Customer::find($this->customer_id);
            if ($cust) $finalName = $cust->name;
        } elseif ($this->customer_source === 'survey' && $this->survey_id) {
            $survey = Survey::with('prospect')->find($this->survey_id);
            if ($survey && $survey->prospect) $finalName = $survey->prospect->name;
        }

        $scheduledAt = Carbon::parse($this->scheduled_date . ' ' . ($this->scheduled_time ?: '09:00:00'));

        $installation = Installation::findOrFail($this->installationId);
        $previousTechId = (int) ($installation->assigned_to ?: $installation->technician_id ?: 0);
        $installation->update([
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
            'updated_by' => Auth::id() ?? 1,
        ]);

        $successMsg = 'Data instalasi berhasil diperbarui!';
        $newTechId = (int) ($this->assigned_to ?: 0);
        // Kirim notifikasi hanya jika teknisi baru ditugaskan / diganti (hindari spam saat edit biasa)
        if ($newTechId && $newTechId !== $previousTechId) {
            $notif = app(\App\Services\CRM\InstallationTechnicianNotifier::class)
                ->notifyAssigned($installation->fresh(), $previousTechId ? 'reassigned' : 'created');
            $successMsg .= $notif['whatsapp']
                ? ' Notifikasi WhatsApp & Portal Teknisi terkirim.'
                : ' Notifikasi Portal Teknisi terkirim; WhatsApp gagal: ' . ($notif['whatsapp_error'] ?? 'tidak diketahui') . '.';
        }

        session()->flash('success', $successMsg);
        return redirect()->route('crm.installations.index');
    }

    public function render()
    {
        $customers = Customer::orderBy('name')->get(['id', 'name', 'code', 'phone']);
        $surveys = Survey::with('prospect')->orderBy('id', 'desc')->get();
        $technicians = \App\Services\CRM\InstallationTechnicianNotifier::technicianQuery()
            ->orderBy('name')
            ->get(['id', 'name', 'email']);

        // Pertahankan penugasan lama (non-teknisi) agar tetap tampil di dropdown
        if ($this->assigned_to && !$technicians->contains('id', (int) $this->assigned_to)) {
            $current = Installation::whereKey($this->installationId)->value('assigned_to');
            if ((int) $current === (int) $this->assigned_to && ($legacy = User::find($current, ['id', 'name', 'email']))) {
                $legacy->name .= ' (penugasan lama)';
                $technicians->prepend($legacy);
            }
        }

        return view('livewire.crm.installation.edit', [
            'customers' => $customers,
            'surveys' => $surveys,
            'technicians' => $technicians,
        ]);
    }
}
