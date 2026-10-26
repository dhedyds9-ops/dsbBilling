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
     * Hanya administrator dan manager yang aktif.
     * Gunakan untuk: dropdown assignee ticket, tugas teknis, dll.
     */
    public function getEligibleAssignees(bool $activeOnly = true): Collection
    {
        return User::whereHas('roles', fn($q) => $q->whereIn('name', [
                UserRole::Administrator->value,
                UserRole::Manager->value,
            ]))
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

        $users = User::whereHas('roles', fn($q) => $q->whereIn('name', [
                UserRole::Administrator->value,
                UserRole::Manager->value,
            ]))
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        // Hitung tiket aktif per petugas
        $activeTicketCounts = \App\Models\Support\Ticket::whereIn('status', ['open', 'in_progress', 'pending_customer'])
            ->whereNotNull('assigned_to')
            ->selectRaw('assigned_to, count(*) as count')
            ->groupBy('assigned_to')
            ->pluck('count', 'assigned_to')
            ->toArray();

        $result = [];
        foreach ($users as $u) {
            $isAdmin = $u->hasRole(UserRole::Administrator->value);
            $attRecord = $attendances->get($u->id);
            $hasAttended = !empty($attRecord);

            // Jika filter attendedOnly aktif:
            // Hanya lolos jika dia sudah absen masuk ATAU dia administrator
            if ($attendedOnly && !$hasAttended && !$isAdmin) {
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

            $result[$u->id] = $label;
        }

        return $result;
    }
    public function getEligibleApproversForDropdown(bool $activeOnly = true): array
    {
        return $this->getEligibleApprovers($activeOnly)->pluck('name', 'id')->toArray();
    }
}