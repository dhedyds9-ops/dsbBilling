<?php

namespace App\Services\Auth;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;

/**
 * UserQueryService â€” Centralized service untuk query user berdasarkan peran bisnis.
 *
 * ATURAN WAJIB:
 *  - Jangan pernah menggunakan User::all() untuk menentukan seller/reseller/financial actor
 *  - Jangan menggunakan fallback User::all() jika result kosong
 *  - Setiap method harus memiliki semantik yang jelas
 *
 * @see App\Enums\UserRole untuk definisi role yang valid
 */
class UserQueryService
{
    // =============================================
    // BACKOFFICE USER QUERIES
    // =============================================

    /**
     * Dapatkan semua user dengan role Administrator.
     * Gunakan untuk: dropdown admin, audit log, assignment tasks sistem.
     */
    public function getAdministrators(bool $activeOnly = true): Collection
    {
        return User::whereHas('roles', fn($q) => $q->where('name', UserRole::Administrator->value))
            ->when($activeOnly, fn($q) => $q->where('is_active', true))
            ->orderBy('name')
            ->get();
    }

    /**
     * Dapatkan semua user dengan role Manager (staff internal ISP).
     * Gunakan untuk: dropdown assignee ticket, technician, supervisor task.
     * Manager BUKAN financial actor secara default.
     */
    public function getManagers(bool $activeOnly = true): Collection
    {
        return User::whereHas('roles', fn($q) => $q->where('name', UserRole::Manager->value))
            ->when($activeOnly, fn($q) => $q->where('is_active', true))
            ->orderBy('name')
            ->get();
    }

    /**
     * Dapatkan semua user dengan role RESELLER untuk dropdown Owner/Reseller.
     * HANYA role 'reseller' - administrator dan manager TIDAK boleh tampil di sini.
     * WAJIB DIKECUALIKAN: customer (role portal pelanggan).
     */
    public function getResellers(bool $activeOnly = true): Collection
    {
        return User::whereHas('roles', fn($q) => $q->where('name', UserRole::Reseller->value))
            // Keamanan ganda: pastikan TIDAK ada customer yang masuk
            ->whereDoesntHave('roles', fn($q) => $q->where('name', UserRole::Customer->value))
            ->when($activeOnly, fn($q) => $q->where('is_active', true))
            ->orderBy('name')
            ->get();
    }

    // =============================================
    // FINANCIAL ACTOR QUERIES
    // =============================================

    /**
     * Dapatkan eligible seller untuk transaksi.
     *
     * HANYA reseller yang menjadi eligible seller.
     * Manager/administrator TIDAK menjadi seller secara otomatis.
     *
     * Gunakan untuk: dropdown seller di voucher, PPPoE, hotspot.
     *
     * DILARANG: User::all() atau mencampurkan semua role.
     */
    public function getEligibleSellers(bool $activeOnly = true): Collection
    {
        return $this->getResellers($activeOnly);
    }

    /**
     * Dapatkan eligible reseller (alias getEligibleSellers, lebih semantik untuk konteks reseller).
     * Gunakan untuk: dropdown reseller_id di customer, invoice, laporan.
     */
    public function getEligibleResellers(bool $activeOnly = true): Collection
    {
        return $this->getResellers($activeOnly);
    }

    // =============================================
    // ASSIGNMENT QUERIES
    // =============================================

    /**
     * Dapatkan user yang dapat di-assign untuk ticket/task.
     * Termasuk Administrator, Manager, NOC, dan Teknisi Lapangan yang aktif.
     * Gunakan untuk: dropdown assignee ticket, tugas teknis, dll.
     */
    public function getEligibleAssignees(bool $activeOnly = true): Collection
    {
        return User::where(function ($q) {
                $q->whereHas('roles', fn($rq) => $rq->whereIn('name', [
                    UserRole::Administrator->value,
                    UserRole::Manager->value,
                    UserRole::Teknisi->value,
                    UserRole::Noc->value,
                    UserRole::CustomerService->value,
                    'technician',
                    'Technician',
                    'teknisi',
                    'Teknisi',
                    'noc',
                    'Noc',
                    'helpdesk',
                ]))
                ->orWhereRaw('UPPER(COALESCE(job_function, "")) IN (?, ?)', ['TECHNICIAN', 'TEKNISI'])
                ->orWhereRaw('UPPER(COALESCE(job_title, "")) LIKE ?', ['%TEKNISI%'])
                ->orWhereRaw('UPPER(COALESCE(job_title, "")) LIKE ?', ['%TECHNICIAN%'])
                ->orWhereRaw('UPPER(COALESCE(name, "")) LIKE ?', ['%TEKNISI%'])
                ->orWhereRaw('UPPER(COALESCE(name, "")) LIKE ?', ['%TECHNICIAN%']);
            })
            ->whereDoesntHave('roles', fn($q) => $q->where('name', UserRole::Customer->value))
            ->when($activeOnly, fn($q) => $q->where('is_active', true))
            ->orderBy('name')
            ->get();
    }

