<?php

namespace Tests\Feature;

use App\Models\ACS\ACSDevice;
use App\Models\ACS\DeviceTask;
use App\Models\Role;
use App\Models\User;
use App\Services\ACS\AcsVerificationEngine;
use App\Services\Adapters\Monitoring\GenieACSDriver;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;
use Tests\TestCase;

class AcsFactoryResetRecoveryTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected ACSDevice $device;

    protected function setUp(): void
    {
        parent::setUp();

        $role = Role::firstOrCreate(['name' => 'administrator'], ['display_name' => 'Administrator']);
        $this->admin = User::factory()->create([
            'email' => 'admin_reset_test@local.test',
        ]);
        $this->admin->roles()->sync([$role->id]);

        $this->device = ACSDevice::create([
            'device_id' => 'ZTEG-F670L-RESET001',
            'uuid' => 'ZTEG-F670L-RESET001',
            'serial_number' => 'ZTEGRESET001',
            'manufacturer' => 'ZTE',
            'model_name' => 'F670L',
            'status' => 'online',
        ]);
    }

    protected function tearDown(): void
    {
        try {
            Mockery::close();
        } finally {
            parent::tearDown();
        }
    }

    /** Test happy path lifecycle across the 10-state recovery machine */
    public function test_factory_reset_happy_path_state_transitions(): void
    {
        $driverMock = Mockery::mock(GenieACSDriver::class);
        $driverMock->shouldReceive('resolveDeviceId')->andReturn($this->device->uuid);
        $driverMock->shouldReceive('factoryResetDevice')->andReturn(true);

        $engine = new AcsVerificationEngine($driverMock);

        // 1. Initial Reset Execution -> State 1: Reset Detected
        $res = $engine->executeFactoryResetAndRediscover($this->device->uuid, $this->admin->id);
        $this->assertTrue($res['success']);
        $taskId = $res['task_id'];
        $this->assertEquals(AcsVerificationEngine::RESET_STATE_RESET_DETECTED, $res['recovery_state']);

        // Assert device marked offline immediately
        $this->device->refresh();
        $this->assertEquals('offline', $this->device->status);

        // 2. ONT reboots and establishes CWMP connection -> State 2: ACS Reachable
        $t1 = $engine->transitionResetRecoveryState($taskId, AcsVerificationEngine::RESET_STATE_ACS_REACHABLE, 'ONT sent 0 BOOTSTRAP inform');
        $this->assertTrue($t1['success']);
        $this->assertEquals(AcsVerificationEngine::RESET_STATE_ACS_REACHABLE, $t1['current_state']);

        // 3. Initiate rediscovery -> State 4: Rediscovery Pending
        $t2 = $engine->transitionResetRecoveryState($taskId, AcsVerificationEngine::RESET_STATE_REDISCOVERY_PENDING, 'Queueing full object discovery');
        $this->assertEquals(AcsVerificationEngine::RESET_STATE_REDISCOVERY_PENDING, $t2['current_state']);

        // 4. Discovery complete -> State 5: Rediscovered
        $t3 = $engine->transitionResetRecoveryState($taskId, AcsVerificationEngine::RESET_STATE_REDISCOVERED, 'WAN and Wi-Fi parameter trees mapped');
        $this->assertEquals(AcsVerificationEngine::RESET_STATE_REDISCOVERED, $t3['current_state']);

        // 5. Push provisioning -> State 6: Provisioning Pending
        $t4 = $engine->transitionResetRecoveryState($taskId, AcsVerificationEngine::RESET_STATE_PROVISIONING_PENDING, 'Pushing PPPoE and SSID settings');
        $this->assertEquals(AcsVerificationEngine::RESET_STATE_PROVISIONING_PENDING, $t4['current_state']);

        // 6. Read-after-write verification succeeds -> State 8: Verified
        $t5 = $engine->transitionResetRecoveryState($taskId, AcsVerificationEngine::RESET_STATE_VERIFIED, 'Post-write telemetry matches desired profile');
        $this->assertEquals(AcsVerificationEngine::RESET_STATE_VERIFIED, $t5['current_state']);
        $this->assertEquals(AcsVerificationEngine::STATUS_VERIFIED, $t5['status']);

        // 7. Transition to operational Idle -> State 10: Idle
        $t6 = $engine->transitionResetRecoveryState($taskId, AcsVerificationEngine::RESET_STATE_IDLE, 'Recovery cycle successfully concluded');
        $this->assertEquals(AcsVerificationEngine::RESET_STATE_IDLE, $t6['current_state']);
        $this->assertEquals(AcsVerificationEngine::STATUS_VERIFIED, $t6['status']);

        // Evaluate Status
        $eval = $engine->evaluateResetRecoveryStatus($taskId);
        $this->assertTrue($eval['is_healthy']);
        $this->assertFalse($eval['requires_manual_intervention']);
        $this->assertCount(7, $eval['state_history']);
    }

    /** Test unreachable failure path requiring technician manual recovery */
    public function test_factory_reset_unreachable_requires_manual_recovery(): void
    {
        $driverMock = Mockery::mock(GenieACSDriver::class);
        $driverMock->shouldReceive('resolveDeviceId')->andReturn($this->device->uuid);

        $engine = new AcsVerificationEngine($driverMock);
        $task = $engine->trackResetRecovery($this->device->uuid, AcsVerificationEngine::RESET_STATE_RESET_DETECTED);

        // State 3: ACS Unreachable (e.g. ONT lost connection or failed bootstrap)
        $t1 = $engine->transitionResetRecoveryState($task, AcsVerificationEngine::RESET_STATE_ACS_UNREACHABLE, 'Modem failed to contact ACS after 10 minutes');
        $this->assertEquals(AcsVerificationEngine::RESET_STATE_ACS_UNREACHABLE, $t1['current_state']);
        $this->assertEquals(AcsVerificationEngine::STATUS_FAILED, $t1['status']);

        // State 9: Manual Recovery Required
        $t2 = $engine->transitionResetRecoveryState($task, AcsVerificationEngine::RESET_STATE_MANUAL_RECOVERY_REQUIRED, 'Technician dispatched for on-site inspection');
        $this->assertEquals(AcsVerificationEngine::RESET_STATE_MANUAL_RECOVERY_REQUIRED, $t2['current_state']);
        $this->assertEquals(AcsVerificationEngine::STATUS_FAILED, $t2['status']);

        $eval = $engine->evaluateResetRecoveryStatus($task);
        $this->assertFalse($eval['is_healthy']);
        $this->assertTrue($eval['requires_manual_intervention']);
    }

    /** Test provisioning failure path */
    public function test_factory_reset_provisioning_failure_path(): void
    {
        $driverMock = Mockery::mock(GenieACSDriver::class);
        $driverMock->shouldReceive('resolveDeviceId')->andReturn($this->device->uuid);

        $engine = new AcsVerificationEngine($driverMock);
        $task = $engine->trackResetRecovery($this->device->uuid, AcsVerificationEngine::RESET_STATE_PROVISIONING_PENDING);

        // State 7: Provisioning Failed
        $t1 = $engine->transitionResetRecoveryState($task, AcsVerificationEngine::RESET_STATE_PROVISIONING_FAILED, 'TR-069 Fault 9005: Invalid parameter value');
        $this->assertEquals(AcsVerificationEngine::RESET_STATE_PROVISIONING_FAILED, $t1['current_state']);
        $this->assertEquals(AcsVerificationEngine::STATUS_FAILED, $t1['status']);

        $eval = $engine->evaluateResetRecoveryStatus($task);
        $this->assertFalse($eval['is_healthy']);
        $this->assertTrue($eval['requires_manual_intervention']);
    }

    /** Test invalid state throws InvalidArgumentException */
    public function test_invalid_state_transition_throws_exception(): void
    {
        $driverMock = Mockery::mock(GenieACSDriver::class);
        $driverMock->shouldReceive('resolveDeviceId')->andReturn($this->device->uuid);

        $engine = new AcsVerificationEngine($driverMock);
        $task = $engine->trackResetRecovery($this->device->uuid);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid recovery state: Invalid_State_Name');

        $engine->transitionResetRecoveryState($task, 'Invalid_State_Name');
    }
}
