<?php

namespace App\Enums;

/**
 * UserRole — Single Source of Truth untuk role aplikasi dsBilling.
 *
 * PRINSIP:
 *  - ROLE ≠ PERMISSION
 *  - ROLE ≠ BRANCH
 *  - ROLE ≠ CUSTOMER
 *  - ROLE ≠ FINANCIAL ACTOR
 *
 * Backoffice roles:
 *   administrator → akses penuh sistem ISP
 *   manager       → staff internal ISP, akses berdasarkan permission
 *   reseller      → business/channel partner, financial actor
 *
 * Portal role (terpisah dari backoffice):
 *   customer      → pelanggan ISP, hanya akses customer portal
 *
 * DILARANG membuat role baru untuk:
 *   - branch/cabang → gunakan Branch entity sebagai business scope
 *   - technician → gunakan permission granular
 */
enum UserRole: string
{
    // =============================================
    // BACKOFFICE ROLES
    // =============================================

    /** Akses penuh sistem. Menggantikan legacy: super_admin, admin */
    case Administrator = 'administrator';

    /** Staff internal ISP. Akses berdasarkan permission & branch scope. */
    case Manager = 'manager';

    /** Business/channel partner. Financial actor. Scope: customer miliknya. */
    case Reseller = 'reseller';

    // =============================================
    // PORTAL ROLE (terpisah dari backoffice)
    // =============================================

    /**
     * Pelanggan ISP — hanya untuk customer portal login.
     * BUKAN staff, BUKAN reseller, BUKAN financial actor.
     * Customer TIDAK boleh muncul di dropdown staff/reseller/seller.
     */
    case Customer = 'customer';

    // =============================================
    // HELPER METHODS
    // =============================================

    /**
     * Semua valid backoffice roles (tanpa customer portal role).
     */
    public static function backofficeRoles(): array
    {
        return [
            self::Administrator->value,
            self::Manager->value,
            self::Reseller->value,
        ];
    }

    /**
     * Semua valid role values (termasuk customer portal).
     */
    public static function allValues(): array
    {
        return array_column(self::cases(), 'value');
    }

    /**
     * Role yang berhak mengakses backoffice dashboard.
     */
    public static function dashboardRoles(): array
    {
        return self::backofficeRoles();
    }

    /**
     * Role yang merupakan financial actor (dapat menjadi reseller/seller).
     * Financial actor HANYA reseller.
     * Manager dan administrator bukan financial actor secara default.
     */
    public static function financialActorRoles(): array
    {
        return [
            self::Reseller->value,
        ];
    }

    public function label(): string
    {
        return match($this) {
            self::Administrator => 'Administrator',
            self::Manager       => 'Manager',
            self::Reseller      => 'Reseller',
            self::Customer      => 'Customer',
        };
    }

    public function isBackoffice(): bool
    {
        return in_array($this, [self::Administrator, self::Manager, self::Reseller]);
    }

    public function isFinancialActor(): bool
    {
        return $this === self::Reseller;
    }
}