    /**
     * Dapatkan user yang dapat menjadi approver untuk expense/keuangan.
     * Gunakan permission 'finance.settlement' sebagai gating, bukan role.
     */
    public function getEligibleApprovers(bool $activeOnly = true): Collection
    {
        return User::whereHas('roles', fn($q) => $q->whereIn('name', [
                UserRole::Administrator->value,
                UserRole::Manager->value,
            ]))
            ->when($activeOnly, fn($q) => $q->where('is_active', true))
            ->orderBy('name')
            ->get();
    }

    // =============================================
    // PORTAL QUERIES
    // =============================================

    /**
     * Dapatkan user dengan role customer (untuk customer portal).
     * Gunakan HANYA untuk keperluan portal login â€” BUKAN untuk dropdown staff/seller.
     *
     * PENTING: Customer tidak boleh muncul di dropdown:
     *  - Reseller dropdown
     *  - Seller dropdown
     *  - Manager dropdown
     *  - Financial actor dropdown
     */
    public function getCustomerPortalUsers(bool $activeOnly = true): Collection
    {
        return User::whereHas('roles', fn($q) => $q->where('name', UserRole::Customer->value))
            ->when($activeOnly, fn($q) => $q->where('is_active', true))
            ->orderBy('name')
            ->get();
    }

    // =============================================
    // SCOPED QUERIES (berdasarkan user yang login)
    // =============================================

    /**
     * Dapatkan daftar reseller yang dapat dilihat oleh user yang login.
     *
     * Administrator: semua reseller
     * Manager: semua reseller (sesuai branch jika diperlukan)
     * Reseller: hanya diri sendiri
     */
    public function getAccessibleResellers(User $currentUser): Collection
    {
        if ($currentUser->hasRole(UserRole::Reseller->value)) {
            // Reseller hanya bisa lihat dirinya sendiri
            return collect([$currentUser]);
        }

        return $this->getResellers();
    }

    // =============================================
    // DROPDOWN FORMAT HELPERS
    // =============================================

    /**
     * Format untuk dropdown: ['id' => 'name'] map.
     * Gunakan untuk select/dropdown di Livewire/Blade.
     */
    public function getResellersForDropdown(bool $activeOnly = true): array
    {
        return $this->getResellers($activeOnly)->pluck('name', 'id')->toArray();
    }

    public function getManagersForDropdown(bool $activeOnly = true): array
    {
        return $this->getManagers($activeOnly)->pluck('name', 'id')->toArray();
    }

    public function getEligibleSellersForDropdown(bool $activeOnly = true): array
    {
        return $this->getEligibleSellers($activeOnly)->pluck('name', 'id')->toArray();
    }

    public function getEligibleAssigneesForDropdown(bool $activeOnly = true): array
    {
        return $this->getEligibleAssignees($activeOnly)->pluck('name', 'id')->toArray();
    }

