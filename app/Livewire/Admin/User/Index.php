<?php

namespace App\Livewire\Admin\User;

use App\Livewire\Admin\BaseAdminComponent;
use App\Models\User as UserModel;

class Index extends BaseAdminComponent
{
    public function mount()
    {
        parent::mount();
        $this->activeModule = 'admin';
        $this->activePage = 'users';
        $this->filters = ['status' => ''];
        $this->breadcrumbs = [
            ['label' => 'Dashboard', 'url' => route('dashboard')],
            ['label' => 'Administration', 'url' => route('admin.users.index')],
            ['label' => 'Users'],
        ];
    }

    public function delete($id)
    {
        $user = UserModel::findOrFail($id);
        if ($user->id === auth()->id()) {
            session()->flash('error', 'Tidak bisa menghapus akun sendiri!');
            return;
        }
        $user->delete();
        session()->flash('success', 'User berhasil dihapus!');
    }

    public function export()
    {
        session()->flash('info', 'Export feature will be implemented later!');
    }

    public function render()
    {
        $query = UserModel::query()->with('roles');

        if ($this->search) {
            $query->where(function($q) {
                $q->where('name', 'like', '%' . $this->search . '%')
                  ->orWhere('email', 'like', '%' . $this->search . '%');
            });
        }

        $users = $query->orderBy($this->sortField, $this->sortDirection)
                      ->paginate($this->perPage);

        return view('livewire.admin.user.index', compact('users'));
    }
}
