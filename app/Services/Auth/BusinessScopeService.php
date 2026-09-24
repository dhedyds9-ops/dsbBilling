<?php

namespace App\Services\Auth;

use App\Enums\UserRole;
use App\Models\CRM\Customer;
use App\Models\Billing\Invoice;
use App\Models\ISP\PPPoEUser;
use App\Models\ISP\HotspotUser;
use App\Models\ISP\Voucher;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;

/**
 * BusinessScopeService — Centralized service untuk membatasi akses data berdasarkan business scope.
 *
 * PRINSIP:
 *  - Backend harus enforce scope, bukan hanya frontend hiding
 *  - Scope ditentukan oleh role + business relationship, bukan frontend input
 *  - Jangan percaya reseller_id/branch_id dari request tanpa validasi scope
 *
 * Scope rules:
 *  Administrator → semua data
 *  Manager       → semua data (atau sesuai branch jika multi-branch aktif)
 *  Reseller      → hanya data dalam scope reseller (customer miliknya)
 *  Customer      → hanya data miliknya sendiri
 */
class BusinessScopeService
{
    // =============================================
    // CUSTOMER SCOPE
    // =============================================

    /**
     * Apply scope customer berdasarkan user yang login.
     * Gunakan di semua query Customer untuk memastikan scope enforcement.
     */
    public function scopeCustomers(Builder $query, User $user): Builder
    {
        if ($user->hasRole(UserRole::Administrator->value)) {
            return $query; // Akses semua
        }

        if ($user->hasRole(UserRole::Manager->value)) {
            return $query; // Akses semua (TODO: filter by branch jika multi-branch)
        }

        if ($user->hasRole(UserRole::Reseller->value)) {
            return $query->where('reseller_id', $user->id);
        }

        // Customer hanya bisa lihat dirinya sendiri
        if ($user->hasRole(UserRole::Customer->value)) {
            return $query->where('user_id', $user->id);
        }

        // Default: tidak ada akses
        return $query->whereRaw('1=0');
    }

    /**
     * Dapatkan collection customer yang dapat diakses user.
     */
    public function getScopedCustomers(User $user, bool $activeOnly = false)
    {
        $query = Customer::query();

        if ($activeOnly) {
            $query->where('status', 'active');
        }

        return $this->scopeCustomers($query, $user)->get();
    }

    /**
     * Validasi apakah user boleh mengakses customer tertentu.
     */
    public function canAccessCustomer(User $user, Customer $customer): bool
    {
        if ($user->hasRole(UserRole::Administrator->value)) {
            return true;
        }

        if ($user->hasRole(UserRole::Manager->value)) {
            return true; // TODO: filter by branch
        }

        if ($user->hasRole(UserRole::Reseller->value)) {
            return $customer->reseller_id === $user->id;
        }

        if ($user->hasRole(UserRole::Customer->value)) {
            return $customer->user_id === $user->id;
        }

        return false;
    }

    // =============================================
    // INVOICE SCOPE
    // =============================================

    /**
     * Apply scope invoice berdasarkan user yang login.
     */
    public function scopeInvoices(Builder $query, User $user): Builder
    {
        if ($user->hasRole(UserRole::Administrator->value)) {
            return $query;
        }

        if ($user->hasRole(UserRole::Manager->value)) {
            return $query;
        }

        if ($user->hasRole(UserRole::Reseller->value)) {
            return $query->whereHas('customer', fn($q) => $q->where('reseller_id', $user->id));
        }

        if ($user->hasRole(UserRole::Customer->value)) {
            return $query->whereHas('customer', fn($q) => $q->where('user_id', $user->id));
        }

        return $query->whereRaw('1=0');
    }

    // =============================================
    // PPPoE USER SCOPE
    // =============================================

    /**
     * Apply scope PPPoE user berdasarkan user yang login.
     */
    public function scopePppoeUsers(Builder $query, User $user): Builder
    {
        if ($user->hasRole(UserRole::Administrator->value)) {
            return $query;
        }

        if ($user->hasRole(UserRole::Manager->value)) {
            return $query;
        }

        if ($user->hasRole(UserRole::Reseller->value)) {
            return $query->where('reseller_id', $user->id);
        }

        return $query->whereRaw('1=0');
    }

    // =============================================
    // HOTSPOT USER SCOPE
    // =============================================

    /**
     * Apply scope Hotspot user berdasarkan user yang login.
     */
    public function scopeHotspotUsers(Builder $query, User $user): Builder
    {
        if ($user->hasRole(UserRole::Administrator->value)) {
            return $query;
        }

        if ($user->hasRole(UserRole::Manager->value)) {
            return $query;
        }

        if ($user->hasRole(UserRole::Reseller->value)) {
            return $query->where('reseller_id', $user->id);
        }

        return $query->whereRaw('1=0');
    }

    // =============================================
    // VOUCHER SCOPE
    // =============================================

    /**
     * Apply scope Voucher berdasarkan user yang login.
     */
    public function scopeVouchers(Builder $query, User $user): Builder
    {
        if ($user->hasRole(UserRole::Administrator->value)) {
            return $query;
        }

        if ($user->hasRole(UserRole::Manager->value)) {
            return $query;
        }

        if ($user->hasRole(UserRole::Reseller->value)) {
            return $query->where('reseller_id', $user->id);
        }

        return $query->whereRaw('1=0');
    }

    // =============================================
    // RESELLER SCOPE
    // =============================================

    /**
     * Validasi bahwa reseller_id yang dikirim dari request
     * adalah reseller yang valid dalam scope user yang login.
     *
     * SECURITY: Jangan percaya reseller_id dari hidden input tanpa validasi ini.
     */
    public function validateResellerId(User $currentUser, ?int $resellerId): ?int
    {
        if ($resellerId === null) {
            // Jika reseller, default ke diri sendiri
            if ($currentUser->hasRole(UserRole::Reseller->value)) {
                return $currentUser->id;
            }
            return null;
        }

        // Administrator dan Manager boleh assign reseller manapun
        if ($currentUser->hasRole(UserRole::Administrator->value) ||
            $currentUser->hasRole(UserRole::Manager->value)) {

            // Validasi bahwa reseller_id adalah user dengan role reseller
            $resellerExists = User::where('id', $resellerId)
                ->whereHas('roles', fn($q) => $q->where('name', UserRole::Reseller->value))
                ->exists();

            return $resellerExists ? $resellerId : null;
        }

        // Reseller hanya bisa assign dirinya sendiri
        if ($currentUser->hasRole(UserRole::Reseller->value)) {
            return $currentUser->id; // Force ke diri sendiri, abaikan input
        }

        return null;
    }

    /**
     * Dapatkan reseller_id yang tepat untuk diset ke record baru.
     * Gunakan ini saat membuat PPPoE user, Hotspot user, Customer, dll.
     */
    public function resolveResellerId(User $currentUser, ?int $requestedResellerId): ?int
    {
        return $this->validateResellerId($currentUser, $requestedResellerId);
    }
}