    /**
     * Dapatkan daftar assignee tiket dengan filter kehadiran hari ini & jumlah tiket aktif.
     *
     * @param bool $attendedOnly Jika true, teknisi yang belum absen hari ini tidak akan dimunculkan
     *                           (Administrator tetap dimunculkan sebagai penanggung jawab pusat).
     */
    public function getEligibleAssigneesWithAttendance(bool $attendedOnly = false): array
    {
        $today = \Carbon\Carbon::today()->toDateString();
        $attendances = \App\Models\Workforce\Attendance::whereDate('date', $today)
            ->whereNotNull('checked_in_at')
            ->whereIn('status', [
                \Src\Domain\Workforce\Enums\AttendanceStatus::CHECKED_IN->value,
                \Src\Domain\Workforce\Enums\AttendanceStatus::LATE->value,
                \Src\Domain\Workforce\Enums\AttendanceStatus::CHECKED_OUT->value,
                'checked_in', 'late', 'checked_out'
            ])
            ->get(['technician_id', 'checked_in_at', 'status'])
            ->keyBy('technician_id');

        $users = $this->getEligibleAssignees(true);

        // Hitung tiket aktif per petugas
        $activeTicketCounts = \App\Models\Support\Ticket::whereIn('status', ['open', 'in_progress', 'pending_customer'])
            ->whereNotNull('assigned_to')
            ->selectRaw('assigned_to, count(*) as count')
            ->groupBy('assigned_to')
            ->pluck('count', 'assigned_to')
            ->toArray();

        // Cek apakah ada staff teknis yang sudah absen hadir hari ini
        $hasAnyAttendance = $attendances->isNotEmpty();
        // Jika filter attendedOnly aktif tetapi TIDAK ADA SATUPUN staff yang absen hari ini,
        // jangan sembunyikan semua teknisi agar tiket tetap dapat didelegasikan.
        $enforceAttendance = $attendedOnly && $hasAnyAttendance;

        $attendedList = [];
        $unattendedList = [];
        $adminList = [];

        foreach ($users as $u) {
            $isAdmin = $u->hasRole(UserRole::Administrator->value);
            $attRecord = $attendances->get($u->id);
            $hasAttended = !empty($attRecord);

            // Jika filter attendedOnly aktif dan ada data absen hari ini:
            if ($enforceAttendance && !$hasAttended && !$isAdmin) {
                continue;
            }

            $checkInTime = $hasAttended ? \Carbon\Carbon::parse($attRecord->checked_in_at)->format('H:i') : null;
            $ticketCount = $activeTicketCounts[$u->id] ?? 0;

            $label = $u->name;
            if ($hasAttended) {
                $label .= " (Hadir: {$checkInTime})";
            } elseif ($isAdmin) {
                $label .= " (Admin / Pusat)";
            } else {
                $label .= " (Belum Absen)";
            }

            if ($ticketCount > 0) {
                $label .= " [{$ticketCount} Tiket Aktif]";
            } else {
                $label .= " [Senggang]";
            }

            if ($hasAttended) {
                $attendedList[$u->id] = $label;
            } elseif ($isAdmin) {
                $adminList[$u->id] = $label;
            } else {
                $unattendedList[$u->id] = $label;
            }
        }

        // Urutan: Yang sudah hadir di paling atas, lalu teknisi lainnya, lalu admin/pusat
        return $attendedList + $unattendedList + $adminList;
    }
    public function getEligibleApproversForDropdown(bool $activeOnly = true): array
    {
        return $this->getEligibleApprovers($activeOnly)->pluck('name', 'id')->toArray();
    }

