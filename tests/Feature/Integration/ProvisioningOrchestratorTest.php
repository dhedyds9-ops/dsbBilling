<?php

namespace Tests\Feature\Integration;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Cache;
use App\Models\CRM\Customer;
use App\Models\Customer\Contract;
use App\Models\Customer\CustomerService;
use App\Models\ISP\ServiceProfile;
use App\Models\Provisioning\NetworkProfile;
use App\Models\Provisioning\ProvisionPipeline;
use App\Models\ISP\Olt;
use App\Models\ISP\Vendor;
use App\Models\ISP\Onu;
use App\Services\Provisioning\ProvisioningService;
use App\Jobs\Provisioning\StartProvisioningPipelineJob;
use App\Services\Provisioning\Pipeline\PipelineOrchestrator;
use App\Services\Provisioning\Pipeline\PipelineStateManager;
use App\Jobs\Provisioning\Steps\ResourceReservationJob;
use App\Jobs\Provisioning\Steps\ActivationJob;
use App\Jobs\Provisioning\Steps\ServiceVerificationJob;
use App\Jobs\Provisioning\Steps\OltOnuRegistrationJob;
use App\Jobs\Provisioning\Steps\OltServiceProvisioningJob;
use App\Services\Adapters\Provisioning\OltRegistry;
use App\Services\Adapters\Provisioning\Contracts\OltDriverInterface;
use Mockery;

class ProvisioningOrchestratorTest extends TestCase
{
    use RefreshDatabase;

