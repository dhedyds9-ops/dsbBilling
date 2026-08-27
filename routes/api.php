<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

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

// ========================= WHATSAPP WEBHOOKS (NO AUTH — signature-based security) ========================
Route::prefix('whatsapp')->name('whatsapp.')->group(function () {
    Route::post('/webhook/{driver}/{sigToken}', \App\Http\Controllers\Api\WhatsApp\WhatsAppWebhookController::class)
        ->name('webhook')
        ->middleware('throttle:180,1');
    Route::get('/health/{driver}/{sigToken}', [\App\Http\Controllers\Api\WhatsApp\WhatsAppWebhookController::class, 'health'])
        ->name('health')
        ->middleware('throttle:60,1');
});

Route::prefix('v1')->group(function () {
    Route::prefix('radius')->name('radius.')->middleware(['radius.nas_secret'])->group(function () {
        Route::post('/accounting', [
            \App\Http\Controllers\Api\Radius\AccountingController::class, 'ingest',
        ])->name('accounting.ingest')->middleware('throttle:radius.accounting');

        Route::post('/preauth', [
            \App\Http\Controllers\Api\Radius\AccountingController::class, 'preAuth',
        ])->name('accounting.preauth')->middleware('throttle:radius.preauth');

        Route::post('/authorize', [
            \App\Http\Controllers\Api\Radius\AccountingController::class, 'authorizeOnly',
        ])->name('accounting.authorize')->middleware('throttle:radius.preauth');

        Route::get('/accounting/health', [
            \App\Http\Controllers\Api\Radius\AccountingController::class, 'health',
        ])->name('accounting.health');
    });

    // =============================== ISP FIBER API ======================================
    Route::prefix('isp')->middleware(['auth:sanctum', 'ability:isp:operate'])->name('isp.')->group(function () {
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
