<?php

namespace App\Livewire\Admin\User;

use App\Livewire\AdminComponent;
use App\Models\User as UserModel;
use App\Models\Role;
use Illuminate\Support\Facades\Hash;

class Create extends AdminComponent
{
    public $name;
    public $email;
    public $password;
    public $whatsapp;
    public $wilayah;
    public $selectedRoles = [];

    public function mount()
    {
        parent::mount();
        $this->activeModule = 'admin';
        $this->activePage = 'users';
        $this->breadcrumbs = [
            ['label' => 'Dashboard', 'url' => route('dashboard')],
            ['label' => 'Administration', 'url' => route('admin.users.index')],
            ['label' => 'Users', 'url' => route('admin.users.index')],
            ['label' => 'Create'],
        ];
    }

    public function save()
    {
        $this->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|min:8',
            'whatsapp' => 'required|string|max:255',
            'wilayah' => 'required|string|max:255',
        ]);

        $user = UserModel::create([
            'name' => $this->name,
            'email' => $this->email,
            'password' => Hash::make($this->password),
            'whatsapp' => $this->whatsapp,
            'wilayah' => $this->wilayah,
        ]);

        if (!empty($this->selectedRoles)) {
            $user->roles()->sync($this->selectedRoles);
        }

        session()->flash('success', 'User berhasil dibuat!');
        return redirect()->route('admin.users.index');
    }

    public function render()
    {
        $roles = Role::all();
        return view('livewire.admin.user.create', compact('roles'));
    }
}
