<?php

namespace App\Livewire\Support\Ticket;

use App\Livewire\BaseEnterpriseList;
use App\Services\Support\TicketService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Throwable;

class Index extends BaseEnterpriseList
{
    public string $activeModule = 'support';
    public string $activePage = 'ticket';

    public array $tabs = [
        'kanban' => 'Kanban',
        'table' => 'Table',
        'timeline' => 'Timeline',
    ];

    public ?array $summary = null;
    public ?array $filterOptions = null;
    public ?array $kanbanData = null;
    public ?array $timelineData = null;
    public ?int $timelineTicketId = null;
    public ?int $bulkAssigneeId = null;
    public bool $showBulkAssign = false;
    public bool $showCreateForm = false;
    public bool $showEditModal = false;
    public array $newTicket = [];
    public array $editingTicket = [];
    public string $customerSearch = '';
    public bool $showAllStaff = true;
    public string $resolveNote = '';

    public function getAssigneeOptionsProperty(): array
    {
        return $this->ticketService->getAssigneeOptions(!$this->showAllStaff);
    }

    public function getAttendedTechniciansProperty(): array
    {
        return app(\App\Services\Auth\UserQueryService::class)->getAttendedTechniciansWithWorkload(2);
    }

    public function getResellerOptionsProperty(): array
    {
        $resellers = app(\App\Services\Auth\UserQueryService::class)->getResellers();
        $options = [];
        foreach ($resellers as $r) {
            $options[$r->id] = $r->name;
        }
        return $options;
    }

    public function getFilteredCustomersProperty(): array
    {
        $resellerId = $this->newTicket['reseller_id'] ?? null;

        if (Auth::user()?->hasRole('reseller')) {
            $resellerId = (string) (Auth::user()->getEffectiveResellerId() ?? Auth::id());
        }

        $query = \App\Models\CRM\Customer::withoutGuests()->where('status', 'active');

        if ($resellerId !== null && $resellerId !== '') {
            if ($resellerId === 'hq' || $resellerId === '0') {
                $query->whereNull('reseller_id');
            } else {
                $query->where('reseller_id', (int) $resellerId);
            }
        }

        if (!empty(trim($this->customerSearch))) {
            $q = trim($this->customerSearch);
            $query->where(function ($sq) use ($q) {
                $sq->where('name', 'like', "%{$q}%")
                    ->orWhere('phone', 'like', "%{$q}%")
                    ->orWhere('code', 'like', "%{$q}%");
            });
        }

        return $query->orderBy('name')->pluck('name', 'id')->toArray();
    }

    public function updatedNewTicketResellerId($value): void
    {
        $this->newTicket['customer_id'] = '';
        $this->customerSearch = '';
    }

    public function updatedNewTicketCustomerId($value): void
    {
        if ($value && empty($this->newTicket['reseller_id'])) {
            $cust = \App\Models\CRM\Customer::find($value);
            if ($cust) {
                $this->newTicket['reseller_id'] = $cust->reseller_id ? (string) $cust->reseller_id : 'hq';
            }
        }
    }

    protected TicketService $ticketService;

    public function boot(TicketService $ticketService): void
    {
        $this->ticketService = $ticketService;
    }

    public function mount(): void
    {
        $user = Auth::user();
        if ($user && strtoupper((string) $user->job_function) === 'TECHNICIAN' && !$user->hasRole(['administrator', 'superadmin'])) {
            $search = request()->query('search');
            $this->redirectRoute('technician.my-jobs.troubleshooting', $search ? ['search' => $search] : []);
            return;
        }

        parent::mount();
        $this->activeTab = 'kanban';

        $defaultResellerId = '';
        if (Auth::user()?->hasRole('reseller')) {
            $defaultResellerId = (string) (Auth::user()->getEffectiveResellerId() ?? Auth::id());
            $this->breadcrumbs = [
                ['label' => 'Portal Reseller', 'url' => route('reseller-portal.dashboard')],
                ['label' => 'Tiket Gangguan', 'url' => route('reseller-portal.tickets.index')],
            ];
            $this->activeModule = 'reseller-portal';
        } else {
            $this->breadcrumbs = [
                ['label' => 'Support', 'url' => '#'],
                ['label' => 'Tiket', 'url' => route('support.ticket')],
            ];
        }

        $this->filters = [
            'status' => '',
            'priority' => '',
            'reseller_id' => $defaultResellerId,
            'assigned_to' => '',
            'customer_id' => '',
            'category' => '',
            'due_from' => '',
            'due_to' => '',
            'ignored' => false,
        ];
        $this->newTicket = [
            'is_manual_customer' => false,
            'manual_type' => 'customer',
            'force_new_customer' => false,
            'duplicate_found' => false,
            'reseller_id' => $defaultResellerId,
            'customer_id' => '',
            'manual_customer_name' => '',
            'manual_customer_phone' => '',
            'manual_customer_address' => '',
            'title' => '',
            'description' => '',
            'category' => 'internet',
            'priority' => 'medium',
            'assigned_to' => '',
            'technician_ids' => [],
            'due_date' => '',
        ];
        $this->loadAll();
    }

