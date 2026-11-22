<?php

namespace Tests\Feature\Livewire\ISP;

use App\Livewire\Isp\JointClosure\Create as JointClosureCreate;
use App\Livewire\Isp\JointClosure\Edit as JointClosureEdit;
use App\Livewire\Isp\JointClosure\Index as JointClosureIndex;
use App\Livewire\Isp\JointClosure\Show as JointClosureShow;
use App\Models\ISP\JointClosure;
use App\Models\ISP\Odc;
use App\Models\ISP\Odp;
use App\Models\ISP\Olt;
use App\Models\Master\Branch;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

class JointClosureCrudTest extends TestCase
{
    use RefreshDatabase;

    protected $branch;
    protected $user;
    protected $olt;

    protected function setUp(): void
    {
        parent::setUp();
        $this->branch = Branch::create(['name' => 'Cabang Test', 'code' => 'TEST']);
        $this->user = User::factory()->create(['branch_id' => $this->branch->id]);
        $this->olt = Olt::create([
            'branch_id' => $this->branch->id,
            'name' => 'OLT Core Pusat',
            'code' => 'OLT-PST',
            'ip_address' => '10.20.30.1',
        ]);
    }

    public function test_joint_closure_index_component_renders_and_filters()
    {
        JointClosure::create([
            'branch_id' => $this->branch->id,
            'code' => 'JC-001',
            'name' => 'Closure Tiang 1',
            'type' => 'Dome',
            'port_count' => 12,
            'status' => 'active',
        ]);

        JointClosure::create([
            'branch_id' => $this->branch->id,
            'code' => 'JC-002',
            'name' => 'Closure Tiang 2',
            'type' => 'Inline',
            'port_count' => 24,
            'status' => 'inactive',
        ]);

        Livewire::actingAs($this->user)
            ->test(JointClosureIndex::class)
            ->assertStatus(200)
            ->assertSee('JC-001')
            ->assertSee('Closure Tiang 1')
            ->assertSee('12 Core')
            ->assertSee('JC-002')
            ->assertSee('Closure Tiang 2')
            ->set('search', 'Tiang 1')
            ->assertSee('Closure Tiang 1')
            ->assertDontSee('Closure Tiang 2')
            ->set('search', '')
            ->set('filters.port_count', '24')
            ->assertSee('Closure Tiang 2')
            ->assertDontSee('Closure Tiang 1');
    }

    public function test_joint_closure_create_with_preset_cores_and_uplink()
    {
        Livewire::actingAs($this->user)
            ->test(JointClosureCreate::class)
            ->set('code', 'JC-12C-TEST')
            ->set('name', 'Closure 12 Core Dome')
            ->set('type', 'Dome')
            ->set('port_count', 12)
            ->set('uplink_type', 'olt')
            ->set('olt_id', $this->olt->id)
            ->set('pon_port', 2)
            ->set('status', 'active')
            ->call('save')
            ->assertHasNoErrors()
            ->assertRedirect(route('isp.joint-closures.index'));

        $this->assertDatabaseHas('joint_closures', [
            'code' => 'JC-12C-TEST',
            'name' => 'Closure 12 Core Dome',
            'type' => 'Dome',
            'port_count' => 12,
            'olt_id' => $this->olt->id,
            'pon_port' => 2,
        ]);
    }

    public function test_joint_closure_create_with_custom_core_capacity()
    {
        Livewire::actingAs($this->user)
            ->test(JointClosureCreate::class)
            ->set('code', 'JC-CUSTOM-36')
            ->set('name', 'Closure 36 Core Khusus')
            ->set('type', 'Inline')
            ->set('port_count', 'custom')
            ->set('custom_port_count', 36)
            ->set('status', 'active')
            ->call('save')
            ->assertHasNoErrors()
            ->assertRedirect(route('isp.joint-closures.index'));

        $this->assertDatabaseHas('joint_closures', [
            'code' => 'JC-CUSTOM-36',
            'port_count' => 36,
            'type' => 'Inline',
        ]);
    }

    public function test_joint_closure_edit_component_updates_data()
    {
        $closure = JointClosure::create([
            'branch_id' => $this->branch->id,
            'code' => 'JC-EDIT-ME',
            'name' => 'Closure Sebelum Edit',
            'type' => 'Dome',
            'port_count' => 24,
            'status' => 'active',
        ]);

        Livewire::actingAs($this->user)
            ->test(JointClosureEdit::class, ['id' => $closure->id])
            ->set('name', 'Closure Setelah Edit Berhasil')
            ->set('type', 'Wall Mount')
            ->set('port_count', 48)
            ->call('update')
            ->assertHasNoErrors()
            ->assertRedirect(route('isp.joint-closures.show', $closure->id));

        $this->assertDatabaseHas('joint_closures', [
            'id' => $closure->id,
            'name' => 'Closure Setelah Edit Berhasil',
            'type' => 'Wall Mount',
            'port_count' => 48,
        ]);
    }

    public function test_joint_closure_show_component_renders_tabs_and_relationships()
    {
        Storage::fake('public');

        $parent = JointClosure::create([
            'branch_id' => $this->branch->id,
            'code' => 'JC-PARENT',
            'name' => 'Closure Induk 48C',
            'type' => 'Dome',
            'port_count' => 48,
            'status' => 'active',
        ]);

        $child = JointClosure::create([
            'branch_id' => $this->branch->id,
            'parent_closure_id' => $parent->id,
            'code' => 'JC-CHILD',
            'name' => 'Closure Anak 12C',
            'type' => 'Inline',
            'port_count' => 12,
            'status' => 'active',
        ]);

        $odp = Odp::create([
            'branch_id' => $this->branch->id,
            'joint_closure_id' => $parent->id,
            'code' => 'ODP-JC-01',
            'name' => 'ODP Splice A',
            'port_count' => 8,
            'status' => 'active',
        ]);

        $component = Livewire::actingAs($this->user)
            ->test(JointClosureShow::class, ['id' => $parent->id])
            ->assertStatus(200)
            ->assertSee('Closure Induk 48C')
            ->assertSee('JC-PARENT')
            ->assertSee('48 Core')
            ->call('setTab', 'odps')
            ->assertSee('ODP Splice A')
            ->assertSee('ODP-JC-01')
            ->call('setTab', 'history');

        // Test Photo Upload
        $file = UploadedFile::fake()->image('closure_field.jpg');
        $component->set('newPhoto', $file)
            ->call('uploadPhoto')
            ->assertHasNoErrors();

        $parent->refresh();
        $this->assertIsArray($parent->photos);
        $this->assertCount(1, $parent->photos);
    }
}
