<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\CRM\Customer;
use App\Models\Billing\Invoice;
use App\Models\ISP\Voucher;
use App\Models\ISP\Onu;
use App\Models\Role;
use App\Enums\UserRole;
use App\Enums\JobFunction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ResellerIsolationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Role::firstOrCreate(['name' => UserRole::Reseller->value], ['display_name' => 'Reseller']);
    }

    private function createResellerTeam()
    {
        $owner = User::factory()->create(['job_function' => null]);
        $owner->attachRole(UserRole::Reseller->value);

        $sales = User::factory()->create(['job_function' => JobFunction::SALES->value, 'reseller_id' => $owner->id]);
        $sales->attachRole(UserRole::Reseller->value);

        $pengurus = User::factory()->create(['job_function' => JobFunction::PENGURUS->value, 'reseller_id' => $owner->id]);
        $pengurus->attachRole(UserRole::Reseller->value);

        return [$owner, $sales, $pengurus];
    }

    public function test_effective_reseller_scope_for_isolation()
    {
        [$ownerA, $salesA, $pengurusA] = $this->createResellerTeam();
        [$ownerB, $salesB, $pengurusB] = $this->createResellerTeam();

        $userCustA = User::factory()->create(['reseller_id' => $ownerA->id]);
        $userCustA->attachRole(UserRole::Customer->value);
        $customerA = \Database\Factories\CRM\CustomerFactory::new()->create(['user_id' => $userCustA->id, 'reseller_id' => $ownerA->id]);

        $userCustB = User::factory()->create(['reseller_id' => $ownerB->id]);
        $userCustB->attachRole(UserRole::Customer->value);
        $customerB = \Database\Factories\CRM\CustomerFactory::new()->create(['user_id' => $userCustB->id, 'reseller_id' => $ownerB->id]);

        $invoiceA = \Database\Factories\Billing\InvoiceFactory::new()->create(['customer_id' => $customerA->id]);
        $invoiceB = \Database\Factories\Billing\InvoiceFactory::new()->create(['customer_id' => $customerB->id]);

        // TEST 7: Sales A -> customer Reseller A = ALLOWED
        $this->actingAs($salesA);
        $this->assertNotNull(Customer::find($customerA->id));

        // TEST 8: Sales A -> customer Reseller B = DENIED
        $this->assertNull(Customer::find($customerB->id));

        // We have successfully verified Customer isolation.
    }
}
