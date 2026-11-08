<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\GenieAcsWebhookController;

// ========================= PAYMENT WEBHOOKS (NO AUTH — signature-based security) ========================
Route::prefix('payment')->name('payment.')->group(function () {
    // Midtrans, Xendit, Tripay, Duitku — 3-Layer Security: URL token + gateway signature + dedup hash
    Route::post('/webhook/{driver}/{signatureToken}', \App\Http\Controllers\Api\Payment\PaymentWebhookController::class)
        ->name('webhook')
        ->middleware('throttle:60,1'); // 60 req/minute global anti brute force

    // Moota push notification (bank mutation)
    Route::post('/moota/push', [\App\Http\Controllers\Api\Payment\PaymentWebhookController::class, 'mootaPush'])
        ->name('moota.push')
        ->middleware('throttle:30,1');
});

// ========================= TELEGRAM WEBHOOK ==============================================
Route::post('/telegram/webhook', [\App\Http\Controllers\Api\TelegramWebhookController::class, 'handle'])
    ->name('telegram.webhook');

// ========================= WHATSAPP WEBHOOKS (NO AUTH — signature-based security) ========================
Route::prefix('whatsapp')->name('whatsapp.')->group(function () {
    Route::post('/webhook/{driver?}/{sigToken?}', \App\Http\Controllers\Api\WhatsApp\WhatsAppWebhookController::class)
        ->name('webhook')
        ->middleware('throttle:180,1');
    Route::get('/health/{driver?}/{sigToken?}', [\App\Http\Controllers\Api\WhatsApp\WhatsAppWebhookController::class, 'health'])
        ->name('health')
        ->middleware('throttle:60,1');
});

$radiusRouteGroup = function () {
    Route::post('/accounting', [
        \App\Http\Controllers\Api\Radius\AccountingController::class, 'ingest',
    ])->name('accounting.ingest')->middleware('throttle:radius.accounting');

    Route::post('/preauth', [
        \App\Http\Controllers\Api\Radius\AccountingController::class, 'preAuth',
    ])->name('accounting.preauth')->middleware('throttle:radius.preauth');

    Route::post('/authorize', [
        \App\Http\Controllers\Api\Radius\AccountingController::class, 'authorizeOnly',
    ])->name('accounting.authorize')->middleware('throttle:radius.preauth');

    Route::match(['get', 'post'], '/health', [
        \App\Http\Controllers\Api\Radius\AccountingController::class, 'health',
    ])->name('health');

    Route::match(['get', 'post'], '/accounting/health', [
        \App\Http\Controllers\Api\Radius\AccountingController::class, 'health',
    ])->name('accounting.health');
};

Route::prefix('v1/radius')->name('radius.v1.')->middleware(['radius.nas_secret'])->group($radiusRouteGroup);
Route::prefix('radius')->name('radius.')->middleware(['radius.nas_secret'])->group($radiusRouteGroup);