    public function loadAll(): void
    {
        $this->summary = $this->ticketService->summary();
        $this->filterOptions = [
            'statuses' => [
                'open' => 'Open',
                'in_progress' => 'In Progress',
                'pending_customer' => 'Pending Customer',
                'resolved' => 'Resolved',
                'closed' => 'Closed',
            ],
            'priorities' => [
                'low' => 'Low',
                'medium' => 'Medium',
                'high' => 'High',
                'critical' => 'Critical',
            ],
            'resellers' => $this->resellerOptions,
            'assignees' => $this->ticketService->getAssigneeOptions(),
            'customers' => $this->ticketService->getCustomerOptions(),
            'categories' => [
                'billing' => 'Billing',
                'internet' => 'Internet',
                'installation' => 'Installation',
                'other' => 'Other',
            ],
        ];
        if ($this->activeTab === 'kanban') {
            $this->kanbanData = $this->ticketService->kanbanByStatus();
        }
    }

    public function getRowsQuery()
    {
        return $this->ticketService->list($this->filters, $this->search, $this->sortField, $this->sortDirection, 0)->getQuery();
    }

    public function getRows()
    {
        return $this->withLoading(function () {
            return $this->ticketService->list(
                $this->filters,
                $this->search,
                $this->sortField,
                $this->sortDirection,
                $this->perPage
            );
        }, 'Gagal memuat tiket');
    }

    public function handleBulkAction(string $action, array $ids): int
    {
        $userId = Auth::id() ?? 0;
        return match ($action) {
            'assign' => $this->ticketService->bulkAssign($ids, (int) $this->bulkAssigneeId, $userId),
            'resolve' => $this->ticketService->bulkResolve($ids, null, $userId),
            'close' => $this->ticketService->bulkClose($ids, $userId),
            'delete' => $this->ticketService->bulkDelete($ids),
            'export' => $this->doBulkExport($ids),
            default => 0,
        };
    }

    public function applyBulk(string $action): void
    {
        if ($action === 'assign') {
            if (count($this->selected) === 0) {
                $this->errorMessage = 'Pilih data terlebih dahulu.';
                return;
            }
            $this->showBulkAssign = true;
            return;
        }
        parent::applyBulk($action);
    }

    public function submitBulkAssign(): void
    {
        $userId = Auth::id() ?? 0;
        if (!$this->bulkAssigneeId) {
            $this->errorMessage = 'Pilih petugas.';
            return;
        }
        $count = $this->ticketService->bulkAssign($this->selected, (int) $this->bulkAssigneeId, $userId);
        session()->flash('success', "Berhasil assign {$count} tiket.");
        $this->showBulkAssign = false;
        $this->bulkAssigneeId = null;
        $this->selected = [];
        $this->loadAll();
    }

    public function cancelBulkAssign(): void
    {
        $this->showBulkAssign = false;
        $this->bulkAssigneeId = null;
    }

    protected function doBulkExport(array $ids): int
    {
        $rows = $this->ticketService->list($this->filters, $this->search, $this->sortField, $this->sortDirection, 0);
        if ($rows instanceof \Illuminate\Pagination\LengthAwarePaginator) {
            $rows = $rows->getCollection();
        }
        $filtered = $rows->whereIn('id', $ids)->all();
        $response = $this->ticketService->exportCsv($filtered);
        $response->send();
        exit;
        return count($ids);
    }

    public function exportCsv(): StreamedResponse|BinaryFileResponse|\Illuminate\Http\RedirectResponse
    {
        $rows = $this->ticketService->list($this->filters, $this->search, $this->sortField, $this->sortDirection, 0);
        if ($rows instanceof \Illuminate\Pagination\LengthAwarePaginator) {
            $rows = $rows->getCollection()->all();
        }
        return $this->ticketService->exportCsv($rows);
    }

    public function setActiveTab(string $tab): void
    {
        if (isset($this->tabs[$tab])) {
            $this->activeTab = $tab;
            if ($tab === 'kanban') {
                $this->kanbanData = $this->ticketService->kanbanByStatus();
            }
        }
    }

