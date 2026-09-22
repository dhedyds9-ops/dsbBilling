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
     * Dapatkan semua user yang bisa menjadi Owner/Reseller di dropdown.
     * Termasuk: administrator, manager, reseller.
     * WAJIB DIKECUALIKAN: customer (role portal pelanggan).
     */
    public function getResellers(bool $activeOnly = true): Collection
    {
        return User::whereHas('roles', fn($q) => $q->whereIn('name', UserRole::backofficeRoles()))
            // Keamanan ganda: pastikan TIDAK ada customer yang masuk
            ->whereDoesntHave('roles', fn($q) => $q->where('name', UserRole::Customer->value))
            ->when($activeOnly, fn($q) => $q->where('is_active', true))
            ->orderBy('name')
            ->get();
    }

    /**
     * Dapatkan HANYA user dengan role Reseller (tidak termasuk admin/manager).
     * Gunakan untuk konteks yang memang HANYA butuh reseller murni.
     */
    public function getResellersOnly(bool $activeOnly = true): Collection
    {
        return User::whereHas('roles', fn($q) => $q->where('name', UserRole::Reseller->value))
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
    public function getEligibleApproversForDropdown(bool $activeOnly = true): array
    {
        return $this->getEligibleApprovers($activeOnly)->pluck('name', 'id')->toArray();
    }
}