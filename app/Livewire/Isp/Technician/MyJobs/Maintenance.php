<?php

namespace App\Livewire\Isp\Technician\MyJobs;

use App\Models\Customer\CustomerService;
use App\Models\ISP\Onu;
use App\Models\Support\Ticket;
use App\Services\ISP\GenieAcsDiagnosticService;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Schema;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;
use Src\Domain\Support\Events\TicketResolvedEvent;

#[Layout('layouts.technician-app')]
class Maintenance extends Component
{
    use WithPagination;

    #[Url]
    public string $activeTab = 'active'; // active, history

    #[Url]
    public string $search = '';

    // Modal Completion
    public bool $showModal = false;
    public bool $isEditing = false;
    public string $modalSource = 'maintenance'; // maintenance, ticket
    public ?int $selectedId = null;
    public string $selectedCode = '';
    public string $selectedTitle = '';
    public string $selectedTarget = '';
    public string $resolution_notes = '';
    public string $edit_status = 'completed';

    // Live ONT Diagnostics States (P1-1B)
    public bool $showDiagnosticModal = false;
    public ?int $selectedOnuId = null;
    public ?array $diagnostics = null;
    public bool $diagnosticLoading = false;
    public ?string $diagnosticError = null;
    public ?string $diagnosticSuccess = null;
    public ?string $diagnosticCustomerName = null;
    public ?string $diagnosticCustomerPhone = null;
    public ?string $diagnosticCustomerAddress = null;

    // Reboot Field Action States
    public bool $showRebootModal = false;
    public bool $isRebooting = false;
    public ?string $rebootStatusMessage = null;
    public ?string $priorLastInform = null;
    public ?int $priorUptime = null;
    public bool $rebootVerified = false;

    // Wi-Fi Field Action States
    public bool $showWifiModal = false;
    public string $wifi_ssid = '';
    public string $wifi_password = '';
    public bool $isSavingWifi = false;

    // Quick ONT Selector
    public bool $showSearchOntModal = false;
    public string $searchOntQuery = '';

    public function mount(): void
    {
        if (request()->has('diagnose_onu')) {
            $this->openDiagnosticsForOnu((int) request('diagnose_onu'));
        } elseif (request()->has('diagnose_ticket')) {
            $this->openDiagnostics('ticket', (int) request('diagnose_ticket'));
        } elseif (request()->has('diagnose_maint')) {
            $this->openDiagnostics('maintenance', (int) request('diagnose_maint'));
        }
    }

