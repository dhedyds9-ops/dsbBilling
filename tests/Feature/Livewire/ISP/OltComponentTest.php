<?php

namespace Tests\Feature\Livewire\ISP;

use App\Livewire\ISP\Olt\Index as OltIndex;
use App\Models\ISP\Olt;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class OltComponentTest extends TestCase
{
    use RefreshDatabase;

    public function test_renders_successfully()
    {
        $user = User::factory()->create();
        $role = \App\Models\Role::firstOrCreate(
            ['name' => 'super_admin'],
            ['display_name' => 'Super Admin', 'description' => 'Super Admin Role']
        );
        $user->roles()->attach($role);

        Livewire::actingAs($user)
            ->test(OltIndex::class)
            ->assertStatus(200);
    }

    public function test_can_search_olts()
    {
        $user = User::factory()->create();
        $role = \App\Models\Role::firstOrCreate(
            ['name' => 'super_admin'],
            ['display_name' => 'Super Admin', 'description' => 'Super Admin Role']
        );
        $user->roles()->attach($role);

        Olt::create([
            'name' => 'ZTE C320 Searchable',
            'code' => 'ZTE-001',
            'host' => '192.168.1.100',
            'port' => 23,
            'username' => 'admin',
            'password' => 'admin',
            'status' => 'active',
            'type' => 'zte',
        ]);

        Livewire::actingAs($user)
            ->test(OltIndex::class)
            ->set('search', 'Searchable')
            ->assertSee('ZTE C320 Searchable');
    }
}
