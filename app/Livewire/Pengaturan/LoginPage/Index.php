<?php

namespace App\Livewire\Pengaturan\LoginPage;

use App\Livewire\AdminComponent;

class Index extends AdminComponent
{
    public string $activeModule = 'pengaturan';
    public string $activePage = 'loginpage';

    public string $githubUrl = 'https://github.com/dhedyds9-ops/dsloginpage';
    public string $downloadZipUrl = 'https://github.com/dhedyds9-ops/dsloginpage/archive/refs/heads/main.zip';

    public function render()
    {
        return view('livewire.pengaturan.loginpage.index');
    }
}
