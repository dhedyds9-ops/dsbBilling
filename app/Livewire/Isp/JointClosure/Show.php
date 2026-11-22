<?php

namespace App\Livewire\Isp\JointClosure;

use App\Livewire\AdminComponent;
use App\Models\ISP\JointClosure;
use Livewire\WithFileUploads;

class Show extends AdminComponent
{
    use WithFileUploads;

    public $closureId;
    public $closure;
    public string $activeTab = 'overview'; // overview, odps, odcs, history
    public $newPhoto;

    public function mount($id = null)
    {
        parent::mount();
        $this->activeModule = 'isp';
        $this->activePage = 'joint-closures';
        $this->closureId = $id;

        $this->loadClosure();

        $this->breadcrumbs = [
            ['label' => 'Dashboard', 'url' => route('dashboard')],
            ['label' => 'Infrastruktur Fiber', 'url' => route('isp.joint-closures.index')],
            ['label' => 'Joint Closure', 'url' => route('isp.joint-closures.index')],
            ['label' => $this->closure->name],
        ];
    }

    public function loadClosure()
    {
        $this->closure = JointClosure::with([
            'olt',
            'odc.olt',
            'parentClosure.olt',
            'childClosures',
            'odps.onus',
            'odcs',
            'branch',
            'vendor',
            'createdBy',
            'updatedBy',
        ])->findOrFail($this->closureId);
    }

    public function setTab(string $tab)
    {
        if (in_array($tab, ['overview', 'odps', 'odcs', 'history'], true)) {
            $this->activeTab = $tab;
        }
    }

    public function uploadPhoto()
    {
        $this->validate([
            'newPhoto' => 'required|image|max:10240',
        ]);

        $path = $this->newPhoto->store('nodes/closures', 'public');
        $photos = is_array($this->closure->photos) ? $this->closure->photos : [];
        $photos[] = '/storage/' . $path;

        $this->closure->update(['photos' => $photos]);
        $this->newPhoto = null;
        $this->loadClosure();

        session()->flash('success', 'Foto dokumentasi tiang/closure berhasil diunggah!');
    }

    public function deletePhoto($index)
    {
        $photos = is_array($this->closure->photos) ? $this->closure->photos : [];
        if (isset($photos[$index])) {
            unset($photos[$index]);
            $this->closure->update(['photos' => array_values($photos)]);
            $this->loadClosure();
            session()->flash('success', 'Foto dokumentasi berhasil dihapus.');
        }
    }

    public function render()
    {
        return view('livewire.isp.joint-closure.show', [
            'connectedOdps' => $this->closure->odps,
            'connectedOdcs' => $this->closure->odcs,
            'childClosures' => $this->closure->childClosures,
        ])->layout('layouts.admin');
    }
}
