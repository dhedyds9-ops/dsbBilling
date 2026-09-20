<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

// ==================== PUBLIC ROUTES ====================
Route::get('/', function () {
    $packagesPppoe = \App\Models\ISP\ServiceProfile::where('is_active', true)->whereIn('service_type', ['pppoe', 'PPPoE', 'PPPOE'])->orderBy('price')->take(3)->get();
    $packagesHotspot = \App\Models\ISP\ServiceProfile::where('is_active', true)->whereIn('service_type', ['hotspot', 'Hotspot', 'HOTSPOT'])->orderBy('price')->take(3)->get();
    $packagesVoucher = \App\Models\ISP\ServiceProfile::where('is_active', true)->whereIn('service_type', ['voucher', 'Voucher', 'VOUCHER'])->orderBy('price')->take(4)->get();

    return view('landing', compact('packagesPppoe', 'packagesHotspot', 'packagesVoucher'));
})->name('home');
Route::get('/coming-soon', fn() => view('coming-soon'))->name('coming-soon');

Route::get('/payment', [\App\Http\Controllers\GuestPaymentController::class, 'index'])->name('guest.payment');
Route::post('/payment/checkout', [\App\Http\Controllers\GuestPaymentController::class, 'checkout'])->name('guest.payment.checkout');
Route::post('/payment/reset', [\App\Http\Controllers\GuestPaymentController::class, 'resetSession'])->name('guest.payment.reset');

Route::post('/buy-voucher-guest', function (\Illuminate\Http\Request $request) {
    return redirect()->route('buy-voucher.page', [
        'paket' => $request->input('service_profile_id'),
        'phone' => $request->input('phone'),
    ]);
})->name('buy-voucher-guest');

Route::get('/buy-voucher', \App\Livewire\Guest\BuyVoucher::class)->name('buy-voucher.page');

Route::post('/register-lead', function (\Illuminate\Http\Request $request) {
    $data = $request->validate([
        'name' => 'required|string|max:255',
        'phone' => 'required|string|max:50',
        'address' => 'required|string',
        'package' => 'nullable|string|max:255',
    ]);
    
    \App\Models\CRM\Lead::create([
        'uuid' => \Illuminate\Support\Str::uuid(),
        'name' => $data['name'],
        'phone' => $data['phone'],
        'address' => $data['address'],
        'notes' => 'Pendaftaran paket: ' . ($data['package'] ?? '-'),
        'source' => 'website',
        'status' => 'new',
    ]);

    return back()->with('success', 'Terima kasih, pendaftaran Anda telah kami terima. Tim kami akan segera menghubungi Anda.');
})->name('register.lead');

// Dev Helper (hanya local)
Route::get('/login-as-admin', function () {
    if (! app()->isLocal()) abort(404);
    
    $user = User::where('email', 'admin@example.com')->firstOrFail();
    Auth::login($user);
    return redirect()->route('dashboard');
})->name('login-as-admin');

// ==================== PLACEHOLDER HELPER ====================
$cs = fn() => view('coming-soon');

