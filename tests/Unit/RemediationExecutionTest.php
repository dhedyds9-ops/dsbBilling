<?php

namespace Tests\Unit\Provisioning;

use App\Models\ISP\Onu;
use App\Models\OnuRemediationJob;
use App\Services\Provisioning\RemediationExecutionService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RemediationExecutionTest extends TestCase
{
    use RefreshDatabase;

    public function test_stale_plan_is_rejected()
    {
        $onuId = \Illuminate\Support\Facades\DB::table('onus')->insertGetId([
            'serial_number' => 'ZTEG12345678',
            'code' => 'ONU-TEST-1',
            'name' => 'Test ONU',
            'mac_address' => '00:11:22:33:44:55',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $onu = Onu::find($onuId);

        $service = new RemediationExecutionService();

        $plan = [
            'target_capability' => 'BRIDGE',
            'identity' => [
                'vendor' => 'ZTE',
                'model' => 'F670L',
                // Mismatched hardware version
                'hardware_version' => 'V9.9',
                'software_version' => 'V1.0.1'
            ],
            'steps' => []
        ];

        $result = $service->startRemediation($onu, $plan, null);
        $this->assertEquals('PLAN_STALE', $result['status']);
    }

    public function test_job_is_created_and_locked()
    {
        $onuId = \Illuminate\Support\Facades\DB::table('onus')->insertGetId([
            'serial_number' => 'ZTEG12345678',
            'code' => 'ONU-TEST-2',
            'name' => 'Test ONU',
            'mac_address' => '00:11:22:33:44:55',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $onu = Onu::find($onuId);

        $service = new RemediationExecutionService();

        $plan = [
            'target_capability' => 'BRIDGE',
            'identity' => [
                'vendor' => 'UNKNOWN',
                'model' => null,
                'hardware_version' => null,
                'software_version' => null
            ],
            'steps' => [],
            'requires_approval' => false
        ];
        $plan['plan_hash'] = hash('sha256', json_encode([
            'identity' => $plan['identity'],
            'target_capability' => $plan['target_capability'],
            'capability_state_hash' => '',
            'steps' => [],
            'requires_approval' => false,
            'customer_service_id' => null
        ]));

        $userId = \Illuminate\Support\Facades\DB::table('users')->insertGetId([
            'uuid' => (string) \Illuminate\Support\Str::uuid(),
            'name' => 'Admin', 'email' => 'admin@test.com', 'password' => 'secret', 'created_at' => now(), 'updated_at' => now()
        ]);
        $result = $service->startRemediation($onu, $plan, $userId);
        $this->assertEquals('SUCCESS', $result['status']);
        
        $job = OnuRemediationJob::where('onu_id', $onu->id)->first();
        $this->assertNotNull($job);
        $this->assertEquals('PENDING', $job->status);
    }
}