Route::prefix('v1')->group(function () {

    // =============================== HOTSPOT PORTAL API ==================================
    Route::prefix('hotspot')->name('hotspot.')->group(function () {
        Route::match(['GET', 'OPTIONS'], '/packages', [\App\Http\Controllers\Api\Hotspot\HotspotPortalController::class, 'packages'])->name('packages');
        Route::match(['GET', 'OPTIONS'], '/check-user', [\App\Http\Controllers\Api\Hotspot\HotspotPortalController::class, 'checkUser'])->name('check-user');
        Route::match(['GET', 'OPTIONS'], '/check-bill', [\App\Http\Controllers\Api\Hotspot\HotspotPortalController::class, 'checkBill'])->name('check-bill');
        Route::match(['GET', 'POST', 'OPTIONS'], '/kick-user', [\App\Http\Controllers\Api\Hotspot\HotspotPortalController::class, 'kickUser'])->name('kick-user');
        Route::match(['POST', 'OPTIONS'], '/register-lead', [\App\Http\Controllers\Api\Hotspot\HotspotPortalController::class, 'registerLead'])->name('register-lead');
    });

    // Compatibility aliases with legacy MSRadius endpoints
    Route::match(['GET', 'OPTIONS'], '/evoucher', [\App\Http\Controllers\Api\Hotspot\HotspotPortalController::class, 'packages']);
    Route::match(['GET', 'OPTIONS'], '/checkusername', [\App\Http\Controllers\Api\Hotspot\HotspotPortalController::class, 'checkUser']);
    Route::match(['GET', 'POST', 'OPTIONS'], '/optimizeusername', [\App\Http\Controllers\Api\Hotspot\HotspotPortalController::class, 'kickUser']);

    // =============================== ISP FIBER API ======================================
    Route::prefix('isp')->middleware(['auth:sanctum', 'ability:isp:operate'])->name('isp.api.')->group(function () {
        // ------- OLT -------
        Route::prefix('olts')->name('olts.')->group(function () {
            Route::get('/', [\App\Http\Controllers\Api\ISP\OltApiController::class, 'index'])->name('index');
            Route::get('/{id}', [\App\Http\Controllers\Api\ISP\OltApiController::class, 'show'])->where('id', '\d+')->name('show');
            Route::get('/{id}/system-info', [\App\Http\Controllers\Api\ISP\OltApiController::class, 'systemInfo'])->name('system-info');
            Route::get('/{id}/pon-ports', [\App\Http\Controllers\Api\ISP\OltApiController::class, 'ponPorts'])->name('pon-ports');
            Route::get('/{id}/onus', [\App\Http\Controllers\Api\ISP\OltApiController::class, 'onuList'])->name('onus');
            Route::get('/{id}/discover-unregistered', [\App\Http\Controllers\Api\ISP\OltApiController::class, 'discoverUnregistered'])->name('discover-unregistered');
            Route::post('/{id}/execute-command', [\App\Http\Controllers\Api\ISP\OltApiController::class, 'executeCommand'])->name('execute-command');
            Route::post('/{id}/poll', [\App\Http\Controllers\Api\ISP\OltApiController::class, 'poll'])->name('poll');
            Route::post('/{id}/reboot', [\App\Http\Controllers\Api\ISP\OltApiController::class, 'reboot'])->name('reboot');
            Route::post('/{id}/save-config', [\App\Http\Controllers\Api\ISP\OltApiController::class, 'saveConfig'])->name('save-config');
        });
        Route::post('/olts/poll-all', [\App\Http\Controllers\Api\ISP\OltApiController::class, 'pollAll'])->name('olts.poll-all');

        // ------- ONU -------
        Route::prefix('onus')->name('onus.')->group(function () {
            // Remediation Engine (Phase 3 & 4: Capability Discovery & Dry Run)
            Route::post('/{id}/discover', [\App\Http\Controllers\Api\Provisioning\OnuCapabilityController::class, 'discover'])->name('discover');
            Route::get('/{id}/capabilities', [\App\Http\Controllers\Api\Provisioning\OnuCapabilityController::class, 'getCapabilities'])->name('capabilities');
            Route::post('/{id}/remediate/dry-run', [\App\Http\Controllers\Api\Provisioning\OnuCapabilityController::class, 'dryRunRemediation'])->name('remediate.dry-run');
            Route::post('/{id}/remediate', [\App\Http\Controllers\Api\Provisioning\OnuCapabilityController::class, 'remediate'])->name('remediate.execute');
            Route::get('/remediation-jobs/{uuid}', [\App\Http\Controllers\Api\Provisioning\OnuCapabilityController::class, 'getRemediationJob'])->name('remediation.show');
            Route::post('/remediation-jobs/{uuid}/reconcile', [\App\Http\Controllers\Api\Provisioning\OnuRemediationReconciliationController::class, 'reconcile'])->name('remediation.reconcile');

            Route::get('/', [\App\Http\Controllers\Api\ISP\OnuApiController::class, 'index'])->name('index');
            Route::get('/{id}', [\App\Http\Controllers\Api\ISP\OnuApiController::class, 'show'])->where('id', '\d+')->name('show');
            Route::post('/', [\App\Http\Controllers\Api\ISP\OnuApiController::class, 'provision'])->name('provision');
            Route::post('/{id}/status', [\App\Http\Controllers\Api\ISP\OnuApiController::class, 'setStatus'])->name('set-status');
            Route::get('/{id}/signal', [\App\Http\Controllers\Api\ISP\OnuApiController::class, 'signal'])->name('signal');
            Route::get('/{id}/history', [\App\Http\Controllers\Api\ISP\OnuApiController::class, 'history'])->name('signal-history');
            Route::post('/{id}/wifi', [\App\Http\Controllers\Api\ISP\OnuApiController::class, 'wifi'])->name('wifi');
            Route::post('/{id}/reboot', [\App\Http\Controllers\Api\ISP\OnuApiController::class, 'reboot'])->name('reboot');
            Route::post('/{id}/factory-reset', [\App\Http\Controllers\Api\ISP\OnuApiController::class, 'factoryReset'])->name('factory-reset');
            Route::post('/{id}/bandwidth', [\App\Http\Controllers\Api\ISP\OnuApiController::class, 'setBandwidth'])->name('set-bandwidth');
        });

        // ------- ODP -------
        Route::prefix('odps')->name('odps.')->group(function () {
            Route::get('/', [\App\Http\Controllers\Api\ISP\OdpApiController::class, 'index'])->name('index');
            Route::get('/problematic', [\App\Http\Controllers\Api\ISP\OdpApiController::class, 'problematic'])->name('problematic');
            Route::get('/geojson', [\App\Http\Controllers\Api\ISP\OdpApiController::class, 'geojson'])->name('geojson');
            Route::get('/{id}', [\App\Http\Controllers\Api\ISP\OdpApiController::class, 'show'])->where('id', '\d+')->name('show');
            Route::post('/{id}/recalculate', [\App\Http\Controllers\Api\ISP\OdpApiController::class, 'recalculate'])->name('recalculate');
            Route::post('/{id}/geocode', [\App\Http\Controllers\Api\ISP\OdpApiController::class, 'geocode'])->name('geocode');
        });
        Route::post('/odps/recalculate-all', [\App\Http\Controllers\Api\ISP\OdpApiController::class, 'recalculateAll'])->name('odps.recalculate-all');

        // ------- GenieACS / TR-069 -------
        Route::prefix('genieacs')->name('genieacs.')->group(function () {
            Route::get('/devices', [\App\Http\Controllers\Api\ISP\GenieAcsApiController::class, 'listDevices'])->name('list-devices');
            Route::get('/device-info', [\App\Http\Controllers\Api\ISP\GenieAcsApiController::class, 'deviceInfo'])->name('device-info');
            Route::post('/set-parameter-values', [\App\Http\Controllers\Api\ISP\GenieAcsApiController::class, 'setParameterValues'])->name('set-parameter-values');
            Route::post('/reboot-device', [\App\Http\Controllers\Api\ISP\GenieAcsApiController::class, 'rebootDevice'])->name('reboot-device');
            Route::post('/factory-reset', [\App\Http\Controllers\Api\ISP\GenieAcsApiController::class, 'factoryReset'])->name('factory-reset');
            Route::get('/tasks', [\App\Http\Controllers\Api\ISP\GenieAcsApiController::class, 'tasks'])->name('tasks');
            Route::get('/provisions', [\App\Http\Controllers\Api\ISP\GenieAcsApiController::class, 'provisionsList'])->name('provisions-list');
            Route::post('/publish-provisions', [\App\Http\Controllers\Api\ISP\GenieAcsApiController::class, 'publishProvisions'])->name('publish-provisions');
            Route::get('/customer-chain/{customerId}', [\App\Http\Controllers\Api\ISP\GenieAcsApiController::class, 'customerChain'])->where('customerId', '\d+')->name('customer-chain');
            Route::get('/diagnose-cs/{customerServiceId}', [\App\Http\Controllers\Api\ISP\GenieAcsApiController::class, 'customerServiceDiagnose'])->where('customerServiceId', '\d+')->name('diagnose-customer-service');
        });
    });
});