    /**
     * Dapatkan daftar teknisi yang SAH bertugas hari ini:
     * 1. HANYA akun teknisi (job_function TECHNICIAN atau role teknisi)
     * 2. HANYA yang sudah absen masuk hari ini dan belum checkout
     * 3. Menghitung beban tugas aktif (Instalasi & Tiket aktif)
     * 4. Ditandai is_full jika beban >= $maxQuota (default 2)
     */
    public function getAttendedTechniciansWithWorkload(int $maxQuota = 2): array
    {
        $today = \Carbon\Carbon::today()->toDateString();
        $attendances = \App\Models\Workforce\Attendance::whereDate('date', $today)
            ->whereNotNull('checked_in_at')
            ->whereNull('checked_out_at')
            ->whereIn('status', [
                \Src\Domain\Workforce\Enums\AttendanceStatus::CHECKED_IN->value ?? 'checked_in',
                \Src\Domain\Workforce\Enums\AttendanceStatus::LATE->value ?? 'late',
                'checked_in', 'late'
            ])
            ->get(['technician_id', 'checked_in_at', 'status'])
            ->keyBy('technician_id');

        if ($attendances->isEmpty()) {
            return [];
        }

        $technicians = User::where(function ($q) {
                $q->whereHas('roles', fn($rq) => $rq->whereIn('name', [
                    UserRole::Teknisi->value,
                    'technician',
                    'Technician',
                    'teknisi',
                    'Teknisi',
                ]))
                ->orWhereRaw('UPPER(COALESCE(job_function, "")) IN (?, ?)', ['TECHNICIAN', 'TEKNISI'])
                ->orWhereRaw('UPPER(COALESCE(job_title, "")) LIKE ?', ['%TEKNISI%'])
                ->orWhereRaw('UPPER(COALESCE(job_title, "")) LIKE ?', ['%TECHNICIAN%']);
            })
            ->whereDoesntHave('roles', fn($q) => $q->where('name', UserRole::Customer->value))
            ->where('is_active', true)
            ->whereIn('id', $attendances->keys())
            ->orderBy('name')
            ->get();

        if ($technicians->isEmpty()) {
            return [];
        }

        // Ambil semua instalasi aktif
        $hasInstTechIds = \Illuminate\Support\Facades\Schema::hasColumn('installations', 'technician_ids');
        $hasTicketTechIds = \Illuminate\Support\Facades\Schema::hasColumn('tickets', 'technician_ids');

        $instCols = ['id', 'technician_id', 'assigned_to'];
        if ($hasInstTechIds) {
            $instCols[] = 'technician_ids';
        }

        $ticketCols = ['id', 'assigned_to'];
        if ($hasTicketTechIds) {
            $ticketCols[] = 'technician_ids';
        }

        $activeInstallations = \App\Models\CRM\Installation::withoutGlobalScopes()
            ->whereIn('status', ['pending', 'scheduled', 'in_progress', 'assigned'])
            ->get($instCols);

        // Ambil semua tiket aktif
        $activeTickets = \App\Models\Support\Ticket::withoutGlobalScopes()
            ->whereIn('status', ['open', 'in_progress', 'assigned', 'pending_customer'])
            ->get($ticketCols);

        $result = [];
        foreach ($technicians as $t) {
            $att = $attendances->get($t->id);
            $checkInTime = $att ? \Carbon\Carbon::parse($att->checked_in_at)->format('H:i') : '-';

            $instCount = $activeInstallations->filter(function ($inst) use ($t, $hasInstTechIds) {
                if ((int) $inst->technician_id === (int) $t->id || (int) $inst->assigned_to === (int) $t->id) {
                    return true;
                }
                if ($hasInstTechIds && !empty($inst->technician_ids)) {
                    $ids = is_array($inst->technician_ids) ? $inst->technician_ids : json_decode($inst->technician_ids ?? '[]', true);
                    return in_array($t->id, (array) $ids);
                }
                return false;
            })->count();

            $tktCount = $activeTickets->filter(function ($tkt) use ($t, $hasTicketTechIds) {
                if ((int) $tkt->assigned_to === (int) $t->id) {
                    return true;
                }
                if ($hasTicketTechIds && !empty($tkt->technician_ids)) {
                    $ids = is_array($tkt->technician_ids) ? $tkt->technician_ids : json_decode($tkt->technician_ids ?? '[]', true);
                    return in_array($t->id, (array) $ids);
                }
                return false;
            })->count();

            $totalActive = $instCount + $tktCount;
            $isFull = $totalActive >= $maxQuota;

            $statusBadge = $isFull ? "[{$totalActive}/{$maxQuota} PENUH]" : "[{$totalActive}/{$maxQuota} Aktif]";
            $label = "{$t->name} (Hadir: {$checkInTime}) {$statusBadge}";

            $result[$t->id] = [
                'id' => $t->id,
                'name' => $t->name,
                'phone' => $t->phone ?? $t->whatsapp,
                'check_in_time' => $checkInTime,
                'active_installations' => $instCount,
                'active_tickets' => $tktCount,
                'total_active' => $totalActive,
                'max_quota' => $maxQuota,
                'is_full' => $isFull,
                'label' => $label,
            ];
        }

        return $result;
    }
}