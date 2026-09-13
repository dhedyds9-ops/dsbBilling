<?php
$file = 'D:/dsBilling/routes/web.php';
$content = file_get_contents($file);

$search = "Route::get('/technician-portal/dashboard', \App\Livewire\ISP\Technician\Dashboard::class)->name('technician.dashboard');";
$replace = $search . "
    Route::get('/technician-portal/my-jobs', \App\Livewire\ISP\Technician\MyJobs\Index::class)->name('technician.my-jobs.index');
    Route::get('/technician-portal/my-jobs/{job}', \App\Livewire\ISP\Technician\MyJobs\Show::class)->name('technician.my-jobs.show');
    Route::get('/technician-portal/installation/wizard', \App\Livewire\ISP\Technician\Installation\Wizard::class)->name('technician.installation.wizard');
    Route::get('/technician-portal/provisioning/{id}', \App\Livewire\ISP\Technician\Provisioning\Show::class)->name('technician.provisioning.show');
    Route::get('/technician-portal/attendance', \App\Livewire\ISP\Technician\Attendance\Index::class)->name('technician.attendance');
    
    // Fallback/Dummy routes to prevent MenuRegistry crashes
    Route::get('/technician-portal/dummy', fn()=>'dummy')->name('technician.history');
    Route::get('/technician-portal/dummy2', fn()=>'dummy')->name('technician.my-jobs.psb');
    Route::get('/technician-portal/dummy3', fn()=>'dummy')->name('technician.my-jobs.maintenance');
    Route::get('/technician-portal/dummy4', fn()=>'dummy')->name('technician.my-jobs.troubleshooting');
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
";

$content = str_replace($search, $replace, $content);
file_put_contents($file, $content);
echo "Added missing technician routes.\n";
?>
