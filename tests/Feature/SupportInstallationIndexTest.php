<?php

namespace Tests\Feature;

use App\Livewire\Support\Installation\Index;
use App\Models\CRM\Customer;
use App\Models\CRM\Installation;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class SupportInstallationIndexTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected User $technician;
    protected Customer $customer;

    protected function setUp(): void
    {
        parent::setUp();

        $adminRole = Role::firstOrCreate(['name' => 'administrator'], ['display_name' => 'Administrator', 'guard_name' => 'web']);
        $techRole = Role::firstOrCreate(['name' => 'technician'], ['display_name' => 'Teknisi', 'guard_name' => 'web']);

        $this->admin = User::factory()->create([
            'email' => 'admin_install@dsbilling.test',
            'job_function' => 'ADMINISTRATOR',
            'is_active' => true,
        ]);
        $this->admin->roles()->attach($adminRole->id);

        $this->technician = User::factory()->create([
            'email' => 'tech_install@dsbilling.test',
            'job_function' => 'TECHNICIAN',
            'is_active' => true,
        ]);
        $this->technician->roles()->attach($techRole->id);

        $this->customer = Customer::create([
            'uuid' => (string) \Illuminate\Support\Str::uuid(),
            'name' => 'Pelanggan Uji Coba',
            'code' => 'CUST-TEST-001',
            'phone' => '08123456789',
            'status' => 'active',
        ]);
    }

    public function test_installation_page_accessible_by_admin(): void
    {
        $response = $this->actingAs($this->admin)->get('/support/installation');
        $response->assertStatus(200);
    }

    public function test_installation_livewire_component_renders(): void
    {
        Installation::create([
            'customer_id' => $this->customer->id,
            'customer_name' => $this->customer->name,
            'technician_id' => $this->technician->id,
            'status' => 'scheduled',
            'priority' => 'high',
            'scheduled_date' => now()->toDateString(),
            'scheduled_time' => '10:00',
            'notes' => 'Pemasangan baru drop core 50m',
        ]);

        Livewire::actingAs($this->admin)
            ->test(Index::class)
            ->assertStatus(200)
            ->assertSee('Support Instalasi')
            ->assertSee('Pelanggan Uji Coba')
            ->assertSet('activeTab', 'workorder');
    }

    public function test_installation_livewire_tabs_and_filters(): void
    {
        Livewire::actingAs($this->admin)
            ->test(Index::class)
            ->set('showFilters', true)
            ->assertStatus(200)
            ->set('activeTab', 'technician')
            ->assertStatus(200)
            ->set('activeTab', 'checklist')
            ->assertStatus(200)
            ->assertSee('Template Checklist Instalasi');
    }
}
