<?php

namespace Tests\Feature;

use App\Models\Alarm;
use App\Models\ISP\Router;
use App\Models\Setting;
use App\Services\Adapters\Monitoring\MikroTikDriver;
use App\Services\ISP\Session\OnlineSessionStore;
use App\Services\Monitoring\AlertEngine;
use App\Services\Monitoring\Collectors\RouterCollector;
use App\Services\Telegram\TelegramService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Mockery;
use Tests\TestCase;

class RouterMonitoringAlertTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Setup Telegram Settings in DB
        Setting::setValue('telegram.bot', [
            'bot_token' => '123456:TEST_TOKEN_FOR_ALERT',
        ]);
        Setting::setValue('telegram.chatIds', [
            ['id' => '-100999888777', 'name' => 'NOC Alert Group', 'enabled' => true, 'events' => 'alarm,outage'],
        ]);
    }

    public function test_telegram_service_broadcasts_network_device_outage_and_recovery_alert(): void
    {
        Http::fake([
            'https://api.telegram.org/*' => Http::response(['ok' => true, 'result' => ['message_id' => 999]], 200),
        ]);

        $telegram = new TelegramService();
        $this->assertTrue($telegram->isEnabled());

        // Test Offline Alert
        $telegram->sendNetworkDeviceAlert(
            'Core-CCR1036',
            '192.168.1.1',
            'OFFLINE',
            'Ping timeout',
            'Router MikroTik'
        );

        Http::assertSent(function ($request) {
            $body = $request->data();
            return str_contains($body['text'], 'GANGGUAN (OFFLINE / DOWN)')
                && str_contains($body['text'], 'Core-CCR1036')
                && str_contains($body['text'], '192.168.1.1')
                && $body['chat_id'] === '-100999888777';
        });

        // Test Recovery Alert
        $telegram->sendNetworkDeviceAlert(
            'Core-CCR1036',
            '192.168.1.1',
            'ONLINE',
            'Koneksi pulih',
            'Router MikroTik'
        );

        Http::assertSent(function ($request) {
            $body = $request->data();
            return str_contains($body['text'], 'PULIH (ONLINE / UP)')
                && str_contains($body['text'], 'Core-CCR1036');
        });
    }

    public function test_router_collector_triggers_telegram_offline_and_recovery_alert(): void
    {
        Http::fake([
            'https://api.telegram.org/*' => Http::response(['ok' => true, 'result' => ['message_id' => 1001]], 200),
        ]);

        $router = Router::create([
            'name' => 'Router-Uji-Coba',
            'code' => 'RTR-01',
            'ip_address' => '10.10.10.1',
            'status' => 'active',
        ]);

        $driver = Mockery::mock(MikroTikDriver::class);
        $alertEngine = new AlertEngine();
        $sessionStore = Mockery::mock(OnlineSessionStore::class);
        $telegram = new TelegramService();

        $collector = new RouterCollector($driver, $alertEngine, $sessionStore, $telegram);

        // 1. SIMULASI ROUTER OFFLINE (Ping Gagal)
        $driver->shouldReceive('ping')->with(Mockery::on(fn($r) => $r->id === $router->id))->andReturn(false);

        $collector->collect();

        // Assert alarm created in database
        $this->assertDatabaseHas('alarms', [
            'source_type' => Router::class,
            'source_id' => $router->id,
            'title' => 'Router is offline',
            'status' => 'open',
        ]);

        // Assert Telegram offline alert sent
        Http::assertSent(function ($request) {
            $body = $request->data();
            return str_contains($body['text'], 'GANGGUAN (OFFLINE / DOWN)')
                && str_contains($body['text'], 'Router-Uji-Coba');
        });

        // 2. SIMULASI ROUTER KEMBALI ONLINE (Ping Berhasil)
        $driver = Mockery::mock(MikroTikDriver::class);
        $driver->shouldReceive('ping')->with(Mockery::on(fn($r) => $r->id === $router->id))->andReturn(true);
        $driver->shouldReceive('getSystemInfo')->andReturn(['identity' => 'Router-Uji-Coba', 'version' => '7.12', 'cpu_load' => 10, 'free_memory' => 50000000, 'total_memory' => 100000000, 'uptime' => '1d']);
        $driver->shouldReceive('getPPPActive')->andReturn([]);
        $driver->shouldReceive('getHotspotActive')->andReturn([]);
        $driver->shouldReceive('getQueueStats')->andReturn([]);
        $driver->shouldReceive('getInterfaceStats')->andReturn([]);

        $sessionStore = Mockery::mock(OnlineSessionStore::class);
        $sessionStore->shouldReceive('upsertPppoeFromRouter')->andReturnNull();
        $sessionStore->shouldReceive('upsertHotspotFromRouter')->andReturnNull();

        $collector = new RouterCollector($driver, $alertEngine, $sessionStore, $telegram);
        $collector->collect();

        // Assert alarm resolved in database
        $this->assertDatabaseHas('alarms', [
            'source_type' => Router::class,
            'source_id' => $router->id,
            'title' => 'Router is offline',
            'status' => 'resolved',
        ]);

        // Assert Telegram recovery alert sent
        Http::assertSent(function ($request) {
            $body = $request->data();
            return str_contains($body['text'], 'PULIH (ONLINE / UP)')
                && str_contains($body['text'], 'Router-Uji-Coba');
        });
    }
}
