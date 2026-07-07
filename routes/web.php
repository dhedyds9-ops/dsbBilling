<?php

use App\Http\Controllers\ProfileController;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
})->name('home');

Route::get('/login-as-admin', function () {
    if (! app()->isLocal()) {
        abort(404);
    }

    $user = User::where('email', 'admin@example.com')->first();
    if (! $user) {
        abort(404);
    }

    Auth::login($user);
    return redirect()->route('isp.service-profiles.create');
})->name('login-as-admin');

Route::middleware(['auth'])->group(function () {
    Route::get('/test', \App\Livewire\Test::class)->name('test');
    Route::get('/test-counter', \App\Livewire\TestCounter::class)->name('test-counter');
    Route::get('/dashboard', \App\Livewire\Dashboard\Index::class)->name('dashboard');

    // AAA - PPPoE Users
    Route::prefix('aaa/pppoe-users')->name('aaa.pppoe-users.')->group(function () {
        Route::get('/', \App\Livewire\AAA\PppoeUser\Index::class)->name('index');
        Route::get('/create', \App\Livewire\AAA\PppoeUser\Create::class)->name('create');
        Route::get('/{id}/edit', \App\Livewire\AAA\PppoeUser\Edit::class)->name('edit');
        Route::get('/{id}', \App\Livewire\AAA\PppoeUser\Show::class)->name('show');
    });

    // AAA - Hotspot Users
    Route::prefix('aaa/hotspot-users')->name('aaa.hotspot-users.')->group(function () {
        Route::get('/', \App\Livewire\AAA\HotspotUser\Index::class)->name('index');
        Route::get('/create', \App\Livewire\AAA\HotspotUser\Create::class)->name('create');
        Route::get('/{id}/edit', \App\Livewire\AAA\HotspotUser\Edit::class)->name('edit');
        Route::get('/{id}', \App\Livewire\AAA\HotspotUser\Show::class)->name('show');
    });

    // AAA - Vouchers
    Route::prefix('aaa/vouchers')->name('aaa.vouchers.')->group(function () {
        Route::get('/', \App\Livewire\AAA\Voucher\Index::class)->name('index');
        Route::get('/create', \App\Livewire\AAA\Voucher\Create::class)->name('create');
        Route::get('/{id}/edit', \App\Livewire\AAA\Voucher\Edit::class)->name('edit');
        Route::get('/{id}', \App\Livewire\AAA\Voucher\Show::class)->name('show');
    });

    // CRM - Leads
    Route::prefix('crm/leads')->name('crm.leads.')->group(function () {
        Route::get('/', \App\Livewire\Crm\Lead\Index::class)->name('index');
        Route::get('/create', \App\Livewire\Crm\Lead\Create::class)->name('create');
        Route::get('/{id}/edit', \App\Livewire\Crm\Lead\Edit::class)->name('edit');
        Route::get('/{id}', \App\Livewire\Crm\Lead\Show::class)->name('show');
    });

    // CRM - Surveys
    Route::prefix('crm/surveys')->name('crm.surveys.')->group(function () {
        Route::get('/', \App\Livewire\Crm\Survey\Index::class)->name('index');
        Route::get('/create', \App\Livewire\Crm\Survey\Create::class)->name('create');
        Route::get('/{id}/edit', \App\Livewire\Crm\Survey\Edit::class)->name('edit');
        Route::get('/{id}', \App\Livewire\Crm\Survey\Show::class)->name('show');
    });

    // CRM - Quotations
    Route::prefix('crm/quotations')->name('crm.quotations.')->group(function () {
        Route::get('/', \App\Livewire\Crm\Quotation\Index::class)->name('index');
        Route::get('/create', \App\Livewire\Crm\Quotation\Create::class)->name('create');
        Route::get('/{id}/edit', \App\Livewire\Crm\Quotation\Edit::class)->name('edit');
        Route::get('/{id}', \App\Livewire\Crm\Quotation\Show::class)->name('show');
    });

    // CRM - Contracts
    Route::prefix('crm/contracts')->name('crm.contracts.')->group(function () {
        Route::get('/', \App\Livewire\Crm\Contract\Index::class)->name('index');
        Route::get('/create', \App\Livewire\Crm\Contract\Create::class)->name('create');
        Route::get('/{id}/edit', \App\Livewire\Crm\Contract\Edit::class)->name('edit');
        Route::get('/{id}', \App\Livewire\Crm\Contract\Show::class)->name('show');
    });

    // CRM - Installations
    Route::prefix('crm/installations')->name('crm.installations.')->group(function () {
        Route::get('/', \App\Livewire\Crm\Installation\Index::class)->name('index');
        Route::get('/create', \App\Livewire\Crm\Installation\Create::class)->name('create');
        Route::get('/{id}/edit', \App\Livewire\Crm\Installation\Edit::class)->name('edit');
        Route::get('/{id}', \App\Livewire\Crm\Installation\Show::class)->name('show');
    });

    // CRM - Activations
    Route::prefix('crm/activations')->name('crm.activations.')->group(function () {
        Route::get('/', \App\Livewire\Crm\Activation\Index::class)->name('index');
        Route::get('/create', \App\Livewire\Crm\Activation\Create::class)->name('create');
        Route::get('/{id}/edit', \App\Livewire\Crm\Activation\Edit::class)->name('edit');
        Route::get('/{id}', \App\Livewire\Crm\Activation\Show::class)->name('show');
    });

    // CRM - Customers
    Route::prefix('crm/customers')->name('crm.customers.')->group(function () {
        Route::get('/', \App\Livewire\Crm\Customer\Index::class)->name('index');
        Route::get('/create', \App\Livewire\Crm\Customer\Create::class)->name('create');
        Route::get('/{id}/edit', \App\Livewire\Crm\Customer\Edit::class)->name('edit');
        Route::get('/{id}', \App\Livewire\Crm\Customer\Show::class)->name('show');
        Route::get('/{id}/360', \App\Livewire\Crm\Customer\Customer360::class)->name('360');
    });

    // Billing - Invoices
    Route::prefix('billing/invoices')->name('billing.invoices.')->group(function () {
        Route::get('/', \App\Livewire\Billing\Invoice\Index::class)->name('index');
        Route::get('/create', \App\Livewire\Billing\Invoice\Create::class)->name('create');
        Route::get('/{id}/edit', \App\Livewire\Billing\Invoice\Edit::class)->name('edit');
        Route::get('/{id}', \App\Livewire\Billing\Invoice\Show::class)->name('show');
    });

    // Billing - Payments
    Route::prefix('billing/payments')->name('billing.payments.')->group(function () {
        Route::get('/', \App\Livewire\Billing\Payment\Index::class)->name('index');
        Route::get('/create', \App\Livewire\Billing\Payment\Create::class)->name('create');
        Route::get('/{id}/edit', \App\Livewire\Billing\Payment\Edit::class)->name('edit');
        Route::get('/{id}', \App\Livewire\Billing\Payment\Show::class)->name('show');
    });

    // Service Profiles (Paket Internet)
    Route::prefix('isp/service-profiles')->name('isp.service-profiles.')->group(function () {
        Route::get('/', \App\Livewire\ISP\ServiceProfile\Index::class)->name('index');
        Route::get('/create', \App\Livewire\ISP\ServiceProfile\Create::class)->name('create');
        Route::get('/{id}/edit', \App\Livewire\ISP\ServiceProfile\Edit::class)->name('edit');
        Route::get('/{id}', \App\Livewire\ISP\ServiceProfile\Show::class)->name('show');
    });

    // PPPoE Users
    Route::prefix('isp/pppoe-users')->name('isp.pppoe-users.')->group(function () {
        Route::get('/', \App\Livewire\ISP\PppoeUser\Index::class)->name('index');
        Route::get('/create', \App\Livewire\ISP\PppoeUser\Create::class)->name('create');
    });

    // Hotspot Users
    Route::prefix('isp/hotspot-users')->name('isp.hotspot-users.')->group(function () {
        Route::get('/', \App\Livewire\ISP\HotspotUser\Index::class)->name('index');
        Route::get('/create', \App\Livewire\ISP\HotspotUser\Create::class)->name('create');
    });

    // Vouchers
    Route::prefix('isp/vouchers')->name('isp.vouchers.')->group(function () {
        Route::get('/', \App\Livewire\ISP\Voucher\Index::class)->name('index');
        Route::get('/create', \App\Livewire\ISP\Voucher\Create::class)->name('create');
    });

    // Route::resource('members', \App\Http\Controllers\MemberController::class);
    // Route::resource('income-categories', \App\Http\Controllers\IncomeCategoryController::class);
    // Route::resource('expense-categories', \App\Http\Controllers\ExpenseCategoryController::class);
    // Route::resource('cash-accounts', \App\Http\Controllers\CashAccountController::class);
    // Route::resource('member-incomes', \App\Http\Controllers\MemberIncomeController::class);
    // Route::resource('cash-transactions', \App\Http\Controllers\CashTransactionController::class);
    // Route::resource('internet-packages', \App\Http\Controllers\InternetPackageController::class);
    
    // Network Management
    Route::get('/vendors', function () {
        return redirect()->route('isp.vendors.index');
    })->name('vendors.index');

    // Service Profile Management
        // Route::get('/service-profiles', [\App\Http\Controllers\ISP\ServiceProfileController::class, 'index'])->name('service-profiles.index');
        // Route::get('/service-profiles/export/excel', [\App\Http\Controllers\ISP\ServiceProfileController::class, 'exportExcel'])->name('service-profiles.export.excel');
        // Route::get('/service-profiles/export/pdf', [\App\Http\Controllers\ISP\ServiceProfileController::class, 'exportPdf'])->name('service-profiles.export.pdf');
        // Route::get('/service-profiles/export/csv', [\App\Http\Controllers\ISP\ServiceProfileController::class, 'exportCsv'])->name('service-profiles.export.csv');
    // Route::get('expense-sharing', [\App\Http\Controllers\CashTransactionController::class, 'expenseSharingIndex'])->name('expense-sharing.index');
    // Route::get('expense-sharing/create', [\App\Http\Controllers\CashTransactionController::class, 'expenseSharingCreate'])->name('expense-sharing.create');
    // Route::post('expense-sharing', [\App\Http\Controllers\CashTransactionController::class, 'expenseSharingStore'])->name('expense-sharing.store');
    // Route::get('expense-sharing/{id}', [\App\Http\Controllers\CashTransactionController::class, 'expenseSharingShow'])->name('expense-sharing.show');
    // Route::get('expense-sharing/{id}/edit', [\App\Http\Controllers\CashTransactionController::class, 'expenseSharingEdit'])->name('expense-sharing.edit');
    // Route::put('expense-sharing/{id}', [\App\Http\Controllers\CashTransactionController::class, 'expenseSharingUpdate'])->name('expense-sharing.update');
    // Route::delete('expense-sharing/{id}', [\App\Http\Controllers\CashTransactionController::class, 'expenseSharingDestroy'])->name('expense-sharing.destroy');
    
    // Revenue Sharing New
    // Route::get('/revenue-sharing', [\App\Http\Controllers\RevenueSharingBatchController::class, 'index'])->name('revenue-sharing.index');
    // Route::post('/revenue-sharing/generate', [\App\Http\Controllers\RevenueSharingBatchController::class, 'generate'])->name('revenue-sharing.generate');
    // Route::post('/revenue-sharing/{batch}/approve', [\App\Http\Controllers\RevenueSharingBatchController::class, 'approve'])->name('revenue-sharing.approve');
    // Route::post('/revenue-sharing/{batch}/lock', [\App\Http\Controllers\RevenueSharingBatchController::class, 'lock'])->name('revenue-sharing.lock');
    // Route::get('/revenue-sharing/{batch}', [\App\Http\Controllers\RevenueSharingBatchController::class, 'show'])->name('revenue-sharing.show');

    // Reports
    // Route::get('/reports/trial-balance', [\App\Http\Controllers\ReportController::class, 'trialBalance'])->name('reports.trial-balance');
    // Route::get('/reports/profit-loss', [\App\Http\Controllers\ReportController::class, 'profitLoss'])->name('reports.profit-loss');
    
    // Audit Trail
    // Route::get('/audit-logs', [\App\Http\Controllers\AuditLogController::class, 'index'])->name('audit-logs.index');
    
    // User Management
    // Route::resource('users', \App\Http\Controllers\UserController::class);

    // Onboarding Routes (using app/Livewire)
    Route::prefix('onboarding')->group(function () {
        Route::get('/leads', \App\Livewire\Onboarding\LeadIndex::class)->name('onboarding.leads.index');
        Route::get('/leads/create', function () { return view('livewire.onboarding.lead-create'); })->name('onboarding.leads.create');
        
        Route::get('/prospects', \App\Livewire\Onboarding\ProspectIndex::class)->name('onboarding.prospects.index');
        Route::get('/prospects/create', \App\Livewire\Onboarding\ProspectCreate::class)->name('onboarding.prospects.create');
        
        Route::get('/coverage-checks', \App\Livewire\Onboarding\CoverageCheckIndex::class)->name('onboarding.coverage-checks.index');
        Route::get('/surveys', \App\Livewire\Onboarding\SurveyIndex::class)->name('onboarding.surveys.index');
        Route::get('/quotations', \App\Livewire\Onboarding\QuotationIndex::class)->name('onboarding.quotations.index');
        Route::get('/contracts', \App\Livewire\Onboarding\ContractIndex::class)->name('onboarding.contracts.index');
        Route::get('/installations', \App\Livewire\Onboarding\InstallationIndex::class)->name('onboarding.installations.index');
        Route::get('/qc', \App\Livewire\Onboarding\QualityControlIndex::class)->name('onboarding.qc.index');
    });

    // ISP Network Infrastructure Routes
    Route::prefix('isp')->group(function () {
        // Vendors
        Route::prefix('vendors')->name('isp.vendors.')->group(function () {
            Route::get('/', \App\Livewire\ISP\Vendor\Index::class)->name('index');
            Route::get('/create', \App\Livewire\ISP\Vendor\Create::class)->name('create');
            Route::get('/{id}/edit', \App\Livewire\ISP\Vendor\Edit::class)->name('edit');
            Route::get('/{id}', \App\Livewire\ISP\Vendor\Show::class)->name('show');
        });

        // Towers
        Route::prefix('towers')->name('isp.towers.')->group(function () {
            Route::get('/', \App\Livewire\ISP\Tower\Index::class)->name('index');
            Route::get('/create', \App\Livewire\ISP\Tower\Create::class)->name('create');
            Route::get('/{id}/edit', \App\Livewire\ISP\Tower\Edit::class)->name('edit');
            Route::get('/{id}', \App\Livewire\ISP\Tower\Show::class)->name('show');
        });

        // POPs
        Route::prefix('pops')->name('isp.pops.')->group(function () {
            Route::get('/', \App\Livewire\ISP\Pop\Index::class)->name('index');
            Route::get('/create', \App\Livewire\ISP\Pop\Create::class)->name('create');
            Route::get('/{id}/edit', \App\Livewire\ISP\Pop\Edit::class)->name('edit');
            Route::get('/{id}', \App\Livewire\ISP\Pop\Show::class)->name('show');
        });

        // OLTs
        Route::prefix('olts')->name('isp.olts.')->group(function () {
            Route::get('/', \App\Livewire\ISP\Olt\Index::class)->name('index');
            Route::get('/create', \App\Livewire\ISP\Olt\Create::class)->name('create');
            Route::get('/{id}/edit', \App\Livewire\ISP\Olt\Edit::class)->name('edit');
            Route::get('/{id}', \App\Livewire\ISP\Olt\Show::class)->name('show');
        });

        // ODCs
        Route::prefix('odcs')->name('isp.odcs.')->group(function () {
            Route::get('/', \App\Livewire\ISP\Odc\Index::class)->name('index');
            Route::get('/create', \App\Livewire\ISP\Odc\Create::class)->name('create');
            Route::get('/{id}/edit', \App\Livewire\ISP\Odc\Edit::class)->name('edit');
            Route::get('/{id}', \App\Livewire\ISP\Odc\Show::class)->name('show');
        });

        // ODPs
        Route::prefix('odps')->name('isp.odps.')->group(function () {
            Route::get('/', \App\Livewire\ISP\Odp\Index::class)->name('index');
            Route::get('/create', \App\Livewire\ISP\Odp\Create::class)->name('create');
            Route::get('/{id}/edit', \App\Livewire\ISP\Odp\Edit::class)->name('edit');
            Route::get('/{id}', \App\Livewire\ISP\Odp\Show::class)->name('show');
        });

        // ONUs
        Route::prefix('onus')->name('isp.onus.')->group(function () {
            Route::get('/', \App\Livewire\ISP\Onu\Index::class)->name('index');
            Route::get('/create', \App\Livewire\ISP\Onu\Create::class)->name('create');
            Route::get('/{id}/edit', \App\Livewire\ISP\Onu\Edit::class)->name('edit');
            Route::get('/{id}', \App\Livewire\ISP\Onu\Show::class)->name('show');
        });
        
        // Routers
        Route::prefix('routers')->name('isp.routers.')->group(function () {
            Route::get('/', \App\Livewire\ISP\Router\Index::class)->name('index');
            Route::get('/{router}', \App\Livewire\ISP\Router\Show::class)->name('show');
        });
    });

    // GIS Platform Routes
    Route::prefix('gis')->group(function () {
        // Main GIS Platform
        Route::get('/', \App\Livewire\Gis\GisDashboard::class)->name('gis.index');
        Route::get('/map', \App\Livewire\Gis\GisMap::class)->name('gis.map');
        Route::get('/dashboard', \App\Livewire\Gis\GisDashboard::class)->name('gis.dashboard');
        
        // GIS Components
        Route::get('/alarms', \App\Livewire\Gis\AlarmPanel::class)->name('gis.alarms');
        Route::get('/tickets', \App\Livewire\Gis\TicketPanel::class)->name('gis.tickets');
        Route::get('/analytics', \App\Livewire\Gis\GisAnalytics::class)->name('gis.analytics');
        Route::get('/route-planner', \App\Livewire\Gis\RoutePlanner::class)->name('gis.route-planner');
        Route::get('/heatmap', \App\Livewire\Gis\NetworkHeatmap::class)->name('gis.heatmap');
        
        // Node Management
        Route::get('/nodes/{type}/{id}', \App\Livewire\Gis\NodeDetails::class)->name('gis.nodes.show');
        
        // Search
        Route::get('/search', \App\Livewire\Gis\SearchPanel::class)->name('gis.search');
    });

    // Administration Routes
    Route::prefix('admin/users')->name('admin.users.')->group(function () {
        Route::get('/', \App\Livewire\Admin\User\Index::class)->name('index');
        Route::get('/create', \App\Livewire\Admin\User\Create::class)->name('create');
        Route::get('/{id}/edit', \App\Livewire\Admin\User\Edit::class)->name('edit');
        Route::get('/{id}', \App\Livewire\Admin\User\Show::class)->name('show');
    });

    Route::prefix('admin/audit-trail')->name('admin.audit-trail.')->group(function () {
        Route::get('/', \App\Livewire\Admin\AuditTrail\Index::class)->name('index');
    });

    // Settings Route
    Route::prefix('admin/settings')->name('admin.settings.')->group(function () {
        Route::get('/', \App\Livewire\Admin\Settings\Index::class)->name('index');
    });
    
    // Direct Settings Route
    Route::get('/settings', function () {
        return redirect()->route('admin.settings.index');
    })->name('settings');

    // Inventory Routes
    Route::prefix('inventory/assets')->name('inventory.assets.')->group(function () {
        Route::get('/', \App\Livewire\Inventory\AssetList::class)->name('index');
    });

    // NOC Routes
    Route::prefix('noc/alerts')->name('noc.alerts.')->group(function () {
        Route::get('/', \App\Livewire\NOC\AlertList::class)->name('index');
    });

    // ACS Routes
    Route::prefix('acs')->name('acs.')->group(function () {
        Route::get('/dashboard', \App\Livewire\ACS\Dashboard::class)->name('dashboard');
        
        // Devices
        Route::prefix('devices')->name('devices.')->group(function () {
            Route::get('/', \App\Livewire\ACS\Device\Index::class)->name('index');
            Route::get('/create', \App\Livewire\ACS\Device\Create::class)->name('create');
            Route::get('/{id}/edit', \App\Livewire\ACS\Device\Edit::class)->name('edit');
            Route::get('/{id}', \App\Livewire\ACS\Device\Show::class)->name('show');
        });
        
        // Provisioning
        Route::prefix('provisioning/templates')->name('provisioning.templates.')->group(function () {
            Route::get('/', \App\Livewire\ACS\Provisioning\Template\Index::class)->name('index');
            Route::get('/create', \App\Livewire\ACS\Provisioning\Template\Create::class)->name('create');
            Route::get('/{id}/edit', \App\Livewire\ACS\Provisioning\Template\Edit::class)->name('edit');
        });
        
        // Firmware
        Route::prefix('firmware')->name('firmware.')->group(function () {
            Route::get('/', \App\Livewire\ACS\Firmware\Index::class)->name('index');
            Route::get('/create', \App\Livewire\ACS\Firmware\Create::class)->name('create');
            Route::get('/{id}/edit', \App\Livewire\ACS\Firmware\Edit::class)->name('edit');
        });
        
        // Tasks
        Route::prefix('tasks')->name('tasks.')->group(function () {
            Route::get('/', \App\Livewire\ACS\Task\Index::class)->name('index');
        });
        
        // Alarms
        Route::prefix('alarms')->name('alarms.')->group(function () {
            Route::get('/', \App\Livewire\ACS\Alarm\Index::class)->name('index');
        });
        
        // Logs
        Route::prefix('logs')->name('logs.')->group(function () {
            Route::get('/', \App\Livewire\ACS\Log\Index::class)->name('index');
        });
    });

    // Reports Routes
    Route::prefix('reports/trial-balance')->name('reports.trial-balance.')->group(function () {
        Route::get('/', \App\Livewire\Reports\TrialBalance::class)->name('index');
    });

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
