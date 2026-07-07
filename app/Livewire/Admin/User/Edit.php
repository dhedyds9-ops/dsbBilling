<?php

namespace App\Livewire\Admin\User;

use App\Livewire\AdminComponent;
use App\Models\User as UserModel;
use App\Models\Role;
use Illuminate\Support\Facades\Hash;

class Edit extends AdminComponent
{
    public $userId;
    public $user;
    public $name;
    public $email;
    public $password;
    public $selectedRoles = [];

    public function mount($id = null)
    {
        parent::mount();
        $this->activeModule = 'admin';
        $this->activePage = 'users';
        $this->userId = $id;
        $this->user = UserModel::findOrFail($id);

        $this->name = $this->user->name;
        $this->email = $this->user->email;
        $this->selectedRoles = $this->user->roles->pluck('id')->toArray();
        $this->breadcrumbs = [
            ['label' => 'Dashboard', 'url' => route('dashboard')],
            ['label' => 'Administration', 'url' => route('admin.users.index')],
            ['label' => 'Users', 'url' => route('admin.users.index')],
            ['label' => $this->user->name, 'url' => route('admin.users.show', $this->userId)],
            ['label' => 'Edit'],
        ];
    }

    public function save()
    {
        $validation = [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $this->userId,
        ];

        if ($this->password) {
            $validation['password'] = 'min:8';
        }

        $this->validate($validation);

        $data = [
            'name' => $this->name,
            'email' => $this->email,
        ];

        if ($this->password) {
            $data['password'] = Hash::make($this->password);
        }

        $this->user->update($data);
        $this->user->roles()->sync($this->selectedRoles);

        session()->flash('success', 'User berhasil diperbarui!');
        return redirect()->route('admin.users.show', $this->userId);
    }

    public function render()
    {
        $roles = Role::all();
        return view('livewire.admin.user.edit', compact('roles'));
    }
}
