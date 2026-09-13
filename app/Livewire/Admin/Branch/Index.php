<?php

namespace App\Livewire\Admin\Branch;

use App\Livewire\AdminComponent;
use App\Models\Master\Branch;
use Illuminate\Support\Str;
use Livewire\WithPagination;

class Index extends AdminComponent
{
    use WithPagination;

    public string $search = '';
    public bool $showModal = false;
    public bool $isEditing = false;

    // Form fields
    public $form_id = null;
    public string $form_code = '';
    public string $form_name = '';
    public string $form_city = '';
    public string $form_province = '';
    public string $form_address = '';
    public string $form_phone = '';
    public string $form_email = '';
    public bool $form_is_active = true;
    public string $form_notes = '';

    protected function rules(): array
    {
        return [
            'form_code'      => 'required|string|max:20|unique:branches,code' . ($this->form_id ? ",{$this->form_id}" : ''),
            'form_name'      => 'required|string|max:255',
            'form_city'      => 'nullable|string|max:100',
            'form_province'  => 'nullable|string|max:100',
            'form_address'   => 'nullable|string',
            'form_phone'     => 'nullable|string|max:20',
            'form_email'     => 'nullable|email|max:255',
            'form_is_active' => 'boolean',
            'form_notes'     => 'nullable|string',
        ];
    }

    protected $messages = [
        'form_code.required'  => 'Kode cabang wajib diisi.',
        'form_code.unique'    => 'Kode cabang sudah digunakan.',
        'form_name.required'  => 'Nama cabang wajib diisi.',
        'form_email.email'    => 'Format email tidak valid.',
    ];

    public function mount()
    {
        parent::mount();
        $this->activeModule = 'admin';
        $this->activePage   = 'branches';
        $this->breadcrumbs  = [
            ['label' => 'Dashboard', 'url' => route('dashboard')],
            ['label' => 'Manajemen Cabang', 'url' => '#'],
        ];
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    // Open modal to CREATE
    public function openCreate(): void
    {
        $this->resetForm();
        $this->isEditing  = false;
        $this->showModal  = true;
        // Auto-suggest next code
        $lastBranch = Branch::withTrashed()->orderBy('id', 'desc')->first();
        $nextNum    = $lastBranch ? (intval(substr($lastBranch->code, -2)) + 1) : 1;
        $this->form_code = 'CBG-' . str_pad($nextNum, 2, '0', STR_PAD_LEFT);
    }

    // Open modal to EDIT
    public function openEdit(int $id): void
    {
        $branch = Branch::findOrFail($id);
        $this->form_id        = $branch->id;
        $this->form_code      = $branch->code;
        $this->form_name      = $branch->name;
        $this->form_city      = $branch->city ?? '';
        $this->form_province  = $branch->province ?? '';
        $this->form_address   = $branch->address ?? '';
        $this->form_phone     = $branch->phone ?? '';
        $this->form_email     = $branch->email ?? '';
        $this->form_is_active = (bool) $branch->is_active;
        $this->form_notes     = $branch->notes ?? '';
        $this->isEditing      = true;
        $this->showModal      = true;
    }

    public function save(): void
    {
        $this->validate();

        $data = [
            'code'       => strtoupper(trim($this->form_code)),
            'name'       => $this->form_name,
            'city'       => $this->form_city ?: null,
            'province'   => $this->form_province ?: null,
            'address'    => $this->form_address ?: null,
            'phone'      => $this->form_phone ?: null,
            'email'      => $this->form_email ?: null,
            'is_active'  => $this->form_is_active,
            'notes'      => $this->form_notes ?: null,
            'updated_by' => auth()->id(),
        ];

        if ($this->isEditing) {
            Branch::findOrFail($this->form_id)->update($data);
            session()->flash('success', "Cabang '{$this->form_name}' berhasil diperbarui.");
        } else {
            $data['created_by'] = auth()->id();
            Branch::create($data);
            session()->flash('success', "Cabang '{$this->form_name}' berhasil ditambahkan.");
        }

        $this->showModal = false;
        $this->resetForm();
    }

    public function toggleActive(int $id): void
    {
        $branch = Branch::findOrFail($id);
        $branch->update(['is_active' => !$branch->is_active]);
        session()->flash('success', "Status cabang berhasil diubah.");
    }

    public function delete(int $id): void
    {
        $branch = Branch::withCount('customers')->findOrFail($id);

        if ($branch->customers_count > 0) {
            session()->flash('error', "Tidak dapat menghapus: cabang ini masih memiliki {$branch->customers_count} pelanggan aktif.");
            return;
        }

        $branch->delete();
        session()->flash('success', "Cabang berhasil dihapus.");
    }

    private function resetForm(): void
    {
        $this->form_id        = null;
        $this->form_code      = '';
        $this->form_name      = '';
        $this->form_city      = '';
        $this->form_province  = '';
        $this->form_address   = '';
        $this->form_phone     = '';
        $this->form_email     = '';
        $this->form_is_active = true;
        $this->form_notes     = '';
        $this->resetValidation();
    }

    public function render()
    {
        $branches = Branch::withCount('customers')
            ->when($this->search, fn($q) =>
                $q->where('name', 'like', "%{$this->search}%")
                  ->orWhere('code', 'like', "%{$this->search}%")
                  ->orWhere('city', 'like', "%{$this->search}%")
            )
            ->orderBy('name')
            ->paginate(15);

        $stats = [
            'total'    => Branch::count(),
            'active'   => Branch::where('is_active', true)->count(),
            'inactive' => Branch::where('is_active', false)->count(),
        ];

        return view('livewire.admin.branch.index', compact('branches', 'stats'));
    }
}