Route::middleware(['auth:sanctum'])->get('/user', function (Request $request) {
    return $request->user();
});

Route::post('/webhooks/genieacs', [GenieAcsWebhookController::class, 'handleEvent'])->name('api.webhooks.genieacs');
Route::post('/webhooks/genieacs/inform', [\App\Http\Controllers\Api\GenieAcs\GenieAcsInformWebhookController::class, 'handle'])
    ->name('api.webhooks.genieacs.inform')
    ->middleware('throttle:600,1');

Route::match(['get', 'post'], '/v1/provision/router', [\App\Http\Controllers\Api\Provisioning\RouterBootstrapController::class, 'bootstrap'])->name('api.provision.router');
Route::match(['get', 'post'], '/v1/router-provision/bootstrap', [\App\Http\Controllers\Api\Provisioning\RouterBootstrapController::class, 'bootstrap'])->name('router-provision.bootstrap');
Route::post('/v1/customer-services/{id}/provision', [\App\Http\Controllers\Api\Provisioning\CustomerServiceProvisionController::class, 'provision'])
    ->middleware(['auth:sanctum'])
    ->name('api.customer-services.provision');

// ========================= MSTORE EMPLOYEE SYNC API ========================
Route::post('/v1/employees/sync', [\App\Http\Controllers\Api\EmployeeSyncApiController::class, 'sync'])
    ->name('api.employees.sync');
