<?php

namespace App\Livewire\Support\TicketCategory;

use Livewire\Component;

class Index extends Component
{
    public array $categories = [
        ['id' => 1, 'name' => 'Billing & Payment', 'level' => 'high', 'poin' => 10, 'pic' => 'Finance Dept', 'active' => true],
        ['id' => 2, 'name' => 'Internet Connection', 'level' => 'critical', 'poin' => 20, 'pic' => 'NOC Team', 'active' => true],
        ['id' => 3, 'name' => 'New Installation', 'level' => 'medium', 'poin' => 5, 'pic' => 'Technician', 'active' => true],
        ['id' => 4, 'name' => 'General Inquiry', 'level' => 'low', 'poin' => 2, 'pic' => 'Customer Service', 'active' => false],
    ];

    public bool $showForm = false;
    public array $formData = [
        'name' => '',
        'level' => 'low',
        'poin' => 1,
        'pic' => '',
        'active' => true,
    ];

    public function openForm(?int $id = null): void
    {
        if ($id) {
            $cat = collect($this->categories)->firstWhere('id', $id);
            if ($cat) {
                $this->formData = $cat;
            }
        } else {
            $this->formData = [
                'name' => '',
                'level' => 'low',
                'poin' => 1,
                'pic' => '',
                'active' => true,
            ];
        }
        $this->showForm = true;
    }

    public function closeForm(): void
    {
        $this->showForm = false;
    }

    public function save(): void
    {
        session()->flash('success', 'Kategori tiket berhasil disimpan.');
        $this->showForm = false;
    }

    public function render()
    {
        return view('livewire.support.ticket-category.index')->layout('layouts.enterprise');
    }
}
