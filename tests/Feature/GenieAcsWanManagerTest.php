<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\ACS\ACSDevice;
use App\Models\User;
use App\Livewire\ACS\Device\WanManager;
use App\Services\Adapters\Monitoring\GenieACSDriver;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Livewire\Livewire;

class GenieAcsWanManagerTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected ACSDevice $device;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create([
            'email' => 'admin_wan@test.local',
        ]);
        $role = \App\Models\Role::firstOrCreate(['name' => 'administrator']);
        $this->admin->roles()->sync([$role->id]);

        $this->device = ACSDevice::create([
            'device_id' => '202BC1-F609-ZTEGC12345678',
            'uuid' => '202BC1-F609-ZTEGC12345678',
            'serial_number' => 'ZTEGC12345678',
            'manufacturer' => 'ZTE',
            'product_class' => 'F609',
            'status' => 'online',
        ]);
    }

    public function test_wan_manager_component_mounts_and_form_mode_is_defined(): void
    {
        Livewire::actingAs($this->admin)
            ->test(WanManager::class, ['device' => $this->device])
            ->assertSet('formMode', 'pppoe_route')
            ->assertSet('formVlanEnabled', true)
            ->set('showWanModal', true)
            ->set('isLoading', false)
            ->assertSee('Manajemen Koneksi WAN')
            ->assertSee('Reboot Modem')
            ->assertSee('Summon / Refresh');
    }

    public function test_save_new_wan_does_not_throw_undefined_variable_form_mode(): void
    {
        Http::fake([
            '*/provisions/*' => Http::response('', 200),
            '*/devices/*/tasks*' => Http::response(['_id' => 'task123'], 200),
            '*/devices/*' => Http::response([
                '_id' => '202BC1-F609-ZTEGC12345678',
                'InternetGatewayDevice' => [],
            ], 200),
        ]);

        Livewire::actingAs($this->admin)
            ->test(WanManager::class, ['device' => $this->device])
            ->set('showWanModal', true)
            ->set('isLoading', false)
            ->set('isCreating', true)
            ->set('formMode', 'pppoe_route')
            ->set('formUsername', 'sri_mulyani')
            ->set('formPassword', 'secret123')
            ->set('formVlanEnabled', true)
            ->set('formVlan', '105')
            ->call('saveNewWan')
            ->assertSee('Perintah pembuatan WAN');
    }

    public function test_save_wan_migrates_ip_connection_to_pppoe_with_provisions_task(): void
    {
        $interceptedTaskPayload = null;

        Http::fake([
            '*/provisions/*' => Http::response('', 200),
            '*/devices/*/tasks*' => function (\Illuminate\Http\Client\Request $request) use (&$interceptedTaskPayload) {
                $interceptedTaskPayload = $request->data();
                return Http::response(['_id' => 'task456'], 200);
            },
            '*/devices/*' => Http::response([
                '_id' => '202BC1-F609-ZTEGC12345678',
                'InternetGatewayDevice' => [],
            ], 200),
        ]);

        $testWan = [
            'key' => 'WANConnectionDevice.1.WANIPConnection.1',
            'parentPath' => 'InternetGatewayDevice.WANDevice.1.WANConnectionDevice.1',
            'fullPath' => 'InternetGatewayDevice.WANDevice.1.WANConnectionDevice.1.WANIPConnection.1',
            'name' => '1_INTERNET_B_VID_',
            'type' => 'IP_Bridged',
            'mode_label' => 'Bridge IP',
            'vlan' => null,
            'nat' => false,
            'service_list' => 'INTERNET',
            'port_bind' => '',
            'username' => '',
            'password' => '',
        ];

        Livewire::actingAs($this->admin)
            ->test(WanManager::class, ['device' => $this->device])
            ->set('showWanModal', true)
            ->set('isLoading', false)
            ->set('wanConnections', [$testWan])
            ->call('editWan', 'InternetGatewayDevice.WANDevice.1.WANConnectionDevice.1.WANIPConnection.1')
            ->set('formMode', 'pppoe_route')
            ->set('formUsername', 'SRI_MULYANI')
            ->set('formPassword', 'mulyani@pass')
            ->set('formVlanEnabled', true)
            ->set('formVlan', '105')
            ->call('saveWan')
            ->assertSee('Task migrasi WAN ke PPPoE');

        $this->assertNotNull($interceptedTaskPayload, 'GenieACS task should have been dispatched.');
        $this->assertEquals('provisions', $interceptedTaskPayload['name']);
        $this->assertIsArray($interceptedTaskPayload['provisions']);
        $this->assertStringStartsWith('temp_migrate_wan_', $interceptedTaskPayload['provisions'][0]);
    }

    public function test_driver_add_provision_task_uses_provisions_task_name(): void
    {
        $interceptedBody = null;

        Http::fake([
            '*/devices/*/tasks*' => function (\Illuminate\Http\Client\Request $request) use (&$interceptedBody) {
                $interceptedBody = $request->data();
                return Http::response(['_id' => 'task789'], 200);
            },
        ]);

        $driver = new GenieACSDriver();
        $result = $driver->addProvisionTask('test-device-id', 'dsBilling_Setup_WAN');

        $this->assertTrue($result);
        $this->assertNotNull($interceptedBody);
        $this->assertEquals('provisions', $interceptedBody['name']);
        $this->assertEquals(['dsBilling_Setup_WAN'], $interceptedBody['provisions']);
    }

    public function test_reboot_device_sends_reboot_task(): void
    {
        Http::fake([
            '*/devices/*/tasks*' => Http::response(['_id' => 'reboot_task_1'], 200),
        ]);

        Livewire::actingAs($this->admin)
            ->test(WanManager::class, ['device' => $this->device])
            ->set('showWanModal', true)
            ->set('isLoading', false)
            ->call('rebootDevice')
            ->assertSee('Perintah reboot berhasil dikirim');
    }

    public function test_delete_wan_dispatches_task_and_removes_from_list(): void
    {
        Http::fake([
            '*/provisions/*' => Http::response([], 200),
            '*/devices/*/tasks*' => Http::response(['_id' => 'del_task_1'], 200),
        ]);

        $testWan = [
            'key' => 'WANConnectionDevice.3.WANIPConnection.1',
            'parentPath' => 'InternetGatewayDevice.WANDevice.1.WANConnectionDevice.3',
            'fullPath' => 'InternetGatewayDevice.WANDevice.1.WANConnectionDevice.3.WANIPConnection.1',
            'name' => '3_INTERNET_R_VID_105',
            'type' => 'IP_Routed',
            'mode_label' => 'IP Route (DHCP)',
            'vlan' => 105,
            'nat' => true,
            'service_list' => 'INTERNET',
            'port_bind' => '',
            'username' => '',
            'password' => '',
        ];

        Livewire::actingAs($this->admin)
            ->test(WanManager::class, ['device' => $this->device])
            ->set('showWanModal', true)
            ->set('isLoading', false)
            ->set('wanConnections', [$testWan])
            ->call('deleteWan', 'InternetGatewayDevice.WANDevice.1.WANConnectionDevice.3.WANIPConnection.1')
            ->assertSee('Perintah hapus koneksi WAN')
            ->assertSet('wanConnections', []);
    }

    public function test_delete_wan_protects_tr069_connection(): void
    {
        $tr069Wan = [
            'key' => 'WANConnectionDevice.2.WANIPConnection.1',
            'parentPath' => 'InternetGatewayDevice.WANDevice.1.WANConnectionDevice.2',
            'fullPath' => 'InternetGatewayDevice.WANDevice.1.WANConnectionDevice.2.WANIPConnection.1',
            'name' => '2_TR069_R_VID_99',
            'type' => 'IP_Routed',
            'mode_label' => 'IP Route (DHCP)',
            'vlan' => 99,
            'nat' => true,
            'service_list' => 'TR069',
            'port_bind' => '',
            'username' => '',
            'password' => '',
        ];

        Livewire::actingAs($this->admin)
            ->test(WanManager::class, ['device' => $this->device])
            ->set('showWanModal', true)
            ->set('isLoading', false)
            ->set('wanConnections', [$tr069Wan])
            ->call('deleteWan', 'InternetGatewayDevice.WANDevice.1.WANConnectionDevice.2.WANIPConnection.1')
            ->assertSee('Koneksi TR-069 dilindungi')
            ->assertCount('wanConnections', 1);
    }
}