// ==================== AUTHENTICATED ROUTES ====================
Route::middleware(['auth'])->group(function () use ($cs) {
    Route::get('/technician-portal/dashboard', \App\Livewire\ISP\Technician\Dashboard::class)->name('technician.dashboard');
    Route::middleware(['workforce.checked_in'])->group(function () {
        Route::get('/technician-portal/my-jobs', \App\Livewire\ISP\Technician\MyJobs\Index::class)->name('technician.my-jobs.index');
        Route::get('/technician-portal/my-jobs/{job}', \App\Livewire\ISP\Technician\MyJobs\Show::class)->name('technician.my-jobs.show');
        Route::get('/technician-portal/installation/wizard', \App\Livewire\ISP\Technician\Installation\Wizard::class)->name('technician.installation.wizard');
        Route::get('/technician-portal/provisioning/{id}', \App\Livewire\ISP\Technician\Provisioning\Show::class)->name('technician.provisioning.show');
    });
    Route::get('/technician-portal/attendance', \App\Livewire\ISP\Technician\Attendance\Index::class)->name('technician.attendance');
    Route::get('/technician-portal/payroll', \App\Livewire\ISP\Technician\Payroll\Index::class)->name('technician.payroll.index');
    Route::get('/technician-portal/payroll/{id}', \App\Livewire\ISP\Technician\Payroll\Show::class)->name('technician.payroll.show');
    
    // Fallback/Dummy routes to prevent MenuRegistry crashes
    Route::get('/technician-portal/dummy', fn()=>'dummy')->name('technician.history');
    Route::get('/technician-portal/dummy2', fn()=>'dummy')->name('technician.my-jobs.psb');
    Route::get('/technician-portal/dummy3', fn()=>'dummy')->name('technician.my-jobs.maintenance');
    Route::get('/technician-portal/tickets', \App\Livewire\ISP\Technician\Tickets\Index::class)->name('technician.my-jobs.troubleshooting');
    Route::get('/technician-portal/dummy5', fn()=>'dummy')->name('technician.installation.index');
    Route::get('/technician-portal/dummy6', fn()=>'dummy')->name('technician.installation.scan');
    Route::get('/technician-portal/dummy7', fn()=>'dummy')->name('technician.installation.register');
    Route::get('/technician-portal/dummy8', fn()=>'dummy')->name('technician.installation.provision');
    Route::get('/technician-portal/dummy9', fn()=>'dummy')->name('technician.installation.test');
    Route::get('/technician-portal/dummy10', fn()=>'dummy')->name('technician.installation.docs');
    Route::get('/technician-portal/dummy11', fn()=>'dummy')->name('technician.odp.search');
    Route::get('/technician-portal/dummy12', fn()=>'dummy')->name('technician.odp.nearest');
    Route::get('/technician-portal/dummy13', fn()=>'dummy')->name('technician.odp.ports');
    Route::get('/technician-portal/dummy14', fn()=>'dummy')->name('technician.customers.show');
    Route::get('/technician-portal/dummy15', fn()=>'dummy')->name('technician.customers.status');
    Route::get('/technician-portal/dummy16', fn()=>'dummy')->name('technician.customers.history');



    // ==================== STAFF / ADMIN AREA ====================
    Route::middleware(['role:administrator,manager'])->group(function () use ($cs) {

        // ==================== 1. DASHBOARD (TANPA SUBMENU SESUAI SSOT v2.0) ====================
        Route::get('/dashboard', \App\Livewire\Dashboard\Index::class)->name('dashboard');

        // ==================== ISP CORE CRUD (EXISTING) ====================
        Route::prefix('isp')->name('isp.')->group(function () {
            // Voucher Templates
            Route::get('/voucher-templates', \App\Livewire\ISP\VoucherTemplate\Index::class)->name('voucher-templates.index');
            Route::get('/voucher-templates/create', \App\Livewire\ISP\VoucherTemplate\Editor::class)->name('voucher-templates.create');
            Route::get('/voucher-templates/import', \App\Livewire\ISP\VoucherTemplate\Import::class)->name('voucher-templates.import');
            Route::get('/voucher-templates/{id}/edit', \App\Livewire\ISP\VoucherTemplate\Editor::class)->name('voucher-templates.edit');
            Route::get('/voucher-templates/{id}/preview', \App\Livewire\ISP\VoucherTemplate\Preview::class)->name('voucher-templates.preview');
            Route::get('/voucher-templates/{id}/versions', \App\Livewire\ISP\VoucherTemplate\Versions::class)->name('voucher-templates.versions');

            // Service Profile = Profile PPPoE (referenced dari Profile Paket > Profile PPPoE
            Route::get('/service-profiles', \App\Livewire\ISP\ServiceProfile\Index::class)->name('service-profiles.index');
            Route::get('/service-profiles/create', \App\Livewire\ISP\ServiceProfile\Create::class)->name('service-profiles.create');
            Route::get('/service-profiles/{id}/edit', \App\Livewire\ISP\ServiceProfile\Edit::class)->name('service-profiles.edit');


            // User Online = List Pelanggan > User Online (tabs PPPoE/Hotspot/Voucher + Kick)
            Route::get('/user-online', \App\Livewire\ISP\UserOnline\Index::class)->name('user-online.index');

            // PPPoE = List Pelanggan > User PPPoE
            Route::get('/pppoe-users', \App\Livewire\ISP\PPPoEUser\Index::class)->name('pppoe-users.index');
            Route::get('/pppoe-users/create', \App\Livewire\ISP\PPPoEUser\Create::class)->name('pppoe-users.create');
            Route::get('/pppoe-users/{id}/edit', \App\Livewire\ISP\PPPoEUser\Edit::class)->name('pppoe-users.edit');
            Route::get('/pppoe-users/{id}', \App\Livewire\ISP\PPPoEUser\Show::class)->name('pppoe-users.show');

            // Hotspot = List Pelanggan > User Hotspot
            Route::get('/hotspot-users', \App\Livewire\ISP\HotspotUser\Index::class)->name('hotspot-users.index');
            Route::get('/hotspot-users/create', \App\Livewire\ISP\HotspotUser\Create::class)->name('hotspot-users.create');
            Route::get('/hotspot-users/{id}/edit', \App\Livewire\ISP\HotspotUser\Edit::class)->name('hotspot-users.edit');
            Route::get('/hotspot-users/{id}', \App\Livewire\ISP\HotspotUser\Show::class)->name('hotspot-users.show');

            // Voucher (existing CRUD, List Pelanggan > User Voucher tabs biasa/E-Voucher
            Route::get('/vouchers', \App\Livewire\ISP\Voucher\Index::class)->name('vouchers.index');
            Route::post('/vouchers/print', [\App\Http\Controllers\ISP\VoucherPrintController::class, 'print'])->name('vouchers.print');
            Route::get('/evouchers', \App\Livewire\ISP\EVoucher\Index::class)->name('evouchers.index');

            // Router & NAS = Jaringan > Router & NAS
            Route::get('/routers', \App\Livewire\ISP\Router\Index::class)->name('routers.index');
            Route::get('/routers/create', \App\Livewire\ISP\Router\Create::class)->name('routers.create');
            Route::get('/routers/{id}/edit', \App\Livewire\ISP\Router\Edit::class)->name('routers.edit');
            Route::get('/routers/{id}', \App\Livewire\ISP\Router\Show::class)->name('routers.show');

            // Fiber Infrastructure = Jaringan > Fiber & ONU
            Route::get('/olts', \App\Livewire\ISP\Olt\Index::class)->name('olts.index');
            Route::get('/olts/create', \App\Livewire\ISP\Olt\Create::class)->name('olts.create');
            Route::get('/olts/{id}/edit', \App\Livewire\ISP\Olt\Edit::class)->name('olts.edit');
            Route::get('/olts/{id}', \App\Livewire\ISP\Olt\Show::class)->name('olts.show');

            Route::get('/onus', \App\Livewire\ISP\Onu\Index::class)->name('onus.index');
            Route::get('/onus/create', \App\Livewire\ISP\Onu\Create::class)->name('onus.create');
            Route::get('/onus/{id}/edit', \App\Livewire\ISP\Onu\Edit::class)->name('onus.edit');
            Route::get('/onus/{id}', \App\Livewire\ISP\Onu\Show::class)->name('onus.show');

            Route::get('/odps', \App\Livewire\ISP\Odp\Index::class)->name('odps.index');
            Route::get('/odps/create', \App\Livewire\ISP\Odp\Create::class)->name('odps.create');
            Route::get('/odps/{id}/edit', \App\Livewire\ISP\Odp\Edit::class)->name('odps.edit');
            Route::get('/odps/{id}', \App\Livewire\ISP\Odp\Show::class)->name('odps.show');

            Route::get('/odcs', \App\Livewire\ISP\Odc\Index::class)->name('odcs.index');
            Route::get('/odcs/create', \App\Livewire\ISP\Odc\Create::class)->name('odcs.create');
            Route::get('/odcs/{id}/edit', \App\Livewire\ISP\Odc\Edit::class)->name('odcs.edit');
            Route::get('/odcs/{id}', \App\Livewire\ISP\Odc\Show::class)->name('odcs.show');

            Route::get('/pops', \App\Livewire\ISP\Pop\Index::class)->name('pops.index');
            Route::get('/pops/create', \App\Livewire\ISP\Pop\Create::class)->name('pops.create');
            Route::get('/pops/{id}/edit', \App\Livewire\ISP\Pop\Edit::class)->name('pops.edit');
            Route::get('/pops/{id}', \App\Livewire\ISP\Pop\Show::class)->name('pops.show');

            Route::get('/towers', \App\Livewire\ISP\Tower\Index::class)->name('towers.index');
            Route::get('/towers/create', \App\Livewire\ISP\Tower\Create::class)->name('towers.create');
            Route::get('/towers/{id}/edit', \App\Livewire\ISP\Tower\Edit::class)->name('towers.edit');
            Route::get('/towers/{id}', \App\Livewire\ISP\Tower\Show::class)->name('towers.show');

            Route::get('/vendors', \App\Livewire\ISP\Vendor\Index::class)->name('vendors.index');
            Route::get('/vendors/create', \App\Livewire\ISP\Vendor\Create::class)->name('vendors.create');
            Route::get('/vendors/{id}/edit', \App\Livewire\ISP\Vendor\Edit::class)->name('vendors.edit');
            Route::get('/vendors/{id}', \App\Livewire\ISP\Vendor\Show::class)->name('vendors.show');
        });

        // ==================== 3. LIST PELANGGAN ====================
        Route::prefix('pelanggan')->name('pelanggan.')->group(function () use ($cs) {
            // User Voucher: tabs Aktif | Terpakai | Expired | Semua
            Route::get('/voucher', \App\Livewire\Pelanggan\Voucher\Index::class)->name('voucher');
            // Isolir: daftar user ter-isolir + aktivasi kembali
            Route::get('/isolir', \App\Livewire\Pelanggan\Isolir\Index::class)->name('isolir');
        });



        // === Billing / Semua Tagihan (create: dropdown Tipe Service PPPoE / Hotspot Member) ===
        Route::prefix('billing')->name('billing.')->group(function () {
            Route::get('/periode', \App\Livewire\Billing\PeriodeTagihan\Index::class)->name('periode.index');
            Route::get('/invoices', \App\Livewire\Billing\Invoice\Index::class)->name('invoices.index');
            Route::get('/invoices/create', \App\Livewire\Billing\Invoice\Create::class)->name('invoices.create');
            Route::get('/invoices/{id}/edit', \App\Livewire\Billing\Invoice\Edit::class)->name('invoices.edit');
            Route::get('/invoices/{id}', \App\Livewire\Billing\Invoice\Show::class)->name('invoices.show');

            Route::get('/payments', \App\Livewire\Billing\Payment\Index::class)->name('payments.index');
            Route::get('/payments/create', \App\Livewire\Billing\Payment\Create::class)->name('payments.create');
            Route::get('/payments/{id}/edit', \App\Livewire\Billing\Payment\Edit::class)->name('payments.edit');
            Route::get('/payments/{id}', \App\Livewire\Billing\Payment\Show::class)->name('payments.show');
        });

        // ==================== 5. DATA KEUANGAN ====================
        Route::prefix('keuangan')->name('keuangan.')->group(function () use ($cs) {
            // Topup Reseller: Summary | Riwayat | Approval | Bukti Transfer | Export
            Route::get('/topup-reseller', \App\Livewire\Keuangan\TopupReseller\Index::class)->name('topup-reseller');
            // Income Harian: Chart | Top Customer | Top Sales | Payment Method | Cash Flow
            Route::get('/income-harian', \App\Livewire\Keuangan\IncomeHarian\Index::class)->name('income-harian');
            // Income Periode: Comparison | Growth | Export Excel | Export PDF
            Route::get('/income-periode', \App\Livewire\Keuangan\IncomePeriode\Index::class)->name('income-periode');
            // Pengeluaran: Kategori | Approval | Attachment | Status
            Route::get('/pengeluaran', \App\Livewire\Keuangan\Pengeluaran\Index::class)->name('pengeluaran');
            // Laba Rugi: Income Statement | Cash Flow | Expense | Top Revenue | AR Aging
            Route::get('/laba-rugi', \App\Livewire\Keuangan\LabaRugi\Index::class)->name('laba-rugi');
            // BHP | USO: persentase dari revenue
            Route::get('/bhp-uso', \App\Livewire\Keuangan\BhpUso\Index::class)->name('bhp-uso');
        });

        // ==================== 6. JARINGAN ====================
        Route::prefix('jaringan')->name('jaringan.')->group(function () use ($cs) {
            // Fiber & ONU: tabs Map | ODC | ODP | OLT | ONU | LOS Alarm
            Route::get('/fiber', \App\Livewire\Jaringan\Fiber\Index::class)->name('fiber');
            // Monitoring: Realtime | PPPoE Online | Hotspot Online | Bandwidth | CPU | Memory | Traffic
            Route::get('/monitoring', \App\Livewire\Jaringan\Monitoring\Index::class)->name('monitoring');
        });

        // ==================== 7. SUPPORT ====================
        Route::prefix('support')->name('support.')->group(function () use ($cs) {
            // Tiket Support: Kanban | Table | Timeline | Priority | Assignment
            Route::get('/ticket', \App\Livewire\Support\Ticket\Index::class)->name('ticket');
            // Instalasi: Work Order | Schedule | Technician | Map | Checklist
            Route::get('/installation', \App\Livewire\Support\Installation\Index::class)->name('installation');
            // Maintenance: Calendar | History | Technician | Material
            Route::get('/maintenance', \App\Livewire\Support\Maintenance\Index::class)->name('maintenance');
        });

        // ==================== 8. LAPORAN ====================
        Route::prefix('laporan')->name('laporan.')->group(function () use ($cs) {
            // Pendapatan: Chart | Comparison | Top Package
            Route::get('/pendapatan', \App\Livewire\Laporan\Pendapatan\Index::class)->name('pendapatan');
            // Pelanggan: Customer Growth | Activation | Suspension | Termination
            Route::get('/pelanggan', \App\Livewire\Laporan\Pelanggan\Index::class)->name('pelanggan');
            // Jaringan: Availability | Downtime | LOS | Router Health
            Route::get('/jaringan', \App\Livewire\Laporan\Jaringan\Index::class)->name('jaringan');
        });

        // ==================== 9. PENGATURAN SESUAI SSOT v2.0 ====================
        Route::prefix('pengaturan')->name('pengaturan.')->group(function () use ($cs) {
            // Perusahaan: Konfigurasi nama, alamat, logo perusahaan
            Route::get('/perusahaan', \App\Livewire\Pengaturan\Perusahaan\Index::class)->name('perusahaan');
            // Koneksi Perangkat: tabs Router API | Radius | GenieACS
            Route::get('/koneksi', \App\Livewire\Pengaturan\Koneksi\Index::class)->name('koneksi');
            // Telegram Bot: Bot Token, Webhook, Chat ID Notification
            Route::get('/telegram', \App\Livewire\Pengaturan\Telegram\Index::class)->name('telegram');
            // WhatsApp Gateway: API Key WhatsApp untuk Tagihan & Notifikasi Pelanggan
            Route::get('/whatsapp', \App\Livewire\Pengaturan\WhatsApp\Index::class)->name('whatsapp');
            // Payment Gateway: Midtrans, Xendit, BCA VA, dll
            Route::get('/payment-gateway', \App\Livewire\Pengaturan\PaymentGateway\Index::class)->name('payment-gateway');
        });

        // === GenieACS (BACKEND ONLY —- Koneksi Perangkat
        Route::prefix('acs')->name('acs.')->group(function () use ($cs) {
            Route::get('/dashboard', \App\Livewire\ACS\Dashboard::class)->name('dashboard');
            Route::get('/devices', \App\Livewire\ACS\Device\Index::class)->name('devices.index');
                        Route::get('/devices/{id}/edit', \App\Livewire\ACS\Device\Edit::class)->name('devices.edit');
            Route::get('/devices/{id}', \App\Livewire\ACS\Device\Show::class)->name('devices.show');
            Route::get('/tasks', \App\Livewire\ACS\Task\Index::class)->name('tasks.index');
            Route::get('/alarms', \App\Livewire\ACS\Alarm\Index::class)->name('alarms.index');
            Route::get('/firmware', \App\Livewire\ACS\Firmware\Index::class)->name('firmware.index');
            Route::get('/firmware/create', \App\Livewire\ACS\Firmware\Create::class)->name('firmware.create');
            Route::get('/firmware/{id}/edit', \App\Livewire\ACS\Firmware\Edit::class)->name('firmware.edit');
            Route::get('/settings', \App\Livewire\ACS\Settings::class)->name('settings');
        });

        // === GIS (Peta Pelanggan) ===
        Route::prefix('gis')->name('gis.')->group(function () use ($cs) {
            Route::get('/', \App\Livewire\Gis\GisDashboard::class)->name('index');
            Route::get('/map', \App\Livewire\Gis\GisMap::class)->name('map');
            Route::get('/analytics', \App\Livewire\Gis\GisAnalytics::class)->name('analytics');
            Route::get('/customer-map', fn() => redirect()->route('gis.map'))->name('customer-map');
        });

        // === GIS Map APIs (for the JS frontend) ===
        Route::prefix('map')->group(function () {
            Route::get('/connections', [\App\Http\Controllers\Gis\MapApiController::class, 'getConnections'])->name('map.connections.index');
            Route::post('/connections/save', [\App\Http\Controllers\Gis\MapApiController::class, 'saveConnection'])->name('map.connections.save');
            Route::put('/location/{type}/{id}', [\App\Http\Controllers\Gis\MapApiController::class, 'updateLocation'])->name('map.location.update');
            Route::post('/node/{type}', [\App\Http\Controllers\Gis\MapApiController::class, 'storeNode'])->name('map.node.store');
            Route::put('/node/{type}/{id}', [\App\Http\Controllers\Gis\MapApiController::class, 'updateNode'])->name('map.node.update');
            Route::get('/wlan-status/{id}', [\App\Http\Controllers\Gis\MapApiController::class, 'wlanStatus'])->name('map.wlan-status');
            Route::post('/wlan-update/{id}', [\App\Http\Controllers\Gis\MapApiController::class, 'wlanUpdate'])->name('map.wlan-update');
            Route::post('/ping', [\App\Http\Controllers\Gis\MapApiController::class, 'ping'])->name('map.ping');
        });
        Route::get('/api/network/online-paths', [\App\Http\Controllers\Gis\MapApiController::class, 'onlinePaths'])->name('api.network.online-paths');

        // === Inventory (BACKWARD COMPAT - TIDAK DI SIDEBAR) ===
        Route::prefix('inventory')->name('inventory.')->group(function () {
            Route::get('/assets', \App\Livewire\Inventory\AssetList::class)->name('assets.index');
        });

                // === NOC ===
        Route::prefix('noc')->name('noc.')->group(function () {
            Route::get('/', \App\Livewire\NOC\Overview::class)->name('overview');
            Route::middleware(['workforce.checked_in'])->group(function () {
                Route::get('/alerts', \App\Livewire\NOC\AlertList::class)->name('alerts.index');
                Route::get('/alarms', \App\Livewire\NOC\Alarms\Index::class)->name('alarms.index');
                Route::get('/alarms/{alarm}', \App\Livewire\NOC\Alarms\Show::class)->name('alarms.show');
                Route::get('/provisioning', \App\Livewire\NOC\Provisioning\Index::class)->name('provisioning.index');
                Route::get('/provisioning/{id}', \App\Livewire\NOC\Provisioning\Show::class)->name('provisioning.show');
                Route::get('/routers', \App\Livewire\NOC\Router\Index::class)->name('routers.index');
                Route::get('/routers/{router}', \App\Livewire\NOC\Router\Show::class)->name('routers.show');
                Route::get('/olts', \App\Livewire\NOC\Olt\Index::class)->name('olts.index');
                Route::get('/olts/{olt}', \App\Livewire\NOC\Olt\Show::class)->name('olts.show');
                Route::get('/onus', \App\Livewire\NOC\Onu\Index::class)->name('onus.index');
                Route::get('/onus/{onu}', \App\Livewire\NOC\Onu\Show::class)->name('onus.show');
                Route::get('/pppoe', \App\Livewire\NOC\Pppoe\Index::class)->name('pppoe.index');
                Route::get('/topology', \App\Livewire\NOC\Topology\Index::class)->name('topology.index');
            });
        });

        // === Reports (BACKWARD COMPAT - TIDAK DI SIDEBAR) ===
        Route::prefix('reports')->name('reports.')->group(function () {
            Route::get('/trial-balance', \App\Livewire\Reports\TrialBalance::class)->name('trial-balance.index');
        });

        // === CRM (BACKWARD COMPAT - TIDAK DI SIDEBAR SSOT - HANYA URL LANGSUNG) ===
        Route::prefix('crm')->name('crm.')->group(function () {
            Route::get('/customers', \App\Livewire\Crm\Customer\Index::class)->name('customers.index');
            Route::get('/customers/create', \App\Livewire\Crm\Customer\Create::class)->name('customers.create');
            Route::get('/customers/{id}/edit', \App\Livewire\Crm\Customer\Edit::class)->name('customers.edit');
            Route::get('/customers/{id}', \App\Livewire\Crm\Customer\Customer360::class)->name('customers.show');
            
            Route::get('/leads', \App\Livewire\Crm\Lead\Index::class)->name('leads.index');
            Route::get('/leads/create', \App\Livewire\Crm\Lead\Create::class)->name('leads.create');
            Route::get('/leads/{id}/edit', \App\Livewire\Crm\Lead\Edit::class)->name('leads.edit');
            Route::get('/leads/{id}', \App\Livewire\Crm\Lead\Show::class)->name('leads.show');
            
            Route::get('/surveys', \App\Livewire\Crm\Survey\Index::class)->name('surveys.index');
            Route::get('/surveys/create', \App\Livewire\Crm\Survey\Create::class)->name('surveys.create');
            Route::get('/surveys/{id}/edit', \App\Livewire\Crm\Survey\Edit::class)->name('surveys.edit');
            Route::get('/surveys/{id}', \App\Livewire\Crm\Survey\Show::class)->name('surveys.show');
            
            Route::get('/quotations', \App\Livewire\Crm\Quotation\Index::class)->name('quotations.index');
            Route::get('/quotations/create', \App\Livewire\Crm\Quotation\Create::class)->name('quotations.create');
            Route::get('/quotations/{id}/edit', \App\Livewire\Crm\Quotation\Edit::class)->name('quotations.edit');
            Route::get('/quotations/{id}', \App\Livewire\Crm\Quotation\Show::class)->name('quotations.show');
            
            Route::get('/contracts', \App\Livewire\Crm\Contract\Index::class)->name('contracts.index');
            Route::get('/contracts/create', \App\Livewire\Crm\Contract\Create::class)->name('contracts.create');
            Route::get('/contracts/{id}/edit', \App\Livewire\Crm\Contract\Edit::class)->name('contracts.edit');
            Route::get('/contracts/{id}', \App\Livewire\Crm\Contract\Show::class)->name('contracts.show');
            
            Route::get('/installations', \App\Livewire\Crm\Installation\Index::class)->name('installations.index');
            Route::get('/installations/create', \App\Livewire\Crm\Installation\Create::class)->name('installations.create');
            Route::get('/installations/{id}/edit', \App\Livewire\Crm\Installation\Edit::class)->name('installations.edit');
            Route::get('/installations/{id}', \App\Livewire\Crm\Installation\Show::class)->name('installations.show');
            
            Route::get('/activations', \App\Livewire\Crm\Activation\Index::class)->name('activations.index');
            Route::get('/activations/create', \App\Livewire\Crm\Activation\Create::class)->name('activations.create');
            Route::get('/activations/{id}/edit', \App\Livewire\Crm\Activation\Edit::class)->name('activations.edit');
            Route::get('/activations/{id}', \App\Livewire\Crm\Activation\Show::class)->name('activations.show');
        });

        // === Workflow (BACKWARD COMPAT - TIDAK DI SIDEBAR) ===
        Route::prefix('workflow')->name('workflow.')->group(function () {
            Route::get('/list', \App\Livewire\Workflow\WorkflowList::class)->name('list.index');
        });

        // === Administration (Pengaturan > Pengguna Akses & Sistem) ===
        Route::prefix('admin')->name('admin.')->group(function () {
            Route::get('/users', \App\Livewire\Admin\User\Index::class)->name('users.index');
            Route::get('/users/create', \App\Livewire\Admin\User\Create::class)->name('users.create');
            Route::get('/users/{id}/edit', \App\Livewire\Admin\User\Edit::class)->name('users.edit');
            Route::get('/users/{id}', \App\Livewire\Admin\User\Show::class)->name('users.show');
            
            Route::get('/audit-trail', \App\Livewire\Admin\AuditTrail\Index::class)->name('audit-trail.index');
            Route::get('/settings', \App\Livewire\Admin\Settings\Index::class)->name('settings.index');
            Route::get('/attendance', \App\Livewire\Admin\Attendance\Index::class)->name('attendance.index');
            
            Route::get('/employee', \App\Livewire\Admin\Employee\Index::class)->name('employee.index');
            Route::get('/employee/create', \App\Livewire\Admin\Employee\Create::class)->name('employee.create');
            Route::get('/employee/{id}/edit', \App\Livewire\Admin\Employee\Edit::class)->name('employee.edit');
            
            Route::get('/payroll', \App\Livewire\Admin\Payroll\Index::class)->name('payroll.index');
            Route::get('/payroll/generate', \App\Livewire\Admin\Payroll\Generate::class)->name('payroll.generate');
            Route::get('/payroll/{id}', \App\Livewire\Admin\Payroll\Show::class)->name('payroll.show');
            Route::get('/payroll/{id}/edit', \App\Livewire\Admin\Payroll\Edit::class)->name('payroll.edit');
        });
    });

    // ==================== RESELLER PORTAL ====================
    Route::middleware(['role:reseller'])->prefix('reseller-portal')->name('reseller-portal.')->group(function () {
        Route::get('/dashboard', \App\Livewire\ResellerPortal\Dashboard::class)->name('dashboard');
        
        // Placeholder routes for missing modules
        $comingSoon = \App\Livewire\ResellerPortal\ComingSoon::class;
        
        Route::prefix('customers')->name('customers.')->group(function() use ($comingSoon) {
            Route::get('/', \App\Livewire\ResellerPortal\Customer\Index::class)->name('index');
            Route::get('/create', \App\Livewire\ResellerPortal\Customer\Create::class)->name('create');
            Route::get('/hotspot/create', \App\Livewire\ResellerPortal\Customer\CreateHotspot::class)->name('hotspot.create');
            Route::get('/{id}/edit', \App\Livewire\ResellerPortal\Customer\Edit::class)->name('edit');
            Route::get('/{id}/detail', \App\Livewire\ResellerPortal\Customer\Customer360::class)->name('show');
            Route::get('/pppoe', \App\Livewire\ResellerPortal\Customer\Pppoe::class)->name('pppoe');
            Route::get('/hotspot', \App\Livewire\ResellerPortal\Customer\Hotspot::class)->name('hotspot');
            Route::get('/user-online', \App\Livewire\ResellerPortal\Customer\UserOnline::class)->name('user-online');
            Route::get('/active', \App\Livewire\ResellerPortal\Customer\Active::class)->name('active');
            Route::get('/isolated', \App\Livewire\ResellerPortal\Customer\Isolated::class)->name('isolated');
        });

        Route::prefix('finance')->name('finance.')->group(function() {
            Route::get('/balance', \App\Livewire\ResellerPortal\Finance\Balance::class)->name('balance');
            Route::get('/topup', \App\Livewire\ResellerPortal\Finance\Topup::class)->name('topup');
            Route::get('/mutations', \App\Livewire\ResellerPortal\Finance\Mutations::class)->name('mutations');
            
        });

        Route::prefix('billing')->name('billing.')->group(function() {
            Route::get('/invoices', \App\Livewire\ResellerPortal\Billing\Invoices::class)->name('invoices');
            Route::get('/invoices/{id}', \App\Livewire\ResellerPortal\Billing\InvoiceShow::class)->name('invoices.show');
            Route::get('/payments', \App\Livewire\ResellerPortal\Billing\Payments::class)->name('payments');
        });

        Route::prefix('reports')->name('reports.')->group(function() {
            Route::get('/sales', \App\Livewire\ResellerPortal\Reports\Sales::class)->name('sales');
            Route::get('/sales/print', [\App\Http\Controllers\Reseller\ReportPrintController::class, 'printSales'])->name('sales.print');
        Route::get('/commission/print', [\App\Http\Controllers\Reseller\ReportPrintController::class, 'printCommission'])->name('commission.print');
            Route::get('/revenue', \App\Livewire\ResellerPortal\Reports\Revenue::class)->name('revenue');
            Route::get('/commission', \App\Livewire\ResellerPortal\Reports\Commission::class)->name('commission');
        });

        Route::prefix('sales')->name('sales.')->group(function() {
            Route::get('/voucher', \App\Livewire\ResellerPortal\Sales\VoucherIndex::class)->name('voucher');
            Route::post('/voucher/print', [\App\Http\Controllers\ISP\VoucherPrintController::class, 'print'])->name('voucher.print');
            Route::post('/voucher', [\App\Http\Controllers\ISP\VoucherPrintController::class, 'print']);
            Route::get('/voucher/preview-template/{id}', [\App\Http\Controllers\ISP\VoucherPrintController::class, 'previewTemplate']);
        });
        
        Route::get('/service-profiles', $comingSoon)->name('service-profiles.index');
        Route::get('/pppoe-users', $comingSoon)->name('pppoe-users.index');
        Route::get('/hotspot-users', $comingSoon)->name('hotspot-users.index');
        Route::get('/vouchers', $comingSoon)->name('vouchers.index');
        Route::get('/invoices', $comingSoon)->name('invoices.index');
        Route::get('/payments', $comingSoon)->name('payments.index');
        Route::get('/reports', $comingSoon)->name('reports.index');
    });

    // ==================== CUSTOMER PORTAL ====================
    Route::middleware(['role:customer'])->prefix('customer-portal')->name('customer-portal.')->group(function () {
        Route::get('/dashboard', \App\Livewire\CustomerPortal\Dashboard::class)->name('dashboard');

        Route::prefix('billing')->name('billing.')->group(function () {
            Route::get('/invoices', \App\Livewire\CustomerPortal\Billing\InvoiceList::class)->name('invoice-list');
            Route::get('/invoices/{id}', \App\Livewire\CustomerPortal\Billing\InvoiceShow::class)->name('invoice-show');
            Route::get('/invoices/{id}/print-80mm', [\App\Http\Controllers\CustomerPortal\InvoiceController::class, 'print80mm'])->name('invoice-print-80mm');
        });

        Route::prefix('support')->name('support.')->group(function () {
            Route::get('/tickets', \App\Livewire\CustomerPortal\Support\TicketList::class)->name('ticket-list');
            Route::get('/tickets/create', \App\Livewire\CustomerPortal\Support\TicketCreate::class)->name('ticket-create');
            Route::get('/contact', \App\Livewire\CustomerPortal\Support\ContactAdmin::class)->name('contact-admin');
        });

        Route::get('/info', \App\Livewire\CustomerPortal\Info::class)->name('info');
        Route::get('/connection-info', \App\Livewire\CustomerPortal\ConnectionInfo::class)->name('self-service.connection-info');
        Route::get('/speed-test', \App\Livewire\CustomerPortal\SelfService\SpeedTest::class)->name('self-service.speed-test');
        Route::get('/profile', \App\Livewire\CustomerPortal\Profile\Profile::class)->name('profile');

        // Tambahan di self-service
        Route::prefix('self-service')->name('self-service.')->group(function () {
            Route::get('/change-plan', \App\Livewire\CustomerPortal\SelfService\ChangePlan::class)->name('change-plan');
            Route::get('/active-sessions', \App\Livewire\CustomerPortal\SelfService\ActiveSessions::class)->name('active-sessions');
            Route::get('/connected-devices', \App\Livewire\CustomerPortal\SelfService\ConnectedDevices::class)->name('connected-devices');
            Route::get('/speedtest', \App\Livewire\CustomerPortal\SelfService\SpeedTest::class)->name('speedtest');
        });
        
        Route::prefix('self-service')->name('self-service.')->group(function () {
            Route::get('/change-pppoe-password', \App\Livewire\CustomerPortal\SelfService\ChangePppoePassword::class)
                ->name('change-pppoe-password');
            Route::get('/change-onu-wifi-password', \App\Livewire\CustomerPortal\SelfService\ChangeOnuWifiPassword::class)
                ->name('change-onu-wifi-password');
            Route::get('/change-hotspot-credentials', \App\Livewire\CustomerPortal\SelfService\ChangeHotspotCredentials::class)
                ->name('change-hotspot-credentials');
        });
    });

    // Profile
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';

Route::get('/test-livewire', \App\Livewire\ISP\Technician\Attendance\Index::class);