    protected $provisioningService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->provisioningService = app(ProvisioningService::class);
    }

    protected function createDummyCustomerAndService()
    {
        if (\App\Models\User::where('id', 1)->doesntExist()) {
            \App\Models\User::factory()->create(['id' => 1]);
        }
        
        $customer = Customer::create(['uuid' => \Str::uuid(), 'code' => 'CUST-'.rand(10000,99999), 'name' => 'John Doe', 'phone' => '08123456789']);
        $contract = Contract::create(['uuid' => \Str::uuid(), 'customer_id' => $customer->id, 'contract_number' => 'CTR-'.rand(10000,99999), 'status' => 'active', 'start_date' => now()]);
        $sptId = \Illuminate\Support\Facades\DB::table('service_profile_types')->insertGetId(['code' => 'SPT-'.rand(10000,99999), 'name' => 'Internet']);
        $serviceProfile = ServiceProfile::create(['code' => 'SP-'.rand(10000,99999), 'name' => '20 Mbps', 'service_type' => 'pppoe', 'base_price' => 200000, 'service_profile_type_id' => $sptId]);
        
        $router = \App\Models\ISP\Router::firstOrCreate(['id' => 1], \App\Models\ISP\Router::factory()->raw(['id' => 1]));
        $networkProfile = NetworkProfile::create(['name' => 'VLAN 100', 'type' => 'pppoe', 'router_id' => $router->id]);

        $vendor = Vendor::create(['code' => 'VND-'.rand(10000,99999), 'name' => 'ZTE']);
        $olt = Olt::create(['code' => 'OLT-'.rand(10000,99999), 'name' => 'OLT Core', 'ip_address' => '10.0.0.1', 'vendor_id' => $vendor->id]);
        $onu = Onu::create(['olt_id' => $olt->id, 'serial_number' => 'ZTEG'.rand(10000,99999),
            'name' => 'Test ONU', 'pon_port' => 1]);

        return CustomerService::create([
            'uuid' => \Str::uuid(),
            'customer_id' => $customer->id,
            'contract_id' => $contract->id,
            'service_id' => \Illuminate\Support\Facades\DB::table('services')->insertGetId(['uuid' => \Str::uuid(), 'name' => 'Service', 'type' => 'pppoe', 'price' => 100000, 'is_active' => true, 'service_catalog_id' => \Illuminate\Support\Facades\DB::table('service_catalogs')->insertGetId(['uuid' => \Str::uuid(), 'name' => 'Cat'])]),
            'service_profile_id' => $serviceProfile->id,
            'network_profile_id' => $networkProfile->id,
            'username' => 'johndoe'.rand(100,999),
            'password' => 'secret',
            'status' => 'pending',
            'onu_id' => $onu->id,
        ]);
    }

    /** @test */
    public function p0_state_transition_test()
    {
        $cs = $this->createDummyCustomerAndService();
        $serviceInstance = $this->provisioningService->startProvisioning($cs, 1);
        $pipeline = $serviceInstance->provisionPipeline;

        $this->assertEquals('pending', $pipeline->status);
        $this->assertEquals('pending', $cs->fresh()->status);

        $startJob = new StartProvisioningPipelineJob($pipeline);
        $startJob->handle(app(PipelineOrchestrator::class));

        $pipeline->refresh();
        $cs->refresh();

        $this->assertEquals('running', $pipeline->status);
        $this->assertEquals('provisioning', $cs->status);

        $stateManager = app(PipelineStateManager::class);
        $firstStep = $stateManager->getNextPendingStep($pipeline);
        
        $this->assertEquals('customer_validation', $firstStep->step_type);
    }

    /** @test */
    public function p1_idempotency_and_retry_test()
    {
        $cs = $this->createDummyCustomerAndService();
        $serviceInstance = $this->provisioningService->startProvisioning($cs, 1);
        $pipeline = $serviceInstance->provisionPipeline;

        $stateManager = app(PipelineStateManager::class);
        $orchestrator = app(PipelineOrchestrator::class);

        $stateManager->markPipelineRunning($pipeline);

        $step1 = $pipeline->steps()->where('order', 1)->first();
        $step2 = $pipeline->steps()->where('order', 2)->first();
        $stateManager->updateStepStatus($step1, 'completed');
        $stateManager->updateStepStatus($step2, 'completed');

        $nextStep = $stateManager->getNextPendingStep($pipeline);
        
        $this->assertNotNull($nextStep);
        $this->assertEquals(3, $nextStep->order);
        $this->assertEquals('olt_onu_registration', $nextStep->step_type);

        $completedStepsCount = $pipeline->steps()->where('status', 'completed')->count();
        $this->assertEquals(2, $completedStepsCount);
    }

    /** @test */
    public function p2_resource_lock_test()
    {
        $cs1 = $this->createDummyCustomerAndService();
        $cs2 = $this->createDummyCustomerAndService();
        $cs2->update(['onu_id' => $cs1->onu_id]); 
        
        $p1 = $this->provisioningService->startProvisioning($cs1, 1)->provisionPipeline;
        $p2 = $this->provisioningService->startProvisioning($cs2, 1)->provisionPipeline;

        $step1 = $p1->steps()->where('step_type', 'resource_reservation')->first();
        $step2 = $p2->steps()->where('step_type', 'resource_reservation')->first();

        $job1 = new ResourceReservationJob($step1);
        $stateManager = app(PipelineStateManager::class);
        $orchestrator = app(PipelineOrchestrator::class);
        
        $job1->handle($stateManager, $orchestrator);
        $this->assertEquals('completed', $step1->refresh()->status);

        $job2 = new ResourceReservationJob($step2);
        
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage("ONU sedang diprovisioning oleh proses lain.");
        $job2->handle($stateManager, $orchestrator);
    }

    /** @test */
    public function p3_failure_matrix_test()
    {
        $cs = $this->createDummyCustomerAndService();
        $serviceInstance = $this->provisioningService->startProvisioning($cs, 1);
        $pipeline = $serviceInstance->provisionPipeline;

        $stateManager = app(PipelineStateManager::class);
        $orchestrator = app(PipelineOrchestrator::class);
        $stateManager->markPipelineRunning($pipeline);

        $step = $pipeline->steps()->where('order', 3)->first();
        $stateManager->updateStepStatus($step, 'failed', 'OLT Timeout');
        $stateManager->markPipelineFailed($pipeline, 'OLT Timeout');

        $pipeline->refresh();
        $cs->refresh();

        $this->assertEquals('failed', $pipeline->status);
        $this->assertNotEquals('suspended', $cs->status);
        $this->assertNotEquals('active', $cs->status);
        $this->assertTrue(in_array($cs->status, ['pending', 'provisioning']));
    }

    /** @test */
    public function p4_activation_strict_requirements_test()
    {
        $cs = $this->createDummyCustomerAndService();
        $serviceInstance = $this->provisioningService->startProvisioning($cs, 1);
        $pipeline = $serviceInstance->provisionPipeline;

        $activationStep = $pipeline->steps()->where('step_type', 'activation')->first();
        $job = new ActivationJob($activationStep);

        $stateManager = app(PipelineStateManager::class);
        $orchestrator = app(PipelineOrchestrator::class);

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage("Cannot activate: Service verification has not passed.");
        
        $job->handle($stateManager, $orchestrator);
        
        $this->assertNotEquals('active', $cs->fresh()->status);
    }

    /** @test */
    public function p5_real_olt_driver_integration_test()
    {
        // PHASE 13: Test Real OLT Integration via Mocks
        $cs = $this->createDummyCustomerAndService();
        $serviceInstance = $this->provisioningService->startProvisioning($cs, 1);
        $pipeline = $serviceInstance->provisionPipeline;

        // Mock the OltDriverInterface
        $mockDriver = Mockery::mock(OltDriverInterface::class);
        $mockDriver->shouldReceive('provisionOnu')
            ->once()
            ->with(Mockery::type(Onu::class), 'ZTEG123456', 1, 'default')
            ->andReturn(true);
        $mockDriver->shouldReceive('saveConfig')
            ->once();
        $mockDriver->shouldReceive('setOnuBandwidthLimit')
            ->once()
            ->with(Mockery::type(Onu::class), 50, 20)
            ->andReturn(true);

        // Mock OltRegistry to return our mock driver
        $registryMock = Mockery::mock(OltRegistry::class);
        $registryMock->shouldReceive('forOlt')->andReturn($mockDriver);
        $this->app->instance(OltRegistry::class, $registryMock);

        $stateManager = app(PipelineStateManager::class);
        $orchestrator = app(PipelineOrchestrator::class);

        // Test Step: OLT ONU Registration
        $regStep = $pipeline->steps()->where('step_type', 'olt_onu_registration')->first();
        $regJob = new OltOnuRegistrationJob($regStep);
        $regJob->handle($stateManager, $orchestrator);

        $this->assertEquals('completed', $regStep->refresh()->status);
        $this->assertEquals('online', $cs->onu->refresh()->status);

        // Test Step: OLT Service Provisioning (VLAN & QoS)
        $srvStep = $pipeline->steps()->where('step_type', 'olt_service_provisioning')->first();
        $srvJob = new OltServiceProvisioningJob($srvStep);
        $srvJob->handle($stateManager, $orchestrator);

        $this->assertEquals('completed', $srvStep->refresh()->status);
    }
}