    public function getToolbarActions(): array
    {
        return [
            ['label' => 'Ticket Baru', 'icon' => 'play', 'action' => 'openCreateForm()'],
            ['label' => 'Export', 'icon' => 'download', 'action' => 'exportCsv()'],
            ['label' => 'Bulk Assign', 'icon' => 'users', 'action' => "applyBulk('assign')"],
        ];
    }

    public function getBulkActions(): array
    {
        return [
            ['key' => 'assign', 'label' => 'Assign'],
            ['key' => 'resolve', 'label' => 'Resolve'],
            ['key' => 'close', 'label' => 'Close'],
            ['key' => 'export', 'label' => 'Export'],
            ['key' => 'delete', 'label' => 'Delete', 'variant' => 'bg-red-600 text-white hover:bg-red-700'],
        ];
    }

    public function getFilterConfig(): array
    {
        $opts = $this->filterOptions ?? [];
        $resellerFilterOptions = ['' => 'Semua Reseller', 'hq' => 'Kantor Pusat (HQ)'];
        foreach (($opts['resellers'] ?? []) as $k => $v) {
            $resellerFilterOptions[$k] = $v;
        }

        return [
            ['key' => 'status', 'label' => 'Status', 'type' => 'select', 'options' => $opts['statuses'] ?? []],
            ['key' => 'priority', 'label' => 'Priority', 'type' => 'select', 'options' => $opts['priorities'] ?? []],
            ['key' => 'reseller_id', 'label' => 'Reseller / Mitra', 'type' => 'select', 'options' => $resellerFilterOptions],
            ['key' => 'assigned_to', 'label' => 'Assignee', 'type' => 'select', 'options' => $opts['assignees'] ?? []],
            ['key' => 'customer_id', 'label' => 'Customer', 'type' => 'select', 'options' => $opts['customers'] ?? []],
            ['key' => 'category', 'label' => 'Kategori', 'type' => 'select', 'options' => $opts['categories'] ?? []],
            ['key' => 'due_from', 'label' => 'Due Date (Awal)', 'type' => 'date'],
            ['key' => 'due_to', 'label' => 'Due Date (Akhir)', 'type' => 'date'],
            ['key' => 'ignored', 'label' => 'Tampilkan Hanya Tiket Terabaikan (>24 jam)', 'type' => 'boolean'],
        ];
    }

    public function getSummaryItems(): array
    {
        $s = $this->summary ?? [];
        return [
            ['label' => 'Open', 'value' => $s['open'] ?? 0, 'color' => 'blue', 'icon' => 'ticket'],
            ['label' => 'In Progress', 'value' => $s['in_progress'] ?? 0, 'color' => 'amber', 'icon' => 'activity'],
            ['label' => 'Pending Customer', 'value' => $s['pending_customer'] ?? 0, 'color' => 'purple', 'icon' => 'clock'],
            ['label' => 'Resolved', 'value' => $s['resolved'] ?? 0, 'color' => 'green', 'icon' => 'check-circle'],
            ['label' => 'Closed', 'value' => $s['closed'] ?? 0, 'color' => 'slate', 'icon' => 'file-text'],
        ];
    }

    public function openCreateForm(): void
    {
        $user = Auth::user();
        if ($user && ($user->hasRole(['customer_service', 'cs', 'admin_gudang', 'kasir', 'technician']) || strtoupper((string) ($user->job_function ?? '')) === 'CS')) {
            $hasCheckedIn = \App\Models\Workforce\Attendance::where('technician_id', $user->id)
                ->whereDate('date', \Carbon\Carbon::today())
                ->whereNotNull('checked_in_at')
                ->exists();
            if (! $hasCheckedIn && ! $user->isSuperAdmin()) {
                $this->dispatch('open-attendance-prompt');
                $this->dispatch('toast', [
                    'type' => 'warning',
                    'message' => "Halo {$user->name}, Anda belum melakukan Absen Hadir hari ini. Silakan klik tombol Absen Masuk untuk memulai shift dan melayani transaksi.",
                ]);
                return;
            }
        }

        $this->resetErrorBag();
        $this->customerSearch = '';
        $this->showAllStaff = true;

        $defaultResellerId = '';
        if (Auth::user()?->hasRole('reseller')) {
            $defaultResellerId = (string) (Auth::user()->getEffectiveResellerId() ?? Auth::id());
        }

        $this->newTicket = [
            'is_manual_customer' => false,
            'manual_type' => 'customer',
            'force_new_customer' => false,
            'duplicate_found' => false,
            'reseller_id' => $defaultResellerId,
            'customer_id' => '',
            'manual_customer_name' => '',
            'manual_customer_phone' => '',
            'manual_customer_address' => '',
            'title' => '',
            'description' => '',
            'category' => 'internet',
            'priority' => 'medium',
            'assigned_to' => '',
            'technician_ids' => [],
            'due_date' => '',
        ];
        $this->showCreateForm = true;
    }