Route::post('/employees/sync', [\App\Http\Controllers\Api\EmployeeSyncApiController::class, 'sync']);

// ========================= DSBILLING STAFF MOBILE API ========================
Route::prefix('mobile')->name('api.mobile.')->group(function () {
    Route::post('/login', [\App\Http\Controllers\Api\Mobile\MobileAuthController::class, 'login'])->name('login');
    Route::post('/auth/login', [\App\Http\Controllers\Api\Mobile\MobileAuthController::class, 'login'])->name('auth.login');
    Route::get('/app/check-update', [\App\Http\Controllers\Api\Mobile\MobileAppApiController::class, 'checkUpdate'])->name('app.check-update');
    Route::get('/version/check', [\App\Http\Controllers\Api\Mobile\MobileAppApiController::class, 'checkUpdate']);

    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/logout', [\App\Http\Controllers\Api\Mobile\MobileAuthController::class, 'logout'])->name('logout');
        Route::get('/me', [\App\Http\Controllers\Api\Mobile\MobileAuthController::class, 'me'])->name('me');

        // Workforce (SPK) - Phase 2A Operational Workflow
        Route::prefix('workforce')->name('workforce.')->group(function () {
            Route::get('/work-orders', [\App\Http\Controllers\Api\Mobile\MobileWorkforceApiController::class, 'workOrders'])->name('work-orders');
            Route::get('/work-orders/{id}', [\App\Http\Controllers\Api\Mobile\MobileWorkforceApiController::class, 'show'])->where('id', '\d+')->name('work-orders.show');
            Route::post('/work-orders/{id}/assign', [\App\Http\Controllers\Api\Mobile\MobileWorkforceApiController::class, 'assign'])->where('id', '\d+')->name('work-orders.assign');
            Route::post('/work-orders/{id}/status', [\App\Http\Controllers\Api\Mobile\MobileWorkforceApiController::class, 'updateStatus'])->where('id', '\d+')->name('work-orders.status');
            Route::post('/work-orders/{id}/start', [\App\Http\Controllers\Api\Mobile\MobileWorkforceApiController::class, 'startWorkOrder'])->where('id', '\d+')->name('work-orders.start');
            Route::post('/work-orders/{id}/complete', [\App\Http\Controllers\Api\Mobile\MobileWorkforceApiController::class, 'completeWorkOrder'])->where('id', '\d+')->name('work-orders.complete');
            Route::post('/work-orders/{id}/materials', [\App\Http\Controllers\Api\Mobile\MobileWorkforceApiController::class, 'addMaterialUsage'])->where('id', '\d+')->name('work-orders.materials');
            // Phase 2C Field Service & Work Evidence
            Route::post('/work-orders/{id}/checklists', [\App\Http\Controllers\Api\Mobile\MobileWorkforceApiController::class, 'addChecklist'])->where('id', '\d+')->name('work-orders.checklists.add');
            Route::post('/work-orders/{id}/checklists/{checklistId}/toggle', [\App\Http\Controllers\Api\Mobile\MobileWorkforceApiController::class, 'toggleChecklist'])->where('id', '\d+')->where('checklistId', '\d+')->name('work-orders.checklists.toggle');
            Route::post('/work-orders/{id}/measurements', [\App\Http\Controllers\Api\Mobile\MobileWorkforceApiController::class, 'saveMeasurements'])->where('id', '\d+')->name('work-orders.measurements');
            Route::post('/work-orders/{id}/evidence', [\App\Http\Controllers\Api\Mobile\MobileWorkforceApiController::class, 'uploadEvidence'])->where('id', '\d+')->name('work-orders.evidence');
            Route::post('/work-orders/{id}/sign', [\App\Http\Controllers\Api\Mobile\MobileWorkforceApiController::class, 'saveSignature'])->where('id', '\d+')->name('work-orders.sign');
            Route::get('/technicians', [\App\Http\Controllers\Api\Mobile\MobileWorkforceApiController::class, 'technicians'])->name('technicians');
            Route::post('/gps/log', [\App\Http\Controllers\Api\Mobile\MobileWorkforceApiController::class, 'logGPS'])->name('gps.log');
        });

        // Direct aliases for /api/mobile/work-orders
        Route::prefix('work-orders')->name('work-orders.')->group(function () {
            Route::get('/', [\App\Http\Controllers\Api\Mobile\MobileWorkforceApiController::class, 'workOrders']);
            Route::get('/{id}', [\App\Http\Controllers\Api\Mobile\MobileWorkforceApiController::class, 'show'])->where('id', '\d+');
            Route::post('/{id}/assign', [\App\Http\Controllers\Api\Mobile\MobileWorkforceApiController::class, 'assign'])->where('id', '\d+');
            Route::post('/{id}/status', [\App\Http\Controllers\Api\Mobile\MobileWorkforceApiController::class, 'updateStatus'])->where('id', '\d+');
            Route::post('/{id}/start', [\App\Http\Controllers\Api\Mobile\MobileWorkforceApiController::class, 'startWorkOrder'])->where('id', '\d+');
            Route::post('/{id}/complete', [\App\Http\Controllers\Api\Mobile\MobileWorkforceApiController::class, 'completeWorkOrder'])->where('id', '\d+');
            Route::post('/{id}/materials', [\App\Http\Controllers\Api\Mobile\MobileWorkforceApiController::class, 'addMaterialUsage'])->where('id', '\d+');
            Route::post('/{id}/checklists', [\App\Http\Controllers\Api\Mobile\MobileWorkforceApiController::class, 'addChecklist'])->where('id', '\d+');
            Route::post('/{id}/checklists/{checklistId}/toggle', [\App\Http\Controllers\Api\Mobile\MobileWorkforceApiController::class, 'toggleChecklist'])->where('id', '\d+')->where('checklistId', '\d+');
            Route::post('/{id}/measurements', [\App\Http\Controllers\Api\Mobile\MobileWorkforceApiController::class, 'saveMeasurements'])->where('id', '\d+');
            Route::post('/{id}/evidence', [\App\Http\Controllers\Api\Mobile\MobileWorkforceApiController::class, 'uploadEvidence'])->where('id', '\d+');
            Route::post('/{id}/sign', [\App\Http\Controllers\Api\Mobile\MobileWorkforceApiController::class, 'saveSignature'])->where('id', '\d+');
        });

        // Inventory & Material Management (Phase 2B)
        Route::prefix('inventory')->name('inventory.')->group(function () {
            Route::get('/my-stock', [\App\Http\Controllers\Api\Mobile\MobileInventoryApiController::class, 'myStock'])->name('my-stock');
            Route::get('/items', [\App\Http\Controllers\Api\Mobile\MobileInventoryApiController::class, 'items'])->name('items');
            Route::post('/requisitions', [\App\Http\Controllers\Api\Mobile\MobileInventoryApiController::class, 'createRequisition'])->name('requisitions.create');
            Route::get('/requisitions', [\App\Http\Controllers\Api\Mobile\MobileInventoryApiController::class, 'requisitions'])->name('requisitions.index');
            Route::post('/requisitions/{id}/approve', [\App\Http\Controllers\Api\Mobile\MobileInventoryApiController::class, 'approveRequisition'])->where('id', '\d+')->name('requisitions.approve');
            Route::post('/custody/transfer', [\App\Http\Controllers\Api\Mobile\MobileInventoryApiController::class, 'transferCustody'])->name('custody.transfer');
            Route::post('/returns', [\App\Http\Controllers\Api\Mobile\MobileInventoryApiController::class, 'returnItems'])->name('returns.create');
        });

        // Device & Network Operations (Phase 2D)
        Route::prefix('network')->name('network.')->group(function () {
            Route::get('/devices', [\App\Http\Controllers\Api\Mobile\MobileNetworkApiController::class, 'devices'])->name('devices.index');
            Route::get('/devices/{identifier}', [\App\Http\Controllers\Api\Mobile\MobileNetworkApiController::class, 'showDevice'])->name('devices.show');
            Route::post('/devices/provision-ont', [\App\Http\Controllers\Api\Mobile\MobileNetworkApiController::class, 'provisionOnt'])->name('devices.provision');
            Route::get('/devices/{serialNumber}/diagnostics', [\App\Http\Controllers\Api\Mobile\MobileNetworkApiController::class, 'diagnostics'])->name('devices.diagnostics');
            Route::get('/odps', [\App\Http\Controllers\Api\Mobile\MobileNetworkApiController::class, 'odps'])->name('odps.index');
        });

        // Offline Sync & Notifications (Phase 2E)
        Route::prefix('sync')->name('sync.')->group(function () {
            Route::get('/bootstrap', [\App\Http\Controllers\Api\Mobile\MobileSyncApiController::class, 'bootstrap'])->name('bootstrap');
            Route::post('/offline-batch', [\App\Http\Controllers\Api\Mobile\MobileSyncApiController::class, 'syncBatch'])->name('offline-batch');
        });

        Route::prefix('notifications')->name('notifications.')->group(function () {
            Route::get('/', [\App\Http\Controllers\Api\Mobile\MobileSyncApiController::class, 'notifications'])->name('index');
            Route::post('/{id}/read', [\App\Http\Controllers\Api\Mobile\MobileSyncApiController::class, 'markAsRead'])->where('id', '\d+')->name('read');
            Route::post('/mark-all-read', [\App\Http\Controllers\Api\Mobile\MobileSyncApiController::class, 'markAllAsRead'])->name('mark-all-read');
        });

        // Operational Reports & History (Phase 2F)
        Route::prefix('reports')->name('reports.')->group(function () {
            Route::get('/technician-performance', [\App\Http\Controllers\Api\Mobile\MobileReportsApiController::class, 'technicianPerformance'])->name('technician-performance');
            Route::get('/material-consumption', [\App\Http\Controllers\Api\Mobile\MobileReportsApiController::class, 'materialConsumption'])->name('material-consumption');
            Route::get('/activity-logs', [\App\Http\Controllers\Api\Mobile\MobileReportsApiController::class, 'activityLogs'])->name('activity-logs');
        });

        // ACS TR-069 Proxy (Replaces direct NBI access)
        Route::prefix('acs')->name('acs.')->group(function () {
            Route::get('/devices', [\App\Http\Controllers\Api\Mobile\MobileAcsProxyController::class, 'listDevices'])->name('devices.index');
            Route::get('/devices/{id}', [\App\Http\Controllers\Api\Mobile\MobileAcsProxyController::class, 'deviceDetail'])->name('devices.show');
            Route::post('/devices/{id}/summon', [\App\Http\Controllers\Api\Mobile\MobileAcsProxyController::class, 'summon'])->name('devices.summon');
            Route::post('/devices/{id}/reboot', [\App\Http\Controllers\Api\Mobile\MobileAcsProxyController::class, 'reboot'])->name('devices.reboot');
            Route::post('/devices/{id}/wifi', [\App\Http\Controllers\Api\Mobile\MobileAcsProxyController::class, 'setWifi'])->name('devices.wifi');
            Route::post('/devices/{id}/wan', [\App\Http\Controllers\Api\Mobile\MobileAcsProxyController::class, 'setWan'])->name('devices.wan');
            Route::post('/devices/{id}/admin-account', [\App\Http\Controllers\Api\Mobile\MobileAcsProxyController::class, 'setAdminAccount'])->name('devices.admin-account');
            Route::delete('/devices/{id}/tasks', [\App\Http\Controllers\Api\Mobile\MobileAcsProxyController::class, 'clearTasks'])->name('devices.tasks.clear');
        });

        // AI Voice Proxy
        Route::prefix('ai')->name('ai.')->group(function () {
            Route::post('/ask', [\App\Http\Controllers\Api\Mobile\MobileAiProxyController::class, 'ask'])->name('ask');
        });
    });
});



