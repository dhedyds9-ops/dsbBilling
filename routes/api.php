<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
// use App\Http\Controllers\Api\Customer\DashboardController;
// use App\Http\Controllers\Api\Customer\BillingController;
// use App\Http\Controllers\Api\Customer\SupportController;
// use App\Http\Controllers\Api\Customer\ProfileController;
// use App\Http\Controllers\Api\Customer\SelfServiceController;
// use App\Http\Controllers\Api\Mobile\WorkOrderController;
// use App\Http\Controllers\Api\Mobile\GPSController;

// // Customer API Routes
// Route::middleware(['auth:sanctum'])->prefix('customer')->group(function () {
//     Route::get('/dashboard', [DashboardController::class, 'index']);
//     Route::prefix('billing')->group(function () {
//         Route::get('/invoices', [BillingController::class, 'invoices']);
//         Route::get('/invoices/{id}', [BillingController::class, 'invoiceDetails']);
//     });
//     Route::prefix('support')->group(function () {
//         Route::get('/tickets', [SupportController::class, 'tickets']);
//         Route::get('/tickets/{id}', [SupportController::class, 'ticketDetails']);
//         Route::post('/tickets', [SupportController::class, 'storeTicket']);
//     });
//     Route::prefix('profile')->group(function () {
//         Route::get('/', [ProfileController::class, 'index']);
//         Route::put('/', [ProfileController::class, 'update']);
//         Route::post('/change-password', [ProfileController::class, 'changePassword']);
//     });
//     Route::prefix('self-service')->group(function () {
//         Route::post('/change-pppoe-password', [SelfServiceController::class, 'changePppoePassword']);
//     });
// });

// // Mobile Workforce API Routes
// Route::middleware(['auth:sanctum'])->prefix('mobile/workforce')->group(function () {
//     Route::prefix('work-orders')->group(function () {
//         Route::get('/', [WorkOrderController::class, 'index']);
//         Route::get('/{id}', [WorkOrderController::class, 'show']);
//         Route::post('/{id}/start', [WorkOrderController::class, 'start']);
//         Route::post('/{id}/complete', [WorkOrderController::class, 'complete']);
//         Route::post('/{id}/checklists', [WorkOrderController::class, 'addChecklist']);
//         Route::post('/{id}/checklists/{checklistId}/complete', [WorkOrderController::class, 'completeChecklist']);
//         Route::post('/{id}/materials', [WorkOrderController::class, 'addMaterialUsage']);
//     });
//     Route::prefix('gps')->group(function () {
//         Route::post('/log', [GPSController::class, 'logLocation']);
//         Route::get('/last-location', [GPSController::class, 'getLastLocation']);
//     });
//     Route::post('/check-in', [GPSController::class, 'checkIn']);
//     Route::post('/check-out', [GPSController::class, 'checkOut']);
// });

Route::middleware(['auth:sanctum'])->get('/user', function (Request $request) {
    return $request->user();
});