    public function closeCreateForm(): void
    {
        $this->showCreateForm = false;
        $this->resetErrorBag();
    }

    public function submitCreate(): void
    {
        $user = Auth::user();
        if ($user && ($user->hasRole(['customer_service', 'cs', 'admin_gudang', 'kasir', 'technician']) || strtoupper((string) ($user->job_function ?? '')) === 'CS')) {
            $hasCheckedIn = \App\Models\Workforce\Attendance::where('technician_id', $user->id)
                ->whereDate('date', \Carbon\Carbon::today())
                ->whereNotNull('checked_in_at')
                ->exists();
            if (! $hasCheckedIn && ! $user->isSuperAdmin()) {
                $this->dispatch('open-attendance-prompt');
                $this->dispatch('toast', [
                    'type' => 'warning',
                    'message' => "Halo {$user->name}, Anda belum melakukan Absen Hadir hari ini. Silakan klik tombol Absen Masuk untuk memulai shift dan melayani transaksi.",
                ]);
                return;
            }
        }

        $userId = Auth::id() ?? 0;
        $this->resetErrorBag();

        try {
            $data = $this->newTicket;

            // Validasi Title
            if (empty(trim($data['title'] ?? ''))) {
                $this->addError('newTicket.title', 'Judul tiket / keluhan wajib diisi.');
                return;
            }

            $resellerId = null;
            if (Auth::user()?->hasRole('reseller')) {
                $resellerId = (int) (Auth::user()->getEffectiveResellerId() ?? Auth::id());
            } elseif (!empty($data['reseller_id']) && $data['reseller_id'] !== 'hq') {
                $resellerId = (int) $data['reseller_id'];
            }

            $category = $data['category'] ?? 'internet';
            $isPsb = in_array($category, ['installation', 'psb'], true);
            $psbInfo = null;

            // Validasi Customer
            if (!empty($data['is_manual_customer'])) {
                $manualName = trim((string) ($data['manual_customer_name'] ?? ''));
                if ($manualName === '') {
                    $this->addError('newTicket.manual_customer_name', 'Nama pelapor / objek wajib diisi.');
                    return;
                }

                $rawPhone = trim((string) ($data['manual_customer_phone'] ?? ''));
                $cleanPhone = preg_replace('/[^0-9]/', '', $rawPhone);
                if (empty($cleanPhone)) {
                    $cleanPhone = $rawPhone !== '' ? $rawPhone : '-';
                }
                $rawAddress = trim((string) ($data['manual_customer_address'] ?? ''));
                $cleanAddress = $rawAddress !== '' ? $rawAddress : '-';
                $manualType = $data['manual_type'] ?? 'customer';

                if ($manualType === 'network') {
                    // Objek jaringan (ODP/Jalur/ODC): tiket tanpa customer_id
                    $data['customer_id'] = null;
                    $objInfo = '[Objek Jaringan: ' . $manualName . ']'
                        . ($cleanAddress !== '-' ? ' Lokasi: ' . $cleanAddress . '.' : '')
                        . ($cleanPhone !== '-' ? ' PIC: ' . $cleanPhone . '.' : '');
                    $data['description'] = trim($objInfo . "\n" . ($data['description'] ?? ''));
                } elseif ($isPsb) {
                    // Calon pelanggan baru (PSB): akun pelanggan resmi dibuat di Wizard saat pemasangan
                    $data['customer_id'] = null;
                    $psbInfo = ['name' => $manualName, 'phone' => $cleanPhone, 'address' => $cleanAddress];
                    $data['description'] = trim(
                        '[Calon Pelanggan: ' . $manualName . ' | Telp: ' . $cleanPhone . ' | Alamat: ' . $cleanAddress . "]\n"
                        . ($data['description'] ?? '')
                    );
                } else {
                    // Pelanggan lama yang belum tercatat: daftarkan sebagai pelanggan resmi dsBilling
                    $dup = $this->findSimilarCustomer($manualName, $cleanPhone);
                    if ($dup && empty($data['force_new_customer'])) {
                        $this->newTicket['duplicate_found'] = true;
                        $this->addError('create_error', 'Ditemukan data pelanggan yang mirip: ' . $dup->name
                            . ' (' . ($dup->code ?: '-') . ', ' . ($dup->phone ?: '-') . '). '
                            . 'Gunakan tab "Pelanggan Terdaftar", atau centang opsi "Tetap daftarkan sebagai pelanggan baru".');
                        return;
                    }

                    $newCustomer = \App\Models\CRM\Customer::create([
                        'name' => $manualName,
                        'phone' => $cleanPhone,
                        'address' => $cleanAddress,
                        'reseller_id' => $resellerId,
                        'status' => 'active',
                        'created_by' => $userId,
                    ]);
                    $data['customer_id'] = $newCustomer->id;
                }
            } else {
                if (empty($data['customer_id'])) {
                    $this->addError('newTicket.customer_id', 'Silakan pilih pelanggan dari daftar, atau aktifkan opsi Belum Terdaftar (Input Manual).');
                    return;
                }

                // Jika reseller_id belum dipilih, warisi dari reseller customer yang dipilih
                if ($resellerId === null && empty($data['reseller_id'])) {
                    $selectedCustomer = \App\Models\CRM\Customer::find($data['customer_id']);
                    $resellerId = $selectedCustomer?->reseller_id;
                }
            }

            // Validasi & Pembagian Teknisi (Multi-Technician & Kuota Max 2)
            $selectedTechIds = array_values(array_filter(array_map('intval', (array) ($data['technician_ids'] ?? []))));
            if (empty($selectedTechIds) && !empty($data['assigned_to'])) {
                $selectedTechIds = [(int) $data['assigned_to']];
            }

            if (!empty($selectedTechIds)) {
                $attendedTechs = $this->attendedTechnicians;
                foreach ($selectedTechIds as $tId) {
                    $techData = $attendedTechs[$tId] ?? null;
                    if (!$techData) {
                        $this->addError('newTicket.technician_ids', 'Teknisi terpilih belum absen hadir hari ini.');
                        return;
                    }
                    if (!empty($techData['is_full'])) {
                        $this->addError('newTicket.technician_ids', "Teknisi {$techData['name']} telah mencapai batas maksimal 2 tugas aktif. Selesaikan tugas sebelumnya terlebih dahulu.");
                        return;
                    }
                }
            }

            $primaryTechId = !empty($selectedTechIds) ? $selectedTechIds[0] : null;
            $data['assigned_to'] = $primaryTechId;
            $data['technician_ids'] = !empty($selectedTechIds) ? $selectedTechIds : null;
            $data['reseller_id'] = $resellerId;
            $ticket = $this->ticketService->create($data, $userId);

            // Tiket PSB mengacu ke SPK Instalasi (Work Order PSB terintegrasi)
            if ($isPsb && $ticket) {
                $this->createInstallationForTicket($ticket, $psbInfo, $data, $userId);
            }

            session()->flash('success', 'Tiket berhasil dibuat' . ($isPsb ? ' dan SPK Instalasi baru telah diterbitkan.' : '.'));
            $this->showCreateForm = false;
            $this->loadAll();
        } catch (\Throwable $e) {
            $this->addError('create_error', 'Gagal membuat tiket: ' . $e->getMessage());
        }
    }

