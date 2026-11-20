<?php

namespace Tests\Feature;

use App\Enums\UserPermission;
use App\Enums\UserRole;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use App\Services\Auth\PermissionRegistry;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserGranularPermissionTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        PermissionRegistry::syncAllPermissionsToDatabase();
    }

    public function test_default_reseller_inherits_standard_permissions()
    {
        $resellerRole = Role::where('name', UserRole::Reseller->value)->first();
        $user = User::factory()->create();
        $user->roles()->sync([$resellerRole->id]);

        // Default reseller tanpa direct permission kustom mengikuti standard permissions
        $this->assertTrue($user->hasPermission(UserPermission::CustomerCreate->value));
        $this->assertTrue($user->hasPermission(UserPermission::VoucherCreate->value));
    }

    public function test_custom_reseller_a_full_vs_reseller_b_restricted()
    {
        $resellerRole = Role::where('name', UserRole::Reseller->value)->first();

        // Reseller A: Diberi custom direct permissions lengkap (Customer, Voucher, Tagihan)
        $permCustomer = Permission::where('name', UserPermission::CustomerCreate->value)->first();
        $permVoucher = Permission::where('name', UserPermission::VoucherCreate->value)->first();
        $permInvoice = Permission::where('name', UserPermission::InvoiceView->value)->first();

        $resellerA = User::factory()->create();
        $resellerA->roles()->sync([$resellerRole->id]);
        $resellerA->directPermissions()->sync([$permCustomer->id, $permVoucher->id, $permInvoice->id]);

        // Reseller B: Hanya diberi izin InvoiceView (dicabut izin customer create dan voucher create)
        $resellerB = User::factory()->create();
        $resellerB->roles()->sync([$resellerRole->id]);
        $resellerB->directPermissions()->sync([$permInvoice->id]);

        // Reseller A memiliki izin Customer & Voucher
        $this->assertTrue($resellerA->hasPermission(UserPermission::CustomerCreate->value));
        $this->assertTrue($resellerA->hasPermission(UserPermission::VoucherCreate->value));
        $this->assertTrue($resellerA->hasPermission(UserPermission::InvoiceView->value));

        // Reseller B HANYA memiliki InvoiceView, dan DILARANG create customer & voucher
        $this->assertTrue($resellerB->hasPermission(UserPermission::InvoiceView->value));
        $this->assertFalse($resellerB->hasPermission(UserPermission::CustomerCreate->value));
        $this->assertFalse($resellerB->hasPermission(UserPermission::VoucherCreate->value));
    }

    public function test_custom_technician_a_full_vs_technician_b_restricted()
    {
        $technicianRole = Role::where('name', UserRole::Teknisi->value)->first();

        $permWan = Permission::where('name', UserPermission::OnuConfigureWan->value)->first();
        $permJobs = Permission::where('name', UserPermission::WorkforceJobsView->value)->first();

        // Teknisi A: Memiliki izin konfigurasi WAN & pekerjaan tugas
        $techA = User::factory()->create(['job_function' => 'TECHNICIAN']);
        $techA->roles()->sync([$technicianRole->id]);
        $techA->directPermissions()->sync([$permWan->id, $permJobs->id]);

        // Teknisi B: Hanya diizinkan melihat pekerjaan tugas (dicabut izin konfigurasi WAN ONT)
        $techB = User::factory()->create(['job_function' => 'TECHNICIAN']);
        $techB->roles()->sync([$technicianRole->id]);
        $techB->directPermissions()->sync([$permJobs->id]);

        $this->assertTrue($techA->hasPermission(UserPermission::OnuConfigureWan->value));
        $this->assertTrue($techA->hasPermission(UserPermission::WorkforceJobsView->value));

        $this->assertTrue($techB->hasPermission(UserPermission::WorkforceJobsView->value));
        $this->assertFalse($techB->hasPermission(UserPermission::OnuConfigureWan->value));
    }

    public function test_clearing_direct_permissions_reverts_user_to_role_default()
    {
        $resellerRole = Role::where('name', UserRole::Reseller->value)->first();
        $permInvoice = Permission::where('name', UserPermission::InvoiceView->value)->first();

        $user = User::factory()->create();
        $user->roles()->sync([$resellerRole->id]);
        $user->directPermissions()->sync([$permInvoice->id]);

        // Saat custom permissions aktif: restricted
        $this->assertFalse($user->hasPermission(UserPermission::VoucherCreate->value));

        // Saat admin menonaktifkan kustomisasi (detach direct permissions):
        $user->directPermissions()->detach();
        $user->unsetRelation('directPermissions');

        // Kembali otomatis mengikuti template role default
        $this->assertTrue($user->hasPermission(UserPermission::VoucherCreate->value));
    }
}
