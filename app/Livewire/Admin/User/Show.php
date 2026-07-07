<?php

namespace App\Livewire\Admin\User;

use App\Livewire\AdminComponent;
use App\Models\User as UserModel;

class Show extends AdminComponent
{
    public $userId;
    public $user;

    public function mount($id = null)
    {
        parent::mount();
        $this->activeModule = 'admin';
        $this->activePage = 'users';
        $this->userId = $id;
        $this->user = UserModel::with('roles')->findOrFail($id);
        $this->breadcrumbs = [
            ['label' => 'Dashboard', 'url' => route('dashboard')],
            ['label' => 'Administration', 'url' => route('admin.users.index')],
            ['label' => 'Users', 'url' => route('admin.users.index')],
            ['label' => $this->user->name],
        ];
    }

    public function render()
    {
        return view('livewire.admin.user.show');
    }
}
