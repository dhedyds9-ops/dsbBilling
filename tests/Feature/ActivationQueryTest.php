<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\CRM\Customer;
use App\Models\CRM\Installation;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class ActivationQueryTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_render_activation_page_with_installations(): void
    {
        Role::firstOrCreate(['name' => UserRole::Administrator->value], ['display_name' => 'Administrator']);

        $admin = User::factory()->create([
            'email' => 'admin_activation@example.com',
            'is_active' => true,
        ]);
        $admin->attachRole(UserRole::Administrator->value);

        // 1. Pending installation
        $pendingCustomer = Customer::create([
            'name' => 'Sarminah Test',
            'phone' => '081234567890',
            'address' => 'Kp. Cijoro',
            'status' => 'active',
        ]);

        $pendingInst = Installation::create([
            'customer_id' => $pendingCustomer->id,
            'customer_name' => $pendingCustomer->name,
            'customer_phone' => $pendingCustomer->phone,
            'installation_address' => $pendingCustomer->address,
            'status' => 'scheduled',
            'priority' => 'medium',
        ]);

        // 2. Completed installation
        $completedCustomer = Customer::create([
            'name' => 'Hendra Selesai',
            'phone' => '085319000000',
            'address' => 'Kp. Warunggunung',
            'status' => 'active',
        ]);

        $completedInst = Installation::create([
            'customer_id' => $completedCustomer->id,
            'customer_name' => $completedCustomer->name,
            'customer_phone' => $completedCustomer->phone,
            'installation_address' => $completedCustomer->address,
            'status' => 'completed',
            'completed_at' => now(),
            'priority' => 'medium',
        ]);

        // Check HTTP response
        $response = $this->actingAs($admin)->get('/crm/activations');
        $response->assertStatus(200);

        // Test Livewire component
        $lw = Livewire::actingAs($admin)
            ->test(\App\Livewire\Crm\Activation\Index::class);

        // Assert pending installation appears in pending tab
        $lw->assertSee('Sarminah Test')
           ->assertDontSee('Hendra Selesai');

        // Switch to completed tab and assert completed installation appears
        $lw->call('switchTab', 'completed')
           ->assertSee('Hendra Selesai')
           ->assertDontSee('Sarminah Test');

        // Test opening activation modal (installation must be completed before activation)
        $pendingInst->update(['status' => 'completed', 'completed_at' => now()]);
        $lw->call('switchTab', 'pending')
           ->call('openActivationModal', $pendingInst->id, 'installation')
           ->assertSet('showActivationModal', true)
           ->assertSet('activation_prospect_name', 'Sarminah Test');
    }
}