    private function isAdminOversight(): bool
    {
        $user = Auth::user();
        if (!$user) {
            return false;
        }

        return $user->hasRole(['administrator', 'superadmin'])
            && strtoupper((string) $user->job_function) !== 'TECHNICIAN';
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function switchTab(string $tab): void
    {
        $this->activeTab = in_array($tab, ['active', 'history'], true) ? $tab : 'active';
        $this->resetPage();
    }

    // =========================================================================
    // LIVE ONT DIAGNOSTICS & FIELD ACTIONS (P1-1B)
    // =========================================================================

    /**
     * Membuka panel diagnostik ONT berdasarkan tiket atau tugas maintenance.
     */
    public function openDiagnostics(string $source, int $id): void
    {
        $isAdmin = $this->isAdminOversight();
        $uid = (int) Auth::id();
        $this->diagnosticError = null;
        $this->diagnosticSuccess = null;
        $this->rebootStatusMessage = null;
        $this->rebootVerified = false;

        $targetOnu = null;
        $customer = null;

        if ($source === 'ticket') {
            $ticket = Ticket::withoutGlobalScopes()
                ->with(['customer' => fn ($q) => $q->withoutGlobalScopes()])
                ->findOrFail($id);

            if (!$isAdmin && (int) $ticket->assigned_to !== $uid) {
                abort(403, 'Tiket ini tidak ditugaskan kepada Anda.');
            }

            $customer = $ticket->customer;
            if ($customer) {
                $cs = CustomerService::withoutGlobalScopes()
                    ->with('onu')
                    ->where('customer_id', $customer->id)
                    ->whereNotNull('onu_id')
                    ->first();
                $targetOnu = $cs?->onu;

                if (!$targetOnu && method_exists($customer, 'getOnuDeviceAttribute')) {
                    $onuDev = $customer->onu_device;
                    $targetOnu = $onuDev['onu'] ?? null;
                }
            }
        } elseif ($source === 'maintenance' && Schema::hasTable('support_maintenances')) {
            $row = DB::table('support_maintenances')->where('id', $id)->first();
            if (!$row) {
                abort(404, 'Tugas maintenance tidak ditemukan.');
            }
            if (!$isAdmin && (int) ($row->assigned_to ?? 0) !== $uid) {
                abort(403, 'Jadwal maintenance ini tidak ditugaskan kepada Anda.');
            }

            if (!empty($row->device_name)) {
                $targetOnu = Onu::withoutGlobalScopes()
                    ->where('serial_number', $row->device_name)
                    ->orWhere('code', $row->device_name)
                    ->first();
            }
        }

        if (!$targetOnu) {
            $this->diagnosticError = 'Perangkat ONT/ONU tidak terhubung dengan pekerjaan ini atau belum terdaftar.';
            $this->diagnostics = null;
            $this->selectedOnuId = null;
            $this->showDiagnosticModal = true;
            return;
        }

        $this->loadOnuDiagnostics($targetOnu, $customer);
    }

    /**
     * Membuka panel diagnostik langsung untuk perangkat ONT tertentu.
     */
    public function openDiagnosticsForOnu(int $onuId): void
    {
        $this->diagnosticError = null;
        $this->diagnosticSuccess = null;
        $this->rebootStatusMessage = null;
        $this->rebootVerified = false;

        $onu = Onu::withoutGlobalScopes()->with('vendor')->findOrFail($onuId);

        // Cari relasi customer jika ada
        $cs = CustomerService::withoutGlobalScopes()
            ->with(['customer' => fn ($q) => $q->withoutGlobalScopes()])
            ->where('onu_id', $onu->id)
            ->first();
        $customer = $cs?->customer;

        $this->loadOnuDiagnostics($onu, $customer);
    }

    /**
     * Memuat dan mengeksekusi service diagnostik untuk ONT.
     */
    protected function loadOnuDiagnostics(Onu $onu, $customer = null, bool $forceFresh = false): void
    {
        $this->selectedOnuId = $onu->id;
        $this->diagnosticCustomerName = $customer?->name ?? 'Pelanggan';
        $this->diagnosticCustomerPhone = $customer?->phone ?? '-';
        $this->diagnosticCustomerAddress = $customer?->address ?? ($onu->description ?: 'Lokasi ONT');

        $service = app(GenieAcsDiagnosticService::class);

        try {
            // Validasi otorisasi tenant & branch
            $service->authorizeTechnician($onu, Auth::user());

            $this->diagnostics = $service->getLiveDiagnostics($onu, $forceFresh);
            $this->wifi_ssid = (string) ($this->diagnostics['wifi']['ssid_24'] !== 'Tidak Didukung'
                ? $this->diagnostics['wifi']['ssid_24']
                : ($onu->wifi_ssid ?: ''));
            $this->wifi_password = ''; // Jangan memuat password ke form
            $this->showDiagnosticModal = true;
        } catch (\Throwable $e) {
            $this->diagnosticError = 'Gagal membaca diagnostik ONT: ' . $e->getMessage();
            $this->diagnostics = null;
            $this->showDiagnosticModal = true;
        }
    }

    /**
     * Action: Refresh Parameters (Summon) ke GenieACS NBI.
     */
    public function refreshDiagnostics(): void
    {
        if (!$this->selectedOnuId) {
            return;
        }

        $this->diagnosticError = null;
        $this->diagnosticSuccess = null;

        $onu = Onu::withoutGlobalScopes()->findOrFail($this->selectedOnuId);
        $service = app(GenieAcsDiagnosticService::class);

        try {
            $result = $service->refreshParameters($onu, Auth::user());
            if ($result['status'] === 'throttled') {
                $this->diagnosticError = $result['message'];
                return;
            }

            $this->diagnostics = $result['diagnostics'];
            $this->diagnosticSuccess = 'Parameter ONT berhasil di-refresh dari GenieACS.';
        } catch (\Throwable $e) {
            $this->diagnosticError = 'Refresh gagal: ' . $e->getMessage();
        }
    }

    /**
     * Action: Konfirmasi Reboot ONT.
     */
    public function confirmReboot(): void
    {
        $this->showRebootModal = true;
    }

    /**
     * Action: Eksekusi Reboot ONT via GenieACS.
     */
    public function executeReboot(): void
    {
        if (!$this->selectedOnuId) {
            return;
        }

        $this->isRebooting = true;
        $this->diagnosticError = null;
        $this->diagnosticSuccess = null;

        $onu = Onu::withoutGlobalScopes()->findOrFail($this->selectedOnuId);
        $service = app(GenieAcsDiagnosticService::class);

        try {
            $res = $service->rebootOnt($onu, Auth::user());

            if ($res['status'] === 'throttled') {
                $this->diagnosticError = $res['message'];
                $this->showRebootModal = false;
                $this->isRebooting = false;
                return;
            }

            $this->priorLastInform = $res['prior_last_inform'] ?? null;
            $this->priorUptime = $res['prior_uptime'] ?? null;
            $this->rebootStatusMessage = $res['message'];
            $this->diagnosticSuccess = 'Task reboot berhasil dikirim ke GenieACS. Menunggu ONT restart...';
            $this->showRebootModal = false;
        } catch (\Throwable $e) {
            $this->diagnosticError = 'Gagal me-reboot ONT: ' . $e->getMessage();
            $this->showRebootModal = false;
        } finally {
            $this->isRebooting = false;
        }
    }

    /**
     * Action: Verifikasi status reboot ONT.
     */
    public function verifyReboot(): void
    {
        if (!$this->selectedOnuId) {
            return;
        }

        $onu = Onu::withoutGlobalScopes()->findOrFail($this->selectedOnuId);
        $service = app(GenieAcsDiagnosticService::class);

        try {
            $res = $service->verifyReboot($onu, $this->priorLastInform, $this->priorUptime);
            $this->rebootVerified = $res['verified'] ?? false;
            $this->rebootStatusMessage = $res['message'] ?? '';

            if ($this->rebootVerified) {
                $this->diagnosticSuccess = $res['message'];
                // Refresh data terbaru setelah terverifikasi
                $this->diagnostics = $service->getLiveDiagnostics($onu, true);
            }
        } catch (\Throwable $e) {
            $this->rebootStatusMessage = 'Pengecekan reboot gagal: ' . $e->getMessage();
        }
    }

    /**
     * Action: Buka Modal Ubah Wi-Fi.
     */
    public function openWifiModal(): void
    {
        $this->showWifiModal = true;
        $this->wifi_password = '';
    }

    /**
     * Action: Simpan Konfigurasi Wi-Fi baru ke ONT via GenieACS.
     */
    public function saveWifi(): void
    {
        if (!$this->selectedOnuId) {
            return;
        }

        $this->validate([
            'wifi_ssid' => 'required|string|min:1|max:32',
            'wifi_password' => 'nullable|string|min:8|max:63',
        ], [
            'wifi_ssid.required' => 'SSID Wi-Fi wajib diisi.',
            'wifi_ssid.max' => 'SSID Wi-Fi maksimal 32 karakter.',
            'wifi_password.min' => 'Password Wi-Fi minimal 8 karakter.',
        ]);

        $this->isSavingWifi = true;
        $this->diagnosticError = null;
        $this->diagnosticSuccess = null;

        $onu = Onu::withoutGlobalScopes()->findOrFail($this->selectedOnuId);
        $service = app(GenieAcsDiagnosticService::class);

        try {
            $res = $service->updateWifi($onu, [
                'ssid_24' => $this->wifi_ssid,
                'password_24' => $this->wifi_password ?: null,
            ], Auth::user());

            if (($res['status'] ?? '') === 'throttled') {
                $this->diagnosticError = $res['message'];
                $this->isSavingWifi = false;
                return;
            }

            $this->showWifiModal = false;
            $this->wifi_password = ''; // Bersihkan password dari state memory
            $this->diagnosticSuccess = $res['message'];

            // Refresh diagnostik agar tampilan Wi-Fi terupdate
            $this->diagnostics = $service->getLiveDiagnostics($onu, true);
        } catch (\Throwable $e) {
            $this->diagnosticError = 'Gagal mengubah Wi-Fi: ' . $e->getMessage();
        } finally {
            $this->isSavingWifi = false;
        }
    }

    public function closeDiagnostics(): void
    {
        $this->showDiagnosticModal = false;
        $this->selectedOnuId = null;
        $this->diagnostics = null;
        $this->diagnosticError = null;
        $this->diagnosticSuccess = null;
        $this->wifi_password = '';
    }

    // =========================================================================
    // MAINTENANCE WORKFLOW ACTIONS
    // =========================================================================

    public function startItem(string $source, int $id): void
    {
        $isAdmin = $this->isAdminOversight();
        $uid = (int) Auth::id();

        if ($source === 'maintenance' && Schema::hasTable('support_maintenances')) {
            $row = DB::table('support_maintenances')->where('id', $id)->first();
            if (!$row) {
                return;
            }
            if (!$isAdmin && (int) ($row->assigned_to ?? 0) !== $uid) {
                abort(403, 'Jadwal maintenance ini tidak ditugaskan kepada Anda.');
            }
            DB::table('support_maintenances')->where('id', $id)->update([
                'status' => 'in_progress',
                'updated_by' => $uid,
                'updated_at' => now(),
            ]);
            session()->flash('success', "Maintenance #MT-{$id} mulai dikerjakan.");
            return;
        }

        if ($source === 'ticket') {
            $ticket = Ticket::withoutGlobalScopes()->findOrFail($id);
            if (!$isAdmin && (int) $ticket->assigned_to !== $uid) {
                abort(403, 'Tiket ini tidak ditugaskan kepada Anda.');
            }
            $ticket->update(['status' => 'in_progress']);
            session()->flash('success', "Tiket #{$id} mulai dikerjakan.");
        }
    }

    public function openCompleteModal(string $source, int $id, bool $edit = false): void
    {
        $isAdmin = $this->isAdminOversight();
        $uid = (int) Auth::id();

        if ($source === 'maintenance' && Schema::hasTable('support_maintenances')) {
            $row = DB::table('support_maintenances')->where('id', $id)->first();
            if (!$row) {
                return;
            }
            if (!$isAdmin && (int) ($row->assigned_to ?? 0) !== $uid) {
                abort(403, 'Jadwal maintenance ini tidak ditugaskan kepada Anda.');
            }
            $this->modalSource = 'maintenance';
            $this->selectedId = (int) $row->id;
            $this->selectedCode = 'MT-' . $row->id;
            $this->selectedTitle = (string) ($row->title ?? '-');
            $this->selectedTarget = trim(($row->device_name ?: ucfirst((string) ($row->device_type ?? 'Jaringan'))) . ($row->location_name ? ' · ' . $row->location_name : ''));
            $this->resolution_notes = (string) ($row->resolution_notes ?? '');
            $this->isEditing = $edit;
            $this->edit_status = 'completed';
            $this->showModal = true;
            return;
        }

        $ticket = Ticket::withoutGlobalScopes()
            ->with(['customer' => fn ($q) => $q->withoutGlobalScopes()])
            ->findOrFail($id);

        if (!$isAdmin && (int) $ticket->assigned_to !== $uid) {
            abort(403, 'Tiket ini tidak ditugaskan kepada Anda.');
        }

        if ($edit && $ticket->status === 'closed') {
            session()->flash('error', 'Tiket sudah ditutup permanen (Closed) oleh Admin.');
            return;
        }

        $this->modalSource = 'ticket';
        $this->selectedId = $ticket->id;
        $this->selectedCode = !empty($ticket->uuid)
            ? ('TKT-' . strtoupper(substr(str_replace('-', '', $ticket->uuid), 0, 6)))
            : ('TKT-' . $ticket->id);
        $this->selectedTitle = (string) $ticket->title;
        $this->selectedTarget = $ticket->customer?->name ?? 'Jaringan Pelanggan';
        $this->resolution_notes = (string) ($ticket->resolution_notes ?? '');
        $this->isEditing = $edit;
        $this->edit_status = 'completed';
        $this->showModal = true;
    }

    public function saveCompletion(): void
    {
        if (!$this->selectedId) {
            return;
        }

        $isAdmin = $this->isAdminOversight();
        $uid = (int) Auth::id();

        if ($this->modalSource === 'maintenance' && Schema::hasTable('support_maintenances')) {
            $row = DB::table('support_maintenances')->where('id', $this->selectedId)->first();
            if (!$row) {
                return;
            }
            if (!$isAdmin && (int) ($row->assigned_to ?? 0) !== $uid) {
                abort(403);
            }

            if ($this->isEditing && $this->edit_status === 'in_progress') {
                DB::table('support_maintenances')->where('id', $row->id)->update([
                    'status' => 'in_progress',
                    'completed_at' => null,
                    'resolution_notes' => trim($this->resolution_notes) !== '' ? trim($this->resolution_notes) : null,
                    'updated_by' => $uid,
                    'updated_at' => now(),
                ]);
                $this->showModal = false;
                $this->activeTab = 'active';
                session()->flash('success', "Maintenance #{$this->selectedCode} dibuka kembali ke Tugas Aktif.");
                return;
            }

            $note = trim($this->resolution_notes) !== ''
                ? trim($this->resolution_notes)
                : 'Pemeliharaan jaringan selesai dikerjakan oleh teknisi';

            DB::table('support_maintenances')->where('id', $row->id)->update([
                'status' => 'completed',
                'completed_at' => $row->completed_at ?? now(),
                'resolution_notes' => $note,
                'updated_by' => $uid,
                'updated_at' => now(),
            ]);

            $this->showModal = false;
            session()->flash('success', $this->isEditing
                ? "Laporan penyelesaian #{$this->selectedCode} berhasil diperbarui."
                : "Maintenance #{$this->selectedCode} berhasil diselesaikan.");
            return;
        }

        $ticket = Ticket::withoutGlobalScopes()->findOrFail($this->selectedId);
        if (!$isAdmin && (int) $ticket->assigned_to !== $uid) {
            abort(403);
        }
        if ($ticket->status === 'closed') {
            $this->showModal = false;
            session()->flash('error', 'Tiket sudah ditutup permanen oleh Admin.');
            return;
        }

        $hasCol = Schema::hasColumn('tickets', 'resolution_notes');

        if ($this->isEditing && $this->edit_status === 'in_progress') {
            $payload = ['status' => 'in_progress', 'resolved_at' => null];
            if ($hasCol) {
                $payload['resolution_notes'] = trim($this->resolution_notes) !== '' ? trim($this->resolution_notes) : null;
            }
            $ticket->update($payload);
            $this->showModal = false;
            $this->activeTab = 'active';
            session()->flash('success', "Tiket {$this->selectedCode} dibuka kembali ke Tugas Aktif.");
            return;
        }

        $note = trim($this->resolution_notes) !== ''
            ? trim($this->resolution_notes)
            : 'Maintenance selesai dikerjakan oleh teknisi';

        $payload = [
            'status' => 'resolved',
            'resolved_at' => $ticket->resolved_at ?? now(),
        ];
        if ($hasCol) {
            $payload['resolution_notes'] = $note;
        }
        $ticket->update($payload);

        if (!$this->isEditing) {
            Event::dispatch(
                TicketResolvedEvent::create(
                    (string) $ticket->id,
                    (string) $uid,
                    $note
                )
            );
        }

        $this->showModal = false;
        session()->flash('success', $this->isEditing
            ? "Catatan penyelesaian {$this->selectedCode} berhasil diperbarui."
            : "Tiket Maintenance {$this->selectedCode} berhasil diselesaikan.");
    }

    public function render()
    {
        $uid = Auth::id();
        $isAdmin = $this->isAdminOversight();
        $search = trim($this->search);
        $items = collect();

        $activeCount = 0;
        $historyCount = 0;

        if (Schema::hasTable('support_maintenances')) {
            $baseMaint = DB::table('support_maintenances')
                ->when(!$isAdmin, fn ($q) => $q->where('assigned_to', $uid));

            $activeCount += (clone $baseMaint)->whereIn('status', ['scheduled', 'in_progress'])->count();
            $historyCount += (clone $baseMaint)->whereIn('status', ['completed', 'cancelled'])->count();

            $mq = clone $baseMaint;
            if ($this->activeTab === 'active') {
                $mq->whereIn('status', ['scheduled', 'in_progress']);
            } else {
                $mq->whereIn('status', ['completed', 'cancelled']);
            }

            if ($search !== '') {
                $mq->where(function ($sub) use ($search) {
                    $sub->where('title', 'like', "%{$search}%")
                        ->orWhere('device_name', 'like', "%{$search}%")
                        ->orWhere('location_name', 'like', "%{$search}%")
                        ->orWhere('notes', 'like', "%{$search}%");
                });
            }

            foreach ($mq->orderByDesc('scheduled_date')->orderByDesc('id')->get() as $m) {
                $isDone = in_array($m->status, ['completed', 'cancelled'], true);
                $sched = trim(($m->scheduled_date ?? '') . ' ' . ($m->scheduled_time ?? '09:00'));

                $onuId = null;
                $onuSn = null;
                if (!empty($m->device_name)) {
                    $foundOnu = Onu::withoutGlobalScopes()
                        ->where('serial_number', $m->device_name)
                        ->orWhere('code', $m->device_name)
                        ->first();
                    if ($foundOnu) {
                        $onuId = (int) $foundOnu->id;
                        $onuSn = $foundOnu->serial_number;
                    }
                }

                $items->push((object) [
                    'source' => 'maintenance',
                    'id' => (int) $m->id,
                    'code' => 'MT-' . $m->id,
                    'title' => $m->title ?? 'Pemeliharaan Jaringan',
                    'target_name' => $m->device_name ?: ('Perangkat ' . strtoupper((string) ($m->device_type ?? 'Jaringan'))),
                    'type_label' => strtoupper((string) ($m->type ?? 'PREVENTIVE')),
                    'priority' => $m->priority ?? 'medium',
                    'status' => $m->status ?? 'scheduled',
                    'is_completed' => $isDone,
                    'is_locked' => $m->status === 'cancelled',
                    'location' => $m->location_name ?: 'Lokasi Infrastruktur',
                    'description' => $m->notes,
                    'resolution_notes' => $m->resolution_notes ?? null,
                    'date' => $sched ?: $m->created_at,
                    'completed_at' => $m->completed_at ?? null,
                    'onu_id' => $onuId,
                    'onu_sn' => $onuSn,
                ]);
            }
        }

        $baseTickets = Ticket::withoutGlobalScopes()
            ->where('category', 'maintenance')
            ->when(!$isAdmin, fn ($q) => $q->where('assigned_to', $uid));

        $activeCount += (clone $baseTickets)->whereIn('status', ['open', 'in_progress', 'pending_customer'])->count();
        $historyCount += (clone $baseTickets)->whereIn('status', ['resolved', 'closed'])->count();

        $tq = (clone $baseTickets)->with(['customer' => fn ($q) => $q->withoutGlobalScopes()]);
        if ($this->activeTab === 'active') {
            $tq->whereIn('status', ['open', 'in_progress', 'pending_customer']);
        } else {
            $tq->whereIn('status', ['resolved', 'closed']);
        }

        if ($search !== '') {
            $tq->where(function ($sub) use ($search) {
                $sub->where('title', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%")
                    ->orWhereHas('customer', function ($cq) use ($search) {
                        $cq->withoutGlobalScopes()->where('name', 'like', "%{$search}%")
                            ->orWhere('address', 'like', "%{$search}%");
                    });
            });
        }

        foreach ($tq->latest('created_at')->get() as $t) {
            $isDone = in_array($t->status, ['resolved', 'closed'], true);
            $code = !empty($t->uuid)
                ? ('TKT-' . strtoupper(substr(str_replace('-', '', $t->uuid), 0, 6)))
                : ('TKT-' . $t->id);

            // Periksa ketersediaan ONU pada pelanggan tiket
            $onuId = null;
            $onuSn = null;
            if ($t->customer_id) {
                $cs = CustomerService::withoutGlobalScopes()
                    ->with('onu')
                    ->where('customer_id', $t->customer_id)
                    ->whereNotNull('onu_id')
                    ->first();
                if ($cs && $cs->onu_id) {
                    $onuId = (int) $cs->onu_id;
                    $onuSn = $cs->onu?->serial_number ?? null;
                }
            }

            $items->push((object) [
                'source' => 'ticket',
                'id' => (int) $t->id,
                'code' => $code,
                'title' => $t->title,
                'target_name' => $t->customer?->name ?? 'Jaringan Pelanggan',
                'type_label' => 'TIKET MAINTENANCE',
                'priority' => $t->priority ?? 'medium',
                'status' => $t->status,
                'is_completed' => $isDone,
                'is_locked' => $t->status === 'closed',
                'location' => $t->customer?->address ?? 'Lokasi ODC / ODP / Pelanggan',
                'description' => $t->description,
                'resolution_notes' => $t->resolution_notes ?? null,
                'date' => $t->created_at,
                'completed_at' => $t->resolved_at,
                'onu_id' => $onuId,
                'onu_sn' => $onuSn,
            ]);
        }

        $sorted = $items->sortByDesc(fn ($i) => $i->date ? \Carbon\Carbon::parse($i->date)->timestamp : 0)->values();
        $page = $this->getPage();
        $perPage = 10;

        $jobs = new LengthAwarePaginator(
            $sorted->forPage($page, $perPage),
            $sorted->count(),
            $perPage,
            $page,
            ['path' => request()->url(), 'query' => request()->query()]
        );

        return view('livewire.isp.technician.my-jobs.maintenance', array_merge(
            get_object_vars($this),
            [
                'jobs' => $jobs,
                'activeCount' => $activeCount,
                'historyCount' => $historyCount,
            ]
        ));
    }
}
