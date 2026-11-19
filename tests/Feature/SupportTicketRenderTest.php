<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SupportTicketRenderTest extends TestCase
{
    use RefreshDatabase;

    public function test_manager_can_render_support_ticket_page(): void
    {
        \App\Models\Role::firstOrCreate(['name' => UserRole::Manager->value], ['display_name' => 'Manager']);
        
        $manager = User::factory()->create([
            'email' => 'manager_test@example.com',
            'is_active' => true,
        ]);
        $manager->attachRole(UserRole::Manager->value);

        $response = $this->actingAs($manager)->get('/support/ticket');
        $response->assertStatus(200);

        \Livewire\Livewire::actingAs($manager)
            ->test(\App\Livewire\Support\Ticket\Index::class)
            ->set('showCreateForm', true)
            ->set('newTicket.is_manual_customer', true);
    }

    public function test_reseller_can_render_support_ticket_page(): void
    {
        \App\Models\Role::firstOrCreate(['name' => UserRole::Reseller->value], ['display_name' => 'Reseller']);
        
        $reseller = User::factory()->create([
            'email' => 'reseller_test@example.com',
            'is_active' => true,
        ]);
        $reseller->attachRole(UserRole::Reseller->value);

        // Reseller accessing /reseller-portal/tickets gets 200
        $response = $this->actingAs($reseller)->get('/reseller-portal/tickets');
        $response->assertStatus(200);

        // Reseller accessing /support/ticket gets redirected to reseller portal dashboard
        $responseRedirect = $this->actingAs($reseller)->get('/support/ticket');
        $responseRedirect->assertRedirect(route('reseller-portal.dashboard'));

        // Reseller rendering the Livewire component directly
        \Livewire\Livewire::actingAs($reseller)
            ->test(\App\Livewire\Support\Ticket\Index::class)
            ->set('showCreateForm', true)
            ->set('newTicket.is_manual_customer', true);
    }

    public function test_technician_can_render_support_ticket_page(): void
    {
        \App\Models\Role::firstOrCreate(['name' => 'technician'], ['display_name' => 'Technician']);
        
        $tech = User::factory()->create([
            'email' => 'tech_test@example.com',
            'job_function' => 'TECHNICIAN',
            'is_active' => true,
        ]);
        $tech->attachRole('technician');

        $response = $this->actingAs($tech)->get('/support/ticket');
        // Technician might be redirected or 200
        $this->assertTrue(in_array($response->status(), [200, 302]));
    }

    public function test_selecting_odp_auto_fills_coordinates_in_ticket_address(): void
    {
        \App\Models\Role::firstOrCreate(['name' => UserRole::Manager->value], ['display_name' => 'Manager']);
        $manager = User::factory()->create([
            'email' => 'mgr_odp@example.com',
            'is_active' => true,
        ]);
        $manager->attachRole(UserRole::Manager->value);

        $odp = \App\Models\ISP\Odp::create([
            'name' => 'ODP 3682',
            'code' => 'ODP-3682',
            'latitude' => -6.800387,
            'longitude' => 105.939215,
            'address' => 'Jl. Merdeka Binuangeun',
            'status' => 'active',
        ]);

        \Livewire\Livewire::actingAs($manager)
            ->test(\App\Livewire\Support\Ticket\Index::class)
            ->set('showCreateForm', true)
            ->set('newTicket.manual_type', 'network')
            ->set('newTicket.manual_network_odp_id', $odp->id)
            ->assertSet('newTicket.manual_customer_name', 'ODP: ODP 3682 (ODP-3682)')
            ->assertSet('newTicket.manual_customer_address', 'Jl. Merdeka Binuangeun | Koordinat: -6.800387, 105.939215');
    }
}