    /** Cari pelanggan non-guest yang mirip (telepon 8 digit terakhir atau nama persis). */
    protected function findSimilarCustomer(string $name, string $phone): ?\App\Models\CRM\Customer
    {
        $digits = preg_replace('/[^0-9]/', '', $phone);
        $q = \App\Models\CRM\Customer::query()
            ->where(function ($g) {
                $g->whereNull('code')->orWhere('code', 'not like', 'GST-%');
            })
            ->where(function ($w) use ($name, $digits) {
                $w->whereRaw('LOWER(name) = ?', [mb_strtolower($name)]);
                if (strlen($digits) >= 8) {
                    $w->orWhere('phone', 'like', '%' . substr($digits, -8));
                }
            });
        return $q->first();
    }

    /** Buat SPK Instalasi dari tiket PSB lalu tautkan lewat tickets.installation_id. */
    protected function createInstallationForTicket(\App\Models\Support\Ticket $ticket, ?array $psbInfo, array $data, int $userId): void
    {
        try {
            $customer = $ticket->customer_id ? \App\Models\CRM\Customer::find($ticket->customer_id) : null;
            $wo = new \App\Models\CRM\Installation();
            $wo->customer_id = $customer?->id;
            $wo->customer_name = $customer?->name ?? ($psbInfo['name'] ?? 'Calon Pelanggan');
            $wo->technician_id = $ticket->assigned_to ?: null;
            $wo->assigned_to = $ticket->assigned_to ?: null;
            $wo->technician_ids = $ticket->technician_ids;
            $wo->priority = $ticket->priority ?: 'medium';
            $wo->status = $ticket->assigned_to ? 'scheduled' : 'pending';
            $wo->scheduled_date = !empty($data['due_date']) ? $data['due_date'] : now()->addDay()->toDateString();
            $wo->scheduled_time = '09:00';
            $wo->notes = trim('Dari Tiket #' . $ticket->id . ': ' . $ticket->title
                . ($psbInfo ? "\nTelp: " . $psbInfo['phone'] . ' | Alamat: ' . $psbInfo['address'] : '')
                . ($ticket->description ? "\n" . $ticket->description : ''));
            $wo->created_by = $userId ?: 0;
            $wo->save();

            if (\Illuminate\Support\Facades\Schema::hasColumn('tickets', 'installation_id')) {
                $ticket->installation_id = $wo->id;
                $ticket->save();
            }

            if (!empty($wo->technician_id)) {
                try {
                    app(\App\Services\CRM\InstallationTechnicianNotifier::class)->notifyAssigned($wo, 'created');
                } catch (\Throwable $ne) {
                    Log::warning('[Ticket->Installation] Gagal notif teknisi: ' . $ne->getMessage());
                }
            }
        } catch (\Throwable $e) {
            Log::warning('[Ticket] Gagal membuat SPK Instalasi dari tiket #' . $ticket->id . ': ' . $e->getMessage());
        }
    }

