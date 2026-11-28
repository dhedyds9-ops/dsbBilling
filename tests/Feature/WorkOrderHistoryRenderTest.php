<?php

namespace Tests\Feature;

use App\Livewire\Support\Installation\WorkOrderHistory;
use App\Models\User;
use App\Models\Role;
use App\Models\Master\Branch;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class WorkOrderHistoryRenderTest extends TestCase
{
    use RefreshDatabase;

    public function test_work_order_history_component_renders_without_sql_errors(): void
    {
        $role = Role::firstOrCreate(['name' => 'admin'], ['display_name' => 'Administrator']);
        $branch = Branch::firstOrCreate(['name' => 'Cabang 1'], ['code' => 'CB1']);

        $user = User::factory()->create([
            'job_function' => 'ADMINISTRATOR',
            'branch_id' => $branch->id,
            'is_active' => true,
        ]);
        $user->attachRole('admin');

        $this->actingAs($user);

        Livewire::test(WorkOrderHistory::class)
            ->assertStatus(200)
            ->assertSee('Daftar Riwayat Instalasi');
    }
}
