<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\CRM\Customer;
use App\Models\CRM\Installation;
use App\Models\Master\Branch;
use App\Models\User;
use App\Models\Workforce\MaterialUsage as WorkforceMaterialUsage;
use App\Models\Workforce\WorkOrder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Livewire\Livewire;
use Tests\TestCase;

class TechnicianMyJobsShowTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        \App\Models\Role::firstOrCreate(['name' => UserRole::Manager->value], ['display_name' => 'Manager']);
        \App\Models\Role::firstOrCreate(['name' => 'technician'], ['display_name' => 'Technician']);
    }

    public function test_technician_can_view_job_detail_and_start_job(): void
    {
        $branch = Branch::create(['name' => 'Cabang 1', 'code' => 'CB1', 'is_active' => true]);

        $tech = User::factory()->create([
            'job_function' => 'TECHNICIAN',
            'branch_id' => $branch->id,
            'is_active' => true,
        ]);
        $tech->attachRole('technician');

        $customer = Customer::create([
            'code' => 'CUST-001',
            'name' => 'Budi Santoso',
            'phone' => '08123456789',
            'branch_id' => $branch->id,
            'status' => 'pending',
        ]);

        $inst = Installation::create([
            'customer_id' => $customer->id,
            'branch_id' => $branch->id,
            'assigned_to' => $tech->id,
            'status' => 'pending',
            'notes' => 'Tolong pasang pagi hari',
        ]);

        $wo = WorkOrder::create([
            'uuid' => (string) Str::uuid(),
            'installation_id' => $inst->id,
            'customer_id' => $customer->id,
            'assigned_to' => $tech->id,
            'branch_id' => $branch->id,
            'title' => 'PSB: Budi Santoso',
            'status' => 'pending',
        ]);

        $this->actingAs($tech);

        Livewire::test(\App\Livewire\Isp\Technician\MyJobs\Show::class, ['job' => $wo->id])
            ->assertSee('PSB: Budi Santoso')
            ->assertSee('Budi Santoso')
            ->assertSet('jobData.can_start', true)
            ->call('startJob')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('work_orders', [
            'id' => $wo->id,
            'status' => 'in_progress',
        ]);

        $this->assertDatabaseHas('installations', [
            'id' => $inst->id,
            'status' => 'in_progress',
        ]);
    }

    public function test_technician_can_complete_job_with_optical_power_and_notes(): void
    {
        $branch = Branch::create(['name' => 'Cabang 1', 'code' => 'CB1', 'is_active' => true]);

        $tech = User::factory()->create([
            'job_function' => 'TECHNICIAN',
            'branch_id' => $branch->id,
            'is_active' => true,
        ]);
        $tech->attachRole('technician');

        $customer = Customer::create([
            'code' => 'CUST-002',
            'name' => 'Siti Rahma',
            'phone' => '08987654321',
            'branch_id' => $branch->id,
            'status' => 'pending',
        ]);

        $inst = Installation::create([
            'customer_id' => $customer->id,
            'branch_id' => $branch->id,
            'assigned_to' => $tech->id,
            'status' => 'in_progress',
        ]);

        $wo = WorkOrder::create([
            'uuid' => (string) Str::uuid(),
            'installation_id' => $inst->id,
            'customer_id' => $customer->id,
            'assigned_to' => $tech->id,
            'branch_id' => $branch->id,
            'title' => 'PSB: Siti Rahma',
            'status' => 'in_progress',
            'started_at' => now()->subHour(),
        ]);

        $this->actingAs($tech);

        Livewire::test(\App\Livewire\Isp\Technician\MyJobs\Show::class, ['job' => $wo->id])
            ->assertSet('jobData.can_complete', true)
            ->set('optical_power', '-18.50')
            ->set('completion_notes', 'Instalasi selesai, sinyal redaman bagus')
            ->call('completeJob')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('work_orders', [
            'id' => $wo->id,
            'status' => 'completed',
            'optical_rx_power' => -18.50,
        ]);

        $this->assertDatabaseHas('installations', [
            'id' => $inst->id,
            'status' => 'completed',
            'optical_power' => '-18.50',
            'notes' => 'Instalasi selesai, sinyal redaman bagus',
        ]);
    }

    public function test_material_usage_cross_links_installation_and_work_order(): void
    {
        $branch = Branch::create(['name' => 'Cabang 1', 'code' => 'CB1', 'is_active' => true]);

        $inst = Installation::create([
            'branch_id' => $branch->id,
            'status' => 'in_progress',
        ]);

        $wo = WorkOrder::create([
            'uuid' => (string) Str::uuid(),
            'installation_id' => $inst->id,
            'branch_id' => $branch->id,
            'title' => 'WO Material Cross Link Test',
            'status' => 'in_progress',
        ]);

        $usage = WorkforceMaterialUsage::create([
            'uuid' => (string) Str::uuid(),
            'work_order_id' => $wo->id,
            'installation_id' => $inst->id,
            'material_name' => 'Dropcore Optik 1C',
            'quantity' => 150,
        ]);

        $this->assertDatabaseHas('material_usages', [
            'id' => $usage->id,
            'work_order_id' => $wo->id,
            'installation_id' => $inst->id,
            'material_name' => 'Dropcore Optik 1C',
            'quantity' => 150,
        ]);

        $this->assertEquals($wo->id, $usage->workOrder->id);
        $this->assertEquals($inst->id, $usage->installation->id);
    }

    public function test_unassigned_technician_from_different_branch_cannot_access_job(): void
    {
        $branch1 = Branch::create(['name' => 'Cabang 1', 'code' => 'CB1', 'is_active' => true]);
        $branch2 = Branch::create(['name' => 'Cabang 2', 'code' => 'CB2', 'is_active' => true]);

        $tech1 = User::factory()->create([
            'job_function' => 'TECHNICIAN',
            'branch_id' => $branch1->id,
            'is_active' => true,
        ]);
        $tech1->attachRole('technician');

        $tech2 = User::factory()->create([
            'job_function' => 'TECHNICIAN',
            'branch_id' => $branch2->id,
            'is_active' => true,
        ]);
        $tech2->attachRole('technician');

        $wo = WorkOrder::create([
            'uuid' => (string) Str::uuid(),
            'assigned_to' => $tech1->id,
            'branch_id' => $branch1->id,
            'title' => 'Secret Job Cabang 1',
            'status' => 'pending',
        ]);

        $this->actingAs($tech2);

        Livewire::test(\App\Livewire\Isp\Technician\MyJobs\Show::class, ['job' => $wo->id])
            ->assertForbidden();
    }
}