    public function rowDetail(int $id): void
    {
        $this->timelineTicketId = $id;
        $this->timelineData = $this->ticketService->timeline($id);
        $this->activeTab = 'timeline';
    }

    public function rowEdit(int $id): void
    {
        $this->resetErrorBag();
        $ticket = \App\Models\Support\Ticket::with(['customer', 'assignedTo', 'reseller'])->find($id);
        if (!$ticket) {
            $this->errorMessage = 'Tiket tidak ditemukan.';
            return;
        }

        $effectiveReseller = $ticket->reseller_id ? (string)$ticket->reseller_id : ($ticket->customer?->reseller_id ? (string)$ticket->customer->reseller_id : 'hq');

        $this->editingTicket = [
            'id' => $ticket->id,
            'customer_name' => $ticket->customer?->name ?? 'Pelanggan',
            'reseller_id' => $effectiveReseller,
            'title' => $ticket->title,
            'description' => $ticket->description ?? '',
            'category' => $ticket->category ?? 'internet',
            'priority' => $ticket->priority ?? 'medium',
            'status' => $ticket->status ?? 'open',
            'assigned_to' => $ticket->assigned_to ? (string) $ticket->assigned_to : '',
            'technician_ids' => $ticket->technician_ids ?? ($ticket->assigned_to ? [$ticket->assigned_to] : []),
            'due_date' => $ticket->due_date ? (is_string($ticket->due_date) ? substr($ticket->due_date, 0, 10) : $ticket->due_date->format('Y-m-d')) : '',
            'note' => $ticket->resolution_notes ?? '',
        ];
        $this->showAllStaff = true;
        $this->showEditModal = true;
    }

    public function closeEditModal(): void
    {
        $this->showEditModal = false;
        $this->editingTicket = [];
        $this->resetErrorBag();
    }

    public function submitEdit(): void
    {
        $this->resetErrorBag();
        if (empty(trim($this->editingTicket['title'] ?? ''))) {
            $this->addError('editingTicket.title', 'Judul tiket wajib diisi.');
            return;
        }

        $ticketId = (int) ($this->editingTicket['id'] ?? 0);
        $ticket = \App\Models\Support\Ticket::with(['customer', 'assignedTo'])->find($ticketId);
        if (!$ticket) {
            $this->addError('edit_error', 'Tiket tidak ditemukan.');
            return;
        }

        $userId = Auth::id() ?? 0;
        $oldStatus = $ticket->status;
        $oldAssignee = $ticket->assigned_to;

        try {
            $editTechIds = array_values(array_filter(array_map('intval', (array) ($this->editingTicket['technician_ids'] ?? []))));
            if (empty($editTechIds) && !empty($this->editingTicket['assigned_to'])) {
                $editTechIds = [(int) $this->editingTicket['assigned_to']];
            }
            if (!empty($editTechIds)) {
                $attendedTechs = $this->attendedTechnicians;
                foreach ($editTechIds as $tId) {
                    $techData = $attendedTechs[$tId] ?? null;
                    if (!$techData) {
                        $this->addError('editingTicket.technician_ids', 'Teknisi terpilih belum absen hadir hari ini.');
                        return;
                    }
                    $alreadyAssigned = in_array($tId, (array) ($ticket->technician_ids ?? [])) || (int) $ticket->assigned_to === (int) $tId;
                    if (!$alreadyAssigned && !empty($techData['is_full'])) {
                        $this->addError('editingTicket.technician_ids', "Teknisi {$techData['name']} telah mencapai batas maksimal 2 tugas aktif.");
                        return;
                    }
                }
            }

            $newAssignee = !empty($editTechIds) ? $editTechIds[0] : null;
            $newStatus = $this->editingTicket['status'] ?? $ticket->status;
            $newResellerId = null;
            if (!empty($this->editingTicket['reseller_id']) && $this->editingTicket['reseller_id'] !== 'hq') {
                $newResellerId = (int) $this->editingTicket['reseller_id'];
            }

            $updatePayload = [
                'title' => trim($this->editingTicket['title']),
                'description' => $this->editingTicket['description'] ?? '',
                'category' => $this->editingTicket['category'] ?? 'internet',
                'priority' => $this->editingTicket['priority'] ?? 'medium',
                'status' => $newStatus,
                'assigned_to' => $newAssignee,
                'technician_ids' => !empty($editTechIds) ? $editTechIds : null,
                'reseller_id' => $newResellerId,
                'resolved_at' => ($newStatus === 'resolved' && $oldStatus !== 'resolved') ? now() : $ticket->resolved_at,
                'resolved_by' => ($newStatus === 'resolved' && $oldStatus !== 'resolved') ? $userId : $ticket->resolved_by,
            ];
            if (\Illuminate\Support\Facades\Schema::hasColumn('tickets', 'due_date')) {
                $updatePayload['due_date'] = !empty($this->editingTicket['due_date']) ? $this->editingTicket['due_date'] : null;
            }
            if (\Illuminate\Support\Facades\Schema::hasColumn('tickets', 'resolution_notes') && isset($this->editingTicket['note']) && trim((string) $this->editingTicket['note']) !== '') {
                $updatePayload['resolution_notes'] = trim((string) $this->editingTicket['note']);
            }
            $ticket->update($updatePayload);

            $isAssigneeChanged = ($newAssignee && $newAssignee != $oldAssignee);
            $isResolvedChanged = ($newStatus === 'resolved' && $oldStatus !== 'resolved');

            // Dispatch event jika status berubah menjadi resolved (akan dikirimkan notif TIKET SELESAI)
            if ($isResolvedChanged) {
                \Illuminate\Support\Facades\Event::dispatch(\Src\Domain\Support\Events\TicketResolvedEvent::create(
                    (string) $ticket->id,
                    (string) $userId,
                    $this->editingTicket['note'] ?? null,
                ));
            } elseif ($isAssigneeChanged) {
                // Dispatch event jika petugas / teknisi ditugaskan atau diganti
                \Illuminate\Support\Facades\Event::dispatch(\Src\Domain\Support\Events\TicketAssignedEvent::create(
                    (string) $ticket->id,
                    (string) $newAssignee,
                    (string) $userId,
                ));
            } else {
                // Hanya kirim notifikasi edit umum jika BUKAN resolve dan BUKAN ganti assignee
                // (Mencegah pesan terkirim ganda 2x ke grup WhatsApp & Telegram)
                try {
                    $waConn = \App\Models\Setting::getValue('whatsapp.connection', []);
                    $ticketGroupId = trim((string)($waConn['ticket_group_id'] ?? ''));
                    if ($ticketGroupId !== '') {
                        $template = app(\App\Services\Notifications\WhatsApp\WaTemplateEngine::class);
                        $code = !empty($ticket->uuid) ? ('TKT-' . strtoupper(substr(str_replace('-', '', $ticket->uuid), 0, 6))) : ('TKT-' . $ticket->id);
                        $groupText = $template->ticketUpdated([
                            'ticket_code'      => $code,
                            'ticket_id'        => $ticket->id,
                            'title'            => $ticket->title,
                            'category'         => $ticket->category ?? 'internet',
                            'customer_name'    => $ticket->customer?->name ?? 'Pelanggan',
                            'customer_address' => $ticket->customer?->address ?? '-',
                            'status'           => $ticket->status,
                            'priority'         => $ticket->priority,
                            'technician_name'  => $ticket->fresh()->assignedTo?->name ?? 'Belum Ditugaskan',
                            'updated_by'       => Auth::user()?->name ?? 'Staff',
                            'note'             => $this->editingTicket['note'] ?? '',
                        ]);

                        \App\Services\Notifications\WhatsApp\WhatsAppDirectSender::send($ticketGroupId, $groupText);
                    }
                } catch (\Throwable $we) {
                    \Illuminate\Support\Facades\Log::warning('[TicketEdit] Gagal kirim WA grup: ' . $we->getMessage());
                }

                // Kirim Notifikasi Edit ke Telegram Bot
                try {
                    $telegram = app(\App\Services\Telegram\TelegramService::class);
                    if ($telegram->isEnabled()) {
                        $code = !empty($ticket->uuid) ? ('TKT-' . strtoupper(substr(str_replace('-', '', $ticket->uuid), 0, 6))) : ('TKT-' . $ticket->id);
                        $telegram->sendTicketNotification([
                            'ticket_code'      => $code,
                            'ticket_id'        => $ticket->id,
                            'title'            => $ticket->title,
                            'customer_name'    => $ticket->customer?->name ?? 'Pelanggan',
                            'customer_address' => $ticket->customer?->address ?? '-',
                            'category'         => $ticket->category ?? 'internet',
                            'priority'         => $ticket->priority ?? 'medium',
                            'status'           => $ticket->status,
                            'technician_name'  => $ticket->fresh()->assignedTo?->name ?? 'Belum Ditugaskan',
                            'updated_by'       => Auth::user()?->name ?? 'Staff',
                            'note'             => $this->editingTicket['note'] ?? '',
                        ], 'updated');
                    }
                } catch (\Throwable $te) {
                    \Illuminate\Support\Facades\Log::warning('[TicketEdit] Gagal kirim Telegram: ' . $te->getMessage());
                }
            }

            session()->flash('success', "Tiket #{$ticket->id} berhasil diperbarui.");
            $this->showEditModal = false;
            $this->editingTicket = [];
            $this->loadAll();
        } catch (\Throwable $e) {
            $this->addError('edit_error', 'Gagal memperbarui tiket: ' . $e->getMessage());
        }
    }

    public function rowAssign(int $id): void
    {
        $this->selected = [(string) $id];
        $this->showBulkAssign = true;
    }

    public function rowResolve(int $id): void
    {
        $userId = Auth::id() ?? 0;
        try {
            $this->ticketService->resolve($id, null, $userId);
            session()->flash('success', 'Tiket di-resolve.');
            $this->loadAll();
        } catch (Throwable $e) {
            $this->errorMessage = 'Gagal resolve: ' . $e->getMessage();
        }
    }

    public function rowClose(int $id): void
    {
        $this->confirmTitle = 'Close Ticket';
        $this->confirmMessage = "Anda akan menutup Ticket ID #{$id}. Lanjutkan?";
        $this->confirmAction = 'close-ticket';
        $this->confirmParams = ['id' => $id];
        $this->confirmBtnText = 'Close';
        $this->confirmBtnClass = 'bg-slate-700 hover:bg-slate-800 text-white';
        $this->dispatch('open-modal', name: 'confirm');
    }

    public function rowDelete(int $id): void
    {
        $this->confirmTitle = 'Hapus Ticket';
        $this->confirmMessage = "Anda akan menghapus Ticket ID #{$id}. Lanjutkan?";
        $this->confirmAction = 'delete-ticket';
        $this->confirmParams = ['id' => $id];
        $this->confirmBtnText = 'Hapus';
        $this->confirmBtnClass = 'bg-red-600 hover:bg-red-700 text-white';
        $this->dispatch('open-modal', name: 'confirm');
    }

    public function handleConfirm(): void
    {
        $userId = Auth::id() ?? 0;
        if ($this->confirmAction === 'close-ticket') {
            try {
                $this->ticketService->close((int) ($this->confirmParams['id'] ?? 0), $userId);
                session()->flash('success', 'Tiket ditutup.');
                $this->loadAll();
            } catch (Throwable $e) {
                $this->errorMessage = 'Gagal close: ' . $e->getMessage();
            }
            $this->dispatch('close-modal', name: 'confirm');
            $this->confirmAction = '';
            $this->confirmParams = [];
            return;
        }
        if ($this->confirmAction === 'delete-ticket') {
            try {
                $ticket = \App\Models\Support\Ticket::findOrFail((int) ($this->confirmParams['id'] ?? 0));
                $this->ticketService->delete($ticket);
                session()->flash('success', 'Tiket dihapus.');
                $this->loadAll();
            } catch (Throwable $e) {
                $this->errorMessage = 'Gagal delete: ' . $e->getMessage();
            }
            $this->dispatch('close-modal', name: 'confirm');
            $this->confirmAction = '';
            $this->confirmParams = [];
            return;
        }
        parent::handleConfirm();
    }

    public function updatedActiveTab(string $value = ''): void
    {
        parent::updatedActiveTab($value);
        $this->loadAll();
    }

    public function render()
    {
        $rows = $this->getRows();
        return view('livewire.support.ticket.index', compact('rows'));
    }
}
